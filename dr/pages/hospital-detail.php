<?php include '../includes/header.php'; ?>

<?php
// submit review
if (isset($_REQUEST['entity_id']) && $_REQUEST['entity_id'] != 0) {
    $entity_id = $_REQUEST['entity_id'];
    $commenter_name = isset($_POST['reviewer_name']) ? trim($_POST['reviewer_name']) : '';
    $commenter_gmail = isset($_POST['reviewer_email']) ? trim($_POST['reviewer_email']) : '';
    $comment = isset($_POST['review_comment']) ? trim($_POST['review_comment']) : '';
    $stars = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;

    $insert_query = "INSERT INTO feedback (entity_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at) 
                    VALUES ($entity_id,
                    '" . mysqli_real_escape_string($con, $commenter_name) . "', 
                    '" . mysqli_real_escape_string($con, $commenter_gmail) . "', 
                    '" . mysqli_real_escape_string($con, $comment) . "', 
                    $stars, 1, NOW(), NOW())";
    $feedback_run = mysqli_query($con, $insert_query);
}

// Get hospital ID from URL
$hospital_id = isset($_GET['hospital_id']) ? (int)$_GET['hospital_id'] : 0;

// Fetch hospital details
$hospital_query = "SELECT h.*, c.city_name 
                   FROM hospitals h 
                   LEFT JOIN cities c ON h.city_id = c.city_id 
                   LEFT JOIN entities e ON e.entity_id = h.entity_id
                   WHERE h.hospital_id = $hospital_id AND e.status = 1 AND h.approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital = mysqli_fetch_assoc($hospital_result);

if (!$hospital) {
    header('Location: ' . BASE_URL . 'hospitals');
    exit();
}

$entity_id = $hospital['entity_id'];

// Fetch beds data
$beds_query = "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id";
$beds_result = mysqli_query($con, $beds_query);
$beds = mysqli_fetch_assoc($beds_result);

// Fetch facilities data
$facilities_query = "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id";
$facilities_result = mysqli_query($con, $facilities_query);
$facilities = [];
while ($row = mysqli_fetch_assoc($facilities_result)) {
    $facilities[] = $row;
}
$total_facilities = count($facilities);
$available_facilities = 0;
foreach ($facilities as $fac) {
    if ($fac['is_available'] == 1) $available_facilities++;
}

// Fetch doctors in this hospital
$doctors_query = "SELECT d.*, dct.type as specialization 
                  FROM doctors d
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  LEFT JOIN entities e ON d.entity_id = e.entity_id
                  WHERE d.hospital_id = $hospital_id AND e.status = 1 AND d.approve = 1
                  ORDER BY d.doctor_name ASC";
$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = mysqli_num_rows($doctors_result);

// Fetch feedbacks
$feedback_query = "SELECT f.* FROM feedback f WHERE f.entity_id = $entity_id AND f.status = 1 ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);

// Calculate average rating
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews 
                 FROM feedback WHERE entity_id = $entity_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;
?>

<!-- Navbar -->
<?php include BASE_PATH . '/includes/menu.php'; ?>

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

/* ===== HERO SECTION ===== */
.hospital-hero {
    background: linear-gradient(135deg, #0d6efd 0%, #4facfe 100%);
    padding: 80px 0 50px;
    color: white;
    position: relative;
    overflow: hidden;
}

.hospital-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}

.hospital-hero::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.hospital-hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 40px;
}

.hospital-hero-image {
    flex-shrink: 0;
}

.hospital-hero-image img {
    width: 180px;
    height: 180px;
    border-radius: 20px;
    object-fit: cover;
    border: 4px solid rgba(255,255,255,0.3);
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.hospital-hero-image .placeholder {
    width: 180px;
    height: 180px;
    border-radius: 20px;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: rgba(255,255,255,0.5);
    border: 4px solid rgba(255,255,255,0.2);
}

.hospital-hero-info {
    flex: 1;
}

.hospital-hero-info h1 {
    font-size: 2.8rem;
    font-weight: 800;
    margin-bottom: 10px;
}

.hospital-hero-info .sub-info {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 6px;
}

.hospital-hero-info .sub-info i {
    margin-right: 8px;
}

.hospital-hero-info .rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.15);
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    margin-top: 10px;
}

