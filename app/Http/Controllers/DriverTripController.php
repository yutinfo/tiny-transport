<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripItem;
use App\Services\TripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class DriverTripController extends Controller
{
    public function __construct(private TripService $tripService)
    {
    }

    public function index()
    {
        $trips = Trip::query()
            ->where('driver_user_id', Auth::id())
            ->whereNotIn('status', [Trip::STATUS_COMPLETED, Trip::STATUS_CANCELLED])
            ->orderByDesc('trip_date')
            ->orderByDesc('id')
            ->withCount('tripItems')
            ->paginate(10);

        return view('driver.trips.index', [
            'trips' => $trips,
        ]);
    }

    public function showDriverTrip(Trip $trip)
    {
        $this->ensureDriverOwnsTrip($trip);

        return $this->renderTrip($trip, 'driver.trips.show');
    }

    private function ensureDriverOwnsTrip(Trip $trip): void
    {
        if ((int) $trip->driver_user_id !== (int) Auth::id()) {
            abort(403);
        }
    }

    public function show(Trip $trip)
    {
        return $this->renderTrip($trip, 'admin.trip.driver');
    }

    private function renderTrip(Trip $trip, string $view)
    {
        $trip->load([
            'tripItems.order',
            'tripItems.orderReceive',
        ]);

        $items = $trip->tripItems->sortBy('id');
        $delivered = $items->where('delivery_status', TripItem::DELIVERY_STATUS_DELIVERED)->count();
        $failed = $items->where('delivery_status', TripItem::DELIVERY_STATUS_FAILED)->count();
        $returned = $items->where('delivery_status', TripItem::DELIVERY_STATUS_RETURNED)->count();

        return view($view, [
            'data' => $trip,
            'items' => $items,
            'summary' => [
                'total_parcels' => $items->count(),
                'delivered_count' => $delivered,
                'failed_count' => $failed,
                'remaining_count' => max(0, $items->count() - $delivered - $failed - $returned),
                'total_cod_amount' => (float) $items->sum('cod_amount'),
                'collected_amount' => (float) $items->sum('collected_amount'),
            ],
            'deliveryStatusLabels' => TripItem::deliveryStatusLabels(),
            'paymentStatusLabels' => TripItem::paymentStatusLabels(),
            'failedReasons' => $this->failedReasons(),
            'readOnly' => $this->isReadOnly($trip),
        ]);
    }

    private function ensureDriverOwnsTripItem(TripItem $tripItem): TripItem
    {
        $tripItem = $tripItem->fresh(['trip']) ?: $tripItem;

        if (! $tripItem->trip || (int) $tripItem->trip->driver_user_id !== (int) Auth::id()) {
            abort(403);
        }

        return $tripItem;
    }

    private function handleDeliveryStatusUpdate(TripItem $tripItem, Request $request): void
    {
        $request->validate([
            'delivery_status' => [
                'required',
                Rule::in([
                    TripItem::DELIVERY_STATUS_DELIVERED,
                    TripItem::DELIVERY_STATUS_FAILED,
                    TripItem::DELIVERY_STATUS_RETURNED,
                ]),
            ],
            'failed_reason' => ['required_if:delivery_status,' . TripItem::DELIVERY_STATUS_FAILED, 'nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'required' => ':attribute จำเป็นต้องกรอก',
            'required_if' => ':attribute จำเป็นต้องกรอกเมื่อจัดส่งไม่สำเร็จ',
            'max' => ':attribute ยาวเกินไป',
        ], [
            'delivery_status' => 'สถานะจัดส่ง',
            'failed_reason' => 'เหตุผลที่จัดส่งไม่สำเร็จ',
            'note' => 'หมายเหตุ',
        ]);

        $this->tripService->updateDeliveryStatus(
            $tripItem,
            $request->delivery_status,
            $request->note,
            $request->failed_reason,
            $this->userName()
        );
    }

    public function updateDeliveryStatus(TripItem $tripItem, Request $request)
    {
        try {
            $this->handleDeliveryStatusUpdate($tripItem, $request);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['delivery_status' => $exception->getMessage()]);
        }

        return redirect()->route('admin.trips.driver', $tripItem->trip_id)->with('success', 'บันทึกสถานะจัดส่งแล้ว');
    }

    public function updateDriverDeliveryStatus(TripItem $tripItem, Request $request)
    {
        $tripItem = $this->ensureDriverOwnsTripItem($tripItem);

        try {
            $this->handleDeliveryStatusUpdate($tripItem, $request);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['delivery_status' => $exception->getMessage()]);
        }

        return redirect()->route('driver.trips.show', $tripItem->trip_id)->with('success', 'บันทึกสถานะจัดส่งแล้ว');
    }

    private function handlePaymentStatusUpdate(TripItem $tripItem, Request $request): void
    {
        $tripItem = $tripItem->fresh(['trip']) ?: $tripItem;

        $request->validate([
            'payment_status' => ['required', Rule::in([TripItem::PAYMENT_STATUS_PAID])],
            'collected_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'required' => ':attribute จำเป็นต้องกรอก',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
            'min' => ':attribute ต้องไม่ติดลบ',
        ], [
            'payment_status' => 'สถานะชำระเงิน',
            'collected_amount' => 'ยอดเก็บเงิน',
            'note' => 'หมายเหตุ',
        ]);

        if ((float) $tripItem->cod_amount <= 0) {
            throw new InvalidArgumentException('พัสดุนี้ไม่มียอด COD ให้เก็บ');
        }

        if ($tripItem->delivery_status !== TripItem::DELIVERY_STATUS_DELIVERED) {
            throw new InvalidArgumentException('เก็บเงิน COD ได้หลังจัดส่งสำเร็จเท่านั้น');
        }

        $this->tripService->updatePaymentCollection(
            $tripItem,
            $request->payment_status,
            $request->input('collected_amount', $tripItem->cod_amount),
            $request->note,
            $this->userName()
        );
    }

    public function updatePaymentStatus(TripItem $tripItem, Request $request)
    {
        try {
            $this->handlePaymentStatusUpdate($tripItem, $request);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['payment_status' => $exception->getMessage()]);
        }

        return redirect()->route('admin.trips.driver', $tripItem->trip_id)->with('success', 'บันทึกการเก็บเงินแล้ว');
    }

    public function updateDriverPaymentStatus(TripItem $tripItem, Request $request)
    {
        $tripItem = $this->ensureDriverOwnsTripItem($tripItem);

        try {
            $this->handlePaymentStatusUpdate($tripItem, $request);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['payment_status' => $exception->getMessage()]);
        }

        return redirect()->route('driver.trips.show', $tripItem->trip_id)->with('success', 'บันทึกการเก็บเงินแล้ว');
    }

    private function failedReasons(): array
    {
        return [
            'ติดต่อไม่ได้',
            'ไม่มีผู้รับ',
            'ที่อยู่ผิด',
            'เลื่อนส่ง',
            'ลูกค้าปฏิเสธรับ',
            'อื่น ๆ',
        ];
    }

    public function startTrip(Trip $trip)
    {
        $this->ensureDriverOwnsTrip($trip);

        try {
            $this->tripService->startTrip($trip, $this->userName());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['trip' => $exception->getMessage()]);
        }

        return redirect()->route('driver.trips.show', $trip)->with('success', 'เริ่มจัดส่งรอบขนส่งนี้เรียบร้อยแล้ว');
    }

    public function submitTrip(Trip $trip)
    {
        $this->ensureDriverOwnsTrip($trip);

        try {
            $this->tripService->submitTrip($trip, $this->userName());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['trip' => $exception->getMessage()]);
        }

        return redirect()->route('driver.trips.show', $trip)->with('success', 'ส่งยอดและปิดรอบจัดส่งเรียบร้อยแล้ว รอการตรวจสอบจากเจ้าหน้าที่');
    }

    private function isReadOnly(Trip $trip): bool
    {
        return $trip->status !== Trip::STATUS_IN_TRANSIT;
    }

    private function userName(): ?string
    {
        return Auth::user()->name ?? null;
    }
}
