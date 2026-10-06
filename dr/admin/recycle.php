<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php
$user_id = $_SESSION['user_id'];
// ============================================
// RESTORE FUNCTION
// ============================================
if (isset($_GET['restore_id']) && is_numeric($_GET['restore_id']) && isset($_GET['type'])) {
    $restore_id = (int)$_GET['restore_id'];

    $type = mysqli_real_escape_string($con, $_GET['type']);
    
    $update_query = "UPDATE users SET status = 1 WHERE user_id = $restore_id";
    
    if (mysqli_query($con, $update_query)) {

        $status_change_history = "INSERT INTO user_status_change_by
            SET 
            user_id = '". $restore_id ."',
            change_by = '". $user_id ."',
            status_to = 1
        ";
       mysqli_query($con, $status_change_history);


        $_SESSION['success_msg'] = ucfirst($type) . " restored successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    
    header('Location: ' . BASE_URL . 'admin/recycle.php');
    exit();
}

// ============================================
// PERMANENT DELETE FUNCTION
// ============================================
if (isset($_GET['permanent_delete_id']) && is_numeric($_GET['permanent_delete_id']) && isset($_GET['type'])) {
    // NOTE: $user_id (logged in admin) is kept untouched, so deleted user's id is stored in $delete_id
    $delete_id = (int)$_GET['permanent_delete_id'];
    $type = mysqli_real_escape_string($con, $_GET['type']);
    
    // Delete based on type
    if ($type == 'doctor') {
        // Get doctor details
        $doc_query = "SELECT doctor_id, doctor_pic FROM doctors WHERE user_id = $delete_id";
        $doc_result = mysqli_query($con, $doc_query);
        $doc = mysqli_fetch_assoc($doc_result);
        
        // Delete doctor_in_hospital
        mysqli_query($con, "DELETE FROM doctor_in_hospital WHERE doctor_id = " . $doc['doctor_id']);
        
        // Delete clinical_info
        $ci_query = "DELETE ci FROM clinical_info ci 
                     INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id 
                     WHERE dih.doctor_id = " . $doc['doctor_id'];
        mysqli_query($con, $ci_query);
        
        // Delete doctor
        mysqli_query($con, "DELETE FROM doctors WHERE doctor_id = " . $doc['doctor_id']);
        
        // Delete picture
        if (!empty($doc['doctor_pic'])) {
            $pic_path = BASE_PATH . "/admin/inc/uploads/doctors/" . $doc['doctor_pic'];
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
        }
    } elseif ($type == 'hospital') {
        // Get hospital details
        $hosp_query = "SELECT hospital_id, hospital_pic FROM hospitals WHERE user_id = $delete_id";
        $hosp_result = mysqli_query($con, $hosp_query);
        $hosp = mysqli_fetch_assoc($hosp_result);
        
        // Delete hospital_beds
        mysqli_query($con, "DELETE FROM hospital_beds WHERE hospital_id = " . $hosp['hospital_id']);
        
        // Delete hospital_facilities
        mysqli_query($con, "DELETE FROM hospital_facilities WHERE hospital_id = " . $hosp['hospital_id']);
        
        // Delete hospital
        mysqli_query($con, "DELETE FROM hospitals WHERE hospital_id = " . $hosp['hospital_id']);
        
        // Delete picture
        if (!empty($hosp['hospital_pic'])) {
            $pic_path = BASE_PATH . "/admin/inc/uploads/hospitals/" . $hosp['hospital_pic'];
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
        }
    } elseif ($type == 'lab') {
        // Get lab details
        $lab_query = "SELECT lab_id, lab_pic FROM laboratories WHERE user_id = $delete_id";
        $lab_result = mysqli_query($con, $lab_query);
        $lab = mysqli_fetch_assoc($lab_result);
        
        // Delete lab
        mysqli_query($con, "DELETE FROM laboratories WHERE lab_id = " . $lab['lab_id']);
        
        // Delete picture
        if (!empty($lab['lab_pic'])) {
            $pic_path = BASE_PATH . "/admin/inc/uploads/laboratories/" . $lab['lab_pic'];
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
        }
    } elseif ($type == 'blood_bank') {
        // Get blood bank details
        $bb_query = "SELECT bb_id, bb_pic FROM blood_bank WHERE user_id = $delete_id";
        $bb_result = mysqli_query($con, $bb_query);
        $bb = mysqli_fetch_assoc($bb_result);
        
        // Delete blood bank
        mysqli_query($con, "DELETE FROM blood_bank WHERE bb_id = " . $bb['bb_id']);
        
        // Delete picture
        if (!empty($bb['bb_pic'])) {
            $pic_path = BASE_PATH . "/admin/inc/uploads/blood-banks/" . $bb['bb_pic'];
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
        }
    }
    
    // Delete user
    mysqli_query($con, "DELETE FROM users WHERE user_id = $delete_id");
    
    $_SESSION['success_msg'] = ucfirst($type) . " permanently deleted!";
    header('Location: ' . BASE_URL . 'admin/recycle.php');
    exit();
}

// ============================================
// FETCH DELETED USERS
// ============================================

// Doctors (status = 0)
$doctors_query = "SELECT d.*, c.city_name, dct.type as specialization, u.user_id, u.status as ustatus
                  FROM doctors d
                  LEFT JOIN users u ON u.user_id = d.user_id
                  LEFT JOIN cities c ON d.city_id = c.city_id
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  WHERE u.status = 0
                  ORDER BY d.updated_at DESC, d.created_at DESC";
$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = mysqli_num_rows($doctors_result);

// Hospitals (status = 0)
$hospitals_query = "SELECT h.*, c.city_name, u.user_id, u.status as ustatus
                    FROM hospitals h
                    LEFT JOIN users u ON u.user_id = h.user_id
                    LEFT JOIN cities c ON h.city_id = c.city_id
                    WHERE u.status = 0
                    ORDER BY h.updated_at DESC, h.created_at DESC";
$hospitals_result = mysqli_query($con, $hospitals_query);
$total_hospitals = mysqli_num_rows($hospitals_result);

// Laboratories (status = 0)
$labs_query = "SELECT l.*, c.city_name, u.user_id, u.status as ustatus
               FROM laboratories l
               LEFT JOIN users u ON u.user_id = l.user_id
               LEFT JOIN cities c ON l.city_id = c.city_id
               WHERE u.status = 0
               ORDER BY l.updated_at DESC, l.created_at DESC";
$labs_result = mysqli_query($con, $labs_query);
$total_labs = mysqli_num_rows($labs_result);

// Blood Banks (status = 0)
$blood_banks_query = "SELECT bb.*, c.city_name, u.user_id, u.status as ustatus
                      FROM blood_bank bb
                      LEFT JOIN users u ON u.user_id = bb.user_id
                      LEFT JOIN cities c ON bb.city_id = c.city_id
                      WHERE u.status = 0
                      ORDER BY bb.updated_at DESC, bb.created_at DESC";
$blood_banks_result = mysqli_query($con, $blood_banks_query);
$total_blood_banks = mysqli_num_rows($blood_banks_result);
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/admin/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/recycle-admin.css">

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header-modern">
        <div class="page-header-content">
            <div>
                <h1><i class="fas fa-trash-alt me-2"></i> Recycle Bin</h1>
                <p>All deleted users - Restore or permanently delete</p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <span class="badge-total">
                    <i class="fas fa-user-md me-1"></i> <?php echo $total_doctors; ?> Doctors
                </span>
                <span class="badge-total">
                    <i class="fas fa-hospital me-1"></i> <?php echo $total_hospitals; ?> Hospitals
                </span>
                <span class="badge-total">
                    <i class="fas fa-flask me-1"></i> <?php echo $total_labs; ?> Labs
                </span>
                <span class="badge-total">
                    <i class="fas fa-tint me-1"></i> <?php echo $total_blood_banks; ?> Blood Banks
                </span>
                <a href="<?php echo BASE_URL; ?>admin" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- ===== ALERTS ===== -->
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert-custom alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert-custom alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
        </div>
    <?php endif; ?>

    <!-- ========================================== -->
    <!-- ===== DOCTORS SECTION ===== -->
    <!-- ========================================== -->
    <div class="recycle-section">
        <div class="section-header">
            <h5>
                <span class="icon icon-doctor"><i class="fas fa-user-md"></i></span>
                Deleted Doctors
                <span class="badge bg-danger ms-2"><?php echo $total_doctors; ?></span>
            </h5>
        </div>
        <div class="section-body">
            <?php if ($total_doctors > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Doctor</th>
                                <th>Specialization</th>
                                <th>City</th>
                                <th>Phone</th>
                                <th>Deleted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serial = 1; while ($doctor = mysqli_fetch_assoc($doctors_result)): ?>
                                <tr>
                                    <td><?php echo $serial++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($doctor['doctor_pic'])): ?>
                                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                                     style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
                                            <?php else: ?>
                                                <div style="width:32px; height:32px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($doctor['doctor_email']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['city_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['doctor_phone']); ?></td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($doctor['updated_at'] ?? $doctor['created_at'])); ?>
                                        <br><small class="text-muted"><?php echo date('h:i A', strtotime($doctor['updated_at'] ?? $doctor['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="?restore_id=<?php echo $doctor['user_id']; ?>&type=doctor" 
                                               class="btn-action btn-restore" 
                                               onclick="return confirm('Restore this doctor?')">
                                                <i class="fas fa-undo"></i> Restore
                                            </a>
                                            <a href="?permanent_delete_id=<?php echo $doctor['user_id']; ?>&type=doctor" 
                                               class="btn-action btn-delete-permanent" 
                                               onclick="return confirm('Permanently delete this doctor? This cannot be undone!')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-records">
                    <i class="fas fa-user-md"></i>
                    <p>No deleted doctors found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ===== HOSPITALS SECTION ===== -->
    <!-- ========================================== -->
    <div class="recycle-section">
        <div class="section-header">
            <h5>
                <span class="icon icon-hospital"><i class="fas fa-hospital"></i></span>
                Deleted Hospitals
                <span class="badge bg-danger ms-2"><?php echo $total_hospitals; ?></span>
            </h5>
        </div>
        <div class="section-body">
            <?php if ($total_hospitals > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Hospital</th>
                                <th>City</th>
                                <th>Phone</th>
                                <th>Deleted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serial = 1; while ($hospital = mysqli_fetch_assoc($hospitals_result)): ?>
                                <tr>
                                    <td><?php echo $serial++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($hospital['hospital_pic'])): ?>
                                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital['hospital_pic']; ?>" 
                                                     style="width:32px; height:32px; border-radius:8px; object-fit:cover;">
                                            <?php else: ?>
                                                <div style="width:32px; height:32px; border-radius:8px; background:#e2e8f0; display:flex; align-items:center; justify-content:center;">
                                                    <i class="fas fa-hospital text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($hospital['hospital_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($hospital['hospital_address'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($hospital['city_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($hospital['hospital_phone']); ?></td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($hospital['updated_at'] ?? $hospital['created_at'])); ?>
                                        <br><small class="text-muted"><?php echo date('h:i A', strtotime($hospital['updated_at'] ?? $hospital['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="?restore_id=<?php echo $hospital['user_id']; ?>&type=hospital" 
                                               class="btn-action btn-restore" 
                                               onclick="return confirm('Restore this hospital?')">
                                                <i class="fas fa-undo"></i> Restore
                                            </a>
                                            <a href="?permanent_delete_id=<?php echo $hospital['user_id']; ?>&type=hospital" 
                                               class="btn-action btn-delete-permanent" 
                                               onclick="return confirm('Permanently delete this hospital? This cannot be undone!')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-records">
                    <i class="fas fa-hospital"></i>
                    <p>No deleted hospitals found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ===== LABORATORIES SECTION ===== -->
    <!-- ========================================== -->
    <div class="recycle-section">
        <div class="section-header">
            <h5>
                <span class="icon icon-lab"><i class="fas fa-flask"></i></span>
                Deleted Laboratories
                <span class="badge bg-danger ms-2"><?php echo $total_labs; ?></span>
            </h5>
        </div>
        <div class="section-body">
            <?php if ($total_labs > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Laboratory</th>
                                <th>City</th>
                                <th>Phone</th>
                                <th>Deleted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serial = 1; while ($lab = mysqli_fetch_assoc($labs_result)): ?>
                                <tr>
                                    <td><?php echo $serial++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($lab['lab_pic'])): ?>
                                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo $lab['lab_pic']; ?>" 
                                                     style="width:32px; height:32px; border-radius:8px; object-fit:cover;">
                                            <?php else: ?>
                                                <div style="width:32px; height:32px; border-radius:8px; background:#e2e8f0; display:flex; align-items:center; justify-content:center;">
                                                    <i class="fas fa-flask text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($lab['lab_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($lab['lab_email'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($lab['city_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($lab['lab_phone']); ?></td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($lab['updated_at'] ?? $lab['created_at'])); ?>
                                        <br><small class="text-muted"><?php echo date('h:i A', strtotime($lab['updated_at'] ?? $lab['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="?restore_id=<?php echo $lab['user_id']; ?>&type=lab" 
                                               class="btn-action btn-restore" 
                                               onclick="return confirm('Restore this laboratory?')">
                                                <i class="fas fa-undo"></i> Restore
                                            </a>
                                            <a href="?permanent_delete_id=<?php echo $lab['user_id']; ?>&type=lab" 
                                               class="btn-action btn-delete-permanent" 
                                               onclick="return confirm('Permanently delete this laboratory? This cannot be undone!')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-records">
                    <i class="fas fa-flask"></i>
                    <p>No deleted laboratories found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ===== BLOOD BANKS SECTION ===== -->
    <!-- ========================================== -->
    <div class="recycle-section">
        <div class="section-header">
            <h5>
                <span class="icon icon-blood"><i class="fas fa-tint"></i></span>
                Deleted Blood Banks
                <span class="badge bg-danger ms-2"><?php echo $total_blood_banks; ?></span>
            </h5>
        </div>
        <div class="section-body">
            <?php if ($total_blood_banks > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Blood Bank</th>
                                <th>City</th>
                                <th>Contact</th>
                                <th>Deleted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serial = 1; while ($bb = mysqli_fetch_assoc($blood_banks_result)): ?>
                                <tr>
                                    <td><?php echo $serial++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($bb['bb_pic'])): ?>
                                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo $bb['bb_pic']; ?>" 
                                                     style="width:32px; height:32px; border-radius:8px; object-fit:cover;">
                                            <?php else: ?>
                                                <div style="width:32px; height:32px; border-radius:8px; background:#e2e8f0; display:flex; align-items:center; justify-content:center;">
                                                    <i class="fas fa-tint text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($bb['bb_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($bb['bb_contact'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($bb['city_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($bb['bb_contact']); ?></td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($bb['updated_at'] ?? $bb['created_at'])); ?>
                                        <br><small class="text-muted"><?php echo date('h:i A', strtotime($bb['updated_at'] ?? $bb['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="?restore_id=<?php echo $bb['user_id']; ?>&type=blood_bank" 
                                               class="btn-action btn-restore" 
                                               onclick="return confirm('Restore this blood bank?')">
                                                <i class="fas fa-undo"></i> Restore
                                            </a>
                                            <a href="?permanent_delete_id=<?php echo $bb['user_id']; ?>&type=blood_bank" 
                                               class="btn-action btn-delete-permanent" 
                                               onclick="return confirm('Permanently delete this blood bank? This cannot be undone!')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-records">
                    <i class="fas fa-tint"></i>
                    <p>No deleted blood banks found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>