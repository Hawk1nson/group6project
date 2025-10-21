<?php
// NON-FUNCTIONAL CURRENTLY
// Debug and get working for phase 2

if (!auth_check()) redirect('../../login.php');
$pdo = DB::conn();
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM vehicles WHERE vehicle_id=? LIMIT 1');
$st->execute([$id]);
$veh = $st->fetch();
if (!$veh) {
    echo 'Vehicle not found';
    exit;
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vin = trim($_POST['vin'] ?? '');
    $make = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['model_year'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $img = trim($_POST['image_filename'] ?? '');
    try {
        $u = $pdo->prepare('UPDATE vehicles SET vin=?, make=?, model=?, model_year=?, color=?, price=?, status=?, image_filename=? WHERE vehicle_id=?');
        $u->execute([$vin, $make, $model, $year, $color, $price, $status, $img, $id]);
        $msg = 'Saved';
        $st->execute([$id]);
        $veh = $st->fetch();
    } catch (Throwable $e) {
        $msg = 'Error: ' . e($e->getMessage());
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Edit Vehicle</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>

<body>
    <div class="layout">
        <?php include __DIR__ . '/../_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Edit Vehicle #<?= (int)$id ?></div>
                <div class="right"><a href="index.php">Back to Vehicles</a></div>
            </div>
            <?php if ($msg): ?><div class="card" style="margin-bottom:12px;"><?= e($msg) ?></div><?php endif; ?>
            <div class="card">
                <form method="post" class="form">
                    <label>VIN</label>
                    <input name="vin" value="<?= e($veh['vin']) ?>" required>
                    <label>Make</label>
                    <input name="make" value="<?= e($veh['make']) ?>" required>
                    <label>Model</label>
                    <input name="model" value="<?= e($veh['model']) ?>" required>
                    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px;">
                        <div>
                            <label>Year</label>
                            <input type="number" name="model_year" value="<?= (int)$veh['model_year'] ?>" min="1900" max="2100">
                        </div>
                        <div>
                            <label>Color</label>
                            <input name="color" value="<?= e($veh['color']) ?>">
                        </div>
                        <div>
                            <label>Price</label>
                            <input type="number" step="0.01" name="price" value="<?= e($veh['price']) ?>">
                        </div>
                    </div>
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['available', 'reserved', 'sold'] as $s): ?>
                            <option value="<?= $s ?>" <?= $veh['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Image Filename <span class="note">(optional, shown in table)</span></label>
                    <input name="image_filename" value="<?= e($veh['image_filename'] ?? '') ?>" placeholder="/images/vehicles/coming_soon.png">
                    <div style="margin-top:12px">
                        <button>Save</button>
                        <a class="btn secondary" href="index.php">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>