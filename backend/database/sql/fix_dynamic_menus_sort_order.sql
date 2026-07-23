-- Align Security Permissions menu order with sidebar (layout-sidenav).
-- Run on tenant DB. Does not change sidebar HTML — only dynamic_menus.sort_order
-- (and menu_title for Masters → matches "Chit" label in sidenav).
-- Requires backend: get-menu-for-access-setting uses sort_order ASC.

-- Top-level modules (parent_menu_id = 0)
UPDATE `dynamic_menus` SET `sort_order` = 1  WHERE `id` = 1;   -- Dashboard
UPDATE `dynamic_menus` SET `sort_order` = 2  WHERE `id` = 2;   -- Masters / Chit
UPDATE `dynamic_menus` SET `sort_order` = 3  WHERE `id` = 34;  -- HR (Employee Management)
UPDATE `dynamic_menus` SET `sort_order` = 4  WHERE `id` = 28;  -- Subscriber / CRM
UPDATE `dynamic_menus` SET `sort_order` = 5  WHERE `id` = 37;  -- Transaction Management
UPDATE `dynamic_menus` SET `sort_order` = 6  WHERE `id` = 42;  -- Reports
UPDATE `dynamic_menus` SET `sort_order` = 7  WHERE `id` = 85;  -- Loan Management
UPDATE `dynamic_menus` SET `sort_order` = 8  WHERE `id` = 62;  -- Other Transactions
UPDATE `dynamic_menus` SET `sort_order` = 9  WHERE `id` = 25;  -- Accounts (legacy module)
UPDATE `dynamic_menus` SET `sort_order` = 10 WHERE `id` = 52;  -- Menu Management
UPDATE `dynamic_menus` SET `sort_order` = 11 WHERE `id` = 53;  -- Security Permissions
UPDATE `dynamic_menus` SET `sort_order` = 12 WHERE `id` = 111; -- Bidding Customer

-- Optional: match sidenav group label (permission header only)
UPDATE `dynamic_menus` SET `menu_title` = 'Chit' WHERE `id` = 2;

-- Masters (2) — schemes, location, accounts masters used in sidenav
UPDATE `dynamic_menus` SET `sort_order` = 1  WHERE `id` = 54;  -- Tenant
UPDATE `dynamic_menus` SET `sort_order` = 2  WHERE `id` = 8;   -- Scheme
UPDATE `dynamic_menus` SET `sort_order` = 3  WHERE `id` = 9;   -- Group
UPDATE `dynamic_menus` SET `sort_order` = 4  WHERE `id` = 6;   -- Head Office / Regional
UPDATE `dynamic_menus` SET `sort_order` = 5  WHERE `id` = 7;   -- Branch
UPDATE `dynamic_menus` SET `sort_order` = 6  WHERE `id` = 113; -- Country
UPDATE `dynamic_menus` SET `sort_order` = 7  WHERE `id` = 114; -- State
UPDATE `dynamic_menus` SET `sort_order` = 8  WHERE `id` = 115; -- District
UPDATE `dynamic_menus` SET `sort_order` = 9  WHERE `id` = 15;  -- City
UPDATE `dynamic_menus` SET `sort_order` = 10 WHERE `id` = 10;  -- Collection Area
UPDATE `dynamic_menus` SET `sort_order` = 11 WHERE `id` = 17;  -- Bank Detail
UPDATE `dynamic_menus` SET `sort_order` = 12 WHERE `id` = 13;  -- Charges Type
UPDATE `dynamic_menus` SET `sort_order` = 13 WHERE `id` = 102; -- Account Group
UPDATE `dynamic_menus` SET `sort_order` = 14 WHERE `id` = 103; -- Account Head
UPDATE `dynamic_menus` SET `sort_order` = 15 WHERE `id` = 11;  -- Role
UPDATE `dynamic_menus` SET `sort_order` = 16 WHERE `id` = 12;  -- Designation
UPDATE `dynamic_menus` SET `sort_order` = 17 WHERE `id` = 14;  -- Relationship
UPDATE `dynamic_menus` SET `sort_order` = 18 WHERE `id` = 16;  -- Auction On Holidays
UPDATE `dynamic_menus` SET `sort_order` = 19 WHERE `id` = 90;  -- Source Type / Lead Source
UPDATE `dynamic_menus` SET `sort_order` = 20 WHERE `id` = 116; -- Case Stage
UPDATE `dynamic_menus` SET `sort_order` = 21 WHERE `id` = 117; -- Lawyer
UPDATE `dynamic_menus` SET `sort_order` = 22 WHERE `id` = 19;  -- Slabs group
UPDATE `dynamic_menus` SET `sort_order` = 23 WHERE `id` = 18;  -- Petty Cash Type
UPDATE `dynamic_menus` SET `sort_order` = 24 WHERE `id` = 112; -- Family Code
UPDATE `dynamic_menus` SET `sort_order` = 25 WHERE `id` = 110; -- Bidding Form

-- Slabs (19)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 20; -- Due Slab
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 24; -- Payment Slab
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 21; -- Penalty Slab
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 22; -- Bonus Slab
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 23; -- Interest Slab

