<?php
include '../config.php';

// ============================================
// HOSPITAL AUTHENTICATION
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$hospital_query  = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data   = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = (int)$hospital_data['hospital_id'];

// ============================================
// REMOVE DOCTOR (Soft Delete — inactive = 1)
// ============================================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $del_doctor_id = (int) $_GET['delete_id'];

    $check_query = "SELECT doctor_in_hosp_id, if_clinic 
                    FROM doctor_in_hospital 
                    WHERE doctor_id = $del_doctor_id AND hospital_id = $hospital_id";
    $check_result = mysqli_query($con, $check_query);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $row       = mysqli_fetch_assoc($check_result);
        $dih_id    = (int) $row['doctor_in_hosp_id'];
        $if_clinic = (int) $row['if_clinic'];

        $delete_query = "UPDATE doctor_in_hospital 
                         SET inactive = 1, updated_at = NOW() 
                         WHERE doctor_in_hosp_id = $dih_id 
                         AND doctor_id = $del_doctor_id 
                         AND hospital_id = $hospital_id";

        if (mysqli_query($con, $delete_query)) {
            if ($if_clinic == 1) {
                mysqli_query($con, "UPDATE doctors SET clinic_status = 1 WHERE doctor_id = $del_doctor_id");
            }
            $_SESSION['success_msg'] = "Doctor removed from your hospital successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
        }
    } else {
        $_SESSION['error_msg'] = "Doctor not found in your hospital.";
    }

    header('Location: ' . BASE_URL . 'hospital/doctors.php');
    exit();
}

// ============================================
// GET DOCTORS - JOIN WITH doctor_in_hospital
// ============================================
$search   = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset   = ($page - 1) * $per_page;

$where = "dih.hospital_id = $hospital_id AND d.approve = 1 AND u.status = 1 AND dih.inactive = 0";

if (!empty($search)) {
    $where .= " AND (d.doctor_name LIKE '%$search%' OR dct.type LIKE '%$search%')";
}

// Count total
$count_query = "SELECT COUNT(DISTINCT d.doctor_id) as total 
                FROM doctor_in_hospital dih
                INNER JOIN doctors d ON dih.doctor_id = d.doctor_id
                LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                LEFT JOIN users u ON u.user_id = d.user_id
                WHERE $where";
$count_result  = mysqli_query($con, $count_query);
$total_records = (int)mysqli_fetch_assoc($count_result)['total'];
$total_pages   = (int)ceil($total_records / $per_page);

// Fetch doctors
$doctors_query = "SELECT d.*, dct.type as specialization, u.status as ustatus
                  FROM doctor_in_hospital dih
                  INNER JOIN doctors d ON dih.doctor_id = d.doctor_id
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  LEFT JOIN users u ON u.user_id = d.user_id
                  WHERE $where
                  ORDER BY d.created_at DESC
                  LIMIT $offset, $per_page";
$doctors_result = mysqli_query($con, $doctors_query);

if (!$doctors_result) {
    die("SQL Error: " . mysqli_error($con));
}

// IMPORTANT: includes se PEHLE data fetch kar lein
$doctors_list = mysqli_fetch_all($doctors_result, MYSQLI_ASSOC);
mysqli_free_result($doctors_result);
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-doctors-admin.css">

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
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
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
            <?php if (count($doctors_list) > 0): ?>
                <?php $serial = $offset + 1; ?>
                <?php foreach ($doctors_list as $doctor): ?>
                    <tr>
                        <td><?php echo $serial++; ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($doctor['doctor_pic'])): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo htmlspecialchars($doctor['doctor_pic']); ?>"
                                         alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>"
                                         style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:32px; height:32px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold">Dr. <?php echo htmlspecialchars($doctor['doctor_name'] ?? 'Unknown'); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($doctor['doctor_email'] ?? ''); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></td>
                        <td><?php echo htmlspecialchars($doctor['doctor_phone'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($doctor['experience_years'] ?? '0'); ?> yrs</td>
                        <td>
                            <?php $is_active = (isset($doctor['ustatus']) && $doctor['ustatus'] == 1); ?>
                            <span class="badge-status <?php echo $is_active ? 'active' : 'inactive'; ?>">
                                <?php echo $is_active ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?php echo BASE_URL; ?>hospital/doctor-detail?id=<?php echo (int)$doctor['doctor_id']; ?>"
                                   class="btn-action view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>hospital/doctor-add?id=<?php echo (int)$doctor['doctor_id']; ?>"
                                   class="btn-action edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete_id=<?php echo (int)$doctor['doctor_id']; ?>"
                                   class="btn-action delete" title="Remove from this hospital"
                                   onclick="return confirm('Are you sure you want to remove Dr. <?php echo htmlspecialchars(addslashes($doctor['doctor_name'])); ?> from your hospital?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
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
        <?php $search_qs = urlencode($_GET['search'] ?? ''); ?>
        <div class="pagination-container">
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search_qs; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search_qs; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search_qs; ?>">
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