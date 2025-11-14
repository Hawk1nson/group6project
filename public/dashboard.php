<?php
require_once __DIR__ . '/bootstrap.php';
if (!auth_check()) {
    redirect('login.php');
}
$u = auth_user();
?>

<?php
// MAIN dashboard for dealership users

// added dashboard stats and recent activity

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

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

<?php require_once __DIR__ . '/bootstrap.php'; ?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Dealership Dashboard</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body>
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">

            <div class="header">
                <div class="title">Welcome, <?= e($u['name'] ?? 'User') ?></div>
                <div class="right" style="display:flex;align-items:center;gap:10px;">
                    <div class="theme-menu-wrapper" style="position:relative;">
                        <button id="theme-gear" aria-haspopup="true" aria-expanded="false" title="Theme settings" style="background:transparent;border:1px solid var(--border);padding:6px;border-radius:8px;cursor:pointer;color:var(--text)">
                            ⚙️
                        </button>
                        <div id="theme-menu" role="menu" aria-label="Theme" style="display:none;position:absolute;right:0;top:36px;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:8px;box-shadow:0 6px 18px rgba(2,6,23,0.12);z-index:50;">
                            <button class="theme-option" data-theme="system" role="menuitem" title="Use system preference" style="display:flex;gap:8px;align-items:center;padding:8px;border-radius:6px;border:none;background:transparent;cursor:pointer;color:var(--text)">🖥 System</button>
                            <button class="theme-option" data-theme="light" role="menuitem" title="Light theme" style="display:flex;gap:8px;align-items:center;padding:8px;border-radius:6px;border:none;background:transparent;cursor:pointer;color:var(--text)">🌤 Light</button>
                            <button class="theme-option" data-theme="dark" role="menuitem" title="Dark theme" style="display:flex;gap:8px;align-items:center;padding:8px;border-radius:6px;border:none;background:transparent;cursor:pointer;color:var(--text)">🌙 Dark</button>
                        </div>
                    </div>
                    <a href="vehicles.php">Go to Vehicles</a> • <a href="<?= BASE_URL ?>/logout.php">Logout</a>
                </div>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
            <?php endif; ?>

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
            <div class="two-col mt-12">
                <div class="card">
                    <div class="flex-between mb-8">
                        <div class="fw-700">Recent Reservations</div>
                        <a class="btn secondary" href="reservations.php">View all</a>
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
                                        <div class="fw-600"><?= e($who ?: 'Unknown Customer') ?></div>
                                        <small><?= e($car) ?> • <?= e($when) ?></small>
                                    </div>
                                    <div><span class="pill <?= $cls ?>"><?= e($st) ?></span></div>
                                </li>
                        <?php endforeach;
                        endif; ?>
                    </ul>
                </div>

                <div class="card">
                    <div class="fw-700 mb-8">Quick Actions</div>
                    <div class="actions">
                        <a class="btn" href="vehicles.php">Manage Vehicles</a>
                        <a class="btn" href="reservations.php">View Reservations</a>
                        <a class="btn btn-primary" href="<?= BASE_URL ?>/add_reservation.php">Add Reservation</a>
                        <a class="btn" href="customers.php">Find Customer</a>
                        <a class="btn btn-primary" href="<?= BASE_URL ?>/new_sale.php">New Sale</a>
                    </div>
                    <div class="note mt-10">
                    </div>
                </div>
            </div>

            <!-- Recent sales - good addition, but needs work to make it more functional -->
            <div class="card mt-12">
                <div class="flex-between mb-8">
                    <div class="fw-700">Recent Sales</div>
                    <a class="btn" href="sales.php">Sales</a>
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

<script>
    (function(){
        var gear = document.getElementById('theme-gear');
        var menu = document.getElementById('theme-menu');
        if (!gear || !menu) return;
        var options = menu.querySelectorAll('.theme-option');
        function applyThemeChoice(v){
            try {
                if (v === 'light') document.documentElement.setAttribute('data-theme','light');
                else if (v === 'dark') document.documentElement.setAttribute('data-theme','dark');
                else document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', v);
            } catch (err) { }
        }
        function setAriaExpanded(val){ gear.setAttribute('aria-expanded', val ? 'true' : 'false'); }

        // open/close menu
        function openMenu(){ menu.style.display = 'block'; setAriaExpanded(true); }
        function closeMenu(){ menu.style.display = 'none'; setAriaExpanded(false); }

        gear.addEventListener('click', function(e){
            e.stopPropagation();
            if (menu.style.display === 'block') closeMenu(); else openMenu();
        });

        // click outside closes
        document.addEventListener('click', function(){ closeMenu(); });
        // keyboard support
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeMenu(); });

        // init selection highlight based on localStorage
        try {
            var cur = localStorage.getItem('theme') || 'system';
            options.forEach(function(b){
                b.classList.toggle('active', b.getAttribute('data-theme') === cur);
            });
        } catch (err) { }

        options.forEach(function(b){
            b.addEventListener('click', function(e){
                e.stopPropagation();
                var v = this.getAttribute('data-theme');
                applyThemeChoice(v);
                options.forEach(function(x){ x.classList.remove('active'); });
                this.classList.add('active');
                closeMenu();
            });
        });

        // sync across tabs
        window.addEventListener('storage', function(e){
            if (e.key === 'theme'){
                var val = e.newValue || 'system';
                options.forEach(function(b){ b.classList.toggle('active', b.getAttribute('data-theme') === val); });
            }
        });
    })();
</script>

</html>