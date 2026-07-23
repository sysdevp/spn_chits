<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChitPledge extends Model
{
  use SoftDeletes;

  // protected $fillable = [
  //   'tenant_id','branch_id','customer_id','enrollment_id','pledge_customer_id','pledge_enrollment_id'
  // ];

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
  
   public function enrollment_det()
  {
    return $this->belongsTo(ChitDetail::class,'enrollment_id');
  }
  
  public function pledge_customer_det()
  {
    return $this->belongsTo(Customer::class,'pledge_customer_id');
  }
  
   public function pledge_enrollment_det()
  {
    return $this->belongsTo(ChitDetail::class,'pledge_enrollment_id');
  }
  
 
}
