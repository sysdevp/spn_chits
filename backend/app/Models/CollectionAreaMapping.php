<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionAreaMapping extends Model
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

    public function employee_det()
    {
    	return $this->belongsTo(Employee::class,'employee_id');
    }

    public function collection_area_det()
    {
    	return $this->belongsTo(CollectionArea::class,'collection_area_id');
    }
}
