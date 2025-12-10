<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pdo = DB::conn();
try {
    $st = $pdo->prepare("SELECT * FROM vehicles WHERE vehicle_id = ? LIMIT 1");
    $st->execute([$id]);
    $v = $st->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $v = false;
}

if (!$v) {
    header('Location: ' . BASE_URL . '/index.php?msg=' . urlencode('Vehicle not found'));
    exit;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars(($v['make'] ?? '') . ' ' . ($v['model'] ?? '')) ?> — CDMS</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <style>
        .detail-wrap { max-width:900px; margin:24px auto; }
        .detail-grid { display:grid; grid-template-columns: 1fr 320px; gap:16px; }
        .detail-img { width:100%; height:420px; object-fit:cover; border-radius:10px; border:1px solid var(--border); }
        .specs { background:var(--card-bg); padding:12px; border-radius:8px; border:1px solid var(--border); }
    </style>
</head>
<body>
    <div class="detail-wrap">
        <a class="btn" href="index.php">← Back to listings</a>
        <h1 class="mt-12"><?= htmlspecialchars(($v['model_year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['model'] ?? '')) ?></h1>
        <div class="detail-grid">
            <?php $img = $v['image_filename'] ? (BASE_URL . '/../images/vehicles/' . $v['image_filename']) : (BASE_URL . '/assets/placeholder-car.png'); ?>
            <div>
                <img class="detail-img" src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?>">
                <div class="mt-8 spec s note">Location: <?= htmlspecialchars($v['location'] ?? '') ?></div>
            </div>
            <div>
                <div class="specs">
                    <div class="fw-700">Price: <span class="vehicle-price">$<?= number_format((float)($v['price'] ?? 0),2) ?></span></div>
                    <dl style="margin-top:10px">
                        <dt>VIN</dt><dd><?= htmlspecialchars($v['vin'] ?? '') ?></dd>
                        <dt>Color</dt><dd><?= htmlspecialchars($v['color'] ?? '') ?></dd>
                        <dt>Year</dt><dd><?= htmlspecialchars($v['model_year'] ?? '') ?></dd>
                        <dt>Condition</dt><dd><?= htmlspecialchars($v['condition'] ?? 'Used') ?></dd>
                    </dl>
                    <div class="mt-12">
                        <a class="btn btn-primary" href="<?= BASE_URL ?>/contact.php?vehicle=<?= (int)$v['vehicle_id'] ?>">Request Info</a>
                        <a class="btn" href="<?= BASE_URL ?>/contact_us.php?vehicle=<?= (int)$v['vehicle_id'] ?>&tag=Reservation">Request Reservation</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
