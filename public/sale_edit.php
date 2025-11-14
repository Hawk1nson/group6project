<?php
require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/login.php');
$u = auth_user();
$is_manager_or_admin = isset($u['role']) && in_array($u['role'], ['admin','manager'], true);

$pdo = DB::conn();
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM sales WHERE sale_id = ? LIMIT 1');
$st->execute([$id]);
$sale = $st->fetch(PDO::FETCH_ASSOC);
if (!$sale) {
    echo 'Sale not found';
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // delete (admin/manager only)
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!$is_manager_or_admin) {
            $msg = 'You do not have permission to delete sales.';
        } else {
            try {
                $d = $pdo->prepare('DELETE FROM sales WHERE sale_id = ?');
                $d->execute([$id]);
                redirect('sales.php?msg=' . urlencode('Sale deleted'));
            } catch (Throwable $e) {
                $msg = 'Error deleting sale: ' . e($e->getMessage());
            }
        }
    }

    // update
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $vehicle_id  = (int)($_POST['vehicle_id'] ?? 0);
    $employee_id = (int)($_POST['employee_id'] ?? 0);
    $sale_date   = trim($_POST['sale_date'] ?? '');
    $sale_price  = trim($_POST['sale_price'] ?? '');
    $notes       = trim($_POST['notes'] ?? '');
    $payment_method = trim(strtolower($_POST['payment_method'] ?? ''));
    $allowed_methods = ['cash','finance','lease','other'];
    if ($payment_method === '' || !in_array($payment_method, $allowed_methods, true)) {
        $payment_method = 'finance';
    }

    $errors = [];
    if ($customer_id <= 0) $errors[] = 'Please select a customer.';
    if ($vehicle_id <= 0) $errors[] = 'Please select a vehicle.';
    if ($sale_date === '') $errors[] = 'Please provide a sale date.';
    if ($sale_price === '') $errors[] = 'Please provide a sale price.';

    if (empty($errors)) {
        try {
            // Check schema for optional columns (notes, payment_method)
            $cols = $pdo->query("SHOW COLUMNS FROM sales")->fetchAll(PDO::FETCH_COLUMN);
            $hasNotes = in_array('notes', $cols, true);
            $hasPayment = in_array('payment_method', $cols, true);

            // Build update variants depending on available columns
            if ($hasNotes && $hasPayment) {
                $u = $pdo->prepare('UPDATE sales SET customer_id = ?, vehicle_id = ?, employee_id = ?, sale_date = ?, sale_price = ?, notes = ?, payment_method = ? WHERE sale_id = ?');
                $u->execute([$customer_id, $vehicle_id, $employee_id, $sale_date, $sale_price, $notes !== '' ? $notes : null, $payment_method, $id]);
            } elseif ($hasNotes) {
                $u = $pdo->prepare('UPDATE sales SET customer_id = ?, vehicle_id = ?, employee_id = ?, sale_date = ?, sale_price = ?, notes = ? WHERE sale_id = ?');
                $u->execute([$customer_id, $vehicle_id, $employee_id, $sale_date, $sale_price, $notes !== '' ? $notes : null, $id]);
            } elseif ($hasPayment) {
                $u = $pdo->prepare('UPDATE sales SET customer_id = ?, vehicle_id = ?, employee_id = ?, sale_date = ?, sale_price = ?, payment_method = ? WHERE sale_id = ?');
                $u->execute([$customer_id, $vehicle_id, $employee_id, $sale_date, $sale_price, $payment_method, $id]);
            } else {
                $u = $pdo->prepare('UPDATE sales SET customer_id = ?, vehicle_id = ?, employee_id = ?, sale_date = ?, sale_price = ? WHERE sale_id = ?');
                $u->execute([$customer_id, $vehicle_id, $employee_id, $sale_date, $sale_price, $id]);
            }
            redirect('sales.php?msg=' . urlencode('Sale saved'));
        } catch (Throwable $e) {
            $msg = 'Error saving sale: ' . e($e->getMessage());
        }
    } else {
        $msg = implode(' ', $errors);
    }
}

