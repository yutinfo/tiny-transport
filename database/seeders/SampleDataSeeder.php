<?php

namespace Database\Seeders;

use App\Models\Amphure;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\ParcelStatusLog;
use App\Models\Province;
use App\Models\Trip;
use App\Models\TripCost;
use App\Models\TripItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear old demo data to ensure a fresh, consistent state
        TripItem::query()->delete();
        TripCost::query()->delete();
        Trip::query()->delete();
        ParcelStatusLog::query()->delete();
        OrderReceive::query()->delete();
        Order::query()->delete();

        // 1. Seed extra drivers
        $driver1 = User::where('username', 'driver')->first();
        if ($driver1) {
            $driver1->update([
                'name' => 'สมยศ',
                'last_name' => 'ศรีดี',
            ]);
        } else {
            $driver1 = User::create([
                'username' => 'driver',
                'name' => 'สมยศ',
                'last_name' => 'ศรีดี',
                'email' => 'driver@admin.com',
                'status' => 'active',
                'role_name' => User::ROLE_DRIVER,
                'username_verified_at' => now(),
                'password' => 'password',
                'remember_token' => Str::random(10),
            ]);
        }

        $driver2 = User::updateOrCreate(
            ['username' => 'driver2'],
            [
                'name' => 'สมชาย',
                'last_name' => 'รักดี',
                'email' => 'driver2@admin.com',
                'status' => 'active',
                'role_name' => User::ROLE_DRIVER,
                'username_verified_at' => now(),
                'password' => 'password',
                'remember_token' => Str::random(10),
            ]
        );

        $driver3 = User::updateOrCreate(
            ['username' => 'driver3'],
            [
                'name' => 'วิชัย',
                'last_name' => 'ดวงดี',
                'email' => 'driver3@admin.com',
                'status' => 'active',
                'role_name' => User::ROLE_DRIVER,
                'username_verified_at' => now(),
                'password' => 'password',
                'remember_token' => Str::random(10),
            ]
        );

        $drivers = [$driver1, $driver2, $driver3];
        $carIds = ['1กก-1234', '2ขข-5678', '3คค-9012'];
        $driverMobiles = ['0891234567', '0812345678', '0867891234'];

        // 2. Fetch list of districts, amphures, provinces to populate sender and receiver locations
        $districts = District::all();
        if ($districts->isEmpty()) {
            throw new \RuntimeException('Location tables are empty. Please run LocationSeeder first.');
        }

        $amphures = Amphure::all()->keyBy('id');
        $provinces = Province::all()->keyBy('id');

        // Helper to get random geography info
        $getRandomLocation = function () use ($districts, $amphures, $provinces) {
            $district = $districts->random();
            $amphure = $amphures->get($district->amphure_id);
            $province = $provinces->get($amphure->province_id);

            return [
                'district_id' => $district->id,
                'district_name' => $district->name_th,
                'amphure_id' => $amphure->id,
                'amphure_name' => $amphure->name_th,
                'province_id' => $province->id,
                'province_name' => $province->name_th,
                'zip_code' => (string) $district->zip_code,
            ];
        };

        // Seed Senders (Orders) and Receivers (OrderReceives)
        $senderNames = [
            'กิตติศักดิ์ พูนทรัพย์', 'จรรยา วงศ์สว่าง', 'นภา พรประเสริฐ',
            'พีระ พงษ์ศิริ', 'วิภา รุ่งเรือง', 'ธงชัย มั่นคง',
            'วรรณภา สุขใจ', 'มานะ ชูใจ', 'ปิติ ยินดี', 'ชูศักดิ์ เลิศล้ำ'
        ];
        $senderMobiles = ['0811234567', '0898765432', '0861112222', '0853334444', '0825556666'];
        $senderAddresses = [
            '123/45 ถนนวิภาวดีรังสิต', '99 ม.2 ถนนเพชรเกษม', '456 ซอยสุขุมวิท 23',
            '12/8 ถนนพหลโยธิน', '789 ม.5 ถนนลาดพร้าว'
        ];

        $receiverNames = [
            'เอกชัย บุญมา', 'วิไลภรณ์ อินทร์ทอง', 'ชานนท์ ชาติไทย',
            'สิริวรรณ รักษ์ชาติ', 'ธีรยุทธ อารีย์', 'ณรงค์ เลิศวิจิตร',
            'กัญญารัตน์ ใจกว้าง', 'สุชาติ มีสุข', 'อมรา รุ่งเรืองอนันต์',
            'อารีพงศ์ แสงทอง', 'พัชรินทร์ สมบูรณ์', 'สมชาย เจริญสุข'
        ];
        $receiverMobiles = ['0831111111', '0842222222', '0853333333', '0864444444', '0875555555'];
        $receiverAddresses = [
            '78/9 ซอยรัชดาภิเษก 10', '101/5 ม.3 ถนนแจ้งวัฒนะ', '258 ถนนรามคำแหง',
            '45 ซอยทองหล่อ 13', '9/9 ถนนพระราม 2', '12 ซอยนวมินทร์ 50'
        ];

        $parcelDescriptions = [
            'เสื้อยืดแขนสั้น สีดำ', 'รองเท้าผ้าใบ แฟชั่น', 'เคสโทรศัพท์มือถือ iPhone 15',
            'หูฟังบลูทูธไร้สาย', 'กระเป๋าสะพายข้าง ผู้หญิง', 'หนังสือการ์ตูน มังงะ',
            'เครื่องสำอาง ลิปสติก', 'กาแฟสำเร็จรูป อาราบิก้า', 'วิตามินซี อาหารเสริม'
        ];

        // We will generate 25 orders to make a rich dashboard
        $orders = [];
        for ($i = 0; $i < 25; $i++) {
            $senderLoc = $getRandomLocation();
            $driverIndex = $i % 3;

            // Generate Order Code (matching format generateOrderCode in OrderController)
            $orderUuid = substr(str_replace('-', '', Str::uuid()->toString()), 0, 5);
            $orderCode = strtoupper('OR' . date('Y') . $orderUuid);

            $order = Order::create([
                'code' => $orderCode,
                'customer_name' => Arr::random($senderNames),
                'customer_mobile' => Arr::random($senderMobiles),
                'customer_address' => Arr::random($senderAddresses),
                'province_name' => $senderLoc['province_name'],
                'amphures_name' => $senderLoc['amphure_name'],
                'district_name' => $senderLoc['district_name'],
                'zip_code' => $senderLoc['zip_code'],
                'car_id' => $carIds[$driverIndex],
                'driver_name' => $drivers[$driverIndex]->name . ' ' . $drivers[$driverIndex]->last_name,
                'driver_mobile' => $driverMobiles[$driverIndex],
                'parcel_amount' => 0, // Will be updated
                'parcel_total' => 0,  // Will be updated
                'order_status' => 'waiting',
                'created_by' => 'System',
                'updated_by' => 'System',
            ]);

            // Add 1-3 receivers per order
            $parcelCount = rand(1, 3);
            $orderTotal = 0;

            for ($j = 0; $j < $parcelCount; $j++) {
                $receiveLoc = $getRandomLocation();
                $price = rand(50, 1500) + (rand(0, 3) * 0.25);
                $orderTotal += $price;

                $parcelUuid = substr(str_replace('-', '', Str::uuid()->toString()), 0, 9);
                $parcelCode = strtoupper('P' . date('Y') . $parcelUuid);

                $receiver = OrderReceive::create([
                    'order_id' => $order->id,
                    'parcel_code' => $parcelCode,
                    'parcel_description' => Arr::random($parcelDescriptions),
                    'receive_name' => Arr::random($receiverNames),
                    'receive_mobile' => Arr::random($receiverMobiles),
                    'receive_address' => Arr::random($receiverAddresses),
                    'province_id' => $receiveLoc['province_id'],
                    'amphures_id' => $receiveLoc['amphure_id'],
                    'district_id' => $receiveLoc['district_id'],
                    'province_name' => $receiveLoc['province_name'],
                    'amphures_name' => $receiveLoc['amphure_name'],
                    'district_name' => $receiveLoc['district_name'],
                    'zip_code' => $receiveLoc['zip_code'],
                    'parcel_pickup_type' => rand(1, 10) > 8 ? 'pickup' : 'delivery',
                    'payment_type' => rand(1, 2) === 1 ? 'immediately' : 'on_delivery',
                    'delivery_status' => 'waiting',
                    'payment_status' => 'waiting',
                    'parcel_pice' => $price,
                    'parcel_price' => $price,
                    'created_by' => 'System',
                    'updated_by' => 'System',
                ]);

                // Create initial parcel status log
                ParcelStatusLog::create([
                    'order_receive_id' => $receiver->id,
                    'from_status' => 'waiting',
                    'to_status' => 'waiting',
                    'note' => 'สร้างรายการพัสดุเรียบร้อย',
                    'created_by' => 'System',
                ]);
            }

            $order->update([
                'parcel_amount' => $parcelCount,
                'parcel_total' => $orderTotal,
            ]);

            $orders[] = $order;
        }

        // 3. Seed Trips
        $dates = [
            Carbon::yesterday()->toDateString(),
            Carbon::today()->toDateString(),
            Carbon::today()->toDateString(),
            Carbon::tomorrow()->toDateString()
        ];
        $statuses = [
            Trip::STATUS_COMPLETED,
            Trip::STATUS_IN_TRANSIT,
            Trip::STATUS_ASSIGNED,
            Trip::STATUS_DRAFT
        ];
        $tripDrivers = [$driver1, $driver2, $driver3, $driver1];
        $areas = ['สมุทรปราการ', 'กรุงเทพมหานคร', 'นนทบุรี', 'ปทุมธานี'];

        for ($t = 0; $t < 4; $t++) {
            $date = $dates[$t];
            $driver = $tripDrivers[$t];
            $driverIdx = array_search($driver, $drivers);
            if ($driverIdx === false) {
                $driverIdx = 0;
            }

            $tripCode = Trip::generateCode($date);

            $trip = Trip::create([
                'code' => $tripCode,
                'trip_date' => $date,
                'driver_user_id' => $driver->id,
                'driver_name' => $driver->name . ' ' . $driver->last_name,
                'driver_mobile' => $driverMobiles[$driverIdx],
                'car_id' => $carIds[$driverIdx],
                'area_name' => $areas[$t],
                'status' => $statuses[$t],
                'total_parcels' => 0,
                'total_cod_amount' => 0,
                'collected_amount' => 0,
                'started_at' => $statuses[$t] !== Trip::STATUS_DRAFT && $statuses[$t] !== Trip::STATUS_ASSIGNED ? Carbon::parse($date)->setHour(8)->setMinute(0) : null,
                'completed_at' => $statuses[$t] === Trip::STATUS_COMPLETED ? Carbon::parse($date)->setHour(16)->setMinute(30) : null,
                'created_by' => 'System',
                'updated_by' => 'System',
            ]);

            // Assign some parcels/receivers to the trip
            // Filter receivers based on status/dates to make it sensible
            // Let's grab some receivers that are not yet assigned to any trip items
            $assignedReceiveIds = TripItem::pluck('order_receive_id')->toArray();
            $availableReceivers = OrderReceive::whereNotIn('id', $assignedReceiveIds)->limit(4)->get();

            $totalParcels = 0;
            $totalCod = 0;
            $collected = 0;

            foreach ($availableReceivers as $idx => $receiver) {
                $order = $receiver->order;

                // Sync statuses based on trip status
                $itemDeliveryStatus = TripItem::DELIVERY_STATUS_WAITING;
                $itemPaymentStatus = TripItem::PAYMENT_STATUS_WAITING;
                $itemCollected = 0;
                $failedReason = null;
                $note = null;
                $deliveredAt = null;

                if ($trip->status === Trip::STATUS_COMPLETED) {
                    // All delivered or failed
                    if ($idx === 3) {
                        $itemDeliveryStatus = TripItem::DELIVERY_STATUS_FAILED;
                        $itemPaymentStatus = TripItem::PAYMENT_STATUS_UNPAID;
                        $failedReason = 'ติดต่อไม่ได้';
                        $note = 'โทรหาผู้รับแล้วสายตัด';
                    } else {
                        $itemDeliveryStatus = TripItem::DELIVERY_STATUS_DELIVERED;
                        $itemPaymentStatus = $receiver->payment_type === 'on_delivery' ? TripItem::PAYMENT_STATUS_PAID : TripItem::PAYMENT_STATUS_WAITING;
                        $itemCollected = $receiver->payment_type === 'on_delivery' ? (float)$receiver->getParcelPriceValue() : 0;
                        $deliveredAt = Carbon::parse($date)->setHour(10 + $idx)->setMinute(15);
                        $note = 'ส่งมอบให้ผู้รับเรียบร้อย';
                    }
                } elseif ($trip->status === Trip::STATUS_IN_TRANSIT) {
                    if ($idx === 0) {
                        $itemDeliveryStatus = TripItem::DELIVERY_STATUS_DELIVERED;
                        $itemPaymentStatus = $receiver->payment_type === 'on_delivery' ? TripItem::PAYMENT_STATUS_PAID : TripItem::PAYMENT_STATUS_WAITING;
                        $itemCollected = $receiver->payment_type === 'on_delivery' ? (float)$receiver->getParcelPriceValue() : 0;
                        $deliveredAt = Carbon::parse($date)->setHour(9)->setMinute(30);
                        $note = 'ฝากไว้ที่ป้อมยาม';
                    } elseif ($idx === 1) {
                        $itemDeliveryStatus = TripItem::DELIVERY_STATUS_FAILED;
                        $itemPaymentStatus = TripItem::PAYMENT_STATUS_UNPAID;
                        $failedReason = 'ผู้รับเลื่อนวันรับพัสดุ';
                        $note = 'เลื่อนไปวันพรุ่งนี้';
                    } else {
                        $itemDeliveryStatus = TripItem::DELIVERY_STATUS_IN_TRANSIT;
                    }
                } elseif ($trip->status === Trip::STATUS_ASSIGNED) {
                    $itemDeliveryStatus = TripItem::DELIVERY_STATUS_WAITING;
                }

                $codAmount = $receiver->payment_type === 'on_delivery' ? (float)$receiver->getParcelPriceValue() : 0;

                // Create Trip Item
                TripItem::create([
                    'trip_id' => $trip->id,
                    'order_id' => $order->id,
                    'order_receive_id' => $receiver->id,
                    'parcel_code' => $receiver->parcel_code,
                    'delivery_status' => $itemDeliveryStatus,
                    'payment_status' => $itemPaymentStatus,
                    'cod_amount' => $codAmount,
                    'collected_amount' => $itemCollected,
                    'failed_reason' => $failedReason,
                    'note' => $note,
                    'delivered_at' => $deliveredAt,
                    'created_by' => 'System',
                    'updated_by' => 'System',
                ]);

                // Sync receiver delivery status
                $receiver->update([
                    'delivery_status' => $itemDeliveryStatus,
                    'payment_status' => $itemPaymentStatus === TripItem::PAYMENT_STATUS_PAID ? 'success' : ($itemPaymentStatus === TripItem::PAYMENT_STATUS_UNPAID ? 'fail' : 'waiting'),
                ]);

                // Create parcel status log transitions
                if ($itemDeliveryStatus !== TripItem::DELIVERY_STATUS_WAITING) {
                    ParcelStatusLog::create([
                        'order_receive_id' => $receiver->id,
                        'trip_id' => $trip->id,
                        'from_status' => 'waiting',
                        'to_status' => $itemDeliveryStatus,
                        'note' => $note ?? 'อัปเดตสถานะโดยระบบ',
                        'created_by' => 'System',
                    ]);
                }

                $totalParcels++;
                $totalCod += $codAmount;
                $collected += $itemCollected;
            }

            // Update trip totals
            $trip->update([
                'total_parcels' => $totalParcels,
                'total_cod_amount' => $totalCod,
                'collected_amount' => $collected,
            ]);

            // Add Trip Costs
            if ($trip->status !== Trip::STATUS_DRAFT) {
                TripCost::create([
                    'trip_id' => $trip->id,
                    'type' => TripCost::TYPE_FUEL,
                    'description' => 'ค่าน้ำมันรอบวิ่งประจำวัน',
                    'amount' => rand(300, 600),
                    'created_by' => 'System',
                    'updated_by' => 'System',
                ]);

                if ($t === 0) { // Completed trip gets some toll charges
                    TripCost::create([
                        'trip_id' => $trip->id,
                        'type' => TripCost::TYPE_TOLL,
                        'description' => 'ค่าด่านทางด่วนพิเศษ',
                        'amount' => 50.00,
                        'created_by' => 'System',
                        'updated_by' => 'System',
                    ]);
                }
            }
        }
    }
}
