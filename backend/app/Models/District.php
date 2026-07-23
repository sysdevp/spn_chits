<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
	use SoftDeletes;
	
    public function employee_det()
	{
		return $this->hasMany(Employee::class);
	}

	public function state_det()
	{
		return $this->belongsTo(State::class,'state_id');
	}

	public function customer_det()
	{
		return $this->hasMany(Customer::class);
	}

	public function city_det()
	{
		return $this->hasMany(City::class);
	}

	public function business_agent_det()
	{
		return $this->hasMany(BusinessAgent::class);
	}

	public function head_office_det()
	{
		return $this->hasMany(HeadOffice::class);
	}
	
	public function lead_management_det()
	{
		return $this->hasMany(LeadManagement::class);
	}

	public function branch_det()
	{
		return $this->hasMany(Branch::class);
	}
}
