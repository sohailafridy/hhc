<?php
/**
 * Hospital Detail API
 * Returns complete details of a single hospital
 * Method: GET
 * URL: /api/hospital_detail_api.php?hospital_id=1
 * 
 * Optional:
 * - review_page=1  (reviews pagination, default 1)
 * - doctors_page=1 (doctors pagination, default 1)
 */

// ============================================
// STEP 1: Headers & CORS
// ============================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// STEP 2: Database Connection
// ============================================
include '../config.php';

if (!$con) {
    echo json_encode(["status" => false, "message" => "DB connection failed"]);
    exit();
}
mysqli_set_charset($con, 'utf8mb4');

// ============================================
// STEP 3: Input Validation
// ============================================
$hospital_id = isset($_GET['hospital_id']) ? (int)$_GET['hospital_id'] : 0;

if ($hospital_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid or missing hospital_id"
    ]);
    exit();
}

// Reviews pagination
$review_page = isset($_GET['review_page']) ? max(1, (int)$_GET['review_page']) : 1;
$reviews_per_page = 10;
$review_offset = ($review_page - 1) * $reviews_per_page;

// Doctors pagination
$doctors_page = isset($_GET['doctors_page']) ? max(1, (int)$_GET['doctors_page']) : 1;
$doctors_per_page = 20;
$doctors_offset = ($doctors_page - 1) * $doctors_per_page;

// ============================================
// STEP 4: Hospital Basic Info
// ============================================
$hospital_query = "
    SELECT h.*, c.city_name, u.user_id
    FROM hospitals h 
    LEFT JOIN cities c ON h.city_id = c.city_id 
    LEFT JOIN users u ON u.user_id = h.user_id
    WHERE h.hospital_id = $hospital_id 
      AND u.status = 1 
      AND h.approve = 1
    LIMIT 1
";

$hospital_result = mysqli_query($con, $hospital_query);

if (!$hospital_result || mysqli_num_rows($hospital_result) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "Hospital not found or not approved"
    ]);
    exit();
}

$hospital = mysqli_fetch_assoc($hospital_result);
$hospital_user_id = (int)$hospital['user_id'];

// ============================================
// STEP 5: Rating Summary
// ============================================
$rating_query = "SELECT AVG(stars) AS avg_rating, COUNT(feedback_id) AS total_reviews 
                 FROM feedback 
                 WHERE user_id = $hospital_user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);

$avg_rating    = $rating_data['avg_rating'] ? round((float)$rating_data['avg_rating'], 1) : 0;
$total_reviews = (int)$rating_data['total_reviews'];

// ============================================
// STEP 6: Beds Data
// ============================================
$beds_query = "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id LIMIT 1";
$beds_result = mysqli_query($con, $beds_query);
$beds = mysqli_fetch_assoc($beds_result);

$beds_data = null;
if ($beds) {
    $beds_data = [
        "total_beds"    => (int)$beds['total_beds'],
        "icu_beds"      => (int)$beds['icu_beds'],
        "general_beds"  => (int)$beds['general_beds'],
        "private_beds"  => (int)$beds['private_beds'],
        "updated_at"    => $beds['updated_at'] ?? ''
    ];
}

// ============================================
// STEP 7: Facilities Data
// ============================================
$facilities_query = "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id";
$facilities_result = mysqli_query($con, $facilities_query);

$facilities = [];
$total_facilities = 0;
$available_facilities = 0;

while ($row = mysqli_fetch_assoc($facilities_result)) {
    $total_facilities++;
    if ($row['is_available'] == 1) {
        $available_facilities++;
    }
    $facilities[] = [
        "facility_id"   => (int)$row['id'],
        "facility_name" => $row['facility_name'],
        "description"   => $row['description'] ?? '',
        "is_available"  => (int)$row['is_available']
    ];
}

// ============================================
// STEP 8: Doctors (Paginated)
// ============================================
$count_doctors_query = "
    SELECT COUNT(*) AS total
    FROM doctor_in_hospital dih
    INNER JOIN doctors d ON d.doctor_id = dih.doctor_id
    LEFT JOIN users u ON d.user_id = u.user_id
    WHERE dih.hospital_id = $hospital_id 
      AND u.status = 1 
      AND d.approve = 1 
      AND dih.inactive = 0
";
$count_doctors_result = mysqli_query($con, $count_doctors_query);
$count_doctors_row = mysqli_fetch_assoc($count_doctors_result);
$total_doctors = (int)$count_doctors_row['total'];
$total_doctors_pages = ($total_doctors > 0) ? ceil($total_doctors / $doctors_per_page) : 0;

