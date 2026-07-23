<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiddingDetail extends Model
{
    use SoftDeletes;

    public function tenant_det()
	{
		return $this->belongsTo(Tenant::class);
    }
    
    public function branch_det() 
	{
		return $this->belongsTo(Branch::class,'branch_id');
    }

    public function group_det()
	{
		return $this->belongsTo(Group::class,'group_id');
	}
    public function customer_det()
	{
		return $this->belongsTo(Customer::class,'customer_id');
    }

    public function enrollment_det()
	{
		return $this->belongsTo(ChitDetail::class,'enrollment_id');
	}

	public function payment_det()
	{
		return $this->hasMany(Payment::class,'bidding_id');
	}

	public function receipt_det()
    {
      return $this->hasMany(Receipt::class);
    }
}
