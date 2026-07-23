<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
	use SoftDeletes;
	
    protected $fillable = [
        'tenant_id', 'head_office_id', 'branch_name','start_date','address_line_1','address_line_2','landmark','branch_code','state_id','district_id','city_id','pincode','branch_wise_penalty','bonus','email_id','phone_no','prize_subscriber_penalty','non_prize_subscriber_penalty','bonus_days','penalty_days','business_agent_target','branch_fd_total_months','remarks','agent_incentive_percent','agent_commission_percent','cgst_percent','sgst_percent','gst_percent'
    ];

    public function head_office_det()
	{
		return $this->belongsTo(HeadOffice::class,'head_office_id');
	}
	
	public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}

	public function state_det()
	{
		return $this->belongsTo(State::class,'state_id');
	}

	public function district_det()
	{
		return $this->belongsTo(District::class,'district_id');
	}

	public function city_det()
	{
		return $this->belongsTo(City::class,'city_id');
	}

	public function bank_det()
	{
		return $this->hasMany(BankDetail::class);
	}

	public function employee_det()
	{
		return $this->hasMany(Employee::class);
	}

	public function slab_penaulty_det()
	{
		return $this->hasMany(SlabPenalty::class);
	}

	public function slab_interest_det()
	{
		return $this->hasMany(SlabInterest::class);
	}

	public function slab_bonus_det()
	{
		return $this->hasMany(SlabBonus::class);
	}

	public function slab_due_det()
	{
		return $this->hasMany(SlabDue::class);
	}

	public function collection_area_det()
	{
		return $this->hasMany(CollectionArea::class);
	}

	public function role_det()
	{
		return $this->hasMany(Role::class);
	}

	public function customer_det()
	{
		return $this->hasMany(Customer::class);
	}

	public function petty_cash_expense_det()
	{
		return $this->hasMany(PettyCashExpense::class);
	}

	public function petty_cash_income_det()
	{
		return $this->hasMany(PettyCashIncome::class);
	}

	public function business_agent_det()
	{
		return $this->hasMany(BusinessAgent::class);
	}

	public function document_det()
	{
		return $this->hasMany(Document::class);
	}

	public function employee_attendance_det()
	{
		return $this->hasMany(EmployeeAttendance::class);
	}

	public function employee_payroll_det()
	{
		return $this->hasMany(EmployeePayroll::class);
	}

	public function lead_management_det()
	{
		return $this->hasMany(LeadManagement::class);
	}
	
	public function employee_salary_det()
	{
		return $this->hasMany(EmployeeSalary::class);
	}

	public function followup_det()
	{
		return $this->hasMany(Followup::class);
	}

	public function field_investigation_det()
	{
		return $this->hasMany(FieldInvestigation::class);
	}

	public function enrol_det()
    {
      return $this->hasMany(ChitDetail::class);
	}
	
	public function payment_det()
	{
		return $this->hasMany(Payment::class);
	}
	
	public function holiday_det()
	{
		return $this->hasMany(AuctionOnHoliday::class);
	}

	public function cash_settlement_det()
	{
		return $this->hasMany(CashSettlement::class);
	}
	
	public function receipt_det()
    {
      return $this->hasMany(Receipt::class);
    }

    public function user_det()
	{
		return $this->hasMany(User::class);
	}

	public function agent_commission_payment_det()
	{
		return $this->hasMany(AgentCommissionPayment::class);
	}

	public function commitment_chit_det()
	{
		return $this->hasMany(CommitmentAuction::class);
	}

	public function update_branch_det()
	{
		return $this->hasMany(UpdateBranch::class);
	}

	public function loan_detail_det()
	{
		return $this->hasMany(LoanDetail::class);
	}

	public function loan_receipt_det()
	{
		return $this->hasMany(LoanReceipt::class);
	}

	public function source_type_det()
	{
		return $this->hasMany(SourceType::class);
	}
}
