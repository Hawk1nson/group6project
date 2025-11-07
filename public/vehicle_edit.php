<?php
require_once __DIR__ . '/bootstrap.php';

// require auth before doing anything
if (!auth_check()) redirect('/login.php');

$pdo = DB::conn();
$id = (int)($_GET['id'] ?? 0);

// current user and role check
$u = auth_user();
$is_manager_or_admin = isset($u['role']) && in_array($u['role'], ['admin', 'manager'], true);

$st = $pdo->prepare('SELECT * FROM vehicles WHERE vehicle_id=? LIMIT 1');
$st->execute([$id]);
$veh = $st->fetch();
if (!$veh) {
    echo 'Vehicle not found';
    exit;
}
// message placeholder (used when update fails)
$msg = '';
// Handle POST: update then redirect back to vehicles list
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If delete action requested, remove the vehicle record (only admin/manager)
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (!$is_manager_or_admin) {
            $msg = 'You do not have permission to delete vehicles.';
        } else {
            try {
                // remove image file if exists
                $oldName = $veh['image_filename'] ?? '';
                if ($oldName !== '') {
                    $targetDir = rtrim(APP_ROOT, '/') . '/images/vehicles';
                    $oldBase = basename($oldName);
                    $oldPath = $targetDir . '/' . $oldBase;
                    if (is_file($oldPath)) @unlink($oldPath);
                }

                $d = $pdo->prepare('DELETE FROM vehicles WHERE vehicle_id = ?');
                $d->execute([$id]);
                redirect('vehicles.php?msg=' . urlencode('Vehicle deleted'));
            } catch (Throwable $e) {
                $msg = 'Error deleting vehicle: ' . e($e->getMessage());
            }
        }
    }
    $vin = trim($_POST['vin'] ?? '');
    $make = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['model_year'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    // keep existing image filename unless a new file is uploaded
    $img = trim($_POST['image_filename'] ?? '');

    try {
        // handle uploaded image (optional)
        if (!empty($_FILES['image_file']['name'])) {
            $file = $_FILES['image_file'];
            // basic upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('File upload error code: ' . $file['error']);
            }

            // limit size (5MB)
            $maxBytes = 5 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                throw new RuntimeException('File too large (max 5MB)');
            }

            // validate MIME type using finfo
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

            // ensure target directory exists: APP_ROOT . '/images/vehicles'
            $targetDir = rtrim(APP_ROOT, '/') . '/images/vehicles';
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                    throw new RuntimeException('Failed to create image directory');
                }
            }

            // generate unique filename
            $basename = sprintf('%s_%s.%s', time(), bin2hex(random_bytes(6)), $ext);
            $targetPath = $targetDir . '/' . $basename;

            // ensure directory is writable before moving the uploaded file
            if (!is_writable($targetDir)) {
                // helpful guidance for fixing permission problems
                $hint = "Upload directory not writable: $targetDir.\n" .
                    "Fix (macOS XAMPP example): sudo chown -R _www:_www " . APP_ROOT . "/images/vehicles && sudo chmod -R 0755 " . APP_ROOT . "/images/vehicles\n" .
                    "Or on many Linux systems: sudo chown -R www-data:www-data " . APP_ROOT . "/images/vehicles && sudo chmod -R 0755 " . APP_ROOT . "/images/vehicles";
                throw new RuntimeException($hint);
            }

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new RuntimeException('Failed to move uploaded file from ' . $file['tmp_name'] . ' to ' . $targetPath);
            }

            // delete previous image file (if any) so each vehicle has only one image
            $oldName = $veh['image_filename'] ?? '';
            if ($oldName !== '') {
                $oldBase = basename($oldName);
                $oldPath = $targetDir . '/' . $oldBase;
                if (is_file($oldPath) && $oldBase !== $basename) {
                    @unlink($oldPath);
                }
            }

            // set the image filename to the basename (vehicle_img_src will resolve path)
            $img = $basename;
        }

        $u = $pdo->prepare('UPDATE vehicles SET vin=?, make=?, model=?, model_year=?, color=?, price=?, status=?, image_filename=? WHERE vehicle_id=?');
        $u->execute([$vin, $make, $model, $year, $color, $price, $status, $img, $id]);

        // redirect to vehicles listing with success message (Post/Redirect/Get)
        redirect('vehicles.php?msg=' . urlencode('Vehicle saved'));
    } catch (Throwable $e) {
        // keep showing the form with an error message
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
    <?php include __DIR__ . '/../includes/header.php'; ?>
</head>

<body class="page-vehicles">
    <div class="layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
        <div class="content">
            <div class="header">
                <div class="title">Edit Vehicle #<?= (int)$id ?></div>
                <div class="right"><a href="<?= BASE_URL ?>/vehicles.php">Back to Vehicles</a></div>
            </div>
            <?php if ($msg): ?><div class="card mb-12"><?= e($msg) ?></div><?php endif; ?>
            <div class="card">
                <form method="post" class="form" enctype="multipart/form-data">
                    <label>VIN</label>
                    <input name="vin" value="<?= e($veh['vin']) ?>" required>
                    <label>Make</label>
                    <input name="make" value="<?= e($veh['make']) ?>" required>
                    <label>Model</label>
                    <input name="model" value="<?= e($veh['model']) ?>" required>
                    <div class="grid-3">
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
                    <?php
                    // show current image preview if available (larger on edit page)
                    $cur = $veh['image_filename'] ?? '';
                    $src = '';
                    if ($cur !== '') {
                        $src = vehicle_img_src($cur);
                    }
                    ?>
                    <?php if ($src): ?>
                        <div class="mt-8">
                            <a href="<?= e($src) ?>" target="_blank" rel="noopener">
                                <img class="thumb-img-xl" src="<?= e($src) ?>" alt="current image">
                            </a>
                        </div>
                    <?php endif; ?>

                    <label class="mt-10">Upload new image <span class="note">(optional, JPEG/PNG/GIF/WebP, max 5MB)</span></label>
                    <input type="file" name="image_file" accept="image/*">
                    <div class="mt-12">
                        <button>Save</button>
                        <a class="btn secondary" href="<?= BASE_URL ?>/vehicles.php">Cancel</a>
                        <!-- Delete button (will submit the separate hidden form below) -->
                        <?php if ($is_manager_or_admin): ?>
                            <button type="button" class="btn secondary" id="deleteBtn" style="margin-left:8px;">Delete</button>
                        <?php endif; ?>
                    </div>
                </form>
                <!-- hidden delete form must be outside the main form to avoid nested forms -->
                <?php if ($is_manager_or_admin): ?>
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
                if (confirm('Delete this vehicle? This action is permanent and cannot be undone. Are you sure?')) {
                    document.getElementById('deleteForm').submit();
                }
            });
        })();
    </script>
</body>

</html>