<?php
/**
 * Hospitals Listing API
 * Returns approved hospitals (NO duplicate cities data)
 * Method: GET
 * URL: /api/hospitals_api.php
 * 
 * Query Parameters (optional):
 * - search=xyz        (hospital name search)
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
$where_clause = " AND h.approve = 1 AND u.status = 1 ";

if (!empty($search)) {
    $where_clause .= " AND h.hospital_name LIKE '%$search%' ";
}
if (!empty($city_id) && $city_id > 0) {
    $where_clause .= " AND h.city_id = $city_id ";
}

// ============================================
// STEP 5: Total Count
// ============================================
$count_query = "
    SELECT COUNT(*) AS total
    FROM hospitals h
    LEFT JOIN cities c ON h.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = h.user_id
    WHERE 1=1 $where_clause
";
$count_result = mysqli_query($con, $count_query);

if (!$count_result) {
    echo json_encode(["status" => false, "message" => "Count query failed: " . mysqli_error($con)]);
    exit();
}

$total_row       = mysqli_fetch_assoc($count_result);
$total_hospitals = (int)$total_row['total'];
$total_pages     = ($total_hospitals > 0) ? ceil($total_hospitals / $per_page) : 0;

// ============================================
// STEP 6: Main Query
// ============================================
$query = "
    SELECT 
        h.hospital_id,
        h.hospital_name,
        h.hospital_address,
        h.hospital_phone,
        h.hospital_pic,
        h.city_id,
        h.updated_at,
        c.city_name,
        (SELECT AVG(f.stars) FROM feedback f WHERE f.user_id = h.user_id AND f.status = 1) AS avg_rating,
        (SELECT COUNT(*) FROM feedback f WHERE f.user_id = h.user_id AND f.status = 1) AS total_reviews
    FROM hospitals h
    LEFT JOIN cities c ON h.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = h.user_id
    WHERE 1=1 $where_clause
    ORDER BY h.hospital_name ASC
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
$hospitals = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Hospital Pic Full URL
    $hospital_pic = '';
    if (!empty($row['hospital_pic'])) {
        $hospital_pic = BASE_URL . 'admin/inc/uploads/hospitals/' . $row['hospital_pic'];
    }

    $avg_rating    = $row['avg_rating'] ? round((float)$row['avg_rating'], 1) : 0;
    $total_reviews = (int)$row['total_reviews'];

    $hospitals[] = [
        "hospital_id"      => (int)$row['hospital_id'],
        "hospital_name"    => $row['hospital_name'],
        "hospital_pic"     => $hospital_pic,
        "hospital_address" => $row['hospital_address'] ?? '',
        "hospital_phone"   => $row['hospital_phone'] ?? '',
        "city_id"          => (int)$row['city_id'],
        "city_name"        => $row['city_name'] ?? '',
        "rating"           => $avg_rating,
        "total_reviews"    => $total_reviews,
        "updated_at"       => $row['updated_at'] ?? ''
    ];
}

// ============================================
// STEP 8: Final Response
// ============================================
echo json_encode([
    "status"      => true,
    "message"     => "Hospitals fetched successfully",
    "server_time" => date('Y-m-d H:i:s'),
    "pagination"  => [
        "total_hospitals" => $total_hospitals,
        "current_page"    => $page,
        "per_page"        => $per_page,
        "total_pages"     => $total_pages
    ],
    "data" => [
        "hospitals" => $hospitals
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>