$doctors_query = "
    SELECT 
        d.doctor_id,
        d.doctor_name,
        d.doctor_pic,
        d.gender,
        d.experience_years,
        d.emergency_status,
        dct.type AS specialization
    FROM doctor_in_hospital dih
    INNER JOIN doctors d ON d.doctor_id = dih.doctor_id
    LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
    LEFT JOIN users u ON d.user_id = u.user_id
    WHERE dih.hospital_id = $hospital_id 
      AND u.status = 1 
      AND d.approve = 1 
      AND dih.inactive = 0
    ORDER BY d.doctor_name ASC
    LIMIT $doctors_per_page OFFSET $doctors_offset
";
$doctors_result = mysqli_query($con, $doctors_query);

$doctors = [];
while ($doc = mysqli_fetch_assoc($doctors_result)) {
    $doctor_pic = '';
    if (!empty($doc['doctor_pic'])) {
        $doctor_pic = BASE_URL . 'admin/inc/uploads/doctors/' . $doc['doctor_pic'];
    }

    $doctors[] = [
        "doctor_id"        => (int)$doc['doctor_id'],
        "doctor_name"      => "Dr. " . $doc['doctor_name'],
        "doctor_pic"       => $doctor_pic,
        "specialization"   => $doc['specialization'] ?? 'General',
        "gender"           => $doc['gender'] ?? '',
        "is_lady_doctor"   => (strtolower($doc['gender'] ?? '') === 'female') ? 1 : 0,
        "experience_years" => (int)($doc['experience_years'] ?? 0),
        "availability"     => ($doc['emergency_status'] == 1) ? "Unavailable" : "Available"
    ];
}

// ============================================
// STEP 9: Reviews (Paginated)
// ============================================
$reviews_query = "
    SELECT feedback_id, commenter_name, commenter_gmail, comment, stars, created_at
    FROM feedback 
    WHERE user_id = $hospital_user_id AND status = 1
    ORDER BY created_at DESC
    LIMIT $reviews_per_page OFFSET $review_offset
";
$reviews_result = mysqli_query($con, $reviews_query);

$reviews = [];
while ($r = mysqli_fetch_assoc($reviews_result)) {
    $reviews[] = [
        "review_id"     => (int)$r['feedback_id'],
        "patient_name"  => $r['commenter_name'],
        "patient_email" => $r['commenter_gmail'],
        "comment"       => $r['comment'] ?? '',
        "stars"         => (int)$r['stars'],
        "date"          => date('d M Y', strtotime($r['created_at']))
    ];
}

$total_review_pages = ($total_reviews > 0) ? ceil($total_reviews / $reviews_per_page) : 0;

// ============================================
// STEP 10: Hospital Pic Full URL
// ============================================
$hospital_pic = '';
if (!empty($hospital['hospital_pic'])) {
    $hospital_pic = BASE_URL . 'admin/inc/uploads/hospitals/' . $hospital['hospital_pic'];
}

// ============================================
// STEP 11: Final Response
// ============================================
echo json_encode([
    "status"  => true,
    "message" => "Hospital detail fetched successfully",
    "data" => [
        // ---------- BASIC INFO ----------
        "hospital_info" => [
            "hospital_id"      => (int)$hospital['hospital_id'],
            "hospital_name"    => $hospital['hospital_name'],
            "hospital_pic"     => $hospital_pic,
            "hospital_address" => $hospital['hospital_address'] ?? '',
            "hospital_phone"   => $hospital['hospital_phone'] ?? '',
            "city_id"          => (int)($hospital['city_id'] ?? 0),
            "city_name"        => $hospital['city_name'] ?? '',
            "updated_at"       => $hospital['updated_at'] ?? ''
        ],

        // ---------- STATS SUMMARY (Hero Stats Bar) ----------
        "stats" => [
            "total_doctors"        => $total_doctors,
            "total_beds"           => $beds_data ? $beds_data['total_beds'] : 0,
            "total_facilities"     => $total_facilities,
            "available_facilities" => $available_facilities,
            "total_reviews"        => $total_reviews
        ],

        // ---------- RATING SUMMARY ----------
        "rating_summary" => [
            "average_rating" => $avg_rating,
            "total_reviews"  => $total_reviews
        ],

        // ---------- BEDS ----------
        "beds" => $beds_data,

        // ---------- FACILITIES ----------
        "facilities" => $facilities,

        // ---------- DOCTORS (Paginated) ----------
        "doctors" => [
            "list"          => $doctors,
            "current_page"  => $doctors_page,
            "total_pages"   => $total_doctors_pages,
            "total_doctors" => $total_doctors,
            "per_page"      => $doctors_per_page
        ],

        // ---------- REVIEWS (Paginated) ----------
        "reviews" => [
            "list"          => $reviews,
            "current_page"  => $review_page,
            "total_pages"   => $total_review_pages,
            "total_reviews" => $total_reviews,
            "per_page"      => $reviews_per_page
        ]
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>