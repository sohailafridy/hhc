<?php
/**
 * Doctors List API
 *
 * 1) Doctors list (search + filters + pagination)
 *    GET /api/doctors-list-api.php
 *        ?search=ahmed          (doctor ka naam, optional)
 *        &city=5                (city_id, optional. 0 ya khali = All Cities)
 *        &hospital=3            (hospital_id, optional)
 *        &specialization=7      (dr_cat_type_id, optional)
 *        &lady_doctor=1         (sirf female doctors, optional)
 *        &page=1&per_page=12
 *
 * 2) Filter dropdowns ka data (cities + specializations)
 *    GET /api/doctors-list-api.php?action=filters
 *
 * 3) City ke hospitals (get_hospitals.php ki jagah)
 *    GET /api/doctors-list-api.php?action=hospitals&city_id=5
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---- Apni existing connection file yahan include karein ($con, BASE_URL aur $city_id deti ho) ----
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(405, ["success" => false, "message" => "Sirf GET allowed hai"]);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// ======================= action=filters =======================

if ($action === 'filters') {

    // Cities
    $cities = fetch_all(
        $con,
        "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC"
    );

    // Specializations (dr_cat_types table se)
    $spec_rows = fetch_all($con, "SELECT * FROM dr_cat_types ORDER BY `type` ASC");

    $specializations = [];
    foreach ($spec_rows as $s) {
        $specializations[] = [
            "id"          => (int)$s['dr_cat_type_id'],
            "category_id" => (int)$s['dr_cat_id'],
            "name"        => $s['type'],
        ];
    }

    // Default city (web page me jo default city_id use hoti hai)
    $default_city = isset($city_id) ? (int)$city_id : 0;

    respond(200, [
        "success" => true,
        "data" => [
            "default_city_id" => $default_city,
            "cities"          => array_map(function ($c) {
                return ["id" => (int)$c['city_id'], "name" => $c['city_name']];
            }, $cities),
            "specializations" => $specializations,
        ],
    ]);
}

// ======================= action=hospitals =======================

if ($action === 'hospitals') {

    $city_param = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;

    if ($city_param > 0) {
        $rows = fetch_all(
            $con,
            "SELECT DISTINCT h.hospital_id, h.hospital_name
             FROM hospitals h
             LEFT JOIN users u ON u.user_id = h.user_id
             WHERE u.status = 1 AND h.city_id = ?
             ORDER BY h.hospital_name ASC",
            'i',
            [$city_param]
        );
    } else {
        $rows = fetch_all(
            $con,
            "SELECT DISTINCT h.hospital_id, h.hospital_name
             FROM hospitals h
             LEFT JOIN users u ON u.user_id = h.user_id
             WHERE u.status = 1
             ORDER BY h.hospital_name ASC"
        );
    }

    respond(200, [
        "success" => true,
        "data" => array_map(function ($h) {
            return ["id" => (int)$h['hospital_id'], "name" => $h['hospital_name']];
        }, $rows),
    ]);
}

// ======================= action=list (default) =======================

$search         = isset($_GET['search']) ? trim($_GET['search']) : '';
$hospital       = isset($_GET['hospital']) ? (int)$_GET['hospital'] : 0;
$specialization = isset($_GET['specialization']) ? (int)$_GET['specialization'] : 0;
$lady_doctor    = (isset($_GET['lady_doctor']) && $_GET['lady_doctor'] == 1) ? 1 : 0;

// City: agar param aaya hi nahi to default city (jaise web page me). 0 ya khali = All Cities
if (isset($_GET['city'])) {
    $city = (int)$_GET['city'];
} else {
    $city = isset($city_id) ? (int)$city_id : 0;
}

$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = isset($_GET['per_page']) ? max(1, min(50, (int)$_GET['per_page'])) : 12;
$offset   = ($page - 1) * $per_page;

// WHERE ko dynamic banana (sab values prepared statement se jayengi)
$where  = "WHERE u.status = 1 AND d.approve = 1";
$types  = '';
$params = [];

if ($search !== '') {
    $where   .= " AND d.doctor_name LIKE ?";
    $types   .= 's';
    $params[] = '%' . $search . '%';
}
if ($hospital > 0) {
    $where   .= " AND d.hospital_id = ?";
    $types   .= 'i';
    $params[] = $hospital;
}
if ($specialization > 0) {
    $where   .= " AND d.cat_type_id = ?";
    $types   .= 'i';
    $params[] = $specialization;
}
if ($city > 0) {
    $where   .= " AND d.city_id = ?";
    $types   .= 'i';
    $params[] = $city;
}
if ($lady_doctor === 1) {
    $where .= " AND d.gender = 'Female'";
}

// Total count (poori list load kiye baghair)
$count_row = fetch_one(
    $con,
    "SELECT COUNT(*) AS total
     FROM doctors d
     LEFT JOIN users u ON u.user_id = d.user_id
     $where",
    $types,
    $params
);
$total       = $count_row ? (int)$count_row['total'] : 0;
$total_pages = $total > 0 ? (int)ceil($total / $per_page) : 0;

// Doctors (rating ek hi query me)
$list_types   = $types . 'ii';
$list_params  = array_merge($params, [$per_page, $offset]);

$rows = fetch_all(
    $con,
    "SELECT d.doctor_id, d.user_id, d.doctor_name, d.doctor_pic, d.gender,
            d.experience_years, d.short_detail,
            dct.type AS doctor_of, c.city_name, c.city_id, h.hospital_name,
            f.avg_stars, f.total_reviews
     FROM doctors d
     LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
     LEFT JOIN cities c ON d.city_id = c.city_id
     LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
     LEFT JOIN users u ON u.user_id = d.user_id
     LEFT JOIN (
         SELECT user_id, AVG(stars) AS avg_stars, COUNT(*) AS total_reviews
         FROM feedback
         WHERE status = 1
         GROUP BY user_id
     ) f ON f.user_id = d.user_id
     $where
     ORDER BY d.doctor_name ASC
     LIMIT ? OFFSET ?",
    $list_types,
    $list_params
);

$doctors = [];
foreach ($rows as $r) {
    $doctors[] = [
        "doctor_id"        => (int)$r['doctor_id'],
        "name"             => $r['doctor_name'],
        "picture"          => !empty($r['doctor_pic'])
            ? BASE_URL . 'admin/inc/uploads/doctors/' . $r['doctor_pic']
            : null,
        "specialty"        => !empty($r['doctor_of']) ? $r['doctor_of'] : 'Medical Specialist',
        "city_id"          => $r['city_id'] !== null ? (int)$r['city_id'] : null,
        "city"             => $r['city_name'],
        "hospital"         => $r['hospital_name'],
        "gender"           => $r['gender'],
        "experience_years" => (int)$r['experience_years'],
        "short_detail"     => $r['short_detail'],
        "rating"           => $r['avg_stars'] !== null ? round((float)$r['avg_stars'], 1) : null, // null = "New"
        "total_reviews"    => (int)$r['total_reviews'],
    ];
}

respond(200, [
    "success" => true,
    "data" => [
        "doctors"    => $doctors,
        "pagination" => [
            "page"        => $page,
            "per_page"    => $per_page,
            "total"       => $total,
            "total_pages" => $total_pages,
            "has_more"    => $page < $total_pages,
        ],
    ],
]);