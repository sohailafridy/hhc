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

// Provinces Mapping (Excel name to DB province_id)
function get_province_id($province_name) {
    $province_name = strtolower(trim($province_name));
    
    // KPK
    if (strpos($province_name, 'khyber') !== false || strpos($province_name, 'kpk') !== false || strpos($province_name, 'kp') !== false) {
        return 1;
    }
    // Punjab
    if (strpos($province_name, 'punjab') !== false || strpos($province_name, 'panjab') !== false) {
        return 2;
    }
    // Balochistan
    if (strpos($province_name, 'balochistan') !== false) {
        return 3;
    }
    // Sindh
    if (strpos($province_name, 'sindh') !== false) {
        return 4;
    }
    
    return 0; // Unknown
}

// Check if city already exists
function is_city_exists($con, $city_name, $province_id) {
    $city_name_esc = mysqli_real_escape_string($con, trim($city_name));
    $q = mysqli_query($con, "SELECT city_id FROM cities WHERE LOWER(city_name) = LOWER('$city_name_esc') AND province_id = $province_id LIMIT 1");
    return ($q && mysqli_num_rows($q) > 0);
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

                        // Excel Columns Mapping
                        $province_name = clean_text($row[0] ?? '');
                        $city_name     = clean_text($row[1] ?? '');

                        if ($province_name === '' && $city_name === '') continue;

                        $province_id = get_province_id($province_name);
                        
                        // Check if exists in database
                        $exists = is_city_exists($con, $city_name, $province_id);

                        $parsed_data[] = [
                            'sno'           => count($parsed_data) + 1,
                            'province_name' => $province_name,
                            'province_id'   => $province_id,
                            'city_name'     => $city_name,
                            'exists'        => $exists
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
// STEP 3: Save (Sirf Checked Records)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all'])) {
    $rows = $_POST['rows'] ?? [];
    $checked = $_POST['checked'] ?? [];
    $saved = 0; $failed = 0;

    foreach ($checked as $idx) {
        if (!isset($rows[$idx])) continue;
        $r = $rows[$idx];

        $city_name   = mysqli_real_escape_string($con, trim($r['city_name'] ?? ''));
        $province_id = (int)($r['province_id'] ?? 0);

        if ($city_name === '' || $province_id === 0) {
            $failed++; continue;
        }

        // Final duplicate check before insert
        if (is_city_exists($con, $city_name, $province_id)) { 
            $failed++; continue; 
        }

        // Insert into cities
        $q = "INSERT INTO cities (province_id, city_name, status, approve) 
              VALUES ($province_id, '$city_name', 1, 1)";
        
        if (mysqli_query($con, $q)) {
            $saved++;
        } else {
            $failed++;
        }
    }
    $success_msg = "✅ $saved cities save ho gayi. ❌ $failed fail ho gayi (duplicate ya invalid province).";
    $parsed_data = [];
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
    .table-import th { background:#0f172a; color:#fff; white-space:nowrap; font-size:12px; padding:8px; }
    .table-import td { padding:6px; vertical-align:middle; }
    .table-import input, .table-import select { font-size:12px; padding:4px 8px; }
    .status-ok { color:#16a34a; font-weight:600; font-size: 16px; }
    .status-bad { color:#dc2626; font-weight:600; font-size: 16px; }
    .btn-save-all { background:#16a34a; color:#fff; border:none; padding:10px 22px; border-radius:8px; font-weight:600; }
    .btn-save-all:hover { background:#15803d; color:#fff; }
    .row-checked { background:#f0fdf4; }
    .row-unchecked { opacity:0.6; background: #f8fafc; }
    .row-disabled { opacity:0.5; background: #fee2e2; } /* Red tint for existing records */
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="page-title mb-0" style="font-weight:700; color:#1e293b;">Import Cities (Excel)</h4>
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
                    <a href="<?=BASE_URL?>includes/cities-sample.xlsx">Sample File</a>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">📄 Read Data</button>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted d-block" style="font-size:11px;">
                        Header format: Province | City Name
                    </small>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <?php if (!empty($parsed_data)): ?>
        <div class="import-card">
            <h3>✏️ Verify & Save (<?= count($parsed_data) ?> rows found)</h3>

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
                                <th style="background:#0f172a;">Province (Excel)</th>
                                <th style="background:#0f172a;">Mapped Province ID</th>
                                <th style="background:#0f172a;">City Name</th>
                                <th style="background:#0f172a; text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parsed_data as $i => $row): 
                                // Agar pehle se DB me hai, to disable kar do
                                $is_disabled = $row['exists'] || $row['province_id'] === 0;
                                $row_class = $is_disabled ? 'row-disabled' : 'row-checked';
                                $checked_attr = $is_disabled ? '' : 'checked';
                            ?>
                            <tr class="<?= $row_class ?>" data-row-index="<?= $i ?>">
                                <td class="text-center">
                                    <input type="checkbox" class="row-checkbox" name="checked[]" value="<?= $i ?>" <?= $checked_attr ?> <?= $is_disabled ? 'disabled' : '' ?> style="cursor:pointer;">
                                </td>
                                <td class="text-center fw-bold"><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($row['province_name']) ?></td>
                                <td>
                                    <?php if ($row['province_id'] > 0): ?>
                                        <span class="badge bg-success">ID: <?= $row['province_id'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Invalid Province</span>
                                    <?php endif; ?>
                                    
                                    <!-- Hidden fields for saving -->
                                    <input type="hidden" name="rows[<?= $i ?>][province_id]" value="<?= $row['province_id'] ?>">
                                    <input type="hidden" name="rows[<?= $i ?>][city_name]" value="<?= htmlspecialchars($row['city_name']) ?>">
                                </td>
                                <td><?= htmlspecialchars($row['city_name']) ?></td>
                                <td class="text-center">
                                    <?php if ($is_disabled): ?>
                                        <?php if ($row['exists']): ?>
                                            <span class="status-bad" title="Already exists in Database">❌ Already Exists</span>
                                        <?php else: ?>
                                            <span class="status-bad" title="Invalid Province Name">❌ Invalid Province</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="status-ok">✓ Ready to Import</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn-save-all">💾 Save Checked Cities</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>

<script>
$(document).ready(function() {

    // ============================================
    // CHECKBOX: Row select / deselect
    // ============================================
    $('#checkAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        // Only check those which are NOT disabled
        $('.row-checkbox:not(:disabled)').prop('checked', isChecked).trigger('change');
    });

    $(document).on('change', '.row-checkbox', function() {
        const $row = $(this).closest('tr');
        if ($(this).is(':checked')) {
            $row.removeClass('row-unchecked').addClass('row-checked');
        } else {
            $row.removeClass('row-checked').addClass('row-unchecked');
        }

        // Update checkAll state based on ENABLED checkboxes
        const total = $('.row-checkbox:not(:disabled)').length;
        const checked = $('.row-checkbox:not(:disabled):checked').length;
        $('#checkAll').prop('checked', total === checked);
    });
});
</script>