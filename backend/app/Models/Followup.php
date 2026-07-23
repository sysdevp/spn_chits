<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Followup extends Model
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

	public function lead_management_det()
	{
		return $this->belongsTo(LeadManagement::class,'lead_management_id');
	}

	public function employee_det()
	{
		return $this->belongsTo(Employee::class,'employee_id');
	}

	public function followup_status_type_det()
	{
		return $this->belongsTo(FollowupStatusType::class,'followup_status_type_id');
	}
}
