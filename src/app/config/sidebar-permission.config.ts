/**
 * Security Permissions UI tree — must match layout-sidenav + layout-navbar order.
 * storageModuleId = top-level module id used in saved role_access (findRecord parent).
 */
export interface PermissionLeaf {
  title: string;
  menuId: number;
  storageModuleId: number;
  pageType: number;
  route?: string;
}

export interface PermissionGroup {
  title: string;
  leaves: PermissionLeaf[];
}

export interface PermissionSection {
  title: string;
  /** Real dynamic_menus id when section maps to one module (e.g. Dashboard). */
  menuId?: number;
  pageType?: number;
  virtual?: boolean;
  leaves?: PermissionLeaf[];
  groups?: PermissionGroup[];
}

export const MODULE_TITLES: { [id: number]: string } = {
  1: 'Dashboard',
  2: 'Masters',
  19: 'Slabs',
  28: 'Subscriber Management',
  34: 'Employee Management',
  37: 'Transaction Management',
  42: 'Reports',
  48: 'Enrollment Report',
  65: 'Payment Report',
  66: 'Collection Report',
  67: 'Accounts Report',
  68: 'Business Development',
  83: 'Loan Report',
  88: 'Log Monitoring',
  124: 'Customer Report',
  85: 'Loan Management',
  53: 'Security Permissions',
};

/** Order of modules in saved role_access (after flatten). */
export const SAVED_MODULE_ORDER = [1, 2, 19, 28, 34, 37, 42, 48, 65, 43, 66, 67, 68, 83, 88, 124, 85, 62, 53];

const P = {
  VIEW: 1,
  REPORT: 2,
  DASH: 3,
  GROUP: 4,
};

