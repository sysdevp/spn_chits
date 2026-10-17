import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { AuthGuardService } from '../Services/auth-guard.service';

// *******************************************************************************
import { NoAccessComponent } from './NoAccess/no-access.component';
// Dashboard
import { DashboardComponent } from './dashboard/dashboard.component';

// Masters 
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
import { DeleteAuctionOnHolidaysComponent } from './Masters/AuctionOnHolidays/delete-auction-on-holidays/delete-auction-on-holidays.component' ;
//Bank Detail
import { AddBankDetailsComponent } from './Masters/BankDetails/add-bank-details/add-bank-details.component';
import { UpdateBankDetailsComponent } from './Masters/BankDetails/update-bank-details/update-bank-details.component';
import { ViewBankDetailsComponent } from './Masters/BankDetails/view-bank-details/view-bank-details.component';
import { DeleteBankDetailsComponent } from './Masters/BankDetails/delete-bank-details/delete-bank-details.component' ;
//Branch
import { AddBranchComponent } from './Masters/Branch/add-branch/add-branch.component';
import { UpdateBranchComponent } from './Masters/Branch/update-branch/update-branch.component';
import { ViewBranchComponent } from './Masters/Branch/view-branch/view-branch.component';
import { DeleteBranchComponent } from './Masters/Branch/delete-branch/delete-branch.component' ;
//Branch Configuration
import { AddBranchConfigurationComponent } from './Masters/BranchConfiguration/add-branch-configuration/add-branch-configuration.component';
import { UpdateBranchConfigurationComponent } from './Masters/BranchConfiguration/update-branch-configuration/update-branch-configuration.component';
import { ViewBranchConfigurationComponent } from './Masters/BranchConfiguration/view-branch-configuration/view-branch-configuration.component';
import { DeleteBranchConfigurationComponent } from './Masters/BranchConfiguration/delete-branch-configuration/delete-branch-configuration.component';
//Charges Type
import { AddChargesTypeComponent } from './Masters/ChargesType/add-charges-type/add-charges-type.component';
import { UpdateChargesTypeComponent } from './Masters/ChargesType/update-charges-type/update-charges-type.component';
import { ViewChargesTypeComponent } from './Masters/ChargesType/view-charges-type/view-charges-type.component';
import { DeleteChargesTypeComponent } from './Masters/ChargesType/delete-charges-type/delete-charges-type.component' ;
//City
import { AddCityComponent } from './Masters/City/add-city/add-city.component';
import { UpdateCityComponent } from './Masters/City/update-city/update-city.component';
import { ViewCityComponent } from './Masters/City/view-city/view-city.component';
import { DeleteCityComponent } from './Masters/City/delete-city/delete-city.component' ;
//Gift
import { AddGiftComponent } from './Masters/Gift/add-gift/add-gift.component';
import { UpdateGiftComponent } from './Masters/Gift/update-gift/update-gift.component';
import { ViewGiftComponent } from './Masters/Gift/view-gift/view-gift.component';
import { DeleteGiftComponent } from './Masters/Gift/delete-gift/delete-gift.component';
//Collection Area
import { AddCollectionAreaComponent } from './Masters/CollectionArea/add-collection-area/add-collection-area.component';
import { UpdateCollectionAreaComponent } from './Masters/CollectionArea/update-collection-area/update-collection-area.component';
import { ViewCollectionAreaComponent } from './Masters/CollectionArea/view-collection-area/view-collection-area.component';
import { DeleteCollectionAreaComponent } from './Masters/CollectionArea/delete-collection-area/delete-collection-area.component' ;
//Customer
import { AddCustomerComponent } from './Masters/Customer/add-customer/add-customer.component';
import { UpdateCustomerComponent } from './Masters/Customer/update-customer/update-customer.component';
import { ViewCustomerComponent } from './Masters/Customer/view-customer/view-customer.component';
import { BranchTransferCustomerComponent } from './Masters/Customer/branch-transfer-customer/branch-transfer-customer.component';
import { BranchTransferCustomerReportComponent } from './Masters/Customer/branch-transfer-customer-report/branch-transfer-customer-report.component';
import { DeleteCustomerComponent } from './Masters/Customer/delete-customer/delete-customer.component';
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
import { DeleteGroupComponent } from './Masters/Group/delete-group/delete-group.component' ;
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
import { DeleteStateComponent } from './Masters/State/delete-state/delete-state.component' ;
//Surity Detail
import { AddSuretyDetailsComponent } from './Masters/SuretyDetails/add-surety-details/add-surety-details.component';
import { UpdateSuretyDetailsComponent } from './Masters/SuretyDetails/update-surety-details/update-surety-details.component';
import { ViewSuretyDetailsComponent } from './Masters/SuretyDetails/view-surety-details/view-surety-details.component';
import { DeleteSuretyDetailsComponent } from './Masters/SuretyDetails/delete-surety-details/delete-surety-details.component' ;
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
//Chit
import { AddCommitmentChitComponent } from './SubscriberManagement/CommitmentChit/add-commitment-chit/add-commitment-chit.component';
import { UpdateCommitmentChitComponent } from './SubscriberManagement/CommitmentChit/update-commitment-chit/update-commitment-chit.component';
import { DeleteCommitmentChitComponent } from './SubscriberManagement/CommitmentChit/delete-commitment-chit/delete-commitment-chit.component';
import { ViewCommitmentChitComponent } from './SubscriberManagement/CommitmentChit/view-commitment-chit/view-commitment-chit.component';
//Prized Payment
import { AddCommitmentPaymentComponent } from './SubscriberManagement/CommitmentPayment/add-commitment-payment/add-commitment-payment.component';
import { DeleteCommitmentPaymentComponent } from './SubscriberManagement/CommitmentPayment/delete-commitment-payment/delete-commitment-payment.component';
import { UpdateCommitmentPaymentComponent } from './SubscriberManagement/CommitmentPayment/update-commitment-payment/update-commitment-payment.component';
import { ViewCommitmentPaymentComponent } from './SubscriberManagement/CommitmentPayment/view-commitment-payment/view-commitment-payment.component';
//Enrol Refund
import { AddEnrolRefundComponent } from './SubscriberManagement/EnrolmentRefundAmount/add-enrol-refund/add-enrol-refund.component';
import { UpdateEnrolRefundComponent } from './SubscriberManagement/EnrolmentRefundAmount/update-enrol-refund/update-enrol-refund.component';
import { DeleteEnrolRefundComponent } from './SubscriberManagement/EnrolmentRefundAmount/delete-enrol-refund/delete-enrol-refund.component';
import { ViewEnrolRefundComponent } from './SubscriberManagement/EnrolmentRefundAmount/view-enrol-refund/view-enrol-refund.component';
//Passbook Printing
import { PassbookEntryComponent } from './SubscriberManagement/Passbook/passbook-entry/passbook-entry.component';
import { PassbookIndexComponent } from './SubscriberManagement/Passbook/passbook-index/passbook-index.component';
import { PassbookTransactionComponent } from './SubscriberManagement/Passbook/passbook-transaction/passbook-transaction.component';
import { PassbookTransactionIndexComponent } from './SubscriberManagement/Passbook/passbook-transaction-index/passbook-transaction-index.component';
//Petty Cash Expense
import { AddPettyCashExpenseComponent } from './Accounts/PettyCashExpense/add-petty-cash-expense/add-petty-cash-expense.component';
import { UpdatePettyCashExpenseComponent } from './Accounts/PettyCashExpense/update-petty-cash-expense/update-petty-cash-expense.component';
import { ViewPettyCashExpenseComponent } from './Accounts/PettyCashExpense/view-petty-cash-expense/view-petty-cash-expense.component';
import { DeletePettyCashExpenseComponent } from './Accounts/PettyCashExpense/delete-petty-cash-expense/delete-petty-cash-expense.component';
//Petty Cash Income
import { AddPettyCashIncomeComponent } from './Accounts/PettyCashIncome/add-petty-cash-income/add-petty-cash-income.component';
import { UpdatePettyCashIncomeComponent } from './Accounts/PettyCashIncome/update-petty-cash-income/update-petty-cash-income.component';
import { ViewPettyCashIncomeComponent } from './Accounts/PettyCashIncome/view-petty-cash-income/view-petty-cash-income.component';
import { DeletePettyCashIncomeComponent } from './Accounts/PettyCashIncome/delete-petty-cash-income/delete-petty-cash-income.component';
//Employee Attendance
import { AddEmployeeAttendanceComponent } from './EmployeeManagement/EmployeeAttendance/add-employee-attendance/add-employee-attendance.component';
import { UpdateEmployeeAttendanceComponent } from './EmployeeManagement/EmployeeAttendance/update-employee-attendance/update-employee-attendance.component';
import { ViewEmployeeAttendanceComponent } from './EmployeeManagement/EmployeeAttendance/view-employee-attendance/view-employee-attendance.component';
import { DeleteEmployeeAttendanceComponent } from './EmployeeManagement/EmployeeAttendance/delete-employee-attendance/delete-employee-attendance.component';
import { ViewLeaveRequestsComponent } from './EmployeeManagement/LeaveRequests/view-leave-requests/view-leave-requests.component';
import { OfficeTicketsComponent } from './EmployeeManagement/OfficeTickets/office-tickets.component';
import { PaymentDeskComponent } from './Reports/payment-desk/payment-desk.component';
import { PrizedShowGroupComponent } from './Reports/prized-show-group/prized-show-group.component';
import { PrizedShowReportComponent } from './Reports/prized-show-report/prized-show-report.component';
import { SpnAuctionReportComponent } from './Reports/spn-auction-report/spn-auction-report.component';
import { CompletedNotPaidReportComponent } from './Reports/completed-not-paid-report/completed-not-paid-report.component';
import { CashBoxComponent } from './Reports/cash-box/cash-box.component';
import { MyDayReportComponent } from './Reports/my-day-report/my-day-report.component';
import { BranchIncomeExpenseComponent } from './Reports/branch-income-expense/branch-income-expense.component';
import { PettyCashBranchReportComponent } from './Reports/petty-cash-branch-report/petty-cash-branch-report.component';
import { EmployeeAllowanceComponent } from './EmployeeManagement/Allowance/employee-allowance.component';
import { CustomerCategoryReportComponent } from './Reports/customer-category-report/customer-category-report.component';
import { SpnNewGroupComponent } from './Reports/spn-groups/spn-new-group.component';
import { SpnGroupViewComponent } from './Reports/spn-groups/spn-group-view.component';
import { EmployeeAdvanceComponent } from './EmployeeManagement/EmployeeAdvance/employee-advance.component';
import { SectionHubComponent } from './Reports/section-hub/section-hub.component';
import { BranchOverviewComponent } from './Reports/section-hub/branch-overview.component';
import { ViewPunchAttendanceComponent } from './EmployeeManagement/PunchAttendance/view-punch-attendance/view-punch-attendance.component';
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
//Commission Payment
import { AddCommissionPaymentComponent } from './TransactionManagement/CommissionPayment/add-commission-payment/add-commission-payment.component';
import { UpdateCommissionPaymentComponent } from './TransactionManagement/CommissionPayment/update-commission-payment/update-commission-payment.component';
import { ViewCommissionPaymentComponent } from './TransactionManagement/CommissionPayment/view-commission-payment/view-commission-payment.component';
import { DeleteCommissionPaymentComponent } from './TransactionManagement/CommissionPayment/delete-commission-payment/delete-commission-payment.component';
// Payment Slab
import { AddPaymentSlabComponent } from './Masters/Slab/PaymentSlab/add-payment-slab/add-payment-slab.component';
import { UpdatePaymentSlabComponent } from './Masters/Slab/PaymentSlab/update-payment-slab/update-payment-slab.component';
import { ViewPaymentSlabComponent } from './Masters/Slab/PaymentSlab/view-payment-slab/view-payment-slab.component';
import { DeletePaymentSlabComponent } from './Masters/Slab/PaymentSlab/delete-payment-slab/delete-payment-slab.component';
import { DueVsPaymentsComponent } from './Masters/Slab/due-vs-payments/due-vs-payments.component';
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
//Payment Refund Report
import { PaymentRefundReportComponent } from './Reports/payment-refund-report/payment-refund-report.component';
//Prized Payment Report
import { CommitmentPaymentReportComponent } from './Reports/commitment-payment-report/commitment-payment-report/commitment-payment-report.component';
//Commisson Payment Report
import { CommissionPaymentReportComponent } from './Reports/commission-payment-report/commission-payment-report/commission-payment-report.component';
//Commisson Pending Report
import { CommissionAmountPendingReportComponent } from './Reports/commission-amount-pending-report/commission-amount-pending-report/commission-amount-pending-report.component';
//Accounts Report
import { AccountsReportComponent } from './Reports/accounts-report/accounts-report/accounts-report.component';
// *******************************************************************************
//Business Agent Report
import { BusinessAgentReportComponent } from './Reports/business-agent/business-agent-report/business-agent-report.component';
//Day Closing Report
import { DayClosingReportComponent } from './Reports/DayClosingReport/day-closing-report/day-closing-report.component';
import { ViewDayClosingComponent } from './Reports/DayClosingReport/view-day-closing/view-day-closing.component';
//Receipt Report
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
import { CustomerAppLeadsReportComponent } from './Reports/customer-app-leads/customer-app-leads-report/customer-app-leads-report.component';
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
//Prized Pending Payment Report
import { CommitmentPendingReportComponent } from './Reports/commitment-pending/commitment-pending-report/commitment-pending-report.component';
import { SchemeWisePayablePendingReportComponent } from './Reports/spn-collection-reports/scheme-wise-payable-pending/scheme-wise-payable-pending-report.component';
import { NextMonthCollectionReportComponent } from './Reports/spn-collection-reports/next-month-collection/next-month-collection-report.component';
import { ThisMonthUnpaidReportComponent } from './Reports/spn-collection-reports/this-month-unpaid/this-month-unpaid-report.component';
import { DueNoCustomerReportComponent } from './Reports/spn-collection-reports/due-no-customer/due-no-customer-report.component';
import { CommitmentEligibilityApprovalComponent } from './Reports/commitment-eligibility-approval/commitment-eligibility-approval.component';
//Customer Ledger
import { CustomerLedgerComponent } from './TransactionManagement/customer-ledger/customer-ledger.component';
import { CustomerScoreRulesComponent } from './settings/customer-score-rules/customer-score-rules.component';
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

