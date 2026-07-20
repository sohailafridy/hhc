<?php
include '../config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['city_id']) || empty($data['city_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'City not received']);
    exit;
}

$city_id = (int)$data['city_id'];
$result = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE city_id = $city_id LIMIT 1");

if ($result && $row = mysqli_fetch_assoc($result)) {
    $_SESSION['city'] = $row['city_name'];
    $_SESSION['city_id'] = (int)$row['city_id'];
    echo json_encode([
        'success' => true,
        'city' => $row['city_name'],
        'city_id' => $row['city_id']
    ]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'City not found']);
}
