<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReceiveDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_receive_delete_requires_authentication()
    {
        $order = $this->createOrderWithReceivers();
        $receiver = $order->receivers()->first();

        $this->deleteJson(route('ta-admin.orderreceive.delete', $receiver->id))
            ->assertUnauthorized();

        $this->assertDatabaseHas('order_receives', [
            'id' => $receiver->id,
        ]);
    }

    public function test_authenticated_user_can_delete_order_receiver_and_recalculate_order_totals()
    {
        $user = $this->createUser();
        $order = $this->createOrderWithReceivers();
        $receiver = $order->receivers()->first();

        $this->actingAs($user)
            ->deleteJson(route('ta-admin.orderreceive.delete', $receiver->id))
            ->assertOk()
            ->assertJson([
                'deleted' => true,
                'id' => $receiver->id,
            ]);

        $this->assertDatabaseMissing('order_receives', [
            'id' => $receiver->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'parcel_amount' => 1,
            'parcel_total' => 75.5,
        ]);
    }

    public function test_public_api_route_no_longer_deletes_order_receivers()
    {
        $order = $this->createOrderWithReceivers();
        $receiver = $order->receivers()->first();

        $this->deleteJson('/api/order-receive/' . $receiver->id)
            ->assertNotFound();

        $this->assertDatabaseHas('order_receives', [
            'id' => $receiver->id,
        ]);
    }

    private function createUser(): User
    {
        return User::create([
            'username' => 'admin-test',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin-test@example.com',
            'status' => 'active',
            'role_name' => 'admin',
            'username_verified_at' => now(),
        ]);
    }

    private function createOrderWithReceivers(): Order
    {
        $order = Order::create([
            'code' => 'OR2026TEST',
            'customer_name' => 'Sender',
            'customer_mobile' => '0800000000',
            'parcel_amount' => 2,
            'parcel_total' => 175.50,
            'order_status' => 'waiting',
        ]);

        OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026DELETE',
            'receive_name' => 'Receiver One',
            'receive_mobile' => '0811111111',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'immediately',
            'delivery_status' => 'waiting',
            'payment_status' => 'waiting',
            'parcel_pice' => 100.00,
        ]);

        OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026KEEP',
            'receive_name' => 'Receiver Two',
            'receive_mobile' => '0822222222',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => 'waiting',
            'payment_status' => 'waiting',
            'parcel_pice' => 75.50,
        ]);

        return $order;
    }
}
