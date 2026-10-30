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

                        // Excel Columns Mapping based on your provided files
                        $username   = clean_text($row[0] ?? '');
                        $password   = clean_text($row[1] ?? '');
                        $city_name  = clean_text($row[2] ?? '');
                        $name       = clean_text($row[3] ?? ''); // Hospital/Lab/BB Name
                        $contact    = clean_text($row[4] ?? '');
                        $address    = clean_text($row[5] ?? '');

                        if ($username === '' && $name === '') continue;

                        $parsed_data[] = [
                            'sno'       => count($parsed_data) + 1,
                            'username'  => $username,
                            'password'  => $password ?: '123',
                            'city_name' => $city_name,
                            'city_id'   => find_city_id($con, $city_name),
                            'name'      => $name,
                            'contact'   => $contact,
                            'address'   => $address,
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

// ============================================================
// STEP 4: Save (Sirf Checked Records)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all'])) {
    $rows = $_POST['rows'] ?? [];
    $checked = $_POST['checked'] ?? [];
    $global_import_as = mysqli_real_escape_string($con, $_POST['global_import_as'] ?? '');
    
    $saved = 0; $failed = 0;

    if (empty($global_import_as)) {
        $error_msg = "Please select what type of data you are importing (Hospital, Lab, or Blood Bank).";
    } else {
        foreach ($checked as $idx) {
            if (!isset($rows[$idx])) continue;
            $r = $rows[$idx];

            $username   = mysqli_real_escape_string($con, trim($r['username'] ?? ''));
            $password   = trim($r['password'] ?? '123');
            $name       = mysqli_real_escape_string($con, trim($r['name'] ?? ''));
            $contact    = mysqli_real_escape_string($con, trim($r['contact'] ?? ''));
            $address    = mysqli_real_escape_string($con, trim($r['address'] ?? ''));
            $city_id    = (int)($r['city_id'] ?? 0);

            if ($name === '' || $username === '' || $city_id === 0) {
                $failed++; continue;
            }

            // Check for duplicate username
            $dup_u = mysqli_query($con, "SELECT 1 FROM users WHERE username = '$username' LIMIT 1");
            if ($dup_u && mysqli_num_rows($dup_u) > 0) { $failed++; continue; }

            // Determine User Type ID based on Global Selection
            $user_type_id = 5; // Hospital default
            if ($global_import_as === 'lab') $user_type_id = 3;
            if ($global_import_as === 'blood_bank') $user_type_id = 6;

            // Insert into users
            $email = $username . '@import.local';
            $encoded_password = base64_encode($password);
            $created_at = date('Y-m-d H:i:s');
            
            $user_q = "INSERT INTO users (username, email, password, user_type_id, status, created_at) 
                       VALUES ('$username', '$email', '$encoded_password', $user_type_id, 1, '$created_at')";
            
            if (!mysqli_query($con, $user_q)) { $failed++; continue; }
            $new_user_id = mysqli_insert_id($con);

            $insert_success = false;

            // Insert into respective table
            if ($global_import_as === 'hospital') {
                $q = "INSERT INTO hospitals (city_id, user_id, hospital_name, hospital_address, hospital_phone, approve, created_at) 
                      VALUES ($city_id, $new_user_id, '$name', '$address', '$contact', 1, NOW())";
                $insert_success = mysqli_query($con, $q);
            } 
            elseif ($global_import_as === 'lab') {
                // type 2 = Independent Lab
                $q = "INSERT INTO laboratories (user_id, city_id, lab_name, lab_address, lab_phone, lab_type, approve, created_at) 
                      VALUES ($new_user_id, $city_id, '$name', '$address', '$contact', 2, 1, NOW())";
                $insert_success = mysqli_query($con, $q);
            } 
            elseif ($global_import_as === 'blood_bank') {
                $q = "INSERT INTO blood_bank (user_id, city_id, bb_name, bb_address, bb_contact, approve, created_at) 
                      VALUES ($new_user_id, $city_id, '$name', '$address', '$contact', 1, NOW())";
                $insert_success = mysqli_query($con, $q);
            }

            if ($insert_success) {
                $saved++;
            } else {
                // Rollback user if main table insert failed
                mysqli_query($con, "DELETE FROM users WHERE user_id = $new_user_id");
                $failed++;
            }
        }
        
        if ($saved > 0 || $failed > 0) {
            $success_msg = "✅ $saved records save ho gaye. ❌ $failed fail ho gaye (duplicate ya validation error).";
            $parsed_data = [];
        }
    }
}

// ============================================================
// INCLUDE ADMIN TEMPLATE
// ============================================================
include BASE_PATH.'/admin/inc/header.php';
include BASE_PATH.'/admin/inc/top.php';
include BASE_PATH.'/admin/inc/nav.php';
?>

