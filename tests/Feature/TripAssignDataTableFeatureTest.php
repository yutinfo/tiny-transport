<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripAssignDataTableFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'username' => 'admin-adt',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'ADT',
            'email' => 'admin-adt@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);
    }

    private function makeTrip(): Trip
    {
        return Trip::create([
            'code' => 'RUN-20260611-0001',
            'trip_date' => '2026-06-11',
            'status' => Trip::STATUS_DRAFT,
        ]);
    }

    private function makeWaitingParcel(string $parcelCode, string $receiveName): OrderReceive
    {
        $order = Order::create([
            'code' => 'OR' . $parcelCode,
            'customer_name' => 'Sender',
            'parcel_amount' => 1,
            'parcel_total' => 50,
            'order_status' => 'waiting',
        ]);

        return OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => $parcelCode,
            'receive_name' => $receiveName,
            'receive_mobile' => '0811111111',
            'province_name' => 'กรุงเทพมหานคร',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => 'waiting',
            'payment_status' => 'waiting',
            'parcel_pice' => 50,
        ]);
    }

    private function dtParams(array $overrides = []): array
    {
        return array_merge([
            'draw' => 1,
            'start' => 0,
            'length' => 20,
            'search' => ['value' => '', 'regex' => 'false'],
            'order' => [['column' => 1, 'dir' => 'asc']],
        ], $overrides);
    }

    public function test_unauthenticated_user_is_redirected()
    {
        $trip = $this->makeTrip();

        $this->get(route('admin.trips.assign.data', $trip))->assertRedirect('/login');
    }

    public function test_admin_sees_only_assignable_waiting_parcels()
    {
        $trip = $this->makeTrip();
        $this->makeWaitingParcel('P2026ADT1', 'Alpha Receiver');
        $this->makeWaitingParcel('P2026ADT2', 'Beta Receiver');

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.trips.assign.data', [$trip, ...$this->dtParams()]));

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(2, $response->json('recordsFiltered'));
        $this->assertStringContainsString('row-select', $response->json('data.0.select'));
    }

    public function test_assigned_parcel_is_excluded_from_pool()
    {
        $trip = $this->makeTrip();
        $assignable = $this->makeWaitingParcel('P2026FREE', 'Free Receiver');
        $assigned = $this->makeWaitingParcel('P2026BUSY', 'Busy Receiver');

        // Place "busy" parcel onto an active (non-cancelled) trip item.
        TripItem::create([
            'trip_id' => $trip->id,
            'order_id' => $assigned->order_id,
            'order_receive_id' => $assigned->id,
            'parcel_code' => $assigned->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'cod_amount' => 50,
            'collected_amount' => 0,
        ]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.trips.assign.data', [$trip, ...$this->dtParams()]));

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsTotal'));
        $this->assertStringContainsString('P2026FREE', $response->json('data.0.parcel_code'));
    }

    public function test_search_narrows_records_filtered()
    {
        $trip = $this->makeTrip();
        $this->makeWaitingParcel('P2026ADT1', 'Alpha Receiver');
        $this->makeWaitingParcel('P2026ADT2', 'Beta Receiver');

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.trips.assign.data', [$trip, ...$this->dtParams([
                'search' => ['value' => 'Beta', 'regex' => 'false'],
            ])]));

        $response->assertOk();
        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('P2026ADT2', $response->json('data.0.parcel_code'));
    }
}
