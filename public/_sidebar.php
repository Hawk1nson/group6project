<?php
// sidebar for dealership DASHBOARD users
// should be on EVERY dealership page

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';

if (!auth_check()) redirect('../login.php');
$u    = auth_user();
$uri  = $_SERVER['REQUEST_URI'] ?? '';
$base = BASE_URL; // /cdms/public  - also defined in config.php - remove redundancy later

function is_active(string $needle, string $uri): string
{
    return str_contains($uri, $needle) ? 'active' : '';
}
?>
<div class="sidebar">
    <div class="sidebar-logo" style="text-align:center;margin-bottom:10px;">
        <img src="<?= $base ?>/../images/system/NLAuto_logo.png" alt="NL Auto" style="max-width:180px;width:100%;height:auto;display:inline-block;">
    </div>
    <div class="brand">CDMS • Dealership Portal</div>
    <div class="note mt-10">
        <?= e($u['name'] ?? 'User') ?>
    </div>
    <nav>
            <a href="<?= $base ?>/dashboard.php"
            class="<?= is_active('/dashboard.php', $uri) ?>">Dashboard • Home</a>

        <div class="muted">Inventory</div>

        <a href="<?= $base ?>/vehicles.php"
            class="<?= is_active('/vehicles.php', $uri) ?>">Vehicles</a>

        <div class="muted">Operations</div>

        <a href="<?= BASE_URL ?>/sales.php"
            class="<?= is_active('/sales.php', $_SERVER['REQUEST_URI'] ?? '') ?>">Sales</a>

        <a href="<?= $base ?>/reservations.php"
            class="<?= is_active('/reservations.php', $uri) ?>">Reservations</a>

        <a href="<?= BASE_URL ?>/customers.php"
            class="<?= is_active('/customers.php', $_SERVER['REQUEST_URI'] ?? '') ?>">Customers</a>

        <a href="<?= BASE_URL ?>/employees.php"
            class="<?= is_active('/employees.php', $_SERVER['REQUEST_URI'] ?? '') ?>">Employees</a>
        <?php if (isset($u['role']) && $u['role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/reports_activity.php"
            class="<?= is_active('/reports_activity.php', $_SERVER['REQUEST_URI'] ?? '') ?>">Reports &amp; Logs</a>
        <?php endif; ?>

        <div class="muted">Communication</div>

        <a href="<?= BASE_URL ?>/message.php"
            class="<?= is_active('/message.php', $_SERVER['REQUEST_URI'] ?? '') ?>">Customer Messages</a>
        <!-- 
        <div class="muted">Account</div>
        -->
        <a href="<?= $base ?>/logout.php">Logout</a>
    </nav>
</div>