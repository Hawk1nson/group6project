<?php
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';

if (!auth_check()) redirect('login.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
	redirect('vehicles.php');
}

$pdo = DB::conn();
$v = null;
$reserved = null;
try {
	$st = $pdo->prepare('SELECT * FROM vehicles WHERE vehicle_id = ? LIMIT 1');
	$st->execute([$id]);
	$v = $st->fetch(PDO::FETCH_ASSOC);

	if ($v && strcasecmp((string)$v['status'], 'reserved') === 0) {
		// attempt to load reservation to prefill purchase
		try {
			$rq = $pdo->prepare("SELECT r.reservation_id, r.customer_id, c.first_name, c.last_name FROM reservations r LEFT JOIN customers c ON c.customer_id = r.customer_id WHERE r.vehicle_id = ? ORDER BY r.start_datetime DESC LIMIT 1");
			$rq->execute([$id]);
			$reserved = $rq->fetch(PDO::FETCH_ASSOC) ?: null;
		} catch (Throwable $e2) {
			$reserved = null;
		}
	}
} catch (Throwable $e) {
	$v = null;
}

if (!$v) {
	redirect('vehicles.php?msg=' . urlencode('Vehicle not found'));
}

// Image source helper
$img = '';
if (!empty($v['image_filename'])) {
	$img = vehicle_img_src($v['image_filename']);
}
if (!$img && !empty($v['image_url'])) {
	$img = $v['image_url'];
}

// Status badge class
$status = $v['status'] ?? '';
$statusCls = $status === 'available' ? 'ok' : ($status === 'reserved' ? 'warn' : 'muted');

?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Vehicle Detail — Dealer</title>
	<?php include __DIR__ . '/../includes/header.php'; ?>
	<style>
		.detail-shell { max-width: 1100px; margin: 0 auto; }
		.detail-card { background: var(--card-bg); border:1px solid var(--border); border-radius:12px; padding:16px; box-shadow:0 10px 24px rgba(0,0,0,0.08); }
		.detail-grid { display:grid; grid-template-columns: 1fr 320px; gap:16px; align-items:start; }
		.detail-img { width:100%; min-height:320px; object-fit:cover; border-radius:10px; border:1px solid var(--border); background: var(--bg); }
		.specs dt { font-weight:700; }
		.specs dd { margin:0 0 8px 0; }
	</style>
</head>
<body>
	<div class="layout">
		<?php include __DIR__ . '/_sidebar.php'; ?>
		<div class="content">
			<div class="header">
				<div class="title">Vehicle Detail</div>
				<div class="right"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a> • <a href="<?= BASE_URL ?>/logout.php">Logout</a></div>
			</div>

			<div class="detail-shell">
				<div class="flex-between mb-10">
					<a class="btn" href="<?= BASE_URL ?>/vehicles.php">← Back to Vehicles</a>
					<div class="flex-row-wrap" style="gap:8px;">
						<a class="btn" href="<?= BASE_URL ?>/vehicle_edit.php?id=<?= (int)$id ?>">Edit</a>
						<?php
							$purchaseHref = BASE_URL . '/new_sale.php?vehicle_id=' . (int)$id;
							$resName = '';
							if ($reserved && !empty($reserved['customer_id'])) {
								$resName = trim(($reserved['first_name'] ?? '') . ' ' . ($reserved['last_name'] ?? ''));
								$purchaseHref .= '&reserved_customer_id=' . (int)$reserved['customer_id'];
							}
						?>
						<a class="btn btn-primary" id="purchaseBtn" href="<?= e($purchaseHref) ?>" data-res-name="<?= e($resName) ?>">Purchase vehicle</a>
					</div>
				</div>

				<div class="detail-card">
					<div class="detail-grid">
						<div>
							<?php if ($img): ?>
								<img class="detail-img" src="<?= e($img) ?>" alt="<?= e(($v['make'] ?? '') . ' ' . ($v['model'] ?? '')) ?>">
							<?php else: ?>
								<div class="detail-img" style="display:flex;align-items:center;justify-content:center;" aria-label="No image">No image</div>
							<?php endif; ?>
						</div>
						<div>
							<div class="fw-700" style="font-size:20px;"><?= e(($v['model_year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['model'] ?? '')) ?></div>
							<div class="mt-4">Status: <span class="badge <?= $statusCls ?>"><?= e($status) ?></span></div>
							<div class="mt-4 fw-700">Price: $<?= number_format((float)($v['price'] ?? 0), 2) ?></div>
							<dl class="specs mt-6">
								<dt>VIN</dt><dd><?= e($v['vin'] ?? '') ?></dd>
								<dt>Color</dt><dd><?= e($v['color'] ?? '') ?></dd>
								<dt>Condition</dt><dd><?= e($v['condition'] ?? 'Used') ?></dd>
								<dt>Mileage</dt><dd><?= isset($v['mileage']) ? e($v['mileage']) : '—' ?></dd>
								<dt>Location</dt><dd><?= e($v['location'] ?? '') ?></dd>
								<dt>Added</dt><dd><?= isset($v['created_at']) ? e(date('M j, Y', strtotime($v['created_at']))) : '—' ?></dd>
								<dt>Notes</dt><dd><?= nl2br(e($v['notes'] ?? '')) ?></dd>
							</dl>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
<script>
(function(){
	var btn = document.getElementById('purchaseBtn');
	if (!btn) return;
	var resName = btn.getAttribute('data-res-name') || '';
	var isReserved = '<?= strtolower($status) === 'reserved' ? '1' : '0' ?>' === '1';
	if (!isReserved || !resName) return;

	btn.addEventListener('click', function(ev){
		ev.preventDefault();
		var href = btn.getAttribute('href');
		var ok = confirm('This vehicle is reserved for ' + resName + '. Continue to purchase?');
		if (ok) {
			window.location.href = href;
		}
	});
})();
</script>
</body>
</html>
