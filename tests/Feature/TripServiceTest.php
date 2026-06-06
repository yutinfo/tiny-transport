<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Models\TripItem;
use App\Services\TripService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TripServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_assign_parcel_to_trip_copies_receiver_data_and_recalculates_totals()
    {
        $service = new TripService();
        $trip = $this->createTrip();
        [$order, $receiver] = $this->createOrderAndReceiver([
            'payment_type' => 'on_delivery',
            'delivery_status' => TripItem::DELIVERY_STATUS_PICKED_UP,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'parcel_pice' => 125.75,
        ]);

        $tripItem = $service->assignParcel($trip, $receiver, 'Admin');

        $this->assertSame($trip->id, $tripItem->trip_id);
        $this->assertSame($order->id, $tripItem->order_id);
        $this->assertSame($receiver->id, $tripItem->order_receive_id);
        $this->assertSame($receiver->parcel_code, $tripItem->parcel_code);
        $this->assertSame(TripItem::DELIVERY_STATUS_PICKED_UP, $tripItem->delivery_status);
        $this->assertSame(TripItem::PAYMENT_STATUS_WAITING, $tripItem->payment_status);
        $this->assertSame('125.75', $tripItem->cod_amount);
        $this->assertSame('Admin', $tripItem->created_by);

        $trip->refresh();

        $this->assertSame(1, $trip->total_parcels);
        $this->assertSame('125.75', $trip->total_cod_amount);
        $this->assertSame('0.00', $trip->collected_amount);
    }

    public function test_assign_parcel_creates_initial_tracking_log()
    {
        $service = new TripService();
        $trip = $this->createTrip();
        [, $receiver] = $this->createOrderAndReceiver([
            'delivery_status' => null,
        ]);

        $service->assignParcel($trip, $receiver, 'Admin');

        $this->assertDatabaseHas('parcel_status_logs', [
            'order_receive_id' => $receiver->id,
            'trip_id' => $trip->id,
            'from_status' => null,
            'to_status' => TripItem::DELIVERY_STATUS_WAITING,
            'note' => 'เพิ่มเข้ารอบขนส่ง ' . $trip->code,
            'created_by' => 'Admin',
        ]);
    }

    public function test_assign_parcel_blocks_duplicate_active_assignment_across_trips()
    {
        $service = new TripService();
        $firstTrip = $this->createTrip('RUN-20260606-0001');
        $secondTrip = $this->createTrip('RUN-20260606-0002');
        [, $receiver] = $this->createOrderAndReceiver();

        $service->assignParcel($firstTrip, $receiver);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('พัสดุนี้ถูกจัดเข้ารอบจัดส่งที่ยังใช้งานอยู่แล้ว');

        $service->assignParcel($secondTrip, $receiver);
    }

    public function test_assign_parcel_allows_new_trip_when_previous_item_failed()
    {
        $service = new TripService();
        $firstTrip = $this->createTrip('RUN-20260606-0001');
        $secondTrip = $this->createTrip('RUN-20260606-0002');
        [, $receiver] = $this->createOrderAndReceiver();

        $firstItem = $service->assignParcel($firstTrip, $receiver);
        $service->updateDeliveryStatus($firstItem, TripItem::DELIVERY_STATUS_FAILED, 'ติดต่อผู้รับไม่ได้', 'ติดต่อผู้รับไม่ได้');

        $secondItem = $service->assignParcel($secondTrip, $receiver);

        $this->assertSame($secondTrip->id, $secondItem->trip_id);
        $this->assertSame($receiver->id, $secondItem->order_receive_id);
    }

    public function test_update_delivery_status_updates_receiver_and_creates_log()
    {
        $service = new TripService();
        $trip = $this->createTrip();
        [, $receiver] = $this->createOrderAndReceiver();
        $tripItem = $service->assignParcel($trip, $receiver);

        $updatedItem = $service->updateDeliveryStatus($tripItem, TripItem::DELIVERY_STATUS_DELIVERED, 'ส่งสำเร็จ', null, 'Admin');

        $this->assertSame(TripItem::DELIVERY_STATUS_DELIVERED, $updatedItem->delivery_status);
        $this->assertNotNull($updatedItem->delivered_at);

        $receiver->refresh();

        $this->assertSame(TripItem::DELIVERY_STATUS_DELIVERED, $receiver->delivery_status);
        $this->assertDatabaseHas('parcel_status_logs', [
            'order_receive_id' => $receiver->id,
            'trip_id' => $trip->id,
            'from_status' => TripItem::DELIVERY_STATUS_WAITING,
            'to_status' => TripItem::DELIVERY_STATUS_DELIVERED,
            'note' => 'ส่งสำเร็จ',
            'created_by' => 'Admin',
        ]);
    }

    public function test_complete_trip_fails_when_item_is_not_final()
    {
        $service = new TripService();
        $trip = $this->createTrip();
        [, $receiver] = $this->createOrderAndReceiver();

        $service->assignParcel($trip, $receiver);
        $service->startTrip($service->assignTrip($trip));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ยังมีพัสดุที่ยังไม่จบสถานะจัดส่ง');

        $service->completeTrip($trip);
    }

    public function test_complete_trip_succeeds_when_all_items_are_final()
    {
        $service = new TripService();
        $trip = $this->createTrip();
        [, $receiver] = $this->createOrderAndReceiver();

        $tripItem = $service->assignParcel($trip, $receiver);
        $service->assignTrip($trip);
        $service->startTrip($trip);
        $service->updateDeliveryStatus($tripItem, TripItem::DELIVERY_STATUS_DELIVERED, 'ส่งสำเร็จ');
        $service->updatePaymentCollection($tripItem, TripItem::PAYMENT_STATUS_PAID, 125.75);

        $completedTrip = $service->completeTrip($trip);

        $this->assertSame(Trip::STATUS_COMPLETED, $completedTrip->status);
        $this->assertNotNull($completedTrip->completed_at);
        $this->assertSame(1, $completedTrip->total_parcels);
        $this->assertSame('125.75', $completedTrip->total_cod_amount);
        $this->assertSame('125.75', $completedTrip->collected_amount);
    }

    public function test_assign_items_assigns_multiple_receivers()
    {
        $service = new TripService();
        $trip = $this->createTrip();
        [, $firstReceiver] = $this->createOrderAndReceiver(['parcel_code' => 'P2026BULK01']);
        [, $secondReceiver] = $this->createOrderAndReceiver(['parcel_code' => 'P2026BULK02', 'parcel_pice' => 50]);

        $items = $service->assignItems($trip, [$firstReceiver->id, $secondReceiver->id], 'Admin');

        $this->assertCount(2, $items);
        $this->assertDatabaseHas('trip_items', [
            'trip_id' => $trip->id,
            'order_receive_id' => $firstReceiver->id,
        ]);
        $this->assertDatabaseHas('trip_items', [
            'trip_id' => $trip->id,
            'order_receive_id' => $secondReceiver->id,
        ]);

        $trip->refresh();

        $this->assertSame(2, $trip->total_parcels);
        $this->assertSame('175.75', $trip->total_cod_amount);
    }

    public function test_remove_item_recalculates_trip_totals()
    {
        $service = new TripService();
        $trip = $this->createTrip();
        [, $receiver] = $this->createOrderAndReceiver();
        $tripItem = $service->assignParcel($trip, $receiver);

        $service->removeItem($tripItem);

        $this->assertDatabaseMissing('trip_items', [
            'id' => $tripItem->id,
        ]);

        $trip->refresh();

        $this->assertSame(0, $trip->total_parcels);
        $this->assertSame('0.00', $trip->total_cod_amount);
    }

    public function test_remove_item_fails_when_trip_is_completed()
    {
        $service = new TripService();
        $trip = $this->createTrip();
        [, $receiver] = $this->createOrderAndReceiver();
        $tripItem = $service->assignParcel($trip, $receiver);
        $service->assignTrip($trip);
        $service->startTrip($trip);
        $service->updateDeliveryStatus($tripItem, TripItem::DELIVERY_STATUS_DELIVERED, 'ส่งสำเร็จ');
        $service->updatePaymentCollection($tripItem, TripItem::PAYMENT_STATUS_PAID, 125.75);
        $service->completeTrip($trip);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ลบพัสดุได้เฉพาะรอบจัดส่งแบบร่างหรือมอบหมายแล้ว');

        $service->removeItem($tripItem);
    }

    public function test_paid_payment_defaults_to_cod_amount_when_amount_is_empty()
    {
        $service = new TripService();
        $trip = $this->createTrip();
        [, $receiver] = $this->createOrderAndReceiver();
        $tripItem = $service->assignParcel($trip, $receiver);

        $updatedItem = $service->updatePaymentCollection($tripItem, TripItem::PAYMENT_STATUS_PAID, null);

        $this->assertSame('125.75', $updatedItem->collected_amount);
    }

    private function createTrip(string $code = 'RUN-20260606-0001'): Trip
    {
        return Trip::create([
            'code' => $code,
            'trip_date' => '2026-06-06',
            'status' => Trip::STATUS_DRAFT,
        ]);
    }

    private function createOrderAndReceiver(array $receiverOverrides = []): array
    {
        $order = Order::create([
            'code' => 'OR2026SERVICE' . str_pad((string) Order::count(), 2, '0', STR_PAD_LEFT),
            'customer_name' => 'Sender',
            'customer_mobile' => '0800000000',
            'parcel_amount' => 1,
            'parcel_total' => $receiverOverrides['parcel_pice'] ?? 125.75,
            'order_status' => 'waiting',
        ]);

        $receiver = OrderReceive::create(array_merge([
            'order_id' => $order->id,
            'parcel_code' => 'P2026SERVICE' . str_pad((string) OrderReceive::count(), 2, '0', STR_PAD_LEFT),
            'receive_name' => 'Receiver',
            'receive_mobile' => '0811111111',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'parcel_pice' => 125.75,
        ], $receiverOverrides));

        return [$order, $receiver];
    }
}
