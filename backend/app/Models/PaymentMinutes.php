<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMinutes extends Model
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
	
	public function group_det() 
	{
		return $this->belongsTo(Group::class,'group_id');
	}
	
	public function enrol_det()
  {
    return $this->belongsTo(ChitDetail::class,'enrollment_id');
  }

	public function customer_det()
  {
    return $this->belongsTo(Customer::class,'customer_id');
  }

  public function employee_det()
  {
    return $this->belongsTo(Employee::class,'employee_id');
  }

  public function slab_det()
  {
    return $this->belongsTo(SlabDue::class,'slab_id');
  }

  public function payment_type_det()
  {
    return $this->belongsTo(PaymentType::class,'payment_type_id');
  }

  public function auction_det()
  {
    return $this->belongsTo(MinutesOfAuction::class,'bidding_id');
  }
	
  public function bank_det()
  {
    return $this->belongsTo(BankDetail::class,'bank_id');
  }
}