// dropdown data
$customers = $pdo->query('SELECT customer_id, first_name, last_name FROM customers ORDER BY last_name, first_name')->fetchAll(PDO::FETCH_ASSOC);
$vehicles  = $pdo->query('SELECT vehicle_id, make, model, model_year, vin FROM vehicles ORDER BY model_year DESC, make, model')->fetchAll(PDO::FETCH_ASSOC);
$employees = $pdo->query('SELECT employee_id, first_name, last_name FROM employees WHERE is_active = 1 ORDER BY last_name, first_name')->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Edit Sale</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body class="page-sales">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Edit Sale #<?= (int)$id ?></div>
                <div class="right"><a href="<?= BASE_URL ?>/sales.php">Back to Sales</a></div>
            </div>

            <?php if ($msg): ?><div class="card mb-12"><?= e($msg) ?></div><?php endif; ?>

            <div class="card">
                <form method="post" class="form">
                    <div class="grid-3">
                        <div>
                            <label>Customer</label>
                            <select name="customer_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= (int)$c['customer_id'] ?>" <?= ((int)($sale['customer_id'] ?? 0) === (int)$c['customer_id']) ? 'selected' : '' ?>><?= e($c['last_name'] . ', ' . $c['first_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Vehicle</label>
                            <select name="vehicle_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?= (int)$v['vehicle_id'] ?>" <?= ((int)($sale['vehicle_id'] ?? 0) === (int)$v['vehicle_id']) ? 'selected' : '' ?>><?= e(trim(($v['model_year'] ? $v['model_year'] . ' ' : '') . $v['make'] . ' ' . $v['model'] . ' (VIN: ' . $v['vin'] . ')')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Salesperson</label>
                            <select name="employee_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= (int)$emp['employee_id'] ?>" <?= ((int)($sale['employee_id'] ?? 0) === (int)$emp['employee_id']) ? 'selected' : '' ?>><?= e($emp['last_name'] . ', ' . $emp['first_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label>Sale date</label>
                            <input type="datetime-local" name="sale_date" value="<?= e(date('Y-m-d\TH:i', strtotime($sale['sale_date'] ?? ''))) ?>">
                        </div>
                        <div>
                            <label>Sale price</label>
                            <input type="number" step="0.01" name="sale_price" value="<?= e($sale['sale_price'] ?? '') ?>">
                        </div>
                    </div>

                    <?php
                    // include optional columns if present in schema
                    $cols = $pdo->query('SHOW COLUMNS FROM sales')->fetchAll(PDO::FETCH_COLUMN);
                    $hasNotes = in_array('notes', $cols, true);
                    $hasPayment = in_array('payment_method', $cols, true);
                    ?>
                    <?php if ($hasNotes): ?>
                        <label>Notes</label>
                        <textarea name="notes" rows="4"><?= e($_POST['notes'] ?? $sale['notes'] ?? '') ?></textarea>
                    <?php endif; ?>

                    <?php if ($hasPayment):
                        $currMethod = $_POST['payment_method'] ?? $sale['payment_method'] ?? 'finance';
                    ?>
                        <label>Payment Method</label>
                        <select name="payment_method">
                            <?php foreach (['cash','finance','lease','other'] as $m): ?>
                                <option value="<?= $m ?>" <?= ($currMethod === $m) ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <div class="mt-12">
                        <button>Save</button>
                        <a class="btn secondary" href="<?= BASE_URL ?>/sales.php">Cancel</a>
                        <?php if ($is_manager_or_admin): ?>
                            <button type="button" id="deleteBtn" class="btn secondary" style="margin-left:8px;">Delete</button>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($is_manager_or_admin): ?>
                    <form id="deleteForm" method="post" style="display:none;"><input type="hidden" name="action" value="delete"></form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        (function(){
            var del = document.getElementById('deleteBtn');
            if (!del) return;
            del.addEventListener('click', function(e){
                if (confirm('Delete this sale? This action is permanent and cannot be undone.')) {
                    document.getElementById('deleteForm').submit();
                }
            });
        })();
    </script>
</body>
</html>
