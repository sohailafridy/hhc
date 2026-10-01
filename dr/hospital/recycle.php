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
$hospital_name = $hospital_data['hospital_name'];

// ============================================
// RESTORE DOCTOR
// ============================================

if (isset($_GET['restore_id']) && is_numeric($_GET['restore_id'])) {
    $restore_id = $_GET['restore_id'];
    $restore_query = "UPDATE users SET status = 1 WHERE user_id = $restore_id";
    if (mysqli_query($con, $restore_query)) {
        $status_change_history = "INSERT INTO user_status_change_by
            SET 
            user_id = '". $restore_id ."',
            change_by = '". $user_id ."',
            status_to = 1
        ";
        mysqli_query($con, $status_change_history);

        $_SESSION['success_msg'] = "Doctor removed successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    header('Location: ' . BASE_URL . 'hospital/recycle.php');
    exit();
}


// ============================================
// PERMANENTLY DELETE DOCTOR
// ============================================
if (isset($_GET['permanent_delete_id']) && is_numeric($_GET['permanent_delete_id'])) {
    $delete_id = (int)$_GET['permanent_delete_id'];
    
    // Verify doctor belongs to this hospital
    $check_query = "SELECT doctor_id, doctor_pic FROM doctors WHERE doctor_id = $delete_id AND hospital_id = $hospital_id";
    $check_result = mysqli_query($con, $check_query);
    $doctor_data = mysqli_fetch_assoc($check_result);
    
    if ($doctor_data) {
        // Get entity_id
        $entity_query = "SELECT entity_id FROM doctors WHERE doctor_id = $delete_id";
        $entity_result = mysqli_query($con, $entity_query);
        $entity_row = mysqli_fetch_assoc($entity_result);
        $entity_id = $entity_row['entity_id'];
        
        // Delete from entities
        mysqli_query($con, "DELETE FROM entities WHERE entity_id = $entity_id");
        
        // Delete from doctor_in_hospital
        mysqli_query($con, "DELETE FROM doctor_in_hospital WHERE doctor_id = $delete_id");
        
        // Delete from clinical_info (via join)
        $ci_query = "DELETE ci FROM clinical_info ci 
                     INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id 
                     WHERE dih.doctor_id = $delete_id";
        mysqli_query($con, $ci_query);
        
        // Delete from users
        $user_query = "DELETE FROM users WHERE user_id = (SELECT user_id FROM doctors WHERE doctor_id = $delete_id)";
        mysqli_query($con, $user_query);
        
        // Delete doctor
        $delete_query = "DELETE FROM doctors WHERE doctor_id = $delete_id";
        if (mysqli_query($con, $delete_query)) {
            // Delete picture
            if (!empty($doctor_data['doctor_pic'])) {
                $pic_path = BASE_PATH . "/admin/inc/uploads/doctors/" . $doctor_data['doctor_pic'];
                if (file_exists($pic_path)) {
                    unlink($pic_path);
                }
            }
            $_SESSION['success_msg'] = "Doctor permanently deleted!";
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
        }
    } else {
        $_SESSION['error_msg'] = "You don't have permission to delete this doctor.";
    }
    
    header('Location: ' . BASE_URL . 'hospital/recycle.php');
    exit();
}

// ============================================
// FILTERS & PAGINATION
// ============================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build WHERE clause - Only show doctors with status = 0 (inactive)
$where = "d.hospital_id = $hospital_id AND u.status = 0";

if (!empty($search)) {
    $where .= " AND (d.doctor_name LIKE '%$search%' OR d.doctor_email LIKE '%$search%' OR d.doctor_phone LIKE '%$search%')";
}

// Count total
$count_query = "SELECT COUNT(*) as total 
                 FROM doctors d
                 LEFT JOIN entities e ON d.entity_id = e.entity_id
                 LEFT JOIN users u ON u.user_id = d.user_id
                 WHERE $where";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch deleted doctors
$query = "SELECT d.*, 
                 e.status as estatus,
                 e.reference as ref,
                 dct.type as specialization,
                 c.city_name
          FROM doctors d
          LEFT JOIN entities e ON d.entity_id = e.entity_id
          LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = d.user_id
          WHERE $where
          ORDER BY d.updated_at DESC, d.created_at DESC
          LIMIT $offset, $per_page";

$result = mysqli_query($con, $query);
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-recycle-admin.css">

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header-modern">
        <div class="page-header-content">
            <div class="page-header-left">
                <h1>
                    <i class="fas fa-trash-alt me-2"></i> Recycle Bin
                    <span class="badge bg-light text-dark ms-2"><?php echo $total_records; ?></span>
                </h1>
                <p>Deleted/inactive doctors from <?php echo htmlspecialchars($hospital_name); ?></p>
            </div>
            <div class="page-header-actions">
                <a href="<?php echo BASE_URL; ?>hospital/doctors.php" class="btn-action-header">
                    <i class="fas fa-arrow-left"></i> Back to Doctors
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

    <!-- ===== FILTER SECTION ===== -->
    <div class="filter-section">
        <form method="GET" action="">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Search</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search by name, email or phone...">
                </div>
                <div class="filter-group" style="flex: 0 0 auto; display: flex; gap: 8px;">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="<?php echo BASE_URL; ?>hospital/recycle.php" class="btn-reset">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <!-- ===== TABLE ===== -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Doctor</th>
                    <th>Specialization</th>
                    <th>Contact</th>
                    <th>Type</th>
                    <th>Deleted Date</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $serial = $offset + 1; ?>
                    <?php while ($doctor = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $serial++; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($doctor['doctor_pic'])): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                             alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" 
                                             style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 35px; height: 35px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
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
                            <td><?php echo htmlspecialchars($doctor['doctor_phone']); ?></td>
                            <td>
                                <span class="badge-type <?php echo $doctor['doctor_type'] == 1 ? 'hospital' : 'clinic'; ?>">
                                    <?php echo $doctor['doctor_type'] == 1 ? 'Hospital' : 'Clinic'; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo date('d M Y', strtotime($doctor['updated_at'] ?? $doctor['created_at'])); ?>
                                <br>
                                <small class="text-muted"><?php echo date('h:i A', strtotime($doctor['updated_at'] ?? $doctor['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <!-- Restore Button -->
                                    <a href="?restore_id=<?php echo $doctor['user_id']; ?>" 
                                       class="btn-action btn-restore" 
                                       title="Restore Doctor"
                                       onclick="return confirm('Are you sure you want to restore this doctor?')">
                                        <i class="fas fa-undo"></i> Restore
                                    </a>
                                    <!-- Permanent Delete Button -->
                                    <a href="?permanent_delete_id=<?php echo $doctor['user_id']; ?>" 
                                       class="btn-action btn-delete-permanent" 
                                       title="Permanently Delete"
                                       onclick="return confirm('Are you sure you want to permanently delete this doctor? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="no-records">
                                <i class="fas fa-trash-alt"></i>
                                <h5>Recycle Bin is Empty</h5>
                                <p>No deleted or inactive doctors found.</p>
                                <a href="<?php echo BASE_URL; ?>hospital/doctors.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Doctors
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== PAGINATION ===== -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="pagination-info">
                Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_records); ?> of <?php echo $total_records; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>