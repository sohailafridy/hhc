<?php
/**
 * Cities List API (public, login ki zaroorat nahi, sirf GET)
 *
 * GET /api/cities.php?search_city=koh&filter_status=1&page=1&per_page=5
 *
 * Saare params optional hain.
 * NOTE: include line apne project ke hisaab se adjust karein
 *       (jahan $con aur BASE_URL define hote hain).
 */
include '../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only GET allowed']);
    exit();
}

try {
    $base = defined('BASE_URL') ? BASE_URL : '';

    // ---------------- Filters ----------------
    $search_city   = isset($_GET['search_city']) ? trim($_GET['search_city']) : '';
    $filter_status = (isset($_GET['filter_status']) && $_GET['filter_status'] !== '')
                        ? (int)$_GET['filter_status'] : null;   // null = All Status

    // ---------------- Pagination ----------------
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $per_page = (int)($_GET['per_page'] ?? 5);                  // page mein 5 hai
    if ($per_page < 1)   { $per_page = 5; }
    if ($per_page > 100) { $per_page = 100; }
    $offset = ($page - 1) * $per_page;

    // ---------------- WHERE (prepared statement) ----------------
    $where  = "WHERE ci.approve = 1";
    $types  = '';
    $params = [];

    if ($search_city !== '') {
        $where   .= " AND ci.city_name LIKE ?";
        $types   .= 's';
        $params[] = '%' . $search_city . '%';
    }
    if ($filter_status !== null) {
        $where   .= " AND ci.status = ?";
        $types   .= 'i';
        $params[] = $filter_status;
    }

    $from = "FROM cities ci
             LEFT JOIN provinces p ON ci.province_id = p.p_id";

    // ---------------- Total count ----------------
    $count_stmt = mysqli_prepare($con, "SELECT COUNT(*) AS total $from $where");
    if ($count_stmt === false) { throw new Exception(mysqli_error($con)); }
    if ($types !== '') { mysqli_stmt_bind_param($count_stmt, $types, ...$params); }
    mysqli_stmt_execute($count_stmt);
    $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
    mysqli_stmt_close($count_stmt);
    $total_pages = (int)ceil($total / $per_page);

    // ---------------- Records ----------------
    $stmt = mysqli_prepare($con,
        "SELECT ci.city_id, ci.city_name, ci.city_pic, ci.province_id,
                p.p_name AS province_name, ci.status, ci.created_at
         $from $where
         ORDER BY ci.created_at DESC
         LIMIT ? OFFSET ?");
    if ($stmt === false) { throw new Exception(mysqli_error($con)); }
    $rec_params = array_merge($params, [$per_page, $offset]);
    mysqli_stmt_bind_param($stmt, $types . 'ii', ...$rec_params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $cities = [];
    $serial = $offset + 1;
    while ($row = mysqli_fetch_assoc($res)) {
        $cities[] = [
            'serial'           => $serial++,
            'city_id'          => (int)$row['city_id'],
            'city_name'        => $row['city_name'],
            'city_pic'         => !empty($row['city_pic'])
                                    ? $base . 'admin/inc/uploads/cities/' . $row['city_pic']
                                    : $base . 'admin/inc/uploads/default/city.jpg',
            'province_id'      => (int)$row['province_id'],
            'province_name'    => $row['province_name'],
            'status'           => (int)$row['status'],
            'status_label'     => (int)$row['status'] === 1 ? 'Active' : 'Inactive',
            'created_at'       => $row['created_at'],
            'created_at_label' => date('d M Y, h:i A', strtotime($row['created_at'])),
        ];
    }
    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => true,
        'filters' => [
            'search_city'   => $search_city,
            'filter_status' => $filter_status,
        ],
        'pagination' => [
            'page'          => $page,
            'per_page'      => $per_page,
            'total_records' => $total,
            'total_pages'   => $total_pages,
            'showing_from'  => $total > 0 ? $offset + 1 : 0,
            'showing_to'    => min($offset + $per_page, $total),
            'has_next'      => $page < $total_pages,
            'has_prev'      => $page > 1,
        ],
        'count' => count($cities),
        'data'  => $cities,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}