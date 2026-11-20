<?php
// ============================================
// START SESSION & INCLUDE CONFIG
// ============================================
include '../config.php';

// ============================================
// HOSPITAL AUTHENTICATION CHECK
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id = $_SESSION['user_id'];

// ============================================
// ENSURE hospitals.availability COLUMN EXISTS
// ============================================
$col_check = mysqli_query($con, "SHOW COLUMNS FROM hospitals LIKE 'availability'");
if ($col_check && mysqli_num_rows($col_check) == 0) {
    mysqli_query($con, "ALTER TABLE hospitals ADD COLUMN availability VARCHAR(500) NULL DEFAULT NULL");
}

// Get hospital data
$hospital_query = "SELECT h.*, c.city_name 
                   FROM hospitals h 
                   LEFT JOIN cities c ON h.city_id = c.city_id 
                   WHERE h.user_id = $user_id AND h.approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];
$entity_id   = $hospital_data['entity_id'];

// ============================================
// AVAILABILITY HELPERS
// Stored format in hospitals.availability:
//   "Open 24/7"
//   "Mon - Fri | 09:00 AM - 05:00 PM"
//   "Temporarily Closed"
//   + optional " | Emergency 24/7"  + optional " | Note: text"
// ============================================
function av_days_order() {
    return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
}

