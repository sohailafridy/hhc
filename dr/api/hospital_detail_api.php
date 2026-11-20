<?php
/**
 * All Hospitals Detail API
 * Returns complete details of ALL approved hospitals (with pagination)
 * Method: GET
 * URL: /api/hospital_detail_api.php?page=1&limit=10
 *
 * Optional:
 * - page=1         (hospitals pagination, default 1)
 * - limit=10       (records per page, default 10)
 * - review_page=1  (reviews pagination per hospital, default 1)
 * - doctors_page=1 (doctors pagination per hospital, default 1)
 */

// ============================================
// STEP 1: Headers & CORS
// ============================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// STEP 2: Database Connection
// ============================================
include '../config.php';

if (!$con) {
    echo json_encode(["status" => false, "message" => "DB connection failed"]);
    exit();
}
mysqli_set_charset($con, 'utf8mb4');

// ============================================
// STEP 3: Input Validation & Pagination
// ============================================
$page  = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) && (int)$_GET['limit'] > 0 ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

$review_page = isset($_GET['review_page']) ? max(1, (int)$_GET['review_page']) : 1;
$reviews_per_page = 10;
$review_offset = ($review_page - 1) * $reviews_per_page;

$doctors_page = isset($_GET['doctors_page']) ? max(1, (int)$_GET['doctors_page']) : 1;
$doctors_per_page = 20;
$doctors_offset = ($doctors_page - 1) * $doctors_per_page;

$labs_page = isset($_GET['labs_page']) ? max(1, (int)$_GET['labs_page']) : 1;
$labs_per_page = 20;
$labs_offset = ($labs_page - 1) * $labs_per_page;

$rad_page = isset($_GET['rad_page']) ? max(1, (int)$_GET['rad_page']) : 1;
$rad_per_page = 20;
$rad_offset = ($rad_page - 1) * $rad_per_page;

// ============================================
// AVAILABILITY HELPERS (same as detail page)
// ============================================
function pav_days_order() {
    return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
}

function pav_days_parse($label) {
    $label = trim($label);
    if ($label === 'All Days')  return pav_days_order();
    if ($label === 'Mon - Fri') return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    if ($label === 'Mon - Sat') return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $parts = array_map('trim', explode(',', $label));
    return array_values(array_intersect(pav_days_order(), $parts));
}

function pav_parse($raw) {
    $view = [
        'type'       => 'none',
        'days'       => [],
        'from'       => '09:00',
        'to'         => '17:00',
        'days_label' => '',
        'time_label' => '',
        'emergency'  => false,
        'note'       => '',
        'open_now'   => null,
        'today'      => ''
    ];

    $tz   = new DateTimeZone('Asia/Karachi');
    $now  = new DateTime('now', $tz);
    $view['today'] = $now->format('D');

    $raw = trim((string)$raw);
    if ($raw === '') return $view;

    $segments = array_map('trim', explode(' | ', $raw));
    $first = array_shift($segments);

    if (strcasecmp($first, 'Open 24/7') === 0 || $first === '24/7') {
        $view['type'] = '247';
        $view['open_now'] = true;
        $view['days'] = pav_days_order();
    } elseif (strcasecmp($first, 'Temporarily Closed') === 0) {
        $view['type'] = 'closed';
        $view['open_now'] = false;
    } else {
        $view['type'] = 'custom';
        $view['days'] = pav_days_parse($first);
        $view['days_label'] = $first;

        if (!empty($segments) && strpos($segments[0], ' - ') !== false
            && stripos($segments[0], 'Note:') !== 0 && stripos($segments[0], 'Emergency') !== 0) {
            $time_seg = array_shift($segments);
            $tp = explode(' - ', $time_seg);
            $f = strtotime($tp[0]);
            $t = isset($tp[1]) ? strtotime($tp[1]) : false;
            if ($f) $view['from'] = date('H:i', $f);
            if ($t) $view['to']   = date('H:i', $t);
            $view['time_label'] = trim($time_seg);
        }

        $cur = $now->format('H:i');
        $yesterday = (clone $now)->modify('-1 day')->format('D');
        $from = $view['from'];
        $to   = $view['to'];

        if ($from <= $to) {
            $view['open_now'] = in_array($view['today'], $view['days']) && $cur >= $from && $cur < $to;
        } else {
            $view['open_now'] = (in_array($view['today'], $view['days']) && $cur >= $from)
                             || (in_array($yesterday, $view['days']) && $cur < $to);
        }
    }

    foreach ($segments as $seg) {
        if (stripos($seg, 'Emergency') === 0) {
            $view['emergency'] = true;
        } elseif (stripos($seg, 'Note:') === 0) {
            $view['note'] = trim(substr($seg, 5));
        }
    }
    return $view;
}

