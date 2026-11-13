<?php 
// ============================================================
// 1. DB se Hospital ki basic info nikalna (Title & Schema ke liye)
// ============================================================
include_once '../config.php'; 
$hospital_id = isset($_GET['hospital_id']) ? (int)$_GET['hospital_id'] : 0;

$hospital_query = "SELECT h.*, c.city_name 
                   FROM hospitals h 
                   LEFT JOIN cities c ON h.city_id = c.city_id 
                   LEFT JOIN users u ON u.user_id = h.user_id
                   WHERE h.hospital_id = $hospital_id AND u.status = 1 AND h.approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_info = mysqli_fetch_assoc($hospital_result);

// ============================================================
// 2. SEO Title, Description aur Schema set karna
// ============================================================
$hosp_name_for_seo = $hospital_info ? htmlspecialchars($hospital_info['hospital_name']) : 'Hospital';
$city_name_for_seo = $hospital_info ? htmlspecialchars($hospital_info['city_name']) : 'Pakistan';

$GLOBALS['page_title'] = "$hosp_name_for_seo — Doctors, Facilities & Reviews | SehatNexa";
$GLOBALS['page_description'] = "View complete details, available beds, facilities, associated doctors, and patient reviews for $hosp_name_for_seo in $city_name_for_seo.";
$GLOBALS['page_image'] = ($hospital_info && !empty($hospital_info['hospital_pic'])) ? BASE_URL.'admin/inc/uploads/hospitals/'.$hospital_info['hospital_pic'] : BASE_URL.'admin/inc/uploads/default/hosp.jpg';

if ($hospital_info) {
    // Schema Markup for Google
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "Hospital",
        "name" => $hospital_info['hospital_name'],
        "image" => $GLOBALS['page_image'],
        "telephone" => $hospital_info['hospital_phone'],
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => $hospital_info['hospital_address'],
            "addressLocality" => $hospital_info['city_name'],
            "addressCountry" => "PK"
        ]
    ];
    $GLOBALS['page_schema'] = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

// ============================================================
// 3. Header Include karein
// ============================================================
include '../includes/header.php'; 

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

$hospital = $hospital_info;

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
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id
                  LEFT JOIN users u ON d.user_id = u.user_id
                  WHERE dih.hospital_id = $hospital_id AND u.status = 1 AND d.approve = 1 AND dih.inactive = 0
                  ORDER BY d.doctor_name ASC";
$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = mysqli_num_rows($doctors_result);

// FETCH REGISTERED LABS
$labs_query = "SELECT l.*, c.city_name 
               FROM assign_hospital ah 
               INNER JOIN laboratories l ON l.user_id = ah.user_id
               LEFT JOIN cities c ON l.city_id = c.city_id
               LEFT JOIN users u ON u.user_id = l.user_id
               WHERE ah.hospital_id = $hospital_id AND ah.status = 1 AND l.approve = 1 AND u.status = 1
               ORDER BY l.lab_name ASC";
$labs_result = mysqli_query($con, $labs_query);
$total_labs = mysqli_num_rows($labs_result);

// FETCH REGISTERED RADIOLOGY
$rad_query = "SELECT r.*, c.city_name 
              FROM assign_hospital_radiology ahr 
              INNER JOIN radiology r ON r.user_id = ahr.user_id
              LEFT JOIN cities c ON r.city_id = c.city_id
              LEFT JOIN users u ON u.user_id = r.user_id
              WHERE ahr.hospital_id = $hospital_id AND ahr.status = 1 AND r.approve = 1 AND u.status = 1
              ORDER BY r.rad_name ASC";
$rad_result = mysqli_query($con, $rad_query);
$total_radiology = $rad_result ? mysqli_num_rows($rad_result) : 0;

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

// Fetch HOSPITAL NOTES
$notes_query = "SELECT * FROM notes WHERE user_id = $user_id LIMIT 1";
$notes_result = mysqli_query($con, $notes_query);
$hospital_note = ($notes_result && mysqli_num_rows($notes_result) > 0)
    ? mysqli_fetch_assoc($notes_result)
    : null;
