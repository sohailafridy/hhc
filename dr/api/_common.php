<?php
// api/_common.php - shared helpers for all mobile APIs
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// TODO: apni existing DB connection file include karein ($conn = mysqli object hona chahiye)
require_once __DIR__ . '/../config.php';
$conn->set_charset('utf8mb4');

define('BASE_URL', 'https://hassanhomehealthcare.com/dr/');
define('DOCTOR_IMG_URL', BASE_URL . 'admin/inc/uploads/doctors/');

function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $msg, int $code = 400): void {
    json_out(['success' => false, 'message' => $msg], $code);
}

function photo_url(?string $file): ?string {
    return $file ? DOCTOR_IMG_URL . $file : null;
}

// Prepared statement helper: returns all rows as assoc array
function db_all(mysqli $c, string $sql, string $types = '', array $params = []): array {
    $st = $c->prepare($sql);
    if (!$st) json_error('Query error', 500);
    if ($types !== '') $st->bind_param($types, ...$params);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $rows;
}

function db_one(mysqli $c, string $sql, string $types = '', array $params = []): ?array {
    $rows = db_all($c, $sql, $types, $params);
    return $rows[0] ?? null;
}

// Reads JSON body or form-data
function request_body(): array {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    return is_array($json) ? $json : $_POST;
}