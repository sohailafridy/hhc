<?php include '../config.php'; ?>

<?php
// Handle emergency status toggle
if (isset($_POST['toggle_emergency']) && is_numeric($_POST['toggle_emergency']) && isset($_POST['status'])) {
    $doctor_id = $_POST['toggle_emergency'];
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
    $delete_id = $_GET['delete_id'];
    
    $pic_query = "SELECT doctor_pic FROM doctors WHERE doctor_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $doctor_pic_data = mysqli_fetch_assoc($pic_result);
    $doctor_pic = $doctor_pic_data ? $doctor_pic_data['doctor_pic'] : '';
    
    $delete_query = "UPDATE entities set status = 0 WHERE entity_id = $delete_id";
    
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
$where_conditions[] = "e.status = 1";

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
                LEFT JOIN entities e ON e.entity_id = d.entity_id
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
                e.status as estatus
          FROM doctors d
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
          LEFT JOIN feedback f ON d.entity_id = f.entity_id
          LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
          LEFT JOIN entities e ON e.entity_id = d.entity_id
          $where_clause
          GROUP BY d.doctor_id
          ORDER BY d.created_at DESC 
          LIMIT $offset, $records_per_page";
$result = mysqli_query($con, $query);
?>

<style>
:root {
    --primary: #4f46e5;
    --primary-light: #818cf8;
    --primary-dark: #4338ca;
    --success: #22c55e;
    --warning: #f59e0b;
    --danger: #ef4444;
    --text: #1e293b;
    --text-light: #64748b;
    --bg: #f1f5f9;
    --card: #ffffff;
    --border: #e2e8f0;
    --shadow: 0 1px 3px rgba(0,0,0,0.06);
    --shadow-lg: 0 8px 25px rgba(0,0,0,0.08);
}

.content-wrapper {
    background: var(--bg);
    min-height: 100vh;
    padding: 20px 25px 40px;
}

/* ===== PAGE HEADER ===== */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.page-header h4 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.page-header h4 i {
    color: var(--primary);
    margin-right: 8px;
}

.page-header h4 .badge-count {
    background: var(--primary);
    color: #fff;
    font-size: 12px;
    padding: 2px 10px;
    border-radius: 50px;
    margin-left: 8px;
}

.btn-add {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s;
}

.btn-add:hover {
    background: var(--primary-dark);
    color: #fff;
    transform: translateY(-1px);
}

/* ===== FILTER SECTION ===== */
.filter-section {
    background: var(--card);
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 20px;
    border: 1px solid var(--border);
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 140px;
}

.filter-group label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-light);
    margin-bottom: 3px;
    display: block;
    letter-spacing: 0.3px;
}

.filter-group .form-control,
.filter-group .form-select {
    border: 1.5px solid var(--border);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 13px;
    transition: all 0.3s;
    background: #fff;
    width: 100%;
}

.filter-group .form-control:focus,
.filter-group .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,70,229,0.08);
}

.filter-checkboxes {
    display: flex;
    gap: 16px;
    align-items: center;
    flex-wrap: wrap;
    padding-top: 6px;
}

.filter-checkboxes .form-check {
    margin: 0;
}

.filter-checkboxes .form-check-input {
    width: 16px;
    height: 16px;
    margin-top: 2px;
    accent-color: var(--primary);
}

.filter-checkboxes .form-check-label {
    font-size: 13px;
    color: var(--text);
    font-weight: 500;
}

.btn-filter {
    background: var(--primary);
    color: #fff;
    border: none;
    padding: 7px 18px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s;
}

.btn-filter:hover {
    background: var(--primary-dark);
    color: #fff;
}

.btn-reset {
    background: #f1f5f9;
    color: var(--text-light);
    border: 1.5px solid var(--border);
    padding: 7px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-reset:hover {
    background: #e2e8f0;
    color: var(--text);
}

/* ===== DOCTORS GRID ===== */
.doctors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
}

.doctor-card {
    background: var(--card);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    box-shadow: var(--shadow);
}

