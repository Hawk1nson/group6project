<?php
// sidebar for public shop pages

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/helpers.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$uri = $_SERVER['REQUEST_URI'] ?? '';
$base = BASE_URL;

function is_active_shop(string $needle, string $uri): string
{
    return str_contains($uri, $needle) ? 'active' : '';
}
?>
<div class="sidebar">
    <div class="sidebar-logo" style="text-align:center;margin-bottom:10px;">
        <img src="<?= $base ?>/../images/system/NLAuto_logo.png" alt="NL Auto" style="max-width:180px;width:100%;height:auto;display:inline-block;">
    </div>
    <?php $cust = $_SESSION['customer'] ?? null; ?>
    <div class="brand">
        <?php if ($cust): ?>
            Welcome, <?= e($cust['name'] ?: $cust['email']) ?>!
        <?php else: ?>
            Welcome!
        <?php endif; ?>
    </div>
    <nav>
        <a href="<?= $base ?>/index.php" class="<?= is_active_shop('/index.php', $uri) ?>">Shop Inventory</a>
        <a href="<?= $base ?>/search.php" class="<?= is_active_shop('/search.php', $uri) ?>">Search</a>
        <a href="<?= $base ?>/contact_us.php" class="<?= is_active_shop('/contact_us.php', $uri) ?>">Contact Us</a>
        <?php if ($cust): ?>
            <a href="<?= $base ?>/profile.php" class="<?= is_active_shop('/profile.php', $uri) ?>">My Profile</a>
            <a href="<?= $base ?>/index.php?logout_customer=1">Logout</a>
        <?php else: ?>
            <a href="<?= $base ?>/public_login.php" class="<?= is_active_shop('/public_login.php', $uri) ?>">Customer Login</a>
        <?php endif; ?>
        <a href="<?= $base ?>/login.php">Dealership Login</a>
    </nav>
</div>
