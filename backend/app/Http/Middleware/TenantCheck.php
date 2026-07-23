<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class TenantCheck
{
    public function handle($request, Closure $next)
    {
        try {
            if ($request->bearerToken()) {
                $token = JWTAuth::parseToken();
                $payload = $token->getPayload();
                $tenantDb = $payload->get('tenant_db');

                if ($tenantDb) {
                    Config::set('database.connections.mysql.database', $tenantDb);
                    DB::purge('mysql');
                    DB::reconnect('mysql');
                }
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'TenantCheck Error: ' . $e->getMessage(),
            ], 401);
        }
    }
}