.hospital-hero-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.btn-call-hero {
    background: white;
    color: var(--primary);
    border: none;
    padding: 12px 30px;
    border-radius: 12px;
    font-weight: 700;
    transition: all 0.3s ease;
}

.btn-call-hero:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.btn-appointment-hero {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 2px solid rgba(255,255,255,0.3);
    padding: 12px 30px;
    border-radius: 12px;
    font-weight: 700;
    transition: all 0.3s ease;
}

.btn-appointment-hero:hover {
    background: white;
    color: var(--primary);
}

/* ===== SECTION PADDING ===== */
.section-padding {
    padding: 60px 0;
}

/* ===== STATS BAR ===== */
.stats-bar {
    background: white;
    border-radius: 16px;
    padding: 24px 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 16px;
    margin-top: -30px;
    position: relative;
    z-index: 2;
}

.stat-item {
    text-align: center;
}

.stat-item .stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text);
}

.stat-item .stat-label {
    font-size: 0.8rem;
    color: var(--muted);
    font-weight: 500;
}

/* ===== DETAIL GRID ===== */
.detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

/* ===== INFO CARDS ===== */
.info-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    border: 1px solid var(--border);
    margin-bottom: 24px;
}

.info-card-header {
    padding: 16px 24px;
    background: #f8fafc;
    border-bottom: 1px solid var(--border);
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

/* ===== BEDS GRID ===== */
.beds-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 12px;
}

.bed-item {
    background: #f8fafc;
    border-radius: 10px;
    padding: 14px;
    text-align: center;
    border: 1px solid var(--border);
}

.bed-item .bed-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.bed-item .bed-label {
    font-size: 0.7rem;
    color: var(--muted);
    font-weight: 500;
}

/* ===== FACILITIES ===== */
.facilities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px;
}

.facility-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.facility-item:hover {
    border-color: var(--primary);
}

.facility-item .facility-status {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.facility-item .facility-status.available {
    background: var(--success);
}

.facility-item .facility-status.unavailable {
    background: var(--danger);
}

.facility-item .facility-name {
    font-weight: 500;
    font-size: 0.85rem;
    color: var(--text);
}

/* ===== DOCTOR CARD ===== */
.doctor-mini-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid var(--border);
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.doctor-mini-card:hover {
    border-color: var(--primary);
    background: #f0f7ff;
}

.doctor-mini-card .doctor-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
}

.doctor-mini-card .doctor-avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.2rem;
}

.doctor-mini-card .doctor-info {
    flex: 1;
}

.doctor-mini-card .doctor-info h6 {
    margin: 0;
    font-weight: 700;
    font-size: 0.95rem;
}

.doctor-mini-card .doctor-info .doctor-spec {
    font-size: 0.8rem;
    color: var(--muted);
}

/* ===== FEEDBACK ===== */
.feedback-item {
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
}

.feedback-item:last-child {
    border-bottom: none;
}

.feedback-item .feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.feedback-item .feedback-name {
    font-weight: 600;
    font-size: 0.95rem;
}

.feedback-item .feedback-rating {
    color: var(--warning);
    font-size: 0.85rem;
}

.feedback-item .feedback-comment {
    color: var(--text);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 4px;
}

.feedback-item .feedback-date {
    font-size: 0.75rem;
    color: var(--muted);
}

/* ===== REVIEW FORM ===== */
.review-form-card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    border: 1px solid var(--border);
}

.review-form-card h4 {
    font-weight: 700;
    margin-bottom: 20px;
}

