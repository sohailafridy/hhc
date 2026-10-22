<?php
/**
 * City List API (Admin)
 *
 * 1) Cities ki list
 *    GET /api/city-list-api.php
 *        ?search_city=kohat      (city ka naam, optional)
 *        &filter_status=1        (1 = Active, 0 = Inactive, khali = All)
 *        &page=1&per_page=5
 *
 * 2) City delete (soft delete: status = 0)
 *    POST /api/city-list-api.php
 *    body (JSON ya form-data): { "action": "delete", "city_id": 3 }
 *
 * NOTE: Is API me login check nahi hai.
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---- Apni existing connection file ($con aur BASE_URL deti ho). Path apne hisaab se theek karein ----
require_once __DIR__ . '/../config.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', 'https://yourdomain.com/'); // apna domain likhein
}

mysqli_set_charset($con, 'utf8mb4');

// ======================= Helpers =======================

function respond($code, $data)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function fetch_all($con, $sql, $types = '', $params = [])
{
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        respond(500, ["success" => false, "message" => "Query error"]);
    }
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

function fetch_one($con, $sql, $types = '', $params = [])
{
    $rows = fetch_all($con, $sql, $types, $params);
    return $rows ? $rows[0] : null;
}

// ======================= GET: cities list =======================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $search_city   = isset($_GET['search_city']) ? trim($_GET['search_city']) : '';
    $filter_status = isset($_GET['filter_status']) ? trim($_GET['filter_status']) : '';

    $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = isset($_GET['per_page']) ? max(1, min(50, (int)$_GET['per_page'])) : 5;
    $offset   = ($page - 1) * $per_page;

    // WHERE (original page ki tarah approve = 1 hamesha lagta hai)
    $where  = "WHERE cities.approve = 1";
    $types  = '';
    $params = [];

    if ($search_city !== '') {
        $where   .= " AND cities.city_name LIKE ?";
        $types   .= 's';
        $params[] = '%' . $search_city . '%';
    }
    if ($filter_status === '0' || $filter_status === '1') {
        $where   .= " AND cities.status = ?";
        $types   .= 'i';
        $params[] = (int)$filter_status;
    }

    // Total
    $count_row = fetch_one($con, "SELECT COUNT(*) AS total FROM cities $where", $types, $params);
    $total       = $count_row ? (int)$count_row['total'] : 0;
    $total_pages = $total > 0 ? (int)ceil($total / $per_page) : 0;

    // Data
    $rows = fetch_all(
        $con,
        "SELECT cities.city_id, cities.city_name, cities.city_pic,
                provinces.p_name AS province_name, cities.status, cities.created_at
         FROM cities
         LEFT JOIN provinces ON cities.province_id = provinces.p_id
         $where
         ORDER BY cities.created_at DESC
         LIMIT ? OFFSET ?",
        $types . 'ii',
        array_merge($params, [$per_page, $offset])
    );

    $cities = [];
    foreach ($rows as $r) {
        $cities[] = [
            "city_id"       => (int)$r['city_id'],
            "name"          => $r['city_name'],
            "picture"       => !empty($r['city_pic'])
                ? BASE_URL . 'admin/inc/uploads/cities/' . $r['city_pic']
                : BASE_URL . 'admin/inc/uploads/default/city.jpg',
            "province"      => $r['province_name'],
            "status"        => (int)$r['status'],
            "status_label"  => ((int)$r['status'] === 1) ? 'Active' : 'Inactive',
            "created_at"    => !empty($r['created_at']) ? date('d M Y, h:i A', strtotime($r['created_at'])) : null,
        ];
    }

    respond(200, [
        "success" => true,
        "data" => [
            "cities"     => $cities,
            "pagination" => [
                "page"        => $page,
                "per_page"    => $per_page,
                "total"       => $total,
                "total_pages" => $total_pages,
                "has_more"    => $page < $total_pages,
            ],
        ],
    ]);
}

// ======================= POST: delete city =======================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $action  = isset($input['action']) ? $input['action'] : '';
    $city_id = isset($input['city_id']) ? (int)$input['city_id'] : 0;

    if ($action !== 'delete') {
        respond(400, ["success" => false, "message" => "action = delete bhejein"]);
    }
    if ($city_id <= 0) {
        respond(422, ["success" => false, "message" => "city_id zaroori hai"]);
    }

    $exists = fetch_one($con, "SELECT city_id FROM cities WHERE city_id = ?", 'i', [$city_id]);
    if (!$exists) {
        respond(404, ["success" => false, "message" => "City nahi mili"]);
    }

    // Soft delete (original page ki tarah status = 0, picture file delete nahi hoti)
    $stmt = mysqli_prepare($con, "UPDATE cities SET status = 0 WHERE city_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $city_id);

    if (mysqli_stmt_execute($stmt)) {
        respond(200, ["success" => true, "message" => "City delete ho gayi"]);
    }

    respond(500, ["success" => false, "message" => "City delete nahi ho saki"]);
}

respond(405, ["success" => false, "message" => "Method allowed nahi"]);