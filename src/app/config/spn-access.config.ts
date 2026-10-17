/**
 * SPN page-level access catalogue (Security Permissions screen + sidebar + route guard).
 *
 * Every sidebar link, section Overview, dashboard design and corporate panel has its own id,
 * saved under role_access module SPN_ACCESS_MODULE_ID. `legacy` lists the older
 * [menuId, parentMenuId] pairs that are written alongside on save, so backend middleware,
 * in-page buttons and roles saved before this catalogue keep working.
 * Ids are permanent once saved in role_access — add new ids, never renumber.
 */

export type AccessAction = 'view' | 'add' | 'edit' | 'delete';

export interface AccessPage {
  id: number;
  title: string;
  route?: string;
  qp?: { [k: string]: string };
  /** Extra route paths guarded by this page (CRUD pages auto-include add-/update-/delete-). */
  routes?: string[];
  actions: AccessAction[];
  legacy: number[][];
  /** Read-only legacy check for roles not yet saved with page access (never written on save). */
  fallback?: number[][];
  overview?: boolean;
  group?: string;
  hint?: string;
}

export interface AccessSection {
  key: string;
  title: string;
  icon: string;
  description: string;
  pages: AccessPage[];
}

export const SPN_ACCESS_MODULE_ID = 500;
export const SPN_ACCESS_MODULE_TITLE = 'SPN Page Access';

const V: AccessAction[] = ['view'];
const VA: AccessAction[] = ['view', 'add'];
const VAE: AccessAction[] = ['view', 'add', 'edit'];
const ALL: AccessAction[] = ['view', 'add', 'edit', 'delete'];
const ANY_DASHBOARD = [[1, 0], [201, 1], [202, 1], [203, 1], [204, 1], [205, 1], [206, 1]];

