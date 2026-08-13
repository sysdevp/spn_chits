<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChitDetail extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'tenant_id','branch_id','customer_id','group_id','ticket_no','prized_status','eligibility_status','chit_intimation_status','chit_status','payment_type','collection_area_id','collection_pin','collection_agent_id','agent_type','introducer_name','introducer_id','date_introducer','customer_type','agent_incentive','pledge_status','pledge_enrl_id','discount_due','cancel_amount','return_amount','ticket_type','enrolment_type','remarks','bonus_percentage','penalty_percentage','interest_percentage','dividend_cancel'
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

	public function scheme_det()
  {
    return $this->belongsTo(Scheme::class,'scheme_id');
  }
  public function slab_due_det()
	{
		return $this->belongsTo(SlabDue::class,'slab_id');
	}

  public function customer_det()
  {
    return $this->belongsTo(Customer::class,'customer_id');
  }
  
  public function employee_agent_det()
  {
    return $this->belongsTo(Employee::class,'employee_agent_id');
  }
  
  public function collection_agent_det()
  {
    return $this->belongsTo(Employee::class,'collection_agent_id');
  }

	public function employee_agent_intro_det()
  {
    return $this->belongsTo(Employee::class,'introducer_id');
  }

 
   public function collection_area_det() 
  {
    return $this->belongsTo(CollectionArea::class,'collection_area_id');
  } 

  public function receipt_det()
  {
    return $this->hasMany(Receipt::class);
  }

  public function agent_commission_payment_det()
  {
	 return $this->hasMany(AgentCommissionPayment::class);
  }

  public function commitment_chit_det()
  {
   return $this->hasMany(CommitmentAuction::class);
  }

  public function get_unique_list($arr,$key) { 
    $key_array = [];
    $record_array = [];
    foreach($arr as $rec) { 
      $key_value = $rec[$key]; //return $key_value;
      if(!(in_array($key_value,$key_array))) {
        array_push($record_array,$rec);
        array_push($key_array,$key_value);
      }
    }
    return $record_array;
  }
  public function guarantor_relation_det()
	{
		return $this->hasMany(GuarantorRelation::class);
	}

  /**
   * Display label for commitment enrollment: "Slab Name / AR No".
   */
  public static function formatSlabWithArNo($slabName, $arNo = null): string
  {
    $slab = trim((string) ($slabName ?? ''));
    $ar = trim((string) ($arNo ?? ''));
    if ($slab !== '' && $ar !== '') {
      return $slab . ' / ' . $ar;
    }
    if ($slab !== '') {
      return $slab;
    }
    return $ar !== '' ? $ar : '';
  }

  /** Resolve slab_name + ar_no for this enrollment row. */
  public function slabLabelWithArNo(): string
  {
    $slab = '';
    if ($this->relationLoaded('slab_due_det') && $this->slab_due_det) {
      $slab = (string) ($this->slab_due_det->slab_name ?? '');
    } elseif (!empty($this->slab_id)) {
      $due = SlabDue::find($this->slab_id);
      $slab = $due ? (string) ($due->slab_name ?? '') : '';
    }
    return self::formatSlabWithArNo($slab, $this->ar_no ?? null);
  }
}
