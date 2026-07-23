<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuiteFilling extends Model
{
    use SoftDeletes;

    public function branch_det() 
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function cus_det()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function getChitDetails()
    {
        return $this->belongsTo(ChitDetail::class,'enroll_id');
    }
    public function group_details()
    {
        return $this->belongsTo(Group::class,'group_id');
    }
    public function lawyer_details()
    {
        return $this->belongsTo(Lawyer::class,'lawyer_id');
    }
    public function case_detailsvalue()
    {
        return $this->belongsTo(CaseName::class,'case_stage');
    }

}
