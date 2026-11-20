<?php
// ============================================================
// AJAX Get Hospitals By City
// ============================================================
include_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['ajax'])) {
    echo json_encode(['success' => false, 'hospitals' => []]);
    exit();
}

$city_id = isset($_POST['city_id']) ? (int)$_POST['city_id'] : 0;

if ($city_id <= 0) {
    echo json_encode(['success' => false, 'hospitals' => []]);
    exit();
}

$query = "SELECT DISTINCT h.hospital_id, h.hospital_name 
          FROM hospitals h 
          LEFT JOIN users u ON u.user_id = h.user_id
          WHERE u.status = 1 AND h.approve = 1 AND h.city_id = $city_id
          ORDER BY h.hospital_name ASC";

$result = mysqli_query($con, $query);

$hospitals = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $hospitals[] = [
            'hospital_id'   => (int)$row['hospital_id'],
            'hospital_name' => $row['hospital_name'],
        ];
    }
}

echo json_encode([
    'success'   => true,
    'hospitals' => $hospitals,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);