<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageLog extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'tenant_id','branch_id','group_id','installment_no'
  ];

 
 
}
