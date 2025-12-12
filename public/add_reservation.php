<?php
// cdms/public/add_reservations.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/bootstrap.php';

if (!auth_check()) redirect('/../login.php');
$currentUser = auth_user();
$currentEmployeeId = isset($currentUser['id']) ? (int)$currentUser['id'] : 0;

$pdo = DB::conn();
$errors = [];

$prefCustomerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
$prefVehicleId  = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
$prefCustomerEmail = isset($_GET['customer_email']) ? trim($_GET['customer_email']) : '';

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = (int)($_POST['employee_id'] ?? 0);
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $vehicle_id  = (int)($_POST['vehicle_id'] ?? 0);
    $start_dt    = trim($_POST['start_datetime'] ?? '');
    $type        = trim($_POST['type'] ?? '');
    $notes       = trim($_POST['notes'] ?? '');

    if ($employee_id <= 0) $errors[] = 'Please select an employee.';
    if ($customer_id <= 0) $errors[] = 'Please select a customer.';
    if ($vehicle_id  <= 0) $errors[] = 'Please select a vehicle.';
    if ($start_dt === '') $errors[] = 'Please select a date and time for the reservation.';
    if (!in_array($type, ['test_drive','hold'], true)) $errors[] = 'Please select a valid reservation type.';

    if (!$errors) {
        // compute a default end_datetime because the DB requires a non-null end_datetime
        // Use sensible defaults: test_drive => +45 minutes, hold => +2 hours
        $start_ts = strtotime($start_dt);
        if ($start_ts === false) {
            $errors[] = 'Invalid start date/time format.';
        }
    }

    if (!$errors) {
        try {
            if ($type === 'test_drive') {
                $end_ts = $start_ts + 45 * 60; // 45 minutes
            } else {
                $end_ts = $start_ts + 2 * 60 * 60; // 2 hours for hold (default)
            }
            $end_dt = date('Y-m-d H:i:s', $end_ts);

            $pdo->beginTransaction();
            $ins = $pdo->prepare(
                "INSERT INTO reservations (vehicle_id, customer_id, created_by_employee_id, type, start_datetime, end_datetime, status, notes)
                VALUES (:vehicle_id, :customer_id, :employee_id, :type, :start_dt, :end_dt, 'pending', :notes)"
            );
            $ins->execute([
                ':vehicle_id'  => $vehicle_id,
                ':customer_id' => $customer_id,
                ':employee_id' => $employee_id,
                ':type'        => $type,
                ':start_dt'    => date('Y-m-d H:i:s', $start_ts),
                ':end_dt'      => $end_dt,
                ':notes'       => ($notes !== '' ? $notes : null),
            ]);
            $resId = $pdo->lastInsertId();
            $pdo->commit();

            // Build a short success message
            $msg = 'Reservation scheduled.';
            if ($resId) {
                try {
                    $d = $pdo->prepare(
                        "SELECT r.reservation_id, r.start_datetime, r.type, v.make, v.model, v.model_year, v.vin,
                                c.first_name AS c_first, c.last_name AS c_last, e.first_name AS e_first, e.last_name AS e_last
                        FROM reservations r
                        JOIN vehicles v ON r.vehicle_id = v.vehicle_id
                        JOIN customers c ON r.customer_id = c.customer_id
                        JOIN employees e ON r.created_by_employee_id = e.employee_id
                        WHERE r.reservation_id = ? LIMIT 1"
                    );
                    $d->execute([$resId]);
                    $info = $d->fetch(PDO::FETCH_ASSOC);
                    if ($info) {
                        $custName = trim(($info['c_first'] ?? '') . ' ' . ($info['c_last'] ?? ''));
                        $empName  = trim(($info['e_first'] ?? '') . ' ' . ($info['e_last'] ?? ''));
                        $vehicleLabel = trim(($info['model_year'] ?? '') . ' ' . ($info['make'] ?? '') . ' ' . ($info['model'] ?? ''));
                        $when = date('Y-m-d H:i', strtotime($info['start_datetime'] ?? ''));
                        $msg = sprintf("Reservation scheduled: %s (VIN: %s) for %s on %s (%s)", $vehicleLabel, $info['vin'] ?? '', $custName, $when, $empName);
                    }
                } catch (Throwable $e) {
                    $msg = 'Reservation scheduled.';
                }
            }

            header('Location: ' . BASE_URL . '/dashboard.php?msg=' . urlencode($msg));
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Dropdown data
$employees = $pdo->query("SELECT employee_id, first_name, last_name FROM employees WHERE is_active = 1 ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
$customers = $pdo->query("SELECT customer_id, first_name, last_name FROM customers ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
$vehicles  = $pdo->query("SELECT vehicle_id, make, model, model_year, vin FROM vehicles WHERE status = 'available' ORDER BY model_year DESC, make, model")->fetchAll(PDO::FETCH_ASSOC);

// If a customer email was provided and no customer_id yet, try to preselect matching customer
if ($prefCustomerId === 0 && $prefCustomerEmail !== '') {
    try {
        $st = $pdo->prepare('SELECT customer_id FROM customers WHERE email = ? LIMIT 1');
        $st->execute([$prefCustomerEmail]);
        $prefCustomerId = (int)($st->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        $prefCustomerId = 0;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add Reservation</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">New Reservation</div>
                <div class="right"><a href="<?= BASE_URL ?>/reservations.php">Reservations</a></div>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header"><strong>Reservation Details</strong></div>
                <div class="card-body">
                    <form id="resForm" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="employee_id">Employee</label>
                                <select name="employee_id" id="employee_id" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($employees as $emp): ?>
                                            <?php $eid = (int)$emp['employee_id']; ?>
                                            <option value="<?= $eid ?>" <?= ($eid === $currentEmployeeId) ? 'selected' : '' ?>><?= htmlspecialchars($emp['last_name'] . ', ' . $emp['first_name']) ?></option>
                                        <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="customer_id">Customer</label>
                                <select name="customer_id" id="customer_id" required>
                                    <option value="">-- Select --</option>
                                        <?php foreach ($customers as $c): ?>
                                            <?php $cid = (int)$c['customer_id']; ?>
                                            <option value="<?= $cid ?>" <?= ($cid === $prefCustomerId) ? 'selected' : '' ?>><?= htmlspecialchars($c['last_name'] . ', ' . $c['first_name']) ?></option>
                                        <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full">
                                <label for="vehicle_id">Vehicle</label>
                                <select name="vehicle_id" id="vehicle_id" required>
                                    <option value="">-- Select available vehicle --</option>
                                    <?php foreach ($vehicles as $v): ?>
                                        <?php $vid = (int)$v['vehicle_id']; ?>
                                        <option value="<?= $vid ?>" <?= ($vid === $prefVehicleId) ? 'selected' : '' ?>><?= htmlspecialchars("{$v['model_year']} {$v['make']} {$v['model']} (VIN: {$v['vin']})") ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_datetime">Reservation Date & Time</label>
                                <input type="datetime-local" name="start_datetime" id="start_datetime" required>
                            </div>
                            <div class="form-group">
                                <label for="type">Type</label>
                                <select name="type" id="type" required>
                                    <option value="test_drive">Test drive</option>
                                    <option value="hold">Hold</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" rows="6" style="min-height:140px;width:100%;" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>

                        <div class="form-row">
                            <button class="btn btn-success" type="submit">Confirm Reservation</button>
                            <a class="btn btn-secondary" href="<?= BASE_URL ?>/reservations.php">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // simple confirm popup before creating reservation
        document.getElementById('resForm').addEventListener('submit', function(e){
            var empTxt = document.getElementById('employee_id').options[document.getElementById('employee_id').selectedIndex].text;
            var custTxt = document.getElementById('customer_id').options[document.getElementById('customer_id').selectedIndex].text;
            var vehTxt = document.getElementById('vehicle_id').options[document.getElementById('vehicle_id').selectedIndex].text;
            var when = document.getElementById('start_datetime').value;
            var type = document.getElementById('type').value;
            var msg = `Confirm reservation?\n\nEmployee: ${empTxt}\nCustomer: ${custTxt}\nVehicle: ${vehTxt}\nWhen: ${when}\nType: ${type}`;
            if (!window.confirm(msg)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
