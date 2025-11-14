<?php
// this page shows all reservations made by customers - viewable by dealership staff only

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/../login.php');

$pdo = DB::conn();

/* Pull distinct statuses for the filter */
$statusOptions = $pdo->query("SELECT DISTINCT status FROM reservations ORDER BY status")
    ->fetchAll(PDO::FETCH_COLUMN);

/* Fetch ALL reservations; client-side JS (table.js) handles filtering/sorting/paging */
$rows = $pdo->query("
    SELECT
        r.reservation_id,
        r.type,
        r.start_datetime,
        r.end_datetime,
        r.status,
        r.notes,
        c.first_name, c.last_name, c.email,
        v.vin, v.make, v.model, v.model_year
    FROM reservations r
    LEFT JOIN customers c ON c.customer_id = r.customer_id
    LEFT JOIN vehicles  v ON v.vehicle_id  = r.vehicle_id
    ORDER BY r.reservation_id DESC
")->fetchAll();

/* Prefill filter inputs (used by JS only) */
$q          = $_GET['q'] ?? '';
$status     = $_GET['status'] ?? '';
$date_from  = $_GET['date_from'] ?? '';
$date_to    = $_GET['date_to'] ?? '';
$per_page   = (int)($_GET['per_page'] ?? 10);
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Reservations</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body class="page-reservations">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Reservations Home</div>
                <div class="right"><a href="<?= BASE_URL ?>/dashboard.php">Return to Dashboard</a> • <a href="<?= BASE_URL ?>/logout.php">Logout</a></div>
            </div>

            <!-- Add Reservation button placed directly under the title -->
            <div class="mt-10">
                <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/add_reservation.php">Add Reservation</a>
            </div>

            <!-- FILTERS -->
            <div class="card filters">
                <form onsubmit="return false;">
                    <div class="row">
                        <div>
                            <label>Search</label>
                            <input id="q" type="text" value="<?= e($q) ?>" placeholder="Name, email, VIN, make/model">
                        </div>
                        <div>
                            <label>Status</label>
                            <select id="status">
                                <option value="">All</option>
                                <?php foreach ($statusOptions as $s): if ($s === null || $s === '') continue; ?>
                                    <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                                <?php endforeach; ?>
                            </select>
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
                            <label>Per page</label>
                            <select id="per_page">
                                <?php foreach ([10, 20, 30, 50] as $pp): ?>
                                    <option value="<?= $pp ?>" <?= $per_page === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Type</label>
                            <select id="type">
                                <option value="">All</option>
                                <option value="test_drive">Test drive</option>
                                <option value="hold">Hold</option>
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
                <table id="reservationsTable" class="table">
                    <thead>
                        <tr>
                            <th data-sort>ID</th>
                            <th data-sort>Start</th>
                            <th data-sort>End</th>
                            <th data-sort>Type</th>
                            <th data-sort>Customer</th>
                            <th data-sort class="mono">Email</th>
                            <th data-sort class="mono">VIN</th>
                            <th data-sort>Vehicle</th>
                            <th data-sort>Notes</th></th>
                            <th data-sort>Status</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $start = $r['start_datetime'] ? date('Y-m-d H:i', strtotime($r['start_datetime'])) : '';
                            $end   = $r['end_datetime']   ? date('Y-m-d H:i', strtotime($r['end_datetime']))   : '';
                            $cust  = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                            $veh   = trim(($r['model_year'] ? $r['model_year'] . ' ' : '') . ($r['make'] ?? '') . ' ' . ($r['model'] ?? ''));
                            $statusText = $r['status'] ?? '';
                            $statusCls  = in_array($statusText, ['confirmed', 'completed'], true) ? 'ok'
                                : (in_array($statusText, ['canceled', 'expired'], true) ? 'muted' : 'warn');
                            ?>
                            <tr>
                                <td><?= (int)$r['reservation_id'] ?></td>
                                <td><?= e($start) ?></td>
                                <td><?= e($end) ?></td>
                                <td><?= e($r['type'] ?? '') ?></td>
                                <td><?= e($cust) ?></td>
                                <td class="mono"><?= e($r['email'] ?? '') ?></td>
                                <td class="mono"><?= e($r['vin'] ?? '') ?></td>
                                <td><?= e($veh) ?></td>
                                <td><?= e($r['notes'] ?? '') ?></td>
                                <td><span class="badge <?= $statusCls ?>"><?= e($statusText) ?></span></td>
                                <td><a class="btn secondary" href="reservation_edit.php?id=<?= (int)$r['reservation_id'] ?>">Edit</a></td>
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
        // Initialize the general interactive behavior
        SimpleTable.init({
            tableId: 'reservationsTable',
            perPage: parseInt(document.getElementById('per_page')?.value || '10', 10),

            selSearch: '#q',
            selStatus: '#status',
            selPerPage: '#per_page',

            // pager + meta label
            selPrev: '#prevBtn',
            selNext: '#nextBtn',
            selMeta: '#metaLbl'
        });

        // Extra filters (date range + type) layered on top of SimpleTable
        (function() {
            var df = document.querySelector('#date_from');
            var dt = document.querySelector('#date_to');
            var tp = document.querySelector('#type');
            var table = document.getElementById('reservationsTable');

            function rowVisibleAfterExtraFilters(tr) {
                var cells = tr.children;
                var startTxt = cells[1]?.textContent.trim(); // Start column
                var typeTxt = cells[3]?.textContent.trim(); // Type column

                // Date filter: compare YYYY-MM-DD part lexically
                if (df && df.value && startTxt) {
                    var rowDate = startTxt.substring(0, 10);
                    if (rowDate < df.value) return false;
                }
                if (dt && dt.value && startTxt) {
                    var rowDate = startTxt.substring(0, 10);
                    if (rowDate > dt.value) return false;
                }
                // Type filter
                if (tp && tp.value && typeTxt && typeTxt !== tp.value) return false;

                return true;
            }

            function applyExtraFiltersAndFixMeta() {
                var tbody = table.querySelector('tbody');
                var rows = Array.from(tbody.querySelectorAll('tr'));
                var shown = 0;
                rows.forEach(function(tr) {
                    var ok = rowVisibleAfterExtraFilters(tr);
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
                    applyExtraFiltersAndFixMeta();
                };
            }

            ['change', 'input'].forEach(function(evt) {
                df && df.addEventListener(evt, function() {
                    SimpleTable.update();
                });
                dt && dt.addEventListener(evt, function() {
                    SimpleTable.update();
                });
                tp && tp.addEventListener(evt, function() {
                    SimpleTable.update();
                });
            });

            applyExtraFiltersAndFixMeta();
        })();
    </script>
</body>

</html>