.review-form-card .form-label {
    font-weight: 600;
    font-size: 0.9rem;
}

.review-form-card .form-control {
    border-radius: 10px;
    border: 2px solid var(--border);
    padding: 10px 14px;
}

.review-form-card .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,172,254,0.15);
}

.review-form-card .btn-submit {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.3s ease;
}

.review-form-card .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(13,110,253,0.3);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .hospital-hero-content {
        flex-direction: column;
        text-align: center;
    }
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .hospital-hero-info h1 {
        font-size: 2.2rem;
    }
    .hospital-hero-actions {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .hospital-hero {
        padding: 60px 0 40px;
    }
    .hospital-hero-image img,
    .hospital-hero-image .placeholder {
        width: 120px;
        height: 120px;
    }
    .hospital-hero-info h1 {
        font-size: 1.8rem;
    }
    .stats-bar {
        grid-template-columns: repeat(2, 1fr);
    }
    .facilities-grid {
        grid-template-columns: 1fr;
    }
    .beds-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .section-padding {
        padding: 40px 0;
    }
}

@media (max-width: 480px) {
    .hospital-hero-info h1 {
        font-size: 1.5rem;
    }
    .stats-bar {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding: 16px;
    }
    .beds-grid {
        grid-template-columns: 1fr 1fr;
    }
    .review-form-card {
        padding: 20px;
    }
}
</style>

<!-- ===== HERO SECTION ===== -->
<section class="hospital-hero">
    <div class="container">
        <div class="hospital-hero-content">
            <div class="hospital-hero-image">
                <?php if (!empty($hospital['hospital_pic']) && file_exists(BASE_PATH . '/admin/inc/uploads/hospitals/' . $hospital['hospital_pic'])): ?>
                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital['hospital_pic']; ?>" 
                         alt="<?php echo htmlspecialchars($hospital['hospital_name']); ?>">
                <?php else: ?>
                    <div class="placeholder"><i class="fas fa-hospital"></i></div>
                <?php endif; ?>
            </div>
            <div class="hospital-hero-info">
                <h1><?php echo htmlspecialchars($hospital['hospital_name']); ?></h1>
                <p class="sub-info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hospital['city_name']); ?></p>
                <p class="sub-info"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($hospital['hospital_phone']); ?></p>
                <div class="rating-badge">
                    <i class="fas fa-star text-warning"></i>
                    <?php echo $avg_rating > 0 ? $avg_rating : 'New'; ?>
                    <span>(<?php echo $total_reviews; ?> reviews)</span>
                </div>
                <div class="hospital-hero-actions">
                    <a href="tel:<?php echo $hospital['hospital_phone']; ?>" class="btn-call-hero">
                        <i class="fas fa-phone-alt me-2"></i> Call Now
                    </a>
                    <a href="#reviews" class="btn-appointment-hero">
                        <i class="fas fa-star me-2"></i> Write a Review
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== STATS BAR ===== -->
<div class="container">
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-number"><?php echo $total_doctors; ?></div>
            <div class="stat-label">Doctors</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $beds ? $beds['total_beds'] : 0; ?></div>
            <div class="stat-label">Total Beds</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $available_facilities; ?>/<?php echo $total_facilities; ?></div>
            <div class="stat-label">Facilities</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $total_reviews; ?></div>
            <div class="stat-label">Reviews</div>
        </div>
    </div>
</div>

