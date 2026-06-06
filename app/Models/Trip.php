<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Trip extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'trips';

    protected $casts = [
        'trip_date' => 'date:Y-m-d',
        'driver_user_id' => 'int',
        'total_parcels' => 'int',
        'total_cod_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'started_at' => 'datetime:Y-m-d H:i:s',
        'completed_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'code',
        'trip_date',
        'driver_user_id',
        'driver_name',
        'driver_mobile',
        'car_id',
        'area_name',
        'status',
        'total_parcels',
        'total_cod_amount',
        'collected_amount',
        'started_at',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    public function tripItems()
    {
        return $this->hasMany(TripItem::class);
    }

    public function costs()
    {
        return $this->hasMany(TripCost::class);
    }

    public function items()
    {
        return $this->tripItems();
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_ASSIGNED,
            self::STATUS_IN_TRANSIT,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'แบบร่าง',
            self::STATUS_ASSIGNED => 'มอบหมายแล้ว',
            self::STATUS_IN_TRANSIT => 'กำลังจัดส่ง',
            self::STATUS_COMPLETED => 'เสร็จสิ้น',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public static function statusLabel(string $status): string
    {
        return self::statusLabels()[$status] ?? $status;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->statusLabel($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_ASSIGNED => 'badge-info',
            self::STATUS_IN_TRANSIT => 'badge-primary',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
        ][$this->status] ?? 'badge-secondary';
    }

    public static function generateCode($date = null): string
    {
        $tripDate = Carbon::parse($date ?? now())->startOfDay();

        return DB::transaction(function () use ($tripDate) {
            $prefix = 'RUN-' . $tripDate->format('Ymd');
            $latestCode = self::query()
                ->whereDate('trip_date', $tripDate->toDateString())
                ->where('code', 'like', $prefix . '-%')
                ->lockForUpdate()
                ->orderByDesc('code')
                ->value('code');

            $nextNumber = 1;

            if ($latestCode && preg_match('/^' . preg_quote($prefix, '/') . '-(\d{4})$/', $latestCode, $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            }

            return sprintf('%s-%04d', $prefix, $nextNumber);
        });
    }
}
