<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scheme extends Model
{
	use SoftDeletes;

    protected $fillable = [
       'tenant_id', 'chit_value', 'no_of_months', 'members', 'register_fees', 'enrollment_fees', 'chit_letter', 'short_term', 'monthly_due', 'branch_id'
    ];
	
    public function slab_penaulty_det()
	{
		return $this->hasMany(SlabPenalty::class);
	}

	public function slab_interest_det()
	{
		return $this->hasMany(SlabInterest::class);
	}

	public function slab_bonus_det()
	{
		return $this->hasMany(SlabBonus::class);
	}

	public function slab_due_det()
	{
		return $this->hasMany(SlabDue::class);
	}

	public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}
	
	public function branch_det() 
	{
		return $this->belongsTo(Branch::class,'branch_id');
	}

	public function receipt_det()
    {
      return $this->hasMany(Receipt::class);
    }
    
	public function enrol_det()
    {
      return $this->hasMany(ChitDetail::class);
    }
}
