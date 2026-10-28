<?php
/**
 * Blood Banks Listing API
 * Returns approved blood banks (NO duplicate cities data)
 * Method: GET
 * URL: /api/blood_banks_api.php
 * 
 * Query Parameters (optional):
 * - search=xyz        (blood bank name search)
 * - city=1            (city id filter)
 * - page=1            (pagination)
 * - limit=10          (per page, max 50)
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
// STEP 3: Input Parameters
// ============================================
$search  = isset($_GET['search']) ? mysqli_real_escape_string($con, trim($_GET['search'])) : '';
$city_id = isset($_GET['city']) ? (int)$_GET['city'] : 0;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 10;
$offset  = ($page - 1) * $per_page;

// ============================================
// STEP 4: WHERE Clause
// ============================================
$where_clause = " AND bb.approve = 1 AND u.status = 1 ";

if (!empty($search)) {
    $where_clause .= " AND bb.bb_name LIKE '%$search%' ";
}
if (!empty($city_id) && $city_id > 0) {
    $where_clause .= " AND bb.city_id = $city_id ";
}

// ============================================
// STEP 5: Total Count
// ============================================
$count_query = "
    SELECT COUNT(*) AS total
    FROM blood_bank bb
    LEFT JOIN cities c ON bb.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = bb.user_id
    WHERE 1=1 $where_clause
";
$count_result = mysqli_query($con, $count_query);

if (!$count_result) {
    echo json_encode(["status" => false, "message" => "Count query failed: " . mysqli_error($con)]);
    exit();
}

$total_row        = mysqli_fetch_assoc($count_result);
$total_blood_banks = (int)$total_row['total'];
$total_pages      = ($total_blood_banks > 0) ? ceil($total_blood_banks / $per_page) : 0;

// ============================================
// STEP 6: Main Query
// ============================================
$query = "
    SELECT 
        bb.bb_id,
        bb.bb_name,
        bb.bb_address,
        bb.bb_contact,
        bb.bb_pic,
        bb.city_id,
        bb.updated_at,
        c.city_name,
        (SELECT AVG(f.stars) FROM feedback f WHERE f.user_id = bb.user_id AND f.status = 1) AS avg_rating,
        (SELECT COUNT(*) FROM feedback f WHERE f.user_id = bb.user_id AND f.status = 1) AS total_reviews
    FROM blood_bank bb
    LEFT JOIN cities c ON bb.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = bb.user_id
    WHERE 1=1 $where_clause
    ORDER BY bb.bb_name ASC
    LIMIT $per_page OFFSET $offset
";

$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(["status" => false, "message" => "Query failed: " . mysqli_error($con)]);
    exit();
}

// ============================================
// STEP 7: Data Prepare
// ============================================
$blood_banks = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Blood Bank Pic Full URL
    $bb_pic = '';
    if (!empty($row['bb_pic'])) {
        $bb_pic = BASE_URL . 'admin/inc/uploads/blood_banks/' . $row['bb_pic'];
    }

    $avg_rating    = $row['avg_rating'] ? round((float)$row['avg_rating'], 1) : 0;
    $total_reviews = (int)$row['total_reviews'];

    $blood_banks[] = [
        "bb_id"        => (int)$row['bb_id'],
        "bb_name"      => $row['bb_name'],
        "bb_pic"       => $bb_pic,
        "bb_address"   => $row['bb_address'] ?? '',
        "bb_contact"   => $row['bb_contact'] ?? '',
        "city_id"      => (int)$row['city_id'],
        "city_name"    => $row['city_name'] ?? '',
        "rating"       => $avg_rating,
        "total_reviews"=> $total_reviews,
        "updated_at"   => $row['updated_at'] ?? ''
    ];
}

// ============================================
// STEP 8: Final Response
// ============================================
echo json_encode([
    "status"      => true,
    "message"     => "Blood banks fetched successfully",
    "server_time" => date('Y-m-d H:i:s'),
    "pagination"  => [
        "total_blood_banks" => $total_blood_banks,
        "current_page"      => $page,
        "per_page"          => $per_page,
        "total_pages"       => $total_pages
    ],
    "data" => [
        "blood_banks" => $blood_banks
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>