<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiddingCommissionDetail extends Model
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

   
    public function customer_det()
	{
		return $this->belongsTo(Customer::class,'customer_id');
    }

    public function enrollment_det()
	{
		return $this->belongsTo(ChitDetail::class,'enrollment_id');
	}

	
}
