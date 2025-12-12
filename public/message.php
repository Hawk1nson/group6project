<?php
// Customer messages viewer for dealership staff
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('login.php');

$pdo = DB::conn();
$notice = '';

// Handle delete request (any authenticated user)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_message') {
    $deleteId = (int)($_POST['msg_id'] ?? 0);
    $logFileDel = APP_ROOT . '/storage/logs/contact_messages.log';
    if ($deleteId > 0 && is_file($logFileDel) && is_writable($logFileDel)) {
        try {
            $lines = file($logFileDel, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $idx = $deleteId - 1; // _id starts at 1 in original order
            if ($idx >= 0 && $idx < count($lines)) {
                array_splice($lines, $idx, 1);
                $data = implode("\n", $lines);
                file_put_contents($logFileDel, $data . (strlen($data) ? "\n" : ''), LOCK_EX);
                redirect('message.php?msg=' . urlencode('Message deleted.'));
            } else {
                $notice = 'Message not found in log.';
            }
        } catch (Throwable $e) {
            $notice = 'Could not delete message: ' . e($e->getMessage());
        }
    } else {
        $notice = 'Log file is missing or not writable.';
    }
}

// Read messages from log file
$logFile = APP_ROOT . '/storage/logs/contact_messages.log';
$messages = [];

if (is_file($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $idx => $line) {
        $data = @json_decode($line, true);
        if ($data && is_array($data)) {
            $data['_id'] = $idx + 1; // simple ID for viewing
            $messages[] = $data;
        }
    }
}

// Reverse so newest is first
$messages = array_reverse($messages);

// If viewing a specific message
$view_id = isset($_GET['view']) ? (int)$_GET['view'] : null;
$viewing = null;
if ($view_id) {
    foreach ($messages as $m) {
        if ((int)$m['_id'] === $view_id) {
            $viewing = $m;
            break;
        }
    }
}