//Customer Outstanding Report
import { CommitmentcustomerOutstandingComponent } from './Reports/OutstandingReport/commitmentcustomer-outstanding-report/commitmentcustomer-outstanding/commitmentcustomer-outstanding.component';
//Receipt Report
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
import { SetEmployeeTargetComponent } from './EmployeeManagement/TargetSet/set-employee-target/set-employee-target.component';
import { ViewEmployeeMobileModulesComponent } from './EmployeeManagement/MobileModules/view-employee-mobile-modules/view-employee-mobile-modules.component';
import { PigmyReportComponent } from './Reports/pigmy-report/pigmy-report.component';
import { TaskReportComponent } from './Reports/task-report/task-report.component';
import { ViewDailyTasksTargetsComponent } from './EmployeeManagement/DailyTasksTargets/view-daily-tasks-targets/view-daily-tasks-targets.component';
import { CommitmentReceiptPrintComponent } from './TransactionManagement/commitment-receipt-print/commitment-receipt-print.component';
import { BeforeDeleteReceiptComponent } from './SubscriberManagement/before-delete-receipt/before-delete-receipt.component';
import { RefundTransferReportComponent } from './Reports/refund-transfer-report/refund-transfer-report.component';
import { TransferCustomerDetailsReportComponent } from './Reports/transfer-customer-details-report/transfer-customer-details-report.component';
import { AuctionReportFinalComponent } from './Reports/auction-report-final/auction-report-final.component';
import { CommitmentReportComponent } from './Reports/commitment-report/commitment-report/commitment-report.component';
import { CommitmentPromiseReportComponent } from './Reports/commitment-promise-report/commitment-promise-report.component';
import { UtrReceiptReportComponent } from './Reports/utr-receipt-report/utr-receipt-report.component';
import { CollectionPredictionReportComponent } from './Reports/collection-prediction-report/collection-prediction-report.component';
import { EmployeePerformanceAiComponent } from './Reports/employee-performance-ai/employee-performance-ai.component';
import { RefundApprovalComponent } from './Reports/refund-approval/refund-approval.component';
import { DocumentPreparationComponent } from './Reports/document-preparation/document-preparation.component';

import { CashbookComponent } from './Reports/cashbook/cashbook.component';
import { BankbookComponent } from './Reports/bankbook/bankbook.component';


