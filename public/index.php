<?php
// Public-facing vehicle browse page (shows vehicles with status = 'available')
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';

// Use customer auth utilities from public_login.php (embed mode to suppress page render)
define('CUSTOMER_AUTH_EMBED', true);
require_once __DIR__ . '/public_login.php';

$customer_err = '';
if (isset($_GET['logout_customer'])) {
    customer_logout();
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'customer_login') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!customer_login($email, $pass)) {
        $customer_err = 'Invalid email or password.';
    } else {
        redirect('index.php');
    }
}

$customer = customer_user();

$pdo = DB::conn();

try {
    $st = $pdo->prepare("SELECT vehicle_id, make, model, model_year, price, image_filename, location, vin
        FROM vehicles WHERE status = 'available' ORDER BY model_year DESC, make, model");
    $st->execute();
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
    <title>Shop Vehicles — CDMS</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <style>
        .shop-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(320px,1fr)); gap:16px; }
        .vehicle-card { background: var(--card-bg); border:1px solid var(--border); border-radius:10px; padding:12px; }
        .vehicle-card img { width:100%; height:160px; object-fit:cover; border-radius:8px; background:#f3f4f6; }
        .vehicle-meta { margin-top:8px; }
        .vehicle-price { font-weight:700; color:var(--accent); }
        .vehicle-actions { margin-top:10px; display:flex; gap:8px; }
        .header-bar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
        .login-card { background: var(--card-bg); border:1px solid var(--border); border-radius:10px; padding:12px; margin-bottom:12px; max-width:520px; }
        .login-card form { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
        .login-card .field { flex:1 1 160px; }
        .login-card label { font-weight:600; font-size:14px; }
        .login-card input { width:100%; }
        .alert-error { color:#f87171; }
    </style>
</head>
<body>
    <div class="site-wrap">
        <div class="layout">
            <?php include __DIR__ . '/_sidebar_shop.php'; ?>
            <div class="content shop-content">
        <h1>Shop Vehicles</h1>
        <p class="note">Browse available vehicles. Click "View" for details.</p>

        <?php if (!$vehicles): ?>
            <p class="note">No vehicles available right now. Please check back later.</p>
        <?php else: ?>
            <div class="shop-grid">
                <?php foreach ($vehicles as $v): ?>
                    <div class="vehicle-card">
                        <?php
                        $img = $v['image_filename'] ? (BASE_URL . '/../images/vehicles/' . $v['image_filename']) : (BASE_URL . '/assets/placeholder-car.png');
                        ?>
                        <a href="vehicle_view.php?id=<?= (int)$v['vehicle_id'] ?>">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?>">
                        </a>
                        <div class="vehicle-meta">
                            <div class="fw-700"><?= htmlspecialchars(($v['model_year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['model'] ?? '')) ?></div>
                            <div class="muted">VIN: <?= htmlspecialchars($v['vin'] ?? '') ?> • <?= htmlspecialchars($v['location'] ?? '') ?></div>
                            <div class="vehicle-price">$<?= number_format((float)($v['price'] ?? 0), 2) ?></div>
                            <div class="vehicle-actions">
                                <a class="btn" href="vehicle_view.php?id=<?= (int)$v['vehicle_id'] ?>">View</a>
                                <a class="btn btn-primary" href="<?= BASE_URL ?>/contact.php?vehicle=<?= (int)$v['vehicle_id'] ?>">Contact</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
</body>
</html>