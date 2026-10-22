<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>

<?php
// Fetch counts for dashboard
$cities_count_query = "SELECT COUNT(*) as total FROM cities WHERE status = 1";
$cities_count_result = mysqli_query($con, $cities_count_query);
$cities_count = (int) mysqli_fetch_assoc($cities_count_result)['total'];

$doctors_count_query = "SELECT COUNT(*) as total FROM doctors LEFT JOIN users u ON u.user_id = doctors.user_id WHERE u.status = 1 AND doctors.approve = 1";
$doctors_count_result = mysqli_query($con, $doctors_count_query);
$doctors_count = (int) mysqli_fetch_assoc($doctors_count_result)['total'];

$hospitals_count_query = "SELECT COUNT(*) as total FROM hospitals LEFT JOIN users u ON u.user_id = hospitals.user_id WHERE u.status = 1 AND hospitals.approve = 1";
$hospitals_count_result = mysqli_query($con, $hospitals_count_query);
$hospitals_count = (int) mysqli_fetch_assoc($hospitals_count_result)['total'];

$labs_count_query = "SELECT COUNT(*) as total FROM laboratories LEFT JOIN users u ON u.user_id = laboratories.user_id WHERE u.status = 1 AND laboratories.approve = 1";
$labs_count_result = mysqli_query($con, $labs_count_query);
$labs_count = (int) mysqli_fetch_assoc($labs_count_result)['total'];

$blood_banks_count_query = "SELECT COUNT(*) as total FROM blood_bank LEFT JOIN users u ON u.user_id = blood_bank.user_id WHERE u.status = 1 AND blood_bank.approve = 1";
$blood_banks_count_result = mysqli_query($con, $blood_banks_count_query);
$blood_banks_count = (int) mysqli_fetch_assoc($blood_banks_count_result)['total'];

// Users count
$users_count_query = "SELECT COUNT(*) as total FROM users WHERE status = 1";
$users_count_result = mysqli_query($con, $users_count_query);
$users_count = (int) mysqli_fetch_assoc($users_count_result)['total'];



// cities count
$city_count_query = "SELECT COUNT(*) as total FROM cities WHERE status = 1 AND approve=1";
$city_count_result = mysqli_query($con, $city_count_query);
$city_count = (int) mysqli_fetch_assoc($city_count_result)['total'];

// Feedbacks count
$feedbacks_count_query = "SELECT COUNT(*) as total FROM feedback WHERE status = 1";
$feedbacks_count_result = mysqli_query($con, $feedbacks_count_query);
$feedbacks_count = (int) mysqli_fetch_assoc($feedbacks_count_result)['total'];

// Recent doctors
$recent_doctors_query = "SELECT d.*, c.city_name, h.hospital_name, dct.type as specialization ,u.status as ustatus
                         FROM doctors d 
                         LEFT JOIN cities c ON d.city_id = c.city_id 
                         LEFT JOIN users u ON u.user_id = d.user_id
                         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                         WHERE u.status = 1 AND d.approve = 1
                         ORDER BY d.created_at DESC 
                         LIMIT 5";
$recent_doctors_result = mysqli_query($con, $recent_doctors_query);

$chart_month_labels = [];
$chart_month_keys = [];
for ($i = 5; $i >= 0; $i--) {
    $month_ts = strtotime("-$i months");
    $chart_month_keys[] = date('Y-m', $month_ts);
    $chart_month_labels[] = date('M Y', $month_ts);
}
$chart_start = $chart_month_keys[0] . '-01';

$chart_month_doctors = array_fill_keys($chart_month_keys, 0);
$chart_month_hospitals = array_fill_keys($chart_month_keys, 0);
$chart_month_labs = array_fill_keys($chart_month_keys, 0);
$chart_month_blood_banks = array_fill_keys($chart_month_keys, 0);

$monthly_fill = function ($sql, &$bucket) use ($con) {
    $result = mysqli_query($con, $sql);
    if (!$result) {
        return;
    }
    while ($row = mysqli_fetch_assoc($result)) {
        if (isset($bucket[$row['ym']])) {
            $bucket[$row['ym']] = (int) $row['total'];
        }
    }
};