<style>
    .import-card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:20px; border: 1px solid #e2e8f0; }
    .import-card h3 { font-size:18px; font-weight:700; margin-bottom:16px; color:#1e293b; }
    .table-import { font-size:13px; }
    .table-import th { color:#fff; white-space:nowrap; font-size:12px; padding:8px; }
    .table-import td { padding:6px; vertical-align:middle; }
    .table-import input, .table-import select { font-size:12px; padding:4px 8px; }
    .status-ok { color:#16a34a; font-weight:600; font-size: 16px; }
    .status-bad { color:#dc2626; font-weight:600; font-size: 16px; }
    .btn-save-all { background:#16a34a; color:#fff; border:none; padding:10px 22px; border-radius:8px; font-weight:600; }
    .btn-save-all:hover:not(:disabled) { background:#15803d; color:#fff; }
    .btn-save-all:disabled { background:#94a3b8; cursor:not-allowed; opacity:0.7; }
    .row-checked { background:#f0fdf4; }
    .row-unchecked { opacity:0.6; background: #f8fafc; }
    
    /* Global Type Select Styling */
    .global-type-selector { display: flex; justify-content: center; align-items: center; gap: 20px; padding: 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .global-type-selector label { font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 0; color: #334155; display: flex; align-items: center; gap: 6px; }
    .global-type-selector input[type="radio"] { width: 16px; height: 16px; cursor: pointer; }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="page-title mb-0" style="font-weight:700; color:#1e293b;">Import Directory Data (Hospitals / Labs / Blood Banks)</h4>
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
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">📄 Read Data</button>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted d-block" style="font-size:11px;">
                        Header format: Username | Password | City | Name | Contact | Address
                    </small>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <?php if (!empty($parsed_data)): ?>
        <div class="import-card p-0 overflow-hidden">
            <h3 class="p-3 mb-0 border-bottom">✏️ Verify & Save (<?= count($parsed_data) ?> rows found)</h3>

            <form method="POST" id="bulkSaveForm">
                <input type="hidden" name="save_all" value="1">
                
                <!-- Global Import Type Selector -->
                <div class="global-type-selector">
                    <span class="fw-bold text-muted me-2">Import Data As:</span>
                    <label>
                        <input type="radio" name="global_import_as" value="hospital" class="global-type-radio"> Hospital
                    </label>
                    <label>
                        <input type="radio" name="global_import_as" value="lab" class="global-type-radio"> Lab
                    </label>
                    <label>
                        <input type="radio" name="global_import_as" value="blood_bank" class="global-type-radio"> Blood Bank
                    </label>
                </div>

                <div class="table-responsive" style="max-height:70vh; overflow:auto;">
                    <table class="table table-bordered table-sm table-import mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width:40px; text-align:center; background:#0f172a;">
                                    <input type="checkbox" id="checkAll" title="Select All" checked style="cursor:pointer;">
                                </th>
                                <th style="background:#0f172a;">SNo</th>
                                <th style="background:#0f172a;">Username</th>
                                <th style="background:#0f172a;">Password</th>
                                <th style="background:#0f172a;">Name</th>
                                <th style="background:#0f172a;">Contact</th>
                                <th style="background:#0f172a;">City</th>
                                <th style="background:#0f172a;">Address</th>
                                <th style="background:#0f172a; text-align:center;">Status</th>
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
                                    <input type="text" name="rows[<?= $i ?>][username]" class="form-control username-input" value="<?= htmlspecialchars($row['username']) ?>" data-idx="<?= $i ?>" required style="width: 120px;">
                                    <small class="msg-small username-msg"></small>
                                </td>
                                <td><input type="text" name="rows[<?= $i ?>][password]" class="form-control" value="<?= htmlspecialchars($row['password']) ?>" required style="width: 80px;"></td>
                                <td><input type="text" name="rows[<?= $i ?>][name]" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required style="width: 200px;"></td>
                                <td><input type="text" name="rows[<?= $i ?>][contact]" class="form-control" value="<?= htmlspecialchars($row['contact']) ?>" style="width: 100px;"></td>
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
                                <td><input type="text" name="rows[<?= $i ?>][address]" class="form-control" value="<?= htmlspecialchars($row['address']) ?>" style="width: 250px;"></td>
                                
                                <td class="text-center">
                                    <?php if ($row['city_id'] > 0): ?>
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

                <div class="p-3 text-end border-top bg-light">
                    <button type="submit" class="btn-save-all" id="saveRecordsBtn" disabled>
                        💾 Save Checked Records
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>

<!-- Select2 JS -->
<link rel="stylesheet" href="<?= BASE_URL ?>admin/assets/css/select2.min.css">
<script src="<?= BASE_URL ?>admin/assets/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    // Initialize Select2
    $('.table-import select').select2({ width: '100%', theme: 'bootstrap-5' });

    // ============================================
    // GLOBAL TYPE SELECTION LOGIC
    // ============================================
    $('.global-type-radio').on('change', function() {
        // Enable the save button if any radio is selected
        if ($('.global-type-radio:checked').length > 0) {
            $('#saveRecordsBtn').prop('disabled', false);
        }
    });

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

    // Page load par auto-check
    $('.username-input').trigger('keyup');
});
</script>