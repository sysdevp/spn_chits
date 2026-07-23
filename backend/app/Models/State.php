<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
	use SoftDeletes;

	public function country_det()
	{
		return $this->belongsTo(Country::class,'country_id');
	}

    public function employee_det()
	{
		return $this->hasMany(Employee::class);
	}

	public function district_det()
	{
		return $this->hasMany(District::class);
	}

	public function customer_det()
	{
		return $this->hasMany(Customer::class);
	}

	public function head_office_det()
	{
		return $this->hasMany(HeadOffice::class);
	}

	public function business_agent_det()
	{
		return $this->hasMany(BusinessAgent::class);
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