export const SPN_ACCESS_SECTIONS: AccessSection[] = [
  {
    key: 'dashboards',
    title: 'Dashboards',
    icon: 'fa fa-tachometer',
    description: 'Which dashboard designs the role can open (first selected opens by default).',
    pages: [
      { id: 201, title: 'MD Dashboard – Overall View', route: 'dashboard', actions: V, legacy: [[201, 1]] },
      { id: 202, title: 'Branch Admin Panel', route: 'dashboard', actions: V, legacy: [[202, 1]] },
      { id: 203, title: 'Branch Manager Dashboard', route: 'dashboard', actions: V, legacy: [[203, 1]] },
      { id: 204, title: 'Sales Manager Dashboard', route: 'dashboard', actions: V, legacy: [[204, 1]] },
      { id: 205, title: 'Collections Manager Dashboard', route: 'dashboard', actions: V, legacy: [[205, 1]] },
      { id: 206, title: 'Branch Sales Panel', route: 'dashboard', actions: V, legacy: [[206, 1]] },
    ],
  },
  {
    key: 'dashboard-overviews',
    title: 'Dashboard Overviews (KPI cards)',
    icon: 'fa fa-th',
    description: 'Detail overview opened when a dashboard card is clicked.',
    pages: [
      { id: 511, title: 'Today Collection', route: 'hub/today-collection', actions: V, legacy: [], overview: true },
      { id: 512, title: 'Sales Target', route: 'hub/sales', actions: V, legacy: [], overview: true },
      { id: 513, title: 'Today Enrolment', route: 'hub/today-enrollment', actions: V, legacy: [], overview: true },
      { id: 514, title: 'Pending Collection', route: 'hub/pending-collection', actions: V, legacy: [], overview: true },
      { id: 515, title: 'Overdue Collection', route: 'hub/overdue-collection', actions: V, legacy: [], overview: true },
      { id: 516, title: 'Top Performers', route: 'hub/top-performers', actions: V, legacy: [], overview: true },
      { id: 517, title: 'Today Attendance', route: 'hub/attendance', actions: V, legacy: [], overview: true },
      { id: 518, title: 'Birthdays', route: 'hub/birthdays', actions: V, legacy: [], overview: true },
      { id: 519, title: 'Today Task & Target', route: 'hub/tasks-targets', actions: V, legacy: [], overview: true },
      { id: 520, title: 'Expenses', route: 'hub/expenses', actions: V, legacy: [], overview: true },
      { id: 521, title: 'Settlement Approvals', route: 'hub/settlement-approvals', actions: V, legacy: [], overview: true },
    ],
  },
  {
    key: 'corporate',
    title: 'Corporate Office',
    icon: 'fa fa-building',
    description: 'Company-wide panels for corporate officers.',
    pages: [
      { id: 531, title: 'Corporate Admin Panel', route: 'hub/corporate', actions: V, legacy: [], fallback: ANY_DASHBOARD, overview: true },
      { id: 532, title: 'Branch Overview (all branches)', route: 'branch-overview', actions: V, legacy: [], fallback: ANY_DASHBOARD.concat([[7, 2]]) },
      { id: 533, title: 'Live Map View (dashboard button)', route: 'view-punch-attendance', actions: V, legacy: [[35, 34]] },
    ],
  },
  {
    key: 'my-customers',
    title: 'SPN My Customers',
    icon: 'fa fa-link',
    description: 'Approved chit customers, ledger and category.',
    pages: [
      { id: 541, title: 'Overview', route: 'hub/chit', actions: V, legacy: [], overview: true },
      { id: 542, title: 'Active Customers (SPN Approved)', route: 'view-commitment-chit', routes: ['add-commitment-chit', 'update-commitment-chit', 'delete-commitment-chit'], actions: ALL, legacy: [[32, 28]] },
      { id: 543, title: 'Cancelled Customers', route: 'cancel-customer-report', actions: V, legacy: [[69, 48]] },
      { id: 544, title: 'Customer Category (Excellent / Good / Bad)', route: 'customer-category-report', actions: V, legacy: [[32, 28]] },
      { id: 545, title: 'Category Rules', route: 'customer-score-rules', actions: VAE, legacy: [], fallback: [[32, 28], [69, 48]] },
      { id: 546, title: 'Customer Ledger', route: 'customer-ledger', actions: V, legacy: [[61, 37], [126, 124]] },
    ],
  },
  {
    key: 'new-customers',
    title: 'SPN New Customers',
    icon: 'fa fa-user-plus',
    description: 'Onboarding, KYC verification, approval and branch transfer.',
    pages: [
      { id: 551, title: 'Overview', route: 'hub/new-customers', actions: V, legacy: [], overview: true },
      { id: 552, title: 'Profile Creation', route: 'view-customer', routes: ['add-customer', 'update-customer', 'delete-customer'], actions: ALL, legacy: [[5, 28]] },
      { id: 553, title: 'Enrolment Approval', route: 'commitment-eligibility-approval', actions: VAE, legacy: [[152, 28]] },
      { id: 554, title: 'Verification (KYC Documents)', route: 'view-documents', routes: ['add-document', 'update-document', 'delete-document', 'kyc-upload'], actions: ALL, legacy: [[38, 28]] },
      { id: 555, title: 'Branch Transfer Customer', route: 'branch-transfer-customer', actions: VA, legacy: [[5, 28]] },
      { id: 556, title: 'Branch Transfer Report', route: 'branch-transfer-customer-report', actions: V, legacy: [[5, 28]] },
    ],
  },
  {
    key: 'chit-management',
    title: 'SPN Chit Management',
    icon: 'fa fa-th-large',
    description: 'Groups, schemes, slabs and auction holidays.',
    pages: [
      { id: 561, title: 'Overview', route: 'hub/groups', actions: V, legacy: [], overview: true },
      { id: 562, title: 'SPN New Group (Group Formation)', route: 'spn-new-group', actions: VAE, legacy: [[8, 2]] },
      { id: 563, title: 'Group View', route: 'spn-group-view', actions: V, legacy: [[8, 2]] },
      { id: 564, title: 'Schemes (New Scheme Chit)', route: 'view-scheme', actions: ALL, legacy: [[8, 2]] },
      { id: 565, title: 'Due Slab', route: 'view-due-slab', actions: ALL, legacy: [[20, 2]] },
      { id: 566, title: 'Payment Slab', route: 'view-payment-slab', actions: ALL, legacy: [[24, 2]] },
      { id: 567, title: 'Due vs Payments', route: 'due-vs-payments', actions: V, legacy: [[20, 2], [24, 2]] },
      { id: 568, title: 'Auction On Holidays', route: 'view-auction-on-holidays', actions: ALL, legacy: [[16, 2]] },
    ],
  },
  {
    key: 'collection',
    title: 'SPN Collection',
    icon: 'fa fa-money',
    description: 'Receipts, collection lists and collection analytics.',
    pages: [
      { id: 571, title: 'Overview', route: 'hub/collection', actions: V, legacy: [], overview: true },
      { id: 572, title: 'Receipts', route: 'add-commitment-receipt', routes: ['view-commitment-receipt', 'update-commitment-receipt', 'delete-commitment-receipt'], actions: ALL, legacy: [[57, 37]] },
      { id: 573, title: 'Receipt Report (New / Old)', route: 'commitment-receipt-report', actions: V, legacy: [[82, 66]] },
      { id: 574, title: 'Scheme Wise Collection', route: 'scheme-wise-payable-pending-report', actions: V, legacy: [[164, 168]] },
      { id: 575, title: 'Coming Month Collection (Unpaid)', route: 'this-month-unpaid-report', actions: V, legacy: [[166, 168]] },
      { id: 576, title: 'Next Month Collection', route: 'next-month-collection-report', actions: V, legacy: [[165, 168]] },
      { id: 577, title: 'Due No Customer List', route: 'due-no-customer-report', actions: V, legacy: [[167, 168]] },
      { id: 578, title: 'Other Charges Collection', route: 'other-receipt-charges-report', routes: ['add-other-receipt-charges'], actions: VA, legacy: [[60, 37], [74, 66]] },
      { id: 579, title: 'RF / Before Enrolment Collection', route: 'before-enrol-receipt-report', actions: V, legacy: [[146, 66]] },
      { id: 580, title: 'UTR Receipt Report', route: 'utr-receipt-report', actions: V, legacy: [[82, 66]] },
      { id: 581, title: 'Branch / Employee Collection', route: 'employee-collection-report', actions: V, legacy: [[145, 66]] },
      { id: 582, title: 'Collection Prediction', route: 'collection-prediction-report', actions: V, legacy: [[51, 66]] },
      { id: 583, title: 'Historical Performance', route: 'historical-performance-report', actions: V, legacy: [[51, 66]] },
      { id: 584, title: 'Business Forecast', route: 'business-forecast-report', actions: V, legacy: [[51, 66]] },
      { id: 585, title: 'Outstanding Customers', route: 'commitmentcustomer-outstanding-report', actions: V, legacy: [[81, 43]] },
      { id: 586, title: 'Settlement Report / Approval', route: 'settlement-report', actions: VAE, legacy: [[147, 66]] },
      { id: 587, title: 'Collection Feedback', route: 'view-feed-back', actions: V, legacy: [[127, 124]] },
    ],
  },
  {
    key: 'chit-payment',
    title: 'SPN Chit Payment',
    icon: 'fa fa-inr',
    description: 'Prized / closed / refund payments, approvals and payment reports.',
    pages: [
      { id: 591, title: 'Overview', route: 'hub/payment', actions: V, legacy: [], overview: true },
      { id: 592, title: 'Payments Dashboard', route: 'payment-dashboard', actions: V, legacy: [[56, 28]] },
      { id: 593, title: 'Payment Entry', route: 'view-commitment-payment', routes: ['add-commitment-payment', 'update-commitment-payment', 'delete-commitment-payment'], actions: ALL, legacy: [[56, 28]] },
      { id: 594, title: 'Prized Payment', route: 'payment-priced', actions: VAE, legacy: [[56, 28]] },
      { id: 595, title: 'Final Closed Payment', route: 'payment-closed', actions: VAE, legacy: [[56, 28]] },
      { id: 596, title: 'Prized Payment Documents', route: 'document-preparation', qp: { kind: 'prized' }, actions: VAE, legacy: [[56, 28]] },
      { id: 597, title: 'Closed Payment Documents', route: 'document-preparation', qp: { kind: 'closed' }, actions: VAE, legacy: [[56, 28]] },
      { id: 598, title: 'Pending Approval', route: 'payment-pending-approvals', actions: VAE, legacy: [[56, 28]] },
      { id: 599, title: 'Refund Payment', route: 'payment-refund-desk', actions: VAE, legacy: [[56, 28]] },
      { id: 600, title: 'Refund Entry', route: 'add-commitment-enrol', routes: ['view-commitment-enrol', 'add-enrol-refund-amount'], actions: VAE, legacy: [[109, 37]] },
      { id: 601, title: 'Refund Approval', route: 'refund-approval', actions: VAE, legacy: [[109, 37]] },
      { id: 602, title: 'Prized Chit Payment Report', route: 'commitment-payment-report', actions: V, legacy: [[70, 65]] },
      { id: 603, title: 'Pending Payments Report', route: 'commitment-pending-report', actions: V, legacy: [[71, 65]] },
      { id: 604, title: 'Promised Report (Calendar)', route: 'commitment-promise-report', actions: V, legacy: [[71, 65]] },
      { id: 605, title: 'Final Closed Report', route: 'final-closed-report', actions: V, legacy: [[71, 65]] },
      { id: 606, title: 'Completed – Payment Not Given', route: 'completed-not-paid-report', actions: V, legacy: [[71, 65]] },
      { id: 607, title: 'Refund Report', route: 'payment-refund-report', actions: V, legacy: [[63, 65]] },
    ],
  },
  {
    key: 'auction',
    title: 'SPN Auction',
    icon: 'fa fa-trophy',
    description: 'Prized show group and auction reports.',
    pages: [
      { id: 611, title: 'Prized Show Group', route: 'prized-show-group', actions: VAE, legacy: [[56, 28]] },
      { id: 612, title: 'Prized Show Report', route: 'prized-show-report', actions: V, legacy: [[56, 28]] },
      { id: 613, title: 'Auction Report (Scheme-wise)', route: 'spn-auction-report', actions: V, legacy: [[56, 28]] },
    ],
  },
  {
    key: 'crm',
    title: 'SPN Customer CRM',
    icon: 'fa fa-heart',
    description: 'Wishes, SMS, leads and lead sources.',
    pages: [
      { id: 621, title: 'Overview', route: 'hub/crm', actions: V, legacy: [], overview: true },
      { id: 622, title: 'Birthday Wishes', route: 'customer-birthday-report', actions: V, legacy: [[161, 28], [161, 162]] },
      { id: 623, title: 'New Group Wishes', route: 'sales-promotion', actions: VA, legacy: [[160, 28]] },
      { id: 624, title: 'Send SMS / Wishes', route: 'send-message', actions: VA, legacy: [[163, 162]] },
      { id: 625, title: 'Wish Templates', route: 'sms-content', actions: ALL, legacy: [[162, 162]] },
      { id: 626, title: 'Leads', route: 'view-lead-management', actions: ALL, legacy: [[41, 28]] },
      { id: 627, title: 'Referral & App Leads', route: 'customer-app-leads-report', actions: V, legacy: [[41, 28]] },
      { id: 628, title: 'Lead Source', route: 'view-source-type', actions: ALL, legacy: [[90, 2]] },
    ],
  },
  {
    key: 'hr',
    title: 'SPN HR Department',
    icon: 'fa fa-users',
    description: 'Employees, salary, allowances, attendance and leave.',
    pages: [
      { id: 631, title: 'Overview', route: 'hub/hr', actions: V, legacy: [], overview: true },
      { id: 632, title: 'Employee Details', route: 'view-employee', actions: ALL, legacy: [[3, 34]] },
      { id: 633, title: 'Designation', route: 'view-designation', actions: ALL, legacy: [[12, 2]] },
      { id: 634, title: 'Salary', route: 'employee-salary', actions: VAE, legacy: [[101, 34]] },
      { id: 635, title: 'Employee Advance / Loan', route: 'employee-advance', actions: VAE, legacy: [[101, 34]] },
      { id: 636, title: 'Day Allowance', route: 'day-allowance-report', actions: V, legacy: [[101, 34]] },
      { id: 637, title: 'Petrol Allowance', route: 'petrol-allowance', actions: VAE, legacy: [[101, 34]] },
      { id: 638, title: 'Attendance', route: 'view-employee-attendance', actions: VAE, legacy: [[35, 34]] },
      { id: 639, title: 'Leave / Permission / OD', route: 'view-leave-requests', actions: VAE, legacy: [[35, 34]] },
      { id: 640, title: 'Live Tracking & Punch Map', route: 'view-punch-attendance', actions: V, legacy: [[35, 34]] },
      { id: 641, title: 'Collection Area Mapping', route: 'view-collection-area-mapping', actions: ALL, legacy: [[55, 34]] },
      { id: 642, title: 'Employees Care (Skills & Achievements)', route: 'view-employee-mobile-modules', actions: VAE, legacy: [[118, 34]] },
    ],
  },
  {
    key: 'target-task',
    title: 'SPN Target / Task',
    icon: 'fa fa-bullseye',
    description: 'Task assignment, targets and performance.',
    pages: [
      { id: 651, title: 'Overview', route: 'hub/action', actions: V, legacy: [], overview: true },
      { id: 652, title: 'New / Assign Task', route: 'add-task', actions: VAE, legacy: [[118, 34]] },
      { id: 653, title: 'Daily Tasks & Targets', route: 'view-daily-tasks-targets', actions: VAE, legacy: [[118, 34]] },
      { id: 654, title: 'Target Set', route: 'set-employee-target', actions: VAE, legacy: [[118, 34]] },
      { id: 655, title: 'Task Report', route: 'task-report', actions: V, legacy: [[118, 34]] },
      { id: 656, title: 'Performance AI', route: 'employee-performance-ai', actions: V, legacy: [[145, 66], [51, 66]] },
    ],
  },
  {
    key: 'ticket',
    title: 'SPN Ticket / Request',
    icon: 'fa fa-ticket',
    description: 'Office wishes, tickets and requests.',
    pages: [
      { id: 661, title: 'Overview', route: 'hub/ticket', actions: V, legacy: [], overview: true },
      { id: 662, title: 'Office Wish & Tickets', route: 'office-tickets', actions: VAE, legacy: [[35, 34]] },
    ],
  },
  {
    key: 'documents',
    title: 'SPN Document Management',
    icon: 'fa fa-folder-open',
    description: 'Customer, employee and loan documents.',
    pages: [
      { id: 671, title: 'Overview', route: 'hub/documents', actions: V, legacy: [], overview: true },
      { id: 672, title: 'Customer Documents', route: 'view-documents', actions: ALL, legacy: [[38, 28]] },
      { id: 673, title: 'Employee Documents', route: 'view-employee-document', actions: ALL, legacy: [[118, 34]] },
      { id: 674, title: 'Loan Documents', route: 'view-loan-customer-document', actions: ALL, legacy: [[100, 85]] },
    ],
  },
  {
    key: 'security',
    title: 'SPN Security Power',
    icon: 'fa fa-shield',
    description: 'User logins, roles, permissions and audit logs.',
    pages: [
      { id: 681, title: 'Overview', route: 'hub/security', actions: V, legacy: [], overview: true },
      { id: 682, title: 'User Management (Logins)', route: 'view-employee-login', actions: ALL, legacy: [[36, 34]] },
      { id: 683, title: 'Roles', route: 'view-role', actions: ALL, legacy: [[11, 2]] },
      { id: 684, title: 'Role & Permission', route: 'add-permission', routes: ['update-permission'], actions: VAE, legacy: [[53, 53]] },
      { id: 685, title: 'Login Activity / Audit Logs', route: 'log-monitoring-report', actions: V, legacy: [[89, 88]] },
    ],
  },
  {
    key: 'reports',
    title: 'SPN Reports Section',
    icon: 'fa fa-bar-chart',
    description: 'Customer, sales and loan reports.',
    pages: [
      { id: 691, title: 'Overview', route: 'hub/reports', actions: V, legacy: [], overview: true },
      { id: 704, title: 'Chit Enrolment Report', route: 'commitment-report', actions: V, legacy: [[69, 48]], group: 'Customer Reports' },
      { id: 692, title: 'Pledge', route: 'pledge-customer-report', actions: V, legacy: [[132, 48]], group: 'Customer Reports' },
      { id: 693, title: 'Suit Filed', route: 'suit-file-report', actions: V, legacy: [[133, 48]], group: 'Customer Reports' },
      { id: 694, title: 'Business Agent Report', route: 'business-agent-report', actions: V, legacy: [[77, 68]], group: 'Sales Reports' },
      { id: 695, title: 'Commission Pending', route: 'commission-pending-report', actions: V, legacy: [[78, 68]], group: 'Sales Reports' },
      { id: 696, title: 'Commission Payment', route: 'commission-payment-report', actions: V, legacy: [[150, 68]], group: 'Sales Reports' },
      { id: 697, title: 'Incentive Payment', route: 'incentive-payment-report', actions: V, legacy: [[98, 68]], group: 'Sales Reports' },
      { id: 698, title: 'Incentive Pending', route: 'incentive-pending-report', actions: V, legacy: [[97, 68]], group: 'Sales Reports' },
      { id: 699, title: 'Lead Report', route: 'lead-management-report', actions: V, legacy: [[95, 68]], group: 'Sales Reports' },
      { id: 700, title: 'Loan Receipt', route: 'loan-receipt-report', actions: V, legacy: [[84, 83]], group: 'Loan Reports' },
      { id: 701, title: 'Loan Customer Report', route: 'loan-customer-report', actions: V, legacy: [[91, 83]], group: 'Loan Reports' },
      { id: 702, title: 'Loan Outstanding', route: 'loan-customerwise-outstanding-report', actions: V, legacy: [[93, 43]], group: 'Loan Reports' },
      { id: 703, title: 'Loan Day Closing', route: 'loan-day-closing-report', actions: V, legacy: [[94, 67]], group: 'Loan Reports' },
    ],
  },
  {
    key: 'accounts',
    title: 'SPN Account Management',
    icon: 'fa fa-calculator',
    description: 'Payouts, cash box, entries, books and account masters.',
    pages: [
      { id: 711, title: 'Accounts Payout Approval', route: 'payment-accounts-payout', actions: VAE, legacy: [[56, 28]], group: 'Payments Done', hint: 'Prized / Final / Refund payment links here use the Chit Payment selections.' },
      { id: 712, title: 'My Cash Box', route: 'my-cash-box', actions: VA, legacy: [[148, 67]], group: 'My Cash Box' },
      { id: 713, title: 'Cash Box Approvals', route: 'cash-box-approvals', actions: VAE, legacy: [[148, 67]], group: 'My Cash Box' },
      { id: 714, title: 'Income Cash Box Report', route: 'income-cash-box-report', actions: V, legacy: [[148, 67]], group: 'My Cash Box' },
      { id: 715, title: 'My Day Report', route: 'my-day-report', actions: V, legacy: [[148, 67]], group: 'My Cash Box' },
      { id: 716, title: 'Branch Income & Expense (AI)', route: 'branch-income-expense-report', actions: V, legacy: [[105, 67]], group: 'Branch Reports' },
      { id: 717, title: 'Petty Cash Branch Report', route: 'petty-cash-branch-report', actions: V, legacy: [[26, 62], [27, 62]], group: 'Branch Reports' },
      { id: 718, title: 'Income / Expenses Entry', route: 'view-expenses-income', actions: ALL, legacy: [[104, 37]], group: 'Entries' },
      { id: 719, title: 'Petty Cash Income', route: 'view-petty-cash-income', actions: ALL, legacy: [[27, 62]], group: 'Entries' },
      { id: 720, title: 'Petty Cash Expense', route: 'view-petty-cash-expense', actions: ALL, legacy: [[26, 62]], group: 'Entries' },
      { id: 721, title: 'Cheque In Bank', route: 'cheque-in-bank', actions: VAE, legacy: [[104, 37]], group: 'Entries' },
      { id: 722, title: 'Day Book', route: 'day-book-report', actions: V, legacy: [[148, 67]], group: 'Books & Statements' },
      { id: 723, title: 'Cash Box Book', route: 'cashbook', actions: V, legacy: [[148, 67]], group: 'Books & Statements' },
      { id: 724, title: 'Bank Book', route: 'bank-book', actions: V, legacy: [[148, 67]], group: 'Books & Statements' },
      { id: 725, title: 'Day Closing Report', route: 'day-closing-report', actions: V, legacy: [[75, 67]], group: 'Books & Statements' },
      { id: 726, title: 'Accounts Report', route: 'accounts-report', actions: V, legacy: [[76, 67]], group: 'Books & Statements' },
      { id: 727, title: 'Income / Expenses Report', route: 'add-expenses-income-report', actions: V, legacy: [[105, 67]], group: 'Books & Statements' },
      { id: 728, title: 'Trial Balance', route: 'trial-balance', actions: V, legacy: [[106, 67]], group: 'Books & Statements' },
      { id: 729, title: 'Profit & Loss', route: 'profit-loss-report', actions: V, legacy: [[108, 67]], group: 'Books & Statements' },
      { id: 730, title: 'Balance Sheet', route: 'balance-sheet-report', actions: V, legacy: [[107, 67]], group: 'Books & Statements' },
      { id: 731, title: 'Account Groups', route: 'view-accounting-group', actions: ALL, legacy: [[102, 2]], group: 'Account Masters' },
      { id: 732, title: 'Account Head', route: 'view-accounting-head', actions: ALL, legacy: [[103, 2]], group: 'Account Masters' },
      { id: 733, title: 'Petty Cash Head', route: 'view-petty-cash-head', actions: ALL, legacy: [[18, 2]], group: 'Account Masters' },
      { id: 734, title: 'Bank Details', route: 'view-bank-details', actions: ALL, legacy: [[17, 2]], group: 'Account Masters' },
    ],
  },
  {
    key: 'loan-legal',
    title: 'Loan & Legal',
    icon: 'fa fa-university',
    description: 'Loan disbursement / receipts and legal masters.',
    pages: [
      { id: 741, title: 'Loan Disbursement', route: 'view-loan', actions: ALL, legacy: [[86, 85]] },
      { id: 742, title: 'Loan Receipt', route: 'add-loan-receipt', routes: ['view-loan-receipt'], actions: VAE, legacy: [[87, 85]] },
      { id: 743, title: 'Case Stages', route: 'view-case-stage', actions: ALL, legacy: [[116, 2]] },
      { id: 744, title: 'Lawyer', route: 'view-lawyer', actions: ALL, legacy: [[117, 2]] },
    ],
  },
  {
    key: 'masters',
    title: 'Masters & Settings',
    icon: 'fa fa-cogs',
    description: 'Offices, locations, collection areas and charge types.',
    pages: [
      { id: 751, title: 'Regional Office', route: 'view-head-office', actions: ALL, legacy: [[6, 2]] },
      { id: 752, title: 'Branch Office', route: 'view-branch', actions: ALL, legacy: [[7, 2]] },
      { id: 753, title: 'Country', route: 'view-country', actions: ALL, legacy: [[113, 2]] },
      { id: 754, title: 'State', route: 'view-state', actions: ALL, legacy: [[114, 2]] },
      { id: 755, title: 'District', route: 'view-district', actions: ALL, legacy: [[115, 2]] },
      { id: 756, title: 'City', route: 'view-city', actions: ALL, legacy: [[15, 2]] },
      { id: 757, title: 'Collection Area', route: 'view-collection-area', actions: ALL, legacy: [[10, 2]] },
      { id: 758, title: 'Charges Types', route: 'view-charges-type', actions: ALL, legacy: [[13, 2]] },
      { id: 759, title: 'Family Code Master', route: 'view-family-code', actions: ALL, legacy: [[5, 28]] },
    ],
  },
];

