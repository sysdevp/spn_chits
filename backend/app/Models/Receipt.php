<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use SoftDeletes;
	
	protected $fillable = [
    'tenant_id',
    'branch_id',
    'other_branch',
    'customer_id',
    'enrollment_id',
    'group_id',
    'ticket_no',
    'employee_id',
    'employee_branch_id',
    'receipt_no',
    'offline_receipt_no',
    'adjust_id',
    'amount',
    'receipt_date',
    'type_of_collection',
    'payment_type_id',
    'debit_to',
    'cheque_no',
    'cheque_date',
    'cheque_return_amount',
    'cheque_clear_return_date',
    'cheque_debit_to',
    'bank_name_id',
    'branch_name',
    'transaction_no',
    'transaction_date',
    'transaction_bill',
    'transaction_book',
    'neft_adjust_id',
    'receipt_mode',
    'receipt_time',
    'accounts_date',
    'printed',
    'auction_id',
    'installment_no',
    'pending_days',
    'penalty',
    'bonus',
    'discount',
    'received_amount',
    'cancel_dividend_amount',
    'advance_available',
    'remarks',
    'latitude',
    'longitude',
    'location_name',
    'created_by',
    'updated_by',
    'deleted_by',
    'deletion_remark',
    'status',
    'receipt_passbook_mode',
    'submit_to_bank',
    'balance_advance_amount',
];

    public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
    }

    public function branch_det() 
	{
		return $this->belongsTo(Branch::class,'branch_id');
    }
	
	public function group_det() 
	{
		return $this->belongsTo(Group::class,'group_id');
    }

    public function payment_type_det() 
	{
		return $this->belongsTo(PaymentType::class,'payment_type_id');
    }

    public function customer_det() 
	{
		return $this->belongsTo(Customer::class,'customer_id');
    }

	public function other_charges_det() 
	{
		return $this->belongsTo(ChargeType::class,'charges_type_id');
    }

	public function enrollment_det() 
	{
		return $this->belongsTo(ChitDetail::class,'enrollment_id');
    }

	public function scheme_det() 
	{
		return $this->belongsTo(Scheme::class,'scheme_id');
    }

	public function employee_det() 
	{
		return $this->belongsTo(Employee::class,'employee_id');
    }

    public function collected_branch_det() 
	{
		return $this->belongsTo(Branch::class,'other_branch');
	}

    public function customer_bank_det() 
	{
		return $this->belongsTo(BankDetail::class,'bank_name_id');
    }
    
    public function cheque_debit_to_info() 
	{
		return $this->belongsTo(BankDetail::class,'cheque_debit_to');
    }

	
}
