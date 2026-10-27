<?php
/**
 * Doctor Detail API
 *
 * GET  /api/doctor-detail.php?doctor_id=12&reviews_page=1&reviews_per_page=5
 * POST /api/doctor-detail.php   (review submit)
 *      fields: doctor_id, commenter_name, commenter_gmail, comment, stars (1-5)
 *      form-data ya JSON body dono chalte hain.
 *
 * NOTE: neeche wali include line apne project ke hisaab se adjust karein
 *       (jahan $con aur BASE_URL define hote hain).
 */
include '../config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

function api_out($code, $payload)
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function fmt_time($t)
{
    return !empty($t) ? date('h:i A', strtotime($t)) : null;
}

function mask_email($email)
{
    $parts = explode('@', (string)$email);
    if (count($parts) !== 2) { return ''; }
    $name = $parts[0];
    return substr($name, 0, 2) . str_repeat('*', max(1, strlen($name) - 2)) . '@' . $parts[1];
}

$base = defined('BASE_URL') ? BASE_URL : '';
$method = $_SERVER['REQUEST_METHOD'];

// Input (GET / POST form / JSON)
$input = $_GET;
if ($method === 'POST') {
    $input = $_POST;
    $raw = file_get_contents('php://input');
    if (empty($input) && $raw !== '') {
        $json = json_decode($raw, true);
        if (is_array($json)) { $input = $json; }
    }
}

$doctor_id = (int)($input['doctor_id'] ?? 0);
if ($doctor_id <= 0) {
    api_out(400, ['success' => false, 'message' => 'doctor_id is required']);
}

