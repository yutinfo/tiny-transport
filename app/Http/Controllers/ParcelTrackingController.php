<?php

namespace App\Http\Controllers;

use App\Models\OrderReceive;
use App\Models\TripItem;
use App\Support\DataTable;
use Illuminate\Http\Request;

class ParcelTrackingController extends Controller
{
    public function search(Request $request)
    {
        return view('admin.parcel.search', [
            'keyword' => trim((string) $request->input('q', '')),
        ]);
    }

    /**
     * Server-side DataTables endpoint for parcel search. Shows all parcels and
     * searches in MySQL; the page's q box feeds the DataTables global search.
     */
    public function searchData(Request $request)
    {
        $base = OrderReceive::query()
            ->with(['order', 'tripItems.trip'])
            ->orderByDesc('id');

        $columns = [
            ['key' => 'parcel_code', 'db' => 'order_receives.parcel_code', 'orderable' => true, 'searchable' => true],
            ['key' => 'order_code', 'orderable' => false, 'searchable' => false],
            ['key' => 'receive_name', 'db' => 'order_receives.receive_name', 'orderable' => false, 'searchable' => true],
            ['key' => 'destination', 'orderable' => false, 'searchable' => false],
            ['key' => 'delivery_status', 'orderable' => false, 'searchable' => false],
            ['key' => 'created_at', 'db' => 'order_receives.created_at', 'orderable' => true, 'searchable' => false],
            ['key' => 'actions', 'orderable' => false, 'searchable' => false],
        ];

        $payload = DataTable::respond($request, $base, $columns, function (OrderReceive $parcel) {
            $destination = trim(implode(' ', array_filter([
                $parcel->receive_address,
                $parcel->district_name,
                $parcel->amphures_name,
                $parcel->province_name,
                $parcel->zip_code,
            ])));

            $status = $parcel->delivery_status ?: TripItem::DELIVERY_STATUS_WAITING;

            return [
                'parcel_code' => e($parcel->parcel_code),
                'order_code' => e($parcel->order->code ?? '-'),
                'receive_name' => e($parcel->receive_name ?: '-')
                    . '<small class="d-block text-muted">' . e($parcel->receive_mobile) . '</small>',
                'destination' => e($destination),
                'delivery_status' => e(TripItem::deliveryStatusLabel($status)),
                'created_at' => optional($parcel->created_at)->format('Y-m-d'),
                'actions' => view('admin.parcel._row-actions', ['parcel' => $parcel])->render(),
            ];
        });

        return response()->json($payload);
    }

    public function code(string $parcelCode)
    {
        $parcel = OrderReceive::query()
            ->where('parcel_code', $parcelCode)
            ->first();

        if (! $parcel) {
            return redirect()
                ->route('admin.parcels.search', ['q' => $parcelCode])
                ->withErrors(['parcel_code' => 'ไม่พบรหัสพัสดุ ' . $parcelCode]);
        }

        return redirect()->route('admin.parcels.tracking', $parcel);
    }

    public function show(OrderReceive $orderReceive)
    {
        $orderReceive->load([
            'order',
            'statusLogs.trip',
            'tripItems.trip',
            'notifications',
        ]);

        $currentTripItem = $orderReceive->tripItems()
            ->with('trip')
            ->latest('id')
            ->first();

        return view('admin.parcel.tracking', [
            'parcel' => $orderReceive,
            'order' => $orderReceive->order,
            'currentTripItem' => $currentTripItem,
            'currentTrip' => $currentTripItem?->trip,
            'logs' => $orderReceive->statusLogs->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ]),
            'notifications' => $orderReceive->notifications->sortByDesc('created_at'),
            'deliveryStatusLabels' => TripItem::deliveryStatusLabels(),
            'paymentStatusLabels' => TripItem::paymentStatusLabels(),
        ]);
    }

    public function storeNotification(OrderReceive $orderReceive, Request $request)
    {
        $data = $request->validate([
            'channel' => ['required', 'in:sms,line,email,manual'],
            'recipient' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:1000'],
        ], [
            'required' => ':attribute จำเป็นต้องกรอก',
            'in' => 'ช่องทางที่ระบุไม่ถูกต้อง',
            'max' => ':attribute ยาวเกินไป',
        ], [
            'channel' => 'ช่องทางแจ้งเตือน',
            'recipient' => 'ผู้รับ',
            'message' => 'ข้อความ',
        ]);

        $notification = $orderReceive->notifications()->create(array_merge($data, [
            'status' => 'pending',
            'created_by' => auth()->user()->name ?? 'System',
        ]));

        $service = app(\App\Services\ParcelNotificationService::class);
        $service->send($notification);

        return redirect()->back()->with('success', 'บันทึกประวัติการแจ้งเตือนสำเร็จ');
    }
}
