<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/../login.php');

// error handling - remove for production
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = DB::conn();

// Fetch all sales with related data
$rows = $pdo->query("
    SELECT
        s.sale_id,
        s.sale_date,
        s.sale_price,
        c.first_name AS cust_first, c.last_name AS cust_last, c.email AS cust_email,
        v.vin, v.make, v.model, v.model_year,
        e.first_name AS emp_first, e.last_name AS emp_last, e.email AS emp_email
    FROM sales s
    LEFT JOIN customers c ON c.customer_id = s.customer_id
    LEFT JOIN vehicles  v ON v.vehicle_id  = s.vehicle_id
    LEFT JOIN employees e ON e.employee_id = s.employee_id
    ORDER BY s.sale_id DESC
")->fetchAll();

// Prefill filter inputs 
$q          = $_GET['q'] ?? '';
$date_from  = $_GET['date_from'] ?? '';
$date_to    = $_GET['date_to'] ?? '';
$price_min  = $_GET['price_min'] ?? '';
$price_max  = $_GET['price_max'] ?? '';
$per_page   = (int)($_GET['per_page'] ?? 10);
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Sales</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body class="page-sales">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Sales Home</div>
                <div class="right"><a href="<?= BASE_URL ?>/dashboard.php">Return to Dashboard</a> • <a href="<?= BASE_URL ?>/logout.php">Logout</a></div>
            </div>
            <div class="mt-10">
                <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/new_sale.php">New Sale</a><br>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
                <div class="card mb-12 alert-success"><?= e($_GET['msg']) ?></div>
            <?php endif; ?>

            <!-- FILTERS -->
            <div class="card filters">
                <form onsubmit="return false;">
                    <div class="row">
                        <div>
                            <label>Search</label>
                            <input id="q" type="text" value="<?= e($q) ?>" placeholder="Customer, email, VIN, vehicle, employee">
                        </div>
                        <div>
                            <label>Date ≥</label>
                            <input id="date_from" type="date" value="<?= e($date_from) ?>">
                        </div>
                        <div>
                            <label>Date ≤</label>
                            <input id="date_to" type="date" value="<?= e($date_to) ?>">
                        </div>
                        <div>
                            <label>Price ≥</label>
                            <input id="price_min" type="number" step="0.01" value="<?= e($price_min) ?>">
                        </div>
                        <div>
                            <label>Price ≤</label>
                            <input id="price_max" type="number" step="0.01" value="<?= e($price_max) ?>">
                        </div>
                        <div>
                            <label>Per page</label>
                            <select id="per_page">
                                <?php foreach ([10, 20, 30, 50] as $pp): ?>
                                    <option value="<?= $pp ?>" <?= $per_page === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="flex-row-wrap mt-10">
                        <button class="btn" type="button" onclick="window.SimpleTable && SimpleTable.update && SimpleTable.update()">Apply</button>
                        <a class="btn secondary" href="index.php">Reset</a>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="card scrollable">
                <table id="salesTable" class="table">
                    <thead>
                        <tr>
                            <th data-sort>ID</th>
                            <th data-sort>Date</th>
                            <th data-sort class="amt">Amount</th>
                            <th data-sort>Customer</th>
                            <th data-sort class="mono">Cust Email</th>
                            <th data-sort class="mono">VIN</th>
                            <th data-sort>Vehicle</th>
                            <th data-sort>Salesperson</th>
                            <th data-sort class="mono">Emp Email</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $date  = $r['sale_date'] ? date('Y-m-d', strtotime($r['sale_date'])) : '';
                            $amt   = ($r['sale_price'] !== null) ? number_format((float)$r['sale_price'], 2) : '0.00';
                            $cust  = trim(($r['cust_first'] ?? '') . ' ' . ($r['cust_last'] ?? ''));
                            $veh   = trim(($r['model_year'] ? $r['model_year'] . ' ' : '') . ($r['make'] ?? '') . ' ' . ($r['model'] ?? ''));
                            $emp   = trim(($r['emp_first'] ?? '') . ' ' . ($r['emp_last'] ?? ''));
                            ?>
                            <tr>
                                <td><?= (int)$r['sale_id'] ?></td>
                                <td><?= e($date) ?></td>
                                <td class="amt">$<?= e($amt) ?></td>
                                <td><?= e($cust) ?></td>
                                <td class="mono"><?= e($r['cust_email'] ?? '') ?></td>
                                <td class="mono"><?= e($r['vin'] ?? '') ?></td>
                                <td><?= e($veh) ?></td>
                                <td><?= e($emp) ?></td>
                                <td class="mono"><?= e($r['emp_email'] ?? '') ?></td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/sale_view.php?id=<?= (int)$r['sale_id'] ?>">View</a>
                                    <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/sale_edit.php?id=<?= (int)$r['sale_id'] ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- client-side pager/meta -->
            <div class="header mt-12">
                <div class="muted" id="metaLbl"></div>
                <div>
                    <a class="btn secondary" href="#" id="prevBtn">Prev</a>
                    <a class="btn secondary" href="#" id="nextBtn">Next</a>
                </div>
            </div>

        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/table.js"></script>
    <script>
        // Initialize base interactive behavior
        SimpleTable.init({
            tableId: 'salesTable',
            perPage: parseInt(document.getElementById('per_page')?.value || '10', 10),

            // generic search (matches visible text: customer, emails, vin, vehicle, employee)
            selSearch: '#q',
            selPerPage: '#per_page',

            // pager + meta
            selPrev: '#prevBtn',
            selNext: '#nextBtn',
            selMeta: '#metaLbl'
        });

        // Extra filters: date range + price range layered on top
        (function() {
            var df = document.querySelector('#date_from');
            var dt = document.querySelector('#date_to');
            var p1 = document.querySelector('#price_min');
            var p2 = document.querySelector('#price_max');
            var table = document.getElementById('salesTable');

            function parseAmt(txt) {
                var n = parseFloat((txt || '').replace(/[^0-9.\-]/g, ''));
                return isNaN(n) ? null : n;
            }

            function rowVisible(tr) {
                var tds = tr.children;
                var date = tds[1]?.textContent.trim().slice(0, 10); // YYYY-MM-DD
                var amtT = tds[2]?.textContent.trim(); // "$1,234.00"
                var amt = parseAmt(amtT);

                if (df && df.value && date && date < df.value) return false;
                if (dt && dt.value && date && date > dt.value) return false;
                if (p1 && p1.value !== '' && amt !== null && amt < parseFloat(p1.value)) return false;
                if (p2 && p2.value !== '' && amt !== null && amt > parseFloat(p2.value)) return false;

                return true;
            }

            function applyFiltersAndFixMeta() {
                var tbody = table.querySelector('tbody');
                var rows = Array.from(tbody.querySelectorAll('tr'));
                var shown = 0;
                rows.forEach(function(tr) {
                    var ok = rowVisible(tr);
                    tr.style.display = ok ? '' : 'none';
                    if (ok) shown++;
                });
                var meta = document.getElementById('metaLbl');
                if (meta) meta.textContent = "Visible: " + shown;
            }

            if (window.SimpleTable && typeof SimpleTable.update === 'function') {
                var orig = SimpleTable.update;
                SimpleTable.update = function() {
                    orig();
                    applyFiltersAndFixMeta();
                };
            }

            ['change', 'input'].forEach(function(evt) {
                df && df.addEventListener(evt, function() {
                    SimpleTable.update();
                });
                dt && dt.addEventListener(evt, function() {
                    SimpleTable.update();
                });
                p1 && p1.addEventListener(evt, function() {
                    SimpleTable.update();
                });
                p2 && p2.addEventListener(evt, function() {
                    SimpleTable.update();
                });
            });

            applyFiltersAndFixMeta();
        })();
    </script>
</body>

</html>