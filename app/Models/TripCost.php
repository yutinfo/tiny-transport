<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripCost extends Model
{
    public const TYPE_FUEL = 'fuel';
    public const TYPE_DRIVER_WAGE = 'driver_wage';
    public const TYPE_TOLL = 'toll';
    public const TYPE_PARKING = 'parking';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_OTHER = 'other';

    protected $table = 'trip_costs';

    protected $casts = [
        'trip_id' => 'int',
        'amount' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'trip_id',
        'type',
        'description',
        'amount',
        'created_by',
        'updated_by',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public static function types(): array
    {
        return array_keys(self::typeLabels());
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_FUEL => 'ค่าน้ำมัน',
            self::TYPE_DRIVER_WAGE => 'ค่าแรงคนขับ',
            self::TYPE_TOLL => 'ค่าทางด่วน',
            self::TYPE_PARKING => 'ค่าจอดรถ',
            self::TYPE_MAINTENANCE => 'ค่าซ่อมบำรุง',
            self::TYPE_OTHER => 'อื่น ๆ',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return self::typeLabels()[$type] ?? $type;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabel($this->type);
    }
}
