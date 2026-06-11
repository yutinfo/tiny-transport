<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParcelSearchDataTableFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'username' => 'admin-psd',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'PSD',
            'email' => 'admin-psd@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);
    }

    private function staff(): User
    {
        return User::create([
            'username' => 'staff-psd',
            'password' => 'password',
            'name' => 'Staff',
            'last_name' => 'PSD',
            'email' => 'staff-psd@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_STAFF,
        ]);
    }

    private function makeParcel(string $parcelCode, string $receiveName): OrderReceive
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
            'draw' => 5,
            'start' => 0,
            'length' => 20,
            'search' => ['value' => '', 'regex' => 'false'],
            'order' => [['column' => 0, 'dir' => 'desc']],
        ], $overrides);
    }

    public function test_unauthenticated_user_is_redirected()
    {
        $this->get(route('admin.parcels.search.data'))->assertRedirect('/login');
    }

    public function test_admin_gets_all_parcels_json_shape()
    {
        $this->makeParcel('P2026PSD1', 'Alpha Receiver');
        $this->makeParcel('P2026PSD2', 'Beta Receiver');

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.parcels.search.data', $this->dtParams()));

        $response->assertOk()
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertSame(5, $response->json('draw'));
        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(2, $response->json('recordsFiltered'));
        $this->assertStringContainsString('เปิด', $response->json('data.0.actions'));
    }

    public function test_staff_can_reach_endpoint()
    {
        $this->makeParcel('P2026PSD1', 'Alpha Receiver');

        $this->actingAs($this->staff())
            ->getJson(route('admin.parcels.search.data', $this->dtParams()))
            ->assertOk();
    }

    public function test_search_by_parcel_code_narrows_records_filtered()
    {
        $this->makeParcel('P2026PSD1', 'Alpha Receiver');
        $this->makeParcel('P2026PSD2', 'Beta Receiver');

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.parcels.search.data', $this->dtParams([
                'search' => ['value' => 'P2026PSD2', 'regex' => 'false'],
            ])));

        $response->assertOk();
        $this->assertSame(2, $response->json('recordsTotal'));
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('P2026PSD2', $response->json('data.0.parcel_code'));
    }

    public function test_search_by_receiver_name_narrows_records_filtered()
    {
        $this->makeParcel('P2026PSD1', 'Alpha Receiver');
        $this->makeParcel('P2026PSD2', 'Beta Receiver');

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.parcels.search.data', $this->dtParams([
                'search' => ['value' => 'Alpha', 'regex' => 'false'],
            ])));

        $response->assertOk();
        $this->assertSame(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('P2026PSD1', $response->json('data.0.parcel_code'));
    }
}
