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
Route::group(['middleware' => ['tenantCheck','auth.jwt']], function () {
	
	
	Route::middleware('auth:api')->get('/validate-token', function (Request $request) {
    return response()->json(['status' => 'Token is valid']);
});
	//Banks
	Route::any('new-login', 'AuthController@newlogin');
	Route::post('store-bank','BankDetailController@store_bank');
	Route::post('update-bank','BankDetailController@update_bank');
	Route::post('get-banks','BankDetailController@get_banks');
	Route::post('list-banks','BankDetailController@list_banks');
	Route::post('delete-bank','BankDetailController@delete_bank');
	Route::post('update-bank-status','BankDetailController@update_status');
	Route::post('validate-account-no','BankDetailController@validate_account_no');
	
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
	Route::post('super-admin-get-tenants','TenantController@super_admin_get_tenants');
	Route::post('list-tenants','TenantController@list_tenants');
	Route::post('super-admin-list-tenants','TenantController@super_admin_list_tenants');
	Route::post('delete-tenant','TenantController@delete_tenant');
	Route::post('update-tenant-status','TenantController@update_status');
	Route::post('tenant-uploads','TenantController@fileUpload');
	Route::post('menu-list','TenantController@menu_list');
	//Cities
	Route::post('store-city','CityController@store_city');
	Route::post('update-city','CityController@update_city');
	Route::post('get-cities','CityController@get_cities');
	Route::post('list-cities','CityController@list_cities');
	Route::post('delete-city','CityController@delete_city');
	Route::post('update-city-status','CityController@update_status');
	//Gifts
	Route::post('store-gift','CityController@store_gift');
	Route::post('get-gifts','CityController@get_gifts');
	Route::post('list-gifts','CityController@list_gifts');
	Route::post('delete-gift','CityController@delete_gift');
	Route::post('store-gift-issue','ChitDetailController@store_gift_issue');
	Route::post('gift-issue-view','ChitDetailController@gift_issue_view');
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
	Route::post('update-head-office-status','HeadOfficeController@update_status');
	//Roles
	Route::post('store-role','RoleController@store_role');
	Route::post('update-role','RoleController@update_role');
	Route::post('get-roles','RoleController@get_roles');
	Route::post('list-roles','RoleController@list_roles');
	Route::post('delete-role','RoleController@delete_role');
	Route::post('update-role-status','RoleController@update_status');
	Route::post('get-roles-for-permission','RoleController@get_roles_for_permission');
	//Role Types
	Route::post('get-role-types','RoleTypeController@get_role_types');
	Route::post('list-role-types','RoleTypeController@list_role_types');
	//Employees
	Route::post('store-employee','EmployeeController@store_employee');
	Route::post('update-employee','EmployeeController@update_employee');
	Route::post('get-employees','EmployeeController@get_employees');
	Route::post('list-employees','EmployeeController@list_employees');
	Route::post('get-employees-for-role-tree','EmployeeController@get_employees_for_role_tree');
	Route::post('get-branches-for-role-tree','EmployeeController@get_branches_for_role_tree');
	Route::post('delete-employee','EmployeeController@delete_employee');
	Route::post('update-employee-status','EmployeeController@update_status');
	Route::post('employee-file-upload','EmployeeController@employee_file_upload');
	Route::post('get-collection-employees','EmployeeController@get_collection_employees');
	Route::post('store-employee-monthly-salary','EmployeeController@store_employee_monthly_salary');
	Route::post('list-employee-monthly-salary','EmployeeController@list_employee_monthly_salary');
	Route::post('get-employee-salary','EmployeeController@get_employee_salary');
	Route::post('update-employee-monthly-salary','EmployeeController@update_employee_monthly_salary');
	Route::post('delete-employee-monthly-salary','EmployeeController@delete_employee_salary');
	
	Route::post('app-store-collection-remarks','EmployeeController@app_store_collection_remarks');
	Route::post('app-update-collection-remarks','EmployeeController@app_update_collection_remarks');	
	Route::post('app-get-collection-remarks','EmployeeController@app_get_collection_remarks');
	Route::post('app-list-collection-remarks','EmployeeController@app_list_collection_remarks');
	Route::post('app-delete-collection-remarks','EmployeeController@app_delete_collection_remarks');

	
	

	Route::post('get-commision-payment-report','ChitDetailController@get_agent_commision_payment_report');
	Route::post('list-commision-payment-report','ChitDetailController@list_commision_payment_customers');
	Route::post('get-commision-pending-report','ChitDetailController@get_agent_commision_pending_report');
	Route::post('list-commision-pending-report','ChitDetailController@list_commision_pending_customers');
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
	Route::post('update-petty-cash-expense-type-status','PettyCashExpenseTypeController@update_status');
	Route::post('store-account-group','PettyCashExpenseTypeController@store_accounting_group');
	Route::post('update-account-group','PettyCashExpenseTypeController@update_accounts_group');
	Route::post('get-account-group','PettyCashExpenseTypeController@get_accounts_group');
	Route::post('list-account-group','PettyCashExpenseTypeController@list_accounts_group');
	Route::post('delete-account-group','PettyCashExpenseTypeController@delete_accounts_group');
	Route::post('store-head-group','PettyCashExpenseTypeController@store_accounting_head');
	Route::post('update-head-group','PettyCashExpenseTypeController@update_accounts_head');
	Route::post('get-head-group','PettyCashExpenseTypeController@get_accounts_head');
	Route::post('list-head-group','PettyCashExpenseTypeController@list_accounts_head');
	Route::post('delete-head-group','PettyCashExpenseTypeController@delete_accounts_head');
	//Charge Types
	Route::post('store-charge-type','ChargeTypeController@store_charge_type');
	Route::post('update-charge-type','ChargeTypeController@update_charge_type');
	Route::post('get-charge-types','ChargeTypeController@get_charge_types');
	Route::post('list-charge-types','ChargeTypeController@list_charge_types');
	Route::post('delete-charge-type','ChargeTypeController@delete_charge_type');
	Route::post('update-charge-type-status','ChargeTypeController@update_status');
	Route::post('get-charge-types-mobile','MobileApiController@get_charge_types_mobile');
	Route::post('auction-wise-to-be-collected-mobile','MobileApiController@auction_wise_to_be_collected_mobile');
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
	Route::post('get-slab-type','SlabDueController@get_slab_type');
	Route::post('get-customer-slab-type','SlabDueController@get_customer_slab_type');
	//Slab Payments
	Route::post('store-slab-payment','SlabPaymentController@store_slab_payment');
	Route::post('update-slab-payment','SlabPaymentController@update_slab_payment');
	Route::post('get-slab-payments','SlabPaymentController@get_slab_payments');
	Route::post('list-slab-payments','SlabPaymentController@list_slab_payments');
	Route::post('delete-slab-payment','SlabPaymentController@delete_slab_payment');
	Route::post('update-slab-payment-status','SlabPaymentController@update_status');
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
	Route::post('store-country','CountryController@store_country');
	Route::post('update-country','CountryController@update_country');
	Route::post('get-countries','CountryController@get_countries');
	Route::post('list-countries','CountryController@list_countries');
	Route::post('delete-country','CountryController@delete_countries');
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
	Route::post('update-group-status','GroupController@update_status');
	Route::post('fill-group','GroupController@fill_group');
	Route::post('group-starting-report','GroupController@group_starting_report');
	Route::post('group-termination-report','GroupController@group_termination_report');
	//Customers
	Route::post('store-customer','CustomerController@store_customer');
	Route::post('update-customer','CustomerController@update_customer');
	Route::post('get-unenrollment-report','CustomerController@get_unenrollment_report');
	Route::post('get-customers','CustomerController@get_customers');
	Route::post('list-customers','CustomerController@list_customers');
	Route::post('delete-customer','CustomerController@delete_customer');
	Route::post('update-customer-status','CustomerController@update_status');
	Route::post('customer-file-upload','CustomerController@fileUpload');
	Route::post('store-guarantor','CustomerController@store_guarantor');
	Route::post('list-guarantors','CustomerController@list_guarantors');
	Route::post('get-guarantors','CustomerController@get_guarantors');
	Route::post('update-guarantor','CustomerController@update_guarantor');
	Route::post('delete-guarantor','CustomerController@delete_guarantor');
	Route::post('store-nominee','CustomerController@store_nominee');
	Route::post('list-nominees','CustomerController@list_nominees');
	Route::post('get-nominees','CustomerController@get_nominees');
	Route::post('get-nominee-details','CustomerController@get_nominee_details');
	Route::post('update-nominee','CustomerController@update_nominee');
	Route::post('delete-nominee','CustomerController@delete_nominee');
	Route::post('interest-customer-report','CustomerController@interest_customer_report');
	Route::post('get-guarantor-details','CustomerController@get_guarantor_details');
	Route::post('commitment-customer-wise-outstanding','CustomerController@commitment_customer_wise_outstanding');
	
	//Enrollments
	Route::post('store-customer-enrollment','ChitDetailController@store_enrol');
	Route::post('update-customer-enrollment','ChitDetailController@update_enrol');
	Route::post('get-customer-enrollments','ChitDetailController@get_enrolls');
	Route::post('get-enrolls-for-receipts','ChitDetailController@get_enrolls_for_receipts');	
	Route::post('get-customer-enrollments-for-gaurantor','ChitDetailController@get_enrolls_for_gaurantor');
	Route::post('list-customer-enrollments','ChitDetailController@list_enrolls');
	Route::post('delete-customer-enrollment','ChitDetailController@delete_enrol');
	Route::post('cancel-customer-enrollment','ChitDetailController@cancel_enrol');
	Route::post('update-customer-enrollment-status','ChitDetailController@update_status');
	Route::post('store-dummy-customer-enrollment','ChitDetailController@store_dummy_subscriber');
	Route::post('get-enrollment-transactions','ChitDetailController@get_enrollment_transactions');
	Route::post('get-unassigned-tickets','ChitDetailController@get_unassigned_tickets');
	Route::post('get-enroll-ticket-list','ChitDetailController@get_enroll_ticket_list');
	Route::post('get-unrolled-customers','ChitDetailController@get_unrolled_customers');
	Route::post('get-unrolled-customer-list','ChitDetailController@get_unrolled_customer_list');
	Route::post('get-enrolled-customers','ChitDetailController@get_enrolled_customers');
	Route::post('get-auctionable-enrolled-customers','ChitDetailController@get_auctionable_enrolled_customers');
	Route::post('get-cancel-enrolled-customers','ChitDetailController@get_cancel_enroll_customers');
	Route::post('get-cancel-customer-details','ChitDetailController@get_cancel_customers_payment_details');
	Route::post('store-payment-refund','ChitDetailController@store_payment_refund');
	Route::post('delete-customer-commitment-enrollment','ChitDetailController@delete_commitment_enrol');
	Route::post('get-payment-refund','ChitDetailController@get_payment_refund');
	Route::post('payment-refund-report','ChitDetailController@payment_refund_report');
	Route::post('payment-transfer-report','ChitDetailController@payment_transfer_report');
	Route::post('delete-payment-refund','ChitDetailController@delete_payment_refund');
	Route::post('get-business-agent-report','ChitDetailController@get_business_agent_report');
	Route::post('get-enrolls-for-customer-ledger','ChitDetailController@get_enrolls_for_customer_ledger');
	Route::post('store-transfer-customer','ChitDetailController@store_payment_transfer');	
	//Route::post('pass-book-first-page','ChitDetailController@pass_book_first_page');
	Route::post('get-commitment-customers','ChitDetailController@get_commitment_customers');
	Route::post('group-delay-list','ChitDetailController@group_delay_list');
	Route::post('get-sms-group-details','ChitDetailController@get_group_sms_details');
	Route::post('get-sms-trigger','ChitDetailController@get_sms_trigger');
	Route::post('overall-business-report','ChitDetailController@overall_business_report');

	//SMS Template
	Route::post('store-sms-template','ChitDetailController@store_sms_template');
	Route::post('get-sms-templates','ChitDetailController@get_sms_template');
	Route::post('list-sms-templates','ChitDetailController@list_sms_template');
	Route::post('delete-sms-template','ChitDetailController@delete_sms_template');

	//Family Code
	Route::post('store-family-code','CustomerController@store_family_code');
	Route::post('update-family-code','CustomerController@update_family_code');
	Route::post('get-family-codes','CustomerController@get_family_codes');
	Route::post('list-family-codes','CustomerController@list_family_codes');
	Route::post('delete-family-code','CustomerController@delete_family_code');
	Route::post('get-customers-full','CustomerController@get_customers_full');
	Route::post('get-customers-view','CustomerController@get_customers_view');
	Route::post('search-customers-history','CustomerController@search_customers_history');
	
	//Bid Advance
	Route::post('store-bid-advance','PaymentController@store_bid_advance');

	//Petty cash Expenses
	Route::post('store-petty-cash-expense','PettyCashExpenseController@store_petty_cash_expense');
	Route::post('update-petty-cash-expense','PettyCashExpenseController@update_petty_cash_expense');
	Route::post('get-petty-cash-expenses','PettyCashExpenseController@get_petty_cash_expenses');
	Route::post('list-petty-cash-expenses','PettyCashExpenseController@list_petty_cash_expenses');
	Route::post('delete-petty-cash-expense','PettyCashExpenseController@delete_petty_cash_expense');
	 Route::post('update-petty-cash-expense-status','PettyCashExpenseController@update_status');
	 Route::post('cheque-return-petty-cash-expense','PettyCashExpenseController@cheque_return_petty_cash_expense');
	Route::post('cheque-cleared-petty-cash-expense','PettyCashExpenseController@cheque_cleared_petty_cash_expense');
	Route::post('store-account-expense','PettyCashExpenseController@store_accounts_expense');
	Route::post('list-account-expense','PettyCashExpenseController@list_account_expenses');
	Route::post('trial-balance','PettyCashExpenseController@trial_balance');
	Route::post('profit-loss-expense-report','PettyCashExpenseController@profitLossReport');
	Route::post('profit-loss-income-report','PettyCashExpenseController@profit_loss_income_report');
	Route::post('balance-sheet','PettyCashExpenseController@getBalanceSheet');
	Route::post('debit-credit-view','PettyCashExpenseController@debit_credit_view');
	Route::post('cash-bank-book','PettyCashExpenseController@bookReport');
	
	//Petty cash Income
	Route::post('store-petty-cash-income','PettyCashIncomeController@store_petty_cash_income');
	Route::post('update-petty-cash-income','PettyCashIncomeController@update_petty_cash_income');
	Route::post('get-petty-cash-incomes','PettyCashIncomeController@get_petty_cash_incomes');
	Route::post('list-petty-cash-incomes','PettyCashIncomeController@list_petty_cash_incomes');
	Route::post('delete-petty-cash-income','PettyCashIncomeController@delete_petty_cash_income');
	Route::post('update-petty-cash-income-status','PettyCashIncomeController@update_status');
	Route::post('get-available-amounts','PettyCashIncomeController@get_available_amounts');
	Route::post('cheque-return-petty-cash-income','PettyCashIncomeController@cheque_return_petty_cash_income');
	Route::post('cheque-cleared-petty-cash-income','PettyCashIncomeController@cheque_cleared_petty_cash_income');

	//Other Charges Receipts
	Route::post('store-other-charge-receipt','OtherChargeController@store_other_charge_type');
	Route::post('update-other-charge-receipt','OtherChargeController@update_other_charge_type');
	Route::post('get-other-charge-receipts','OtherChargeController@get_other_charge_receipts');
	Route::post('list-other-charge-receipts','OtherChargeController@list_other_charge_receipts');
	Route::post('delete-other-charge-receipt','OtherChargeController@delete_other_charge_receipts');
	Route::post('other-charge-receipt-report','OtherChargeController@other_charges_receipt_report');
	Route::post('cheque-return-other-charge','OtherChargeController@cheque_return_other_charge');
	Route::post('cheque-cleared-other-charge','OtherChargeController@cheque_cleared_other_charge');
	 //Documents
	Route::post('store-document','DocumentController@store_document');
	Route::post('update-document','DocumentController@update_document');
	Route::post('get-documents','DocumentController@get_documents');
	Route::post('list-documents','DocumentController@list_documents');
	Route::post('delete-document','DocumentController@delete_document');
	Route::post('asset-document-upload','DocumentController@assetFileUpload');
	Route::post('return-document','DocumentDetailController@return_document');
	Route::post('remove-document','DocumentDetailController@remove_document');
	Route::post('return-asset','AssetDetailController@return_asset');
	Route::post('remove-asset','AssetDetailController@remove_asset');
	Route::post('store-loan-document','DocumentController@store_loan_document');
	Route::post('update-loan-document','DocumentController@update_loan_document');
	Route::post('get-loan-documents','DocumentController@get_loan_documents');
    Route::post('delete-loan-documents','DocumentController@delete_loan_document');
	Route::post('return-loan-document','DocumentDetailController@return_loan_document');
	Route::post('remove-loan-document','DocumentDetailController@remove_loan_document');
	Route::post('return-loan-asset','AssetDetailController@return_loan_asset');
	Route::post('remove-loan-asset','AssetDetailController@remove_loan_asset');
	
	//Before enrol receipt
	Route::post('store-before-enrol-receipt','OtherChargeController@store_before_enrol_receipt');
	Route::post('get-before-enrol-receipts','OtherChargeController@get_before_enrol_receipts');
	Route::post('list-before-enrol-receipts','OtherChargeController@list_before_enrol_receipts');
	Route::post('before-enrol-receipt-cheque-return','OtherChargeController@before_enrol_receipt_cheque_return');
	Route::post('before-enrol-receipt-cheque-cleared','OtherChargeController@before_enrol_receipt_cheque_cleared');
	Route::post('delete-before-enrol-receipt','OtherChargeController@delete_before_enrol_receipt');
	
	
	Route::post('list-pigmy-receipts','OtherChargeController@list_pigmy_receipts');
	
	//bid form
	Route::post('store-online-bid','BiddingDetailController@store_online_bid');
	Route::post('getcompleted-groupauction-details','BiddingDetailController@get_completed_group_auction_details');
	Route::post('get-group-bid','BiddingDetailController@get_group_bid');
	Route::post('get-completed-bid','BiddingDetailController@get_completed_bid');
	Route::post('reactive-bid','BiddingDetailController@reactive_bid');
	Route::post('enable-bid','BiddingDetailController@enable_bid');
	Route::post('disable-bid','BiddingDetailController@disable_bid');
	Route::post('get-maxamount-bid-details','BiddingDetailController@get_max_amount_bid_details');
	Route::post('get-online-bid','BiddingDetailController@get_online_bid');
	Route::post('get-log-bid-view','BiddingDetailController@get_log_bid_view');
	Route::post('get-log-bid-viewsort','BiddingDetailController@get_log_bid_viewsort');
	Route::post('get-group-bid-inst','BiddingDetailController@get_group_bid_inst');

	//Message Log
	Route::post('store-message-bidlog','BiddingDetailController@store_message_bidlog');
	Route::post('delete-bid','BiddingDetailController@delete_bid');

	Route::post('get-gst-included','BiddingDetailController@get_gst_included');
	//Employee Attendances
	Route::post('store-employee-attendance','EmployeeAttendanceController@store_employee_attendance');
	Route::post('update-employee-attendance','EmployeeAttendanceController@update_employee_attendance');
	//Route::get('get-employee-attendances/{branchId}/{attYear}/{attMonth}/{attDate}','EmployeeAttendanceController@get_employee_attendances');
	Route::post('get-employee-attendances','EmployeeAttendanceController@get_employee_attendances');
	Route::post('list-employee-attendances','EmployeeAttendanceController@list_employee_attendances');
	Route::post('delete-employee-attendance','EmployeeAttendanceController@delete_employee_attendance');
	Route::post('store-leave-request','EmployeeLeaveRequestController@store_leave_request');
	Route::post('list-leave-requests','EmployeeLeaveRequestController@list_leave_requests');
	Route::post('decide-leave-request','EmployeeLeaveRequestController@decide_leave_request');
	Route::post('list-punch-attendances','EmployeeLeaveRequestController@list_punch_attendances');
	// Employee Tasks & Targets
	Route::post('store-employee-task','EmployeeTaskTargetController@store_employee_task');
	Route::post('list-employee-tasks','EmployeeTaskTargetController@list_employee_tasks');
	Route::post('update-employee-task-status','EmployeeTaskTargetController@update_employee_task_status');
	Route::post('delete-employee-task','EmployeeTaskTargetController@delete_employee_task');
	Route::post('store-employee-target','EmployeeTaskTargetController@store_employee_target');
	Route::post('list-employee-targets','EmployeeTaskTargetController@list_employee_targets');
	Route::post('update-employee-target-progress','EmployeeTaskTargetController@update_employee_target_progress');
	Route::post('delete-employee-target','EmployeeTaskTargetController@delete_employee_target');
	Route::post('today-task-target-summary','EmployeeTaskTargetController@today_task_target_summary');
	// Employee Extended Modules (skills, performance, visits, achievements)
	Route::post('store-employee-skill','EmployeeExtendedController@store_skill');
	Route::post('list-employee-skills','EmployeeExtendedController@list_skills');
	Route::post('update-employee-skill','EmployeeExtendedController@update_skill');
	Route::post('delete-employee-skill','EmployeeExtendedController@delete_skill');
	Route::post('store-skill-assessment','EmployeeExtendedController@store_skill_assessment');
	Route::post('list-skill-assessments','EmployeeExtendedController@list_skill_assessments');
	Route::post('delete-skill-assessment','EmployeeExtendedController@delete_skill_assessment');
	Route::post('get-employee-performance','EmployeeExtendedController@get_employee_performance');
	Route::post('list-performance-scores','EmployeeExtendedController@list_performance_scores');
	Route::post('store-work-plan','EmployeeExtendedController@store_work_plan');
	Route::post('list-work-plans','EmployeeExtendedController@list_work_plans');
	Route::post('update-work-plan-status','EmployeeExtendedController@update_work_plan_status');
	Route::post('delete-work-plan','EmployeeExtendedController@delete_work_plan');
	Route::post('store-customer-visit','EmployeeExtendedController@store_customer_visit');
	Route::post('list-customer-visits','EmployeeExtendedController@list_customer_visits');
	Route::post('delete-customer-visit','EmployeeExtendedController@delete_customer_visit');
	Route::post('store-employee-badge','EmployeeExtendedController@store_badge');
	Route::post('list-employee-badges','EmployeeExtendedController@list_badges');
	Route::post('delete-employee-badge','EmployeeExtendedController@delete_badge');
	Route::post('get-employee-achievements','EmployeeExtendedController@get_employee_achievements');
	Route::post('get-leaderboard','EmployeeExtendedController@get_leaderboard');
	Route::post('store-employee-certificate','EmployeeExtendedController@store_certificate');
	Route::post('list-employee-certificates','EmployeeExtendedController@list_certificates');
	Route::post('store-attendance-settings','EmployeeExtendedController@store_attendance_settings');
	Route::post('list-attendance-settings','EmployeeExtendedController@list_attendance_settings');
	Route::post('get-conversion-stats','EmployeeExtendedController@get_conversion_stats');
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
	Route::post('report-followup','FollowupController@get_followup_report');
	//Followupstatus
	Route::post('store-followup-status','FollowupStatusTypeController@store_followup_status');
	Route::post('update-followup-status','FollowupStatusTypeController@update_followup_status');
	Route::post('get-followup-status','FollowupStatusTypeController@get_followup_status');
	Route::post('list-followup-status','FollowupStatusTypeController@list_followup_status');
	Route::post('delete-followup-status','FollowupStatusTypeController@delete_followup_status');
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
	Route::post('bidding-advance-details','BiddingDetailController@get_advance_bidding_details');
	Route::post('update-auction-status','BiddingDetailController@update_status');
	Route::post('get-customer-commitment-details','BiddingDetailController@get_commitment_payment_details');
	Route::post('due-calculation','BiddingDetailController@due_amount_calculation');
	Route::post('get-auction-details-dashboard','BiddingDetailController@get_auction_details_dashboard');
	Route::post('get-branch-wise-group-details','BiddingDetailController@get_branch_wise_group_details');
	Route::post('get-profit-chit-avaiable-amount','BiddingDetailController@get_profit_chit_avaiable_amount');
	Route::post('commitment-auction-cron','BiddingDetailController@commitment_auction_cron');
	Route::post('store-auction-minutes','BiddingDetailController@store_auction_minutes');
	Route::post('get-auction-minutes','BiddingDetailController@get_auction_minutes');
	Route::post('get-minutes-group-details','BiddingDetailController@get_minutes_group_details');

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
	Route::post('delete-commitment-receipt','ReceiptController@delete_receipt');
	Route::post('delete-receipt-before','ReceiptController@delete_receipt_before');
	Route::post('cheque-return','ReceiptController@cheque_return');
	Route::post('cheque-submit','ReceiptController@cheque_submit');
	Route::post('cheque-cleared','ReceiptController@cheque_cleared'); 
	Route::post('get-enrol-details','ReceiptController@get_enrol_details'); 
	Route::post('commitment-receipt-report','ReceiptController@commitment_receipt_report'); 
	Route::post('current-month-collection','ReceiptController@current_month_collection');
	
	// Route::post('accounts-report','ReceiptController@accounts_report');
	Route::post('commitment-details-receipt','ReceiptController@get_commitment_receipt_details');
	Route::post('store-bp-adjust-receipt','ReceiptController@store_bp_adjust_receipt');
	Route::post('get-prized-customer-details','ReceiptController@get_prized_customer_details');
	Route::post('get-pending-release-amount','ReceiptController@get_pending_release_amount');
	Route::post('store-advance-receipt','ReceiptController@store_advance_receipt');
	Route::post('get-available-advance-details','ReceiptController@get_available_advance_details');
	Route::post('list-advanced-receipts','ReceiptController@list_advanced_receipts');
	Route::post('available-advanced-reports','ReceiptController@available_advanced_receipts');
	Route::post('get-auctionwise-receipt','ReceiptController@get_auctionwise_receipt');
	Route::post('get-enrol-details-divident-cancel','ReceiptController@get_enrol_details_divident_cancel');
	Route::post('get-enrol-details-bonus-cancel','ReceiptController@get_enrol_details_bonus_cancel');
	Route::post('customer-ledger-running-enroll-info','ReceiptController@customer_ledger_running_enroll_info');
	Route::post('customer-ledger-closed-enroll-info','ReceiptController@customer_ledger_closed_enroll_info');
	Route::post('cust-ledger-paid-and-pending','ReceiptController@cust_ledger_paid_and_pending');
	Route::post('customer-ledger-customer-document-info','ReceiptController@customer_ledger_customer_document_info');
	Route::post('customer-ledger-guarantor-document-info','ReceiptController@customer_ledger_guarantor_document_info');
	Route::post('customer-ledger-nominee-document-info','ReceiptController@customer_ledger_nominee_document_info');
	Route::post('auction-wise-to-be-collected','ReceiptController@auction_wise_to_be_collected');
	Route::post('auction-wise-to-be-collected-report','ReceiptController@auction_wise_to_be_collected_report');
	Route::post('group-abstract-report','ReceiptController@group_abstract_report');
	Route::post('customer-ledger-relationship-det','ReceiptController@customer_ledger_relationship_det');
	Route::post('customer-ledger-group-advance-details','ReceiptController@customer_ledger_group_advance_details');
	Route::post('customer-statement','ReceiptController@customer_statement');
	Route::post('receipt-report-excel-export','ReceiptController@receipt_report_excel_export');
	Route::post('advance-receipt-cheque-return','ReceiptController@advance_receipt_cheque_return');
	Route::post('advance-receipt-cheque-cleared','ReceiptController@advance_receipt_cheque_cleared');
	Route::post('customer-ledger-get-enrol-details','ReceiptController@customer_ledger_get_enrol_details');
	Route::post('store-receipt-duplicate','ReceiptController@store_receipt_duplicate');
	Route::post('get-enrol-details-duplicate','ReceiptController@get_enrol_details_duplicate'); 
	Route::post('receipt-print','ReceiptController@receipt_print'); 
	Route::post('commitment-receipt-print','ReceiptController@commitment_receipt_print');  
	Route::post('store-unknown-transaction','ReceiptController@store_unknown_transaction');
	Route::post('update-unknown-transaction','ReceiptController@update_unknown_transaction');
	Route::post('get-unknown-transaction','ReceiptController@get_unknown_transaction');
	Route::post('list-unknown-transaction','ReceiptController@list_unknown_transaction');
	Route::post('delete-unknown-transaction','ReceiptController@delete_unknown_transaction');
	Route::post('advance-receipt-print','ReceiptController@advance_receipt_print');
	Route::post('before-enrol-receipt-print','ReceiptController@before_enrol_receipt_print');
	Route::post('other-receipt-print','ReceiptController@other_receipt_print');
	Route::post('customer-amount-receipt-report','ReceiptController@customer_amount_receipt_report');
	Route::post('customer-ledger-group-advance-detail','ChitDetailController@customer_ledger_group_advance_details');
	Route::post('day-book','ChitDetailController@day_book');
	Route::post('day-book-excess','ChitDetailController@day_book_excess');
	Route::post('get-commitment-enrolls-for-receipts','ChitDetailController@get_commitment_enrolls_for_receipts');
	Route::post('cash-settlement-report','ReceiptController@cash_settlement_report');
	Route::post('member-wise-outstanding','ReceiptController@member_wise_outstanding');
	Route::post('get-pigmy-details','ReceiptController@get_pigmy_details');
	
	//Payments
	Route::post('store-payment','PaymentController@store_payment');
	Route::post('update-payment','PaymentController@update_payment');
	Route::post('get-payments','PaymentController@get_payments');
	Route::post('list-payments','PaymentController@list_payments');
	Route::post('delete-payment','PaymentController@delete_payment');
	Route::post('view-payment','PaymentController@view_payment');
	Route::post('store-commitment-payment','PaymentController@store_commitment_payment');
	Route::post('delete-commitment-payment','PaymentController@delete_commitment_payment');
	Route::post('update-commitment-payment','PaymentController@update_commitment_payment');
	Route::post('get-commitment-payments','PaymentController@get_commitment_payments');
	Route::post('list-commitment-payments','PaymentController@list_commitment_payments');	
	Route::post('get-commitment-payment-report','PaymentController@get_commitment_payment_report');
	Route::post('day-closing-reports','PaymentController@day_closing_reports');
	Route::post('accounts-report','PaymentController@accounts_report');
	Route::post('get-commitment-pending-report','PaymentController@get_commitment_pending_report');
	Route::post('scheme-wise-payable-pending-report','SpnCollectionReportController@scheme_wise_payable_pending');
	Route::post('next-month-collection-report','SpnCollectionReportController@next_month_collection');
	Route::post('this-month-unpaid-report','SpnCollectionReportController@this_month_unpaid');
	Route::post('due-no-customer-report','SpnCollectionReportController@due_no_customer_list');
	Route::post('get-customer-ledger-payment','PaymentController@customer_ledger_payment_details');
	Route::post('cheque-return-commitment-payment','PaymentController@cheque_return_commitment_payment');
	Route::post('cheque-cleared-commitment-payment','PaymentController@cheque_cleared_commitment_payment');
	Route::post('cheque-return-payment-released','PaymentController@cheque_return_payment_release');
	Route::post('cheque-cleared-payment-released','PaymentController@cheque_cleared_payment_release');
	Route::post('loan-day-closing-reports','PaymentController@loan_day_closing_reports');
	
	//SMS Template
	Route::post('store-sms-template','ChitDetailController@store_sms_template');
	Route::post('get-sms-templates','ChitDetailController@get_sms_template');
	Route::post('list-sms-templates','ChitDetailController@list_sms_template');
	Route::post('delete-sms-template','ChitDetailController@delete_sms_template');
	
	//Bid Advance
	Route::post('store-bid-advance','PaymentController@store_bid_advance');

	//Holidays
	Route::post('store-holiday','AuctionOnHolidayController@store_holiday');
	Route::post('update-holiday','AuctionOnHolidayController@update_holiday');
	Route::post('get-holidays','AuctionOnHolidayController@get_holidays');
	Route::post('list-holidays','AuctionOnHolidayController@list_holidays');
	Route::post('delete-holiday','AuctionOnHolidayController@delete_holiday');
	Route::post('update-holiday-status','AuctionOnHolidayController@update_status');
	Route::post('auction-day-details','AuctionOnHolidayController@auction_day_details');

	//Cash Settlement
	Route::post('store-cash-settlement','CashSettlementController@store_cash_settlement');
	Route::post('update-cash-settlement','CashSettlementController@update_cash_settlement');
	Route::post('get-cash-settlements','CashSettlementController@get_cash_settlements');
	Route::post('list-cash-settlements','CashSettlementController@list_cash_settlements');
	Route::post('delete-cash-settlement','CashSettlementController@delete_cash_settlement');
	Route::post('approve-cash-settlement','CashSettlementController@approve_cash_settlement');
	//Collection Area Mapping
	Route::post('store-collection-area-mapping','CollectionAreaMappingController@store_collection_area_mapping');
	Route::post('update-collection-area-mapping','CollectionAreaMappingController@update_collection_area_mapping');
	Route::post('get-collection-area-mappings','CollectionAreaMappingController@get_collection_area_mappings');
	Route::post('list-collection-area-mappings','CollectionAreaMappingController@list_collection_area_mappings');
	Route::post('delete-collection-area-mapping','CollectionAreaMappingController@delete_collection_area_mapping');
	//Users
	Route::post('store-user111','UserController@store_user');
	Route::post('update-user','UserController@update_user');
	Route::post('/list-users', 'UserController@list_users');
	Route::post('get-users','UserController@get_user');
	Route::post('delete-user','UserController@delete_user');
	Route::post('update-user-status','UserController@update_user_status');
	Route::post('check-email-validation','UserController@check_email_validation');
	Route::post('change-password','UserController@change_password');
	Route::post('forget-password','UserController@forget_password');

	//gst voucher
	Route::post('gst-voucher','PaymentController@gst_voucher');

	//Reports 
	Route::post('auction-report','BiddingDetailController@get_auction_report');
	Route::post('get-vacant-report','BiddingDetailController@get_vacant_report');
	Route::post('payment-release-report','PaymentController@get_payment_report');
	Route::post('payment-pending-report','PaymentController@get_payment_pending_report');
	Route::post('enrol-report','ChitDetailController@get_enrol_report');
	Route::post('customer-wise-outstanding-report','CustomerController@get_customer_wise_outstanding');
	Route::post('enrollment-wise-details','ChitDetailController@customer_wise_enrol_details');
	Route::post('group-wise-outstanding-report','ChitDetailController@get_group_wise_outstanding');
	Route::post('area-wise-outstanding','ChitDetailController@area_wise_outstanding');
	Route::post('employee-wise-outstanding','ChitDetailController@employee_wise_outstanding');
	Route::post('customer-wise-employee-outstanding','ChitDetailController@customer_wise_employee_outstanding');		
	Route::post('receipt-report','ReceiptController@list_receipts');
	Route::post('dashboard','ReceiptController@get_dashboard');
	Route::post('get-chit-dashboard-bundle','ChitDashboardController@get_bundle');
	Route::post('historical-performance-report','BusinessAnalyticsReportController@historical_performance');
	Route::post('business-forecast-report','BusinessAnalyticsReportController@forecast');
	Route::post('dashboard-bid','BiddingDetailController@get_bid_dashborad');
	Route::post('auction-minutes-report','BiddingDetailController@get_auction_minutes_report');
	Route::post('payment-minutes-release-report','PaymentController@get_payment_minutes_report');
	Route::post('payment-minutes-pending-report','PaymentController@get_payment_pending_minutes_report');
	Route::post('store-pledge-customer','ChitDetailController@store_pledge_chit');
	Route::post('get-pledge-customer','ChitDetailController@get_pledge_chit');
	Route::post('update-pledge','ChitDetailController@update_pledge');

	//cheque in hand report
	Route::post('cheque-hand-report','ReceiptController@cheque_hand_report');
	//cheque in bank report
	Route::post('cheque-bank-report','ReceiptController@cheque_bank_report');
	//cheque issue report
	Route::post('cheque-issue-report','PaymentController@cheque_issue_report');
	//fd report
	Route::post('fd-report','GroupController@fd_report');

	//Agent Commission Payment
	Route::post('store-agent-commission-payment','AgentCommissionPaymentController@store_agent_commission_payment');
	Route::post('update-agent-commission-payment','AgentCommissionPaymentController@update_agent_commission_payment');
	Route::post('get-agent-commission-payments','AgentCommissionPaymentController@get_agent_commission_payments');
	Route::post('list-agent-commission-payments','AgentCommissionPaymentController@list_agent_commission_payments');
	Route::post('delete-agent-commission-payment','AgentCommissionPaymentController@delete_agent_commission_payment');
	Route::post('get-agent-enrol-details','AgentCommissionPaymentController@get_agent_enrol_details');
	Route::post('cheque-return-commision-payment','AgentCommissionPaymentController@cheque_return_commision_payment');
	Route::post('cheque-cleared-commision-payment','AgentCommissionPaymentController@cheque_cleared_commision_payment');

	//Agent Incentive Payment
	Route::post('store-agent-incentive-payment','AgentIncentivePaymentController@store_agent_incentive_payment');
	Route::post('update-agent-incentive-payment','AgentIncentivePaymentController@update_agent_incentive_payment');
	Route::post('get-agent-incentive-payments','AgentIncentivePaymentController@get_agent_incentive_payments');
	Route::post('list-agent-incentive-payments','AgentIncentivePaymentController@list_agent_incentive_payments');
	Route::post('delete-agent-incentive-payment','AgentIncentivePaymentController@delete_agent_incentive_payment');
	Route::post('get-agent-incentive-enrol-details','AgentIncentivePaymentController@get_agent_incentive_enrol_details');
	Route::post('cheque-return-incentive-payment','AgentIncentivePaymentController@cheque_return_incentive_payment');
	Route::post('cheque-cleared-incentive-payment','AgentIncentivePaymentController@cheque_cleared_incentive_payment');
	Route::post('agent-type-details','AgentIncentivePaymentController@agent_type_details');


	//Dynamic Menu
	Route::post('store-dynamic-menu','DynamicMenucontroller@store_dynamic_menu');
	Route::post('update-dynamic-menu','DynamicMenucontroller@update_dynamic_menu');
	Route::post('get-dynamic-menus','DynamicMenucontroller@get_dynamic_menu');
	Route::post('list-dynamic-menus','DynamicMenucontroller@list_dynamic_menus');
	Route::post('delete-dynamic-menu','DynamicMenucontroller@delete_dynamic_menu');
	Route::post('update-dynamic-menu-status','DynamicMenucontroller@update_status');
	Route::post('get-parent-menus','DynamicMenucontroller@get_parent_menu_list');
	Route::post('get-menu-for-access-setting','DynamicMenucontroller@get_menu_for_access_setting');
	Route::post('get-menu-sort-orders','DynamicMenucontroller@get_menu_sort_orders');
	//Role Permission
	Route::post('store-permission','RolePermissionController@store_permission');
	Route::post('get-permissions','RolePermissionController@get_permissions');
	Route::post('update-permission','RolePermissionController@update_permission');
	

	//Commitment Enrollment
	Route::post('store-commitment-enrolled-customers','ChitDetailController@store_commitment_enrol');
	Route::post('update-commitment-enrolled-customers','ChitDetailController@update_commitmnent_enrol');
	Route::post('get-commitment-enrolled-customers','ChitDetailController@get_commitment_enrolls');
	Route::post('list-commitment-enrolled-customers','ChitDetailController@list_commitment_enrolls');
	Route::post('store-temp-commitment-chit','ChitDetailController@store_temp_commitment_chit');
	Route::post('list-commitment-eligibility-pending','ChitDetailController@list_commitment_eligibility_pending');
	Route::post('approve-commitment-eligibility','ChitDetailController@approve_commitment_eligibility');
	Route::post('get-commitment-reports','ChitDetailController@get_commitment_reports');
	Route::post('cheque-return-payment-refund','ChitDetailController@cheque_return_payment_refund');
	Route::post('cheque-cleared-payment-refund','ChitDetailController@cheque_cleared_payment_refund');
	
	//Loan Detail
	Route::post('store-loan-detail','LoanDetailController@store_loan_detail');
	Route::post('update-loan-detail','LoanDetailController@update_loan_detail');
	Route::post('get-loan-detail','LoanDetailController@get_loan_detail');
	Route::post('list-loan-details','LoanDetailController@list_loan_details');
	Route::post('delete-loan-detail','LoanDetailController@delete_loan_detail');
	Route::post('alert-auction-details','LoanDetailController@alert_auction_details');	
	Route::post('alert-loan-details','LoanDetailController@alert_loan_details');
	Route::post('customer-loan-report','LoanDetailController@get_loan_customer');
	Route::post('loan-customer-outstanding','LoanDetailController@loan_customer_outstanding');

	//Loan Receipt
	Route::post('store-loan-receipt','LoanReceiptController@store_loan_receipt');
	Route::post('update-loan-receipt','LoanReceiptController@update_loan_receipt');
	Route::post('get-loan-receipt','LoanReceiptController@get_loan_receipt');
	Route::post('list-loan-receipts','LoanReceiptController@list_loan_receipts');
	Route::post('delete-loan-receipt','LoanReceiptController@delete_loan_receipt');
	Route::post('get-customer-wise-loan-detail','LoanReceiptController@get_customer_wise_loan_detail');
	Route::post('loan-cheque-return','LoanReceiptController@cheque_return_loan');
	Route::post('loan-cheque-cleared','LoanReceiptController@cheque_cleared_loan');
	Route::post('customer-ledger-loan-receipts','LoanReceiptController@customer_ledger_loan_receipts');
	Route::post('get-loan-details-customer-wise','LoanReceiptController@get_loan_details_customer_wise');

	// Log Monitoring 
	Route::post('module-name-list','ReceiptController@module_name_list');
	Route::post('log-monitoring-report','ReceiptController@log_monitoring_report');

	// Source Types
	Route::post('store-source-type','SourceTypeController@store_source_type');
	Route::post('update-source-type','SourceTypeController@update_source_type');
	Route::post('get-source-types','SourceTypeController@get_source_types');
	Route::post('list-source-types','SourceTypeController@list_source_types');
	Route::post('delete-source-type','SourceTypeController@delete_source_type');

	// Incentive reports
	Route::post('get-incentive-payment-report','ChitDetailController@get_agent_incentive_payment_report');
	Route::post('list-incentive-payment-report','ChitDetailController@list_incentive_payment_customers');
	Route::post('get-incentive-pending-report','ChitDetailController@get_agent_incentive_pending_report');
	Route::post('list-incentive-pending-report','ChitDetailController@list_incentive_pending_customers');

	// Case Masters
	Route::post('store-case-name','CaseController@store_case_name');
	Route::post('get-case-names','CaseController@get_case_names');
	Route::post('list-case-names','CaseController@list_case_names');
	Route::post('delete-case-name','CaseController@delete_case_name');

	// Lawyer Masters
	Route::post('store-lawyer-name','LawyerController@store_lawyer_name');
	Route::post('get-lawyer-names','LawyerController@get_lawyer_names');
	Route::post('list-lawyer-names','LawyerController@list_lawyer_names');
	Route::post('delete-lawyer-name','LawyerController@delete_lawyer_name');

	Route::post('get-suit-filling-report','SuitFillingController@get_suit_filling_report');
	Route::post('add-suit-filling','SuitFillingController@add_suit_filling');
	Route::post('update-suit-filling','SuitFillingController@update_suit_filling');
	Route::post('edit-suit-filling','SuitFillingController@edit_suit_filling');

	

	
	Route::post('get-all-customers','CustomerController@get_all_customers');
	Route::post('get-enrolls-for-customer-ledger-without-trash','ChitDetailController@get_enrolls_for_customer_ledger');

	Route::post('get-before-enrollment-transactions','ChitDetailController@get_before_enrollment_transactions');
	Route::post('get-cancel-enrol-report','ChitDetailController@get_cancel_customer_report'); 
	Route::post('get-voucher-no','PettyCashExpenseTypeController@get_voucher_no');
	Route::post('delete-expenses','PettyCashExpenseController@delete_expenses');
	Route::post('delete-advance-receipt','ReceiptController@delete_advance_receipt');
	
	Route::post('enrol-report-cancel','ChitDetailController@get_enrol_report_cancel');
	
	Route::post('get-othercharge-details','OtherChargeController@get_othercharge_details');
	Route::post('getTrialBalance','PaymentController@getTrialBalance');
	Route::post('daily-collection-book', 'ReceiptController@getDailyCollection');
	 Route::post('customer-statement-report', 'ReceiptController@customer_statement_report');
	 
	 Route::post('list-before-enrol-receipts-customer','ReceiptController@list_before_enrol_receipts_customer');
});

  