export const LEGACY_MODULE_TITLES: { [id: number]: string } = {
  1: 'Dashboard', 2: 'Masters', 19: 'Slabs', 28: 'Subscriber Management', 34: 'Employee Management',
  37: 'Transaction Management', 42: 'Reports', 43: 'Outstanding Report', 48: 'Enrollment Report',
  53: 'Security Permissions', 62: 'Petty Cash', 65: 'Payment Report', 66: 'Collection Report',
  67: 'Accounts Report', 68: 'Business Development', 83: 'Loan Report', 85: 'Loan Management',
  88: 'Log Monitoring', 124: 'Customer Report', 162: 'SMS', 168: 'SPN Collection Reports',
};

export const ALL_ACCESS_PAGES: AccessPage[] = SPN_ACCESS_SECTIONS.reduce(
  (acc, s) => acc.concat(s.pages), [] as AccessPage[]
);

export const ACCESS_PAGE_BY_ID: { [id: number]: AccessPage } = ALL_ACCESS_PAGES.reduce(
  (acc, p) => { acc[p.id] = p; return acc; }, {} as { [id: number]: AccessPage }
);

export const ACCESS_SECTION_BY_KEY: { [key: string]: AccessSection } = SPN_ACCESS_SECTIONS.reduce(
  (acc, s) => { acc[s.key] = s; return acc; }, {} as { [key: string]: AccessSection }
);

