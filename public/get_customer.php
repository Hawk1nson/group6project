<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once __DIR__ . '/bootstrap.php';

$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
if ($customer_id <= 0) {
    echo json_encode(['error' => 'bad id']);
    exit;
}

// Get PDO connection from shared bootstrap wrapper
$pdo = DB::conn();

// Some DB schemas use 'state_province' — alias to 'state' so client code can expect `state`
$stmt = $pdo->prepare(
    "SELECT customer_id, first_name, last_name, email, phone, address_line1, address_line2, city, state_province AS state, postal_code, created_at
        FROM customers WHERE customer_id = ? LIMIT 1"
);
$stmt->execute([$customer_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['error' => 'not found']);
    exit;
}

// Normalize field names for client (ensure keys exist, even if null)
$out = [
    'customer_id' => (int)$row['customer_id'],
    'first_name'  => $row['first_name'] ?? null,
    'last_name'   => $row['last_name'] ?? null,
    'email'       => $row['email'] ?? null,
    'phone'       => $row['phone'] ?? null,
    'address_line1' => $row['address_line1'] ?? null,
    'address_line2' => $row['address_line2'] ?? null,
    'city'        => $row['city'] ?? null,
    'state'       => $row['state'] ?? null,
    'postal_code' => $row['postal_code'] ?? null,
    'created_at'  => $row['created_at'] ?? null,
];

echo json_encode($out);
