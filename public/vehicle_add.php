<?php
require_once __DIR__ . '/bootstrap.php';

// require auth
if (!auth_check()) redirect('/login.php');

$pdo = DB::conn();
$errors = [];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vin = trim($_POST['vin'] ?? '');
    $make = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['model_year'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    // image filename is provided via file upload only
    $img = '';

    // basic validation
    if ($vin === '') $errors[] = 'VIN is required.';
    if ($make === '') $errors[] = 'Make is required.';
    if ($model === '') $errors[] = 'Model is required.';

    try {
        // check VIN uniqueness (case-insensitive)
        if ($vin !== '') {
            $chk = $pdo->prepare('SELECT vehicle_id FROM vehicles WHERE LOWER(vin) = LOWER(?) LIMIT 1');
            $chk->execute([$vin]);
            $exists = $chk->fetch(PDO::FETCH_ASSOC);
            if ($exists) {
                $errors[] = 'A vehicle with that VIN already exists (ID #' . (int)$exists['vehicle_id'] . '). If you want to update it, please use Edit.';
            }
        }

        // only proceed with upload/insert if no validation errors
        if (empty($errors)) {
            // handle upload if present
            if (!empty($_FILES['image_file']['name'])) {
                $file = $_FILES['image_file'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('File upload error code: ' . $file['error']);
                }
                $maxBytes = 5 * 1024 * 1024;
                if ($file['size'] > $maxBytes) {
                    throw new RuntimeException('File too large (max 5MB)');
                }
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);
                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ];
                if (!isset($allowed[$mime])) {
                    throw new RuntimeException('Unsupported image type');
                }
                $ext = $allowed[$mime];
                $targetDir = rtrim(APP_ROOT, '/') . '/images/vehicles';
                if (!is_dir($targetDir)) {
                    if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                        throw new RuntimeException('Failed to create image directory');
                    }
                }
                if (!is_writable($targetDir)) {
                    $hint = "Upload directory not writable: $targetDir.\n" .
                        "Fix (macOS XAMPP example): sudo chown -R _www:_www " . APP_ROOT . "/images/vehicles && sudo chmod -R 0755 " . APP_ROOT . "/images/vehicles";
                    throw new RuntimeException($hint);
                }
                $basename = sprintf('%s_%s.%s', time(), bin2hex(random_bytes(6)), $ext);
                $targetPath = $targetDir . '/' . $basename;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new RuntimeException('Failed to move uploaded file');
                }
                $img = $basename;
            }

            $ins = $pdo->prepare('INSERT INTO vehicles (vin, make, model, model_year, color, price, status, image_filename) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $ins->execute([$vin, $make, $model, $year, $color, $price, $status, $img]);
            $newId = (int)$pdo->lastInsertId();
            redirect('vehicles.php?msg=' . urlencode('Vehicle added'));
        }
    } catch (Throwable $e) {
        $errors[] = 'Error: ' . e($e->getMessage());
    }
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CDMS — Add Vehicle</title>
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>
<body class="page-vehicles">
    <div class="layout">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Add Vehicle</div>
                <div class="right"><a href="<?= BASE_URL ?>/vehicles.php">Back to Vehicles</a></div>
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
                <form method="post" enctype="multipart/form-data">
                    <label>VIN</label>
                    <input name="vin" value="<?= e($_POST['vin'] ?? '') ?>" required>

                    <label>Make</label>
                    <input name="make" value="<?= e($_POST['make'] ?? '') ?>" required>

                    <label>Model</label>
                    <input name="model" value="<?= e($_POST['model'] ?? '') ?>" required>

                    <div class="grid-3">
                        <div>
                            <label>Year</label>
                            <input type="number" name="model_year" value="<?= e($_POST['model_year'] ?? '') ?>" min="1900" max="2100">
                        </div>
                        <div>
                            <label>Color</label>
                            <input name="color" value="<?= e($_POST['color'] ?? '') ?>">
                        </div>
                        <div>
                            <label>Price</label>
                            <input type="number" step="0.01" name="price" value="<?= e($_POST['price'] ?? '') ?>">
                        </div>
                    </div>

                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['available', 'reserved', 'sold'] as $s): ?>
                            <option value="<?= $s ?>" <?= (($_POST['status'] ?? 'available') === $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="mt-10">Upload image <span class="note">(optional, JPEG/PNG/GIF/WebP, max 5MB)</span></label>
                    <input type="file" name="image_file" accept="image/*">

                    <div class="mt-12">
                        <button>Add Vehicle</button>
                        <a class="btn secondary" href="<?= BASE_URL ?>/vehicles.php">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
