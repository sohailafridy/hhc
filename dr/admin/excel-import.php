<?php
include '../config.php';
include '../check_auth.php';

// ============================================================
// SimpleXLSX Include
// ============================================================
require_once '../includes/simplexlsx/src/SimpleXLSX.php';
require_once '../includes/simplexlsx/src/SimpleXLSXEx.php';

use Shuchkin\SimpleXLSX;

// ============================================================
// STEP 1: Helper Functions
// ============================================================
function clean_text($val) {
    return trim((string)$val);
}

function find_city_id($con, $city_name) {
    $city_name = mysqli_real_escape_string($con, trim($city_name));
    $q = mysqli_query($con, "SELECT city_id FROM cities WHERE LOWER(city_name) = LOWER('$city_name') AND status = 1 LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) return (int)mysqli_fetch_assoc($q)['city_id'];
    return 0;
}

function find_specialization_id($con, $spec_text) {
    $spec_text_clean = trim($spec_text);
    if (empty($spec_text_clean)) return 0;

    $spec_text_esc = mysqli_real_escape_string($con, $spec_text_clean);
    
    // 1. Pehle EXACT match check karein (Case Insensitive)
    $q = mysqli_query($con, "SELECT dr_cat_type_id FROM dr_cat_types WHERE LOWER(type) = LOWER('$spec_text_esc') LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        return (int)mysqli_fetch_assoc($q)['dr_cat_type_id'];
    }

    // 2. Agar exact na mile, toh "LIKE" match try karein (e.g. PEDIATRICIAN -> Pediatrician (Child Specialist))
    // Isse wo DB ke bracket wale naam bhi pakad lega.
    $q_like = mysqli_query($con, "SELECT dr_cat_type_id FROM dr_cat_types WHERE type LIKE '%$spec_text_esc%' LIMIT 1");
    if ($q_like && mysqli_num_rows($q_like) > 0) {
        return (int)mysqli_fetch_assoc($q_like)['dr_cat_type_id'];
    }

    // 3. Special Manual Map (Kyunke kuch short forms match nahi hotin)
    $excel_spec = strtoupper($spec_text_clean);
    $manual_map = [
        'GP - GENERAL PRACTICE' => 'General Physician / Family Medicine',
        'PHYSICIAN GP'          => 'General Physician / Family Medicine',
        'MEDICAL GP'            => 'General Physician / Family Medicine',
        'PHYSICIAN'             => 'General Physician / Family Medicine',
        'MEDICAL'               => 'General Physician / Family Medicine',
        'GYNECOLOGIST/OBS'      => 'Gynecologist & Obstetrician (OB-GYN)',
        'ORTHOPEDICS'           => 'Orthopedic Surgeon',
        'NEURO'                 => 'Neurologist',
        'CHEST'                 => 'Pulmonologist'
    ];

    if (array_key_exists($excel_spec, $manual_map)) {
        $mapped_name = mysqli_real_escape_string($con, $manual_map[$excel_spec]);
        $q_map = mysqli_query($con, "SELECT dr_cat_type_id FROM dr_cat_types WHERE type = '$mapped_name' LIMIT 1");
        if ($q_map && mysqli_num_rows($q_map) > 0) {
            return (int)mysqli_fetch_assoc($q_map)['dr_cat_type_id'];
        }
    }

    return 0; // Agar kuch bhi match na ho
}

// ============================================================
// STEP 2: File Upload + Parse
// ============================================================
$parsed_data = [];
$error_msg   = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];

    if ($file['error'] !== 0) {
        $error_msg = "File upload error. Please try again.";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            $error_msg = "Sirf .xlsx, .xls, ya .csv files allowed hain.";
        } else {
            try {
                $upload_dir = BASE_PATH . '/admin/inc/uploads/excel_temp/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $temp_path = $upload_dir . time() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $temp_path);

                if ($xlsx = \Shuchkin\SimpleXLSX::parse($temp_path)) {
                    $rows = $xlsx->rows();

                    for ($i = 1; $i < count($rows); $i++) {
                        $row = $rows[$i] ?? null;
                        if (!$row) continue;

                        $pmdc_no        = clean_text($row[0] ?? '');
                        $city_name      = clean_text($row[1] ?? '');
                        $specialization = clean_text($row[2] ?? '');
                        $qualification  = clean_text($row[3] ?? '');
                        $gender         = strtolower(clean_text($row[4] ?? ''));
                        $name           = clean_text($row[5] ?? '');
                        $mobile         = clean_text($row[6] ?? '');
                        $address        = clean_text($row[7] ?? '');

                        if ($pmdc_no === '' && $name === '' && $mobile === '') continue;

                        // Auto-generate username
                        $auto_username = strtolower(preg_replace('/[^a-z0-9]/', '', $name)) . '_' . rand(1000, 9999);

                        $parsed_data[] = [
                            'sno'            => count($parsed_data) + 1,
                            'pmdc_no'        => $pmdc_no,
                            'city_name'      => $city_name,
                            'city_id'        => find_city_id($con, $city_name),
                            'specialization' => $specialization,
                            'spec_id'        => find_specialization_id($con, $specialization),
                            'qualification'  => $qualification,
                            'gender'         => $gender,
                            'name'           => $name,
                            'mobile'         => $mobile,
                            'address'        => $address,
                            'username'       => $auto_username,
                            'password'       => 'doctor123',
                        ];
                    }

                    if (count($parsed_data) === 0) {
                        $error_msg = "Excel file mein koi data nahi mila.";
                    } else {
                        $success_msg = count($parsed_data) . " rows parse ho gaye. Neeche table mein check karein aur Save karein.";
                    }
                } else {
                    $error_msg = "Excel read error: " . \Shuchkin\SimpleXLSX::parseError();
                }
                @unlink($temp_path);
            } catch (Exception $e) {
                $error_msg = "Excel read error: " . $e->getMessage();
            }
        }
    }
}

