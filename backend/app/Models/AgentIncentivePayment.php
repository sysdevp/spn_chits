<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentIncentivePayment extends Model
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

	public function employee_agent_det() 
	{
		return $this->belongsTo(Employee::class,'employee_agent_id');
	}

	public function enrollment_det() 
	{
		return $this->belongsTo(ChitDetail::class,'enrollment_id');
	}

	public function customer_det() 
	{
		return $this->belongsTo(Customer::class,'customer_id');
	}

	public function group_det() 
	{
		return $this->belongsTo(Group::class,'group_id');
	}

	public function payment_type_det() 
	{
		return $this->belongsTo(PaymentType::class,'payment_type_id');
	}

	public function bank_name_det() 
	{
		return $this->belongsTo(BankDetail::class,'bank_name_id');
	}
}
