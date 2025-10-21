<?php
// MAIN dashboard for dealership users

// added dashboard stats and recent activity

require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../lib/db.php';
if (!auth_check()) redirect('../login.php');

$u   = auth_user();
$pdo = DB::conn();

/* ---- Safe stats (guards if a table/column is missing) ---- */
function oneVal(PDO $pdo, string $sql, array $args = [], $fallback = 0)
{
    try {
        $st = $pdo->prepare($sql);
        $st->execute($args);
        return (int)$st->fetchColumn();
    } catch (Throwable $e) {
        return $fallback;
    }
}
function oneMoney(PDO $pdo, string $sql, array $args = [], $fallback = 0.0)
{
    try {
        $st = $pdo->prepare($sql);
        $st->execute($args);
        return (float)$st->fetchColumn();
    } catch (Throwable $e) {
        return $fallback;
    }
}

$veh_total     = oneVal($pdo, "SELECT COUNT(*) FROM vehicles");
$veh_avail     = oneVal($pdo, "SELECT COUNT(*) FROM vehicles WHERE status='available'");
$veh_reserved  = oneVal($pdo, "SELECT COUNT(*) FROM vehicles WHERE status='reserved'");
$veh_sold      = oneVal($pdo, "SELECT COUNT(*) FROM vehicles WHERE status='sold'");

$today = date('Y-m-d');
$month_start = date('Y-m-01');

$res_upcoming  = oneVal($pdo, "SELECT COUNT(*) FROM reservations WHERE start_datetime >= ?", [$today . ' 00:00:00']);
$res_today     = oneVal($pdo, "SELECT COUNT(*) FROM reservations WHERE DATE(start_datetime)=?", [$today]);

$sales_month   = oneVal($pdo, "SELECT COUNT(*) FROM sales WHERE sale_date >= ?", [$month_start]);
$revenue_month = oneMoney($pdo, "SELECT SUM(sale_price) FROM sales WHERE sale_date >= ?", [$month_start]);

