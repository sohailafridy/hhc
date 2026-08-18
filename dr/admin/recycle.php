<?php include '../config.php'; ?>

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
    $delete_id = (int)$_GET['permanent_delete_id'];
    $type = mysqli_real_escape_string($con, $_GET['type']);
    
    // Get entity_id first
    $entity_query = "SELECT entity_id FROM entities WHERE entity_id = $delete_id";
    $entity_result = mysqli_query($con, $entity_query);
    $entity_row = mysqli_fetch_assoc($entity_result);
    $entity_id = $entity_row['entity_id'];
    
    // Delete based on type
    if ($type == 'doctor') {
        // Get doctor details
        $doc_query = "SELECT doctor_id, doctor_pic FROM doctors WHERE entity_id = $entity_id";
        $doc_result = mysqli_query($con, $doc_query);
        $doc = mysqli_fetch_assoc($doc_result);
        
        // Delete doctor_in_hospital
        mysqli_query($con, "DELETE FROM doctor_in_hospital WHERE doctor_id = " . $doc['doctor_id']);
        
        // Delete clinical_info
        $ci_query = "DELETE ci FROM clinical_info ci 
                     INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id 
                     WHERE dih.doctor_id = " . $doc['doctor_id'];
        mysqli_query($con, $ci_query);
        
        // Delete user
        mysqli_query($con, "DELETE FROM users WHERE user_id = (SELECT user_id FROM doctors WHERE doctor_id = " . $doc['doctor_id'] . ")");
        
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
        $hosp_query = "SELECT hospital_id, hospital_pic FROM hospitals WHERE entity_id = $entity_id";
        $hosp_result = mysqli_query($con, $hosp_query);
        $hosp = mysqli_fetch_assoc($hosp_result);
        
        // Delete hospital_beds
        mysqli_query($con, "DELETE FROM hospital_beds WHERE hospital_id = " . $hosp['hospital_id']);
        
        // Delete hospital_facilities
        mysqli_query($con, "DELETE FROM hospital_facilities WHERE hospital_id = " . $hosp['hospital_id']);
        
        // Delete user
        mysqli_query($con, "DELETE FROM users WHERE user_id = (SELECT user_id FROM hospitals WHERE hospital_id = " . $hosp['hospital_id'] . ")");
        
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
        $lab_query = "SELECT lab_id, lab_pic FROM laboratories WHERE entity_id = $entity_id";
        $lab_result = mysqli_query($con, $lab_query);
        $lab = mysqli_fetch_assoc($lab_result);
        
        // Delete user
        mysqli_query($con, "DELETE FROM users WHERE user_id = (SELECT user_id FROM laboratories WHERE lab_id = " . $lab['lab_id'] . ")");
        
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
        $bb_query = "SELECT bb_id, bb_pic FROM blood_bank WHERE entity_id = $entity_id";
        $bb_result = mysqli_query($con, $bb_query);
        $bb = mysqli_fetch_assoc($bb_result);
        
        // Delete user
        mysqli_query($con, "DELETE FROM users WHERE user_id = (SELECT user_id FROM blood_bank WHERE bb_id = " . $bb['bb_id'] . ")");
        
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
    
    // Delete entity
    mysqli_query($con, "DELETE FROM entities WHERE entity_id = $entity_id");
    
    $_SESSION['success_msg'] = ucfirst($type) . " permanently deleted!";
    header('Location: ' . BASE_URL . 'admin/recycle.php');
    exit();
}

// ============================================
// FETCH DELETED ENTITIES
// ============================================

// Doctors (status = 0)
$doctors_query = "SELECT d.*, c.city_name, dct.type as specialization, e.entity_id, e.status as estatus
                  FROM doctors d
                  LEFT JOIN entities e ON d.entity_id = e.entity_id
                  LEFT JOIN cities c ON d.city_id = c.city_id
                  LEFT JOIN users u ON u.user_id = d.user_id
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  WHERE u.status = 0
                  ORDER BY d.updated_at DESC, d.created_at DESC";
$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = mysqli_num_rows($doctors_result);

// Hospitals (status = 0)
$hospitals_query = "SELECT h.*, c.city_name, e.entity_id, e.status as estatus
                    FROM hospitals h
                    LEFT JOIN entities e ON h.entity_id = e.entity_id
                    LEFT JOIN users u ON u.user_id = h.user_id
                    LEFT JOIN cities c ON h.city_id = c.city_id
                    WHERE u.status = 0
                    ORDER BY h.updated_at DESC, h.created_at DESC";
$hospitals_result = mysqli_query($con, $hospitals_query);
$total_hospitals = mysqli_num_rows($hospitals_result);

