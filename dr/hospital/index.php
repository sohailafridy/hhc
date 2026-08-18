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
$hospital_query = "SELECT h.*, c.city_name 
                   FROM hospitals h 
                   LEFT JOIN cities c ON h.city_id = c.city_id 
                   WHERE h.user_id = $user_id AND h.approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];
$entity_id = $hospital_data['entity_id'];

// ============================================
// STATISTICS
// ============================================
$doctors_query = "SELECT COUNT(*) as total FROM doctors WHERE hospital_id = $hospital_id AND approve = 1";
$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = mysqli_fetch_assoc($doctors_result)['total'];

$beds_query = "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id";
$beds_result = mysqli_query($con, $beds_query);
$beds = mysqli_fetch_assoc($beds_result);
$total_beds = $beds ? $beds['total_beds'] : 0;

$facilities_query = "SELECT COUNT(*) as total FROM hospital_facilities WHERE hospital_id = $hospital_id";
$facilities_result = mysqli_query($con, $facilities_query);
$total_facilities = mysqli_fetch_assoc($facilities_result)['total'];

$available_facilities_query = "SELECT COUNT(*) as total FROM hospital_facilities 
                               WHERE hospital_id = $hospital_id AND is_available = 1";
$available_facilities_result = mysqli_query($con, $available_facilities_query);
$available_facilities = mysqli_fetch_assoc($available_facilities_result)['total'];

$reviews_query = "SELECT COUNT(*) as total FROM feedback WHERE entity_id = $entity_id AND status = 1";
$reviews_result = mysqli_query($con, $reviews_query);
$total_reviews = mysqli_fetch_assoc($reviews_result)['total'];

$rating_query = "SELECT AVG(stars) as avg_rating FROM feedback WHERE entity_id = $entity_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;

// Recent Doctors
$recent_doctors_query = "SELECT d.*, dct.type as specialization 
                         FROM doctors d
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                         WHERE d.hospital_id = $hospital_id AND d.approve = 1
                         ORDER BY d.created_at DESC LIMIT 5";
$recent_doctors_result = mysqli_query($con, $recent_doctors_query);

// Recent Reviews
$recent_reviews_query = "SELECT * FROM feedback 
                         WHERE entity_id = $entity_id AND status = 1
                         ORDER BY created_at DESC LIMIT 5";
$recent_reviews_result = mysqli_query($con, $recent_reviews_query);
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
}

.content-wrapper {
    background: var(--bg);
    min-height: 100vh;
    padding: 24px 32px 60px;
}

/* ===== WELCOME SECTION - FIXED ===== */
.welcome-section {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 20px;
    padding: 35px 40px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(79, 70, 229, 0.25);
}

.welcome-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -15%;
    width: 350px;
    height: 350px;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 50%;
}

.welcome-section::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: -10%;
    width: 250px;
    height: 250px;
    background: rgba(255, 255, 255, 0.04);
    border-radius: 50%;
}

.welcome-content {
    position: relative;
    z-index: 1;
}

.welcome-content h1 {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 6px;
    color: #ffffff;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
}

.welcome-content .welcome-sub {
    font-size: 1rem;
    opacity: 0.92;
    margin-bottom: 12px;
    color: rgba(255, 255, 255, 0.9);
}

.welcome-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 6px;
}

.welcome-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 500;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: all 0.3s ease;
}

.welcome-badge:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}

.welcome-badge i {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
}

/* ===== STATS ROW ===== */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 14px;
    padding: 20px 22px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.stat-icon.blue {
    background: #dbeafe;
    color: #1e40af;
}

.stat-icon.green {
    background: #d1fae5;
    color: #065f46;
}

.stat-icon.orange {
    background: #fef3c7;
    color: #92400e;
}

.stat-icon.purple {
    background: #ede9fe;
    color: #5b21b6;
}

.stat-icon.red {
    background: #fee2e2;
    color: #991b1b;
}

.stat-info .stat-number {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text);
    line-height: 1.2;
}

.stat-info .stat-label {
    font-size: 0.8rem;
    color: var(--muted);
    font-weight: 500;
}

/* ===== GRID ===== */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

/* ===== CARDS ===== */
.info-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid var(--border);
}

.info-card-header {
    padding: 14px 20px;
    background: #f8fafc;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.info-card-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-card-header h5 i {
    color: var(--primary);
}

.info-card-body {
    padding: 16px 20px;
}

/* ===== DOCTOR ITEM ===== */
.doctor-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}

.doctor-item:last-child {
    border-bottom: none;
}

.doctor-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.doctor-item .doctor-info {
    flex: 1;
}

.doctor-item .doctor-info .name {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text);
}

.doctor-item .doctor-info .spec {
    font-size: 0.75rem;
    color: var(--muted);
}

.doctor-item .doctor-info .date {
    font-size: 0.7rem;
    color: var(--muted);
}

/* ===== REVIEW ITEM ===== */
.review-item {
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}

.review-item:last-child {
    border-bottom: none;
}

.review-item .review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.review-item .review-name {
    font-weight: 600;
    font-size: 0.85rem;
}

.review-item .review-stars {
    color: var(--warning);
    font-size: 0.8rem;
}

