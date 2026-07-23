<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hierarchy extends Model
{
    use SoftDeletes;

	public function hier_det()
	{
		return $this->hasMany(Hierarchy::class);
	}
	
    public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}

	public function parent_det()
	{
		return $this->belongsTo(Hierarchy::class,'under_id');
	}

	public function child_det()
	{
		return $this->belongsTo(Hierarchy::class,'under_id');
	}
}
