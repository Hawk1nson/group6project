<?php
// Public contact form — can be prefilled for a specific vehicle via ?vehicle=ID
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = DB::conn();

// Prefill from logged-in customer (if present)
$cust_session = $_SESSION['customer'] ?? null;
$cust_prefill = null;
if ($cust_session && isset($cust_session['id'])) {
    try {
        $st = $pdo->prepare('SELECT first_name, last_name, email, phone FROM customers WHERE customer_id = ? LIMIT 1');
        $st->execute([(int)$cust_session['id']]);
        $cust_prefill = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        $cust_prefill = null;
    }
}

$vehicle_id = isset($_GET['vehicle']) ? (int)$_GET['vehicle'] : null;
$is_embed = isset($_GET['embed']) || isset($_POST['embed']);
$tag = sanitize($_GET['tag'] ?? ($_POST['tag'] ?? ''));

// Load vehicles for dropdown (exclude sold)
try {
    $st = $pdo->prepare('SELECT vehicle_id, model_year, make, model, vin, price FROM vehicles WHERE status != ? ORDER BY model_year DESC, make, model');
    $st->execute(['sold']);
    $vehicles_list = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $vehicles_list = [];
}

$prefill_vehicle = null;
if ($vehicle_id) {
    foreach ($vehicles_list as $v) {
        if ((int)$v['vehicle_id'] === $vehicle_id) { $prefill_vehicle = $v; break; }
    }
    // If not in list (maybe sold or missing), try to fetch anyway
    if (!$prefill_vehicle) {
        try {
            $st = $pdo->prepare('SELECT vehicle_id, model_year, make, model, vin, price FROM vehicles WHERE vehicle_id = ? LIMIT 1');
            $st->execute([$vehicle_id]);
            $prefill_vehicle = $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            $prefill_vehicle = null;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $sel_vehicle = isset($_POST['vehicle_id']) && $_POST['vehicle_id'] !== '' ? (int)$_POST['vehicle_id'] : null;
    $tag = sanitize($_POST['tag'] ?? $tag ?? '');

    $errors = [];
    if ($name === '') $errors[] = 'Please enter your name.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($message === '') $errors[] = 'Please include a message.';

    if (empty($errors)) {
        // Try to fetch selected vehicle details (if any)
        $vehicle_info = null;
        if ($sel_vehicle) {
            try {
                $st = $pdo->prepare('SELECT vehicle_id, model_year, make, model, vin, price FROM vehicles WHERE vehicle_id = ? LIMIT 1');
                $st->execute([$sel_vehicle]);
                $vehicle_info = $st->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (Throwable $e) {
                $vehicle_info = null;
            }
        }

        // Build email
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $dealerEmail = defined('DEALER_EMAIL') ? constant('DEALER_EMAIL') : null;
        $to = $dealerEmail ?: ('info@' . $host);
        $subject = 'Website Inquiry';
        if ($vehicle_info) {
            $subject .= ' — ' . ($vehicle_info['model_year'] ?? '') . ' ' . ($vehicle_info['make'] ?? '') . ' ' . ($vehicle_info['model'] ?? '');
        }

        $body = "New customer inquiry from website:\n\n";
        if ($tag) {
            $body .= "Tag: " . $tag . "\n\n";
        }
        $body .= "Name: " . $name . "\n";
        $body .= "Phone: " . $phone . "\n";
        $body .= "Email: " . $email . "\n\n";
        if ($vehicle_info) {
            $body .= "Interested vehicle:\n";
            $body .= "ID: " . $vehicle_info['vehicle_id'] . "\n";
            $body .= "Year/Make/Model: " . ($vehicle_info['model_year'] ?? '') . ' ' . ($vehicle_info['make'] ?? '') . ' ' . ($vehicle_info['model'] ?? '') . "\n";
            $body .= "VIN: " . ($vehicle_info['vin'] ?? '') . "\n";
            $body .= "Price: $" . number_format((float)($vehicle_info['price'] ?? 0), 2) . "\n\n";
        }
        $body .= "Message:\n" . $message . "\n\n";
        $body .= "Sent from: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

        $headers = [];
        $headers[] = 'From: ' . ($email !== '' ? $email : 'no-reply@' . $host);
        $headers[] = 'Reply-To: ' . $email;
        $headers[] = 'X-Origin-IP: ' . ($_SERVER['REMOTE_ADDR'] ?? '');

        $sent = false;
        try {
            // Use mail() if available; suppress warnings
            $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
        } catch (Throwable $e) {
            $sent = false;
        }

        // Always persist to a log file for dealer review
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

        // Fallback to a temp location if the main log cannot be written (common on local hosts with strict perms)
        $logTarget = is_writable($logFile) ? $logFile : sys_get_temp_dir() . '/contact_messages.log';
        $entry = [
            'ts' => date('c'),
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'vehicle_id' => $sel_vehicle,
            'vehicle' => $vehicle_info,
            'message' => $message,
            'tag' => $tag ?: null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'sent_email' => $sent,
        ];
        $logged = @file_put_contents($logTarget, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

        if ($sent) {
            flash('contact_msg', 'Your message was sent. The dealership will contact you soon.', 'alert alert-success');
        } else {
            flash('contact_msg', 'Your message was received and saved; we could not send email from the server. The dealership will review it shortly.', 'alert alert-info');
        }
        if ($logged === false) {
            flash('contact_msg', 'We could not write to the message log; please inform the dealership.', 'alert alert-error');
        } elseif ($logTarget !== $logFile) {
            flash('contact_msg', 'Your message was saved to a temporary log file; please ask the dealership to fix storage/logs permissions.', 'alert alert-warning');
        }

        if ($is_embed) {
            // Minimal response for iframe embed: notify parent and stop navigation
            $tagSafe = $tag ? htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') : '';
            ?><!doctype html><html><body><script>parent.postMessage({type:'contact_sent', tag:'<?= $tagSafe ?>'}, '*');</script></body></html><?php
            exit;
        }

        // Redirect to avoid form resubmission
        redirect('contact_us.php');
    }
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Contact Us — CDMS</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <style>
        .contact-shell { max-width: 1040px; margin: 0 auto; }
        .form-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 14px; padding: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .form-row { display:flex; gap:12px; flex-wrap:wrap; }
        .form-row .field { flex:1 1 260px; }
        .form-row label { display:block; font-weight:600; margin-bottom:4px; }
        .form-row input, .form-row select, textarea { width:100%; }
        .vehicle-info { border:1px solid var(--border); padding:12px; border-radius:10px; background:var(--card-bg); margin-top:10px; }
        textarea { min-height: clamp(180px, 28vh, 320px); resize: vertical; }
        .actions { margin-top:16px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    </style>
</head>
<body>
    <div class="site-wrap">
        <div class="layout">
            <?php include __DIR__ . '/_sidebar_shop.php'; ?>
            <div class="content shop-content">
                <div class="contact-shell">
                    <h1>Contact Us</h1>
                    <?php flash('contact_msg'); ?>

                    <p class="note">Fill out the form below and we'll get back to you. You can optionally select a vehicle you're interested in.</p>

                    <div class="form-card">
                        <form method="post" novalidate>
                                    <?php $default_message = ($tag === 'Reservation' && $prefill_vehicle && empty($_POST['message'])) ? 'Reservation request for ' . trim(($prefill_vehicle['model_year'] ?? '') . ' ' . ($prefill_vehicle['make'] ?? '') . ' ' . ($prefill_vehicle['model'] ?? '')) . ' (VIN: ' . ($prefill_vehicle['vin'] ?? '') . '). Please contact me to confirm availability and schedule dates.' : ($_POST['message'] ?? ''); ?>
                                    <input type="hidden" name="tag" value="<?= e($tag) ?>">
                                    <?php if ($is_embed): ?>
                                        <input type="hidden" name="embed" value="1">
                                    <?php endif; ?>
                            <div class="form-row">
                                <?php
                                    $prefill_name  = $cust_prefill ? trim(($cust_prefill['first_name'] ?? '') . ' ' . ($cust_prefill['last_name'] ?? '')) : '';
                                    $prefill_phone = $cust_prefill['phone'] ?? '';
                                    $prefill_email = $cust_prefill['email'] ?? '';
                                ?>
                                <div class="field">
                                    <label for="name">Full name</label>
                                    <input id="name" name="name" type="text" value="<?= e($_POST['name'] ?? $prefill_name) ?>" required>
                                </div>
                                <div class="field">
                                    <label for="phone">Phone</label>
                                    <input id="phone" name="phone" type="text" value="<?= e($_POST['phone'] ?? $prefill_phone) ?>">
                                </div>
                                <div class="field">
                                    <label for="email">Email</label>
                                    <input id="email" name="email" type="email" value="<?= e($_POST['email'] ?? $prefill_email) ?>" required>
                                </div>
                            </div>

                            <div style="margin-top:14px;">
                                <label for="vehicle_id">Vehicle (optional)</label>
                                <select id="vehicle_id" name="vehicle_id">
                                    <option value="">-- No specific vehicle --</option>
                                    <?php foreach ($vehicles_list as $v):
                                        $sel = (int)($v['vehicle_id'] ?? 0) === (int)($vehicle_id ?? 0) ? ' selected' : '';
                                        $data = 'data-year="' . e($v['model_year']) . '" data-make="' . e($v['make']) . '" data-model="' . e($v['model']) . '" data-vin="' . e($v['vin']) . '" data-price="' . e($v['price']) . '"';
                                    ?>
                                        <option value="<?= (int)$v['vehicle_id'] ?>" <?= $sel ?> <?= $data ?>><?= e(($v['model_year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['model'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <div id="vehicle_info" class="vehicle-info" <?= $prefill_vehicle ? '' : 'style="display:none;"' ?>>
                                    <?php if ($prefill_vehicle): ?>
                                        <div><strong id="vi_title"><?= e(($prefill_vehicle['model_year'] ?? '') . ' ' . ($prefill_vehicle['make'] ?? '') . ' ' . ($prefill_vehicle['model'] ?? '')) ?></strong></div>
                                        <div>VIN: <span id="vi_vin"><?= e($prefill_vehicle['vin'] ?? '') ?></span></div>
                                        <div>Price: $<span id="vi_price"><?= number_format((float)($prefill_vehicle['price'] ?? 0), 2) ?></span></div>
                                    <?php else: ?>
                                        <div id="vi_empty">No vehicle selected.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="margin-top:14px;">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" required><?= e($default_message) ?></textarea>
                            </div>

                            <div class="actions">
                                <button class="btn btn-primary" type="submit">Send Message</button>
                                <a class="btn" href="<?= BASE_URL ?>/index.php">Back to Shop</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            var sel = document.getElementById('vehicle_id');
            var info = document.getElementById('vehicle_info');
            var title = document.getElementById('vi_title');
            var vin = document.getElementById('vi_vin');
            var price = document.getElementById('vi_price');
            var empty = document.getElementById('vi_empty');
            function update() {
                var o = sel.options[sel.selectedIndex];
                if (!o || !o.value) {
                    if (info) info.style.display = 'none';
                    return;
                }
                var y = o.getAttribute('data-year') || '';
                var m = o.getAttribute('data-make') || '';
                var mo = o.getAttribute('data-model') || '';
                var v = o.getAttribute('data-vin') || '';
                var p = o.getAttribute('data-price') || '';
                if (title) title.textContent = (y + ' ' + m + ' ' + mo).trim();
                if (vin) vin.textContent = v;
                if (price) price.textContent = parseFloat(p || 0).toFixed(2);
                if (empty) empty.style.display = 'none';
                if (info) info.style.display = '';
            }
            sel.addEventListener('change', update);
            // initial
            update();
        })();
    </script>
</body>
</html>
