import { CommonModule } from '@angular/common';
import { FormsModule,ReactiveFormsModule } from '@angular/forms';
import { NgModule, CUSTOM_ELEMENTS_SCHEMA, NO_ERRORS_SCHEMA }      from '@angular/core';
import { adminpanelRoutingModule } from './admin-panel-routing.module';
import { NgxDatatableModule } from '@swimlane/ngx-datatable';
import { DataTablesModule } from 'angular-datatables';

import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { AuthInterceptor } from '../auth.interceptor';
// *******************************************************************************
// Libs
import { PerfectScrollbarModule } from 'ngx-perfect-scrollbar';
import { ChartsModule as Ng2ChartsModule } from 'ng2-charts/ng2-charts';
import { SelectModule } from 'ng-select';
import { TagInputModule } from 'ngx-chips';
import { ColorPickerModule } from 'ngx-color-picker';
import { LMarkdownEditorModule } from 'ngx-markdown-editor';
import { QuillModule } from '../../vendor/libs/quill/quill.module';
import { DropzoneModule } from 'ngx-dropzone-wrapper';
import { FileUploadModule } from 'ng2-file-upload';
import { SidenavModule } from '../../vendor/libs/sidenav/sidenav.module';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { TourNgBootstrapModule } from 'ngx-tour-ng-bootstrap';
import { HttpClientModule } from '@angular/common/http';
import { NgxSpinnerModule } from 'ngx-spinner';

