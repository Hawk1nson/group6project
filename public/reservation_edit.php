<?php
require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/login.php');
$u = auth_user();
$is_admin = isset($u['role']) && $u['role'] === 'admin';

$pdo = DB::conn();
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM reservations WHERE reservation_id = ? LIMIT 1');
$st->execute([$id]);
$res = $st->fetch(PDO::FETCH_ASSOC);
if (!$res) {
    echo 'Reservation not found';
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete flow (admin only)
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!$is_admin) {
            $msg = 'You do not have permission to delete reservations.';
        } elseif (empty($_POST['confirm_password'])) {
            $msg = 'Password confirmation is required to delete reservations.';
        } else {
            try {
                $d = $pdo->prepare('DELETE FROM reservations WHERE reservation_id = ?');
                $d->execute([$id]);
                redirect('reservations.php?msg=' . urlencode('Reservation deleted'));
            } catch (Throwable $e) {
                $msg = 'Error deleting reservation: ' . e($e->getMessage());
            }
        }
    }

    // Update flow
    $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $employee_id = (int)($_POST['employee_id'] ?? $res['created_by_employee_id'] ?? 0);
    $type = trim($_POST['type'] ?? '');
    $start_dt = trim($_POST['start_datetime'] ?? '');
    $end_dt = trim($_POST['end_datetime'] ?? '');
    $status = trim($_POST['status'] ?? $res['status'] ?? 'pending');
    $notes = trim($_POST['notes'] ?? '');

    $errors = [];
    if ($vehicle_id <= 0) $errors[] = 'Please select a vehicle.';
    if ($customer_id <= 0) $errors[] = 'Please select a customer.';
    if ($start_dt === '') $errors[] = 'Please provide a start date/time.';
    if ($end_dt === '') $errors[] = 'Please provide an end date/time.';
    if (!in_array($type, ['test_drive','hold','other'], true)) {
        // allow 'other' as a permissive fallback
        $type = $type ?: $res['type'] ?? 'test_drive';
    }

    if (empty($errors)) {
        try {
            $u = $pdo->prepare('UPDATE reservations SET vehicle_id = ?, customer_id = ?, created_by_employee_id = ?, type = ?, start_datetime = ?, end_datetime = ?, status = ?, notes = ? WHERE reservation_id = ?');
            $u->execute([$vehicle_id, $customer_id, $employee_id, $type, $start_dt, $end_dt, $status, $notes !== '' ? $notes : null, $id]);
            redirect('reservations.php?msg=' . urlencode('Reservation saved'));
        } catch (Throwable $e) {
            $msg = 'Error saving reservation: ' . e($e->getMessage());
        }
    } else {
        $msg = implode(' ', $errors);
    }
}

// Dropdowns
$employees = $pdo->query("SELECT employee_id, first_name, last_name FROM employees WHERE is_active = 1 ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
$customers = $pdo->query("SELECT customer_id, first_name, last_name FROM customers ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
$vehicles  = $pdo->query("SELECT vehicle_id, make, model, model_year, vin FROM vehicles ORDER BY model_year DESC, make, model")->fetchAll(PDO::FETCH_ASSOC);
$statusOptions = $pdo->query("SELECT DISTINCT status FROM reservations ORDER BY status")->fetchAll(PDO::FETCH_COLUMN);

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Edit Reservation</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body class="page-reservations">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Edit Reservation #<?= (int)$id ?></div>
                <div class="right"><a href="<?= BASE_URL ?>/reservations.php">Back to Reservations</a></div>
            </div>

            <?php if ($msg): ?><div class="card mb-12"><?= e($msg) ?></div><?php endif; ?>

            <div class="card">
                <form method="post" class="form">
                    <div class="grid-3">
                        <div>
                            <label>Employee</label>
                            <select name="employee_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= (int)$emp['employee_id'] ?>" <?= ((int)($res['created_by_employee_id'] ?? 0) === (int)$emp['employee_id']) ? 'selected' : '' ?>><?= e($emp['last_name'] . ', ' . $emp['first_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Customer</label>
                            <select name="customer_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= (int)$c['customer_id'] ?>" <?= ((int)($res['customer_id'] ?? 0) === (int)$c['customer_id']) ? 'selected' : '' ?>><?= e($c['last_name'] . ', ' . $c['first_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Vehicle</label>
                            <select name="vehicle_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?= (int)$v['vehicle_id'] ?>" <?= ((int)($res['vehicle_id'] ?? 0) === (int)$v['vehicle_id']) ? 'selected' : '' ?>><?= e(trim(($v['model_year'] ? $v['model_year'] . ' ' : '') . $v['make'] . ' ' . $v['model'] . ' (VIN: ' . $v['vin'] . ')')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label>Start (local)</label>
                            <input type="datetime-local" name="start_datetime" value="<?= e(date('Y-m-d\TH:i', strtotime($res['start_datetime'] ?? ''))) ?>">
                        </div>
                        <div>
                            <label>End (local)</label>
                            <input type="datetime-local" name="end_datetime" value="<?= e(date('Y-m-d\TH:i', strtotime($res['end_datetime'] ?? ''))) ?>">
                        </div>
                    </div>

                    <div class="grid-3">
                        <div>
                            <label>Type</label>
                            <select name="type">
                                <option value="test_drive" <?= ($res['type'] === 'test_drive') ? 'selected' : '' ?>>Test drive</option>
                                <option value="hold" <?= ($res['type'] === 'hold') ? 'selected' : '' ?>>Hold</option>
                                <option value="other" <?= ($res['type'] !== 'test_drive' && $res['type'] !== 'hold') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <label>Status</label>
                            <select name="status">
                                <?php foreach ($statusOptions as $s): ?>
                                    <option value="<?= e($s) ?>" <?= ($res['status'] === $s) ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div></div>
                    </div>

                    <label>Notes</label>
                    <textarea name="notes" rows="4"><?= e($_POST['notes'] ?? $res['notes'] ?? '') ?></textarea>

                    <div class="mt-12">
                        <button>Save</button>
                        <a class="btn secondary" href="<?= BASE_URL ?>/reservations.php">Cancel</a>
                        <?php if ($is_admin): ?>
                            <button type="button" id="deleteBtn" class="btn secondary" style="margin-left:8px;">Delete</button>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($is_admin): ?>
                    <form id="deleteForm" method="post" style="display:none;">
                        <input type="hidden" name="action" value="delete">
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        (function(){
            var del = document.getElementById('deleteBtn');
            if (!del) return;
            del.addEventListener('click', function(e){
                var first = confirm('Delete this reservation from inventory? This cannot be undone.');
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
