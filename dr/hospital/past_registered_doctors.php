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
// RE-ADD DOCTOR (inactive = 0)
// ============================================
if (isset($_GET['readd_id']) && is_numeric($_GET['readd_id'])) {
    $readd_doctor_id = (int) $_GET['readd_id'];

    // Pehle check karein ke yeh doctor is hospital ka inactive row hai
    $check_query = "SELECT doctor_in_hosp_id, if_clinic 
                    FROM doctor_in_hospital 
                    WHERE doctor_id = $readd_doctor_id 
                    AND hospital_id = $hospital_id 
                    AND inactive = 1";
    $check_result = mysqli_query($con, $check_query);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $row = mysqli_fetch_assoc($check_result);
        $dih_id    = (int) $row['doctor_in_hosp_id'];
        $if_clinic = (int) $row['if_clinic'];

        // Re-add: inactive = 0
        $readd_query = "UPDATE doctor_in_hospital 
                        SET inactive = 0, updated_at = NOW() 
                        WHERE doctor_in_hosp_id = $dih_id 
                        AND doctor_id = $readd_doctor_id 
                        AND hospital_id = $hospital_id";

        if (mysqli_query($con, $readd_query)) {
            // Agar Personal Clinic thi to doctors.clinic_status = 0 (assigned) kar dein
            if ($if_clinic == 1) {
                mysqli_query($con, "UPDATE doctors SET clinic_status = 0 WHERE doctor_id = $readd_doctor_id");
            }

            $_SESSION['success_msg'] = "Doctor re-added to your hospital successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
        }
    } else {
        $_SESSION['error_msg'] = "Doctor not found in removed list.";
    }

    header('Location: ' . BASE_URL . 'hospital/past_registered_doctors.php');
    exit();
}

// ============================================
// GET INACTIVE DOCTORS (Removed)
// ============================================
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = "dih.hospital_id = $hospital_id AND dih.inactive = 1";

if (!empty($search)) {
    $where .= " AND (d.doctor_name LIKE '%$search%' OR dct.type LIKE '%$search%')";
}

// Count total
$count_query = "SELECT COUNT(DISTINCT d.doctor_id) as total 
                FROM doctor_in_hospital dih
                LEFT JOIN doctors d ON dih.doctor_id = d.doctor_id
                LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                WHERE $where";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch inactive doctors
$query = "SELECT DISTINCT d.*, dct.type as specialization, dih.updated_at as removed_at
          FROM doctor_in_hospital dih
          LEFT JOIN doctors d ON dih.doctor_id = d.doctor_id
          LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
          WHERE $where
          ORDER BY dih.updated_at DESC
          LIMIT $offset, $per_page";
$result = mysqli_query($con, $query);
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-doctors-admin.css">

<div class="content-wrapper">

    <div class="page-header">
        <h4><i class="fas fa-history me-2"></i> Past Registered Doctors</h4>
        <div>
            <a href="<?php echo BASE_URL; ?>hospital/doctors.php" class="btn-add">
                <i class="fas fa-arrow-left me-2"></i> Back to Active Doctors
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
                    <th>Removed On</th>
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
                                <?php if (!empty($doctor['removed_at'])): ?>
                                    <?php echo date('d M, Y', strtotime($doctor['removed_at'])); ?>
                                    <br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($doctor['removed_at'])); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?php echo BASE_URL; ?>hospital/doctor-detail?id=<?php echo $doctor['doctor_id']; ?>" 
                                       class="btn-action view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?readd_id=<?php echo $doctor['doctor_id']; ?>" 
                                       class="btn-action edit" title="Re-add to my hospital"
                                       onclick="return confirm('Re-add Dr. <?php echo htmlspecialchars(addslashes($doctor['doctor_name'])); ?> to your hospital?')">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-history fa-2x mb-2 d-block" style="color:#cbd5e1;"></i>
                            No past registered doctors found.
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