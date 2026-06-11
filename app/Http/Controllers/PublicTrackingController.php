<?php

namespace App\Http\Controllers;

use App\Models\OrderReceive;
use App\Models\TripItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicTrackingController extends Controller
{
    /**
     * Public, unauthenticated parcel tracking lookup.
     *
     * Accepts up to 10 parcel codes and returns one result per code in the
     * order requested. Each result exposes only customer-facing tracking
     * information (status, receiver, timeline) — never internal cost data.
     */
    public function track(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codes' => ['required', 'array', 'min:1', 'max:10'],
            'codes.*' => ['required', 'string', 'max:50'],
        ], [
            'codes.required' => 'กรุณาระบุรหัสพัสดุ',
            'codes.array' => 'รูปแบบรหัสพัสดุไม่ถูกต้อง',
            'codes.min' => 'กรุณาระบุรหัสพัสดุอย่างน้อย 1 รายการ',
            'codes.max' => 'ค้นหาได้สูงสุด 10 รายการต่อครั้ง',
            'codes.*.required' => 'รหัสพัสดุห้ามว่าง',
            'codes.*.string' => 'รหัสพัสดุไม่ถูกต้อง',
            'codes.*.max' => 'รหัสพัสดุยาวเกินไป',
        ]);

        // Preserve the caller's order and de-duplicate while keeping first-seen
        // position, so the response cards line up with what was searched.
        $codes = [];
        foreach ($validated['codes'] as $code) {
            $code = trim($code);
            if ($code !== '' && ! in_array($code, $codes, true)) {
                $codes[] = $code;
            }
        }

        // Single query for all requested codes, then map back per code.
        $parcels = OrderReceive::query()
            ->whereIn('parcel_code', $codes)
            ->with([
                'order',
                'statusLogs' => function ($query) {
                    $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
                },
                'tripItems.trip',
            ])
            ->get()
            ->keyBy('parcel_code');

        $results = [];
        foreach ($codes as $code) {
            /** @var \App\Models\OrderReceive|null $parcel */
            $parcel = $parcels->get($code);

            if (! $parcel) {
                $results[] = [
                    'code' => $code,
                    'found' => false,
                ];

                continue;
            }

            $results[] = $this->presentParcel($code, $parcel);
        }

        return response()->json($results);
    }

    /**
     * Shape a single parcel into the public tracking payload.
     */
    private function presentParcel(string $code, OrderReceive $parcel): array
    {
        // The current trip item carries the live delivery status and the COD
        // amount for this parcel (order_receives has no cod column).
        $currentTripItem = $parcel->tripItems
            ->sortByDesc('id')
            ->first();

        $rawStatus = $parcel->delivery_status
            ?: ($currentTripItem->delivery_status ?? TripItem::DELIVERY_STATUS_WAITING);

        $codAmount = $currentTripItem
            ? (float) $currentTripItem->cod_amount
            : 0.0;

        $address = trim(implode(' ', array_filter([
            $parcel->receive_address,
            $parcel->district_name,
            $parcel->amphures_name,
            $parcel->province_name,
            $parcel->zip_code,
        ])));

        return [
            'code' => $code,
            'found' => true,
            'receive_name' => $parcel->receive_name ?: null,
            'receive_address' => $address !== '' ? $address : null,
            'status' => $this->statusBucket($rawStatus),
            'status_label' => TripItem::deliveryStatusLabel($rawStatus),
            'cod_amount' => $codAmount,
            'payment_type' => $parcel->payment_type,
            'timeline' => $this->buildTimeline($parcel),
        ];
    }

    /**
     * Collapse the internal delivery statuses into the four public buckets the
     * frontend badges expect: waiting | in_transit | delivered | failed.
     */
    private function statusBucket(?string $status): string
    {
        return [
            TripItem::DELIVERY_STATUS_WAITING => 'waiting',
            TripItem::DELIVERY_STATUS_PICKED_UP => 'in_transit',
            TripItem::DELIVERY_STATUS_IN_TRANSIT => 'in_transit',
            TripItem::DELIVERY_STATUS_DELIVERED => 'delivered',
            TripItem::DELIVERY_STATUS_FAILED => 'failed',
            TripItem::DELIVERY_STATUS_RETURNED => 'failed',
        ][$status] ?? 'waiting';
    }

    /**
     * Build the chronological timeline from parcel_status_logs.
     */
    private function buildTimeline(OrderReceive $parcel): array
    {
        return $parcel->statusLogs->map(function ($log) {
            return [
                'event' => TripItem::deliveryStatusLabel($log->to_status),
                'note' => $log->note,
                'datetime' => optional($log->created_at)->toIso8601String(),
            ];
        })->values()->all();
    }
}
