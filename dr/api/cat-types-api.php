<?php
/**
 * Doctor Category Types API (dr_cat_types table)
 *
 * GET /api/doctor-cat-types-api.php
 *     ?cat_id=1       (sirf ek category ki types, optional)
 *     &search=heart   (type ke naam me search, optional)
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---- Apni existing connection file yahan include karein ($con deti ho) ----
require_once __DIR__ . '/../config.php';

mysqli_set_charset($con, 'utf8mb4');

function respond($code, $data)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** 0000-00-00 jaisi invalid date par null deta hai */
function fmt_date($d)
{
    if (empty($d) || strpos($d, '0000-00-00') === 0) {
        return null;
    }
    return date('d M Y, h:i A', strtotime($d));
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(405, ["success" => false, "message" => "Sirf GET allowed hai"]);
}

$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Aapki query + optional filters
$sql    = "SELECT * FROM dr_cat_types WHERE 1=1";
$types  = '';
$params = [];

if ($cat_id > 0) {
    $sql     .= " AND dr_cat_id = ?";
    $types   .= 'i';
    $params[] = $cat_id;
}
if ($search !== '') {
    $sql     .= " AND `type` LIKE ?";
    $types   .= 's';
    $params[] = '%' . $search . '%';
}

$sql .= " ORDER BY dr_cat_type_id ASC";

$stmt = mysqli_prepare($con, $sql);
if (!$stmt) {
    respond(500, ["success" => false, "message" => "Query error"]);
}
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = [
        "id"          => (int)$row['dr_cat_type_id'],
        "category_id" => (int)$row['dr_cat_id'],
        "type"        => $row['type'],
        "created_at"  => fmt_date($row['created_at']),
        "updated_at"  => fmt_date($row['updated_at']),
    ];
}
mysqli_stmt_close($stmt);

respond(200, [
    "success" => true,
    "data" => [
        "types" => $items,
        "total" => count($items),
    ],
]);