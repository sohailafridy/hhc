<?php 
// ============================================================
// 1. DB se Doctor ki basic info nikalna (Title & Schema ke liye)
// ============================================================
include_once '../config.php'; 
$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;

$doctor_query = "SELECT d.*, c.city_name, h.hospital_name 
                 FROM doctors d 
                 LEFT JOIN cities c ON d.city_id = c.city_id 
                 LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                 LEFT JOIN users u ON u.user_id = d.user_id
                 WHERE d.doctor_id = $doctor_id AND u.status = 1 AND d.approve=1";
$doctor_result = mysqli_query($con, $doctor_query);
$doctor_info = mysqli_fetch_assoc($doctor_result);

$spec_query = "SELECT * FROM dr_cat_types WHERE dr_cat_type_id = " . (int)($doctor_info['cat_type_id'] ?? 0);
$spec_result = mysqli_query($con, $spec_query);
$spec = mysqli_fetch_assoc($spec_result);

// ============================================================
// 2. SEO Title, Description aur Schema set karna
// ============================================================
$doc_name_for_seo = $doctor_info ? "Dr. " . htmlspecialchars($doctor_info['doctor_name']) : 'Doctor';
$spec_name_for_seo = $spec ? htmlspecialchars($spec['type']) : 'Specialist';
$city_name_for_seo = $doctor_info ? htmlspecialchars($doctor_info['city_name']) : 'Pakistan';

$GLOBALS['page_title'] = "$doc_name_for_seo — $spec_name_for_seo in $city_name_for_seo | SehatNexa";
$GLOBALS['page_description'] = "Book an appointment with $doc_name_for_seo, a highly experienced $spec_name_for_seo in $city_name_for_seo. Check clinic timings, patient reviews, and contact details.";
$GLOBALS['page_image'] = ($doctor_info && !empty($doctor_info['doctor_pic'])) ? BASE_URL.'admin/inc/uploads/doctors/'.$doctor_info['doctor_pic'] : BASE_URL.'admin/inc/uploads/default/doctor.jpg';

if ($doctor_info) {
    // Schema Markup for Google
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "Physician",
        "name" => "Dr. " . $doctor_info['doctor_name'],
        "image" => $GLOBALS['page_image'],
        "telephone" => $doctor_info['doctor_phone'],
        "medicalSpecialty" => $spec ? $spec['type'] : "General Practice",
        "address" => [
            "@type" => "PostalAddress",
            "addressLocality" => $doctor_info['city_name'],
            "addressCountry" => "PK"
        ]
    ];
    $GLOBALS['page_schema'] = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

// ============================================================
// 3. Header Include karein
// ============================================================
include '../includes/header.php'; 

$doctor = $doctor_info;
if (!$doctor) {
    header('Location: ' . BASE_URL . 'doctors');
    exit();
}

// Submit review
if (isset($_POST['user_id']) && (int)$_POST['user_id'] != 0) {
    $user_id = (int)$_POST['user_id'];
    $commenter_name = isset($_POST['commenter_name']) ? trim($_POST['commenter_name']) : '';
    $commenter_gmail = isset($_POST['commenter_gmail']) ? trim($_POST['commenter_gmail']) : '';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $stars = isset($_POST['stars']) ? (int)$_POST['stars'] : 5;

    $insert_query = "INSERT INTO feedback (user_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at) 
                    VALUES ($user_id,
                    '" . mysqli_real_escape_string($con, $commenter_name) . "', 
                    '" . mysqli_real_escape_string($con, $commenter_gmail) . "', 
                    '" . mysqli_real_escape_string($con, $comment) . "', 
                    $stars, 1, NOW(), NOW())";
    $feedback_run = mysqli_query($con, $insert_query);
}

// Rating query
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(*) as total_reviews FROM feedback WHERE user_id = " . (int)$doctor['user_id'] . " AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-detail.css?v=<?= @filemtime(__DIR__ . '/style/doctor-detail.css') ?: time() ?>">

<!-- Navbar -->
<?php include BASE_PATH . '/includes/menu.php'; ?>

<!-- ===== HERO SECTION ===== -->
<section class="profile-hero">
    <div class="container">
        <div class="text-center text-white" data-aos="fade-up">
            <!-- SEO: H1 properly added -->
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 8px; text-shadow: 0 2px 15px rgba(0,0,0,0.2);">
                Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?>
            </h1>
            <p style="font-size: 1.05rem; opacity: 0.9;">
                <?php echo $spec ? htmlspecialchars($spec['type']) : 'General Practitioner'; ?>
            </p>
        </div>
    </div>
</section>

