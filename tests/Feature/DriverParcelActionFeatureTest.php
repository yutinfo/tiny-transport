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
        // A non-COD parcel can be delivered directly (no money to collect).
        $item->update(['cod_amount' => 0]);

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

    public function test_driver_cannot_mark_cod_parcel_delivered_before_collecting()
    {
        [$driver, $trip, $item] = $this->createDriverTripItem();

        $this->actingAs($driver)
            ->from('/driver/trips/' . $trip->id)
            ->post('/driver/trip-items/' . $item->id . '/delivery-status', [
                'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
                'note' => 'ส่งสำเร็จ',
            ])
            ->assertRedirect('/driver/trips/' . $trip->id)
            ->assertSessionHasErrors('delivery_status');

        $this->assertDatabaseHas('trip_items', [
            'id' => $item->id,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
        ]);
    }

    public function test_driver_can_collect_cod_then_mark_delivered()
    {
        [$driver, $trip, $item] = $this->createDriverTripItem();

        $this->actingAs($driver)
            ->post('/driver/trip-items/' . $item->id . '/payment-status', [
                'payment_status' => TripItem::PAYMENT_STATUS_PAID,
                'collected_amount' => 125.75,
            ])
            ->assertRedirect('/driver/trips/' . $trip->id);

        $this->assertDatabaseHas('trip_items', [
            'id' => $item->id,
            'payment_status' => TripItem::PAYMENT_STATUS_PAID,
            'collected_amount' => 125.75,
        ]);

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

    public function test_driver_can_return_unpaid_cod_parcel_with_reason()
    {
        [$driver, $trip, $item] = $this->createDriverTripItem();
        $receiverId = $item->order_receive_id;

        // returning REQUIRES a reason now
        $this->actingAs($driver)
            ->from('/driver/trips/' . $trip->id)
            ->post('/driver/trip-items/' . $item->id . '/delivery-status', [
                'delivery_status' => TripItem::DELIVERY_STATUS_RETURNED,
            ])
            ->assertSessionHasErrors('failed_reason');

        // with a reason it succeeds and is terminal (parcel NOT re-queued)
        $this->actingAs($driver)
            ->post('/driver/trip-items/' . $item->id . '/delivery-status', [
                'delivery_status' => TripItem::DELIVERY_STATUS_RETURNED,
                'failed_reason' => 'COD ไม่จ่าย',
            ])
            ->assertRedirect('/driver/trips/' . $trip->id);

        $this->assertDatabaseHas('trip_items', [
            'id' => $item->id,
            'delivery_status' => TripItem::DELIVERY_STATUS_RETURNED,
            'failed_reason' => 'COD ไม่จ่าย',
        ]);
        $this->assertDatabaseHas('order_receives', [
            'id' => $receiverId,
            'delivery_status' => TripItem::DELIVERY_STATUS_RETURNED,
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

    public function test_driver_can_start_assigned_trip()
    {
        [$driver, $trip, $item] = $this->createDriverTripItem();
        $trip->update(['status' => Trip::STATUS_ASSIGNED]);

        $this->actingAs($driver)
            ->post('/driver/trips/' . $trip->id . '/start')
            ->assertRedirect('/driver/trips/' . $trip->id);

        $this->assertSame(Trip::STATUS_IN_TRANSIT, $trip->fresh()->status);
    }

    public function test_driver_can_submit_transit_trip_when_items_are_final()
    {
        [$driver, $trip, $item] = $this->createDriverTripItem();
        $item->update([
            'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
            'payment_status' => TripItem::PAYMENT_STATUS_PAID,
            'collected_amount' => 125.75,
        ]);

        $this->actingAs($driver)
            ->post('/driver/trips/' . $trip->id . '/submit')
            ->assertRedirect('/driver/trips/' . $trip->id);

        $this->assertSame(Trip::STATUS_PENDING_VERIFICATION, $trip->fresh()->status);
    }

    public function test_driver_cannot_submit_transit_trip_when_items_are_not_final()
    {
        [$driver, $trip, $item] = $this->createDriverTripItem();

        $this->actingAs($driver)
            ->from('/driver/trips/' . $trip->id)
            ->post('/driver/trips/' . $trip->id . '/submit')
            ->assertRedirect('/driver/trips/' . $trip->id)
            ->assertSessionHasErrors('trip');

        $this->assertSame(Trip::STATUS_IN_TRANSIT, $trip->fresh()->status);
    }
}
