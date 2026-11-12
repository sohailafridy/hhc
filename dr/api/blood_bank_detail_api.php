<?php
/**
 * All Blood Banks Detail API
 * Matched with latest blood-bank-detail.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Config path check
if (!file_exists('../config.php')) {
    echo json_encode(["status" => false, "message" => "config.php not found at ../config.php"]);
    exit();
}
include '../config.php';

if (!$con) {
    echo json_encode(["status" => false, "message" => "DB connection failed: " . mysqli_connect_error()]);
    exit();
}
mysqli_set_charset($con, 'utf8mb4');

// Pagination
$page  = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) && (int)$_GET['limit'] > 0 ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

$review_page = isset($_GET['review_page']) ? max(1, (int)$_GET['review_page']) : 1;
$reviews_per_page = 10;
$review_offset = ($review_page - 1) * $reviews_per_page;

// ============================================
// STEP 4: Fetch ALL Approved Blood Banks
// ============================================
$bb_query = "
    SELECT 
        bb.*,
        c.city_name,
        u.user_id
    FROM blood_bank bb
    LEFT JOIN cities c ON bb.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = bb.user_id
    WHERE u.status = 1 
      AND bb.approve = 1
    ORDER BY bb.bb_id DESC
";

$bb_result = mysqli_query($con, $bb_query);

if (!$bb_result) {
    echo json_encode(["status" => false, "message" => "Query failed: " . mysqli_error($con)]);
    exit();
}

$total_bb_query = "SELECT COUNT(*) as total FROM blood_bank bb 
                   LEFT JOIN users u ON u.user_id = bb.user_id 
                   WHERE u.status = 1 AND bb.approve = 1";
$total_bb_result = mysqli_query($con, $total_bb_query);
$total_bb = $total_bb_result ? mysqli_fetch_assoc($total_bb_result)['total'] : 0;
$total_pages = ($total_bb > 0) ? ceil($total_bb / $limit) : 0;

if (mysqli_num_rows($bb_result) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "No approved blood banks found"
    ]);
    exit();
}

$blood_banks_list = [];

while ($bb = mysqli_fetch_assoc($bb_result)) {
    $bb_id = (int)$bb['bb_id'];
    $bb_user_id = (int)$bb['user_id'];

    // ============================================
    // Rating Summary
    // ============================================
    $rating_query = "SELECT AVG(stars) AS avg_rating, COUNT(feedback_id) AS total_reviews 
                     FROM feedback 
                     WHERE user_id = $bb_user_id AND status = 1";
    $rating_result = mysqli_query($con, $rating_query);
    $rating_data = $rating_result ? mysqli_fetch_assoc($rating_result) : ['avg_rating'=>0, 'total_reviews'=>0];

    $avg_rating    = $rating_data['avg_rating'] ? round((float)$rating_data['avg_rating'], 1) : 0;
    $total_reviews = (int)$rating_data['total_reviews'];

    // ============================================
    // Available Blood Bags (Matched with Detail Page)
    // ============================================
    $stock_query = "SELECT 
                        bb_available_blood.avail_b_id, 
                        bb_available_blood.stock, 
                        blood_groups.bg_name AS blood_group
                    FROM bb_available_blood
                    LEFT JOIN blood_groups ON blood_groups.bg_id = bb_available_blood.bg_id
                    WHERE bb_available_blood.bb_id = $bb_id
                    ORDER BY blood_groups.bg_id ASC";
    $stock_result = mysqli_query($con, $stock_query);

    $blood_stock = [];
    if ($stock_result) {
        while ($s = mysqli_fetch_assoc($stock_result)) {
            $stock_count = (int)$s['stock'];
            $blood_stock[] = [
                "avail_b_id"  => (int)$s['avail_b_id'],
                "blood_group" => $s['blood_group'] ?? 'N/A',
                "stock"       => $stock_count,
                "available"   => ($stock_count > 0) ? true : false,
                "stock_status"=> ($stock_count > 0) ? $stock_count . " Bags" : "Out of Stock"
            ];
        }
    }

    // ============================================
    // Blood Bank Admin Message (Notes from Detail Page)
    // ============================================
    $notes_query = "SELECT * FROM notes WHERE user_id = $bb_user_id LIMIT 1";
    $notes_result = mysqli_query($con, $notes_query);
    $bb_note = ($notes_result && mysqli_num_rows($notes_result) > 0) ? mysqli_fetch_assoc($notes_result) : null;

    $notes_data = null;
    // Same logic as detail page: Show only if not empty and not just '-'
    if ($bb_note && !empty(trim($bb_note['notes'])) && trim($bb_note['notes']) !== '-') {
        $notes_data = [
            "note_id"    => (int)$bb_note['note_id'],
            "notes"      => $bb_note['notes'],
            "updated_at" => $bb_note['updated_at'] ?? ''
        ];
    }

    // ============================================
    // Reviews (Paginated)
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
    if ($reviews_result) {
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
    }

    $total_review_pages = ($total_reviews > 0) ? ceil($total_reviews / $reviews_per_page) : 0;

    // ============================================
    // Blood Bank Pic Full URL
    // ============================================
    $bb_pic = '';
    $default_pic = defined('BASE_URL') ? BASE_URL . 'admin/inc/uploads/default/bb.jpg' : '';

    if (!empty($bb['bb_pic'])) {
        $bb_pic = defined('BASE_URL') ? BASE_URL . 'admin/inc/uploads/blood-banks/' . $bb['bb_pic'] : '';
    } else {
        $bb_pic = $default_pic;
    }

    // ============================================
    // Build Blood Bank Data Array
    // ============================================
    $blood_banks_list[] = [
        "bb_info" => [
            "bb_id"         => $bb_id,
            "bb_name"       => $bb['bb_name'],
            "bb_pic"        => $bb_pic,
            "bb_address"    => $bb['bb_address'] ?? '',
            "bb_contact"    => $bb['bb_contact'] ?? '',
            "city_id"       => (int)($bb['city_id'] ?? 0),
            "city_name"     => $bb['city_name'] ?? '',
            "working_hours" => "24/7 Emergency Services",
            "updated_at"    => $bb['updated_at'] ?? ''
        ],
        "rating_summary" => [
            "average_rating" => $avg_rating,
            "total_reviews"  => $total_reviews
        ],
        "blood_stock" => $blood_stock,
        "admin_message" => [
            "show_section" => ($notes_data !== null),
            "notes"        => $notes_data
        ],
        "reviews" => [
            "list"          => $reviews,
            "current_page"  => $review_page,
            "total_pages"   => $total_review_pages,
            "total_reviews" => $total_reviews,
            "per_page"      => $reviews_per_page
        ]
    ];
}

echo json_encode([
    "status"  => true,
    "message" => "All blood banks detail fetched successfully",
    "pagination" => [
        "current_page"  => $page,
        "limit"         => $limit,
        "total_records" => (int)$total_bb,
        "total_pages"   => $total_pages
    ],
    "data" => $blood_banks_list
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>