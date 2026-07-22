<?php include '../config.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
 <?php include BASE_PATH.'/admin/inc/nav.php';?> 

<?php

$entity_id =0;
if (isset($_GET['entity_id'])) {
    $entity_id =$_GET['entity_id'];
}

// Check if it's edit mode
$edit_mode = false;
$doctor_data = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $doctor_id = $_GET['id'];
    $edit_query = "SELECT doctors.*, e.entity_id as e_id,e.status as estatus,e.reference as ref
    FROM doctors 
    LEFT JOIN entities e ON e.entity_id = doctors.entity_id
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
$hospitals_query = "SELECT hospitals.entity_id,hospital_id, hospital_name, hospitals.city_id,cities.city_name
FROM 
hospitals
LEFT JOIN cities ON hospitals.city_id = cities.city_id
LEFT JOIN entities e ON e.entity_id = hospitals.entity_id
 WHERE e.status = 1 AND hospitals.approve=1 ORDER BY hospital_name ASC";
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
    $entity_id = mysqli_real_escape_string($con, $_POST['entity_id']);
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
    $static_clinical_info = mysqli_real_escape_string($con, $_POST['static_clinical_info']);
    $status = isset($_POST['status']) ? 1 : 0;
    
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
    if (isset($_FILES['doctor_pic']) && $_FILES['doctor_pic']['error'] == 0) {
        $target_dir = BASE_PATH."/admin/inc/uploads/doctors/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["doctor_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Allow certain file formats
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["doctor_pic"]["tmp_name"], $target_file)) {
                $doctor_pic = $file_name;
            }
        }
    }
    
    if ($edit_mode) {
        // Update existing doctor
        $update_query = "UPDATE doctors SET city_id = '$city_id', doctor_name = '$doctor_name', cat_type_id = '$specialization', experience_years = '$experience_years', doctor_phone = '$doctor_phone', doctor_email = '$doctor_email', doctor_type = '$doctor_type', gender = '$gender', other = '$other', static_clinical_info = '$static_clinical_info'";
        
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


             mysqli_query($con, "UPDATE entities set status='". $status ."', reference ='". $ref ."' WHERE entity_id='". $entity_id ."'");
            $success_msg = "Doctor updated successfully!";
            // Refresh data
            $edit_result = mysqli_query($con, "SELECT * FROM doctors WHERE doctor_id = $doctor_id");
            $doctor_data = mysqli_fetch_assoc($edit_result);

            if($hospital_id !== null && $hospital_id !== '' && $hospital_id !== 0){
                $insert_hospital_query = "INSERT INTO doctor_in_hospital (doctor_id, hospital_id) VALUES ($doctor_id, $hospital_id)";
                mysqli_query($con, $insert_hospital_query);
            }else{
                $insert_hospital_query = "INSERT INTO doctor_in_hospital (doctor_id, if_clinic,hospital_id) VALUES ($doctor_id, 1,0)";
                mysqli_query($con, $insert_hospital_query);
            }

        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }

    } else {
        // Insert new doctor
        $hospital_id_value = ($doctor_type == 1 && !empty($hospital_id)) ? "'" . $hospital_id . "'" : "NULL";
        
        $generate_ent_it = "INSERT INTO entities (entity_type,status, created_at) VALUES ('doctor',1,date('Y-m-d'))";
        mysqli_query($con, $generate_ent_it);
        $entity_id = mysqli_insert_id($con);



       $created_at = date('Y-m-d');

$generate_user_id = "INSERT INTO users (username, email, password, user_type_id, created_at)
VALUES ('$user_name', '$doctor_email', '$password', 2, '$created_at')";
        mysqli_query($con, $generate_user_id);
        $userid = mysqli_insert_id($con);



         $insert_query = "INSERT INTO doctors (entity_id, user_id, city_id, hospital_id, doctor_name, short_detail, cat_type_id, experience_years, doctor_phone, doctor_email, doctor_type, clinic_name, clinic_address, doctor_pic, static_clinical_info, approve, gender, other, created_at) 
                       VALUES ($entity_id,$userid, '$city_id', $hospital_id_value, '$doctor_name', '$short_detail', '$specialization', '$experience_years', '$doctor_phone', '$doctor_email', '$doctor_type', '$clinic_name', '$clinic_address', '$doctor_pic', '$static_clinical_info', 1, '$gender', '$other', NOW())";
        
        if (mysqli_query($con, $insert_query)) {
            $last_insert_id = mysqli_insert_id($con);
            $success_msg = "Doctor added successfully!";
            if($hospital_id_value !== null && $hospital_id_value !== '' && $hospital_id_value !== 0){
                $insert_hospital_query = "INSERT INTO doctor_in_hospital (doctor_id, hospital_id) VALUES ($last_insert_id, $hospital_id_value)";
                mysqli_query($con, $insert_hospital_query);
            }else{
                $insert_hospital_query = "INSERT INTO doctor_in_hospital (doctor_id, if_clinic,hospital_id) VALUES ($last_insert_id, 1,0)";
                mysqli_query($con, $insert_hospital_query);
            }


        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    }
}
?>

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

    body {
        background-color: var(--bg-color);
        color: var(--text-color);
        font-family: 'Ubuntu', sans-serif;
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

    .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .form-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
        display: block;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control-modern {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        background-color: var(--input-bg);
        transition: all 0.3s;
        font-size: 15px;
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

    .toggle-container {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 15px;
        border: 1px solid #eee;
        margin-bottom: 30px;
    }

    .custom-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        margin-right: 15px;
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
        height: 26px;
        width: 26px;
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
        transform: translateX(26px);
    }
    
    .toggle-label {
        font-weight: 600;
        font-size: 16px;
    }
    
    .toggle-description {
        display: block;
        font-size: 13px;
        color: #777;
        margin-top: 2px;
    }

    .btn-action {
        padding: 12px 30px;
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
        font-size: 14px;
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
        margin-left: 15px;
    }
    
    .btn-cancel:hover {
        background: #e74c3c;
        color: white;
    }

    .img-preview {
        width: 150px;
        height: 150px;
        border-radius: 15px;
        object-fit: cover;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border: 4px solid white;
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

    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-status.hospital {
        background: rgba(79, 172, 254, 0.1);
        color: #4facfe;
    }
    
    .badge-status.clinic {
        background: rgba(118, 75, 162, 0.1);
        color: #764ba2;
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-up {
        animation: fadeInUp 0.5s ease-out forwards;
    }
    
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }

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
             <input type="hidden" name="entity_id" value="<?php if(isset($doctor_data['entity_id'])){ echo $doctor_data['entity_id']; } ?>">
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
                                <label class="form-label">City</label>
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

                            <!-- <div id="hospital_section" style="display:none;"> -->
                            <div id="hospital_section" >
                                <div class="form-group">
                                    <label class="form-label">Hospital</label>
                                    <select class="form-control-modern" id="hospitalId" name="hospital_id">
                                        <option value="">Select Hospital</option>
                                        <?php 
                                        mysqli_data_seek($hospitals_result, 0);
                                        while ($hospital = mysqli_fetch_assoc($hospitals_result)): ?>
                                            <option value="<?php echo $hospital['hospital_id']; ?>" 
                                                    data-city="<?php echo $hospital['city_id']; ?>"
                                                    <?php echo ($edit_mode && $doctor_data['hospital_id'] == $hospital['hospital_id']) ? 'selected' : ''; ?>>
                                                <span style="color: red;"><?=$hospital['city_name']."-";?></span> <?php echo htmlspecialchars($hospital['hospital_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div id="clinic_section" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label">Clinic Name</label>
                                    <input type="text" class="form-control-modern" id="clinicName" name="clinic_name" 
                                           placeholder="e.g. Dr. Smith's Care"
                                           value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['clinic_name']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Clinic Address</label>
                                    <textarea class="form-control-modern" id="clinicAddress" name="clinic_address" rows="3"
                                              placeholder="Full address of the clinic"><?php echo $edit_mode ? htmlspecialchars($doctor_data['clinic_address']) : ''; ?></textarea>
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
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control-modern" name="username" min="0" 
                                               placeholder="e.g. 5"
                                               value="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control-modern" name="password" 
                                               placeholder="Contact Number"
                                               value="">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Doctor Name</label>
                                        <input type="text" class="form-control-modern" name="doctor_name" required
                                               placeholder="Dr. John Doe"
                                               value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['doctor_name']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Specialization</label>
                                        <select class="form-control-modern" name="specialization" id="specialization">
                                            <option value="">Select Specialization</option>
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

                                        <input type="text" class="form-control-modern" name="specialization_txt"
                                               placeholder="Enter Specialization" id="specialization_txt" style="display:none;">



                                        <input type="checkbox" value="1" id="if_not_available" name="if_not_available"> 
                                        <label class="form-label text-danger" for="if_not_available">If not availabel</label>

                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Experience (Years)</label>
                                        <input type="number" class="form-control-modern" name="experience_years" min="0" 
                                               placeholder="e.g. 5"
                                               value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['experience_years']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control-modern" name="doctor_phone" 
                                               placeholder="Contact Number"
                                               value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['doctor_phone']) : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Short Detail</label>
                                <input type="text" class="form-control-modern" name="short_detail" 
                                       placeholder="MBBS/FCPS/LONDON/CHINA"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['short_detail']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Other</label>
                                <input type="text" class="form-control-modern" name="other"
                                       placeholder="Incharge/DHQ etc"
                                       value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['other']) : ''; ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control-modern" name="doctor_email"
                                            placeholder="doctor@example.com"
                                            value="<?php echo $edit_mode ? htmlspecialchars($doctor_data['doctor_email']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Gender</label>
                                        <select class="form-control-modern" name="gender" id="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php echo ($edit_mode && $doctor_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($edit_mode && $doctor_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                            <option value="Other" <?php echo ($edit_mode && $doctor_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>


                             <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Clincal Info Detail</label>
                                        <textarea class="form-control-modern" name="static_clinical_info"><?php echo $edit_mode ? htmlspecialchars($doctor_data['static_clinical_info']) : ''; ?></textarea>
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
                                        <label class="form-label">Doctor Picture</label>
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
                                <label class="custom-switch" style="vertical-align: middle;">
                                    <input type="checkbox" id="estatus" name="status" value="1"
                                        <?php echo (!$edit_mode || (isset($doctor_data['estatus']) && $doctor_data['estatus'] == 1)) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>

                                <span class="ms-3 fw-bold">Active Status</span>
                                <small class="text-muted d-block mt-1">
                                    Enable to make this doctor visible in the public directory.
                                </small>

                                <div class="mb-3" id="refDiv">
                                    <label for="ref" class="form-label">Inactive Status Detail</label>
                                    <textarea class="form-control" id="ref" name="ref" rows="5"><?php if(isset($doctor_data['ref'])){ echo $doctor_data['ref']; } ?></textarea>
                                </div>
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
                <button type="submit" class="btn-action btn-save">
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
        $('#cityId').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search city...',
            allowClear: true,
            width: '100%'
        });
                $('#hospitalId').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search hospital...',
                allowClear: true,
                width: '100%'
            });
            //  $('#specialization').select2({
            //     theme: 'bootstrap-5',
            //     placeholder: 'Search hospital...',
            //     allowClear: true,
            //     width: '100%'
            // });
});



