<?php
/**
 * Database settings for customer account deletion page.
 * Reads backend/.env when available; edit the values below if needed.
 */

function customer_account_load_env($envPath)
{
    $env = [];
    if (!is_readable($envPath)) {
        return $env;
    }
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
    return $env;
}

$env = customer_account_load_env(dirname(__DIR__) . '/.env');

define('CA_DB_HOST', $env['DB_HOST_1'] ?? $env['DB_HOST'] ?? 'localhost');
define('CA_DB_PORT', $env['DB_PORT_1'] ?? $env['DB_PORT'] ?? '3306');
define('CA_ADMIN_DB', $env['DB_DATABASE_1'] ?? 'admin_chits');
define('CA_DB_USER', $env['DB_USERNAME_1'] ?? $env['DB_USERNAME'] ?? 'root');
define('CA_DB_PASS', $env['DB_PASSWORD_1'] ?? $env['DB_PASSWORD'] ?? '');
define('CA_DEFAULT_TENANT_DB', $env['DB_DATABASE'] ?? 'spn_chits');
define('CA_APP_NAME', 'Sri SPN Chits');
define('CA_SUPPORT_EMAIL', 'support@srispnchitfunds.com');
define('CA_SUPPORT_PHONE', '');

function ca_pdo($database)
{
    $dsn = 'mysql:host=' . CA_DB_HOST . ';port=' . CA_DB_PORT . ';dbname=' . $database . ';charset=utf8mb4';
    return new PDO($dsn, CA_DB_USER, CA_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function ca_start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function ca_csrf_token()
{
    ca_start_session();
    if (empty($_SESSION['ca_csrf'])) {
        $_SESSION['ca_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['ca_csrf'];
}

function ca_verify_csrf($token)
{
    ca_start_session();
    return isset($_SESSION['ca_csrf']) && hash_equals($_SESSION['ca_csrf'], (string) $token);
}

function ca_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function ca_get_active_tenants(PDO $admin)
{
    $stmt = $admin->query('SELECT id, slag_url, chit_fund_name FROM tenants WHERE status = 1 ORDER BY id ASC');
    $rows = $stmt->fetchAll();
    if ($rows) {
        return $rows;
    }
    return [[
        'id' => 0,
        'slag_url' => CA_DEFAULT_TENANT_DB,
        'chit_fund_name' => CA_APP_NAME,
    ]];
}

function ca_find_customer(PDO $tenantDb, $username)
{
    $sql = 'SELECT id, name, customer_code, mobile_no, password, user_enabled, tenant_id, branch_id
            FROM customers
            WHERE deleted_at IS NULL
              AND (customer_code = :u1 OR mobile_no = :u2)
            LIMIT 1';
    $stmt = $tenantDb->prepare($sql);
    $stmt->execute([':u1' => $username, ':u2' => $username]);
    return $stmt->fetch() ?: null;
}

function ca_disable_customer_account(PDO $tenantDb, array $customer)
{
    $disabledPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
    $remark = 'Customer requested account deletion via web on ' . date('Y-m-d H:i:s');

    $stmt = $tenantDb->prepare(
        'UPDATE customers
         SET user_enabled = 0,
             password = :password,
             deletion_remark = :remark,
             updated_at = NOW()
         WHERE id = :id AND deleted_at IS NULL'
    );
    $stmt->execute([
        ':password' => $disabledPassword,
        ':remark' => $remark,
        ':id' => $customer['id'],
    ]);

    // Disable linked mobile login user record when present.
    $userStmt = $tenantDb->prepare(
        "UPDATE users
         SET status = 0,
             email = CONCAT(email, '.deleted.', UNIX_TIMESTAMP()),
             updated_at = NOW()
         WHERE employee_id = :customer_id
           AND user_type = '5'
           AND deleted_at IS NULL"
    );
    $userStmt->execute([':customer_id' => $customer['id']]);

    return $stmt->rowCount() > 0;
}
