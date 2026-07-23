<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Document extends Model
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
    
    public function customer_det() 
	{
		return $this->belongsTo(Customer::class,'customer_id');
	}
	
	public function document_det()
	{
		return $this->hasMany(DocumentDetail::class);
	}
	
	public function asset_det()
	{
		return $this->hasMany(AssetDetail::class);
	}

	public function enrollment_det() 
	{
		return $this->belongsTo(ChitDetail::class,'enrollment_id');
	}
	
	public function collected_emp_det() 
	{
		return $this->belongsTo(Employee::class,'collected_employee_id');
	}

	public function guarantor_det() 
	{
		return $this->belongsTo(Customer::class,'customer_id');
	}
	public function nominee_det() 
	{
		return $this->belongsTo(Nominee::class,'nominee_id');
	}
}
