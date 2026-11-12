<?php
// ============================================================
// AJAX DUPLICATE CHECKER
// Location: admin/check_duplicate.php
// ============================================================
include '../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$type       = $_GET['type']  ?? '';
$value      = trim($_GET['value'] ?? '');
$exclude_id = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;

if ($value === '' || !in_array($type, ['reg_no', 'username'], true)) {
    echo json_encode(['exists' => false]);
    exit;
}

$value_safe = mysqli_real_escape_string($con, $value);
$exists = false;

if ($type === 'reg_no') {
    $sql = "SELECT rad_id FROM radiology WHERE reg_no = '$value_safe'";
    if ($exclude_id > 0) $sql .= " AND rad_id != $exclude_id";
    $sql .= " LIMIT 1";
    $r = mysqli_query($con, $sql);
    $exists = ($r && mysqli_num_rows($r) > 0);
} elseif ($type === 'username') {
    $sql = "SELECT user_id FROM users WHERE username = '$value_safe'";
    if ($exclude_id > 0) $sql .= " AND user_id != $exclude_id";
    $sql .= " LIMIT 1";
    $r = mysqli_query($con, $sql);
    $exists = ($r && mysqli_num_rows($r) > 0);
}

echo json_encode(['exists' => $exists]);
exit;