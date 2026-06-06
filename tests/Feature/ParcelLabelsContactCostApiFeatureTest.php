<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ParcelLabelsContactCostApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_print_order_and_trip_labels_then_open_parcel_by_code()
    {
        $user = $this->createUser();
        [$trip, $tripItem, $receiver, $order] = $this->createTripWithItem();

        $this->actingAs($user)
            ->get('/admin/orders/' . $order->id . '/labels')
            ->assertOk()
            ->assertSee('พิมพ์ใบปะหน้าพัสดุ')
            ->assertSee($order->code)
            ->assertSee($receiver->parcel_code)
            ->assertSee('<svg', false);

        $this->actingAs($user)
            ->get('/admin/trips/' . $trip->id . '/labels')
            ->assertOk()
            ->assertSee($trip->code)
            ->assertSee($tripItem->parcel_code)
            ->assertSee('<svg', false);

        $this->actingAs($user)
            ->get('/admin/parcels/search?q=' . $receiver->parcel_code)
            ->assertOk()
            ->assertSee($receiver->parcel_code)
            ->assertSee($receiver->receive_name);

        $this->actingAs($user)
            ->get('/admin/parcels/code/' . $receiver->parcel_code)
            ->assertRedirect('/admin/parcels/' . $receiver->id . '/tracking');
    }

    public function test_contact_search_supports_query_autocomplete_and_order_sync_merges_matching_mobile_to_both()
    {
        $user = $this->createUser();

        Contact::create([
            'type' => 'receiver',
            'name' => 'Somchai Receiver',
            'mobile' => '0812223333',
            'address' => 'Receiver address',
        ]);

        $this->actingAs($user)
            ->getJson('/admin/api/contacts/search?type=receiver&q=Som')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Somchai Receiver')
            ->assertJsonPath('data.0.mobile', '0812223333');

        $this->actingAs($user)->postJson('/admin/orders/store', [
            'sender_name' => 'Shared Person',
            'sender_mobile' => '0801234567',
            'parcel_description' => 'กล่องเอกสาร',
            'parcel_pice' => 50,
            'payment_type' => '1',
            'pickup_type' => '1',
            'receive_name' => 'Receiver One',
            'receive_mobile' => '0810000001',
        ])->assertOk();

        $this->actingAs($user)->postJson('/admin/orders/store', [
            'sender_name' => 'Another Sender',
            'sender_mobile' => '0809999999',
            'parcel_description' => 'กล่องสินค้า',
            'parcel_pice' => 75,
            'payment_type' => '2',
            'pickup_type' => '1',
            'receive_name' => 'Shared Person',
            'receive_mobile' => '080-123-4567',
        ])->assertOk();

        $this->assertDatabaseHas('contacts', [
            'mobile' => '0801234567',
            'type' => 'both',
            'name' => 'Shared Person',
        ]);
    }

    public function test_trip_costs_can_be_added_listed_and_blocked_after_trip_completion()
    {
        $user = $this->createUser();
        [$trip] = $this->createTripWithItem([], ['parcel_pice' => 200], ['cod_amount' => 200]);

        $this->actingAs($user)
            ->post('/admin/trips/' . $trip->id . '/costs', [
                'type' => 'fuel',
                'description' => 'เติมน้ำมัน',
                'amount' => 30,
            ])
            ->assertRedirect('/admin/trips/' . $trip->id);

        $this->assertDatabaseHas('trip_costs', [
            'trip_id' => $trip->id,
            'type' => 'fuel',
            'description' => 'เติมน้ำมัน',
            'amount' => 30,
        ]);

        $this->actingAs($user)
            ->get('/admin/trips/' . $trip->id)
            ->assertOk()
            ->assertSee('รายรับค่าขนส่ง')
            ->assertSee('200.00')
            ->assertSee('30.00')
            ->assertSee('170.00');

        $trip->update(['status' => Trip::STATUS_COMPLETED]);
        $cost = $trip->costs()->first();

        $this->actingAs($user)
            ->delete('/admin/trip-costs/' . $cost->id)
            ->assertSessionHasErrors('cost');

        $this->assertDatabaseHas('trip_costs', [
            'id' => $cost->id,
        ]);
    }

    public function test_trip_api_lists_trips_updates_statuses_and_returns_parcel_detail()
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);

        [$trip, $tripItem, $receiver] = $this->createTripWithItem([
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        $this->getJson('/api/trips?status=' . Trip::STATUS_IN_TRANSIT)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.code', $trip->code);

        $this->getJson('/api/trips/' . $trip->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.parcel_code', $receiver->parcel_code);

        $this->postJson('/api/trip-items/' . $tripItem->id . '/delivery-status', [
            'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
            'note' => 'ส่งสำเร็จ',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.delivery_status', TripItem::DELIVERY_STATUS_DELIVERED);

        $this->postJson('/api/trip-items/' . $tripItem->id . '/payment-status', [
            'payment_status' => TripItem::PAYMENT_STATUS_PAID,
            'collected_amount' => 125.75,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment_status', TripItem::PAYMENT_STATUS_PAID);

        $this->getJson('/api/parcels/' . $receiver->parcel_code)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_receive.parcel_code', $receiver->parcel_code)
            ->assertJsonPath('data.current_trip_item.delivery_status', TripItem::DELIVERY_STATUS_DELIVERED)
            ->assertJsonCount(1, 'data.status_logs');
    }

    private function createUser(): User
    {
        return User::create([
            'username' => 'admin-feature',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'Feature',
            'email' => 'admin-feature@example.com',
            'status' => 'active',
            'role_name' => 'admin',
            'username_verified_at' => '2026-06-06 00:00:00',
        ]);
    }

    private function createTripWithItem(array $tripOverrides = [], array $receiverOverrides = [], array $itemOverrides = []): array
    {
        $trip = Trip::create(array_merge([
            'code' => 'RUN-20260606-' . str_pad((string) (Trip::count() + 1), 4, '0', STR_PAD_LEFT),
            'trip_date' => '2026-06-06',
            'driver_name' => 'Driver One',
            'driver_mobile' => '0809999999',
            'car_id' => 'CAR-01',
            'area_name' => 'Bangkok',
            'status' => Trip::STATUS_DRAFT,
        ], $tripOverrides));

        [$order, $receiver] = $this->createOrderAndReceiver($receiverOverrides);

        $tripItem = TripItem::create(array_merge([
            'trip_id' => $trip->id,
            'order_id' => $order->id,
            'order_receive_id' => $receiver->id,
            'parcel_code' => $receiver->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'cod_amount' => $receiver->parcel_pice,
            'collected_amount' => 0,
        ], $itemOverrides));

        $trip->update([
            'total_parcels' => $trip->tripItems()->count(),
            'total_cod_amount' => $trip->tripItems()->sum('cod_amount'),
            'collected_amount' => $trip->tripItems()->sum('collected_amount'),
        ]);

        return [$trip, $tripItem, $receiver, $order];
    }

    private function createOrderAndReceiver(array $receiverOverrides = []): array
    {
        $order = Order::create([
            'code' => 'OR2026LABEL' . str_pad((string) (Order::count() + 1), 4, '0', STR_PAD_LEFT),
            'customer_name' => 'Sender One',
            'customer_mobile' => '0800000000',
            'customer_address' => 'Sender address',
            'parcel_amount' => 1,
            'parcel_total' => $receiverOverrides['parcel_pice'] ?? 125.75,
            'order_status' => 'waiting',
        ]);

        $receiver = OrderReceive::create(array_merge([
            'order_id' => $order->id,
            'parcel_code' => 'P2026LABEL' . str_pad((string) (OrderReceive::count() + 1), 4, '0', STR_PAD_LEFT),
            'parcel_description' => 'Box',
            'receive_name' => 'Receiver One',
            'receive_mobile' => '0811111111',
            'receive_address' => '99 Destination Road',
            'district_name' => 'แขวงทดสอบ',
            'amphures_name' => 'เขตทดสอบ',
            'province_name' => 'กรุงเทพมหานคร',
            'zip_code' => '10200',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'parcel_pice' => 125.75,
        ], $receiverOverrides));

        return [$order, $receiver];
    }
}
