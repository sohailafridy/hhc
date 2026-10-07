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

$user_id = (int)$_SESSION['user_id'];

// Get hospital data
$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id   = (int)$hospital_data['hospital_id'];
$city_id       = (int)$hospital_data['city_id'];
$hospital_name = $hospital_data['hospital_name'];

// ============================================
// doctor_in_hospital sync (duplicate row nahi banti)
//   Hospital doctor => hospital_id = X, if_clinic = 0
// ============================================
function sync_doctor_workplace($con, $doctor_id, $hospital_id) {
    $doctor_id   = (int)$doctor_id;
    $hospital_id = (int)$hospital_id;
    if ($hospital_id === 0) return;

    $check = mysqli_query($con, "SELECT doctor_in_hosp_id FROM doctor_in_hospital
                                 WHERE doctor_id = $doctor_id AND hospital_id = $hospital_id AND if_clinic = 0
                                 LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        mysqli_query($con, "UPDATE doctor_in_hospital SET inactive = 0
                            WHERE doctor_in_hosp_id = " . (int)$row['doctor_in_hosp_id']);
    } else {
        mysqli_query($con, "INSERT INTO doctor_in_hospital (doctor_id, hospital_id, if_clinic, inactive)
                            VALUES ($doctor_id, $hospital_id, 0, 0)");
    }
}

// ============================================
// EDIT MODE - Get doctor data
// ============================================
$edit_mode = false;
$doctor_data = null;
$is_edit = isset($_GET['id']) && is_numeric($_GET['id']);

if ($is_edit) {
    $doctor_id = (int)$_GET['id'];

    $check_query = "SELECT d.*, dct.type as specialization_name, u.status as estatus,
                           u.status as user_status, u.reference as ref, u.username
                    FROM doctors d
                    LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                    LEFT JOIN users u ON u.user_id = d.user_id
                    LEFT JOIN doctor_in_hospital dih ON dih.doctor_id = d.doctor_id
                    WHERE dih.doctor_id = $doctor_id AND dih.hospital_id = $hospital_id AND d.approve = 1
                    LIMIT 1";
    $check_result = mysqli_query($con, $check_query);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $edit_mode = true;
        $doctor_data = mysqli_fetch_assoc($check_result);
    } else {
        $_SESSION['error_msg'] = "Doctor not found or you don't have permission to edit.";
        header("Location: " . BASE_URL . "hospital/doctors/list");
        exit();
    }
}

// ============================================
// FETCH SPECIALIZATIONS
// ============================================
$categories_query = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type 
                   FROM dr_categories dc 
                   LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id 
                   ORDER BY dc.cat_name, dct.type";
$categories_result = mysqli_query($con, $categories_query);

$categories_data = [];
if ($categories_result) {
    while ($row = mysqli_fetch_assoc($categories_result)) {
        if (!isset($categories_data[$row['dr_cat_id']])) {
            $categories_data[$row['dr_cat_id']] = ['cat_name' => $row['cat_name'], 'types' => []];
        }
        if ($row['dr_cat_type_id']) {
            $categories_data[$row['dr_cat_id']]['types'][] = [
                'dr_cat_type_id' => $row['dr_cat_type_id'],
                'type' => $row['type']
            ];
        }
    }
}

// ============================================
// HANDLE FORM SUBMISSION
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $selected_existing_doctor = (!$edit_mode && !empty($_POST['existing_doctor_id']))
        ? (int)$_POST['existing_doctor_id']
        : 0;

    if ($selected_existing_doctor > 0) {
        // ============================================
        // EXISTING DOCTOR - Just assign to this hospital
        // ============================================
        $ex_id = $selected_existing_doctor;

        $check_dih = mysqli_query($con, "SELECT 1 FROM doctor_in_hospital
                                         WHERE doctor_id = $ex_id AND hospital_id = $hospital_id AND inactive = 0 LIMIT 1");
        if ($check_dih && mysqli_num_rows($check_dih) > 0) {
            $error_msg = "This doctor is already assigned to your hospital!";
        } else {
            sync_doctor_workplace($con, $ex_id, $hospital_id);
            $_SESSION['success_msg'] = "Doctor assigned to hospital successfully!";
            header("Location: " . BASE_URL . "hospital/doctors.php");
            exit();
        }

    } else {
        // ============================================
        // Common fields (add + edit)
        // ============================================
        $doctor_name = mysqli_real_escape_string($con, $_POST['doctor_name']);
        $cat_type_id = (int)$_POST['specialization'];

        if (isset($_POST['if_not_available']) && $_POST['if_not_available'] == 1) {
            $doct_role = mysqli_real_escape_string($con, $_POST['specialization_txt']);
            $new_cat = "INSERT INTO dr_cat_types (dr_cat_id, type) VALUES (12, '$doct_role')";
            if (mysqli_query($con, $new_cat)) {
                $cat_type_id = mysqli_insert_id($con);
            }
        }

        $experience_years     = (int)$_POST['experience_years'];
        $doctor_phone         = mysqli_real_escape_string($con, $_POST['doctor_phone']);
        $gender               = mysqli_real_escape_string($con, $_POST['gender']);
        $short_detail         = mysqli_real_escape_string($con, $_POST['short_detail']);
        $other                = mysqli_real_escape_string($con, $_POST['other'] ?? '');
        $static_clinical_info = mysqli_real_escape_string($con, $_POST['static_clinical_info'] ?? '');
        $status               = isset($_POST['status']) ? 1 : 0;
        $mahre_amraz          = mysqli_real_escape_string($con, $_POST['mahre_amraz'] ?? '');
        $notes                = mysqli_real_escape_string($con, $_POST['notes'] ?? '');
        $pass                 = mysqli_real_escape_string($con, $_POST['password'] ?? '');

        if ($edit_mode) {
            // ============================================
            // EDIT MODE - username / email / cnic update nahi hote (readonly)
            // ============================================
            $edit_user_id = (int)$doctor_data['user_id'];   // POST se nahi, DB se

            // Picture upload
            $doctor_pic = '';
            if (isset($_FILES['doctor_pic']) && $_FILES['doctor_pic']['error'] == 0) {
                $target_dir = BASE_PATH . "/admin/inc/uploads/doctors/";
                if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
                $file_name = time() . '_' . basename($_FILES["doctor_pic"]["name"]);
                $target_file = $target_dir . $file_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    if (move_uploaded_file($_FILES["doctor_pic"]["tmp_name"], $target_file)) {
                        $doctor_pic = $file_name;
                    }
                }
            }

            $update_query = "UPDATE doctors SET
                                doctor_name = '$doctor_name',
                                cat_type_id = '$cat_type_id',
                                experience_years = '$experience_years',
                                doctor_phone = '$doctor_phone',
                                gender = '$gender',
                                short_detail = '$short_detail',
                                other = '$other',
                                static_clinical_info = '$static_clinical_info',
                                mahre_amraz = '$mahre_amraz',
                                notes = '$notes'";
            if ($doctor_pic !== '') { $update_query .= ", doctor_pic = '$doctor_pic'"; }
            $update_query .= ", updated_at = NOW() WHERE doctor_id = $doctor_id";

            if (mysqli_query($con, $update_query)) {
                $ref = ($status == 0) ? mysqli_real_escape_string($con, $_POST['ref'] ?? '') : '';
                $user_update = "UPDATE users SET status = '$status', reference = '$ref'";
                if ($pass !== '') { $user_update .= ", password = '" . base64_encode($pass) . "'"; }
                $user_update .= " WHERE user_id = $edit_user_id";
                mysqli_query($con, $user_update);

                // Clinical info save (doctor_in_hosp_id ke hisaab se update ya insert)
                if (!empty($_POST['clinical_data']) && is_array($_POST['clinical_data'])) {
                    foreach ($_POST['clinical_data'] as $dih_id => $ci) {
                        $dih_id = (int)$dih_id;
                        // Sirf is hospital ki apni row allow karo
                        $own = mysqli_query($con, "SELECT 1 FROM doctor_in_hospital
                                                   WHERE doctor_in_hosp_id = $dih_id AND doctor_id = $doctor_id AND hospital_id = $hospital_id LIMIT 1");
                        if (!$own || mysqli_num_rows($own) == 0) continue;

                        $f = [];
                        foreach (['morning_opening_time','morning_closing_time','evening_opening_time','evening_closing_time','season','contact','days','off_days','detail'] as $k) {
                            $f[$k] = mysqli_real_escape_string($con, trim($ci[$k] ?? ''));
                        }

                        // Shift auto-detect (time ke hisaab se)
                        $m_ok = ($f['morning_opening_time'] !== '' && $f['morning_closing_time'] !== '');
                        $e_ok = ($f['evening_opening_time'] !== '' && $f['evening_closing_time'] !== '');
                        $f['shift'] = ($m_ok && $e_ok) ? 'Both' : ($m_ok ? 'Morning' : ($e_ok ? 'Evening' : ''));

                        $exists = mysqli_query($con, "SELECT 1 FROM clinical_info WHERE doctor_in_hosp_id = $dih_id LIMIT 1");
                        if ($exists && mysqli_num_rows($exists) > 0) {
                            mysqli_query($con, "UPDATE clinical_info SET
                                morning_opening_time='{$f['morning_opening_time']}', morning_closing_time='{$f['morning_closing_time']}',
                                evening_opening_time='{$f['evening_opening_time']}', evening_closing_time='{$f['evening_closing_time']}',
                                season='{$f['season']}', contact='{$f['contact']}', days='{$f['days']}',
                                off_days='{$f['off_days']}', shift='{$f['shift']}', detail='{$f['detail']}'
                                WHERE doctor_in_hosp_id = $dih_id");
                        } else {
                            mysqli_query($con, "INSERT INTO clinical_info
                                (doctor_in_hosp_id, morning_opening_time, morning_closing_time, evening_opening_time, evening_closing_time, season, contact, days, off_days, shift, detail)
                                VALUES ($dih_id, '{$f['morning_opening_time']}', '{$f['morning_closing_time']}', '{$f['evening_opening_time']}', '{$f['evening_closing_time']}',
                                        '{$f['season']}', '{$f['contact']}', '{$f['days']}', '{$f['off_days']}', '{$f['shift']}', '{$f['detail']}')");
                        }
                    }
                }

                $_SESSION['success_msg'] = "Doctor updated successfully!";
                header("Location: " . BASE_URL . "hospital/doctors.php");
                exit();
            } else {
                $error_msg = "Error: " . mysqli_error($con);
            }

        } else {
            // ============================================
            // NEW DOCTOR - duplicate check (Username / Email / CNIC)
            // ============================================
            $doctor_email = mysqli_real_escape_string($con, $_POST['doctor_email']);
            $username     = mysqli_real_escape_string($con, $_POST['username']);
            $cnic         = mysqli_real_escape_string($con, trim($_POST['cnic'] ?? ''));
            $cnic_digits  = preg_replace('/\D/', '', $cnic);
            $password     = !empty($pass) ? base64_encode($pass) : base64_encode('123456');

            $dup_errors = [];
            if ($cnic_digits !== '' && mysqli_num_rows(mysqli_query($con,
                "SELECT 1 FROM doctors WHERE REPLACE(cnic,'-','')='$cnic_digits' LIMIT 1")) > 0) {
                $dup_errors[] = "This CNIC '" . htmlspecialchars($_POST['cnic']) . "' already exists.";
            }
            if ($doctor_email !== '' && (
                mysqli_num_rows(mysqli_query($con, "SELECT 1 FROM doctors WHERE doctor_email='$doctor_email' LIMIT 1")) > 0 ||
                mysqli_num_rows(mysqli_query($con, "SELECT 1 FROM users WHERE email='$doctor_email' LIMIT 1")) > 0)) {
                $dup_errors[] = "This Email '" . htmlspecialchars($_POST['doctor_email']) . "' already exists.";
            }
            if ($username !== '' && mysqli_num_rows(mysqli_query($con,
                "SELECT 1 FROM users WHERE username='$username' LIMIT 1")) > 0) {
                $dup_errors[] = "This Username '" . htmlspecialchars($_POST['username']) . "' already exists.";
            }

            if (!empty($dup_errors)) {
                $error_msg = implode('<br>', $dup_errors);
            } else {
                // Picture upload (duplicate check ke baad)
                $doctor_pic = '';
                if (isset($_FILES['doctor_pic']) && $_FILES['doctor_pic']['error'] == 0) {
                    $target_dir = BASE_PATH . "/admin/inc/uploads/doctors/";
                    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
                    $file_name = time() . '_' . basename($_FILES["doctor_pic"]["name"]);
                    $target_file = $target_dir . $file_name;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        if (move_uploaded_file($_FILES["doctor_pic"]["tmp_name"], $target_file)) {
                            $doctor_pic = $file_name;
                        }
                    }
                }

                $user_query = "INSERT INTO users (username, email, password, user_type_id, status, created_at) 
                               VALUES ('$username', '$doctor_email', '$password', 2, 1, NOW())";
                if (!mysqli_query($con, $user_query)) {
                    $error_msg = "User create nahi hua: " . mysqli_error($con);
                } else {
                    $user_id_new = mysqli_insert_id($con);

                    $insert_query = "INSERT INTO doctors (
                                        user_id, city_id, hospital_id, doctor_name, 
                                        cat_type_id, experience_years, doctor_phone, doctor_email, cnic,
                                        doctor_type, gender, short_detail, other, static_clinical_info,
                                        mahre_amraz, notes, doctor_pic, approve, status, created_at
                                    ) VALUES (
                                        '$user_id_new', '$city_id', '$hospital_id', '$doctor_name',
                                        '$cat_type_id', '$experience_years', '$doctor_phone', '$doctor_email', '$cnic',
                                        '1', '$gender', '$short_detail', '$other', '$static_clinical_info',
                                        '$mahre_amraz', '$notes', '$doctor_pic', 1, 1, NOW()
                                    )";

                    if (mysqli_query($con, $insert_query)) {
                        $last_insert_id = mysqli_insert_id($con);
                        sync_doctor_workplace($con, $last_insert_id, $hospital_id);

                        $_SESSION['success_msg'] = "Doctor added successfully!";
                        header("Location: " . BASE_URL . "hospital/doctors.php");
                        exit();
                    } else {
                        $error_msg = "Error: " . mysqli_error($con);
                        mysqli_query($con, "DELETE FROM users WHERE user_id = " . (int)$user_id_new);  // orphan user hata do
                    }
                }
            }
        }
    }
}

// ============================================
// FETCH EXISTING DOCTORS FOR THIS HOSPITAL'S CITY (add mode only)
// ============================================
$existing_doctors = [];
if (!$edit_mode) {
    $existing_doctors_query = "SELECT d.doctor_id, d.doctor_name, dct.type as specialization 
                               FROM doctors d
                               LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                               LEFT JOIN users u ON u.user_id = d.user_id
                               WHERE d.city_id = $city_id AND d.approve = 1 AND u.status = 1
                               ORDER BY d.doctor_name ASC";
    $existing_doctors_result = mysqli_query($con, $existing_doctors_query);
    while ($row = mysqli_fetch_assoc($existing_doctors_result)) {
        $existing_doctors[] = $row;
    }
}

// ============================================
// FETCH DOCTOR-IN-HOSPITAL (for clinical info)
// ============================================
$doctor_in_hosp_ids = [];
$clinical_info = [];
$dih_rows = [];

if ($edit_mode) {
    $dih_query = "SELECT dih.doctor_in_hosp_id, h.hospital_name, h.hospital_id
                  FROM doctor_in_hospital dih
                  LEFT JOIN hospitals h ON dih.hospital_id = h.hospital_id
                  WHERE dih.doctor_id = $doctor_id AND dih.hospital_id = $hospital_id";
    $dih_result = mysqli_query($con, $dih_query);
    while ($row = mysqli_fetch_assoc($dih_result)) {
        $doctor_in_hosp_ids[] = $row['doctor_in_hosp_id'];
        $dih_rows[] = $row;
    }

    if (!empty($doctor_in_hosp_ids)) {
        $ids_string = implode(',', array_map('intval', $doctor_in_hosp_ids));
        $ci_result = mysqli_query($con, "SELECT * FROM clinical_info WHERE doctor_in_hosp_id IN ($ids_string)");
        while ($row = mysqli_fetch_assoc($ci_result)) {
            $clinical_info[$row['doctor_in_hosp_id']] = $row;
        }
    }
}

// Form values: POST fallback (error ke baad form khali na ho) -> edit data -> empty
function fv($key, $post_key = null) {
    global $edit_mode, $doctor_data;
    $pk = $post_key ?? $key;
    if (isset($_POST[$pk])) return htmlspecialchars($_POST[$pk]);
    return ($edit_mode && isset($doctor_data[$key])) ? htmlspecialchars($doctor_data[$key]) : '';
}

// ---- Clinical info helpers ----
// "14:30", "14:30:00" ya "02:30 PM" -> hamesha "02:30 PM" (flatpickr format)
function ci_time($v) {
    $v = trim((string)$v);
    if ($v === '') return '';
    $t = strtotime($v);
    return $t ? date('h:i A', $t) : $v;
}
// Dropdown options; agar purani value list mein nahi hai to bhi show hogi
function ci_options(array $options, $selected) {
    $selected = (string)$selected;
    if ($selected !== '' && !in_array($selected, $options, true)) {
        $options[] = $selected;
    }
    foreach ($options as $opt) {
        echo '<option value="' . htmlspecialchars($opt) . '"' . ($selected === $opt ? ' selected' : '') . '>'
            . htmlspecialchars($opt) . '</option>';
    }
}
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-blood-bank-add-admin.css">
<style>
    .field-msg { display:block; margin-top:4px; font-size:12px; }
    .field-msg.error { color:#dc3545; }
    #saveBtn:disabled { opacity:.5; cursor:not-allowed; }
    .form-control-modern[readonly] { background:#f1f3f5; cursor:not-allowed; }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        
        <!-- ===== PAGE HEADER ===== -->
        <div class="page-header animate-up">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="page-title">
                        <i class="fas fa-user-md me-2"></i> 
                        <?php echo $edit_mode ? 'Edit Doctor' : 'Add New Doctor'; ?>
                    </h2>
                    <p class="page-subtitle">
                        <?php echo $edit_mode ? 'Update doctor information' : 'Add a new doctor to ' . htmlspecialchars($hospital_name); ?>
                    </p>
                </div>
                <a href="<?php echo BASE_URL; ?>hospital/doctors.php" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>

        <!-- ===== ALERTS ===== -->
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-modern alert-success-modern animate-up">
                <i class="fas fa-check-circle fs-4 me-2"></i>
                <strong>Success!</strong> &nbsp; <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-modern alert-danger-modern animate-up">
                <i class="fas fa-exclamation-circle fs-4 me-2"></i>
                <strong>Error!</strong> &nbsp; <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- ===== FORM ===== -->
        <form method="POST" action="" enctype="multipart/form-data" class="animate-up delay-1" id="doctorForm">
            
            <input type="hidden" name="user_id" value="<?php if(isset($doctor_data['user_id'])){ echo (int)$doctor_data['user_id']; } ?>">
            
            <!-- ===== EXISTING DOCTOR SELECTION ===== -->
            <?php if (!empty($existing_doctors)): ?>
            <div class="modern-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-user-check me-2"></i> Existing Doctors</h5>
                    <span class="badge bg-primary"><?php echo count($existing_doctors); ?> Available</span>
                </div>
                <div class="card-body-custom">
                    <div class="existing-doctor-section">
                        <div class="section-label">
                            <i class="fas fa-info-circle"></i>
                            Select an existing doctor to assign to your hospital
                        </div>
                        <div class="form-group">
                            <label class="form-label">Select Doctor</label>
                            <select class="form-control-modern" id="existingDoctorSelect" name="existing_doctor_id" style="width:100%;">
                                <option value="">-- Select Existing Doctor --</option>
                                <?php foreach ($existing_doctors as $doc): ?>
                                    <option value="<?php echo $doc['doctor_id']; ?>">
                                        <?php echo htmlspecialchars($doc['doctor_name']); ?> 
                                        (<?php echo htmlspecialchars($doc['specialization'] ?? 'General'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Selecting a doctor will auto-fill the form below. Submit will assign them to your hospital.</small>
                        </div>
                        <div class="text-center mt-2"><span class="text-muted">OR</span></div>
                        <div class="text-center mt-2">
                            <small>Fill the form below to add a <strong>new doctor</strong> to the system.</small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- ===== HOSPITAL INFO ===== -->
            <div class="modern-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-hospital me-2"></i> Hospital Information</h5>
                </div>
                <div class="card-body-custom">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Hospital</label>
                                <input type="text" class="form-control-modern" 
                                       value="<?php echo htmlspecialchars($hospital_name); ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control-modern" 
                                       value="<?php 
                                            $city_result = mysqli_query($con, "SELECT city_name FROM cities WHERE city_id = $city_id");
                                            $city_row = mysqli_fetch_assoc($city_result);
                                            echo htmlspecialchars($city_row['city_name'] ?? 'N/A');
                                       ?>" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== PERSONAL DETAILS ===== -->
            <div class="modern-card" id="personalDetailsCard">
                <div class="card-header-custom">
                    <h5><i class="fas fa-user me-2"></i> Personal Details</h5>
                </div>
                <div class="card-body-custom">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Username <span class="required">*</span></label>
                                <input type="text" class="form-control-modern" id="username" name="username" 
                                       placeholder="Enter username" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['username'] ?? '') : fv('username'); ?>"
                                       <?php echo $edit_mode ? 'readonly' : ''; ?>>
                                <small class="field-msg" id="username_msg"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    Password
                                    <?php if (!$edit_mode): ?>
                                        <span class="required">*</span>
                                    <?php else: ?>
                                        <span class="optional">(Optional)</span>
                                    <?php endif; ?>
                                </label>
                                <input type="password" class="form-control-modern" id="password" name="password" 
                                       placeholder="<?php echo $edit_mode ? 'Leave blank to keep current' : 'Enter password'; ?>"
                                       <?php echo $edit_mode ? '' : 'required'; ?>>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Doctor Name <span class="required">*</span></label>
                                <input type="text" class="form-control-modern" id="doctorName" name="doctor_name" required
                                       placeholder="Dr. John Doe" value="<?php echo fv('doctor_name'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Email <span class="required">*</span></label>
                                <input type="email" class="form-control-modern" id="doctorEmail" name="doctor_email" required
                                       placeholder="doctor@example.com" value="<?php echo fv('doctor_email'); ?>"
                                       <?php echo $edit_mode ? 'readonly' : ''; ?>>
                                <small class="field-msg" id="doctorEmail_msg"></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Phone <span class="required">*</span></label>
                                <input type="tel" class="form-control-modern" id="doctorPhone" name="doctor_phone" required
                                       placeholder="+92 300 1234567" value="<?php echo fv('doctor_phone'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">CNIC</label>
                                <input type="text" class="form-control-modern" id="cnic" name="cnic"
                                       placeholder="1234512345671" maxlength="15" title="Format: 1234512345671"
                                       value="<?php echo fv('cnic'); ?>"
                                       <?php echo $edit_mode ? 'readonly' : ''; ?>>
                                <small class="field-msg" id="cnic_msg"></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Gender <span class="required">*</span></label>
                                <?php $g = $_POST['gender'] ?? ($edit_mode ? $doctor_data['gender'] : ''); ?>
                                <select class="form-control-modern" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo $g == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $g == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $g == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Experience (Years) <span class="optional">(Optional)</span></label>
                                <input type="number" class="form-control-modern" id="experienceYears" name="experience_years" min="0" max="60"
                                       placeholder="e.g. 5" value="<?php echo fv('experience_years'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Specialization <span class="required">*</span></label>
                                <?php $sel_cat = $_POST['specialization'] ?? ($edit_mode ? $doctor_data['cat_type_id'] : ''); ?>
                                <select class="form-control-modern" name="specialization" id="specializationSelect" style="width:100%;" required>
                                    <option value="">-- Search Specialization --</option>
                                    <?php foreach ($categories_data as $category_id => $category): ?>
                                        <optgroup label="<?php echo htmlspecialchars($category['cat_name']); ?>">
                                            <?php foreach ($category['types'] as $type): ?>
                                                <option value="<?php echo $type['dr_cat_type_id']; ?>" 
                                                        <?php echo ($sel_cat == $type['dr_cat_type_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($type['type']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                                
                                <div style="margin-top: 8px;">
                                    <input type="checkbox" value="1" id="if_not_available" name="if_not_available"> 
                                    <label class="form-label text-danger" for="if_not_available" style="display:inline; font-size:12px; text-transform:none;">Specialization not listed?</label>
                                </div>
                                <input type="text" class="form-control-modern" name="specialization_txt"
                                       placeholder="Enter Specialization" id="specialization_txt" style="display:none; margin-top:6px;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Short Detail (Qualifications) <span class="optional">(Optional)</span></label>
                                <input type="text" class="form-control-modern" id="shortDetail" name="short_detail" 
                                       placeholder="MBBS/FCPS/LONDON/CHINA" value="<?php echo fv('short_detail'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Other Information <span class="optional">(Optional)</span></label>
                                <input type="text" class="form-control-modern" id="other" name="other"
                                       placeholder="Incharge / DHQ / Department Head etc" value="<?php echo fv('other'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group field-mahre">
                                <label class="form-label">
                                    <i class="fas fa-star me-2" style="color:#f59e0b;"></i> ماہرِ امراض (Specialist in Disease)
                                    <span class="optional">(Optional)</span>
                                </label>
                                <input type="text" class="form-control-modern" id="mahreAmraz" name="mahre_amraz" 
                                       placeholder="مثال: ماہرِ قلب، ماہرِ اعصاب، ماہرِ اطفال"
                                       value="<?php echo fv('mahre_amraz'); ?>">
                                <small class="text-muted">وہ بیماری یا شعبہ جس میں ڈاکٹر مہارت رکھتا ہے</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group field-notes">
                                <label class="form-label">
                                    <i class="fas fa-sticky-note me-2" style="color:#22c55e;"></i> خصوصی نوٹس / آفرز (Notes / Special Offers)
                                    <span class="optional">(Optional)</span>
                                </label>
                                <input type="text" class="form-control-modern" id="notes" name="notes" 
                                       placeholder="مثال: مفت الٹراساؤنڈ، مفت ایکس رے، مفت مشورہ"
                                       value="<?php echo fv('notes'); ?>">
                                <small class="text-muted">کوئی خاص پیشکش، نوٹس یا ہدایات (جیسے: مفت الٹراساؤنڈ، مفت ایکس رے، ڈسکاؤنٹ)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Clinical Info Detail <span class="optional">(Optional)</span></label>
                                <textarea class="form-control-modern" id="staticClinicalInfo" name="static_clinical_info" rows="3"
                                          placeholder="Add clinical notes or special instructions..."><?php echo fv('static_clinical_info'); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== CLINICAL INFO SECTION ===== -->
            <?php if ($edit_mode && !empty($dih_rows)): ?>
            <?php
            $working_days  = ["Monday to Friday", "Monday to Saturday", "Monday to Sunday",
                              "Tuesday to Sunday", "Friday to Sunday", "Saturday & Sunday", "Sunday Only", "24/7"];
            $off_days_list = ["None", "Monday", "Tuesday", "Wednesday", "Thursday",
                              "Friday", "Saturday", "Sunday", "Saturday & Sunday", "Friday & Saturday"];
            ?>
            <div class="modern-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-clock me-2"></i> Clinical Information</h5>
                    <span class="badge bg-info text-white"><?php echo count($dih_rows); ?> Hospital(s)</span>
                </div>
                <div class="card-body-custom">
                    <?php foreach ($dih_rows as $rs):
                        $dih_id = (int)$rs['doctor_in_hosp_id'];
                        $hospital_name_display = !empty($rs['hospital_name']) ? $rs['hospital_name'] : 'Personal Clinic';
                        $has_ci  = isset($clinical_info[$dih_id]);
                        $ci_data = $has_ci ? $clinical_info[$dih_id] : [];
                    ?>
                        <div class="hospital-clinical-card">
                            <div class="hospital-name d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hospital me-2"></i><?php echo htmlspecialchars($hospital_name_display); ?></span>
                                <?php if ($has_ci): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i> Info Saved
                                        <?php if (!empty($ci_data['shift'])): ?>(<?php echo htmlspecialchars($ci_data['shift']); ?>)<?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not added yet</span>
                                <?php endif; ?>
                            </div>

                            <div class="clinical-grid">
                                <div class="full-width">
                                    <div class="shift-label">
                                        <i class="fas fa-sun text-warning"></i> Morning Shift
                                        <span class="hint">(Leave empty if not available)</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Opening Time</label>
                                    <input type="text" class="form-control-modern timepicker" autocomplete="off" placeholder="Select time"
                                           name="clinical_data[<?php echo $dih_id; ?>][morning_opening_time]"
                                           value="<?php echo htmlspecialchars(ci_time($ci_data['morning_opening_time'] ?? '')); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Closing Time</label>
                                    <input type="text" class="form-control-modern timepicker" autocomplete="off" placeholder="Select time"
                                           name="clinical_data[<?php echo $dih_id; ?>][morning_closing_time]"
                                           value="<?php echo htmlspecialchars(ci_time($ci_data['morning_closing_time'] ?? '')); ?>">
                                </div>

                                <div class="full-width">
                                    <hr class="clinical-divider">
                                    <div class="shift-label">
                                        <i class="fas fa-moon text-primary"></i> Evening Shift
                                        <span class="hint">(Leave empty if not available)</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Opening Time</label>
                                    <input type="text" class="form-control-modern timepicker" autocomplete="off" placeholder="Select time"
                                           name="clinical_data[<?php echo $dih_id; ?>][evening_opening_time]"
                                           value="<?php echo htmlspecialchars(ci_time($ci_data['evening_opening_time'] ?? '')); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Closing Time</label>
                                    <input type="text" class="form-control-modern timepicker" autocomplete="off" placeholder="Select time"
                                           name="clinical_data[<?php echo $dih_id; ?>][evening_closing_time]"
                                           value="<?php echo htmlspecialchars(ci_time($ci_data['evening_closing_time'] ?? '')); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Season</label>
                                    <select class="form-control-modern" name="clinical_data[<?php echo $dih_id; ?>][season]">
                                        <option value="">Select Season</option>
                                        <?php ci_options(["Summer", "Winter"], $ci_data['season'] ?? ''); ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Contact</label>
                                    <input type="text" class="form-control-modern"
                                           name="clinical_data[<?php echo $dih_id; ?>][contact]"
                                           value="<?php echo htmlspecialchars($ci_data['contact'] ?? ''); ?>"
                                           placeholder="0300-1234567">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Working Days</label>
                                    <select class="form-control-modern" name="clinical_data[<?php echo $dih_id; ?>][days]">
                                        <option value="">Select Working Days</option>
                                        <?php ci_options($working_days, $ci_data['days'] ?? ''); ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Off Days</label>
                                    <select class="form-control-modern" name="clinical_data[<?php echo $dih_id; ?>][off_days]">
                                        <option value="">Select Off Days</option>
                                        <?php ci_options($off_days_list, $ci_data['off_days'] ?? ''); ?>
                                    </select>
                                </div>

                                <div class="form-group full-width">
                                    <label class="form-label">Additional Detail</label>
                                    <textarea class="form-control-modern"
                                              name="clinical_data[<?php echo $dih_id; ?>][detail]"
                                              rows="2"
                                              placeholder="Additional clinical notes..."><?php echo htmlspecialchars($ci_data['detail'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ===== ADDITIONAL SETTINGS ===== -->
            <div class="modern-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-cog me-2"></i> Additional Settings</h5>
                </div>
                <div class="card-body-custom">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Profile Picture <span class="optional">(Optional)</span></label>
                                <input type="file" class="form-control-modern" name="doctor_pic" accept="image/*">
                                <small class="text-muted mt-2 d-block">Recommended: 500x500px (JPG, PNG)</small>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <?php if ($edit_mode && !empty($doctor_data['doctor_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo htmlspecialchars($doctor_data['doctor_pic']); ?>" 
                                     alt="Current Picture" class="img-preview">
                                <p class="text-muted mt-1">Current Photo</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr style="border-top: 1px solid #eee; margin: 25px 0;">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Status <span class="required">*</span></label>
                                <div>
                                    <label class="custom-switch">
                                        <input type="checkbox" id="estatus" name="status" value="1"
                                            <?php echo (!$edit_mode || (isset($doctor_data['estatus']) && $doctor_data['estatus'] == 1)) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="ms-3 fw-bold" id="statusLabel">Active</span>
                                    <small class="text-muted d-block mt-1">Enable to make this doctor visible in the public directory.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="refDiv" style="display: <?php echo ($edit_mode && isset($doctor_data['estatus']) && $doctor_data['estatus'] == 0) ? 'block' : 'none'; ?>;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Inactive Status Detail <span class="required">*</span></label>
                                <textarea class="form-control-modern" id="ref" name="ref" rows="4" 
                                          placeholder="Reason for inactive status..."
                                          <?php echo ($edit_mode && isset($doctor_data['estatus']) && $doctor_data['estatus'] == 0) ? 'required' : ''; ?>><?php if(isset($doctor_data['ref'])){ echo htmlspecialchars($doctor_data['ref']); } ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== FORM ACTIONS ===== -->
            <div class="text-center mt-4 mb-5 animate-up delay-2">
                <button type="submit" class="btn-action btn-save" id="saveBtn">
                    <i class="fas <?php echo $edit_mode ? 'fa-save' : 'fa-plus-circle'; ?> me-2"></i>
                    <?php echo $edit_mode ? 'Update Doctor' : 'Add / Assign Doctor'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>hospital/doctors.php" class="btn-action btn-cancel ms-2">
                    <i class="fas fa-times me-2"></i> Cancel
                </a>
            </div>

        </form>
    </div>
</div>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>
<script>
// ============================================
// DUPLICATE CHECK (Username / Email / CNIC) - sirf ADD mode
// ============================================
var dupFields = {};
function refreshSaveBtn() {
    var anyBad = Object.keys(dupFields).some(function (k) { return dupFields[k].bad; });
    $('#saveBtn').prop('disabled', anyBad);
}
function resetDupChecks() {
    Object.keys(dupFields).forEach(function (k) {
        dupFields[k].bad = false;
        dupFields[k].req++;
        dupFields[k].$msg.text('').removeClass('error');
    });
    refreshSaveBtn();
}

$(document).ready(function() {
    // ----- Time pickers (clinical info) -----
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.timepicker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'h:i K',   // 09:30 AM
            time_24hr: false,
            minuteIncrement: 5,
            allowInput: true
        });
    }

    // ----- Select2 -----
    $('#specializationSelect').select2({
        theme: 'bootstrap-5', placeholder: 'Search Specialization...',
        allowClear: true, width: '100%', dropdownCssClass: 'specialization-dropdown'
    });
    $('#existingDoctorSelect').select2({
        theme: 'bootstrap-5', placeholder: 'Search existing doctor...',
        allowClear: true, width: '100%'
    });

    // ----- Duplicate check setup -----
    var checkUrl = '<?php echo BASE_URL; ?>hospital/ajax/check_unique.php';
    dupFields = {
        cnic:     { $el: $('#cnic'),        $msg: $('#cnic_msg'),        label: 'CNIC',     bad: false, timer: null, req: 0 },
        email:    { $el: $('#doctorEmail'), $msg: $('#doctorEmail_msg'), label: 'Email',    bad: false, timer: null, req: 0 },
        username: { $el: $('#username'),    $msg: $('#username_msg'),    label: 'Username', bad: false, timer: null, req: 0 }
    };

    function check(key) {
        var f = dupFields[key];
        var value = $.trim(f.$el.val());
        if (value === '' || f.$el.prop('readonly') || f.$el.prop('disabled')) {
            f.bad = false; f.$msg.text('').removeClass('error'); refreshSaveBtn(); return;
        }
        var myReq = ++f.req;
        $.post(checkUrl, { field: key, value: value }, function (res) {
            if (myReq !== f.req) return;
            if (res.exists) {
                f.bad = true;
                f.$msg.text(f.label + ' pehle se maujood hai.').addClass('error');
            } else {
                f.bad = false; f.$msg.text('').removeClass('error');
            }
            refreshSaveBtn();
        }, 'json').fail(function () {
            f.bad = false;
            f.$msg.text('Check nahi ho saka, dobara koshish karein.').addClass('error');
            refreshSaveBtn();
        });
    }

    $.each(dupFields, function (key, f) {
        f.$el.on('keyup input', function () {
            clearTimeout(f.timer);
            f.timer = setTimeout(function () { check(key); }, 400);
        });
    });

    // CNIC auto-dash: 12345-1234567-1
    $('#cnic').on('input', function () {
        if (this.readOnly) return;
        var v = this.value.replace(/\D/g, '').substring(0, 13);
        if (v.length > 12)     v = v.replace(/^(\d{5})(\d{7})(\d{1}).*/, '$1-$2-$3');
        else if (v.length > 5) v = v.replace(/^(\d{5})(\d{0,7}).*/, '$1-$2');
        this.value = v;
    });

    // ----- Existing doctor select - auto fill -----
    $('#existingDoctorSelect').on('change', function() {
        var doctorId = $(this).val();
        if (doctorId) {
            $('#personalDetailsCard input, #personalDetailsCard select, #personalDetailsCard textarea').prop('disabled', true);
            resetDupChecks();   // existing doctor assign ho raha hai, duplicate check ki zarurat nahi

            $.ajax({
                url: '<?php echo BASE_URL; ?>hospital/ajax/get-doctor.php',
                type: 'GET',
                data: { id: doctorId },
                dataType: 'json',
                success: function(data) {
                    if (data.status) {
                        var doc = data.data;
                        $('#username').val(doc.username || '');
                        $('#doctorName').val(doc.doctor_name || '');
                        $('#doctorEmail').val(doc.doctor_email || '');
                        $('#doctorPhone').val(doc.doctor_phone || '');
                        $('#cnic').val(doc.cnic || '');
                        $('#gender').val(doc.gender || '');
                        $('#experienceYears').val(doc.experience_years || '');
                        $('#specializationSelect').val(doc.cat_type_id || '').trigger('change');
                        $('#shortDetail').val(doc.short_detail || '');
                        $('#other').val(doc.other || '');
                        $('#mahreAmraz').val(doc.mahre_amraz || '');
                        $('#notes').val(doc.notes || '');
                        $('#staticClinicalInfo').val(doc.static_clinical_info || '');
                    }
                },
                error: function() { alert('Error fetching doctor data'); }
            });
        } else {
            $('#personalDetailsCard input, #personalDetailsCard select, #personalDetailsCard textarea').prop('disabled', false);
            $('#username, #doctorName, #doctorEmail, #doctorPhone, #cnic, #experienceYears, #shortDetail, #other, #mahreAmraz, #notes, #staticClinicalInfo').val('');
            $('#gender, #specializationSelect').val('').trigger('change');
            resetDupChecks();
        }
    });
});

// ============================================
// STATUS SWITCH + SPECIALIZATION NOT LISTED
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const estatus = document.getElementById('estatus');
    const statusLabel = document.getElementById('statusLabel');
    const refDiv = document.getElementById('refDiv');
    const ref = document.getElementById('ref');

    function toggleStatus() {
        if (estatus.checked) {
            statusLabel.textContent = 'Active';
            statusLabel.style.color = '#22c55e';
            refDiv.style.display = 'none';
            ref.required = false;
        } else {
            statusLabel.textContent = 'Inactive';
            statusLabel.style.color = '#ef4444';
            refDiv.style.display = 'block';
            ref.required = true;
        }
    }
    toggleStatus();
    estatus.addEventListener('change', toggleStatus);

    const checkbox = document.getElementById('if_not_available');
    const selectWrapper = document.getElementById('specializationSelect');
    const textWrapper = document.getElementById('specialization_txt');

    function toggleFields() {
        if (checkbox.checked) {
            selectWrapper.style.display = 'none';
            textWrapper.style.display = 'block';
            selectWrapper.value = '';
            textWrapper.setAttribute('required', 'required');
            selectWrapper.removeAttribute('required');
            $('#specializationSelect').select2('destroy');
            $('#specializationSelect').hide();
        } else {
            selectWrapper.style.display = 'block';
            textWrapper.style.display = 'none';
            textWrapper.value = '';
            selectWrapper.setAttribute('required', 'required');
            textWrapper.removeAttribute('required');
            $('#specializationSelect').show();
            $('#specializationSelect').select2({
                theme: 'bootstrap-5', placeholder: 'Search Specialization...',
                allowClear: true, width: '100%', dropdownCssClass: 'specialization-dropdown'
            });
        }
    }
    toggleFields();
    checkbox.addEventListener('change', toggleFields);
});
</script>