// ============================================================
// STEP 3: Dropdowns ke liye Data
// ============================================================
$cities_data = [];
$city_q = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC");
while ($c = mysqli_fetch_assoc($city_q)) $cities_data[] = $c;

$specs_data = [];
$spec_q = mysqli_query($con, "SELECT dct.dr_cat_type_id, dct.type, dc.cat_name FROM dr_cat_types dct LEFT JOIN dr_categories dc ON dct.dr_cat_id = dc.dr_cat_id ORDER BY dc.cat_name, dct.type");
while ($s = mysqli_fetch_assoc($spec_q)) $specs_data[] = $s;

$hospitals_data = [];
$hosp_q = mysqli_query($con, "SELECT hospital_id, hospital_name, city_id FROM hospitals WHERE approve = 1 ORDER BY hospital_name ASC");
while ($h = mysqli_fetch_assoc($hosp_q)) $hospitals_data[] = $h;

// ============================================================
// STEP 4: Save (Sirf Checked Records)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all'])) {
    $rows = $_POST['rows'] ?? [];
    $checked = $_POST['checked'] ?? [];
    $saved = 0; $failed = 0;

    foreach ($checked as $idx) {
        if (!isset($rows[$idx])) continue;
        $r = $rows[$idx];

        $pmdc_no        = mysqli_real_escape_string($con, trim($r['pmdc_no'] ?? ''));
        $name           = mysqli_real_escape_string($con, trim($r['name'] ?? ''));
        $qualification  = mysqli_real_escape_string($con, trim($r['qualification'] ?? ''));
        $gender         = mysqli_real_escape_string($con, trim($r['gender'] ?? 'Male'));
        $mobile         = mysqli_real_escape_string($con, trim($r['mobile'] ?? ''));
        $address        = mysqli_real_escape_string($con, trim($r['address'] ?? ''));
        $city_id        = (int)($r['city_id'] ?? 0);
        $spec_id        = (int)($r['spec_id'] ?? 0);
        $hospital_id    = (int)($r['hospital_id'] ?? 0);
        $username       = mysqli_real_escape_string($con, trim($r['username'] ?? ''));
        $password_raw   = $r['password'] ?? 'doctor123';

        if ($name === '' || $pmdc_no === '' || $city_id === 0 || $spec_id === 0 || $username === '') {
            $failed++; continue;
        }

        // Final check before insert
        $dup = mysqli_query($con, "SELECT 1 FROM doctors WHERE UPPER(pmdc_no) = UPPER('$pmdc_no') LIMIT 1");
        if ($dup && mysqli_num_rows($dup) > 0) { $failed++; continue; }

        $dup_u = mysqli_query($con, "SELECT 1 FROM users WHERE username = '$username' LIMIT 1");
        if ($dup_u && mysqli_num_rows($dup_u) > 0) { $failed++; continue; }

        // Insert into users
        $email = $username . '@import.local';
        $password = base64_encode($password_raw);
        $created_at = date('Y-m-d');
        
        $user_q = "INSERT INTO users (username, email, password, user_type_id, status, created_at) VALUES ('$username', '$email', '$password', 2, 1, '$created_at')";
        if (!mysqli_query($con, $user_q)) { $failed++; continue; }
        $new_user_id = mysqli_insert_id($con);

        // Insert into doctors
        $clinic_status = ($hospital_id === 0) ? 1 : 0;
        $doctor_type   = ($hospital_id === 0) ? 2 : 1;
        $added_by      = (int)($_SESSION['user_id'] ?? 0);
        $if_clinic     = ($hospital_id == 0) ? 1 : 0;

        // Pehle naam ko format karein
        $formatted_name = mysqli_real_escape_string($con, ucwords(strtolower(trim($name))));

        $doc_q = "INSERT INTO doctors (user_id, city_id, doctor_name, short_detail, cat_type_id, gender, doctor_phone, doctor_email, doctor_type, clinic_status, clinic_address, approve, pmdc_no, added_by, created_at) 
          VALUES ($new_user_id, $city_id, '$formatted_name', '$qualification', $spec_id, '$gender', '$mobile', '$email', $doctor_type, $clinic_status, '$address', 1, '$pmdc_no', $added_by, NOW())";
        if (!mysqli_query($con, $doc_q)) { $failed++; continue; }
        $new_doctor_id = mysqli_insert_id($con);

        // Insert into doctor_in_hospital
        if ($hospital_id > 0) {
            mysqli_query($con, "INSERT INTO doctor_in_hospital (doctor_id, hospital_id, if_clinic, inactive) VALUES ($new_doctor_id, $hospital_id, $if_clinic, 0)");
        }
        $saved++;
    }
    $success_msg = "✅ $saved doctors save ho gaye. ❌ $failed fail ho gaye (duplicate ya validation error).";
    $parsed_data = [];
}