/* ---- Tiny sparkline: last 12 weeks reservations (numbers only; fallback zeros) ---- */
$weekly = array_fill(0, 12, 0);
try {
    $st = $pdo->prepare("
        SELECT YEARWEEK(start_datetime, 3) AS yw, COUNT(*) AS c
        FROM reservations
        WHERE start_datetime >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
        GROUP BY yw
        ORDER BY yw
    ");
    $st->execute();
    $rows = $st->fetchAll();
    // map to last 12 week buckets
    $idx = 12 - count($rows);
    foreach ($rows as $r) {
        if ($idx < 12) {
            $weekly[$idx] = (int)$r['c'];
            $idx++;
        }
    }
} catch (Throwable $e) { /* keep zeros */
}

/* ---- Recent activity lists (limit 6; guarded) ---- */
$recent_res = [];
try {
    $recent_res = $pdo->query("
        SELECT r.reservation_id, r.start_datetime, r.status, c.first_name, c.last_name, v.make, v.model
        FROM reservations r
        LEFT JOIN customers c ON c.customer_id = r.customer_id
        LEFT JOIN vehicles  v ON v.vehicle_id  = r.vehicle_id
        ORDER BY r.start_datetime DESC
        LIMIT 6
    ")->fetchAll();
} catch (Throwable $e) {
}

$recent_sales = [];
try {
    $recent_sales = $pdo->query("
        SELECT s.sale_id, s.sale_date, s.sale_price, c.first_name, c.last_name, v.make, v.model, v.model_year
        FROM sales s
        LEFT JOIN customers c ON c.customer_id = s.customer_id
        LEFT JOIN vehicles  v ON v.vehicle_id  = s.vehicle_id
        ORDER BY s.sale_date DESC
        LIMIT 6
    ")->fetchAll();
} catch (Throwable $e) {
}

?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Dealership Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">

            <div class="header">
                <div class="title">Welcome, <?= e($u['name'] ?? 'User') ?></div>
                <div class="right"><a href="vehicles/index.php">Go to Vehicles</a> • <a href="../logout.php">Logout</a></div>
            </div>

            <!-- Top KPI row -->
            <div class="stats">
                <div class="card">
                    <div class="kpi">
                        <div class="num"><?= number_format($veh_total) ?></div>
                        <div>
                            <div class="label">Vehicles in inventory</div>
                            <div><span class="badge ok">Avail <?= (int)$veh_avail ?></span>
                                <span class="badge warn">Res <?= (int)$veh_reserved ?></span>
                                <span class="badge muted">Sold <?= (int)$veh_sold ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="kpi">
                        <div class="num"><?= number_format($res_today) ?></div>
                        <div>
                            <div class="label">Reservations today</div>
                            <div><small>Upcoming: <?= (int)$res_upcoming ?></small></div>
                        </div>
                    </div>
                    <!-- mini sparkline (last 12 weeks) -->
                    <svg viewBox="0 0 120 46" class="spark" aria-hidden="true">
                        <?php
                        $max = max(1, max($weekly));
                        $points = [];
                        foreach ($weekly as $i => $v) {
                            $x = 10 + $i * ((120 - 20) / 11); // 10px padding left/right
                            $y = 46 - (($v / $max) * 36) - 5; // top/bottom padding
                            $points[] = $x . ',' . $y;
                        }
                        ?>
                        <polyline fill="none" stroke="#2563eb" stroke-width="2" points="<?= e(implode(' ', $points)) ?>" />
                    </svg>
                    <div class="note">Last 12 weeks of reservations</div>
                </div>

                <div class="card">
                    <div class="kpi">
                        <div class="num">$<?= number_format($revenue_month ?: 0, 0) ?></div>
                        <div>
                            <div class="label">Revenue (this month)</div>
                            <div><small>Sales: <?= (int)$sales_month ?></small></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-column: Recent activity & Quick actions -->
            <div class="two-col" style="margin-top:12px">
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
                        <div style="font-weight:700">Recent Reservations</div>
                        <a class="btn secondary" href="reservations/index.php">View all</a>
                    </div>
                    <ul class="list">
                        <?php if (!$recent_res): ?>
                            <li><small>No recent reservations</small></li>
                            <?php else: foreach ($recent_res as $r):
                                $when = $r['start_datetime'] ? date('M j, Y g:ia', strtotime($r['start_datetime'])) : '';
                                $who  = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                                $car  = trim(($r['make'] ?? '') . ' ' . ($r['model'] ?? ''));
                                $st   = $r['status'] ?? '';
                                $cls  = in_array($st, ['confirmed', 'completed'], true) ? 'ok' : (in_array($st, ['canceled', 'expired'], true) ? 'muted' : 'warn');
                            ?>
                                <li>
                                    <div>
                                        <div style="font-weight:600"><?= e($who ?: 'Unknown Customer') ?></div>
                                        <small><?= e($car) ?> • <?= e($when) ?></small>
                                    </div>
                                    <div><span class="pill <?= $cls ?>"><?= e($st) ?></span></div>
                                </li>
                        <?php endforeach;
                        endif; ?>
                    </ul>
                </div>

                <div class="card">
                    <div style="font-weight:700; margin-bottom:8px">Quick Actions</div>
                    <div class="actions">
                        <a class="btn" href="vehicles/index.php">Manage Vehicles</a>
                        <a class="btn" href="reservations/index.php">View Reservations</a>
                        <a class="btn" href="customers/index.php">Find Customer</a>
                        <a class="btn" href="#" onclick="return false" style="opacity:.6; cursor:not-allowed">New Sale (coming soon)</a>
                    </div>

                    <div class="note" style="margin-top:10px">
                    </div>
                </div>
            </div>

            <!-- Recent sales - good addition, but needs work to make it more functional -->
            <div class="card" style="margin-top:12px">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
                    <div style="font-weight:700">Recent Sales</div>
                    <a class="btn secondary" href="#" onclick="return false" style="opacity:.6; cursor:not-allowed">Sales (coming soon)</a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$recent_sales): ?>
                            <tr>
                                <td colspan="5"><span class="note">No recent sales</span></td>
                            </tr>
                            <?php else: foreach ($recent_sales as $s):
                                $when = $s['sale_date'] ? date('Y-m-d', strtotime($s['sale_date'])) : '';
                                $who  = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
                                $car  = trim(($s['model_year'] ? $s['model_year'] . ' ' : '') . ($s['make'] ?? '') . ' ' . ($s['model'] ?? ''));
                                $amt  = $s['sale_price'] !== null ? '$' . number_format((float)$s['sale_price'], 2) : '$0.00';
                            ?>
                                <tr>
                                    <td><?= (int)$s['sale_id'] ?></td>
                                    <td><?= e($when) ?></td>
                                    <td><?= e($who ?: 'Unknown') ?></td>
                                    <td><?= e($car) ?></td>
                                    <td><?= e($amt) ?></td>
                                </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>

</html>