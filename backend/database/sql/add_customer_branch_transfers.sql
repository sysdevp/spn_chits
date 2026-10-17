-- Customer branch transfer history (also auto-created by CustomerController)
CREATE TABLE IF NOT EXISTS `customer_branch_transfers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT NOT NULL DEFAULT 0,
  `customer_id` INT NOT NULL DEFAULT 0,
  `from_branch_id` INT NOT NULL DEFAULT 0,
  `to_branch_id` INT NOT NULL DEFAULT 0,
  `remarks` TEXT NULL,
  `transferred_by` INT NOT NULL DEFAULT 0,
  `transfer_date` DATE NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cbt_tenant_date` (`tenant_id`, `transfer_date`),
  KEY `idx_cbt_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