// ============================================================
// INCLUDE ADMIN TEMPLATE (Header, Top Nav, Sidebar)
// ============================================================
include BASE_PATH.'/admin/inc/header.php';
include BASE_PATH.'/admin/inc/top.php';
include BASE_PATH.'/admin/inc/nav.php';
?>

<style>
    .import-card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:20px; border: 1px solid #e2e8f0; }
    .import-card h3 { font-size:18px; font-weight:700; margin-bottom:16px; color:#1e293b; }
    .table-import { font-size:13px; }
    .table-import th { background:#1e293b; color:#fff; white-space:nowrap; font-size:12px; padding:8px; }
    .table-import td { padding:6px; vertical-align:middle; }
    .table-import input, .table-import select { font-size:12px; padding:4px 8px; }
    .status-ok { color:#16a34a; font-weight:600; font-size: 16px; }
    .status-bad { color:#dc2626; font-weight:600; font-size: 16px; }
    .btn-save-all { background:#16a34a; color:#fff; border:none; padding:10px 22px; border-radius:8px; font-weight:600; }
    .btn-save-all:hover { background:#15803d; color:#fff; }
    .msg-small { display:block; margin-top:2px; font-size:11px; }
    .msg-ok { color:#16a34a; }
    .msg-bad { color:#dc2626; }
    .row-checked { background:#f0fdf4; }
    .row-unchecked { opacity:0.6; background: #f8fafc; }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="page-title mb-0" style="font-weight:700; color:#1e293b;">Import Doctors (Excel)</h4>
            </div>
        </div>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="import-card">
            <h3>📥 Upload Excel File</h3>
            <form method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Select File (.xlsx, .xls, .csv)</label>
                    <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <a href="<?=BASE_URL?>includes/sample.xlsx">Sample File</a>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">📄 Read Data</button>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted d-block" style="font-size:11px;">
                        Header format: PMDC NO | CITY | SPECIALIZATION | QUALIFICATION | GENDER | NAME | MOBILE | ADDRESS
                    </small>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <?php if (!empty($parsed_data)): ?>
        <div class="import-card">
            <h3>✏️ Edit & Save (<?= count($parsed_data) ?> rows found)</h3>

            <form method="POST" id="bulkSaveForm">
                <input type="hidden" name="save_all" value="1">

                <div class="table-responsive" style="max-height:70vh; overflow:auto; border: 1px solid #e2e8f0;">
                    <table class="table table-bordered table-sm table-import mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width:40px; text-align:center; background:#0f172a;">
                                    <input type="checkbox" id="checkAll" title="Select All" checked style="cursor:pointer;">
                                </th>
                                <th style="background:#0f172a;">SNo</th>
                                <th style="background:#0f172a;">PMDC No</th>
                                <th style="background:#0f172a;">Doctor Name</th>
                                <th style="background:#0f172a;">Qualification</th>
                                <th style="background:#0f172a;">Gender</th>
                                <th style="background:#0f172a;">Mobile</th>
                                <th style="background:#0f172a;">Username</th>
                                <th style="background:#0f172a;">Password</th>
                                <th style="background:#0f172a;">City</th>
                                <th style="background:#0f172a;">Specialization</th>
                                <th style="background:#0f172a;">Hospital</th>
                                <th style="background:#0f172a;">Address</th>
                                <th style="background:#0f172a;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parsed_data as $i => $row): ?>
                            <tr class="row-checked" data-row-index="<?= $i ?>">
                                <td class="text-center">
                                    <input type="checkbox" class="row-checkbox" name="checked[]" value="<?= $i ?>" checked style="cursor:pointer;">
                                </td>
                                <td class="text-center fw-bold"><?= $i + 1 ?></td>
                                <td>
                                    <input type="text" name="rows[<?= $i ?>][pmdc_no]" class="form-control pmdc-input" value="<?= htmlspecialchars($row['pmdc_no']) ?>" data-idx="<?= $i ?>" required style="width: 80px;">
                                    <small class="msg-small pmdc-msg"></small>
                                </td>
                                <td><input type="text" name="rows[<?= $i ?>][name]" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required style="width: 150px;"></td>
                                <td><input type="text" name="rows[<?= $i ?>][qualification]" class="form-control" value="<?= htmlspecialchars($row['qualification']) ?>" style="width: 120px;"></td>
                                <td>
                                    <select name="rows[<?= $i ?>][gender]" class="form-select">
                                        <option value="Male" <?= $row['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= $row['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= ($row['gender'] !== 'male' && $row['gender'] !== 'female') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </td>
                                <td><input type="text" name="rows[<?= $i ?>][mobile]" class="form-control" value="<?= htmlspecialchars($row['mobile']) ?>" style="width: 100px;"></td>
                                <td>
                                    <input type="text" name="rows[<?= $i ?>][username]" class="form-control username-input" value="<?= htmlspecialchars($row['username']) ?>" data-idx="<?= $i ?>" required style="width: 150px;">
                                    <small class="msg-small username-msg"></small>
                                </td>
                                <td>
                                    <input type="text" name="rows[<?= $i ?>][password]" class="form-control" value="<?= htmlspecialchars($row['password']) ?>" required>
                                </td>
                                <td>
                                    <select name="rows[<?= $i ?>][city_id]" class="form-select" required>
                                        <option value="">-- City --</option>
                                        <?php foreach ($cities_data as $c): ?>
                                            <option value="<?= $c['city_id'] ?>" <?= (strtoupper($row['city_name']) == strtoupper($c['city_name'])) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['city_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted" style="font-size:10px;">Excel: <?= htmlspecialchars($row['city_name']) ?></small>
                                </td>
                                <td>
                                    <select name="rows[<?= $i ?>][spec_id]" class="form-select" required>
                                        <option value="">-- Spec --</option>
                                        <?php foreach ($specs_data as $s): ?>
                                            <option value="<?= $s['dr_cat_type_id'] ?>" <?= ($row['spec_id'] == $s['dr_cat_type_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['type']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted" style="font-size:10px;">Excel: <?= htmlspecialchars($row['specialization']) ?></small>
                                </td>
                                <td>
                                    <select name="rows[<?= $i ?>][hospital_id]" class="form-select">
                                        <option value="0">-- Personal Clinic --</option>
                                        <?php foreach ($hospitals_data as $h): ?>
                                            <option value="<?= $h['hospital_id'] ?>" data-city="<?= $h['city_id'] ?>">
                                                <?= htmlspecialchars($h['hospital_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" name="rows[<?= $i ?>][address]" class="form-control" value="<?= htmlspecialchars($row['address']) ?>" style="width: 250px;"></td>
                                <td class="text-center">
                                    <?php if ($row['city_id'] > 0 && $row['spec_id'] > 0): ?>
                                        <span class="status-ok">✓</span>
                                    <?php else: ?>
                                        <span class="status-bad">✗</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn-save-all">💾 Save Checked Doctors</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>

<!-- Select2 JS (Agar admin footer mein pehle se nahi hai, to yeh lazmi chalega) -->
<link rel="stylesheet" href="<?= BASE_URL ?>admin/assets/css/select2.min.css">
<script src="<?= BASE_URL ?>admin/assets/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    // Initialize Select2
    $('.table-import select').select2({ width: '100%', theme: 'bootstrap-5' });

    // ============================================
    // CHECKBOX: Row select / deselect
    // ============================================
    $('#checkAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked).trigger('change');
    });

    $(document).on('change', '.row-checkbox', function() {
        const $row = $(this).closest('tr');
        if ($(this).is(':checked')) {
            $row.removeClass('row-unchecked').addClass('row-checked');
        } else {
            $row.removeClass('row-checked').addClass('row-unchecked');
        }

        // Update checkAll state
        const total = $('.row-checkbox').length;
        const checked = $('.row-checkbox:checked').length;
        $('#checkAll').prop('checked', total === checked);
    });

    // ============================================
    // USERNAME LIVE CHECK
    // ============================================
    $(document).on('keyup change', '.username-input', function() {
        const $input = $(this);
        const $row = $input.closest('tr');
        const $msg = $row.find('.username-msg');
        const value = $.trim($input.val());

        if (value === '') {
            $msg.text('').removeClass('msg-ok msg-bad');
            return;
        }

        $msg.text('Checking...').removeClass('msg-bad').addClass('msg-ok');

        $.ajax({
            url: '<?= BASE_URL ?>admin/check_unique_import.php',
            type: 'POST',
            dataType: 'json',
            data: { field: 'username', value: value },
            success: function(res) {
                if (res && res.exists) {
                    $msg.text('❌ Not available').removeClass('msg-ok').addClass('msg-bad');
                    $row.find('.row-checkbox').prop('checked', false).trigger('change');
                } else {
                    $msg.text('✓ Available').removeClass('msg-bad').addClass('msg-ok');
                }
            }
        });
    });

    // ============================================
    // PMDC LIVE CHECK
    // ============================================
    $(document).on('keyup change', '.pmdc-input', function() {
        const $input = $(this);
        const $row = $input.closest('tr');
        const $msg = $row.find('.pmdc-msg');
        const value = $.trim($input.val());

        if (value === '') {
            $msg.text('').removeClass('msg-ok msg-bad');
            return;
        }

        $msg.text('Checking...').removeClass('msg-bad').addClass('msg-ok');

        $.ajax({
            url: '<?= BASE_URL ?>admin/check_unique_import.php',
            type: 'POST',
            dataType: 'json',
            data: { field: 'pmdc_no', value: value },
            success: function(res) {
                if (res && res.exists) {
                    $msg.text('❌ Already exists').removeClass('msg-ok').addClass('msg-bad');
                    $row.find('.row-checkbox').prop('checked', false).trigger('change');
                } else {
                    $msg.text('✓ Available').removeClass('msg-bad').addClass('msg-ok');
                }
            }
        });
    });

    // ============================================
    // City change -> Hospital filter
    // ============================================
    $(document).on('change', 'select[name$="[city_id]"]', function() {
        const $row = $(this).closest('tr');
        const cityId = $(this).val();
        const $hosp = $row.find('select[name$="[hospital_id]"]');
        $hosp.find('option').each(function() {
            const hcity = $(this).data('city');
            if (!hcity || hcity == cityId || $(this).val() === '0') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        $hosp.val('0').trigger('change.select2');
    });

    // Page load par auto-check
    $('.username-input').trigger('keyup');
    $('.pmdc-input').trigger('keyup');
});
</script>