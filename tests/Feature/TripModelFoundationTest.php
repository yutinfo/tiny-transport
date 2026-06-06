<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\ParcelStatusLog;
use App\Models\Trip;
use App\Models\TripItem;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripModelFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_trip_code_uses_daily_running_number()
    {
        $this->assertSame('RUN-20260606-0001', Trip::generateCode('2026-06-06'));

        Trip::create([
            'code' => Trip::generateCode('2026-06-06'),
            'trip_date' => '2026-06-06',
            'status' => Trip::STATUS_DRAFT,
        ]);

        $this->assertSame('RUN-20260606-0002', Trip::generateCode('2026-06-06'));
    }

    public function test_status_helpers_return_thai_labels()
    {
        $this->assertSame('แบบร่าง', Trip::statusLabel(Trip::STATUS_DRAFT));
        $this->assertSame('กำลังจัดส่ง', Trip::statusLabel(Trip::STATUS_IN_TRANSIT));
        $this->assertSame('จัดส่งสำเร็จ', TripItem::deliveryStatusLabel(TripItem::DELIVERY_STATUS_DELIVERED));
        $this->assertSame('ยกเว้น', TripItem::paymentStatusLabel(TripItem::PAYMENT_STATUS_WAIVED));
    }

    public function test_trip_items_link_order_receiver_and_prevent_duplicate_receiver_in_same_trip()
    {
        [$order, $receiver] = $this->createOrderAndReceiver();
        $trip = $this->createTrip('RUN-20260606-0001');

        $tripItem = TripItem::create([
            'trip_id' => $trip->id,
            'order_id' => $order->id,
            'order_receive_id' => $receiver->id,
            'parcel_code' => $receiver->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'cod_amount' => $receiver->parcel_pice,
        ]);

        $this->assertTrue($trip->tripItems->contains($tripItem));
        $this->assertSame($order->id, $tripItem->order->id);
        $this->assertSame($receiver->id, $tripItem->orderReceive->id);
        $this->assertTrue($receiver->tripItems->contains($tripItem));

        $this->expectException(QueryException::class);

        TripItem::create([
            'trip_id' => $trip->id,
            'order_id' => $order->id,
            'order_receive_id' => $receiver->id,
            'parcel_code' => $receiver->parcel_code,
            'delivery_status' => TripItem::DELIVERY_STATUS_WAITING,
            'payment_status' => TripItem::PAYMENT_STATUS_WAITING,
            'cod_amount' => $receiver->parcel_pice,
        ]);
    }

    public function test_parcel_status_logs_link_receiver_and_optional_trip()
    {
        [, $receiver] = $this->createOrderAndReceiver();
        $trip = $this->createTrip('RUN-20260606-0001');

        $log = ParcelStatusLog::create([
            'order_receive_id' => $receiver->id,
            'trip_id' => $trip->id,
            'from_status' => null,
            'to_status' => TripItem::DELIVERY_STATUS_WAITING,
            'note' => 'เริ่มเข้ารอบจัดส่ง',
            'created_by' => 'Admin',
        ]);

        $this->assertSame($receiver->id, $log->orderReceive->id);
        $this->assertSame($trip->id, $log->trip->id);
        $this->assertTrue($receiver->statusLogs->contains($log));
    }

    private function createTrip(string $code): Trip
    {
        return Trip::create([
            'code' => $code,
            'trip_date' => '2026-06-06',
            'status' => Trip::STATUS_DRAFT,
        ]);
    }

    private function createOrderAndReceiver(): array
    {
        $order = Order::create([
            'code' => 'OR2026TRIP',
            'customer_name' => 'Sender',
            'customer_mobile' => '0800000000',
            'parcel_amount' => 1,
            'parcel_total' => 125.75,
            'order_status' => 'waiting',
        ]);

        $receiver = OrderReceive::create([
            'order_id' => $order->id,
            'parcel_code' => 'P2026TRIP',
            'receive_name' => 'Receiver',
            'receive_mobile' => '0811111111',
            'parcel_pickup_type' => 'delivery',
            'payment_type' => 'on_delivery',
            'delivery_status' => 'waiting',
            'payment_status' => 'waiting',
            'parcel_pice' => 125.75,
        ]);

        return [$order, $receiver];
    }
}
