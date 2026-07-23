<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashIncome extends Model
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

	public function payment_type_det()
	{
		return $this->belongsTo(PaymentType::class,'payment_type_id');
	}
	
	public function bank_det()
	{
		return $this->belongsTo(BankDetail::class,'bank_id');
	}
}