.review-item .review-comment {
    font-size: 0.85rem;
    color: var(--muted);
    margin: 4px 0 0;
}

.review-item .review-date {
    font-size: 0.7rem;
    color: var(--muted);
}

/* ===== QUICK LINKS ===== */
.quick-links {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.quick-link-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid var(--border);
    text-decoration: none;
    color: var(--text);
    transition: all 0.3s ease;
}

.quick-link-item:hover {
    border-color: var(--primary);
    background: #f0f7ff;
    transform: translateX(4px);
}

.quick-link-item i {
    color: var(--primary);
    font-size: 1.1rem;
}

.quick-link-item span {
    font-weight: 600;
    font-size: 0.85rem;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .content-wrapper {
        padding: 16px;
    }
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .welcome-content h1 {
        font-size: 1.5rem;
    }
    .quick-links {
        grid-template-columns: 1fr;
    }
    .welcome-badges {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    .stat-card {
        padding: 14px 16px;
    }
    .welcome-section {
        padding: 24px 20px;
    }
}
</style>

<div class="content-wrapper">

    <!-- ===== WELCOME SECTION - FIXED ===== -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>👋 Welcome, <?php echo htmlspecialchars($hospital_data['hospital_name']); ?></h1>
            <p class="welcome-sub">Manage your hospital dashboard, doctors, beds, facilities and more.</p>
            <div class="welcome-badges">
                <span class="welcome-badge">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo htmlspecialchars($hospital_data['city_name'] ?? 'N/A'); ?>
                </span>
                <span class="welcome-badge">
                    <i class="fas fa-phone"></i>
                    <?php echo htmlspecialchars($hospital_data['hospital_phone']); ?>
                </span>
                <span class="welcome-badge">
                    <i class="fas fa-envelope"></i>
                    <?php echo htmlspecialchars($hospital_data['hospital_email'] ?? 'N/A'); ?>
                </span>
                <span class="welcome-badge">
                    <i class="fas fa-calendar-alt"></i>
                    Member since <?php echo date('M Y', strtotime($hospital_data['created_at'])); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ===== STATS ROW ===== -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $total_doctors; ?></div>
                <div class="stat-label">Total Doctors</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-bed"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $total_beds; ?></div>
                <div class="stat-label">Total Beds</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-concierge-bell"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $available_facilities; ?>/<?php echo $total_facilities; ?></div>
                <div class="stat-label">Facilities Available</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?></div>
                <div class="stat-label">Rating (<?php echo $total_reviews; ?> reviews)</div>
            </div>
        </div>
    </div>

    <!-- ===== DASHBOARD GRID ===== -->
    <div class="dashboard-grid">

        <!-- ===== LEFT COLUMN ===== -->
        <div class="left-column">

            <!-- Recent Doctors -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-user-md"></i> Recent Doctors</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/doctors/list" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($recent_doctors_result) > 0): ?>
                        <?php while ($doctor = mysqli_fetch_assoc($recent_doctors_result)): ?>
                            <div class="doctor-item">
                                <div class="doctor-avatar">
                                    <?php echo strtoupper(substr($doctor['doctor_name'], 0, 1)); ?>
                                </div>
                                <div class="doctor-info">
                                    <div class="name">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></div>
                                    <div class="spec"><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></div>
                                    <div class="date">Added: <?php echo date('d M Y', strtotime($doctor['created_at'])); ?></div>
                                </div>
                                <a href="<?php echo BASE_URL; ?>hospital/doctor-add?id=<?php echo $doctor['doctor_id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">Edit</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No doctors registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- ===== RIGHT COLUMN ===== -->
        <div class="right-column">

            <!-- Quick Links -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="info-card-body">
                    <div class="quick-links">
                        <a href="<?php echo BASE_URL; ?>hospital/profile" class="quick-link-item">
                            <i class="fas fa-edit"></i>
                            <span>Edit Profile</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/doctors/list" class="quick-link-item">
                            <i class="fas fa-user-md"></i>
                            <span>Manage Doctors</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/beds" class="quick-link-item">
                            <i class="fas fa-bed"></i>
                            <span>Manage Beds</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/facilities" class="quick-link-item">
                            <i class="fas fa-concierge-bell"></i>
                            <span>Manage Facilities</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/feedback" class="quick-link-item">
                            <i class="fas fa-star"></i>
                            <span>View Reviews</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/recycle" class="quick-link-item" style="color: var(--danger);">
                            <i class="fas fa-trash"></i>
                            <span>Recycle Bin</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>logout" class="quick-link-item" style="color: var(--danger);">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Recent Reviews</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/feedback" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($recent_reviews_result) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($recent_reviews_result)): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="review-name"><?php echo htmlspecialchars($review['commenter_name']); ?></span>
                                    <span class="review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['stars'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 0.75rem;"></i>
                                        <?php endfor; ?>
                                    </span>
                                </div>
                                <?php if (!empty($review['comment'])): ?>
                                    <p class="review-comment"><?php echo htmlspecialchars(substr($review['comment'], 0, 80)) . (strlen($review['comment']) > 80 ? '...' : ''); ?></p>
                                <?php endif; ?>
                                <div class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No reviews yet.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</div>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>