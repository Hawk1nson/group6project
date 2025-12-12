<?php
require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/login.php');

$pdo = DB::conn();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('reservations.php');
}

$sql = "SELECT r.*, 
               c.first_name AS cust_first, c.last_name AS cust_last, c.email AS cust_email, c.phone AS cust_phone,
               v.make, v.model, v.model_year, v.vin, v.price, v.status AS vehicle_status,
               e.first_name AS emp_first, e.last_name AS emp_last
        FROM reservations r
        LEFT JOIN customers c ON c.customer_id = r.customer_id
        LEFT JOIN vehicles  v ON v.vehicle_id  = r.vehicle_id
        LEFT JOIN employees e ON e.employee_id = r.created_by_employee_id
        WHERE r.reservation_id = ?
        LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute([$id]);
$res = $st->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    redirect('reservations.php?msg=' . urlencode('Reservation not found'));
}

$start = $res['start_datetime'] ? date('M j, Y g:ia', strtotime($res['start_datetime'])) : '—';
$end   = $res['end_datetime']   ? date('M j, Y g:ia', strtotime($res['end_datetime']))   : '—';
$cust  = trim(($res['cust_first'] ?? '') . ' ' . ($res['cust_last'] ?? '')) ?: 'Unknown';
$veh   = trim(($res['model_year'] ? $res['model_year'] . ' ' : '') . ($res['make'] ?? '') . ' ' . ($res['model'] ?? ''));
$statusText = $res['status'] ?? '';
$statusCls  = in_array($statusText, ['confirmed', 'completed'], true) ? 'ok'
    : (in_array($statusText, ['canceled', 'expired'], true) ? 'muted' : 'warn');
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reservation #<?= (int)$id ?> — CDMS</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body class="page-reservations">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Reservation #<?= (int)$id ?></div>
            </div>

            <div class="card">
                <div class="flex-between mb-8">
                    <div class="fw-700">Summary</div>
                    <span class="badge <?= $statusCls ?>"><?= e($statusText) ?></span>
                </div>
                <div class="grid-3">
                    <div><div class="muted">Type</div><div class="fw-600"><?= e($res['type'] ?? '—') ?></div></div>
                    <div><div class="muted">Start</div><div class="fw-600"><?= e($start) ?></div></div>
                    <div><div class="muted">End</div><div class="fw-600"><?= e($end) ?></div></div>
                </div>
            </div>

            <div class="grid-2 mt-12" style="gap:24px;">
                <div class="card">
                    <div class="fw-700 mb-8">Customer</div>
                    <div class="field-row"><span class="field-label">Name:</span> <?= e($cust) ?></div>
                    <div class="field-row"><span class="field-label">Email:</span> <?= e($res['cust_email'] ?? '') ?></div>
                    <div class="field-row"><span class="field-label">Phone:</span> <?= e($res['cust_phone'] ?? '') ?></div>
                </div>
                <div class="card">
                    <div class="fw-700 mb-8">Vehicle</div>
                    <div class="field-row"><span class="field-label">Vehicle:</span> <?= e($veh ?: '—') ?></div>
                    <div class="field-row"><span class="field-label">VIN:</span> <?= e($res['vin'] ?? '') ?></div>
                    <div class="field-row"><span class="field-label">Price:</span> <?= isset($res['price']) ? '$' . number_format((float)$res['price'], 2) : '—' ?></div>
                    <div class="field-row"><span class="field-label">Vehicle Status:</span> <?= e($res['vehicle_status'] ?? '') ?></div>
                    <?php if (!empty($res['vehicle_id'])): ?>
                        <div class="field-row"><a class="btn btn-sm" href="<?= BASE_URL ?>/vehicle_view_dealer.php?id=<?= (int)$res['vehicle_id'] ?>">View Vehicle</a></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-12">
                <div class="fw-700 mb-8">Notes</div>
                <div class="note" style="white-space:pre-wrap;"><?= e($res['notes'] ?? 'None') ?></div>
            </div>

            <div class="card mt-12">
                <div class="fw-700 mb-4">Created By</div>
                <div><?= e(trim(($res['emp_first'] ?? '') . ' ' . ($res['emp_last'] ?? '')) ?: 'Unknown') ?></div>
            </div>

            <div class="mt-10" style="display:flex;gap:10px;align-items:center;">
                <a class="btn secondary" href="<?= BASE_URL ?>/reservations.php">Back to Reservations</a>
                <a class="btn" href="<?= BASE_URL ?>/reservation_edit.php?id=<?= (int)$id ?>">Edit</a>
            </div>
        </div>
    </div>
</body>
</html>