import { AgGridModule } from 'ag-grid-angular';
import { RouterModule } from '@angular/router';
import { ReportPopupModalComponent } from '../modal/report-popup-modal/report-popup-modal.component';
import { ReportRedirectModalComponent } from '../modal/report-redirect-modal/report-redirect-modal.component';
import {ReportChequeSubmitComponent} from '../modal/report-cheque-submit-modal/report-cheque-submit-modal.component';
import {ReportChequeBankComponent} from '../modal/report-cheque-bank-modal/report-cheque-bank-modal.component';
import { NgxSmartModalModule } from 'ngx-smart-modal';
// *******************************************************************************
import { NoAccessComponent } from './NoAccess/no-access.component';
import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';
//Dashboard
import { DashboardComponent } from './dashboard/dashboard.component';
// Masters
//Bank Detail
import { AddBankDetailsComponent } from './Masters/BankDetails/add-bank-details/add-bank-details.component';
import { UpdateBankDetailsComponent } from './Masters/BankDetails/update-bank-details/update-bank-details.component';
import { ViewBankDetailsComponent } from './Masters/BankDetails/view-bank-details/view-bank-details.component';
import { DeleteBankDetailsComponent } from './Masters/BankDetails/delete-bank-details/delete-bank-details.component';
//Branch
import { AddBranchComponent } from './Masters/Branch/add-branch/add-branch.component';
import { UpdateBranchComponent } from './Masters/Branch/update-branch/update-branch.component';
import { ViewBranchComponent } from './Masters/Branch/view-branch/view-branch.component';
import { DeleteBranchComponent } from './Masters/Branch/delete-branch/delete-branch.component';
//Branch Configuration
import { AddBranchConfigurationComponent } from './Masters/BranchConfiguration/add-branch-configuration/add-branch-configuration.component';
import { UpdateBranchConfigurationComponent } from './Masters/BranchConfiguration/update-branch-configuration/update-branch-configuration.component';
import { ViewBranchConfigurationComponent } from './Masters/BranchConfiguration/view-branch-configuration/view-branch-configuration.component';
import { DeleteBranchConfigurationComponent } from './Masters/BranchConfiguration/delete-branch-configuration/delete-branch-configuration.component';
//Charges Type
import { AddChargesTypeComponent } from './Masters/ChargesType/add-charges-type/add-charges-type.component';
import { UpdateChargesTypeComponent } from './Masters/ChargesType/update-charges-type/update-charges-type.component';
import { ViewChargesTypeComponent } from './Masters/ChargesType/view-charges-type/view-charges-type.component';
import { DeleteChargesTypeComponent } from './Masters/ChargesType/delete-charges-type/delete-charges-type.component';
//City
import { AddCityComponent } from './Masters/City/add-city/add-city.component';
import { UpdateCityComponent } from './Masters/City/update-city/update-city.component';
import { ViewCityComponent } from './Masters/City/view-city/view-city.component';
import { DeleteCityComponent } from './Masters/City/delete-city/delete-city.component';
//Gift
import { AddGiftComponent } from './Masters/Gift/add-gift/add-gift.component';
import { UpdateGiftComponent } from './Masters/Gift/update-gift/update-gift.component';
import { ViewGiftComponent } from './Masters/Gift/view-gift/view-gift.component';
import { DeleteGiftComponent } from './Masters/Gift/delete-gift/delete-gift.component';
//Collection Area
import { AddCollectionAreaComponent } from './Masters/CollectionArea/add-collection-area/add-collection-area.component';
import { UpdateCollectionAreaComponent } from './Masters/CollectionArea/update-collection-area/update-collection-area.component';
import { ViewCollectionAreaComponent } from './Masters/CollectionArea/view-collection-area/view-collection-area.component';
import { DeleteCollectionAreaComponent } from './Masters/CollectionArea/delete-collection-area/delete-collection-area.component';
//Customer
import { AddCustomerComponent } from './Masters/Customer/add-customer/add-customer.component';
import { UpdateCustomerComponent } from './Masters/Customer/update-customer/update-customer.component';
import { ViewCustomerComponent } from './Masters/Customer/view-customer/view-customer.component';
import { DeleteCustomerComponent } from './Masters/Customer/delete-customer/delete-customer.component';
//Advocate Detail
import { AddAdvocateDetailsComponent } from './Masters/AdvocateDetails/add-advocate-details/add-advocate-details.component';
import { UpdateAdvocateDetailsComponent } from './Masters/AdvocateDetails/update-advocate-details/update-advocate-details.component';
import { ViewAdvocateDetailsComponent } from './Masters/AdvocateDetails/view-advocate-details/view-advocate-details.component';
import { DeleteAdvocateDetailsComponent } from './Masters/AdvocateDetails/delete-advocate-details/delete-advocate-details.component';
//Approval Mapping
import { AddApprovalMappingComponent } from './Masters/ApprovalMapping/add-approval-mapping/add-approval-mapping.component';
import { UpdateApprovalMappingComponent } from './Masters/ApprovalMapping/update-approval-mapping/update-approval-mapping.component';
import { ViewApprovalMappingComponent } from './Masters/ApprovalMapping/view-approval-mapping/view-approval-mapping.component';
import { DeleteApprovalMappingComponent } from './Masters/ApprovalMapping/delete-approval-mapping/delete-approval-mapping.component';
//Auction On Holiday
import { AddAuctionOnHolidaysComponent } from './Masters/AuctionOnHolidays/add-auction-on-holidays/add-auction-on-holidays.component';
import { UpdateAuctionOnHolidaysComponent } from './Masters/AuctionOnHolidays/update-auction-on-holidays/update-auction-on-holidays.component';
import { ViewAuctionOnHolidaysComponent } from './Masters/AuctionOnHolidays/view-auction-on-holidays/view-auction-on-holidays.component';
import { DeleteAuctionOnHolidaysComponent } from './Masters/AuctionOnHolidays/delete-auction-on-holidays/delete-auction-on-holidays.component';
//Designation
import { AddDesignationComponent } from './Masters/Designation/add-designation/add-designation.component';
import { UpdateDesignationComponent } from './Masters/Designation/update-designation/update-designation.component';
import { ViewDesignationComponent } from './Masters/Designation/view-designation/view-designation.component';
import { DeleteDesignationComponent } from './Masters/Designation/delete-designation/delete-designation.component';
//District
import { AddDistrictComponent } from './Masters/District/add-district/add-district.component';
import { UpdateDistrictComponent } from './Masters/District/update-district/update-district.component';
import { ViewDistrictComponent } from './Masters/District/view-district/view-district.component';
import { DeleteDistrictComponent } from './Masters/District/delete-district/delete-district.component';
//Employee
import { AddEmployeeComponent } from './Masters/Employee/add-employee/add-employee.component';
import { UpdateEmployeeComponent } from './Masters/Employee/update-employee/update-employee.component';
import { ViewEmployeeComponent } from './Masters/Employee/view-employee/view-employee.component';
import { DeleteEmployeeComponent } from './Masters/Employee/delete-employee/delete-employee.component';
//Group
import { AddGroupComponent } from './Masters/Group/add-group/add-group.component';
import { UpdateGroupComponent } from './Masters/Group/update-group/update-group.component';
import { ViewGroupComponent } from './Masters/Group/view-group/view-group.component';
import { DeleteGroupComponent } from './Masters/Group/delete-group/delete-group.component';
//Guarantor
import { AddGuarantorComponent } from './Masters/Guarantor/add-guarantor/add-guarantor.component';
import { UpdateGuarantorComponent } from './Masters/Guarantor/update-guarantor/update-guarantor.component';
import { ViewGuarantorComponent } from './Masters/Guarantor/view-guarantor/view-guarantor.component';
import { DeleteGuarantorComponent } from './Masters/Guarantor/delete-guarantor/delete-guarantor.component';
//Head Office
import { AddHeadOfficeComponent } from './Masters/HeadOffice/add-head-office/add-head-office.component';
import { UpdateHeadOfficeComponent } from './Masters/HeadOffice/update-head-office/update-head-office.component';
import { ViewHeadOfficeComponent } from './Masters/HeadOffice/view-head-office/view-head-office.component';
import { DeleteHeadOfficeComponent } from './Masters/HeadOffice/delete-head-office/delete-head-office.component';
//Hierarchy
import { AddHierarchyComponent } from './Masters/Hierarchy/add-hierarchy/add-hierarchy.component';
import { UpdateHierarchyComponent } from './Masters/Hierarchy/update-hierarchy/update-hierarchy.component';
import { ViewHierarchyComponent } from './Masters/Hierarchy/view-hierarchy/view-hierarchy.component';
import { DeleteHierarchyComponent } from './Masters/Hierarchy/delete-hierarchy/delete-hierarchy.component';
//Ledger
import { AddLedgerComponent } from './Masters/Ledger/add-ledger/add-ledger.component';
import { UpdateLedgerComponent } from './Masters/Ledger/update-ledger/update-ledger.component';
import { ViewLedgerComponent } from './Masters/Ledger/view-ledger/view-ledger.component';
import { DeleteLedgerComponent } from './Masters/Ledger/delete-ledger/delete-ledger.component';
//Petty Cash Expense Type
import { AddPettyCashExpenseTypeComponent } from './Masters/PettyCashExpenseType/add-petty-cash-expense-type/add-petty-cash-expense-type.component';
import { UpdatePettyCashExpenseTypeComponent } from './Masters/PettyCashExpenseType/update-petty-cash-expense-type/update-petty-cash-expense-type.component';
import { ViewPettyCashExpenseTypeComponent } from './Masters/PettyCashExpenseType/view-petty-cash-expense-type/view-petty-cash-expense-type.component';
import { DeletePettyCashExpenseTypeComponent } from './Masters/PettyCashExpenseType/delete-petty-cash-expense-type/delete-petty-cash-expense-type.component';
//Relationship
import { AddRelationshipComponent } from './Masters/Relationship/add-relationship/add-relationship.component';
import { UpdateRelationshipComponent } from './Masters/Relationship/update-relationship/update-relationship.component';
import { ViewRelationshipComponent } from './Masters/Relationship/view-relationship/view-relationship.component';
import { DeleteRelationshipComponent } from './Masters/Relationship/delete-relationship/delete-relationship.component';
//Role
import { AddRoleComponent } from './Masters/Role/add-role/add-role.component';
import { UpdateRoleComponent } from './Masters/Role/update-role/update-role.component';
import { ViewRoleComponent } from './Masters/Role/view-role/view-role.component';
import { DeleteRoleComponent } from './Masters/Role/delete-role/delete-role.component';
//Scheme
import { AddSchemeComponent } from './Masters/Scheme/add-scheme/add-scheme.component';
import { UpdateSchemeComponent } from './Masters/Scheme/update-scheme/update-scheme.component';
import { ViewSchemeComponent } from './Masters/Scheme/view-scheme/view-scheme.component';
import { DeleteSchemeComponent } from './Masters/Scheme/delete-scheme/delete-scheme.component';
//State
import { AddStateComponent } from './Masters/State/add-state/add-state.component';
import { UpdateStateComponent } from './Masters/State/update-state/update-state.component';
import { ViewStateComponent } from './Masters/State/view-state/view-state.component';
import { DeleteStateComponent } from './Masters/State/delete-state/delete-state.component';
//Surity Detail
import { AddSuretyDetailsComponent } from './Masters/SuretyDetails/add-surety-details/add-surety-details.component';
import { UpdateSuretyDetailsComponent } from './Masters/SuretyDetails/update-surety-details/update-surety-details.component';
import { ViewSuretyDetailsComponent } from './Masters/SuretyDetails/view-surety-details/view-surety-details.component';
import { DeleteSuretyDetailsComponent } from './Masters/SuretyDetails/delete-surety-details/delete-surety-details.component';
//Bonus Slab
import { AddBonusSlabComponent } from './Masters/Slab/BonusSlab/add-bonus-slab/add-bonus-slab.component';
import { UpdateBonusSlabComponent } from './Masters/Slab/BonusSlab/update-bonus-slab/update-bonus-slab.component';
import { ViewBonusSlabComponent } from './Masters/Slab/BonusSlab/view-bonus-slab/view-bonus-slab.component';
import { DeleteBonusSlabComponent } from './Masters/Slab/BonusSlab/delete-bonus-slab/delete-bonus-slab.component';
//Due Slab
import { AddDueSlabComponent } from './Masters/Slab/DueSlab/add-due-slab/add-due-slab.component';
import { UpdateDueSlabComponent } from './Masters/Slab/DueSlab/update-due-slab/update-due-slab.component';
import { ViewDueSlabComponent } from './Masters/Slab/DueSlab/view-due-slab/view-due-slab.component';
import { DeleteDueSlabComponent } from './Masters/Slab/DueSlab/delete-due-slab/delete-due-slab.component';
//Interest Slab
import { AddInterestSlabComponent } from './Masters/Slab/InterestSlab/add-interest-slab/add-interest-slab.component';
import { UpdateInterestSlabComponent } from './Masters/Slab/InterestSlab/update-interest-slab/update-interest-slab.component';
import { ViewInterestSlabComponent } from './Masters/Slab/InterestSlab/view-interest-slab/view-interest-slab.component';
import { DeleteInterestSlabComponent } from './Masters/Slab/InterestSlab/delete-interest-slab/delete-interest-slab.component';
//Penalty Slab
import { AddPenaltySlabComponent } from './Masters/Slab/PenaltySlab/add-penalty-slab/add-penalty-slab.component';
import { UpdatePenaltySlabComponent } from './Masters/Slab/PenaltySlab/update-penalty-slab/update-penalty-slab.component';
import { ViewPenaltySlabComponent } from './Masters/Slab/PenaltySlab/view-penalty-slab/view-penalty-slab.component';
import { DeletePenaltySlabComponent } from './Masters/Slab/PenaltySlab/delete-penalty-slab/delete-penalty-slab.component';
//Payment Slab
import { AddPaymentSlabComponent } from './Masters/Slab/PaymentSlab/add-payment-slab/add-payment-slab.component';
import { UpdatePaymentSlabComponent } from './Masters/Slab/PaymentSlab/update-payment-slab/update-payment-slab.component';
import { ViewPaymentSlabComponent } from './Masters/Slab/PaymentSlab/view-payment-slab/view-payment-slab.component';
import { DeletePaymentSlabComponent } from './Masters/Slab/PaymentSlab/delete-payment-slab/delete-payment-slab.component';
//Tenant
import { AddTenantComponent } from './Masters/Tenant/add-tenant/add-tenant.component';
import { UpdateTenantComponent } from './Masters/Tenant/update-tenant/update-tenant.component';
import { ViewTenantComponent } from './Masters/Tenant/view-tenant/view-tenant.component';
import { DeleteTenantComponent } from './Masters/Tenant/delete-tenant/delete-tenant.component';
//Customer Enrollment
import { AddCustomerEnrollmentComponent } from './SubscriberManagement/CustomerEnrollment/add-customer-enrollment/add-customer-enrollment.component';
import { UpdateCustomerEnrollmentComponent } from './SubscriberManagement/CustomerEnrollment/update-customer-enrollment/update-customer-enrollment.component';
import { ViewCustomerEnrollmentComponent } from './SubscriberManagement/CustomerEnrollment/view-customer-enrollment/view-customer-enrollment.component';
import { DeleteCustomerEnrollmentComponent } from './SubscriberManagement/CustomerEnrollment/delete-customer-enrollment/delete-customer-enrollment.component';
//Auction
import { AddAuctionComponent } from './SubscriberManagement/Auction/add-auction/add-auction.component';
import { UpdateAuctionComponent } from './SubscriberManagement/Auction/update-auction/update-auction.component';
import { ViewAuctionComponent } from './SubscriberManagement/Auction/view-auction/view-auction.component';
import { DeleteAuctionComponent } from './SubscriberManagement/Auction/delete-auction/delete-auction.component';
//Payment
import { AddPaymentComponent } from './SubscriberManagement/Payment/add-payment/add-payment.component';
import { UpdatePaymentComponent } from './SubscriberManagement/Payment/update-payment/update-payment.component';
import { ViewPaymentComponent } from './SubscriberManagement/Payment/view-payment/view-payment.component';
import { DeletePaymentComponent } from './SubscriberManagement/Payment/delete-payment/delete-payment.component';
//Receipt
import { AddReceiptComponent } from './SubscriberManagement/Receipts/add-receipt/add-receipt.component';
import { UpdateReceiptComponent } from './SubscriberManagement/Receipts/update-receipt/update-receipt.component';
import { ViewReceiptComponent } from './SubscriberManagement/Receipts/view-receipt/view-receipt.component';
import { DeleteReceiptComponent } from './SubscriberManagement/Receipts/delete-receipt/delete-receipt.component';
//Commitment Chit
import { AddCommitmentChitComponent } from './SubscriberManagement/CommitmentChit/add-commitment-chit/add-commitment-chit.component';
import { UpdateCommitmentChitComponent } from './SubscriberManagement/CommitmentChit/update-commitment-chit/update-commitment-chit.component';
import { DeleteCommitmentChitComponent } from './SubscriberManagement/CommitmentChit/delete-commitment-chit/delete-commitment-chit.component';
import { ViewCommitmentChitComponent } from './SubscriberManagement/CommitmentChit/view-commitment-chit/view-commitment-chit.component';
//Commitment Payment
import { AddCommitmentPaymentComponent } from './SubscriberManagement/CommitmentPayment/add-commitment-payment/add-commitment-payment.component';
import { DeleteCommitmentPaymentComponent } from './SubscriberManagement/CommitmentPayment/delete-commitment-payment/delete-commitment-payment.component';
import { UpdateCommitmentPaymentComponent } from './SubscriberManagement/CommitmentPayment/update-commitment-payment/update-commitment-payment.component';
import { ViewCommitmentPaymentComponent } from './SubscriberManagement/CommitmentPayment/view-commitment-payment/view-commitment-payment.component';
//Enrol Refund
import { AddEnrolRefundComponent } from './SubscriberManagement/EnrolmentRefundAmount/add-enrol-refund/add-enrol-refund.component';
import { UpdateEnrolRefundComponent } from './SubscriberManagement/EnrolmentRefundAmount/update-enrol-refund/update-enrol-refund.component';
import { DeleteEnrolRefundComponent } from './SubscriberManagement/EnrolmentRefundAmount/delete-enrol-refund/delete-enrol-refund.component';
import { ViewEnrolRefundComponent } from './SubscriberManagement/EnrolmentRefundAmount/view-enrol-refund/view-enrol-refund.component';
//Passbook
import { PassbookEntryComponent } from './SubscriberManagement/Passbook/passbook-entry/passbook-entry.component';
import { PassbookIndexComponent } from './SubscriberManagement/Passbook/passbook-index/passbook-index.component';
import { PassbookTransactionComponent } from './SubscriberManagement/Passbook/passbook-transaction/passbook-transaction.component';
import { PassbookTransactionIndexComponent } from './SubscriberManagement/Passbook/passbook-transaction-index/passbook-transaction-index.component';
//Petty Cash Expense
import { AddPettyCashExpenseComponent } from './Accounts/PettyCashExpense/add-petty-cash-expense/add-petty-cash-expense.component';
import { ViewPettyCashExpenseComponent } from './Accounts/PettyCashExpense/view-petty-cash-expense/view-petty-cash-expense.component';
import { UpdatePettyCashExpenseComponent } from './Accounts/PettyCashExpense/update-petty-cash-expense/update-petty-cash-expense.component';
import { DeletePettyCashExpenseComponent } from './Accounts/PettyCashExpense/delete-petty-cash-expense/delete-petty-cash-expense.component';
//Petty Cash Income
import { AddPettyCashIncomeComponent } from './Accounts/PettyCashIncome/add-petty-cash-income/add-petty-cash-income.component';
import { ViewPettyCashIncomeComponent } from './Accounts/PettyCashIncome/view-petty-cash-income/view-petty-cash-income.component';
import { UpdatePettyCashIncomeComponent } from './Accounts/PettyCashIncome/update-petty-cash-income/update-petty-cash-income.component';
import { DeletePettyCashIncomeComponent } from './Accounts/PettyCashIncome/delete-petty-cash-income/delete-petty-cash-income.component';
//Employee Attendance
import { AddEmployeeAttendanceComponent } from './EmployeeManagement/EmployeeAttendance/add-employee-attendance/add-employee-attendance.component';
import { UpdateEmployeeAttendanceComponent } from './EmployeeManagement/EmployeeAttendance/update-employee-attendance/update-employee-attendance.component';
import { ViewEmployeeAttendanceComponent } from './EmployeeManagement/EmployeeAttendance/view-employee-attendance/view-employee-attendance.component';
import { DeleteEmployeeAttendanceComponent } from './EmployeeManagement/EmployeeAttendance/delete-employee-attendance/delete-employee-attendance.component';
//Employee Login
import { AddEmployeeLoginComponent } from './EmployeeManagement/EmployeeLogin/add-employee-login/add-employee-login.component';
import { UpdateEmployeeLoginComponent } from './EmployeeManagement/EmployeeLogin/update-employee-login/update-employee-login.component';
import { ViewEmployeeLoginComponent } from './EmployeeManagement/EmployeeLogin/view-employee-login/view-employee-login.component';
import { DeleteEmployeeLoginComponent } from './EmployeeManagement/EmployeeLogin/delete-employee-login/delete-employee-login.component';
//Document
import { AddDocumentComponent } from './TransactionManagement/Documents/add-document/add-document.component';
import { UpdateDocumentComponent } from './TransactionManagement/Documents/update-document/update-document.component';
import { ViewDocumentComponent } from './TransactionManagement/Documents/view-document/view-document.component';
import { DeleteDocumentComponent } from './TransactionManagement/Documents/delete-document/delete-document.component';
//Lead Management
import { AddLeadManagementComponent } from './LeadManager/Lead/add-lead-management/add-lead-management.component';
import { ViewLeadManagementComponent } from './LeadManager/Lead/view-lead-management/view-lead-management.component';
import { UpdateLeadManagementComponent } from './LeadManager/Lead/update-lead-management/update-lead-management.component';
import { DeleteLeadManagementComponent } from './LeadManager/Lead/delete-lead-management/delete-lead-management.component';
import { MoveToCustomerComponent } from './LeadManager/Lead/move-to-customer/move-to-customer.component';
//Followup
import { AddFollowupsComponent } from './LeadManager/Followup/add-followups/add-followups.component';
import { ViewFollowupsComponent } from './LeadManager/Followup/view-followups/view-followups.component';
import { UpdateFollowupsComponent } from './LeadManager/Followup/update-followups/update-followups.component';
import { DeleteFollowupsComponent } from './LeadManager/Followup/delete-followups/delete-followups.component';
//Reports
//Payment Reports
import { PrizedMoneyPendingReportComponent } from './Reports/PaymentReport/prized-money-pending-report/prized-money-pending-report.component';
import { PrizedMoneyReleasedReportComponent } from './Reports/PaymentReport/prized-money-released-report/prized-money-released-report.component';
//Outstanding Reports
import { CustomerwiseOutstandingReportComponent } from './Reports/OutstandingReport/customerwise-outstanding-report/customerwise-outstanding-report.component';
import { EmployeewiseOutstandingReportComponent } from './Reports/OutstandingReport/employeewise-outstanding-report/employeewise-outstanding-report.component';
import { GroupewiseOutstandingReportComponent } from './Reports/OutstandingReport/groupewise-outstanding-report/groupewise-outstanding-report.component';
//Other Reports
import { EnrollmentReportComponent } from './Reports/enrollment-report/enrollment-report.component';
import { VacantReportComponent } from './Reports/vacant-report/vacant-report.component';
import { AuctionReportComponent } from './Reports/auction-report/auction-report.component';
import { ReceiptReportComponent } from './Reports/receipt-report/receipt-report.component';
import { PaymentRefundReportComponent } from './Reports/payment-refund-report/payment-refund-report.component';
//Commission Payment
import { AddCommissionPaymentComponent } from './TransactionManagement/CommissionPayment/add-commission-payment/add-commission-payment.component';
import { UpdateCommissionPaymentComponent } from './TransactionManagement/CommissionPayment/update-commission-payment/update-commission-payment.component';
import { ViewCommissionPaymentComponent } from './TransactionManagement/CommissionPayment/view-commission-payment/view-commission-payment.component';
import { DeleteCommissionPaymentComponent } from './TransactionManagement/CommissionPayment/delete-commission-payment/delete-commission-payment.component';
//Dynamic Menu
import { AddDynamicMenuComponent } from './MenuManager/add-dynamic-menu/add-dynamic-menu.component';
import { UpdateDynamicMenuComponent } from './MenuManager/update-dynamic-menu/update-dynamic-menu.component';
import { ViewDynamicMenuComponent } from './MenuManager/view-dynamic-menu/view-dynamic-menu.component';
import { DeleteDynamicMenuComponent } from './MenuManager/delete-dynamic-menu/delete-dynamic-menu.component';
//Security Permission
import { AddPermissionComponent } from './SecurityPermission/add-permission/add-permission.component';
import { UpdatePermissionComponent } from './SecurityPermission/update-permission/update-permission.component';
import { ViewPermissionComponent } from './SecurityPermission/view-permission/view-permission.component';
import { DeletePermissionComponent } from './SecurityPermission/delete-permission/delete-permission.component';
//Commitment Payment Report
import { CommitmentPaymentReportComponent } from './Reports/commitment-payment-report/commitment-payment-report/commitment-payment-report.component';
//Commission Payment Report
import { CommissionPaymentReportComponent } from './Reports/commission-payment-report/commission-payment-report/commission-payment-report.component';
//Commission amount pending Report
import { CommissionAmountPendingReportComponent } from './Reports/commission-amount-pending-report/commission-amount-pending-report/commission-amount-pending-report.component';
//Business Agent Report
import { BusinessAgentReportComponent } from './Reports/business-agent/business-agent-report/business-agent-report.component';
//Accounts Report
import { AccountsReportComponent } from './Reports/accounts-report/accounts-report/accounts-report.component';
//Day Closing Report
import { DayClosingReportComponent } from './Reports/DayClosingReport/day-closing-report/day-closing-report.component';
import { ViewDayClosingComponent } from './Reports/DayClosingReport/view-day-closing/view-day-closing.component';
//Commitment Receipt Report
import { AddCommitmentReceiptComponent } from './SubscriberManagement/CommitmentReceipts/add-commitment-receipt/add-commitment-receipt.component';
import { DeleteCommitmentReceiptComponent } from './SubscriberManagement/CommitmentReceipts/delete-commitment-receipt/delete-commitment-receipt.component';
//Bid Payment Adjustment
import { AddBidpaymentAdjustmentComponent } from './SubscriberManagement/BidPayment-Adjustments/add-bidpayment-adjustment/add-bidpayment-adjustment.component';
//Advanced Receipts
import { AddAdvancedReceiptComponent } from './SubscriberManagement/AdvancedReceipt/add-advanced-receipt/add-advanced-receipt.component';
import { UpdateAdvancedReceiptComponent } from './SubscriberManagement/AdvancedReceipt/update-advanced-receipt/update-advanced-receipt.component';
import { DeleteAdvancedReceiptComponent } from './SubscriberManagement/AdvancedReceipt/delete-advanced-receipt/delete-advanced-receipt.component';
import { ViewAdvancedReceiptComponent } from './SubscriberManagement/AdvancedReceipt/view-advanced-receipt/view-advanced-receipt.component';
//Advanced Receipt Report
import { AdvancedReceiptReportComponent } from './Reports/advanced-receipt/advanced-receipt-report/advanced-receipt-report.component';
//Available Advance Amount Report
import { AvailableAdvanceAmountReportComponent } from './Reports/available-advance-amount/available-advance-amount-report/available-advance-amount-report.component';
//Interested Customer Report
import { InterestedCustomerReportComponent } from './Reports/interested-customer/interested-customer-report/interested-customer-report.component';
//Collection Area Mapping
import { AddCollectionAreaMappingComponent } from './EmployeeManagement/Collection-area-mapping/add-collection-area-mapping/add-collection-area-mapping.component';
import { UpdateCollectionAreaMappingComponent } from './EmployeeManagement/Collection-area-mapping/update-collection-area-mapping/update-collection-area-mapping.component';
import { DeleteCollectionAreaMappingComponent } from './EmployeeManagement/Collection-area-mapping/delete-collection-area-mapping/delete-collection-area-mapping.component';
import { ViewCollectionAreaMappingComponent } from './EmployeeManagement/Collection-area-mapping/view-collection-area-mapping/view-collection-area-mapping.component';
//Other Receipt Charges
import { AddOtherReceiptChargesComponent } from './SubscriberManagement/otherReceiptCharges/add-other-receipt-charges/add-other-receipt-charges.component';
import { UpdateOtherReceiptChargesComponent } from './SubscriberManagement/otherReceiptCharges/update-other-receipt-charges/update-other-receipt-charges.component';
import { DeleteOtherReceiptChargesComponent } from './SubscriberManagement/otherReceiptCharges/delete-other-receipt-charges/delete-other-receipt-charges.component';
import { ViewOtherReceiptChargesComponent } from './SubscriberManagement/otherReceiptCharges/view-other-receipt-charges/view-other-receipt-charges.component';
//Other Receipt Charges Report
import { OtherReceiptChargesReportComponent } from './Reports/other-Receipt-Charges/other-receipt-charges-report/other-receipt-charges-report.component';
//Commitment Pending Report
import { CommitmentPendingReportComponent } from './Reports/commitment-pending/commitment-pending-report/commitment-pending-report.component';
import { CommitmentEligibilityApprovalComponent } from './Reports/commitment-eligibility-approval/commitment-eligibility-approval.component';
//Customer Ledger
import { CustomerLedgerComponent } from './TransactionManagement/customer-ledger/customer-ledger.component';
import { AddDuplicateReceiptComponent } from './SubscriberManagement/DuplicateReceipt/add-duplicate-receipt/add-duplicate-receipt.component';
//Password Change
import { PasswordChangeComponent } from './EmployeeManagement/PasswordChange/password-change/password-change.component';
//Receipt Print
import { ReceiptPrintComponent } from './TransactionManagement/receipt-print/receipt-print.component';
//Feedback Type
import { AddFeedbackTypeComponent } from './Masters/FeedBackTypes/add-feedback-type/add-feedback-type.component';
import { UpdateFeedbackTypeComponent } from './Masters/FeedBackTypes/update-feedback-type/update-feedback-type.component';
import { DeleteFeedbackTypeComponent } from './Masters/FeedBackTypes/delete-feedback-type/delete-feedback-type.component';
import { ViewFeedbackTypeComponent } from './Masters/FeedBackTypes/view-feedback-type/view-feedback-type.component';
//Collection Employee Feedback
import { AddFeedbackComponent } from './SubscriberManagement/CollectionActivity/add-feedback/add-feedback.component';
import { UpdateFeedbackComponent } from './SubscriberManagement/CollectionActivity/update-feedback/update-feedback.component';
import { ViewFeedbackComponent } from './SubscriberManagement/CollectionActivity/view-feedback/view-feedback.component';
import { DeleteFeedbackComponent } from './SubscriberManagement/CollectionActivity/delete-feedback/delete-feedback.component';
//Commitmentcustomer Outstanding Report
import { CommitmentcustomerOutstandingComponent } from './Reports/OutstandingReport/commitmentcustomer-outstanding-report/commitmentcustomer-outstanding/commitmentcustomer-outstanding.component';
//Commitmentcustomer Receipt Report
import { CommitmentReceiptReportComponent } from './Reports/commitment-receipt-report/commitment-receipt-report.component';
//Loan Module
import { AddLoanComponent } from './SubscriberManagement/LoanDetails/add-loan/add-loan.component';
import { UpdateLoanComponent } from './SubscriberManagement/LoanDetails/update-loan/update-loan.component';
import { DeleteLoanComponent } from './SubscriberManagement/LoanDetails/delete-loan/delete-loan.component';
import { ViewLoanComponent } from './SubscriberManagement/LoanDetails/view-loan/view-loan.component';
//Loan receipt
import { AddLoanReceiptComponent } from './SubscriberManagement/LoanReceipt/add-loan-receipt/add-loan-receipt.component';
import { UpdateLoanReceiptComponent } from './SubscriberManagement/LoanReceipt/update-loan-receipt/update-loan-receipt.component';
import { ViewLoanReceiptComponent } from './SubscriberManagement/LoanReceipt/view-loan-receipt/view-loan-receipt.component';
import { DeleteLoanReceiptComponent } from './SubscriberManagement/LoanReceipt/delete-loan-receipt/delete-loan-receipt.component';
//Loan receipt report
import { LoanReceiptReportComponent } from './Reports/loan-receipt-report/loan-receipt-report.component';
//Log Monitoring Report
import { LogMonitoringReportComponent } from './Reports/log-monitoring-report/log-monitoring-report.component';
//Employee Incentive
import { AddEmployeeIncentiveComponent } from './TransactionManagement/EmployeeIncentive/add-employee-incentive/add-employee-incentive.component';
import { UpdateEmployeeIncentiveComponent } from './TransactionManagement/EmployeeIncentive/update-employee-incentive/update-employee-incentive.component';
import { ViewEmployeeIncentiveComponent } from './TransactionManagement/EmployeeIncentive/view-employee-incentive/view-employee-incentive.component';
import { DeleteEmployeeIncentiveComponent } from './TransactionManagement/EmployeeIncentive/delete-employee-incentive/delete-employee-incentive.component';
//Employee Incentive Report
import { IncentivePaymentReportComponent } from './Reports/incentive-payment-report/incentive-payment-report.component';
import { IncentivePendingReportComponent } from './Reports/incentive-pending-report/incentive-pending-report.component';
//Lead Management Report
import { LeadReportComponent } from './Reports/lead-management-report/lead-report/lead-report.component';
//Source Type
import { AddSourceTypeComponent } from './Masters/SourceType/add-source-type/add-source-type.component';
import { UpdateSourceTypeComponent } from './Masters/SourceType/update-source-type/update-source-type.component';
import { ViewSourceTypeComponent } from './Masters/SourceType/view-source-type/view-source-type.component';
import { DeleteSourceTypeComponent } from './Masters/SourceType/delete-source-type/delete-source-type.component';
//Loan Customer Report
import { LoanCustomerReportComponent } from './Reports/loan-customer-report/loan-customer-report.component';
//Loan Customerwise Outstanding Report
import { LoanCustomerwiseOutstandingReportComponent } from './Reports/OutstandingReport/loan-customerwise-outstanding-report/loan-customerwise-outstanding-report.component';
//Loan Day Closing Report
import { LoanDayClosingReportComponent } from './Reports/loan-day-closing-report/loan-day-closing-report.component';
//Auction Profit Report
import { CommissionProfitReportComponent } from './Reports/commission-profit-report/commission-profit-report.component';
//Loan Customer Document
import { AddLoanCustomerDocumentComponent } from './TransactionManagement/LoanCustomerDocument/add-loan-customer-document/add-loan-customer-document.component';
import { UpdateLoanCustomerDocumentComponent } from './TransactionManagement/LoanCustomerDocument/update-loan-customer-document/update-loan-customer-document.component';
import { ViewLoanCustomerDocumentComponent } from './TransactionManagement/LoanCustomerDocument/view-loan-customer-document/view-loan-customer-document.component';
import { DeleteLoanCustomerDocumentComponent } from './TransactionManagement/LoanCustomerDocument/delete-loan-customer-document/delete-loan-customer-document.component';
//Employee Salary
import { EmployeeSalaryComponent } from './EmployeeManagement/EmployeeSalary/employee-salary/employee-salary.component';
import { UpdateEmployeeSalaryComponent } from './EmployeeManagement/EmployeeSalary/update-employee-salary/update-employee-salary.component';
import { ViewEmployeeSalaryComponent } from './EmployeeManagement/EmployeeSalary/view-employee-salary/view-employee-salary.component';
import { DeleteEmployeeSalaryComponent } from './EmployeeManagement/EmployeeSalary/delete-employee-salary/delete-employee-salary.component';
//Accounting Group
import { AddAccountingGroupComponent } from './Masters/Accounting Group/add-accounting-group/add-accounting-group.component';
import { UpdateAccountingGroupComponent } from './Masters/Accounting Group/update-accounting-group/update-accounting-group.component';
import { ViewAccountingGroupComponent } from './Masters/Accounting Group/view-accounting-group/view-accounting-group.component';
import { DeleteAccountingGroupComponent } from './Masters/Accounting Group/delete-accounting-group/delete-accounting-group.component';
//Accounting Head
import { AddAccountingHeadComponent } from './Masters/Accounting Head/add-accounting-head/add-accounting-head.component';
import { UpdateAccountingHeadComponent } from './Masters/Accounting Head/update-accounting-head/update-accounting-head.component';
import { ViewAccountingHeadComponent } from './Masters/Accounting Head/view-accounting-head/view-accounting-head.component';
import { DeleteAccountingHeadComponent } from './Masters/Accounting Head/delete-accounting-head/delete-accounting-head.component';
//Transaction Expenses / Income
import { AddExpensesIncomeComponent } from './TransactionManagement/ExpencesIncome/add-expenses-income/add-expenses-income.component';
import { UpdateExpensesIncomeComponent } from './TransactionManagement/ExpencesIncome/update-expenses-income/update-expenses-income.component';
import { ViewExpensesIncomeComponent } from './TransactionManagement/ExpencesIncome/view-expenses-income/view-expenses-income.component';
import { DeleteExpensesIncomeComponent } from './TransactionManagement/ExpencesIncome/delete-expenses-income/delete-expenses-income.component';
//Transaction Expenses / Income Report
import { AccountExpensesReportComponent } from './Reports/Account-expenses/account-expenses-report/account-expenses-report.component';
//Trial Balance
import { TrialBalanceComponent } from './Reports/Trial-balance/trial-balance/trial-balance.component';
//Profit Loss Report
import { ProfitLossReportComponent } from './Reports/Profit-Loss-report/profit-loss-report/profit-loss-report.component';
//Unknown Transaction
import { UnknownTransactionComponent } from './TransactionManagement/UnknownTransaction/unknown-transaction/unknown-transaction.component';
import { UpdateUnknownTransactionComponent } from './TransactionManagement/UnknownTransaction/update-unknown-transaction/update-unknown-transaction.component';
import { ViewUnknownTransactionComponent } from './TransactionManagement/UnknownTransaction/view-unknown-transaction/view-unknown-transaction.component';
import { DeleteUnknownTransactionComponent } from './TransactionManagement/UnknownTransaction/delete-unknown-transaction/delete-unknown-transaction.component';
//Balance Sheet
import { BalanceSheetReportComponent } from './Reports/BalanceSheet/balance-sheet-report/balance-sheet-report.component';
//Day Book
import { DayBookComponent } from './Reports/Day-book/day-book/day-book.component';
//Commitment Enrolrefund Amount
import { AddCommitmentenrolRefundComponent } from './SubscriberManagement/CommitmentEnrolmentRefundAmount/add-commitmentenrol-refund/add-commitmentenrol-refund.component';
//installmentwise report
import { InstallmentwiseReportComponent } from './Reports/OutstandingReport/installmentwise-outstanding-report/installmentwise-report/installmentwise-report.component';
//before enrol receipt
import { BeforeEnrolReceiptComponent } from './SubscriberManagement/BeforeEnrollReceipt/before-enrol-receipt/before-enrol-receipt.component';
//before enrol receipt report
import { BeforeEnrolReceiptReportComponent } from './Reports/before-enrol-receipt/before-enrol-receipt-report/before-enrol-receipt-report.component';
//minutes of auction
import { MinutesOfAuctionComponent } from './SubscriberManagement/MinutesOfAuction/minutes-of-auction/minutes-of-auction.component';
import { ViewMinutesOfAuctionComponent } from './SubscriberManagement/MinutesOfAuction/view-minutes-of-auction/view-minutes-of-auction.component';
//minutes of auction report
import { AuctionMinutesReportComponent } from './Reports/auction-minutes-report/auction-minutes-report/auction-minutes-report.component';
//customer birthday report
import { CustomerBirthdayComponent } from './Reports/customer-birthday-report/customer-birthday/customer-birthday.component';
//sms option
import { SendMessageComponent } from './SubscriberManagement/SMS/send-message/send-message.component';
//birthday mail and message
import { BirthdayMailComponent } from './Reports/customer-birthday-report/birthday-mail/birthday-mail/birthday-mail.component';
import { BirthdayMessageComponent } from './Reports/customer-birthday-report/birthday-message/birthday-message/birthday-message.component';
//Add Country
import { AddCountryComponent } from './Masters/Country/add-country/add-country.component';
import { UpdateCountryComponent } from './Masters/Country/update-country/update-country.component';
import { ViewCountryComponent } from './Masters/Country/view-country/view-country.component';
import { DeleteCountryComponent } from './Masters/Country/delete-country/delete-country.component';
//receipt print
import { AdvanceReceiptPrintComponent } from './TransactionManagement/advance-receipt-print/advance-receipt-print.component';
import { BeforeAdvanceReceiptPrintComponent } from './TransactionManagement/before-advance-receipt-print/before-advance-receipt-print.component';
//nominee
import { AddNomineeComponent } from './Masters/Nominee/add-nominee/add-nominee.component';
import { UpdateNomineeComponent } from './Masters/Nominee/update-nominee/update-nominee.component';
import { ViewNomineeComponent } from './Masters/Nominee/view-nominee/view-nominee.component';
import { DeleteNomineeComponent } from './Masters/Nominee/delete-nominee/delete-nominee.component';
//pledge customer
import { PledgeCustomerComponent } from './SubscriberManagement/pledge-customer/pledge-customer.component';
import { UpdatePledgeCustomerComponent } from './SubscriberManagement/update-pledge-customer/update-pledge-customer.component';
import { PledgeCustomerReportComponent } from './Reports/pledge-customer-report/pledge-customer-report.component';
//transfer customer report
import { TransferCustomerReportComponent } from './Reports/transfer-report/transfer-customer-report/transfer-customer-report.component';
import { AddTransferCustomerComponent } from './TransactionManagement/TransferCustomer/add-transfer-customer/add-transfer-customer.component';
//Bid Cancellation Letter
import { BidCancelationLetterComponent } from './Forms/BidCancellationLetter/bid-cancelation-letter/bid-cancelation-letter.component';
//payment voucher
import { PaymentVoucherComponent } from './SubscriberManagement/Payment/payment-voucher/payment-voucher.component';
//gst voucher
import { GstVoucherComponent } from './Forms/gst-voucher/gst-voucher.component';
import { GstPrintComponent } from './Forms/gst-print/gst-print.component';
//cheque report
import { ChequeInHandComponent } from './Forms/cheque-in-hand/cheque-in-hand.component';
import { ChequeInBankComponent } from './Forms/cheque-in-bank/cheque-in-bank.component';
import { ChequeIssueComponent } from './Forms/cheque-issue/cheque-issue.component';
//fd report
import { FdReportComponent } from './Reports/fd-report/fd-report.component';
//termination report
import { TerminationGroupReportComponent } from './Reports/termination-group-report/termination-group-report.component';
//add bid advance
import { AddBidAdvanceComponent } from './SubscriberManagement/add-bid-advance/add-bid-advance.component';
//sms content master
import { SmsContentComponent } from './Masters/SmsContent/sms-content/sms-content.component';
import { ViewSmsTemplateComponent } from './Masters/SmsContent/view-sms-template/view-sms-template.component';
import { DeleteSmsTemplateComponent } from './Masters/SmsContent/delete-sms-template/delete-sms-template.component';
//groupwise abstract
import { GroupwiseOutstandingAbstractComponent } from './Reports/OutstandingReport/groupwise-outstanding-abstract/groupwise-outstanding-abstract.component';
//current month collection
import { CurrentMonthCollectionComponent } from './Reports/current-month-collection/current-month-collection/current-month-collection.component';
import { HistoricalPerformanceReportComponent } from './Reports/historical-performance-report/historical-performance-report.component';
import { BusinessForecastReportComponent } from './Reports/business-forecast-report/business-forecast-report.component';
//unenrollment report
import { UnenrollmentReportComponent } from './Reports/unenrollment-report/unenrollment-report.component';
//starting group report
import { StartingGroupReportComponent } from './Reports/starting-group-report/starting-group-report.component';
//Family Code
import { AddFamilyCodeComponent } from './Masters/FamilyCode/add-family-code/add-family-code.component';
import { UpdateFamilyCodeComponent } from './Masters/FamilyCode/update-family-code/update-family-code.component';
import { DeleteFamilyCodeComponent } from './Masters/FamilyCode/delete-family-code/delete-family-code.component';
import { ViewFamilyCodeComponent } from './Masters/FamilyCode/view-family-code/view-family-code.component';
//default interest
import { GroupAbstractDefaultInterestComponent } from './Reports/group-abstract-default-interest/group-abstract-default-interest.component';
//family code balance sheet
import { FamilyCodeWiseBalancesheetComponent } from './Reports/family-code-wise-balancesheet/family-code-wise-balancesheet.component';
//delay list
import { DelayListReportComponent } from './Reports/delay-list-report/delay-list-report.component';
//familycode wise due list
import { FamilyCodeWiseDueListComponent } from './Reports/family-code-wise-due-list/family-code-wise-due-list.component';
//customer label list
import { CustomerLabelListComponent } from './Reports/customer-label-list/customer-label-list.component';
//Bidding Form
import { BiddingFormComponent } from './Reports/bidding-form-report/bidding-form/bidding-form.component';
//Bidding Customer
import { BiddingCustomerComponent } from './Reports/bidding-customer/bidding-customer/bidding-customer.component';
import { LastAuctionDetailsComponent } from './Reports/bidding-customer/last-auction-details/last-auction-details.component';
import { LogViewComponent } from './Reports/bidding-customer/log-view/log-view.component';
import { LogBiddingComponent } from './Reports/bidding-customer/log-bidding/log-bidding.component';
//Bid Delete
import { BiddingDeleteComponent } from './Reports/bidding-form-report/bidding-delete/bidding-delete/bidding-delete.component';
//employee collection report
import { EmployeeCollectionReportComponent } from './Reports/employee-collection-report/employee-collection-report.component';
//other receipt print
import { OtherReceiptPrintComponent } from './SubscriberManagement/otherReceiptCharges/other-receipt-print/other-receipt-print.component';
//passbook before print
import { PassbookBeforeEntryComponent } from './SubscriberManagement/passbookbeforeprint/passbook-before-entry/passbook-before-entry.component';
import { PassbookBeforeIndexComponent } from './SubscriberManagement/passbookbeforeprint/passbook-before-index/passbook-before-index.component';
import { PassbookBeforeTransactionComponent } from './SubscriberManagement/passbookbeforeprint/passbook-before-transaction/passbook-before-transaction.component';
import { BeforePassbookTransactionIndexComponent } from './SubscriberManagement/passbookbeforeprint/before-passbook-transaction-index/before-passbook-transaction-index.component';
//sms sales promotion
import { SalesPromotionSmsComponent } from './SalesPromotion/sales-promotion-sms/sales-promotion-sms.component';
//Case Stages
import { AddCaseStageComponent } from './Masters/CaseStage/add-case-stage/add-case-stage.component';
import { ViewCaseStageComponent } from './Masters/CaseStage/view-case-stage/view-case-stage.component';
import { DeleteCaseStageComponent } from './Masters/CaseStage/delete-case-stage/delete-case-stage.component';
//Lawyer
import { AddLawyerComponent } from './Masters/Lawyer/add-lawyer/add-lawyer.component';
import { DeleteLawyerComponent } from './Masters/Lawyer/delete-lawyer/delete-lawyer.component';
import { ViewLawyerComponent } from './Masters/Lawyer/view-lawyer/view-lawyer.component';
//suit file report
import { SuitFileComponent } from './Reports/suit-file-report/suit-file/suit-file.component';
//Suit File
import { AddSuitFileComponent } from './Masters/SuitFile/add-suit-file/add-suit-file.component';
import { UpdateSuitFileComponent } from './Masters/SuitFile/update-suit-file/update-suit-file.component';
import { ViewSuitFileComponent } from './Masters/SuitFile/view-suit-file/view-suit-file.component';
//settlement report
import { SettlementReportComponent } from './Reports/settlement-report/settlement-report/settlement-report.component';
import { ReviewStatementReportComponent } from './Reports/review-statement-report/review-statement-report.component';
import { DueArrearReportComponent } from './Reports/due-arrear-report/due-arrear-report.component';
import { AuctionResultReportComponent } from './Reports/auction-result-report/auction-result-report.component';
import { MemberwiseOutstandingReportComponent } from './Reports/memberwise-outstanding-report/memberwise-outstanding-report.component';
import { AreawiseOutstandingReportComponent } from './Reports/areawise-outstanding-report/areawise-outstanding-report.component';
import { DailyCollectionStatementComponent } from './Reports/daily-collection-statement/daily-collection-statement.component';
import { AreawiseCollectableCollectionReportComponent } from './Reports/areawise-collectable-collection-report/areawise-collectable-collection-report.component';
import { AreawiseSummaryComponent } from './Reports/areawise-summary/areawise-summary.component';
import { DirectorwiseCollectableCollectionReportComponent } from './Reports/directorwise-collectable-collection-report/directorwise-collectable-collection-report.component';
import { AreawiseDueArrearReportComponent } from './Reports/areawise-due-arrear-report/areawise-due-arrear-report.component';
import { DirectorwiseDueArrearReportComponent } from './Reports/directorwise-due-arrear-report/directorwise-due-arrear-report.component';
import { GiftIssueComponent } from './TransactionManagement/gift-issue/gift-issue.component';
import { ClosureComponent } from './TransactionManagement/closure/closure.component';
import { AuctionResultlComponent } from './Reports/auction-resultl/auction-resultl.component';
import { DrawComponent } from './SubscriberManagement/draw/draw.component';
import { DebitCreditViewComponent } from './Reports/debit-credit-view/debit-credit-view.component';
import { GiftIssueViewComponent } from './TransactionManagement/gift-issue-view/gift-issue-view.component';
import { DrawReceiptComponent } from './SubscriberManagement/draw-receipt/draw-receipt.component';
import { AuctionValueComponent } from './Masters/auction-value/auction-value.component';
import { AuctionReportOriginalComponent } from './Reports/auction-report-original/auction-report-original.component';
import { DeleteAccountExpenseComponent } from './TransactionManagement/delete-account-expense/delete-account-expense.component';
import { ClosureReportComponent } from './Reports/closure-report/closure-report.component';
import { DrawReportComponent } from './Reports/draw-report/draw-report.component';
import { AddEmployeeDocumentComponent } from './TransactionManagement/EmployeeDocument/add-employee-document/add-employee-document.component';
import { ViewEmployeeDocumentComponent } from './TransactionManagement/EmployeeDocument/view-employee-document/view-employee-document.component';
import { OverallBusinessReportComponent } from './Reports/overall-business-report/overall-business-report.component';
import { CollectionCheckingReportComponent } from './Reports/collection-checking-report/collection-checking-report/collection-checking-report.component';
import { DebitCreditEditComponent } from './Reports/debit-credit-edit/debit-credit-edit.component';
import { PassbookVerifyComponent } from './Reports/passbook-verify/passbook-verify.component';
import { CompanyProfitReportComponent } from './Reports/company-profit-report/company-profit-report.component';
import { CompanyExcessReportComponent } from './Reports/company-excess-report/company-excess-report.component';
import { CancelCustomerReportComponent } from './Reports/cancel-customer-report/cancel-customer-report.component';
import { TargetIncentiveComponent } from './TransactionManagement/target-incentive/target-incentive.component';
import { AddPigmyCollectionComponent } from './SubscriberManagement/PigmyCollection/add-pigmy-collection/add-pigmy-collection.component';
import { TaskManagementComponent } from './EmployeeManagement/task-management/task-management.component';
import { PigmyReportComponent } from './Reports/pigmy-report/pigmy-report.component';
import { TaskReportComponent } from './Reports/task-report/task-report.component';
import { CommitmentReceiptPrintComponent } from './TransactionManagement/commitment-receipt-print/commitment-receipt-print.component';
import { BeforeDeleteReceiptComponent } from './SubscriberManagement/before-delete-receipt/before-delete-receipt.component';
import { RefundTransferReportComponent } from './Reports/refund-transfer-report/refund-transfer-report.component';
import { AuctionReportFinalComponent } from './Reports/auction-report-final/auction-report-final.component';
import { TransferCustomerDetailsReportComponent } from './Reports/transfer-customer-details-report/transfer-customer-details-report.component';
import { CommitmentReportComponent } from './Reports/commitment-report/commitment-report/commitment-report.component';
import { CashbookComponent } from './Reports/cashbook/cashbook.component';
import { BankbookComponent } from './Reports/bankbook/bankbook.component';


