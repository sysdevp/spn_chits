<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuarantorRelation extends Model
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
	public function customer_det() 
	{
		return $this->belongsTo(Customer::class,'customer_id');
    }
    public function enrol_det()
    {
      return $this->belongsTo(ChitDetail::class,'enrollment_id');
    }
	public function guarantor_det() 
	{
		return $this->belongsTo(Customer::class,'guarantor_id');
    }
}
