<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckDynamicPermission
{
    public function handle($request, Closure $next, $permissionType)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $roleId = $user->role_id;

        // Step 1: Get permissions (serialized array)
        $permissions = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->value('role_access');

        if (!$permissions) {
            return response()->json(['message' => 'No permissions found'], 403);
        }

        $decodedPermissions = unserialize($permissions);

        // Step 2: Get route name and resolve to menu_id
        $routeName = $request->route()->getName(); // e.g., list-users

        if (!$routeName) {
            return response()->json(['message' => 'Route name not found'], 403);
        }

        $routerLink = DB::table('dynamic_menus')
            ->where('route_name', $routeName)
            ->value('router_link');

        if (!$routerLink) {
            return response()->json(['message' => 'Router link not found'], 403);
        }

        $menuId = DB::table('dynamic_menus')
            ->where('router_link', $routerLink)
            ->value('id');

        if (!$menuId) {
            return response()->json(['message' => 'Menu ID not found'], 403);
        }

        // Step 3: Check permission recursively
        $hasPermission = $this->searchPermission($decodedPermissions, $menuId, $permissionType);

        if (!$hasPermission) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return $next($request);
    }

    private function searchPermission($permissionsArray, $targetMenuId, $permissionType)
    {
        foreach ($permissionsArray as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (
                isset($item['menu_id']) &&
                (int)$item['menu_id'] === (int)$targetMenuId &&
                !empty($item[$permissionType . '_perm'])
            ) {
                return true;
            }

            if (!empty($item['Submenus']) && is_array($item['Submenus'])) {
				
                if ($this->searchPermission($item['Submenus'], $targetMenuId, $permissionType)) {
                    return true;
                }
            }
        }

        return false;
    }
}
