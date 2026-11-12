<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>

<?php
// ===== EDIT MODE DETECTION =====
$edit_mode = false;
$hospital_data = null;
$beds_data = null;
$facilities_data = [];
$created_at = date('Y-m-d');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $hospital_id = (int)$_GET['id'];
    $edit_query = "SELECT h.*, u.status as estatus, u.username, u.email as hospital_email
                   FROM hospitals h
                   LEFT JOIN users u ON u.user_id = h.user_id
                   WHERE h.hospital_id = $hospital_id";
    $edit_result = mysqli_query($con, $edit_query);

    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $hospital_data = mysqli_fetch_assoc($edit_result);

        $beds_result = mysqli_query($con, "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id");
        $beds_data = mysqli_fetch_assoc($beds_result);

        $facilities_result = mysqli_query($con, "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id");
        while ($row = mysqli_fetch_assoc($facilities_result)) {
            $facilities_data[] = $row;
        }
    }
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// ===== CITIES =====
$cities_query = "SELECT c.city_id, c.city_name, p.p_name
                 FROM cities c
                 LEFT JOIN provinces p ON c.province_id = p.p_id
                 WHERE c.status = 1
                 ORDER BY c.city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// ===== FACILITY LIST =====
$facility_list = [
    'emergency'         => 'Emergency',
    'icu'               => 'ICU',
    'nicu'              => 'NICU',
    'operation_theatre' => 'Operation Theatre',
    'pharmacy'          => 'Pharmacy',
    'laboratory'        => 'Laboratory',
    'blood_bank'        => 'Blood Bank',
    'radiology'         => 'Radiology',
    'mri'               => 'MRI',
    'ct_scan'           => 'CT Scan',
    'ambulance'         => 'Ambulance',
    'parking'           => 'Parking',
    'cafeteria'         => 'Cafeteria',
    'prayer_area'       => 'Prayer Area'
];

