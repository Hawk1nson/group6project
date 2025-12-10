<?php
// Returns latest reservation-related event for dealership staff
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (!auth_check()) {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$latest = null;
try {
    $logFile = APP_ROOT . '/storage/logs/contact_messages.log';
    if (is_file($logFile)) {
        // Read last 80 lines to find most recent reservation event
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -80);
        $lines = array_reverse($lines);
        foreach ($lines as $line) {
            $data = @json_decode($line, true);
            if (!$data || !is_array($data)) continue;
            $tag = $data['tag'] ?? '';
            if (strcasecmp((string)$tag, 'reservation') !== 0) continue;
            $ts = $data['ts'] ?? null;
            if (!$ts) continue;
            $status = 'new';
            $msgTxt = strtolower($data['message'] ?? '');
            if (str_contains($msgTxt, 'cancel')) {
                $status = 'cancelled';
            } elseif (!empty($data['status'])) {
                $status = strtolower((string)$data['status']);
            }
            $veh = '';
            if (!empty($data['vehicle'])) {
                $v = $data['vehicle'];
                if (isset($v['label'])) {
                    $veh = $v['label'];
                } else {
                    $veh = trim(($v['model_year'] ?? '') . ' ' . ($v['make'] ?? '') . ' ' . ($v['model'] ?? ''));
                }
            }
            $latest = [
                'ts' => $ts,
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'status' => $status,
                'vehicle' => $veh,
                'message' => $data['message'] ?? '',
            ];
            break;
        }
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error']);
    exit;
}

if (!$latest) {
    echo json_encode(['none' => true]);
    exit;
}

echo json_encode($latest);
