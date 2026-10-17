export interface HubAction {
  label: string;
  icon: string;
  route: string;
  qp?: any;
}

export interface HubReportGroup {
  title: string;
  icon: string;
  links: HubAction[];
}

export interface HubSection {
  word: string;
  tagline: string;
  icon: string;
  chips: string[];
  slogan: string[];
  actions: HubAction[];
  reportGroups?: HubReportGroup[];
}

export const HUB_SECTIONS: { [key: string]: HubSection } = {
  'today-collection': {
    word: 'TODAY COLLECTION',
    tagline: 'Every receipt collected today • Branch wise • Employee wise • Payment mode',
    icon: 'fa fa-money',
    chips: ['Receipts', 'RF', 'Advance', 'Branch Wise', 'Employee Wise'],
    slogan: ['Every Rupee', 'Counted', 'On Time'],
    actions: [
      { label: 'Receipt Entry', icon: 'fa fa-plus-circle', route: 'add-commitment-receipt' },
      { label: 'Receipt Report', icon: 'fa fa-file-text-o', route: 'commitment-receipt-report' },
      { label: 'Employee Collection', icon: 'fa fa-id-badge', route: 'employee-collection-report' },
      { label: 'Day Book', icon: 'fa fa-book', route: 'day-book-report' },
    ]
  },
  'sales': {
    word: 'SALES TARGET',
    tagline: 'New business this month • Target vs Achieved • Top 3 Branches & Employees',
    icon: 'fa fa-line-chart',
    chips: ['Target', 'Achieved', 'Branch Wise', 'Employee Wise', 'Top 3'],
    slogan: ['More Groups', 'More Members', 'More Growth'],
    actions: [
      { label: 'Set Target', icon: 'fa fa-crosshairs', route: 'set-employee-target' },
      { label: 'New Enrolment', icon: 'fa fa-user-plus', route: 'add-customer' },
      { label: 'Business Agent Report', icon: 'fa fa-file-text-o', route: 'business-agent-report' },
      { label: 'Business Forecast', icon: 'fa fa-line-chart', route: 'business-forecast-report' },
    ]
  },
  'today-enrollment': {
    word: 'TODAY ENROLMENT',
    tagline: 'Who joined today • Which scheme • Which branch • Enrolled by whom',
    icon: 'fa fa-user-plus',
    chips: ['Customers', 'Schemes', 'Branch Wise', 'Employee Wise'],
    slogan: ['New Customer', 'New Relationship'],
    actions: [
      { label: 'New Profile', icon: 'fa fa-user-plus', route: 'add-customer' },
      { label: 'Chit Customers', icon: 'fa fa-users', route: 'view-commitment-chit' },
      { label: 'Enrolment Approval', icon: 'fa fa-check-square-o', route: 'commitment-eligibility-approval' },
    ]
  },
  'pending-collection': {
    word: 'PENDING COLLECTION',
    tagline: 'This month due not yet collected • Branch wise • Customer list',
    icon: 'fa fa-hourglass-half',
    chips: ['This Month Due', 'Partially Paid', 'Not Paid', 'Branch Wise'],
    slogan: ['Follow Up', 'Collect', 'On Time'],
    actions: [
      { label: 'This Month Unpaid', icon: 'fa fa-calendar', route: 'this-month-unpaid-report' },
      { label: 'Receipt Entry', icon: 'fa fa-plus-circle', route: 'add-commitment-receipt' },
      { label: 'Scheme Wise Pending', icon: 'fa fa-pie-chart', route: 'scheme-wise-payable-pending-report' },
    ]
  },
  'overdue-collection': {
    word: 'OVERDUE COLLECTION',
    tagline: 'Old dues pending • Ageing • High risk customers',
    icon: 'fa fa-exclamation-triangle',
    chips: ['1 Due', '2-3 Dues', '3+ Dues', 'Branch Wise'],
    slogan: ['Recover', 'Old Dues', 'Fast'],
    actions: [
      { label: 'Outstanding Report', icon: 'fa fa-clock-o', route: 'commitmentcustomer-outstanding-report' },
      { label: 'Suit File', icon: 'fa fa-gavel', route: 'suit-file-report' },
      { label: 'Receipt Entry', icon: 'fa fa-plus-circle', route: 'add-commitment-receipt' },
    ]
  },
  'top-performers': {
    word: 'TOP PERFORMERS',
    tagline: 'Top 3 Employees & Branches • How they are best • Business & Targets',
    icon: 'fa fa-trophy',
    chips: ['Collection', 'Target %', 'Receipts', 'New Chits', 'New Business'],
    slogan: ['Best People', 'Best Branches', 'Greater SPN'],
    actions: [
      { label: 'Employee Collection', icon: 'fa fa-id-badge', route: 'employee-collection-report' },
      { label: 'Performance AI', icon: 'fa fa-magic', route: 'employee-performance-ai' },
      { label: 'Set Target', icon: 'fa fa-crosshairs', route: 'set-employee-target' },
    ]
  },
  'attendance': {
    word: 'TODAY ATTENDANCE',
    tagline: 'Present • Absent • On Leave • Live GPS tracking',
    icon: 'fa fa-calendar-check-o',
    chips: ['Present', 'Absent', 'On Leave', 'Live Tracking'],
    slogan: ['Our People', 'Our Strength'],
    actions: [
      { label: 'Attendance Report', icon: 'fa fa-calendar-check-o', route: 'view-employee-attendance' },
      { label: 'Live Tracking Map', icon: 'fa fa-map-marker', route: 'view-punch-attendance' },
      { label: 'Leave Requests', icon: 'fa fa-plane', route: 'view-leave-requests' },
    ]
  },
  'birthdays': {
    word: 'BIRTHDAYS',
    tagline: 'Employee birthdays today & this month • Customer birthdays today',
    icon: 'fa fa-birthday-cake',
    chips: ['Today', 'This Month', 'Employees', 'Customers'],
    slogan: ['Wish', 'Celebrate', 'Together'],
    actions: [
      { label: 'Employees', icon: 'fa fa-id-card-o', route: 'view-employee' },
      { label: 'Customer Birthdays', icon: 'fa fa-birthday-cake', route: 'customer-birthday-report' },
      { label: 'Send SMS', icon: 'fa fa-envelope', route: 'send-message' },
    ]
  },
  'tasks-targets': {
    word: 'TODAY TASK & TARGET',
    tagline: 'Tasks due today • Running targets • Achieved vs pending',
    icon: 'fa fa-bullseye',
    chips: ['Tasks', 'Targets', 'Branch Wise', 'Employee Wise'],
    slogan: ['Small Actions', 'Big Results'],
    actions: [
      { label: 'New Task', icon: 'fa fa-plus-circle', route: 'add-task' },
      { label: 'Set Target', icon: 'fa fa-crosshairs', route: 'set-employee-target' },
      { label: 'Task Report', icon: 'fa fa-file-text-o', route: 'task-report' },
    ]
  },
  'expenses': {
    word: 'EXPENSES',
    tagline: 'Today & this month petty cash expenses • Type wise • Branch wise',
    icon: 'fa fa-credit-card',
    chips: ['Today', 'This Month', 'Expense Type', 'Branch Wise'],
    slogan: ['Spend Wise', 'Save More'],
    actions: [
      { label: 'Expense List', icon: 'fa fa-list', route: 'view-petty-cash-expense' },
      { label: 'Income / Expenses', icon: 'fa fa-exchange', route: 'add-expenses-income-report' },
      { label: 'Day Book', icon: 'fa fa-book', route: 'day-book-report' },
    ]
  },
  'settlement-approvals': {
    word: 'SETTLEMENT APPROVALS',
    tagline: 'Cash settlements from field staff waiting for approval',
    icon: 'fa fa-check-square-o',
    chips: ['Pending', 'Approved', 'Branch Wise'],
    slogan: ['Verify', 'Approve', 'Secure'],
    actions: [
      { label: 'Approve Settlements', icon: 'fa fa-check', route: 'settlement-report' },
      { label: 'Cash Box Book', icon: 'fa fa-book', route: 'cashbook' },
    ]
  },
  'corporate': {
    word: 'CORPORATE ADMIN PANEL',
    tagline: 'Company-wide business • Collection • Payments • Expenses • Branch performance',
    icon: 'fa fa-building',
    chips: ['All Branches', 'Running Business', 'Collection', 'Payments', 'Expenses'],
    slogan: ['One Company', 'All Branches', 'Greater SPN'],
    actions: [
      { label: 'Branch Overview', icon: 'fa fa-university', route: 'branch-overview' },
      { label: 'Top Performers', icon: 'fa fa-trophy', route: 'hub/top-performers' },
      { label: 'Profit & Loss', icon: 'fa fa-line-chart', route: 'profit-loss-report' },
      { label: 'Balance Sheet', icon: 'fa fa-balance-scale', route: 'balance-sheet-report' },
      { label: 'Trial Balance', icon: 'fa fa-list-alt', route: 'trial-balance' },
    ]
  },
  'chit': {
    word: 'MY CUSTOMERS',
    tagline: 'All approved chit customers • Ledger • Category • Cancelled',
    icon: 'fa fa-link',
    chips: ['Customers', 'Ledger', 'Category', 'Collection', 'Reports', 'Profit & Trust'],
    slogan: ["People's Trust", 'Our Strength'],
    actions: [
      { label: 'Active Customers', icon: 'fa fa-users', route: 'view-commitment-chit' },
      { label: 'Old Pending', icon: 'fa fa-clock-o', route: 'commitmentcustomer-outstanding-report' },
      { label: 'Cancelled', icon: 'fa fa-times-circle', route: 'cancel-customer-report' },
      { label: 'Customer Category', icon: 'fa fa-trophy', route: 'customer-category-report' },
      { label: 'Chit Customer Report', icon: 'fa fa-file-text-o', route: 'commitment-report' },
      { label: 'Customer Ledger', icon: 'fa fa-book', route: 'customer-ledger' },
      { label: 'Due No List', icon: 'fa fa-list-ol', route: 'due-no-customer-report' },
    ]
  },
  'new-customers': {
    word: 'NEW CUSTOMERS',
    tagline: 'Complete Customer Onboarding • Verification • Approval • Tracking',
    icon: 'fa fa-user-plus',
    chips: ['Profile', 'KYC', 'Approval', 'Branch Transfer'],
    slogan: ['New Customer', 'New Relationship', 'Stronger Tomorrow'],
    actions: [
      { label: 'New Profile', icon: 'fa fa-user-plus', route: 'add-customer' },
      { label: 'Profile List', icon: 'fa fa-address-card-o', route: 'view-customer' },
      { label: 'Enrolment Approval', icon: 'fa fa-check-square-o', route: 'commitment-eligibility-approval' },
      { label: 'Verify KYC', icon: 'fa fa-shield', route: 'view-documents' },
      { label: 'Upload Documents', icon: 'fa fa-upload', route: 'add-document' },
      { label: 'Branch Transfer', icon: 'fa fa-exchange', route: 'branch-transfer-customer' },
      { label: 'Transfer Report', icon: 'fa fa-file-text-o', route: 'branch-transfer-customer-report' },
    ]
  },
  'groups': {
    word: 'CHIT MANAGEMENT',
    tagline: 'Create • Manage • Monitor • Grow',
    icon: 'fa fa-th-large',
    chips: ['New Scheme', 'Slabs', 'Group Formation', 'Prized Groups', 'Reports & Analysis'],
    slogan: ['More Groups', 'More Members', 'More Growth'],
    actions: [
      { label: 'SPN New Group', icon: 'fa fa-object-group', route: 'spn-new-group' },
      { label: 'Group View', icon: 'fa fa-th', route: 'spn-group-view' },
      { label: 'Create New Scheme', icon: 'fa fa-plus-circle', route: 'add-scheme' },
      { label: 'Scheme List', icon: 'fa fa-list', route: 'view-scheme' },
      { label: 'Due Slab', icon: 'fa fa-th-list', route: 'view-due-slab' },
      { label: 'Payment Slab', icon: 'fa fa-table', route: 'view-payment-slab' },
      { label: 'Due vs Payments', icon: 'fa fa-balance-scale', route: 'due-vs-payments' },
      { label: 'Prized Show Group', icon: 'fa fa-star', route: 'prized-show-group' },
      { label: 'Auction Holidays', icon: 'fa fa-calendar', route: 'view-auction-on-holidays' },
    ]
  },
  'collection': {
    word: 'COLLECTION',
    tagline: 'Complete Collection Management System',
    icon: 'fa fa-money',
    chips: ['New Collection', 'Old Collection', 'Scheme Wise', 'Branch Wise', 'Reports'],
    slogan: ['Every Rupee', 'Counted', 'On Time'],
    actions: [
      { label: 'Receipts', icon: 'fa fa-plus-circle', route: 'add-commitment-receipt' },
      { label: 'Receipt Report (New / Old)', icon: 'fa fa-users', route: 'commitment-receipt-report' },
      { label: 'Scheme Wise', icon: 'fa fa-pie-chart', route: 'scheme-wise-payable-pending-report' },
      { label: 'Coming Month', icon: 'fa fa-calendar', route: 'this-month-unpaid-report' },
      { label: 'Next Month', icon: 'fa fa-calendar-plus-o', route: 'next-month-collection-report' },
      { label: 'RF Collection', icon: 'fa fa-registered', route: 'before-enrol-receipt-report' },
      { label: 'Other Charges', icon: 'fa fa-database', route: 'other-receipt-charges-report' },
      { label: 'UTR Receipts', icon: 'fa fa-exchange', route: 'utr-receipt-report' },
      { label: 'Branch / Employee', icon: 'fa fa-university', route: 'employee-collection-report' },
      { label: 'Prediction', icon: 'fa fa-line-chart', route: 'collection-prediction-report' },
    ]
  },
  'payment': {
    word: 'CHIT PAYMENT',
    tagline: 'Safe Payment • Instant Receipt • Accurate Update • Complete Tracking',
    icon: 'fa fa-inr',
    chips: ['Cash', 'UPI', 'NEFT', 'Others', 'All Schemes', 'All Branches'],
    slogan: ['Instant Receipt', 'Auto Update', '100% Accurate'],
    actions: [
      { label: 'Payments Desk', icon: 'fa fa-desktop', route: 'payment-dashboard' },
      { label: 'Payment Entry', icon: 'fa fa-plus-circle', route: 'view-commitment-payment' },
      { label: 'Priced Payment', icon: 'fa fa-trophy', route: 'payment-priced' },
      { label: 'Final Closed', icon: 'fa fa-lock', route: 'payment-closed' },
      { label: 'Pending Approval', icon: 'fa fa-hourglass-half', route: 'payment-pending-approvals' },
      { label: 'Refund Payment', icon: 'fa fa-undo', route: 'payment-refund-desk' },
      { label: 'Refund Approval', icon: 'fa fa-check', route: 'refund-approval' },
      { label: 'Prized Show', icon: 'fa fa-star', route: 'prized-show-report' },
      { label: 'Payment Report', icon: 'fa fa-file-text-o', route: 'commitment-payment-report' },
      { label: 'Refund Report', icon: 'fa fa-file-o', route: 'payment-refund-report' },
    ]
  },
  'crm': {
    word: 'CUSTOMER CRM',
    tagline: 'Wish • Engage • Retain • Grow Together',
    icon: 'fa fa-heart',
    chips: ['Birthday Wishes', 'Festival Wishes', 'Personal Wishes', 'New Customer Wishes', 'Action Group Wishes'],
    slogan: ['Happy Customers', 'Happy Relationship', 'Greater SPN'],
    actions: [
      { label: 'Birthday Wish', icon: 'fa fa-birthday-cake', route: 'customer-birthday-report' },
      { label: 'New Group Wish', icon: 'fa fa-users', route: 'sales-promotion' },
      { label: 'Send SMS', icon: 'fa fa-envelope', route: 'send-message' },
      { label: 'Wish Templates', icon: 'fa fa-file-text-o', route: 'sms-content' },
      { label: 'Leads', icon: 'fa fa-bullhorn', route: 'view-lead-management' },
      { label: 'New Lead', icon: 'fa fa-user-plus', route: 'add-lead-management' },
      { label: 'App & Referral Leads', icon: 'fa fa-mobile', route: 'customer-app-leads-report' },
      { label: 'Lead Report', icon: 'fa fa-bar-chart', route: 'lead-management-report' },
    ]
  },
  'hr': {
    word: 'HR DEPARTMENT',
    tagline: 'Employee Details & Administration',
    icon: 'fa fa-users',
    chips: ['People', 'Process', 'Support', 'Growth', 'Success'],
    slogan: ['Our People', 'Our Strength', 'Greater SPN'],
    actions: [
      { label: 'Employee Details', icon: 'fa fa-id-card-o', route: 'view-employee' },
      { label: 'Add Employee', icon: 'fa fa-user-plus', route: 'add-employee' },
      { label: 'Documents', icon: 'fa fa-folder-open', route: 'view-employee-document' },
      { label: 'Salary & Advance', icon: 'fa fa-inr', route: 'employee-salary' },
      { label: 'Attendance', icon: 'fa fa-calendar-check-o', route: 'view-employee-attendance' },
      { label: 'Leave / OD', icon: 'fa fa-plane', route: 'view-leave-requests' },
      { label: 'Live Tracking', icon: 'fa fa-map-marker', route: 'view-punch-attendance' },
      { label: 'Employees Care', icon: 'fa fa-heartbeat', route: 'view-employee-mobile-modules' },
      { label: 'Role Login', icon: 'fa fa-key', route: 'view-employee-login' },
      { label: 'Designation', icon: 'fa fa-sitemap', route: 'view-designation' },
    ]
  },
  'action': {
    word: 'TARGET / TASK',
    tagline: 'Plan • Assign • Track • Complete • Follow-up',
    icon: 'fa fa-bullseye',
    chips: ['Targets & Tasks for All Branches & Departments'],
    slogan: ['Small Actions', 'Big Results', 'Stronger SPN'],
    actions: [
      { label: 'New Task', icon: 'fa fa-plus-circle', route: 'add-task' },
      { label: 'Daily Tasks & Targets', icon: 'fa fa-tasks', route: 'view-daily-tasks-targets' },
      { label: 'Target Set', icon: 'fa fa-crosshairs', route: 'set-employee-target' },
      { label: 'Task Report', icon: 'fa fa-file-text-o', route: 'task-report' },
      { label: 'Performance AI', icon: 'fa fa-magic', route: 'employee-performance-ai' },
      { label: 'Branch Actions', icon: 'fa fa-university', route: 'employee-collection-report' },
    ]
  },
  'ticket': {
    word: 'TICKET / REQUEST',
    tagline: 'Raise • Track • Resolve • Support',
    icon: 'fa fa-ticket',
    chips: ['Customer Requests', 'Employee Requests', 'Branch Requests', 'Department Requests'],
    slogan: ['Quick Support', 'Faster Resolution', 'Better Service'],
    actions: [
      { label: 'All Tickets', icon: 'fa fa-ticket', route: 'office-tickets' },
      { label: 'Leave Requests', icon: 'fa fa-plane', route: 'view-leave-requests' },
      { label: 'Refund Requests', icon: 'fa fa-undo', route: 'refund-approval' },
      { label: 'Payment Requests', icon: 'fa fa-hourglass-half', route: 'payment-pending-approvals' },
    ]
  },
  'documents': {
    word: 'DOCUMENT MANAGEMENT',
    tagline: 'Organize • Secure • Access • Anytime • Anywhere',
    icon: 'fa fa-folder-open',
    chips: ['Digital Storage', 'Quick Search', 'Secure Access', 'Branch Wise Documents'],
    slogan: ['All Documents', 'One Place', 'Greater SPN'],
    actions: [
      { label: 'Upload Document', icon: 'fa fa-cloud-upload', route: 'add-document' },
      { label: 'Customer Documents', icon: 'fa fa-users', route: 'view-documents' },
      { label: 'Employee Documents', icon: 'fa fa-id-badge', route: 'view-employee-document' },
      { label: 'Loan Documents', icon: 'fa fa-university', route: 'view-loan-customer-document' },
      { label: 'Prized Documents', icon: 'fa fa-trophy', route: 'document-preparation', qp: { kind: 'prized' } },
      { label: 'Closed Documents', icon: 'fa fa-lock', route: 'document-preparation', qp: { kind: 'closed' } },
    ]
  },
  'security': {
    word: 'SECURITY POWER',
    tagline: 'People • Data • Access • Control • Safety • Always Secure',
    icon: 'fa fa-shield',
    chips: ['User Management', 'Role & Permission', 'Login Activity', 'Device Control', 'Audit Trail'],
    slogan: ['Secure System', 'Safe Data', 'Stronger SPN'],
    actions: [
      { label: 'User Management', icon: 'fa fa-users', route: 'view-employee-login' },
      { label: 'Add User', icon: 'fa fa-user-plus', route: 'add-employee-login' },
      { label: 'Roles', icon: 'fa fa-sitemap', route: 'view-role' },
      { label: 'Role & Permission', icon: 'fa fa-key', route: 'add-permission' },
      { label: 'Audit Logs', icon: 'fa fa-history', route: 'log-monitoring-report' },
      { label: 'Device & Location', icon: 'fa fa-map-marker', route: 'view-punch-attendance' },
    ]
  },
  'reports': {
    word: 'REPORTS SECTION',
    tagline: 'Data Today | Better Decisions Tomorrow',
    icon: 'fa fa-bar-chart',
    chips: ['Accurate Data', 'Complete Reports', 'Branch Wise Analysis', 'Growth Tracking'],
    slogan: ['Reports', 'Insights', 'Growth', 'Greater SPN'],
    actions: [],
    reportGroups: [
      { title: 'Financial Reports', icon: 'fa fa-inr', links: [
        { label: 'Day Book', icon: '', route: 'day-book-report' },
        { label: 'Cash Box Book', icon: '', route: 'cashbook' },
        { label: 'Day Closing Report', icon: '', route: 'day-closing-report' },
        { label: 'Accounts Report', icon: '', route: 'accounts-report' },
        { label: 'Income / Expenses', icon: '', route: 'add-expenses-income-report' },
        { label: 'Trial Balance', icon: '', route: 'trial-balance' },
        { label: 'Profit & Loss', icon: '', route: 'profit-loss-report' },
        { label: 'Balance Sheet', icon: '', route: 'balance-sheet-report' },
      ] },
      { title: 'Customer Reports', icon: 'fa fa-users', links: [
        { label: 'Enrolled Customers', icon: '', route: 'commitment-report' },
        { label: 'Customer Ledger', icon: '', route: 'customer-ledger' },
        { label: 'Outstanding Customers', icon: '', route: 'commitmentcustomer-outstanding-report' },
        { label: 'Cancelled Customers', icon: '', route: 'cancel-customer-report' },
        { label: 'Pledge', icon: '', route: 'pledge-customer-report' },
        { label: 'Suit Filed', icon: '', route: 'suit-file-report' },
        { label: 'Birthday Report', icon: '', route: 'customer-birthday-report' },
      ] },
      { title: 'Collection Reports', icon: 'fa fa-money', links: [
        { label: 'Receipt Report', icon: '', route: 'commitment-receipt-report' },
        { label: 'UTR Receipt Report', icon: '', route: 'utr-receipt-report' },
        { label: 'Scheme-wise Payable & Pending', icon: '', route: 'scheme-wise-payable-pending-report' },
        { label: 'This Month Unpaid', icon: '', route: 'this-month-unpaid-report' },
        { label: 'Next Month Collection', icon: '', route: 'next-month-collection-report' },
        { label: 'Employee Collection', icon: '', route: 'employee-collection-report' },
        { label: 'Settlement Report', icon: '', route: 'settlement-report' },
        { label: 'Collection Prediction', icon: '', route: 'collection-prediction-report' },
      ] },
      { title: 'Sales Reports', icon: 'fa fa-line-chart', links: [
        { label: 'Business Agent Report', icon: '', route: 'business-agent-report' },
        { label: 'Commission Pending', icon: '', route: 'commission-pending-report' },
        { label: 'Commission Payment', icon: '', route: 'commission-payment-report' },
        { label: 'Incentive Payment', icon: '', route: 'incentive-payment-report' },
        { label: 'Incentive Pending', icon: '', route: 'incentive-pending-report' },
        { label: 'Lead Report', icon: '', route: 'lead-management-report' },
        { label: 'Business Forecast', icon: '', route: 'business-forecast-report' },
      ] },
      { title: 'Chit Reports', icon: 'fa fa-link', links: [
        { label: 'Prized Chit Payment', icon: '', route: 'commitment-payment-report' },
        { label: 'Prized Pending Payments', icon: '', route: 'commitment-pending-report' },
        { label: 'Promise / Final Amount', icon: '', route: 'commitment-promise-report' },
        { label: 'Refund Report', icon: '', route: 'payment-refund-report' },
        { label: 'Prized Show Report', icon: '', route: 'prized-show-report' },
        { label: 'Due No Customer List', icon: '', route: 'due-no-customer-report' },
      ] },
      { title: 'Branch Reports', icon: 'fa fa-university', links: [
        { label: 'Historical Performance', icon: '', route: 'historical-performance-report' },
        { label: 'Branch Transfer Report', icon: '', route: 'branch-transfer-customer-report' },
        { label: 'Other Charges Report', icon: '', route: 'other-receipt-charges-report' },
        { label: 'Before Enrolment Receipts', icon: '', route: 'before-enrol-receipt-report' },
      ] },
      { title: 'Employee Reports', icon: 'fa fa-id-badge', links: [
        { label: 'Task Report', icon: '', route: 'task-report' },
        { label: 'Performance AI', icon: '', route: 'employee-performance-ai' },
        { label: 'Attendance', icon: '', route: 'view-employee-attendance' },
        { label: 'Leave Report', icon: '', route: 'view-leave-requests' },
        { label: 'Employee Salary', icon: '', route: 'employee-salary' },
      ] },
      { title: 'Loan & Audit Reports', icon: 'fa fa-file-text-o', links: [
        { label: 'Loan Receipt', icon: '', route: 'loan-receipt-report' },
        { label: 'Loan Customer Report', icon: '', route: 'loan-customer-report' },
        { label: 'Loan Outstanding', icon: '', route: 'loan-customerwise-outstanding-report' },
        { label: 'Audit Logs', icon: '', route: 'log-monitoring-report' },
      ] },
    ]
  },
};
