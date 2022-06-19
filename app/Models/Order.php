<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 *
 * @property int $id
 * @property string $code
 * @property string|null $customer_name
 * @property string|null $customer_mobile
 * @property string|null $customer_address
 * @property string|null $geographie_name
 * @property string|null $province_name
 * @property string|null $amphures_name
 * @property string|null $district_name
 * @property string|null $zip_code
 * @property string|null $car_id
 * @property string|null $driver_name
 * @property string|null $driver_mobile
 * @property int $parcel_amount
 * @property float $parcel_total
 * @property string $parcel_pickup_type
 * @property string $payment_type
 * @property string $delivery_status
 * @property string $payment_status
 * @property string $order_status
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Order extends Model
{
	protected $table = 'orders';

	protected $casts = [
		'parcel_amount' => 'int',
		'parcel_total' => 'float',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'

	];


	protected $fillable = [
		'code',
		'customer_name',
		'customer_mobile',
		'customer_address',
		'province_name',
		'amphures_name',
		'district_name',
		'zip_code',
		'car_id',
		'driver_name',
		'driver_mobile',
		'parcel_amount',
		'parcel_total',
		'parcel_pickup_type',
		'payment_type',
		'delivery_status',
		'payment_status',
		'order_status',
		'created_by',
		'updated_by'
	];

    public function receivers()
    {
        return $this->hasMany(OrderReceive::class);
    }
}
