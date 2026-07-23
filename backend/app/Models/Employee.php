<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
	use SoftDeletes;
	
    // protected $fillable = [
        // 'tenant_id','id','branch_id','saluation','first_name','last_name','gender','dob','photo','collection_agent','phone_no','mobile_no','adhar_card','driving_license','pan_no','ration_card_no','father_name','spouse_name','door_no','building_name','address_line_1','address_line_2','land_mark','state_id','district_id','city_id','pincode','communication_door_no','communication_building_name','communication_address_line_1','communication_address_line_2','communication_land_mark','designation_id','role_id','esi','pf','blood_group','qualification','previous_company_details','joining_date','experience','verification_on_call_by','last_month_salary','holder_name','bank_id','account_no','ifsc_code','target','salary','collection_area','commission','incentive'
    // ];
	
	public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}
	
	public function branch_det() 
	{
		return $this->belongsTo(Branch::class,'branch_id');
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

	public function communication_state_det() 
	{
		return $this->belongsTo(State::class,'communication_state_id');
	}
	
	public function communication_district_det() 
	{
		return $this->belongsTo(District::class,'communication_district_id');
	}
	
	public function communication_city_det() 
	{
		return $this->belongsTo(City::class,'communication_city_id');
	}
	
	public function designation_det() 
	{
		return $this->belongsTo(Designation::class,'designation_id');
	}
	
	public function role_det() 
	{
		return $this->belongsTo(Role::class,'role_id');
	}
	
	public function bank_det() 
	{
		return $this->belongsTo(BankDetail::class,'bank_id');
	}

	public function petty_cash_expense_det()
	{
		return $this->hasMany(PettyCashExpense::class);
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

	public function user_det()
	{
		return $this->hasMany(Employee::class);
	}

	public function cash_settlement_det()
	{
		return $this->hasMany(CashSettlement::class);
	}
	
	public function collection_area_det() 
	{
		return $this->belongsTo(CollectionArea::class,'collection_area_id');
	}
	
	public function collection_area_mapping_det()
	{
		return $this->hasMany(CollectionAreaMapping::class);
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
