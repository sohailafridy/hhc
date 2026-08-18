<?php include '../config.php'; ?>

<?php
// Fetch counts for dashboard
$cities_count_query = "SELECT COUNT(*) as total FROM cities WHERE status = 1";
$cities_count_result = mysqli_query($con, $cities_count_query);
$cities_count = mysqli_fetch_assoc($cities_count_result)['total'];

$doctors_count_query = "SELECT COUNT(*) as total FROM doctors LEFT JOIN entities e ON e.entity_id = doctors.entity_id WHERE e.status = 1 AND doctors.approve = 1";
$doctors_count_result = mysqli_query($con, $doctors_count_query);
$doctors_count = mysqli_fetch_assoc($doctors_count_result)['total'];

$hospitals_count_query = "SELECT COUNT(*) as total FROM hospitals LEFT JOIN entities e ON e.entity_id = hospitals.entity_id WHERE e.status = 1 AND hospitals.approve = 1";
$hospitals_count_result = mysqli_query($con, $hospitals_count_query);
$hospitals_count = mysqli_fetch_assoc($hospitals_count_result)['total'];

$labs_count_query = "SELECT COUNT(*) as total FROM laboratories LEFT JOIN entities e ON e.entity_id = laboratories.entity_id WHERE e.status = 1 AND laboratories.approve = 1";
$labs_count_result = mysqli_query($con, $labs_count_query);
$labs_count = mysqli_fetch_assoc($labs_count_result)['total'];

$blood_banks_count_query = "SELECT COUNT(*) as total FROM blood_bank LEFT JOIN entities e ON e.entity_id = blood_bank.entity_id WHERE e.status = 1 AND blood_bank.approve = 1";
$blood_banks_count_result = mysqli_query($con, $blood_banks_count_query);
$blood_banks_count = mysqli_fetch_assoc($blood_banks_count_result)['total'];

// Users count
$users_count_query = "SELECT COUNT(*) as total FROM users WHERE status = 1";
$users_count_result = mysqli_query($con, $users_count_query);
$users_count = mysqli_fetch_assoc($users_count_result)['total'];

// Feedbacks count
$feedbacks_count_query = "SELECT COUNT(*) as total FROM feedback WHERE status = 1";
$feedbacks_count_result = mysqli_query($con, $feedbacks_count_query);
$feedbacks_count = mysqli_fetch_assoc($feedbacks_count_result)['total'];

// Recent doctors
$recent_doctors_query = "SELECT d.*, c.city_name, h.hospital_name, dct.type as specialization ,u.status as ustatus
                         FROM doctors d 
                         LEFT JOIN cities c ON d.city_id = c.city_id 
                         LEFT JOIN users u ON u.user_id = d.user_id
                         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                         LEFT JOIN entities e ON e.entity_id = d.entity_id
                         WHERE u.status = 1 AND d.approve = 1
                         ORDER BY d.created_at DESC 
                         LIMIT 5";
$recent_doctors_result = mysqli_query($con, $recent_doctors_query);
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/admin/inc/nav.php'; ?>

<style>
:root {
    --primary: #6366f1;
    --primary-light: #818cf8;
    --primary-dark: #4f46e5;
    --secondary: #8b5cf6;
    --success: #22c55e;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #0ea5e9;
    --text: #0f172a;
    --text-light: #64748b;
    --bg: #f1f5f9;
    --card: rgba(255,255,255,0.85);
    --shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
}

.content-wrapper {
    background: var(--bg);
    min-height: 100vh;
    padding: 24px 32px 60px;
}

/* ===== PAGE HEADER ===== */
.page-header-modern {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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
}

.page-header-content h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 4px;
    color: #ffffff;
    text-shadow: 0 2px 20px rgba(0,0,0,0.1);
}

.page-header-content p {
    margin: 0;
    color: rgba(255,255,255,0.85);
    font-size: 0.95rem;
}

.page-header-content .header-badges {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 12px;
}

.page-header-content .header-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 16px;
    background: rgba(255,255,255,0.12);
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
    border: 1px solid rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.85);
}

