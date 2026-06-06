<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Models\TripItem;
use App\Services\TripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class TripController extends Controller
{
    public function __construct(private TripService $tripService)
    {
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(Trip::statuses())],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'car_id' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        $query = Trip::query()->orderByDesc('trip_date')->orderByDesc('id');

        if ($request->filled('date_from')) {
            $query->whereDate('trip_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trip_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        foreach (['driver_name', 'car_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, 'like', '%' . trim((string) $request->{$field}) . '%');
            }
        }

        $trips = $query->limit(100)->get();

        return $this->success($trips->map(fn (Trip $trip) => $this->tripData($trip))->values());
    }

    public function show(Trip $trip)
    {
        $trip->load([
            'tripItems.order',
            'tripItems.orderReceive',
            'costs',
        ]);

        return $this->success($this->tripData($trip, true));
    }

    public function items(Trip $trip)
    {
        $trip->load([
            'tripItems.order',
            'tripItems.orderReceive',
        ]);

        return $this->success($trip->tripItems->map(fn (TripItem $item) => $this->tripItemData($item))->values());
    }

    public function updateDeliveryStatus(TripItem $tripItem, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_status' => ['required', Rule::in(TripItem::deliveryStatuses())],
            'failed_reason' => ['required_if:delivery_status,' . TripItem::DELIVERY_STATUS_FAILED, 'nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $updated = $this->tripService->updateDeliveryStatus(
                $tripItem,
                $request->delivery_status,
                $request->note,
                $request->failed_reason,
                Auth::user()->name ?? null
            );
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 400);
        }

        return $this->success($this->tripItemData($updated->load(['order', 'orderReceive'])), 'บันทึกสถานะจัดส่งแล้ว');
    }

    public function updatePaymentStatus(TripItem $tripItem, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => ['required', Rule::in(TripItem::paymentStatuses())],
            'collected_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $updated = $this->tripService->updatePaymentCollection(
                $tripItem,
                $request->payment_status,
                $request->input('collected_amount'),
                $request->note,
                Auth::user()->name ?? null
            );
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 400);
        }

        return $this->success($this->tripItemData($updated->load(['order', 'orderReceive'])), 'บันทึกสถานะชำระเงินแล้ว');
    }

    public function parcel(string $parcelCode)
    {
        $parcel = OrderReceive::query()
            ->with([
                'order',
                'statusLogs.trip',
                'tripItems.trip',
            ])
            ->where('parcel_code', $parcelCode)
            ->first();

        if (! $parcel) {
            return $this->error('ไม่พบรหัสพัสดุ', 404);
        }

        $currentTripItem = $parcel->tripItems->sortByDesc('id')->first();

        return $this->success([
            'order' => $parcel->order ? $this->orderData($parcel->order) : null,
            'order_receive' => $this->orderReceiveData($parcel),
            'current_trip_item' => $currentTripItem ? $this->tripItemData($currentTripItem) : null,
            'status_logs' => $parcel->statusLogs->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])->map(function ($log) {
                return [
                    'id' => $log->id,
                    'trip_id' => $log->trip_id,
                    'trip_code' => $log->trip?->code,
                    'from_status' => $log->from_status,
                    'to_status' => $log->to_status,
                    'to_status_label' => TripItem::deliveryStatusLabel($log->to_status),
                    'note' => $log->note,
                    'created_by' => $log->created_by,
                    'created_at' => optional($log->created_at)->format('Y-m-d H:i:s'),
                ];
            })->values(),
        ]);
    }

    private function tripData(Trip $trip, bool $includeItems = false): array
    {
        $data = [
            'id' => $trip->id,
            'code' => $trip->code,
            'trip_date' => optional($trip->trip_date)->format('Y-m-d'),
            'driver_name' => $trip->driver_name,
            'driver_mobile' => $trip->driver_mobile,
            'car_id' => $trip->car_id,
            'area_name' => $trip->area_name,
            'status' => $trip->status,
            'status_label' => $trip->status_label,
            'total_parcels' => (int) $trip->total_parcels,
            'total_cod_amount' => (float) $trip->total_cod_amount,
            'collected_amount' => (float) $trip->collected_amount,
            'started_at' => optional($trip->started_at)->format('Y-m-d H:i:s'),
            'completed_at' => optional($trip->completed_at)->format('Y-m-d H:i:s'),
        ];

        if ($includeItems) {
            $items = $trip->tripItems;
            $data['summary'] = [
                'delivered_count' => $items->where('delivery_status', TripItem::DELIVERY_STATUS_DELIVERED)->count(),
                'failed_count' => $items->where('delivery_status', TripItem::DELIVERY_STATUS_FAILED)->count(),
                'returned_count' => $items->where('delivery_status', TripItem::DELIVERY_STATUS_RETURNED)->count(),
                'remaining_cod' => max(0, (float) $trip->total_cod_amount - (float) $trip->collected_amount),
                'financial' => $this->tripService->financialSummary($trip),
            ];
            $data['items'] = $items->map(fn (TripItem $item) => $this->tripItemData($item))->values();
        }

        return $data;
    }

    private function tripItemData(TripItem $item): array
    {
        return [
            'id' => $item->id,
            'trip_id' => $item->trip_id,
            'order_id' => $item->order_id,
            'order_receive_id' => $item->order_receive_id,
            'parcel_code' => $item->parcel_code,
            'delivery_status' => $item->delivery_status,
            'delivery_status_label' => $item->delivery_status_label,
            'payment_status' => $item->payment_status,
            'payment_status_label' => $item->payment_status_label,
            'cod_amount' => (float) $item->cod_amount,
            'collected_amount' => (float) $item->collected_amount,
            'failed_reason' => $item->failed_reason,
            'note' => $item->note,
            'delivered_at' => optional($item->delivered_at)->format('Y-m-d H:i:s'),
            'order' => $item->relationLoaded('order') && $item->order ? $this->orderData($item->order) : null,
            'order_receive' => $item->relationLoaded('orderReceive') && $item->orderReceive ? $this->orderReceiveData($item->orderReceive) : null,
        ];
    }

    private function orderData($order): array
    {
        return [
            'id' => $order->id,
            'code' => $order->code,
            'customer_name' => $order->customer_name,
            'customer_mobile' => $order->customer_mobile,
            'customer_address' => $order->customer_address,
            'parcel_amount' => (int) $order->parcel_amount,
            'parcel_total' => (float) $order->parcel_total,
        ];
    }

    private function orderReceiveData(OrderReceive $receiver): array
    {
        return [
            'id' => $receiver->id,
            'order_id' => $receiver->order_id,
            'parcel_code' => $receiver->parcel_code,
            'parcel_description' => $receiver->parcel_description,
            'receive_name' => $receiver->receive_name,
            'receive_mobile' => $receiver->receive_mobile,
            'receive_address' => $receiver->receive_address,
            'province_name' => $receiver->province_name,
            'amphures_name' => $receiver->amphures_name,
            'district_name' => $receiver->district_name,
            'zip_code' => $receiver->zip_code,
            'parcel_pickup_type' => $receiver->parcel_pickup_type,
            'payment_type' => $receiver->payment_type,
            'delivery_status' => $receiver->delivery_status,
            'payment_status' => $receiver->payment_status,
            'parcel_pice' => (float) $receiver->parcel_pice,
        ];
    }

    private function success($data = null, ?string $message = null)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ]);
    }

    private function error(string $message, int $status)
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => $message,
        ], $status);
    }

    private function validationError($validator)
    {
        return response()->json([
            'success' => false,
            'data' => [
                'errors' => $validator->errors(),
            ],
            'message' => 'ข้อมูลไม่ถูกต้อง',
        ], 422);
    }
}
