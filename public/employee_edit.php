<?php
require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/login.php');
$u = auth_user();
if (!isset($u['role']) || $u['role'] !== 'admin') {
	redirect('employees.php?msg=' . urlencode('Admins only.'));
}

$pdo = DB::conn();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
	redirect('employees.php?msg=' . urlencode('Employee not found'));
}

// optional columns
function emp_has(PDO $pdo, string $col): bool {
	static $cols = null;
	if ($cols === null) {
		$cols = $pdo->query("SHOW COLUMNS FROM employees")->fetchAll(PDO::FETCH_COLUMN, 0);
	}
	return in_array($col, $cols, true);
}
$opt_phone = emp_has($pdo, 'phone');
$opt_is_active = emp_has($pdo, 'is_active');
$opt_hire_date = emp_has($pdo, 'hire_date');

$st = $pdo->prepare('SELECT * FROM employees WHERE employee_id = ? LIMIT 1');
$st->execute([$id]);
$emp = $st->fetch(PDO::FETCH_ASSOC);
if (!$emp) {
	redirect('employees.php?msg=' . urlencode('Employee not found'));
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Delete flow (admin only)
	if (isset($_POST['action']) && $_POST['action'] === 'delete') {
		if (empty($_POST['confirm_password'])) {
			$msg = 'Password confirmation is required to delete an employee.';
		} else {
			try {
				$d = $pdo->prepare('DELETE FROM employees WHERE employee_id = ?');
				$d->execute([$id]);
				redirect('employees.php?msg=' . urlencode('Employee deleted'));
			} catch (Throwable $e) {
				$msg = 'Error deleting employee: ' . e($e->getMessage());
			}
		}
	}

	$first = trim($_POST['first_name'] ?? '');
	$last  = trim($_POST['last_name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$role  = trim($_POST['role'] ?? '');
	$phone = $opt_phone ? trim($_POST['phone'] ?? '') : null;
	$isActive = $opt_is_active ? (isset($_POST['is_active']) ? 1 : 0) : null;
	$hireDate = $opt_hire_date ? trim($_POST['hire_date'] ?? '') : null;
	$newPass  = trim($_POST['new_password'] ?? '');

	$errors = [];
	if ($first === '') $errors[] = 'First name is required';
	if ($last === '') $errors[] = 'Last name is required';
	if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
	if (!in_array($role, ['sales','manager','admin'], true)) $errors[] = 'Role must be sales, manager, or admin';

	if (empty($errors)) {
		try {
			$fields = ['first_name' => $first, 'last_name' => $last, 'email' => $email, 'role' => $role];
			if ($opt_phone) $fields['phone'] = $phone;
			if ($opt_is_active) $fields['is_active'] = $isActive;
			if ($opt_hire_date) $fields['hire_date'] = $hireDate !== '' ? $hireDate : null;
			if ($newPass !== '') {
				$fields['password_hash'] = password_hash($newPass, PASSWORD_BCRYPT);
			}

			$sets = [];
			$vals = [];
			foreach ($fields as $col => $val) {
				$sets[] = "$col = ?";
				$vals[] = $val;
			}
			$vals[] = $id;

			$sql = 'UPDATE employees SET ' . implode(', ', $sets) . ' WHERE employee_id = ?';
			$u = $pdo->prepare($sql);
			$u->execute($vals);

			redirect('employees.php?msg=' . urlencode('Employee updated'));
		} catch (Throwable $e) {
			$msg = 'Error saving employee: ' . e($e->getMessage());
		}
	} else {
		$msg = implode(' ', $errors);
	}
}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Edit Employee</title>
	<?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body>
	<div class="layout">
		<?php include __DIR__ . '/_sidebar.php'; ?>
		<div class="content">
			<div class="header">
				<div class="title">Edit Employee #<?= (int)$id ?></div>
				<div class="right"><a href="<?= BASE_URL ?>/employees.php">Back to Employees</a></div>
			</div>

			<?php if ($msg): ?><div class="card mb-12"><?= e($msg) ?></div><?php endif; ?>

			<div class="card">
				<form method="post" class="form">
					<div class="grid-2">
						<div>
							<label>First Name</label>
							<input name="first_name" value="<?= e($_POST['first_name'] ?? $emp['first_name'] ?? '') ?>" required>
						</div>
						<div>
							<label>Last Name</label>
							<input name="last_name" value="<?= e($_POST['last_name'] ?? $emp['last_name'] ?? '') ?>" required>
						</div>
					</div>

					<label>Email</label>
					<input type="email" name="email" value="<?= e($_POST['email'] ?? $emp['email'] ?? '') ?>" required>

					<?php if ($opt_phone): ?>
						<label>Phone</label>
						<input name="phone" value="<?= e($_POST['phone'] ?? $emp['phone'] ?? '') ?>">
					<?php endif; ?>

					<label>Role</label>
					<select name="role" required>
						<?php foreach (['sales','manager','admin'] as $r): ?>
							<option value="<?= $r ?>" <?= (($emp['role'] ?? '') === $r || ($_POST['role'] ?? '') === $r) ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
						<?php endforeach; ?>
					</select>

					<?php if ($opt_is_active): ?>
						<label><input type="checkbox" name="is_active" value="1" <?= !empty($_POST) ? (isset($_POST['is_active']) ? 'checked' : '') : (!empty($emp['is_active']) ? 'checked' : '') ?>> Active</label>
					<?php endif; ?>

					<?php if ($opt_hire_date): ?>
						<label>Hire Date</label>
						<input type="date" name="hire_date" value="<?= e($_POST['hire_date'] ?? $emp['hire_date'] ?? '') ?>">
					<?php endif; ?>

					<label>New Password <span class="note">(leave blank to keep current)</span></label>
					<input type="password" name="new_password" autocomplete="new-password">

					<div class="mt-12">
						<button type="submit" class="btn btn-primary">Save</button>
						<a class="btn secondary" href="<?= BASE_URL ?>/employees.php">Cancel</a>
						<button type="button" class="btn secondary" id="deleteBtn" style="margin-left:8px;">Delete</button>
					</div>
				</form>
				<form id="deleteForm" method="post" style="display:none;">
					<input type="hidden" name="action" value="delete">
				</form>
			</div>
		</div>
	</div>
	<script>
		(function(){
			var del = document.getElementById('deleteBtn');
			if (!del) return;
			del.addEventListener('click', function(){
				var first = confirm('Delete this employee? This cannot be undone.');
				if (!first) return;
				var pwd = prompt('To confirm, enter your password:');
				if (pwd === null || pwd === '') return;
				var form = document.getElementById('deleteForm');
				if (!form) return;
				var inp = document.createElement('input');
				inp.type = 'hidden';
				inp.name = 'confirm_password';
				inp.value = pwd;
				form.appendChild(inp);
				form.submit();
			});
		})();
	</script>
</body>
</html>