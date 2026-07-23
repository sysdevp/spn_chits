<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupBiddingDetail extends Model
{
  
  use SoftDeletes;

  protected $fillable = [
    'tenant_id','branch_id','group_id','installment_no','progress'
  ];

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
  

}