//Mobile API Links
	Route::post('mobile-login','MobileApiController@mobile_login');
	Route::post('mobile-list-tenants','MobileApiController@mobile_list_tenants');
	Route::post('mobile-list-branches','MobileApiController@mobile_list_branches');
	Route::post('mobile-list-schemes','MobileApiController@list_schemes');
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
	Route::post('mobile-list-collection-area','MobileApiController@mobile_get_collection_area_customers');
	Route::post('mobile-get-collection-area-customers-vvrs','MobileApiController@mobile_get_collection_area_customers_vvrs');
	Route::post('reset-password','MobileApiController@reset_password');
	Route::post('profile','MobileApiController@profile');
	Route::post('mobile-get-collection-area-customers','MobileApiController@mobile_get_collection_area_customers');
	Route::post('mobile-get-customer-wise-entrollments','MobileApiController@mobile_get_customer_wise_entrolment_details');
	Route::post('mobile-store-other-charge-type','MobileApiController@mobile_store_other_charge_type');
	Route::post('update-device-status','MobileApiController@update_device_status');
	Route::post('get-device-users','MobileApiController@get_device_users');
	Route::post('update-device-login-status','MobileApiController@update_device_login_status');
	Route::post('show-lead-details','MobileApiController@show_lead_details');
	Route::post('update-lead-detail','MobileApiController@update_lead_detail');
	Route::post('entrollment-wise-group-details','MobileApiController@entrollment_wise_group_details');
	Route::post('mobile-add-receipt','MobileApiController@mobile_add_receipt');
	Route::post('mobile-list-payment-types','MobileApiController@mobile_list_payment_types');
	Route::post('get-collection-agent-dashboard','MobileApiController@get_collection_agent_dashboard');
	Route::post('get-business-agent-dashboard','MobileApiController@get_business_agent_dashboard');
	Route::post('get-cash-in-hand','MobileApiController@get_cash_in_hand');
	Route::post('get-admin-dashboard','MobileApiController@get_admin_dashboard');
	Route::post('generated-receipt','MobileApiController@generated_receipt');
	Route::post('receipt-details','MobileApiController@receipt_details');
	Route::post('printed-status-update','MobileApiController@printed_status_update');
	Route::post('list-customer-bank-names','MobileApiController@list_customer_bank_names');
	Route::post('mobile-get-employee-wise-customer-details','MobileApiController@mobile_get_employee_wise_customer_details');
	Route::post('customer-signup-otp','MobileApiController@customer_signup_otp');
	Route::post('customer-update-password','MobileApiController@customer_update_password');
	Route::post('customer-update-register-password','MobileApiController@customer_update_register_password');
	Route::post('customer-signin','MobileApiController@customer_signin');
	Route::post('customer-my-chits','MobileApiController@customer_my_chits');
	Route::post('customer-chit-details','MobileApiController@customer_chit_details');
	Route::post('customer-download-receipts','MobileApiController@customer_download_receipts');
	Route::post('customer-account-copy','MobileApiController@customer_account_copy');
	Route::post('customer-new-commenced-chits','MobileApiController@customer_new_commenced_chits');
	Route::post('enrollment-wise-receipt-details','MobileApiController@enrollment_wise_receipt_details');
	Route::post('customer-interest-message','MobileApiController@customer_interest_message');
	Route::post('mobile-store-advance-receipt','MobileApiController@mobile_store_advance_receipt');
	Route::post('mobile-list-groups','MobileApiController@mobile_list_groups');
	Route::post('mobile-list-customers','MobileApiController@mobile_list_customers');
	Route::post('mobile-list-employees','MobileApiController@mobile_list_employees');
	Route::post('collection-reports','MobileApiController@collection_reports');
	Route::post('list-collection-remarks','MobileApiController@list_collection_remarks');
	Route::post('store-remarks','MobileApiController@store_remarks');
	Route::post('outstanding-reports','MobileApiController@outstanding_reports');
	Route::post('auction-reports','MobileApiController@auction_reports');
	Route::post('commitment-payment-reports','MobileApiController@commitment_payment_reports');
	Route::post('mobile-list-collection-area','MobileApiController@mobile_list_collection_area');
	Route::post('payment-details','MobileApiController@payment_details');
	Route::post('mobile-vacant-report','MobileApiController@mobile_vacant_report');
	Route::post('business-agent-report','MobileApiController@business_agent_report');
	Route::post('agent-wise-customer-details','MobileApiController@agent_wise_customer_details');
	Route::post('mobile-day-closing-reports','MobileApiController@mobile_day_closing_reports');
	Route::post('commitment-payment-details','MobileApiController@commitment_payment_details');
	Route::post('collection-agent-details','MobileApiController@collection_agent_details');
	Route::post('feedback-reports','MobileApiController@feedback_reports');
	Route::post('store-collection-remarks','MobileApiController@store_collection_remarks');
	Route::post('mobile-get-employee-attendance','MobileApiController@mobile_get_employee_attendance');
	Route::post('mobile-store-employee-attendance','MobileApiController@mobile_store_employee_attendance');	
	Route::post('change-password-customer','MobileApiController@change_password_customer');
	Route::post('change-forget-password-customer','MobileApiController@customer_forget_password_otp');
	Route::post('customer-register','MobileApiController@customer_register'); 
	Route::post('update-lead-profile','MobileApiController@update_lead_profile');
	Route::post('customer-signup-otp-kmf','MobileApiController@customer_signup_otp_kmf'); 
	Route::post('customer-signin-kmf','MobileApiController@customer_signin_kmf'); 
	Route::post('mobile-store-lead-management-kmf','MobileApiController@mobile_store_lead_management_kmf'); 
	Route::post('mobile-store-lead-login','MobileApiController@mobile_store_lead_login'); 
	Route::post('lead-update-password','MobileApiController@lead_update_password'); 
	Route::post('mobile-customer-dashboard','MobileApiController@mobile_customer_dashboard');
	Route::post('customer-dashboard','MobileApiController@mobile_customer_dashboard');
	Route::post('customer-profile','MobileApiController@customer_profile');
	Route::post('customer-due-details','MobileApiController@customer_due_details'); 
	Route::post('customer-app-version','MobileApiController@customer_app_version'); 
	



  

 

