<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripItem extends Model
{
    public const DELIVERY_STATUS_WAITING = 'waiting';
    public const DELIVERY_STATUS_PICKED_UP = 'picked_up';
    public const DELIVERY_STATUS_IN_TRANSIT = 'in_transit';
    public const DELIVERY_STATUS_DELIVERED = 'delivered';
    public const DELIVERY_STATUS_FAILED = 'failed';
    public const DELIVERY_STATUS_RETURNED = 'returned';

    public const PAYMENT_STATUS_WAITING = 'waiting';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_WAIVED = 'waived';

    protected $table = 'trip_items';

    protected $casts = [
        'trip_id' => 'int',
        'order_id' => 'int',
        'order_receive_id' => 'int',
        'cod_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'delivered_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'trip_id',
        'order_id',
        'order_receive_id',
        'parcel_code',
        'delivery_status',
        'payment_status',
        'cod_amount',
        'collected_amount',
        'failed_reason',
        'note',
        'delivered_at',
        'created_by',
        'updated_by',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderReceive()
    {
        return $this->belongsTo(OrderReceive::class);
    }

    public static function deliveryStatuses(): array
    {
        return [
            self::DELIVERY_STATUS_WAITING,
            self::DELIVERY_STATUS_PICKED_UP,
            self::DELIVERY_STATUS_IN_TRANSIT,
            self::DELIVERY_STATUS_DELIVERED,
            self::DELIVERY_STATUS_FAILED,
            self::DELIVERY_STATUS_RETURNED,
        ];
    }

    public static function deliveryStatusLabels(): array
    {
        return [
            self::DELIVERY_STATUS_WAITING => 'รอดำเนินการ',
            self::DELIVERY_STATUS_PICKED_UP => 'รับพัสดุแล้ว',
            self::DELIVERY_STATUS_IN_TRANSIT => 'กำลังจัดส่ง',
            self::DELIVERY_STATUS_DELIVERED => 'จัดส่งสำเร็จ',
            self::DELIVERY_STATUS_FAILED => 'จัดส่งไม่สำเร็จ',
            self::DELIVERY_STATUS_RETURNED => 'ส่งคืน',
        ];
    }

    public static function deliveryStatusLabel(string $status): string
    {
        return self::deliveryStatusLabels()[$status] ?? $status;
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        return self::deliveryStatusLabel($this->delivery_status);
    }

    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_WAITING,
            self::PAYMENT_STATUS_PAID,
            self::PAYMENT_STATUS_UNPAID,
            self::PAYMENT_STATUS_WAIVED,
        ];
    }

    public static function paymentStatusLabels(): array
    {
        return [
            self::PAYMENT_STATUS_WAITING => 'รอชำระ',
            self::PAYMENT_STATUS_PAID => 'ชำระแล้ว',
            self::PAYMENT_STATUS_UNPAID => 'ยังไม่ชำระ',
            self::PAYMENT_STATUS_WAIVED => 'ยกเว้น',
        ];
    }

    public static function paymentStatusLabel(string $status): string
    {
        return self::paymentStatusLabels()[$status] ?? $status;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::paymentStatusLabel($this->payment_status);
    }
}
