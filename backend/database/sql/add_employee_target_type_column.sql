-- Run once if target_type column is missing on employee_targets
ALTER TABLE `employee_targets`
  ADD COLUMN `target_type` VARCHAR(50) NOT NULL DEFAULT 'existing_client_collection' AFTER `employee_id`;