// ===== FORM SUBMISSION =====
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_id          = mysqli_real_escape_string($con, $_POST['city_id'] ?? '');
    $user_id          = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $hospital_name    = mysqli_real_escape_string($con, $_POST['hospital_name'] ?? '');
    $hospital_address = mysqli_real_escape_string($con, $_POST['hospital_address'] ?? '');
    $hospital_phone   = mysqli_real_escape_string($con, $_POST['hospital_phone'] ?? '');
    $reg_no           = mysqli_real_escape_string($con, $_POST['reg_no'] ?? '');
    $status           = isset($_POST['status']) ? 1 : 0;
    $hospital_email   = mysqli_real_escape_string($con, $_POST['hospital_email'] ?? '');
    $username         = mysqli_real_escape_string($con, $_POST['username'] ?? '');
    $pass             = isset($_POST['password']) ? trim($_POST['password']) : '';
    $password         = !empty($pass) ? base64_encode($pass) : '';

    $total_beds   = (int)($_POST['total_beds'] ?? 0);
    $icu_beds     = (int)($_POST['icu_beds'] ?? 0);
    $general_beds = (int)($_POST['general_beds'] ?? 0);
    $private_beds = (int)($_POST['private_beds'] ?? 0);

    $facilities_post = $_POST['facilities'] ?? [];

    // ===== SERVER-SIDE DUPLICATE CHECK =====
    $dup_errors = [];

    if (!$edit_mode && !empty($username)) {
        $chk = mysqli_query($con, "SELECT user_id FROM users WHERE username = '$username' LIMIT 1");
        if (mysqli_num_rows($chk) > 0) $dup_errors[] = "Username '{$username}' is already taken.";
    }

    if (!empty($reg_no)) {
        $reg_check_where = $edit_mode ? "AND hospital_id != $hospital_id" : "";
        $chk = mysqli_query($con, "SELECT hospital_id FROM hospitals WHERE reg_no = '$reg_no' $reg_check_where LIMIT 1");
        if (mysqli_num_rows($chk) > 0) $dup_errors[] = "Registration No '{$reg_no}' already exists.";
    }

    if (!empty($dup_errors)) {
        $error_msg = implode(' ', $dup_errors);
    } else {

        // ===== FILE UPLOAD =====
        $hospital_pic = '';
        if (isset($_FILES['hospital_pic']) && $_FILES['hospital_pic']['error'] == 0) {
            $target_dir = BASE_PATH . "/admin/inc/uploads/hospitals/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

            $file_name = time() . '_' . basename($_FILES["hospital_pic"]["name"]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                if (move_uploaded_file($_FILES["hospital_pic"]["tmp_name"], $target_file)) {
                    $hospital_pic = $file_name;
                }
            }
        }

        if ($edit_mode) {
            $update_query = "UPDATE hospitals SET 
                                city_id = '$city_id', 
                                hospital_name = '$hospital_name', 
                                hospital_address = '$hospital_address', 
                                hospital_phone = '$hospital_phone',
                                reg_no = '$reg_no'";

            if (!empty($hospital_pic)) {
                $update_query .= ", hospital_pic = '$hospital_pic'";
            }
            $update_query .= ", updated_at = NOW() WHERE hospital_id = $hospital_id";

            if (mysqli_query($con, $update_query)) {
                if (!empty($user_id)) {
                    $user_update = "UPDATE users SET status = '$status'";
                    if (!empty($password)) $user_update .= ", password = '$password'";
                    $user_update .= " WHERE user_id = $user_id";
                    mysqli_query($con, $user_update);
                }

                $beds_check = mysqli_query($con, "SELECT id FROM hospital_beds WHERE hospital_id = $hospital_id");
                if (mysqli_num_rows($beds_check) > 0) {
                    mysqli_query($con, "UPDATE hospital_beds SET 
                                            total_beds = $total_beds,
                                            icu_beds = $icu_beds,
                                            general_beds = $general_beds,
                                            private_beds = $private_beds,
                                            updated_at = NOW()
                                        WHERE hospital_id = $hospital_id");
                } else {
                    if ($total_beds > 0 || $icu_beds > 0 || $general_beds > 0 || $private_beds > 0) {
                        mysqli_query($con, "INSERT INTO hospital_beds (hospital_id, total_beds, icu_beds, general_beds, private_beds, created_at, updated_at) 
                                            VALUES ($hospital_id, $total_beds, $icu_beds, $general_beds, $private_beds, NOW(), NOW())");
                    }
                }

                mysqli_query($con, "DELETE FROM hospital_facilities WHERE hospital_id = $hospital_id");
                foreach ($facilities_post as $key => $facility) {
                    $facility_name = ucwords(str_replace('_', ' ', $key));
                    $description   = mysqli_real_escape_string($con, $facility['description'] ?? '');
                    $is_available  = isset($facility['available']) && $facility['available'] == '1' ? 1 : 0;

                    if ($is_available == 1 || !empty($description)) {
                        mysqli_query($con, "INSERT INTO hospital_facilities (hospital_id, facility_name, description, is_available, created_at, updated_at) 
                                            VALUES ($hospital_id, '$facility_name', '$description', $is_available, NOW(), NOW())");
                    }
                }

                $success_msg = "Hospital updated successfully!";

                // Refresh
                $edit_result = mysqli_query($con, "SELECT h.*, u.status as estatus, u.username, u.email as hospital_email
                                                   FROM hospitals h
                                                   LEFT JOIN users u ON u.user_id = h.user_id
                                                   WHERE h.hospital_id = $hospital_id");
                $hospital_data = mysqli_fetch_assoc($edit_result);

                $beds_result = mysqli_query($con, "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id");
                $beds_data = mysqli_fetch_assoc($beds_result);

                $facilities_result = mysqli_query($con, "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id");
                $facilities_data = [];
                while ($row = mysqli_fetch_assoc($facilities_result)) $facilities_data[] = $row;
            } else {
                $error_msg = "Error: " . mysqli_error($con);
            }
        } else {
            $generate_user = "INSERT INTO users (username, email, password, user_type_id, status, created_at)
                              VALUES ('$username', '$hospital_email', '$password', 5, 1, '$created_at')";
            mysqli_query($con, $generate_user);
            $userid = mysqli_insert_id($con);

            $insert_query = "INSERT INTO hospitals 
                                (city_id, user_id, hospital_name, hospital_address, hospital_phone, hospital_pic, reg_no, approve, created_at) 
                             VALUES 
                                ('$city_id', '$userid', '$hospital_name', '$hospital_address', '$hospital_phone', '$hospital_pic', '$reg_no', 1, NOW())";

            if (mysqli_query($con, $insert_query)) {
                $hospital_id = mysqli_insert_id($con);

                if ($total_beds > 0 || $icu_beds > 0 || $general_beds > 0 || $private_beds > 0) {
                    mysqli_query($con, "INSERT INTO hospital_beds (hospital_id, total_beds, icu_beds, general_beds, private_beds, created_at, updated_at) 
                                        VALUES ($hospital_id, $total_beds, $icu_beds, $general_beds, $private_beds, NOW(), NOW())");
                }

                foreach ($facilities_post as $key => $facility) {
                    $facility_name = ucwords(str_replace('_', ' ', $key));
                    $description   = mysqli_real_escape_string($con, $facility['description'] ?? '');
                    $is_available  = isset($facility['available']) && $facility['available'] == '1' ? 1 : 0;

                    if ($is_available == 1 || !empty($description)) {
                        mysqli_query($con, "INSERT INTO hospital_facilities (hospital_id, facility_name, description, is_available, created_at, updated_at) 
                                            VALUES ($hospital_id, '$facility_name', '$description', $is_available, NOW(), NOW())");
                    }
                }

                $success_msg = "Hospital added successfully!";
            } else {
                $error_msg = "Error: " . mysqli_error($con);
            }
        }
    }
}
?>

<!-- ===== BRAND COLOR THEME (Same as list page) ===== -->
<style>
:root {
    --brand:        #1e40af;
    --brand-light:  #3b82f6;
    --brand-soft:   #eff6ff;
    --brand-border: #dbeafe;
    --success:      #16a34a;
    --danger:       #dc2626;
    --text:         #0f172a;
    --text-muted:   #64748b;
    --border:       #e2e8f0;
}

/* Form card override */
.card .card-block { padding: 1.25rem; }

/* Section divider */
.form-section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--brand);
    margin: 1.5rem 0 1rem;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--brand-border);
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-section-title:first-child { margin-top: 0; }
.form-section-title i { font-size: 14px; }

/* RegNo highlighted box */
.regno-box {
    background: var(--brand-soft);
    border: 2px solid var(--brand-border);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
}
.regno-box label {
    color: var(--brand);
    font-weight: 700;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.regno-input {
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* Form controls with brand focus */
.form-control:focus, .form-select:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.12);
}
.form-control.is-invalid {
    border-color: var(--danger);
    background: #fef2f2;
}
.form-control.is-valid {
    border-color: var(--success);
    background: #f0fdf4;
}

/* Hint text */
.field-hint {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
    display: block;
}
.field-hint.is-invalid { color: var(--danger); font-weight: 500; }
.field-hint.is-valid   { color: var(--success); font-weight: 500; }

/* Bed grid */
.bed-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
.bed-card {
    background: var(--brand-soft);
    border: 1px solid var(--brand-border);
    border-radius: 8px;
    padding: 12px;
    transition: all 0.2s;
}
.bed-card:hover {
    background: #e0efff;
    transform: translateY(-2px);
}
.bed-card label {
    font-size: 12px;
    color: var(--brand);
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

/* Facilities */
.facility-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 12px;
}
.facility-item {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px;
    transition: all 0.2s;
}
.facility-item.active {
    background: var(--brand-soft);
    border-color: var(--brand);
}
.facility-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.facility-header input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--brand);
    cursor: pointer;
}
.facility-header label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    cursor: pointer;
    margin: 0;
}
.facility-item .form-control {
    font-size: 12px;
    padding: 6px 10px;
}
.facility-item .form-control:disabled {
    background: #f8fafc;
    color: var(--text-muted);
}

