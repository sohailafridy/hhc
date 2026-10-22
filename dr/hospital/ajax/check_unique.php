<?php
// Save as: hospital/ajax/check_unique.php
include '../../config.php';
header('Content-Type: application/json');

// Session check (agar user login nahi hai to false return karega)
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    echo json_encode(['exists' => false]);
    exit();
}

$field = $_POST['field'] ?? '';
$value = mysqli_real_escape_string($con, trim($_POST['value'] ?? ''));
$exists = false;
$q = null;

if ($value !== '') {
    if ($field === 'cnic') {
        $d = preg_replace('/\D/', '', $value);
        if ($d !== '') $q = "SELECT 1 FROM doctors WHERE REPLACE(cnic,'-','')='$d' LIMIT 1";
    } 
    elseif ($field === 'email') {
        $q = "SELECT 1 FROM doctors WHERE doctor_email='$value'
              UNION SELECT 1 FROM users WHERE email='$value' LIMIT 1";
    } 
    elseif ($field === 'username') {
        $q = "SELECT 1 FROM users WHERE username='$value' LIMIT 1";
    } 
    // ===== NEW: PMDC NO CHECK =====
    elseif ($field === 'pmdc_no') {
        // Uppercase me convert karke check karein (1001-n == 1001-N)
        $v = mysqli_real_escape_string($con, strtoupper($value));
        $q = "SELECT 1 FROM doctors WHERE UPPER(pmdc_no)='$v' LIMIT 1";
    }
    // ===============================

    if ($q) {
        $r = mysqli_query($con, $q);
        $exists = $r && mysqli_num_rows($r) > 0;
    }
}
echo json_encode(['exists' => $exists]);