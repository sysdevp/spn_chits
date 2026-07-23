<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommitmentAuction extends Model
{
       use SoftDeletes;

  protected $fillable = [
       'tenant_id','branch_id','customer_id','enrollment_id','installment_no','auction_date','due_amount'
    ];

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



}
