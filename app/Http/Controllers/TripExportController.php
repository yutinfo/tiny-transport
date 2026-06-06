<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TripExportController extends Controller
{
    public function tripsCsv(Request $request)
    {
        $request->validate($this->tripFilterRules());

        $query = $this->filteredTrips($request)
            ->orderBy('trip_date')
            ->orderBy('code');

        return $this->streamCsv('trips-summary-' . now()->format('Ymd-His') . '.csv', [
            'วันที่รอบขนส่ง',
            'รหัสรอบ',
            'พนักงานขับรถ',
            'ทะเบียนรถ',
            'พื้นที่',
            'สถานะ',
            'จำนวนพัสดุ',
            'ยอด COD',
            'ยอดเก็บแล้ว',
            'ยอดคงเหลือ',
            'เวลาปิดรอบ',
        ], function ($handle) use ($query) {
            foreach ($query->cursor() as $trip) {
                fputcsv($handle, [
                    optional($trip->trip_date)->format('Y-m-d'),
                    $trip->code,
                    $trip->driver_name,
                    $trip->car_id,
                    $trip->area_name,
                    Trip::statusLabel($trip->status),
                    $trip->total_parcels,
                    number_format((float) $trip->total_cod_amount, 2, '.', ''),
                    number_format((float) $trip->collected_amount, 2, '.', ''),
                    number_format(max(0, (float) $trip->total_cod_amount - (float) $trip->collected_amount), 2, '.', ''),
                    optional($trip->completed_at)->format('Y-m-d H:i:s'),
                ]);
            }
        });
    }

    public function tripItemsCsv(Trip $trip)
    {
        return $this->streamCsv('trip-items-' . $trip->code . '.csv', [
            'รหัสรอบ',
            'รหัสพัสดุ',
            'รหัสออเดอร์',
            'ชื่อผู้ฝาก',
            'เบอร์ผู้ฝาก',
            'ชื่อผู้รับ',
            'เบอร์ผู้รับ',
            'ที่อยู่ปลายทาง',
            'สถานะจัดส่ง',
            'สถานะชำระเงิน',
            'ยอด COD',
            'ยอดเก็บแล้ว',
            'เหตุผลส่งไม่สำเร็จ',
            'เวลาส่งสำเร็จ',
        ], function ($handle) use ($trip) {
            TripItem::query()
                ->with(['order', 'orderReceive'])
                ->where('trip_id', $trip->id)
                ->orderBy('id')
                ->chunk(500, function ($items) use ($handle, $trip) {
                    foreach ($items as $item) {
                        $receiver = $item->orderReceive;
                        $order = $item->order;

                        fputcsv($handle, [
                            $trip->code,
                            $item->parcel_code,
                            $order?->code,
                            $order?->customer_name,
                            $order?->customer_mobile,
                            $receiver?->receive_name,
                            $receiver?->receive_mobile,
                            $this->destinationAddress($receiver),
                            TripItem::deliveryStatusLabel($item->delivery_status),
                            TripItem::paymentStatusLabel($item->payment_status),
                            number_format((float) $item->cod_amount, 2, '.', ''),
                            number_format((float) $item->collected_amount, 2, '.', ''),
                            $item->failed_reason,
                            optional($item->delivered_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });
        });
    }

    public function tripCodCsv(Trip $trip)
    {
        return $this->streamCsv('trip-cod-' . $trip->code . '.csv', [
            'รหัสรอบ',
            'พนักงานขับรถ',
            'รหัสพัสดุ',
            'ชื่อผู้รับ',
            'ยอด COD',
            'ยอดเก็บแล้ว',
            'สถานะชำระเงิน',
            'สถานะจัดส่ง',
        ], function ($handle) use ($trip) {
            TripItem::query()
                ->with('orderReceive')
                ->where('trip_id', $trip->id)
                ->where('cod_amount', '>', 0)
                ->orderBy('id')
                ->chunk(500, function ($items) use ($handle, $trip) {
                    foreach ($items as $item) {
                        fputcsv($handle, [
                            $trip->code,
                            $trip->driver_name,
                            $item->parcel_code,
                            $item->orderReceive?->receive_name,
                            number_format((float) $item->cod_amount, 2, '.', ''),
                            number_format((float) $item->collected_amount, 2, '.', ''),
                            TripItem::paymentStatusLabel($item->payment_status),
                            TripItem::deliveryStatusLabel($item->delivery_status),
                        ]);
                    }
                });
        });
    }

    private function streamCsv(string $filename, array $headers, callable $writer)
    {
        return response()->streamDownload(function () use ($headers, $writer) {
            echo "\xEF\xBB\xBF";

            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            $writer($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredTrips(Request $request): Builder
    {
        $query = Trip::query();

        if ($request->filled('date_from')) {
            $query->whereDate('trip_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trip_date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $driver = $request->filled('driver_name') ? $request->driver_name : $request->input('driver');
        if (filled($driver)) {
            $query->where('driver_name', 'like', '%' . trim($driver) . '%');
        }

        if ($request->filled('car_id')) {
            $query->where('car_id', 'like', '%' . trim($request->car_id) . '%');
        }

        return $query;
    }

    private function tripFilterRules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'status' => ['nullable', 'in:' . implode(',', Trip::statuses())],
            'driver' => ['nullable', 'string', 'max:100'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'car_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    private function destinationAddress($receiver): string
    {
        if (! $receiver) {
            return '';
        }

        return trim(collect([
            $receiver->receive_address,
            $receiver->district_name,
            $receiver->amphures_name,
            $receiver->province_name,
            $receiver->zip_code,
        ])->filter()->implode(' '));
    }
}
