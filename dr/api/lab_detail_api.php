<?php
/**
 * Lab Detail API
 * Returns complete details of a single laboratory
 * Method: GET
 * URL: /api/lab_detail_api.php?lab_id=1
 * 
 * Optional:
 * - review_page=1  (reviews pagination, default 1)
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
$lab_id = isset($_GET['lab_id']) ? (int)$_GET['lab_id'] : 0;

if ($lab_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid or missing lab_id"
    ]);
    exit();
}

// Reviews pagination
$review_page = isset($_GET['review_page']) ? max(1, (int)$_GET['review_page']) : 1;
$reviews_per_page = 10;
$review_offset = ($review_page - 1) * $reviews_per_page;

// ============================================
// STEP 4: Lab Basic Info Fetch
// ============================================
$lab_query = "
    SELECT 
        l.*,
        c.city_name,
        u.user_id
    FROM laboratories l 
    LEFT JOIN cities c ON l.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = l.user_id 
    WHERE l.lab_id = $lab_id 
      AND u.status = 1 
      AND l.approve = 1
    LIMIT 1
";

$lab_result = mysqli_query($con, $lab_query);

if (!$lab_result || mysqli_num_rows($lab_result) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "Laboratory not found or not approved"
    ]);
    exit();
}

$lab = mysqli_fetch_assoc($lab_result);
$lab_user_id = (int)$lab['user_id'];

// ============================================
// STEP 5: Rating Summary
// ============================================
$rating_query = "SELECT AVG(stars) AS avg_rating, COUNT(feedback_id) AS total_reviews 
                 FROM feedback 
                 WHERE user_id = $lab_user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);

$avg_rating    = $rating_data['avg_rating'] ? round((float)$rating_data['avg_rating'], 1) : 0;
$total_reviews = (int)$rating_data['total_reviews'];

// ============================================
// STEP 6: Reviews (Paginated)
// ============================================
$reviews_query = "
    SELECT feedback_id, commenter_name, commenter_gmail, comment, stars, created_at
    FROM feedback 
    WHERE user_id = $lab_user_id AND status = 1
    ORDER BY feedback_id DESC
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
// STEP 7: Lab Pic Full URL
// ============================================
$lab_pic = '';
$default_pic = BASE_URL . 'admin/inc/uploads/default/lab.jpg';

if (!empty($lab['lab_pic'])) {
    $lab_pic = BASE_URL . 'admin/inc/uploads/labs/' . $lab['lab_pic'];
} else {
    $lab_pic = $default_pic;
}

// ============================================
// STEP 8: Final Response
// ============================================
echo json_encode([
    "status"  => true,
    "message" => "Laboratory detail fetched successfully",
    "data" => [
        // ---------- BASIC INFO ----------
        "lab_info" => [
            "lab_id"        => (int)$lab['lab_id'],
            "lab_name"      => $lab['lab_name'],
            "lab_pic"       => $lab_pic,
            "lab_type"      => (int)($lab['lab_type'] ?? 0),
            "lab_address"   => $lab['lab_address'] ?? '',
            "lab_phone"     => $lab['lab_phone'] ?? '',
            "lab_email"     => $lab['lab_email'] ?? '',
            "city_id"       => (int)($lab['city_id'] ?? 0),
            "city_name"     => $lab['city_name'] ?? '',
            "working_hours" => "24/7 Emergency Services",  // static (aapke code se)
            "updated_at"    => $lab['updated_at'] ?? ''
        ],

        // ---------- RATING SUMMARY ----------
        "rating_summary" => [
            "average_rating" => $avg_rating,
            "total_reviews"  => $total_reviews
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