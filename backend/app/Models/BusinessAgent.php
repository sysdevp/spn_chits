<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessAgent extends Model
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

	public function state_det()
	{
		return $this->belongsTo(State::class,'state_id');
	}

	public function district_det()
	{
		return $this->belongsTo(District::class,'district_id');
	}

	public function city_det()
	{
		return $this->belongsTo(City::class,'city_id');
	}

	public function designation_det()
	{
		return $this->belongsTo(Designation::class,'designation_id');
	}

	public function role_det()
	{
		return $this->belongsTo(Role::class,'role_id');
	}

	public function bank_det()
	{
		return $this->belongsTo(BankDetail::class,'bank_id');
	}
}
