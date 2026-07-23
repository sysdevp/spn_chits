<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountExpense extends Model
{
	use SoftDeletes;
	
	protected $fillable = [
    'tenant_id',
    'branch_id',
    'entry_date',
    'voucher_no',
    'debit_account_head',
    'debit_amount',
    'credit_account_head',
    'credit_amount',
    'remarks',
    'debit_remarks',
    'status',
];
	
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
	
	public function account_head_det()
	{
		return $this->belongsTo(AccountHead::class,'credit_account_head');
	}
	public function accounts_group_det()
	{
		return $this->belongsTo(AccountGroup::class,'group_id');
	}
	
	public function debit_account_head_det()
	{
		return $this->belongsTo(AccountHead::class,'debit_account_head');
	}
	
	public function debitAccount()
{
    return $this->belongsTo(AccountHead::class, 'debit_account_head');
}

public function creditAccount()
{
    return $this->belongsTo(AccountHead::class, 'credit_account_head');
}
}
