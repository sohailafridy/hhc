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

// Get hospital data
$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];
$city_id = $hospital_data['city_id'];
$hospital_name = $hospital_data['hospital_name'];

// ============================================
// EDIT MODE - Get doctor data
// ============================================
$edit_mode = false;
$doctor_data = null;
$is_edit = isset($_GET['id']) && is_numeric($_GET['id']);

if ($is_edit) {
    $doctor_id = (int)$_GET['id'];
    
    $check_query = "SELECT d.*, dct.type as specialization_name, e.status as estatus, e.reference as ref, u.status as user_status
                    FROM doctors d
                    LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                    LEFT JOIN entities e ON e.entity_id = d.entity_id
                    LEFT JOIN users u ON u.user_id = d.user_id
                    WHERE d.doctor_id = $doctor_id AND d.hospital_id = $hospital_id AND d.approve = 1";
    $check_result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $edit_mode = true;
        $doctor_data = mysqli_fetch_assoc($check_result);
    } else {
        $_SESSION['error_msg'] = "Doctor not found or you don't have permission to edit.";
        header("Location: " . BASE_URL . "hospital/doctors.php");
        exit();
    }
}

// ============================================
// FETCH EXISTING DOCTORS FOR THIS HOSPITAL'S CITY
// ============================================
$existing_doctors = [];
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

// ============================================
// FETCH DOCTOR-IN-HOSPITAL (for clinical info)
// ============================================
$doctor_in_hosp_ids = [];
$clinical_info = [];

