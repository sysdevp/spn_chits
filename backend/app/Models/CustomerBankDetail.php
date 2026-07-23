<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerBankDetail extends Model
{
    use SoftDeletes;

    public function receipt_det()
	{
		return $this->hasMany(Receipt::class);
	}
}
