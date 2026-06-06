<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderReceive;
use App\Models\Trip;
use App\Services\SimpleQrCode;

class ParcelLabelController extends Controller
{
    public function __construct(private SimpleQrCode $qrCode)
    {
    }

    public function order(Order $order)
    {
        $order->load('receivers');

        return view('admin.parcel.labels', [
            'title' => 'พิมพ์ใบปะหน้าพัสดุ',
            'subtitle' => 'Order ' . $order->code,
            'labels' => $order->receivers->map(fn (OrderReceive $receiver) => $this->labelData($receiver, $order))->values(),
        ]);
    }

    public function trip(Trip $trip)
    {
        $trip->load([
            'tripItems.order',
            'tripItems.orderReceive',
        ]);

        return view('admin.parcel.labels', [
            'title' => 'พิมพ์ใบปะหน้าพัสดุ',
            'subtitle' => 'Trip ' . $trip->code,
            'labels' => $trip->tripItems->map(function ($item) {
                return $this->labelData($item->orderReceive, $item->order, $item);
            })->values(),
        ]);
    }

    private function labelData(OrderReceive $receiver, ?Order $order, $tripItem = null): array
    {
        $parcelCode = (string) $receiver->parcel_code;

        return [
            'parcel_code' => $parcelCode,
            'qr_svg' => $this->qrCode->svg($parcelCode),
            'sender_name' => $order->customer_name ?? '-',
            'sender_mobile' => $order->customer_mobile ?? '-',
            'receiver_name' => $receiver->receive_name ?? '-',
            'receiver_mobile' => $receiver->receive_mobile ?? '-',
            'destination_address' => trim(implode(' ', array_filter([
                $receiver->receive_address,
                $receiver->district_name,
                $receiver->amphures_name,
                $receiver->province_name,
                $receiver->zip_code,
            ]))),
            'payment_type' => $this->paymentTypeLabel($receiver->payment_type),
            'cod_amount' => $receiver->payment_type === 'on_delivery' ? (float) $receiver->parcel_pice : null,
            'pickup_type' => $this->pickupTypeLabel($receiver->parcel_pickup_type),
            'created_date' => optional($receiver->created_at)->format('Y-m-d'),
            'order_code' => $order->code ?? '-',
            'trip_code' => $tripItem?->trip?->code,
        ];
    }

    private function paymentTypeLabel(?string $paymentType): string
    {
        return [
            'immediately' => 'จ่ายทันที',
            'on_delivery' => 'เก็บเงินปลายทาง',
        ][$paymentType] ?? ($paymentType ?: '-');
    }

    private function pickupTypeLabel(?string $pickupType): string
    {
        return [
            'pickup' => 'รับที่ร้าน',
            'delivery' => 'จัดส่งปกติ',
        ][$pickupType] ?? ($pickupType ?: '-');
    }
}