/* Upload box */
.upload-box {
    border: 2px dashed var(--brand-border);
    border-radius: 8px;
    padding: 24px 16px;
    text-align: center;
    cursor: pointer;
    background: var(--brand-soft);
    transition: all 0.2s;
}
.upload-box:hover {
    border-color: var(--brand);
    background: #e0efff;
}
.upload-box i {
    font-size: 28px;
    color: var(--brand);
    margin-bottom: 8px;
    display: block;
}
.upload-box p {
    margin: 0 0 4px;
    font-size: 13px;
    font-weight: 600;
    color: var(--brand);
}
.upload-box small {
    font-size: 11px;
    color: var(--text-muted);
}
.upload-box input[type="file"] { display: none; }

/* Preview image */
.img-preview {
    margin-top: 12px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
}
.img-preview img {
    width: 100%;
    max-height: 180px;
    object-fit: cover;
    display: block;
}

/* Status toggle */
.status-toggle {
    background: var(--brand-soft);
    border: 1px solid var(--brand-border);
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.status-toggle input[type="checkbox"] {
    width: 40px;
    height: 22px;
    accent-color: var(--brand);
    cursor: pointer;
}
.status-toggle .status-text strong {
    display: block;
    font-size: 13px;
    color: var(--text);
    font-weight: 600;
}
.status-toggle .status-text small {
    font-size: 11px;
    color: var(--text-muted);
}

/* Buttons */
.btn-submit {
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-light) 100%);
    color: #fff;
    border: none;
    padding: 10px 24px;
    font-weight: 600;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(30,64,175,0.2);
    transition: all 0.2s;
}
.btn-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(30,64,175,0.3);
    color: #fff;
}
.btn-submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    box-shadow: none;
}

