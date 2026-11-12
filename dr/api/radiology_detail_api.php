<?php
/**
 * All Radiology Detail API
 * Returns complete details of ALL approved radiology centers (with pagination)
 * Method: GET
 * URL: /api/radiology_detail_api.php?page=1&limit=10
 * 
 * Optional:
 * - page=1         (radiology pagination, default 1)
 * - limit=10       (records per page, default 10)
 * - review_page=1  (reviews pagination per radiology center, default 1)
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
// Radiology pagination
$page   = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit  = isset($_GET['limit']) && (int)$_GET['limit'] > 0 ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Reviews pagination (per radiology center)
$review_page = isset($_GET['review_page']) ? max(1, (int)$_GET['review_page']) : 1;
$reviews_per_page = 10;
$review_offset = ($review_page - 1) * $reviews_per_page;

// ============================================
// STEP 4: Fetch ALL Approved Radiology Centers
// ============================================
$rad_query = "
    SELECT 
        r.*,
        c.city_name,
        u.user_id
    FROM radiology r 
    LEFT JOIN cities c ON r.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = r.user_id 
    WHERE u.status = 1 
      AND r.approve = 1
    ORDER BY r.rad_id DESC
    LIMIT $limit OFFSET $offset
";

$rad_result = mysqli_query($con, $rad_query);

if (!$rad_result) {
    echo json_encode(["status" => false, "message" => "Database query failed: " . mysqli_error($con)]);
    exit();
}

// Total count for pagination
$total_rads_query = "SELECT COUNT(*) as total FROM radiology r 
                     LEFT JOIN users u ON u.user_id = r.user_id 
                     WHERE u.status = 1 AND r.approve = 1";
$total_rads_result = mysqli_query($con, $total_rads_query);
$total_rads = mysqli_fetch_assoc($total_rads_result)['total'];
$total_pages = ($total_rads > 0) ? ceil($total_rads / $limit) : 0;

if (mysqli_num_rows($rad_result) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "No approved radiology centers found"
    ]);
    exit();
}

$rad_list = [];

// Loop through all radiology centers
while ($rad = mysqli_fetch_assoc($rad_result)) {
    $rad_id = (int)$rad['rad_id'];
    $rad_user_id = (int)$rad['user_id'];

    // ============================================
    // STEP 5: Rating Summary
    // ============================================
    $rating_query = "SELECT AVG(stars) AS avg_rating, COUNT(feedback_id) AS total_reviews 
                     FROM feedback 
                     WHERE user_id = $rad_user_id AND status = 1";
    $rating_result = mysqli_query($con, $rating_query);
    $rating_data = mysqli_fetch_assoc($rating_result);

    $avg_rating    = $rating_data['avg_rating'] ? round((float)$rating_data['avg_rating'], 1) : 0;
    $total_reviews = (int)$rating_data['total_reviews'];

    // ============================================
    // STEP 6: Reviews (Paginated per radiology center)
    // ============================================
    $reviews_query = "
        SELECT feedback_id, commenter_name, commenter_gmail, comment, stars, created_at
        FROM feedback 
        WHERE user_id = $rad_user_id AND status = 1
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
    // STEP 7: Radiology Notes (Message Section)
    // ============================================
    $notes_query = "SELECT notes FROM notes WHERE user_id = $rad_user_id LIMIT 1";
    $notes_result = mysqli_query($con, $notes_query);
    $notes_data = mysqli_fetch_assoc($notes_result);
    $rad_notes = isset($notes_data['notes']) ? trim($notes_data['notes']) : '';

    // Show notes only if not empty and not '-' (same as detail page)
    $show_notes = (!empty($rad_notes) && $rad_notes !== '-') ? true : false;

    // ============================================
    // STEP 8: Available Tests (tbl_radiology_clinic_tests + tbl_radiology_tests)
    // ============================================
    $tests_query = "
        SELECT 
            t.test_id, 
            t.test_name, 
            ct.price,
            c.category_name 
        FROM tbl_radiology_clinic_tests ct 
        INNER JOIN tbl_radiology_tests t ON ct.test_id = t.test_id 
        INNER JOIN tbl_radiology_categories c ON ct.category_id = c.category_id
        WHERE ct.clinic_id = $rad_id 
          AND ct.status = 1 
          AND t.status = 1 
          AND c.status = 1
        ORDER BY c.category_name ASC, t.test_name ASC
    ";
    $tests_result = mysqli_query($con, $tests_query);

    // Grouping tests by category
    $grouped_tests = [];
    if ($tests_result && mysqli_num_rows($tests_result) > 0) {
        while ($t = mysqli_fetch_assoc($tests_result)) {
            $cat_name = $t['category_name'] ?? 'General';
            $price = (float)$t['price'];
            
            // Build WhatsApp link if price is 0/empty
            $wa_link = '';
            if ($price <= 0) {
                $rad_phone_clean = preg_replace('/[^0-9]/', '', $rad['rad_phone']);
                if (strpos($rad_phone_clean, '0') === 0) {
                    $rad_phone_clean = '92' . substr($rad_phone_clean, 1);
                }
                $wa_msg = urlencode("Hello " . $rad['rad_name'] . ", I want to ask the price for the test: " . $t['test_name']);
                $wa_link = "https://wa.me/" . $rad_phone_clean . "?text=" . $wa_msg;
            } else {
                $wa_msg_book = urlencode("Hello " . $rad['rad_name'] . ", I want to book the test: " . $t['test_name'] . " (Price: Rs. " . number_format($price, 0) . ")");
                $wa_link = "https://wa.me/" . $rad_phone_clean . "?text=" . $wa_msg_book;
            }

            if (!isset($grouped_tests[$cat_name])) {
                $grouped_tests[$cat_name] = [];
            }

            $grouped_tests[$cat_name][] = [
                "test_id"       => (int)$t['test_id'],
                "test_name"     => $t['test_name'],
                "price"         => $price,
                "price_display" => ($price > 0) ? "Rs. " . number_format($price, 0) : "Not Available",
                "ask_whatsapp"  => ($price <= 0) ? true : false,
                "wa_link"       => $wa_link
            ];
        }
    }

    // ============================================
    // STEP 9: Working in Hospitals (assign_hospital_radiology)
    // ============================================
    $hospitals_query = "
        SELECT h.hospital_id, h.hospital_name, h.hospital_phone, c.city_name
        FROM assign_hospital_radiology asg
        LEFT JOIN hospitals h ON h.hospital_id = asg.hospital_id
        LEFT JOIN cities c ON h.city_id = c.city_id
        LEFT JOIN users u ON u.user_id = asg.user_id
        WHERE u.status = 1 AND h.approve = 1 
          AND asg.user_id = $rad_user_id AND asg.status = 1
        ORDER BY h.hospital_name ASC
    ";
    $hospitals_result = mysqli_query($con, $hospitals_query);

    $working_hospitals = [];
    while ($h = mysqli_fetch_assoc($hospitals_result)) {
        $working_hospitals[] = [
            "hospital_id"    => (int)$h['hospital_id'],
            "hospital_name"  => $h['hospital_name'],
            "hospital_phone" => $h['hospital_phone'] ?? '',
            "city_name"      => $h['city_name'] ?? ''
        ];
    }

    // ============================================
    // STEP 10: Radiology Pic Full URL
    // ============================================
    $rad_pic = '';
    $default_pic = BASE_URL . 'admin/inc/uploads/default/radiology.jpg';

    if (!empty($rad['rad_pic'])) {
        $rad_pic = BASE_URL . 'admin/inc/uploads/radiology/' . $rad['rad_pic'];
    } else {
        $rad_pic = $default_pic;
    }

    // ============================================
    // STEP 11: Build Radiology Data Array
    // ============================================
    $rad_list[] = [
        // ---------- BASIC INFO ----------
        "rad_info" => [
            "rad_id"         => $rad_id,
            "rad_name"       => $rad['rad_name'],
            "rad_pic"        => $rad_pic,
            "rad_address"    => $rad['rad_address'] ?? '',
            "rad_phone"      => $rad['rad_phone'] ?? '',
            "rad_email"      => $rad['rad_email'] ?? '',
            "city_id"        => (int)($rad['city_id'] ?? 0),
            "city_name"      => $rad['city_name'] ?? '',
            "working_hours"  => "24/7 Services",
            "updated_at"     => $rad['updated_at'] ?? ''
        ],

        // ---------- NOTES / MESSAGE ----------
        "rad_notes" => [
            "show_section" => $show_notes,
            "notes"        => $show_notes ? $rad_notes : ''
        ],

        // ---------- RATING SUMMARY ----------
        "rating_summary" => [
            "average_rating" => $avg_rating,
            "total_reviews"  => $total_reviews
        ],

        // ---------- WORKING IN HOSPITALS ----------
        "working_hospitals" => $working_hospitals,

        // ---------- AVAILABLE TESTS ----------
        "available_tests" => $grouped_tests,

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
// STEP 12: Final Response
// ============================================
echo json_encode([
    "status"  => true,
    "message" => "All radiology details fetched successfully",
    "pagination" => [
        "current_page"  => $page,
        "limit"         => $limit,
        "total_records" => (int)$total_rads,
        "total_pages"   => $total_pages
    ],
    "data" => $rad_list
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>