<?php include '../config.php'; ?>

<?php
// Handle emergency status toggle
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

// Handle delete operation
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    $pic_query = "SELECT doctor_pic FROM doctors WHERE user_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $doctor_pic_data = mysqli_fetch_assoc($pic_result);
    $doctor_pic = $doctor_pic_data ? $doctor_pic_data['doctor_pic'] : '';
    
    $delete_query = "UPDATE users set status = 0 WHERE user_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        $_SESSION['success_msg'] = "Doctor deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/admin/inc/nav.php'; ?>

<?php
// Pagination variables
$records_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search filters
$search_doctor = isset($_GET['search_doctor']) ? mysqli_real_escape_string($con, $_GET['search_doctor']) : '';
$filter_city_id = isset($_GET['filter_city_id']) ? mysqli_real_escape_string($con, $_GET['filter_city_id']) : '';
$filter_specialization = isset($_GET['filter_specialization']) ? mysqli_real_escape_string($con, $_GET['filter_specialization']) : '';
$filter_emergency = isset($_GET['filter_emergency']) ? mysqli_real_escape_string($con, $_GET['filter_emergency']) : '';
$gender = isset($_GET['gender']) ? mysqli_real_escape_string($con, $_GET['gender']) : '';

// Build WHERE clause
$where_conditions = [];
if (!empty($search_doctor)) {
    $where_conditions[] = "(d.doctor_name LIKE '%$search_doctor%' OR dct.type LIKE '%$search_doctor%')";
}
if (!empty($filter_city_id) && is_numeric($filter_city_id)) {
    $where_conditions[] = "d.city_id = $filter_city_id";
}
if (!empty($filter_specialization) && is_numeric($filter_specialization)) {
    $where_conditions[] = "d.cat_type_id = $filter_specialization";
}
if ($filter_emergency == '1') {
    $where_conditions[] = "d.emergency_status = 1";
}
if ($gender != '') {
    $where_conditions[] = "d.gender = '$gender'";
}
$where_conditions[] = "d.approve = 1";
$where_conditions[] = "u.status = 1";

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Fetch cities for dropdown
$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// Fetch specializations for dropdown
$specializations_query = "SELECT dr_cat_type_id, type FROM dr_cat_types ORDER BY type ASC";
$specializations_result = mysqli_query($con, $specializations_query);

// Count total records
$count_query = "SELECT COUNT(*) as total 
                FROM doctors d
                LEFT JOIN cities c ON d.city_id = c.city_id
                LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                LEFT JOIN users u ON u.user_id = d.user_id
                $where_clause";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch doctors data
$query = "SELECT d.*, 
                c.city_name,
                h.hospital_name,
                COUNT(f.feedback_id) as total_feedbacks,
                AVG(f.stars) as avg_rating,
                dct.type as specialization,
                u.status as estatus
          FROM doctors d
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
          LEFT JOIN feedback f ON d.user_id = f.user_id
          LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
          LEFT JOIN users u ON u.user_id = d.user_id
          $where_clause
          GROUP BY d.doctor_id
          ORDER BY d.created_at DESC 
          LIMIT $offset, $records_per_page";
