<?php
/**
 * Blood Bank Detail API
 * Returns complete details of a single blood bank
 * Method: GET
 * URL: /api/blood_bank_detail_api.php?bb_id=1
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
// STEP 2: Database Connection (Aapke instruction ke mutabiq)
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
$bb_id = isset($_GET['bb_id']) ? (int)$_GET['bb_id'] : 0;

if ($bb_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid or missing bb_id"
    ]);
    exit();
}

// Reviews pagination
$review_page = isset($_GET['review_page']) ? max(1, (int)$_GET['review_page']) : 1;
$reviews_per_page = 10;
$review_offset = ($review_page - 1) * $reviews_per_page;

// ============================================
// STEP 4: Blood Bank Basic Info Fetch
// ============================================
$bb_query = "
    SELECT 
        bb.*,
        c.city_name,
        u.user_id
    FROM blood_bank bb
    LEFT JOIN cities c ON bb.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = bb.user_id
    WHERE bb.bb_id = $bb_id 
      AND u.status = 1 
      AND bb.approve = 1
    LIMIT 1
";

$bb_result = mysqli_query($con, $bb_query);

if (!$bb_result || mysqli_num_rows($bb_result) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "Blood bank not found or not approved"
    ]);
    exit();
}

$bb = mysqli_fetch_assoc($bb_result);
$bb_user_id = (int)$bb['user_id'];

// ============================================
// STEP 5: Rating Summary
// ============================================
$rating_query = "SELECT AVG(stars) AS avg_rating, COUNT(feedback_id) AS total_reviews 
                 FROM feedback 
                 WHERE user_id = $bb_user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);

$avg_rating    = $rating_data['avg_rating'] ? round((float)$rating_data['avg_rating'], 1) : 0;
$total_reviews = (int)$rating_data['total_reviews'];

// ============================================
// STEP 6: Blood Stock Availability (bb_available_blood table se)
// ============================================
$stock_query = "SELECT avail_b_id, b_group, stock 
                FROM bb_available_blood 
                WHERE bb_id = $bb_id 
                ORDER BY b_group ASC";
$stock_result = mysqli_query($con, $stock_query);

$blood_stock = [];
if ($stock_result) {
    while ($s = mysqli_fetch_assoc($stock_result)) {
        $blood_stock[] = [
            "avail_b_id" => (int)$s['avail_b_id'],
            "blood_group" => $s['b_group'],
            "stock"       => (int)$s['stock'],
            "available"   => ($s['stock'] > 0) ? true : false
        ];
    }
}

// ============================================
// STEP 7: Reviews (Paginated)
// ============================================
$reviews_query = "
    SELECT feedback_id, commenter_name, commenter_gmail, comment, stars, created_at
    FROM feedback 
    WHERE user_id = $bb_user_id AND status = 1
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
// STEP 8: Blood Bank Pic Full URL (Aapke path ke mutabiq)
// ============================================
$bb_pic = '';
$default_pic = BASE_URL . 'admin/inc/uploads/default/bb.jpg';

if (!empty($bb['bb_pic'])) {
    $bb_pic = BASE_URL . 'admin/inc/uploads/blood-banks/' . $bb['bb_pic'];
} else {
    $bb_pic = $default_pic;
}

// ============================================
// STEP 9: Final Response
// ============================================
echo json_encode([
    "status"  => true,
    "message" => "Blood bank detail fetched successfully",
    "data" => [
        // ---------- BASIC INFO ----------
        "bb_info" => [
            "bb_id"         => (int)$bb['bb_id'],
            "bb_name"       => $bb['bb_name'],
            "bb_pic"        => $bb_pic,
            "bb_address"    => $bb['bb_address'] ?? '',
            "bb_contact"    => $bb['bb_contact'] ?? '',
            "city_id"       => (int)($bb['city_id'] ?? 0),
            "city_name"     => $bb['city_name'] ?? '',
            "working_hours" => "24/7 Emergency Services",
            "updated_at"    => $bb['updated_at'] ?? ''
        ],

        // ---------- RATING SUMMARY ----------
        "rating_summary" => [
            "average_rating" => $avg_rating,
            "total_reviews"  => $total_reviews
        ],

        // ---------- BLOOD STOCK AVAILABILITY ----------
        "blood_stock" => $blood_stock,

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