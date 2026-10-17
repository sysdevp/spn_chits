-- Live location columns for employees while checked-in (punch_status = checked_in).
-- Run on tenant DB. Skip any column that already exists.

ALTER TABLE `employee_attendances` ADD COLUMN `live_latitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `live_longitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `live_location_at` DATETIME NULL DEFAULT NULL;
