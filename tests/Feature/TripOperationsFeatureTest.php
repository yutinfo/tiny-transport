<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\ParcelStatusLog;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripOperationsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_can_view_parcel_tracking_timeline()
    {
        $user = $this->createUser();
        [$trip, $tripItem, $receiver, $order] = $this->createTripWithItem();

        ParcelStatusLog::create([
            'order_receive_id' => $receiver->id,
            'trip_id' => $trip->id,
            'from_status' => TripItem::DELIVERY_STATUS_WAITING,
            'to_status' => TripItem::DELIVERY_STATUS_DELIVERED,
            'note' => 'ส่งสำเร็จ',
            'created_by' => 'Driver One',
            'created_at' => '2026-06-06 10:15:00',
        ]);

        $this->actingAs($user)
            ->get('/admin/parcels/' . $receiver->id . '/tracking')
            ->assertOk()
            ->assertSee($tripItem->parcel_code)
            ->assertSee($order->code)
            ->assertSee('จัดส่งสำเร็จ')
            ->assertSee('ส่งสำเร็จ')
            ->assertSee('Driver One')
            ->assertSee($trip->code);
    }

    public function test_driver_view_shows_mobile_actions_and_updates_delivery_status()
    {
        $user = $this->createUser();
        [$trip, $tripItem, $receiver] = $this->createTripWithItem([
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        $this->actingAs($user)
            ->get('/admin/trips/' . $trip->id . '/driver')
            ->assertOk()
            ->assertSee($trip->code)
            ->assertSee($receiver->receive_name)
            ->assertSee('tel:' . $receiver->receive_mobile, false)
            ->assertSee('เปิดแผนที่');

        $this->actingAs($user)
            ->post('/admin/driver/trip-items/' . $tripItem->id . '/delivery-status', [
                'delivery_status' => TripItem::DELIVERY_STATUS_FAILED,
                'failed_reason' => 'ติดต่อไม่ได้',
                'note' => 'โทรไม่ติด',
            ])
            ->assertRedirect('/admin/trips/' . $trip->id . '/driver');

        // The trip item keeps 'failed' as this trip's record...
        $this->assertDatabaseHas('trip_items', [
            'id' => $tripItem->id,
            'delivery_status' => TripItem::DELIVERY_STATUS_FAILED,
            'failed_reason' => 'ติดต่อไม่ได้',
        ]);
        // ...but the parcel is re-queued (order_receive -> waiting) for a future trip.
        $this->assertDatabaseHas('order_receives', [
            'id' => $receiver->id,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
        ]);
        $this->assertDatabaseHas('parcel_status_logs', [
            'order_receive_id' => $receiver->id,
            'trip_id' => $trip->id,
            'to_status' => TripItem::DELIVERY_STATUS_FAILED,
            'note' => 'โทรไม่ติด',
        ]);
    }

    public function test_failed_parcel_can_be_reassigned_but_returned_cannot()
    {
        $user = $this->createUser();
        $tripService = app(\App\Services\TripService::class);

        // FAILED parcel: re-queued, can be assigned to a new trip
        [$failTrip, $failItem, $failReceiver] = $this->createTripWithItem([
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);
        $tripService->updateDeliveryStatus($failItem, TripItem::DELIVERY_STATUS_FAILED, 'note', 'ติดต่อไม่ได้');

        $newTrip = $this->createTrip(['status' => Trip::STATUS_DRAFT]);
        $reassigned = $tripService->assignParcel($newTrip, $failReceiver->fresh());
        $this->assertSame(TripItem::DELIVERY_STATUS_WAITING, $reassigned->delivery_status);

        // RETURNED parcel: terminal, cannot be re-assigned
        [$retTrip, $retItem, $retReceiver] = $this->createTripWithItem([
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);
        $tripService->updateDeliveryStatus($retItem, TripItem::DELIVERY_STATUS_RETURNED, null, 'ลูกค้าปฏิเสธรับ');

        $this->assertDatabaseHas('order_receives', [
            'id' => $retReceiver->id,
            'delivery_status' => TripItem::DELIVERY_STATUS_RETURNED,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $tripService->assignParcel($this->createTrip(['status' => Trip::STATUS_DRAFT]), $retReceiver->fresh());
    }

    public function test_cod_must_be_collected_before_parcel_is_marked_delivered()
    {
        $user = $this->createUser();
        [$trip, $tripItem] = $this->createTripWithItem([
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        // 1) cannot mark a COD parcel delivered before the money is collected
        $this->actingAs($user)
            ->from('/admin/trips/' . $trip->id . '/driver')
            ->post('/admin/driver/trip-items/' . $tripItem->id . '/delivery-status', [
                'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
                'note' => 'ส่งสำเร็จ',
            ])
            ->assertSessionHasErrors('delivery_status');

        $this->assertDatabaseHas('trip_items', [
            'id' => $tripItem->id,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
        ]);

        // 2) collecting COD BEFORE delivery is now allowed (no "must be delivered first")
        $this->actingAs($user)
            ->post('/admin/driver/trip-items/' . $tripItem->id . '/payment-status', [
                'payment_status' => TripItem::PAYMENT_STATUS_PAID,
                'collected_amount' => 125.75,
            ])
            ->assertRedirect('/admin/trips/' . $trip->id . '/driver');

        $this->assertDatabaseHas('trip_items', [
            'id' => $tripItem->id,
            'payment_status' => TripItem::PAYMENT_STATUS_PAID,
            'collected_amount' => 125.75,
        ]);

        // 3) once paid, the parcel can be marked delivered
        $this->actingAs($user)
            ->post('/admin/driver/trip-items/' . $tripItem->id . '/delivery-status', [
                'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
                'note' => 'ส่งสำเร็จ',
            ])
            ->assertRedirect('/admin/trips/' . $trip->id . '/driver');

        $this->assertDatabaseHas('trip_items', [
            'id' => $tripItem->id,
            'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
            'payment_status' => TripItem::PAYMENT_STATUS_PAID,
        ]);
    }

    public function test_dashboard_shows_operational_kpis_for_selected_date_range()
    {
        $user = $this->createUser();
        [$todayTrip, $deliveredItem] = $this->createTripWithItem([
            'code' => 'RUN-20260606-0001',
            'trip_date' => '2026-06-06',
            'status' => Trip::STATUS_IN_TRANSIT,
        ], [], [
            'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
            'payment_status' => TripItem::PAYMENT_STATUS_PAID,
            'collected_amount' => 125.75,
        ]);
        $this->createTripItem($todayTrip, [
            'parcel_code' => 'P2026FAILED',
            'receive_name' => 'Failed Receiver',
        ], [
            'delivery_status' => TripItem::DELIVERY_STATUS_FAILED,
            'payment_status' => TripItem::PAYMENT_STATUS_UNPAID,
            'cod_amount' => 80,
        ]);
        $todayTrip->update([
            'total_parcels' => 2,
            'total_cod_amount' => 205.75,
            'collected_amount' => 125.75,
        ]);
        $olderTrip = $this->createTrip([
            'code' => 'RUN-20260605-0001',
            'trip_date' => '2026-06-05',
            'status' => Trip::STATUS_COMPLETED,
        ]);

        $this->actingAs($user)
            ->get('/admin/dashboard?date_from=2026-06-06&date_to=2026-06-06')
            ->assertOk()
            ->assertSee('KPI การขนส่ง')
            ->assertSee('รอบขนส่ง')
            ->assertSee('ส่งสำเร็จ')
            ->assertSee('ยอด COD คงเหลือ')
            ->assertSee('อัตราส่งสำเร็จ')
            ->assertSee($todayTrip->code)
            ->assertDontSee($olderTrip->code)
            ->assertSee('50.00');

        $this->assertSame(TripItem::DELIVERY_STATUS_DELIVERED, $deliveredItem->delivery_status);
    }

    public function test_dashboard_prioritizes_summary_filters_recent_trips_and_reports()
    {
        $user = $this->createUser();
        $this->createTripWithItem([
            'code' => 'RUN-20260606-0001',
            'trip_date' => '2026-06-06',
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        $this->actingAs($user)
            ->get('/admin/dashboard?date_from=2026-06-06&date_to=2026-06-06')
            ->assertOk()
            ->assertSeeInOrder([
                'ภาพรวมการขนส่ง',
                'ตัวกรองข้อมูล',
                'สรุปการขนส่ง',
                'รอบขนส่งล่าสุด',
                'รายงานพัสดุ',
            ]);
    }

    public function test_dashboard_uses_compact_header_and_safe_empty_report_table()
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/admin/dashboard?date_from=2026-06-06&date_to=2026-06-06')
            ->assertOk()
            ->assertSee('แดชบอร์ดการขนส่ง')
            ->assertDontSee('Operational overview')
            ->assertSee('id="order_table"', false)
            ->assertDontSee('colspan="9" class="text-center text-muted">ไม่พบข้อมูลรายงานพัสดุตามตัวกรองที่เลือก', false)
            ->assertSee('ไม่พบข้อมูลรายงานพัสดุตามตัวกรองที่เลือก');
    }

    public function test_trip_detail_uses_modernized_admin_page_structure()
    {
        $user = $this->createUser();
        [$trip, $tripItem, $receiver] = $this->createTripWithItem();

        $this->actingAs($user)
            ->get('/admin/trips/' . $trip->id)
            ->assertOk()
            ->assertSee('class="container-fluid ta-page-shell"', false)
            ->assertSee('class="ta-page-header-card"', false)
            ->assertSee('class="ta-kpi-grid"', false)
            ->assertSee('class="card ta-table-card"', false)
            ->assertSee('class="ta-form-layout ta-trip-summary-layout"', false)
            ->assertSee('class="card ta-table-card ta-trip-full-width-section"', false)
            ->assertSee('class="ta-trip-cost-form"', false)
            ->assertSee('class="ta-trip-item-actions"', false)
            ->assertSee('Trips')
            ->assertSee('รอบขนส่ง ' . $trip->code)
            ->assertSee('ภาพรวมรอบขนส่ง')
            ->assertSee('ต้นทุนและกำไรโดยประมาณ')
            ->assertSee('ข้อมูลรอบและการดำเนินการ')
            ->assertSee('รายการพัสดุในรอบ')
            ->assertSee($tripItem->parcel_code)
            ->assertSee($receiver->receive_name);
    }

    public function test_trip_csv_exports_stream_thai_excel_friendly_output()
    {
        $user = $this->createUser();
        [$trip, $tripItem, $receiver] = $this->createTripWithItem([
            'trip_date' => '2026-06-06',
            'status' => Trip::STATUS_COMPLETED,
            'completed_at' => '2026-06-06 18:00:00',
            'total_parcels' => 1,
            'total_cod_amount' => 125.75,
            'collected_amount' => 125.75,
        ], [], [
            'delivery_status' => TripItem::DELIVERY_STATUS_DELIVERED,
            'payment_status' => TripItem::PAYMENT_STATUS_PAID,
            'collected_amount' => 125.75,
            'delivered_at' => '2026-06-06 10:30:00',
        ]);

        $summary = $this->actingAs($user)
            ->get('/admin/trips/export/csv?date_from=2026-06-06&date_to=2026-06-06');

        $summary->assertOk();
        $summaryCsv = $summary->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $summaryCsv);
        $this->assertStringContainsString('รหัสรอบ', $summaryCsv);
        $this->assertStringContainsString($trip->code, $summaryCsv);

        $items = $this->actingAs($user)
            ->get('/admin/trips/' . $trip->id . '/items/export/csv');

        $items->assertOk();
        $itemsCsv = $items->streamedContent();
        $this->assertStringContainsString('รหัสพัสดุ', $itemsCsv);
        $this->assertStringContainsString($tripItem->parcel_code, $itemsCsv);
        $this->assertStringContainsString($receiver->receive_name, $itemsCsv);

        $cod = $this->actingAs($user)
            ->get('/admin/trips/' . $trip->id . '/cod/export/csv');

        $cod->assertOk();
        $codCsv = $cod->streamedContent();
        $this->assertStringContainsString('ยอด COD', $codCsv);
        $this->assertStringContainsString('125.75', $codCsv);
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
        ]);
    }

    private function createTripWithItem(array $tripOverrides = [], array $receiverOverrides = [], array $itemOverrides = []): array
    {
        $trip = $this->createTrip($tripOverrides);
        [$order, $receiver] = $this->createOrderAndReceiver($receiverOverrides);
        $tripItem = TripItem::create(array_merge([
            'trip_id' => $trip->id,
            'order_id' => $order->id,
            'order_receive_id' => $receiver->id,
            'parcel_code' => $receiver->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'cod_amount' => 125.75,
            'collected_amount' => 0,
        ], $itemOverrides));

        $trip->update([
            'total_parcels' => $trip->tripItems()->count(),
            'total_cod_amount' => $trip->tripItems()->sum('cod_amount'),
            'collected_amount' => $trip->tripItems()->sum('collected_amount'),
        ]);

        return [$trip, $tripItem, $receiver, $order];
    }

    private function createTripItem(Trip $trip, array $receiverOverrides = [], array $itemOverrides = []): TripItem
    {
        [$order, $receiver] = $this->createOrderAndReceiver($receiverOverrides);

        return TripItem::create(array_merge([
            'trip_id' => $trip->id,
            'order_id' => $order->id,
            'order_receive_id' => $receiver->id,
            'parcel_code' => $receiver->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'cod_amount' => $receiver->parcel_pice,
            'collected_amount' => 0,
        ], $itemOverrides));
    }

    private function createTrip(array $overrides = []): Trip
    {
        $date = $overrides['trip_date'] ?? '2026-06-06';

        return Trip::create(array_merge([
            'code' => 'RUN-' . str_replace('-', '', $date) . '-' . str_pad((string) (Trip::count() + 1), 4, '0', STR_PAD_LEFT),
            'trip_date' => $date,
            'driver_name' => 'Driver One',
            'driver_mobile' => '0809999999',
            'car_id' => 'CAR-01',
            'area_name' => 'Bangkok',
            'status' => Trip::STATUS_DRAFT,
        ], $overrides));
    }

    private function createOrderAndReceiver(array $receiverOverrides = []): array
    {
        $orderNumber = str_pad((string) (Order::count() + 1), 4, '0', STR_PAD_LEFT);
        $order = Order::create([
            'code' => 'OR2026OPS' . $orderNumber,
            'customer_name' => 'Sender',
            'customer_mobile' => '0800000000',
            'customer_address' => 'Sender address',
            'parcel_amount' => 1,
            'parcel_total' => $receiverOverrides['parcel_pice'] ?? 125.75,
            'order_status' => 'waiting',
        ]);

        $receiverNumber = str_pad((string) (OrderReceive::count() + 1), 4, '0', STR_PAD_LEFT);
        $receiver = OrderReceive::create(array_merge([
            'order_id' => $order->id,
            'parcel_code' => 'P2026OPS' . $receiverNumber,
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
