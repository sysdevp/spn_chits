<?php
require __DIR__ . '/config.php';

ca_start_session();

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirm = isset($_POST['confirm_delete']);
    $csrf = $_POST['csrf'] ?? '';

    if (!ca_verify_csrf($csrf)) {
        $message = 'Session expired. Please refresh the page and try again.';
        $messageType = 'error';
    } elseif ($username === '' || $password === '') {
        $message = 'Please enter your username and password.';
        $messageType = 'error';
    } elseif (!$confirm) {
        $message = 'Please tick the confirmation box to delete your account.';
        $messageType = 'error';
    } else {
        try {
            $admin = ca_pdo(CA_ADMIN_DB);
            $tenants = ca_get_active_tenants($admin);
            $matchedCustomer = null;
            $matchedTenant = null;
            $tenantDb = null;

            foreach ($tenants as $tenant) {
                $dbName = $tenant['slag_url'] ?: CA_DEFAULT_TENANT_DB;
                try {
                    $pdo = ca_pdo($dbName);
                    $customer = ca_find_customer($pdo, $username);
                    if ($customer && password_verify($password, (string) $customer['password'])) {
                        $matchedCustomer = $customer;
                        $matchedTenant = $tenant;
                        $tenantDb = $pdo;
                        break;
                    }
                } catch (Throwable $inner) {
                    continue;
                }
            }

            if (!$matchedCustomer || !$tenantDb) {
                $message = 'Invalid username or password.';
                $messageType = 'error';
            } elseif ((int) $matchedCustomer['user_enabled'] === 0) {
                $message = 'This account is already disabled.';
                $messageType = 'error';
            } else {
                ca_disable_customer_account($tenantDb, $matchedCustomer);
                $_SESSION['ca_deleted'] = true;
                header('Location: success.php');
                exit;
            }
        } catch (Throwable $e) {
            $message = 'Unable to process your request right now. Please contact support.';
            $messageType = 'error';
        }
    }
}

$csrf = ca_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delete Customer Account | <?= ca_escape(CA_APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f4f7f5;
      color: #1f2937;
      line-height: 1.5;
    }
    .wrap {
      max-width: 520px;
      margin: 40px auto;
      padding: 0 16px;
    }
    .card {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0,0,0,.08);
      padding: 28px;
    }
    .brand {
      color: #025e2d;
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 6px;
    }
    h1 {
      font-size: 24px;
      margin: 0 0 8px;
    }
    p.lead {
      color: #6b7280;
      margin-top: 0;
    }
    label {
      display: block;
      font-weight: 600;
      margin: 16px 0 6px;
    }
    input[type=text], input[type=password] {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      font-size: 16px;
    }
    .check {
      display: flex;
      gap: 10px;
      align-items: flex-start;
      margin: 18px 0;
      background: #fff7ed;
      border: 1px solid #fed7aa;
      border-radius: 10px;
      padding: 12px;
    }
    .check input { margin-top: 4px; }
    .btn {
      width: 100%;
      border: 0;
      border-radius: 10px;
      padding: 14px;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
    }
    .btn-danger { background: #dc2626; color: #fff; }
    .btn-danger:hover { background: #b91c1c; }
    .alert {
      border-radius: 10px;
      padding: 12px 14px;
      margin-bottom: 16px;
    }
    .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .note {
      margin-top: 18px;
      font-size: 14px;
      color: #6b7280;
    }
    .note ul { padding-left: 18px; }
    .footer {
      text-align: center;
      margin-top: 18px;
      color: #6b7280;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="brand"><?= ca_escape(CA_APP_NAME) ?></div>
      <h1>Delete Account</h1>
      <p class="lead">Use your mobile app login details to permanently disable your customer account.</p>

      <?php if ($message !== ''): ?>
        <div class="alert <?= ca_escape($messageType) ?>"><?= ca_escape($message) ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <input type="hidden" name="csrf" value="<?= ca_escape($csrf) ?>">

        <label for="username">Username (Customer Code or Mobile Number)</label>
        <input type="text" id="username" name="username" required autocomplete="username" value="<?= ca_escape($_POST['username'] ?? '') ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <div class="check">
          <input type="checkbox" id="confirm_delete" name="confirm_delete" value="1" required>
          <label for="confirm_delete">I understand this will disable my mobile app login and remove access to my account. This action cannot be undone online.</label>
        </div>

        <button type="submit" class="btn btn-danger">Confirm Delete Account</button>
      </form>

      <div class="note">
        <strong>What happens when you delete:</strong>
        <ul>
          <li>Your mobile app login will be disabled immediately.</li>
          <li>You will not be able to sign in again with the same credentials.</li>
          <li>Your chit/subscriber records with <?= ca_escape(CA_APP_NAME) ?> are kept as per company policy and law.</li>
          <li>For help, contact <?= ca_escape(CA_SUPPORT_EMAIL) ?><?= CA_SUPPORT_PHONE ? ' or ' . ca_escape(CA_SUPPORT_PHONE) : '' ?>.</li>
        </ul>
      </div>
    </div>
    <div class="footer">&copy; <?= date('Y') ?> <?= ca_escape(CA_APP_NAME) ?></div>
  </div>
</body>
</html>