$monthly_fill(
    "SELECT DATE_FORMAT(d.created_at, '%Y-%m') AS ym, COUNT(*) AS total
     FROM doctors d
     LEFT JOIN users u ON u.user_id = d.user_id
     WHERE u.status = 1 AND d.approve = 1 AND d.created_at >= '$chart_start'
     GROUP BY ym",
    $chart_month_doctors
);
$monthly_fill(
    "SELECT DATE_FORMAT(h.created_at, '%Y-%m') AS ym, COUNT(*) AS total
     FROM hospitals h
     LEFT JOIN users u ON u.user_id = h.user_id
     WHERE u.status = 1 AND h.approve = 1 AND h.created_at >= '$chart_start'
     GROUP BY ym",
    $chart_month_hospitals
);
$monthly_fill(
    "SELECT DATE_FORMAT(l.created_at, '%Y-%m') AS ym, COUNT(*) AS total
     FROM laboratories l
     LEFT JOIN users u ON u.user_id = l.user_id
     WHERE u.status = 1 AND l.approve = 1 AND l.created_at >= '$chart_start'
     GROUP BY ym",
    $chart_month_labs
);
$monthly_fill(
    "SELECT DATE_FORMAT(b.created_at, '%Y-%m') AS ym, COUNT(*) AS total
     FROM blood_bank b
     LEFT JOIN users u ON u.user_id = b.user_id
     WHERE u.status = 1 AND b.approve = 1 AND b.created_at >= '$chart_start'
     GROUP BY ym",
    $chart_month_blood_banks
);

