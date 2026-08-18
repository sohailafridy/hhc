<?php
// ============================================
// START SESSION & INCLUDE CONFIG
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];
$hospital_name = $hospital_data['hospital_name'];

// ============================================
// GET DOCTOR ID
// ============================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . BASE_URL . "hospital/doctors.php");
    exit();
}

$doctor_id = (int)$_GET['id'];

// ============================================
// FETCH DOCTOR DETAILS
// ============================================
$query = "SELECT d.*, 
                 c.city_name,
                 dct.type as specialization,
                 e.status as estatus,
                 e.reference as ref,
                 u.username,
                 u.email as user_email
          FROM doctors d
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
          LEFT JOIN entities e ON d.entity_id = e.entity_id
          LEFT JOIN users u ON d.user_id = u.user_id
          WHERE d.doctor_id = $doctor_id AND d.hospital_id = $hospital_id AND d.approve = 1";

$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error_msg'] = "Doctor not found or you don't have permission.";
    header("Location: " . BASE_URL . "hospital/doctors.php");
    exit();
}

$doctor = mysqli_fetch_assoc($result);
$entity_id = $doctor['entity_id'];

// ============================================
// FETCH CLINICAL INFO
// ============================================
$clinical_query = "SELECT ci.*, h.hospital_name, h.hospital_id
                   FROM clinical_info ci
                   LEFT JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id
                   LEFT JOIN hospitals h ON dih.hospital_id = h.hospital_id
                   WHERE dih.doctor_id = $doctor_id
                   ORDER BY ci.season, ci.shift";
$clinical_result = mysqli_query($con, $clinical_query);

// ============================================
// FETCH RATING & REVIEWS
// ============================================
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews 
                 FROM feedback WHERE entity_id = $entity_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;

$reviews_query = "SELECT * FROM feedback 
                  WHERE entity_id = $entity_id AND status = 1 
                  ORDER BY created_at DESC LIMIT 10";
$reviews_result = mysqli_query($con, $reviews_query);
$total_reviews_count = mysqli_num_rows($reviews_result);
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

/* ===== PAGE HEADER ===== */
.page-header-modern {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 20px;
    padding: 30px 35px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.page-header-modern::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}

.page-header-modern::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: -10%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.page-header-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.page-header-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.doctor-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.3);
}

.doctor-avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: rgba(255,255,255,0.6);
    border: 3px solid rgba(255,255,255,0.2);
}

.page-header-title h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 4px;
}

.page-header-title p {
    margin: 0;
    opacity: 0.85;
    font-size: 0.95rem;
}

.page-header-actions {
    display: flex;
    gap: 10px;
}

.btn-action-header {
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    border: none;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}

.btn-action-header:hover {
    background: rgba(255,255,255,0.3);
    color: white;
    transform: translateY(-2px);
}

/* ===== STATS ROW ===== */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    text-align: center;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.stat-card .stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text);
}

.stat-card .stat-label {
    font-size: 0.8rem;
    color: var(--muted);
    font-weight: 500;
}

/* ===== DETAIL GRID ===== */
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.detail-grid .full-width {
    grid-column: 1 / -1;
}

/* ===== INFO CARDS ===== */
.info-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid var(--border);
    margin-bottom: 24px;
}

.info-card-header {
    padding: 16px 24px;
    background: #f8fafc;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.info-card-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 1rem;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-card-header h5 i {
    color: var(--primary);
}

.info-card-body {
    padding: 20px 24px;
}

/* ===== INFO ROWS ===== */
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    font-weight: 600;
    color: var(--muted);
    font-size: 0.9rem;
}

.info-row .value {
    font-weight: 500;
    color: var(--text);
    text-align: right;
}

/* ===== CLINICAL CARDS ===== */
.clinical-card {
    background: white;
    border-radius: 12px;
    border: 1px solid var(--border);
    margin-bottom: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.clinical-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.clinical-card-header {
    padding: 14px 20px;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
}

.clinical-card-header h6 {
    margin: 0;
    font-weight: 600;
    font-size: 0.95rem;
}

.clinical-card-body {
    padding: 16px 20px;
}

.clinical-info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
}

.clinical-info-item:last-child {
    border-bottom: none;
}

.clinical-info-item i {
    width: 24px;
    color: var(--primary);
    font-size: 1rem;
}