Route::post('mobile-store-customer','CustomerController@mobile_store_customer');
Route::post('mobile-customer-file-upload','CustomerController@mobile_customer_file_upload');
Route::post('mobile-document-file-upload','DocumentController@mobile_document_file_upload');
Route::get('mobile-get-collection-area-customers-new','MobileApiController@mobile_get_collection_area_customers_new'); 
	Route::get('mobile-get-customer-chit-summary','MobileApiController@mobile_get_customer_chit_summary'); 
	Route::get('mobile-today-followup-count','MobileApiController@mobile_today_followup_count'); 
	Route::get('mobile-today-followup-leads','MobileApiController@mobile_today_followup_leads'); 
  Route::post('app-store-remarks','EmployeeController@app_store_remarks');
	Route::post('app-update-remarks','EmployeeController@app_update_remarks');
	Route::post('app-get-remarks','EmployeeController@app_get_remarks');
	Route::post('app-list-remarks','EmployeeController@app_list_remarks');
	Route::post('app-delete-remarks','EmployeeController@app_delete_remarks');
	
	Route::post('store-customer-advance-receipt','MobileApiController@store_customer_advance_receipt');
	Route::post('store-customer-pigmy-receipt','MobileApiController@store_customer_pigmy_receipt');
	Route::post('mobile-get-customer-wise-entrolment-details-new','MobileApiController@mobile_get_customer_wise_entrolment_details_new');
	Route::post('receipt-details-new','MobileApiController@receipt_details_new');
	Route::post('feedback-customers','MobileApiController@feedback_customers'); 
	Route::post('payment-report-mobile','MobileApiController@get_payment_report_mobile'); 
	Route::post('enrol-report-mobile','MobileApiController@get_enrol_report_mobile'); 
	Route::post('store-followup-mobile','MobileApiController@store_followup_mobile'); 
	Route::post('get-followups-mobile','MobileApiController@get_followups_mobile'); 
	Route::post('get-lead-report-mobile','MobileApiController@get_lead_report_mobile'); 
	Route::post('day-book-mobile','MobileApiController@day_book_mobile'); 
	Route::post('employee-change-password','MobileApiController@employee_change_password'); 
	Route::post('forget-password-request','MobileApiController@forget_password_request'); 
	Route::post('forget-password-reset','MobileApiController@forget_password_reset'); 
	Route::post('mobile-employee-dashboard','MobileApiController@mobile_employee_dashboard'); 
	Route::post('mobile-app-version','MobileApiController@mobile_app_version'); 
	Route::post('mobile-punch-attendance','MobileApiController@mobile_punch_attendance');
	Route::post('mobile-get-today-attendance','MobileApiController@mobile_get_today_attendance');
	Route::post('mobile-get-collection-area-customers-new','MobileApiController@mobile_get_collection_area_customers_new');
	Route::post('mobile-submit-leave-request','EmployeeLeaveRequestController@store_leave_request');
	Route::post('mobile-list-leave-requests','EmployeeLeaveRequestController@list_leave_requests');
	Route::post('mobile-decide-leave-request','EmployeeLeaveRequestController@decide_leave_request');
	Route::post('mobile-list-punch-attendances','EmployeeLeaveRequestController@list_punch_attendances');
	Route::post('mobile-store-employee-task','EmployeeTaskTargetController@store_employee_task');
	Route::post('mobile-list-employee-tasks','EmployeeTaskTargetController@list_employee_tasks');
	Route::post('mobile-update-employee-task-status','EmployeeTaskTargetController@update_employee_task_status');
	Route::post('mobile-delete-employee-task','EmployeeTaskTargetController@delete_employee_task');
	Route::post('mobile-store-employee-target','EmployeeTaskTargetController@store_employee_target');
	Route::post('mobile-list-employee-targets','EmployeeTaskTargetController@list_employee_targets');
	Route::post('mobile-update-employee-target-progress','EmployeeTaskTargetController@update_employee_target_progress');
	Route::post('mobile-today-task-target-summary','EmployeeTaskTargetController@today_task_target_summary');
	Route::post('mobile-get-employee-performance','EmployeeExtendedController@get_employee_performance');
	Route::post('mobile-list-work-plans','EmployeeExtendedController@list_work_plans');
	Route::post('mobile-store-work-plan','EmployeeExtendedController@store_work_plan');
	Route::post('mobile-update-work-plan-status','EmployeeExtendedController@update_work_plan_status');
	Route::post('mobile-store-customer-visit','EmployeeExtendedController@store_customer_visit');
	Route::post('mobile-list-customer-visits','EmployeeExtendedController@list_customer_visits');
	Route::post('mobile-get-employee-achievements','EmployeeExtendedController@get_employee_achievements');
	Route::post('mobile-get-leaderboard','EmployeeExtendedController@get_leaderboard');
	Route::post('mobile-get-conversion-stats','EmployeeExtendedController@get_conversion_stats');
	Route::post('mobile-list-skill-assessments','EmployeeExtendedController@list_skill_assessments');
	Route::post('mobile-register-push-token','MobileApiController@mobile_register_push_token');
	Route::post('mobile-receipt-file-upload','MobileApiController@mobile_receipt_file_upload');
	Route::post('mobile-list-banks','MobileApiController@mobile_list_banks');
	Route::post('get-unenrollment-mobile','CustomerController@get_unenrollment_mobile'); 