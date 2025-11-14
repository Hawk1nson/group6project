<?php
// this page will be the page that DEALERSHIP accounts see when they log in

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';

require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/../login.php');

$pdo = DB::conn();

// Fetch ALL vehicles
$rows = $pdo->query("
    SELECT vehicle_id, vin, make, model, model_year, color, price, status, image_filename, image_url
    FROM vehicles
    ORDER BY vehicle_id DESC
")->fetchAll();

$q = $_GET['q'] ?? '';
$status = $_GET['status'] ?? '';
$year_min = $_GET['year_min'] ?? '';
$year_max = $_GET['year_max'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$per_page = (int)($_GET['per_page'] ?? 10);
$params = [];

$sql = "SELECT vehicle_id, vin, make, model, model_year, color, price, status, image_filename, image_url
        FROM vehicles
        WHERE 1=1";

if ($q !== '') {
    $sql .= " AND (
        vin LIKE :q OR
        make LIKE :q OR
        model LIKE :q OR
        color LIKE :q OR
        model_year LIKE :q
    )";
    $params[':q'] = '%' . $q . '%';
}

$sql .= " ORDER BY vehicle_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

?>
<?php // vehicle_img_src() is now provided by lib/helpers.php to avoid duplication
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Vehicles</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body class="page-vehicles">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Vehicles Home</div>
                <div class="right"><a href="<?= BASE_URL ?>/dashboard.php">Return to Dashboard</a> • <a href="<?= BASE_URL ?>/logout.php">Logout</a></div>
            </div>

            <!-- Add Vehicle button placed directly under the title -->
            <div class="mt-10">
                <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/vehicle_add.php">Add Vehicle</a><br>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
                <div class="card mb-12 alert-success"><?= e($_GET['msg']) ?></div>
            <?php endif; ?>

            <!-- FILTERS (IDs are for the JS enhancer) -->
            <div class="card filters">
                <form onsubmit="return false;">
                    <div class="row">
                        <div>
                            <label>Search</label>
                            <input id="q" type="text" value="<?= e($q) ?>" placeholder="VIN, make, model, color, year">
                        </div>
                        <div>
                            <label>Status</label>
                            <select id="status">
                                <option value="">All</option>
                                <?php foreach (['available', 'reserved', 'sold'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Year ≥</label>
                            <input id="year_min" type="number" value="<?= e($year_min) ?>" min="1900" max="2100">
                        </div>
                        <div>
                            <label>Year ≤</label>
                            <input id="year_max" type="number" value="<?= e($year_max) ?>" min="1900" max="2100">
                        </div>
                        <div>
                            <label>Price ≥</label>
                            <input id="price_min" type="number" step="0.01" value="<?= e($price_min) ?>">
                        </div>
                        <div>
                            <label>Price ≤</label>
                            <input id="price_max" type="number" step="0.01" value="<?= e($price_max) ?>">
                        </div>
                    </div>
                    <div class="flex-row-wrap mt-10">
                        <label>Per page
                            <select id="per_page">
                                <?php foreach ([10, 20, 30, 50] as $pp): ?>
                                    <option value="<?= $pp ?>"><?= $pp ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button class="btn" type="button" onclick="window.SimpleTable && SimpleTable.update && SimpleTable.update()">Apply</button>
                        <a class="btn secondary" href="#" id="resetBtn">Reset</a>
                    </div>
                </form>
            </div>

            <!-- TABLE -->
            <div class="card scrollable">
                <table id="vehiclesTable" class="table">
                    <thead>
                                <tr>
                                    <th data-sort>ID</th>
                                    <th data-sort>Year</th>
                                    <th data-sort>Make</th>
                                    <th data-sort>Model</th>
                                    <th>Color</th>
                                    <th data-sort>Price</th>
                                    <th data-sort>Status</th>
                                    <th data-sort>VIN</th>
                                    <th>Image</th>
                                    <th>Edit</th>
                                </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= (int)$r['vehicle_id'] ?></td>
                                <td><?= e($r['model_year']) ?></td>
                                <td><?= e($r['make']) ?></td>
                                <td><?= e($r['model']) ?></td>
                                <td><?= e($r['color']) ?></td>
                                <td> $<?= number_format((float)$r['price'], 2) ?></td>
                                <td>
                                    <?php $cls = $r['status'] === 'available' ? 'ok' : ($r['status'] === 'reserved' ? 'warn' : 'muted'); ?>
                                    <span class="badge <?= $cls ?>"><?= e($r['status']) ?></span>
                                </td>
                                <td><?= e($r['vin']) ?></td>
                                <td>
                                    <?php
                                    $src = vehicle_img_src($r['image_filename'] ?? '');
                                    if ($src === '') {
                                        echo '<span class="note">(none)</span>';
                                    } else {
                                        $alt = ($r['make'] ?? '') . ' ' . ($r['model'] ?? '');
                                        $fs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . $src;
                                        if (is_file($fs)) {
                                            echo '<a href="' . e($src) . '" target="_blank" rel="noopener">';
                                            echo '  <img class="thumb-img" src="' . e($src) . '" alt="' . e($alt) . '">';
                                            echo '</a>';
                                        } else {
                                            echo '<a href="' . e($src) . '" target="_blank" rel="noopener">';
                                            echo '  <img class="thumb-img" src="' . e($src) . '" alt="' . e($alt) . '">';
                                            echo '</a>';
                                        }
                                    }
                                    ?>
                                </td>


                                <td><a class="btn secondary" href="<?= BASE_URL ?>/vehicle_edit.php?id=<?= (int)$r['vehicle_id'] ?>">Edit</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- CLIENT-SIDE page links -->
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
        (function(){
            var ppEl = document.getElementById('per_page');
            SimpleTable.init({
                tableId: 'vehiclesTable',
                perPage: parseInt((ppEl && ppEl.value) || '10', 10),

                // filter controls
                selSearch: '#q',
                selStatus: '#status',
                selYearMin: '#year_min',
                selYearMax: '#year_max',
                selPriceMin: '#price_min',
                selPriceMax: '#price_max',
                selPerPage: '#per_page',

                // pager + meta label
                selPrev: '#prevBtn',
                selNext: '#nextBtn',
                selMeta: '#metaLbl'
            });
        })();

        // reset button: clear filter inputs and re-run client-side update
        (function() {
            var reset = document.getElementById('resetBtn');
            if (!reset) return;
            reset.addEventListener('click', function(e) {
                e.preventDefault();
                var ids = ['q','status','year_min','year_max','price_min','price_max'];
                ids.forEach(function(id) {
                    var el = document.getElementById(id);
                    if (!el) return;
                    if (el.tagName.toLowerCase() === 'select') el.selectedIndex = 0;
                    else el.value = '';
                });
                var per = document.getElementById('per_page');
                if (per) per.value = '10';
                if (window.SimpleTable && typeof window.SimpleTable.update === 'function') {
                    window.SimpleTable.update();
                }
            });
        })();
    </script>
</body>

</html>