/* Select2 brand */
.select2-container--bootstrap-5 .select2-selection {
    border: 1px solid #ced4da !important;
    border-radius: 4px !important;
    min-height: 38px !important;
    padding: 4px 8px !important;
}
.select2-container--bootstrap-5.select2-container--focus .select2-selection,
.select2-container--bootstrap-5.select2-container--open .select2-selection {
    border-color: var(--brand) !important;
    box-shadow: 0 0 0 3px rgba(30,64,175,0.12) !important;
}
.select2-container--bootstrap-5 .select2-results__option--highlighted {
    background: var(--brand) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .bed-grid { grid-template-columns: repeat(2, 1fr); }
    .facility-grid { grid-template-columns: 1fr; }
}
</style>

<div class="content-wrapper">
    <!-- Container-fluid starts -->
    <div class="container-fluid">

        <!-- ===== PAGE HEADER ===== -->
        <div class="row">
            <div class="main-header">
                <h4><?= $edit_mode ? 'Edit Hospital' : 'Add New Hospital' ?></h4>
                <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn btn-secondary pull-right">
                    <i class="icon-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- ===== ALERTS ===== -->
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-1"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- ===== FORM CARD ===== -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-header-text">
                            <i class="fas fa-hospital me-2"></i>
                            Hospital Information
                        </h5>
                    </div>
                    <div class="card-block">
                        <form method="POST" action="" enctype="multipart/form-data" id="hospitalForm">

                            <!-- ===== REG NO (HIGHLIGHT) ===== -->
                            <div class="regno-box">
                                <label for="reg_no">
                                    <i class="fas fa-certificate me-1"></i>
                                    Registration No <span style="color:var(--danger);">*</span>
                                </label>
                                <input type="text" class="form-control regno-input" id="reg_no" name="reg_no"
                                       placeholder="e.g. HOSP-2025-0001" required autocomplete="off"
                                       value="<?= $edit_mode ? htmlspecialchars($hospital_data['reg_no'] ?? '') : '' ?>">
                                <small class="field-hint" id="reg_no_hint">
                                    <i class="fas fa-info-circle me-1"></i> Unique hospital registration number
                                </small>
                            </div>

                            <!-- ===== LOGIN ACCOUNT ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-user-lock"></i>
                                <span>Login Account</span>
                            </div>

                            <input type="hidden" name="user_id" value="<?= $edit_mode && isset($hospital_data['user_id']) ? htmlspecialchars($hospital_data['user_id']) : '' ?>">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="username">Username <span style="color:var(--danger);">*</span></label>
                                        <input type="text" class="form-control" id="username" name="username"
                                               placeholder="Enter username" required autocomplete="off"
                                               value="<?= $edit_mode ? htmlspecialchars($hospital_data['username'] ?? '') : '' ?>"
                                               <?= $edit_mode ? 'readonly' : '' ?>>
                                        <small class="field-hint" id="username_hint">
                                            <i class="fas fa-info-circle me-1"></i> Unique username for login
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="hospital_email">Email</label>
                                        <input type="email" class="form-control" id="hospital_email" name="hospital_email"
                                               placeholder="hospital@example.com"
                                               value="<?= $edit_mode ? htmlspecialchars($hospital_data['hospital_email'] ?? '') : '' ?>"
                                               <?= $edit_mode ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password">
                                            Password <?= $edit_mode ? '' : '<span style="color:var(--danger);">*</span>' ?>
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password"
                                               placeholder="<?= $edit_mode ? 'Leave blank to keep current' : 'Enter password' ?>"
                                               <?= $edit_mode ? '' : 'required' ?>>
                                        <?php if ($edit_mode): ?>
                                            <small class="field-hint">Leave blank to keep current password</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== BASIC DETAILS ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-info-circle"></i>
                                <span>Basic Details</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hospitalName">Hospital Name <span style="color:var(--danger);">*</span></label>
                                        <input type="text" class="form-control" id="hospitalName" name="hospital_name"
                                               placeholder="Enter hospital name" required
                                               value="<?= $edit_mode ? htmlspecialchars($hospital_data['hospital_name'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cityId">City <span style="color:var(--danger);">*</span></label>
                                        <select class="form-control" id="cityId" name="city_id" required>
                                            <option value="">-- Search & select city --</option>
                                            <?php
                                            if ($cities_result) {
                                                mysqli_data_seek($cities_result, 0);
                                                while ($city = mysqli_fetch_assoc($cities_result)):
                                            ?>
                                                <option value="<?= $city['city_id'] ?>"
                                                    <?= ($edit_mode && isset($hospital_data['city_id']) && $hospital_data['city_id'] == $city['city_id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($city['city_name']) ?>
                                                    <?= !empty($city['p_name']) ? '(' . htmlspecialchars($city['p_name']) . ')' : '' ?>
                                                </option>
                                            <?php endwhile; } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hospitalPhone">Helpline / Phone <small class="text-muted">(optional)</small></label>
                                        <input type="text" class="form-control" id="hospitalPhone" name="hospital_phone"
                                               placeholder="e.g. 091-1234567"
                                               value="<?= $edit_mode ? htmlspecialchars($hospital_data['hospital_phone'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="hospitalAddress">Address <small class="text-muted">(optional)</small></label>
                                        <textarea class="form-control" id="hospitalAddress" name="hospital_address"
                                                  placeholder="Enter full hospital address" rows="2"><?= $edit_mode ? htmlspecialchars($hospital_data['hospital_address'] ?? '') : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== BEDS ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-bed"></i>
                                <span>Bed Availability</span>
                            </div>

                            <div class="bed-grid">
                                <div class="bed-card">
                                    <label>Total Beds</label>
                                    <input type="number" class="form-control" name="total_beds"
                                           min="0" value="<?= $edit_mode && $beds_data ? $beds_data['total_beds'] : 0 ?>">
                                </div>
                                <div class="bed-card">
                                    <label>ICU Beds</label>
                                    <input type="number" class="form-control" name="icu_beds"
                                           min="0" value="<?= $edit_mode && $beds_data ? $beds_data['icu_beds'] : 0 ?>">
                                </div>
                                <div class="bed-card">
                                    <label>General Beds</label>
                                    <input type="number" class="form-control" name="general_beds"
                                           min="0" value="<?= $edit_mode && $beds_data ? $beds_data['general_beds'] : 0 ?>">
                                </div>
                                <div class="bed-card">
                                    <label>Private Beds</label>
                                    <input type="number" class="form-control" name="private_beds"
                                           min="0" value="<?= $edit_mode && $beds_data ? $beds_data['private_beds'] : 0 ?>">
                                </div>
                            </div>

                            <!-- ===== FACILITIES ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-concierge-bell"></i>
                                <span>Facilities & Services</span>
                            </div>

                            <?php
                            $existing_facilities = [];
                            if ($edit_mode && !empty($facilities_data)) {
                                foreach ($facilities_data as $fac) {
                                    $existing_facilities[$fac['facility_name']] = [
                                        'description'  => $fac['description'] ?? '',
                                        'is_available' => $fac['is_available']
                                    ];
                                }
                            }
                            ?>

                            <div class="facility-grid">
                                <?php foreach ($facility_list as $key => $label):
                                    $is_checked   = isset($existing_facilities[$label]) && $existing_facilities[$label]['is_available'] == 1;
                                    $description  = isset($existing_facilities[$label]) ? $existing_facilities[$label]['description'] : '';
                                ?>
                                <div class="facility-item <?= $is_checked ? 'active' : '' ?>">
                                    <div class="facility-header">
                                        <input type="checkbox" class="facility-checkbox"
                                               id="chk_<?= $key ?>"
                                               data-target="facility_<?= $key ?>"
                                               <?= $is_checked ? 'checked' : '' ?>>
                                        <label for="chk_<?= $key ?>"><?= $label ?></label>
                                    </div>
                                    <input type="text" class="form-control"
                                           id="facility_<?= $key ?>"
                                           name="facilities[<?= $key ?>][description]"
                                           value="<?= htmlspecialchars($description) ?>"
                                           placeholder="e.g. 24/7, 10 beds"
                                           <?= $is_checked ? '' : 'disabled' ?>>
                                    <input type="hidden" name="facilities[<?= $key ?>][available]"
                                           value="<?= $is_checked ? '1' : '0' ?>">
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- ===== PICTURE & STATUS ===== -->
                            <div class="form-section-title">
                                <i class="fas fa-image"></i>
                                <span>Picture & Status</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Hospital Picture</label>
                                        <div class="upload-box" onclick="document.getElementById('hospital_pic').click()">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Click to upload picture</p>
                                            <small>JPG, PNG, GIF, WEBP (max 2MB)</small>
                                            <input type="file" id="hospital_pic" name="hospital_pic" accept="image/*" onchange="previewImage(this)">
                                        </div>

                                        <?php if ($edit_mode && !empty($hospital_data['hospital_pic'])): ?>
                                            <div class="img-preview" id="currentPreview">
                                                <img src="<?= BASE_URL ?>admin/inc/uploads/hospitals/<?= htmlspecialchars($hospital_data['hospital_pic']) ?>"
                                                     alt="Hospital" id="previewImg">
                                            </div>
                                        <?php else: ?>
                                            <div class="img-preview" id="currentPreview" style="display:none;">
                                                <img src="" alt="Preview" id="previewImg">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Active Status</label>
                                        <div class="status-toggle">
                                            <input type="checkbox" id="statusSwitch" name="status" value="1"
                                                   <?php
                                                   if ($edit_mode) {
                                                       echo (isset($hospital_data['estatus']) && $hospital_data['estatus'] == 1) ? 'checked' : '';
                                                   } else {
                                                       echo 'checked';
                                                   }
                                                   ?>>
                                            <div class="status-text">
                                                <strong>Active / Visible</strong>
                                                <small>When active, hospital will appear on public website</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== ACTIONS ===== -->
                            <div class="row mt-4 pt-3" style="border-top: 1px solid #e2e8f0;">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-submit" id="submitBtn">
                                        <i class="fas <?= $edit_mode ? 'fa-save' : 'fa-plus-circle' ?> me-1"></i>
                                        <?= $edit_mode ? 'Update Hospital' : 'Add Hospital' ?>
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn btn-secondary ms-2">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Container-fluid ends -->
</div>

<script>
// ===== FACILITY TOGGLE =====
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.facility-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const targetId    = this.getAttribute('data-target');
            const inputField  = document.getElementById(targetId);
            const hiddenField = this.closest('.facility-item').querySelector('input[type="hidden"]');
            const facilityItem = this.closest('.facility-item');

            if (this.checked) {
                inputField.disabled = false;
                inputField.focus();
                if (hiddenField) hiddenField.value = '1';
                facilityItem.classList.add('active');
            } else {
                inputField.disabled = true;
                inputField.value = '';
                if (hiddenField) hiddenField.value = '0';
                facilityItem.classList.remove('active');
            }
        });
    });
});

