<?php
include '../config.php';
include '../check_auth.php';

header('Content-Type: application/json');

$field = $_POST['field'] ?? '';
$value = trim($_POST['value'] ?? '');

if ($value === '') {
    echo json_encode(['exists' => false]);
    exit();
}

$value_esc = mysqli_real_escape_string($con, $value);
$exists = false;

if ($field === 'username') {
    $q = mysqli_query($con, "SELECT 1 FROM users WHERE username = '$value_esc' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) $exists = true;
} elseif ($field === 'pmdc_no') {
    $q = mysqli_query($con, "SELECT 1 FROM doctors WHERE UPPER(pmdc_no) = UPPER('$value_esc') LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) $exists = true;
}

echo json_encode(['exists' => $exists]);
exit();