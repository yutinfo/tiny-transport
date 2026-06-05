<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class District
 * 
 * @property string $id
 * @property int $zip_code
 * @property string $name_th
 * @property string $name_en
 * @property int $amphure_id
 *
 * @package App\Models
 */
class District extends Model
{
	protected $table = 'districts';
	public $timestamps = false;

	protected $casts = [
		'zip_code' => 'int',
		'amphure_id' => 'int'
	];

	protected $fillable = [
		'zip_code',
		'name_th',
		'name_en',
		'amphure_id'
	];
}
