<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout'); 

Route::group(['middleware' => 'auth.jwt'], function () {
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('signup', 'AuthController@signup');
    Route::post('sendPasswordResetLink','ResetPasswordController@SendEmail');
});    
    //Banks
    Route::post('store-bank','BankDetailController@store_bank');
    Route::post('update-bank','BankDetailController@update_bank');
    Route::post('get-banks','BankDetailController@get_banks');
    Route::post('list-banks','BankDetailController@list_banks');
    Route::post('delete-bank','BankDetailController@delete_bank');
    Route::post('update-bank-status','BankDetailController@update_status');

    //Payment Types
    Route::post('store-payment-type','PaymentTypeController@store_payment_type');
    Route::post('update-payment-type','PaymentTypeController@update_payment_type');
    Route::post('get-payment-types','PaymentTypeController@get_payment_types');
    Route::post('list-payment-types','PaymentTypeController@list_payment_types');
    Route::post('delete-payment-type','PaymentTypeController@delete_payment_type');

    //Tenants
    Route::post('store-tenant','TenantController@store_tenant');
    Route::post('update-tenant','TenantController@update_tenant');
    Route::post('get-tenants','TenantController@get_tenants');
    Route::post('list-tenants','TenantController@list_tenants');
    Route::post('delete-tenant','TenantController@delete_tenant');

	//Cities
    Route::post('store-city','CityController@store_city');
    Route::post('update-city','CityController@update_city');
    Route::post('get-cities','CityController@get_cities');
    Route::post('list-cities','CityController@list_cities');
    Route::post('delete-city','CityController@delete_city');
    Route::post('update-city-status','CityController@update_status');
    //States
    Route::post('store-state','StateController@store_state');
    Route::post('update-state','StateController@update_state');
    Route::post('get-states','StateController@get_states');
    Route::post('list-states','StateController@list_states');
    Route::post('delete-state','StateController@delete_state');
	//Head Offices
    Route::post('store-head-office','HeadOfficeController@store_headoffice');
    Route::post('update-head-office','HeadOfficeController@update_headoffice');
    Route::post('get-head-offices','HeadOfficeController@get_headoffice');
    Route::post('list-head-offices','HeadOfficeController@list_headoffice');
    Route::post('delete-head-office','HeadOfficeController@delete_headoffice');

    //Roles
    Route::post('store-role','RoleController@store_role');
    Route::post('update-role','RoleController@update_role');
    Route::post('get-roles','RoleController@get_roles');
    Route::post('list-roles','RoleController@list_roles');
    Route::post('delete-role','RoleController@delete_role');
    Route::post('update-role-status','RoleController@update_status');

     //Employees
    Route::post('store-employee','EmployeeController@store_employee');
    Route::post('update-employee','EmployeeController@update_employee');
    Route::post('get-employees','EmployeeController@get_employees');
    Route::post('list-employees','EmployeeController@list_employees');
    Route::post('delete-employee','EmployeeController@delete_employee');
    Route::post('update-employee-status','EmployeeController@update_status');
    Route::post('employee-file-upload','EmployeeController@employee_file_upload');

    //Districts
    Route::post('store-district','DistrictController@store_district');
    Route::post('update-district','DistrictController@update_district');
    Route::post('get-districts','DistrictController@get_districts');
    Route::post('list-districts','DistrictController@list_districts');
    Route::post('delete-district','DistrictController@delete_district');

    //Petty Cash Expense Types
    Route::post('store-petty-cash-expense-type','PettyCashExpenseTypeController@store_expense_type');
    Route::post('update-petty-cash-expense-type','PettyCashExpenseTypeController@update_expense_type');
    Route::post('get-petty-cash-expense-types','PettyCashExpenseTypeController@get_expense_types');
    Route::post('list-petty-cash-expense-types','PettyCashExpenseTypeController@list_expense_types');
    Route::post('delete-petty-cash-expense-type','PettyCashExpenseTypeController@delete_expense_type');

    //Charge Types
    Route::post('store-charge-type','ChargeTypeController@store_charge_type');
    Route::post('update-charge-type','ChargeTypeController@update_charge_type');
    Route::post('get-charge-types','ChargeTypeController@get_charge_types');
    Route::post('list-charge-types','ChargeTypeController@list_charge_types');
    Route::post('delete-charge-type','ChargeTypeController@delete_charge_type');
    Route::post('update-charge-type-status','ChargeTypeController@update_status');

    //Slab Penaulties
    Route::post('store-slab-penalty','SlabPenaltyController@store_slab_penalty');
    Route::post('update-slab-penalty','SlabPenaltyController@update_slab_penalty');
    Route::post('get-slab-penalties','SlabPenaltyController@get_slab_penalties');
    Route::post('list-slab-penalties','SlabPenaltyController@list_slab_penalties');
    Route::post('delete-slab-penalty','SlabPenaltyController@delete_slab_penalty');
    Route::post('update-slab-penalty-status','SlabPenaltyController@update_status');

    //Slab Interests
    Route::post('store-slab-interest','SlabInterestController@store_slab_interest');
    Route::post('update-slab-interest','SlabInterestController@update_slab_interest');
    Route::post('get-slab-interests','SlabInterestController@get_slab_interests');
    Route::post('list-slab-interests','SlabInterestController@list_slab_interests');
    Route::post('delete-slab-interest','SlabInterestController@delete_slab_interest');
    Route::post('update-slab-interest-status','SlabInterestController@update_status');

    //Slab Bonuses
    Route::post('store-slab-bonus','SlabBonusController@store_slab_bonus');
    Route::post('update-slab-bonus','SlabBonusController@update_slab_bonus');
    Route::post('get-slab-bonuses','SlabBonusController@get_slab_bonuses');
    Route::post('list-slab-bonuses','SlabBonusController@list_slab_bonuses');
    Route::post('delete-slab-bonus','SlabBonusController@delete_slab_bonus');
    Route::post('update-slab-bonus-status','SlabBonusController@update_status');

    //Slab Dues
    Route::post('store-slab-due','SlabDueController@store_slab_due');
    Route::post('update-slab-due','SlabDueController@update_slab_due');
    Route::post('get-slab-dues','SlabDueController@get_slab_dues');
    Route::post('list-slab-dues','SlabDueController@list_slab_dues');
    Route::post('delete-slab-due','SlabDueController@delete_slab_due');
    Route::post('update-slab-due-status','SlabDueController@update_status');

    //Collection Areas
    Route::post('store-collection-area','CollectionAreaController@store_collection_area');
    Route::post('update-collection-area','CollectionAreaController@update_collection_area');
    Route::post('get-collection-areas','CollectionAreaController@get_collection_areas');
    Route::post('list-collection-areas','CollectionAreaController@list_collection_areas');
    Route::post('delete-collection-area','CollectionAreaController@delete_collection_area');
    Route::post('update-collection-area-status','CollectionAreaController@update_status');
	
	 //Relationships
    Route::post('store-relationship','RelationshipController@store_relationship');
    Route::post('update-relationship','RelationshipController@update_relationship');
    Route::post('get-relationships','RelationshipController@get_relationships');
    Route::post('list-relationships','RelationshipController@list_relationships');
    Route::post('delete-relationship','RelationshipController@delete_relationship');
    Route::post('update-relationship-status','RelationshipController@update_status');
	
	//schemes
    Route::post('store-scheme','SchemeController@store_scheme');
    Route::post('update-scheme','SchemeController@update_scheme');
    Route::post('get-schemes','SchemeController@get_schemes');
    Route::post('list-schemes','SchemeController@list_schemes');
    Route::post('delete-scheme','SchemeController@delete_scheme');
    Route::post('update-scheme-status','SchemeController@update_status');
	
	//Branches
    Route::post('store-branch','BranchController@store_branch');
    Route::post('update-branch','BranchController@update_branch');
    Route::post('get-branches','BranchController@get_branches');
    Route::post('list-branches','BranchController@list_branches');
    Route::post('delete-branch','BranchController@delete_branch');
    Route::post('update-branch-status','BranchController@update_status');
    
	 //Countries
    Route::post('get-countries','CountryController@get_countries');
    Route::post('list-countries','CountryController@list_countries');

	 //Designations
    Route::post('store-designation','DesignationController@store_designation');
    Route::post('update-designation','DesignationController@update_designation');
    Route::post('get-designations','DesignationController@get_designation');
    Route::post('list-designations','DesignationController@list_designations');
    Route::post('delete-designation','DesignationController@delete_designations');
    Route::post('update-designation-status','DesignationController@update_status');

	//Business Agents
    Route::post('store-business-agent','BusinessAgentController@store_business_agent');
    Route::post('update-business-agent','BusinessAgentController@update_business_agent');
    Route::post('get-business-agents','BusinessAgentController@get_business_agent');
    Route::post('list-business-agents','BusinessAgentController@list_business_agent');
    Route::post('delete-business-agent','BusinessAgentController@delete_business_agent');
	
	//Groups
    Route::post('store-group','GroupController@store_group');
    Route::post('update-group','GroupController@update_group');
    Route::post('get-groups','GroupController@get_groups');
    Route::post('list-groups','GroupController@list_groups');
    Route::post('delete-group','GroupController@delete_group');

     //Customers
     Route::post('store-customer','CustomerController@store_customer');
     Route::post('update-customer','CustomerController@update_customer');
     Route::post('get-customers','CustomerController@get_customers');
     Route::post('list-customers','CustomerController@list_customers');
     Route::post('delete-customer','CustomerController@delete_customer');
     Route::post('file-upload','CustomerController@fileUpload');

	  //Enrollments
     Route::post('store-customer-enrollment','ChitDetailController@store_enrol');
     Route::post('update-customer-enrollment','ChitDetailController@update_enrol');
     Route::post('get-customer-enrollments','ChitDetailController@get_enrolls');
     Route::post('list-customer-enrollments','ChitDetailController@list_enrolls');
     Route::post('delete-customer-enrollment','ChitDetailController@delete_enrol');
     Route::post('cancel-customer-enrollment','ChitDetailController@cancel_enrol');
     Route::post('update-customer-enrollment-status','ChitDetailController@update_status');
     Route::post('store-dummy-customer-enrollment','ChitDetailController@store_dummy_subscriber');
     Route::post('get-unassigned-tickets','ChitDetailController@get_unassigned_tickets');
     Route::post('get-enroll-ticket-list','ChitDetailController@get_enroll_ticket_list');
     Route::post('get-unrolled-customers','ChitDetailController@get_unrolled_customers');
     Route::post('get-unrolled-customer-list','ChitDetailController@get_unrolled_customer_list');
     


      //Petty cash Expenses
    Route::post('store-petty-cash-expense','PettyCashExpenseController@store_petty_cash_expense');
    Route::post('update-petty-cash-expense','PettyCashExpenseController@update_petty_cash_expense');
    Route::post('get-petty-cash-expenses','PettyCashExpenseController@get_petty_cash_expenses');
    Route::post('list-petty-cash-expenses','PettyCashExpenseController@list_petty_cash_expenses');
    Route::post('delete-petty-cash-expense','PettyCashExpenseController@delete_petty_cash_expense');

    //Petty cash Incomes
    Route::post('store-petty-cash-income','PettyCashIncomeController@store_petty_cash_income');
    Route::post('update-petty-cash-income','PettyCashIncomeController@update_petty_cash_income');
    Route::post('get-petty-cash-incomes','PettyCashIncomeController@get_petty_cash_incomes');
    Route::post('list-petty-cash-incomes','PettyCashIncomeController@list_petty_cash_incomes');
    Route::post('delete-petty-cash-income','PettyCashIncomeController@delete_petty_cash_income');

     //Documents
     Route::post('store-document','DocumentController@store_document');
     Route::post('update-document','DocumentController@update_document');
     Route::post('get-documents','DocumentController@get_documents');
     Route::post('list-documents','DocumentController@list_documents');
     Route::post('delete-document','DocumentController@delete_document');

     //Employee Attendances
     Route::post('store-employee-attendance','EmployeeAttendanceController@store_employee_attendance');
     Route::post('update-employee-attendance','EmployeeAttendanceController@update_employee_attendance');
     Route::post('get-employee-attendances','EmployeeAttendanceController@get_employee_attendances');
     Route::post('list-employee-attendances','EmployeeAttendanceController@list_employee_attendances');
     Route::post('delete-employee-attendance','EmployeeAttendanceController@delete_employee_attendance');


     //Employee Payrolls
     Route::post('store-employee-payroll','EmployeePayrollController@store_employee_payroll');
     Route::post('update-employee-payroll','EmployeePayrollController@update_employee_payroll');
     Route::post('get-employee-payrolls','EmployeePayrollController@get_employee_payrolls');
     Route::post('list-employee-payrolls','EmployeePayrollController@list_employee_payrolls');
     Route::post('delete-employee-payroll','EmployeePayrollController@delete_employee_payroll');

     //Lead Managements
     Route::post('store-lead-management','LeadManagementController@store_lead_management');
     Route::post('update-lead-management','LeadManagementController@update_lead_management');
     Route::post('get-lead-managements','LeadManagementController@get_lead_managements');
     Route::post('list-lead-managements','LeadManagementController@list_lead_managements');
     Route::post('delete-lead-management','LeadManagementController@delete_lead_management');

     //Employee Salaries
     Route::post('store-employee-salary','EmployeeSalaryController@store_employee_salary');
     Route::post('update-employee-salary','EmployeeSalaryController@update_employee_salary');
     Route::post('get-employee-salaries','EmployeeSalaryController@get_employee_salaries');
     Route::post('list-employee-salaries','EmployeeSalaryController@list_employee_salaries');
     Route::post('delete-employee-salary','EmployeeSalaryController@delete_employee_salary');

    //Followups
     Route::post('store-followup','FollowupController@store_followup');
     Route::post('update-followup','FollowupController@update_followup');
     Route::post('get-followups','FollowupController@get_followups');
     Route::post('list-followups','FollowupController@list_followups');
     Route::post('delete-followup','FollowupController@delete_followup');

     //Field Investigations
     Route::post('store-field-investigation','FieldInvestigationController@store_field_investigation');
     Route::post('update-field-investigation','FieldInvestigationController@update_field_investigation');
     Route::post('get-field-investigations','FieldInvestigationController@get_field_investigations');
     Route::post('list-field-investigations','FieldInvestigationController@list_field_investigations');
     Route::post('delete-field-investigation','FieldInvestigationController@delete_field_investigation');

    //Auction Details
    Route::post('store-auction','BiddingDetailController@store_auction');
    Route::post('update-auction','BiddingDetailController@update_auction');
    Route::post('get-auctions','BiddingDetailController@get_auctions');
    Route::post('list-auctions','BiddingDetailController@list_auctions');
    Route::post('delete-auction','BiddingDetailController@delete_auction');
    Route::post('get-bidding-group-details','BiddingDetailController@get_bidding_group_details');

    //Hierarchies
    Route::post('store-hierarchy','HierarchyController@store_hierarchy');
    Route::post('update-hierarchy','HierarchyController@update_hierarchy');
    Route::post('get-hierarchies','HierarchyController@get_hierarchies');
    Route::post('list-hierarchies','HierarchyController@list_hierarchies');
    Route::post('delete-hierarchy','HierarchyController@delete_hierarchy');
    Route::post('get-under-list','HierarchyController@get_under_list');

     //Receipts
     Route::post('store-receipt','ReceiptController@store_receipt');
     Route::post('update-receipt','ReceiptController@update_receipt');
     Route::post('get-receipts','ReceiptController@get_receipts');
     Route::post('list-receipts','ReceiptController@list_receipts');
     Route::post('delete-receipt','ReceiptController@delete_receipt');
     Route::post('cheque-return','ReceiptController@cheque_return');
     Route::post('cheque-cleared','ReceiptController@cheque_cleared'); 

     //Payments
    Route::post('store-payment','PaymentController@store_payment');
    Route::post('update-payment','PaymentController@update_payment');
    Route::post('get-payments','PaymentController@get_payments');
    Route::post('list-payments','PaymentController@list_payments');
    Route::post('delete-payment','PaymentController@delete_payment');
	
	//Holidays
    Route::post('store-holiday','AuctionOnHolidayController@store_holiday');
    Route::post('update-holiday','AuctionOnHolidayController@update_holiday');
    Route::post('get-holidays','AuctionOnHolidayController@get_holidays');
    Route::post('list-holidays','AuctionOnHolidayController@list_holidays');
    Route::post('delete-holiday','AuctionOnHolidayController@delete_holiday');
	Route::post('update-holiday-status','AuctionOnHolidayController@update_status');

    //Cash Settlement
    Route::post('store-cash-settlement','CashSettlementController@store_cash_settlement');
    Route::post('update-cash-settlement','CashSettlementController@update_cash_settlement');
    Route::post('get-cash-settlements','CashSettlementController@get_cash_settlements');
    Route::post('list-cash-settlements','CashSettlementController@list_cash_settlements');
    Route::post('delete-cash-settlement','CashSettlementController@delete_cash_settlement');

    //Collection Area Mapping
    Route::post('store-collection-area-mapping','CollectionAreaMappingControllerController@store_collection_area_mapping');
    Route::post('update-collection-area-mapping','CollectionAreaMappingControllerController@update_collection_area_mapping');
    Route::post('get-collection-area-mappings','CollectionAreaMappingControllerController@get_collection_area_mappings');
    Route::post('list-collection-area-mappings','CollectionAreaMappingControllerController@list_collection_area_mappings');
    Route::post('delete-collection-area-mapping','CollectionAreaMappingControllerController@delete_collection_area_mapping');


     //Mobile API Links
     Route::post('mobile-login','MobileApiController@mobile_login');
     Route::post('mobile-list-tenants','MobileApiController@mobile_list_tenants');
     Route::post('mobile-list-branches','MobileApiController@mobile_list_branches');
     Route::post('mobile-add-lead-management','MobileApiController@mobile_store_lead_management');
     Route::post('mobile-list-leads','MobileApiController@mobile_list_leads');
     Route::post('check-login','MobileApiController@check_device_login');
     Route::post('otp-verified','MobileApiController@otp_verified');
     Route::post('get-device-counts','MobileApiController@get_device_counts');
     Route::post('get-devices','MobileApiController@get_devices');
     Route::post('add-cash-settlement','MobileApiController@mobile_cash_settlement');
     Route::post('list-followup-status-types','MobileApiController@mobile_followup_status_list');
     Route::post('mobile-list-states','MobileApiController@mobile_state_lists');
     Route::post('mobile-list-districts','MobileApiController@mobile_district_lists');
     Route::post('mobile-list-cities','MobileApiController@mobile_city_lists');

     Route::post('reset-password','MobileApiController@reset_password');
     Route::post('profile','MobileApiController@profile');
     Route::post('mobile-get-collection-area-customers','MobileApiController@mobile_get_collection_area_customers');
    Route::post('mobile-get-collection-area-entrollments','MobileApiController@mobile_get_collection_area_entrollment');