// *******************************************************************************

@NgModule({
  exports: [
    SidenavModule,
  ],
  declarations: [],
  providers: []
})

export class DemoMaterialModule {}
@NgModule({
  imports: [
    //AgGridModule.withComponents([]),
    CommonModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule,
    adminpanelRoutingModule,
    NgbModule,
    NgxSmartModalModule,
    AgGridModule,
    NgMultiSelectDropDownModule.forRoot()
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA, NO_ERRORS_SCHEMA],
  declarations: [
    ReportPopupModalComponent,
    ReportRedirectModalComponent,
    ReportChequeSubmitComponent,
    ReportChequeBankComponent,
    //Dashboard
    DashboardComponent,
    //Masters
    //Bank Detail
    AddBankDetailsComponent,
    UpdateBankDetailsComponent,
    ViewBankDetailsComponent,
    DeleteBankDetailsComponent,
    //Branch
    AddBranchComponent,
    UpdateBranchComponent, 
    ViewBranchComponent,
    DeleteBranchComponent,
    //Branch Configuration
    AddBranchConfigurationComponent,
    UpdateBranchConfigurationComponent,
    ViewBranchConfigurationComponent,
    DeleteBranchConfigurationComponent,
    //Charges Type
    AddChargesTypeComponent, 
    UpdateChargesTypeComponent,
    ViewChargesTypeComponent, 
    DeleteChargesTypeComponent,
    //City
    AddCityComponent,
    UpdateCityComponent,
    ViewCityComponent,
    DeleteCityComponent,
    //Gift
    AddGiftComponent,
    UpdateGiftComponent,
    ViewGiftComponent,
    DeleteGiftComponent,
    //Collection Area
    AddCollectionAreaComponent,
    UpdateCollectionAreaComponent, 
    ViewCollectionAreaComponent,
    DeleteCollectionAreaComponent,
    //Customer
    AddCustomerComponent,
    UpdateCustomerComponent,
    ViewCustomerComponent,
    DeleteCustomerComponent,
    //Advocate Detail
    AddAdvocateDetailsComponent, 
    UpdateAdvocateDetailsComponent,
    ViewAdvocateDetailsComponent, 
    DeleteAdvocateDetailsComponent,
    //Approval Mapping
    AddApprovalMappingComponent,
    UpdateApprovalMappingComponent,
    ViewApprovalMappingComponent,
    DeleteApprovalMappingComponent,
    //Auction On Holiday
    AddAuctionOnHolidaysComponent,
    UpdateAuctionOnHolidaysComponent,
    ViewAuctionOnHolidaysComponent,
    DeleteAuctionOnHolidaysComponent, 
    //Designation
    AddDesignationComponent,
    UpdateDesignationComponent, 
    ViewDesignationComponent, 
    DeleteDesignationComponent,
    //District
    AddDistrictComponent,
    UpdateDistrictComponent,
    ViewDistrictComponent,
    DeleteDistrictComponent,
    //Employee
    AddEmployeeComponent,
    UpdateEmployeeComponent,
    ViewEmployeeComponent,
    DeleteEmployeeComponent,
    //Group
    AddGroupComponent,
    UpdateGroupComponent, 
    ViewGroupComponent,
    DeleteGroupComponent,
    //Guarantor
    AddGuarantorComponent, 
    UpdateGuarantorComponent, 
    ViewGuarantorComponent, 
    DeleteGuarantorComponent,
    //Head Office
    AddHeadOfficeComponent, 
    UpdateHeadOfficeComponent,
    ViewHeadOfficeComponent,
    DeleteHeadOfficeComponent,
    //Hierarchy
    AddHierarchyComponent,
    UpdateHierarchyComponent,
    ViewHierarchyComponent,
    DeleteHierarchyComponent,
    //Ledger
    AddLedgerComponent,
    UpdateLedgerComponent,
    ViewLedgerComponent,
    DeleteLedgerComponent,
    //Petty Cash Expense Type
    AddPettyCashExpenseTypeComponent, 
    UpdatePettyCashExpenseTypeComponent, 
    ViewPettyCashExpenseTypeComponent, 
    DeletePettyCashExpenseTypeComponent,
    //Relationship
    AddRelationshipComponent,
    UpdateRelationshipComponent, 
    ViewRelationshipComponent, 
    DeleteRelationshipComponent,
    //Role
    AddRoleComponent, 
    UpdateRoleComponent, 
    ViewRoleComponent,
    DeleteRoleComponent, 
    //Scheme
    AddSchemeComponent, 
    UpdateSchemeComponent, 
    ViewSchemeComponent, 
    DeleteSchemeComponent,
    //State
    AddStateComponent, 
    UpdateStateComponent,
    ViewStateComponent,
    DeleteStateComponent,
    //Surity Detail
    AddSuretyDetailsComponent, 
    UpdateSuretyDetailsComponent, 
    ViewSuretyDetailsComponent, 
    DeleteSuretyDetailsComponent,
    //Bonus Slab
    AddBonusSlabComponent,
    UpdateBonusSlabComponent,
    ViewBonusSlabComponent,
    DeleteBonusSlabComponent,
    //Due Slab  
    AddDueSlabComponent,
    UpdateDueSlabComponent,
    ViewDueSlabComponent,
    DeleteDueSlabComponent,
    //Interest Slab
    AddInterestSlabComponent,
    UpdateInterestSlabComponent,
    ViewInterestSlabComponent,
    DeleteInterestSlabComponent,
    //Penalty Slab
    AddPenaltySlabComponent,
    UpdatePenaltySlabComponent,
    ViewPenaltySlabComponent,
    DeletePenaltySlabComponent,
    //Payment Slab
    AddPaymentSlabComponent,
    UpdatePaymentSlabComponent,
    ViewPaymentSlabComponent,
    DeletePaymentSlabComponent,
    //Tenant
    AddTenantComponent, 
    UpdateTenantComponent, 
    ViewTenantComponent, 
    DeleteTenantComponent,
    //Customer Enrollment
    AddCustomerEnrollmentComponent, 
    UpdateCustomerEnrollmentComponent, 
    ViewCustomerEnrollmentComponent, 
    DeleteCustomerEnrollmentComponent,
    //Auction
    AddAuctionComponent, 
    UpdateAuctionComponent, 
    ViewAuctionComponent, 
    DeleteAuctionComponent,
    //Payment
    AddPaymentComponent, 
    UpdatePaymentComponent, 
    ViewPaymentComponent, 
    DeletePaymentComponent,
    //Receipt
    AddReceiptComponent,
    UpdateReceiptComponent, 
    ViewReceiptComponent,
    DeleteReceiptComponent,
    //Petty Cash Expense
    AddPettyCashExpenseComponent, 
    ViewPettyCashExpenseComponent, 
    UpdatePettyCashExpenseComponent, 
    DeletePettyCashExpenseComponent, 
    //Petty Cash Income
    AddPettyCashIncomeComponent, 
    ViewPettyCashIncomeComponent, 
    UpdatePettyCashIncomeComponent, 
    DeletePettyCashIncomeComponent,
    //Employee Attendance
    AddEmployeeAttendanceComponent, 
    UpdateEmployeeAttendanceComponent, 
    ViewEmployeeAttendanceComponent, 
    DeleteEmployeeAttendanceComponent,
    //Employee Login
    AddEmployeeLoginComponent, 
    UpdateEmployeeLoginComponent, 
    ViewEmployeeLoginComponent, 
    DeleteEmployeeLoginComponent,
    //Document
    AddDocumentComponent, 
    UpdateDocumentComponent, 
    ViewDocumentComponent, 
    DeleteDocumentComponent,
    //Lead Management
    AddLeadManagementComponent, 
    ViewLeadManagementComponent,
    UpdateLeadManagementComponent,
    DeleteLeadManagementComponent,
    //Followup
    AddFollowupsComponent,
    ViewFollowupsComponent,
    UpdateFollowupsComponent, 
    DeleteFollowupsComponent,
    MoveToCustomerComponent,
    //Reports
    //Payments Reports
    PrizedMoneyPendingReportComponent, 
    PrizedMoneyReleasedReportComponent,
    //Outstanding Reports
    CustomerwiseOutstandingReportComponent,
    EmployeewiseOutstandingReportComponent,
    GroupewiseOutstandingReportComponent, 
    //Other Reports
    EnrollmentReportComponent,
    VacantReportComponent,
    AuctionReportComponent,
    ReceiptReportComponent,
    //Commitment Chit
    AddCommitmentChitComponent, 
    UpdateCommitmentChitComponent,
    DeleteCommitmentChitComponent,
    ViewCommitmentChitComponent,
    //Commission payment
    AddCommissionPaymentComponent,
    UpdateCommissionPaymentComponent,
    ViewCommissionPaymentComponent,
    DeleteCommissionPaymentComponent,
	//Dynamic Menu
    AddDynamicMenuComponent,
    UpdateDynamicMenuComponent, 
    ViewDynamicMenuComponent, 
    DeleteDynamicMenuComponent,
    //Security Permission
    AddPermissionComponent, 
    UpdatePermissionComponent, 
    ViewPermissionComponent, 
    DeletePermissionComponent,
    //Commitment Payment
    AddCommitmentPaymentComponent,
    DeleteCommitmentPaymentComponent,
    UpdateCommitmentPaymentComponent,
    ViewCommitmentPaymentComponent,
    //Enrol Refund
    AddEnrolRefundComponent, 
    UpdateEnrolRefundComponent,
    DeleteEnrolRefundComponent,
    ViewEnrolRefundComponent,
    //Payment Refund Report
    PaymentRefundReportComponent,
    CommitmentPaymentReportComponent,
    CommissionPaymentReportComponent,
    NoAccessComponent,
    //Commission Amount Pending Report
    CommissionAmountPendingReportComponent,
	//Passbook Printing
    PassbookEntryComponent,
    PassbookIndexComponent, 
    PassbookTransactionComponent, 
    PassbookTransactionIndexComponent,
    //Business Agent Report
    BusinessAgentReportComponent,
    //Accounts Report
    AccountsReportComponent,
    //Day Closing Report
    DayClosingReportComponent,
    ViewDayClosingComponent,
    //Commitment Receipt Report
    AddCommitmentReceiptComponent, 
    DeleteCommitmentReceiptComponent,
    //BidPayment Adjustment
    AddBidpaymentAdjustmentComponent,
    //Advanced Receipts
    AddAdvancedReceiptComponent,
    UpdateAdvancedReceiptComponent, 
    DeleteAdvancedReceiptComponent,
    ViewAdvancedReceiptComponent,
    //Advanced Receipts
    AdvancedReceiptReportComponent,
    //Available Advance Amount Report
    AvailableAdvanceAmountReportComponent,
    //Interested Customer Report
    InterestedCustomerReportComponent,
    //Collection Area Mapping
    AddCollectionAreaMappingComponent,
    UpdateCollectionAreaMappingComponent,
    DeleteCollectionAreaMappingComponent,
    ViewCollectionAreaMappingComponent,
    //Other Receipt Charges
    AddOtherReceiptChargesComponent,
    UpdateOtherReceiptChargesComponent, 
    DeleteOtherReceiptChargesComponent,
    ViewOtherReceiptChargesComponent,
    //Other Receipt Charges Report
    OtherReceiptChargesReportComponent,
    //Commitment Pending Report
    CommitmentPendingReportComponent,
    CommitmentEligibilityApprovalComponent,
    //Customer ledger
    CustomerLedgerComponent,
    AddDuplicateReceiptComponent,
    PasswordChangeComponent,
   //ReceiptPrint
    ReceiptPrintComponent,
    //Feedback Type
    AddFeedbackTypeComponent,
    UpdateFeedbackTypeComponent,
    DeleteFeedbackTypeComponent,
    ViewFeedbackTypeComponent,
    //Collection Employee Feedback
    AddFeedbackComponent,
    UpdateFeedbackComponent,
    ViewFeedbackComponent,
    DeleteFeedbackComponent,
    //Commitmentcustomer Outstanding Report
    CommitmentcustomerOutstandingComponent,
    //Commitment Receipt Report
    CommitmentReceiptReportComponent,
    //Loan Module
    AddLoanComponent,
    UpdateLoanComponent,
    DeleteLoanComponent,
    ViewLoanComponent,
    //Loan Receipt
    AddLoanReceiptComponent,
    UpdateLoanReceiptComponent,
    ViewLoanReceiptComponent,
    DeleteLoanReceiptComponent,
    //Loan Receipt Report
    LoanReceiptReportComponent,
    //Log Monitoring Report
    LogMonitoringReportComponent,
    //Employee Incentive
    AddEmployeeIncentiveComponent,
    UpdateEmployeeIncentiveComponent,
    ViewEmployeeIncentiveComponent,
    DeleteEmployeeIncentiveComponent,
     //Employee Incentive Report
    IncentivePaymentReportComponent, 
    IncentivePendingReportComponent,
    //Lead Management Report
    LeadReportComponent,
    //SourceType
    AddSourceTypeComponent,
    UpdateSourceTypeComponent,
    ViewSourceTypeComponent,
    DeleteSourceTypeComponent,
    //Loan Customer Report
    LoanCustomerReportComponent,
    //Loan Customerwise Outstanding Report
    LoanCustomerwiseOutstandingReportComponent,
    //Loan Dayclosing Report
    LoanDayClosingReportComponent,
    //Auction Profit Report
    CommissionProfitReportComponent,
    //Loan Customer Document
    AddLoanCustomerDocumentComponent,
    UpdateLoanCustomerDocumentComponent,
    ViewLoanCustomerDocumentComponent, 
    DeleteLoanCustomerDocumentComponent,
    //Employee Salary
    EmployeeSalaryComponent,
    UpdateEmployeeSalaryComponent,
    ViewEmployeeSalaryComponent,
    DeleteEmployeeSalaryComponent,
    //Accounting Group
    AddAccountingGroupComponent,
    UpdateAccountingGroupComponent,
    ViewAccountingGroupComponent,
    DeleteAccountingGroupComponent,
    //Accounting Head
    AddAccountingHeadComponent,
    UpdateAccountingHeadComponent,
    ViewAccountingHeadComponent,
    DeleteAccountingHeadComponent,
    //Expenses Income
    AddExpensesIncomeComponent, 
    UpdateExpensesIncomeComponent, 
    ViewExpensesIncomeComponent, 
    DeleteExpensesIncomeComponent,
    //Expenses Income Report
    AccountExpensesReportComponent,
    //Trial Balance
    TrialBalanceComponent,
    //Profit Loss Report
    ProfitLossReportComponent,
    //Unknown Transaction
    UnknownTransactionComponent,
    UpdateUnknownTransactionComponent, 
    ViewUnknownTransactionComponent, 
    DeleteUnknownTransactionComponent,
    //Balance Sheet
    BalanceSheetReportComponent,
    //Day Book
    DayBookComponent,
    //Commitment Enrolrefund Amount
    AddCommitmentenrolRefundComponent,
    //installmentwise report
    InstallmentwiseReportComponent,
    //before enrol receipt
    BeforeEnrolReceiptComponent,
    //before enrol receipt report
    BeforeEnrolReceiptReportComponent,
    //minutes of auction
    MinutesOfAuctionComponent,
    ViewMinutesOfAuctionComponent,
    //minutes of auction report
    AuctionMinutesReportComponent,
    //customer birthday report
    CustomerBirthdayComponent,
    //sms 
    SendMessageComponent,
    //birthday mail and message
    BirthdayMailComponent,
    BirthdayMessageComponent,
    //Add Country
    AddCountryComponent,
    UpdateCountryComponent,
    ViewCountryComponent,
    DeleteCountryComponent,
    //receipt print
    AdvanceReceiptPrintComponent, 
    BeforeAdvanceReceiptPrintComponent,
    //nominee
    AddNomineeComponent, 
    UpdateNomineeComponent, 
    ViewNomineeComponent, 
    DeleteNomineeComponent,
	//pledge customer
    PledgeCustomerComponent,
    UpdatePledgeCustomerComponent, 
    PledgeCustomerReportComponent,
    //transfer customer report
    TransferCustomerReportComponent,
    AddTransferCustomerComponent,
    //Bid Cancelation Letter
    BidCancelationLetterComponent,
    //payment voucher
    PaymentVoucherComponent,
    //gst voucher
    GstVoucherComponent,
    GstPrintComponent,
    //cheque report
    ChequeInHandComponent, 
    ChequeInBankComponent, 
    ChequeIssueComponent,
    //fd report
    FdReportComponent,
    //termination report
    TerminationGroupReportComponent,
    //add bid advance
    AddBidAdvanceComponent,
    //sms content master
    SmsContentComponent,
    ViewSmsTemplateComponent,
    DeleteSmsTemplateComponent,
    //groupwise abstract
    GroupwiseOutstandingAbstractComponent,
    //current month collection
    CurrentMonthCollectionComponent,
    HistoricalPerformanceReportComponent,
    BusinessForecastReportComponent,
    //unenrollment report
    UnenrollmentReportComponent,
    //starting group report
    StartingGroupReportComponent,
    //Family Code
    AddFamilyCodeComponent, 
    UpdateFamilyCodeComponent, 
    DeleteFamilyCodeComponent, 
    ViewFamilyCodeComponent,
    //default interest
    GroupAbstractDefaultInterestComponent, 
    //family code balance sheet
    FamilyCodeWiseBalancesheetComponent,
    //delay list
    DelayListReportComponent, 
    //family code due list
    FamilyCodeWiseDueListComponent, 
    //customer label list
    CustomerLabelListComponent,
    //Bidding Form
    BiddingFormComponent,
    //Bidding Customer
    BiddingCustomerComponent,
    LastAuctionDetailsComponent, 
    LogViewComponent, 
    LogBiddingComponent,
    //Bid Delete
    BiddingDeleteComponent,
    //
    EmployeeCollectionReportComponent,
    //other receipt print
    OtherReceiptPrintComponent,
    //passbook before print
    PassbookBeforeEntryComponent,
    PassbookBeforeIndexComponent,
    PassbookBeforeTransactionComponent,
    BeforePassbookTransactionIndexComponent,
    //sms sales promotion
    SalesPromotionSmsComponent,
    //Case Stages
    AddCaseStageComponent, 
    ViewCaseStageComponent, 
    DeleteCaseStageComponent,
    //Lawyer
    AddLawyerComponent, 
    DeleteLawyerComponent, 
    ViewLawyerComponent,
    //suit file report
    SuitFileComponent,
    //Suit File
    AddSuitFileComponent,
	UpdateSuitFileComponent, 
    ViewSuitFileComponent, 
    //settlement report
    SettlementReportComponent,
    ReviewStatementReportComponent, 
    DueArrearReportComponent,
    AuctionResultReportComponent,
    MemberwiseOutstandingReportComponent,
    AreawiseOutstandingReportComponent, 
    DailyCollectionStatementComponent, 
    AreawiseCollectableCollectionReportComponent, 
    AreawiseSummaryComponent,
    DirectorwiseCollectableCollectionReportComponent,
    AreawiseDueArrearReportComponent, 
    DirectorwiseDueArrearReportComponent,
    GiftIssueComponent,
    ClosureComponent,
    AuctionResultlComponent,
    DrawComponent,
    DebitCreditViewComponent,
    GiftIssueViewComponent,
    DrawReceiptComponent,
    AuctionValueComponent,
    AuctionReportOriginalComponent,
    DeleteAccountExpenseComponent,
    ClosureReportComponent,
    DrawReportComponent,
    AddEmployeeDocumentComponent,
    ViewEmployeeDocumentComponent,
    OverallBusinessReportComponent,
    CollectionCheckingReportComponent,
    DebitCreditEditComponent,
    PassbookVerifyComponent,
    CompanyProfitReportComponent,
    CompanyExcessReportComponent,
    CancelCustomerReportComponent,
    TargetIncentiveComponent,
    AddPigmyCollectionComponent,
    TaskManagementComponent,
    PigmyReportComponent, 
    TaskReportComponent,
    CommitmentReceiptPrintComponent,
    BeforeDeleteReceiptComponent,
    RefundTransferReportComponent,
    TransferCustomerDetailsReportComponent,
    AuctionReportFinalComponent,
    CommitmentReportComponent,
    CashbookComponent, 
    BankbookComponent
],  
})

export class adminpanelModule
{ 

}