.doctor-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

/* Card Image */
.doctor-card .card-img {
    position: relative;
    height: 150px;
    overflow: hidden;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
}

.doctor-card .card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.doctor-card:hover .card-img img {
    transform: scale(1.05);
}

/* Emergency Badge */
.doctor-card .emergency-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    padding: 3px 10px;
    background: var(--danger);
    color: #fff;
    border-radius: 50px;
    font-size: 10px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(239,68,68,0.3);
}

/* Card Actions */
.doctor-card .card-actions {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 2;
    display: flex;
    gap: 4px;
}

.doctor-card .card-actions .btn-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(4px);
    color: var(--text);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: all 0.3s;
    text-decoration: none;
}

.doctor-card .card-actions .btn-icon:hover {
    background: #fff;
    transform: scale(1.1);
}

/* Card Body */
.doctor-card .card-body {
    padding: 14px 16px;
}

.doctor-card .doctor-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.doctor-card .doctor-name .female-icon {
    color: #ec4899;
    font-size: 12px;
}

.doctor-card .doctor-spec {
    font-size: 12px;
    color: var(--primary);
    font-weight: 500;
    margin-bottom: 6px;
}

.doctor-card .doctor-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 8px;
}

.doctor-card .doctor-meta .meta-item {
    font-size: 11px;
    color: var(--text-light);
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.doctor-card .doctor-meta .meta-item i {
    font-size: 10px;
    color: var(--primary);
}

/* Rating */
.doctor-card .doctor-rating {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.doctor-card .doctor-rating .stars {
    color: #f59e0b;
    font-size: 11px;
    letter-spacing: 1px;
}

.doctor-card .doctor-rating .rating-text {
    font-size: 11px;
    color: var(--text-light);
}

/* Status Badges */
.doctor-card .doctor-status {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.badge-sm {
    padding: 2px 8px;
    border-radius: 50px;
    font-size: 10px;
    font-weight: 600;
    display: inline-block;
}

.badge-sm.active { background: #d1fae5; color: #065f46; }
.badge-sm.inactive { background: #fee2e2; color: #991b1b; }
.badge-sm.emergency { background: #fef3c7; color: #92400e; }
.badge-sm.hospital { background: #dbeafe; color: #1e40af; }
.badge-sm.clinic { background: #d1fae5; color: #065f46; }

/* ===== PAGINATION ===== */
.pagination-wrap {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
    padding: 12px 16px;
    background: var(--card);
    border-radius: 10px;
    border: 1px solid var(--border);
}

.pagination {
    margin: 0;
    gap: 3px;
}

.pagination .page-link {
    border: none;
    border-radius: 6px;
    color: var(--text);
    padding: 6px 12px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s;
}

.pagination .page-link:hover {
    background: var(--primary);
    color: #fff;
}

.pagination .page-item.active .page-link {
    background: var(--primary);
    color: #fff;
}

.pagination .page-item.disabled .page-link {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-info {
    font-size: 13px;
    color: var(--text-light);
}

/* ===== NO RECORDS ===== */
.no-records {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-light);
}

.no-records i {
    font-size: 40px;
    color: #cbd5e1;
    display: block;
    margin-bottom: 12px;
}

.no-records h5 {
    font-weight: 600;
    color: var(--text);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .filter-row { flex-direction: column; }
    .filter-group { min-width: 100%; }
}

@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .doctors-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
    .pagination-wrap { flex-direction: column; text-align: center; }
}

@media (max-width: 480px) {
    .content-wrapper { padding: 12px; }
    .doctors-grid { grid-template-columns: 1fr; }
    .doctor-card .card-img { height: 120px; }
    .filter-section { padding: 12px; }
}
</style>

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
                            <a href="javascript:void(0)" onclick="deleteDoctor(<?php echo $doctor['entity_id']; ?>)" 
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

function deleteDoctor(entity_id) {
    if (confirm('Are you sure you want to delete this doctor? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + entity_id;
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