/* ===== STATS GRID ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 20px 22px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    opacity: 0;
    transition: opacity 0.3s;
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
}

.stat-card .stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 10px;
}

.stat-card .stat-icon.blue { background: #dbeafe; color: #1e40af; }
.stat-card .stat-icon.green { background: #d1fae5; color: #065f46; }
.stat-card .stat-icon.orange { background: #fef3c7; color: #92400e; }
.stat-card .stat-icon.purple { background: #ede9fe; color: #5b21b6; }
.stat-card .stat-icon.red { background: #fee2e2; color: #991b1b; }
.stat-card .stat-icon.cyan { background: #cffafe; color: #0e7490; }

.stat-card .stat-number {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text);
    line-height: 1.2;
}

.stat-card .stat-label {
    font-size: 0.8rem;
    color: var(--text-light);
    font-weight: 500;
}

.stat-card .stat-change {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-top: 4px;
    padding: 2px 10px;
    border-radius: 50px;
}

.stat-card .stat-change.up { background: #d1fae5; color: #065f46; }
.stat-card .stat-change.down { background: #fee2e2; color: #991b1b; }

/* ===== MAIN GRID ===== */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

/* ===== CARDS ===== */
.glass-card {
    background: var(--card);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.3);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.glass-card .card-header {
    padding: 14px 20px;
    background: rgba(0,0,0,0.02);
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.glass-card .card-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}

.glass-card .card-header h5 i {
    color: var(--primary);
}

.glass-card .card-body {
    padding: 16px 20px;
}

/* ===== RECENT DOCTORS TABLE ===== */
.table-recent {
    width: 100%;
    font-size: 0.85rem;
}

.table-recent thead th {
    font-weight: 700;
    font-size: 0.65rem;
    text-transform: uppercase;
    color: var(--text-light);
    padding: 6px 0;
    border-bottom: 2px solid #e2e8f0;
    text-align: left;
}

.table-recent tbody td {
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-recent tbody tr:last-child td {
    border-bottom: none;
}

.table-recent .doctor-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

.table-recent .doctor-avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
}

.badge-status {
    padding: 2px 10px;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 600;
}

