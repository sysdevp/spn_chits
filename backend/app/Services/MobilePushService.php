<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class MobilePushService
{
    public static function ensureTable()
    {
        if (!Schema::hasTable('employee_push_tokens')) {
            Schema::create('employee_push_tokens', function ($table) {
                $table->increments('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('user_id')->default(0);
                $table->integer('employee_id')->default(0);
                $table->string('push_token', 512);
                $table->string('platform', 20)->default('android');
                $table->timestamps();
                $table->unique(['user_id', 'push_token']);
            });
        }
    }

    public static function registerToken($tenantId, $userId, $employeeId, $token, $platform = 'android')
    {
        self::ensureTable();
        if (!$token) {
            return false;
        }

        DB::table('employee_push_tokens')->updateOrInsert(
            array('user_id' => (int) $userId, 'push_token' => $token),
            array(
                'tenant_id' => (int) $tenantId,
                'employee_id' => (int) $employeeId,
                'platform' => $platform ?: 'android',
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            )
        );
        return true;
    }

    public static function tokensForEmployee($employeeId)
    {
        self::ensureTable();
        if (!$employeeId) {
            return array();
        }
        return DB::table('employee_push_tokens')
            ->where('employee_id', (int) $employeeId)
            ->pluck('push_token')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function tokensForUser($userId)
    {
        self::ensureTable();
        if (!$userId) {
            return array();
        }
        return DB::table('employee_push_tokens')
            ->where('user_id', (int) $userId)
            ->pluck('push_token')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function tokensForAdmins($tenantId, $excludeUserId = 0)
    {
        self::ensureTable();
        $query = User::with('role_det')->where('tenant_id', (int) $tenantId);
        if ($excludeUserId) {
            $query->where('id', '!=', (int) $excludeUserId);
        }
        $users = $query->get();
        $userIds = array();
        foreach ($users as $user) {
            $isAdmin = false;
            if ((int) $user->id === 1) {
                $isAdmin = true;
            }
            if (empty($user->employee_id) || (int) $user->employee_id === 0) {
                $isAdmin = true;
            }
            $role = $user->role_det;
            if ($role) {
                $name = strtolower((string) ($role->role_name ?? ''));
                $type = strtolower((string) ($role->role_type ?? ''));
                if (strpos($name, 'admin') !== false || strpos($type, 'admin') !== false
                    || strpos($name, 'md') !== false || strpos($type, 'md') !== false
                    || strpos($name, 'manager') !== false) {
                    $isAdmin = true;
                }
            }
            if ($isAdmin) {
                $userIds[] = (int) $user->id;
            }
        }
        $userIds = array_values(array_unique($userIds));
        if (empty($userIds)) {
            return array();
        }

        return DB::table('employee_push_tokens')
            ->whereIn('user_id', $userIds)
            ->pluck('push_token')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function notifyEmployee($employeeId, $title, $body, $data = array())
    {
        $tokens = self::tokensForEmployee($employeeId);
        return self::sendPush($tokens, $title, $body, $data);
    }

    public static function notifyUser($userId, $title, $body, $data = array())
    {
        $tokens = self::tokensForUser($userId);
        return self::sendPush($tokens, $title, $body, $data);
    }

    public static function notifyAdmins($tenantId, $title, $body, $data = array(), $excludeUserId = 0)
    {
        $tokens = self::tokensForAdmins($tenantId, $excludeUserId);
        return self::sendPush($tokens, $title, $body, $data);
    }

    private static function sendPush(array $tokens, $title, $body, $data = array())
    {
        if (empty($tokens)) {
            return 0;
        }
        $sent = 0;
        $expo = array();
        $fcm = array();
        foreach ($tokens as $token) {
            if (strpos((string) $token, 'ExponentPushToken') === 0 || strpos((string) $token, 'ExpoPushToken') === 0) {
                $expo[] = $token;
            } else {
                $fcm[] = $token;
            }
        }
        if (!empty($expo)) {
            $sent += self::sendExpo($expo, $title, $body, $data);
        }
        if (!empty($fcm)) {
            $sent += self::sendFcm($fcm, $title, $body, $data);
        }
        return $sent;
    }

    private static function sendExpo(array $tokens, $title, $body, $data = array())
    {
        $messages = array();
        foreach ($tokens as $token) {
            $messages[] = array(
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => array_merge(array('title' => $title, 'body' => $body), $data),
                'priority' => 'high',
            );
        }
        try {
            $response = Http::withHeaders(array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ))->post('https://exp.host/--/api/v2/push/send', $messages);
            return $response->successful() ? count($tokens) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private static function sendFcm(array $tokens, $title, $body, $data = array())
    {
        $key = env('FCM_SERVER_KEY', '');
        if (!$key || empty($tokens)) {
            return 0;
        }

        $sent = 0;
        foreach ($tokens as $token) {
            try {
                $payload = array(
                    'to' => $token,
                    'notification' => array(
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                    ),
                    'data' => array_merge(array('title' => $title, 'body' => $body), $data),
                    'priority' => 'high',
                );
                $response = Http::withHeaders(array(
                    'Authorization' => 'key=' . $key,
                    'Content-Type' => 'application/json',
                ))->post('https://fcm.googleapis.com/fcm/send', $payload);

                if ($response->successful()) {
                    $sent++;
                }
            } catch (\Exception $e) {
                // ignore single token failures
            }
        }
        return $sent;
    }
}
