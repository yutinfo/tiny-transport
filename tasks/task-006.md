# Task 006: Driver Parcel Actions and Ownership Safety

## Goal

ให้คนขับอัปเดตสถานะพัสดุและ COD ได้เฉพาะพัสดุในรอบที่ถูกมอบหมายให้ตนเอง

## Decision

ใช้ `TripService` เดิมสำหรับ business rules และเพิ่ม guard ใน `DriverTripController` ก่อนเรียก service เพื่อปิดช่องทางแก้ trip item ของคนอื่น

## Files

- Modify: `app/Http/Controllers/DriverTripController.php`
- Modify: `resources/views/driver/trips/_parcel-card.blade.php`
- Test: `tests/Feature/DriverParcelActionFeatureTest.php`

## Steps

- [ ] Add a guard in `DriverTripController` for trip item ownership.

```php
private function ensureDriverOwnsTripItem(TripItem $tripItem): TripItem
{
    $tripItem = $tripItem->fresh(['trip']) ?: $tripItem;

    if (! $tripItem->trip || (int) $tripItem->trip->driver_user_id !== (int) Auth::id()) {
        abort(403);
    }

    return $tripItem;
}
```

- [ ] Extract delivery update logic into a private method that throws validation or service exceptions before redirecting.

```php
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
```

- [ ] Update existing admin `updateDeliveryStatus()` to call `handleDeliveryStatusUpdate()`.

```php
public function updateDeliveryStatus(TripItem $tripItem, Request $request)
{
    try {
        $this->handleDeliveryStatusUpdate($tripItem, $request);
    } catch (InvalidArgumentException $exception) {
        return redirect()->back()->withInput()->withErrors(['delivery_status' => $exception->getMessage()]);
    }

    return redirect()->route('admin.trips.driver', $tripItem->trip_id)->with('success', 'บันทึกสถานะจัดส่งแล้ว');
}
```

- [ ] Add `updateDriverDeliveryStatus()` with ownership guard and driver redirect.

```php
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
```

- [ ] Extract payment update logic into a private method.

```php
private function handlePaymentStatusUpdate(TripItem $tripItem, Request $request): void
{
    $tripItem = $tripItem->fresh(['trip']) ?: $tripItem;

    $request->validate([
        'payment_status' => ['required', Rule::in([TripItem::PAYMENT_STATUS_PAID])],
        'collected_amount' => ['nullable', 'numeric', 'min:0'],
        'note' => ['nullable', 'string', 'max:1000'],
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
```

- [ ] Update existing admin `updatePaymentStatus()` to call `handlePaymentStatusUpdate()` and keep redirecting to `admin.trips.driver`.

- [ ] Add `updateDriverPaymentStatus()` with ownership guard and redirect to `driver.trips.show`.

```php
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
```

- [ ] Point driver parcel action forms in `resources/views/driver/trips/_parcel-card.blade.php` to driver route names.

```blade
route('driver.trip-items.delivery-status', $item)
route('driver.trip-items.payment-status', $item)
```

- [ ] Keep admin preview forms pointed to `admin.driver.trip-items.*` so admin/staff can still test the driver preview from a trip detail page.

- [ ] Add `tests/Feature/DriverParcelActionFeatureTest.php`.

```php
<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverParcelActionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_can_update_own_trip_item_delivery_status()
    {
        [$driver, $trip, $item] = $this->createDriverTripItem();

        $this->actingAs($driver)
            ->post('/driver/trip-items/' . $item->id . '/delivery-status', [
                'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
                'note' => 'ส่งสำเร็จ',
            ])
            ->assertRedirect('/driver/trips/' . $trip->id);

        $this->assertDatabaseHas('trip_items', [
            'id' => $item->id,
            'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
        ]);
    }

    public function test_driver_cannot_update_other_driver_trip_item()
    {
        $driver = User::create([
            'username' => 'driver-blocked',
            'password' => 'password',
            'name' => 'Driver',
            'last_name' => 'Blocked',
            'email' => 'driver-blocked@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        [, , $item] = $this->createDriverTripItem('other-driver');

        $this->actingAs($driver)
            ->post('/driver/trip-items/' . $item->id . '/delivery-status', [
                'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
                'note' => 'ส่งสำเร็จ',
            ])
            ->assertForbidden();
    }

    private function createDriverTripItem(string $username = 'driver-owner'): array
    {
        $driver = User::create([
            'username' => $username,
            'password' => 'password',
            'name' => 'Driver',
            'last_name' => 'Owner',
            'email' => $username . '@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $order = Order::create([
            'code' => 'OR-DRIVER-ACTION',
            'customer_name' => 'Sender',
            'customer_mobile' => '0800000000',
            'parcel_amount' => 1,
            'parcel_total' => 125.75,
            'order_status' => 'waiting',
        ]);

        $receiver = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P-DRIVER-ACTION',
            'receive_name' => 'Receiver',
            'receive_mobile' => '0811111111',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'parcel_pice' => 125.75,
        ]);

        $trip = Trip::create([
            'code' => 'RUN-20260607-' . str_pad((string) Trip::count() + 1, 4, '0', STR_PAD_LEFT),
            'trip_date' => '2026-06-07',
            'driver_user_id' => $driver->id,
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        $item = TripItem::create([
            'trip_id' => $trip->id,
            'order_id' => $order->id,
            'order_receive_id' => $receiver->id,
            'parcel_code' => $receiver->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'cod_amount' => 125.75,
            'collected_amount' => 0,
        ]);

        return [$driver, $trip, $item];
    }
}
```

## Validation

Run inside Docker:

```bash
docker compose exec app php artisan test --filter=DriverParcelActionFeatureTest
```

Expected result: own-item update passes and other-driver update is forbidden.

## Commit

```bash
git add app/Http/Controllers/DriverTripController.php resources/views/driver/trips/_parcel-card.blade.php tests/Feature/DriverParcelActionFeatureTest.php
git commit -m "feat: secure driver parcel actions"
```

## Acceptance Criteria

- Driver action POST routes require role `driver`.
- Driver can update only assigned trip items.
- Failed delivery still requires a reason.
- COD collection still requires delivered status.
- Existing `TripService` business rules are reused.
