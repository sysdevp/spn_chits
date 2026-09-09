<?php
require __DIR__ . '/config.php';

ca_start_session();

if (empty($_SESSION['ca_deleted'])) {
    header('Location: index.php');
    exit;
}

unset($_SESSION['ca_deleted']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Deleted | <?= ca_escape(CA_APP_NAME) ?></title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f4f7f5;
      color: #1f2937;
    }
    .wrap {
      max-width: 520px;
      margin: 60px auto;
      padding: 0 16px;
    }
    .card {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0,0,0,.08);
      padding: 28px;
      text-align: center;
    }
    .ok {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: #dcfce7;
      color: #166534;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 34px;
      margin-bottom: 16px;
    }
    a {
      color: #025e2d;
      text-decoration: none;
      font-weight: 700;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="ok">&#10003;</div>
      <h1>Account Deleted</h1>
      <p>Your customer account has been disabled successfully. You can no longer sign in to the mobile app with your old username and password.</p>
      <p><a href="index.php">Back to home</a></p>
    </div>
  </div>
</body>
</html>
