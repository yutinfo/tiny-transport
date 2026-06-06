<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelNotification extends Model
{
    protected $table = 'parcel_notifications';

    protected $casts = [
        'order_receive_id' => 'int',
        'sent_at' => 'datetime',
    ];

    protected $fillable = [
        'order_receive_id',
        'channel',
        'recipient',
        'message',
        'status',
        'provider_response',
        'sent_at',
        'created_by',
    ];

    public function orderReceive()
    {
        return $this->belongsTo(OrderReceive::class);
    }
}
