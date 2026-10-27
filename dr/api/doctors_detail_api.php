<?php
// get_doctor_detail.php
include '../config.php';

$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;

if ($doctor_id == 0) {
    echo json_encode(["status" => false, "message" => "Doctor ID is required"]);
    exit;
}

// 1. Doctor ki Basic Info aur Hospital & Clinical Info
$query = "SELECT 
            u.user_id AS doctor_id, 
            u.username AS doctor_name, 
            u.reference AS qualification,
            c.cat_name AS specialty,
            h.hospital_name,
            h.hospital_address,
            cl.morning_opening_time, cl.morning_closing_time,
            cl.evening_opening_time, cl.evening_closing_time,
            cl.days AS working_days, cl.off_days, cl.contact, cl.detail
          FROM `users` u
          LEFT JOIN `dr_categories` c ON c.dr_cat_id = u.user_type_id
          LEFT JOIN `doctor_in_hospital` dh ON dh.doctor_id = u.user_id
          LEFT JOIN `hospitals` h ON h.hospital_id = dh.hospital_id
          LEFT JOIN `clinical_info` cl ON cl.doctor_in_hosp_id = dh.doctor_in_hosp_id
          WHERE u.user_id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    echo json_encode(["status" => false, "message" => "Doctor not found"]);
    exit;
}

// Data format karna (Morning / Evening time merge karna)
$clinical_info = [
    "morning_time" => $doctor['morning_opening_time'] . " - " . $doctor['morning_closing_time'],
    "evening_time" => $doctor['evening_opening_time'] . " - " . $doctor['evening_closing_time'],
    "working_days" => $doctor['working_days'],
    "off_days" => $doctor['off_days'],
    "contact" => $doctor['contact'],
    "detail" => !empty($doctor['detail']) ? $doctor['detail'] : "No detail right now"
];

// 2. Reviews Fetch Karna
$review_query = "SELECT 
                    f.feedback_id, 
                    u.username AS user_name, 
                    u.email AS user_email, 
                    f.comment, 
                    f.stars AS rating, 
                    DATE_FORMAT(f.created_at, '%d %b %Y') AS date
                 FROM `feedback` f
                 JOIN `users` u ON u.user_id = f.user_id
                 WHERE f.entity_id = ? 
                 ORDER BY f.created_at DESC";
$rev_stmt = $pdo->prepare($review_query);
$rev_stmt->execute([$doctor_id]);
$reviews = $rev_stmt->fetchAll(PDO::FETCH_ASSOC);

// Final JSON Response
$response = [
    "status" => true,
    "data" => [
        "doctor_id" => $doctor['doctor_id'],
        "doctor_name" => $doctor['doctor_name'],
        "qualification" => $doctor['qualification'],
        "specialty" => $doctor['specialty'],
        "hospital" => [
            "name" => $doctor['hospital_name'],
            "address" => $doctor['hospital_address']
        ],
        "clinical_info" => $clinical_info,
        "reviews" => $reviews
    ]
];

echo json_encode($response);
?>