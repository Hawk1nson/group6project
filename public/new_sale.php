<?php
// cdms/public/new_sale.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/bootstrap.php';

// DB connection
$pdo = DB::conn();

$preselectVehicleId = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : null;
$errors = [];

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = (int)($_POST['employee_id'] ?? 0);
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $vehicle_id  = (int)($_POST['vehicle_id'] ?? 0);
    $sale_price  = trim($_POST['sale_price'] ?? '');
    $notes       = trim($_POST['notes'] ?? '');
    $payment_method = trim(strtolower($_POST['payment_method'] ?? ''));

    // normalize/validate payment method
    $allowed_methods = ['cash','finance','lease','other'];
    if ($payment_method === '' || !in_array($payment_method, $allowed_methods, true)) {
        $payment_method = 'finance';
    }

    if ($employee_id <= 0) $errors[] = 'Please select a salesperson.';
    if ($customer_id <= 0) $errors[] = 'Please select a customer.';
    if ($vehicle_id  <= 0) $errors[] = 'Please select a vehicle.';
    if ($sale_price === '' || !is_numeric($sale_price) || $sale_price < 0) $errors[] = 'Enter a valid sale price.';

    // Ensure vehicle still available
    if (!$errors) {
        $chk = $pdo->prepare("SELECT vehicle_id, status FROM vehicles WHERE vehicle_id = ?");
        $chk->execute([$vehicle_id]);
        $veh = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$veh)               $errors[] = 'Vehicle not found.';
        elseif ($veh['status'] !== 'available') $errors[] = 'Vehicle is not available.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            // Insert sale — your DB trigger is expected to mark vehicle as sold
            $ins = $pdo->prepare(
                "INSERT INTO sales (vehicle_id, customer_id, employee_id, sale_price, notes, payment_method)
                VALUES (:vehicle_id, :customer_id, :employee_id, :sale_price, :notes, :payment_method)"
            );
            $ins->execute([
                ':vehicle_id'  => $vehicle_id,
                ':customer_id' => $customer_id,
                ':employee_id' => $employee_id,
                ':sale_price'  => number_format((float)$sale_price, 2, '.', ''),
                ':notes'       => ($notes !== '' ? $notes : null),
                ':payment_method' => $payment_method,
            ]);
            $saleId = $pdo->lastInsertId();
            $pdo->commit();

            // Friendly success message (try to provide useful details, but don't fail on lookup)
            $msg = 'Sale recorded.';
            if ($saleId) {
                try {
                    $d = $pdo->prepare(
                        "SELECT s.sale_id, s.sale_price, v.model_year, v.make, v.model, v.vin,
                                c.first_name AS c_first, c.last_name AS c_last,
                                e.first_name AS e_first, e.last_name AS e_last
                        FROM sales s
                        JOIN vehicles v ON s.vehicle_id = v.vehicle_id
                        JOIN customers c ON s.customer_id = c.customer_id
                        JOIN employees e ON s.employee_id = e.employee_id
                        WHERE s.sale_id = ? LIMIT 1"
                    );
                    $d->execute([$saleId]);
                    $info = $d->fetch(PDO::FETCH_ASSOC);
                    if ($info) {
                        $custName = trim(($info['c_first'] ?? '') . ' ' . ($info['c_last'] ?? ''));
                        $empName  = trim(($info['e_first'] ?? '') . ' ' . ($info['e_last'] ?? ''));
                        $vehicleLabel = trim(($info['model_year'] ?? '') . ' ' . ($info['make'] ?? '') . ' ' . ($info['model'] ?? ''));
                        $vin = $info['vin'] ?? '';
                        $price = number_format((float)($info['sale_price'] ?? 0), 2, '.', '');
                        $msg = sprintf("Sale recorded: %s (VIN: %s) sold to %s by %s for $%s", $vehicleLabel, $vin, $custName, $empName, $price);
                    }
                } catch (Throwable $ignore) {
                    // ignore lookup failures
                }
            }

            header('Location: ' . BASE_URL . '/dashboard.php?msg=' . urlencode($msg));
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $sqlState = $e->getCode();
            $driverErr = $e->errorInfo[1] ?? null; // MySQL numeric error code
            $driverMsg = $e->errorInfo[2] ?? $e->getMessage();
            if ($driverErr === 1062 || $sqlState === '23000') {
                // duplicate entry — vehicle likely already sold (uq_sale_vehicle)
                $errors[] = 'This vehicle has already been sold or reserved by another user. Please choose a different vehicle.';
            } else {
                $errors[] = 'Database error: ' . htmlspecialchars($driverMsg);
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Dropdown data
$employees = $pdo->query(
    "SELECT employee_id, first_name, last_name
    FROM employees
    WHERE is_active = 1
    ORDER BY last_name, first_name"
)->fetchAll(PDO::FETCH_ASSOC);

$customers = $pdo->query(
    "SELECT customer_id, first_name, last_name
    FROM customers
    ORDER BY last_name, first_name"
)->fetchAll(PDO::FETCH_ASSOC);

$vehicles = $pdo->query(
    "SELECT vehicle_id, make, model, model_year, vin
    FROM vehicles
    WHERE status = 'available'
    ORDER BY model_year DESC, make, model"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>New Sale</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body>
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <div class="content">
            <div class="header">
                <div class="title">New Sale</div>
                <div class="right">
                    <div class="right"><a href="<?= BASE_URL ?>/dashboard.php">Return to Dashboard</a> • <a href="<?= BASE_URL ?>/logout.php">Logout</a></div>
                </div>
            </div>

            <?php if (!empty($_GET['msg'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul>
                </div>
            <?php endif; ?>

            <div class="two-col">
                <div class="card">
                    <div class="card-header">
                        <strong>Sale Details</strong>
                    </div>
                    <div class="card-body">
                        <form method="post" id="saleForm" class="form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="employee_id">Salesperson</label>
                                    <select name="employee_id" id="employee_id" required>
                                        <option value="">-- Select --</option>
                                        <?php foreach ($employees as $emp): ?>
                                            <option value="<?= (int)$emp['employee_id'] ?>"><?= htmlspecialchars($emp['last_name'] . ', ' . $emp['first_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="customer_id">Customer</label>
                                    <select name="customer_id" id="customer_id" required>
                                        <option value="">-- Select --</option>
                                        <?php foreach ($customers as $c): ?>
                                            <option value="<?= (int)$c['customer_id'] ?>"><?= htmlspecialchars($c['last_name'] . ', ' . $c['first_name']) ?></option>
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
                                            <option value="<?= (int)$v['vehicle_id'] ?>" <?= $preselectVehicleId === (int)$v['vehicle_id'] ? 'selected' : '' ?>><?= htmlspecialchars("{$v['model_year']} {$v['make']} {$v['model']} (VIN: {$v['vin']})") ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="sale_price">Sale Price (USD)</label>
                                    <div class="price-row">
                                        <input type="number" step="0.01" min="0" name="sale_price" id="sale_price" required>
                                        <div id="askingPriceInline" class="asking-price-highlight asking-price-inline" aria-live="polite">$0.00</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="payment_method">Payment Method</label>
                                    <select name="payment_method" id="payment_method" required>
                                        <option value="cash">Cash</option>
                                        <option value="finance" selected>Finance</option>
                                        <option value="lease">Lease</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="large-textarea" rows="8" placeholder="Optional notes..."></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <button class="btn btn-success" type="submit">Finalize Sale</button>
                                <a class="btn btn-secondary" href="<?= BASE_URL ?>/dashboard.php">Cancel</a>
                            </div>

                            <input type="hidden" id="__baseUrl" value="<?= htmlspecialchars(BASE_URL) ?>">
                        </form>
                    </div>
                </div>

                <div class="card hidden" id="custCard">
                    <div class="card-header"><strong>Selected Customer</strong></div>
                    <div class="card-body" id="custDetailsBody"></div>
                </div>

                <div class="card hidden" id="vehCard">
                    <div class="card-header"><strong>Vehicle Specs & Asking Price</strong></div>
                    <div class="card-body" id="vehDetailsBody"><!-- Filled by JS --></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const vehSel = document.getElementById('vehicle_id');
        const card = document.getElementById('vehCard');
        const body = document.getElementById('vehDetailsBody');
        const custSel = document.getElementById('customer_id');
        const custCard = document.getElementById('custCard');
        const custBody = document.getElementById('custDetailsBody');
        const BASE = document.getElementById('__baseUrl').value;

        async function loadVehDetails(id) {
            if (!id) { card.classList.add('hidden'); body.innerHTML = ''; return; }
            try {
                const res = await fetch(`${BASE}/get_vehicle.php?vehicle_id=${encodeURIComponent(id)}`, { cache: 'no-store' });
                const data = await res.json();
                if (data && data.vehicle_id) {
                    var imgHtml = '';
                    if (data.image_filename) {
                        var baseRoot = BASE.replace(/\/public\/?$/i, '');
                        var src = baseRoot + '/images/vehicles/' + encodeURIComponent(data.image_filename);
                        imgHtml = '<img class="vehicle-image-lrg" src="' + src + '" alt="Vehicle image">';
                    }
                    body.innerHTML = `\n                <div style="display:flex; gap:12px; align-items:flex-start">\n                    <div style="flex:0 0 170px">${imgHtml}</div>\n                    <div style="flex:1">\n                        <div class="specs-grid">\n                            <div><b>Year:</b> ${data.model_year ?? ''}</div>\n                            <div><b>Make:</b> ${data.make ?? ''}</div>\n                            <div><b>Model:</b> ${data.model ?? ''}</div>\n                            ${data.trim ? `<div><b>Trim:</b> ${data.trim}</div>` : ''}\n                            ${data.body_style ? `<div><b>Body Style:</b> ${data.body_style}</div>` : ''}\n                            ${data.transmission ? `<div><b>Transmission:</b> ${data.transmission}</div>` : ''}\n                            ${data.fuel_type ? `<div><b>Fuel Type:</b> ${data.fuel_type}</div>` : ''}\n                            ${data.color ? `<div><b>Color:</b> ${data.color}</div>` : ''}\n                            ${data.mileage != null ? `<div><b>Mileage:</b> ${Number(data.mileage).toLocaleString()}</div>` : ''}\n                            ${data.vin ? `<div><b>VIN:</b> ${data.vin}</div>` : ''}\n                            ${data.location ? `<div><b>Location:</b> ${data.location}</div>` : ''}\n                        </div>\n                        <div class="asking-price" style="margin-top:8px"><b>Asking Price:</b> $${Number(data.price ?? 0).toFixed(2)}</div>\n                    </div>\n                </div>`;
                    var askInline = document.getElementById('askingPriceInline'); if (askInline) askInline.textContent = '$' + Number(data.price ?? 0).toFixed(2);
                    var saleInput = document.getElementById('sale_price'); if (saleInput && (!saleInput.value || Number(saleInput.value) === 0)) saleInput.value = Number(data.price ?? 0).toFixed(2);
                    card.classList.remove('hidden');
                } else { card.classList.add('hidden'); body.innerHTML = ''; }
            } catch (e) { console.error(e); }
        }

        function showCustomer(name) { if (!name) { custCard.classList.add('hidden'); custBody.innerHTML = ''; return; } custBody.innerHTML = `<div><b>${name}</b></div>`; custCard.classList.remove('hidden'); }

        custSel.addEventListener('change', async function(e) {
            var opt = e.target.options[e.target.selectedIndex]; var id = opt && opt.value ? opt.value : 0; if (!id) { showCustomer(''); return; }
            try {
                const res = await fetch(`${BASE}/get_customer.php?customer_id=${encodeURIComponent(id)}`, { cache: 'no-store' });
                const data = await res.json();
                if (data && data.customer_id) {
                    var html = `<div><b>${(data.first_name||'') + ' ' + (data.last_name||'')}</b></div>`;
                    if (data.email) html += `<div><a href="mailto:${data.email}">${data.email}</a></div>`;
                    if (data.phone) html += `<div><a href="tel:${data.phone}">${data.phone}</a></div>`;
                    if (data.address_line1) html += `<div>${data.address_line1}</div>`;
                    if (data.address_line2) html += `<div>${data.address_line2}</div>`;
                    if (data.city || data.state || data.postal_code) html += `<div>${[data.city, data.state, data.postal_code].filter(Boolean).join(', ')}</div>`;
                    custBody.innerHTML = html; custCard.classList.remove('hidden');
                } else showCustomer(opt && opt.text ? opt.text : '');
            } catch (err) { console.error(err); showCustomer(opt && opt.text ? opt.text : ''); }
        });

        if (custSel && custSel.value) (async function(){ var opt0 = custSel.options[custSel.selectedIndex]; var id = opt0 && opt0.value ? opt0.value : 0; if (!id) { showCustomer(opt0 && opt0.text ? opt0.text : ''); return; } try { const res = await fetch(`${BASE}/get_customer.php?customer_id=${encodeURIComponent(id)}`, { cache: 'no-store' }); const data = await res.json(); if (data && data.customer_id) { var html = `<div><b>${(data.first_name||'') + ' ' + (data.last_name||'')}</b></div>`; if (data.email) html += `<div><a href="mailto:${data.email}">${data.email}</a></div>`; if (data.phone) html += `<div><a href="tel:${data.phone}">${data.phone}</a></div>`; if (data.address_line1) html += `<div>${data.address_line1}</div>`; if (data.address_line2) html += `<div>${data.address_line2}</div>`; if (data.city || data.state || data.postal_code) html += `<div>${[data.city, data.state, data.postal_code].filter(Boolean).join(', ')}</div>`; custBody.innerHTML = html; custCard.classList.remove('hidden'); } else showCustomer(opt0 && opt0.text ? opt0.text : ''); } catch (err) { console.error(err); showCustomer(opt0 && opt0.text ? opt0.text : ''); } })();

        vehSel.addEventListener('change', e => loadVehDetails(e.target.value));
        if (vehSel.value) loadVehDetails(vehSel.value);

        const saleForm = document.getElementById('saleForm');
        if (saleForm) {
            saleForm.addEventListener('submit', function(e){
                const emp = document.getElementById('employee_id');
                const cust = document.getElementById('customer_id');
                const veh = document.getElementById('vehicle_id');
                const priceEl = document.getElementById('sale_price');
                const empTxt = emp && emp.options[emp.selectedIndex] ? emp.options[emp.selectedIndex].text : '';
                const custTxt = cust && cust.options[cust.selectedIndex] ? cust.options[cust.selectedIndex].text : '';
                const vehTxt = veh && veh.options[veh.selectedIndex] ? veh.options[veh.selectedIndex].text : '';
                const priceTxt = priceEl && priceEl.value ? Number(priceEl.value).toFixed(2) : '0.00';
                const pmEl = document.getElementById('payment_method');
                const pmTxt = pmEl && pmEl.options[pmEl.selectedIndex] ? pmEl.options[pmEl.selectedIndex].text : 'Finance';
                const msg = `Confirm sale?\n\nSalesperson: ${empTxt}\nCustomer: ${custTxt}\nVehicle: ${vehTxt}\nPrice: $${priceTxt}\nPayment method: ${pmTxt}\n\nProceed to record this sale?`;
                if (!window.confirm(msg)) { e.preventDefault(); return false; }
            });
        }
    </script>
</body>

</html>