.badge-status.active { background: #d1fae5; color: #065f46; }
.badge-status.inactive { background: #fee2e2; color: #991b1b; }

.badge-type {
    padding: 2px 8px;
    border-radius: 50px;
    font-size: 0.6rem;
    font-weight: 600;
}

.badge-type.hospital { background: #dbeafe; color: #1e40af; }
.badge-type.clinic { background: #d1fae5; color: #065f46; }

/* ===== QUICK ACTIONS ===== */
.quick-actions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    color: var(--text);
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    border-color: var(--primary);
    background: #f0f7ff;
    transform: translateX(4px);
}

.quick-action-btn .qa-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.quick-action-btn .qa-icon.blue { background: #dbeafe; color: #1e40af; }
.quick-action-btn .qa-icon.green { background: #d1fae5; color: #065f46; }
.quick-action-btn .qa-icon.orange { background: #fef3c7; color: #92400e; }
.quick-action-btn .qa-icon.purple { background: #ede9fe; color: #5b21b6; }
.quick-action-btn .qa-icon.red { background: #fee2e2; color: #991b1b; }
.quick-action-btn .qa-icon.cyan { background: #cffafe; color: #0e7490; }

.quick-action-btn .qa-text .qa-title {
    font-weight: 600;
    font-size: 0.8rem;
}

.quick-action-btn .qa-text .qa-desc {
    font-size: 0.65rem;
    color: var(--text-light);
}

/* ===== CHART CONTAINER ===== */
.chart-container {
    height: 250px;
    width: 100%;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    .content-wrapper { padding: 16px; }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .page-header-content h1 { font-size: 1.4rem; }
    .quick-actions-grid { grid-template-columns: 1fr; }
    .header-badges { flex-direction: column; align-items: flex-start; }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .stat-card .stat-number { font-size: 1.4rem; }
    .glass-card .card-body { padding: 12px 14px; }
    .page-header-modern { padding: 20px; }
}
</style>

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
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
            <div class="stat-number"><?php echo $doctors_count; ?></div>
            <div class="stat-label">Total Doctors</div>
            <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-hospital"></i></div>
            <div class="stat-number"><?php echo $hospitals_count; ?></div>
            <div class="stat-label">Total Hospitals</div>
            <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-flask"></i></div>
            <div class="stat-number"><?php echo $labs_count; ?></div>
            <div class="stat-label">Total Laboratories</div>
            <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-tint"></i></div>
            <div class="stat-number"><?php echo $blood_banks_count; ?></div>
            <div class="stat-label">Blood Banks</div>
            <span class="stat-change up"><i class="fas fa-arrow-up"></i> Active</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo $users_count; ?></div>
            <div class="stat-label">Total Users</div>
            <span class="stat-change up"><i class="fas fa-arrow-up"></i> Registered</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon cyan"><i class="fas fa-star"></i></div>
            <div class="stat-number"><?php echo $feedbacks_count; ?></div>
            <div class="stat-label">Total Feedbacks</div>
            <span class="stat-change up"><i class="fas fa-arrow-up"></i> Reviews</span>
        </div>
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
                                    <th>Hospital</th>
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
                                            <?php if ($doctor['doctor_type'] == 1 && !empty($doctor['hospital_name'])): ?>
                                                <span class="badge-type hospital"><?php echo htmlspecialchars($doctor['hospital_name']); ?></span>
                                            <?php elseif ($doctor['doctor_type'] == 2): ?>
                                                <span class="badge-type clinic">Clinic</span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
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
                    <h5><i class="fas fa-chart-bar"></i> Entities Overview</h5>
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
// ============================================
// BAR CHART - Entities Overview
// ============================================
Highcharts.chart('barchart', {
    chart: {
        type: 'column',
        backgroundColor: 'transparent',
        style: {
            fontFamily: "'Inter', -apple-system, sans-serif"
        }
    },
    title: {
        text: '',
        style: { display: 'none' }
    },
    xAxis: {
        categories: ['Doctors', 'Hospitals', 'Labs', 'Blood Banks'],
        labels: {
            style: { color: '#64748b', fontWeight: '600' }
        },
        gridLineWidth: 0,
        lineColor: '#e2e8f0'
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Count',
            style: { color: '#64748b', fontWeight: '600' }
        },
        gridLineColor: '#f1f5f9',
        labels: {
            style: { color: '#64748b' }
        }
    },
    legend: {
        enabled: false
    },
    tooltip: {
        backgroundColor: 'rgba(255,255,255,0.95)',
        borderColor: '#e2e8f0',
        borderRadius: 12,
        borderWidth: 1,
        shadow: true,
        padding: 12,
        pointFormat: '<span style="font-size:0.9rem; font-weight:600;">Total: <b>{point.y}</b></span>'
    },
    colors: ['#6366f1', '#22c55e', '#f59e0b', '#ef4444'],
    series: [{
        name: 'Entities',
        data: [<?php echo $doctors_count; ?>, <?php echo $hospitals_count; ?>, <?php echo $labs_count; ?>, <?php echo $blood_banks_count; ?>],
        borderRadius: 6,
        colorByPoint: true,
        dataLabels: {
            enabled: true,
            style: {
                fontWeight: '700',
                color: '#0f172a',
                fontSize: '0.8rem'
            }
        }
    }],
    credits: { enabled: false }
});

// ============================================
// PIE CHART - Distribution
// ============================================
Highcharts.chart('piechart', {
    chart: {
        type: 'pie',
        backgroundColor: 'transparent',
        style: {
            fontFamily: "'Inter', -apple-system, sans-serif"
        }
    },
    title: {
        text: '',
        style: { display: 'none' }
    },
    tooltip: {
        backgroundColor: 'rgba(255,255,255,0.95)',
        borderColor: '#e2e8f0',
        borderRadius: 12,
        borderWidth: 1,
        shadow: true,
        padding: 12,
        pointFormat: '<span style="font-size:0.9rem; font-weight:600;">{point.percentage:.1f}%</span><br/><span style="color:#64748b;">{point.name}</span>'
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b><br/>{point.percentage:.1f}%',
                style: {
                    color: '#0f172a',
                    fontWeight: '600',
                    fontSize: '0.7rem'
                },
                connectorColor: '#e2e8f0'
            },
            showInLegend: false,
            size: '85%'
        }
    },
    colors: ['#6366f1', '#22c55e', '#f59e0b', '#ef4444'],
    series: [{
        name: 'Distribution',
        colorByPoint: true,
        data: [
            { name: 'Doctors', y: <?php echo $doctors_count; ?> },
            { name: 'Hospitals', y: <?php echo $hospitals_count; ?> },
            { name: 'Labs', y: <?php echo $labs_count; ?> },
            { name: 'Blood Banks', y: <?php echo $blood_banks_count; ?> }
        ]
    }],
    credits: { enabled: false }
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>