// ===== IMAGE PREVIEW =====
function previewImage(input) {
    const previewWrap = document.getElementById('currentPreview');
    const previewImg = document.getElementById('previewImg');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewWrap.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ===== SELECT2 CITY SEARCH =====
$(document).ready(function() {
    if ($.fn.select2) {
        $('#cityId').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search city...',
            allowClear: true,
            width: '100%',
            matcher: function(params, data) {
                if (!params.term || params.term.trim() === '') return data;
                const term = params.term.toLowerCase();
                if (data.text.toLowerCase().indexOf(term) > -1) return data;
                return null;
            }
        });
    }
});

// ===== LIVE DUPLICATE CHECK (DEBUG VERSION) =====
(function() {
    const usernameEl = document.getElementById('username');
    const regNoEl    = document.getElementById('reg_no');
    const userHint   = document.getElementById('username_hint');
    const regHint    = document.getElementById('reg_no_hint');
    const submitBtn  = document.getElementById('submitBtn');
    const form       = document.getElementById('hospitalForm');

    const EDIT_MODE = <?= $edit_mode ? 'true' : 'false' ?>;
    
    // IMPORTANT: Check path (agar folder iyi hai to /iyi/admin/... hoga)
    // Console me check karo ke path sahi aa raha hai ya nahi
    const CHECK_URL = '<?= BASE_URL ?>admin/check_hospital_duplicate.php';
    console.log('CHECK URL:', CHECK_URL); // Debug ke liye

    let usernameOK = EDIT_MODE;
    let regNoOK    = false;

    function updateSubmitState() {
        if (submitBtn) {
            submitBtn.disabled = !(usernameOK && regNoOK);
        }
    }

    // Username live check
    if (usernameEl && !EDIT_MODE) {
        usernameEl.addEventListener('input', function() {
            const v = this.value.trim();
            if (v === '') {
                userHint.className = 'field-hint';
                userHint.innerHTML = '<i class="fas fa-info-circle me-1"></i> Unique username for login';
                this.classList.remove('is-invalid', 'is-valid');
                usernameOK = false;
                updateSubmitState();
                return;
            }

            fetch(CHECK_URL + '?field=username&value=' + encodeURIComponent(v))
                .then(r => r.json())
                .then(data => {
                    console.log('Username Check Response:', data); // DEBUG
                    usernameOK = !data.exists;
                    if (usernameOK) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                        userHint.className = 'field-hint is-valid';
                        userHint.innerHTML = '<i class="fas fa-check me-1"></i> Available';
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                        userHint.className = 'field-hint is-invalid';
                        userHint.innerHTML = '<i class="fas fa-times me-1"></i> Already exists';
                    }
                    updateSubmitState();
                })
                .catch(err => {
                    console.error('Username Check Error:', err); // DEBUG
                    usernameOK = true; 
                    updateSubmitState();
                });
        });
    }

    // Reg No live check
    if (regNoEl) {
        regNoEl.addEventListener('input', function() {
            const v = this.value.trim();
            if (v === '') {
                regHint.className = 'field-hint';
                regHint.innerHTML = '<i class="fas fa-info-circle me-1"></i> Unique hospital registration number';
                this.classList.remove('is-invalid', 'is-valid');
                regNoOK = false;
                updateSubmitState();
                return;
            }

            let url = CHECK_URL + '?field=reg_no&value=' + encodeURIComponent(v);
            if (EDIT_MODE) url += '&exclude_id=<?= $edit_mode ? $hospital_id : 0 ?>';

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    console.log('RegNo Check Response:', data); // DEBUG
                    regNoOK = !data.exists;
                    if (regNoOK) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                        regHint.className = 'field-hint is-valid';
                        regHint.innerHTML = '<i class="fas fa-check me-1"></i> Available';
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                        regHint.className = 'field-hint is-invalid';
                        regHint.innerHTML = '<i class="fas fa-times me-1"></i> Already exists in system';
                    }
                    updateSubmitState();
                })
                .catch(err => {
                    console.error('RegNo Check Error:', err); // DEBUG
                    regNoOK = true; 
                    updateSubmitState();
                });
        });
    }

    // Initial state
    if (regNoEl && regNoEl.value.trim() !== '') regNoOK = true;
    updateSubmitState();

    // Final submit guard
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!usernameOK || !regNoOK) {
                e.preventDefault();
                alert('Please fix duplicate errors before submitting.');
            }
        });
    }
})();
</script>


<?php include BASE_PATH.'/admin/inc/footer.php';?>