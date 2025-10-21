<?php
// login page for dealership users

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';

// remove for production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (auth_login($email, $pass)) {
        redirect('dealership/dashboard.php');
    }
    $err = 'Invalid login or inactive account';
}

$registered = isset($_GET['registered']);

$notice = '';
if (isset($_GET['logout'])) {
    $notice = 'User has successfully logged out';
}
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDMS - Dealership login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="center">
        <div class="card login">
            <h2 style="margin:0 0 8px">CDMS — Dealership Login</h2>

            <?php if ($notice): ?>
                <p style="margin:8px 0; padding:8px 10px; border:1px solid #a7f3d0; background:#ecfdf5; color:#065f46; border-radius:8px;">
                    <?= e($notice) ?>
                </p>
            <?php endif; ?>

            <p class="note">Phase 1: internal dealership portal</p>
            <?php if ($err): ?><p style="color:#b91c1c; margin:8px 0;"><?= e($err) ?></p><?php endif; ?>

            <?php if ($registered): ?>
                <p style="color:#065f46; background:#d1fae5; padding:8px; border-radius:8px;">
                    Account created successfully. Please log in.
                </p>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <!-- Login and create user buttons -->
                <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
                    <button type="submit">Login</button>
                    <a href="register.php" class="btn secondary">Create Account</a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>