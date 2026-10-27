<?php
/**
 * Laboratories List API
 * URL example:
 *   /api/laboratories.php?city=1&search=chughtai&page=1&per_page=10
 *
 * Saare params optional hain.
 * NOTE: neeche wali include line apne project ke hisaab se adjust karein
 *       (jahan $con, BASE_URL aur $city_id define hote hain).
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
    // ---------------- Filters ----------------
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    // Page ki tarah: city na aaye to default $city_id; city=0 ya "" matlab All Cities
    if (isset($_GET['city'])) {
        $city = (int)$_GET['city'];
    } else {
        $city = isset($city_id) ? (int)$city_id : 0;
    }

    // ---------------- Pagination ----------------
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $per_page = (int)($_GET['per_page'] ?? 10);
    if ($per_page < 1)  { $per_page = 10; }
    if ($per_page > 50) { $per_page = 50; }
    $offset = ($page - 1) * $per_page;

    // ---------------- WHERE (prepared statement) ----------------
    $where  = "WHERE u.status = 1 AND l.approve = 1";
    $types  = '';
    $params = [];

    if ($search !== '') {
        $where   .= " AND l.lab_name LIKE ?";
        $types   .= 's';
        $params[] = '%' . $search . '%';
    }
    if ($city > 0) {
        $where   .= " AND l.city_id = ?";
        $types   .= 'i';
        $params[] = $city;
    }

    $from = "FROM laboratories l
             LEFT JOIN cities c ON l.city_id = c.city_id
             LEFT JOIN users u ON u.user_id = l.user_id
             LEFT JOIN (
                 SELECT user_id, AVG(stars) AS avg_stars, COUNT(*) AS total_reviews
                 FROM feedback
                 GROUP BY user_id
             ) f ON f.user_id = l.user_id";

    // ---------------- Total count ----------------
    $count_stmt = mysqli_prepare($con, "SELECT COUNT(*) AS total $from $where");
    if ($count_stmt === false) { throw new Exception(mysqli_error($con)); }
    if ($types !== '') { mysqli_stmt_bind_param($count_stmt, $types, ...$params); }
    mysqli_stmt_execute($count_stmt);
    $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
    mysqli_stmt_close($count_stmt);

    $total_pages = (int)ceil($total / $per_page);

    // ---------------- Records ----------------
    $sql = "SELECT l.lab_id, l.lab_name, l.city_id, c.city_name,
                   f.avg_stars, f.total_reviews
            $from $where
            ORDER BY l.lab_name ASC
            LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt === false) { throw new Exception(mysqli_error($con)); }

    $rec_params = array_merge($params, [$per_page, $offset]);
    mysqli_stmt_bind_param($stmt, $types . 'ii', ...$rec_params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $base = defined('BASE_URL') ? BASE_URL : '';
    $labs = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $avg = $row['avg_stars'] !== null ? round((float)$row['avg_stars'], 1) : 0;

        $labs[] = [
            'lab_id'        => (int)$row['lab_id'],
            'lab_name'      => $row['lab_name'],
            'city_id'       => (int)$row['city_id'],
            'city_name'     => $row['city_name'],
            'rating'        => $avg,                    // 0 = New
            'is_new'        => $avg <= 0,
            'total_reviews' => (int)($row['total_reviews'] ?? 0),
            'detail_url'    => $base . 'lab-detail?lab_id=' . (int)$row['lab_id'],
        ];
    }
    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => true,
        'filters' => [
            'search' => $search,
            'city'   => $city,
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
        'count' => count($labs),
        'data'  => $labs,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}