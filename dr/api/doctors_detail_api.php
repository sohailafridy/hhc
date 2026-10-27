<?php
// GET api/doctor-detail.php?doctor_id=2
require_once __DIR__ . '/_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('Method not allowed', 405);

$doctorId = (int)($_GET['doctor_id'] ?? 0);
if ($doctorId <= 0) json_error('doctor_id is required');

// ---- doctor ----
$d = db_one($conn,
    "SELECT d.*, t.type AS specialization, c.city_name
     FROM doctors d
     JOIN users u ON u.user_id = d.user_id
     LEFT JOIN dr_cat_types t ON t.dr_cat_type_id = d.dr_cat_type_id
     LEFT JOIN cities c ON c.city_id = d.city_id
     WHERE d.doctor_id = ? AND u.status = 1 AND d.approve = 1",
    'i', [$doctorId]);

if (!$d) json_error('Doctor not found', 404);

// ---- rating ----
$rating = db_one($conn,
    "SELECT ROUND(COALESCE(AVG(stars), 0), 1) AS average, COUNT(*) AS cnt
     FROM feedback WHERE user_id = ? AND status = 1",
    'i', [(int)$d['user_id']]);

// ---- clinics (doctor_in_hospital + clinical_info) ----
$clinicRows = db_all($conn,
    "SELECT dh.doctor_in_hosp_id, dh.if_clinic,
            h.hospital_id, h.hospital_name, h.hospital_address, h.hospital_phone,
            h.hospital_pic, h.is_personal, hc.city_name AS hospital_city,
            ci.morning_opening_time, ci.morning_closing_time,
            ci.evening_opening_time, ci.evening_closing_time,
            ci.shift, ci.season, ci.days, ci.off_days, ci.contact, ci.detail, ci.clinic_address
     FROM doctor_in_hospital dh
     JOIN hospitals h ON h.hospital_id = dh.hospital_id
     LEFT JOIN cities hc ON hc.city_id = h.city_id
     LEFT JOIN clinical_info ci ON ci.doctor_in_hosp_id = dh.doctor_in_hosp_id
     WHERE dh.doctor_id = ? AND dh.inactive = 0",
    'i', [$doctorId]);

$clinics = array_map(function ($r) {
    $personal = (int)$r['is_personal'] === 1;
    return [
        'hospital_id'     => (int)$r['hospital_id'],
        'hospital_name'   => $r['hospital_name'],
        'is_personal'     => $personal,
        'is_clinic'       => (bool)$r['if_clinic'],
        'city'            => $r['hospital_city'],
        // personal clinic ka apna address clinical_info.clinic_address mein hota hai
        'address'         => ($personal && !empty($r['clinic_address'])) ? $r['clinic_address'] : $r['hospital_address'],
        'morning_opening' => $r['morning_opening_time'],
        'morning_closing' => $r['morning_closing_time'],
        'evening_opening' => $r['evening_opening_time'],
        'evening_closing' => $r['evening_closing_time'],
        'shift'           => $r['shift'],
        'season'          => $r['season'],
        'days'            => $r['days'],
        'off_days'        => $r['off_days'],
        'contact'         => $r['contact'] ?: $r['hospital_phone'],
        'detail'          => $r['detail'],
    ];
}, $clinicRows);

// ---- reviews (email kabhi expose nahi karni) ----
$reviewRows = db_all($conn,
    "SELECT commenter_name, stars, comment, created_at
     FROM feedback WHERE user_id = ? AND status = 1
     ORDER BY created_at DESC LIMIT 50",
    'i', [(int)$d['user_id']]);

$reviews = array_map(fn($r) => [
    'name'       => $r['commenter_name'],
    'stars'      => (int)$r['stars'],
    'comment'    => $r['comment'],
    'created_at' => $r['created_at'],
], $reviewRows);

json_out([
    'success' => true,
    'data' => [
        'doctor' => [
            'doctor_id'      => (int)$d['doctor_id'],
            'name'           => $d['doctor_name'],
            'photo'          => photo_url($d['photo']),
            'specialization' => $d['specialization'],
            'qualification'  => $d['qualification'],
            'experience'     => $d['experience'] !== null ? (int)$d['experience'] : null,
            'city'           => $d['city_name'],
            'phone'          => $d['phone'] ?? null,
            'email'          => $d['email'] ?? null,
            'description'    => $d['description'] ?? null,
            'notes'          => $d['notes'] ?? null,
            'other_info'     => $d['other_info'] ?? null,
            'is_lady_doctor' => (bool)($d['is_lady'] ?? 0),
            'is_available'   => (bool)($d['emergency'] ?? 0),
            'updated_at'     => $d['updated_at'] ?? null,
        ],
        'rating'  => ['average' => (float)$rating['average'], 'count' => (int)$rating['cnt']],
        'clinics' => $clinics,
        'reviews' => $reviews,
    ],
]);