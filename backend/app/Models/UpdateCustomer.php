<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpdateCustomer extends Model
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
    
    public function state_det() 
	{
		return $this->belongsTo(State::class,'state_id');
	}
	
	public function district_det() 
	{
		return $this->belongsTo(District::class,'district_id');
	}
	
	public function city_det() 
	{
		return $this->belongsTo(City::class,'city_id');
    }
    
    public function communication_state_det() 
	{
		return $this->belongsTo(State::class,'state_id');
    }
    
    public function communication_district_det() 
	{
		return $this->belongsTo(District::class,'district_id');
	}
	
	public function communication_city_det() 
	{
		return $this->belongsTo(City::class,'city_id');
	}

	public function relationship_det() 
	{
		return $this->belongsTo(Relationship::class,'relationship_id');
	}

	public function customer_belongs_to_det() 
	{
		return $this->belongsTo(Customer::class,'customer_belongs_to');
	}
		
	public function document_det()
	{
		return $this->hasMany(Document::class);
	}

	public function followup_det()
	{
		return $this->hasMany(Followup::class);
	}

	public function field_investigation_det()
	{
		return $this->hasMany(FieldInvestigation::class);
	}

	public function enrol_det()
    {
      return $this->hasMany(ChitDetail::class);
    }
	
	public function receipt_det()
    {
      return $this->hasMany(Receipt::class);
    }

	public function lead_management_det() 
	{
		return $this->belongsTo(LeadManagement::class,'lead_management_id');
	}
	
	public function payment_det()
	{
		return $this->hasMany(Payment::class);
	}
	public function commitment_chit_det()
	{
		return $this->hasMany(CommitmentAuction::class);
	}
	public function guarantor_relation_det()
	{
		return $this->hasMany(GuarantorRelation::class);
	}

	public function customer_det()
	{
		return $this->belongsTo(Customer::class,'update_customer_id');
	}
}