function pav_display($av) {
    $out = [
        'has_availability' => ($av['type'] !== 'none'),
        'type'             => $av['type'],
        'class'            => 'av-none',
        'icon'             => 'fa-clock',
        'title'            => '',
        'sub'              => '',
        'live'             => '',
        'days'             => $av['days'],
        'today'            => $av['today'],
        'days_label'       => $av['days_label'],
        'time_label'       => $av['time_label'],
        'emergency'        => (bool)$av['emergency'],
        'note'             => $av['note'],
        'open_now'         => $av['open_now']
    ];

    if ($av['type'] === '247') {
        $out['class'] = 'av-open';
        $out['icon']  = 'fa-infinity';
        $out['title'] = 'Open 24/7';
        $out['sub']   = 'Open 24 hours, all 7 days';
        $out['live']  = 'Open Now';
    } elseif ($av['type'] === 'custom') {
        $out['class'] = $av['open_now'] ? 'av-open' : 'av-shut';
        $out['icon']  = 'fa-calendar-check';
        $out['title'] = $av['days_label'];
        $out['sub']   = $av['time_label'];
        $out['live']  = $av['open_now'] ? 'Open Now' : 'Closed Now';
    } elseif ($av['type'] === 'closed') {
        $out['class'] = 'av-shut';
        $out['icon']  = 'fa-door-closed';
        $out['title'] = 'Temporarily Closed';
        $out['sub']   = 'Currently not accepting visits';
        $out['live']  = 'Closed';
    }

    return $out;
}

// ============================================
// STEP 4: Fetch ALL Approved Hospitals (paginated)
// ============================================
$hospitals_query = "
    SELECT h.*, c.city_name, u.user_id
    FROM hospitals h 
    LEFT JOIN cities c ON h.city_id = c.city_id 
    LEFT JOIN users u ON u.user_id = h.user_id
    WHERE u.status = 1 
      AND h.approve = 1
    ORDER BY h.hospital_id DESC
    LIMIT $limit OFFSET $offset
";

$hospitals_result = mysqli_query($con, $hospitals_query);

if (!$hospitals_result) {
    echo json_encode(["status" => false, "message" => "Database query failed: " . mysqli_error($con)]);
    exit();
}

$total_hospitals_query = "SELECT COUNT(*) as total FROM hospitals h 
                          LEFT JOIN users u ON u.user_id = h.user_id 
                          WHERE u.status = 1 AND h.approve = 1";
$total_hospitals_result = mysqli_query($con, $total_hospitals_query);
$total_hospitals = (int)mysqli_fetch_assoc($total_hospitals_result)['total'];
$total_pages = ($total_hospitals > 0) ? ceil($total_hospitals / $limit) : 0;

if (mysqli_num_rows($hospitals_result) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "No approved hospitals found",
        "pagination" => [
            "current_page"  => $page,
            "limit"         => $limit,
            "total_records" => $total_hospitals,
            "total_pages"   => $total_pages
        ],
        "data" => []
    ]);
    exit();
}

