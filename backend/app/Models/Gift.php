<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Gift extends Model
{
	use SoftDeletes;

	protected $fillable = [
        'gift_name', 'price', 'scheme_id', 'quantity'
    ];

  
}
