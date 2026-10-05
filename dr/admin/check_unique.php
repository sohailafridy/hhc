<?php
include '../config.php';
header('Content-Type: application/json');

$field     = $_POST['field'] ?? '';
$value     = trim($_POST['value'] ?? '');
$doctor_id = (int)($_POST['doctor_id'] ?? 0);   // edit mode me apna record exclude karne ke liye
$user_id   = (int)($_POST['user_id'] ?? 0);

$exists = false;

if ($field === 'cnic') {
    // dashes hata kar sirf digits compare hon
    $value = preg_replace('/\D/', '', $value);
}

if ($value !== '') {
    $sql = '';
    $exclude = 0;

    if ($field === 'cnic') {
        $sql = "SELECT 1 FROM doctors WHERE REPLACE(cnic,'-','') = ? AND doctor_id <> ? LIMIT 1";
        $exclude = $doctor_id;
    } elseif ($field === 'email') {
        $sql = "SELECT 1 FROM doctors WHERE doctor_email = ? AND doctor_id <> ? LIMIT 1";
        $exclude = $doctor_id;
    } elseif ($field === 'username') {
        $sql = "SELECT 1 FROM users WHERE username = ? AND user_id <> ? LIMIT 1";
        $exclude = $user_id;
    }

    if ($sql !== '') {
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $value, $exclude);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
    }
}

echo json_encode(['exists' => $exists]);