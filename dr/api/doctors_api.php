<?php
/**
 * Doctors List API
 * URL example:
 *   /api/doctors.php?city=1&hospital=5&specialization=3&search=ali&lady_doctor=1&page=1&per_page=12
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
    $search         = isset($_GET['search']) ? trim($_GET['search']) : '';
    $hospital       = isset($_GET['hospital']) ? (int)$_GET['hospital'] : 0;
    $specialization = isset($_GET['specialization']) ? (int)$_GET['specialization'] : 0;
    // Page ki tarah: city na aaye to default $city_id; city=0 ya "" matlab All Cities
    if (isset($_GET['city'])) {
        $city = (int)$_GET['city'];
    } else {
        $city = isset($city_id) ? (int)$city_id : 0;
    }
    $lady_doctor = (isset($_GET['lady_doctor']) && $_GET['lady_doctor'] == 1) ? 1 : 0;

    // ---------------- Pagination ----------------
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $per_page = (int)($_GET['per_page'] ?? 12);
    if ($per_page < 1)  { $per_page = 12; }
    if ($per_page > 50) { $per_page = 50; }
    $offset = ($page - 1) * $per_page;

    // ---------------- WHERE (prepared statement) ----------------
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

    $from = "FROM doctors d
             LEFT JOIN users u ON u.user_id = d.user_id
             LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
             LEFT JOIN cities c ON d.city_id = c.city_id
             LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
             LEFT JOIN (
                 SELECT user_id, AVG(stars) AS avg_stars, COUNT(*) AS total_reviews
                 FROM feedback
                 GROUP BY user_id
             ) f ON f.user_id = d.user_id";

    // ---------------- Total count ----------------
    $count_stmt = mysqli_prepare($con, "SELECT COUNT(*) AS total $from $where");
    if ($count_stmt === false) { throw new Exception(mysqli_error($con)); }
    if ($types !== '') { mysqli_stmt_bind_param($count_stmt, $types, ...$params); }
    mysqli_stmt_execute($count_stmt);
    $total_doctors = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
    mysqli_stmt_close($count_stmt);

    $total_pages = (int)ceil($total_doctors / $per_page);

    // ---------------- Records ----------------
    $sql = "SELECT d.doctor_id, d.doctor_name, d.doctor_pic, d.short_detail, d.gender,
                   d.experience_years, d.emergency_status, d.city_id, d.hospital_id, d.cat_type_id,
                   dct.type AS doctor_of, c.city_name, h.hospital_name,
                   f.avg_stars, f.total_reviews
            $from $where
            ORDER BY d.doctor_name ASC
            LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt === false) { throw new Exception(mysqli_error($con)); }

    $rec_types  = $types . 'ii';
    $rec_params = array_merge($params, [$per_page, $offset]);
    mysqli_stmt_bind_param($stmt, $rec_types, ...$rec_params);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $base = defined('BASE_URL') ? BASE_URL : '';
    $doctors = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $avg = $row['avg_stars'] !== null ? round((float)$row['avg_stars'], 1) : 0;
        $emergency = (int)$row['emergency_status'];

        $doctors[] = [
            'doctor_id'        => (int)$row['doctor_id'],
            'doctor_name'      => $row['doctor_name'],
            'doctor_pic'       => !empty($row['doctor_pic'])
                                    ? $base . 'admin/inc/uploads/doctors/' . $row['doctor_pic']
                                    : null,
            'specialization'   => !empty($row['doctor_of']) ? $row['doctor_of'] : 'Medical Specialist',
            'qualification'    => $row['short_detail'] ?? '',
            'gender'           => $row['gender'],
            'is_lady_doctor'   => strtolower((string)$row['gender']) === 'female',
            'city_id'          => (int)$row['city_id'],
            'city_name'        => $row['city_name'],
            'hospital_id'      => (int)$row['hospital_id'],
            'hospital_name'    => $row['hospital_name'],
            'experience_years' => (int)($row['experience_years'] ?? 0),
            'rating'           => $avg,                       // 0 = New
            'is_new'           => $avg <= 0,
            'total_reviews'    => (int)($row['total_reviews'] ?? 0),
            'is_available'     => $emergency !== 1,           // emergency_status 1 = Unavailable
            'availability'     => $emergency === 1 ? 'Unavailable' : 'Available',
            'profile_url'      => $base . 'pages/doctor-detail?doctor_id=' . (int)$row['doctor_id'],
        ];
    }
    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => true,
        'filters' => [
            'search'         => $search,
            'city'           => $city,
            'hospital'       => $hospital,
            'specialization' => $specialization,
            'lady_doctor'    => $lady_doctor,
        ],
        'pagination' => [
            'page'          => $page,
            'per_page'      => $per_page,
            'total_records' => $total_doctors,
            'total_pages'   => $total_pages,
            'showing_from'  => $total_doctors > 0 ? $offset + 1 : 0,
            'showing_to'    => min($offset + $per_page, $total_doctors),
            'has_next'      => $page < $total_pages,
            'has_prev'      => $page > 1,
        ],
        'count' => count($doctors),
        'data'  => $doctors,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}