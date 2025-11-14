<?php
// login page for dealership users

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';

require_once __DIR__ . '/bootstrap.php';

// remove for production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (auth_login($email, $pass)) {
        redirect('dashboard.php');
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
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body>
    <div class="center">
        <div class="login-logo" style="text-align:center;margin-bottom:12px;">
            <img src="<?= BASE_URL ?>/../images/system/NLAuto_logo.png" alt="NL Auto" style="max-width:260px;width:100%;height:auto;display:inline-block;">
        </div>
        <div class="card login">
            <h2 class="h2-tight">CDMS — Dealership Login</h2>

            <?php if ($notice): ?>
                <p class="alert alert-success">
                    <?= e($notice) ?>
                </p>
            <?php endif; ?>

            <p class="note">Phase 1: internal dealership portal</p>
            <?php if ($err): ?><p class="alert-error"><?= e($err) ?></p><?php endif; ?>

            <?php if ($registered): ?>
                <p class="alert alert-success">
                    Account created successfully. Please log in.
                </p>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <!-- Login and create user buttons -->
                <div class="flex-between mt-12">
                    <button type="submit">Login</button>
                    <a href="register.php" class="btn secondary">Create Account</a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>