$hospitals_list = [];

while ($hospital = mysqli_fetch_assoc($hospitals_result)) {
    $hospital_id = (int)$hospital['hospital_id'];
    $hospital_user_id = (int)$hospital['user_id'];

    // ---------- AVAILABILITY ----------
    $av = pav_parse($hospital['availability'] ?? '');
    $availability = pav_display($av);

    // ---------- RATING SUMMARY ----------
    $rating_query = "SELECT AVG(stars) AS avg_rating, COUNT(feedback_id) AS total_reviews 
                     FROM feedback 
                     WHERE user_id = $hospital_user_id AND status = 1";
    $rating_result = mysqli_query($con, $rating_query);
    $rating_data = mysqli_fetch_assoc($rating_result);

    $avg_rating    = $rating_data['avg_rating'] ? round((float)$rating_data['avg_rating'], 1) : 0;
    $total_reviews = (int)$rating_data['total_reviews'];

    // ---------- BEDS ----------
    $beds_query = "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id LIMIT 1";
    $beds_result = mysqli_query($con, $beds_query);
    $beds = mysqli_fetch_assoc($beds_result);

    $beds_data = null;
    if ($beds) {
        $total_beds   = (int)($beds['total_beds'] ?? 0);
        $icu_beds     = (int)($beds['icu_beds'] ?? 0);
        $general_beds = (int)($beds['general_beds'] ?? 0);
        $private_beds = (int)($beds['private_beds'] ?? 0);

        $has_beds = ($total_beds > 0 || $icu_beds > 0 || $general_beds > 0 || $private_beds > 0);

        if ($has_beds) {
            $beds_data = [
                "total_beds"   => $total_beds,
                "icu_beds"     => $icu_beds,
                "general_beds" => $general_beds,
                "private_beds" => $private_beds,
                "updated_at"   => $beds['updated_at'] ?? ''
            ];
        }
    }

    // ---------- FACILITIES ----------
    $facilities_query = "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id";
    $facilities_result = mysqli_query($con, $facilities_query);

    $facilities = [];
    $total_facilities = 0;
    $available_facilities = 0;

    while ($row = mysqli_fetch_assoc($facilities_result)) {
        $total_facilities++;
        if ((int)$row['is_available'] === 1) {
            $available_facilities++;
        }
        $facilities[] = [
            "facility_id"   => (int)$row['id'],
            "facility_name" => $row['facility_name'],
            "description"   => $row['description'] ?? '',
            "is_available"  => (int)$row['is_available']
        ];
    }

    // ---------- DOCTORS (paginated) ----------
    $count_doctors_query = "
        SELECT COUNT(*) AS total
        FROM doctor_in_hospital dih
        INNER JOIN doctors d ON d.doctor_id = dih.doctor_id
        LEFT JOIN users u ON d.user_id = u.user_id
        WHERE dih.hospital_id = $hospital_id 
          AND u.status = 1 
          AND d.approve = 1 
          AND dih.inactive = 0
    ";
    $count_doctors_result = mysqli_query($con, $count_doctors_query);
    $count_doctors_row = mysqli_fetch_assoc($count_doctors_result);
    $total_doctors = (int)$count_doctors_row['total'];
    $total_doctors_pages = ($total_doctors > 0) ? ceil($total_doctors / $doctors_per_page) : 0;

    $doctors_query = "
        SELECT 
            d.doctor_id,
            d.doctor_name,
            d.doctor_pic,
            d.gender,
            d.experience_years,
            d.emergency_status,
            dct.type AS specialization,
            dih.doctor_in_hosp_id
        FROM doctor_in_hospital dih
        INNER JOIN doctors d ON d.doctor_id = dih.doctor_id
        LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
        LEFT JOIN users u ON d.user_id = u.user_id
        WHERE dih.hospital_id = $hospital_id 
          AND u.status = 1 
          AND d.approve = 1 
          AND dih.inactive = 0
        ORDER BY d.doctor_name ASC
        LIMIT $doctors_per_page OFFSET $doctors_offset
    ";
    $doctors_result = mysqli_query($con, $doctors_query);

    $doctors = [];
    while ($doc = mysqli_fetch_assoc($doctors_result)) {
        $doctor_pic = '';
        if (!empty($doc['doctor_pic'])) {
            $doctor_pic = BASE_URL . 'admin/inc/uploads/doctors/' . $doc['doctor_pic'];
        } else {
            $doctor_pic = BASE_URL . 'admin/inc/uploads/default/doctor.jpg';
        }

        $doctors[] = [
            "doctor_id"         => (int)$doc['doctor_id'],
            "doctor_in_hosp_id" => (int)$doc['doctor_in_hosp_id'],
            "doctor_name"       => "Dr. " . $doc['doctor_name'],
            "doctor_pic"        => $doctor_pic,
            "specialization"    => $doc['specialization'] ?? 'General',
            "gender"            => $doc['gender'] ?? '',
            "is_lady_doctor"    => (strtolower($doc['gender'] ?? '') === 'female') ? 1 : 0,
            "experience_years"  => (int)($doc['experience_years'] ?? 0),
            "availability"      => ($doc['emergency_status'] == 1) ? "Unavailable" : "Available",
            "detail_url"        => BASE_URL . "doctor-detail?doctor_id=" . $doc['doctor_id']
        ];
    }

    // ---------- REGISTERED LABS (paginated) ----------
    $count_labs_query = "SELECT COUNT(*) AS total 
                         FROM assign_hospital ah 
                         INNER JOIN laboratories l ON l.user_id = ah.user_id
                         LEFT JOIN users u ON u.user_id = l.user_id
                         WHERE ah.hospital_id = $hospital_id AND ah.status = 1 AND l.approve = 1 AND u.status = 1";
    $count_labs_result = mysqli_query($con, $count_labs_query);
    $total_labs = (int)mysqli_fetch_assoc($count_labs_result)['total'];
    $total_labs_pages = ($total_labs > 0) ? ceil($total_labs / $labs_per_page) : 0;

    $labs_query = "SELECT l.*, c.city_name 
                   FROM assign_hospital ah 
                   INNER JOIN laboratories l ON l.user_id = ah.user_id
                   LEFT JOIN cities c ON l.city_id = c.city_id
                   LEFT JOIN users u ON u.user_id = l.user_id
                   WHERE ah.hospital_id = $hospital_id AND ah.status = 1 AND l.approve = 1 AND u.status = 1
                   ORDER BY l.lab_name ASC
                   LIMIT $labs_per_page OFFSET $labs_offset";
    $labs_result = mysqli_query($con, $labs_query);

    $labs = [];
    while ($lab = mysqli_fetch_assoc($labs_result)) {
        $lab_pic_val = trim($lab['lab_pic'] ?? '');
        $has_valid_pic = (!empty($lab_pic_val) && $lab_pic_val !== '0' && strtolower($lab_pic_val) !== 'null');
        $lab_pic = $has_valid_pic
            ? BASE_URL . 'admin/inc/uploads/labs/' . $lab_pic_val
            : BASE_URL . 'admin/inc/uploads/default/lab.jpg';

        $labs[] = [
            "lab_id"     => (int)$lab['lab_id'],
            "lab_name"   => $lab['lab_name'],
            "lab_pic"    => $lab_pic,
            "city_name"  => $lab['city_name'] ?? '',
            "detail_url" => BASE_URL . "lab-detail?lab_id=" . $lab['lab_id']
        ];
    }

    // ---------- REGISTERED RADIOLOGY (paginated) ----------
    $count_rad_query = "SELECT COUNT(*) AS total 
                        FROM assign_hospital_radiology ahr 
                        INNER JOIN radiology r ON r.user_id = ahr.user_id
                        LEFT JOIN users u ON u.user_id = r.user_id
                        WHERE ahr.hospital_id = $hospital_id AND ahr.status = 1 AND r.approve = 1 AND u.status = 1";
    $count_rad_result = mysqli_query($con, $count_rad_query);
    $total_radiology = (int)mysqli_fetch_assoc($count_rad_result)['total'];
    $total_rad_pages = ($total_radiology > 0) ? ceil($total_radiology / $rad_per_page) : 0;

    $rad_query = "SELECT r.*, c.city_name 
                  FROM assign_hospital_radiology ahr 
                  INNER JOIN radiology r ON r.user_id = ahr.user_id
                  LEFT JOIN cities c ON r.city_id = c.city_id
                  LEFT JOIN users u ON u.user_id = r.user_id
                  WHERE ahr.hospital_id = $hospital_id AND ahr.status = 1 AND r.approve = 1 AND u.status = 1
                  ORDER BY r.rad_name ASC
                  LIMIT $rad_per_page OFFSET $rad_offset";
    $rad_result = mysqli_query($con, $rad_query);

    $radiology = [];
    while ($rad = mysqli_fetch_assoc($rad_result)) {
        $rad_pic_val = trim($rad['rad_pic'] ?? '');
        $has_valid_rad_pic = (!empty($rad_pic_val) && $rad_pic_val !== '0' && strtolower($rad_pic_val) !== 'null');
        $rad_pic = $has_valid_rad_pic
            ? BASE_URL . 'admin/inc/uploads/radiology/' . $rad_pic_val
            : BASE_URL . 'admin/inc/uploads/default/radiology.jpg';

        $radiology[] = [
            "rad_id"     => (int)$rad['rad_id'],
            "rad_name"   => $rad['rad_name'],
            "rad_pic"    => $rad_pic,
            "city_name"  => $rad['city_name'] ?? 'N/A',
            "detail_url" => BASE_URL . "radiology-detail?rad_id=" . $rad['rad_id']
        ];
    }

    // ---------- HOSPITAL NOTES ----------
    $notes_query = "SELECT * FROM notes WHERE user_id = $hospital_user_id LIMIT 1";
    $notes_result = mysqli_query($con, $notes_query);
    $hospital_note = ($notes_result && mysqli_num_rows($notes_result) > 0)
        ? mysqli_fetch_assoc($notes_result)
        : null;

    $notes_data = null;
    if ($hospital_note && !empty(trim($hospital_note['notes']))) {
        $notes_data = [
            "note_id"           => (int)$hospital_note['note_id'],
            "notes"             => $hospital_note['notes'],
            "updated_at"        => $hospital_note['updated_at'] ?? '',
            "updated_at_format" => !empty($hospital_note['updated_at']) 
                                    ? date('d M Y, h:i A', strtotime($hospital_note['updated_at'])) 
                                    : ''
        ];
    }

    // ---------- REVIEWS (paginated) ----------
    $reviews_query = "
        SELECT feedback_id, commenter_name, commenter_gmail, comment, stars, created_at
        FROM feedback 
        WHERE user_id = $hospital_user_id AND status = 1
        ORDER BY created_at DESC
        LIMIT $reviews_per_page OFFSET $review_offset
    ";
    $reviews_result = mysqli_query($con, $reviews_query);

    $reviews = [];
    while ($r = mysqli_fetch_assoc($reviews_result)) {
        $stars = (int)$r['stars'];
        $reviews[] = [
            "review_id"     => (int)$r['feedback_id'],
            "patient_name"  => $r['commenter_name'],
            "patient_email" => $r['commenter_gmail'],
            "comment"       => $r['comment'] ?? '',
            "stars"         => $stars,
            "stars_display" => str_repeat('★', $stars) . str_repeat('☆', 5 - $stars),
            "date"          => date('d M Y', strtotime($r['created_at'])),
            "created_at"    => $r['created_at']
        ];
    }

    $total_review_pages = ($total_reviews > 0) ? ceil($total_reviews / $reviews_per_page) : 0;

    // ---------- HOSPITAL PIC ----------
    $hospital_pic = '';
    if (!empty($hospital['hospital_pic'])) {
        $hospital_pic = BASE_URL . 'admin/inc/uploads/hospitals/' . $hospital['hospital_pic'];
    } else {
        $hospital_pic = BASE_URL . 'admin/inc/uploads/default/hosp.jpg';
    }

    // ---------- BUILD DATA ----------
    $hospitals_list[] = [
        // BASIC INFO
        "hospital_info" => [
            "hospital_id"      => $hospital_id,
            "hospital_name"    => $hospital['hospital_name'],
            "user_id"          => $hospital_user_id,
            "hospital_pic"     => $hospital_pic,
            "hospital_address" => $hospital['hospital_address'] ?? '',
            "hospital_phone"   => $hospital['hospital_phone'] ?? '',
            "city_id"          => (int)($hospital['city_id'] ?? 0),
            "city_name"        => $hospital['city_name'] ?? '',
            "availability_raw" => $hospital['availability'] ?? '',
            "updated_at"       => $hospital['updated_at'] ?? '',
            "detail_url"       => BASE_URL . "hospital-detail?hospital_id=" . $hospital_id
        ],

        // AVAILABILITY (Hero + Timing Card)
        "availability" => $availability,

        // STATS SUMMARY (Hero Stats Bar)
        "stats" => [
            "total_doctors"        => $total_doctors,
            "total_beds"           => $beds_data ? $beds_data['total_beds'] : 0,
            "total_facilities"     => $total_facilities,
            "available_facilities" => $available_facilities,
            "total_reviews"        => $total_reviews,
            "total_labs"           => $total_labs,
            "total_radiology"      => $total_radiology
        ],

        // RATING SUMMARY
        "rating_summary" => [
            "average_rating" => $avg_rating,
            "total_reviews"  => $total_reviews,
            "display"        => $avg_rating > 0 ? (string)$avg_rating : "New"
        ],

        // BEDS
        "beds" => $beds_data,

        // FACILITIES
        "facilities" => [
            "list"                 => $facilities,
            "total"                => $total_facilities,
            "available"            => $available_facilities
        ],

        // HOSPITAL NOTES
        "hospital_notes" => $notes_data,

        // DOCTORS (paginated)
        "doctors" => [
            "list"          => $doctors,
            "current_page"  => $doctors_page,
            "total_pages"   => $total_doctors_pages,
            "total_doctors" => $total_doctors,
            "per_page"      => $doctors_per_page
        ],

        // LABS (paginated)
        "labs" => [
            "list"         => $labs,
            "current_page" => $labs_page,
            "total_pages"  => $total_labs_pages,
            "total_labs"   => $total_labs,
            "per_page"     => $labs_per_page
        ],

        // RADIOLOGY (paginated)
        "radiology" => [
            "list"            => $radiology,
            "current_page"    => $rad_page,
            "total_pages"     => $total_rad_pages,
            "total_radiology" => $total_radiology,
            "per_page"        => $rad_per_page
        ],

        // REVIEWS (paginated)
        "reviews" => [
            "list"          => $reviews,
            "current_page"  => $review_page,
            "total_pages"   => $total_review_pages,
            "total_reviews" => $total_reviews,
            "per_page"      => $reviews_per_page
        ]
    ];
}

// ============================================
// STEP 13: Final Response
// ============================================
echo json_encode([
    "status"  => true,
    "message" => "All hospitals detail fetched successfully",
    "pagination" => [
        "current_page"  => $page,
        "limit"         => $limit,
        "total_records" => $total_hospitals,
        "total_pages"   => $total_pages
    ],
    "data" => $hospitals_list
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

mysqli_close($con);
?>