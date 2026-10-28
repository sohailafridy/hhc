<?php
/**
 * Laboratories Listing API
 * Returns approved laboratories (NO duplicate cities data)
 * Method: GET
 * URL: /api/labs_api.php
 * 
 * Query Parameters (optional):
 * - search=xyz        (lab name search)
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
$where_clause = " AND l.approve = 1 AND u.status = 1 ";

if (!empty($search)) {
    $where_clause .= " AND l.lab_name LIKE '%$search%' ";
}
if (!empty($city_id) && $city_id > 0) {
    $where_clause .= " AND l.city_id = $city_id ";
}

// ============================================
// STEP 5: Total Count
// ============================================
$count_query = "
    SELECT COUNT(*) AS total 
    FROM laboratories l 
    LEFT JOIN cities c ON l.city_id = c.city_id 
    LEFT JOIN users u ON u.user_id = l.user_id
    WHERE 1=1 $where_clause
";
$count_result = mysqli_query($con, $count_query);

if (!$count_result) {
    echo json_encode(["status" => false, "message" => "Count query failed: " . mysqli_error($con)]);
    exit();
}

$total_row  = mysqli_fetch_assoc($count_result);
$total_labs = (int)$total_row['total'];
$total_pages = ($total_labs > 0) ? ceil($total_labs / $per_page) : 0;

// ============================================
// STEP 6: Main Query
// ============================================
$query = "
    SELECT 
        l.lab_id,
        l.lab_name,
        l.lab_address,
        l.lab_phone,
        l.lab_email,
        l.lab_pic,
        l.lab_type,
        l.city_id,
        l.updated_at,
        c.city_name,
        (SELECT AVG(f.stars) FROM feedback f WHERE f.user_id = l.user_id AND f.status = 1) AS avg_rating,
        (SELECT COUNT(*) FROM feedback f WHERE f.user_id = l.user_id AND f.status = 1) AS total_reviews
    FROM laboratories l 
    LEFT JOIN cities c ON l.city_id = c.city_id 
    LEFT JOIN users u ON u.user_id = l.user_id
    WHERE 1=1 $where_clause
    ORDER BY l.lab_name ASC
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
$laboratories = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Lab Pic Full URL
    $lab_pic = '';
    if (!empty($row['lab_pic'])) {
        $lab_pic = BASE_URL . 'admin/inc/uploads/labs/' . $row['lab_pic'];
    }

    $avg_rating    = $row['avg_rating'] ? round((float)$row['avg_rating'], 1) : 0;
    $total_reviews = (int)$row['total_reviews'];

    $laboratories[] = [
        "lab_id"        => (int)$row['lab_id'],
        "lab_name"      => $row['lab_name'],
        "lab_pic"       => $lab_pic,
        "lab_type"      => (int)($row['lab_type'] ?? 0),
        "city_id"       => (int)$row['city_id'],
        "city_name"     => $row['city_name'] ?? '',
        "lab_address"   => $row['lab_address'] ?? '',
        "lab_phone"     => $row['lab_phone'] ?? '',
        "lab_email"     => $row['lab_email'] ?? '',
        "rating"        => $avg_rating,
        "total_reviews" => $total_reviews,
        "updated_at"    => $row['updated_at'] ?? ''
    ];
}

// ============================================
// STEP 8: Final Response (NO cities filter_options)
// ============================================
echo json_encode([
    "status"      => true,
    "message"     => "Laboratories fetched successfully",
    "server_time" => date('Y-m-d H:i:s'),
    "pagination"  => [
        "total_labs"   => $total_labs,
        "current_page" => $page,
        "per_page"     => $per_page,
        "total_pages"  => $total_pages
    ],
    "data" => [
        "laboratories" => $laboratories
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>