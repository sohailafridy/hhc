<?php
// get_doctors.php
require_once __DIR__ . '/../config.php';

$city_id = isset($_GET['city_id']) ? $_GET['city_id'] : '';
$hospital_id = isset($_GET['hospital_id']) ? $_GET['hospital_id'] : '';
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';

// Base Query (Users -> Hospital -> City -> Category)
$query = "SELECT 
            u.user_id AS doctor_id, 
            u.username AS doctor_name, 
            u.reference AS qualification, 
            c.cat_name AS specialty,
            ci.city_name,
            h.hospital_name,
            u.status AS is_available
          FROM `users` u
          LEFT JOIN `dr_categories` c ON c.dr_cat_id = u.user_type_id
          LEFT JOIN `doctor_in_hospital` dh ON dh.doctor_id = u.user_id
          LEFT JOIN `hospitals` h ON h.hospital_id = dh.hospital_id
          LEFT JOIN `cities` ci ON ci.city_id = h.city_id
          WHERE u.user_type_id = 2 AND u.status = 1"; // Assuming user_type_id = 2 is for doctors

$params = [];

if (!empty($city_id)) {
    $query .= " AND ci.city_id = ?";
    $params[] = $city_id;
}
if (!empty($hospital_id)) {
    $query .= " AND h.hospital_id = ?";
    $params[] = $hospital_id;
}
if (!empty($category_id)) {
    $query .= " AND c.dr_cat_id = ?";
    $params[] = $category_id;
}

$query .= " GROUP BY u.user_id ORDER BY u.username ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($doctors) {
    echo json_encode(["status" => true, "data" => $doctors]);
} else {
    echo json_encode(["status" => false, "message" => "No doctors found"]);
}
?>
