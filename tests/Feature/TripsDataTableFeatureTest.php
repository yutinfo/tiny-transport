<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripsDataTableFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'username' => 'admin-tdt',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'TDT',
            'email' => 'admin-tdt@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);
    }

    private function staff(): User
    {
        return User::create([
            'username' => 'staff-tdt',
            'password' => 'password',
            'name' => 'Staff',
            'last_name' => 'TDT',
            'email' => 'staff-tdt@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_STAFF,
        ]);
    }

    private function makeTrip(array $overrides = []): Trip
    {
        return Trip::create(array_merge([
            'code' => 'RUN-20260611-' . str_pad((string) (Trip::count() + 1), 4, '0', STR_PAD_LEFT),
            'trip_date' => '2026-06-11',
            'driver_name' => 'Driver One',
            'driver_mobile' => '0809999999',
            'car_id' => 'CAR-01',
            'area_name' => 'Bangkok',
            'status' => Trip::STATUS_DRAFT,
            'collected_amount' => 0,
        ], $overrides));
    }

    private function dtParams(array $overrides = []): array
    {
        return array_merge([
            'draw' => 3,
            'start' => 0,
            'length' => 20,
            'search' => ['value' => '', 'regex' => 'false'],
            'order' => [['column' => 1, 'dir' => 'desc']],
        ], $overrides);
    }

    public function test_unauthenticated_user_is_redirected()
    {
        $this->get(route('admin.trips.data'))->assertRedirect('/login');
    }

    public function test_admin_gets_datatables_json_shape_with_items_count()
    {
        $tripA = $this->makeTrip(['code' => 'RUN-20260611-0001']);
        $tripB = $this->makeTrip(['code' => 'RUN-20260611-0002']);

        // Attach one trip item to tripA so items_count = 1.
        $order = Order::create([
            'code' => 'OR2026TDT1',
            'customer_name' => 'Sender',
            'parcel_amount' => 1,
            'parcel_total' => 50,
            'order_status' => 'waiting',
        ]);
        $receiver = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026TDT1',
            'receive_name' => 'Receiver',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => 'waiting',
            'payment_status' => 'waiting',
            'parcel_pice' => 50,
        ]);
        TripItem::create([
            'trip_id' => $tripA->id,
            'order_id' => $order->id,
            'order_receive_id' => $receiver->id,
            'parcel_code' => $receiver->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'cod_amount' => 50,
            'collected_amount' => 0,
        ]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.trips.data', $this->dtParams()));

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertSame(3, $response->json('draw'));
        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(2, $response->json('recordsFiltered'));

        // Ordered by id desc => tripB first, tripA second (items_count 1).
        $rows = collect($response->json('data'))->keyBy('code');
        $this->assertSame('1', $rows['RUN-20260611-0001']['items_count']);
        $this->assertSame('0', $rows['RUN-20260611-0002']['items_count']);
    }

    public function test_staff_can_reach_endpoint()
    {
        $this->makeTrip();

        $this->actingAs($this->staff())
            ->getJson(route('admin.trips.data', $this->dtParams()))
            ->assertOk();
    }

    public function test_search_narrows_by_code()
    {
        $this->makeTrip(['code' => 'RUN-20260611-0001']);
        $this->makeTrip(['code' => 'RUN-20260611-0002']);

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.trips.data', $this->dtParams([
                'search' => ['value' => '0002', 'regex' => 'false'],
            ])));

        $response->assertOk();
        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame('RUN-20260611-0002', $response->json('data.0.code'));
    }

    public function test_status_filter_applies()
    {
        $this->makeTrip(['code' => 'RUN-20260611-0001', 'status' => Trip::STATUS_DRAFT]);
        $this->makeTrip(['code' => 'RUN-20260611-0002', 'status' => Trip::STATUS_CANCELLED]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.trips.data', $this->dtParams([
                'status' => Trip::STATUS_CANCELLED,
            ])));

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertSame('RUN-20260611-0002', $response->json('data.0.code'));
    }
}
