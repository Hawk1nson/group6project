<?php
// sidebar for dealership DASHBOARD users
// should be on EVERY dealership page

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/helpers.php';

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
    <div class="brand">CDMS • Dealership</div>
    <div class="note" style="color:#9ca3af; font-size:13px; margin-bottom:10px;">
        <?= e($u['name'] ?? 'User') ?>
    </div>
    <nav>
        <a href="<?= $base ?>/dealership/dashboard.php"
            class="<?= is_active('/dealership/dashboard.php', $uri) ?>">Dashboard</a>

        <div class="muted">Inventory</div>
        <a href="<?= $base ?>/dealership/vehicles/index.php"
            class="<?= is_active('/dealership/vehicles/', $uri) ?>">Vehicles</a>

        <div class="muted">Operations</div>
        <a href="<?= $base ?>/dealership/reservations/index.php"
            class="<?= is_active('/dealership/reservations/', $uri) ?>">Reservations</a>

        <a href="<?= BASE_URL ?>/dealership/customers/index.php"
            class="<?= is_active('/dealership/customers/', $_SERVER['REQUEST_URI'] ?? '') ?>">Customers</a>

        <a href="<?= BASE_URL ?>/dealership/sales/index.php"
            class="<?= is_active('/dealership/sales/', $_SERVER['REQUEST_URI'] ?? '') ?>">Sales</a>

        <a href="<?= BASE_URL ?>/dealership/employees/index.php"
            class="<?= is_active('/dealership/employees/', $_SERVER['REQUEST_URI'] ?? '') ?>">Employees</a>

        <!-- placeholders for later -->
        <a href="#" onclick="return false;" style="opacity:.5; cursor:not-allowed;">Employees</a>

        <div class="muted">Account</div>
        <a href="<?= $base ?>/logout.php">Logout</a>
    </nav>
</div>