<?php

namespace App\Http\Controllers;

use App\Models\OrderReceive;
use App\Models\TripItem;
use Illuminate\Http\Request;

class ParcelTrackingController extends Controller
{
    public function search(Request $request)
    {
        $keyword = trim((string) $request->input('q', ''));
        $parcels = collect();

        if ($keyword !== '') {
            $parcels = OrderReceive::query()
                ->with(['order', 'tripItems.trip'])
                ->where('parcel_code', 'like', '%' . $keyword . '%')
                ->orderByDesc('id')
                ->limit(20)
                ->get();
        }

        return view('admin.parcel.search', [
            'keyword' => $keyword,
            'parcels' => $parcels,
        ]);
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
            'deliveryStatusLabels' => TripItem::deliveryStatusLabels(),
            'paymentStatusLabels' => TripItem::paymentStatusLabels(),
        ]);
    }
}
