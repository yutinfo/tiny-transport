<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use App\Models\ParcelNotification;
use App\Services\TripService;
use App\Services\ParcelNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StabilityAndNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'username' => 'admin_test',
            'password' => bcrypt('password'),
            'role_name' => 'admin',
        ]);
    }

    public function test_parcel_price_accessor_mutator_syncs_both_fields()
    {
        $order = Order::create([
            'code' => 'OR2026COMPAT',
            'customer_name' => 'Sender',
            'customer_mobile' => '0800000000',
            'parcel_amount' => 1,
            'parcel_total' => 150.00,
            'order_status' => 'waiting',
        ]);

        // Scenario 1: Creating with parcel_pice
        $receiver1 = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026COMPAT1',
            'receive_name' => 'Receiver 1',
            'receive_mobile' => '0811111111',
            'parcel_pice' => 150.00,
        ]);

        $this->assertEquals(150.00, $receiver1->parcel_price);
        $this->assertEquals(150.00, $receiver1->getParcelPriceValue());

        // Scenario 2: Creating with parcel_price
        $receiver2 = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026COMPAT2',
            'receive_name' => 'Receiver 2',
            'receive_mobile' => '0811111112',
            'parcel_price' => 200.00,
        ]);

        $this->assertEquals(200.00, $receiver2->parcel_pice);
        $this->assertEquals(200.00, $receiver2->getParcelPriceValue());

        // Scenario 3: Updating parcel_pice
        $receiver1->update(['parcel_pice' => 180.00]);
        $this->assertEquals(180.00, $receiver1->fresh()->parcel_price);

        // Scenario 4: Updating parcel_price
        $receiver2->update(['parcel_price' => 220.00]);
        $this->assertEquals(220.00, $receiver2->fresh()->parcel_pice);
    }

    public function test_parcel_notification_service_logs_correctly()
    {
        $order = Order::create([
            'code' => 'OR2026NOTIFY',
            'customer_name' => 'Sender',
            'customer_mobile' => '0800000000',
            'parcel_amount' => 1,
            'parcel_total' => 100.00,
            'order_status' => 'waiting',
        ]);

        $receiver = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026NOTIFY',
            'receive_name' => 'Receiver',
            'receive_mobile' => '0811111111',
            'parcel_price' => 100.00,
        ]);

        $service = new ParcelNotificationService();
        $notification = $service->createPendingNotification($receiver, 'sms', 'assigned_to_trip');

        $this->assertDatabaseHas('parcel_notifications', [
            'id' => $notification->id,
            'order_receive_id' => $receiver->id,
            'channel' => 'sms',
            'recipient' => '0811111111',
            'status' => 'sent',
        ]);

        $this->assertStringContainsString('P2026NOTIFY', $notification->message);
    }

    public function test_trip_controller_validation_and_thai_errors()
    {
        $this->actingAs($this->adminUser);

        // Test collected_amount non-negative
        $trip = Trip::create([
            'code' => 'RUN-20260606-9999',
            'trip_date' => '2026-06-06',
            'status' => Trip::STATUS_DRAFT,
        ]);

        $order = Order::create([
            'code' => 'OR2026VALIDATE',
            'customer_name' => 'Sender',
            'customer_mobile' => '0800000000',
            'parcel_amount' => 1,
            'parcel_total' => 100.00,
            'order_status' => 'waiting',
        ]);

        $receiver = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026VALIDATE',
            'receive_name' => 'Receiver',
            'receive_mobile' => '0811111111',
            'parcel_price' => 100.00,
        ]);

        $tripItem = TripItem::create([
            'trip_id' => $trip->id,
            'order_id' => $order->id,
            'order_receive_id' => $receiver->id,
            'parcel_code' => $receiver->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
        ]);

        // Attempting to post negative collected_amount should fail validation
        $response = $this->post(route('admin.trip-items.payment-status', $tripItem), [
            'payment_status' => TripItem::PAYMENT_STATUS_PAID,
            'collected_amount' => -10,
        ]);

        $response->assertSessionHasErrors(['collected_amount']);
    }
}