$result = mysqli_query($con, $query);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-list-admin.css">

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header">
        <h4>
            <i class="fas fa-user-md"></i> Doctors
            <span class="badge-count"><?php echo $total_records; ?></span>
        </h4>
        <a href="<?php echo BASE_URL; ?>admin/doctors/add" class="btn-add">
            <i class="fas fa-plus"></i> Add Doctor
        </a>
    </div>

    <!-- ===== ALERTS ===== -->
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

    <!-- ===== FILTER SECTION ===== -->
    <div class="filter-section">
        <form method="GET" action="">
            <div class="filter-row">
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Search</label>
                    <input type="text" class="form-control" name="search_doctor" 
                           value="<?php echo htmlspecialchars($search_doctor); ?>" 
                           placeholder="Name or specialization...">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-city"></i> City</label>
                    <select class="form-select" name="filter_city_id">
                        <option value="">All Cities</option>
                        <?php 
                        mysqli_data_seek($cities_result, 0);
                        while ($city = mysqli_fetch_assoc($cities_result)): ?>
                            <option value="<?php echo $city['city_id']; ?>" 
                                    <?php echo ($filter_city_id == $city['city_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['city_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-stethoscope"></i> Specialization</label>
                    <select class="form-select" name="filter_specialization">
                        <option value="">All Specializations</option>
                        <?php 
                        while ($spec = mysqli_fetch_assoc($specializations_result)): ?>
                            <option value="<?php echo $spec['dr_cat_type_id']; ?>" 
                                    <?php echo ($filter_specialization == $spec['dr_cat_type_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec['type']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group" style="flex:0 0 auto;">
                    <label>&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="<?php echo BASE_URL; ?>admin/doctors/list" class="btn-reset">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="filter-row mt-2">
                <div class="filter-checkboxes">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="filter_emergency" value="1" 
                               id="emergencyCheck" <?php echo ($filter_emergency == '1') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="emergencyCheck">
                            <i class="fas fa-exclamation-triangle text-danger"></i> Emergency
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="gender" value="Female" 
                               id="genderCheck" <?php echo ($gender == 'Female') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="genderCheck">
                            <i class="fas fa-female text-pink"></i> Lady Doctors
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ===== DOCTORS GRID ===== -->
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="doctors-grid">
            <?php while ($doctor = mysqli_fetch_assoc($result)): 
                $avg_rating = $doctor['avg_rating'] ? round($doctor['avg_rating'], 1) : 0;
                $total_feedbacks = $doctor['total_feedbacks'] ?? 0;
                $img_src = !empty($doctor['doctor_pic']) 
                    ? BASE_URL . "admin/inc/uploads/doctors/" . $doctor['doctor_pic'] 
                    : '';
            ?>
                <div class="doctor-card">
                    <div class="card-img">
                        <?php if (!empty($img_src)): ?>
                            <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>">
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);font-size:48px;">
                                <i class="fas fa-user-md"></i>
                            </div>
                        <?php endif; ?>

                        <?php if ($doctor['emergency_status'] == 1): ?>
                            <span class="emergency-badge">
                                <i class="fas fa-exclamation-triangle"></i> Emergency
                            </span>
                        <?php endif; ?>

                        <div class="card-actions">
                            <a href="<?php echo BASE_URL; ?>admin/doctors/profile?id=<?php echo $doctor['doctor_id']; ?>" 
                               class="btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo BASE_URL; ?>admin/doctors/add?id=<?php echo $doctor['doctor_id']; ?>" 
                               class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="javascript:void(0)" onclick="deleteDoctor(<?php echo $doctor['user_id']; ?>)" 
                               class="btn-icon" title="Delete" style="color:#ef4444;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="doctor-name">
                            Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                            <?php if ($doctor['gender'] == 'Female'): ?>
                                <i class="fas fa-venus female-icon"></i>
                            <?php endif; ?>
                        </div>
                        <div class="doctor-spec">
                            <i class="fas fa-stethoscope me-1"></i>
                            <?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?>
                        </div>

                        <div class="doctor-meta">
                            <span class="meta-item">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['city_name'] ?? 'N/A'); ?>
                            </span>
                            <?php if ($doctor['experience_years'] > 0): ?>
                                <span class="meta-item">
                                    <i class="fas fa-briefcase"></i> <?php echo $doctor['experience_years']; ?>y
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($avg_rating > 0): ?>
                            <div class="doctor-rating">
                                <span class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $avg_rating ? '' : 'text-muted'; ?>" 
                                           style="opacity:<?php echo $i <= $avg_rating ? '1' : '0.3'; ?>;"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="rating-text"><?php echo $avg_rating; ?> (<?php echo $total_feedbacks; ?>)</span>
                            </div>
                        <?php endif; ?>

                        <div class="doctor-status">
                            <?php if ($doctor['estatus'] == 1): ?>
                                <span class="badge-sm active">Active</span>
                            <?php else: ?>
                                <span class="badge-sm inactive">Inactive</span>
                            <?php endif; ?>
                            
                            <?php if ($doctor['doctor_type'] == 1): ?>
                                <span class="badge-sm hospital">Hospital</span>
                            <?php else: ?>
                                <span class="badge-sm clinic">Clinic</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- ===== PAGINATION ===== -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination-wrap">
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search_doctor=<?php echo urlencode($search_doctor); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_specialization=<?php echo $filter_specialization; ?>&filter_emergency=<?php echo $filter_emergency; ?>&gender=<?php echo $gender; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search_doctor=<?php echo urlencode($search_doctor); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_specialization=<?php echo $filter_specialization; ?>&filter_emergency=<?php echo $filter_emergency; ?>&gender=<?php echo $gender; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search_doctor=<?php echo urlencode($search_doctor); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_specialization=<?php echo $filter_specialization; ?>&filter_emergency=<?php echo $filter_emergency; ?>&gender=<?php echo $gender; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="pagination-info">
                    Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-records">
            <i class="fas fa-user-md"></i>
            <h5>No Doctors Found</h5>
            <p>Try adjusting your search filters or add a new doctor.</p>
            <a href="<?php echo BASE_URL; ?>admin/doctors/add" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-2"></i> Add Doctor
            </a>
        </div>
    <?php endif; ?>

</div>

<script>
$(document).ready(function() {
    $('#filter_city_id, #filter_specialization').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search...',
        allowClear: true,
        width: '100%'
    });
});

function deleteDoctor(user_id) {
    if (confirm('Are you sure you want to delete this doctor? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + user_id;
    }
}

function toggleEmergency(doctorId, status) {
    var action = status == 1 ? 'disable' : 'enable';
    if (confirm('Are you sure you want to ' + action + ' emergency services for this doctor?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                window.location.reload();
            }
        };
        xhr.send('toggle_emergency=' + doctorId + '&status=' + status);
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>