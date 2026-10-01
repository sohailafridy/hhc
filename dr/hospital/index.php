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

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-hospital-admin.css">

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