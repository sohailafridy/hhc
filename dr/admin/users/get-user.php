<?php
include '../../config.php';
header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid user ID']);
    exit();
}

$user_id = (int)$_GET['id'];

$query = "SELECT u.*, ut.type as user_type,
                 (SELECT COUNT(*) FROM entities WHERE user_id = u.user_id) as entity_count
          FROM users u
          LEFT JOIN usertypes ut ON u.user_type_id = ut.usertypes_id
          WHERE u.user_id = $user_id";

$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    echo json_encode(['status' => true, 'data' => $user]);
} else {
    echo json_encode(['status' => false, 'message' => 'User