<?php
/**
 * Doctor Detail API
 *
 * GET  /api/doctor_detail.php?doctor_id=5[&page=1&limit=10]
 *      -> doctor info, specialty, rating, clinical info, reviews
 *
 * POST /api/doctor_detail.php
 *      body (JSON ya form-data): doctor_id, commenter_name, commenter_gmail, comment, stars
 *      -> naya review insert
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---- Apni existing connection file yahan include karein ($con aur BASE_URL deti ho) ----
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

/** Prepared statement chala kar saari rows array me deta hai */
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

function fmt_time($t)
{
    return !empty($t) ? date('h:i A', strtotime($t)) : null;
}

/** Email ko mask karta hai: ali@gmail.com -> a***@gmail.com */
function mask_email($email)
{
    $parts = explode('@', $email);
    if (count($parts) !== 2 || $parts[0] === '') {
        return '';
    }
    return substr($parts[0], 0, 1) . '***@' . $parts[1];
}

/** Sirf approved + active doctor */
function get_doctor($con, $doctor_id)
{
    $sql = "SELECT d.*, c.city_name,u.user_id
            FROM doctors d
            LEFT JOIN cities c ON d.city_id = c.city_id
            LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
            LEFT JOIN users u ON u.user_id = d.user_id
            WHERE d.doctor_id = ? AND u.status = 1 AND d.approve = 1";
    return fetch_one($con, $sql, 'i', [$doctor_id]);
}

// ======================= GET: doctor detail =======================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
    if ($doctor_id <= 0) {
        respond(400, ["success" => false, "message" => "doctor_id zaroori hai"]);
    }

    $doctor = get_doctor($con, $doctor_id);
    if (!$doctor) {
        respond(404, ["success" => false, "message" => "Doctor nahi mila"]);
    }

    $user_id = (int)$doctor['user_id'];
    // Specialty
    $spec = fetch_one(
        $con,
        "SELECT `type` FROM dr_cat_types WHERE dr_cat_type_id = ?",
        'i',
        [(int)$doctor['cat_type_id']]
    );

    // Rating
    $rating = fetch_one(
        $con,
        "SELECT AVG(stars) AS avg_rating, COUNT(*) AS total_reviews
         FROM feedback WHERE user_id = ? AND status = 1",
        'i',
        [$user_id]
    );
    $avg_rating = $rating['avg_rating'] ? round((float)$rating['avg_rating'], 1) : 0;
    $total_reviews = (int)$rating['total_reviews'];

    // Clinical info
    $clinical_rows = fetch_all(
        $con,
        "SELECT ci.*, hospitals.hospital_name, hospitals.hospital_id
         FROM clinical_info ci
         INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id
         LEFT JOIN hospitals ON dih.hospital_id = hospitals.hospital_id
         WHERE dih.doctor_id = ? AND dih.inactive = 0
         ORDER BY ci.season, ci.shift",
        'i',
        [$doctor_id]
    );

    $clinical = [];
    foreach ($clinical_rows as $c) {
        $clinical[] = [
            "hospital_id"   => $c['hospital_id'] ? (int)$c['hospital_id'] : null,
            "hospital_name" => !empty($c['hospital_name']) ? $c['hospital_name'] : 'Personal Clinic',
            "morning" => [
                "opening" => fmt_time($c['morning_opening_time'] ?? null),
                "closing" => fmt_time($c['morning_closing_time'] ?? null),
            ],
            "evening" => [
                "opening" => fmt_time($c['evening_opening_time'] ?? null),
                "closing" => fmt_time($c['evening_closing_time'] ?? null),
            ],
            "days"     => $c['days'] ?? 'N/A',
            "off_days" => $c['off_days'] ?? 'None',
            "contact"  => $c['contact'] ?? '',
            "detail"   => $c['detail'] ?? '',
        ];
    }

    // Doctor kaunse hospitals me kaam karta hai
    $hospital_rows = fetch_all(
        $con,
        "SELECT dih.doctor_in_hosp_id, dih.hospital_id, dih.if_clinic, dih.inactive,
                dih.comment, dih.created_at, dih.updated_at, dih.doctor_id,
                h.hospital_name, c.city_name
         FROM doctor_in_hospital dih
         LEFT JOIN hospitals h ON h.hospital_id = dih.hospital_id
         LEFT JOIN cities c ON c.city_id = h.city_id
         WHERE dih.doctor_id = ? AND dih.inactive = 0
         ORDER BY dih.created_at DESC",
        'i',
        [$doctor_id]
    );

    $registered_hospitals = [];
    foreach ($hospital_rows as $hr) {
        $registered_hospitals[] = [
            "doctor_in_hosp_id" => (int)$hr['doctor_in_hosp_id'],
            "hospital_id"       => $hr['hospital_id'] ? (int)$hr['hospital_id'] : null,
            "hospital_name"     => !empty($hr['hospital_name']) ? $hr['hospital_name'] : null,
            "city"              => $hr['city_name'],
            "is_clinic"         => !empty($hr['if_clinic']),
            "comment"           => $hr['comment'],
            "created_at"        => !empty($hr['created_at']) ? date('d M Y', strtotime($hr['created_at'])) : null,
        ];
    }

    // Reviews (pagination)
    $limit  = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 10;
    $page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    $review_rows = fetch_all(
        $con,
        "SELECT feedback_id, commenter_name, commenter_gmail, comment, stars, created_at
         FROM feedback
         WHERE user_id = ? AND status = 1
         ORDER BY created_at DESC
         LIMIT ? OFFSET ?",
        'iii',
        [$user_id, $limit, $offset]
    );

    $reviews = [];
    foreach ($review_rows as $r) {
        $reviews[] = [
            "id"         => isset($r['feedback_id']) ? (int)$r['feedback_id'] : null,
            "name"       => $r['commenter_name'],
            "email"      => mask_email($r['commenter_gmail']), // privacy ke liye masked
            "comment"    => $r['comment'],
            "stars"      => (int)$r['stars'],
            "created_at" => date('d M Y', strtotime($r['created_at'])),
        ];
    }

    // Personal clinic
    $personal_clinic = null;
    if (!empty($doctor['clinic_name']) && (int)$doctor['clinic_status'] === 0) {
        $personal_clinic = [
            "name"    => $doctor['clinic_name'],
            "address" => $doctor['clinic_address'],
        ];
    }

    respond(200, [
        "success" => true,
        "data" => [
            "doctor" => [
                "doctor_id"        => (int)$doctor['doctor_id'],
                "name"             => $doctor['doctor_name'],
                "picture"          => !empty($doctor['doctor_pic'])
                    ? BASE_URL . 'admin/inc/uploads/doctors/' . $doctor['doctor_pic']
                    : null,
                "specialty"        => $spec ? $spec['type'] : 'General Practitioner',
                "short_detail"     => $doctor['short_detail'],
                "mahre_amraz"      => $doctor['mahre_amraz'],
                "notes"            => $doctor['notes'],
                "experience_years" => (int)$doctor['experience_years'],
                "phone"            => $doctor['doctor_phone'],
                "email"            => $doctor['doctor_email'],
                "city"             => $doctor['city_name'],
                "user_id"         => $doctor['user_id'],
                "clinical_info_text" => $doctor['static_clinical_info'],
                "other"            => $doctor['other'],
                "personal_clinic"  => $personal_clinic,
            ],
            "rating" => [
                "average" => $avg_rating,
                "total"   => $total_reviews,
            ],
            "hospitals" => $registered_hospitals,
            "clinical_info" => $clinical,
            "reviews" => [
                "page"  => $page,
                "limit" => $limit,
                "items" => $reviews,
            ],
        ],
    ]);
}

