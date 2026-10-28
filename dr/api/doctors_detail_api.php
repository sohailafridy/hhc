<?php
/**
 * Doctor Detail API
 * Returns complete details of a single doctor
 * Method: GET
 * URL: /api/doctor_detail_api.php?doctor_id=1
 * 
 * Response mein woh sab kuch hoga jo detail page par dikhta hai:
 * - Basic Info (name, pic, specialization, experience)
 * - Contact Info
 * - Clinical Info (multiple hospitals/shifts)
 * - Reviews (with pagination)
 * - Rating Summary
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
// Agar $con nahi mil raha: $con = mysqli_connect("localhost", "user", "pass", "doctor");

if (!$con) {
    echo json_encode(["status" => false, "message" => "DB connection failed"]);
    exit();
}
mysqli_set_charset($con, 'utf8mb4');

// ============================================
// STEP 3: Input Validation
// ============================================
$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;

if ($doctor_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid or missing doctor_id"
    ]);
    exit();
}

// Reviews pagination (default: pehle 10 reviews)
$review_page = isset($_GET['review_page']) ? (int)$_GET['review_page'] : 1;
$reviews_per_page = 10;
$review_offset = ($review_page - 1) * $reviews_per_page;

// ============================================
// STEP 4: Doctor Basic Info Fetch
// ============================================
$query = "
    SELECT 
        d.*,
        c.city_name,
        h.hospital_name,
        h.hospital_address,
        dct.type AS specialization,
        dct.treatment,
        dc.cat_name AS specialization_category
    FROM doctors d
    LEFT JOIN cities c ON d.city_id = c.city_id
    LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
    LEFT JOIN users u ON u.user_id = d.user_id
    LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
    LEFT JOIN dr_categories dc ON dct.dr_cat_id = dc.dr_cat_id
    WHERE d.doctor_id = $doctor_id 
      AND u.status = 1 
      AND d.approve = 1
    LIMIT 1
";

$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "Doctor not found or not approved"
    ]);
    exit();
}

$doctor = mysqli_fetch_assoc($result);

// ============================================
// STEP 5: Rating Summary
// ============================================
$rating_query = "SELECT AVG(stars) AS avg_rating, COUNT(*) AS total_reviews 
                 FROM feedback 
                 WHERE user_id = " . (int)$doctor['user_id'] . " AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);

$avg_rating = $rating_data['avg_rating'] ? round((float)$rating_data['avg_rating'], 1) : 0;
$total_reviews = (int)$rating_data['total_reviews'];

// ============================================
// STEP 6: Clinical Info (Multiple Hospitals/Shifts)
// ============================================
$clinical_query = "
    SELECT 
        ci.clinical_info_id,
        ci.morning_opening_time,
        ci.morning_closing_time,
        ci.evening_opening_time,
        ci.evening_closing_time,
        ci.shift,
        ci.season,
        ci.detail,
        ci.contact,
        ci.clinic_address,
        ci.days,
        ci.off_days,
        h.hospital_id,
        h.hospital_name,
        h.hospital_address,
        c.city_name
    FROM clinical_info ci
    INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id
    LEFT JOIN hospitals h ON dih.hospital_id = h.hospital_id
    LEFT JOIN cities c ON h.city_id = c.city_id
    WHERE dih.doctor_id = $doctor_id AND dih.inactive = 0
    ORDER BY ci.season, ci.shift
";

$clinical_result = mysqli_query($con, $clinical_query);
$clinical_info = [];

while ($row = mysqli_fetch_assoc($clinical_result)) {
    // Time format (08:00 AM - 12:00 PM) agar value mojood ho
    $morning = null;
    if (!empty($row['morning_opening_time']) && !empty($row['morning_closing_time'])) {
        $morning = date('h:i A', strtotime($row['morning_opening_time'])) 
                 . ' - ' 
                 . date('h:i A', strtotime($row['morning_closing_time']));
    }

    $evening = null;
    if (!empty($row['evening_opening_time']) && !empty($row['evening_closing_time'])) {
        $evening = date('h:i A', strtotime($row['evening_opening_time'])) 
                 . ' - ' 
                 . date('h:i A', strtotime($row['evening_closing_time']));
    }

    $clinical_info[] = [
        "clinical_info_id"  => (int)$row['clinical_info_id'],
        "hospital_id"       => (int)$row['hospital_id'],
        "hospital_name"     => $row['hospital_name'] ?? 'Personal Clinic',
        "hospital_address"  => $row['hospital_address'] ?? ($row['clinic_address'] ?? ''),
        "city_name"         => $row['city_name'] ?? '',
        "morning_timing"    => $morning,
        "evening_timing"    => $evening,
        "working_days"      => $row['days'] ?? 'N/A',
        "off_days"          => $row['off_days'] ?? 'None',
        "contact"           => $row['contact'] ?? '',
        "detail"            => $row['detail'] ?? '',
        "shift"             => $row['shift'] ?? '',
        "season"            => $row['season'] ?? ''
    ];
}

// ============================================
// STEP 7: Reviews (Paginated)
// ============================================
$reviews_query = "
    SELECT feedback_id, commenter_name, commenter_gmail, comment, stars, created_at
    FROM feedback 
    WHERE user_id = " . (int)$doctor['user_id'] . " AND status = 1
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

$total_review_pages = ceil($total_reviews / $reviews_per_page);

// ============================================
// STEP 8: Doctor Pic Full URL
// ============================================
$doctor_pic = '';
if (!empty($doctor['doctor_pic'])) {
    $doctor_pic = BASE_URL . 'admin/inc/uploads/doctors/' . $doctor['doctor_pic'];
}

// ============================================
// STEP 9: Final Response
// ============================================
echo json_encode([
    "status"  => true,
    "message" => "Doctor detail fetched successfully",
    "data" => [
        // ---------- BASIC INFO ----------
        "doctor_info" => [
            "doctor_id"         => (int)$doctor['doctor_id'],
            "user_id"           => (int)$doctor['user_id'],
            "doctor_name"       => "Dr. " . $doctor['doctor_name'],
            "doctor_pic"        => $doctor_pic,
            "gender"            => $doctor['gender'] ?? '',
            "is_lady_doctor"    => (strtolower($doctor['gender'] ?? '') === 'female') ? 1 : 0,
            "specialization"    => $doctor['specialization'] ?? 'General Practitioner',
            "specialization_category" => $doctor['specialization_category'] ?? '',
            "short_detail"      => $doctor['short_detail'] ?? '',
            "experience_years"  => (int)($doctor['experience_years'] ?? 0),
            "emergency_status"  => (int)($doctor['emergency_status'] ?? 0),
            "availability"      => ($doctor['emergency_status'] == 1) ? "Unavailable" : "Available",
            "mahre_amraz"       => $doctor['treatment'] ?? '',
            "notes"             => $doctor['notes'] ?? '',
            "static_clinical_info" => $doctor['static_clinical_info'] ?? '',
            "other"             => $doctor['other'] ?? '',
            "clinic_name"       => $doctor['clinic_name'] ?? '',
            "clinic_address"    => $doctor['clinic_address'] ?? '',
            "clinic_status"     => (int)($doctor['clinic_status'] ?? 0),
            "updated_at"        => $doctor['updated_at'] ?? ''
        ],

        // ---------- CONTACT INFO ----------
        "contact_info" => [
            "phone"        => $doctor['doctor_phone'] ?? '',
            "email"        => $doctor['doctor_email'] ?? '',
            "city_id"      => (int)($doctor['city_id'] ?? 0),
            "city_name"    => $doctor['city_name'] ?? '',
            "hospital_id"  => (int)($doctor['hospital_id'] ?? 0),
            "hospital_name"=> $doctor['hospital_name'] ?? '',
            "hospital_address" => $doctor['hospital_address'] ?? ''
        ],

        // ---------- RATING SUMMARY ----------
        "rating_summary" => [
            "average_rating" => $avg_rating,
            "total_reviews"  => $total_reviews
        ],

        // ---------- CLINICAL INFO (Multiple Shifts) ----------
        "clinical_info" => $clinical_info,

        // ---------- REVIEWS ----------
        "reviews" => [
            "list"           => $reviews,
            "current_page"   => $review_page,
            "total_pages"    => $total_review_pages,
            "total_reviews"  => $total_reviews,
            "per_page"       => $reviews_per_page
        ]
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>