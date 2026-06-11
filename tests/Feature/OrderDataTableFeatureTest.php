<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDataTableFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::create([
            'username' => 'admin-odt',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'ODT',
            'email' => 'admin-odt@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);
    }

    private function staff(): User
    {
        return User::create([
            'username' => 'staff-odt',
            'password' => 'password',
            'name' => 'Staff',
            'last_name' => 'ODT',
            'email' => 'staff-odt@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_STAFF,
        ]);
    }

    private function seedParcels(): array
    {
        Carbon::setTestNow('2026-06-11 09:00:00');

        $order = Order::create([
            'code' => 'OR2026ODT1',
            'customer_name' => 'Alpha Sender',
            'customer_mobile' => '0800000000',
            'parcel_amount' => 2,
            'parcel_total' => 300,
            'order_status' => 'waiting',
        ]);

        $a = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026ALPHA',
            'receive_name' => 'Receiver Alpha',
            'receive_mobile' => '0811111111',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'immediately',
            'delivery_status' => 'waiting',
            'payment_status' => 'waiting',
            'parcel_pice' => 100,
        ]);

        $b = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026BETA',
            'receive_name' => 'Receiver Beta',
            'receive_mobile' => '0822222222',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => 'waiting',
            'payment_status' => 'waiting',
            'parcel_pice' => 200,
        ]);

        return [$order, $a, $b];
    }

    private function dtParams(array $overrides = []): array
    {
        return array_merge([
            'draw' => 1,
            'start' => 0,
            'length' => 20,
            'db_date' => '2026-06-11',
            'search' => ['value' => '', 'regex' => 'false'],
            'order' => [['column' => 1, 'dir' => 'desc']],
        ], $overrides);
    }

    public function test_unauthenticated_user_is_redirected()
    {
        $this->get(route('admin.orders.data'))
            ->assertRedirect('/login');
    }

    public function test_admin_gets_datatables_json_shape()
    {
        $this->seedParcels();

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.orders.data', $this->dtParams()));

        $response->assertOk()
            ->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data',
                'summary' => ['immediately_total', 'on_delivery_total'],
            ]);

        $this->assertSame(1, $response->json('draw'));
        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(2, $response->json('recordsFiltered'));
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(100, $response->json('summary.immediately_total'));
        $this->assertEquals(200, $response->json('summary.on_delivery_total'));
    }

    public function test_staff_can_reach_endpoint()
    {
        $this->seedParcels();

        $this->actingAs($this->staff())
            ->getJson(route('admin.orders.data', $this->dtParams()))
            ->assertOk();
    }

    public function test_search_narrows_records_filtered()
    {
        $this->seedParcels();

        // "BETA" matches only the second parcel's code (the shared customer name
        // is "Alpha Sender"), proving search narrows recordsFiltered.
        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.orders.data', $this->dtParams([
                'search' => ['value' => 'P2026BETA', 'regex' => 'false'],
            ])));

        $response->assertOk();
        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('P2026BETA', $response->json('data.0.parcel_code'));
    }

    public function test_date_filter_excludes_other_days()
    {
        $this->seedParcels();

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.orders.data', $this->dtParams([
                'db_date' => '2026-06-10',
            ])));

        $response->assertOk();
        $this->assertSame(0, $response->json('recordsTotal'));
        $this->assertCount(0, $response->json('data'));
    }
}
