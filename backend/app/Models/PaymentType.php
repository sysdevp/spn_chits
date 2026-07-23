<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'payment_name', 'remarks'
    ];

    public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}

	public function petty_cash_income_det()
	{
		return $this->hasMany(PettyCashIncome::class);
	}
	
	public function petty_cash_expense_det()
	{
		return $this->hasMany(PettyCashExpense::class);
	}

	public function receipt_det()
	{
		return $this->hasMany(Receipt::class);
	}

	public function agent_commission_payment_det()
  	{
    	return $this->hasMany(AgentCommissionPayment::class);
  	}

  	public function loan_detail_det()
	{
		return $this->hasMany(LoanDetail::class);
	}

	public function loan_receipt_det()
	{
		return $this->hasMany(LoanReceipt::class);
	}
}
