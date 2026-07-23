<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
	use SoftDeletes;
	
	public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}

    public function employee_det()
	{
		return $this->hasMany(Employee::class);
	}

	public function business_agent_det()
	{
		return $this->hasMany(BusinessAgent::class);
	}
}
