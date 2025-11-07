<?php
// customer listing page for all DEALERSHIP users

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/../login.php');

$pdo = DB::conn();

/* Discover which optional columns exist so we don't error if your schema is minimal */
function has_col(PDO $pdo, string $table, string $col): bool
{
    static $cache = [];
    $key = $table;
    if (!isset($cache[$key])) {
        $cache[$key] = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    return in_array($col, $cache[$key], true);
}

$optional = [
    'created_at'  => has_col($pdo, 'customers', 'created_at'),
    'city'        => has_col($pdo, 'customers', 'city'),
    'state'       => has_col($pdo, 'customers', 'state'),
    'postal_code' => has_col($pdo, 'customers', 'postal_code'),
];

/* Build SELECT safely */
$select = [
    'customer_id',
    'first_name',
    'last_name',
    'email',
    'phone'
];
foreach ($optional as $col => $exists) {
    if ($exists) $select[] = $col;
}
$selectSql = implode(', ', array_map(fn($c) => "c.$c", $select));

$rows = $pdo->query("
    SELECT $selectSql
    FROM customers c
    ORDER BY c.customer_id DESC
")->fetchAll();

/* Prefill filter inputs for the UI (client-side filtering) */
$q        = $_GET['q'] ?? '';
$per_page = (int)($_GET['per_page'] ?? 10);
?>



<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Customers</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body class="page-customers">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Customers</div>
                <div class="right"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a> • <a href="<?= BASE_URL ?>/logout.php">Logout</a></div>
            </div>

            <div class="mt-8">
                <a class="btn primary" href="<?= BASE_URL ?>/customer_add.php">Add Customer</a>
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
                            <input id="q" type="text" value="<?= e($q) ?>" placeholder="Name, email, phone">
                        </div>
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
                    <div class="flex-row-wrap mt-10">
                        <button class="btn" type="button" onclick="window.SimpleTable && SimpleTable.update && SimpleTable.update()">Apply</button>
                        <a class="btn secondary" href="index.php">Reset</a>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="card scrollable">
                <table id="customersTable" class="table">
                    <thead>
                        <tr>
                            <th data-sort>ID</th>
                            <th data-sort>First</th>
                            <th data-sort>Last</th>
                            <th data-sort class="mono">Email</th>
                            <th data-sort class="mono">Phone</th>
                            <?php if ($optional['city'] || $optional['state'] || $optional['postal_code']): ?>
                                <th data-sort>City</th>
                                <th data-sort>State</th>
                                <th data-sort>Postal</th>
                            <?php endif; ?>
                            <?php if ($optional['created_at']): ?>
                                <th data-sort>Created</th>
                            <?php endif; ?>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $created = '';
                            if ($optional['created_at'] && !empty($r['created_at'])) {
                                $ts = strtotime($r['created_at']);
                                if ($ts) $created = date('Y-m-d H:i', $ts);
                            }
                            ?>
                            <tr>
                                <td><?= (int)$r['customer_id'] ?></td>
                                <td><?= e($r['first_name'] ?? '') ?></td>
                                <td><?= e($r['last_name'] ?? '') ?></td>
                                <td class="mono"><?= e($r['email'] ?? '') ?></td>
                                <td class="mono"><?= e($r['phone'] ?? '') ?></td>
                                <?php if ($optional['city'] || $optional['state'] || $optional['postal_code']): ?>
                                    <td><?= e($r['city'] ?? '') ?></td>
                                    <td><?= e($r['state'] ?? '') ?></td>
                                    <td><?= e($r['postal_code'] ?? '') ?></td>
                                <?php endif; ?>
                                <?php if ($optional['created_at']): ?>
                                    <td><?= e($created) ?></td>
                                <?php endif; ?>
                                <td><a class="btn secondary" href="<?= BASE_URL ?>/customer_edit.php?id=<?= (int)$r['customer_id'] ?>">Edit</a></td>
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
        SimpleTable.init({
            tableId: 'customersTable',
            perPage: parseInt(document.getElementById('per_page')?.value || '10', 10),

            // filtering (SimpleTable searches across visible text in the row)
            selSearch: '#q',
            selPerPage: '#per_page',

            // pager + meta
            selPrev: '#prevBtn',
            selNext: '#nextBtn',
            selMeta: '#metaLbl'
        });
    </script>
</body>

</html>