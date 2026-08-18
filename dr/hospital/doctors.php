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
// DELETE DOCTOR
// ============================================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    $delete_query = "UPDATE users SET status = 0 WHERE user_id = $del_id";
    if (mysqli_query($con, $delete_query)) {
        $status_change_history = "INSERT INTO user_status_change_by
            SET 
            user_id = '". $del_id ."',
            change_by = '". $user_id ."',
            status_to = 0
        ";
        mysqli_query($con, $status_change_history);

        $_SESSION['success_msg'] = "Doctor removed successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    header('Location: ' . BASE_URL . 'hospital/doctors.php');
    exit();
}

// ============================================
// GET DOCTORS
// ============================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = "d.hospital_id = $hospital_id AND d.approve = 1";
$where .= " AND u.status=1";
if (!empty($search)) {
    $where .= " AND (d.doctor_name LIKE '%$search%' OR dct.type LIKE '%$search%')";
}

 $count_query = "SELECT COUNT(*) as total FROM doctors d 
                LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                LEFT JOIN users u ON u.user_id = d.user_id
                WHERE $where";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

$query = "SELECT d.*, dct.type as specialization, u.status as ustatus
          FROM doctors d
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

.table-responsive {
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.table th {
    background: #f8fafc;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: #64748b;
    padding: 12px 16px;
}

.table td {
    padding: 10px 16px;
    vertical-align: middle;
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
}

.btn-action.edit { background: #fef3c7; color: #92400e; }
.btn-action.delete { background: #fee2e2; color: #991b1b; }

.pagination-container {
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border-top: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
    .content-wrapper { padding: 16px; }
    .page-header { flex-direction: column; align-items: stretch; }
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
        <div class="alert alert-success"><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
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
                    <th>#</th>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>Phone</th>
                    <th>Experience</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $serial = $offset + 1; ?>
                    <?php while ($doctor = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $serial++; ?></td>
                            <td><strong><?php echo htmlspecialchars($doctor['doctor_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></td>
                            <td><?php echo htmlspecialchars($doctor['doctor_phone']); ?></td>
                            <td><?php echo $doctor['experience_years']; ?> yrs</td>
                            <td>
                                <span class="badge-status <?php echo $doctor['ustatus'] == 1 ? 'active' : 'inactive'; ?>">
                                    <?php echo $doctor['ustatus'] == 1 ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>hospital/doctor-detail?id=<?php echo $doctor['doctor_id']; ?>" 
                                   class="btn-action view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>hospital/doctor-add?id=<?php echo $doctor['doctor_id']; ?>" 
                                   class="btn-action edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete_id=<?php echo $doctor['user_id']; ?>" 
                                   class="btn-action delete" title="Remove"
                                   onclick="return confirm('Are you sure you want to remove this doctor?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No doctors found.</td>
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
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Prev</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div>
                <small class="text-muted">Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_records); ?> of <?php echo $total_records; ?></small>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>