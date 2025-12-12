<?php
require_once __DIR__ . '/bootstrap.php';

// require auth
if (!auth_check()) redirect('/login.php');
$u = auth_user();
$is_admin = isset($u['role']) && $u['role'] === 'admin';

$pdo = DB::conn();
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ? LIMIT 1');
$st->execute([$id]);
$cust = $st->fetch(PDO::FETCH_ASSOC);
if (!$cust) {
    echo 'Customer not found';
    exit;
}

// helper to check optional columns
function has_col_local(PDO $pdo, string $table, string $col): bool
{
    static $cache = [];
    $key = "$table.$col";
    if (!isset($cache[$key])) {
        $cols = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN, 0);
        $cache[$key] = in_array($col, $cols, true);
    }
    return $cache[$key];
}

$cols = [
    'address_line1' => has_col_local($pdo, 'customers', 'address_line1'),
    'address_line2' => has_col_local($pdo, 'customers', 'address_line2'),
    'city'          => has_col_local($pdo, 'customers', 'city'),
    // detect either state or state_province
    'state'         => has_col_local($pdo, 'customers', 'state') || has_col_local($pdo, 'customers', 'state_province'),
    'postal_code'   => has_col_local($pdo, 'customers', 'postal_code'),
];

// determine which column to use for state if present
$state_col = has_col_local($pdo, 'customers', 'state') ? 'state' : (has_col_local($pdo, 'customers', 'state_province') ? 'state_province' : null);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // handle delete action
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!$is_admin) {
            $msg = 'You do not have permission to delete customers.';
        } elseif (empty($_POST['confirm_password'])) {
            $msg = 'Password confirmation is required to delete customers.';
        } else {
            try {
                $d = $pdo->prepare('DELETE FROM customers WHERE customer_id = ?');
                $d->execute([$id]);
                redirect('customers.php?msg=' . urlencode('Customer deleted'));
            } catch (Throwable $e) {
                $msg = 'Error deleting customer: ' . e($e->getMessage());
            }
        }
    }

    // regular update
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postal = trim($_POST['postal_code'] ?? '');

    try {
        // build update dynamically based on available columns
        $updateCols = ['first_name = ?', 'last_name = ?', 'email = ?', 'phone = ?'];
        $values = [$first, $last, $email, $phone];

        if ($cols['address_line1']) {
            $updateCols[] = 'address_line1 = ?';
            $values[] = $address_line1;
        }
        if ($cols['address_line2']) {
            $updateCols[] = 'address_line2 = ?';
            $values[] = $address_line2;
        }
        if ($cols['city']) {
            $updateCols[] = 'city = ?';
            $values[] = $city;
        }
        if ($state_col !== null) {
            $updateCols[] = "$state_col = ?";
            $values[] = $state;
        }
        if ($cols['postal_code']) {
            $updateCols[] = 'postal_code = ?';
            $values[] = $postal;
        }

        $values[] = $id; // for WHERE
        $sql = 'UPDATE customers SET ' . implode(', ', $updateCols) . ' WHERE customer_id = ?';
        $u = $pdo->prepare($sql);
        $u->execute($values);

        redirect('customers.php?msg=' . urlencode('Customer saved'));
    } catch (Throwable $e) {
        $msg = 'Error saving customer: ' . e($e->getMessage());
    }
}

?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Edit Customer</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body class="page-customers">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Edit Customer #<?= (int)$id ?></div>
                <div class="right"><a href="<?= BASE_URL ?>/customers.php">Back to Customers</a></div>
            </div>

            <?php if ($msg): ?><div class="card mb-12"><?= e($msg) ?></div><?php endif; ?>

            <div class="card">
                <form method="post" class="form">
                    <div class="grid-2">
                        <div>
                            <label>First name</label>
                            <input name="first_name" value="<?= e($_POST['first_name'] ?? $cust['first_name'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Last name</label>
                            <input name="last_name" value="<?= e($_POST['last_name'] ?? $cust['last_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="grid-3">
                        <div>
                            <label>Email</label>
                            <input name="email" type="email" value="<?= e($_POST['email'] ?? $cust['email'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Phone</label>
                            <input name="phone" value="<?= e($_POST['phone'] ?? $cust['phone'] ?? '') ?>">
                        </div>
                        <div></div>
                    </div>

                    <?php if ($cols['address_line1']): ?>
                        <label>Address line 1</label>
                        <input name="address_line1" value="<?= e($_POST['address_line1'] ?? $cust['address_line1'] ?? '') ?>">
                    <?php endif; ?>

                    <?php if ($cols['address_line2']): ?>
                        <label>Address line 2</label>
                        <input name="address_line2" value="<?= e($_POST['address_line2'] ?? $cust['address_line2'] ?? '') ?>">
                    <?php endif; ?>

                    <div class="grid-3">
                        <?php if ($cols['city']): ?>
                            <div>
                                <label>City</label>
                                <input name="city" value="<?= e($_POST['city'] ?? $cust['city'] ?? '') ?>">
                            </div>
                        <?php endif; ?>

                        <?php if ($state_col !== null): ?>
                            <div>
                                <label>State / Province</label>
                                <input name="state" value="<?= e($_POST['state'] ?? ($cust[$state_col] ?? '')) ?>">
                            </div>
                        <?php endif; ?>

                        <?php if ($cols['postal_code']): ?>
                            <div>
                                <label>Postal code</label>
                                <input name="postal_code" value="<?= e($_POST['postal_code'] ?? $cust['postal_code'] ?? '') ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-12">
                        <button>Save</button>
                        <a class="btn secondary" href="<?= BASE_URL ?>/customers.php">Cancel</a>
                        <?php if ($is_admin): ?>
                            <button type="button" class="btn secondary" id="deleteBtn" style="margin-left:8px;">Delete</button>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($is_admin): ?>
                    <form id="deleteForm" method="post" style="display:none;">
                        <input type="hidden" name="action" value="delete">
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        (function(){
            var del = document.getElementById('deleteBtn');
            if (!del) return;
            del.addEventListener('click', function(e){
                var first = confirm('Delete this customer? This cannot be undone.');
                if (!first) return;
                var pwd = prompt('To confirm, enter your password:');
                if (pwd === null || pwd === '') return;
                var form = document.getElementById('deleteForm');
                if (!form) return;
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'confirm_password';
                inp.value = pwd;
                form.appendChild(inp);
                form.submit();
            });
        })();
    </script>
</body>

</html>
