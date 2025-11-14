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
    <div class="brand">CDMS • Dealership</div>
    <div class="note mt-10">
        <?= e($u['name'] ?? 'User') ?>
    </div>
    <nav>
            <a href="<?= $base ?>/dashboard.php"
            class="<?= is_active('/dashboard.php', $uri) ?>">Return to Dashboard</a>

        <div class="muted">Inventory</div>

        <a href="<?= $base ?>/vehicles.php"
            class="<?= is_active('/vehicles.php', $uri) ?>">Vehicles</a>

        <div class="muted">Operations</div>

        <a href="<?= $base ?>/reservations.php"
            class="<?= is_active('/reservations.php', $uri) ?>">Reservations</a>

        <a href="<?= BASE_URL ?>/customers.php"
            class="<?= is_active('/customers.php', $_SERVER['REQUEST_URI'] ?? '') ?>">Customers</a>

        <a href="<?= BASE_URL ?>/sales.php"
            class="<?= is_active('/sales.php', $_SERVER['REQUEST_URI'] ?? '') ?>">Sales</a>

        <a href="<?= BASE_URL ?>/employees.php"
            class="<?= is_active('/employees.php', $_SERVER['REQUEST_URI'] ?? '') ?>">Employees</a>
        <!-- 
        <div class="muted">Account</div>
        -->
        <a href="<?= $base ?>/logout.php">Logout</a>
    </nav>
</div>