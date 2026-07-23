<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permType = 'view', $routeName = null)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $permissions = [];

    // Step 1: Get permissions from role
    if ($user->role && $user->role->permissions) {
        $raw = $user->role->permissions->role_access;

        $decoded = @unserialize($raw);
        $permissions = is_array($decoded) ? $decoded : [];
    }

    // Step 2: Find the matching permission entry by route_name
    $matched = null;

    foreach ($permissions as $perm) {
		
        if (isset($perm['menu_title']) && $perm['menu_title'] === $routeName) {
            $matched = $perm;
            break;
        }
        // OR use route_name if available:
        if (isset($perm['route_name']) && $perm['route_name'] === $routeName) {
            $matched = $perm;
            break;
        }
    }

    if (!$matched || empty($matched["{$permType}_perm"])) {
        return response()->json(['success' => false, 'message' => 'Forbidden – permission missing'], 403);
    }

    return $next($request);
}

}
