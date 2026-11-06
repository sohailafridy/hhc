<?php
/**
 * All Labs Detail API
 * Returns complete details of ALL approved laboratories (with pagination)
 * Method: GET
 * URL: /api/lab_detail_api.php?page=1&limit=10
 * 
 * Optional:
 * - page=1         (labs pagination, default 1)
 * - limit=10       (records per page, default 10)
 * - review_page=1  (reviews pagination per lab, default 1)
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
// STEP 3: Input Validation & Pagination
// ============================================
// Labs pagination
$page  = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) && (int)$_GET['limit'] > 0 ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Reviews pagination (per lab)
$review_page = isset($_GET['review_page']) ? max(1, (int)$_GET['review_page']) : 1;
$reviews_per_page = 10;
$review_offset = ($review_page - 1) * $reviews_per_page;

// ============================================
// STEP 4: Fetch ALL Approved Labs
// ============================================
$labs_query = "
    SELECT 
        l.*,
        c.city_name,
        u.user_id
    FROM laboratories l 
    LEFT JOIN cities c ON l.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = l.user_id 
    WHERE u.status = 1 
      AND l.approve = 1
    ORDER BY l.lab_id DESC
    LIMIT $limit OFFSET $offset
";

$labs_result = mysqli_query($con, $labs_query);

if (!$labs_result) {
    echo json_encode(["status" => false, "message" => "Database query failed: " . mysqli_error($con)]);
    exit();
}

// Total count for pagination
$total_labs_query = "SELECT COUNT(*) as total FROM laboratories l 
                     LEFT JOIN users u ON u.user_id = l.user_id 
                     WHERE u.status = 1 AND l.approve = 1";
$total_labs_result = mysqli_query($con, $total_labs_query);
$total_labs = mysqli_fetch_assoc($total_labs_result)['total'];
$total_pages = ($total_labs > 0) ? ceil($total_labs / $limit) : 0;

if (mysqli_num_rows($labs_result) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "No approved laboratories found"
    ]);
    exit();
}

$labs_list = [];

// Loop through all labs
while ($lab = mysqli_fetch_assoc($labs_result)) {
    $lab_id = (int)$lab['lab_id'];
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
    // STEP 6: Reviews (Paginated per lab)
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
    // STEP 7: Lab Notes (Message Section)
    // ============================================
    $notes_query = "SELECT notes FROM notes WHERE user_id = $lab_user_id LIMIT 1";
    $notes_result = mysqli_query($con, $notes_query);
    $notes_data = mysqli_fetch_assoc($notes_result);
    $lab_notes = isset($notes_data['notes']) ? trim($notes_data['notes']) : '';

    // Show notes only if not empty and not '-' (same as detail page)
    $show_notes = (!empty($lab_notes) && $lab_notes !== '-') ? true : false;

    // ============================================
    // STEP 8: Available Tests (tbl_lab_tests + tbl_tests)
    // ============================================
    $tests_query = "
        SELECT 
            t.test_id, 
            t.test_name, 
            t.description, 
            lt.price 
        FROM tbl_lab_tests lt 
        INNER JOIN tbl_tests t ON lt.test_id = t.test_id 
        WHERE lt.lab_id = $lab_id 
          AND lt.status = 1 
          AND t.status = 1
    ";
    $tests_result = mysqli_query($con, $tests_query);

    $available_tests = [];
    while ($t = mysqli_fetch_assoc($tests_result)) {
        $price = (float)$t['price'];
        
        // Build WhatsApp link if price is 0/empty (same as detail page)
        $wa_link = '';
        if ($price <= 0) {
            $lab_phone_clean = preg_replace('/[^0-9]/', '', $lab['lab_phone']);
            if (strpos($lab_phone_clean, '0') === 0) {
                $lab_phone_clean = '92' . substr($lab_phone_clean, 1);
            }
            $wa_msg = urlencode("Hello " . $lab['lab_name'] . ", I want to ask the price for the test: " . $t['test_name']);
            $wa_link = "https://wa.me/" . $lab_phone_clean . "?text=" . $wa_msg;
        }

        $available_tests[] = [
            "test_id"       => (int)$t['test_id'],
            "test_name"     => $t['test_name'],
            "description"   => $t['description'] ?? '',
            "price"         => $price,
            "price_display" => ($price > 0) ? "Rs. " . number_format($price, 0) : "Not Available",
            "ask_whatsapp"  => ($price <= 0) ? true : false,
            "wa_link"       => $wa_link
        ];
    }

    // ============================================
    // STEP 9: Lab Pic Full URL
    // ============================================
    $lab_pic = '';
    $default_pic = BASE_URL . 'admin/inc/uploads/default/lab.jpg';

    if (!empty($lab['lab_pic'])) {
        $lab_pic = BASE_URL . 'admin/inc/uploads/labs/' . $lab['lab_pic'];
    } else {
        $lab_pic = $default_pic;
    }

    // ============================================
    // STEP 10: Build Lab Data Array
    // ============================================
    $labs_list[] = [
        // ---------- BASIC INFO ----------
        "lab_info" => [
            "lab_id"        => $lab_id,
            "lab_name"      => $lab['lab_name'],
            "lab_pic"       => $lab_pic,
            "lab_type"      => (int)($lab['lab_type'] ?? 0),
            "lab_address"   => $lab['lab_address'] ?? '',
            "lab_phone"     => $lab['lab_phone'] ?? '',
            "lab_email"     => $lab['lab_email'] ?? '',
            "city_id"       => (int)($lab['city_id'] ?? 0),
            "city_name"     => $lab['city_name'] ?? '',
            "working_hours" => "24/7 Emergency Services",  // static (as per your detail page)
            "updated_at"    => $lab['updated_at'] ?? ''
        ],

        // ---------- LAB MESSAGE / NOTES (Show if available) ----------
        "lab_notes" => [
            "show_section" => $show_notes,
            "notes"        => $show_notes ? $lab_notes : ''
        ],

        // ---------- RATING SUMMARY ----------
        "rating_summary" => [
            "average_rating" => $avg_rating,
            "total_reviews"  => $total_reviews
        ],

        // ---------- AVAILABLE TESTS ----------
        "available_tests" => $available_tests,

        // ---------- REVIEWS (Paginated) ----------
        "reviews" => [
            "list"          => $reviews,
            "current_page"  => $review_page,
            "total_pages"   => $total_review_pages,
            "total_reviews" => $total_reviews,
            "per_page"      => $reviews_per_page
        ]
    ];
}

// ============================================
// STEP 11: Final Response
// ============================================
echo json_encode([
    "status"  => true,
    "message" => "All laboratories detail fetched successfully",
    "pagination" => [
        "current_page"  => $page,
        "limit"         => $limit,
        "total_records" => (int)$total_labs,
        "total_pages"   => $total_pages
    ],
    "data" => $labs_list
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>