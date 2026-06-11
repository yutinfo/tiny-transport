<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripItemsDataTableFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'username' => 'admin-idt',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'IDT',
            'email' => 'admin-idt@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);
    }

    private function makeTripWithItems(int $count): Trip
    {
        $trip = Trip::create([
            'code' => 'RUN-20260611-0001',
            'trip_date' => '2026-06-11',
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        for ($n = 1; $n <= $count; $n++) {
            $order = Order::create([
                'code' => 'OR2026IDT' . $n,
                'customer_name' => 'Sender',
                'parcel_amount' => 1,
                'parcel_total' => 50,
                'order_status' => 'waiting',
            ]);
            $receiver = OrderReceive::create([
                'order_id' => $order->id,
                'parcel_code' => 'P2026IDT' . $n,
                'receive_name' => 'Receiver ' . $n,
                'receive_mobile' => '081000000' . $n,
                'parcel_pickup_type' => 'delivery',
                'payment_type' => 'on_delivery',
                'delivery_status' => 'waiting',
                'payment_status' => 'waiting',
                'parcel_pice' => 50,
            ]);
            TripItem::create([
                'trip_id' => $trip->id,
                'order_id' => $order->id,
                'order_receive_id' => $receiver->id,
                'parcel_code' => $receiver->parcel_code,
                'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
                'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
                'cod_amount' => 50,
                'collected_amount' => 0,
            ]);
        }

        return $trip;
    }

    private function dtParams(array $overrides = []): array
    {
        return array_merge([
            'draw' => 2,
            'start' => 0,
            'length' => 20,
            'search' => ['value' => '', 'regex' => 'false'],
            'order' => [['column' => 0, 'dir' => 'asc']],
        ], $overrides);
    }

    public function test_unauthenticated_user_is_redirected()
    {
        $trip = $this->makeTripWithItems(1);

        $this->get(route('admin.trips.items.data', $trip))->assertRedirect('/login');
    }

    public function test_admin_gets_items_scoped_to_trip_with_action_forms()
    {
        $trip = $this->makeTripWithItems(2);

        // A second trip with its own item — must NOT leak into the first trip's data.
        $otherTrip = Trip::create([
            'code' => 'RUN-20260611-0002',
            'trip_date' => '2026-06-11',
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);
        $order = Order::create(['code' => 'OR2026OTHER', 'order_status' => 'waiting', 'parcel_amount' => 1, 'parcel_total' => 10]);
        $receiver = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026OTHER',
            'receive_name' => 'Other',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => 'waiting',
            'payment_status' => 'waiting',
            'parcel_pice' => 10,
        ]);
        TripItem::create([
            'trip_id' => $otherTrip->id,
            'order_id' => $order->id,
            'order_receive_id' => $receiver->id,
            'parcel_code' => $receiver->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'cod_amount' => 10,
            'collected_amount' => 0,
        ]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.trips.items.data', [$trip, ...$this->dtParams()]));

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertSame(2, $response->json('draw'));
        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(2, $response->json('recordsFiltered'));

        // Action cell must carry the CSRF token (@csrf renders a _token hidden input)
        // and the delivery-status form action.
        $this->assertStringContainsString('name="_token"', $response->json('data.0.actions'));
        $this->assertStringContainsString('trip-items', $response->json('data.0.actions'));

        $codes = collect($response->json('data'))->pluck('parcel_code')->implode(',');
        $this->assertStringNotContainsString('P2026OTHER', $codes);
    }

    public function test_search_narrows_records_filtered()
    {
        $trip = $this->makeTripWithItems(3);

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.trips.items.data', [$trip, ...$this->dtParams([
                'search' => ['value' => 'P2026IDT2', 'regex' => 'false'],
            ])]));

        $response->assertOk();
        $this->assertSame(3, $response->json('recordsTotal'));
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('P2026IDT2', $response->json('data.0.parcel_code'));
    }
}
