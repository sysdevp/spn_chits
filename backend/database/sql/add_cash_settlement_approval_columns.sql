-- Run once on live DB (skip any column that already exists).
-- Adds approval fields for Settlement Report approve flow.

ALTER TABLE `cash_settlements` ADD COLUMN `is_approved` TINYINT NOT NULL DEFAULT 0;
ALTER TABLE `cash_settlements` ADD COLUMN `approval_remarks` TEXT NULL;
ALTER TABLE `cash_settlements` ADD COLUMN `approved_by` INT NULL DEFAULT 0;
ALTER TABLE `cash_settlements` ADD COLUMN `approved_at` TIMESTAMP NULL DEFAULT NULL;
