-- Run once on live DB (skip any column that already exists).
-- Adds GPS punch in/out fields for mobile employee attendance.

ALTER TABLE `employee_attendances` ADD COLUMN `check_in_time` TIME NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `check_out_time` TIME NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `check_in_latitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `check_in_longitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `check_out_latitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `check_out_longitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `punch_status` VARCHAR(20) NULL DEFAULT 'none';
