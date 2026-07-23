<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlabPayment extends Model
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

	public function scheme_det()
	{
		return $this->belongsTo(Scheme::class,'schemes_id');
	}
	
	public function slab_due_det()
	{
		return $this->belongsTo(SlabDue::class,'slab_id');
	}
}
