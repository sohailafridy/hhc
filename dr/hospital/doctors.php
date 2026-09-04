<?php
include '../config.php';

// ============================================
// HOSPITAL AUTHENTICATION
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id = $_SESSION['user_id'];

$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];

// ============================================
// DELETE DOCTOR (from hospital)
// ============================================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $del_doctor_id = $_GET['delete_id'];
    
    // Delete from doctor_in_hospital for this hospital
    $delete_query = "DELETE FROM doctor_in_hospital WHERE doctor_id = $del_doctor_id AND hospital_id = $hospital_id";
    if (mysqli_query($con, $delete_query)) {
        $_SESSION['success_msg'] = "Doctor removed from your hospital successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    header('Location: ' . BASE_URL . 'hospital/doctors.php');
    exit();
}

// ============================================
// GET DOCTORS - JOIN WITH doctor_in_hospital
// ============================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// ============================================
// FIX: JOIN doctor_in_hospital to get doctors for this hospital
// ============================================
$where = "dih.hospital_id = $hospital_id AND d.approve = 1 AND u.status = 1";

if (!empty($search)) {
    $where .= " AND (d.doctor_name LIKE '%$search%' OR dct.type LIKE '%$search%')";
}

// Count total
$count_query = "SELECT COUNT(DISTINCT d.doctor_id) as total 
                FROM doctor_in_hospital dih
                LEFT JOIN doctors d ON dih.doctor_id = d.doctor_id
                LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                LEFT JOIN users u ON u.user_id = d.user_id
                WHERE $where";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch doctors
$query = "SELECT DISTINCT d.*, dct.type as specialization, u.status as ustatus
          FROM doctor_in_hospital dih
          LEFT JOIN doctors d ON dih.doctor_id = d.doctor_id
          LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
          LEFT JOIN users u ON u.user_id = d.user_id
          WHERE $where
          ORDER BY d.created_at DESC
          LIMIT $offset, $per_page";
$result = mysqli_query($con, $query);
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/hospital/inc/nav.php'; ?>

<style>
.content-wrapper {
    background: #f8fafc;
    min-height: 100vh;
    padding: 24px 32px 60px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}

.page-header h4 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.page-header h4 i {
    color: #4facfe;
}

.btn-add {
    background: linear-gradient(135deg, #4facfe, #0d6efd);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(13,110,253,0.3);
    color: white;
}

.filter-section {
    background: white;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

.filter-section .form-control {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    transition: all 0.3s ease;
}

.filter-section .form-control:focus {
    border-color: #4facfe;
    box-shadow: 0 0 0 3px rgba(79,172,254,0.08);
}

.table-responsive {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.table {
    margin: 0;
}

.table th {
    background: #f8fafc;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: #64748b;
    padding: 12px 16px;
    border-bottom: 2px solid #e2e8f0;
}

.table td {
    padding: 10px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.badge-status {
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-status.active { background: #d1fae5; color: #065f46; }
.badge-status.inactive { background: #fee2e2; color: #991b1b; }

.btn-action {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    border: none;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-action.view { background: #dbeafe; color: #1e40af; }
.btn-action.view:hover { background: #bfdbfe; }

.btn-action.edit { background: #fef3c7; color: #92400e; }
.btn-action.edit:hover { background: #fde68a; }

.btn-action.delete { background: #fee2e2; color: #991b1b; }
.btn-action.delete:hover { background: #fecaca; }

.pagination-container {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border-top: 1px solid #e2e8f0;
}

.pagination {
    margin: 0;
    gap: 3px;
}

.pagination .page-link {
    border: none;
    border-radius: 6px;
    color: #1e293b;
    padding: 6px 14px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    background: #4facfe;
    color: white;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #4facfe, #0d6efd);
    color: white;
}

.pagination .page-item.disabled .page-link {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-info {
    font-size: 13px;
    color: #64748b;
}

@media (max-width: 768px) {
    .content-wrapper { padding: 16px; }
    .page-header { flex-direction: column; align-items: stretch; }
    .pagination-container { flex-direction: column; text-align: center; }
}

@media (max-width: 480px) {
    .filter-section .row { flex-direction: column; }
    .filter-section .col-md-10, .filter-section .col-md-2 { width: 100%; }
    .btn-action { padding: 3px 8px; font-size: 0.7rem; }
}
</style>

<div class="content-wrapper">

    <div class="page-header">
        <h4><i class="fas fa-user-md me-2"></i> My Doctors</h4>
        <div>
            <a href="<?php echo BASE_URL; ?>hospital/doctor-add" class="btn-add">
                <i class="fas fa-plus me-2"></i> Add Doctor
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="filter-section">
        <form method="GET" class="row g-2">
            <div class="col-md-10">
                <input type="text" class="form-control" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by name or specialization...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Doctor</th>
                    <th>Specialization</th>
                    <th>Phone</th>
                    <th>Experience</th>
                    <th>Status</th>
                    <th style="width:150px;">Actions</th>
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
                            <td><?php echo htmlspecialchars($doctor['doctor_phone']); ?></td>
                            <td><?php echo $doctor['experience_years']; ?> yrs</td>
                            <td>
                                <span class="badge-status <?php echo $doctor['ustatus'] == 1 ? 'active' : 'inactive'; ?>">
                                    <?php echo $doctor['ustatus'] == 1 ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?php echo BASE_URL; ?>hospital/doctor-detail?id=<?php echo $doctor['doctor_id']; ?>" 
                                       class="btn-action view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>hospital/doctor-add?id=<?php echo $doctor['doctor_id']; ?>" 
                                       class="btn-action edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete_id=<?php echo $doctor['doctor_id']; ?>" 
                                       class="btn-action delete" title="Remove from this hospital"
                                       onclick="return confirm('Are you sure you want to remove this doctor from your hospital?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-user-md fa-2x mb-2 d-block" style="color:#cbd5e1;"></i>
                            No doctors found for your hospital.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

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
                        <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
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
                        <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="pagination-info">
                Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_records); ?> of <?php echo $total_records; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>