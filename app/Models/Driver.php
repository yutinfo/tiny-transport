<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Driver extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'drivers';

    protected $fillable = [
        'code',
        'name',
        'last_name',
        'mobile',
        'license_plate',
        'driver_license_no',
        'area_name',
        'note',
        'status',
        'user_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'user_id' => 'int',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . $this->last_name);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status === self::STATUS_ACTIVE ? 'badge-success' : 'badge-secondary';
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_ACTIVE => 'ใช้งาน',
            self::STATUS_INACTIVE => 'ปิดใช้งาน',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Generate the next driver code, e.g. DRV-0001.
     */
    public static function generateCode(): string
    {
        return DB::transaction(function () {
            $latestCode = self::query()
                ->where('code', 'like', 'DRV-%')
                ->lockForUpdate()
                ->orderByDesc('code')
                ->value('code');

            $nextNumber = 1;

            if ($latestCode && preg_match('/^DRV-(\d{4})$/', $latestCode, $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            }

            return sprintf('DRV-%04d', $nextNumber);
        });
    }
}
