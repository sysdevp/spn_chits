<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class GiftIssue extends Model
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
  
   public function customer_det()
  {
    return $this->belongsTo(Customer::class,'customer_id');
  }

}