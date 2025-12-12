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
</head>
<body class="page-vehicle-view">
    <div class="site-wrap">
        <div class="layout">
            <?php include __DIR__ . '/_sidebar_shop.php'; ?>
            <div class="content shop-content">
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
                                <dl class="spec-list">
                                    <dt>VIN</dt><dd><?= htmlspecialchars($v['vin'] ?? '') ?></dd>
                                    <dt>Color</dt><dd><?= htmlspecialchars($v['color'] ?? '') ?></dd>
                                    <dt>Year</dt><dd><?= htmlspecialchars($v['model_year'] ?? '') ?></dd>
                                    <dt>Condition</dt><dd><?= htmlspecialchars($v['condition'] ?? 'Used') ?></dd>
                                </dl>
                                <div class="mt-12">
                                    <button type="button" class="btn btn-primary contact-trigger" data-vehicle-id="<?= (int)$v['vehicle_id'] ?>" data-tag="Inquiry">Request Info</button>
                                    <button type="button" class="btn contact-trigger" data-vehicle-id="<?= (int)$v['vehicle_id'] ?>" data-tag="Reservation">Request Reservation</button>
                                    <div id="contactConfirm" class="alert alert-success hidden" role="status" aria-live="polite" style="margin-top:8px;">Your message was sent. We will be in touch soon.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact modal -->
    <div id="contactModal" class="contact-modal hidden" role="dialog" aria-modal="true" aria-label="Request info form">
        <div class="contact-modal__inner">
            <button type="button" class="contact-modal__close" aria-label="Close">×</button>
            <iframe id="contactFrame" title="Request info" src="" loading="lazy"></iframe>
        </div>
    </div>

<script>
(function(){
    var triggers = Array.prototype.slice.call(document.querySelectorAll('.contact-trigger'));
    var modal = document.getElementById('contactModal');
    var frame = document.getElementById('contactFrame');
    var closeBtn = document.querySelector('.contact-modal__close');
    var confirmBox = document.getElementById('contactConfirm');
    if (!triggers.length || !modal || !frame) return;

    var base = '<?= BASE_URL ?>/contact_us.php';

    function openModal(trigger){
        var vid = trigger.getAttribute('data-vehicle-id') || '';
        var tag = trigger.getAttribute('data-tag') || 'Inquiry';
        var src = base + '?vehicle=' + encodeURIComponent(vid) + '&tag=' + encodeURIComponent(tag) + '&embed=1';
        frame.src = src;
        modal.classList.remove('hidden');
    }
    function closeModal(){
        modal.classList.add('hidden');
        frame.src = '';
    }

    triggers.forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            if (confirmBox) confirmBox.classList.add('hidden');
            openModal(btn);
        });
    });
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

    // Listen for postMessage from embedded contact form to confirm send and close
    window.addEventListener('message', function(e){
        if (!e || !e.data || e.data.type !== 'contact_sent') return;
        closeModal();
        if (confirmBox) {
            confirmBox.classList.remove('hidden');
            confirmBox.focus({preventScroll:true});
        }
    });
})();
</script>
</body>
</html>