/** Route paths (without ':id' segments) → page ids that grant them. */
export const ACCESS_PAGES_BY_ROUTE: { [route: string]: number[] } = (() => {
  const map: { [route: string]: number[] } = {};
  const put = (r: string, id: number) => {
    if (!r) { return; }
    (map[r] = map[r] || []).indexOf(id) < 0 && map[r].push(id);
  };
  ALL_ACCESS_PAGES.forEach((p) => {
    if (p.route && p.route !== 'dashboard') {
      put(p.route, p.id);
      if (p.route.indexOf('view-') === 0) {
        const base = p.route.substring(5);
        put('add-' + base, p.id);
        put('update-' + base, p.id);
        put('delete-' + base, p.id);
      }
    }
    (p.routes || []).forEach((r) => put(r, p.id));
  });
  return map;
})();

export interface AccessFlags { view: number; add: number; edit: number; delete: number; }
export type AccessState = { [pageId: number]: AccessFlags };

export function emptyFlags(): AccessFlags {
  return { view: 0, add: 0, edit: 0, delete: 0 };
}

const FLAG_KEY: { [a: string]: string } = { view: 'view_perm', add: 'add_perm', edit: 'edit_perm', delete: 'delete_perm' };

/** Build role_access payload: SPN page module + derived legacy modules. */
export function buildRoleAccess(state: AccessState): any[] {
  const legacy = new Map<number, any>();
  const ensureModule = (moduleId: number) => {
    if (!legacy.has(moduleId)) {
      legacy.set(moduleId, {
        menu_id: moduleId,
        menu_title: LEGACY_MODULE_TITLES[moduleId] || 'Module',
        page_type: moduleId === 1 ? 3 : 4,
        has_sub_menu: 1,
        add_perm: 0, edit_perm: 0, view_perm: 0, delete_perm: 0,
        Submenus: [],
      });
    }
    return legacy.get(moduleId);
  };

  const spnSubs: any[] = [];
  ALL_ACCESS_PAGES.forEach((page) => {
    const f = state[page.id] || emptyFlags();
    const any = f.view || f.add || f.edit || f.delete;
    spnSubs.push({
      menu_id: page.id,
      menu_title: page.title,
      page_type: 1,
      has_sub_menu: 0,
      add_perm: f.add ? 1 : 0,
      edit_perm: f.edit ? 1 : 0,
      view_perm: any ? 1 : 0,
      delete_perm: f.delete ? 1 : 0,
    });
    if (!any) {
      return;
    }
    page.legacy.forEach(([menuId, parentId]) => {
      const moduleId = parentId || menuId;
      const mod = ensureModule(moduleId);
      let sub = mod.Submenus.find((s: any) => Number(s.menu_id) === Number(menuId));
      if (!sub) {
        sub = { menu_id: menuId, menu_title: page.title, page_type: page.actions.length > 1 ? 1 : 2, has_sub_menu: 0, add_perm: 0, edit_perm: 0, view_perm: 0, delete_perm: 0, top_perm: 0 };
        mod.Submenus.push(sub);
      }
      sub.view_perm = 1;
      sub.top_perm = 1;
      ['add', 'edit', 'delete'].forEach((a) => {
        if ((f as any)[a]) { sub[FLAG_KEY[a]] = 1; }
      });
      mod.view_perm = 1;
    });
  });

  const out: any[] = [];
  Array.from(legacy.keys()).sort((a, b) => a - b).forEach((k) => out.push(legacy.get(k)));
  out.push({
    menu_id: SPN_ACCESS_MODULE_ID,
    menu_title: SPN_ACCESS_MODULE_TITLE,
    page_type: 4,
    has_sub_menu: 1,
    add_perm: 0, edit_perm: 0,
    view_perm: spnSubs.some((s) => s.view_perm) ? 1 : 0,
    delete_perm: 0,
    Submenus: spnSubs,
  });
  return out;
}

