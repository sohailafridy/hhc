<?php
// GET api/doctors.php?search=&city_id=&hospital_id=&specialization_id=&lady_doctor=1&page=1&per_page=12&updated_since=2026-10-01
require_once __DIR__ . '/_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('Method not allowed', 405);

$search   = trim($_GET['search'] ?? '');
$cityId   = (int)($_GET['city_id'] ?? 0);
$hospId   = (int)($_GET['hospital_id'] ?? 0);
$specId   = (int)($_GET['specialization_id'] ?? 0);
$lady     = !empty($_GET['lady_doctor']);
$since    = trim($_GET['updated_since'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = min(50, max(1, (int)($_GET['per_page'] ?? 12)));
$offset   = ($page - 1) * $perPage;

// ---- WHERE ----
$where  = ['u.status = 1', 'd.approve = 1'];
$types  = '';
$params = [];

if ($search !== '') {
    $where[] = 'd.doctor_name LIKE ?';
    $types .= 's'; $params[] = '%' . $search . '%';
}
if ($cityId > 0) {
    $where[] = 'd.city_id = ?';
    $types .= 'i'; $params[] = $cityId;
}
if ($specId > 0) {
    $where[] = 'd.dr_cat_type_id = ?';
    $types .= 'i'; $params[] = $specId;
}
if ($lady) {
    $where[] = 'd.is_lady = 1';
}
if ($hospId > 0) {
    $where[] = 'EXISTS (SELECT 1 FROM doctor_in_hospital dh
                        WHERE dh.doctor_id = d.doctor_id AND dh.hospital_id = ? AND dh.inactive = 0)';
    $types .= 'i'; $params[] = $hospId;
}
if ($since !== '') {
    $where[] = 'd.updated_at >= ?';
    $types .= 's'; $params[] = $since;
}
$whereSql = implode(' AND ', $where);

$from = "FROM doctors d
         JOIN users u ON u.user_id = d.user_id
         LEFT JOIN dr_cat_types t ON t.dr_cat_type_id = d.dr_cat_type_id
         LEFT JOIN cities c ON c.city_id = d.city_id
         LEFT JOIN (
             SELECT user_id, ROUND(AVG(stars), 1) AS avg_stars, COUNT(*) AS cnt
             FROM feedback WHERE status = 1 GROUP BY user_id
         ) f ON f.user_id = d.user_id";

// ---- total ----
$total = (int)(db_one($conn, "SELECT COUNT(*) AS total $from WHERE $whereSql", $types, $params)['total'] ?? 0);

// ---- page data ----
$sql = "SELECT d.doctor_id, d.doctor_name, d.qualification, d.experience, d.photo,
               d.is_lady, d.emergency, d.created_at, d.updated_at,
               t.type AS specialization, c.city_name,
               COALESCE(f.avg_stars, 0) AS rating, COALESCE(f.cnt, 0) AS reviews_count
        $from
        WHERE $whereSql
        ORDER BY d.doctor_id DESC
        LIMIT $perPage OFFSET $offset";
$rows = db_all($conn, $sql, $types, $params);

$data = array_map(function ($r) {
    return [
        'doctor_id'      => (int)$r['doctor_id'],
        'name'           => $r['doctor_name'],
        'qualification'  => $r['qualification'],
        'specialization' => $r['specialization'],
        'city'           => $r['city_name'],
        'experience'     => $r['experience'] !== null ? (int)$r['experience'] : null,
        'photo'          => photo_url($r['photo']),
        'is_lady_doctor' => (bool)$r['is_lady'],
        'is_available'   => (bool)$r['emergency'],
        'is_new'         => strtotime($r['created_at']) > strtotime('-30 days'),
        'rating'         => (float)$r['rating'],
        'reviews_count'  => (int)$r['reviews_count'],
        'updated_at'     => $r['updated_at'],
    ];
}, $rows);

json_out([
    'success'    => true,
    'data'       => $data,
    'pagination' => [
        'page'        => $page,
        'per_page'    => $perPage,
        'total'       => $total,
        'total_pages' => (int)ceil($total / $perPage),
    ],
]);