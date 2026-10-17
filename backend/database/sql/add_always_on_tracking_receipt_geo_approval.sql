-- Always-on employee live location (independent of punch)
CREATE TABLE IF NOT EXISTS `employee_live_locations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) NOT NULL DEFAULT 0,
  `branch_id` INT(11) NOT NULL DEFAULT 0,
  `employee_id` INT(11) NOT NULL DEFAULT 0,
  `user_id` INT(11) NOT NULL DEFAULT 0,
  `latitude` VARCHAR(50) NULL,
  `longitude` VARCHAR(50) NULL,
  `location_name` VARCHAR(255) NULL,
  `updated_at` DATETIME NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_employee_live` (`tenant_id`, `employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Receipt capture location
ALTER TABLE `receipts` ADD COLUMN `latitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `receipts` ADD COLUMN `longitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `receipts` ADD COLUMN `location_name` VARCHAR(255) NULL DEFAULT NULL;

-- Outside-office punch approval + location name on attendance
ALTER TABLE `employee_attendances` ADD COLUMN `outside_approval_status` VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `approved_by` INT(11) NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `approved_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `live_location_name` VARCHAR(255) NULL DEFAULT NULL;
