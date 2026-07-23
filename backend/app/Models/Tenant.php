<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
	use SoftDeletes;

    protected $fillable = [
        'chit_fund_name', 'company_logo', 'receipts_logo','header_name','admin_user_id',
        'password','gst_no','authorized_person_name','phone_no'
    ];

    public function bank_det()
	{
		return $this->hasMany(BankDetail::class);
	}

	public function employee_det()
	{
		return $this->hasMany(Employee::class);
	}

	public function payment_type_det()
	{
		return $this->hasMany(PaymentType::class);
	}

	public function petty_cash_expense_type_det()
	{
		return $this->hasMany(PettyCashExpenseType::class);
	}

	public function charge_type_det()
	{
		return $this->hasMany(ChargeType::class);
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

	public function branch_det()
	{
		return $this->hasMany(Branch::class);
	}
	
	public function group_det()
	{
		return $this->hasMany(Group::class);
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

	public function head_office_det()
	{
		return $this->hasMany(HeadOffice::class);
	}

	public function designation_det()
	{
		return $this->hasMany(Designation::class);
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

	public function bidding_detail_det()
	{
		return $this->hasMany(BiddingDetail::class);
	}

	public function user_det()
	{
		return $this->hasMany(User::class);
	}

	public function payment_det()
	{
		return $this->hasMany(Payment::class);
	}

	public function device_det()
	{
		return $this->hasMany(Device::class);
	}
	
	public function holiday_det()
	{
		return $this->hasMany(AuctionOnHoliday::class);
	}

	public function cash_settlement_det()
	{
		return $this->hasMany(CashSettlement::class);
	}

	public function agent_commission_payment_det()
	{
		return $this->hasMany(AgentCommissionPayment::class);
	}

	public function dynamic_menu_det()
	{
		return $this->hasMany(DynamicMenu::class);
	}
	public function commitment_chit_det()
	{
		return $this->hasMany(CommitmentAuction::class);
	}

	public function collection_remarks_det()
	{
		return $this->hasMany(CollectionRemark::class);
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
