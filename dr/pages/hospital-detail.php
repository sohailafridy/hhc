<?php include '../includes/header.php'; ?>

<?php
// submit review
if (isset($_POST['user_id']) && (int)$_POST['user_id'] != 0) {
    $user_id = (int)$_POST['user_id'];
    $commenter_name = isset($_POST['reviewer_name']) ? trim($_POST['reviewer_name']) : '';
    $commenter_gmail = isset($_POST['reviewer_email']) ? trim($_POST['reviewer_email']) : '';
    $comment = isset($_POST['review_comment']) ? trim($_POST['review_comment']) : '';
    $stars = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;

    $insert_query = "INSERT INTO feedback (user_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at) 
                    VALUES ($user_id,
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
                   LEFT JOIN users u ON u.user_id = h.user_id
                   WHERE h.hospital_id = $hospital_id AND u.status = 1 AND h.approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital = mysqli_fetch_assoc($hospital_result);

if (!$hospital) {
    header('Location: ' . BASE_URL . 'hospitals');
    exit();
}

$user_id = (int)$hospital['user_id'];

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
$doctors_query = "SELECT d.*, dct.type as specialization, dih.doctor_in_hosp_id
                  FROM doctor_in_hospital dih
                  INNER JOIN doctors d ON d.doctor_id = dih.doctor_id
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  LEFT JOIN users u ON d.user_id = u.user_id
                  WHERE dih.hospital_id = $hospital_id AND u.status = 1 AND d.approve = 1 AND dih.inactive = 0
                  ORDER BY d.doctor_name ASC";
$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = mysqli_num_rows($doctors_result);

// Fetch feedbacks
$feedback_query = "SELECT f.* FROM feedback f WHERE f.user_id = $user_id AND f.status = 1 ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);

// Calculate average rating
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews 
                 FROM feedback WHERE user_id = $user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;
?>

<!-- Navbar -->
<?php include BASE_PATH . '/includes/menu.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/hospital-detail.css">
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
                                    <div>
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
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
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