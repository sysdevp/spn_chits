-- Run on tenant DB (e.g. spn_chits) to add SPN Eligibility Approval to dynamic_menus + Security Permissions tree.
-- Matches frontend menu id MENU.SPN_ELIGIBILITY (152) under Subscriber Management (28).

INSERT INTO `dynamic_menus` (
  `id`, `tenant_id`, `menu_title`, `icon_class`, `page_type`, `router_link`,
  `has_submenu`, `tab_type`, `parent_menu_id`, `sort_order`,
  `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deletion_remark`, `status`
) VALUES (
  152, 1, 'SPN Eligibility Approval', '', 1, 'commitment-eligibility-approval',
  0, 1, 28, 99,
  1, 1, 0, NOW(), NOW(), '', 1
) ON DUPLICATE KEY UPDATE
  `menu_title` = VALUES(`menu_title`),
  `router_link` = VALUES(`router_link`),
  `parent_menu_id` = VALUES(`parent_menu_id`),
  `status` = 1;