try {
    // ---------------- Doctor (approved + active user only) ----------------
    $stmt = mysqli_prepare($con,
        "SELECT d.doctor_id, d.user_id, d.doctor_name, d.doctor_pic, d.short_detail, d.mahre_amraz,
                d.notes, d.experience_years, d.emergency_status, d.gender, d.cat_type_id,
                d.clinic_address, d.city_id, d.hospital_id,
                c.city_name, h.hospital_name, dct.type AS specialization
         FROM doctors d
         LEFT JOIN cities c ON d.city_id = c.city_id
         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
         LEFT JOIN dr_cat_types dct ON dct.dr_cat_type_id = d.cat_type_id
         LEFT JOIN users u ON u.user_id = d.user_id
         WHERE d.doctor_id = ? AND u.status = 1 AND d.approve = 1");
    mysqli_stmt_bind_param($stmt, 'i', $doctor_id);
    mysqli_stmt_execute($stmt);
    $doctor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$doctor) {
        api_out(404, ['success' => false, 'message' => 'Doctor not found']);
    }
    $user_id = (int)$doctor['user_id'];

    // =====================================================
    // POST: review submit
    // =====================================================
    if ($method === 'POST') {
        $name  = trim((string)($input['commenter_name'] ?? ''));
        $email = trim((string)($input['commenter_gmail'] ?? ''));
        $comment = trim((string)($input['comment'] ?? ''));
        $stars = (int)($input['stars'] ?? 5);

        $errors = [];
        if ($name === '') { $errors[] = 'commenter_name is required'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'valid commenter_gmail is required'; }
        if ($comment === '') { $errors[] = 'comment is required'; }
        if ($stars < 1 || $stars > 5) { $errors[] = 'stars must be between 1 and 5'; }
        if ($errors) {
            api_out(422, ['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
        }

        $ins = mysqli_prepare($con,
            "INSERT INTO feedback (user_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())");
        mysqli_stmt_bind_param($ins, 'isssi', $user_id, $name, $email, $comment, $stars);
        if (!mysqli_stmt_execute($ins)) { throw new Exception(mysqli_stmt_error($ins)); }
        mysqli_stmt_close($ins);

        api_out(201, ['success' => true, 'message' => 'Review submitted successfully']);
    }

    if ($method !== 'GET') {
        api_out(405, ['success' => false, 'message' => 'Only GET and POST allowed']);
    }

    // =====================================================
    // GET: rating summary
    // =====================================================
    $stmt = mysqli_prepare($con,
        "SELECT AVG(stars) AS avg_rating, COUNT(*) AS total_reviews
         FROM feedback WHERE user_id = ? AND status = 1");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $rt = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $avg_rating    = $rt['avg_rating'] ? round((float)$rt['avg_rating'], 1) : 0;
    $total_reviews = (int)$rt['total_reviews'];

    // =====================================================
    // Clinical info (active hospitals only)
    // =====================================================
    $stmt = mysqli_prepare($con,
        "SELECT ci.*, dih.if_clinic, hs.hospital_name, hs.hospital_id, hs.hospital_address, ct.city_name
         FROM clinical_info ci
         INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id
         LEFT JOIN hospitals hs ON dih.hospital_id = hs.hospital_id
         LEFT JOIN cities ct ON ct.city_id = hs.city_id
         WHERE dih.doctor_id = ? AND dih.inactive = 0
         ORDER BY ci.season, ci.shift");
    mysqli_stmt_bind_param($stmt, 'i', $doctor_id);
    mysqli_stmt_execute($stmt);
    $cres = mysqli_stmt_get_result($stmt);

    $clinical = [];
    while ($ci = mysqli_fetch_assoc($cres)) {
        $has_hospital = !empty($ci['hospital_name']);
        $clinical[] = [
            'clinical_info_id' => (int)$ci['clinical_info_id'],
            'is_personal_clinic' => !$has_hospital,
            'hospital_id'      => $has_hospital ? (int)$ci['hospital_id'] : null,
            'hospital_name'    => $has_hospital ? $ci['hospital_name'] : 'Personal Clinic',
            'hospital_url'     => $has_hospital ? $base . 'hospital-detail?hospital_id=' . (int)$ci['hospital_id'] : null,
            'city_name'        => $has_hospital ? $ci['city_name'] : null,
            'address'          => $has_hospital ? ($ci['hospital_address'] ?? '') : ($doctor['clinic_address'] ?? ''),
            'season'           => $ci['season'] ?? null,
            'shift'            => $ci['shift'] ?? null,
            'morning' => (!empty($ci['morning_opening_time']) || !empty($ci['morning_closing_time']))
                ? ['opening' => fmt_time($ci['morning_opening_time']), 'closing' => fmt_time($ci['morning_closing_time'])]
                : null,
            'evening' => (!empty($ci['evening_opening_time']) || !empty($ci['evening_closing_time']))
                ? ['opening' => fmt_time($ci['evening_opening_time']), 'closing' => fmt_time($ci['evening_closing_time'])]
                : null,
            'working_days' => $ci['days'] ?? 'N/A',
            'off_days'     => $ci['off_days'] ?? 'None',
            'contact'      => $ci['contact'] ?? '',
            'detail'       => $ci['detail'] ?? '',
        ];
    }
    mysqli_stmt_close($stmt);

    // =====================================================
    // Reviews (paginated)
    // =====================================================
    $r_page = max(1, (int)($input['reviews_page'] ?? 1));
    $r_per  = (int)($input['reviews_per_page'] ?? 5);
    if ($r_per < 1)  { $r_per = 5; }
    if ($r_per > 50) { $r_per = 50; }
    $r_off = ($r_page - 1) * $r_per;

    $stmt = mysqli_prepare($con,
        "SELECT feedback_id, commenter_name, commenter_gmail, comment, stars, created_at
         FROM feedback WHERE user_id = ? AND status = 1
         ORDER BY created_at DESC LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, 'iii', $user_id, $r_per, $r_off);
    mysqli_stmt_execute($stmt);
    $fres = mysqli_stmt_get_result($stmt);

    $reviews = [];
    while ($f = mysqli_fetch_assoc($fres)) {
        $reviews[] = [
            'feedback_id'    => (int)$f['feedback_id'],
            'commenter_name' => $f['commenter_name'],
            'commenter_email'=> mask_email($f['commenter_gmail']),
            'stars'          => (int)$f['stars'],
            'comment'        => $f['comment'],
            'created_at'     => $f['created_at'],
            'date'           => date('d M Y', strtotime($f['created_at'])),
        ];
    }
    mysqli_stmt_close($stmt);

    $emergency = (int)$doctor['emergency_status'];

    api_out(200, [
        'success' => true,
        'doctor' => [
            'doctor_id'        => (int)$doctor['doctor_id'],
            'doctor_name'      => $doctor['doctor_name'],
            'doctor_pic'       => !empty($doctor['doctor_pic'])
                                    ? $base . 'admin/inc/uploads/doctors/' . $doctor['doctor_pic'] : null,
            'specialization'   => !empty($doctor['specialization']) ? $doctor['specialization'] : 'General Practitioner',
            'qualification'    => $doctor['short_detail'] ?? '',
            'mahre_amraz'      => $doctor['mahre_amraz'] ?? '',
            'notes'            => $doctor['notes'] ?? '',
            'gender'           => $doctor['gender'],
            'is_lady_doctor'   => strtolower((string)$doctor['gender']) === 'female',
            'experience_years' => (int)($doctor['experience_years'] ?? 0),
            'city_name'        => $doctor['city_name'],
            'hospital_name'    => $doctor['hospital_name'],
            'is_available'     => $emergency !== 1,
            'availability'     => $emergency === 1 ? 'Unavailable' : 'Available',
            'unavailable_message' => $emergency === 1 ? 'Unavailable, Please contact 03371320001' : null,
        ],
        'rating' => [
            'average'       => $avg_rating,
            'total_reviews' => $total_reviews,
            'is_new'        => $avg_rating <= 0,
        ],
        'clinical_info'     => $clinical,
        'clinical_message'  => empty($clinical)
            ? 'اس ڈاکٹر کے متعلق معلومات بہت جلد شامل کی جائیں گی۔ مدد کے لیے رابطہ کریں: 0337-1320001'
            : null,
        'reviews' => $reviews,
        'reviews_pagination' => [
            'page'          => $r_page,
            'per_page'      => $r_per,
            'total_records' => $total_reviews,
            'total_pages'   => (int)ceil($total_reviews / $r_per),
            'has_more'      => ($r_off + count($reviews)) < $total_reviews,
        ],
    ]);

} catch (Throwable $e) {
    api_out(500, ['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}