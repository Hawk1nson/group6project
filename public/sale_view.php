<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/../login.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: ' . BASE_URL . '/sales.php');
    exit;
}

$pdo = DB::conn();

try {
    $st = $pdo->prepare(
        "SELECT s.*, c.first_name AS c_first, c.last_name AS c_last, c.email AS c_email, c.phone AS c_phone,
                v.make, v.model, v.model_year, v.vin, v.location,
                e.first_name AS e_first, e.last_name AS e_last, e.email AS e_email
         FROM sales s
         LEFT JOIN customers c ON c.customer_id = s.customer_id
         LEFT JOIN vehicles  v ON v.vehicle_id  = s.vehicle_id
         LEFT JOIN employees e ON e.employee_id = s.employee_id
         WHERE s.sale_id = ? LIMIT 1"
    );
    $st->execute([$id]);
    $sale = $st->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $sale = false;
}

if (!$sale) {
    header('Location: ' . BASE_URL . '/sales.php?msg=' . urlencode('Sale not found'));
    exit;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>View Sale #<?= (int)$sale['sale_id'] ?></title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Sale #<?= (int)$sale['sale_id'] ?></div>
            </div>

            <div class="two-col">
                <div class="card">
                    <div class="card-header"><strong>Sale Details</strong></div>
                    <div class="card-body">
                        <dl>
                            <dt>Sale ID</dt><dd><?= (int)$sale['sale_id'] ?></dd>
                            <dt>Date</dt><dd><?= htmlspecialchars($sale['sale_date']) ?></dd>
                            <dt>Amount</dt><dd>$<?= number_format((float)$sale['sale_price'], 2) ?></dd>
                            <dt>Payment Method</dt><dd><?= htmlspecialchars(ucfirst($sale['payment_method'] ?? '')) ?></dd>
                            <dt>Notes</dt><dd><pre style="white-space:pre-wrap;margin:0;"><?= htmlspecialchars($sale['notes'] ?? '') ?></pre></dd>
                        </dl>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><strong>Customer</strong></div>
                    <div class="card-body">
                        <div><strong><?= htmlspecialchars(trim(($sale['c_first'] ?? '') . ' ' . ($sale['c_last'] ?? ''))) ?></strong></div>
                        <div><?= htmlspecialchars($sale['c_email'] ?? '') ?></div>
                        <div><?= htmlspecialchars($sale['c_phone'] ?? '') ?></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><strong>Vehicle</strong></div>
                    <div class="card-body">
                        <div><?= htmlspecialchars((($sale['model_year'] ?? '') . ' ' . ($sale['make'] ?? '') . ' ' . ($sale['model'] ?? ''))) ?></div>
                        <div>VIN: <?= htmlspecialchars($sale['vin'] ?? '') ?></div>
                        <div>Location: <?= htmlspecialchars($sale['location'] ?? '') ?></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><strong>Salesperson</strong></div>
                    <div class="card-body">
                        <div><?= htmlspecialchars(trim(($sale['e_first'] ?? '') . ' ' . ($sale['e_last'] ?? ''))) ?></div>
                        <div><?= htmlspecialchars($sale['e_email'] ?? '') ?></div>
                    </div>
                </div>

                <div style="grid-column: 1 / 2; margin-top:12px; display:flex; gap:8px; align-items:flex-start;">
                    <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/sales.php">Back to sales</a>
                    <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/sale_edit.php?id=<?= (int)$sale['sale_id'] ?>">Edit</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
