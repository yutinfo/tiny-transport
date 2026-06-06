<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderReceife
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $parcel_name
 * @property string|null $parcel_description
 * @property string|null $receive_name
 * @property string|null $receive_mobile
 * @property string|null $receive_address
 * @property int $geographie_id
 * @property int $province_id
 * @property int $amphures_id
 * @property int $district_id
 * @property string|null $geographie_name
 * @property string|null $province_name
 * @property string|null $amphures_name
 * @property string|null $district_name
 * @property string|null $zip_code
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class OrderReceive extends Model
{
    protected $table = 'order_receives';

    protected $casts = [
        'order_id' => 'int',
        'province_id' => 'int',
        'amphures_id' => 'int',
        'district_id' => 'int'
    ];

    protected $fillable = [
        "order_id",
        "parcel_code",
        "parcel_description",
        "receive_name",
        "receive_address",
        "receive_mobile",
        "province_id",
        "amphures_id",
        "district_id",
        "province_name",
        "amphures_name",
        "district_name",
        "zip_code",
        "parcel_pickup_type",
        "payment_type",
        "delivery_status",
        "payment_status",
        "parcel_pice",
        "created_by",
        "updated_by",
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function tripItems()
    {
        return $this->hasMany(TripItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ParcelStatusLog::class);
    }
}