$dashboard_chart_data = [
    'months' => $chart_month_labels,
    'monthly' => [
        'doctors' => array_values($chart_month_doctors),
        'hospitals' => array_values($chart_month_hospitals),
        'labs' => array_values($chart_month_labs),
        'bloodBanks' => array_values($chart_month_blood_banks),
    ],
    'totals' => [
        'doctors' => $doctors_count,
        'hospitals' => $hospitals_count,
        'labs' => $labs_count,
        'bloodBanks' => $blood_banks_count,
        'cities' => $city_count,
        'users' => $users_count,
        'feedbacks' => $feedbacks_count,
    ],
];
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/admin/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/admin-index.css">

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header-modern">
        <div class="page-header-content">
            <h1><i class="fas fa-th-large me-2"></i> Dashboard <?php echo date('Y'); ?></h1>
            <p>Welcome to the world of technology</p>
            <div class="header-badges">
                <span class="header-badge"><i class="fas fa-circle" style="color: #4ade80;"></i> System Online</span>
                <span class="header-badge"><i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?></span>
                <span class="header-badge"><i class="fas fa-clock"></i> <?php echo date('h:i A'); ?></span>
            </div>
        </div>
    </div>

    <!-- ===== STATS GRID ===== -->
    <div class="stats-grid">
        <a href="<?=BASE_URL?>admin/cities/list">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-map-marker-alt"></i></div>
                <div class="stat-number"><?php echo $city_count; ?></div>
                <div class="stat-label">Total Cities</div>
                <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active</span>
            </div>
        </a>
        <a href="<?=BASE_URL?>admin/hospitals/list">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-hospital"></i></div>
                <div class="stat-number"><?php echo $hospitals_count; ?></div>
                <div class="stat-label">Total Hospitals</div>
                <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active</span>
            </div>
        </a>
        <a href="<?=BASE_URL?>admin/doctors/list">    
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
                <div class="stat-number"><?php echo $doctors_count; ?></div>
                <div class="stat-label">Total Doctors</div>
                <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active</span>
            </div>
        </a>
        <a href="<?=BASE_URL?>admin/laboratories/list">    
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-flask"></i></div>
                <div class="stat-number"><?php echo $labs_count; ?></div>
                <div class="stat-label">Total Laboratories</div>
                <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active</span>
            </div>
        </a>
        <a href="<?=BASE_URL?>admin/blood-banks/list">   
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-tint"></i></div>
                <div class="stat-number"><?php echo $blood_banks_count; ?></div>
                <div class="stat-label">Blood Banks</div>
                <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active</span>
            </div>
        </a>
        <a href="<?=BASE_URL?>admin/users">    
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?php echo $users_count; ?></div>
                <div class="stat-label">Total Users</div>
                <span class="stat-change up"><i class="fas fa-arrow-up"></i> Registered</span>
            </div>
        </a>
        <a href="#">    
            <div class="stat-card">
                <div class="stat-icon cyan"><i class="fas fa-star"></i></div>
                <div class="stat-number"><?php echo $feedbacks_count; ?></div>
                <div class="stat-label">Total Feedbacks</div>
                <span class="stat-change up"><i class="fas fa-arrow-up"></i> Reviews</span>
            </div>
        </a>
    </div>

    <!-- ===== DASHBOARD GRID ===== -->
    <div class="dashboard-grid">

        <!-- ===== LEFT COLUMN ===== -->
        <div class="left-column">

            <!-- Recent Doctors -->
            <div class="glass-card">
                <div class="card-header">
                    <h5><i class="fas fa-user-md"></i> Recent Doctors</h5>
                    <a href="<?php echo BASE_URL; ?>admin/doctors/list" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($recent_doctors_result) > 0): ?>
                        <table class="table-recent">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Specialization</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($doctor = mysqli_fetch_assoc($recent_doctors_result)): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if (!empty($doctor['doctor_pic'])): ?>
                                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                                         alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
                                                <?php else: ?>
                                                    <div class="doctor-avatar-placeholder">
                                                        <?php echo strtoupper(substr($doctor['doctor_name'], 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-bold" style="font-size:0.85rem;">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($doctor['city_name'] ?? 'N/A'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></td>
                                       
                                        <td>
                                            <span class="badge-status <?php echo $doctor['ustatus'] == 1 ? 'active' : 'inactive'; ?>">
                                                <?php echo $doctor['ustatus'] == 1 ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td style="font-size:0.75rem; color:var(--text-light);">
                                            <?php echo date('d M Y', strtotime($doctor['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-user-md fa-2x mb-2" style="color:#cbd5e1;"></i>
                            <p>No doctors found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chart -->
            <div class="glass-card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar"></i> Last 6 Months</h5>
                </div>
                <div class="card-body">
                    <div id="barchart" class="chart-container"></div>
                </div>
            </div>

        </div>

        <!-- ===== RIGHT COLUMN ===== -->
        <div class="right-column">

            <!-- Quick Actions -->
            <div class="glass-card">
                <div class="card-header">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="quick-actions-grid">
                        <a href="<?php echo BASE_URL; ?>admin/doctors/add" class="quick-action-btn">
                            <span class="qa-icon blue"><i class="fas fa-user-md"></i></span>
                            <span class="qa-text">
                                <span class="qa-title">Add Doctor</span>
                                <span class="qa-desc">New doctor profile</span>
                            </span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/hospitals/add" class="quick-action-btn">
                            <span class="qa-icon green"><i class="fas fa-hospital"></i></span>
                            <span class="qa-text">
                                <span class="qa-title">Add Hospital</span>
                                <span class="qa-desc">New hospital listing</span>
                            </span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/laboratories/add" class="quick-action-btn">
                            <span class="qa-icon orange"><i class="fas fa-flask"></i></span>
                            <span class="qa-text">
                                <span class="qa-title">Add Lab</span>
                                <span class="qa-desc">New laboratory</span>
                            </span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/blood-banks/add" class="quick-action-btn">
                            <span class="qa-icon red"><i class="fas fa-tint"></i></span>
                            <span class="qa-text">
                                <span class="qa-title">Add Blood Bank</span>
                                <span class="qa-desc">New blood bank</span>
                            </span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/users" class="quick-action-btn">
                            <span class="qa-icon purple"><i class="fas fa-users"></i></span>
                            <span class="qa-text">
                                <span class="qa-title">Manage Users</span>
                                <span class="qa-desc">View all users</span>
                            </span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/recycle" class="quick-action-btn">
                            <span class="qa-icon cyan"><i class="fas fa-trash"></i></span>
                            <span class="qa-text">
                                <span class="qa-title">Recycle Bin</span>
                                <span class="qa-desc">Deleted items</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="glass-card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Distribution</h5>
                </div>
                <div class="card-body">
                    <div id="piechart" class="chart-container" style="height:250px;"></div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
window.adminDashboardStats = <?php echo json_encode($dashboard_chart_data, JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>