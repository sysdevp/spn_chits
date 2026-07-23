<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankDetail extends Model
{
    use SoftDeletes;
    
    protected $guarded = ['bank_code','status'];
    
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
		return $this->hasMany(Employee::class);
	}

	public function business_agent_det()
	{
		return $this->hasMany(BusinessAgent::class);
	}

	public function petty_cash_income_det()
	{
		return $this->hasMany(PettyCashIncome::class);
	}

	public function petty_cash_expense_det()
	{
		return $this->hasMany(PettyCashExpense::class);
	}

	public function payment_det()
	{
		return $this->hasMany(Payment::class);
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
