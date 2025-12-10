<?php
// Customer login / auth utilities. Can be included (embed) or visited directly.
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!function_exists('customer_login')) {
	function customer_login(string $email, string $password): bool
	{
		$pdo = DB::conn();
		try {
			$st = $pdo->prepare('SELECT customer_id, first_name, last_name, email, password_hash FROM customers WHERE email=? LIMIT 1');
			$st->execute([$email]);
			$u = $st->fetch(PDO::FETCH_ASSOC);
			if (!$u) return false;

			$stored = (string)($u['password_hash'] ?? '');
			$looksHashed = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon') || str_starts_with($stored, '$pbkdf');
			$ok = $looksHashed ? password_verify($password, $stored) : hash_equals($stored, $password);
			if (!$ok) return false;

			$_SESSION['customer'] = [
				'id'    => (int)$u['customer_id'],
				'name'  => trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')),
				'email' => $u['email'],
			];
			return true;
		} catch (Throwable $e) {
			return false;
		}
	}
}

if (!function_exists('customer_user')) {
	function customer_user(): ?array
	{
		return $_SESSION['customer'] ?? null;
	}
}

if (!function_exists('customer_logout')) {
	function customer_logout(): void
	{
		unset($_SESSION['customer']);
	}
}

// If this file is included for embedding (e.g., from index.php), do not render full page.
if (defined('CUSTOMER_AUTH_EMBED') && constant('CUSTOMER_AUTH_EMBED')) {
	return;
}

// Standalone login page handling
$err = '';
if (isset($_GET['logout_customer'])) {
	customer_logout();
	redirect('public_login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'customer_login') {
	$email = trim($_POST['email'] ?? '');
	$pass  = $_POST['password'] ?? '';
	if (!customer_login($email, $pass)) {
		$err = 'Invalid email or password.';
	} else {
		redirect('index.php');
	}
}

$customer = customer_user();
// success notice after registration
$registered = isset($_GET['registered']);
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Customer Login — CDMS</title>
	<?php include __DIR__ . '/../includes/header.php'; ?>
	<style>
		.login-card label { font-weight:600; margin-top:8px; display:block; }
		.login-card input { width:100%; }
		.alert-error { color:#f87171; margin:8px 0; }
	</style>
</head>
<body>
	<div class="center">
		<div class="login-logo" style="text-align:center;margin-bottom:12px;">
			<img src="<?= BASE_URL ?>/../images/system/NLAuto_logo.png" alt="NL Auto" style="max-width:260px;width:100%;height:auto;display:inline-block;">
		</div>
		<div class="card login login-card">
			<h2 class="h2-tight">Customer Login</h2>
			<?php if ($customer): ?>
				<p class="note">You are logged in as <?= e($customer['name'] ?: $customer['email']) ?>.</p>
				<div class="flex-row-wrap" style="gap:8px;">
					<a class="btn" href="index.php">Back to Shop</a>
					<a class="btn secondary" href="public_login.php?logout_customer=1">Logout</a>
				</div>
			<?php else: ?>
				<p class="note">Hey there, ready to drive? Sign in.</p>
				<?php if ($registered): ?>
					<p class="alert alert-success">Account created successfully. Please log in.</p>
				<?php endif; ?>
				<?php if ($err): ?><div class="alert-error"><?= e($err) ?></div><?php endif; ?>
				<form method="post" autocomplete="off">
					<input type="hidden" name="action" value="customer_login">
					<label for="cust_email">Email</label>
					<input id="cust_email" type="email" name="email" required>
					<label for="cust_pass">Password</label>
					<input id="cust_pass" type="password" name="password" required>
					<div class="flex-between mt-12">
						<button class="btn btn-primary" type="submit">Login</button>
						<a class="btn secondary" href="cust_register.php">Create Account</a>
					</div>
					<div class="mt-10">
						<a class="btn" href="index.php">Back to Shop</a>
					</div>
				</form>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>
