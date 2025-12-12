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
    $st = $pdo->prepare("SELECT vehicle_id, make, model, model_year, price, image_filename, location, vin, status
        FROM vehicles WHERE status <> 'sold' ORDER BY model_year DESC, make, model");
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
        .pager { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin:14px 0; }
        .pager .btn { min-width:82px; text-align:center; }
        .pager .muted { font-size:14px; }
        .controls-bar { display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap; margin:12px 0 6px; }
        .per-page { display:flex; align-items:center; gap:8px; }
        .per-page label { font-weight:600; font-size:14px; }
        .per-page select { min-width:90px; }
        .shop-search { flex:1 1 260px; }
    </style>
</head>
<body>
    <div class="site-wrap">
        <div class="layout">
            <?php include __DIR__ . '/_sidebar_shop.php'; ?>
            <div class="content shop-content">
        <h1>Shop Vehicles</h1>
        <div class="controls-bar">
            <div class="per-page">
                <label for="perPageSelect">Per page</label>
                <select id="perPageSelect">
                    <?php foreach ([15, 30, 45, 60] as $pp): ?>
                        <option value="<?= $pp ?>" <?= $pp === 15 ? 'selected' : '' ?>><?= $pp ?></option>
                    <?php endforeach; ?>
                    <option value="all">All</option>
                </select>
            </div>
            <div class="shop-search">
                <label for="vehicleSearch">Search vehicles</label>
                <input id="vehicleSearch" type="text" placeholder="Search make, model, VIN, location">
            </div>
        </div>

        <div class="pager" id="pagerTop" aria-label="Vehicle pagination">
            <div class="muted" id="pageMetaTop"></div>
            <div style="display:flex;gap:8px;">
                <button class="btn secondary" id="prevTop" type="button">Prev</button>
                <button class="btn" id="nextTop" type="button">Next</button>
            </div>
        </div>

        <p class="note hidden" id="noResults">No vehicles match your search.</p>

        <?php if (!$vehicles): ?>
            <p class="note">No vehicles available right now. Please check back later.</p>
        <?php else: ?>
            <div class="shop-grid">
                <?php foreach ($vehicles as $v): ?>
                    <?php
                        $searchText = strtolower(trim(($v['model_year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['model'] ?? '') . ' ' . ($v['vin'] ?? '') . ' ' . ($v['location'] ?? '')));
                    ?>
                    <div class="vehicle-card" data-search="<?= e($searchText) ?>">
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
            <div class="pager" id="pagerBottom" aria-label="Vehicle pagination">
                <div class="muted" id="pageMetaBottom"></div>
                <div style="display:flex;gap:8px;">
                    <button class="btn secondary" id="prevBottom" type="button">Prev</button>
                    <button class="btn" id="nextBottom" type="button">Next</button>
                </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
<script>
(function(){
    var input = document.getElementById('vehicleSearch');
    var perSelect = document.getElementById('perPageSelect');
    var cards = Array.prototype.slice.call(document.querySelectorAll('.vehicle-card'));
    var noRes = document.getElementById('noResults');
    var perPageStorageKey = 'cdms_shop_per_page';
    var perPageChoice = '15';
    if (perSelect) {
        try {
            var stored = localStorage.getItem(perPageStorageKey);
            if (stored && (stored === 'all' || ['15','30','45','60'].indexOf(stored) !== -1)) {
                perPageChoice = stored;
            }
        } catch (e) {}
        perSelect.value = perPageChoice;
    }
    var perPage = perPageChoice === 'all' ? Math.max(1, cards.length || 1) : parseInt(perPageChoice, 10) || 15;
    var currentPage = 1;
    var filtered = cards.slice();

    var pager = {
        prevTop: document.getElementById('prevTop'),
        nextTop: document.getElementById('nextTop'),
        prevBottom: document.getElementById('prevBottom'),
        nextBottom: document.getElementById('nextBottom'),
        metaTop: document.getElementById('pageMetaTop'),
        metaBottom: document.getElementById('pageMetaBottom'),
        wrapTop: document.getElementById('pagerTop'),
        wrapBottom: document.getElementById('pagerBottom')
    };

    if (!input || !cards.length) return;

    function resolvePerPage(){
        if (!perSelect) return 15;
        var val = perSelect.value;
        if (val === 'all') {
            return Math.max(1, filtered.length || cards.length || 1);
        }
        var num = parseInt(val, 10);
        return isNaN(num) ? 15 : num;
    }

    function updatePager(total){
        perPage = resolvePerPage();
        var totalPages = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        var start = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
        var end = total === 0 ? 0 : Math.min(currentPage * perPage, total);
        var label = total === 0 ? 'No vehicles' : 'Showing ' + start + '–' + end + ' of ' + total;
        if (pager.metaTop) pager.metaTop.textContent = label;
        if (pager.metaBottom) pager.metaBottom.textContent = label;
        var disablePrev = currentPage <= 1;
        var disableNext = currentPage >= totalPages;
        [pager.prevTop, pager.prevBottom].forEach(function(btn){ if (btn) btn.disabled = disablePrev; });
        [pager.nextTop, pager.nextBottom].forEach(function(btn){ if (btn) btn.disabled = disableNext; });
        var hidePager = total <= perPage;
        [pager.wrapTop, pager.wrapBottom].forEach(function(el){ if (el) el.style.display = hidePager ? 'none' : 'flex'; });
    }

    function render(){
        perPage = resolvePerPage();
        var total = filtered.length;
        var totalPages = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        var startIdx = (currentPage - 1) * perPage;
        var endIdx = startIdx + perPage;
        var shown = 0;
        cards.forEach(function(card){ card.style.display = 'none'; });
        filtered.slice(startIdx, endIdx).forEach(function(card){
            card.style.display = '';
            shown++;
        });
        if (noRes) noRes.classList.toggle('hidden', shown !== 0);
        updatePager(total);
    }

    function filter(){
        var term = (input.value || '').toLowerCase().trim();
        filtered = cards.filter(function(card){
            var hay = (card.getAttribute('data-search') || '').toLowerCase();
            return !term || hay.indexOf(term) !== -1;
        });
        currentPage = 1;
        render();
    }

    [pager.prevTop, pager.prevBottom].forEach(function(btn){
        if (btn) btn.addEventListener('click', function(){ if (currentPage > 1) { currentPage--; render(); } });
    });
    [pager.nextTop, pager.nextBottom].forEach(function(btn){
        if (btn) btn.addEventListener('click', function(){
            var totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            if (currentPage < totalPages) { currentPage++; render(); }
        });
    });

    if (perSelect) {
        perSelect.addEventListener('change', function(){
            try { localStorage.setItem(perPageStorageKey, perSelect.value || '15'); } catch (e) {}
            currentPage = 1;
            render();
        });
        try { localStorage.setItem(perPageStorageKey, perSelect.value || '15'); } catch (e) {}
    }

    input.addEventListener('input', filter);
    filter();
})();
</script>
</body>
</html>