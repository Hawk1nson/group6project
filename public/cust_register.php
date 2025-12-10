<?php
// Customer registration page
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/bootstrap.php';

// For development, remove in production
ini_set('display_errors', 1);
error_reporting(E_ALL);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$pdo = DB::conn();

	$first = trim($_POST['first_name'] ?? '');
	$last  = trim($_POST['last_name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	$addr1 = trim($_POST['address_line1'] ?? '');
	$addr2 = trim($_POST['address_line2'] ?? '');
	$city  = trim($_POST['city'] ?? '');
	$state = trim($_POST['state_province'] ?? '');
	$postal= trim($_POST['postal_code'] ?? '');
	$pass1 = $_POST['password'] ?? '';
	$pass2 = $_POST['confirm_password'] ?? '';

	if ($first && $last && $email && $phone && $pass1 && $pass2) {
		if ($pass1 !== $pass2) {
			$msg = 'Passwords do not match.';
		} else {
			try {
				$st = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
				$st->execute([$email]);
				if ($st->fetchColumn() > 0) {
					$msg = 'Email already exists.';
				} else {
					$hash = password_hash($pass1, PASSWORD_BCRYPT);
					$ins = $pdo->prepare("INSERT INTO customers (first_name, last_name, email, phone, address_line1, address_line2, city, state_province, postal_code, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
					$ins->execute([$first, $last, $email, $phone, $addr1, $addr2, $city, $state, $postal, $hash]);
					header("Location: public_login.php?registered=1");
					exit;
				}
			} catch (Throwable $e) {
				$msg = "Error: " . e($e->getMessage());
			}
		}
	} else {
		$msg = 'Please fill in all required fields.';
	}
}
?>

<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Create Customer Account - CDMS</title>
	<?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body>
	<div class="center">
		<div class="login-logo" style="text-align:center;margin-bottom:12px;">
			<img src="<?= BASE_URL ?>/../images/system/NLAuto_logo.png" alt="NL Auto" style="max-width:260px;width:100%;height:auto;display:inline-block;">
		</div>
		<div class="card login">
			<h2 class="h2-tight">Create Account</h2>
			<p class="note">Customer Registration</p>

			<?php if ($msg): ?><p class="alert-error"><?= e($msg) ?></p><?php endif; ?>

			<form method="post" autocomplete="off">
				<label>First Name*</label>
				<input name="first_name" value="<?= e($_POST['first_name'] ?? '') ?>" required>

				<label>Last Name*</label>
				<input name="last_name" value="<?= e($_POST['last_name'] ?? '') ?>" required>

				<label>Email*</label>
				<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>

				<label>Phone*</label>
				<input name="phone" value="<?= e($_POST['phone'] ?? '') ?>" required>

				<label>Address Line 1</label>
				<input name="address_line1" value="<?= e($_POST['address_line1'] ?? '') ?>">

				<label>Address Line 2</label>
				<input name="address_line2" value="<?= e($_POST['address_line2'] ?? '') ?>">

				<label>City</label>
				<input name="city" value="<?= e($_POST['city'] ?? '') ?>">

				<label>State / Province</label>
				<input name="state_province" value="<?= e($_POST['state_province'] ?? '') ?>">

				<label>Postal Code</label>
				<input name="postal_code" value="<?= e($_POST['postal_code'] ?? '') ?>">

				<label>Password*</label>
				<input type="password" name="password" required>

				<label>Confirm Password*</label>
				<input type="password" name="confirm_password" required>

				<div class="flex-between mt-12">
					<button type="submit">Create Account</button>
					<a href="public_login.php" class="btn secondary">Cancel</a>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
