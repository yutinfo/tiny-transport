<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Geography
 * 
 * @property int $id
 * @property string $name
 *
 * @package App\Models
 */
class Geography extends Model
{
	protected $table = 'geographies';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];
}