-- HR / Employee Management (34) — sidenav order
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 3;   -- Employee
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 35;  -- Attendance
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 36;  -- Employee Login
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 101; -- Employee Salary
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 55;  -- Collection Area Mapping
UPDATE `dynamic_menus` SET `sort_order` = 6 WHERE `id` = 118; -- Employee Documents

-- Subscriber Management (28) — Chit + CRM items in sidenav
UPDATE `dynamic_menus` SET `sort_order` = 1  WHERE `id` = 5;   -- Customer / Profile Creation
UPDATE `dynamic_menus` SET `sort_order` = 2  WHERE `id` = 32;  -- Commitment Chit / SPN Approved
UPDATE `dynamic_menus` SET `sort_order` = 3  WHERE `id` = 152; -- SPN Eligibility Approval (if row exists)
UPDATE `dynamic_menus` SET `sort_order` = 4  WHERE `id` = 56;  -- Commitment Payment
UPDATE `dynamic_menus` SET `sort_order` = 5  WHERE `id` = 41;  -- Leads
UPDATE `dynamic_menus` SET `sort_order` = 6  WHERE `id` = 29;  -- Customer Enrollment
UPDATE `dynamic_menus` SET `sort_order` = 7  WHERE `id` = 30;  -- Auction Detail
UPDATE `dynamic_menus` SET `sort_order` = 8  WHERE `id` = 38;  -- Documents
UPDATE `dynamic_menus` SET `sort_order` = 9  WHERE `id` = 39;  -- Commission Payment
UPDATE `dynamic_menus` SET `sort_order` = 10 WHERE `id` = 80;  -- Collection Activity
UPDATE `dynamic_menus` SET `sort_order` = 11 WHERE `id` = 119; -- Pledge Customer
UPDATE `dynamic_menus` SET `sort_order` = 12 WHERE `id` = 151; -- Nominee
UPDATE `dynamic_menus` SET `sort_order` = 13 WHERE `id` = 4;   -- Guarantor

-- Transaction Management (37)
UPDATE `dynamic_menus` SET `sort_order` = 1  WHERE `id` = 57;  -- Commitment Receipt
UPDATE `dynamic_menus` SET `sort_order` = 2  WHERE `id` = 31;  -- Receipt Detail
UPDATE `dynamic_menus` SET `sort_order` = 3  WHERE `id` = 58;  -- Advance Receipt
UPDATE `dynamic_menus` SET `sort_order` = 4  WHERE `id` = 120; -- Before Enrollment Receipt
UPDATE `dynamic_menus` SET `sort_order` = 5  WHERE `id` = 60;  -- Other Receipt Charges
UPDATE `dynamic_menus` SET `sort_order` = 6  WHERE `id` = 61;  -- Customer Ledger
UPDATE `dynamic_menus` SET `sort_order` = 7  WHERE `id` = 59;  -- BidPayment Adjustments
UPDATE `dynamic_menus` SET `sort_order` = 8  WHERE `id` = 33;  -- Enrol Refund Amount
UPDATE `dynamic_menus` SET `sort_order` = 9  WHERE `id` = 109; -- Commitment Enroll Refund
UPDATE `dynamic_menus` SET `sort_order` = 10 WHERE `id` = 104; -- Expense Income
UPDATE `dynamic_menus` SET `sort_order` = 11 WHERE `id` = 96;  -- Employee Incentive
UPDATE `dynamic_menus` SET `sort_order` = 12 WHERE `id` = 121; -- Unknown Transaction
UPDATE `dynamic_menus` SET `sort_order` = 13 WHERE `id` = 122; -- Transfer Customer
UPDATE `dynamic_menus` SET `sort_order` = 14 WHERE `id` = 123; -- Commission Payment (dup route)

-- Other Transactions (62)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 26; -- Petty Cash Expenses
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 27; -- Petty Cash Income

-- Loan Management (85)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 100; -- Loan Customer Document
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 86;  -- Loan Details
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 87;  -- Loan Receipt

-- Reports (42) — sidenav report group order
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 124; -- Customer / Subscribers Report group
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 65; -- Payment Report group
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 43; -- Outstanding Report group
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 66; -- Transaction / Collection Report group
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 67; -- Accounts Report group
UPDATE `dynamic_menus` SET `sort_order` = 6 WHERE `id` = 68; -- Business Development Report group
UPDATE `dynamic_menus` SET `sort_order` = 7 WHERE `id` = 83; -- Loan Report group
UPDATE `dynamic_menus` SET `sort_order` = 8 WHERE `id` = 88; -- Log Monitoring Report group
UPDATE `dynamic_menus` SET `sort_order` = 9 WHERE `id` = 48; -- Enrollment Report group
UPDATE `dynamic_menus` SET `sort_order` = 10 WHERE `id` = 50; -- Auction Report group
UPDATE `dynamic_menus` SET `sort_order` = 11 WHERE `id` = 45; -- Legacy Payment Report node

-- Under Customer Report (124)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 126; -- Customer Ledger report
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 125; -- Customer Label report
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 127; -- Collection Activity report

