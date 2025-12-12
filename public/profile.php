<?php
// Customer profile page (customer-only)
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Load customer auth helpers without rendering the login page
if (!defined('CUSTOMER_AUTH_EMBED')) define('CUSTOMER_AUTH_EMBED', true);
require_once __DIR__ . '/public_login.php';

$cust = customer_user();
if (!$cust) {
	redirect('public_login.php');
}

$pdo = DB::conn();

// Handle reservation cancellation request (auto-message to dealership)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_reservation') {
	$orig_ts = sanitize($_POST['orig_ts'] ?? '');
	$orig_vehicle = sanitize($_POST['orig_vehicle'] ?? '');

	$name = $cust['name'] ?? '';
	$email = $cust['email'] ?? '';
	$phone = '';

	// Attempt to load phone from customer record
	try {
		$stp = $pdo->prepare('SELECT phone FROM customers WHERE customer_id = ? LIMIT 1');
		$stp->execute([(int)$cust['id']]);
		$rowp = $stp->fetch(PDO::FETCH_ASSOC);
		if ($rowp && !empty($rowp['phone'])) {
			$phone = $rowp['phone'];
		}
	} catch (Throwable $e) { /* ignore */ }

	$logDir = APP_ROOT . '/storage/logs';
	if (!is_dir($logDir)) {
		@mkdir($logDir, 0775, true);
	}
	if (!is_writable($logDir)) {
		@chmod($logDir, 0775);
	}

	$logFile = $logDir . '/contact_messages.log';
	if (!is_file($logFile)) {
		@touch($logFile);
	}
	if (is_file($logFile) && !is_writable($logFile)) {
		@chmod($logFile, 0664);
	}

	$logTarget = is_writable($logFile) ? $logFile : sys_get_temp_dir() . '/contact_messages.log';

	$body = "Customer requests to cancel their reservation.";
	if ($orig_vehicle !== '') {
		$body .= " Vehicle: " . $orig_vehicle . '.';
	}
	if ($orig_ts !== '') {
		$body .= " Original request time: " . $orig_ts . '.';
	}

	$entry = [
		'ts' => date('c'),
		'name' => $name,
		'phone' => $phone,
		'email' => $email,
		'vehicle_id' => null,
		'vehicle' => $orig_vehicle ? ['label' => $orig_vehicle] : null,
		'message' => $body,
		'tag' => 'Reservation',
		'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
		'sent_email' => false,
		'status' => 'cancelled',
	];

	$logged = @file_put_contents($logTarget, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

	if ($logged === false) {
		flash('contact_msg', 'We could not log your cancellation; please call the dealership.', 'alert alert-error');
	} elseif ($logTarget !== $logFile) {
		flash('contact_msg', 'Cancellation logged to a temporary file; please notify the dealership.', 'alert alert-warning');
	} else {
		flash('contact_msg', 'Your reservation cancellation was sent to the dealership.', 'alert alert-success');
	}

	redirect('profile.php');
}

// Fetch customer details
$details = null;
try {
	$st = $pdo->prepare('SELECT customer_id, first_name, last_name, email, phone, address, city, state, zip, created_at FROM customers WHERE customer_id = ? LIMIT 1');
	$st->execute([(int)$cust['id']]);
	$details = $st->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
	$details = null;
}

// Fetch customer messages from contact log (match by email)
$messages = [];
try {
	$logFile = APP_ROOT . '/storage/logs/contact_messages.log';
	if (is_file($logFile)) {
		$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			$data = @json_decode($line, true);
			if ($data && is_array($data) && !empty($data['email']) && strcasecmp($data['email'], $cust['email']) === 0) {
				$messages[] = $data;
			}
		}
		$messages = array_reverse($messages); // newest first
	}
} catch (Throwable $e) {
	$messages = [];
}

// Limit to latest 30 messages for display
$messages = array_slice($messages, 0, 30);

// Fetch vehicles purchased by this customer
$purchases = [];
try {
	$st = $pdo->prepare('SELECT s.sale_id, s.sale_date, s.sale_price, v.vehicle_id, v.model_year, v.make, v.model, v.vin FROM sales s LEFT JOIN vehicles v ON v.vehicle_id = s.vehicle_id WHERE s.customer_id = ? ORDER BY s.sale_date DESC');
	$st->execute([(int)$cust['id']]);
	$purchases = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
	$purchases = [];
}

