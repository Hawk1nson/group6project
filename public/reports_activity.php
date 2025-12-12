<?php
require_once __DIR__ . '/bootstrap.php';
if (!auth_check()) redirect('login.php');
$u = auth_user();
if (!isset($u['role']) || $u['role'] !== 'admin') {
    redirect('dashboard.php?msg=' . urlencode('Admins only.'));
}

$pdo = DB::conn();
$reportDir = APP_ROOT . '/storage/reports';
@mkdir($reportDir, 0775, true);

$msg = '';
$downloadLink = '';

function keep_latest_five(string $dir, string $prefix): void {
    $files = glob($dir . '/' . $prefix . '_*.csv');
    if (!$files) return;
    usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
    $extra = array_slice($files, 5);
    foreach ($extra as $f) @unlink($f);
}

function export_csv(string $type, PDO $pdo, string $dir): array {
    $ts = date('Ymd_His');
    $file = $dir . '/' . $type . '_' . $ts . '.csv';

    $titleMap = [
        'vehicles' => 'Vehicle Inventory Report - Northern Lights Auto Group',
        'employees' => 'Employees Report - Northern Lights Auto Group',
        'reservations' => 'Reservations Report - Northern Lights Auto Group',
        'sales' => 'Sales Report - Northern Lights Auto Group',
    ];

    switch ($type) {
        case 'vehicles':
            $rows = $pdo->query("SELECT vehicle_id, vin, make, model, model_year, color, price, status, mileage, location, created_at FROM vehicles ORDER BY vehicle_id DESC")->fetchAll(PDO::FETCH_ASSOC);
            $headers = array_keys($rows[0] ?? ['vehicle_id' => '']);
            break;
        case 'employees':
            $rows = $pdo->query("SELECT employee_id, first_name, last_name, email, role, is_active, phone, hire_date, created_at FROM employees ORDER BY employee_id DESC")->fetchAll(PDO::FETCH_ASSOC);
            $headers = array_keys($rows[0] ?? ['employee_id' => '']);
            break;
        case 'reservations':
            $rows = $pdo->query("SELECT reservation_id, vehicle_id, customer_id, created_by_employee_id, type, start_datetime, end_datetime, status, notes FROM reservations ORDER BY reservation_id DESC")->fetchAll(PDO::FETCH_ASSOC);
            $headers = array_keys($rows[0] ?? ['reservation_id' => '']);
            break;
        case 'sales':
            $cols = $pdo->query('SHOW COLUMNS FROM sales')->fetchAll(PDO::FETCH_COLUMN);
            $colList = $cols ? implode(',', array_map(fn($c) => "`$c`", $cols)) : '*';
            $rows = $pdo->query("SELECT $colList FROM sales ORDER BY sale_id DESC")->fetchAll(PDO::FETCH_ASSOC);
            $headers = array_keys($rows[0] ?? ['sale_id' => '']);
            break;
        default:
            throw new RuntimeException('Unknown report type');
    }

    $fh = fopen($file, 'w');
    if (!$fh) throw new RuntimeException('Cannot write report');
    if (!empty($titleMap[$type])) {
        fputcsv($fh, [$titleMap[$type]]);
        fputcsv($fh, []); // blank line for readability
    }
    if ($headers) fputcsv($fh, $headers);
    foreach ($rows as $r) {
        fputcsv($fh, array_values($r));
    }
    fclose($fh);

    keep_latest_five($dir, $type);
    return [$file, count($rows)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if (in_array($action, ['export_vehicles','export_employees','export_reservations','export_sales'], true)) {
            $type = str_replace('export_', '', $action);
            [$path, $count] = export_csv($type, $pdo, $reportDir);
            $msg = ucfirst($type) . " report created (" . $count . " rows).";
            $downloadLink = BASE_URL . '/download_report.php?file=' . urlencode(basename($path));
        }
    } catch (Throwable $e) {
        $msg = 'Error: ' . e($e->getMessage());
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reports & Activity</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Reports & Activity</div>
                <div class="right"><a href="<?= BASE_URL ?>/dashboard.php">Back to Dashboard</a></div>
            </div>

            <?php if ($msg): ?>
                <div class="card mb-12"><?= e($msg) ?><?php if ($downloadLink): ?> — <a class="btn download-link" href="<?= e($downloadLink) ?>">Download</a><?php endif; ?></div>
            <?php endif; ?>

            <div class="card">
                <h3>Please select which report you would like to download:</h3>
                <form method="post" class="form" style="display:flex;flex-direction:column;gap:10px;max-width:420px;">
                    <button class="btn" type="submit" name="action" value="export_vehicles">Export Vehicle Inventory Report (CSV)</button>
                    <button class="btn" type="submit" name="action" value="export_employees">Export Employees Report (CSV)</button>
                    <button class="btn" type="submit" name="action" value="export_reservations">Export Reservations Report (CSV)</button>
                    <button class="btn" type="submit" name="action" value="export_sales">Export Vehicle Sales Report (CSV)</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        (function(){
            document.querySelectorAll('.download-link').forEach(function(a){
                a.addEventListener('click', function(ev){
                    var ok = confirm('Download this report to your computer?');
                    if (!ok) ev.preventDefault();
                });
            });
        })();
    </script>
</body>
</html>
