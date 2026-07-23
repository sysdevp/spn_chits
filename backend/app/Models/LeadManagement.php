<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadManagement extends Model
{
    use SoftDeletes;

protected $table = 'lead_managements';
    public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}

	public function branch_det()
	{
		return $this->belongsTo(Branch::class,'branch_id');
	}

	public function employee_det()
	{
		return $this->belongsTo(Employee::class,'employee_id');
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

	public function followup_det()
	{
		return $this->hasMany(Followup::class);
	}

	public function followupstatustype_det()
	{
		return $this->belongsTo(FollowupStatusType::class,'followup_status_type_id');
	}

	public function source_type_det()
	{
		return $this->belongsTo(SourceType::class,'source_type_id');
	}
}