?>

<!-- Navbar -->
<?php include BASE_PATH . '/includes/menu.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/hospital-detail.css?v=<?= @filemtime(__DIR__ . '/style/hospital-detail.css') ?: time() ?>">

<!-- ===== HERO SECTION ===== -->
<section class="hospital-hero">
    <div class="container">
        <div class="hospital-hero-content">
            <div class="hospital-hero-image">
                <?php if (!empty($hospital['hospital_pic']) && file_exists(BASE_PATH . '/admin/inc/uploads/hospitals/' . $hospital['hospital_pic'])): ?>
                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital['hospital_pic']; ?>" 
                         alt="<?php echo htmlspecialchars($hospital['hospital_name']); ?> in <?php echo htmlspecialchars($hospital['city_name']); ?>">
                <?php else: ?>
                    <div class="placeholder"><i class="fas fa-hospital"></i></div>
                <?php endif; ?>
            </div>
            <div class="hospital-hero-info">
                <!-- SEO: H1 properly added -->
                <h1><?php echo htmlspecialchars($hospital['hospital_name']); ?></h1>
                <p class="sub-info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hospital['city_name']); ?></p>
                <p class="sub-info"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($hospital['hospital_phone']); ?></p>
                <div class="rating-badge">
                    <i class="fas fa-star text-warning"></i>
                    <?php echo $avg_rating > 0 ? $avg_rating : 'New'; ?>
                    <span>(<?php echo $total_reviews; ?> reviews)</span>
                </div>
                <div class="hospital-hero-actions">
                    <a href="tel:<?php echo htmlspecialchars($hospital['hospital_phone']); ?>" class="btn-call-hero">
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
            <!-- BUG FIX: Null check added for beds -->
            <div class="stat-number"><?php echo ($beds && isset($beds['total_beds'])) ? $beds['total_beds'] : 0; ?></div>
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

