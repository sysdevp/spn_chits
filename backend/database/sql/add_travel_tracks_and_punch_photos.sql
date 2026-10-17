-- Travel trail + punch selfie columns (also auto-created by MobileApiController at runtime)

CREATE TABLE IF NOT EXISTS `employee_location_tracks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT NOT NULL DEFAULT 0,
  `branch_id` INT NOT NULL DEFAULT 0,
  `employee_id` INT NOT NULL DEFAULT 0,
  `latitude` VARCHAR(50) NULL,
  `longitude` VARCHAR(50) NULL,
  `location_name` VARCHAR(255) NULL,
  `recorded_at` DATETIME NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_emp_track_day` (`tenant_id`, `employee_id`, `recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `employee_attendances`
  ADD COLUMN IF NOT EXISTS `check_in_photo` VARCHAR(255) NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `check_out_photo` VARCHAR(255) NULL DEFAULT NULL;
