<?php
require_once __DIR__ . '/bootstrap.php';

// require auth
if (!auth_check()) redirect('/login.php');

$pdo = DB::conn();
$errors = [];

/* helper to detect optional columns in the customers table */
function has_col(PDO $pdo, string $table, string $col): bool
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
    'address_line1' => has_col($pdo, 'customers', 'address_line1'),
    'address_line2' => has_col($pdo, 'customers', 'address_line2'),
    'city'          => has_col($pdo, 'customers', 'city'),
    // some schemas use 'state_province'
    'state'         => has_col($pdo, 'customers', 'state') || has_col($pdo, 'customers', 'state_province'),
    'postal_code'   => has_col($pdo, 'customers', 'postal_code'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postal = trim($_POST['postal_code'] ?? '');

    if ($first === '') $errors[] = 'First name is required.';
    if ($last === '') $errors[] = 'Last name is required.';

    if (empty($errors)) {
        try {
            // build insert dynamically depending on available columns
            $insertCols = ['first_name', 'last_name', 'email', 'phone'];
            $placeholders = ['?', '?', '?', '?'];
            $values = [$first, $last, $email, $phone];

            if ($cols['address_line1']) {
                $insertCols[] = 'address_line1';
                $placeholders[] = '?';
                $values[] = $address_line1;
            }
            if ($cols['address_line2']) {
                $insertCols[] = 'address_line2';
                $placeholders[] = '?';
                $values[] = $address_line2;
            }
            if ($cols['city']) {
                $insertCols[] = 'city';
                $placeholders[] = '?';
                $values[] = $city;
            }
            if ($cols['state']) {
                // prefer state column if exists, otherwise state_province
                if (has_col($pdo, 'customers', 'state')) {
                    $insertCols[] = 'state';
                } else {
                    $insertCols[] = 'state_province';
                }
                $placeholders[] = '?';
                $values[] = $state;
            }
            if ($cols['postal_code']) {
                $insertCols[] = 'postal_code';
                $placeholders[] = '?';
                $values[] = $postal;
            }

            $sql = 'INSERT INTO customers (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', $placeholders) . ')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            redirect('customers.php?msg=' . urlencode('Customer added'));
        } catch (Throwable $e) {
            $errors[] = 'Error: ' . e($e->getMessage());
        }
    }
}

?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Add Customer</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body class="page-customers">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Add Customer</div>
                <div class="right"><a href="<?= BASE_URL ?>/customers.php">Back to Customers</a></div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="card alert-error mb-12">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="post">
                    <div class="grid-2">
                        <div>
                            <label>First name</label>
                            <input name="first_name" value="<?= e($_POST['first_name'] ?? '') ?>" required>
                        </div>
                        <div>
                            <label>Last name</label>
                            <input name="last_name" value="<?= e($_POST['last_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="grid-3">
                        <div>
                            <label>Email</label>
                            <input name="email" type="email" value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Phone</label>
                            <input name="phone" value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                        <div></div>
                    </div>

                    <?php if ($cols['address_line1']): ?>
                        <label>Address line 1</label>
                        <input name="address_line1" value="<?= e($_POST['address_line1'] ?? '') ?>">
                    <?php endif; ?>

                    <?php if ($cols['address_line2']): ?>
                        <label>Address line 2</label>
                        <input name="address_line2" value="<?= e($_POST['address_line2'] ?? '') ?>">
                    <?php endif; ?>

                    <div class="grid-3">
                        <?php if ($cols['city']): ?>
                            <div>
                                <label>City</label>
                                <input name="city" value="<?= e($_POST['city'] ?? '') ?>">
                            </div>
                        <?php endif; ?>

                        <?php if ($cols['state']): ?>
                            <div>
                                <label>State / Province</label>
                                <input name="state" value="<?= e($_POST['state'] ?? '') ?>">
                            </div>
                        <?php endif; ?>

                        <?php if ($cols['postal_code']): ?>
                            <div>
                                <label>Postal code</label>
                                <input name="postal_code" value="<?= e($_POST['postal_code'] ?? '') ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-12">
                        <button>Add Customer</button>
                        <a class="btn secondary" href="<?= BASE_URL ?>/customers.php">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