if ($edit_mode) {
    // Get doctor_in_hospital entries
    $dih_query = "SELECT dih.doctor_in_hosp_id, h.hospital_name, h.hospital_id
                  FROM doctor_in_hospital dih
                  LEFT JOIN hospitals h ON dih.hospital_id = h.hospital_id
                  WHERE dih.doctor_id = $doctor_id";
    $dih_result = mysqli_query($con, $dih_query);
    
    while ($row = mysqli_fetch_assoc($dih_result)) {
        $doctor_in_hosp_ids[] = $row['doctor_in_hosp_id'];
    }
    
    // Get clinical info if exists
    if (!empty($doctor_in_hosp_ids)) {
        $ids_string = implode(',', $doctor_in_hosp_ids);
        $ci_query = "SELECT * FROM clinical_info WHERE doctor_in_hosp_id IN ($ids_string)";
        $ci_result = mysqli_query($con, $ci_query);
        while ($row = mysqli_fetch_assoc($ci_result)) {
            $clinical_info[$row['doctor_in_hosp_id']] = $row;
        }
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
            $categories_data[$row['dr_cat_id']] = [
                'cat_name' => $row['cat_name'],
                'types' => []
            ];
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
    
    // ============================================
    // CHECK IF EXISTING DOCTOR SELECTED
    // ============================================
    $selected_existing_doctor = isset($_POST['existing_doctor_id']) && !empty($_POST['existing_doctor_id']) 
        ? (int)$_POST['existing_doctor_id'] 
        : 0;
    
    if ($selected_existing_doctor > 0) {
        // ============================================
        // EXISTING DOCTOR - Just add to doctor_in_hospital
        // ============================================
        $doctor_id = $selected_existing_doctor;
        
        // Check if already assigned to this hospital
        $check_dih = "SELECT * FROM doctor_in_hospital WHERE doctor_id = $doctor_id AND hospital_id = $hospital_id";
        $check_dih_result = mysqli_query($con, $check_dih);
        
        if (mysqli_num_rows($check_dih_result) > 0) {
            $error_msg = "This doctor is already assigned to your hospital!";
        } else {
            // Insert into doctor_in_hospital
            $insert_dih = "INSERT INTO doctor_in_hospital (doctor_id, hospital_id) VALUES ($doctor_id, $hospital_id)";
            if (mysqli_query($con, $insert_dih)) {
                $_SESSION['success_msg'] = "Doctor assigned to hospital successfully!";
                header("Location: " . BASE_URL . "hospital/doctors.php");
                exit();
            } else {
                $error_msg = "Error: " . mysqli_error($con);
            }
        }
        
    } else {
        // ============================================
        // NEW DOCTOR - Full insert
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
        
        $experience_years = (int)$_POST['experience_years'];
        $doctor_phone = mysqli_real_escape_string($con, $_POST['doctor_phone']);
        $doctor_email = mysqli_real_escape_string($con, $_POST['doctor_email']);
        $gender = mysqli_real_escape_string($con, $_POST['gender']);
        $short_detail = mysqli_real_escape_string($con, $_POST['short_detail']);
        $other = mysqli_real_escape_string($con, $_POST['other'] ?? '');
        $static_clinical_info = mysqli_real_escape_string($con, $_POST['static_clinical_info'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;
        
        $mahre_amraz = mysqli_real_escape_string($con, $_POST['mahre_amraz'] ?? '');
        $notes = mysqli_real_escape_string($con, $_POST['notes'] ?? '');
        
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $pass = mysqli_real_escape_string($con, $_POST['password']);
        $password = !empty($pass) ? base64_encode($pass) : base64_encode('123456');
        
        $doctor_pic = '';
        if (isset($_FILES['doctor_pic']) && $_FILES['doctor_pic']['error'] == 0) {
            $target_dir = BASE_PATH . "/admin/inc/uploads/doctors/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($_FILES["doctor_pic"]["name"]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["doctor_pic"]["tmp_name"], $target_file)) {
                    $doctor_pic = $file_name;
                }
            }
        }
        
        // Check if email already exists
        $email_check = "SELECT user_id FROM users WHERE email = '$doctor_email'";
        $email_check_result = mysqli_query($con, $email_check);
        
        if (mysqli_num_rows($email_check_result) > 0) {
            $error_msg = "Email already exists. Please use a different email.";
        } else {
            
            $entity_query = "INSERT INTO entities (entity_type, status, created_at) VALUES ('doctor', 1, NOW())";
            mysqli_query($con, $entity_query);
            $entity_id_new = mysqli_insert_id($con);
            
            $user_query = "INSERT INTO users (username, email, password, user_type_id, status, created_at) 
                           VALUES ('$username', '$doctor_email', '$password', 2, 1, NOW())";
            mysqli_query($con, $user_query);
            $user_id_new = mysqli_insert_id($con);
            
            $insert_query = "INSERT INTO doctors (
                                entity_id, user_id, city_id, hospital_id, doctor_name, 
                                cat_type_id, experience_years, doctor_phone, doctor_email, 
                                doctor_type, gender, short_detail, other, static_clinical_info,
                                mahre_amraz, notes, doctor_pic, approve, status, created_at
                            ) VALUES (
                                '$entity_id_new', '$user_id_new', '$city_id', '$hospital_id', '$doctor_name',
                                '$cat_type_id', '$experience_years', '$doctor_phone', '$doctor_email',
                                '1', '$gender', '$short_detail', '$other', '$static_clinical_info',
                                '$mahre_amraz', '$notes', '$doctor_pic', 1, 1, NOW()
                            )";
            
            if (mysqli_query($con, $insert_query)) {
                $last_insert_id = mysqli_insert_id($con);
                
                $insert_hospital_query = "INSERT INTO doctor_in_hospital (doctor_id, hospital_id) VALUES ($last_insert_id, $hospital_id)";
                mysqli_query($con, $insert_hospital_query);
                
                $_SESSION['success_msg'] = "Doctor added successfully!";
                header("Location: " . BASE_URL . "hospital/doctors.php");
                exit();
            } else {
                $error_msg = "Error: " . mysqli_error($con);
            }
        }
    }
}
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/hospital/inc/nav.php'; ?>

<style>
:root {
    --primary-color: #4facfe;
    --secondary-color: #00f2fe;
    --accent-color: #764ba2;
    --text-color: #2c3e50;
    --bg-color: #f4f7f6;
    --card-bg: #ffffff;
    --input-bg: #fdfdfd;
    --border-color: #e0e0e0;
    --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.05);
    --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.content-wrapper {
    padding: 30px;
    min-height: 100vh;
}

