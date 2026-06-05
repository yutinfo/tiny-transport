<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';

    protected $casts = [
        'province_id' => 'int',
        'amphure_id' => 'int',
        'district_id' => 'int',
    ];

    protected $fillable = [
        'type',
        'name',
        'mobile',
        'address',
        'province_id',
        'amphure_id',
        'district_id',
        'province_name',
        'amphure_name',
        'district_name',
        'zip_code',
        'created_by',
        'updated_by',
    ];

    public static function typeLabels(): array
    {
        return [
            'sender' => 'ผู้ส่ง',
            'receiver' => 'ผู้รับ',
            'both' => 'ผู้ส่ง/ผู้รับ',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }
}