.clinical-info-item .label {
    font-weight: 500;
    color: var(--muted);
    font-size: 0.85rem;
    min-width: 100px;
}

.clinical-info-item .value {
    font-weight: 500;
    color: var(--text);
    font-size: 0.85rem;
}

/* ===== REVIEW ITEMS ===== */
.review-item {
    padding: 14px 0;
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
    font-size: 0.9rem;
}

.review-item .review-rating {
    color: var(--warning);
    font-size: 0.85rem;
}

.review-item .review-comment {
    color: var(--text);
    font-size: 0.9rem;
    line-height: 1.6;
    margin: 4px 0 0;
}

.review-item .review-date {
    font-size: 0.75rem;
    color: var(--muted);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .content-wrapper { padding: 16px; }
}

@media (max-width: 768px) {
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    .page-header-actions {
        width: 100%;
        flex-wrap: wrap;
    }
    .page-header-left {
        flex-direction: column;
        text-align: center;
        width: 100%;
    }
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    .page-header-actions {
        justify-content: center;
    }
}
</style>

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header-modern">
        <div class="page-header-content">
            <div class="page-header-left">
                <?php if (!empty($doctor['doctor_pic'])): ?>
                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                         alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
                <?php else: ?>
                    <div class="doctor-avatar-placeholder">
                        <i class="fas fa-user-md"></i>
                    </div>
                <?php endif; ?>
                <div class="page-header-title">
                    <h1>Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></h1>
                    <p>
                        <i class="fas fa-stethoscope me-1"></i> <?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-hospital me-1"></i> <?php echo htmlspecialchars($hospital_name); ?>
                        <span class="mx-2">|</span>
                        <span class="badge <?php echo $doctor['estatus'] == 1 ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $doctor['estatus'] == 1 ? 'Active' : 'Inactive'; ?>
                        </span>
                    </p>
                </div>
            </div>
            <div class="page-header-actions">
                <a href="<?php echo BASE_URL; ?>hospital/doctor-add.php?id=<?php echo $doctor['doctor_id']; ?>" class="btn-action-header">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="<?php echo BASE_URL; ?>hospital/doctors.php" class="btn-action-header">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- ===== STATS ROW ===== -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number"><?php echo $doctor['experience_years'] ?? 0; ?></div>
            <div class="stat-label">Experience (Years)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?></div>
            <div class="stat-label">Rating</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_reviews; ?></div>
            <div class="stat-label">Reviews</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo mysqli_num_rows($clinical_result); ?></div>
            <div class="stat-label">Clinical Records</div>
        </div>
    </div>

    <!-- ===== DETAIL GRID ===== -->
    <div class="detail-grid">

        <!-- ===== LEFT COLUMN ===== -->
        <div class="left-column">

            <!-- Personal Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-user"></i> Personal Information</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="label">Doctor Name</span>
                        <span class="value">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['doctor_email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['doctor_phone']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Gender</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['gender']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Experience</span>
                        <span class="value"><?php echo $doctor['experience_years'] ?? 0; ?> Years</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Specialization</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">City</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['city_name'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-lock"></i> Account Information</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="label">Username</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['username'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['user_email'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Status</span>
                        <span class="value">
                            <?php if ($doctor['estatus'] == 1): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($doctor['estatus'] == 0 && !empty($doctor['ref'])): ?>
                        <div class="info-row">
                            <span class="label">Inactive Reason</span>
                            <span class="value text-danger"><?php echo htmlspecialchars($doctor['ref']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span class="label">Created At</span>
                        <span class="value"><?php echo date('d M Y, h:i A', strtotime($doctor['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Short Detail & Other -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-info-circle"></i> Additional Details</h5>
                </div>
                <div class="info-card-body">
                    <?php if (!empty($doctor['short_detail'])): ?>
                        <div class="info-row">
                            <span class="label">Qualifications</span>
                            <span class="value" style="text-align: right;"><?php echo htmlspecialchars($doctor['short_detail']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($doctor['other'])): ?>
                        <div class="info-row">
                            <span class="label">Other Info</span>
                            <span class="value" style="text-align: right;"><?php echo htmlspecialchars($doctor['other']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($doctor['static_clinical_info'])): ?>
                        <div class="info-row">
                            <span class="label">Clinical Notes</span>
                            <span class="value" style="text-align: right;"><?php echo nl2br(htmlspecialchars($doctor['static_clinical_info'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- ===== RIGHT COLUMN ===== -->
        <div class="right-column">

            <!-- Clinical Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-clock"></i> Clinical Information</h5>
                    <span class="badge bg-primary"><?php echo mysqli_num_rows($clinical_result); ?> Records</span>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($clinical_result) > 0): ?>
                        <?php 
                        // Group by season
                        $seasons = ['Summer' => [], 'Winter' => [], 'General' => []];
                        mysqli_data_seek($clinical_result, 0);
                        while ($row = mysqli_fetch_assoc($clinical_result)) {
                            $season = !empty($row['season']) ? $row['season'] : 'General';
                            if (!isset($seasons[$season])) {
                                $seasons[$season] = [];
                            }
                            $seasons[$season][] = $row;
                        }
                        ?>
                        
                        <?php foreach ($seasons as $season_name => $records): ?>
                            <?php if (!empty($records)): ?>
                                <div class="season-group mb-3">
                                    <h6 class="fw-bold mb-2">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo $season_name; ?> Season
                                        <span class="badge bg-secondary ms-1"><?php echo count($records); ?></span>
                                    </h6>
                                    <?php foreach ($records as $clinical): ?>
                                        <div class="clinical-card">
                                            <div class="clinical-card-header">
                                                <i class="fas fa-hospital"></i>
                                                <h6>
                                                    <?php 
                                                    if (!empty($clinical['hospital_name'])) {
                                                        echo htmlspecialchars($clinical['hospital_name']);
                                                    } else {
                                                        echo 'Personal Clinic';
                                                    }
                                                    ?>
                                                </h6>
                                            </div>
                                            <div class="clinical-card-body">
                                                <?php if (!empty($clinical['morning_opening_time']) || !empty($clinical['morning_closing_time'])): ?>
                                                    <div class="clinical-info-item">
                                                        <i class="fas fa-sun text-warning"></i>
                                                        <span class="label">Morning</span>
                                                        <span class="value">
                                                            <?php 
                                                            echo date('h:i A', strtotime($clinical['morning_opening_time']));
                                                            echo ' - ';
                                                            echo date('h:i A', strtotime($clinical['morning_closing_time']));
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($clinical['evening_opening_time']) || !empty($clinical['evening_closing_time'])): ?>
                                                    <div class="clinical-info-item">
                                                        <i class="fas fa-moon text-primary"></i>
                                                        <span class="label">Evening</span>
                                                        <span class="value">
                                                            <?php 
                                                            echo date('h:i A', strtotime($clinical['evening_opening_time']));
                                                            echo ' - ';
                                                            echo date('h:i A', strtotime($clinical['evening_closing_time']));
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="clinical-info-item">
                                                    <i class="fas fa-calendar-day"></i>
                                                    <span class="label">Working Days</span>
                                                    <span class="value"><?php echo htmlspecialchars($clinical['days'] ?? 'N/A'); ?></span>
                                                </div>
                                                
                                                <div class="clinical-info-item">
                                                    <i class="fas fa-calendar-times"></i>
                                                    <span class="label">Off Days</span>
                                                    <span class="value"><?php echo htmlspecialchars($clinical['off_days'] ?? 'None'); ?></span>
                                                </div>
                                                
                                                <?php if (!empty($clinical['contact'])): ?>
                                                    <div class="clinical-info-item">
                                                        <i class="fas fa-phone"></i>
                                                        <span class="label">Contact</span>
                                                        <span class="value"><?php echo htmlspecialchars($clinical['contact']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($clinical['detail'])): ?>
                                                    <div class="clinical-info-item">
                                                        <i class="fas fa-info-circle"></i>
                                                        <span class="label">Detail</span>
                                                        <span class="value"><?php echo htmlspecialchars($clinical['detail']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No clinical information available for this doctor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Patient Reviews</h5>
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-star me-1"></i> <?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?>
                    </span>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="review-name"><?php echo htmlspecialchars($review['commenter_name']); ?></span>
                                    <span class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['stars'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 0.8rem;"></i>
                                        <?php endfor; ?>
                                    </span>
                                </div>
                                <?php if (!empty($review['comment'])): ?>
                                    <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <?php endif; ?>
                                <div class="review-date">
                                    <i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No reviews yet for this doctor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</div>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>