// Laboratories (status = 0)
$labs_query = "SELECT l.*, c.city_name, e.entity_id, e.status as estatus
               FROM laboratories l
               LEFT JOIN entities e ON l.entity_id = e.entity_id
               LEFT JOIN cities c ON l.city_id = c.city_id
               LEFT JOIN users u ON u.user_id = l.user_id
               WHERE u.status = 0
               ORDER BY l.updated_at DESC, l.created_at DESC";
$labs_result = mysqli_query($con, $labs_query);
$total_labs = mysqli_num_rows($labs_result);

// Blood Banks (status = 0)
$blood_banks_query = "SELECT bb.*, c.city_name, e.entity_id, e.status as estatus
                      FROM blood_bank bb
                      LEFT JOIN entities e ON bb.entity_id = e.entity_id
                      LEFT JOIN cities c ON bb.city_id = c.city_id
                      LEFT JOIN users u ON u.user_id = bb.user_id
                      WHERE u.status = 0
                      ORDER BY bb.updated_at DESC, bb.created_at DESC";
$blood_banks_result = mysqli_query($con, $blood_banks_query);
$total_blood_banks = mysqli_num_rows($blood_banks_result);
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/admin/inc/nav.php'; ?>

<style>
:root {
    --primary: #4f46e5;
    --primary-light: #818cf8;
    --success: #22c55e;
    --danger: #ef4444;
    --warning: #f59e0b;
    --text: #0f172a;
    --text-light: #64748b;
    --border: #e2e8f0;
    --bg: #f1f5f9;
    --card: #ffffff;
}

.content-wrapper {
    background: var(--bg);
    min-height: 100vh;
    padding: 24px 32px 60px;
}

/* ===== PAGE HEADER ===== */
.page-header-modern {
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    border-radius: 20px;
    padding: 28px 35px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.page-header-modern::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -15%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.06);
    border-radius: 50%;
}

.page-header-modern::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}

.page-header-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.page-header-content h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 4px;
}

.page-header-content p {
    margin: 0;
    opacity: 0.85;
    font-size: 0.95rem;
}

.page-header-content .badge-total {
    background: rgba(255,255,255,0.2);
    padding: 6px 20px;
    border-radius: 50px;
    font-size: 0.9rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: rgba(255,255,255,0.15);
    color: white;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: rgba(255,255,255,0.25);
    color: white;
}

/* ===== ALERT ===== */
.alert-custom {
    padding: 12px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.alert-custom.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #22c55e;
}

.alert-custom.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

/* ===== SECTION ===== */
.recycle-section {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border);
    margin-bottom: 30px;
}

.recycle-section .section-header {
    padding: 16px 24px;
    background: #f8fafc;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.recycle-section .section-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.recycle-section .section-header h5 .icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
}

.icon-doctor { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
.icon-hospital { background: linear-gradient(135deg, #0ea5e9, #3b82f6); }
.icon-lab { background: linear-gradient(135deg, #22c55e, #16a34a); }
.icon-blood { background: linear-gradient(135deg, #ef4444, #dc2626); }

.section-body {
    padding: 16px 24px;
}

/* ===== TABLE ===== */
.table-responsive {
    overflow-x: auto;
}

.table {
    margin: 0;
    font-size: 0.9rem;
}

.table thead {
    background: #f8fafc;
}

.table th {
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--text-light);
    padding: 10px 14px;
    border-bottom: 2px solid var(--border);
}

.table td {
    padding: 10px 14px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
}

/* ===== BADGES ===== */
.badge-deleted {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    background: #fee2e2;
    color: #991b1b;
}

/* ===== BUTTONS ===== */
.btn-action {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    border: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-decoration: none;
}

.btn-action:hover {
    transform: translateY(-1px);
}

.btn-restore { background: #d1fae5; color: #065f46; }
.btn-restore:hover { background: #a7f3d0; }

.btn-delete-permanent { background: #fee2e2; color: #991b1b; }
.btn-delete-permanent:hover { background: #fecaca; }

.btn-view { background: #dbeafe; color: #1e40af; }
.btn-view:hover { background: #bfdbfe; }

/* ===== NO RECORDS ===== */
.no-records {
    text-align: center;
    padding: 30px 20px;
    color: var(--text-light);
}

.no-records i {
    font-size: 2.5rem;
    color: #cbd5e1;
    margin-bottom: 8px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .content-wrapper { padding: 16px; }
}

@media (max-width: 768px) {
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    .section-body { padding: 12px 16px; }
    .table-responsive { font-size: 0.8rem; }
    .table th, .table td { padding: 8px 10px; }
}

@media (max-width: 480px) {
    .page-header-content h1 { font-size: 1.4rem; }
    .btn-action { padding: 3px 8px; font-size: 0.7rem; }
}
</style>

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header-modern">
        <div class="page-header-content">
            <div>
                <h1><i class="fas fa-trash-alt me-2"></i> Recycle Bin</h1>
                <p>All deleted entities - Restore or permanently delete</p>
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