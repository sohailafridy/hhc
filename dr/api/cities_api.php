<?php
/**
 * Cities API (Public - No Login Required)
 * Returns active + approved cities for mobile app dropdowns
 * Method: GET
 * URL: /api/cities_api.php
 * 
 * Query Parameters (optional):
 * - search=xyz        (city name search)
 * - province=1        (province id filter)
 * - page=1            (pagination)
 * - limit=100         (per page, max 500)
 * - all=1             (saari cities ek saath, pagination ignore)
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
$search     = isset($_GET['search']) ? mysqli_real_escape_string($con, trim($_GET['search'])) : '';
$province_id = isset($_GET['province']) ? (int)$_GET['province'] : 0;
$fetch_all   = isset($_GET['all']) && $_GET['all'] == 1;
$page        = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page    = isset($_GET['limit']) ? min(500, max(1, (int)$_GET['limit'])) : 100;
$offset      = ($page - 1) * $per_page;

// ============================================
// STEP 4: WHERE Clause
// ============================================
// Sirf active + approved cities (admin list ki tarah)
$where_clause = " AND c.status = 1 AND c.approve = 1 ";

if (!empty($search)) {
    $where_clause .= " AND c.city_name LIKE '%$search%' ";
}
if (!empty($province_id) && $province_id > 0) {
    $where_clause .= " AND c.province_id = $province_id ";
}

// ============================================
// STEP 5: Total Count
// ============================================
$count_query = "
    SELECT COUNT(*) AS total 
    FROM cities c 
    LEFT JOIN provinces p ON c.province_id = p.p_id
    WHERE 1=1 $where_clause
";
$count_result = mysqli_query($con, $count_query);

if (!$count_result) {
    echo json_encode(["status" => false, "message" => "Count query failed: " . mysqli_error($con)]);
    exit();
}

$total_row    = mysqli_fetch_assoc($count_result);
$total_cities = (int)$total_row['total'];
$total_pages  = ($total_cities > 0) ? ceil($total_cities / $per_page) : 0;

// ============================================
// STEP 6: Main Query
// ============================================
$limit_clause = "";
if (!$fetch_all) {
    $limit_clause = " LIMIT $per_page OFFSET $offset ";
}

$query = "
    SELECT 
        c.city_id,
        c.city_name,
        c.city_pic,
        c.province_id,
        c.updated_at,
        p.p_name AS province_name
    FROM cities c 
    LEFT JOIN provinces p ON c.province_id = p.p_id
    WHERE 1=1 $where_clause
    ORDER BY c.city_name ASC
    $limit_clause
";

$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(["status" => false, "message" => "Query failed: " . mysqli_error($con)]);
    exit();
}

// ============================================
// STEP 7: Data Prepare
// ============================================
$cities = [];

while ($row = mysqli_fetch_assoc($result)) {
    // City Pic Full URL
    $city_pic = '';
    if (!empty($row['city_pic'])) {
        $city_pic = BASE_URL . 'admin/inc/uploads/cities/' . $row['city_pic'];
    }

    $cities[] = [
        "city_id"       => (int)$row['city_id'],
        "city_name"     => $row['city_name'],
        "city_pic"      => $city_pic,
        "province_id"   => (int)($row['province_id'] ?? 0),
        "province_name" => $row['province_name'] ?? '',
        "updated_at"    => $row['updated_at'] ?? ''
    ];
}

// ============================================
// STEP 8: Final Response
// ============================================
echo json_encode([
    "status"      => true,
    "message"     => "Cities fetched successfully",
    "server_time" => date('Y-m-d H:i:s'),
    "pagination"  => [
        "total_cities" => $total_cities,
        "current_page" => $fetch_all ? 1 : $page,
        "per_page"     => $fetch_all ? $total_cities : $per_page,
        "total_pages"  => $fetch_all ? 1 : $total_pages
    ],
    "data" => [
        "cities" => $cities
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>