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

<style>
:root {
    --primary: #4facfe;
    --primary-dark: #0d6efd;
    --accent: #00f2fe;
    --text: #1e293b;
    --muted: #64748b;
    --border: #e2e8f0;
    --bg: #f8fafc;
    --card: #ffffff;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --info: #0ea5e9;
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

.page-header-left h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 4px;
}

.page-header-left p {
    margin: 0;
    opacity: 0.85;
    font-size: 0.95rem;
}

.page-header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-action-header {
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    border: none;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
}

.btn-action-header:hover {
    background: rgba(255,255,255,0.25);
    color: white;
    transform: translateY(-2px);
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

/* ===== FILTER SECTION ===== */
.filter-section {
    background: white;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border);
    margin-bottom: 24px;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-group label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 4px;
    display: block;
}

.filter-group .form-control {
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    padding: 8px 14px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.filter-group .form-control:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220,38,38,0.12);
}

.btn-filter {
    background: linear-gradient(135deg, #dc2626, #991b1b);
    color: white;
    border: none;
    padding: 9px 24px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(220,38,38,0.3);
    color: white;
}

.btn-reset {
    background: #f1f5f9;
    color: #64748b;
    border: 2px solid #e2e8f0;
    padding: 9px 20px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background: #e2e8f0;
    color: #1e293b;
}

/* ===== TABLE ===== */
.table-responsive {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border);
}

.table {
    margin: 0;
}

.table thead {
    background: #f8fafc;
}

.table th {
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: var(--muted);
    padding: 14px 16px;
    border-bottom: 2px solid var(--border);
}

.table td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
}

/* ===== BADGES ===== */
.badge-status {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-status.deleted { background: #fee2e2; color: #991b1b; }

.badge-type {
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-type.hospital { background: #dbeafe; color: #1e40af; }
.badge-type.clinic { background: #d1fae5; color: #065f46; }

/* ===== BUTTONS ===== */
.btn-action {
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    border: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-action:hover {
    transform: translateY(-1px);
}

.btn-restore { background: #d1fae5; color: #065f46; }
.btn-restore:hover { background: #a7f3d0; }

.btn-delete-permanent { background: #fee2e2; color: #991b1b; }
.btn-delete-permanent:hover { background: #fecaca; }

/* ===== PAGINATION ===== */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding: 16px 24px;
    background: white;
    border-radius: 0 0 16px 16px;
    border-top: 1px solid var(--border);
}

.pagination {
    margin: 0;
    gap: 4px;
}

.pagination .page-link {
    border: none;
    border-radius: 8px;
    color: var(--text);
    padding: 8px 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    background: #dc2626;
    color: white;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #dc2626, #991b1b);
    color: white;
}

.pagination .page-item.disabled .page-link {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-info {
    color: var(--muted);
    font-size: 0.85rem;
}

.no-records {
    text-align: center;
    padding: 40px 20px;
    color: var(--muted);
}

.no-records i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 12px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .content-wrapper { padding: 16px; }
    .filter-row { flex-direction: column; }
    .filter-group { min-width: 100%; }
}

@media (max-width: 768px) {
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    .page-header-actions {
        width: 100%;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .pagination-container {
        flex-direction: column;
        text-align: center;
    }
}
</style>

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