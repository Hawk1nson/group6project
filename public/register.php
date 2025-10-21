<?php
// register/create account page for dealership employees
// maybe add some sort of dealership verification later (so non-employees can't just create accounts)

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';

// For development, remove in production
ini_set('display_errors', 1);
error_reporting(E_ALL);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = DB::conn();

    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = $_POST['role'] ?? 'sales';
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['confirm_password'] ?? '';

    if ($first && $last && $email && $pass1 && $pass2) {
        if ($pass1 !== $pass2) {
            $msg = 'Passwords do not match.';
        } else {
            try {
                $st = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE email = ?");
                $st->execute([$email]);
                if ($st->fetchColumn() > 0) {
                    $msg = 'Email already exists.';
                } else {
                    $hash = password_hash($pass1, PASSWORD_BCRYPT);
                    $ins = $pdo->prepare("
                        INSERT INTO employees (first_name, last_name, email, role, is_active, password_hash)
                        VALUES (?, ?, ?, ?, 1, ?)
                    ");
                    $ins->execute([$first, $last, $email, $role, $hash]);
                    header("Location: login.php?registered=1");
                    exit;
                }
            } catch (Throwable $e) {
                $msg = "Error: " . e($e->getMessage());
            }
        }
    } else {
        $msg = 'Please fill in all fields.';
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Create Account - CDMS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="center">
        <div class="card login">
            <h2 style="margin:0 0 8px">Create Account</h2>
            <p class="note">Dealership Employee Registration</p>

            <?php if ($msg): ?><p style="color:#b91c1c;"><?= e($msg) ?></p><?php endif; ?>

            <form method="post">
                <label>First Name</label>
                <input name="first_name" required>

                <label>Last Name</label>
                <input name="last_name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>

                <label>Role</label>
                <select name="role">
                    <option value="sales">Sales</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>

                <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
                    <button type="submit">Create Account</button>
                    <a href="login.php" class="btn secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>