function av_days_label($days) {
    $days = array_values(array_intersect(av_days_order(), $days));
    if (count($days) == 7) return 'All Days';
    if ($days == ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']) return 'Mon - Fri';
    if ($days == ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']) return 'Mon - Sat';
    return implode(', ', $days);
}

function av_days_parse($label) {
    $label = trim($label);
    if ($label === 'All Days') return av_days_order();
    if ($label === 'Mon - Fri') return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    if ($label === 'Mon - Sat') return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $parts = array_map('trim', explode(',', $label));
    return array_values(array_intersect(av_days_order(), $parts));
}

function av_time_label($t) {
    $ts = strtotime($t);
    return $ts ? date('h:i A', $ts) : '';
}

function av_build($type, $days, $from, $to, $emergency, $note) {
    if ($type === '247') {
        $str = 'Open 24/7';
    } elseif ($type === 'closed') {
        $str = 'Temporarily Closed';
    } else {
        $str = av_days_label($days) . ' | ' . av_time_label($from) . ' - ' . av_time_label($to);
    }
    if ($emergency) {
        $str .= ' | Emergency 24/7';
    }
    if ($note !== '') {
        $str .= ' | Note: ' . $note;
    }
    return $str;
}

function av_parse($raw) {
    $view = [
        'type'       => 'none',
        'days'       => [],
        'from'       => '09:00',
        'to'         => '17:00',
        'days_label' => '',
        'time_label' => '',
        'emergency'  => false,
        'note'       => '',
        'raw'        => (string)$raw
    ];
    $raw = trim((string)$raw);
    if ($raw === '') return $view;

    $segments = array_map('trim', explode(' | ', $raw));
    $first = array_shift($segments);

    if (strcasecmp($first, 'Open 24/7') === 0 || $first === '24/7') {
        $view['type'] = '247';
    } elseif (strcasecmp($first, 'Temporarily Closed') === 0) {
        $view['type'] = 'closed';
    } else {
        $view['type'] = 'custom';
        $view['days'] = av_days_parse($first);
        $view['days_label'] = $first;
        if (!empty($segments) && strpos($segments[0], ' - ') !== false && stripos($segments[0], 'Note:') !== 0 && stripos($segments[0], 'Emergency') !== 0) {
            $time_seg = array_shift($segments);
            $tp = explode(' - ', $time_seg);
            $f = strtotime($tp[0]);
            $t = isset($tp[1]) ? strtotime($tp[1]) : false;
            if ($f) $view['from'] = date('H:i', $f);
            if ($t) $view['to']   = date('H:i', $t);
            $view['time_label'] = trim($time_seg);
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

// ============================================
// AJAX: SAVE AVAILABILITY
// ============================================
if (isset($_POST['action']) && $_POST['action'] == 'save_availability') {
    header('Content-Type: application/json');

    $type      = $_POST['av_type'] ?? '';
    $days_in   = isset($_POST['days']) && is_array($_POST['days']) ? $_POST['days'] : [];
    $days      = array_values(array_intersect(av_days_order(), $days_in));
    $from      = trim($_POST['from_time'] ?? '');
    $to        = trim($_POST['to_time'] ?? '');
    $emergency = !empty($_POST['emergency']) && $_POST['emergency'] == '1';
    $note      = trim($_POST['av_note'] ?? '');
    $note      = trim(str_replace('|', '-', strip_tags($note)));
    $note      = mb_substr($note, 0, 150);

    if (!in_array($type, ['247', 'custom', 'closed'])) {
        echo json_encode(['success' => false, 'message' => 'Please select an availability type.']);
        exit();
    }

    if ($type === 'custom') {
        if (empty($days)) {
            echo json_encode(['success' => false, 'message' => 'Please select at least one day.']);
            exit();
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $from) || !preg_match('/^\d{2}:\d{2}$/', $to)) {
            echo json_encode(['success' => false, 'message' => 'Please select opening and closing time.']);
            exit();
        }
    }

    $availability = av_build($type, $days, $from, $to, $emergency, $note);
    $availability_esc = mysqli_real_escape_string($con, $availability);

    $update = "UPDATE hospitals SET availability = '$availability_esc' WHERE hospital_id = $hospital_id";
    if (mysqli_query($con, $update)) {
        echo json_encode([
            'success' => true,
            'message' => 'Availability updated successfully!',
            'time'    => date('d M Y, h:i A'),
            'view'    => av_parse($availability)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
    }
    exit();
}

$availability_view = av_parse($hospital_data['availability'] ?? '');

// ============================================
// AJAX: REMOVE LAB FROM HOSPITAL
// ============================================
if (isset($_POST['action']) && $_POST['action'] == 'remove_lab') {
    header('Content-Type: application/json');
    $lab_user_id = (int)$_POST['lab_user_id'];

    if ($lab_user_id > 0) {
        $delete = "DELETE FROM assign_hospital WHERE hospital_id = $hospital_id AND user_id = $lab_user_id";
        if (mysqli_query($con, $delete)) {
            echo json_encode(['success' => true, 'message' => 'Lab removed successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Lab ID.']);
    }
    exit();
}

// ============================================
// AJAX: REMOVE RADIOLOGY FROM HOSPITAL
// ============================================
if (isset($_POST['action']) && $_POST['action'] == 'remove_radiology') {
    header('Content-Type: application/json');
    $rad_user_id = (int)$_POST['rad_user_id'];

    if ($rad_user_id > 0) {
        $delete = "DELETE FROM assign_hospital_radiology WHERE hospital_id = $hospital_id AND user_id = $rad_user_id";
        if (mysqli_query($con, $delete)) {
            echo json_encode(['success' => true, 'message' => 'Radiology removed successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Radiology ID.']);
    }
    exit();
}

// ============================================
// STATISTICS
// ============================================
$doctors_query = "SELECT COUNT(*) as total FROM doctor_in_hospital WHERE hospital_id = $hospital_id AND inactive = 0";
$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = mysqli_fetch_assoc($doctors_result)['total'];

$beds_query = "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id";
$beds_result = mysqli_query($con, $beds_query);
$beds = mysqli_fetch_assoc($beds_result);
$total_beds = $beds ? $beds['total_beds'] : 0;

$facilities_query = "SELECT COUNT(*) as total FROM hospital_facilities WHERE hospital_id = $hospital_id";
$facilities_result = mysqli_query($con, $facilities_query);
$total_facilities = mysqli_fetch_assoc($facilities_result)['total'];

$available_facilities_query = "SELECT COUNT(*) as total FROM hospital_facilities 
                               WHERE hospital_id = $hospital_id AND is_available = 1";
$available_facilities_result = mysqli_query($con, $available_facilities_query);
$available_facilities = mysqli_fetch_assoc($available_facilities_result)['total'];

$reviews_query = "SELECT COUNT(*) as total FROM feedback WHERE user_id = $user_id AND status = 1";
$reviews_result = mysqli_query($con, $reviews_query);
$total_reviews = mysqli_fetch_assoc($reviews_result)['total'];

$rating_query = "SELECT AVG(stars) as avg_rating FROM feedback WHERE user_id = $user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;

// ============================================
// FETCH REGISTERED LABS & RADIOLOGY
// ============================================
$labs_query = "SELECT l.lab_id, l.lab_name, l.user_id as lab_user_id, l.lab_pic, c.city_name 
               FROM assign_hospital ah 
               INNER JOIN laboratories l ON l.user_id = ah.user_id
               LEFT JOIN cities c ON l.city_id = c.city_id
               LEFT JOIN users u ON u.user_id = l.user_id
               WHERE ah.hospital_id = $hospital_id AND ah.status = 1 AND l.approve = 1 AND u.status = 1
               ORDER BY l.lab_name ASC";
$labs_result = mysqli_query($con, $labs_query);
$total_labs = mysqli_num_rows($labs_result);

// Fetch Radiology (for Dashboard list & banner)
$rad_query = "SELECT r.rad_id, r.rad_name, r.user_id as rad_user_id, r.rad_pic, c.city_name 
              FROM assign_hospital_radiology ahr 
              INNER JOIN radiology r ON r.user_id = ahr.user_id 
              LEFT JOIN cities c ON r.city_id = c.city_id
              LEFT JOIN users u ON u.user_id = r.user_id
              WHERE ahr.hospital_id = $hospital_id AND ahr.status = 1 AND r.approve = 1 AND u.status = 1
              ORDER BY r.rad_name ASC";
$rad_res = mysqli_query($con, $rad_query);
$total_radiology = mysqli_num_rows($rad_res);

$radiology_list = [];
while($r = mysqli_fetch_assoc($rad_res)) {
    $radiology_list[] = $r;
}

// Fetch All Doctors (for banner)
$all_doctors_list = [];
$doc_query = "SELECT d.doctor_name, dct.type as specialization 
              FROM doctor_in_hospital dih
              INNER JOIN doctors d ON dih.doctor_id = d.doctor_id
              LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
              WHERE dih.hospital_id = $hospital_id AND dih.inactive = 0 AND d.approve = 1";
$doc_res = mysqli_query($con, $doc_query);
while($d = mysqli_fetch_assoc($doc_res)) {
    $all_doctors_list[] = $d;
}

// Recent Doctors
$recent_doctors_query = "SELECT d.*, dct.type as specialization, u.status as ustatus
                         FROM doctor_in_hospital dih
                         INNER JOIN doctors d ON dih.doctor_id = d.doctor_id
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                         LEFT JOIN users u ON u.user_id = d.user_id
                         WHERE dih.hospital_id = $hospital_id AND dih.inactive = 0 AND d.approve = 1 AND u.status = 1
                         ORDER BY d.created_at DESC LIMIT 5";
$recent_doctors_result = mysqli_query($con, $recent_doctors_query);

// ============================================
// NOTES BOARD
// ============================================
$notes_tbl = mysqli_query($con, "SHOW TABLES LIKE 'notes'");
if (mysqli_num_rows($notes_tbl) == 0) {
    mysqli_query($con, "CREATE TABLE notes (
        note_id    INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id    INT(11) NOT NULL,
        notes      TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

if (isset($_POST['save_note'])) {
    header('Content-Type: application/json');
    $note_text = mysqli_real_escape_string($con, trim($_POST['note_text'] ?? ''));
    
    $exists = mysqli_query($con, "SELECT note_id FROM notes WHERE user_id = $user_id LIMIT 1");
    if (mysqli_num_rows($exists) > 0) {
        $row = mysqli_fetch_assoc($exists);
        mysqli_query($con, "UPDATE notes SET notes = '$note_text', updated_at = NOW() WHERE note_id = {$row['note_id']}");
        $action = 'updated';
    } else {
        mysqli_query($con, "INSERT INTO notes (user_id, notes, created_at, updated_at) VALUES ($user_id, '$note_text', NOW(), NOW())");
        $action = 'added';
    }
    echo json_encode(['success' => true, 'action' => $action, 'time' => date('d M Y, h:i A')]);
    exit();
}

$note_row = null;
$note_result = mysqli_query($con, "SELECT * FROM notes WHERE user_id = $user_id LIMIT 1");
if (mysqli_num_rows($note_result) > 0) {
    $note_row = mysqli_fetch_assoc($note_result);
}

// Recent Reviews
$recent_reviews_query = "SELECT * FROM feedback 
                         WHERE user_id = $user_id AND status = 1
                         ORDER BY created_at DESC LIMIT 5";
$recent_reviews_result = mysqli_query($con, $recent_reviews_query);

// ============================================
// VISITING CARD DATA
// ============================================
$vc_name     = $hospital_data['hospital_name'] ?? '';
$vc_phone    = (!empty($hospital_data['hospital_phone'])   && $hospital_data['hospital_phone']   !== '0') ? $hospital_data['hospital_phone']   : '';
$vc_email    = (!empty($hospital_data['hospital_email'])   && $hospital_data['hospital_email']   !== '0') ? $hospital_data['hospital_email']   : '';
$vc_address  = (!empty($hospital_data['hospital_address']) && $hospital_data['hospital_address'] !== '0') ? $hospital_data['hospital_address'] : '';
$vc_city     = $hospital_data['city_name'] ?? '';
$vc_name_len = mb_strlen($vc_name);
if ($vc_name_len > 38)      { $vc_name_size = 21; }
elseif ($vc_name_len > 28)  { $vc_name_size = 25; }
elseif ($vc_name_len > 20)  { $vc_name_size = 29; }
else                        { $vc_name_size = 34; }
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/hospital/inc/nav.php'; ?>

<!-- Libraries for Image & QR Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-hospital-admin.css">

<style>
/* ── 2-Column Dashboard Grid ────────────────────────── */
.two-col-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    align-items: start;
    margin-top: 24px;
}
.two-col-dashboard .col-stack {
    display: flex;
    flex-direction: column;
    gap: 24px;
}
.two-col-dashboard .info-card {
    margin-top: 0 !important;
}
@media (max-width: 992px) {
    .two-col-dashboard {
        grid-template-columns: 1fr;
    }
}

/* Custom styles for Lab List */
.lab-list-item {
    display: flex; align-items: center; padding: 12px;
    background: #f8fafc; border-radius: 10px;
    margin-bottom: 12px; border: 1px solid #e2e8f0; gap: 12px;
}
.lab-list-item:last-child { margin-bottom: 0; }
.lab-avatar {
    width: 45px; height: 45px; border-radius: 8px;
    background: #e0f2fe; color: #0284c7;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0; object-fit: cover;
}
.lab-info { flex: 1; min-width: 0; }
.lab-info .lab-name { font-weight: 700; font-size: 0.95rem; color: #0D2B6E; margin-bottom: 2px; }
.lab-info .lab-city { font-size: 0.8rem; color: #64748b; }
.btn-remove-lab {
    background: #fee2e2; color: #ef4444; border: none;
    padding: 6px 12px; border-radius: 6px; font-size: 0.8rem;
    font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-remove-lab:hover { background: #ef4444; color: #fff; }

/* Radiology Specific Colors */
.rad-list-item { border-left: 3px solid #4338ca; }
.rad-avatar { background: #e0e7ff; color: #4338ca; }

/* ============================================================
   AVAILABILITY CARD
   ============================================================ */
.av-status {
    border-radius: 14px; padding: 16px 18px; margin-bottom: 18px;
    display: flex; align-items: center; gap: 14px;
    border: 1.5px solid #e2e8f0; background: #f8fafc;
}
.av-status .av-ic {
    width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: #fff; background: #94a3b8;
}
.av-status .av-main { font-size: 1.02rem; font-weight: 800; color: #0D2B6E; line-height: 1.25; }
.av-status .av-sub  { font-size: .8rem; font-weight: 600; color: #64748b; margin-top: 3px; }
.av-status.s-247    { background: linear-gradient(135deg, #F0FFF8, #dcfce7); border-color: rgba(39,201,106,.5); }
.av-status.s-247 .av-ic { background: linear-gradient(135deg, #27C96A, #00C9A7); box-shadow: 0 6px 16px rgba(39,201,106,.35); }
.av-status.s-custom { background: linear-gradient(135deg, #E8F7FF, #dbeafe); border-color: rgba(0,168,204,.5); }
.av-status.s-custom .av-ic { background: linear-gradient(135deg, #1C3F8F, #00A8CC); box-shadow: 0 6px 16px rgba(0,168,204,.35); }
.av-status.s-closed { background: #fef2f2; border-color: rgba(239,68,68,.45); }
.av-status.s-closed .av-ic { background: linear-gradient(135deg, #dc2626, #ef4444); box-shadow: 0 6px 16px rgba(239,68,68,.3); }

.av-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.av-tag {
    font-size: .68rem; font-weight: 700; padding: 3px 10px; border-radius: 20px;
    letter-spacing: .4px; text-transform: uppercase;
}
.av-tag.em   { background: #fee2e2; color: #dc2626; border: 1px solid rgba(239,68,68,.35); }
.av-tag.note { background: #fff7ed; color: #c2410c; border: 1px solid rgba(249,115,22,.35); text-transform: none; }

.av-label { font-size: .75rem; font-weight: 800; color: #1C3F8F; letter-spacing: 1.2px; text-transform: uppercase; margin: 0 0 8px; }

.av-types { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
.av-type { position: relative; }
.av-type input { position: absolute; opacity: 0; pointer-events: none; }
.av-type label {
    display: flex; flex-direction: column; align-items: center; gap: 6px; text-align: center;
    padding: 12px 8px; border-radius: 12px; cursor: pointer;
    border: 2px solid rgba(0,168,204,.2); background: #f0f9ff;
    font-size: .8rem; font-weight: 700; color: #0D2B6E; transition: all .2s ease; margin: 0;
}
.av-type label i { font-size: 1.15rem; color: #00A8CC; transition: all .2s ease; }
.av-type label:hover { border-color: #00A8CC; transform: translateY(-2px); }
.av-type input:checked + label {
    background: linear-gradient(135deg, #1C3F8F, #00A8CC); color: #fff;
    border-color: transparent; box-shadow: 0 8px 18px rgba(0,168,204,.35);
}
.av-type input:checked + label i { color: #fff; }
.av-type.t-247 input:checked + label { background: linear-gradient(135deg, #27C96A, #00C9A7); box-shadow: 0 8px 18px rgba(39,201,106,.35); }
.av-type.t-closed input:checked + label { background: linear-gradient(135deg, #dc2626, #ef4444); box-shadow: 0 8px 18px rgba(239,68,68,.3); }

.av-custom { display: none; background: #f0f9ff; border: 1.5px dashed rgba(0,168,204,.4); border-radius: 12px; padding: 14px; margin-bottom: 16px; }
.av-custom.show { display: block; }

.av-days { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 10px; }
.av-day { position: relative; }
.av-day input { position: absolute; opacity: 0; pointer-events: none; }
.av-day span {
    display: inline-block; min-width: 46px; text-align: center; padding: 7px 10px;
    border-radius: 10px; font-size: .78rem; font-weight: 700; cursor: pointer;
    background: #fff; border: 2px solid rgba(0,168,204,.25); color: #1C3F8F; transition: all .15s ease;
}
.av-day input:checked + span {
    background: linear-gradient(135deg, #1C3F8F, #00A8CC); color: #fff; border-color: transparent;
}
.av-quick { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
.av-quick button {
    border: 1px solid rgba(0,168,204,.4); background: #fff; color: #1C3F8F;
    font-size: .7rem; font-weight: 700; padding: 4px 11px; border-radius: 20px; cursor: pointer; transition: all .15s ease;
}
.av-quick button:hover { background: #00A8CC; color: #fff; }

.av-times { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.av-times label { display: block; font-size: .72rem; font-weight: 700; color: #64748b; margin-bottom: 4px; }
.av-input {
    width: 100%; padding: 10px 12px; border: 2px solid rgba(0,168,204,.2); border-radius: 10px;
    font-size: .9rem; color: #0D2B6E; background: #fff; outline: none; font-family: inherit; transition: border-color .2s;
}
.av-input:focus { border-color: #00A8CC; }

.av-emergency {
    display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 12px;
    background: #fef2f2; border: 1.5px solid rgba(239,68,68,.3); margin-bottom: 14px; cursor: pointer;
}
.av-emergency input { width: 18px; height: 18px; accent-color: #ef4444; cursor: pointer; margin: 0; }
.av-emergency span { font-size: .85rem; font-weight: 700; color: #b91c1c; }
.av-emergency small { display: block; font-size: .7rem; font-weight: 500; color: #94a3b8; }

.av-actions { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.av-save {
    background: linear-gradient(135deg, #1C3F8F, #00A8CC); color: #fff; border: none;
    padding: 10px 26px; border-radius: 10px; font-weight: 700; font-size: .88rem; cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px; transition: all .2s ease;
}
.av-save:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0,168,204,.35); }
.av-save:disabled { opacity: .7; cursor: wait; transform: none; box-shadow: none; }
.av-badge {
    display: none; align-items: center; gap: 6px; background: rgba(39,201,106,.12);
    border: 1px solid rgba(39,201,106,.3); border-radius: 20px; padding: 5px 14px;
    font-size: .82rem; font-weight: 700; color: #27C96A;
}
.av-error {
    display: none; background: #fef2f2; border: 1px solid rgba(239,68,68,.35); color: #b91c1c;
    padding: 9px 14px; border-radius: 10px; font-size: .82rem; font-weight: 600; margin-top: 10px;
}
@media (max-width: 480px) {
    .av-types { grid-template-columns: 1fr; }
    .av-times { grid-template-columns: 1fr; }
}

/* Modal Styles */
.custom-modal {
    display: none; position: fixed; z-index: 9999;
    left: 0; top: 0; width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.6); overflow: auto;
}
.custom-modal-content {
    background-color: #f1f5f9; margin: 5% auto; padding: 20px;
    border-radius: 12px; width: 90%; max-width: 800px;
    position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
.close-modal {
    position: absolute; right: 20px; top: 15px;
    font-size: 28px; font-weight: bold; color: #333;
    cursor: pointer; z-index: 10;
}
.close-modal:hover { color: #ef4444; }
.modal-actions {
    text-align: center; margin-top: 20px;
}

/* ============================================================
   PREMIUM VISITING CARD  (Front + Back)  700 x 400
   ============================================================ */
.vc-modal-content {
    background: linear-gradient(160deg, #0D2B6E 0%, #12358a 100%);
    margin: 3% auto;
    padding: 28px 24px 26px;
    border-radius: 20px;
    width: 94%;
    max-width: 790px;
    position: relative;
    box-shadow: 0 25px 60px rgba(0,0,0,.45);
    border: 1px solid rgba(255,255,255,.12);
}
.vc-modal-content .close-modal { color: #fff; top: 12px; right: 18px; }
.vc-modal-content .close-modal:hover { color: #27C96A; }

.vc-modal-title {
    color: #fff; text-align: center;
    font-family: 'Poppins', sans-serif; font-weight: 700;
    font-size: 1.15rem; margin: 0 0 4px;
}
.vc-modal-sub {
    color: #9fd8ea; text-align: center;
    font-family: 'Poppins', sans-serif; font-size: .8rem; margin: 0 0 8px;
}
.vc-label {
    color: #27C96A; font-family: 'Poppins', sans-serif;
    font-size: .7rem; font-weight: 700; letter-spacing: 2px;
    text-transform: uppercase; text-align: center; margin: 18px 0 10px;
}
.vc-scroll { overflow-x: auto; padding: 6px 0 10px; }

.vc-card {
    width: 700px; height: 400px;
    margin: 0 auto;
    border-radius: 22px;
    position: relative; overflow: hidden;
    font-family: 'Poppins', sans-serif;
    box-shadow: 0 18px 40px rgba(0,0,0,.4);
}

/* ---------- FRONT ---------- */
#visitCardFront {
    background:
        radial-gradient(circle at 88% 12%, rgba(255,255,255,.18) 0%, transparent 38%),
        radial-gradient(circle at 8% 108%, rgba(39,201,106,.55) 0%, transparent 45%),
        radial-gradient(circle at 60% 120%, rgba(0,201,167,.35) 0%, transparent 40%),
        linear-gradient(135deg, #0D2B6E 0%, #1C3F8F 55%, #00A8CC 100%);
    color: #fff;
}
#visitCardFront .vcf-ring { position: absolute; border-radius: 50%; border: 1.5px solid rgba(255,255,255,.14); }
#visitCardFront .vcf-ring.r1 { width: 330px; height: 330px; right: -90px; top: -90px; }
#visitCardFront .vcf-ring.r2 { width: 230px; height: 230px; right: -40px; top: -40px; border-color: rgba(255,255,255,.2); }
#visitCardFront .vcf-ring.r3 { width: 130px; height: 130px; right: 10px; top: 10px; background: rgba(255,255,255,.08); }
#visitCardFront .vcf-cross { position: absolute; right: 56px; top: 56px; width: 38px; height: 38px; }
#visitCardFront .vcf-cross::before,
#visitCardFront .vcf-cross::after { content: ''; position: absolute; background: #fff; border-radius: 4px; }
#visitCardFront .vcf-cross::before { left: 14px; top: 0; width: 10px; height: 38px; }
#visitCardFront .vcf-cross::after  { left: 0; top: 14px; width: 38px; height: 10px; }

#visitCardFront .vcf-top {
    position: absolute; left: 34px; top: 28px;
    display: flex; align-items: center; gap: 14px;
}
#visitCardFront .vcf-logo-box {
    width: 66px; height: 66px; border-radius: 18px;
    background: #fff; padding: 8px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 20px rgba(0,0,0,.28);
}
#visitCardFront .vcf-logo-box img { width: 100%; height: 100%; object-fit: contain; }
#visitCardFront .vcf-brand { font-size: 1.35rem; font-weight: 800; line-height: 1; letter-spacing: .3px; }
#visitCardFront .vcf-brand span { color: #27C96A; }
#visitCardFront .vcf-brand small {
    display: block; margin-top: 6px; font-size: .62rem; font-weight: 500;
    letter-spacing: 2px; text-transform: uppercase; color: rgba(255,255,255,.75);
}

#visitCardFront .vcf-main { position: absolute; left: 34px; top: 124px; width: 450px; }
#visitCardFront .vcf-type {
    display: inline-block; padding: 4px 14px; border-radius: 30px;
    background: rgba(39,201,106,.18); border: 1px solid rgba(39,201,106,.55);
    color: #8ef5b8; font-size: .66rem; font-weight: 700;
    letter-spacing: 1.8px; text-transform: uppercase;
}
#visitCardFront .vcf-name {
    margin: 12px 0 0; font-weight: 800; line-height: 1.12;
    letter-spacing: -.3px; text-shadow: 0 2px 10px rgba(0,0,0,.25);
}
#visitCardFront .vcf-line {
    width: 90px; height: 4px; border-radius: 4px; margin: 14px 0 16px;
    background: linear-gradient(90deg, #27C96A, #00C9A7);
}
#visitCardFront .vcf-detail {
    display: flex; align-items: center; gap: 12px;
    font-size: .82rem; font-weight: 500; color: rgba(255,255,255,.95);
    margin-bottom: 9px;
}
#visitCardFront .vcf-detail .ic {
    width: 26px; height: 26px; border-radius: 8px; flex-shrink: 0;
    background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: .68rem; color: #8ef5b8;
}
#visitCardFront .vcf-detail span {
    overflow: hidden; text-overflow: ellipsis;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
#visitCardFront .vcf-qr-wrap {
    position: absolute; right: 30px; bottom: 34px;
    text-align: center; background: #fff;
    padding: 10px 10px 6px; border-radius: 16px;
    box-shadow: 0 10px 26px rgba(0,0,0,.3);
}
#visitCardFront .vcf-qr-wrap .qrbox { width: 86px; height: 86px; }
#visitCardFront .vcf-qr-wrap .qrbox img,
#visitCardFront .vcf-qr-wrap .qrbox canvas { width: 86px !important; height: 86px !important; display: block; }
#visitCardFront .vcf-qr-wrap small {
    display: block; margin-top: 5px; font-size: .55rem; font-weight: 700;
    color: #0D2B6E; letter-spacing: 1.2px; text-transform: uppercase;
}
#visitCardFront .vcf-strip {
    position: absolute; left: 0; right: 0; bottom: 0; height: 8px;
    background: linear-gradient(90deg, #27C96A, #00C9A7, #00A8CC, #1C3F8F);
}

/* ---------- BACK ---------- */
#visitCardBack { background: #ffffff; display: flex; color: #0D2B6E; }
#visitCardBack .vcb-left {
    width: 42%; position: relative; overflow: hidden; color: #fff;
    background:
        radial-gradient(circle at 15% 90%, rgba(39,201,106,.55) 0%, transparent 50%),
        radial-gradient(circle at 90% 10%, rgba(255,255,255,.2) 0%, transparent 40%),
        linear-gradient(160deg, #0D2B6E 0%, #1C3F8F 60%, #00A8CC 100%);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center; padding: 24px;
}
#visitCardBack .vcb-left::before {
    content: ''; position: absolute; width: 260px; height: 260px; border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,.15); left: -90px; top: -90px;
}
#visitCardBack .vcb-left::after {
    content: ''; position: absolute; width: 180px; height: 180px; border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,.15); right: -70px; bottom: -60px;
}
#visitCardBack .vcb-logo-box {
    width: 108px; height: 108px; border-radius: 28px; background: #fff; padding: 14px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 14px 30px rgba(0,0,0,.3); position: relative; z-index: 2;
}
#visitCardBack .vcb-logo-box img { width: 100%; height: 100%; object-fit: contain; }
#visitCardBack .vcb-brand {
    margin-top: 18px; font-size: 1.7rem; font-weight: 800; line-height: 1;
    position: relative; z-index: 2;
}
#visitCardBack .vcb-brand span { color: #27C96A; }
#visitCardBack .vcb-tag {
    margin-top: 8px; font-size: .62rem; font-weight: 600; letter-spacing: 2.2px;
    text-transform: uppercase; color: rgba(255,255,255,.8); position: relative; z-index: 2;
}
#visitCardBack .vcb-right {
    width: 58%; padding: 30px 30px 26px; position: relative;
    display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;
    background: linear-gradient(180deg, #ffffff 0%, #E8F7FF 100%);
}
#visitCardBack .vcb-hname { font-size: 1.05rem; font-weight: 800; color: #0D2B6E; line-height: 1.2; margin-bottom: 4px; }
#visitCardBack .vcb-hcity { font-size: .72rem; font-weight: 600; color: #00A8CC; margin-bottom: 14px; }
#visitCardBack .vcb-qr {
    padding: 10px; background: #fff; border-radius: 18px;
    border: 2px solid #E8F7FF; box-shadow: 0 10px 26px rgba(28,63,143,.15);
}
#visitCardBack .vcb-qr .qrbox { width: 120px; height: 120px; }
#visitCardBack .vcb-qr .qrbox img,
#visitCardBack .vcb-qr .qrbox canvas { width: 120px !important; height: 120px !important; display: block; }
#visitCardBack .vcb-scan {
    margin-top: 10px; font-size: .66rem; font-weight: 700; color: #1C3F8F;
    letter-spacing: 1.6px; text-transform: uppercase;
}
#visitCardBack .vcb-web {
    margin-top: 10px; padding: 6px 18px; border-radius: 30px; color: #fff;
    font-size: .74rem; font-weight: 700; letter-spacing: .5px;
    background: linear-gradient(135deg, #1C3F8F, #00A8CC);
    box-shadow: 0 6px 16px rgba(0,168,204,.35);
}
#visitCardBack .vcb-chips { margin-top: 14px; display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }
#visitCardBack .vcb-chips em {
    font-style: normal; font-size: .56rem; font-weight: 700; padding: 3px 9px; border-radius: 20px;
    background: #F0FFF8; color: #0a9d4c; border: 1px solid rgba(39,201,106,.4);
    letter-spacing: .6px; text-transform: uppercase;
}
#visitCardBack .vcb-strip {
    position: absolute; left: 0; right: 0; bottom: 0; height: 8px;
    background: linear-gradient(90deg, #1C3F8F, #00A8CC, #00C9A7, #27C96A);
}

.vc-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 22px; }
.vc-btn {
    border: none; cursor: pointer; font-family: 'Poppins', sans-serif;
    font-weight: 700; font-size: .84rem; padding: 11px 24px; border-radius: 12px;
    display: inline-flex; align-items: center; gap: 8px; transition: all .2s ease;
}
.vc-btn:hover { transform: translateY(-2px); }
.vc-btn.green { background: linear-gradient(135deg, #27C96A, #00C9A7); color: #fff; box-shadow: 0 8px 20px rgba(39,201,106,.4); }
.vc-btn.blue  { background: linear-gradient(135deg, #00A8CC, #1C3F8F); color: #fff; box-shadow: 0 8px 20px rgba(0,168,204,.4); }
.vc-btn.white { background: #fff; color: #0D2B6E; }
.vc-btn:disabled { opacity: .7; cursor: wait; transform: none; }

/* --- BANNER DESIGN --- */
#bannerPreview {
    width: 800px; height: 450px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 16px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 2px solid #cbd5e1; padding: 30px; position: relative; overflow: hidden;
    display: flex; flex-direction: column;
}
#bannerPreview .bn-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; border-bottom: 2px dashed #cbd5e1; padding-bottom: 15px; }
#bannerPreview .bn-logo { width: 80px; height: 80px; object-fit: contain; border-radius: 12px; background: #fff; padding: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
#bannerPreview .bn-title { font-size: 2rem; font-weight: 900; color: #0D2B6E; line-height: 1.1; margin: 0; }
#bannerPreview .bn-subtitle { color: #00A8CC; font-weight: 600; font-size: 1rem; }
#bannerPreview .bn-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; flex: 1; }
#bannerPreview .bn-section { background: rgba(255,255,255,0.7); padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; }
#bannerPreview .bn-section h4 { font-size: 1rem; font-weight: 800; color: #1C3F8F; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
#bannerPreview .bn-list { list-style: none; padding: 0; margin: 0; }
#bannerPreview .bn-list li { font-size: 0.85rem; color: #475569; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
#bannerPreview .bn-list li i { color: #27C96A; font-size: 0.7rem; }
#bannerPreview .bn-footer { margin-top: 15px; text-align: center; font-size: 0.8rem; color: #64748b; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
</style>

<div class="content-wrapper">

    <!-- ===== WELCOME SECTION ===== -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>👋 Welcome, <?php echo htmlspecialchars($hospital_data['hospital_name']); ?></h1>
            <p class="welcome-sub">Manage your hospital dashboard, doctors, beds, facilities and more.</p>
            <div class="welcome-badges">
                <span class="welcome-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hospital_data['city_name'] ?? 'N/A'); ?></span>
                <span class="welcome-badge"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($hospital_data['hospital_phone']); ?></span>
                <span class="welcome-badge"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($hospital_data['hospital_email'] ?? 'N/A'); ?></span>
                <span class="welcome-badge"><i class="fas fa-calendar-alt"></i> Member since <?php echo date('M Y', strtotime($hospital_data['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <!-- ===== STATS ROW ===== -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $total_doctors; ?></div>
                <div class="stat-label">Total Doctors</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-bed"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $total_beds; ?></div>
                <div class="stat-label">Total Beds</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-concierge-bell"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $available_facilities; ?>/<?php echo $total_facilities; ?></div>
                <div class="stat-label">Facilities Available</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?></div>
                <div class="stat-label">Rating (<?php echo $total_reviews; ?> reviews)</div>
            </div>
        </div>
    </div>

    <!-- ===== 2-COLUMN DASHBOARD ===== -->
    <div class="two-col-dashboard">

        <!-- ============ LEFT COLUMN ============ -->
        <div class="col-stack">

            <!-- Recent Doctors -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-user-md"></i> Recent Doctors</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/doctors/list" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($recent_doctors_result) > 0): ?>
                        <?php while ($doctor = mysqli_fetch_assoc($recent_doctors_result)): ?>
                            <div class="doctor-item">
                                <div class="doctor-avatar"><?php echo strtoupper(substr($doctor['doctor_name'], 0, 1)); ?></div>
                                <div class="doctor-info">
                                    <div class="name">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></div>
                                    <div class="spec"><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></div>
                                    <div class="date">Added: <?php echo date('d M Y', strtotime($doctor['created_at'])); ?></div>
                                </div>
                                <a href="<?php echo BASE_URL; ?>hospital/doctor-add?id=<?php echo $doctor['doctor_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No doctors registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Availability -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-clock"></i> Availability</h5>
                    <small id="availLastSaved" style="color:#94a3b8; font-size:0.78rem; font-weight:600;"></small>
                </div>
                <div class="info-card-body">

                    <!-- Current status display -->
                    <div id="availStatus" class="av-status">
                        <div class="av-ic"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="av-main">Availability not set yet</div>
                            <div class="av-sub">Choose your hospital timing below</div>
                        </div>
                    </div>

                    <!-- Type selector -->
                    <div class="av-label">Availability Type</div>
                    <div class="av-types">
                        <div class="av-type t-247">
                            <input type="radio" name="av_type" id="avType247" value="247">
                            <label for="avType247"><i class="fas fa-infinity"></i> Open 24/7</label>
                        </div>
                        <div class="av-type t-custom">
                            <input type="radio" name="av_type" id="avTypeCustom" value="custom">
                            <label for="avTypeCustom"><i class="fas fa-calendar-alt"></i> Custom Hours</label>
                        </div>
                        <div class="av-type t-closed">
                            <input type="radio" name="av_type" id="avTypeClosed" value="closed">
                            <label for="avTypeClosed"><i class="fas fa-door-closed"></i> Temporarily Closed</label>
                        </div>
                    </div>

                    <!-- Custom hours panel -->
                    <div id="avCustomBox" class="av-custom">
                        <div class="av-label">Working Days</div>
                        <div class="av-days">
                            <?php foreach (av_days_order() as $dy): ?>
                                <label class="av-day">
                                    <input type="checkbox" class="av-day-check" value="<?php echo $dy; ?>">
                                    <span><?php echo $dy; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="av-quick">
                            <button type="button" onclick="avQuickDays('weekdays')">Mon - Fri</button>
                            <button type="button" onclick="avQuickDays('sixdays')">Mon - Sat</button>
                            <button type="button" onclick="avQuickDays('all')">All Days</button>
                            <button type="button" onclick="avQuickDays('none')">Clear</button>
                        </div>
                        <div class="av-times">
                            <div>
                                <label for="avFrom">Opening Time</label>
                                <input type="time" id="avFrom" class="av-input" value="09:00">
                            </div>
                            <div>
                                <label for="avTo">Closing Time</label>
                                <input type="time" id="avTo" class="av-input" value="17:00">
                            </div>
                        </div>
                    </div>

                    <!-- Emergency -->
                    <label class="av-emergency" for="avEmergency">
                        <input type="checkbox" id="avEmergency">
                        <div>
                            <span><i class="fas fa-ambulance"></i> Emergency Services 24/7</span>
                            <small>Emergency department is open round the clock</small>
                        </div>
                    </label>

                    <!-- Note -->
                    <div class="av-label">Extra Note (Optional)</div>
                    <input type="text" id="avNote" class="av-input" maxlength="150" placeholder="e.g. OPD closed on public holidays">

                    <div id="avError" class="av-error"></div>

                    <div class="av-actions">
                        <button id="avSaveBtn" class="av-save" onclick="saveAvailability()">
                            <i class="fas fa-save"></i> Save Availability
                        </button>
                        <div id="avSavedBadge" class="av-badge">
                            <i class="fas fa-check-circle"></i> <span id="avSavedText">Saved!</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Board -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-sticky-note"></i> Notes Board</h5>
                    <?php if ($note_row): ?>
                        <small id="noteLastSaved" style="color:#00A8CC; font-size:0.78rem; font-weight:600;">
                            <i class="fas fa-clock me-1"></i> Last saved: <?= date('d M Y, h:i A', strtotime($note_row['updated_at'])) ?>
                        </small>
                    <?php else: ?>
                        <small id="noteLastSaved" style="color:#94a3b8; font-size:0.78rem;"></small>
                    <?php endif; ?>
                </div>
                <div class="info-card-body">
                    <textarea id="noteTextarea" placeholder="Write your notes, reminders, or important info here..."
                              style="width:100%; min-height:140px; padding:14px 16px; border:2px solid rgba(0,168,204,0.2); border-radius:12px; font-size:0.92rem; color:#0D2B6E; line-height:1.7; resize:vertical; outline:none; transition:border-color 0.2s; font-family:inherit; background:#f0f9ff;"
                              onfocus="this.style.borderColor='#00A8CC'; this.style.background='#fff';"
                              onblur="this.style.borderColor='rgba(0,168,204,0.2)'; this.style.background='#f0f9ff';"
                    ><?= htmlspecialchars($note_row['notes'] ?? '') ?></textarea>
                    <div id="noteError" style="display:none;"></div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:12px; flex-wrap:wrap; gap:8px;">
                        <button id="saveNoteBtn" onclick="saveNote()" style="background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:white; border:none; padding:10px 24px; border-radius:10px; font-weight:700; font-size:0.88rem; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all 0.2s;">
                            <i class="fas fa-save"></i> Save Note
                        </button>
                        <div id="noteSavedBadge" style="display:none; align-items:center; gap:6px; background:rgba(39,201,106,0.12); border:1px solid rgba(39,201,106,0.3); border-radius:20px; padding:5px 14px; font-size:0.82rem; font-weight:700; color:#27C96A;">
                            <i class="fas fa-check-circle"></i> <span id="noteSavedText">Saved!</span>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- end LEFT column -->

        <!-- ============ RIGHT COLUMN ============ -->
        <div class="col-stack">

            <!-- Quick Actions -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="info-card-body">
                    <div class="quick-links">
                        
                        <a href="javascript:void(0)" onclick="openModal('visitCardModal')" class="quick-link-item" style="background: #fef3c7; border-color: #f59e0b; margin-bottom: 8px;">
                            <i class="fas fa-id-card" style="color:#d97706;"></i>
                            <span style="color:#b45309; font-weight:700;">Create Visiting Card</span>
                        </a>
                        
                        <a href="javascript:void(0)" onclick="openModal('bannerModal')" class="quick-link-item" style="background: #f0fdf4; border-color: #22c55e; margin-bottom: 15px;">
                            <i class="fas fa-image" style="color:#15803d;"></i>
                            <span style="color:#166534; font-weight:700;">Create Banner</span>
                        </a>

                        <a href="<?php echo BASE_URL; ?>hospital/profile" class="quick-link-item">
                            <i class="fas fa-edit"></i> <span>Edit Profile</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/doctors/list" class="quick-link-item">
                            <i class="fas fa-user-md"></i> <span>Manage Doctors</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/beds" class="quick-link-item">
                            <i class="fas fa-bed"></i> <span>Manage Beds</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/facilities" class="quick-link-item">
                            <i class="fas fa-concierge-bell"></i> <span>Manage Facilities</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/feedback" class="quick-link-item">
                            <i class="fas fa-star"></i> <span>View Reviews</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>logout" class="quick-link-item" style="color: var(--danger);">
                            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- REGISTERED LABS -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-flask"></i> Registered Labs (<?php echo $total_labs; ?>)</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/labs" class="btn btn-sm btn-primary">Manage</a>
                </div>
                <div class="info-card-body" id="labsContainer">
                    <?php if ($total_labs > 0): ?>
                        <?php while ($lab = mysqli_fetch_assoc($labs_result)): 
                            $pic_path = BASE_PATH . '/admin/inc/uploads/laboratories/' . trim($lab['lab_pic'] ?? '');
                            $has_pic = (!empty(trim($lab['lab_pic'] ?? '')) && strtolower(trim($lab['lab_pic'])) !== 'null' && file_exists($pic_path));
                        ?>
                            <div class="lab-list-item" id="lab-row-<?php echo $lab['lab_user_id']; ?>">
                                <?php if ($has_pic): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo htmlspecialchars($lab['lab_pic']); ?>" class="lab-avatar">
                                <?php else: ?>
                                    <div class="lab-avatar"><i class="fas fa-flask"></i></div>
                                <?php endif; ?>
                                <div class="lab-info">
                                    <div class="lab-name"><?php echo htmlspecialchars($lab['lab_name']); ?></div>
                                    <div class="lab-city"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($lab['city_name']); ?></div>
                                </div>
                                <button class="btn-remove-lab" onclick="removeLab(<?php echo $lab['lab_user_id']; ?>)">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No labs registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- REGISTERED RADIOLOGY -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-x-ray"></i> Registered Radiology (<?php echo $total_radiology; ?>)</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/radiology" class="btn btn-sm btn-primary">Manage</a>
                </div>
                <div class="info-card-body" id="radiologyContainer">
                    <?php if ($total_radiology > 0): ?>
                        <?php foreach ($radiology_list as $rad): 
                            $rad_pic_path = BASE_PATH . '/admin/inc/uploads/radiology/' . trim($rad['rad_pic'] ?? '');
                            $has_rad_pic = (!empty(trim($rad['rad_pic'] ?? '')) && strtolower(trim($rad['rad_pic'])) !== 'null' && file_exists($rad_pic_path));
                        ?>
                            <div class="lab-list-item rad-list-item" id="rad-row-<?php echo $rad['rad_user_id']; ?>">
                                <?php if ($has_rad_pic): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/radiology/<?php echo htmlspecialchars($rad['rad_pic']); ?>" class="lab-avatar rad-avatar">
                                <?php else: ?>
                                    <div class="lab-avatar rad-avatar"><i class="fas fa-x-ray"></i></div>
                                <?php endif; ?>
                                <div class="lab-info">
                                    <div class="lab-name"><?php echo htmlspecialchars($rad['rad_name']); ?></div>
                                    <div class="lab-city"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($rad['city_name'] ?? 'N/A'); ?></div>
                                </div>
                                <button class="btn-remove-lab" onclick="removeRadiology(<?php echo $rad['rad_user_id']; ?>)">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No radiology centers registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Recent Reviews</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/feedback" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($recent_reviews_result) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($recent_reviews_result)): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="review-name"><?php echo htmlspecialchars($review['commenter_name']); ?></span>
                                    <span class="review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['stars'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 0.75rem;"></i>
                                        <?php endfor; ?>
                                    </span>
                                </div>
                                <?php if (!empty($review['comment'])): ?>
                                    <p class="review-comment"><?php echo htmlspecialchars(substr($review['comment'], 0, 80)) . (strlen($review['comment']) > 80 ? '...' : ''); ?></p>
                                <?php endif; ?>
                                <div class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No reviews yet.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- end RIGHT column -->

    </div><!-- end 2-col dashboard -->

</div><!-- end content-wrapper -->


<!-- ========================================== -->
<!-- MODAL: VISITING CARD (FRONT + BACK)        -->
<!-- ========================================== -->
<div id="visitCardModal" class="custom-modal">
    <div class="vc-modal-content">
        <span class="close-modal" onclick="closeModal('visitCardModal')">&times;</span>

        <h3 class="vc-modal-title">Your Professional Visiting Card</h3>
        <p class="vc-modal-sub">Premium design • Front &amp; Back • High quality PNG download</p>

        <!-- FRONT -->
        <div class="vc-label">Front Side</div>
        <div class="vc-scroll">
            <div class="vc-card" id="visitCardFront">
                <div class="vcf-ring r1"></div>
                <div class="vcf-ring r2"></div>
                <div class="vcf-ring r3"></div>
                <div class="vcf-cross"></div>

                <div class="vcf-top">
                    <div class="vcf-logo-box">
                        <img src="<?php echo BASE_URL; ?>includes/uploads/logo.png" alt="SehatNexa" crossorigin="anonymous">
                    </div>
                    <div class="vcf-brand">Sehat<span>Nexa</span><small>Your Health, Our Network</small></div>
                </div>

                <div class="vcf-main">
                    <span class="vcf-type">Hospital &bull; Medical Center</span>
                    <h2 class="vcf-name" style="font-size: <?php echo $vc_name_size; ?>px;"><?php echo htmlspecialchars($vc_name); ?></h2>
                    <div class="vcf-line"></div>

                    <?php if ($vc_phone !== ''): ?>
                    <div class="vcf-detail">
                        <div class="ic"><i class="fas fa-phone"></i></div>
                        <span><?php echo htmlspecialchars($vc_phone); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($vc_email !== ''): ?>
                    <div class="vcf-detail">
                        <div class="ic"><i class="fas fa-envelope"></i></div>
                        <span><?php echo htmlspecialchars($vc_email); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($vc_address !== '' || $vc_city !== ''): ?>
                    <div class="vcf-detail">
                        <div class="ic"><i class="fas fa-map-marker-alt"></i></div>
                        <span><?php echo htmlspecialchars(trim($vc_address . ($vc_address !== '' && $vc_city !== '' ? ', ' : '') . $vc_city)); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="vcf-detail">
                        <div class="ic"><i class="fas fa-globe"></i></div>
                        <span>www.sehatnexa.com</span>
                    </div>
                </div>

                <div class="vcf-qr-wrap">
                    <div class="qrbox" id="qrFront"></div>
                    <small>Scan Profile</small>
                </div>

                <div class="vcf-strip"></div>
            </div>
        </div>

        <!-- BACK -->
        <div class="vc-label">Back Side</div>
        <div class="vc-scroll">
            <div class="vc-card" id="visitCardBack">
                <div class="vcb-left">
                    <div class="vcb-logo-box">
                        <img src="<?php echo BASE_URL; ?>includes/uploads/logo.png" alt="SehatNexa" crossorigin="anonymous">
                    </div>
                    <div class="vcb-brand">Sehat<span>Nexa</span></div>
                    <div class="vcb-tag">Your Health, Our Network</div>
                </div>

                <div class="vcb-right">
                    <div class="vcb-hname"><?php echo htmlspecialchars($vc_name); ?></div>
                    <?php if ($vc_city !== ''): ?>
                        <div class="vcb-hcity"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($vc_city); ?></div>
                    <?php endif; ?>

                    <div class="vcb-qr"><div class="qrbox" id="qrBack"></div></div>
                    <div class="vcb-scan">Scan to view full profile</div>
                    <div class="vcb-web">www.sehatnexa.com</div>

                    <div class="vcb-chips">
                        <em>Doctors</em>
                        <em>Labs</em>
                        <em>Radiology</em>
                        <em>Blood Banks</em>
                        <em>Beds</em>
                    </div>

                    <div class="vcb-strip"></div>
                </div>
            </div>
        </div>

        <div class="vc-actions">
            <button class="vc-btn green" onclick="downloadImage('visitCardFront', 'Visiting_Card_Front_<?php echo $hospital_id; ?>', this)">
                <i class="fas fa-download"></i> Download Front
            </button>
            <button class="vc-btn blue" onclick="downloadImage('visitCardBack', 'Visiting_Card_Back_<?php echo $hospital_id; ?>', this)">
                <i class="fas fa-download"></i> Download Back
            </button>
            <button class="vc-btn white" onclick="downloadBothCards(this)">
                <i class="fas fa-layer-group"></i> Download Both
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: BANNER                              -->
<!-- ========================================== -->
<div id="bannerModal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeModal('bannerModal')">&times;</span>
        
        <div id="bannerPreview">
            <div class="bn-header">
                <img src="<?php echo BASE_URL; ?>includes/uploads/logo.png" alt="Logo" class="bn-logo">
                <div>
                    <h2 class="bn-title"><?php echo htmlspecialchars($hospital_data['hospital_name']); ?></h2>
                    <div class="bn-subtitle"><?php echo htmlspecialchars($hospital_data['city_name'] ?? ''); ?> • <?php echo htmlspecialchars($hospital_data['hospital_phone']); ?></div>
                </div>
            </div>

            <div class="bn-grid">
                <!-- Doctors Column -->
                <div class="bn-section">
                    <h4><i class="fas fa-user-md"></i> Our Doctors</h4>
                    <ul class="bn-list">
                        <?php if(!empty($all_doctors_list)): ?>
                            <?php foreach(array_slice($all_doctors_list, 0, 6) as $d): ?>
                                <li><i class="fas fa-check-circle"></i> Dr. <?php echo htmlspecialchars($d['doctor_name']); ?> <span style="font-size:0.7rem; color:#64748b;">(<?php echo htmlspecialchars($d['specialization'] ?? 'General'); ?>)</span></li>
                            <?php endforeach; ?>
                            <?php if(count($all_doctors_list) > 6): ?>
                                <li style="color:#00A8CC; font-weight:bold; margin-top:5px;">+ <?php echo count($all_doctors_list) - 6; ?> more doctors...</li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li>No doctors registered yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Labs & Radiology Column -->
                <div class="bn-section">
                    <h4><i class="fas fa-flask"></i> Diagnostics</h4>
                    <ul class="bn-list">
                        <?php if($total_labs > 0): ?>
                            <?php 
                                // Reset labs result for banner to avoid interference with main loop
                                mysqli_data_seek($labs_result, 0); 
                            ?>
                            <?php $lab_count = 0; while ($l = mysqli_fetch_assoc($labs_result)): $lab_count++; if($lab_count > 3) break; ?>
                                <li><i class="fas fa-check-circle"></i> <span style="font-weight:600;">Lab:</span> <?php echo htmlspecialchars($l['lab_name']); ?></li>
                            <?php endwhile; ?>
                        <?php endif; ?>

                        <?php if(!empty($radiology_list)): ?>
                            <?php foreach(array_slice($radiology_list, 0, 3) as $r): ?>
                                <li><i class="fas fa-check-circle"></i> <span style="font-weight:600;">Radiology:</span> <?php echo htmlspecialchars($r['rad_name']); ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if($total_labs == 0 && empty($radiology_list)): ?>
                            <li>No diagnostics registered.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="bn-footer">
                Visit us at sehatnexa.com
            </div>
        </div>

        <div class="modal-actions">
            <button onclick="downloadImage('bannerPreview', 'Hospital_Banner_<?php echo $hospital_id; ?>', this)" class="btn btn-success" style="padding:10px 24px; border-radius:8px; font-weight:bold;">
                <i class="fas fa-download"></i> Download Banner
            </button>
        </div>
    </div>
</div>


<?php include BASE_PATH . '/admin/inc/footer.php'; ?>

<script>
// ============================================
// AVAILABILITY
// ============================================
var availabilityData = <?php echo json_encode($availability_view); ?>;

function avEsc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : s;
    return d.innerHTML;
}

function renderAvailability(v) {
    var box = document.getElementById('availStatus');
    var cls = 'av-status';
    var icon = 'fa-clock';
    var main = 'Availability not set yet';
    var sub  = 'Choose your hospital timing below';

    if (v.type === '247') {
        cls += ' s-247'; icon = 'fa-infinity';
        main = 'Open 24/7';
        sub  = 'Open 24 hours, all 7 days';
    } else if (v.type === 'custom') {
        cls += ' s-custom'; icon = 'fa-calendar-check';
        main = v.days_label || 'Custom Hours';
        sub  = v.time_label || '';
    } else if (v.type === 'closed') {
        cls += ' s-closed'; icon = 'fa-door-closed';
        main = 'Temporarily Closed';
        sub  = 'Currently not accepting visits';
    }

    var tags = '';
    if (v.emergency) {
        tags += '<span class="av-tag em"><i class="fas fa-ambulance"></i> Emergency 24/7</span>';
    }
    if (v.note) {
        tags += '<span class="av-tag note"><i class="fas fa-info-circle"></i> ' + avEsc(v.note) + '</span>';
    }

    box.className = cls;
    box.innerHTML =
        '<div class="av-ic"><i class="fas ' + icon + '"></i></div>' +
        '<div>' +
            '<div class="av-main">' + avEsc(main) + '</div>' +
            (sub ? '<div class="av-sub">' + avEsc(sub) + '</div>' : '') +
            (tags ? '<div class="av-tags">' + tags + '</div>' : '') +
        '</div>';
}

function fillAvailabilityForm(v) {
    var radios = document.querySelectorAll('input[name="av_type"]');
    radios.forEach(function (r) { r.checked = (r.value === v.type); });

    var checks = document.querySelectorAll('.av-day-check');
    checks.forEach(function (c) { c.checked = (v.days || []).indexOf(c.value) !== -1; });

    document.getElementById('avFrom').value = v.from || '09:00';
    document.getElementById('avTo').value   = v.to   || '17:00';
    document.getElementById('avEmergency').checked = !!v.emergency;
    document.getElementById('avNote').value = v.note || '';
    toggleCustomBox();
}

function toggleCustomBox() {
    var sel = document.querySelector('input[name="av_type"]:checked');
    var box = document.getElementById('avCustomBox');
    if (sel && sel.value === 'custom') {
        box.classList.add('show');
    } else {
        box.classList.remove('show');
    }
}

function avQuickDays(mode) {
    var map = {
        weekdays: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        sixdays:  ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        all:      ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        none:     []
    };
    var list = map[mode] || [];
    document.querySelectorAll('.av-day-check').forEach(function (c) {
        c.checked = list.indexOf(c.value) !== -1;
    });
}

function saveAvailability() {
    var errBox   = document.getElementById('avError');
    var btn      = document.getElementById('avSaveBtn');
    var badge    = document.getElementById('avSavedBadge');
    var badgeTxt = document.getElementById('avSavedText');
    var lastSaved = document.getElementById('availLastSaved');

    var sel = document.querySelector('input[name="av_type"]:checked');
    errBox.style.display = 'none';

    if (!sel) {
        errBox.textContent = 'Please select an availability type.';
        errBox.style.display = 'block';
        return;
    }

    var fd = new FormData();
    fd.append('action', 'save_availability');
    fd.append('av_type', sel.value);
    fd.append('from_time', document.getElementById('avFrom').value);
    fd.append('to_time', document.getElementById('avTo').value);
    fd.append('emergency', document.getElementById('avEmergency').checked ? '1' : '0');
    fd.append('av_note', document.getElementById('avNote').value.trim());

    var dayCount = 0;
    document.querySelectorAll('.av-day-check:checked').forEach(function (c) {
        fd.append('days[]', c.value);
        dayCount++;
    });

    if (sel.value === 'custom' && dayCount === 0) {
        errBox.textContent = 'Please select at least one working day.';
        errBox.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    badge.style.display = 'none';

    fetch('<?= BASE_URL ?>hospital/index.php', {
        method: 'POST',
        body: fd
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Availability';

        if (data.success) {
            availabilityData = data.view;
            renderAvailability(data.view);
            fillAvailabilityForm(data.view);
            badgeTxt.textContent = 'Saved!';
            badge.style.display = 'flex';
            lastSaved.innerHTML = '<i class="fas fa-clock"></i> Last saved: ' + data.time;
            lastSaved.style.color = '#00A8CC';
            setTimeout(function () { badge.style.display = 'none'; }, 3000);
        } else {
            errBox.textContent = data.message || 'Error occurred.';
            errBox.style.display = 'block';
        }
    })
    .catch(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Availability';
        errBox.textContent = 'Network error. Try again.';
        errBox.style.display = 'block';
    });
}

document.querySelectorAll('input[name="av_type"]').forEach(function (r) {
    r.addEventListener('change', toggleCustomBox);
});

renderAvailability(availabilityData);
fillAvailabilityForm(availabilityData);

// ============================================
// SAVE NOTE
// ============================================
function saveNote() {
    var text = document.getElementById('noteTextarea').value.trim();
    var errBox  = document.getElementById('noteError');
    var btn     = document.getElementById('saveNoteBtn');
    var badge   = document.getElementById('noteSavedBadge');
    var badgeTxt = document.getElementById('noteSavedText');
    var lastSaved = document.getElementById('noteLastSaved');

    errBox.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    badge.style.display = 'none';

    var formData = new FormData();
    formData.append('save_note', '1');
    formData.append('note_text', text);

    fetch('<?= BASE_URL ?>hospital/index.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Note';

        if (data.success) {
            badgeTxt.textContent = data.action === 'updated' ? 'Updated!' : 'Saved!';
            badge.style.display = 'flex';
            lastSaved.innerHTML = '<i class="fas fa-clock me-1"></i> Last saved: ' + data.time;
            lastSaved.style.color = '#00A8CC';
            setTimeout(function() { badge.style.display = 'none'; }, 3000);
        } else {
            errBox.textContent = data.msg || 'Error occurred.';
            errBox.style.display = 'block';
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Note';
        errBox.textContent = 'Network error. Try again.';
        errBox.style.display = 'block';
    });
}

document.getElementById('noteTextarea').addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        saveNote();
    }
});

// ============================================
// REMOVE LAB
// ============================================
function removeLab(labUserId) {
    if (confirm("Are you sure you want to remove this lab from your hospital?")) {
        let formData = new URLSearchParams();
        formData.append('action', 'remove_lab');
        formData.append('lab_user_id', labUserId);

        fetch('<?= BASE_URL ?>hospital/index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                let row = document.getElementById('lab-row-' + labUserId);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Something went wrong. Please try again.");
        });
    }
}

// ============================================
// REMOVE RADIOLOGY
// ============================================
function removeRadiology(radUserId) {
    if (confirm("Are you sure you want to remove this radiology center from your hospital?")) {
        let formData = new URLSearchParams();
        formData.append('action', 'remove_radiology');
        formData.append('rad_user_id', radUserId);

        fetch('<?= BASE_URL ?>hospital/index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                let row = document.getElementById('rad-row-' + radUserId);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Something went wrong. Please try again.");
        });
    }
}

// ============================================
// MODAL & IMAGE GENERATION LOGIC
// ============================================
let qrGenerated = false;
const profileUrl = "<?php echo BASE_URL; ?>hospital-detail?hospital_id=<?php echo $hospital_id; ?>";

function openModal(id) {
    document.getElementById(id).style.display = 'block';

    // Generate QR codes (front + back) once
    if (id === 'visitCardModal' && !qrGenerated) {
        new QRCode(document.getElementById("qrFront"), {
            text: profileUrl,
            width: 172,
            height: 172,
            colorDark: "#0D2B6E",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        new QRCode(document.getElementById("qrBack"), {
            text: profileUrl,
            width: 240,
            height: 240,
            colorDark: "#0D2B6E",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        qrGenerated = true;
    }
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Close modal if clicked outside
window.onclick = function(event) {
    if (event.target.classList.contains('custom-modal')) {
        event.target.style.display = "none";
    }
}

// Core: capture element and download as PNG (returns Promise)
function captureAndDownload(elementId, fileName) {
    let element = document.getElementById(elementId);
    return html2canvas(element, {
        scale: 3,
        useCORS: true,
        allowTaint: false,
        backgroundColor: null,
        logging: false
    }).then(canvas => {
        let link = document.createElement('a');
        link.download = fileName + '.png';
        link.href = canvas.toDataURL('image/png');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
}

// Download single element
function downloadImage(elementId, fileName, btn) {
    let originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    btn.disabled = true;

    captureAndDownload(elementId, fileName)
    .catch(err => {
        console.error("Error generating image: ", err);
        alert("Failed to generate image. Please try again.");
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Download both front & back
function downloadBothCards(btn) {
    let originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    btn.disabled = true;

    captureAndDownload('visitCardFront', 'Visiting_Card_Front_<?php echo $hospital_id; ?>')
    .then(() => new Promise(resolve => setTimeout(resolve, 600)))
    .then(() => captureAndDownload('visitCardBack', 'Visiting_Card_Back_<?php echo $hospital_id; ?>'))
    .catch(err => {
        console.error("Error generating image: ", err);
        alert("Failed to generate image. Please try again.");
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>