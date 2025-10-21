<?php
require_once __DIR__ . '/../../../lib/auth.php';
require_once __DIR__ . '/../../../lib/helpers.php';
require_once __DIR__ . '/../../../lib/db.php';
if (!auth_check()) redirect('../../login.php');

// error handling - remove for production
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = DB::conn();

function emp_has(PDO $pdo, string $col): bool
{
    static $cols = null;
    if ($cols === null) {
        $cols = $pdo->query("SHOW COLUMNS FROM employees")->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    return in_array($col, $cols, true);
}
$opt_hire_date = emp_has($pdo, 'hire_date');
$opt_phone     = emp_has($pdo, 'phone');      
$opt_is_active = emp_has($pdo, 'is_active');


$select = [
    'employee_id',
    'first_name',
    'last_name',
    'email',
    'role'
];
if ($opt_is_active) $select[] = 'is_active';
if ($opt_hire_date) $select[] = 'hire_date';
if ($opt_phone) $select[] = 'phone';

$selectSql = implode(', ', array_map(fn($c) => "e.$c", $select));

$rows = $pdo->query("
    SELECT $selectSql
    FROM employees e
    ORDER BY e.employee_id DESC
")->fetchAll();


$q        = $_GET['q'] ?? '';
$role     = $_GET['role'] ?? '';
$active   = $_GET['active'] ?? ''; 
$per_page = (int)($_GET['per_page'] ?? 10);
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CDMS — Employees</title>
    <link rel="stylesheet" href="../../assets/style.css">
    <style>
        .filters .row {
            display: grid;
            grid-template-columns: repeat(5, minmax(160px, 1fr));
            gap: 8px;
        }

        .filters .row>div {
            min-width: 160px
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        .badge.on {
            background: #d1fae5;
            color: #065f46
        }

        .badge.off {
            background: #fee2e2;
            color: #991b1b
        }
    </style>
</head>

<body>
    <div class="layout">
        <?php include __DIR__ . '/../_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Employees</div>
                <div class="right"><a href="../dashboard.php">Dashboard</a> • <a href="../../logout.php">Logout</a></div>
            </div>

            <!-- FILTERS -->
            <div class="card filters">
                <form onsubmit="return false;">
                    <div class="row">
                        <div>
                            <label>Search</label>
                            <input id="q" type="text" value="<?= e($q) ?>" placeholder="Name, email<?= $opt_phone ? ', phone' : '' ?>">
                        </div>
                        <div>
                            <label>Role</label>
                            <select id="role">
                                <option value="">All</option>
                                <?php foreach (['sales', 'manager', 'admin'] as $r): ?>
                                    <option value="<?= $r ?>" <?= $role === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($opt_is_active): ?>
                            <div>
                                <label>Status</label>
                                <select id="active">
                                    <option value="">All</option>
                                    <option value="1" <?= $active === '1' ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= $active === '0' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>
                        <div>
                            <label>Per page</label>
                            <select id="per_page">
                                <?php foreach ([10, 20, 30, 50] as $pp): ?>
                                    <option value="<?= $pp ?>" <?= $per_page === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div></div>
                    </div>
                    <div style="margin-top:10px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        <button class="btn" type="button" onclick="window.SimpleTable && SimpleTable.update && SimpleTable.update()">Apply</button>
                        <a class="btn secondary" href="index.php">Reset</a>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="card" style="overflow:auto">
                <table id="employeesTable" class="table">
                    <thead>
                        <tr>
                            <th data-sort>ID</th>
                            <th data-sort>First</th>
                            <th data-sort>Last</th>
                            <th data-sort class="mono">Email</th>
                            <?php if ($opt_phone): ?><th data-sort class="mono">Phone</th><?php endif; ?>
                            <th data-sort>Role</th>
                            <?php if ($opt_is_active): ?><th data-sort>Status</th><?php endif; ?>
                            <?php if ($opt_hire_date): ?><th data-sort>Hire Date</th><?php endif; ?>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= (int)$r['employee_id'] ?></td>
                                <td><?= e($r['first_name'] ?? '') ?></td>
                                <td><?= e($r['last_name'] ?? '') ?></td>
                                <td class="mono"><?= e($r['email'] ?? '') ?></td>
                                <?php if ($opt_phone): ?>
                                    <td class="mono"><?= e($r['phone'] ?? '') ?></td>
                                <?php endif; ?>
                                <td><?= e(ucfirst($r['role'] ?? '')) ?></td>
                                <?php if ($opt_is_active): ?>
                                    <?php $on = !empty($r['is_active']); ?>
                                    <td><span class="badge <?= $on ? 'on' : 'off' ?>"><?= $on ? 'Active' : 'Inactive' ?></span></td>
                                <?php endif; ?>
                                <?php if ($opt_hire_date): ?>
                                    <?php
                                    $hd = '';
                                    if (!empty($r['hire_date'])) {
                                        $ts = strtotime($r['hire_date']);
                                        if ($ts) $hd = date('Y-m-d', $ts);
                                    }
                                    ?>
                                    <td><?= e($hd) ?></td>
                                <?php endif; ?>
                                <td><a class="btn secondary" href="edit.php?id=<?= (int)$r['employee_id'] ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- client-side pager/meta -->
            <div class="header" style="margin-top:12px">
                <div class="muted" id="metaLbl"></div>
                <div>
                    <a class="btn secondary" href="#" id="prevBtn">Prev</a>
                    <a class="btn secondary" href="#" id="nextBtn">Next</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/table.js"></script>
    <script>
        // Base interactive behavior
        SimpleTable.init({
            tableId: 'employeesTable',
            perPage: parseInt(document.getElementById('per_page')?.value || '10', 10),
            selSearch: '#q', // search input (by ID)
            selPerPage: '#per_page',
            selPrev: '#prevBtn',
            selNext: '#nextBtn',
            selMeta: '#metaLbl'
        });

        // Extra filters: role + active layered on top
        (function() {
            var roleSel = document.querySelector('#role');
            var actSel = document.querySelector('#active');
            var table = document.getElementById('employeesTable');

            function rowVisible(tr) {
                var tds = tr.children;
                var role = tds[<?php
                                // compute role column index based on optional columns
                                $idx = 0; // id
                                $idx++;   // first
                                $idx++;   // last
                                $idx++;   // email
                                if ($opt_phone) $idx++;
                                echo $idx;
                                ?>]?.textContent.trim().toLowerCase();

                var activeText = <?php if ($opt_is_active): ?>(tds[<?= $opt_phone ? 6 : 5 ?>]?.textContent.trim().toLowerCase())
            <?php else: ?>
                    ''
            <?php endif; ?>;

            if (roleSel && roleSel.value && role !== roleSel.value.toLowerCase()) return false;

            if (actSel && actSel.value !== '') {
                var want = actSel.value === '1' ? 'active' : 'inactive';
                if (!activeText || activeText.toLowerCase() !== want) return false;
            }
            return true;
            }

            function applyFiltersAndFixMeta() {
                var rows = Array.from(table.querySelectorAll('tbody tr'));
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

            ['change', 'input'].forEach(function(ev) {
                roleSel && roleSel.addEventListener(ev, function() {
                    SimpleTable.update();
                });
                actSel && actSel.addEventListener(ev, function() {
                    SimpleTable.update();
                });
            });

            applyFiltersAndFixMeta();
        })();
    </script>
</body>

</html>