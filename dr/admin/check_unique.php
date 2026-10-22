<?php
include '../config.php';

$field     = $_POST['field'] ?? '';
$value     = trim($_POST['value'] ?? '');
$doctor_id = (int)($_POST['doctor_id'] ?? 0);
$user_id   = (int)($_POST['user_id'] ?? 0);

$exists = false;

if ($value !== '') {
    if ($field === 'cnic') {
        $cnic_digits = preg_replace('/\D/', '', $value);
        $q = mysqli_query($con, "SELECT 1 FROM doctors 
                                 WHERE REPLACE(cnic,'-','')='$cnic_digits' 
                                   AND doctor_id<>$doctor_id LIMIT 1");
        $exists = $q && mysqli_num_rows($q) > 0;
    }
    elseif ($field === 'email') {
        $v = mysqli_real_escape_string($con, $value);
        $q = mysqli_query($con, "SELECT 1 FROM doctors 
                                 WHERE doctor_email='$v' 
                                   AND doctor_id<>$doctor_id LIMIT 1");
        $exists = $q && mysqli_num_rows($q) > 0;
    }
    elseif ($field === 'username') {
        $v = mysqli_real_escape_string($con, $value);
        $q = mysqli_query($con, "SELECT 1 FROM users 
                                 WHERE username='$v' 
                                   AND user_id<>$user_id LIMIT 1");
        $exists = $q && mysqli_num_rows($q) > 0;
    }
    elseif ($field === 'pmdc_no') {
        // Case-insensitive: 1001-n == 1001-N
        $v = mysqli_real_escape_string($con, strtoupper($value));
        $q = mysqli_query($con, "SELECT 1 FROM doctors 
                                 WHERE UPPER(pmdc_no)='$v' 
                                   AND doctor_id<>$doctor_id LIMIT 1");
        $exists = $q && mysqli_num_rows($q) > 0;
    }
}

header('Content-Type: application/json');
echo json_encode(['exists' => $exists]);