-- Under Payment Report (65)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 63; -- Payment Refund
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 70; -- Commitment Payment Report
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 71; -- Commitment Pending Report
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 46; -- Prized Money Pending
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 47; -- Prized Money Released

-- Under Outstanding Report (43)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 81;  -- Commitment Customer Outstanding
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 93;  -- Loan Customerwise Outstanding
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 44;  -- Groupwise Outstanding
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 92;  -- Employeewise Outstanding
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 141; -- Installmentwise Outstanding
UPDATE `dynamic_menus` SET `sort_order` = 6 WHERE `id` = 142; -- Group Abstract
UPDATE `dynamic_menus` SET `sort_order` = 7 WHERE `id` = 143; -- Current Month Collection
UPDATE `dynamic_menus` SET `sort_order` = 8 WHERE `id` = 144; -- Overall Business Report

-- Under Transaction Report (66) / Collection Report
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 82;  -- Commitment Receipt Report
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 51;  -- Receipt Report
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 145; -- Employee Collection Report
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 146; -- Before Enrollment Report
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 74;  -- Other Receipt Charges Report
UPDATE `dynamic_menus` SET `sort_order` = 6 WHERE `id` = 147; -- Settlement Report
UPDATE `dynamic_menus` SET `sort_order` = 7 WHERE `id` = 72;  -- Advance Receipt Report
UPDATE `dynamic_menus` SET `sort_order` = 8 WHERE `id` = 73;  -- Available Advance Report

-- Under Accounts Report (67)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 148; -- Day Book
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 75;  -- Day Closing Report
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 76;  -- Accounts Report
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 94;  -- Loan Day Closing Report
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 105; -- Expense Income Report
UPDATE `dynamic_menus` SET `sort_order` = 6 WHERE `id` = 106; -- Trial Balance
UPDATE `dynamic_menus` SET `sort_order` = 7 WHERE `id` = 108; -- Profit Loss
UPDATE `dynamic_menus` SET `sort_order` = 8 WHERE `id` = 107; -- Balance Sheet

-- Under Business Development Report (68)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 77;  -- Business Agent Report
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 78;  -- Commission Pending Report
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 150; -- Commission Payment Report
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 98;  -- Incentive Payment Report
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 97;  -- Incentive Pending Report
UPDATE `dynamic_menus` SET `sort_order` = 6 WHERE `id` = 95;  -- Lead Report
UPDATE `dynamic_menus` SET `sort_order` = 7 WHERE `id` = 149; -- Commission Pending (alt)
UPDATE `dynamic_menus` SET `sort_order` = 8 WHERE `id` = 79;  -- Legacy Commission Payment

-- Under Enrollment Report (48)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 69;  -- Commitment Chit - Enrolled Subscriber
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 49;  -- Vacant Report
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 128; -- Un-Enrollment Report
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 129; -- FD Report
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 130; -- Group Starting Report
UPDATE `dynamic_menus` SET `sort_order` = 6 WHERE `id` = 131; -- Group Ending Report
UPDATE `dynamic_menus` SET `sort_order` = 7 WHERE `id` = 132; -- Transfer Report
UPDATE `dynamic_menus` SET `sort_order` = 8 WHERE `id` = 133; -- Suit File Report

-- Under Auction Report (50)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 64;  -- Auction Report
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 134; -- Auction Minutes Report
UPDATE `dynamic_menus` SET `sort_order` = 3 WHERE `id` = 135; -- Pre Auction Report
UPDATE `dynamic_menus` SET `sort_order` = 4 WHERE `id` = 136; -- Monthly Auction Report
UPDATE `dynamic_menus` SET `sort_order` = 5 WHERE `id` = 137; -- Draw Report
UPDATE `dynamic_menus` SET `sort_order` = 6 WHERE `id` = 138; -- Company Profit Report
UPDATE `dynamic_menus` SET `sort_order` = 7 WHERE `id` = 139; -- Company Excess Fund Report
UPDATE `dynamic_menus` SET `sort_order` = 8 WHERE `id` = 99;  -- Commission Profit Report

-- Under Loan Report (83)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 84; -- Loan Report
UPDATE `dynamic_menus` SET `sort_order` = 2 WHERE `id` = 91; -- Loan customer Report

-- Under Log Monitoring (88)
UPDATE `dynamic_menus` SET `sort_order` = 1 WHERE `id` = 89; -- Log Monitoring Report

-- SPN Eligibility (152) — ensure exists under Subscriber (28)
INSERT INTO `dynamic_menus` (
  `id`, `tenant_id`, `menu_title`, `icon_class`, `page_type`, `router_link`,
  `has_submenu`, `tab_type`, `parent_menu_id`, `sort_order`,
  `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deletion_remark`, `status`
) VALUES (
  152, 1, 'SPN Eligibility Approval', '', 1, 'commitment-eligibility-approval',
  0, 1, 28, 3,
  1, 1, 0, NOW(), NOW(), '', 1
) ON DUPLICATE KEY UPDATE
  `menu_title` = VALUES(`menu_title`),
  `router_link` = VALUES(`router_link`),
  `parent_menu_id` = 28,
  `sort_order` = 3,
  `status` = 1;