<!-- ===== DETAIL SECTION ===== -->
<section class="section-padding">
    <div class="container">
        <div class="detail-grid">

            <!-- ===== LEFT COLUMN ===== -->
            <div class="left-column">

                <!-- Address -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="fas fa-map-pin"></i> Address</h5>
                    </div>
                    <div class="info-card-body">
                        <p><?php echo nl2br(htmlspecialchars($hospital['hospital_address'])); ?></p>
                    </div>
                </div>

                <!-- Beds -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="fas fa-bed"></i> Bed Availability</h5>
                    </div>
                    <div class="info-card-body">
                        <?php if ($beds && ($beds['total_beds'] > 0 || $beds['icu_beds'] > 0 || $beds['general_beds'] > 0 || $beds['private_beds'] > 0)): ?>
                            <div class="beds-grid">
                                <div class="bed-item">
                                    <div class="bed-number"><?php echo $beds['total_beds']; ?></div>
                                    <div class="bed-label">Total Beds</div>
                                </div>
                                <div class="bed-item">
                                    <div class="bed-number"><?php echo $beds['icu_beds']; ?></div>
                                    <div class="bed-label">ICU Beds</div>
                                </div>
                                <div class="bed-item">
                                    <div class="bed-number"><?php echo $beds['general_beds']; ?></div>
                                    <div class="bed-label">General Beds</div>
                                </div>
                                <div class="bed-item">
                                    <div class="bed-number"><?php echo $beds['private_beds']; ?></div>
                                    <div class="bed-label">Private Beds</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No bed information available</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Facilities -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="fas fa-concierge-bell"></i> Facilities & Services</h5>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_facilities > 0): ?>
                            <div class="facilities-grid">
                                <?php foreach ($facilities as $facility): ?>
                                    <div class="facility-item">
                                        <span class="facility-status <?php echo $facility['is_available'] == 1 ? 'available' : 'unavailable'; ?>"></span>
                                        <span class="facility-name"><?php echo htmlspecialchars($facility['facility_name']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No facilities available</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Doctors -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="fas fa-user-md"></i> Doctors (<?php echo $total_doctors; ?>)</h5>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_doctors > 0): ?>
                            <?php while ($doctor = mysqli_fetch_assoc($doctors_result)): ?>
                                <div class="doctor-mini-card">
                                    <?php if (!empty($doctor['doctor_pic'])): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                             alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
                                    <?php else: ?>
                                        <div class="doctor-avatar-placeholder">
                                            <?php echo strtoupper(substr($doctor['doctor_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="doctor-info">
                                        <h6>Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></h6>
                                        <span class="doctor-spec"><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>doctor-detail?doctor_id=<?php echo $doctor['doctor_id']; ?>" 
                                       class="btn btn-sm btn-primary">View</a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No doctors registered at this hospital</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ===== RIGHT COLUMN ===== -->
            <div class="right-column">

                <!-- Reviews -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="fas fa-star"></i> Patient Reviews (<?php echo $total_reviews; ?>)</h5>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_reviews > 0): ?>
                            <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                                <div class="feedback-item">
                                    <div class="feedback-header">
                                        <span class="feedback-name"><?php echo htmlspecialchars($feedback['commenter_name']); ?></span>
                                        <span class="feedback-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 0.8rem;"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($feedback['comment'])): ?>
                                        <p class="feedback-comment"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                                    <?php endif; ?>
                                    <div class="feedback-date">
                                        <i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($feedback['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comments fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No reviews yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Review Form -->
                <div id="reviews" class="review-form-card">
                    <h4><i class="fas fa-pen me-2"></i> Write a Review</h4>
                    <form method="POST">
                        <input type="hidden" name="entity_id" value="<?php echo $entity_id; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="reviewer_name" class="form-control" required placeholder="Enter your name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="reviewer_email" class="form-control" required placeholder="your@email.com">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Rating *</label>
                            <div class="rating-input">
                                <select name="rating" class="form-control" required>
                                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ Very Good</option>
                                    <option value="3">⭐⭐⭐ Good</option>
                                    <option value="2">⭐⭐ Fair</option>
                                    <option value="1">⭐ Poor</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Your Review *</label>
                            <textarea name="review_comment" class="form-control" rows="4" required placeholder="Share your experience..."></textarea>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane me-2"></i> Submit Review
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include BASE_PATH . '/includes/footer.php'; ?>