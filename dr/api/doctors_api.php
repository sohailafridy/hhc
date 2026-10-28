<?php
/**
 * Doctors API - Without Filters
 * Returns all approved doctors with their details
 * Method: GET
 * URL: /api/doctors_api.php
 */

// ============================================
// STEP 1: Headers & CORS Setup (Mobile App ke liye)
// ============================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// OPTIONS preflight request handle karein
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// STEP 2: Database Connection
// ============================================
// Apne path ke hisaab se adjust karein
include '../config.php';
// Agar $con nahi mil raha to direct connection lagayein:
// $con = mysqli_connect("localhost", "username", "password", "doctor");

if (!$con) {
    echo json_encode([
        "status" => false,
        "message" => "Database connection failed",
        "data" => []
    ]);
    exit();
}

mysqli_set_charset($con, 'utf8mb4');

// ============================================
// STEP 3: Main Query (Saara Data Ek Saath)
// ============================================
$query = "
    SELECT 
        d.doctor_id,
        d.doctor_name,
        d.doctor_pic,
        d.short_detail,
        d.gender,
        d.experience_years,
        d.emergency_status,
        d.cat_type_id,
        d.city_id,
        d.hospital_id,
        d.updated_at,
        dct.type AS specialization,
        c.city_name,
        h.hospital_name,
        h.hospital_address,
        u.user_id,
        u.email,
        u.status AS user_status
    FROM doctors d
    LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
    LEFT JOIN cities c ON d.city_id = c.city_id
    LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
    LEFT JOIN users u ON u.user_id = d.user_id
    WHERE u.status = 1 AND d.approve = 1
    ORDER BY d.doctor_name ASC
";

$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode([
        "status" => false,
        "message" => "Query failed: " . mysqli_error($con),
        "data" => []
    ]);
    exit();
}

// ============================================
// STEP 4: Data Prepare Karein
// ============================================
$doctors = [];

while ($row = mysqli_fetch_assoc($result)) {
    $doctor_id = (int)$row['doctor_id'];

    // Rating calculate karein (feedback table se)
    $rating_query = "SELECT AVG(stars) AS avg_stars, COUNT(*) AS total_reviews 
                     FROM feedback WHERE user_id = '$doctor_id'";
    $rating_result = mysqli_query($con, $rating_query);
    $rating_data = mysqli_fetch_assoc($rating_result);
    
    $avg_stars = $rating_data['avg_stars'] ? round((float)$rating_data['avg_stars'], 1) : 0;
    $total_reviews = (int)$rating_data['total_reviews'];

    // Doctor Pic ka full URL banayein
    $doctor_pic = '';
    if (!empty($row['doctor_pic'])) {
        $doctor_pic = BASE_URL . 'admin/inc/uploads/doctors/' . $row['doctor_pic'];
    }

    // Final Data Array
    $doctors[] = [
        "doctor_id"         => $doctor_id,
        "doctor_name"       => "Dr. " . $row['doctor_name'],
        "doctor_pic"        => $doctor_pic,
        "short_detail"      => $row['short_detail'] ?? '',
        "gender"            => $row['gender'] ?? '',
        "is_lady_doctor"    => (strtolower($row['gender'] ?? '') === 'female') ? 1 : 0,
        "experience_years"  => (int)($row['experience_years'] ?? 0),
        "emergency_status"  => (int)($row['emergency_status'] ?? 0),
        "availability"      => ($row['emergency_status'] == 1) ? "Unavailable" : "Available",
        "specialization_id" => (int)$row['cat_type_id'],
        "specialization"    => $row['specialization'] ?? 'Medical Specialist',
        "city_id"           => (int)$row['city_id'],
        "city_name"         => $row['city_name'] ?? '',
        "hospital_id"       => (int)$row['hospital_id'],
        "hospital_name"     => $row['hospital_name'] ?? '',
        "hospital_address"  => $row['hospital_address'] ?? '',
        "email"             => $row['email'] ?? '',
        "rating"            => $avg_stars,
        "total_reviews"     => $total_reviews,
        "updated_at"        => $row['updated_at'] ?? date('Y-m-d H:i:s')
    ];
}

// ============================================
// STEP 5: Filter Dropdowns ka Data (Cities, Hospitals, Specializations)
// ============================================
// Yeh data mobile app ko dropdowns populate karne ke liye chahiye

// Cities
$cities = [];
$city_q = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC");
while ($c = mysqli_fetch_assoc($city_q)) {
    $cities[] = [
        "city_id" => (int)$c['city_id'],
        "city_name" => $c['city_name']
    ];
}

// Hospitals
$hospitals = [];
$hosp_q = mysqli_query($con, "SELECT hospital_id, hospital_name, city_id FROM hospitals WHERE approve = 1 ORDER BY hospital_name ASC");
while ($h = mysqli_fetch_assoc($hosp_q)) {
    $hospitals[] = [
        "hospital_id" => (int)$h['hospital_id'],
        "hospital_name" => $h['hospital_name'],
        "city_id" => (int)$h['city_id']
    ];
}

// Specializations (Categories + Types)
$specializations = [];
$spec_q = mysqli_query($con, "
    SELECT dct.dr_cat_type_id, dct.type, dc.cat_name 
    FROM dr_cat_types dct 
    LEFT JOIN dr_categories dc ON dct.dr_cat_id = dc.dr_cat_id 
    ORDER BY dc.cat_name, dct.type
");
while ($s = mysqli_fetch_assoc($spec_q)) {
    $specializations[] = [
        "specialization_id" => (int)$s['dr_cat_type_id'],
        "specialization_name" => $s['type'],
        "category_name" => $s['cat_name'] ?? ''
    ];
}

// ============================================
// STEP 6: Final JSON Response
// ============================================
echo json_encode([
    "status" => true,
    "message" => "Doctors fetched successfully",
    "total_doctors" => count($doctors),
    "server_time" => date('Y-m-d H:i:s'),
    "data" => [
        "doctors" => $doctors,
        "filter_options" => [
            "cities" => $cities,
            "hospitals" => $hospitals,
            "specializations" => $specializations
        ]
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>