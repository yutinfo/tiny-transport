<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelStatusLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'parcel_status_logs';

    protected $casts = [
        'order_receive_id' => 'int',
        'trip_id' => 'int',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        'order_receive_id',
        'trip_id',
        'from_status',
        'to_status',
        'note',
        'created_by',
    ];

    public function orderReceive()
    {
        return $this->belongsTo(OrderReceive::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
