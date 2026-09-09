-- Extended employee mobile modules: skills, performance, work plans, visits, achievements, attendance settings

CREATE TABLE IF NOT EXISTS `employee_skills` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `skill_name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `description` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_skill_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_skill_assessments` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `skill_id` int(11) NOT NULL DEFAULT 0,
  `score` decimal(5,2) NOT NULL DEFAULT 0,
  `strength_notes` text DEFAULT NULL,
  `weakness_notes` text DEFAULT NULL,
  `training_required` tinyint(4) NOT NULL DEFAULT 0,
  `training_notes` text DEFAULT NULL,
  `assessed_by` int(11) NOT NULL DEFAULT 0,
  `assessed_date` date DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_assess_emp` (`employee_id`, `skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_performance_scores` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `period_month` varchar(7) NOT NULL,
  `collection_score` decimal(5,2) NOT NULL DEFAULT 0,
  `sales_score` decimal(5,2) NOT NULL DEFAULT 0,
  `activity_score` decimal(5,2) NOT NULL DEFAULT 0,
  `attendance_score` decimal(5,2) NOT NULL DEFAULT 0,
  `overall_score` decimal(5,2) NOT NULL DEFAULT 0,
  `performance_rating` varchar(20) DEFAULT 'Average',
  `strengths` text DEFAULT NULL,
  `weaknesses` text DEFAULT NULL,
  `training_required` tinyint(4) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_perf_emp_month` (`employee_id`, `period_month`)
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
  PRIMARY KEY (`id`),
  KEY `idx_work_plan_emp_date` (`employee_id`, `plan_date`)
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
  PRIMARY KEY (`id`),
  KEY `idx_visit_emp_date` (`employee_id`, `visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_badges` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `badge_name` varchar(150) NOT NULL,
  `badge_code` varchar(50) NOT NULL,
  `criteria_type` varchar(50) NOT NULL DEFAULT 'collection_percent',
  `criteria_value` decimal(10,2) NOT NULL DEFAULT 0,
  `icon` varchar(50) DEFAULT 'star',
  `description` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_badge_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_badge_awards` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `badge_id` int(11) NOT NULL DEFAULT 0,
  `awarded_date` date NOT NULL,
  `period_month` varchar(7) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_award_emp` (`employee_id`, `period_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_certificates` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `employee_id` int(11) NOT NULL DEFAULT 0,
  `certificate_title` varchar(255) NOT NULL,
  `certificate_type` varchar(50) NOT NULL DEFAULT 'achievement',
  `period_month` varchar(7) DEFAULT NULL,
  `issued_date` date NOT NULL,
  `issued_by` int(11) NOT NULL DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cert_emp` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employee_attendance_settings` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL DEFAULT 0,
  `shift_start_time` time NOT NULL DEFAULT '09:00:00',
  `shift_end_time` time NOT NULL DEFAULT '18:00:00',
  `late_grace_minutes` int(11) NOT NULL DEFAULT 15,
  `early_leave_grace_minutes` int(11) NOT NULL DEFAULT 15,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_att_set_branch` (`tenant_id`, `branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Extend employee_targets with typed targets if column missing
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'employee_targets' AND COLUMN_NAME = 'target_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `employee_targets` ADD COLUMN `target_type` varchar(30) NOT NULL DEFAULT ''collection'' AFTER `employee_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
