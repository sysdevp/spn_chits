-- Branch office geo-fence for punch attendance
ALTER TABLE `branches` ADD COLUMN `latitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `branches` ADD COLUMN `longitude` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `branches` ADD COLUMN `punch_radius_meters` INT NULL DEFAULT 50;

-- Outside-office punch flags on attendance
ALTER TABLE `employee_attendances` ADD COLUMN `outside_office` TINYINT(1) NULL DEFAULT 0;
ALTER TABLE `employee_attendances` ADD COLUMN `outside_office_remarks` TEXT NULL;
ALTER TABLE `employee_attendances` ADD COLUMN `punch_distance_meters` DECIMAL(10,2) NULL DEFAULT NULL;