<!-- ===== MAIN CONTENT ===== -->
<section style="padding-bottom: 60px;">
    <div class="container">
        
        <!-- ===== PROFILE CARD ===== -->
        <div class="profile-card" data-aos="fade-up" data-aos-delay="100">
            <div class="profile-card-inner">
                
                <!-- LEFT SIDEBAR -->
                <div class="profile-sidebar">
                    
                    <!-- Avatar -->
                    <div class="avatar-wrapper">
                        <?php if (!empty($doctor['doctor_pic'])): ?>
                            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                 alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" 
                                 class="profile-avatar">
                        <?php else: ?>
                            <div class="profile-avatar-placeholder">
                                <i class="fas fa-user-md"></i>
                            </div>
                        <?php endif; ?>
                        <div class="status-badge"></div>
                    </div>

                    <!-- Name & Specialty -->
                    <h2 class="profile-name">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></h2>
                    
                    <div class="profile-specialty">
                        <?php echo $spec ? htmlspecialchars($spec['type']) : 'General Practitioner'; ?>
                    </div>
                    
                    <div class="profile-qualification"><?php echo htmlspecialchars($doctor['short_detail']); ?></div>
                    
                    <!-- Mahre Amraz from cat tbl-->
                    <?php if (!empty($spec['treatment'])): ?>
                        <div class="mahre-amraz-badge">
                            <i class="fas fa-star"></i>
                            ماہرِ امراض: <?php echo htmlspecialchars($spec['treatment']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Notes -->
                    <?php if (!empty($doctor['notes'])): ?>
                        <div class="doctor-notes-box">
                            <i class="fas fa-sticky-note"></i>
                            <?php echo htmlspecialchars($doctor['notes']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Experience -->
                    <?php if (!empty($doctor['experience_years']) && $doctor['experience_years'] != 0): ?>
                        <div class="experience-badge">
                            <i class="fas fa-briefcase"></i>
                            <?php echo $doctor['experience_years']; ?> Years Experience
                        </div>
                    <?php endif; ?>

                    <!-- Availability -->
                    <?php if (!empty($doctor['emergency_status']) && $doctor['emergency_status'] != 0): ?>
                        <div class="experience-badge" style="background-color: red;font-weight: bold;">
                            <i class="fas fa-briefcase"></i>Not Available: 
                            <?php if(!empty($doctor['emergency_detail'])){echo $doctor['emergency_detail']." , Please contact 03371320001";}else{ ?>
                                Unavailable, Please contact 03371320001
                            <?php } ?>
                        </div>
                    <?php endif; ?>

                    <!-- Rating -->
                    <?php if ($avg_rating > 0): ?>
                        <div class="rating-display">
                            <i class="fas fa-star"></i>
                            <span class="rating-value"><?php echo $avg_rating; ?></span>
                            <span style="opacity:0.7;">/5.0</span>
                            <span style="opacity:0.5;">(<?php echo $total_reviews; ?> reviews)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- RIGHT CONTENT (NOW CLINICAL INFO) -->
                <div class="profile-content">
                    <?php
                    $clinical_query = "SELECT ci.*, hospitals.hospital_name, hospitals.hospital_id,hospitals.hospital_address,cities.city_name
                                    FROM clinical_info ci 
                                    INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id 
                                    LEFT JOIN hospitals ON dih.hospital_id = hospitals.hospital_id
                                    LEFT JOIN cities ON cities.city_id = hospitals.city_id
                                    WHERE dih.doctor_id = " . (int)$doctor['doctor_id'] . " AND dih.inactive=0
                                    ORDER BY ci.season, ci.shift";
                    $clinical_result = mysqli_query($con, $clinical_query);
                    ?>

                    <?php if (mysqli_num_rows($clinical_result) > 0): ?>
                        <div class="content-section">
                            <div class="section-title">
                                <i class="fas fa-clock"></i> Clinical Information
                            </div>

                            <div class="clinical-grid">
                                <?php while ($clinical = mysqli_fetch_assoc($clinical_result)): ?>
                                    <div class="clinical-card">
                                        <div class="hospital-name">
                                            <i class="fas fa-hospital"></i>
                                           <?php
                                                if (!empty($clinical['hospital_name'])) {
                                                    ?>
                                                        <a href="<?=BASE_URL?>hospital-detail?hospital_id=<?=$clinical['hospital_id']?>"><?=htmlspecialchars($clinical['hospital_name'])?></a>
                                                    <?php
                                                    echo "<br><i class='fas fa-map-marker-alt text-info me-1'></i><small class='text-muted'>( " . $clinical['city_name'] . " )</small>";
                                                } else {
                                                    echo 'Personal Clinic';
                                                    if (!empty($doctor['clinic_address'])) {
                                                        echo "<br><i class='fas fa-map-marker-alt text-info me-1'></i><small class='text-muted'>( " . nl2br(htmlspecialchars($doctor['clinic_address'])) . " )</small>";
                                                    }
                                                }
                                            ?>
                                        </div>

                                        <?php if (!empty($clinical['morning_opening_time']) || !empty($clinical['morning_closing_time'])): ?>
                                            <div class="clinical-item">
                                                <i class="fas fa-sun text-warning"></i>
                                                <span class="label">Morning</span>
                                                <span class="value">
                                                    <?php echo date('h:i A', strtotime($clinical['morning_opening_time'])); ?>
                                                    - 
                                                    <?php echo date('h:i A', strtotime($clinical['morning_closing_time'])); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($clinical['evening_opening_time']) || !empty($clinical['evening_closing_time'])): ?>
                                            <div class="clinical-item">
                                                <i class="fas fa-moon text-primary"></i>
                                                <span class="label">Evening</span>
                                                <span class="value">
                                                    <?php echo date('h:i A', strtotime($clinical['evening_opening_time'])); ?>
                                                    - 
                                                    <?php echo date('h:i A', strtotime($clinical['evening_closing_time'])); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="clinical-item">
                                            <i class="fas fa-calendar-day text-success"></i>
                                            <span class="label">Working Days</span>
                                            <span class="value"><?php echo htmlspecialchars($clinical['days'] ?? 'N/A'); ?></span>
                                        </div>

                                        <div class="clinical-item">
                                            <i class="fas fa-calendar-times text-danger"></i>
                                            <span class="label">Off Days</span>
                                            <span class="value"><?php echo htmlspecialchars($clinical['off_days'] ?? 'None'); ?></span>
                                        </div>

                                        <?php if (!empty($clinical['contact'])): ?>
                                            <div class="clinical-item">
                                                <i class="fas fa-phone"></i>
                                                <span class="label">Contact</span>
                                                <span class="value"><?php echo htmlspecialchars($clinical['contact']); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($clinical['detail'])): ?>
                                            <div class="clinical-item" style="border-top: 1px dashed var(--border); padding-top: 10px; margin-top: 6px;">
                                                <i class="fas fa-info-circle text-info"></i>
                                                <span class="label">Detail</span>
                                                <span class="value" style="font-size:0.82rem;"><?php echo htmlspecialchars($clinical['detail']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($clinical['hospital_address'])): ?>
                                            <div class="clinical-item" style="border-top: 1px dashed var(--border); padding-top: 10px; margin-top: 6px;">
                                                <i class="fas fa-map-marker-alt text-info me-1"></i>
                                                <span class="label">Hospital Address</span>
                                                <span class="value" style="font-size:0.82rem;"><?php echo htmlspecialchars($clinical['hospital_address']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="content-section">
                            <div class="section-title">
                                <i class="fas fa-clock"></i> Clinical Information
                            </div>
                            <div class="info-card" style="background-color:red;color: white;font-weight: bold;">
                                <i class="fas fa-info-circle text-muted"></i> 
                                اس ڈاکٹر کے متعلق معلومات بہت جلد شامل کی جائیں گی۔ مدد کے لیے رابطہ کریں: 0337-1320001
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ad Space 1 -->
        <div class="my-4" data-aos="fade-up">
            <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                <!-- SpeedyAds Code Yahan Paste Karein -->
                <span class="text-muted small">Advertisement Space</span>
            </div>
        </div>

        <!-- ===== DOCTOR MESSAGE SECTION ===== -->
        <?php
        $note_q = mysqli_query($con, "SELECT notes, updated_at FROM notes WHERE user_id = " . (int)$doctor['user_id'] . " LIMIT 1");
        $note_row = $note_q ? mysqli_fetch_assoc($note_q) : null;
        if ($note_row && !empty(trim($note_row['notes']))): ?>
        <div class="doctor-message-section" data-aos="fade-up" data-aos-delay="180">
            <div class="doctor-message-card">
                <div class="doctor-message-header">
                    <div class="dm-icon-wrap">
                        <i class="fas fa-comment-medical"></i>
                    </div>
                    <div>
                        <h2 class="dm-title">Doctor's Message</h2>
                        <span class="dm-date"><i class="fas fa-clock me-1"></i><?= date('d M Y', strtotime($note_row['updated_at'])) ?></span>
                    </div>
                </div>
                <div class="doctor-message-body">
                    <?= nl2br(htmlspecialchars($note_row['notes'])) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ===== REVIEWS SECTION ===== -->
        <div class="reviews-section" data-aos="fade-up" data-aos-delay="200">
            <div class="section-header">
                <h2><i class="fas fa-star"></i> Patient Reviews</h2>
                <?php if ($total_reviews > 0): ?>
                    <div class="rating-summary">
                        <i class="fas fa-star"></i>
                        <span class="score"><?php echo $avg_rating; ?></span>
                        <span class="count">(<?php echo $total_reviews; ?> reviews)</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Reviews List -->
            <?php
            $feedback_query = "SELECT * FROM feedback WHERE user_id = " . (int)$doctor['user_id'] . " AND status = 1 
                              ORDER BY created_at DESC";
            $feedback_result = mysqli_query($con, $feedback_query);
            ?>

            <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                <div id="reviewsContainer">
                    <?php 
                    $review_index = 0;
                    while ($feedback = mysqli_fetch_assoc($feedback_result)): 
                        $review_index++;
                    ?>
                        <div class="review-item review-card-item" 
                             data-index="<?php echo $review_index; ?>"
                             style="<?php echo $review_index > 5 ? 'display:none;' : ''; ?>">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <?php echo strtoupper(substr($feedback['commenter_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <span class="reviewer-name"><?php echo htmlspecialchars($feedback['commenter_name']); ?></span>
                                        <span class="reviewer-email"><?php echo htmlspecialchars($feedback['commenter_gmail']); ?></span>
                                    </div>
                                </div>
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? '' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php if (!empty($feedback['comment'])): ?>
                                <p class="review-comment"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                            <?php endif; ?>
                            <div class="review-date">
                                <i class="fas fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($feedback['created_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if (mysqli_num_rows($feedback_result) > 5): ?>
                    <div class="load-more-wrapper" style="text-align:center; margin: 20px 0;">
                        <button type="button" id="loadMoreBtn" class="btn-submit-review" style="border:none; cursor:pointer;">
                            <i class="fas fa-chevron-down"></i> Load More Reviews
                        </button>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-reviews">
                    <i class="fas fa-comment-slash"></i>
                    <h4>No Reviews Yet</h4>
                    <p>Be the first to share your experience with this doctor!</p>
                </div>
            <?php endif; ?>

            <!-- Review Form -->
            <div class="review-form">
                <h3><i class="fas fa-pen"></i> Share Your Experience</h3>
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?php echo (int)$doctor['user_id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Your Name *</label>
                            <input type="text" class="form-control" name="commenter_name" required placeholder="Enter your name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="commenter_gmail" required placeholder="your@email.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rating *</label>
                        <div class="rating-select" id="ratingSelect">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <span class="star" data-value="<?php echo $i; ?>" onclick="setRating(<?php echo $i; ?>)">
                                    <i class="fas fa-star"></i>
                                </span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="stars" id="ratingValue" value="5">
                        <span class="text-muted" id="ratingLabel" style="font-size:0.85rem; font-weight:600;">Excellent</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Your Review *</label>
                        <textarea class="form-control" name="comment" rows="4" required placeholder="Share your experience with this doctor..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit-review">
                        <i class="fas fa-paper-plane"></i> Submit Review
                    </button>
                </form>
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

<!-- ===== FOOTER ===== -->
<?php include BASE_PATH . '/includes/footer.php'; ?>

<script>
// ============================================
// STAR RATING SYSTEM
// ============================================
function setRating(value) {
    const stars = document.querySelectorAll('.rating-select .star');
    const ratingValue = document.getElementById('ratingValue');
    const ratingLabel = document.getElementById('ratingLabel');
    
    ratingValue.value = value;
    
    stars.forEach(star => {
        star.classList.remove('active');
        if (parseInt(star.dataset.value) <= value) {
            star.classList.add('active');
        }
    });
    
    const labels = { 1: 'Poor', 2: 'Fair', 3: 'Good', 4: 'Very Good', 5: 'Excellent' };
    ratingLabel.textContent = labels[value];
}

// ============================================
// LOAD MORE REVIEWS (5 at a time)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    setRating(5);
    
    const stars = document.querySelectorAll('.rating-select .star');
    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const value = parseInt(this.dataset.value);
            stars.forEach(s => {
                s.classList.remove('active');
                if (parseInt(s.dataset.value) <= value) { s.classList.add('active'); }
            });
        });
    });
    
    const ratingSelect = document.getElementById('ratingSelect');
    if (ratingSelect) {
        ratingSelect.addEventListener('mouseleave', function() {
            const current = parseInt(document.getElementById('ratingValue').value);
            stars.forEach(star => {
                star.classList.remove('active');
                if (parseInt(star.dataset.value) <= current) { star.classList.add('active'); }
            });
        });
    }

    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const hiddenReviews = document.querySelectorAll('.review-card-item[style*="display:none"]');
            let shown = 0;
            hiddenReviews.forEach(function(item) {
                if (shown < 5) {
                    item.style.display = '';
                    shown++;
                }
            });
            const remaining = document.querySelectorAll('.review-card-item[style*="display:none"]');
            if (remaining.length === 0) { loadMoreBtn.style.display = 'none'; }
        });
    }
});
</script>