<?php
/**
 * Doctors List API
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
    if (!isset($con)) {
        throw new Exception("Database connection (\$con) not found in config.php");
    }

    // ---------------- Filters ----------------
    $city_id     = isset($_GET['city_id']) ? (int)$_GET['city_id'] : null;
    $hospital_id = isset($_GET['hospital_id']) ? (int)$_GET['hospital_id'] : null;
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

    // ---------------- Pagination ----------------
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $per_page = (int)($_GET['per_page'] ?? 10);
    if ($per_page < 1)   { $per_page = 10; }
    if ($per_page > 100) { $per_page = 100; }
    $offset = ($page - 1) * $per_page;

    // ---------------- WHERE ----------------
    // NOTE: Yahan user_type_id = 2 ki bajaye hum status check kar rahe hain. 
    // Agar aapke database mein doctors ki ID 2 hai, toh aap user_type_id = 2 bhi use kar sakte hain.
    $where  = "WHERE u.status = 1"; 
    $types  = '';
    $params = [];

    if ($city_id !== null) {
        $where   .= " AND ci.city_id = ?";
        $types   .= 'i';
        $params[] = $city_id;
    }
    if ($hospital_id !== null) {
        $where   .= " AND h.hospital_id = ?";
        $types   .= 'i';
        $params[] = $hospital_id;
    }
    if ($category_id !== null) {
        $where   .= " AND c.dr_cat_id = ?";
        $types   .= 'i';
        $params[] = $category_id;
    }

    // ---------------- JOINs ----------------
    // Maine yahan INNER JOIN use kiya hai hospitals ke sath, taake sirf woh doctors aayein jinka hospital assigned hai.
    $from = "FROM `users` u
             INNER JOIN `doctor_in_hospital` dh ON dh.doctor_id = u.user_id
             INNER JOIN `hospitals` h ON h.hospital_id = dh.hospital_id
             LEFT JOIN `cities` ci ON ci.city_id = h.city_id
             LEFT JOIN `dr_categories` c ON c.dr_cat_id = u.user_type_id";

    // ---------------- Total count ----------------
    $count_sql = "SELECT COUNT(DISTINCT u.user_id) AS total $from $where";
    $count_stmt = mysqli_prepare($con, $count_sql);
    if ($count_stmt === false) { throw new Exception(mysqli_error($con)); }
    
    if ($types !== '') { 
        mysqli_stmt_bind_param($count_stmt, $types, ...$params); 
    }
    mysqli_stmt_execute($count_stmt);
    $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
    mysqli_stmt_close($count_stmt);
    
    $total_pages = (int)ceil($total / $per_page);

    // ---------------- Records ----------------
    $sql = "SELECT 
                u.user_id AS doctor_id, 
                u.username AS doctor_name, 
                u.reference AS qualification, 
                c.cat_name AS specialty,
                ci.city_name,
                h.hospital_name,
                h.hospital_pic,
                u.status AS is_available
            $from $where 
            GROUP BY u.user_id 
            ORDER BY u.username ASC 
            LIMIT ? OFFSET ?";
            
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt === false) { throw new Exception(mysqli_error($con)); }
    
    $rec_params = array_merge($params, [$per_page, $offset]);
    mysqli_stmt_bind_param($stmt, $types . 'ii', ...$rec_params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $doctors = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $doctors[] = [
            'doctor_id'    => (int)$row['doctor_id'],
            'doctor_name'  => $row['doctor_name'] ? $row['doctor_name'] : 'N/A',
            'qualification'=> $row['qualification'] ? $row['qualification'] : 'N/A',
            'specialty'    => $row['specialty'] ? $row['specialty'] : 'General Physician',
            'city_name'    => $row['city_name'] ? $row['city_name'] : 'N/A',
            'hospital_name'=> $row['hospital_name'] ? $row['hospital_name'] : 'N/A',
            'is_available' => (int)$row['is_available'] === 1 ? true : false
        ];
    }
    mysqli_stmt_close($stmt);

    // ---------------- Response ----------------
    echo json_encode([
        'success' => true,
        'pagination' => [
            'page'          => $page,
            'per_page'      => $per_page,
            'total_records' => $total,
            'total_pages'   => $total_pages,
        ],
        'count' => count($doctors),
        'data'  => $doctors,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}