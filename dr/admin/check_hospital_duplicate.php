<?php
include '../config.php';
include '../check_auth.php';

header('Content-Type: application/json');

$field = $_GET['field'] ?? '';
$value = trim($_GET['value'] ?? '');
$exclude_id = (int)($_GET['exclude_id'] ?? 0);

if ($value === '' || !in_array($field, ['username', 'reg_no'])) {
    echo json_encode(['exists' => false, 'error' => 'Invalid input']);
    exit;
}

$value_esc = mysqli_real_escape_string($con, $value);

if ($field === 'username') {
    $q = "SELECT user_id FROM users WHERE username = '$value_esc' LIMIT 1";
} else {
    $q = "SELECT hospital_id FROM hospitals WHERE reg_no = '$value_esc'";
    if ($exclude_id > 0) $q .= " AND hospital_id != $exclude_id";
    $q .= " LIMIT 1";
}

$r = mysqli_query($con, $q);
echo json_encode(['exists' => mysqli_num_rows($r) > 0]);