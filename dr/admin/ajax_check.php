<?php
include '../config.php'; // admin/ folder se ek step bahar root me config.php hai

// JSON response header (is se HTML error aana band ho jayega)
header('Content-Type: application/json');

// Agar database connect nahi hua to error return karein
if (!$con) {
    echo json_encode(['exists' => false, 'error' => 'DB connection failed']);
    exit;
}

if (isset($_POST['field']) && isset($_POST['value'])) {
    $field = $_POST['field'];
    $value = mysqli_real_escape_string($con, trim($_POST['value']));
    $exclude_id = isset($_POST['exclude_id']) ? (int)$_POST['exclude_id'] : 0;
    
    $exists = false;

    if ($field === 'reg_no') {
        $query = "SELECT lab_id FROM laboratories WHERE reg_no = '$value'";
        if ($exclude_id > 0) {
            $query .= " AND lab_id != $exclude_id";
        }
        $result = mysqli_query($con, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $exists = true;
        }
    } elseif ($field === 'username') {
        $query = "SELECT user_id FROM users WHERE username = '$value'";
        if ($exclude_id > 0) {
            $query .= " AND user_id != (SELECT user_id FROM laboratories WHERE lab_id = $exclude_id)";
        }
        $result = mysqli_query($con, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $exists = true;
        }
    }

    echo json_encode(['exists' => $exists]);
    exit;
} else {
    echo json_encode(['exists' => false, 'error' => 'Invalid request']);
    exit;
}
?>