<?php
/**
 * Hospitals List API (public, login ki zaroorat nahi, sirf GET)
 *
 * GET /api/hospitals.php?search_hospital=civil&search_city=kohat&filter_status=1&page=1&per_page=5
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
    $search_hospital = isset($_GET['search_hospital']) ? trim($_GET['search_hospital']) : '';
    $search_city     = isset($_GET['search_city']) ? trim($_GET['search_city']) : '';
    $filter_status   = (isset($_GET['filter_status']) && $_GET['filter_status'] !== '')
                        ? (int)$_GET['filter_status'] : null;   // null = All Status

    // ---------------- Pagination ----------------
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $per_page = (int)($_GET['per_page'] ?? 5);                  // page mein 5 hai
    if ($per_page < 1)   { $per_page = 5; }
    if ($per_page > 100) { $per_page = 100; }
    $offset = ($page - 1) * $per_page;

    // ---------------- WHERE (prepared statement) ----------------
    $where  = "WHERE h.approve = 1";
    $types  = '';
    $params = [];

    if ($search_hospital !== '') {
        $where   .= " AND h.hospital_name LIKE ?";
        $types   .= 's';
        $params[] = '%' . $search_hospital . '%';
    }
    if ($search_city !== '') {
        $where   .= " AND c.city_name LIKE ?";
        $types   .= 's';
        $params[] = '%' . $search_city . '%';
    }
    if ($filter_status !== null) {
        $where   .= " AND u.status = ?";
        $types   .= 'i';
        $params[] = $filter_status;
    }

    $from = "FROM hospitals h
             LEFT JOIN cities c ON h.city_id = c.city_id
             LEFT JOIN users u ON u.user_id = h.user_id";

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
        "SELECT h.hospital_id, h.hospital_name, h.hospital_address, h.hospital_phone,
                h.hospital_pic, u.status, h.created_at, c.city_name
         $from $where
         ORDER BY h.created_at DESC
         LIMIT ? OFFSET ?");
    if ($stmt === false) { throw new Exception(mysqli_error($con)); }
    $rec_params = array_merge($params, [$per_page, $offset]);
    mysqli_stmt_bind_param($stmt, $types . 'ii', ...$rec_params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $hospitals = [];
    $serial = $offset + 1;
    while ($row = mysqli_fetch_assoc($res)) {
        $hospitals[] = [
            'serial'           => $serial++,
            'hospital_id'      => (int)$row['hospital_id'],
            'hospital_name'    => $row['hospital_name'],
            'hospital_address' => $row['hospital_address'],
            'helpline_no'      => $row['hospital_phone'],
            'city_name'        => $row['city_name'],
            'hospital_pic'     => !empty($row['hospital_pic'])
                                    ? $base . 'admin/inc/uploads/hospitals/' . $row['hospital_pic']
                                    : $base . 'admin/inc/uploads/default/hosp.jpg',
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
            'search_hospital' => $search_hospital,
            'search_city'     => $search_city,
            'filter_status'   => $filter_status,
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
        'count' => count($hospitals),
        'data'  => $hospitals,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}