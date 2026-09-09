<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

/**
 * Branch / employee scope for logged-in users (user_branch_mapping).
 */
class UserLoginScopeService
{
    /**
     * @return int[]|null null = no restriction (tenant-wide); [] = none allowed
     */
    public function resolveAllowedBranchIds(int $tenantId, int $userId, int $logId): ?array
    {
        if ($userId <= 0 && $logId > 0) {
            $userQuery = User::where('employee_id', $logId);
            if ($tenantId > 0) {
                $userQuery->where('tenant_id', $tenantId);
            }
            $resolved = $userQuery->first();
            if ($resolved) {
                $userId = (int) $resolved->id;
            }
        }

        // Super admin (user id 1) — all branches
        if ($userId === 1) {
            return null;
        }

        if ($userId <= 0 && $logId > 0) {
            $emp = Employee::find($logId);
            if ($emp && !empty($emp->branch_id)) {
                return [(int) $emp->branch_id];
            }
            return null;
        }

        if ($userId <= 0) {
            return null;
        }

        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        $branchIds = [];
        $bQuery = DB::table('user_branch_mapping')
            ->where('user_id', $userId)
            ->where('status', 1);
        if ($tenantId > 0) {
            $bQuery->where('tenant_id', $tenantId);
        }
        foreach ($bQuery->pluck('branch_id') as $bid) {
            $branchIds[] = (int) $bid;
        }

        if (!empty($branchIds)) {
            return array_values(array_unique(array_filter($branchIds)));
        }

        if (!empty($user->branch_id)) {
            return [(int) $user->branch_id];
        }

        if (!empty($user->employee_id)) {
            $emp = Employee::find($user->employee_id);
            if ($emp && !empty($emp->branch_id)) {
                return [(int) $emp->branch_id];
            }
            return [];
        }

        // Tenant admin login with no branch mapping — all branches
        return null;
    }

    public function resolveAllowedBranchIdsFromRequest(Request $request): ?array
    {
        $tenantId = 0;
        if (isset($request->conditions['tenant_id'])) {
            $tenantId = (int) $request->conditions['tenant_id'];
        } elseif (!empty($request->tenant_id)) {
            $tenantId = (int) $request->tenant_id;
        }

        $roleTypeId = null;
        if (isset($request->role_type_id)) {
            $roleTypeId = (int) $request->role_type_id;
        } elseif (isset($request->conditions['role_type_id'])) {
            $roleTypeId = (int) $request->conditions['role_type_id'];
        }

        if ($roleTypeId === 1) {
            return null;
        }

        $userId = isset($request->user_id) ? (int) $request->user_id : 0;
        $logId = isset($request->log_id) ? (int) $request->log_id : 0;

        if ($userId <= 0 || $logId <= 0) {
            try {
                $jwtUser = JWTAuth::parseToken()->authenticate();
                if ($jwtUser) {
                    if ($userId <= 0) {
                        $userId = (int) $jwtUser->id;
                    }
                    if ($logId <= 0 && !empty($jwtUser->employee_id)) {
                        $logId = (int) $jwtUser->employee_id;
                    }
                    if ($roleTypeId === null && !empty($jwtUser->role_id)) {
                        $role = DB::table('roles')->where('id', (int) $jwtUser->role_id)->first();
                        if ($role && (int) $role->role_type_id === 1) {
                            return null;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Token not available on this request
            }
        }

        return $this->resolveAllowedBranchIds($tenantId, $userId, $logId);
    }

    /**
     * Branch IDs to use for queries: explicit branch_id or scoped list.
     *
     * @return int[]
     */
    public function resolveEffectiveBranchIds(Request $request, int $tenantId): array
    {
        $scope = $this->resolveAllowedBranchIdsFromRequest($request);

        if (!empty($request->branch_id)) {
            $bid = (int) $request->branch_id;
            if ($scope !== null && !in_array($bid, $scope, true)) {
                return $scope;
            }
            return [$bid];
        }

        if ($scope !== null) {
            return $scope;
        }

        return Branch::where('tenant_id', $tenantId)->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values()->all();
    }
}
