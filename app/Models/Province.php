<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Province
 * 
 * @property int $id
 * @property string $code
 * @property string $name_th
 * @property string $name_en
 * @property int $geography_id
 *
 * @package App\Models
 */
class Province extends Model
{
	protected $table = 'provinces';
	public $timestamps = false;

	protected $casts = [
		'geography_id' => 'int'
	];

	protected $fillable = [
		'code',
		'name_th',
		'name_en',
		'geography_id'
	];
}
