<?php
include '../../config.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid doctor ID']);
    exit();
}

$doctor_id = (int)$_GET['id'];

// JOIN users table to get status
$query = "SELECT d.*, u.username, u.status as user_status 
          FROM doctors d
          LEFT JOIN users u ON d.user_id = u.user_id
          WHERE d.doctor_id = $doctor_id AND d.approve = 1";

$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    echo json_encode(['status' => true, 'data' => $data]);
} else {
    echo json_encode(['status' => false, 'message' => 'Doctor not found']);
}
?>