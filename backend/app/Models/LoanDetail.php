<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanDetail extends Model
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

	public function employee_det() 
	{
		return $this->belongsTo(Employee::class,'collect_employee_id');
	}
	
	public function customer_det() 
	{
		return $this->belongsTo(Customer::class,'customer_id');
	}

	public function payment_type_det() 
	{
		return $this->belongsTo(PaymentType::class,'payment_type_id');
	}

	public function bank_name_det() 
	{
		return $this->belongsTo(BankDetail::class,'bank_name_id');
	}

	public function loan_receipt_det() 
	{
		return $this->hasMany(LoanReceipt::class);
	}

}