<!-- Ad Space 1 -->
<div class="container my-4" data-aos="fade-up">
    <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
        <!-- SpeedyAds Code Yahan Paste Karein -->
        <span class="text-muted small">Advertisement Space</span>
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
                        <h2><i class="fas fa-map-pin"></i> Address</h2>
                    </div>
                    <div class="info-card-body">
                        <p><?php echo nl2br(htmlspecialchars($hospital['hospital_address'])); ?></p>
                    </div>
                </div>

                <!-- ===== NOTES ===== -->
                <?php if ($hospital_note && !empty(trim($hospital_note['notes']))): ?>
                    <div class="info-card notes-card">
                        <div class="info-card-header">
                            <h2><i class="fas fa-sticky-note"></i> Notes</h2>
                            <?php if (!empty($hospital_note['updated_at'])): ?>
                                <small class="notes-updated">
                                    <i class="fas fa-clock me-1"></i>
                                    Updated: <?php echo date('d M Y, h:i A', strtotime($hospital_note['updated_at'])); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="info-card-body">
                            <div class="notes-content">
                                <?php echo nl2br(htmlspecialchars($hospital_note['notes'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Beds -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-bed"></i> Bed Availability</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($beds && (($beds['total_beds'] ?? 0) > 0 || ($beds['icu_beds'] ?? 0) > 0 || ($beds['general_beds'] ?? 0) > 0 || ($beds['private_beds'] ?? 0) > 0)): ?>
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
                        <h2><i class="fas fa-concierge-bell"></i> Facilities & Services</h2>
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
                        <h2><i class="fas fa-user-md"></i> Doctors (<?php echo $total_doctors; ?>)</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_doctors > 0): ?>
                            <?php while ($doctor = mysqli_fetch_assoc($doctors_result)): ?>
                                <div class="doctor-mini-card">
                                    <?php if (!empty($doctor['doctor_pic'])): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                             alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/doctor.jpg" 
                                             alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="mb-0 fs-6 fw-bold">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></h4>
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

                <!-- REGISTERED LABS -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-flask"></i> Registered Labs (<?php echo $total_labs; ?>)</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_labs > 0): ?>
                            <?php while ($lab = mysqli_fetch_assoc($labs_result)): ?>
                                <?php 
                                $lab_pic_val = trim($lab['lab_pic'] ?? ''); 
                                $lab_img_path = BASE_PATH . '/admin/inc/uploads/labs/' . $lab_pic_val;
                                $has_valid_pic = (!empty($lab_pic_val) && $lab_pic_val !== '0' && strtolower($lab_pic_val) !== 'null' && file_exists($lab_img_path));
                                ?>
                                <div class="doctor-mini-card">
                                    <?php if ($has_valid_pic): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/labs/<?php echo htmlspecialchars($lab_pic_val); ?>" 
                                             alt="<?php echo htmlspecialchars($lab['lab_name']); ?>" class="doctor-avatar">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/lab.jpg" 
                                             alt="<?php echo htmlspecialchars($lab['lab_name']); ?>" class="doctor-avatar">
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="mb-0 fs-6 fw-bold"><?php echo htmlspecialchars($lab['lab_name']); ?></h4>
                                        <span class="doctor-spec"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($lab['city_name']); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>lab-detail?lab_id=<?php echo $lab['lab_id']; ?>" 
                                       class="btn btn-sm btn-primary">View</a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No labs registered at this hospital</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- REGISTERED RADIOLOGY -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-x-ray"></i> Registered Radiology (<?php echo $total_radiology; ?>)</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_radiology > 0): ?>
                            <?php while ($rad = mysqli_fetch_assoc($rad_result)): ?>
                                <?php 
                                $rad_pic_val = trim($rad['rad_pic'] ?? ''); 
                                $rad_img_path = BASE_PATH . '/admin/inc/uploads/radiology/' . $rad_pic_val;
                                $has_valid_rad_pic = (!empty($rad_pic_val) && $rad_pic_val !== '0' && strtolower($rad_pic_val) !== 'null' && file_exists($rad_img_path));
                                ?>
                                <div class="doctor-mini-card">
                                    <?php if ($has_valid_rad_pic): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/radiology/<?php echo htmlspecialchars($rad_pic_val); ?>" 
                                             alt="<?php echo htmlspecialchars($rad['rad_name']); ?>" class="doctor-avatar">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/radiology.webp" 
                                             alt="<?php echo htmlspecialchars($rad['rad_name']); ?>" class="doctor-avatar">
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="mb-0 fs-6 fw-bold"><?php echo htmlspecialchars($rad['rad_name']); ?></h4>
                                        <span class="doctor-spec"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($rad['city_name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>radiology-detail?rad_id=<?php echo $rad['rad_id']; ?>" 
                                       class="btn btn-sm btn-primary">View</a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-2">No radiology centers registered at this hospital</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- ===== RIGHT COLUMN ===== -->
            <div class="right-column">

                <!-- Reviews -->
                <div class="info-card">
                    <div class="info-card-header">
                        <h2><i class="fas fa-star"></i> Patient Reviews (<?php echo $total_reviews; ?>)</h2>
                    </div>
                    <div class="info-card-body">
                        <?php if ($total_reviews > 0): ?>
                            <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                                <div class="feedback-item">
                                    <div class="feedback-header">
                                        <span class="feedback-name fw-bold"><?php echo htmlspecialchars($feedback['commenter_name']); ?></span>
                                        <span class="feedback-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 0.8rem;"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($feedback['comment'])): ?>
                                        <p class="feedback-comment mt-2"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                                    <?php endif; ?>
                                    <div class="feedback-date small text-muted mt-1">
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
                    <h3 class="mb-3"><i class="fas fa-pen me-2"></i> Write a Review</h3>
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

<!-- Ad Space 2 -->
<div class="container my-4" data-aos="fade-up">
    <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
        <!-- SpeedyAds Code Yahan Paste Karein -->
        <span class="text-muted small">Advertisement Space</span>
    </div>
</div>

<!-- Footer -->
<?php include BASE_PATH . '/includes/footer.php'; ?>