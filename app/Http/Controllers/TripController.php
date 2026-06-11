<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Models\TripCost;
use App\Models\TripItem;
use App\Models\User;
use App\Services\TripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class TripController extends Controller
{
    public function __construct(private TripService $tripService)
    {
    }

    public function index(Request $request)
    {
        $query = Trip::query()->orderByDesc('trip_date')->orderByDesc('id');

        if ($request->filled('date_from')) {
            $query->whereDate('trip_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trip_date', '<=', $request->date_to);
        }

        foreach (['status', 'driver_name', 'car_id', 'area_name'] as $field) {
            if ($request->filled($field)) {
                $operator = $field === 'status' ? '=' : 'like';
                $value = $field === 'status' ? $request->{$field} : '%' . trim($request->{$field}) . '%';
                $query->where($field, $operator, $value);
            }
        }

        return view('admin.trip.list', [
            'data' => $query->paginate(20)->appends($request->query()),
            'selected' => $request->only(['date_from', 'date_to', 'status', 'driver_name', 'car_id', 'area_name']),
            'statusLabels' => Trip::statusLabels(),
        ]);
    }

    public function create()
    {
        return view('admin.trip.create', [
            'data' => new Trip([
                'trip_date' => now()->toDateString(),
            ]),
            'drivers' => $this->driversForForm(),
            'legacyDriverUsers' => $this->legacyDriverUsers(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->applySelectedDriver($this->validatedTripData($request), $request);
        $data['created_by'] = Auth::user()->name ?? null;
        $data['updated_by'] = Auth::user()->name ?? null;

        $trip = $this->tripService->createTrip($data);

        return redirect()->route('admin.trips.show', $trip)->with('success', 'สร้างรอบขนส่งแล้ว');
    }

    public function show(Trip $trip)
    {
        $trip->load([
            'tripItems.order',
            'tripItems.orderReceive.statusLogs',
            'tripItems.orderReceive.order',
            'costs',
        ]);

        $items = $trip->tripItems;

        return view('admin.trip.show', [
            'data' => $trip,
            'items' => $items,
            'summary' => [
                'delivered_count' => $items->where('delivery_status', TripItem::DELIVERY_STATUS_DELIVERED)->count(),
                'failed_count' => $items->where('delivery_status', TripItem::DELIVERY_STATUS_FAILED)->count(),
                'returned_count' => $items->where('delivery_status', TripItem::DELIVERY_STATUS_RETURNED)->count(),
                'remaining_cod' => max(0, (float) $trip->total_cod_amount - (float) $trip->collected_amount),
            ],
            'deliveryStatusLabels' => TripItem::deliveryStatusLabels(),
            'paymentStatusLabels' => TripItem::paymentStatusLabels(),
            'costTypeLabels' => TripCost::typeLabels(),
            'financialSummary' => $this->tripService->financialSummary($trip),
            'readOnly' => $this->isReadOnly($trip),
        ]);
    }

    public function edit(Trip $trip)
    {
        if ($this->isReadOnly($trip)) {
            return redirect()->route('admin.trips.show', $trip)->withErrors(['trip' => 'รอบขนส่งที่เสร็จสิ้นหรือยกเลิกแล้วแก้ไขไม่ได้']);
        }

        return view('admin.trip.edit', [
            'data' => $trip,
            'drivers' => $this->driversForForm($trip->driver_id),
            'legacyDriverUsers' => $this->legacyDriverUsers(),
        ]);
    }

    public function update(Request $request, Trip $trip)
    {
        if ($this->isReadOnly($trip)) {
            return redirect()->route('admin.trips.show', $trip)->withErrors(['trip' => 'รอบขนส่งที่เสร็จสิ้นหรือยกเลิกแล้วแก้ไขไม่ได้']);
        }

        $data = $this->applySelectedDriver($this->validatedTripData($request, $trip), $request, $trip);
        $data['updated_by'] = Auth::user()->name ?? null;
        $trip->update($data);

        return redirect()->route('admin.trips.show', $trip)->with('success', 'บันทึกรอบขนส่งแล้ว');
    }

    public function start(Trip $trip)
    {
        return $this->runTripAction(fn () => $this->tripService->startTrip($trip, $this->userName()), $trip, 'เริ่มรอบขนส่งแล้ว');
    }

    public function assignStatus(Trip $trip)
    {
        return $this->runTripAction(fn () => $this->tripService->assignTrip($trip, $this->userName()), $trip, 'มอบหมายรอบขนส่งแล้ว');
    }

    public function cancel(Trip $trip)
    {
        return $this->runTripAction(fn () => $this->tripService->cancelTrip($trip, $this->userName()), $trip, 'ยกเลิกรอบขนส่งแล้ว');
    }

    public function complete(Trip $trip)
    {
        return $this->runTripAction(fn () => $this->tripService->completeTrip($trip, $this->userName()), $trip, 'ปิดรอบขนส่งแล้ว');
    }

    public function assign(Trip $trip, Request $request)
    {
        if ($this->isReadOnly($trip)) {
            return redirect()->route('admin.trips.show', $trip)->withErrors(['trip' => 'รอบขนส่งนี้เพิ่มพัสดุไม่ได้']);
        }

        $query = OrderReceive::query()
            ->with('order')
            ->where(function ($query) {
                $query->where('delivery_status', TripItem::DELIVERY_STATUS_WAITING)
                    ->orWhereNull('delivery_status');
            })
            ->whereDoesntHave('tripItems', function ($query) {
                // A FAILED item does not block re-assignment (the parcel is re-queued);
                // RETURNED does block (terminal — sent back to the warehouse).
                $query->whereNotIn('delivery_status', [
                    TripItem::DELIVERY_STATUS_FAILED,
                ])->whereHas('trip', function ($tripQuery) {
                    $tripQuery->where('status', '!=', Trip::STATUS_CANCELLED);
                });
            })
            ->orderByDesc('id');

        if ($request->filled('date_from')) {
            $query->whereHas('order', fn ($orderQuery) => $orderQuery->whereDate('created_at', '>=', $request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->whereHas('order', fn ($orderQuery) => $orderQuery->whereDate('created_at', '<=', $request->date_to));
        }

        foreach (['province_name', 'amphures_name', 'payment_type', 'parcel_pickup_type'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->{$field});
            }
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('parcel_code', 'like', "%{$keyword}%")
                    ->orWhere('receive_name', 'like', "%{$keyword}%")
                    ->orWhere('receive_mobile', 'like', "%{$keyword}%")
                    ->orWhereHas('order', fn ($orderQuery) => $orderQuery->where('code', 'like', "%{$keyword}%"));
            });
        }

        return view('admin.trip.assign', [
            'data' => $trip,
            'candidates' => $query->paginate(20)->appends($request->query()),
            'selected' => $request->only(['date_from', 'date_to', 'province_name', 'amphures_name', 'payment_type', 'parcel_pickup_type', 'keyword']),
        ]);
    }

    public function assignItems(Trip $trip, Request $request)
    {
        $request->validate([
            'order_receive_ids' => ['required', 'array', 'min:1'],
            'order_receive_ids.*' => ['integer', 'exists:order_receives,id'],
        ], [], [
            'order_receive_ids' => 'รายการพัสดุ',
        ]);

        try {
            $this->tripService->assignItems($trip, $request->input('order_receive_ids', []), $this->userName());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['assign' => $exception->getMessage()]);
        }

        return redirect()->route('admin.trips.show', $trip)->with('success', 'เพิ่มพัสดุเข้ารอบแล้ว');
    }

    public function removeItem(TripItem $tripItem)
    {
        $trip = $tripItem->trip;

        try {
            $this->tripService->removeItem($tripItem);
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['remove' => $exception->getMessage()]);
        }

        return redirect()->route('admin.trips.show', $trip)->with('success', 'ลบพัสดุออกจากรอบแล้ว');
    }

    public function updateDeliveryStatus(TripItem $tripItem, Request $request)
    {
        $request->validate([
            'delivery_status' => ['required', Rule::in(TripItem::deliveryStatuses())],
            'failed_reason' => ['required_if:delivery_status,' . TripItem::DELIVERY_STATUS_FAILED, 'nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ], [
            'required' => ':attribute จำเป็นต้องกรอก',
            'required_if' => ':attribute จำเป็นต้องกรอกเมื่อจัดส่งไม่สำเร็จ',
            'max' => ':attribute ยาวเกินไป',
        ], [
            'delivery_status' => 'สถานะจัดส่ง',
            'failed_reason' => 'เหตุผลที่จัดส่งไม่สำเร็จ',
            'note' => 'หมายเหตุ',
        ]);

        try {
            $this->tripService->updateDeliveryStatus(
                $tripItem,
                $request->delivery_status,
                $request->note,
                $request->failed_reason,
                $this->userName()
            );
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['delivery_status' => $exception->getMessage()]);
        }

        return redirect()->route('admin.trips.show', $tripItem->trip_id)->with('success', 'บันทึกสถานะจัดส่งแล้ว');
    }

    public function updatePaymentStatus(TripItem $tripItem, Request $request)
    {
        $request->validate([
            'payment_status' => ['required', Rule::in(TripItem::paymentStatuses())],
            'collected_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ], [
            'required' => ':attribute จำเป็นต้องกรอก',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
            'min' => ':attribute ต้องไม่ติดลบ',
        ], [
            'payment_status' => 'สถานะชำระเงิน',
            'collected_amount' => 'ยอดเก็บเงิน',
            'note' => 'หมายเหตุ',
        ]);

        try {
            $this->tripService->updatePaymentCollection(
                $tripItem,
                $request->payment_status,
                $request->input('collected_amount'),
                $request->note,
                $this->userName()
            );
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->withErrors(['payment_status' => $exception->getMessage()]);
        }

        return redirect()->route('admin.trips.show', $tripItem->trip_id)->with('success', 'บันทึกสถานะชำระเงินแล้ว');
    }

    private function validatedTripData(Request $request, ?Trip $trip = null): array
    {
        return $request->validate([
            'trip_date' => ['required', 'date'],
            'driver_id' => [
                'nullable',
                // Must be an existing driver and active — unless it is the value already
                // saved on the trip being edited (a driver may have been deactivated since).
                Rule::exists('drivers', 'id')->where(function ($query) use ($trip) {
                    $query->where('status', Driver::STATUS_ACTIVE);

                    if ($trip && $trip->driver_id) {
                        $query->orWhere('id', $trip->driver_id);
                    }
                }),
            ],
            'driver_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role_name', User::ROLE_DRIVER)
                        ->where('status', 'active');
                }),
            ],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'driver_mobile' => ['nullable', 'regex:/^\d{9,10}$/'],
            'car_id' => ['nullable', 'string', 'max:100'],
            'area_name' => ['nullable', 'string', 'max:100'],
        ], [
            'required' => ':attribute จำเป็นต้องกรอก',
            'date' => ':attribute รูปแบบวันที่ไม่ถูกต้อง',
            'regex' => ':attribute ต้องเป็นตัวเลข 9 ถึง 10 หลัก',
            'max' => ':attribute ยาวเกินไป',
            'exists' => ':attribute ไม่ถูกต้องหรือถูกปิดใช้งานแล้ว',
        ], [
            'trip_date' => 'วันที่รอบขนส่ง',
            'driver_id' => 'คนขับรถ',
            'driver_name' => 'ชื่อพนักงานขับรถ',
            'driver_mobile' => 'เบอร์โทรศัพท์พนักงานขับรถ',
            'car_id' => 'ทะเบียนรถ',
            'area_name' => 'พื้นที่จัดส่ง',
        ]);
    }

    private function runTripAction(callable $callback, Trip $trip, string $message)
    {
        try {
            $callback();
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withErrors(['trip' => $exception->getMessage()]);
        }

        return redirect()->route('admin.trips.show', $trip)->with('success', $message);
    }

    /**
     * When a driver (master record) is selected, snapshot its details onto the trip
     * and link the driver's login account. The snapshot fields stay editable per trip
     * (the form may override them), so we only fill from the master when the field is
     * left blank by the user.
     *
     * Also enforces the busy guard server-side: if the driver already has a
     * non-cancelled trip on that date and confirm_busy was not sent, fail validation.
     */
    private function applySelectedDriver(array $data, Request $request, ?Trip $trip = null): array
    {
        if (empty($data['driver_id'])) {
            // No master driver chosen — fall back to the legacy free-text behaviour,
            // including the old driver_user_id dropdown if it was used.
            return $this->applyLegacyDriverUser($data);
        }

        $driver = Driver::find($data['driver_id']);

        if (! $driver) {
            return $data;
        }

        $this->guardDriverBusy($driver, $data['trip_date'], $request, $trip);

        // Master record drives the snapshot + linked login account.
        $data['driver_user_id'] = $driver->user_id;
        $data['driver_name'] = $request->filled('driver_name') ? $data['driver_name'] : $driver->full_name;
        $data['driver_mobile'] = $request->filled('driver_mobile') ? $data['driver_mobile'] : $driver->mobile;
        $data['car_id'] = $request->filled('car_id') ? $data['car_id'] : $driver->license_plate;
        $data['area_name'] = $request->filled('area_name') ? $data['area_name'] : $driver->area_name;

        return $data;
    }

    private function applyLegacyDriverUser(array $data): array
    {
        $data['driver_id'] = null;

        if (empty($data['driver_user_id'])) {
            return $data;
        }

        $user = User::find($data['driver_user_id']);

        if ($user && empty($data['driver_name'])) {
            $data['driver_name'] = trim($user->name . ' ' . $user->last_name);
        }

        return $data;
    }

    private function guardDriverBusy(Driver $driver, string $tripDate, Request $request, ?Trip $trip = null): void
    {
        if ($request->boolean('confirm_busy')) {
            return;
        }

        $busyTrip = Trip::query()
            ->whereDate('trip_date', $tripDate)
            ->where('driver_id', $driver->id)
            ->where('status', '!=', Trip::STATUS_CANCELLED)
            ->when($trip, fn ($q) => $q->where('id', '!=', $trip->id))
            ->orderByDesc('id')
            ->first();

        if ($busyTrip) {
            throw ValidationException::withMessages([
                'driver_id' => 'คนขับมีรอบ ' . $busyTrip->code . ' ในวันที่นี้แล้ว หากต้องการจัดรอบซ้อนให้กดยืนยัน',
            ]);
        }
    }

    private function legacyDriverUsers()
    {
        return User::query()
            ->where('role_name', User::ROLE_DRIVER)
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('last_name')
            ->get();
    }

    private function driversForForm(?int $includeDriverId = null)
    {
        return Driver::query()
            ->where(function ($query) use ($includeDriverId) {
                $query->where('status', Driver::STATUS_ACTIVE);

                if ($includeDriverId) {
                    $query->orWhere('id', $includeDriverId);
                }
            })
            ->orderBy('name')
            ->orderBy('last_name')
            ->get();
    }

    private function isReadOnly(Trip $trip): bool
    {
        return in_array($trip->status, [Trip::STATUS_COMPLETED, Trip::STATUS_CANCELLED], true);
    }

    private function userName(): ?string
    {
        return Auth::user()->name ?? null;
    }
}