// Prefill filter inputs
$q = $_GET['q'] ?? '';
$per_page = (int)($_GET['per_page'] ?? 20);
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Customer Messages</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body class="page-messages">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Customer Messages</div>
                <div class="right"><a href="<?= BASE_URL ?>/dashboard.php">Return to Dashboard</a> • <a href="<?= BASE_URL ?>/logout.php">Logout</a></div>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
                <div class="card mb-10 alert-success"><?= e($_GET['msg']) ?></div>
            <?php elseif ($notice): ?>
                <div class="card mb-10 alert-error"><?= e($notice) ?></div>
            <?php endif; ?>

            <?php if ($viewing): ?>
                <!-- Detail View -->
                <div class="mt-10">
                    <a class="btn" href="<?= BASE_URL ?>/message.php">← Back to Messages</a>
                </div>

                <div class="message-detail">
                                <?php
                                $isReservationTag = isset($viewing['tag']) && strtolower($viewing['tag']) === 'reservation';
                                $reservationQuery = '';
                                if ($isReservationTag) {
                                    $prefill = [];
                                    if (!empty($viewing['email'])) {
                                        $prefill['customer_email'] = $viewing['email'];
                                    }
                                    $vehicleIdPrefill = null;
                                    if (!empty($viewing['vehicle']['vehicle_id'])) {
                                        $vehicleIdPrefill = (int)$viewing['vehicle']['vehicle_id'];
                                    } elseif (!empty($viewing['vehicle_id'])) {
                                        $vehicleIdPrefill = (int)$viewing['vehicle_id'];
                                    }
                                    if ($vehicleIdPrefill) {
                                        $prefill['vehicle_id'] = $vehicleIdPrefill;
                                    }
                                    $reservationQuery = http_build_query($prefill);
                                }
                                ?>

                    <div class="section">
                        <div class="section-title">Message #<?= (int)$viewing['_id'] ?></div>
                        <div class="field"><span class="field-label">Received:</span> <?= e(isset($viewing['ts']) ? date('M j, Y g:i A', strtotime($viewing['ts'])) : 'Unknown') ?></div>
                        <?php if (!empty($viewing['tag'])): ?>
                            <div class="field"><span class="field-label">Tag:</span> <?= e($viewing['tag']) ?></div>
                        <?php endif; ?>
                        <div class="field">
                            <span class="field-label">Email Status:</span>
                            <?php if (!empty($viewing['sent_email'])): ?>
                                <span class="status-badge status-sent">Email Sent</span>
                            <?php else: ?>
                                <span class="status-badge status-saved">Saved Only</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">Customer Information</div>
                        <div class="field"><span class="field-label">Name:</span> <?= e($viewing['name'] ?? '') ?></div>
                        <div class="field"><span class="field-label">Email:</span> <a href="mailto:<?= e($viewing['email'] ?? '') ?>"><?= e($viewing['email'] ?? '') ?></a></div>
                        <div class="field"><span class="field-label">Phone:</span> <?= e($viewing['phone'] ?? '') ?></div>
                        <?php if (!empty($viewing['ip'])): ?>
                            <div class="field"><span class="field-label">IP Address:</span> <?= e($viewing['ip']) ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($viewing['vehicle'])): ?>
                        <div class="section">
                            <div class="section-title">Vehicle</div>
                            <?php
                            $v = $viewing['vehicle'];
                            $year = $v['model_year'] ?? '';
                            $make = $v['make'] ?? '';
                            $model = $v['model'] ?? '';
                            $vin = $v['vin'] ?? '';
                            $price = isset($v['price']) ? number_format((float)$v['price'], 2) : '';
                            $vid = isset($v['vehicle_id']) ? (int)$v['vehicle_id'] : null;
                            ?>
                            <div class="field"><span class="field-label">Vehicle:</span> <?= e("$year $make $model") ?></div>
                            <div class="field"><span class="field-label">VIN:</span> <?= e($vin) ?></div>
                            <?php if ($price): ?>
                                <div class="field"><span class="field-label">Price:</span> $<?= e($price) ?></div>
                            <?php endif; ?>
                            <?php if ($vid): ?>
                                <div class="field"><span class="field-label">View Vehicle:</span>
                                    <a class="btn btn-sm" href="<?= BASE_URL ?>/vehicle_view_dealer.php?id=<?= $vid ?>">View</a>
                                    <a class="btn btn-sm secondary" href="<?= BASE_URL ?>/vehicle_edit.php?id=<?= $vid ?>">Edit</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif (!empty($viewing['vehicle_id'])): ?>
                        <div class="section">
                            <div class="section-title">Vehicle</div>
                            <div class="field"><span class="field-label">Vehicle ID:</span> <?= (int)$viewing['vehicle_id'] ?></div>
                            <div class="field">
                                <a class="btn btn-sm" href="<?= BASE_URL ?>/vehicle_view_dealer.php?id=<?= (int)$viewing['vehicle_id'] ?>">View</a>
                                <a class="btn btn-sm secondary" href="<?= BASE_URL ?>/vehicle_edit.php?id=<?= (int)$viewing['vehicle_id'] ?>">Edit</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($isReservationTag): ?>
                        <div class="section">
                            <div class="section-title">Reservation</div>
                            <div class="field">
                                <span class="field-label">Action:</span>
                                <a class="btn btn-primary" href="<?= BASE_URL ?>/add_reservation.php<?= $reservationQuery ? '?' . e($reservationQuery) : '' ?>">Create Reservation</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="section">
                        <div class="section-title">Message</div>
                        <div class="message-text"><?= e($viewing['message'] ?? '') ?></div>
                    </div>

                    <form method="post" onsubmit="return confirm('Delete this message? This cannot be undone.');" class="mt-10">
                        <input type="hidden" name="action" value="delete_message">
                        <input type="hidden" name="msg_id" value="<?= (int)$viewing['_id'] ?>">
                        <button class="btn secondary" type="submit">Delete Message</button>
                    </form>
                </div>

            <?php else: ?>
                <!-- List View -->
                <p class="note mt-10">Messages submitted by customers through the contact form.</p>

                <!-- FILTERS -->
                <div class="card filters">
                    <form onsubmit="return false;">
                        <div class="row">
                            <div class="collapsible-search">
                                <button id="searchToggle" type="button" class="search-toggle" aria-expanded="<?= $q ? 'true' : 'false' ?>" aria-controls="searchBody">
                                    🔍 Search
                                </button>
                                <div id="searchBody" class="search-body <?= $q ? 'open' : '' ?>">
                                    <label for="q">Search</label>
                                    <input id="q" type="text" value="<?= e($q) ?>" placeholder="Name, email, message">
                                    <span class="search-hint">Click to open search, then type to filter.</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex-row-wrap mt-10">
                            <button class="btn" type="button" onclick="window.SimpleTable && SimpleTable.update && SimpleTable.update()">Apply</button>
                            <a class="btn secondary" href="message.php">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- TABLE -->
                <div class="card scrollable">
                    <?php if (empty($messages)): ?>
                        <p class="note">No customer messages yet.</p>
                    <?php else: ?>
                        <table id="messagesTable" class="table">
                            <thead>
                                <tr>
                                    <th data-sort>ID</th>
                                    <th data-sort>Date</th>
                                    <th data-sort>Name</th>
                                    <th data-sort class="mono">Email</th>
                                    <th data-sort class="mono">Phone</th>
                                    <th data-sort>Vehicle</th>
                                    <th data-sort>Tag</th>
                                    <th data-sort>Status</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $m): ?>
                                    <?php
                                    $date = isset($m['ts']) ? date('Y-m-d H:i', strtotime($m['ts'])) : '';
                                    $name = $m['name'] ?? '';
                                    $email = $m['email'] ?? '';
                                    $phone = $m['phone'] ?? '';
                                    $vehicle = '';
                                    if (!empty($m['vehicle'])) {
                                        $v = $m['vehicle'];
                                        $vehicle = trim(($v['model_year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['model'] ?? ''));
                                    }
                                    $status = !empty($m['sent_email']) ? 'Sent' : 'Saved';
                                    $tag = $m['tag'] ?? '';
                                    ?>
                                    <tr>
                                        <td><?= (int)$m['_id'] ?></td>
                                        <td><?= e($date) ?></td>
                                        <td><?= e($name) ?></td>
                                        <td class="mono"><?= e($email) ?></td>
                                        <td class="mono"><?= e($phone) ?></td>
                                        <td><?= e($vehicle) ?></td>
                                        <td><?= e($tag) ?></td>
                                        <td><?= e($status) ?></td>
                                        <td>
                                            <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/message.php?view=<?= (int)$m['_id'] ?>">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- client-side pager/meta -->
                <div class="header mt-12">
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <div class="muted" id="metaLbl"></div>
                        <div class="per-page-inline" style="display:flex;align-items:center;gap:6px;">
                            <label for="per_page" class="muted" style="margin:0;">Per page</label>
                            <select id="per_page" style="width:auto;min-width:80px;">
                                <?php foreach ([10, 20, 30, 50] as $pp): ?>
                                    <option value="<?= $pp ?>" <?= $per_page === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <a class="btn secondary" href="#" id="prevBtn">Prev</a>
                        <a class="btn secondary" href="#" id="nextBtn">Next</a>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>

    <?php if (!$viewing && !empty($messages)): ?>
        <script src="<?= BASE_URL ?>/assets/table.js"></script>
        <script>
            (function(){
                var toggle = document.getElementById('searchToggle');
                var body = document.getElementById('searchBody');
                var input = document.getElementById('q');
                if (!toggle || !body) return;
                function setOpen(open){
                    body.classList.toggle('open', !!open);
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                    if (open && input) {
                        setTimeout(function(){ input.focus(); }, 50);
                    }
                }
                toggle.addEventListener('click', function(){
                    setOpen(!body.classList.contains('open'));
                });
                if (input && input.value.trim()) {
                    setOpen(true);
                }
            })();

            // Initialize base interactive behavior
            SimpleTable.init({
                tableId: 'messagesTable',
                perPage: parseInt(document.getElementById('per_page')?.value || '20', 10),
                selSearch: '#q',
                selPerPage: '#per_page',
                selPrev: '#prevBtn',
                selNext: '#nextBtn',
                selMeta: '#metaLbl'
            });

        </script>
    <?php endif; ?>
</body>
</html>
