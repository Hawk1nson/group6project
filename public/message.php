<?php
// Customer messages viewer for dealership staff
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('login.php');

$pdo = DB::conn();

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
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$per_page = (int)($_GET['per_page'] ?? 20);
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Customer Messages</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <style>
        .message-detail { background: var(--card-bg); border: 1px solid var(--border); border-radius: 10px; padding: 16px; margin-top: 16px; }
        .message-detail .section { margin-bottom: 16px; }
        .message-detail .section-title { font-weight: 700; margin-bottom: 6px; color: var(--accent); }
        .message-detail .field { margin-bottom: 4px; }
        .message-detail .field-label { font-weight: 600; display: inline-block; min-width: 100px; }
        .message-detail .message-text { background: var(--input-bg); border: 1px solid var(--border); padding: 12px; border-radius: 6px; white-space: pre-wrap; word-wrap: break-word; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .status-sent { background: #10b981; color: white; }
        .status-saved { background: #f59e0b; color: white; }
    </style>
</head>
<body class="page-messages">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Customer Messages</div>
                <div class="right"><a href="<?= BASE_URL ?>/dashboard.php">Return to Dashboard</a> • <a href="<?= BASE_URL ?>/logout.php">Logout</a></div>
            </div>

            <?php if ($viewing): ?>
                <!-- Detail View -->
                <div class="mt-10">
                    <a class="btn" href="<?= BASE_URL ?>/message.php">← Back to Messages</a>
                </div>

                <div class="message-detail">
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
                            <div class="section-title">Vehicle Interest</div>
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
                                <div class="field"><span class="field-label">View Vehicle:</span> <a class="btn btn-sm" href="<?= BASE_URL ?>/vehicle_edit.php?id=<?= $vid ?>">Edit Vehicle</a></div>
                            <?php endif; ?>
                        </div>
                    <?php elseif (!empty($viewing['vehicle_id'])): ?>
                        <div class="section">
                            <div class="section-title">Vehicle Interest</div>
                            <div class="field"><span class="field-label">Vehicle ID:</span> <?= (int)$viewing['vehicle_id'] ?></div>
                            <div class="field"><a class="btn btn-sm" href="<?= BASE_URL ?>/vehicle_edit.php?id=<?= (int)$viewing['vehicle_id'] ?>">View Vehicle</a></div>
                        </div>
                    <?php endif; ?>

                    <div class="section">
                        <div class="section-title">Message</div>
                        <div class="message-text"><?= e($viewing['message'] ?? '') ?></div>
                    </div>
                </div>

            <?php else: ?>
                <!-- List View -->
                <p class="note mt-10">Messages submitted by customers through the contact form.</p>

                <!-- FILTERS -->
                <div class="card filters">
                    <form onsubmit="return false;">
                        <div class="row">
                            <div>
                                <label>Search</label>
                                <input id="q" type="text" value="<?= e($q) ?>" placeholder="Name, email, message">
                            </div>
                            <div>
                                <label>Date ≥</label>
                                <input id="date_from" type="date" value="<?= e($date_from) ?>">
                            </div>
                            <div>
                                <label>Date ≤</label>
                                <input id="date_to" type="date" value="<?= e($date_to) ?>">
                            </div>
                            <div>
                                <label>Per page</label>
                                <select id="per_page">
                                    <?php foreach ([10, 20, 30, 50] as $pp): ?>
                                        <option value="<?= $pp ?>" <?= $per_page === $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                    <?php endforeach; ?>
                                </select>
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
                    <div class="muted" id="metaLbl"></div>
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

            // Extra filters: date range
            (function() {
                var df = document.querySelector('#date_from');
                var dt = document.querySelector('#date_to');
                var table = document.getElementById('messagesTable');

                function rowVisible(tr) {
                    var tds = tr.children;
                    var date = tds[1]?.textContent.trim().slice(0, 10); // YYYY-MM-DD

                    // date range
                    if (df && df.value && date < df.value) return false;
                    if (dt && dt.value && date > dt.value) return false;

                    return true;
                }

                function applyDateFilter() {
                    if (!table) return;
                    var rows = table.querySelectorAll('tbody tr');
                    rows.forEach(function(tr) {
                        var vis = rowVisible(tr);
                        tr.style.display = vis ? '' : 'none';
                        tr.setAttribute('data-filtered', vis ? '0' : '1');
                    });
                    if (window.SimpleTable && window.SimpleTable.update) {
                        window.SimpleTable.update();
                    }
                }

                if (df) df.addEventListener('change', applyDateFilter);
                if (dt) dt.addEventListener('change', applyDateFilter);

                // initial
                applyDateFilter();
            })();
        </script>
    <?php endif; ?>
</body>
</html>