function flagOn(v: any): boolean {
  return v === 1 || v === '1' || v === true;
}

function findLegacy(saved: any[], menuId: number, parentId: number): any {
  if (parentId === 0) {
    return saved.find((m: any) => Number(m.menu_id) === Number(menuId)) || null;
  }
  const parent = saved.find((m: any) => Number(m.menu_id) === Number(parentId));
  const direct = parent && Array.isArray(parent.Submenus)
    ? parent.Submenus.find((s: any) => Number(s.menu_id) === Number(menuId)) : null;
  if (direct) { return direct; }
  for (const m of saved) {
    if (Number(m.menu_id) === SPN_ACCESS_MODULE_ID || !Array.isArray(m.Submenus)) { continue; }
    const hit = m.Submenus.find((s: any) => Number(s.menu_id) === Number(menuId));
    if (hit) { return hit; }
  }
  return null;
}

/** Read saved role_access into page state (new format directly; older roles derived from legacy ids). */
export function readRoleAccess(saved: any[]): AccessState {
  const state: AccessState = {};
  ALL_ACCESS_PAGES.forEach((p) => { state[p.id] = emptyFlags(); });
  if (!Array.isArray(saved)) {
    return state;
  }
  const spn = saved.find((m: any) => Number(m.menu_id) === SPN_ACCESS_MODULE_ID);
  if (spn && Array.isArray(spn.Submenus)) {
    spn.Submenus.forEach((s: any) => {
      const page = ACCESS_PAGE_BY_ID[Number(s.menu_id)];
      if (!page) { return; }
      state[page.id] = {
        view: flagOn(s.view_perm) ? 1 : 0,
        add: flagOn(s.add_perm) && page.actions.indexOf('add') >= 0 ? 1 : 0,
        edit: flagOn(s.edit_perm) && page.actions.indexOf('edit') >= 0 ? 1 : 0,
        delete: flagOn(s.delete_perm) && page.actions.indexOf('delete') >= 0 ? 1 : 0,
      };
    });
    return state;
  }

  ALL_ACCESS_PAGES.forEach((page) => {
    const f = emptyFlags();
    page.legacy.forEach(([menuId, parentId]) => {
      const rec = findLegacy(saved, menuId, parentId);
      if (!rec) { return; }
      if (flagOn(rec.view_perm) || flagOn(rec.top_perm)) { f.view = 1; }
      page.actions.forEach((a) => {
        if (a !== 'view' && flagOn(rec[FLAG_KEY[a]])) { (f as any)[a] = 1; f.view = 1; }
      });
    });
    state[page.id] = f;
  });
  // Overviews had no permission before: grant them where the section already had a page.
  SPN_ACCESS_SECTIONS.forEach((section) => {
    const anyPage = section.pages.some((p) => !p.overview && !!state[p.id].view);
    section.pages.filter((p) => p.overview && !p.legacy.length).forEach((p) => {
      if (anyPage) { state[p.id].view = 1; }
    });
  });
  const anyDashboard = ACCESS_SECTION_BY_KEY['dashboards'].pages.some((p) => !!state[p.id].view);
  ['dashboard-overviews', 'corporate'].forEach((key) => {
    ACCESS_SECTION_BY_KEY[key].pages.filter((p) => !p.legacy.length).forEach((p) => {
      if (anyDashboard) { state[p.id].view = 1; }
    });
  });
  return state;
}
