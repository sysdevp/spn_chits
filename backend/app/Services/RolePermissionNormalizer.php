<?php

namespace App\Services;

/**
 * Normalizes permission payloads from the Angular security screen before save.
 * Merges duplicate menu_id rows per module and keeps highest permission flags.
 */
class RolePermissionNormalizer
{
    private const MODULE_ORDER = [1, 2, 19, 28, 34, 37, 42, 48, 65, 43, 66, 67, 68, 83, 88, 124, 85, 62, 53];

    public function normalize($permissions): array
    {
        if (!is_array($permissions)) {
            return [];
        }

        $moduleMap = [];

        foreach ($permissions as $module) {
            if (!is_array($module)) {
                continue;
            }
            $moduleId = (int) ($module['menu_id'] ?? 0);
            $hasSub = (int) ($module['has_sub_menu'] ?? 0) === 1;

            if (!$hasSub) {
                $moduleMap[$moduleId] = $this->normalizeModule($module, []);
                continue;
            }

            $subs = isset($module['Submenus']) && is_array($module['Submenus']) ? $module['Submenus'] : [];
            if (!isset($moduleMap[$moduleId])) {
                $moduleMap[$moduleId] = $this->normalizeModule($module, []);
            }
            foreach ($subs as $sub) {
                if (!is_array($sub)) {
                    continue;
                }
                $menuId = (int) ($sub['menu_id'] ?? 0);
                if ($menuId <= 0) {
                    continue;
                }
                $existing = null;
                foreach ($moduleMap[$moduleId]['Submenus'] as $idx => $row) {
                    if ((int) $row['menu_id'] === $menuId) {
                        $existing = $idx;
                        break;
                    }
                }
                if ($existing === null) {
                    $moduleMap[$moduleId]['Submenus'][] = $this->normalizeSubmenu($sub);
                } else {
                    $moduleMap[$moduleId]['Submenus'][$existing] = $this->mergeSubmenu(
                        $moduleMap[$moduleId]['Submenus'][$existing],
                        $sub
                    );
                }
            }
        }

        $ordered = [];
        foreach (self::MODULE_ORDER as $id) {
            if (isset($moduleMap[$id])) {
                $ordered[] = $moduleMap[$id];
                unset($moduleMap[$id]);
            }
        }
        foreach ($moduleMap as $mod) {
            $ordered[] = $mod;
        }

        return $ordered;
    }

    private function normalizeModule(array $module, array $submenus): array
    {
        return [
            'menu_id' => (int) ($module['menu_id'] ?? 0),
            'menu_title' => $module['menu_title'] ?? '',
            'page_type' => (int) ($module['page_type'] ?? 4),
            'has_sub_menu' => (int) ($module['has_sub_menu'] ?? 0),
            'add_perm' => $this->perm($module['add_perm'] ?? 0),
            'edit_perm' => $this->perm($module['edit_perm'] ?? 0),
            'view_perm' => $this->perm($module['view_perm'] ?? 0),
            'delete_perm' => $this->perm($module['delete_perm'] ?? 0),
            'Submenus' => $submenus,
        ];
    }

    private function normalizeSubmenu(array $sub): array
    {
        $row = [
            'menu_id' => (int) ($sub['menu_id'] ?? 0),
            'menu_title' => $sub['menu_title'] ?? '',
            'page_type' => (int) ($sub['page_type'] ?? 1),
            'has_sub_menu' => 0,
            'add_perm' => $this->perm($sub['add_perm'] ?? 0),
            'edit_perm' => $this->perm($sub['edit_perm'] ?? 0),
            'view_perm' => $this->perm($sub['view_perm'] ?? 0),
            'delete_perm' => $this->perm($sub['delete_perm'] ?? 0),
        ];
        if (array_key_exists('top_perm', $sub)) {
            $row['top_perm'] = $this->perm($sub['top_perm'] ?? 0);
        }
        return $row;
    }

    private function mergeSubmenu(array $target, array $source): array
    {
        $merged = $this->normalizeSubmenu($source);
        $merged['add_perm'] = max($target['add_perm'], $merged['add_perm']);
        $merged['edit_perm'] = max($target['edit_perm'], $merged['edit_perm']);
        $merged['view_perm'] = max($target['view_perm'], $merged['view_perm']);
        $merged['delete_perm'] = max($target['delete_perm'], $merged['delete_perm']);
        if (isset($target['top_perm']) || isset($merged['top_perm'])) {
            $merged['top_perm'] = max($target['top_perm'] ?? 0, $merged['top_perm'] ?? 0);
        }
        $merged['menu_title'] = $target['menu_title'] ?: $merged['menu_title'];
        return $merged;
    }

    private function perm($value): int
    {
        return (int) $value === 1 ? 1 : 0;
    }
}
