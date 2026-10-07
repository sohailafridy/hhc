<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php
$user_id = 0;
if (isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
}

// ============================================
// DELETE CLINICAL INFO
// ============================================
if(isset($_REQUEST['del_clinic_id']) && $_REQUEST['del_clinic_id'] != 0){
    $del_clinic_id = (int)$_REQUEST['del_clinic_id'];
    $clinic_info = "DELETE FROM clinical_info WHERE clinical_info_id = '" . $del_clinic_id . "'";
        if(mysqli_query($con, $clinic_info)){
            $_SESSION['success_msg'] = "Clinical information deleted successfully!";
        }
    // $doctor_in_hospital = "DELETE FROM doctor_in_hospital
    //   WHERE doctor_in_hosp_id = (
    //      SELECT doctor_in_hosp_id
    //      FROM clinical_info
    //      WHERE clinical_info_id = '" . $del_clinic_id . "'
    //   )";
    // if(mysqli_query($con, $doctor_in_hospital)){
    //     $clinic_info = "DELETE FROM clinical_info WHERE clinical_info_id = '" . $del_clinic_id . "'";
    //     if(mysqli_query($con, $clinic_info)){
    //         $_SESSION['success_msg'] = "Clinical information deleted successfully!";
    //     }
    // }
}

// ============================================
// CHECK CLINICAL INFO STATUS
// ============================================
$check = mysqli_query($con, "SELECT COUNT(doctor_in_hosp_id) as ids 
    FROM doctor_in_hospital 
    WHERE doctor_in_hosp_id NOT IN (SELECT doctor_in_hosp_id FROM clinical_info) 
    AND doctor_in_hospital.doctor_id = '" . (int)$_GET['id'] . "'");
$ids = mysqli_fetch_assoc($check);
$ids = $ids['ids'];

// ============================================
// EMERGENCY STATUS TOGGLE
// ============================================
if (isset($_POST['toggle_emergency']) && is_numeric($_POST['toggle_emergency']) && isset($_POST['status'])) {
    $doctor_id = (int)$_POST['toggle_emergency'];
    $new_status = $_POST['status'] == 1 ? 1 : 0;
    $update_query = "UPDATE doctors SET emergency_status = $new_status WHERE doctor_id = $doctor_id";
    if (mysqli_query($con, $update_query)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
    exit();
}

// ============================================
// REMOVE / RE-ADD HOSPITAL (doctor_in_hospital.inactive)
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hospital_action'], $_POST['doctor_in_hosp_id'], $_POST['doctor_id'])
    && is_numeric($_POST['doctor_in_hosp_id']) && is_numeric($_POST['doctor_id'])) {

    $act_dih_id    = (int)$_POST['doctor_in_hosp_id'];
    $act_doctor_id = (int)$_POST['doctor_id'];

    if ($_POST['hospital_action'] === 'remove') {
        $new_inactive = 1;
        $ok_msg = "Hospital removed successfully!";
    } elseif ($_POST['hospital_action'] === 'readd') {
        $new_inactive = 0;
        $ok_msg = "Hospital re-added successfully!";
    } else {
        $new_inactive = null;
    }

    if ($new_inactive !== null) {
        // doctor_id bhi match hota hai taake kisi aur doctor ki row change na ho
        $act_query = "UPDATE doctor_in_hospital 
                      SET inactive = $new_inactive, updated_at = NOW() 
                      WHERE doctor_in_hosp_id = $act_dih_id AND doctor_id = $act_doctor_id";
        if (mysqli_query($con, $act_query)) {

            if($new_inactive == 1){
                // mysqli_query($con, "DELETE FROM `clinical_info` WHERE `doctor_in_hosp_id` = '". $act_dih_id ."'");

                if((int)$_POST['personal_clinic']==1){
                    mysqli_query($con, "UPDATE `doctors` set `clinic_status`=1 WHERE `doctor_id` = '". $act_doctor_id ."'");
                }
            }else{
                mysqli_query($con, "UPDATE `doctors` set `clinic_status`=0 WHERE `doctor_id` = '". $act_doctor_id ."'");
            }
            $_SESSION['success_msg'] = $ok_msg;
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
        }
    }

    // Redirect (refresh par form dobara submit na ho)
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?id=' . $act_doctor_id);
    exit();
}

// ============================================
// DELETE DOCTOR
// ============================================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $pic_query = "SELECT doctor_pic FROM doctors WHERE doctor_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $doctor_pic_data = mysqli_fetch_assoc($pic_result);
    $doctor_pic = $doctor_pic_data ? $doctor_pic_data['doctor_pic'] : '';
    
    $delete_query = "DELETE FROM doctors WHERE doctor_id = $delete_id";
    if (mysqli_query($con, $delete_query)) {
        if (!empty($doctor_pic)) {
            $pic_path = BASE_PATH . "/admin/inc/uploads/doctors/" . $doctor_pic;
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
        }
        $_SESSION['success_msg'] = "Doctor deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/admin/inc/nav.php'; ?>

<?php
// ============================================
// GET DOCTOR ID
// ============================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}

$doctor_id = (int)$_GET['id'];

// ============================================
// FETCH DOCTOR DETAILS - WITH NEW FIELDS
// ============================================
$query = "SELECT d.*, 
                c.city_name,
                h.hospital_name,
                dc.cat_name,
                dct.type as cat_type,
                u.status as estatus,
                u.reference as ref,
                u.username,
                u.password
          FROM doctors d 
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
          LEFT JOIN dr_cat_types dct ON dct.dr_cat_type_id = d.cat_type_id
          LEFT JOIN dr_categories dc ON dc.dr_cat_id = dct.dr_cat_id
          LEFT JOIN users u ON u.user_id = d.user_id
          WHERE d.doctor_id = $doctor_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}

$doctor = mysqli_fetch_assoc($result);
$user_id = (int)$doctor['user_id'];

// ============================================
// FETCH RATING & REVIEWS
// ============================================
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews 
                 FROM feedback WHERE user_id = $user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;

$feedback_query = "SELECT f.* FROM feedback f WHERE f.user_id = $user_id AND f.status = 1 ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);

// ============================================
// FETCH CLINICAL INFO
// ============================================
$clinical_query = "SELECT clinical_info.*, hospitals.hospital_name, hospitals.hospital_id
                   FROM clinical_info 
                   LEFT JOIN doctor_in_hospital dih ON clinical_info.doctor_in_hosp_id = dih.doctor_in_hosp_id
                   LEFT JOIN hospitals ON dih.hospital_id = hospitals.hospital_id
                   WHERE dih.doctor_id = $doctor_id 
                   ORDER BY clinical_info.season, clinical_info.shift";
$clinical_result = mysqli_query($con, $clinical_query);

// ============================================
// FETCH DOCTOR-IN-HOSPITAL (for clinical info count)
// ============================================
$dih_query = "SELECT COUNT(*) as total FROM doctor_in_hospital WHERE doctor_id = $doctor_id";
$dih_result = mysqli_query($con, $dih_query);
$dih_count = mysqli_fetch_assoc($dih_result)['total'];

// ============================================
// FETCH ALL HOSPITALS WHERE DOCTOR IS REGISTERED
// ============================================
$reg_hospitals_query = "SELECT dih.doctor_in_hosp_id, dih.hospital_id, dih.if_clinic, dih.inactive,
                               dih.comment, dih.created_at, dih.updated_at,dih.doctor_id,
                               h.hospital_name, c.city_name
                        FROM doctor_in_hospital dih
                        LEFT JOIN hospitals h ON h.hospital_id = dih.hospital_id
                        LEFT JOIN cities c ON c.city_id = h.city_id
                        WHERE dih.doctor_id = $doctor_id AND dih.inactive = 0
                        ORDER BY dih.created_at DESC";
$reg_hospitals_result = mysqli_query($con, $reg_hospitals_query);

// ============================================
// FETCH PAST (REMOVED) HOSPITALS - inactive = 1
// ============================================
$past_hospitals_query = "SELECT dih.doctor_in_hosp_id, dih.hospital_id, dih.if_clinic, dih.inactive,
                                dih.comment, dih.created_at, dih.updated_at,
                                h.hospital_name, c.city_name
                         FROM doctor_in_hospital dih
                         LEFT JOIN hospitals h ON h.hospital_id = dih.hospital_id
                         LEFT JOIN cities c ON c.city_id = h.city_id
                         WHERE dih.doctor_id = $doctor_id AND dih.inactive = 1
                         ORDER BY dih.updated_at DESC";
$past_hospitals_result = mysqli_query($con, $past_hospitals_query);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-profile-admin.css">

<div class="content-wrapper">

   <!-- ===== PAGE HEADER ===== -->
<div class="page-header-modern">
    <div class="page-header-content">
        <div class="page-header-left">
            <?php if (!empty($doctor['doctor_pic'])): ?>
                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                     alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
            <?php else: ?>
                <div class="doctor-avatar-placeholder">
                    <i class="fas fa-user-md"></i>
                </div>
            <?php endif; ?>
            <div class="page-header-title">
                <h1 style="color: #ffffff !important; text-shadow: 0 2px 10px rgba(0,0,0,0.15);">
                    Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                </h1>
                <p style="color: rgba(255,255,255,0.85) !important; text-shadow: 0 1px 5px rgba(0,0,0,0.08);">
                    <i class="fas fa-stethoscope me-1"></i> <?php echo htmlspecialchars($doctor['cat_type'] ?? 'General'); ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($doctor['cat_name'] ?? 'N/A'); ?>
                    <span class="mx-2">|</span>
                    <span class="badge <?php echo $doctor['estatus'] == 1 ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $doctor['estatus'] == 1 ? 'Active' : 'Inactive'; ?>
                    </span>
                    <?php if ($doctor['emergency_status'] == 1): ?>
                        <span class="badge bg-warning text-dark ms-1">
                            <i class="fas fa-exclamation-triangle"></i> Emergency
                        </span>
                    <?php endif; ?>
                </p>
                
                <!-- ===== DOCTOR BADGES - FIXED COLORS ===== -->
                <div class="doctor-badges">
                    <!-- Short Detail Badge -->
                    <?php if (!empty($doctor['short_detail'])): ?>
                        <span class="badge-custom specialization">
                            <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($doctor['short_detail']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Mahere Amraz Badge -->
                    <?php if (!empty($doctor['mahre_amraz'])): ?>
                        <span class="badge-custom mahre">
                            <i class="fas fa-star"></i> ماہرِ امراض: <?php echo htmlspecialchars($doctor['mahre_amraz']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Notes Badge -->
                    <?php if (!empty($doctor['notes'])): ?>
                        <span class="badge-custom notes">
                            <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($doctor['notes']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="<?php echo BASE_URL; ?>admin/doctors/assign-hospitals?id=<?php echo $doctor['doctor_id']; ?>" class="btn-action-header">
                <i class="fas fa-edit"></i> Assign Doctor
            </a>
            <a href="<?php echo BASE_URL; ?>admin/doctors/add?id=<?php echo $doctor['doctor_id']; ?>" class="btn-action-header">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="javascript:void(0)" onclick="deleteDoctor(<?php echo $doctor['doctor_id']; ?>)" class="btn-action-header danger">
                <i class="fas fa-trash"></i> Delete
            </a>
            <a href="<?php echo BASE_URL; ?>admin/doctors/list" class="btn-action-header">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

    <!-- ===== STATS ROW ===== -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-number"><?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?></div>
            <div class="stat-label">Rating</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-number"><?php echo $total_reviews; ?></div>
            <div class="stat-label">Reviews</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏥</div>
            <div class="stat-number"><?php echo $dih_count; ?></div>
            <div class="stat-label">Hospitals</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-number"><?php echo mysqli_num_rows($clinical_result); ?></div>
            <div class="stat-label">Clinical Records</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-number"><?php echo $doctor['experience_years'] ?? 0; ?></div>
            <div class="stat-label">Experience (Yrs)</div>
        </div>
    </div>

    <!-- ===== DETAIL GRID ===== -->
    <div class="detail-grid">

        <!-- ===== LEFT COLUMN ===== -->
        <div class="left-column">

            <!-- Personal Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-user"></i> Personal Information</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="label">Doctor ID</span>
                        <span class="value">#<?php echo $doctor['doctor_id']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Full Name</span>
                        <span class="value">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></span>
                    </div>
                    
                    <!-- ===== SPECIALIZATION ===== -->
                    <div class="info-row">
                        <span class="label">Specialization</span>
                        <span class="value">
                            <span class="badge-info primary"><?php echo htmlspecialchars($doctor['cat_type'] ?? 'General'); ?></span>
                        </span>
                    </div>
                    
                    <!-- ===== SHORT DETAIL ===== -->
                    <?php if (!empty($doctor['short_detail'])): ?>
                        <div class="info-row">
                            <span class="label">Qualifications</span>
                            <span class="value" style="text-align: right;"><?php echo htmlspecialchars($doctor['short_detail']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- ===== MAHRE AMRAZ (NEW) ===== -->
                    <div class="info-row">
                        <span class="label">
                            <i class="fas fa-star" style="color: #f59e0b;"></i> ماہرِ امراض
                        </span>
                        <span class="value">
                            <?php if (!empty($doctor['mahre_amraz'])): ?>
                                <span class="badge-info warning"><?php echo htmlspecialchars($doctor['mahre_amraz']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <!-- ===== NOTES (NEW) ===== -->
                    <?php if (!empty($doctor['notes'])): ?>
                        <div class="info-row" style="display: block; border-bottom: none; padding-bottom: 4px;">
                            <span class="label" style="display: block; margin-bottom: 6px;">
                                <i class="fas fa-sticky-note" style="color: #22c55e;"></i> Notes
                            </span>
                            <div style="background: #f0fdf4; padding: 10px 14px; border-radius: 8px; border-left: 3px solid #22c55e; font-size: 0.9rem; color: var(--text);">
                                <?php echo nl2br(htmlspecialchars($doctor['notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- ===== OTHER FIELDS ===== -->
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['doctor_email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['doctor_phone']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Gender</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['gender']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Experience</span>
                        <span class="value"><?php echo $doctor['experience_years'] ?? 0; ?> Years</span>
                    </div>
                    <?php if (!empty($doctor['other'])): ?>
                        <div class="info-row">
                            <span class="label">Other Info</span>
                            <span class="value" style="text-align: right;"><?php echo htmlspecialchars($doctor['other']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-lock"></i> Account Information</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="label">Username</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['username'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Password</span>
                        <span class="value">
                            <span class="badge bg-secondary"><?php echo str_repeat('•', 8); ?></span>
                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="showPassword('<?php echo base64_decode($doctor['password']); ?>')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Status</span>
                        <span class="value">
                            <?php if ($doctor['estatus'] == 1): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($doctor['estatus'] == 0 && !empty($doctor['ref'])): ?>
                        <div class="info-row">
                            <span class="label">Inactive Reason</span>
                            <span class="value text-danger" style="text-align: right;"><?php echo htmlspecialchars($doctor['ref']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span class="label">Created At</span>
                        <span class="value"><?php echo date('d M Y, h:i A', strtotime($doctor['created_at'])); ?></span>
                    </div>
                    <?php if (!empty($doctor['updated_at'])): ?>
                        <div class="info-row">
                            <span class="label">Updated At</span>
                            <span class="value"><?php echo date('d M Y, h:i A', strtotime($doctor['updated_at'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Location & Workplace -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-map-marker-alt"></i> Location & Workplace</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        
                            <?php
                                if ((int)$doctor['clinic_status']==0) { ?>
                                    <span class="label bg-success">Active</span>
                                <?php }else{ ?>
                                    <span class="label bg-danger">Removed</span>
                                <?php }
                            ?>
                        
                    </div>
                    <div class="info-row">
                        <span class="label">City</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['city_name'] ?? 'N/A'); ?></span>
                    </div>
                    <?php if ($doctor['doctor_type'] == 1): ?>
                        <div class="info-row">
                            <span class="label">Type</span>
                            <span class="value"><span class="badge bg-info">Hospital</span></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Hospital</span>
                            <span class="value"><?php echo htmlspecialchars($doctor['hospital_name'] ?? 'N/A'); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="info-row">
                            <span class="label">Type</span>
                            <span class="value"><span class="badge bg-success">Personal Clinic</span></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Clinic Name</span>
                            <span class="value"><?php echo htmlspecialchars($doctor['clinic_name'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Clinic Address</span>
                            <span class="value" style="text-align: right;"><?php echo nl2br(htmlspecialchars($doctor['clinic_address'] ?? 'N/A')); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Static Clinical Info -->
            <?php if (!empty($doctor['static_clinical_info'])): ?>
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="fas fa-notes-medical"></i> Clinical Notes</h5>
                    </div>
                    <div class="info-card-body">
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-primary">
                            <?php echo nl2br(htmlspecialchars($doctor['static_clinical_info'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- ===== RIGHT COLUMN ===== -->
        <div class="right-column">

            <!-- Clinical Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-clock"></i> Clinical Information</h5>
                    <div>
                        <?php if ($ids != 0 && $ids != ''): ?>
                            <a href="clinical-info?id=<?php echo $doctor_id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-plus"></i> Add Clinical
                            </a>
                        <?php endif; ?>
                        <span class="badge bg-primary ms-1"><?php echo mysqli_num_rows($clinical_result); ?> Records</span>
                    </div>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($clinical_result) > 0): ?>
                        <?php 
                        // Group by season
                        $seasons = ['Summer' => [], 'Winter' => [], 'General' => []];
                        mysqli_data_seek($clinical_result, 0);
                        while ($row = mysqli_fetch_assoc($clinical_result)) {
                            $season = !empty($row['season']) ? $row['season'] : 'General';
                            if (!isset($seasons[$season])) {
                                $seasons[$season] = [];
                            }
                            $seasons[$season][] = $row;
                        }
                        ?>
                        
                        <div class="clinical-grid-cards">
                            <?php foreach ($seasons as $season_name => $records): ?>
                                <?php if (!empty($records)): ?>
                                    <?php foreach ($records as $clinical): ?>
                                        <div class="clinical-card-modern">
                                            <div class="clinical-card-header">
                                                <i class="fas fa-hospital"></i>
                                                <h6>
                                                    <?php 
                                                    if (!empty($clinical['hospital_name'])) {
                                                        echo htmlspecialchars($clinical['hospital_name']);
                                                    } else {
                                                        echo 'Personal Clinic';
                                                    }
                                                    ?>
                                                </h6>
                                                <span class="ms-auto">
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo $season_name; ?>
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="clinical-card-body">
                                                <?php if (!empty($clinical['morning_opening_time']) || !empty($clinical['morning_closing_time'])): ?>
                                                    <div class="clinical-info-row">
                                                        <i class="fas fa-sun text-warning"></i>
                                                        <span class="c-label">Morning</span>
                                                        <span class="c-value">
                                                            <?php 
                                                            echo date('h:i A', strtotime($clinical['morning_opening_time']));
                                                            echo ' - ';
                                                            echo date('h:i A', strtotime($clinical['morning_closing_time']));
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($clinical['evening_opening_time']) || !empty($clinical['evening_closing_time'])): ?>
                                                    <div class="clinical-info-row">
                                                        <i class="fas fa-moon text-primary"></i>
                                                        <span class="c-label">Evening</span>
                                                        <span class="c-value">
                                                            <?php 
                                                            echo date('h:i A', strtotime($clinical['evening_opening_time']));
                                                            echo ' - ';
                                                            echo date('h:i A', strtotime($clinical['evening_closing_time']));
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="clinical-info-row">
                                                    <i class="fas fa-calendar-day text-success"></i>
                                                    <span class="c-label">Working Days</span>
                                                    <span class="c-value"><?php echo htmlspecialchars($clinical['days'] ?? 'N/A'); ?></span>
                                                </div>
                                                
                                                <div class="clinical-info-row">
                                                    <i class="fas fa-calendar-times text-danger"></i>
                                                    <span class="c-label">Off Days</span>
                                                    <span class="c-value"><?php echo htmlspecialchars($clinical['off_days'] ?? 'None'); ?></span>
                                                </div>
                                                
                                                <?php if (!empty($clinical['contact'])): ?>
                                                    <div class="clinical-info-row">
                                                        <i class="fas fa-phone"></i>
                                                        <span class="c-label">Contact</span>
                                                        <span class="c-value"><?php echo htmlspecialchars($clinical['contact']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($clinical['detail'])): ?>
                                                    <div class="clinical-info-row">
                                                        <i class="fas fa-info-circle text-info"></i>
                                                        <span class="c-label">Detail</span>
                                                        <span class="c-value"><?php echo htmlspecialchars($clinical['detail']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="clinical-info-row mt-2 pt-2 border-top">
                                                    <a href="edit-clinical-info?id=<?php echo $clinical['clinical_info_id']; ?>&doctor_id=<?php echo $doctor_id; ?>" 
                                                       class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" onclick="deleteClinical(<?php echo $clinical['clinical_info_id']; ?>)" 
                                                       class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No clinical information found.</p>
                            <?php if ($ids != 0 && $ids != ''): ?>
                                <a href="clinical-info?id=<?php echo $doctor_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Clinical Info
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Emergency Status -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-ambulance"></i> Emergency Status</h5>
                </div>
                <div class="info-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <?php if ($doctor['emergency_status'] == 1): ?>
                                <span class="badge bg-warning text-dark p-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Emergency Not Available
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success p-2">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Available
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if ($doctor['estatus'] == 1): ?>
                                <?php if ($doctor['emergency_status'] == 1): ?>
                                    <button onclick="toggleEmergencyStatus(<?php echo $doctor['doctor_id']; ?>, 0)" 
                                            class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i> Enable
                                    </button>
                                <?php else: ?>
                                    <button onclick="toggleEmergencyStatus(<?php echo $doctor['doctor_id']; ?>, 1)" 
                                            class="btn btn-sm btn-warning">
                                        <i class="fas fa-times me-1"></i> Disable
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <i class="fas fa-ban me-1"></i> Doctor Inactive
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Registered Hospitals (inactive = 0) ===== -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-hospital-alt"></i> Registered Hospitals</h5>
                    <span class="badge bg-primary"><?php echo mysqli_num_rows($reg_hospitals_result); ?> Records</span>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($reg_hospitals_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Hospital / Clinic</th>
                                        <th>City</th>
                                        <th>Status</th>
                                        <th>Comment</th>
                                        <th>Added</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sr = 1; while ($rh = mysqli_fetch_assoc($reg_hospitals_result)): ?>
                                        <tr>
                                            <td><?php echo $sr++; ?></td>
                                            <td>
                                                <?php if ($rh['if_clinic'] == 1): ?>
                                                    <span class="badge bg-success">Personal Clinic</span>
                                                <?php elseif (!empty($rh['hospital_name'])): ?>
                                                    <i class="fas fa-hospital text-primary me-1"></i>
                                                    <a href="<?=BASE_URL?>admin/hospitals/detail?id=<?=$rh['hospital_id']?>"><?php echo htmlspecialchars($rh['hospital_name']); ?></a>
                                                <?php else: ?>
                                                    <span class="text-muted">Hospital not found</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($rh['city_name'] ?? '-'); ?></td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td><?php echo !empty($rh['comment']) ? nl2br(htmlspecialchars($rh['comment'])) : '-'; ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo !empty($rh['created_at']) ? date('d M Y', strtotime($rh['created_at'])) : '-'; ?>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" action="" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to remove this hospital from the doctor?');">
                                                    <input type="hidden" name="hospital_action" value="remove">
                                                    <input type="hidden" name="personal_clinic" value="<?php if((int)$rh['if_clinic']==1){echo 1;} ?>">
                                                    <input type="hidden" name="doctor_in_hosp_id" value="<?php echo (int)$rh['doctor_in_hosp_id']; ?>">
                                                    <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-times me-1"></i> Remove Hospital
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-hospital fa-3x text-muted mb-3"></i>
                            <p class="text-muted">This doctor is not registered in any hospital.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- ===== END Registered Hospitals ===== -->

            <!-- ===== NEW: Past Registered Hospitals (inactive = 1) ===== -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-history"></i> Past Registered Hospitals</h5>
                    <span class="badge bg-secondary"><?php echo mysqli_num_rows($past_hospitals_result); ?> Records</span>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($past_hospitals_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Hospital / Clinic</th>
                                        <th>City</th>
                                        <th>Status</th>
                                        <th>Comment</th>
                                        <th>Removed On</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sr = 1; while ($ph = mysqli_fetch_assoc($past_hospitals_result)): ?>
                                        <tr>
                                            <td><?php echo $sr++; ?></td>
                                            <td>
                                                <?php if ($ph['if_clinic'] == 1): ?>
                                                    <span class="badge bg-success">Personal Clinic</span>
                                                <?php elseif (!empty($ph['hospital_name'])): ?>
                                                    <i class="fas fa-hospital text-secondary me-1"></i>
                                                    <a href="<?=BASE_URL?>admin/hospitals/detail?id=<?=$ph['hospital_id']?>"><?php echo htmlspecialchars($ph['hospital_name']); ?></a>
                                                <?php else: ?>
                                                    <span class="text-muted">Hospital not found</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($ph['city_name'] ?? '-'); ?></td>
                                            <td><span class="badge bg-danger">Inactive</span></td>
                                            <td><?php echo !empty($ph['comment']) ? nl2br(htmlspecialchars($ph['comment'])) : '-'; ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo !empty($ph['updated_at']) ? date('d M Y', strtotime($ph['updated_at'])) : '-'; ?>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" action="" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to re-add this hospital to the doctor?');">
                                                    <input type="hidden" name="hospital_action" value="readd">
                                                    <input type="hidden" name="doctor_in_hosp_id" value="<?php echo (int)$ph['doctor_in_hosp_id']; ?>">
                                                    <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fas fa-undo me-1"></i> Readd
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No past hospitals found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- ===== END Past Registered Hospitals ===== -->

        </div>
    </div>

    <!-- ===== FEEDBACK SECTION (Full Width) ===== -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Patient Feedbacks</h5>
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-star me-1"></i> <?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?>
                        <span class="ms-1">(<?php echo $total_reviews; ?> reviews)</span>
                    </span>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                        <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <span class="feedback-name">
                                        <?php echo htmlspecialchars($feedback['commenter_name']); ?>
                                        <small class="text-muted ms-2"><?php echo htmlspecialchars($feedback['commenter_gmail']); ?></small>
                                    </span>
                                    <span class="feedback-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                </div>
                                <?php if (!empty($feedback['comment'])): ?>
                                    <p class="feedback-comment"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                                <?php endif; ?>
                                <div class="feedback-date">
                                    <i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($feedback['created_at'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No feedbacks found for this doctor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function deleteDoctor(doctorId) {
    if (confirm('Are you sure you want to delete this doctor? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + doctorId;
    }
}

function toggleEmergencyStatus(doctorId, newStatus) {
    var action = newStatus == 1 ? 'disable emergency services' : 'enable emergency services';
    if (confirm('Are you sure you want to ' + action + ' for this doctor?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                window.location.reload();
            }
        };
        xhr.send('toggle_emergency=' + doctorId + '&status=' + newStatus);
    }
}

function deleteClinical(clinicalId) {
    if (confirm('Are you sure you want to delete this clinical information? This action cannot be undone.')) {
        window.location.href = 'profile?id=<?php echo $doctor['doctor_id']; ?>&del_clinic_id=' + clinicalId;
    }
}

function showPassword(password) {
    alert('Password: ' + password);
}
</script>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>