document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('personal_clinic_toggle');
    const hospitalSection = document.getElementById('hospital_section');
    const clinicSection = document.getElementById('clinic_section');
    const hospitalId = document.getElementById('hospitalId');
    const clinicName = document.getElementById('clinicName');
    const clinicAddress = document.getElementById('clinicAddress');
    const doctorTypeInput = document.getElementById('doctor_type');
    const modeBadge = document.getElementById('mode_badge');
    const citySelect = document.getElementById('cityId');
    
    // Initial State
    updateView();
    filterHospitals();

    // Event Listeners
    toggle.addEventListener('change', updateView);
    citySelect.addEventListener('change', filterHospitals);

    function updateView() {
        if (toggle.checked) {
            // Clinic Mode
            hospitalSection.style.display = 'none';
            clinicSection.style.display = 'block';
            
            hospitalId.removeAttribute('required');
            clinicName.setAttribute('required', 'required');
            clinicAddress.setAttribute('required', 'required');
            
            doctorTypeInput.value = '2';
            modeBadge.className = 'badge-status clinic';
            modeBadge.innerHTML = '<i class="icofont icofont-building"></i> Clinic Mode';
        } else {
            // Hospital Mode
            clinicSection.style.display = 'none';
            // Only show hospital if city is selected
            if ($('#cityId').val()) { // Use Select2 method to check if city is selected
                hospitalSection.style.display = 'block';
            } else {
                hospitalSection.style.display = 'none';
            }
            
            clinicName.removeAttribute('required');
            clinicAddress.removeAttribute('required');
            
            if ($('#cityId').val()) { // Use Select2 method to check if city is selected
                hospitalId.setAttribute('required', 'required');
            }
            
            doctorTypeInput.value = '1';
            modeBadge.className = 'badge-status hospital';
            modeBadge.innerHTML = '<i class="icofont icofont-hospital"></i> Hospital Mode';
        }
    }

    function filterHospitals() {
        const selectedCity = $('#cityId').val(); // Use Select2 method to get value
        const options = hospitalId.options;
        let hasVisibleOptions = false;
        
        if (!selectedCity) {
            hospitalSection.style.display = 'block';
            $('#hospitalId').val(''); // Use Select2 method to clear
            hospitalId.removeAttribute('required');
            return;
        }

        // Show section if in Hospital Mode
        if (!toggle.checked) {
            hospitalSection.style.display = 'block';
            hospitalId.setAttribute('required', 'required');
        }

        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            if (option.value === "") continue; // Skip default option

            const hospitalCity = option.getAttribute('data-city');
            if (hospitalCity == selectedCity) {
                option.style.display = 'block';
                hasVisibleOptions = true;
            } else {
                option.style.display = 'none';
            }
        }
        
        // Reset selection if current selection is now hidden
        const currentOption = options[hospitalId.selectedIndex];
        if (currentOption && currentOption.style.display === 'none') {
            hospitalId.value = '';
        }
    }
});




// if specialization not available in dropdown
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('if_not_available');
    const selectWrapper = document.getElementById('specialization');
    const textWrapper = document.getElementById('specialization_txt');

    // Function to toggle visibility
    function toggleFields() {
        if (checkbox.checked) {
            selectWrapper.style.display = 'none';
            textWrapper.style.display = 'block';
            // Optional: agar chahein to select ka value khali kar den
            document.getElementById('specialization').value = '';
        } else {
            selectWrapper.style.display = 'block';
            textWrapper.style.display = 'none';
            // Optional: text field clear kar sakte ho
            document.getElementById('specialization_txt').value = '';
        }
    }

    // Initial check on page load
    toggleFields();

    // Jab checkbox change ho
    checkbox.addEventListener('change', toggleFields);
});
</script>
