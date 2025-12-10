<?php
// Customer-facing inventory search (excludes sold vehicles)
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';

$pdo = DB::conn();

$q = trim($_GET['q'] ?? '');
$year_min = isset($_GET['year_min']) && $_GET['year_min'] !== '' ? (int)$_GET['year_min'] : null;
$year_max = isset($_GET['year_max']) && $_GET['year_max'] !== '' ? (int)$_GET['year_max'] : null;
$price_min = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? (float)$_GET['price_min'] : null;
$price_max = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? (float)$_GET['price_max'] : null;

$where = ["status != 'sold'"];
$params = [];

if ($q !== '') {
    $where[] = "(make LIKE ? OR model LIKE ? OR vin LIKE ? OR CONCAT(model_year, ' ', make, ' ', model) LIKE ? )";
    $like = '%' . $q . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($year_min !== null) { $where[] = 'model_year >= ?'; $params[] = $year_min; }
if ($year_max !== null) { $where[] = 'model_year <= ?'; $params[] = $year_max; }
if ($price_min !== null) { $where[] = 'price >= ?'; $params[] = $price_min; }
if ($price_max !== null) { $where[] = 'price <= ?'; $params[] = $price_max; }

$sql = 'SELECT vehicle_id, make, model, model_year, price, image_filename, location, vin, status FROM vehicles';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY model_year DESC, make, model LIMIT 200';

try {
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $vehicles = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $vehicles = [];
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Search Inventory — CDMS</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <style>
        .search-form { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:12px; }
        .search-form input, .search-form select { padding:8px 10px; border-radius:8px; border:1px solid var(--border); background:var(--input-bg); color:var(--text); }
        .shop-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(320px,1fr)); gap:16px; }
        .vehicle-card { background: var(--card-bg); border:1px solid var(--border); border-radius:10px; padding:12px; }
        .vehicle-card img { width:100%; height:180px; object-fit:cover; border-radius:8px; background:#f3f4f6; }
    </style>
</head>
<body>
    <div class="site-wrap">
        <div class="layout">
            <?php include __DIR__ . '/_sidebar_shop.php'; ?>
            <div class="content shop-content">
                <h1>Search Inventory</h1>

                <form method="get" class="search-form" role="search">
                    <input type="search" name="q" placeholder="Search make, model, VIN or year" value="<?= htmlspecialchars($q) ?>">
                    <input type="number" name="year_min" placeholder="Year ≥" value="<?= $year_min ?? '' ?>">
                    <input type="number" name="year_max" placeholder="Year ≤" value="<?= $year_max ?? '' ?>">
                    <input type="number" step="0.01" name="price_min" placeholder="Price ≥" value="<?= $price_min ?? '' ?>">
                    <input type="number" step="0.01" name="price_max" placeholder="Price ≤" value="<?= $price_max ?? '' ?>">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <a class="btn" href="search.php">Reset</a>
                </form>

                <?php if (!$vehicles): ?>
                    <p class="note">No matching vehicles found.</p>
                <?php else: ?>
                    <div class="shop-grid">
                        <?php foreach ($vehicles as $v): ?>
                            <div class="vehicle-card">
                                <?php $src = $v['image_filename'] ? vehicle_img_src($v['image_filename']) : (BASE_URL . '/assets/placeholder-car.png'); ?>
                                <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?>">
                                <div class="fw-700 mt-8"><?= htmlspecialchars(($v['model_year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['model'] ?? '')) ?></div>
                                <div class="muted">VIN: <?= htmlspecialchars($v['vin'] ?? '') ?> • <?= htmlspecialchars($v['location'] ?? '') ?></div>
                                <div class="fw-700 mt-6">$<?= number_format((float)($v['price'] ?? 0), 2) ?></div>
                                <div class="mt-8">
                                    <a class="btn" href="vehicle_view.php?id=<?= (int)$v['vehicle_id'] ?>">View</a>
                                    <a class="btn btn-primary" href="<?= BASE_URL ?>/contact.php?vehicle=<?= (int)$v['vehicle_id'] ?>">Contact</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>
