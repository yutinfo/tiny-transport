<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Amphure
 * 
 * @property int $id
 * @property string $code
 * @property string $name_th
 * @property string $name_en
 * @property int $province_id
 *
 * @package App\Models
 */
class Amphure extends Model
{
	protected $table = 'amphures';
	public $timestamps = false;

	protected $casts = [
		'province_id' => 'int'
	];

	protected $fillable = [
		'code',
		'name_th',
		'name_en',
		'province_id'
	];
}