// ======================= POST: review insert =======================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // JSON body ya normal form dono chalenge
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $doctor_id = isset($input['doctor_id']) ? (int)$input['doctor_id'] : 0;
    $name      = isset($input['commenter_name']) ? trim($input['commenter_name']) : '';
    $email     = isset($input['commenter_gmail']) ? trim($input['commenter_gmail']) : '';
    $comment   = isset($input['comment']) ? trim($input['comment']) : '';
    $stars     = isset($input['stars']) ? (int)$input['stars'] : 5;

    // Validation
    $errors = [];
    if ($doctor_id <= 0)                              $errors[] = "doctor_id zaroori hai";
    if ($name === '' || mb_strlen($name) > 100)       $errors[] = "Name zaroori hai (max 100 characters)";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = "Sahi email likhein";
    if ($comment === '' || mb_strlen($comment) > 1000) $errors[] = "Review zaroori hai (max 1000 characters)";
    if ($stars < 1 || $stars > 5)                     $errors[] = "Stars 1 se 5 ke beech hone chahiye";

    if ($errors) {
        respond(422, ["success" => false, "message" => "Validation error", "errors" => $errors]);
    }

    // Doctor check, aur uska user_id server se nikalna (client par bharosa nahi)
    $doctor = get_doctor($con, $doctor_id);
    if (!$doctor) {
        respond(404, ["success" => false, "message" => "Doctor nahi mila"]);
    }
    $user_id = (int)$doctor['user_id'];

    // Spam rok: ek email se ek doctor par aik review
    $dup = fetch_one(
        $con,
        "SELECT COUNT(*) AS cnt FROM feedback WHERE user_id = ? AND commenter_gmail = ?",
        'is',
        [$user_id, $email]
    );
    if ($dup && (int)$dup['cnt'] > 0) {
        respond(409, ["success" => false, "message" => "Aap is doctor ka review pehle de chuke hain"]);
    }

    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO feedback (user_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())"
    );
    mysqli_stmt_bind_param($stmt, 'isssi', $user_id, $name, $email, $comment, $stars);

    if (mysqli_stmt_execute($stmt)) {
        respond(201, [
            "success" => true,
            "message" => "Review submit ho gaya",
            "review_id" => mysqli_insert_id($con),
        ]);
    }

    respond(500, ["success" => false, "message" => "Review save nahi ho saka"]);
}

respond(405, ["success" => false, "message" => "Method allowed nahi"]);