?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Your Profile — CDMS</title>
	<?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body class="page-profile">
	<div class="site-wrap">
		<div class="layout">
			<?php include __DIR__ . '/_sidebar_shop.php'; ?>
			<div class="content shop-content">
					<div class="profile-shell">
				<h1>Your Profile</h1>
				<?php flash('contact_msg'); ?>
				<p class="note">Manage your info and review the messages you've sent us.</p>

				<div class="card-grid">
					<div class="card">
						<h2>Contact Info</h2>
						<?php if ($details): ?>
							<div class="field-row"><span class="field-label">Name:</span> <?= e(trim(($details['first_name'] ?? '') . ' ' . ($details['last_name'] ?? ''))) ?></div>
							<div class="field-row"><span class="field-label">Email:</span> <?= e($details['email'] ?? $cust['email']) ?></div>
							<div class="field-row"><span class="field-label">Phone:</span> <?= e($details['phone'] ?? '') ?></div>
							<div class="field-row"><span class="field-label">Address:</span> <?= e($details['address'] ?? '') ?></div>
							<div class="field-row"><span class="field-label">City/State:</span> <?= e(trim(($details['city'] ?? '') . ' ' . ($details['state'] ?? ''))) ?></div>
							<div class="field-row"><span class="field-label">ZIP:</span> <?= e($details['zip'] ?? '') ?></div>
							<div class="field-row"><span class="field-label">Member since:</span> <?= e(isset($details['created_at']) ? date('M j, Y', strtotime($details['created_at'])) : '—') ?></div>
						<?php else: ?>
							<p class="note">We could not load your profile details right now.</p>
						<?php endif; ?>
					</div>

					<div class="card">
						<h2>Account</h2>
						<p class="note">Need to update your info? Contact the dealership to make changes.</p>
						<div class="actions" style="display:flex;gap:10px;flex-wrap:wrap;">
							<a class="btn" href="contact_us.php?tag=Account">Message Us</a>
							<a class="btn secondary" href="public_login.php?logout_customer=1">Logout</a>
						</div>
					</div>
				</div>

				<div class="card mt-12">
					<div class="flex-between mb-8">
						<h2 style="margin:0;">Vehicles Purchased</h2>
					</div>
					<?php if (!$purchases): ?>
						<p class="note">You have not purchased any vehicles yet.</p>
					<?php else: ?>
						<ul class="msg-list">
							<?php foreach ($purchases as $p):
								$veh = trim(($p['model_year'] ?? '') . ' ' . ($p['make'] ?? '') . ' ' . ($p['model'] ?? ''));
								$saleDate = $p['sale_date'] ? date('M j, Y', strtotime($p['sale_date'])) : '—';
								$price = $p['sale_price'] !== null ? '$' . number_format((float)$p['sale_price'], 2) : '$0.00';
								$vid = $p['vehicle_id'] ?? null;
							?>
							<li class="msg-item">
								<div class="msg-meta">
									<span><?= e($veh ?: 'Vehicle') ?></span>
									<span>• <?= e($saleDate) ?></span>
									<span>• <?= e($price) ?></span>
								</div>
								<div class="msg-body">VIN: <?= e($p['vin'] ?? '') ?></div>
								<?php if ($vid): ?>
									<div class="mt-8">
										<a class="btn" href="<?= BASE_URL ?>/vehicle_view.php?id=<?= (int)$vid ?>">View vehicle</a>
									</div>
								<?php endif; ?>
							</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<div class="card mt-12">
					<div class="flex-between mb-8">
						<h2 style="margin:0;">Your Messages</h2>
						<a class="btn secondary" href="contact_us.php">Send a new message</a>
					</div>
					<?php if (!$messages): ?>
						<p class="note">You haven't sent any messages yet.</p>
					<?php else: ?>
						<ul class="msg-list">
							<?php foreach ($messages as $m):
								$ts = isset($m['ts']) ? date('M j, Y g:ia', strtotime($m['ts'])) : '—';
								$tag = $m['tag'] ?? '';
								$status = !empty($m['sent_email']) ? 'Sent' : (!empty($m['status']) ? ucfirst($m['status']) : 'Saved');
								$pill = !empty($m['sent_email']) ? 'ok' : (!empty($m['status']) && strtolower($m['status']) === 'cancelled' ? 'muted' : 'warn');
								$veh = '';
								if (!empty($m['vehicle'])) {
									$v = $m['vehicle'];
									if (isset($v['label'])) {
										$veh = $v['label'];
									} else {
										$veh = trim(($v['model_year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['model'] ?? ''));
									}
								}
								$body = $m['message'] ?? '';
								$isReservation = strcasecmp((string)$tag, 'reservation') === 0;
							?>
								<li class="msg-item">
									<div class="msg-meta">
										<span><?= e($ts) ?></span>
										<?php if ($veh): ?><span>• <?= e($veh) ?></span><?php endif; ?>
										<?php if ($tag): ?><span class="pill muted"><?= e($tag) ?></span><?php endif; ?>
										<span class="pill <?= $pill ?>"><?= e($status) ?></span>
									</div>
									<div class="msg-body"><?= e($body) ?></div>
									<?php if ($isReservation): ?>
										<form method="post" class="mt-8" onsubmit="return confirm('Cancel this reservation request? We will notify the dealership.');">
											<input type="hidden" name="action" value="cancel_reservation">
											<input type="hidden" name="orig_ts" value="<?= e($ts) ?>">
											<input type="hidden" name="orig_vehicle" value="<?= e($veh) ?>">
											<button class="btn secondary" type="submit">Cancel Reservation</button>
										</form>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
