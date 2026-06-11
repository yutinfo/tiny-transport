<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\ParcelStatusLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTrackingApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeParcel(string $parcelCode, string $receiveName = 'Receiver'): OrderReceive
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
            'receive_address' => '123 หมู่ 4',
            'province_name' => 'กรุงเทพมหานคร',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => 'in_transit',
            'payment_status' => 'waiting',
            'parcel_pice' => 50,
        ]);
    }

    public function test_track_requires_codes_parameter(): void
    {
        $this->getJson(route('api.public.track'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('codes');
    }

    public function test_track_returns_not_found_for_unknown_code(): void
    {
        $response = $this->getJson(route('api.public.track', ['codes' => ['NOPE-XYZ']]));

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.code', 'NOPE-XYZ')
            ->assertJsonPath('0.found', false);
    }

    public function test_track_returns_found_parcel_with_timeline(): void
    {
        $parcel = $this->makeParcel('P2026WEB1', 'Alpha Receiver');

        ParcelStatusLog::create([
            'order_receive_id' => $parcel->id,
            'from_status' => null,
            'to_status' => 'waiting',
            'note' => 'รับเข้าระบบ',
            'created_by' => 'System',
        ]);
        ParcelStatusLog::create([
            'order_receive_id' => $parcel->id,
            'from_status' => 'waiting',
            'to_status' => 'in_transit',
            'note' => 'ออกจัดส่ง',
            'created_by' => 'System',
        ]);

        $response = $this->getJson(route('api.public.track', ['codes' => ['P2026WEB1']]));

        $response->assertOk()
            ->assertJsonPath('0.code', 'P2026WEB1')
            ->assertJsonPath('0.found', true)
            ->assertJsonPath('0.receive_name', 'Alpha Receiver')
            ->assertJsonPath('0.status', 'in_transit')
            ->assertJsonPath('0.payment_type', 'on_delivery')
            ->assertJsonStructure([
                '0' => ['code', 'found', 'receive_name', 'receive_address', 'status', 'status_label', 'cod_amount', 'payment_type', 'timeline'],
            ]);

        $this->assertCount(2, $response->json('0.timeline'));
        $this->assertSame('ออกจัดส่ง', $response->json('0.timeline.1.note'));
    }

    public function test_track_rejects_more_than_ten_codes(): void
    {
        $codes = [];
        for ($i = 1; $i <= 11; $i++) {
            $codes[] = 'CODE-' . $i;
        }

        $this->getJson(route('api.public.track', ['codes' => $codes]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('codes');
    }

    public function test_track_requires_no_authentication(): void
    {
        $this->makeParcel('P2026WEB2', 'Beta Receiver');

        // No actingAs() — public endpoint must respond without a session.
        $this->getJson(route('api.public.track', ['codes' => ['P2026WEB2']]))
            ->assertOk()
            ->assertJsonPath('0.found', true);
    }
}
