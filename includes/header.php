<?php
// Common header include: central place for global <link> / meta tags.
// Assumes bootstrap.php has already defined BASE_URL.
if (!defined('BASE_URL')) {
    // fallback: try to compute a sane BASE_URL
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/cdms/public/index.php'), '/');
    define('BASE_URL', $scheme . '://' . $host . $dir);
}
if (session_status() === PHP_SESSION_NONE) session_start();
$_isEmployee = isset($_SESSION['user']);
?>
<!-- Small inline script to apply theme early (prevents flash).
         Reads localStorage 'theme' which may be 'light', 'dark', or 'system'.
         If 'system' (or no value) we remove the attribute so prefers-color-scheme media queries apply. -->
<script>
    (function() {
        try {
            var t = localStorage.getItem('theme');
            if (t === 'light') {
                document.documentElement.setAttribute('data-theme', 'light');
            } else if (t === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                // 'system' or null -> remove explicit theme so CSS media queries win
                document.documentElement.removeAttribute('data-theme');
            }
            // keep a small global pointer for other scripts
            window.__theme = t || 'system';
            // keep multiple tabs in sync
            window.addEventListener('storage', function(e){
                if (e.key === 'theme') {
                    var v = e.newValue;
                    if (v === 'light') document.documentElement.setAttribute('data-theme','light');
                    else if (v === 'dark') document.documentElement.setAttribute('data-theme','dark');
                    else document.documentElement.removeAttribute('data-theme');
                    window.__theme = v || 'system';
                }
            });
        } catch (err) {
            // ignore
        }
    })();
</script>
<link rel="stylesheet" href="<?php echo BASE_URL ?>/assets/style.css">

<?php if ($_isEmployee): ?>
<style>
    .toast-resv { position: fixed; right: 20px; bottom: 20px; max-width: 320px; background: #0f172a; color: #fff; padding: 14px 16px; border-radius: 12px; box-shadow: 0 12px 28px rgba(0,0,0,0.35); z-index: 9999; display:none; }
    .toast-resv h4 { margin: 0 0 6px 0; font-size: 16px; }
    .toast-resv .meta { font-size: 13px; opacity: 0.85; margin-bottom: 6px; }
    .toast-resv button { background: transparent; border: none; color: #fff; cursor: pointer; font-weight: 700; position: absolute; top: 8px; right: 10px; }
</style>
<div id="toast-resv" class="toast-resv" role="alert" aria-live="assertive" aria-atomic="true">
    <button type="button" aria-label="Close">×</button>
    <h4 id="toast-title">Reservation Alert</h4>
    <div class="meta" id="toast-meta"></div>
    <div id="toast-body"></div>
</div>
<script>
    (function(){
        var toast = document.getElementById('toast-resv');
        if (!toast) return;
        var tMeta = document.getElementById('toast-meta');
        var tBody = document.getElementById('toast-body');
        var tTitle = document.getElementById('toast-title');
        var closeBtn = toast.querySelector('button');
        function showToast(title, meta, body){
            tTitle.textContent = title;
            tMeta.textContent = meta;
            tBody.textContent = body;
            toast.style.display = 'block';
            setTimeout(function(){ toast.style.display = 'none'; }, 8000);
        }
        if (closeBtn) closeBtn.onclick = function(){ toast.style.display = 'none'; };

        var lastKey = 'cdms_reservation_event_ts';
        var lastSeen = localStorage.getItem(lastKey) || '';

        async function checkEvent(){
            try {
                var res = await fetch('<?php echo BASE_URL ?>/notify_events.php', {cache:'no-store'});
                if (!res.ok) return;
                var data = await res.json();
                if (data.none) return;
                var ts = data.ts || '';
                if (!ts) return;
                if (lastSeen && ts <= lastSeen) return;
                var status = (data.status || '').toLowerCase();
                var title = status === 'cancelled' ? 'Reservation Cancelled' : 'New Reservation';
                var meta = ts;
                if (data.vehicle) meta += ' • ' + data.vehicle;
                if (data.name) meta += ' • ' + data.name;
                var body = data.message || '';
                showToast(title, meta, body);
                lastSeen = ts;
                localStorage.setItem(lastKey, ts);
            } catch (err) { /* ignore */ }
        }

        checkEvent();
        setInterval(checkEvent, 20000);
    })();
</script>
<?php endif; ?>
