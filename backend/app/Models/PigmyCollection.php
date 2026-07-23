<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PigmyCollection extends Model
{
   Use SoftDeletes;
   
   public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
    }
    public function branch_det() 
	{
		return $this->belongsTo(Branch::class,'branch_id');
    }	
	public function group_det() 
	{
		return $this->belongsTo(Group::class,'group_id');
    }
    public function payment_type_det() 
	{
		return $this->belongsTo(PaymentType::class,'payment_type_id');
    } 
	public function other_charges_det() 
	{
		return $this->belongsTo(ChargeType::class,'charge_type_id');
    }
    public function customer_det() 
	{
		return $this->belongsTo(Customer::class,'customer_id');
    }
	public function bank_det() 
	{
		return $this->belongsTo(BankDetail::class,'bank_id');
    }
	public function employee_det() 
	{
		return $this->belongsTo(Employee::class,'employee_id');
    }
    public function cheque_debit_to_info() 
	{
		return $this->belongsTo(BankDetail::class,'cheque_debit_to');
    }
}
