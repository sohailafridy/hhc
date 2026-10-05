<?php include '../config.php'; ?>
<?php
// ============================================================
// doctor_in_hospital sync
//   Clinic mode   => hospital_id = 0, if_clinic = 1
//   Hospital mode => hospital_id = X, if_clinic = 0
// Row pehle se ho to duplicate nahi banti (sirf inactive = 0 hoti hai)
// ============================================================
function sync_doctor_workplace($con, $doctor_id, $hospital_id, $is_clinic) {
    $doctor_id   = (int)$doctor_id;
    $hospital_id = $is_clinic ? 0 : (int)$hospital_id;
    $clinic_flag = $is_clinic ? 1 : 0;

    // Hospital mode me hospital select na ho to kuch na karo
    if (!$is_clinic && $hospital_id === 0) return;

    $check = mysqli_query($con, "SELECT doctor_in_hosp_id FROM doctor_in_hospital
                                 WHERE doctor_id = $doctor_id
                                   AND hospital_id = $hospital_id
                                   AND if_clinic = $clinic_flag
                                 LIMIT 1");

    if ($check && mysqli_num_rows($check) > 0) {
        // Row pehle se hai: sirf active kar do (agar pehle remove hui thi)
        $row = mysqli_fetch_assoc($check);
        mysqli_query($con, "UPDATE doctor_in_hospital SET inactive = 0
                            WHERE doctor_in_hosp_id = " . (int)$row['doctor_in_hosp_id']);
    } else {
        mysqli_query($con, "INSERT INTO doctor_in_hospital (doctor_id, hospital_id, if_clinic, inactive)
                            VALUES ($doctor_id, $hospital_id, $clinic_flag, 0)");
    }
}
?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
 <?php include BASE_PATH.'/admin/inc/nav.php';?> 

<?php

$user_id = 0;
if (isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
}

// Check if it's edit mode
$edit_mode = false;
$doctor_data = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $doctor_id = (int)$_GET['id'];
    $edit_query = "SELECT doctors.*, u.user_id as u_id, u.status as estatus, u.username
    FROM doctors 
    LEFT JOIN users u ON u.user_id = doctors.user_id
    WHERE doctor_id = $doctor_id";
    $edit_result = mysqli_query($con, $edit_query);
    
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $doctor_data = mysqli_fetch_assoc($edit_result);
    }
}

// Fetch cities for dropdown
$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// Fetch hospitals for dropdown with city_id for filtering
$hospitals_query = "SELECT hospitals.user_id, hospital_id, hospital_name, hospitals.city_id, cities.city_name
FROM 
hospitals
LEFT JOIN cities ON hospitals.city_id = cities.city_id
LEFT JOIN users u ON u.user_id = hospitals.user_id
 WHERE u.status = 1 AND hospitals.approve=1 ORDER BY hospital_name ASC";
$hospitals_result = mysqli_query($con, $hospitals_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doct_role = "No Role";
    $specialization = mysqli_real_escape_string($con, $_POST['specialization']);
    if (isset($_POST['if_not_available']) && $_POST['if_not_available'] !== '' && is_numeric($_POST['if_not_available'])) {
       
        $if_not_available = (int) mysqli_real_escape_string($con, $_POST['if_not_available']);
        $doct_role = mysqli_real_escape_string($con, $_POST['specialization_txt']);
    
         $new_cat = "INSERT INTO dr_cat_types (dr_cat_id,type) 
                       VALUES (12,'$doct_role')";
        if(mysqli_query($con, $new_cat)){
            $specialization = mysqli_insert_id($con);
        }
    }

  
    $city_id = mysqli_real_escape_string($con, $_POST['city_id']);
    $user_id = (int)$_POST['user_id'];
    $user_name = mysqli_real_escape_string($con, $_POST['username']);
    $pass = mysqli_real_escape_string($con, $_POST['password']);
    $password = base64_encode($pass);

    $doctor_name = mysqli_real_escape_string($con, $_POST['doctor_name']);
    $short_detail = mysqli_real_escape_string($con, $_POST['short_detail']);
    $experience_years = mysqli_real_escape_string($con, $_POST['experience_years']);
    $doctor_phone = mysqli_real_escape_string($con, $_POST['doctor_phone']);
    $doctor_email = mysqli_real_escape_string($con, $_POST['doctor_email']);
    $doctor_type = mysqli_real_escape_string($con, $_POST['doctor_type']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $other = mysqli_real_escape_string($con, $_POST['other']);
    $cnic = mysqli_real_escape_string($con, trim($_POST['cnic']));   // ===== NEW: CNIC =====
    $static_clinical_info = mysqli_real_escape_string($con, $_POST['static_clinical_info']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // ===== NEW FIELDS =====
    $mahre_amraz = mysqli_real_escape_string($con, $_POST['mahre_amraz']);
    $notes = mysqli_real_escape_string($con, $_POST['notes']);

    // ===== Server-side duplicate check (CNIC / Email / Username) - saare errors ek saath =====
    $dup_errors   = [];
    $ex_doctor_id = $edit_mode ? $doctor_id : 0;
    $cnic_digits  = preg_replace('/\D/', '', $cnic);   // dashes hata kar sirf digits

    if ($cnic_digits !== '' && mysqli_num_rows(mysqli_query($con,
        "SELECT 1 FROM doctors WHERE REPLACE(cnic,'-','')='$cnic_digits' AND doctor_id<>$ex_doctor_id LIMIT 1")) > 0) {
        $dup_errors[] = "This CNIC '" . htmlspecialchars($_POST['cnic']) . "' already exists.";
    }

    if ($doctor_email !== '' && mysqli_num_rows(mysqli_query($con,
        "SELECT 1 FROM doctors WHERE doctor_email='$doctor_email' AND doctor_id<>$ex_doctor_id LIMIT 1")) > 0) {
        $dup_errors[] = "This Email '" . htmlspecialchars($_POST['doctor_email']) . "' already exists.";
    }

    if ($user_name !== '' && mysqli_num_rows(mysqli_query($con,
        "SELECT 1 FROM users WHERE username='$user_name' AND user_id<>$user_id LIMIT 1")) > 0) {
        $dup_errors[] = "This Username '" . htmlspecialchars($_POST['username']) . "' already exists.";
    }

    $dup_error = implode('<br>', $dup_errors);
    
    // Handle doctor type specific fields
    $hospital_id = null;
    $clinic_name = '';
    $clinic_address = '';
    
    if ($doctor_type == 1) {
        // Hospital doctor
        $hospital_id = mysqli_real_escape_string($con, $_POST['hospital_id']);
    } else {
        // Personal clinic
        $clinic_name = mysqli_real_escape_string($con, $_POST['clinic_name']);
        $clinic_address = mysqli_real_escape_string($con, $_POST['clinic_address']);
    }
    
    // Handle file upload for doctor picture
    $doctor_pic = '';
    if ($dup_error === '' && isset($_FILES['doctor_pic']) && $_FILES['doctor_pic']['error'] == 0) {
        $target_dir = BASE_PATH."/admin/inc/uploads/doctors/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["doctor_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Allow certain file formats
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["doctor_pic"]["tmp_name"], $target_file)) {
                $doctor_pic = $file_name;
            }
        }
    }
    
    if ($dup_error !== '') {
        $error_msg = $dup_error;
    } elseif ($edit_mode) {
        // Update existing doctor - ADDED NEW FIELDS
        $update_query = "UPDATE doctors SET 
                            city_id = '$city_id', 
                            doctor_name = '$doctor_name', 
                            cat_type_id = '$specialization', 
                            experience_years = '$experience_years', 
                            doctor_phone = '$doctor_phone', 
                            doctor_email = '$doctor_email', 
                            doctor_type = '$doctor_type', 
                            gender = '$gender', 
                            other = '$other', 
                            cnic = '$cnic',
                            static_clinical_info = '$static_clinical_info',
                            mahre_amraz = '$mahre_amraz',
                            notes = '$notes'";
        
        // Update type-specific fields
        if ($doctor_type == 1) {
            // Hospital doctor: ensure valid hospital_id and clear clinic fields
            $update_query .= ", hospital_id = '" . $hospital_id . "', clinic_name = '', clinic_address = ''";
        } else {
            // Personal clinic: set hospital_id to NULL and save clinic details
            $update_query .= ", hospital_id = NULL, clinic_name = '$clinic_name', clinic_address = '$clinic_address'";
        }
        
        // Update picture only if new one is uploaded
        if (!empty($doctor_pic)) {
            $update_query .= ", doctor_pic = '$doctor_pic'";
        }
        
        $update_query .= ", updated_at = NOW() WHERE doctor_id = $doctor_id";
        
        if (mysqli_query($con, $update_query)) {
            $ref='';
            if($status==0){
                $ref = mysqli_real_escape_string($con, $_POST['ref']);
            }

            // Update users table (status, reference, and password only if a new one was entered)
            $user_update = "UPDATE users SET status='". $status ."', reference='". $ref ."'";
            if ($pass !== '') {
                $user_update .= ", password='". $password ."'";
            }
            $user_update .= " WHERE user_id=". $user_id;
            mysqli_query($con, $user_update);

            $success_msg = "Doctor updated successfully!";
            // Refresh data (with users join so username/status/ref stay available)
            $edit_result = mysqli_query($con, "SELECT doctors.*, u.user_id as u_id, u.status as estatus, u.reference as ref, u.username
                FROM doctors LEFT JOIN users u ON u.user_id = doctors.user_id
                WHERE doctor_id = $doctor_id");
            $doctor_data = mysqli_fetch_assoc($edit_result);

            // doctor_in_hospital sync (clinic => hospital_id=0, if_clinic=1)
            sync_doctor_workplace($con, $doctor_id, $hospital_id, $doctor_type == 2);

        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }

    } else {
        // Insert new doctor - ADDED NEW FIELDS
        $hospital_id_value = ($doctor_type == 1 && !empty($hospital_id)) ? "'" . $hospital_id . "'" : "NULL";

        $created_at = date('Y-m-d');

        $generate_user_id = "INSERT INTO users (username, email, password, user_type_id, status, created_at)
        VALUES ('$user_name', '$doctor_email', '$password', 2, 1, '$created_at')";
        mysqli_query($con, $generate_user_id);
        $userid = mysqli_insert_id($con);

         $insert_query = "INSERT INTO doctors (
                            user_id, city_id, hospital_id, doctor_name, 
                            short_detail, cat_type_id, experience_years, doctor_phone, 
                            doctor_email, doctor_type, clinic_name, clinic_address, 
                            doctor_pic, static_clinical_info, approve, gender, other, cnic,
                            mahre_amraz, notes, created_at
                        ) VALUES (
                            $userid, '$city_id', $hospital_id_value, '$doctor_name', 
                            '$short_detail', '$specialization', '$experience_years', '$doctor_phone', 
                            '$doctor_email', '$doctor_type', '$clinic_name', '$clinic_address', 
                            '$doctor_pic', '$static_clinical_info', 1, '$gender', '$other', '$cnic',
                            '$mahre_amraz', '$notes', NOW()
                        )";
        
        if (mysqli_query($con, $insert_query)) {
            $last_insert_id = mysqli_insert_id($con);
            $success_msg = "Doctor added successfully!";

            // doctor_in_hospital entry (clinic => hospital_id=0, if_clinic=1)
            sync_doctor_workplace($con, $last_insert_id, $hospital_id, $doctor_type == 2);

        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-add-admin.css">
<style>
    .field-msg { display:block; margin-top:4px; font-size:12px; }
    .field-msg.error { color:#dc3545; }
    .field-msg.ok { color:#198754; }
    #saveBtn:disabled { opacity:.5; cursor:not-allowed; }
</style>

<?php
// Fetch doctor categories and types for specialization dropdown
$categories_query = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type 
                   FROM dr_categories dc 
                   LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id 
                   ORDER BY dc.cat_name, dct.type";
$categories_result = mysqli_query($con, $categories_query);

// Group categories and types
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
?>

<div class="content-wrapper">
    <div class="container-fluid">
        
        <!-- Header -->
        <div class="page-header animate-up">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="page-title">
                        <i class="icofont icofont-doctor-alt"></i> 
                        <?php echo $edit_mode ? 'Edit Doctor Profile' : 'Add New Doctor'; ?>
                    </h2>
                    <p class="page-subtitle">Manage doctor information, clinics, and specializations</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>admin/doctors/list" class="btn btn-outline-light rounded-pill">
                        <i class="icofont icofont-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($success_msg)): ?>
            <div class="alert alert-modern alert-success-modern animate-up">
                <i class="icofont icofont-check-circled fs-4 me-2"></i>
                <strong>Success!</strong> &nbsp; <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-modern alert-danger-modern animate-up">
                <i class="icofont icofont-warning-alt fs-4 me-2"></i>
                <strong>Error!</strong> &nbsp; <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="animate-up delay-1">
            <input type="hidden" name="doctor_type" id="doctor_type" value="<?php echo $edit_mode ? $doctor_data['doctor_type'] : '1'; ?>">
            <input type="hidden" name="user_id" value="<?php if(isset($doctor_data['user_id'])){ echo $doctor_data['user_id']; } ?>">
            
            <!-- Mode Selection -->
            <div class="modern-card">
                <div class="card-body-custom">
                    <div class="toggle-container">
                        <label class="custom-switch">
                            <input type="checkbox" id="personal_clinic_toggle" <?php echo ($edit_mode && $doctor_data['doctor_type'] == 2) ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <div>
                            <div class="toggle-label">Personal Clinic Mode</div>
                            <span class="toggle-description">Switch ON if this doctor runs a private clinic. Switch OFF for hospital doctors.</span>
                        </div>
                        <div class="ms-auto">
                            <span id="mode_badge" class="badge-status hospital">
                                <i class="icofont icofont-hospital"></i> Hospital Mode
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Location & Clinic Info -->
                <div class="col-lg-6">
                    <div class="modern-card h-100">
                        <div class="card-header-custom">
                            <h5><i class="icofont icofont-location-pin"></i> Location & Workplace</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="form-group">
                                <label class="form-label">
                                    City
                                    <span class="required">*</span>
                                </label>
                                <select class="form-control-modern" id="cityId" name="city_id" required>
                                    <option value="">Select City</option>
                                    <?php 
                                    mysqli_data_seek($cities_result, 0);
                                    while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                        <option value="<?php echo $city['city_id']; ?>" 
                                                <?php echo ($edit_mode && $doctor_data['city_id'] == $city['city_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($city['city_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div id="hospital_section" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label">
                                        Hospital
                                        <span class="required">*</span>
                                    </label>
                                    <select class="form-control-modern" id="hospitalId" name="hospital_id" required>
                                        <option value="">Select Hospital</option>
                                        <?php 
                                        mysqli_data_seek($hospitals_result, 0);
                                        while ($hospital = mysqli_fetch_assoc($hospitals_result)): ?>
                                            <option value="<?php echo $hospital['hospital_id']; ?>" 
                                                    data-city="<?php echo $hospital['city_id']; ?>"
                                                    <?php echo ($edit_mode && $doctor_data['hospital_id'] == $hospital['hospital_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($hospital['hospital_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div id="clinic_section" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label">
                                        Clinic Name
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control-modern" id="clinicName" name="clinic_name" 
                                           placeholder="e.g. Dr. Smith's Care"
                                           value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['clinic_name']) : ''; ?>"
                                           required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        Clinic Address
                                        <span class="required">*</span>
                                    </label>
                                    <textarea class="form-control-modern" id="clinicAddress" name="clinic_address" rows="3"
                                              placeholder="Full address of the clinic" required><?php echo $edit_mode ? htmlspecialchars($doctor_data['clinic_address']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="col-lg-6">
                    <div class="modern-card h-100">
                        <div class="card-header-custom">
                            <h5><i class="icofont icofont-user-alt-3"></i> Personal Details</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Username
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" class="form-control-modern" name="username" id="username"
                                               placeholder="Enter username" required
                                               value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['username'] ?? '') : ''; ?>"
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
                                        <input type="password" class="form-control-modern" name="password" 
                                               placeholder="<?php echo $edit_mode ? 'Leave blank to keep current' : 'Enter password'; ?>"
                                               <?php echo $edit_mode ? '' : 'required'; ?>>
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
                                        <input type="text" class="form-control-modern" name="doctor_name" required
                                               placeholder="Dr. John Doe"
                                               value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['doctor_name']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Specialization
                                            <span class="required">*</span>
                                        </label>
                                        <!-- ===== SELECT2 SEARCHABLE DROPDOWN ===== -->
                                        <select class="form-control-modern" name="specialization" id="specializationSelect" required>
                                            <option value="">Search Specialization...</option>
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
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Experience (Years)
                                            <span class="optional">(Optional)</span>
                                        </label>
                                        <input type="number" class="form-control-modern" name="experience_years" min="0" 
                                               placeholder="e.g. 5"
                                               value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['experience_years']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Phone Number
                                            <span class="required">*</span>
                                        </label>
                                        <input type="tel" class="form-control-modern" name="doctor_phone" 
                                               placeholder="Contact Number"
                                               value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['doctor_phone']) : ''; ?>"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Short Detail
                                    <span class="optional">(Optional)</span>
                                </label>
                                <input type="text" class="form-control-modern" name="short_detail" 
                                       placeholder="MBBS/FCPS/LONDON/CHINA"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['short_detail']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    Other
                                    <span class="optional">(Optional)</span>
                                </label>
                                <input type="text" class="form-control-modern" name="other"
                                       placeholder="Incharge/DHQ etc"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['other']) : ''; ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Email Address
                                        </label>
                                        <input type="email" class="form-control-modern" name="doctor_email" id="doctor_email"
                                            placeholder="doctor@example.com"
                                            value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['doctor_email']) : ''; ?>"
                                            >
                                        <small class="field-msg" id="doctor_email_msg"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Gender
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-control-modern" name="gender" id="gender" required>
                                            <option value="Male" <?php echo ($edit_mode && $doctor_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($edit_mode && $doctor_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($edit_mode && $doctor_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ===== NEW FIELD: CNIC ===== -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            CNIC
                                        </label>
                                        <input type="text" class="form-control-modern" name="cnic" id="cnic"
                                               placeholder="1234512345671" maxlength="15"
                                               title="Format: 1234512345671"
                                               value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['cnic'] ?? '') : ''; ?>"
                                               >
                                        <small class="field-msg" id="cnic_msg"></small>
                                    </div>
                                </div>
                            </div>
                            <!-- ===== END CNIC ===== -->

                            <!-- ===== NEW FIELDS: MAHRE AMRAZ & NOTES ===== -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group field-mahre">
                                        <label class="form-label">
                                            <i class="fas fa-star me-2" style="color:#f59e0b;"></i> ماہرِ امراض (Specialist in Disease)
                                            <span class="optional">(Optional)</span>
                                        </label>
                                        <input type="text" class="form-control-modern" name="mahre_amraz" 
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
                                        <input type="text" class="form-control-modern" name="notes" 
                                               placeholder="مثال: مفت الٹراساؤنڈ، مفت ایکس رے، مفت مشورہ"
                                               value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['notes']) : ''; ?>">
                                        <small class="text-muted">کوئی خاص پیشکش، نوٹس یا ہدایات (جیسے: مفت الٹراساؤنڈ، مفت ایکس رے، ڈسکاؤنٹ)</small>
                                    </div>
                                </div>
                            </div>
                            <!-- ===== END NEW FIELDS ===== -->

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Clinical Info Detail
                                            <span class="optional">(Optional)</span>
                                        </label>
                                        <textarea class="form-control-modern" name="static_clinical_info" rows="3"
                                                  placeholder="Add clinical notes or special instructions..."><?php echo $edit_mode ? htmlspecialchars($doctor_data['static_clinical_info']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="modern-card">
                        <div class="card-header-custom">
                            <h5><i class="icofont icofont-settings"></i> Additional Settings</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Doctor Picture
                                            <span class="optional">(Optional)</span>
                                        </label>
                                        <input type="file" class="form-control-modern" name="doctor_pic" accept="image/*">
                                        <small class="text-muted mt-2 d-block">Recommended size: 500x500px (JPG, PNG)</small>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <?php if ($edit_mode && !empty($doctor_data['doctor_pic'])): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor_data['doctor_pic']; ?>" 
                                             alt="Current Picture" class="img-preview">
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <hr style="border-top: 1px solid #eee; margin: 30px 0;">
                            
                            <div class="form-group">
                                <label class="form-label">
                                    Active Status
                                    <span class="required">*</span>
                                </label>
                                <div>
                                    <label class="custom-switch" style="vertical-align: middle;">
                                        <input type="checkbox" id="estatus" name="status" value="1"
                                            <?php echo (!$edit_mode || (isset($doctor_data['estatus']) && $doctor_data['estatus'] == 1)) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="ms-3 fw-bold" id="statusLabel">Active</span>
                                    <small class="text-muted d-block mt-1">
                                        Enable to make this doctor visible in the public directory.
                                    </small>
                                </div>
                            </div>

                            <div class="mb-3" id="refDiv" style="display: <?php echo ($edit_mode && isset($doctor_data['estatus']) && $doctor_data['estatus'] == 0) ? 'block' : 'none'; ?>;">
                                <label for="ref" class="form-label">
                                    Inactive Status Detail
                                    <span class="required">*</span>
                                </label>
                                <textarea class="form-control" id="ref" name="ref" rows="5"
                                          <?php echo ($edit_mode && isset($doctor_data['estatus']) && $doctor_data['estatus'] == 0) ? 'required' : ''; ?>><?php if(isset($doctor_data['ref'])){ echo $doctor_data['ref']; } ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
document.addEventListener('DOMContentLoaded', function () {
    const estatus = document.getElementById('estatus');
    const refDiv = document.getElementById('refDiv');
    const ref = document.getElementById('ref');

    function toggleRef() {
        if (estatus.checked) {
            // Active => Hide textarea
            refDiv.style.display = 'none';
            ref.required = false;
        } else {
            // Inactive => Show textarea
            refDiv.style.display = 'block';
            ref.required = true;
        }
    }

    // Initial state
    toggleRef();

    // On checkbox change
    estatus.addEventListener('change', toggleRef);
});
</script>

            <div class="text-center mt-4 mb-5 animate-up delay-2">
                <button type="submit" class="btn-action btn-save" id="saveBtn">
                    <i class="icofont icofont-save me-2"></i> Save Doctor Details
                </button>
                <a href="<?php echo BASE_URL; ?>admin/doctors/list" class="btn-action btn-cancel">
                    <i class="icofont icofont-close me-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
<?php include BASE_PATH.'/admin/inc/footer.php';?>

<script>
    $(document).ready(function() {
        // ===== SELECT2 FOR CITY =====
        $('#cityId').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search city...',
            allowClear: true,
            width: '100%'
        });

        // ===== SELECT2 FOR HOSPITAL =====
        $('#hospitalId').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search hospital...',
            allowClear: true,
            width: '100%'
        });

        // ===== SELECT2 FOR SPECIALIZATION - WITH SEARCH =====
        $('#specializationSelect').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search Specialization...',
            allowClear: true,
            width: '100%',
            dropdownCssClass: 'specialization-dropdown'
        });
    });

    // ===== CITY -> HOSPITAL FILTER + CLINIC/HOSPITAL MODE =====
    // (Select2 init upar wale ready block me hota hai, ye block uske baad chalta hai)
    $(document).ready(function () {
        const $toggle          = $('#personal_clinic_toggle');
        const $hospitalSection = $('#hospital_section');
        const $clinicSection   = $('#clinic_section');
        const $hospital        = $('#hospitalId');
        const $clinicName      = $('#clinicName');
        const $clinicAddress   = $('#clinicAddress');
        const $city            = $('#cityId');
        const $doctorType      = $('#doctor_type');
        const $badge           = $('#mode_badge');

        // Page load par saare hospitals memory me save kar lo
        const allHospitals = [];
        $hospital.find('option').each(function () {
            if (this.value !== '') {
                allHospitals.push({
                    id:   this.value,
                    text: $.trim($(this).text()),
                    city: String($(this).data('city'))
                });
            }
        });
        const initialHospital = $hospital.val();

        // Sirf selected city ke hospitals dropdown me daalo
        function buildHospitals(selectedId) {
            const city = String($city.val() || '');
            $hospital.empty().append('<option value="">Select Hospital</option>');

            allHospitals.forEach(function (h) {
                if (h.city === city) {
                    $hospital.append($('<option>').val(h.id).text(h.text));
                }
            });

            if (selectedId && $hospital.find('option[value="' + selectedId + '"]').length) {
                $hospital.val(selectedId);
            } else {
                $hospital.val('');
            }
            $hospital.trigger('change.select2');   // Select2 ka UI refresh
        }

        function updateView() {
            if ($toggle.is(':checked')) {
                // Clinic Mode
                $hospitalSection.hide();
                $clinicSection.show();

                $hospital.prop('required', false);
                $clinicName.prop('required', true);
                $clinicAddress.prop('required', true);

                $doctorType.val('2');
                $badge.attr('class', 'badge-status clinic')
                      .html('<i class="icofont icofont-building"></i> Clinic Mode');
            } else {
                // Hospital Mode
                $clinicSection.hide();
                $clinicName.prop('required', false);
                $clinicAddress.prop('required', false);

                if ($city.val()) {
                    $hospitalSection.show();
                    $hospital.prop('required', true);
                } else {
                    $hospitalSection.hide();          // city select nahi to hospital nahi
                    $hospital.prop('required', false);
                }

                $doctorType.val('1');
                $badge.attr('class', 'badge-status hospital')
                      .html('<i class="icofont icofont-hospital"></i> Hospital Mode');
            }
        }

        // City change -> hospitals dobara banao
        $city.on('change', function () {
            buildHospitals('');
            updateView();
        });
        $toggle.on('change', updateView);

        // Initial state (edit mode me purana hospital selected rahega)
        buildHospitals(initialHospital);
        updateView();
    });

    // if specialization not available in dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('if_not_available');
        const selectWrapper = document.getElementById('specializationSelect');
        const textWrapper = document.getElementById('specialization_txt');

        function toggleFields() {
            if (checkbox.checked) {
                selectWrapper.style.display = 'none';
                textWrapper.style.display = 'block';
                document.getElementById('specializationSelect').value = '';
                // Make text field required when checkbox is checked
                textWrapper.setAttribute('required', 'required');
                selectWrapper.removeAttribute('required');
                // Destroy and recreate Select2 when hidden/shown
                $('#specializationSelect').select2('destroy');
                $('#specializationSelect').hide();
            } else {
                selectWrapper.style.display = 'block';
                textWrapper.style.display = 'none';
                document.getElementById('specialization_txt').value = '';
                // Make select field required when checkbox is unchecked
                selectWrapper.setAttribute('required', 'required');
                textWrapper.removeAttribute('required');
                // Reinitialize Select2
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

    // ===== DUPLICATE CHECK (CNIC / Email / Username) =====
    $(document).ready(function () {
        const checkUrl = '<?= BASE_URL ?>admin/doctors/check_unique.php';
        const doctorId = <?php echo $edit_mode ? (int)$doctor_id : 0; ?>;
        const userId   = <?php echo ($edit_mode && isset($doctor_data['user_id'])) ? (int)$doctor_data['user_id'] : 0; ?>;
        const $saveBtn = $('#saveBtn');

        const fields = {
            cnic:     { $el: $('#cnic'),         $msg: $('#cnic_msg'),         label: 'CNIC',     bad: false, timer: null, req: 0 },
            email:    { $el: $('#doctor_email'), $msg: $('#doctor_email_msg'), label: 'Email',    bad: false, timer: null, req: 0 },
            username: { $el: $('#username'),     $msg: $('#username_msg'),     label: 'Username', bad: false, timer: null, req: 0 }
        };

        function refreshButton() {
            const anyBad = Object.values(fields).some(f => f.bad);
            $saveBtn.prop('disabled', anyBad);
        }

        function check(key) {
            const f = fields[key];
            const value = $.trim(f.$el.val());

            if (value === '' || f.$el.prop('readonly')) {
                f.bad = false;
                f.$msg.text('').removeClass('error ok');
                refreshButton();
                return;
            }

            const myReq = ++f.req; // purane responses ignore karne ke liye
            $.post(checkUrl, { field: key, value: value, doctor_id: doctorId, user_id: userId }, function (res) {
                if (myReq !== f.req) return;
                if (res.exists) {
                    f.bad = true;
                    f.$msg.text(f.label + ' pehle se maujood hai.').removeClass('ok').addClass('error');
                } else {
                    f.bad = false;
                    f.$msg.text('').removeClass('error ok');
                }
                refreshButton();
            }, 'json');
        }

        $.each(fields, function (key, f) {
            f.$el.on('keyup input', function () {
                clearTimeout(f.timer);
                f.timer = setTimeout(function () { check(key); }, 400);
            });
        });

        // ===== CNIC auto-dash format: 12345-1234567-1 =====
        $('#cnic').on('input', function () {
            let v = this.value.replace(/\D/g, '').substring(0, 13);
            if (v.length > 12)      v = v.replace(/^(\d{5})(\d{7})(\d{1}).*/, '$1-$2-$3');
            else if (v.length > 5)  v = v.replace(/^(\d{5})(\d{0,7}).*/, '$1-$2');
            this.value = v;
        });
    });
</script>