@NgModule({
  imports: [RouterModule.forChild([
  //dashboard
  { path: 'access-denied', component: NoAccessComponent},
  { path: '', component: DashboardComponent}, 
  { path: 'dashboard', component: DashboardComponent,canActivate: [AuthGuardService],data: {menuId: 1, parentMenuId: 0, permType:''}},
  //Masters
  //Advocate Detail
  { path: 'add-advocate-details', component: AddAdvocateDetailsComponent}, 
  { path: 'update-advocate-details', component: UpdateAdvocateDetailsComponent}, 
  { path: 'view-advocate-details', component: ViewAdvocateDetailsComponent}, 
  { path: 'delete-advocate-details', component: DeleteAdvocateDetailsComponent},
  //Approval Mapping
  { path: 'add-approval-mapping', component: AddApprovalMappingComponent}, 
  { path: 'update-approval-mapping', component: UpdateApprovalMappingComponent}, 
  { path: 'view-approval-mapping', component: ViewApprovalMappingComponent}, 
  { path: 'delete-approval-mapping', component: DeleteApprovalMappingComponent}, 
  //Auction On Holiday
  { path: 'add-auction-on-holidays', component: AddAuctionOnHolidaysComponent,canActivate: [AuthGuardService],data: {menuId: 16, parentMenuId: 2, permType:'add_perm'}}, 
  { path: 'update-auction-on-holidays/:id', component: UpdateAuctionOnHolidaysComponent,canActivate: [AuthGuardService],data: {menuId: 16, parentMenuId: 2, permType:'edit_perm'}}, 
  { path: 'view-auction-on-holidays', component: ViewAuctionOnHolidaysComponent,canActivate: [AuthGuardService],data: {menuId: 16, parentMenuId: 2, permType:'view_perm'}}, 
  { path: 'delete-auction-on-holidays/:id', component: DeleteAuctionOnHolidaysComponent,canActivate: [AuthGuardService],data: {menuId: 16, parentMenuId: 2, permType:'delete_perm'}}, 
  //Bank Detail
  { path: 'add-bank-details', component: AddBankDetailsComponent,canActivate: [AuthGuardService],data: {menuId: 17, parentMenuId: 2, permType:'add_perm'}}, 
  { path: 'update-bank-details/:id', component: UpdateBankDetailsComponent,canActivate: [AuthGuardService],data: {menuId: 17, parentMenuId: 2, permType:'edit_perm'}}, 
  { path: 'view-bank-details', component: ViewBankDetailsComponent,canActivate: [AuthGuardService],data: {menuId: 17, parentMenuId: 2, permType:'view_perm'}}, 
  { path: 'delete-bank-details/:id', component: DeleteBankDetailsComponent,canActivate: [AuthGuardService],data: {menuId: 17, parentMenuId: 2, permType:'delete_perm'}},  
  //Branch
  { path: 'add-branch', component: AddBranchComponent,canActivate: [AuthGuardService],data: {menuId: 7, parentMenuId: 2, permType:'add_perm'}},  
  { path: 'update-branch/:id', component: UpdateBranchComponent,canActivate: [AuthGuardService],data: {menuId: 7, parentMenuId: 2, permType:'edit_perm'}},  
  { path: 'view-branch', component: ViewBranchComponent,canActivate: [AuthGuardService],data: {menuId: 7, parentMenuId: 2, permType:'view_perm'}}, 
  { path: 'delete-branch/:id', component: DeleteBranchComponent,canActivate: [AuthGuardService],data: {menuId: 7, parentMenuId: 2, permType:'delete_perm'}}, 
  //Branch Configuration
  { path: 'add-branch-configuration', component: AddBranchConfigurationComponent}, 
  { path: 'update-branch-configuration', component: UpdateBranchConfigurationComponent}, 
  { path: 'view-branch-configuration', component: ViewBranchConfigurationComponent}, 
  { path: 'delete-branch-configuration', component: DeleteBranchConfigurationComponent},  
  //Charges Type
  { path: 'add-charges-type', component: AddChargesTypeComponent,canActivate: [AuthGuardService],data: {menuId: 13, parentMenuId: 2, permType:'add_perm'}},  
  { path: 'update-charges-type/:id', component: UpdateChargesTypeComponent,canActivate: [AuthGuardService],data: {menuId: 13, parentMenuId: 2, permType:'edit_perm'}},  
  { path: 'view-charges-type', component: ViewChargesTypeComponent,canActivate: [AuthGuardService],data: {menuId: 13, parentMenuId: 2, permType:'view_perm'}},  
  { path: 'delete-charges-type/:id', component: DeleteChargesTypeComponent,canActivate: [AuthGuardService],data: {menuId: 13, parentMenuId: 2, permType:'delete_perm'}}, 
  //City
  { path: 'add-city', component: AddCityComponent,canActivate: [AuthGuardService],data: {menuId: 15, parentMenuId: 2, permType:'add_perm'}},  
  { path: 'update-city/:id', component: UpdateCityComponent,canActivate: [AuthGuardService],data: {menuId: 15, parentMenuId: 2, permType:'edit_perm'}},  
  { path: 'view-city', component: ViewCityComponent,canActivate: [AuthGuardService],data: {menuId: 15, parentMenuId: 2, permType:'view_perm'}},  
  { path: 'delete-city/:id', component: DeleteCityComponent,canActivate: [AuthGuardService],data: {menuId: 15, parentMenuId: 2, permType:'delete_perm'}}, 
  //Gift
  { path: 'add-gift', component: AddGiftComponent},  
  { path: 'update-gift/:id', component: UpdateGiftComponent},  
  { path: 'view-gift', component: ViewGiftComponent},  
  { path: 'delete-gift/:id', component: DeleteGiftComponent}, 
  
  //Collection Area
  { path: 'add-collection-area', component: AddCollectionAreaComponent,canActivate: [AuthGuardService],data: {menuId: 10, parentMenuId: 2, permType:'add_perm'}},  
  { path: 'update-collection-area/:id', component: UpdateCollectionAreaComponent,canActivate: [AuthGuardService],data: {menuId: 10, parentMenuId: 2, permType:'edit_perm'}},  
  { path: 'view-collection-area', component: ViewCollectionAreaComponent,canActivate: [AuthGuardService],data: {menuId: 10, parentMenuId: 2, permType:'view_perm'}}, 
  { path: 'delete-collection-area/:id', component: DeleteCollectionAreaComponent,canActivate: [AuthGuardService],data: {menuId: 10, parentMenuId: 2, permType:'delete_perm'}},  
  //Customer
  { path: 'add-customer', component: AddCustomerComponent,canActivate: [AuthGuardService],data: {menuId: 5, parentMenuId: 28, permType:'add_perm'}},  
  { path: 'update-customer/:id', component: UpdateCustomerComponent,canActivate: [AuthGuardService],data: {menuId: 5, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'view-customer', component: ViewCustomerComponent,canActivate: [AuthGuardService],data: {menuId: 5, parentMenuId: 28, permType:'view_perm'}},
  { path: 'branch-transfer-customer', component: BranchTransferCustomerComponent,canActivate: [AuthGuardService],data: {menuId: 5, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'branch-transfer-customer-report', component: BranchTransferCustomerReportComponent,canActivate: [AuthGuardService],data: {menuId: 5, parentMenuId: 28, permType:'view_perm'}},
  { path: 'delete-customer/:id', component: DeleteCustomerComponent,canActivate: [AuthGuardService],data: {menuId: 5, parentMenuId: 28, permType:'delete_perm'}}, 
  //Designation
  { path: 'add-designation', component: AddDesignationComponent,canActivate: [AuthGuardService],data: {menuId: 12, parentMenuId: 2, permType:'add_perm'}},  
  { path: 'update-designation/:id', component: UpdateDesignationComponent,canActivate: [AuthGuardService],data: {menuId: 12, parentMenuId: 2, permType:'edit_perm'}},  
  { path: 'view-designation', component: ViewDesignationComponent,canActivate: [AuthGuardService],data: {menuId: 12, parentMenuId: 2, permType:'view_perm'}},  
  { path: 'delete-designation/:id', component: DeleteDesignationComponent,canActivate: [AuthGuardService],data: {menuId: 12, parentMenuId: 2, permType:'delete_perm'}},  
  //District
  { path: 'add-district', component: AddDistrictComponent}, 
  { path: 'update-district/:id', component: UpdateDistrictComponent}, 
  { path: 'view-district', component: ViewDistrictComponent}, 
  { path: 'delete-district/:id', component: DeleteDistrictComponent}, 
  //Employee
  { path: 'add-employee', component: AddEmployeeComponent,canActivate: [AuthGuardService],data: {menuId: 3, parentMenuId: 34, permType:'add_perm'}},  
  { path: 'update-employee/:id', component: UpdateEmployeeComponent,canActivate: [AuthGuardService],data: {menuId: 3, parentMenuId: 34, permType:'edit_perm'}},  
  { path: 'view-employee', component: ViewEmployeeComponent,canActivate: [AuthGuardService],data: {menuId: 3, parentMenuId: 34, permType:'view_perm'}},  
  { path: 'delete-employee/:id', component: DeleteEmployeeComponent,canActivate: [AuthGuardService],data: {menuId: 3, parentMenuId: 34, permType:'delete_perm'}},  
  //Group
  { path: 'add-group', component: AddGroupComponent,canActivate: [AuthGuardService],data: {menuId: 9, parentMenuId: 2, permType:'add_perm'}},  
  { path: 'update-group/:id', component: UpdateGroupComponent,canActivate: [AuthGuardService],data: {menuId: 9, parentMenuId: 2, permType:'edit_perm'}},  
  { path: 'view-group', component: ViewGroupComponent,canActivate: [AuthGuardService],data: {menuId: 9, parentMenuId: 2, permType:'view_perm'}},  
  { path: 'delete-group/:id', component: DeleteGroupComponent,canActivate: [AuthGuardService],data: {menuId: 9, parentMenuId: 2, permType:'delete_perm'}},  
  //Guarantor
  { path: 'add-guarantor', component: AddGuarantorComponent,canActivate: [AuthGuardService],data: {menuId: 4, parentMenuId: 28, permType:'add_perm'}},  
  { path: 'update-guarantor/:id', component: UpdateGuarantorComponent,canActivate: [AuthGuardService],data: {menuId: 4, parentMenuId: 28, permType:'edit_perm'}},  
  { path: 'view-guarantor', component: ViewGuarantorComponent,canActivate: [AuthGuardService],data: {menuId: 4, parentMenuId: 28, permType:'view_perm'}},  
  { path: 'delete-guarantor/:id', component: DeleteGuarantorComponent,canActivate: [AuthGuardService],data: {menuId: 4, parentMenuId: 28, permType:'delete_perm'}},  
  //Head Office
  { path: 'add-head-office', component: AddHeadOfficeComponent,canActivate: [AuthGuardService],data: {menuId: 6, parentMenuId: 2, permType:'add_perm'}},  
  { path: 'update-head-office/:id', component: UpdateHeadOfficeComponent,canActivate: [AuthGuardService],data: {menuId: 6, parentMenuId: 2, permType:'edit_perm'}},  
  { path: 'view-head-office', component: ViewHeadOfficeComponent,canActivate: [AuthGuardService],data: {menuId: 6, parentMenuId: 2, permType:'view_perm'}},  
  { path: 'delete-head-office/:id', component: DeleteHeadOfficeComponent,canActivate: [AuthGuardService],data: {menuId: 6, parentMenuId: 2, permType:'delete_perm'}},  
  //Hierarchy
  { path: 'add-hierarchy', component: AddHierarchyComponent}, 
  { path: 'update-hierarchy', component: UpdateHierarchyComponent}, 
  { path: 'view-hierarchy', component: ViewHierarchyComponent}, 
  { path: 'delete-hierarchy', component: DeleteHierarchyComponent}, 
  //Ledger
  { path: 'add-ledger', component: AddLedgerComponent}, 
  { path: 'update-ledger', component: UpdateLedgerComponent}, 
  { path: 'view-ledger', component: ViewLedgerComponent}, 
  { path: 'delete-ledger', component: DeleteLedgerComponent}, 
  //Petty Cash Expense Type
  { path: 'add-petty-cash-head', component: AddPettyCashExpenseTypeComponent,canActivate: [AuthGuardService],data: {menuId: 18, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-petty-cash-head/:id', component: UpdatePettyCashExpenseTypeComponent,canActivate: [AuthGuardService],data: {menuId: 18, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-petty-cash-head', component: ViewPettyCashExpenseTypeComponent,canActivate: [AuthGuardService],data: {menuId: 18, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-petty-cash-head/:id', component: DeletePettyCashExpenseTypeComponent,canActivate: [AuthGuardService],data: {menuId: 18, parentMenuId: 2, permType:'delete_perm'}},
  //Relationship
  { path: 'add-relationship', component: AddRelationshipComponent,canActivate: [AuthGuardService],data: {menuId: 14, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-relationship/:id', component: UpdateRelationshipComponent,canActivate: [AuthGuardService],data: {menuId: 14, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-relationship', component: ViewRelationshipComponent,canActivate: [AuthGuardService],data: {menuId: 14, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-relationship/:id', component: DeleteRelationshipComponent,canActivate: [AuthGuardService],data: {menuId: 14, parentMenuId: 2, permType:'delete_perm'}},
  //Role
  { path: 'add-role', component: AddRoleComponent,canActivate: [AuthGuardService],data: {menuId: 11, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-role/:id', component: UpdateRoleComponent,canActivate: [AuthGuardService],data: {menuId: 11, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-role', component: ViewRoleComponent,canActivate: [AuthGuardService],data: {menuId: 11, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-role/:id', component: DeleteRoleComponent,canActivate: [AuthGuardService],data: {menuId: 11, parentMenuId: 2, permType:'delete_perm'}},
  //Scheme
  { path: 'add-scheme', component: AddSchemeComponent,canActivate: [AuthGuardService],data: {menuId: 8, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-scheme/:id', component: UpdateSchemeComponent,canActivate: [AuthGuardService],data: {menuId: 8, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-scheme', component: ViewSchemeComponent,canActivate: [AuthGuardService],data: {menuId: 8, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-scheme/:id', component: DeleteSchemeComponent,canActivate: [AuthGuardService],data: {menuId: 8, parentMenuId: 2, permType:'delete_perm'}},
  //State
  { path: 'add-state', component: AddStateComponent},
  { path: 'update-state/:id', component: UpdateStateComponent},
  { path: 'view-state', component: ViewStateComponent},
  { path: 'delete-state/:id', component: DeleteStateComponent},
  //Surity Detail
  { path: 'add-surety-details', component: AddSuretyDetailsComponent},
  { path: 'update-surety-details', component: UpdateSuretyDetailsComponent},
  { path: 'view-surety-details', component: ViewSuretyDetailsComponent},
  { path: 'delete-surety-details', component: DeleteSuretyDetailsComponent},
  //Bonus Slab
  { path: 'add-bonus-slab', component: AddBonusSlabComponent,canActivate: [AuthGuardService],data: {menuId: 22, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-bonus-slab/:id', component: UpdateBonusSlabComponent,canActivate: [AuthGuardService],data: {menuId: 22, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-bonus-slab', component: ViewBonusSlabComponent,canActivate: [AuthGuardService],data: {menuId: 22, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-bonus-slab/:id', component: DeleteBonusSlabComponent,canActivate: [AuthGuardService],data: {menuId: 22, parentMenuId: 2, permType:'delete_perm'}},
  //Due Slab
  { path: 'add-due-slab', component: AddDueSlabComponent,canActivate: [AuthGuardService],data: {menuId: 20, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-due-slab/:id', component: UpdateDueSlabComponent,canActivate: [AuthGuardService],data: {menuId: 20, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-due-slab', component: ViewDueSlabComponent,canActivate: [AuthGuardService],data: {menuId: 20, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-due-slab/:id', component: DeleteDueSlabComponent,canActivate: [AuthGuardService],data: {menuId: 20, parentMenuId: 2, permType:'delete_perm'}},
  //Interest Slab
  { path: 'add-interest-slab', component: AddInterestSlabComponent,canActivate: [AuthGuardService],data: {menuId: 23, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-interest-slab/:id', component: UpdateInterestSlabComponent,canActivate: [AuthGuardService],data: {menuId: 23, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-interest-slab', component: ViewInterestSlabComponent,canActivate: [AuthGuardService],data: {menuId: 23, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-interest-slab/:id', component: DeleteInterestSlabComponent,canActivate: [AuthGuardService],data: {menuId: 23, parentMenuId: 2, permType:'delete_perm'}},
  //Penalty Slab
  { path: 'add-penalty-slab', component: AddPenaltySlabComponent,canActivate: [AuthGuardService],data: {menuId: 21, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-penalty-slab/:id', component: UpdatePenaltySlabComponent,canActivate: [AuthGuardService],data: {menuId: 21, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-penalty-slab', component: ViewPenaltySlabComponent,canActivate: [AuthGuardService],data: {menuId: 21, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-penalty-slab/:id', component: DeletePenaltySlabComponent,canActivate: [AuthGuardService],data: {menuId: 21, parentMenuId: 2, permType:'delete_perm'}},
  //Payment Slab
  { path: 'add-payment-slab', component: AddPaymentSlabComponent,canActivate: [AuthGuardService],data: {menuId: 24, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-payment-slab/:id', component: UpdatePaymentSlabComponent,canActivate: [AuthGuardService],data: {menuId: 24, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-payment-slab', component: ViewPaymentSlabComponent,canActivate: [AuthGuardService],data: {menuId: 24, parentMenuId: 2, permType:'view_perm'}},
  { path: 'due-vs-payments', component: DueVsPaymentsComponent, canActivate: [AuthGuardService] },
  { path: 'delete-payment-slab/:id', component: DeletePaymentSlabComponent,canActivate: [AuthGuardService],data: {menuId: 24, parentMenuId: 2, permType:'delete_perm'}},
  //Tenant
  /*{ path: 'add-tenant', component: AddTenantComponent,canActivate: [AuthGuardService],data: {menuId: 54, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-tenant/:id', component: UpdateTenantComponent,canActivate: [AuthGuardService],data: {menuId: 54, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-tenant', component: ViewTenantComponent,canActivate: [AuthGuardService],data: {menuId: 54, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-tenant/:id', component: DeleteTenantComponent,canActivate: [AuthGuardService],data: {menuId: 54, parentMenuId: 2, permType:'delete_perm'}},*/
  { path: 'add-tenant', component: AddTenantComponent,canActivate: [AuthGuardService],data: {menuId: 0, parentMenuId: 2, permType:'add_perm'}},
  { path: 'update-tenant/:id', component: UpdateTenantComponent,canActivate: [AuthGuardService],data: {menuId: 0, parentMenuId: 2, permType:'edit_perm'}},
  { path: 'view-tenant', component: ViewTenantComponent,canActivate: [AuthGuardService],data: {menuId: 0, parentMenuId: 2, permType:'view_perm'}},
  { path: 'delete-tenant/:id', component: DeleteTenantComponent,canActivate: [AuthGuardService],data: {menuId: 0, parentMenuId: 2, permType:'delete_perm'}},
  //Customer Enrollment
  { path: 'add-customer-enrollment', component: AddCustomerEnrollmentComponent,canActivate: [AuthGuardService],data: {menuId: 29, parentMenuId: 28, permType:'add_perm'}},
  { path: 'update-customer-enrollment/:id', component: UpdateCustomerEnrollmentComponent,canActivate: [AuthGuardService],data: {menuId: 29, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'view-customer-enrollment', component: ViewCustomerEnrollmentComponent,canActivate: [AuthGuardService],data: {menuId: 29, parentMenuId: 28, permType:'view_perm'}},
  { path: 'delete-customer-enrollment/:id', component: DeleteCustomerEnrollmentComponent,canActivate: [AuthGuardService],data: {menuId: 29, parentMenuId: 28, permType:'delete_perm'}},
  //Passbook
  { path: 'passbook-entry', component: PassbookEntryComponent},
  { path: 'print-passbook-index/:enrollmentId', component: PassbookIndexComponent},
  { path: 'passbook-transactions/:enrollmentId', component: PassbookTransactionComponent},
  { path: 'passbook-transaction-index/:enrollmentId', component: PassbookTransactionIndexComponent},
  //Auction
  { path: 'add-auction', component: AddAuctionComponent,canActivate: [AuthGuardService],data: {menuId: 30, parentMenuId: 28, permType:'add_perm'}},
  { path: 'update-auction/:id', component: UpdateAuctionComponent,canActivate: [AuthGuardService],data: {menuId: 30, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'view-auction', component: ViewAuctionComponent,canActivate: [AuthGuardService],data: {menuId: 30, parentMenuId: 28, permType:'view_perm'}},
  { path: 'delete-auction/:id', component: DeleteAuctionComponent,canActivate: [AuthGuardService],data: {menuId: 30, parentMenuId: 28, permType:'delete_perm'}},
  //Payment
  { path: 'add-payment/:auctionId', component: AddPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 30, parentMenuId: 28, permType:'add_perm'}},
  { path: 'update-payment/:id', component: UpdatePaymentComponent,canActivate: [AuthGuardService],data: {menuId: 30, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'view-payments/:auctionId', component: ViewPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 30, parentMenuId: 28, permType:'view_perm'}},
  { path: 'delete-payment/:id', component: DeletePaymentComponent,canActivate: [AuthGuardService],data: {menuId: 30, parentMenuId: 28, permType:'delete_perm'}},
  //Receipt
  { path: 'add-receipt', component: AddReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 31, parentMenuId: 37, permType:'add_perm'}},
  { path: 'update-receipt', component: UpdateReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 31, parentMenuId: 37, permType:'edit_perm'}},
  { path: 'view-receipt', component: ViewReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 31, parentMenuId: 37, permType:'view_perm'}},
  { path: 'delete-receipt/:receipt_no', component: DeleteReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 31, parentMenuId: 37, permType:'delete_perm'}},
  //Petty Cash Expense
  { path: 'add-petty-cash-expense', component: AddPettyCashExpenseComponent,canActivate: [AuthGuardService],data: {menuId: 26, parentMenuId: 62, permType:'add_perm'}},
  { path: 'update-petty-cash-expense/:id', component: UpdatePettyCashExpenseComponent,canActivate: [AuthGuardService],data: {menuId: 26, parentMenuId: 62, permType:'edit_perm'}},
  { path: 'view-petty-cash-expense', component: ViewPettyCashExpenseComponent,canActivate: [AuthGuardService],data: {menuId: 26, parentMenuId: 62, permType:'view_perm'}},
  { path: 'delete-petty-cash-expense/:id', component: DeletePettyCashExpenseComponent,canActivate: [AuthGuardService],data: {menuId: 26, parentMenuId: 62, permType:'delete_perm'}},
  //Petty Cash Income
  { path: 'add-petty-cash-income', component: AddPettyCashIncomeComponent,canActivate: [AuthGuardService],data: {menuId: 27, parentMenuId: 62, permType:'add_perm'}},
  { path: 'update-petty-cash-income/:id', component: UpdatePettyCashIncomeComponent,canActivate: [AuthGuardService],data: {menuId: 27, parentMenuId: 62, permType:'edit_perm'}},
  { path: 'view-petty-cash-income', component: ViewPettyCashIncomeComponent,canActivate: [AuthGuardService],data: {menuId: 27, parentMenuId: 62, permType:'view_perm'}},
  { path: 'delete-petty-cash-income/:id', component: DeletePettyCashIncomeComponent,canActivate: [AuthGuardService],data: {menuId: 27, parentMenuId: 62, permType:'delete_perm'}},
  //Employee Attendance
  { path: 'add-employee-attendance', component: AddEmployeeAttendanceComponent,canActivate: [AuthGuardService],data: {menuId: 35, parentMenuId: 34, permType:'add_perm'}},
  { path: 'update-employee-attendance/:branchId/:attYear/:attMonth/:attDate', component: UpdateEmployeeAttendanceComponent,canActivate: [AuthGuardService],data: {menuId: 35, parentMenuId: 34, permType:'edit_perm'}},
  { path: 'view-employee-attendance', component: ViewPunchAttendanceComponent,canActivate: [AuthGuardService],data: {menuId: 35, parentMenuId: 34, permType:'view_perm', mode: 'attendance'}},
  { path: 'view-leave-requests', component: ViewLeaveRequestsComponent,canActivate: [AuthGuardService],data: {menuId: 35, parentMenuId: 34, permType:'view_perm'}},
  { path: 'office-tickets', component: OfficeTicketsComponent, canActivate: [AuthGuardService], data: { menuId: 35, parentMenuId: 34, permType: 'view_perm' } },
  { path: 'payment-dashboard', component: PaymentDeskComponent, canActivate: [AuthGuardService], data: { menuId: 56, parentMenuId: 28, permType: 'view_perm', desk: 'dashboard' } },
  { path: 'payment-priced', component: PaymentDeskComponent, canActivate: [AuthGuardService], data: { menuId: 56, parentMenuId: 28, permType: 'view_perm', desk: 'prized' } },
  { path: 'payment-closed', component: PaymentDeskComponent, canActivate: [AuthGuardService], data: { menuId: 56, parentMenuId: 28, permType: 'view_perm', desk: 'closed' } },
  { path: 'payment-refund-desk', component: PaymentDeskComponent, canActivate: [AuthGuardService], data: { menuId: 56, parentMenuId: 28, permType: 'view_perm', desk: 'refund' } },
  { path: 'payment-pending-approvals', component: PaymentDeskComponent, canActivate: [AuthGuardService], data: { menuId: 56, parentMenuId: 28, permType: 'view_perm', desk: 'pending' } },
  { path: 'payment-accounts-payout', component: PaymentDeskComponent, canActivate: [AuthGuardService], data: { menuId: 56, parentMenuId: 28, permType: 'view_perm', desk: 'pending', stage: 'accounts' } },
  { path: 'prized-show-group', component: PrizedShowGroupComponent, canActivate: [AuthGuardService], data: { menuId: 56, parentMenuId: 28, permType: 'view_perm' } },
  { path: 'prized-show-report', component: PrizedShowReportComponent, canActivate: [AuthGuardService], data: { menuId: 56, parentMenuId: 28, permType: 'view_perm' } },
  { path: 'completed-not-paid-report', component: CompletedNotPaidReportComponent, canActivate: [AuthGuardService], data: { menuId: 71, parentMenuId: 65, permType: 'view_perm' } },
  { path: 'my-cash-box', component: CashBoxComponent, canActivate: [AuthGuardService], data: { menuId: 148, parentMenuId: 67, permType: 'view_perm', tab: 'box' } },
  { path: 'cash-box-approvals', component: CashBoxComponent, canActivate: [AuthGuardService], data: { menuId: 148, parentMenuId: 67, permType: 'view_perm', tab: 'approvals' } },
  { path: 'income-cash-box-report', component: CashBoxComponent, canActivate: [AuthGuardService], data: { menuId: 148, parentMenuId: 67, permType: 'view_perm', tab: 'income' } },
  { path: 'my-day-report', component: MyDayReportComponent, canActivate: [AuthGuardService], data: { menuId: 148, parentMenuId: 67, permType: 'view_perm' } },
  { path: 'branch-income-expense-report', component: BranchIncomeExpenseComponent, canActivate: [AuthGuardService], data: { menuId: 105, parentMenuId: 67, permType: 'view_perm' } },
  { path: 'petty-cash-branch-report', component: PettyCashBranchReportComponent, canActivate: [AuthGuardService], data: { menuId: 26, parentMenuId: 62, permType: 'view_perm' } },
  { path: 'day-allowance-report', component: EmployeeAllowanceComponent, canActivate: [AuthGuardService], data: { menuId: 101, parentMenuId: 34, permType: 'view_perm', mode: 'day' } },
  { path: 'petrol-allowance', component: EmployeeAllowanceComponent, canActivate: [AuthGuardService], data: { menuId: 101, parentMenuId: 34, permType: 'view_perm', mode: 'petrol' } },
  { path: 'spn-auction-report', component: SpnAuctionReportComponent, canActivate: [AuthGuardService], data: { menuId: 56, parentMenuId: 28, permType: 'view_perm' } },
  { path: 'customer-category-report', component: CustomerCategoryReportComponent, canActivate: [AuthGuardService], data: { menuId: 32, parentMenuId: 28, permType: 'view_perm' } },
  { path: 'spn-new-group', component: SpnNewGroupComponent, canActivate: [AuthGuardService], data: { menuId: 8, parentMenuId: 2, permType: 'add_perm' } },
  { path: 'spn-group-view', component: SpnGroupViewComponent, canActivate: [AuthGuardService], data: { menuId: 8, parentMenuId: 2, permType: 'view_perm' } },
  { path: 'employee-advance', component: EmployeeAdvanceComponent, canActivate: [AuthGuardService], data: { menuId: 101, parentMenuId: 34, permType: 'view_perm' } },
  { path: 'hub/:section', component: SectionHubComponent, canActivate: [AuthGuardService] },
  { path: 'branch-overview', component: BranchOverviewComponent, canActivate: [AuthGuardService] },
  { path: 'branch-overview/:id', component: BranchOverviewComponent, canActivate: [AuthGuardService] },
  { path: 'view-punch-attendance', component: ViewPunchAttendanceComponent,canActivate: [AuthGuardService],data: {menuId: 35, parentMenuId: 34, permType:'view_perm'}},
  { path: 'delete-employee-attendance/:id', component: DeleteEmployeeAttendanceComponent,canActivate: [AuthGuardService],data: {menuId: 35, parentMenuId: 34, permType:'delete_perm'}},
  //Employee Login
  { path: 'add-employee-login', component: AddEmployeeLoginComponent,canActivate: [AuthGuardService],data: {menuId: 36, parentMenuId: 34, permType:'add_perm'}},
  { path: 'update-employee-login/:id', component: UpdateEmployeeLoginComponent,canActivate: [AuthGuardService],data: {menuId: 36, parentMenuId: 34, permType:'edit_perm'}},
  { path: 'view-employee-login', component: ViewEmployeeLoginComponent,canActivate: [AuthGuardService],data: {menuId: 36, parentMenuId: 34, permType:'view_perm'}},
  { path: 'delete-employee-login/:id', component: DeleteEmployeeLoginComponent,canActivate: [AuthGuardService],data: {menuId: 36, parentMenuId: 34, permType:'delete_perm'}},
  //Document
  { path: 'add-document', component: AddDocumentComponent,canActivate: [AuthGuardService],data: {menuId: 38, parentMenuId: 28, permType:'add_perm'}},
  { path: 'update-document/:id', component: UpdateDocumentComponent,canActivate: [AuthGuardService],data: {menuId: 38, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'view-documents', component: ViewDocumentComponent,canActivate: [AuthGuardService],data: {menuId: 38, parentMenuId: 28, permType:'view_perm'}},
  { path: 'delete-document/:id', component: DeleteDocumentComponent,canActivate: [AuthGuardService],data: {menuId: 38, parentMenuId: 28, permType:'delete_perm'}},
  //Lead Management
  { path: 'add-lead-management', component: AddLeadManagementComponent,canActivate: [AuthGuardService],data: {menuId: 41, parentMenuId: 28, permType:'add_perm'}},
  { path: 'update-lead-management/:id', component: UpdateLeadManagementComponent,canActivate: [AuthGuardService],data: {menuId: 41, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'view-lead-management', component: ViewLeadManagementComponent,canActivate: [AuthGuardService],data: {menuId: 41, parentMenuId: 28, permType:'view_perm'}},
  { path: 'delete-lead-management/:id', component: DeleteLeadManagementComponent,canActivate: [AuthGuardService],data: {menuId: 41, parentMenuId: 28, permType:'delete_perm'}},
  { path: 'move-to-customer/:id', component: MoveToCustomerComponent,canActivate: [AuthGuardService],data: {menuId: 41, parentMenuId: 28, permType:'add_perm'}},
  //Followup
  { path: 'add-followups/:id', component: AddFollowupsComponent,canActivate: [AuthGuardService],data: {menuId: 41, parentMenuId: 28, permType:'add_perm'}},
  { path: 'update-followups/:leadId/:id', component: UpdateFollowupsComponent,canActivate: [AuthGuardService],data: {menuId: 41, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'view-followups/:id', component: ViewFollowupsComponent,canActivate: [AuthGuardService],data: {menuId: 41, parentMenuId: 28, permType:'view_perm'}},
  { path: 'delete-followups/:id', component: DeleteFollowupsComponent,canActivate: [AuthGuardService],data: {menuId: 41, parentMenuId: 28, permType:'delete_perm'}},
  //Reports
  //Payment Reports
  { path: 'prized-money-pending-report', component: PrizedMoneyPendingReportComponent,canActivate: [AuthGuardService],data: {menuId: 46, parentMenuId: 42, permType:'add_perm'}},
  { path: 'prized-money-released-report', component: PrizedMoneyReleasedReportComponent,canActivate: [AuthGuardService],data: {menuId: 47, parentMenuId: 42, permType:'add_perm'}},
  { path: 'get-commitment-payment-report', component: CommitmentPaymentReportComponent,canActivate: [AuthGuardService],data: {menuId: 70, parentMenuId: 42, permType:'add_perm'}},
  //Outstanding Report
  { path: 'customerwise-outstanding-report', component: CustomerwiseOutstandingReportComponent,canActivate: [AuthGuardService],data: {menuId: 92, parentMenuId: 42, permType:'add_perm'}},
  { path: 'employeewise-outstanding-report', component: EmployeewiseOutstandingReportComponent,canActivate: [AuthGuardService],data: {menuId: 92, parentMenuId: 42, permType:'add_perm'}},
  { path: 'groupwise-outstanding-report', component: GroupewiseOutstandingReportComponent,canActivate: [AuthGuardService],data: {menuId: 44, parentMenuId: 42, permType:'add_perm'}},
  //Other Reports
  { path: 'enrollment-report', component: EnrollmentReportComponent,canActivate: [AuthGuardService],data: {menuId: 69, parentMenuId: 48, permType:'view_perm'}},
  { path: 'vacant-report', component: VacantReportComponent,canActivate: [AuthGuardService],data: {menuId: 49, parentMenuId: 42, permType:'add_perm'}},
  { path: 'auction-report', component: AuctionReportComponent,canActivate: [AuthGuardService],data: {menuId: 64, parentMenuId: 42, permType:'add_perm'}},
  { path: 'receipt-report', component: ReceiptReportComponent,canActivate: [AuthGuardService],data: {menuId: 51, parentMenuId: 42, permType:'add_perm'}},
   //Chit
   { path: 'add-commitment-chit', component: AddCommitmentChitComponent,canActivate: [AuthGuardService],data: {menuId: 32, parentMenuId: 28, permType:'add_perm'}},
   { path: 'update-commitment-chit/:id', component: UpdateCommitmentChitComponent,canActivate: [AuthGuardService],data: {menuId: 32, parentMenuId: 28, permType:'edit_perm'}},
   { path: 'delete-commitment-chit/:id', component: DeleteCommitmentChitComponent,canActivate: [AuthGuardService],data: {menuId: 32, parentMenuId: 28, permType:'view_perm'}},
   { path: 'view-commitment-chit', component: ViewCommitmentChitComponent,canActivate: [AuthGuardService],data: {menuId: 32, parentMenuId: 28, permType:'delete_perm'}},
   //Commission Payment
  { path: 'add-commission-payment', component: AddCommissionPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 39, parentMenuId: 28, permType:'add_perm'}},
  { path: 'update-commission-payment/:commissionNo', component: UpdateCommissionPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 39, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'view-commission-payment', component: ViewCommissionPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 39, parentMenuId: 28, permType:'view_perm'}},
  { path: 'delete-commission-payment/:commissionNo', component: DeleteCommissionPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 39, parentMenuId: 28, permType:'delete_perm'}},
  //Dynamic Menu
  /*{ path: 'add-dynamic-menu', component: AddDynamicMenuComponent,canActivate: [AuthGuardService],data: {menuId: 52, parentMenuId: 0, permType:'add_perm'}}, 
  { path: 'update-dynamic-menu/:id', component: UpdateDynamicMenuComponent, canActivate: [AuthGuardService],data: {menuId: 52, parentMenuId: 0, permType:'edit_perm'}}, 
  { path: 'view-dynamic-menus', component: ViewDynamicMenuComponent, canActivate: [AuthGuardService],data: {menuId: 52, parentMenuId: 0, permType:'view_perm'}},  
  { path: 'delete-dynamic-menu/:id', component: DeleteDynamicMenuComponent,canActivate: [AuthGuardService],data: {menuId: 52, parentMenuId: 0, permType:'delete_perm'}}, */
  { path: 'add-dynamic-menu', component: AddDynamicMenuComponent,canActivate: [AuthGuardService],data: {menuId: 0, parentMenuId: 0, permType:'add_perm'}}, 
  { path: 'update-dynamic-menu/:id', component: UpdateDynamicMenuComponent, canActivate: [AuthGuardService],data: {menuId: 0, parentMenuId: 0, permType:'edit_perm'}}, 
  { path: 'view-dynamic-menus', component: ViewDynamicMenuComponent, canActivate: [AuthGuardService],data: {menuId: 0, parentMenuId: 0, permType:'view_perm'}},  
  { path: 'delete-dynamic-menu/:id', component: DeleteDynamicMenuComponent,canActivate: [AuthGuardService],data: {menuId: 0, parentMenuId: 0, permType:'delete_perm'}}, 
  //Security Permission
  { path: 'add-permission', component: AddPermissionComponent,canActivate: [AuthGuardService],data: {menuId: 53, parentMenuId: 0, permType:'add_perm'}},
  { path: 'update-permission', component: UpdatePermissionComponent,canActivate: [AuthGuardService],data: {menuId: 53, parentMenuId: 0, permType:'edit_perm'}},
 // { path: 'view-permissions', component: ViewPermissionComponent,canActivate: [AuthGuardService],data: {menuId: 6, parentMenuId: 0, permType:'add_perm'}},
 // { path: 'delete-permission/:id', component: DeletePermissionComponent,canActivate: [AuthGuardService],data: {menuId: 6, parentMenuId: 0, permType:'add_perm'}},
  //Prized Payment
  { path: 'add-commitment-payment', component: AddCommitmentPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 56, parentMenuId: 28, permType:'add_perm'}},
  { path: 'update-commitment-payment/:id', component: UpdateCommitmentPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 56, parentMenuId: 28, permType:'edit_perm'}},
  { path: 'view-commitment-payment', component: ViewCommitmentPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 56, parentMenuId: 28, permType:'view_perm'}},
  { path: 'delete-commitment-payment/:id', component: DeleteCommitmentPaymentComponent,canActivate: [AuthGuardService],data: {menuId: 56, parentMenuId: 28, permType:'delete_perm'}},
 //Enrol Refund Amount
 { path: 'add-enrol-refund-amount', component: AddEnrolRefundComponent,canActivate: [AuthGuardService],data: {menuId: 33, parentMenuId: 37, permType:'add_perm'}},
 { path: 'update-enrol-refund-amount/:id', component: UpdateEnrolRefundComponent,canActivate: [AuthGuardService],data: {menuId: 33, parentMenuId: 37, permType:'edit_perm'}},
 { path: 'view-enrol-refund-amount', component: ViewEnrolRefundComponent,canActivate: [AuthGuardService],data: {menuId: 33, parentMenuId: 37, permType:'view_perm'}},
 { path: 'delete-enrol-refund-amount/:id', component: DeleteEnrolRefundComponent,canActivate: [AuthGuardService],data: {menuId: 33, parentMenuId: 37, permType:'delete_perm'}},
 //Commitment  Report
 { path: 'commitment-payment-report', component: CommitmentPaymentReportComponent,canActivate: [AuthGuardService],data: {menuId: 70, parentMenuId: 42, permType:'add_perm'}}, 
 { path: 'commitment-pending-report', component: CommitmentPendingReportComponent,canActivate: [AuthGuardService],data: {menuId: 71, parentMenuId: 65, permType:'view_perm'}}, 
 { path: 'commitment-promise-report', component: CommitmentPromiseReportComponent,canActivate: [AuthGuardService],data: {menuId: 71, parentMenuId: 65, permType:'view_perm', mode: 'promised'}},
 { path: 'final-closed-report', component: CommitmentPromiseReportComponent,canActivate: [AuthGuardService],data: {menuId: 71, parentMenuId: 65, permType:'view_perm', mode: 'final'}},
 { path: 'utr-receipt-report', component: UtrReceiptReportComponent,canActivate: [AuthGuardService],data: {menuId: 82, parentMenuId: 66, permType:'view_perm'}},
 { path: 'collection-prediction-report', component: CollectionPredictionReportComponent, canActivate: [AuthGuardService], data: { routeKey: 'collection-prediction-report', permType: 'view_perm' } },
 { path: 'employee-performance-ai', component: EmployeePerformanceAiComponent, canActivate: [AuthGuardService], data: { routeKey: 'employee-performance-ai', permType: 'view_perm' } },
 { path: 'refund-approval', component: RefundApprovalComponent,canActivate: [AuthGuardService],data: {menuId: 109, parentMenuId: 37, permType:'view_perm'}},
 { path: 'document-preparation', component: DocumentPreparationComponent,canActivate: [AuthGuardService],data: {menuId: 56, parentMenuId: 28, permType:'view_perm'}},
 { path: 'scheme-wise-payable-pending-report', component: SchemeWisePayablePendingReportComponent,canActivate: [AuthGuardService],data: {menuId: 164, parentMenuId: 168, permType:'view_perm'}},
 { path: 'next-month-collection-report', component: NextMonthCollectionReportComponent,canActivate: [AuthGuardService],data: {menuId: 165, parentMenuId: 168, permType:'view_perm'}},
 { path: 'this-month-unpaid-report', component: ThisMonthUnpaidReportComponent,canActivate: [AuthGuardService],data: {menuId: 166, parentMenuId: 168, permType:'view_perm'}},
 { path: 'due-no-customer-report', component: DueNoCustomerReportComponent,canActivate: [AuthGuardService],data: {menuId: 167, parentMenuId: 168, permType:'view_perm'}},
 { path: 'commitment-eligibility-approval', component: CommitmentEligibilityApprovalComponent,canActivate: [AuthGuardService],data: { menuId: 152, parentMenuId: 28, permType:'add_perm' }},
  //Commission Payment Report
  { path: 'commission-payment-report', component: CommissionPaymentReportComponent,canActivate: [AuthGuardService],data: {menuId: 79, parentMenuId: 42, permType:'add_perm'}},
  //Payment Refund Report 
  { path: 'payment-refund-report', component: PaymentRefundReportComponent,canActivate: [AuthGuardService],data: {menuId: 63, parentMenuId: 42, permType:'add_perm'}},
  //Commission Pending Report
  { path: 'commission-pending-report', component: CommissionAmountPendingReportComponent,canActivate: [AuthGuardService],data: {menuId: 78, parentMenuId: 42, permType:'add_perm'}},
  //Business Agent Report
  { path: 'business-agent-report', component: BusinessAgentReportComponent,canActivate: [AuthGuardService],data: {menuId: 77, parentMenuId: 42, permType:'add_perm'}},
  //Accounts Report
  { path: 'accounts-report', component: AccountsReportComponent,canActivate: [AuthGuardService],data: {menuId: 76, parentMenuId: 42, permType:'add_perm'}},
  //Day Closing Report
  { path: 'day-closing-report', component: DayClosingReportComponent,canActivate: [AuthGuardService],data: {menuId: 75, parentMenuId: 42, permType:'add_perm'}},
  { path: 'view-day-closing', component: ViewDayClosingComponent},
  //Receipt Report
  { path: 'add-commitment-receipt', component: AddCommitmentReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 57, parentMenuId: 37, permType:'add_perm'}},
  { path: 'delete-commitment-receipt/:receipt_no', component: DeleteCommitmentReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 57, parentMenuId: 37, permType:'delete_perm'}},
  //BidPayment Adjustment
  { path: 'add-bidpayment-adjustment', component: AddBidpaymentAdjustmentComponent,canActivate: [AuthGuardService],data: {menuId: 59, parentMenuId: 37, permType:'add_perm'}},
  //Advanced Receipt
  { path: 'add-advanced-receipt', component: AddAdvancedReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 58, parentMenuId: 37, permType:'add_perm'}},
  { path: 'update-advanced-receipt', component: UpdateAdvancedReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 58, parentMenuId: 37, permType:'edit_perm'}},
  { path: 'delete-advanced-receipt/:receipt_no', component: DeleteAdvancedReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 58, parentMenuId: 37, permType:'delete_perm'}},
  { path: 'view-advanced-receipt', component: ViewAdvancedReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 58, parentMenuId: 37, permType:'view_perm'}},
  //Advanced Receipt Report
  { path: 'advanced-receipt-report', component: AdvancedReceiptReportComponent,canActivate: [AuthGuardService],data: {menuId: 72, parentMenuId: 42, permType:'add_perm'}},
   //Available Advance Amount Report
   { path: 'available-advance-amount-report', component: AvailableAdvanceAmountReportComponent,canActivate: [AuthGuardService],data: {menuId: 73, parentMenuId: 42, permType:'add_perm'}},

  //Available Advance Amount Report
  { path: 'interested-customer-report', component: InterestedCustomerReportComponent},
  { path: 'customer-app-leads-report', component: CustomerAppLeadsReportComponent},
  //Collection Area Mapping
  { path: 'add-collection-area-mapping', component: AddCollectionAreaMappingComponent,canActivate: [AuthGuardService],data: {menuId: 55, parentMenuId: 34, permType:'add_perm'}},
  { path: 'update-collection-area-mapping/:id', component: UpdateCollectionAreaMappingComponent,canActivate: [AuthGuardService],data: {menuId: 55, parentMenuId: 34, permType:'edit_perm'}},
  { path: 'delete-collection-area-mapping/:id', component: DeleteCollectionAreaMappingComponent,canActivate: [AuthGuardService],data: {menuId: 55, parentMenuId: 34, permType:'delete_perm'}},
  { path: 'view-collection-area-mapping', component: ViewCollectionAreaMappingComponent,canActivate: [AuthGuardService],data: {menuId: 55, parentMenuId: 34, permType:'view_perm'}},
  //Other Receipt Charges     
  { path: 'add-other-receipt-charges', component: AddOtherReceiptChargesComponent,canActivate: [AuthGuardService],data: {menuId: 60, parentMenuId: 37, permType:'add_perm'}},
  { path: 'update-other-receipt-charges/:id', component: UpdateOtherReceiptChargesComponent,canActivate: [AuthGuardService],data: {menuId: 60, parentMenuId: 37, permType:'edit_perm'}},
  { path: 'delete-other-receipt-charges/:id', component: DeleteOtherReceiptChargesComponent,canActivate: [AuthGuardService],data: {menuId: 60, parentMenuId: 37, permType:'delete_perm'}},
  { path: 'view-other-receipt-charges', component: ViewOtherReceiptChargesComponent,canActivate: [AuthGuardService],data: {menuId: 60, parentMenuId: 37, permType:'view_perm'}},
  //Other Receipt Charges Report  
  { path: 'other-receipt-charges-report', component: OtherReceiptChargesReportComponent,canActivate: [AuthGuardService],data: {menuId: 74, parentMenuId: 42, permType:'add_perm'}},
  //Customer Ledger 
  { path: 'customer-ledger', component: CustomerLedgerComponent,canActivate: [AuthGuardService],data: { routeKey: 'customer-ledger' }},
  { path: 'customer-score-rules', component: CustomerScoreRulesComponent, canActivate: [AuthGuardService] },
  { path: 'add-duplicate-receipt', component: AddDuplicateReceiptComponent},
  { path: 'password-change', component: PasswordChangeComponent},
  //Receipt Print
  { path: 'receipt-print/:receipt_no', component: ReceiptPrintComponent},
  { path: 'advance-receipt-print/:receipt_no', component: AdvanceReceiptPrintComponent},
  { path: 'before-advance-receipt-print/:receipt_no', component: BeforeAdvanceReceiptPrintComponent},
   //Feedback Type     
  { path: 'add-feed-back-type', component: AddFeedbackTypeComponent,canActivate: [AuthGuardService],data: {menuId: 80, parentMenuId: 28, permType:'add_perm'}},  
  { path: 'update-feed-back-type/:id', component: UpdateFeedbackTypeComponent,canActivate: [AuthGuardService],data: {menuId: 80, parentMenuId: 28, permType:'edit_perm'}},  
  { path: 'view-feed-back-type', component: ViewFeedbackTypeComponent,canActivate: [AuthGuardService],data: {menuId: 80, parentMenuId: 28, permType:'view_perm'}},  
  { path: 'delete-feed-back-type/:id', component: DeleteFeedbackTypeComponent,canActivate: [AuthGuardService],data: {menuId: 80, parentMenuId: 28, permType:'delete_perm'}},
  //Collection Employee Feedback     
  { path: 'add-feed-back', component: AddFeedbackComponent,canActivate: [AuthGuardService],data: {menuId: 80, parentMenuId: 28, permType:'add_perm'}},  
  { path: 'update-feed-back/:id', component: UpdateFeedbackComponent,canActivate: [AuthGuardService],data: {menuId: 80, parentMenuId: 28, permType:'edit_perm'}},  
  { path: 'view-feed-back', component: ViewFeedbackComponent,canActivate: [AuthGuardService],data: {menuId: 80, parentMenuId: 28, permType:'view_perm'}},  
  { path: 'delete-feed-back/:id', component: DeleteFeedbackComponent,canActivate: [AuthGuardService],data: {menuId: 80, parentMenuId: 28, permType:'delete_perm'}},  
 
  //Customer Outstanding Report     
  { path: 'commitmentcustomer-outstanding-report', component: CommitmentcustomerOutstandingComponent,canActivate: [AuthGuardService],data: {menuId: 81, parentMenuId: 42, permType:'add_perm'}}, 
  //Receipt Report     
  { path: 'commitment-receipt-report', component: CommitmentReceiptReportComponent,canActivate: [AuthGuardService],data: {menuId: 82, parentMenuId: 42, permType:'add_perm'}},
  
  //Loan Module     
  { path: 'add-loan', component: AddLoanComponent,canActivate: [AuthGuardService],data: {menuId: 86, parentMenuId: 85, permType:'add_perm'}},
  { path: 'update-loan/:id', component: UpdateLoanComponent,canActivate: [AuthGuardService],data: {menuId: 86, parentMenuId: 85, permType:'edit_perm'}},
  { path: 'view-loan', component: ViewLoanComponent,canActivate: [AuthGuardService],data: {menuId: 86, parentMenuId: 85, permType:'view_perm'}},
  { path: 'delete-loan/:id', component: DeleteLoanComponent,canActivate: [AuthGuardService],data: {menuId: 86, parentMenuId: 85, permType:'delete_perm'}},
  //Loan Receipt     
  { path: 'add-loan-receipt', component: AddLoanReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 87, parentMenuId: 85, permType:'add_perm'}},
  { path: 'update-loan-receipt/:id', component: UpdateLoanReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 87, parentMenuId: 85, permType:'edit_perm'}},
  { path: 'view-loan-receipt', component: ViewLoanReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 87, parentMenuId: 85, permType:'view_perm'}},
  { path: 'delete-loan-receipt/:receipt_no', component: DeleteLoanReceiptComponent,canActivate: [AuthGuardService],data: {menuId: 87, parentMenuId: 85, permType:'delete_perm'}},  

  //Log Monitoring Report  
   { path: 'log-monitoring-report', component: LogMonitoringReportComponent,canActivate: [AuthGuardService],data: {menuId: 88, parentMenuId: 42, permType:'add_perm'}},
  
 //Loan Receipt Report  
 { path: 'loan-receipt-report', component: LoanReceiptReportComponent,canActivate: [AuthGuardService],data: {menuId: 84, parentMenuId: 42, permType:'add_perm'}},
  //Log Monitoring Report  
 { path: 'log-monitoring-report', component: LogMonitoringReportComponent,canActivate: [AuthGuardService],data: {menuId: 89, parentMenuId: 42, permType:'add_perm'}},
 //Employee Incentive
 { path: 'add-employee-incentive', component: AddEmployeeIncentiveComponent,canActivate: [AuthGuardService],data: {menuId: 96, parentMenuId: 37, permType:'add_perm'}}, 
 { path: 'update-employee-incentive', component: UpdateEmployeeIncentiveComponent,canActivate: [AuthGuardService],data: {menuId: 96, parentMenuId: 37, permType:'edit_perm'}}, 
 { path: 'view-employee-incentive', component: ViewEmployeeIncentiveComponent,canActivate: [AuthGuardService],data: {menuId: 96, parentMenuId: 37, permType:'view_perm'}},
 { path: 'delete-employee-incentive', component: DeleteEmployeeIncentiveComponent,canActivate: [AuthGuardService],data: {menuId: 96, parentMenuId: 37, permType:'delete_perm'}},
 //Employee Incentive Report
 { path: 'incentive-payment-report', component: IncentivePaymentReportComponent,canActivate: [AuthGuardService],data: {menuId: 98, parentMenuId: 42, permType:'add_perm'}},
 { path: 'incentive-pending-report', component: IncentivePendingReportComponent,canActivate: [AuthGuardService],data: {menuId: 97, parentMenuId: 42, permType:'add_perm'}},
  
 //Lead Management Report
 { path: 'lead-management-report', component: LeadReportComponent,canActivate: [AuthGuardService],data: {menuId: 95, parentMenuId: 42, permType:'add_perm'}},
 //Source Type
 { path: 'add-source-type', component: AddSourceTypeComponent,canActivate: [AuthGuardService],data: {menuId: 90, parentMenuId: 2, permType:'add_perm'}},
 { path: 'update-source-type/:id', component: UpdateSourceTypeComponent,canActivate: [AuthGuardService],data: {menuId: 90, parentMenuId: 2, permType:'edit_perm'}},
 { path: 'view-source-type', component: ViewSourceTypeComponent,canActivate: [AuthGuardService],data: {menuId: 90, parentMenuId: 2, permType:'view_perm'}},
 { path: 'delete-source-type/:id', component: DeleteSourceTypeComponent,canActivate: [AuthGuardService],data: {menuId: 90, parentMenuId: 2, permType:'delete_perm'}},
 //Loan Customer Report
 { path: 'loan-customer-report', component: LoanCustomerReportComponent,canActivate: [AuthGuardService],data: {menuId: 91, parentMenuId: 42, permType:'add_perm'}},
 //Loan Customerwise Outstanding Report
 { path: 'loan-customerwise-outstanding-report', component: LoanCustomerwiseOutstandingReportComponent,canActivate: [AuthGuardService],data: {menuId: 93, parentMenuId: 42, permType:'add_perm'}},
 //Loan Dayclosing Report
 { path: 'loan-day-closing-report', component: LoanDayClosingReportComponent,canActivate: [AuthGuardService],data: {menuId: 94, parentMenuId: 42, permType:'add_perm'}},
 //Auction Profit Report
 { path: 'commission-profit-report', component: CommissionProfitReportComponent,canActivate: [AuthGuardService],data: {menuId: 99, parentMenuId: 42, permType:'add_perm'}}, 
 //Loan Customer Document
 { path: 'add-loan-customer-document', component: AddLoanCustomerDocumentComponent,canActivate: [AuthGuardService],data: {menuId: 100, parentMenuId: 85, permType:'add_perm'}}, 
 { path: 'update-loan-customer-document/:id', component: UpdateLoanCustomerDocumentComponent,canActivate: [AuthGuardService],data: {menuId: 100, parentMenuId: 85, permType:'edit_perm'}}, 
 { path: 'view-loan-customer-document', component: ViewLoanCustomerDocumentComponent,canActivate: [AuthGuardService],data: {menuId: 100, parentMenuId: 85, permType:'view_perm'}},
 { path: 'delete-loan-customer-document/:id', component: DeleteLoanCustomerDocumentComponent,canActivate: [AuthGuardService],data: {menuId: 100, parentMenuId: 85, permType:'delete_perm'}}, 
 //Employee Salary
 { path: 'employee-salary', component: EmployeeSalaryComponent,canActivate: [AuthGuardService],data: {menuId: 101, parentMenuId: 34, permType:'add_perm'}}, 
 { path: 'update-employee-salary/:branchId/:attYear/:attMonth/:attDate', component: UpdateEmployeeSalaryComponent,canActivate: [AuthGuardService],data: {menuId: 101, parentMenuId: 34, permType:'edit_perm'}},
 { path: 'view-employee-salary', component: ViewEmployeeSalaryComponent,canActivate: [AuthGuardService],data: {menuId: 101, parentMenuId: 34, permType:'view_perm'}},
 { path: 'delete-employee-salary/:branchId/:attYear/:attMonth/:attDate', component: DeleteEmployeeSalaryComponent,canActivate: [AuthGuardService],data: {menuId: 101, parentMenuId: 34, permType:'delete_perm'}},
 //Accounting Group
 { path: 'add-accounting-group', component: AddAccountingGroupComponent,canActivate: [AuthGuardService],data: {menuId: 102, parentMenuId: 2, permType:'add_perm'}}, 
 { path: 'update-accounting-group/:id', component: UpdateAccountingGroupComponent,canActivate: [AuthGuardService],data: {menuId: 102, parentMenuId: 2, permType:'edit_perm'}},
 { path: 'view-accounting-group', component: ViewAccountingGroupComponent,canActivate: [AuthGuardService],data: {menuId: 102, parentMenuId: 2, permType:'view_perm'}},
 { path: 'delete-accounting-group/:id', component: DeleteAccountingGroupComponent,canActivate: [AuthGuardService],data: {menuId: 102, parentMenuId: 2, permType:'delete_perm'}},
 //Accounting Head
 { path: 'add-accounting-head', component: AddAccountingHeadComponent,canActivate: [AuthGuardService],data: {menuId: 103, parentMenuId: 2, permType:'add_perm'}}, 
 { path: 'update-accounting-head/:id', component: UpdateAccountingHeadComponent,canActivate: [AuthGuardService],data: {menuId: 103, parentMenuId: 2, permType:'edit_perm'}},
 { path: 'view-accounting-head', component: ViewAccountingHeadComponent,canActivate: [AuthGuardService],data: {menuId: 103, parentMenuId: 2, permType:'view_perm'}},
 { path: 'delete-accounting-head/:id', component: DeleteAccountingHeadComponent,canActivate: [AuthGuardService],data: {menuId: 103, parentMenuId: 2, permType:'delete_perm'}},
 //Transaction Expenses / Income
 { path: 'add-expenses-income', component: AddExpensesIncomeComponent,canActivate: [AuthGuardService],data: {menuId: 104, parentMenuId: 37, permType:'add_perm'}}, 
 { path: 'update-expenses-income/:id', component: UpdateExpensesIncomeComponent,canActivate: [AuthGuardService],data: {menuId: 104, parentMenuId: 37, permType:'edit_perm'}},
 { path: 'view-expenses-income', component: ViewExpensesIncomeComponent,canActivate: [AuthGuardService],data: {menuId: 104, parentMenuId: 37, permType:'view_perm'}},
 { path: 'delete-expenses-income/:id', component: DeleteExpensesIncomeComponent,canActivate: [AuthGuardService],data: {menuId: 104, parentMenuId: 37, permType:'delete_perm'}},
 //Transaction Expenses / Income Report
 { path: 'add-expenses-income-report', component: AccountExpensesReportComponent,canActivate: [AuthGuardService],data: {menuId: 105, parentMenuId: 42, permType:'add_perm'}},
 //Trail Balance
 { path: 'trial-balance', component: TrialBalanceComponent,canActivate: [AuthGuardService],data: {menuId: 106, parentMenuId: 42, permType:'add_perm'}}, 
 //Profit Loss Report
 { path: 'profit-loss-report', component: ProfitLossReportComponent,canActivate: [AuthGuardService],data: {menuId: 107, parentMenuId: 42, permType:'add_perm'}},
 //Unknown Transaction
 { path: 'unknown-transaction', component: UnknownTransactionComponent,canActivate: [AuthGuardService],data: {menuId: 110, parentMenuId: 37, permType:'add_perm'}},
 { path: 'update-unknown-transaction/:id', component: UpdateUnknownTransactionComponent,canActivate: [AuthGuardService],data: {menuId: 110, parentMenuId: 37, permType:'edit_perm'}},
 { path: 'view-unknown-transaction', component: ViewUnknownTransactionComponent,canActivate: [AuthGuardService],data: {menuId: 110, parentMenuId: 2, permType:'view_perm'}},
 { path: 'delete-unknown-transaction/:id', component: DeleteUnknownTransactionComponent,canActivate: [AuthGuardService],data: {menuId: 110, parentMenuId: 2, permType:'delete_perm'}},
 //Balance Sheet
 { path: 'balance-sheet-report', component: BalanceSheetReportComponent,canActivate: [AuthGuardService],data: {menuId: 108, parentMenuId: 42, permType:'add_perm'}},
 //Day Book
 { path: 'day-book-report', component: DayBookComponent},
 //Commitment Enrolrefund Amount
 { path: 'add-commitment-enrol', component: AddCommitmentenrolRefundComponent,canActivate: [AuthGuardService],data: {menuId: 109, parentMenuId: 37, permType:'add_perm'}},
 //installmentwise report
 { path: 'installmentwise-report', component: InstallmentwiseReportComponent,canActivate: [AuthGuardService],data: {menuId: 111, parentMenuId: 42, permType:'add_perm'}},
 //Approval Mapping
 { path: 'add-before-enrol-receipt', component: BeforeEnrolReceiptComponent}, 
 //before enrol receipt report
 { path: 'before-enrol-receipt-report', component: BeforeEnrolReceiptReportComponent},
 //minutes of auction
 { path: 'minutes-of-auction', component: MinutesOfAuctionComponent},
 { path: 'view-minutes-of-auction', component: ViewMinutesOfAuctionComponent},
 //minutes of auction report
 { path: 'auction-minutes-report', component: AuctionMinutesReportComponent},
 //customer birthday report
 { path: 'customer-birthday-report', component: CustomerBirthdayComponent},
 //SMS
 { path: 'send-message', component: SendMessageComponent},
 //birthday mail and message
 { path: 'birthday-mail/:id', component: BirthdayMailComponent},
 { path: 'birthday-message/:id', component: BirthdayMessageComponent},
 //City
 { path: 'add-country', component: AddCountryComponent,canActivate: [AuthGuardService],data: {menuId: 15, parentMenuId: 2, permType:'add_perm'}},  
 { path: 'update-country/:id', component: UpdateCountryComponent,canActivate: [AuthGuardService],data: {menuId: 15, parentMenuId: 2, permType:'edit_perm'}},  
 { path: 'view-country', component: ViewCountryComponent,canActivate: [AuthGuardService],data: {menuId: 15, parentMenuId: 2, permType:'view_perm'}},  
 { path: 'delete-country/:id', component: DeleteCountryComponent,canActivate: [AuthGuardService],data: {menuId: 15, parentMenuId: 2, permType:'delete_perm'}},
 //Nominee
 { path: 'add-nominee', component: AddNomineeComponent},  
 { path: 'update-nominee/:id', component: UpdateNomineeComponent},  
 { path: 'view-nominee', component: ViewNomineeComponent},  
 { path: 'delete-nominee/:id', component: DeleteNomineeComponent},  
 //pledge customer
 { path: 'add-pledge-customer', component: PledgeCustomerComponent}, 
 { path: 'update-pledge-customer/:id', component: UpdatePledgeCustomerComponent}, 
 { path: 'pledge-customer-report', component: PledgeCustomerReportComponent},
 //transfer customer report
 { path: 'transfer-customer-report', component: TransferCustomerReportComponent}, 
 { path: 'add-transfer-customer', component: AddTransferCustomerComponent}, 
 //Bid Cancellation Letter
 { path: 'bid-cancelation-letter/:id', component: BidCancelationLetterComponent}, 
 //payment voucher
 { path: 'payment-voucher/:id', component: PaymentVoucherComponent},
 //gst voucher
 { path: 'gst-voucher', component: GstVoucherComponent},
 { path: 'gst-print/:id', component: GstPrintComponent},  
 //cheque report
 { path: 'cheque-in-hand', component: ChequeInHandComponent},
 { path: 'cheque-in-bank', component: ChequeInBankComponent}, 
 { path: 'cheque-issue', component: ChequeIssueComponent}, 
 //fd report
 { path: 'fd-report', component: FdReportComponent},
 //termination report
 { path: 'termination-report', component: TerminationGroupReportComponent},
 //add bid advance
 { path: 'add-bid-advance', component: AddBidAdvanceComponent},
 //sms content master
 { path: 'sms-content', component: SmsContentComponent},
 { path: 'view-sms-content', component: ViewSmsTemplateComponent},
 { path: 'delete-sms-content/:id', component: DeleteSmsTemplateComponent}, 
 //groupwise abstract
 { path: 'groupwise-outstanding-abstract', component: GroupwiseOutstandingAbstractComponent},
 //current month collection
 { path: 'current-month-collection', component: CurrentMonthCollectionComponent},
 { path: 'historical-performance-report', component: HistoricalPerformanceReportComponent, canActivate: [AuthGuardService], data: { menuId: 51, parentMenuId: 42, permType: 'add_perm' } },
 { path: 'business-forecast-report', component: BusinessForecastReportComponent, canActivate: [AuthGuardService], data: { menuId: 51, parentMenuId: 42, permType: 'add_perm' } },
 //unenrollment report
 { path: 'unenrollment-report', component: UnenrollmentReportComponent},
 //starting group report
 { path: 'starting-group-report', component: StartingGroupReportComponent},
 //Family Code
 { path: 'add-family-code', component: AddFamilyCodeComponent},  
 { path: 'update-family-code/:id', component: UpdateFamilyCodeComponent},  
 { path: 'view-family-code', component: ViewFamilyCodeComponent},  
 { path: 'delete-family-code/:id', component: DeleteFamilyCodeComponent},
  //default interest
 { path: 'default-interest-report', component: GroupAbstractDefaultInterestComponent},
 //family code balance sheet
 { path: 'familycode-balancesheet-report', component: FamilyCodeWiseBalancesheetComponent},
 // delay list
 { path: 'delay-list-report', component: DelayListReportComponent},
 //familycode wise due list
 { path: 'familycode-due-report', component: FamilyCodeWiseDueListComponent},
 //customer label list
 { path: 'customer-label-report', component: CustomerLabelListComponent},
 //Bidding Form
 { path: 'bidding-form', component: BiddingFormComponent},
 //Bidding Customer
 { path: 'bidding-customer', component: BiddingCustomerComponent},
 { path: 'last-auction-details/:group_name/:ticket_no', component: LastAuctionDetailsComponent},
 { path: 'log-view', component: LogViewComponent},
 { path: 'log-bidding/:id', component: LogBiddingComponent},
 //Bid Delete
 { path: 'bidding-delete/:id', component: BiddingDeleteComponent},
 //Employee Collection Report
 { path: 'employee-collection-report', component: EmployeeCollectionReportComponent},
 //other Receipt Print
 { path: 'other-receipt-print/:id', component: OtherReceiptPrintComponent},
 //Passbook before enrol
 { path: 'passbook-before-entry', component: PassbookBeforeEntryComponent},
 { path: 'passbook-before-index/:enrollmentId', component: PassbookBeforeIndexComponent},
 { path: 'passbook-before-transactions/:enrollmentId', component: PassbookBeforeTransactionComponent},
 { path: 'passbook-before-transaction-index/:enrollmentId', component: BeforePassbookTransactionIndexComponent},
 //sms sales promotion
 { path: 'sales-promotion', component: SalesPromotionSmsComponent}, 
 //case stages
 { path: 'add-case-stage', component: AddCaseStageComponent},  
 { path: 'view-case-stage', component: ViewCaseStageComponent},  
 { path: 'delete-case-stage/:id', component: DeleteCaseStageComponent},
 //Lawyer
 { path: 'add-lawyer', component: AddLawyerComponent},  
 { path: 'view-lawyer', component: ViewLawyerComponent},  
 { path: 'delete-lawyer/:id', component: DeleteLawyerComponent},
 //suit file report
 { path: 'suit-file-report', component: SuitFileComponent}, 
 //suit file
 { path: 'add-suit-file/:id', component: AddSuitFileComponent},
 //settlement report
 { path: 'settlement-report', component: SettlementReportComponent},
//summary report
{ path: 'auction-result-report', component: AuctionResultReportComponent},
{ path: 'due-arrear-report', component: DueArrearReportComponent},
{ path: 'review-statement-report', component: ReviewStatementReportComponent},
{ path: 'memberwise-outstanding-report', component: MemberwiseOutstandingReportComponent},
{ path: 'areawise-outstanding-report', component: AreawiseOutstandingReportComponent},
{ path: 'daily-collection-statement', component: DailyCollectionStatementComponent},
{ path: 'areawise-collectable-collection-report', component: AreawiseCollectableCollectionReportComponent},
{ path: 'areawise-summary-report', component: AreawiseSummaryComponent},
{ path: 'directorwise-collectable-collection-report', component: DirectorwiseCollectableCollectionReportComponent},
{ path: 'areawise-due-arrear-report', component: AreawiseDueArrearReportComponent},
{ path: 'directorwise-due-arrear-report', component: DirectorwiseDueArrearReportComponent},
{ path: 'gift-issue/:id', component: GiftIssueComponent},
{ path: 'closure', component: ClosureComponent},
{ path: 'auction-result', component: AuctionResultlComponent},
{ path: 'draw', component: DrawComponent},
{ path: 'debit-credit-view/:voucher_no', component: DebitCreditViewComponent},
{ path: 'gift-issue-view/:id', component: GiftIssueViewComponent},
{ path: 'draw-receipt', component: DrawReceiptComponent},
{ path: 'auction-value', component: AuctionValueComponent},
{ path: 'auction-report-original', component: AuctionReportOriginalComponent},
{ path: 'delete-account-expenses', component: DeleteAccountExpenseComponent},
{ path: 'closure-report', component: ClosureReportComponent},
{ path: 'draw-report', component: DrawReportComponent},
{ path: 'add-employee-document', component: AddEmployeeDocumentComponent, canActivate: [AuthGuardService], data: {menuId: 118, parentMenuId: 34, permType:'view_perm'}},
{ path: 'update-employee-document/:id', component: AddEmployeeDocumentComponent, canActivate: [AuthGuardService], data: {menuId: 118, parentMenuId: 34, permType:'view_perm'}},
{ path: 'view-employee-document', component: ViewEmployeeDocumentComponent, canActivate: [AuthGuardService], data: {menuId: 118, parentMenuId: 34, permType:'view_perm'}},
{ path: 'overall-business-report', component: OverallBusinessReportComponent},
{ path: 'collection-checking-report', component: CollectionCheckingReportComponent},
{ path: 'debit-credit-edit/:voucher_no', component: DebitCreditEditComponent},
{ path: 'passbook-verify', component: PassbookVerifyComponent},
{ path: 'company-profit-report', component: CompanyProfitReportComponent},
{ path: 'company-excess-report', component: CompanyExcessReportComponent},
{ path: 'cancel-customer-report', component: CancelCustomerReportComponent},
{ path: 'target-incentive', component: TargetIncentiveComponent},
{ path: 'add-pigmy-collection', component: AddPigmyCollectionComponent},
{ path: 'add-task', component: TaskManagementComponent, canActivate: [AuthGuardService], data: {menuId: 118, parentMenuId: 34, permType:'view_perm'}},
{ path: 'set-employee-target', component: SetEmployeeTargetComponent, canActivate: [AuthGuardService], data: {menuId: 118, parentMenuId: 34, permType:'view_perm'}},
{ path: 'view-employee-mobile-modules', component: ViewEmployeeMobileModulesComponent, canActivate: [AuthGuardService], data: {menuId: 118, parentMenuId: 34, permType:'view_perm'}},
{ path: 'pigmy-report', component: PigmyReportComponent},
{ path: 'task-report', component: TaskReportComponent, canActivate: [AuthGuardService], data: {menuId: 118, parentMenuId: 34, permType:'view_perm'}},
{ path: 'view-daily-tasks-targets', component: ViewDailyTasksTargetsComponent, canActivate: [AuthGuardService], data: {menuId: 118, parentMenuId: 34, permType:'view_perm'}},
{ path: 'commitment-receipt-print/:receipt_no', component: CommitmentReceiptPrintComponent},
{ path: 'before-delete-receipt/:receipt_no', component: BeforeDeleteReceiptComponent},
{ path: 'refund-transfer-report', component: RefundTransferReportComponent},
{ path: 'transfer-customer-details-report', component: TransferCustomerDetailsReportComponent},
{ path: 'auction-report-final', component: AuctionReportFinalComponent},
{ path: 'commitment-report', component: CommitmentReportComponent, canActivate: [AuthGuardService], data: { menuId: 69, parentMenuId: 48, permType: 'view_perm' }},
{ path: 'cash-book', component: CashbookComponent},
{ path: 'cashbook', component: CashbookComponent},
{ path: 'bank-book', component: BankbookComponent},
])],

  exports: [RouterModule]
})

export class adminpanelRoutingModule 
{ 

}
