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
