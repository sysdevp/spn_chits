-- =============================================================================
-- SPN Employee App — tenant schema (safe to re-run)
-- Run this on the TENANT database (the `db` value from mobile login),
-- NOT the master database.
--
-- demo_mobile_sample_data.sql is only sample rows. This file creates the
-- tables/columns the Work / Tickets / Field verification / Loans screens need.
-- =============================================================================

-- Tasks + targets
CREATE TABLE IF NOT EXISTS `employee_tasks` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `task_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'medium',
  `task_status` varchar(20) NOT NULL DEFAULT 'pending',
  `assigned_by` int(11) NOT NULL DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `employee_remark` text DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_targets` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `target_type` varchar(50) NOT NULL DEFAULT 'existing_client_collection',
  `period_type` varchar(20) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `target_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `achieved_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `target_label` varchar(100) DEFAULT 'Collection',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'employee_targets' AND COLUMN_NAME = 'target_type');
SET @sql = IF(@col = 0, 'ALTER TABLE `employee_targets` ADD COLUMN `target_type` varchar(50) NOT NULL DEFAULT ''existing_client_collection'' AFTER `employee_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `employee_leave_requests` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `request_type` varchar(20) NOT NULL,
  `leave_type` tinyint(4) NOT NULL DEFAULT 0,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `from_time` time DEFAULT NULL,
  `to_time` time DEFAULT NULL,
  `session` varchar(20) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `request_status` varchar(20) NOT NULL DEFAULT 'pending',
  `approved_by` int(11) NOT NULL DEFAULT 0,
  `approved_at` datetime DEFAULT NULL,
  `approval_remark` text DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_work_plans` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `plan_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `plan_status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_customer_visits` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `customer_id` int(11) NOT NULL DEFAULT 0,
  `lead_id` int(11) NOT NULL DEFAULT 0,
  `visit_type` varchar(30) NOT NULL DEFAULT 'collection',
  `visit_date` date NOT NULL,
  `visit_time` time DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `outcome` text DEFAULT NULL,
  `followup_date` date DEFAULT NULL,
  `visit_status` varchar(20) NOT NULL DEFAULT 'completed',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_loan_advances` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `request_type` varchar(20) NOT NULL DEFAULT 'advance',
  `amount` decimal(15,2) NOT NULL DEFAULT 0,
  `tenure_months` int(11) NOT NULL DEFAULT 0,
  `emi_amount` decimal(15,2) NOT NULL DEFAULT 0,
  `request_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `request_status` varchar(20) NOT NULL DEFAULT 'pending',
  `decided_by` int(11) NOT NULL DEFAULT 0,
  `decided_at` datetime DEFAULT NULL,
  `decision_remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `office_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_no` VARCHAR(40) NOT NULL,
  `tenant_id` INT NOT NULL DEFAULT 0,
  `branch_id` INT NOT NULL DEFAULT 0,
  `employee_id` INT NOT NULL DEFAULT 0,
  `employee_name` VARCHAR(191) NULL,
  `branch_name` VARCHAR(191) NULL,
  `kind` VARCHAR(20) NOT NULL DEFAULT 'office_wish',
  `category` VARCHAR(80) NULL,
  `subject` VARCHAR(191) NULL,
  `description` TEXT NULL,
  `priority` VARCHAR(20) NOT NULL DEFAULT 'medium',
  `from_date` DATE NULL,
  `to_date` DATE NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'open',
  `attachment` VARCHAR(255) NULL,
  `rating` INT NULL,
  `feedback` TEXT NULL,
  `decision_remarks` TEXT NULL,
  `updated_by` INT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `office_ticket_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT NOT NULL,
  `sender_name` VARCHAR(191) NULL,
  `sender_role` VARCHAR(40) NULL,
  `message` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payment_desk_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT NOT NULL DEFAULT 0,
  `branch_id` INT NOT NULL DEFAULT 0,
  `customer_id` INT NOT NULL DEFAULT 0,
  `enrollment_id` INT NOT NULL DEFAULT 0,
  `kind` VARCHAR(20) NOT NULL,
  `customer_name` VARCHAR(191) NULL,
  `mobile` VARCHAR(50) NULL,
  `ar_no` VARCHAR(80) NULL,
  `scheme_name` VARCHAR(191) NULL,
  `chit_value` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `months` VARCHAR(50) NULL,
  `start_date` VARCHAR(40) NULL,
  `end_date` VARCHAR(40) NULL,
  `prized_month` VARCHAR(40) NULL,
  `closed_month` VARCHAR(40) NULL,
  `year_label` VARCHAR(40) NULL,
  `amount` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `deduction` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `net_amount` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `payment_mode` VARCHAR(50) NULL,
  `payment_date` VARCHAR(40) NULL,
  `transaction_no` VARCHAR(80) NULL,
  `bank_name` VARCHAR(120) NULL,
  `reason` VARCHAR(255) NULL,
  `remarks` TEXT NULL,
  `branch_split` TEXT NULL,
  `documents` TEXT NULL,
  `photos` TEXT NULL,
  `home_lat` VARCHAR(50) NULL,
  `home_lng` VARCHAR(50) NULL,
  `collection_lat` VARCHAR(50) NULL,
  `collection_lng` VARCHAR(50) NULL,
  `field_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `status` VARCHAR(30) NOT NULL DEFAULT 'draft',
  `approval_remarks` TEXT NULL,
  `created_by` INT NULL,
  `approved_by` INT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cash in hand: pending until approved (then deducted from cash in hand)
SET @tbl = (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cash_settlements');
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cash_settlements' AND COLUMN_NAME = 'is_approved');
SET @sql = IF(@tbl > 0 AND @col = 0, 'ALTER TABLE `cash_settlements` ADD COLUMN `is_approved` TINYINT NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cash_settlements' AND COLUMN_NAME = 'approval_remarks');
SET @sql = IF(@tbl > 0 AND @col = 0, 'ALTER TABLE `cash_settlements` ADD COLUMN `approval_remarks` TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cash_settlements' AND COLUMN_NAME = 'approved_by');
SET @sql = IF(@tbl > 0 AND @col = 0, 'ALTER TABLE `cash_settlements` ADD COLUMN `approved_by` INT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cash_settlements' AND COLUMN_NAME = 'approved_at');
SET @sql = IF(@tbl > 0 AND @col = 0, 'ALTER TABLE `cash_settlements` ADD COLUMN `approved_at` DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Collection follow-up time (only if remarks table exists)
SET @tbl = (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'remarks');
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'remarks' AND COLUMN_NAME = 'next_followup_time');
SET @sql = IF(@tbl > 0 AND @col = 0, 'ALTER TABLE `remarks` ADD COLUMN `next_followup_time` time NULL DEFAULT NULL AFTER `next_followup_date`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