.page-header {
    margin-bottom: 30px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    padding: 30px;
    border-radius: 20px;
    color: white;
    box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
    position: relative;
    overflow: hidden;
}

.page-header::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
}

.page-subtitle {
    opacity: 0.9;
    margin-top: 5px;
    font-size: 16px;
}

.btn-back {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
    padding: 8px 20px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: rgba(255,255,255,0.3);
    color: white;
}

.modern-card {
    background: var(--card-bg);
    border-radius: 20px;
    box-shadow: var(--shadow-soft);
    border: none;
    margin-bottom: 30px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.modern-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.card-header-custom {
    padding: 20px 25px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-header-custom h5 {
    margin: 0;
    font-weight: 600;
    color: #333;
    font-size: 18px;
    display: flex;
    align-items: center;
}

.card-header-custom h5 i {
    margin-right: 10px;
    color: var(--primary-color);
    font-size: 20px;
}

.card-body-custom {
    padding: 30px;
}

/* ===== EXISTING DOCTOR SELECT ===== */
.existing-doctor-section {
    background: #f0f7ff;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #dbeafe;
    margin-bottom: 25px;
}

.existing-doctor-section .section-label {
    font-weight: 600;
    color: #1e40af;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.existing-doctor-section .form-control-modern {
    background: white;
}

/* ===== SELECT2 STYLES ===== */
.select2-container--bootstrap-5 .select2-selection {
    border: 2px solid var(--border-color) !important;
    border-radius: 10px !important;
    min-height: 42px !important;
    padding: 4px 12px !important;
    background: var(--input-bg) !important;
}

.select2-container--bootstrap-5.select2-container--focus .select2-selection {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 4px rgba(79, 172, 254, 0.1) !important;
}

.select2-dropdown {
    border: 2px solid var(--border-color) !important;
    border-radius: 10px !important;
    box-shadow: var(--shadow-soft) !important;
}

.select2-search__field {
    border: 2px solid var(--border-color) !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    font-size: 13px !important;
}

.select2-results__option {
    padding: 8px 14px !important;
    font-size: 13px !important;
}

.select2-results__option--highlighted {
    background: var(--primary-color) !important;
    color: white !important;
}

/* ===== FORM STYLES ===== */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: #555;
    margin-bottom: 6px;
    display: block;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-label .required {
    color: #e74c3c;
    font-weight: 700;
    font-size: 16px;
    margin-left: 2px;
}

.form-label .optional {
    color: #94a3b8;
    font-weight: 400;
    font-size: 11px;
    margin-left: 4px;
    text-transform: none;
}

.form-control-modern {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    background-color: var(--input-bg);
    transition: all 0.3s;
    font-size: 14px;
    color: #333;
}

.form-control-modern:focus {
    border-color: var(--primary-color);
    background-color: #fff;
    outline: none;
    box-shadow: 0 0 0 4px rgba(79, 172, 254, 0.1);
}

select.form-control-modern {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 16px;
}

textarea.form-control-modern {
    resize: vertical;
    min-height: 80px;
}

.img-preview {
    width: 100px;
    height: 100px;
    border-radius: 10px;
    object-fit: cover;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border: 3px solid white;
}

.field-mahre {
    border-left: 3px solid #f59e0b;
    padding-left: 16px;
}

.field-notes {
    border-left: 3px solid #22c55e;
    padding-left: 16px;
}

.field-mahre .form-label i {
    color: #f59e0b;
}

.field-notes .form-label i {
    color: #22c55e;
}

.custom-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.custom-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

input:checked + .slider {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

input:checked + .slider:before {
    transform: translateX(24px);
}

.btn-action {
    padding: 10px 28px;
    border-radius: 50px;
    font-weight: 600;
    letter-spacing: 0.5px;
    border: none;
    transition: all 0.3s;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-transform: uppercase;
    font-size: 13px;
}

.btn-save {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(56, 239, 125, 0.3);
    color: white;
}

.btn-cancel {
    background: white;
    color: #e74c3c;
    border: 2px solid #e74c3c;
}

.btn-cancel:hover {
    background: #e74c3c;
    color: white;
}

.alert-modern {
    border-radius: 10px;
    border: none;
    padding: 15px 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
}

.alert-success-modern {
    background: rgba(56, 239, 125, 0.15);
    color: #065f46;
    border-left: 4px solid #38ef7d;
}

.alert-danger-modern {
    background: rgba(231, 76, 60, 0.15);
    color: #922b21;
    border-left: 4px solid #e74c3c;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-up {
    animation: fadeInUp 0.5s ease-out forwards;
}

@media (max-width: 768px) {
    .content-wrapper { padding: 16px; }
    .page-header { padding: 20px; }
    .page-title { font-size: 22px; }
    .card-body-custom { padding: 20px; }
}
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
            
            <input type="hidden" name="entity_id" value="<?php if(isset($doctor_data['entity_id'])){ echo $doctor_data['entity_id']; } ?>">
            
            <!-- ============================================ -->
            <!-- ===== EXISTING DOCTOR SELECTION ===== -->
            <!-- ============================================ -->
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
                        <div class="text-center mt-2">
                            <span class="text-muted">OR</span>
                        </div>
                        <div class="text-center mt-2">
                            <small>Fill the form below to add a <strong>new doctor</strong> to the system.</small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- ============================================ -->
            <!-- ===== HOSPITAL INFO ===== -->
            <!-- ============================================ -->
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
                                            $city_query = "SELECT city_name FROM cities WHERE city_id = $city_id";
                                            $city_result = mysqli_query($con, $city_query);
                                            $city_row = mysqli_fetch_assoc($city_result);
                                            echo htmlspecialchars($city_row['city_name'] ?? 'N/A');
                                       ?>" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- ===== PERSONAL DETAILS ===== -->
            <!-- ============================================ -->
            <div class="modern-card" id="personalDetailsCard">
                <div class="card-header-custom">
                    <h5><i class="fas fa-user me-2"></i> Personal Details</h5>
                </div>
                <div class="card-body-custom">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    Username
                                    <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control-modern" id="username" name="username" 
                                       placeholder="Enter username" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['username'] ?? '') : ''; ?>"
                                       <?php echo $edit_mode ? 'readonly' : ''; ?>>
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
                                <?php if (!$edit_mode): ?>
                                    <small class="text-muted">Default: 123456</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    Doctor Name
                                    <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control-modern" id="doctorName" name="doctor_name" required
                                       placeholder="Dr. John Doe"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['doctor_name']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    Email
                                    <span class="required">*</span>
                                </label>
                                <input type="email" class="form-control-modern" id="doctorEmail" name="doctor_email" required
                                       placeholder="doctor@example.com"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['doctor_email']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    Phone
                                    <span class="required">*</span>
                                </label>
                                <input type="tel" class="form-control-modern" id="doctorPhone" name="doctor_phone" required
                                       placeholder="+92 300 1234567"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['doctor_phone']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    Gender
                                    <span class="required">*</span>
                                </label>
                                <select class="form-control-modern" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($edit_mode && $doctor_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($edit_mode && $doctor_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($edit_mode && $doctor_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    Experience (Years)
                                    <span class="optional">(Optional)</span>
                                </label>
                                <input type="number" class="form-control-modern" id="experienceYears" name="experience_years" min="0" max="60"
                                       placeholder="e.g. 5"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['experience_years']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    Specialization
                                    <span class="required">*</span>
                                </label>
                                <select class="form-control-modern" name="specialization" id="specializationSelect" style="width:100%;" required>
                                    <option value="">-- Search Specialization --</option>
                                    <?php foreach ($categories_data as $category_id => $category): ?>
                                        <optgroup label="<?php echo htmlspecialchars($category['cat_name']); ?>">
                                            <?php foreach ($category['types'] as $type): ?>
                                                <option value="<?php echo $type['dr_cat_type_id']; ?>" 
                                                        <?php echo ($edit_mode && $doctor_data['cat_type_id'] == $type['dr_cat_type_id']) ? 'selected' : ''; ?>>
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
                                <label class="form-label">
                                    Short Detail (Qualifications)
                                    <span class="optional">(Optional)</span>
                                </label>
                                <input type="text" class="form-control-modern" id="shortDetail" name="short_detail" 
                                       placeholder="MBBS/FCPS/LONDON/CHINA"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['short_detail']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">
                                    Other Information
                                    <span class="optional">(Optional)</span>
                                </label>
                                <input type="text" class="form-control-modern" id="other" name="other"
                                       placeholder="Incharge / DHQ / Department Head etc"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['other']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- ===== NEW FIELDS: MAHRE AMRAZ & NOTES ===== -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group field-mahre">
                                <label class="form-label">
                                    <i class="fas fa-star me-2" style="color:#f59e0b;"></i> ماہرِ امراض (Specialist in Disease)
                                    <span class="optional">(Optional)</span>
                                </label>
                                <input type="text" class="form-control-modern" id="mahreAmraz" name="mahre_amraz" 
                                       placeholder="مثال: ماہرِ قلب، ماہرِ اعصاب، ماہرِ اطفال"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['mahre_amraz']) : ''; ?>">
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
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['notes']) : ''; ?>">
                                <small class="text-muted">کوئی خاص پیشکش، نوٹس یا ہدایات (جیسے: مفت الٹراساؤنڈ، مفت ایکس رے، ڈسکاؤنٹ)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">
                                    Clinical Info Detail
                                    <span class="optional">(Optional)</span>
                                </label>
                                <textarea class="form-control-modern" id="staticClinicalInfo" name="static_clinical_info" rows="3"
                                          placeholder="Add clinical notes or special instructions..."><?php echo $edit_mode ? htmlspecialchars($doctor_data['static_clinical_info']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- ===== CLINICAL INFO SECTION ===== -->
            <!-- ============================================ -->
            <?php if ($edit_mode && !empty($doctor_in_hosp_ids)): ?>
            <div class="modern-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-clock me-2"></i> Clinical Information</h5>
                    <span class="badge bg-info text-white"><?php echo count($doctor_in_hosp_ids); ?> Hospital(s)</span>
                </div>
                <div class="card-body-custom">
                    <?php 
                    $dih_query = "SELECT dih.doctor_in_hosp_id, h.hospital_name, h.hospital_id
                                  FROM doctor_in_hospital dih
                                  LEFT JOIN hospitals h ON dih.hospital_id = h.hospital_id
                                  WHERE dih.doctor_id = $doctor_id";
                    $dih_result = mysqli_query($con, $dih_query);
                    
                    while ($rs = mysqli_fetch_assoc($dih_result)): 
                        $dih_id = $rs['doctor_in_hosp_id'];
                        $hospital_name_display = !empty($rs['hospital_name']) ? $rs['hospital_name'] : 'Personal Clinic';
                        $ci_data = isset($clinical_info[$dih_id]) ? $clinical_info[$dih_id] : [];
                    ?>
                        <div class="hospital-clinical-card">
                            <div class="hospital-name">
                                <i class="fas fa-hospital me-2"></i>
                                <?php echo htmlspecialchars($hospital_name_display); ?>
                            </div>
                            
                            <div class="clinical-grid">
                                <!-- Morning Shift -->
                                <div class="full-width">
                                    <div class="shift-label">
                                        <i class="fas fa-sun text-warning"></i> Morning Shift
                                        <span class="hint">(Leave empty if not available)</span>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Opening Time</label>
                                    <input type="time" class="form-control-modern timepicker" 
                                           name="clinical_data[<?php echo $dih_id; ?>][morning_opening_time]"
                                           value="<?php echo isset($ci_data['morning_opening_time']) ? htmlspecialchars($ci_data['morning_opening_time']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Closing Time</label>
                                    <input type="time" class="form-control-modern timepicker" 
                                           name="clinical_data[<?php echo $dih_id; ?>][morning_closing_time]"
                                           value="<?php echo isset($ci_data['morning_closing_time']) ? htmlspecialchars($ci_data['morning_closing_time']) : ''; ?>">
                                </div>
                                
                                <!-- Evening Shift -->
                                <div class="full-width">
                                    <hr class="clinical-divider">
                                    <div class="shift-label">
                                        <i class="fas fa-moon text-primary"></i> Evening Shift
                                        <span class="hint">(Leave empty if not available)</span>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Opening Time</label>
                                    <input type="time" class="form-control-modern timepicker" 
                                           name="clinical_data[<?php echo $dih_id; ?>][evening_opening_time]"
                                           value="<?php echo isset($ci_data['evening_opening_time']) ? htmlspecialchars($ci_data['evening_opening_time']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Closing Time</label>
                                    <input type="time" class="form-control-modern timepicker" 
                                           name="clinical_data[<?php echo $dih_id; ?>][evening_closing_time]"
                                           value="<?php echo isset($ci_data['evening_closing_time']) ? htmlspecialchars($ci_data['evening_closing_time']) : ''; ?>">
                                </div>
                                
                                <!-- Season -->
                                <div class="form-group">
                                    <label class="form-label">Season</label>
                                    <select class="form-control-modern" name="clinical_data[<?php echo $dih_id; ?>][season]">
                                        <option value="">Select Season</option>
                                        <option value="Summer" <?php echo (isset($ci_data['season']) && $ci_data['season'] == 'Summer') ? 'selected' : ''; ?>>Summer</option>
                                        <option value="Winter" <?php echo (isset($ci_data['season']) && $ci_data['season'] == 'Winter') ? 'selected' : ''; ?>>Winter</option>
                                    </select>
                                </div>
                                
                                <!-- Contact -->
                                <div class="form-group">
                                    <label class="form-label">Contact</label>
                                    <input type="text" class="form-control-modern" 
                                           name="clinical_data[<?php echo $dih_id; ?>][contact]"
                                           value="<?php echo isset($ci_data['contact']) ? htmlspecialchars($ci_data['contact']) : ''; ?>"
                                           placeholder="0300-1234567">
                                </div>
                                
                                <!-- Working Days -->
                                <div class="form-group">
                                    <label class="form-label">Working Days</label>
                                    <select class="form-control-modern" name="clinical_data[<?php echo $dih_id; ?>][days]">
                                        <option value="">Select Working Days</option>
                                        <?php 
                                        $working_days = [
                                            "Monday to Friday", "Monday to Saturday", "Monday to Sunday",
                                            "Tuesday to Sunday", "Friday to Sunday", "Saturday & Sunday",
                                            "Sunday Only", "24/7"
                                        ];
                                        $selected_days = isset($ci_data['days']) ? $ci_data['days'] : '';
                                        foreach ($working_days as $day): ?>
                                            <option value="<?php echo $day; ?>" <?php echo ($selected_days == $day) ? 'selected' : ''; ?>>
                                                <?php echo $day; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Off Days -->
                                <div class="form-group">
                                    <label class="form-label">Off Days</label>
                                    <select class="form-control-modern" name="clinical_data[<?php echo $dih_id; ?>][off_days]">
                                        <option value="">Select Off Days</option>
                                        <?php 
                                        $off_days = [
                                            "None", "Monday", "Tuesday", "Wednesday", "Thursday",
                                            "Friday", "Saturday", "Sunday", "Saturday & Sunday", "Friday & Saturday"
                                        ];
                                        $selected_off = isset($ci_data['off_days']) ? $ci_data['off_days'] : '';
                                        foreach ($off_days as $day): ?>
                                            <option value="<?php echo $day; ?>" <?php echo ($selected_off == $day) ? 'selected' : ''; ?>>
                                                <?php echo $day; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Detail -->
                                <div class="form-group full-width">
                                    <label class="form-label">Additional Detail</label>
                                    <textarea class="form-control-modern" 
                                              name="clinical_data[<?php echo $dih_id; ?>][detail]" 
                                              rows="2" 
                                              placeholder="Additional clinical notes..."><?php echo isset($ci_data['detail']) ? htmlspecialchars($ci_data['detail']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ============================================ -->
            <!-- ===== ADDITIONAL SETTINGS ===== -->
            <!-- ============================================ -->
            <div class="modern-card">
                <div class="card-header-custom">
                    <h5><i class="fas fa-cog me-2"></i> Additional Settings</h5>
                </div>
                <div class="card-body-custom">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    Profile Picture
                                    <span class="optional">(Optional)</span>
                                </label>
                                <input type="file" class="form-control-modern" name="doctor_pic" accept="image/*">
                                <small class="text-muted mt-2 d-block">Recommended: 500x500px (JPG, PNG)</small>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <?php if ($edit_mode && !empty($doctor_data['doctor_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor_data['doctor_pic']; ?>" 
                                     alt="Current Picture" class="img-preview">
                                <p class="text-muted mt-1">Current Photo</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <hr style="border-top: 1px solid #eee; margin: 25px 0;">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">
                                    Status
                                    <span class="required">*</span>
                                </label>
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
                                <label class="form-label">
                                    Inactive Status Detail
                                    <span class="required">*</span>
                                </label>
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
                <button type="submit" class="btn-action btn-save">
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
// SELECT2 FOR SPECIALIZATION
// ============================================
$(document).ready(function() {
    $('#specializationSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search Specialization...',
        allowClear: true,
        width: '100%',
        dropdownCssClass: 'specialization-dropdown'
    });
    
    // ============================================
    // SELECT2 FOR EXISTING DOCTOR
    // ============================================
    $('#existingDoctorSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search existing doctor...',
        allowClear: true,
        width: '100%'
    });
    
    // ============================================
    // EXISTING DOCTOR SELECT - AUTO FILL FORM
    // ============================================
    $('#existingDoctorSelect').on('change', function() {
        var doctorId = $(this).val();
        if (doctorId) {
            // Disable form fields to prevent editing
            $('#personalDetailsCard input, #personalDetailsCard select, #personalDetailsCard textarea').prop('disabled', true);
            
            // Fetch doctor data via AJAX
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
                error: function() {
                    alert('Error fetching doctor data');
                }
            });
        } else {
            // Enable form fields
            $('#personalDetailsCard input, #personalDetailsCard select, #personalDetailsCard textarea').prop('disabled', false);
            // Clear form
            $('#username, #doctorName, #doctorEmail, #doctorPhone, #experienceYears, #shortDetail, #other, #mahreAmraz, #notes, #staticClinicalInfo').val('');
            $('#gender, #specializationSelect').val('').trigger('change');
        }
    });
});

// ============================================
// STATUS SWITCH
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

    // ============================================
    // SPECIALIZATION - IF NOT AVAILABLE
    // ============================================
    const checkbox = document.getElementById('if_not_available');
    const selectWrapper = document.getElementById('specializationSelect');
    const textWrapper = document.getElementById('specialization_txt');

    function toggleFields() {
        if (checkbox.checked) {
            selectWrapper.style.display = 'none';
            textWrapper.style.display = 'block';
            document.getElementById('specializationSelect').value = '';
            textWrapper.setAttribute('required', 'required');
            selectWrapper.removeAttribute('required');
            $('#specializationSelect').select2('destroy');
            $('#specializationSelect').hide();
        } else {
            selectWrapper.style.display = 'block';
            textWrapper.style.display = 'none';
            document.getElementById('specialization_txt').value = '';
            selectWrapper.setAttribute('required', 'required');
            textWrapper.removeAttribute('required');
            $('#specializationSelect').show();
            $('#specializationSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search Specialization...',
                allowClear: true,
                width: '100%',
                dropdownCssClass: 'specialization-dropdown'
            });
        }
    }

    toggleFields();
    checkbox.addEventListener('change', toggleFields);
});
</script>