/** Mirrors layout-sidenav.component.html */
export const SIDEBAR_PERMISSION_SECTIONS: PermissionSection[] = [
  {
    title: 'Dashboard',
    menuId: 1,
    pageType: P.DASH,
    leaves: [],
  },
  {
    title: 'Chit',
    virtual: true,
    leaves: [
      { title: 'Schemes', menuId: 8, storageModuleId: 2, pageType: P.VIEW, route: 'view-scheme' },
      { title: 'Profile Creation', menuId: 5, storageModuleId: 28, pageType: P.VIEW, route: 'view-customer' },
      { title: 'SPN Approved Customers', menuId: 32, storageModuleId: 28, pageType: P.VIEW, route: 'view-commitment-chit' },
      { title: 'SPN Eligibility Approval', menuId: 152, storageModuleId: 28, pageType: P.VIEW, route: 'commitment-eligibility-approval' },
      { title: 'Receipt', menuId: 57, storageModuleId: 37, pageType: P.VIEW, route: 'add-commitment-receipt' },
      { title: 'Payment', menuId: 56, storageModuleId: 28, pageType: P.VIEW, route: 'view-commitment-payment' },
      { title: 'Refund', menuId: 109, storageModuleId: 37, pageType: P.VIEW, route: 'add-commitment-enrol' },
      { title: 'Other Charges Receipt', menuId: 60, storageModuleId: 37, pageType: P.VIEW, route: 'other-receipt-charges-report' },
    ],
  },
  {
    title: 'Slabs',
    virtual: true,
    leaves: [
      { title: 'Due Slab', menuId: 20, storageModuleId: 2, pageType: P.VIEW, route: 'view-due-slab' },
      { title: 'Payment Slab', menuId: 24, storageModuleId: 2, pageType: P.VIEW, route: 'view-payment-slab' },
    ],
  },
  {
    title: 'HR',
    virtual: true,
    leaves: [
      { title: 'Employee', menuId: 3, storageModuleId: 34, pageType: P.VIEW, route: 'view-employee' },
      { title: 'Designation', menuId: 12, storageModuleId: 2, pageType: P.VIEW, route: 'view-designation' },
      { title: 'Role', menuId: 11, storageModuleId: 2, pageType: P.VIEW, route: 'view-role' },
      { title: 'Security Permissions', menuId: 53, storageModuleId: 0, pageType: P.VIEW, route: 'add-permission' },
      { title: 'User Logins', menuId: 36, storageModuleId: 34, pageType: P.VIEW, route: 'view-employee-login' },
      { title: 'Employee Salary', menuId: 101, storageModuleId: 34, pageType: P.VIEW, route: 'employee-salary' },
      { title: 'Collection Area Mapping', menuId: 55, storageModuleId: 34, pageType: P.VIEW, route: 'view-collection-area-mapping' },
      { title: 'Attendance', menuId: 35, storageModuleId: 34, pageType: P.VIEW, route: 'view-employee-attendance' },
      { title: 'Employee Documents', menuId: 118, storageModuleId: 34, pageType: P.VIEW, route: 'view-employee-document' },
      { title: 'Auction On Holidays', menuId: 16, storageModuleId: 2, pageType: P.VIEW, route: 'view-auction-on-holidays' },
      { title: 'Task Management', menuId: 118, storageModuleId: 34, pageType: P.VIEW, route: 'add-task' },
    ],
  },
  {
    title: 'CRM',
    virtual: true,
    leaves: [
      { title: 'Leads', menuId: 41, storageModuleId: 28, pageType: P.VIEW, route: 'view-lead-management' },
      { title: 'Lead Source', menuId: 90, storageModuleId: 2, pageType: P.VIEW, route: 'view-source-type' },
    ],
  },
  {
    title: 'Loan',
    virtual: true,
    leaves: [
      { title: 'Document', menuId: 100, storageModuleId: 85, pageType: P.VIEW, route: 'view-loan-customer-document' },
      { title: 'Disbursement', menuId: 86, storageModuleId: 85, pageType: P.VIEW, route: 'view-loan' },
      { title: 'Receipt', menuId: 87, storageModuleId: 85, pageType: P.VIEW, route: 'add-loan-receipt' },
    ],
  },
  {
    title: 'Legal',
    virtual: true,
    leaves: [
      { title: 'Case Stages', menuId: 116, storageModuleId: 2, pageType: P.VIEW, route: 'view-case-stage' },
      { title: 'Lawyer', menuId: 117, storageModuleId: 2, pageType: P.VIEW, route: 'view-lawyer' },
    ],
  },
  {
    title: 'Location',
    virtual: true,
    leaves: [
      { title: 'Regional Office', menuId: 6, storageModuleId: 2, pageType: P.VIEW, route: 'view-head-office' },
      { title: 'Branch Office', menuId: 7, storageModuleId: 2, pageType: P.VIEW, route: 'view-branch' },
      { title: 'Country', menuId: 113, storageModuleId: 2, pageType: P.VIEW, route: 'view-country' },
      { title: 'State', menuId: 114, storageModuleId: 2, pageType: P.VIEW, route: 'view-state' },
      { title: 'District', menuId: 115, storageModuleId: 2, pageType: P.VIEW, route: 'view-district' },
      { title: 'City', menuId: 15, storageModuleId: 2, pageType: P.VIEW, route: 'view-city' },
      { title: 'Collection Area', menuId: 10, storageModuleId: 2, pageType: P.VIEW, route: 'view-collection-area' },
    ],
  },
  {
    title: 'Accounts',
    virtual: true,
    leaves: [
      { title: 'Bank Details', menuId: 17, storageModuleId: 2, pageType: P.VIEW, route: 'view-bank-details' },
      { title: 'Charges Types', menuId: 13, storageModuleId: 2, pageType: P.VIEW, route: 'view-charges-type' },
      { title: 'Account Groups', menuId: 102, storageModuleId: 2, pageType: P.VIEW, route: 'view-accounting-group' },
      { title: 'Account Head', menuId: 103, storageModuleId: 2, pageType: P.VIEW, route: 'view-accounting-head' },
    ],
  },
  {
    title: 'Reports',
    virtual: true,
    groups: [
      {
        title: 'Subscribers Report',
        leaves: [
          { title: 'Commitment Chit - Enrolled Subscriber', menuId: 69, storageModuleId: 48, pageType: P.REPORT, route: 'commitment-report' },
          { title: 'Ledger', menuId: 61, storageModuleId: 37, pageType: P.VIEW, route: 'customer-ledger' },
          { title: 'Pledge', menuId: 132, storageModuleId: 48, pageType: P.REPORT, route: 'pledge-customer-report' },
          { title: 'Suit Filed', menuId: 133, storageModuleId: 48, pageType: P.REPORT, route: 'suit-file-report' },
        ],
      },
      {
        title: 'Payment Report',
        leaves: [
          { title: 'Refund', menuId: 63, storageModuleId: 65, pageType: P.REPORT, route: 'payment-refund-report' },
          { title: 'Commitment Chit Payments', menuId: 70, storageModuleId: 65, pageType: P.REPORT, route: 'commitment-payment-report' },
          { title: 'Commitment Chit Pending Payments', menuId: 71, storageModuleId: 65, pageType: P.REPORT, route: 'commitment-pending-report' },
        ],
      },
      {
        title: 'Outstanding Report',
        leaves: [
          { title: 'Commitment Customer', menuId: 81, storageModuleId: 43, pageType: P.REPORT, route: 'commitmentcustomer-outstanding-report' },
          { title: 'Loan Outstanding', menuId: 93, storageModuleId: 43, pageType: P.REPORT, route: 'loan-customerwise-outstanding-report' },
        ],
      },
      {
        title: 'Collection Report',
        leaves: [
          { title: 'Commitment Receipt Report', menuId: 82, storageModuleId: 66, pageType: P.REPORT, route: 'commitment-receipt-report' },
          { title: 'Historical performance (3 / 6 / 12 mo)', menuId: 51, storageModuleId: 66, pageType: P.REPORT, route: 'historical-performance-report' },
          { title: 'Business forecast (3 mo vs YoY)', menuId: 51, storageModuleId: 66, pageType: P.REPORT, route: 'business-forecast-report' },
          { title: 'Employee Collection Report', menuId: 145, storageModuleId: 66, pageType: P.REPORT, route: 'employee-collection-report' },
          { title: 'Before Enrollment Receipt Report', menuId: 146, storageModuleId: 66, pageType: P.REPORT, route: 'before-enrol-receipt-report' },
          { title: 'Other Charges Report', menuId: 74, storageModuleId: 66, pageType: P.REPORT, route: 'other-receipt-charges-report' },
          { title: 'Settlement Report', menuId: 147, storageModuleId: 66, pageType: P.REPORT, route: 'settlement-report' },
          { title: 'Collection Feedback', menuId: 127, storageModuleId: 124, pageType: P.REPORT, route: 'view-feed-back' },
        ],
      },
      {
        title: 'Accounts Report',
        leaves: [
          { title: 'Day Book', menuId: 148, storageModuleId: 67, pageType: P.REPORT, route: 'day-book-report' },
          { title: 'Day Closing Report', menuId: 75, storageModuleId: 67, pageType: P.REPORT, route: 'day-closing-report' },
          { title: 'Accounts Report', menuId: 76, storageModuleId: 67, pageType: P.REPORT, route: 'accounts-report' },
          { title: 'Loan - Day closing report', menuId: 94, storageModuleId: 67, pageType: P.REPORT, route: 'loan-day-closing-report' },
          { title: 'Account Income/Expenses', menuId: 105, storageModuleId: 67, pageType: P.REPORT, route: 'add-expenses-income-report' },
          { title: 'Trial Balance', menuId: 106, storageModuleId: 67, pageType: P.REPORT, route: 'trial-balance' },
          { title: 'Profit & Loss', menuId: 108, storageModuleId: 67, pageType: P.REPORT, route: 'profit-loss-report' },
          { title: 'Balance Sheet', menuId: 107, storageModuleId: 67, pageType: P.REPORT, route: 'balance-sheet-report' },
        ],
      },
      {
        title: 'Business Development Report',
        leaves: [
          { title: 'Business Agent Report', menuId: 77, storageModuleId: 68, pageType: P.REPORT, route: 'business-agent-report' },
          { title: 'Commission Pending Report', menuId: 78, storageModuleId: 68, pageType: P.REPORT, route: 'commission-pending-report' },
          { title: 'Commission Payment report', menuId: 150, storageModuleId: 68, pageType: P.REPORT, route: 'commission-payment-report' },
          { title: 'Incentive Payment', menuId: 98, storageModuleId: 68, pageType: P.REPORT, route: 'incentive-payment-report' },
          { title: 'Incentive Pending', menuId: 97, storageModuleId: 68, pageType: P.REPORT, route: 'incentive-pending-report' },
          { title: 'Lead Report', menuId: 95, storageModuleId: 68, pageType: P.REPORT, route: 'lead-management-report' },
        ],
      },
      {
        title: 'Loan Report',
        leaves: [
          { title: 'Loan Receipt', menuId: 84, storageModuleId: 83, pageType: P.REPORT, route: 'loan-receipt-report' },
          { title: 'Loan Customer Report', menuId: 91, storageModuleId: 83, pageType: P.REPORT, route: 'loan-customer-report' },
          { title: 'Loan Outstanding', menuId: 93, storageModuleId: 83, pageType: P.REPORT, route: 'loan-customerwise-outstanding-report' },
          { title: 'Loan - Day closing report', menuId: 94, storageModuleId: 83, pageType: P.REPORT, route: 'loan-day-closing-report' },
        ],
      },
      {
        title: 'Log Monitoring Report',
        leaves: [
          { title: 'Logs', menuId: 89, storageModuleId: 88, pageType: P.REPORT, route: 'log-monitoring-report' },
        ],
      },
    ],
  },
  {
    title: 'Top Bar (Header Shortcuts)',
    virtual: true,
    groups: [
      {
        title: 'Subscriber',
        leaves: [
          { title: 'Profile Creation', menuId: 5, storageModuleId: 28, pageType: P.VIEW, route: 'add-customer' },
          { title: 'SPN Approved', menuId: 32, storageModuleId: 28, pageType: P.VIEW, route: 'view-commitment-chit' },
          { title: 'SPN Approval', menuId: 152, storageModuleId: 28, pageType: P.VIEW, route: 'commitment-eligibility-approval' },
          { title: 'Nominee', menuId: 151, storageModuleId: 28, pageType: P.VIEW, route: 'add-nominee' },
          { title: 'Guarantor', menuId: 4, storageModuleId: 28, pageType: P.VIEW, route: 'add-guarantor' },
          { title: 'Documents', menuId: 38, storageModuleId: 28, pageType: P.VIEW, route: 'view-documents' },
          { title: 'Ledger', menuId: 61, storageModuleId: 37, pageType: P.VIEW, route: 'customer-ledger' },
        ],
      },
      {
        title: 'Collection',
        leaves: [
          { title: 'Receipt', menuId: 31, storageModuleId: 37, pageType: P.VIEW, route: 'add-receipt' },
          { title: 'Advance Receipt (Before Enrollment)', menuId: 120, storageModuleId: 37, pageType: P.VIEW, route: 'add-before-enrol-receipt' },
          { title: 'Advance Receipt (After Enrollment)', menuId: 58, storageModuleId: 37, pageType: P.VIEW, route: 'add-advanced-receipt' },
          { title: 'Other Charges', menuId: 60, storageModuleId: 37, pageType: P.VIEW, route: 'add-other-receipt-charges' },
          { title: 'Commitment Chit', menuId: 57, storageModuleId: 37, pageType: P.VIEW, route: 'add-commitment-receipt' },
        ],
      },
      {
        title: 'Payment',
        leaves: [
          { title: 'Bidpayment', menuId: 30, storageModuleId: 28, pageType: P.VIEW, route: 'view-auction' },
          { title: 'Bidpayment Adjustment', menuId: 59, storageModuleId: 37, pageType: P.VIEW, route: 'add-bidpayment-adjustment' },
          { title: 'Refund Enrollment Amount', menuId: 33, storageModuleId: 37, pageType: P.VIEW, route: 'add-enrol-refund-amount' },
          { title: 'Commitment Payment', menuId: 56, storageModuleId: 28, pageType: P.VIEW, route: 'add-commitment-payment' },
        ],
      },
      {
        title: 'Accounts',
        leaves: [
          { title: 'Income and Expense', menuId: 104, storageModuleId: 37, pageType: P.VIEW, route: 'add-expenses-income' },
          { title: 'Petty Cash Income', menuId: 27, storageModuleId: 62, pageType: P.VIEW, route: 'add-petty-cash-income' },
          { title: 'Petty cash expenses', menuId: 26, storageModuleId: 62, pageType: P.VIEW, route: 'add-petty-cash-expense' },
          { title: 'Salary', menuId: 101, storageModuleId: 34, pageType: P.VIEW, route: 'employee-salary' },
          { title: 'Incentive', menuId: 96, storageModuleId: 37, pageType: P.VIEW, route: 'add-employee-incentive' },
          { title: 'Commission', menuId: 39, storageModuleId: 28, pageType: P.VIEW, route: 'add-commission-payment' },
          { title: 'Day book', menuId: 148, storageModuleId: 42, pageType: P.REPORT, route: 'day-book-report' },
        ],
      },
    ],
  },
];
