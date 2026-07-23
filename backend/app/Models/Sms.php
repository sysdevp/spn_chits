<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Sms extends Model
{
	use SoftDeletes;

	
	
	public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}
	
	public function branch_det() 
	{
		return $this->belongsTo(Branch::class,'branch_id');
	}

}
