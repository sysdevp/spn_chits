<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionArea extends Model
{
    use SoftDeletes;
	
    protected $fillable = [
        'tenant_id','id','branch_id','collection_area','area_pincode'
    ];
	
	public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}
	
	public function branch_det() 
	{
		return $this->belongsTo(Branch::class,'branch_id');
	}
}
