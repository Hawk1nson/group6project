<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
header('Content-Type: application/json');
require_once __DIR__ . '/bootstrap.php';

$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
if ($vehicle_id <= 0) { echo json_encode(['error' => 'bad id']); exit; }

$stmt = $pdo->prepare("
    SELECT
        vehicle_id, vin, make, model, trim, model_year,
        color, body_style, transmission, fuel_type,
        mileage, price, status, location, image_filename, image_url
    FROM vehicles
    WHERE vehicle_id = ?
");
$stmt->execute([$vehicle_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo $row ? json_encode($row) : json_encode(['error' => 'not found']);