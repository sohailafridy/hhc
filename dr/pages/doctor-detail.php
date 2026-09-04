<?php include '../includes/header.php'; ?>

<?php
// Get doctor ID from URL
$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;

if ($doctor_id > 0) {
    // Fetch doctor details - INCLUDING NEW FIELDS
    $query = "SELECT d.*, c.city_name, h.hospital_name 
             FROM doctors d 
             LEFT JOIN cities c ON d.city_id = c.city_id 
             LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
             LEFT JOIN entities e ON e.entity_id = d.entity_id
             WHERE d.doctor_id = $doctor_id AND e.status = 1 AND d.approve=1";
    $result = mysqli_query($con, $query);
    $doctor = mysqli_fetch_assoc($result);
}

// Submit review
if (isset($_REQUEST['entity_id']) && $_REQUEST['entity_id'] != 0) {
    $entity_id = $_REQUEST['entity_id'];
    $commenter_name = isset($_POST['commenter_name']) ? trim($_POST['commenter_name']) : '';
    $commenter_gmail = isset($_POST['commenter_gmail']) ? trim($_POST['commenter_gmail']) : '';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $stars = isset($_POST['stars']) ? (int)$_POST['stars'] : 5;

    $insert_query = "INSERT INTO feedback (entity_id, commenter_name, commenter_gmail, comment, stars, status, created_at, updated_at) 
                    VALUES ($entity_id,
                    '" . mysqli_real_escape_string($con, $commenter_name) . "', 
                    '" . mysqli_real_escape_string($con, $commenter_gmail) . "', 
                    '" . mysqli_real_escape_string($con, $comment) . "', 
                    $stars, 1, NOW(), NOW())";
    $feedback_run = mysqli_query($con, $insert_query);
}
?>

<style>
:root {
    --primary: #4f46e5;
    --primary-light: #818cf8;
    --primary-dark: #3730a3;
    --secondary: #0ea5e9;
    --success: #22c55e;
    --warning: #f59e0b;
    --danger: #ef4444;
    --text: #0f172a;
    --muted: #64748b;
    --bg: #f1f5f9;
    --card: #ffffff;
    --border: #e2e8f0;
    --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
}

/* ===== PAGE HEADER ===== */
.page-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    padding: 80px 0 50px;
    color: white;
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: rgba(255,255,255,0.06);
    border-radius: 50%;
}

.page-header::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}

.page-header h1 {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 8px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.15);
}

.page-header p {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* ===== SECTION ===== */
.section-padding {
    padding: 60px 0;
}

/* ===== DOCTOR PROFILE CARD ===== */
.doctor-profile-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
    margin-bottom: 40px;
    border: 1px solid rgba(255,255,255,0.1);
}

.doctor-profile-card .profile-left {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    padding: 40px 30px;
    color: white;
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.profile-avatar {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255,255,255,0.3);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    margin-bottom: 20px;
}

.profile-avatar-placeholder {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: rgba(255,255,255,0.5);
    border: 4px solid rgba(255,255,255,0.2);
    margin-bottom: 20px;
}

.profile-left h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 4px;
    color: #ffffff;
    text-shadow: 0 2px 10px rgba(0,0,0,0.15);
}

.profile-left .specialty {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 4px;
    color: rgba(255,255,255,0.9);
}

.profile-left .qualification {
    font-size: 0.9rem;
    opacity: 0.8;
    color: rgba(255,255,255,0.8);
}

/* ===== NEW: MAHRE AMRAZ ===== */
.profile-left .mahre-amraz {
    font-size: 0.95rem;
    margin-top: 8px;
    padding: 6px 18px;
    background: rgba(245,158,11,0.2);
    border-radius: 50px;
    border: 1px solid rgba(245,158,11,0.2);
    color: #fcd34d;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.profile-left .mahre-amraz i {
    color: #fcd34d;
    font-size: 0.8rem;
}

/* ===== NEW: NOTES ===== */
.profile-left .doctor-notes {
    font-size: 0.85rem;
    margin-top: 10px;
    padding: 8px 16px;
    background: rgba(34,197,94,0.15);
    border-radius: 8px;
    border-left: 3px solid #22c55e;
    color: #86efac;
    text-align: left;
    width: 100%;
    max-width: 280px;
}

.profile-left .doctor-notes i {
    color: #86efac;
    margin-right: 6px;
}

.profile-left .experience {
    font-size: 0.9rem;
    opacity: 0.85;
    margin-top: 8px;
    color: rgba(255,255,255,0.85);
}

.profile-left .rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 18px;
    background: rgba(255,255,255,0.15);
    border-radius: 50px;
    font-size: 0.9rem;
    margin-top: 12px;
    border: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.9);
}

.profile-left .rating-badge i {
    color: #fbbf24;
}

/* Profile Right */
.profile-right {
    padding: 30px 35px;
}

.profile-right .section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-right .section-title i {
    color: var(--primary);
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 24px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.contact-item:hover {
    border-color: var(--primary);
    background: #f0f7ff;
}

.contact-item i {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary);
    color: white;
    border-radius: 10px;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.contact-item .label {
    font-size: 0.7rem;
    color: var(--muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-item .value {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text);
}

.about-text {
    color: var(--muted);
    line-height: 1.8;
    font-size: 0.95rem;
}

/* ===== CLINICAL INFO ===== */
.clinical-section {
    background: white;
    border-radius: 20px;
    padding: 30px 35px;
    box-shadow: var(--shadow);
    margin-bottom: 30px;
    border: 1px solid var(--border);
}

.clinical-section .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--border);
}

.clinical-section .section-header h3 {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
}

.clinical-section .section-header h3 i {
    color: var(--primary);
}

.clinical-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.clinical-card {
    background: #f8fafc;
    border-radius: 14px;
    padding: 20px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.clinical-card:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow);
}

.clinical-card .hospital-name {
    font-weight: 700;
    font-size: 1rem;
    color: var(--primary);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.clinical-card .hospital-name i {
    font-size: 0.9rem;
}

.clinical-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 0;
    font-size: 0.85rem;
}

.clinical-item i {
    width: 18px;
    color: var(--primary);
    font-size: 0.8rem;
}

.clinical-item .label {
    color: var(--muted);
    font-weight: 500;
    min-width: 90px;
}

.clinical-item .value {
    color: var(--text);
    font-weight: 500;
}

/* ===== REVIEWS SECTION ===== */
.reviews-section {
    background: white;
    border-radius: 20px;
    padding: 30px 35px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

.reviews-section .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--border);
}

.reviews-section .section-header h3 {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
}

.reviews-section .section-header .rating-summary {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    background: #fef3c7;
    border-radius: 50px;
}

.reviews-section .section-header .rating-summary i {
    color: #f59e0b;
}

.reviews-section .section-header .rating-summary .score {
    font-weight: 700;
    font-size: 1.1rem;
}

/* Review Form */
.review-form {
    background: #f8fafc;
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 30px;
    border: 1px solid var(--border);
}

.review-form h4 {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 16px;
}

.review-form .form-control {
    border-radius: 10px;
    border: 2px solid var(--border);
    padding: 10px 14px;
    transition: all 0.3s ease;
}

.review-form .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
}

.review-form .rating-select {
    display: flex;
    gap: 8px;
    margin: 8px 0 16px;
}

.review-form .rating-select .star {
    font-size: 1.8rem;
    color: #d1d5db;
    cursor: pointer;
    transition: all 0.2s ease;
}

.review-form .rating-select .star:hover,
.review-form .rating-select .star.active {
    color: #f59e0b;
    transform: scale(1.1);
}

.btn-submit-review {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    border: none;
    padding: 10px 30px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-submit-review:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(79,70,229,0.3);
    color: white;
}

/* Review List */
.review-item {
    padding: 16px 0;
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

.review-item .reviewer-name {
    font-weight: 600;
    font-size: 0.95rem;
}

.review-item .reviewer-email {
    font-size: 0.8rem;
    color: var(--muted);
}

.review-item .review-stars {
    color: #f59e0b;
    font-size: 0.9rem;
}

.review-item .review-comment {
    color: var(--text);
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 6px 0 0;
}

.review-item .review-date {
    font-size: 0.75rem;
    color: var(--muted);
    margin-top: 4px;
}

/* No Reviews */
.no-reviews {
    text-align: center;
    padding: 40px 20px;
    color: var(--muted);
}

.no-reviews i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 12px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .clinical-grid {
        grid-template-columns: 1fr;
    }
    .contact-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 2.2rem;
    }
    .profile-left {
        padding: 30px 20px;
    }
    .profile-right {
        padding: 20px;
    }
    .clinical-section,
    .reviews-section {
        padding: 20px;
    }
    .review-form {
        padding: 16px;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.8rem;
    }
    .section-padding {
        padding: 40px 0;
    }
    .contact-item {
        flex-direction: column;
        text-align: center;
    }
    .profile-left .doctor-notes {
        max-width: 100%;
    }
}
</style>

<!-- Navbar -->
<?php include BASE_PATH . '/includes/menu.php'; ?>

<!-- ===== PAGE HEADER ===== -->
<section class="page-header">
    <div class="container text-center">
        <h1 data-aos="fade-up">Doctor Profile</h1>
        <p data-aos="fade-up" data-aos-delay="100">Complete doctor information and details</p>
    </div>
</section>

<!-- ===== DOCTOR DETAIL ===== -->
<section class="section-padding">
    <div class="container">

        <?php if ($doctor && !empty($doctor)): ?>

            <!-- ===== PROFILE CARD ===== -->
            <div class="doctor-profile-card" data-aos="fade-up">
                <div class="row g-0">
                    <!-- Left Column -->
                    <div class="col-lg-4">
                        <div class="profile-left">
                            <?php if (!empty($doctor['doctor_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                     alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="profile-avatar">
                            <?php else: ?>
                                <div class="profile-avatar-placeholder">
                                    <i class="fas fa-user-md"></i>
                                </div>
                            <?php endif; ?>

                            <h2>Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></h2>
                            
                            <div class="specialty">
                                <?php
                                $spec_query = "SELECT `type` FROM dr_cat_types WHERE dr_cat_type_id = " . $doctor['cat_type_id'];
                                $spec_result = mysqli_query($con, $spec_query);
                                $spec = mysqli_fetch_assoc($spec_result);
                                echo $spec ? htmlspecialchars($spec['type']) : 'General Practitioner';
                                ?>
                            </div>
                            
                            <div class="qualification"><?php echo htmlspecialchars($doctor['short_detail']); ?></div>
                            
                            <!-- ===== NEW: MAHRE AMRAZ ===== -->
                            <?php if (!empty($doctor['mahre_amraz'])): ?>
                                <div class="mahre-amraz">
                                    <i class="fas fa-star"></i>
                                    ماہرِ امراض: <?php echo htmlspecialchars($doctor['mahre_amraz']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- ===== NEW: NOTES ===== -->
                            <?php if (!empty($doctor['notes'])): ?>
                                <div class="doctor-notes">
                                    <i class="fas fa-sticky-note"></i>
                                    <?php echo htmlspecialchars($doctor['notes']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($doctor['experience_years']) && $doctor['experience_years'] != 0): ?>
                                <div class="experience">
                                    <i class="fas fa-briefcase me-1"></i> <?php echo $doctor['experience_years']; ?> Years Experience
                                </div>
                            <?php endif; ?>

                            <?php 
                            // Calculate average rating
                            $rating_query = "SELECT AVG(stars) as avg_rating, COUNT(*) as total_reviews 
                                          FROM feedback WHERE entity_id = " . $doctor['entity_id'] . " AND status = 1";
                            $rating_result = mysqli_query($con, $rating_query);
                            $rating_data = mysqli_fetch_assoc($rating_result);
                            $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
                            $total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;
                            
                            if ($avg_rating > 0): ?>
                                <div class="rating-badge">
                                    <i class="fas fa-star"></i>
                                    <?php echo $avg_rating; ?>/5.0
                                    <span style="opacity:0.7;">(<?php echo $total_reviews; ?> reviews)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-8">
                        <div class="profile-right">
                            <!-- Contact Information -->
                            <div class="section-title">
                                <i class="fas fa-address-card"></i> Contact Information
                            </div>
                            <div class="contact-grid">
                                <?php if (!empty($doctor['doctor_phone'])): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-phone"></i>
                                        <div>
                                            <div class="label">Phone</div>
                                            <div class="value"><?php echo htmlspecialchars($doctor['doctor_phone']); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($doctor['doctor_email'])): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-envelope"></i>
                                        <div>
                                            <div class="label">Email</div>
                                            <div class="value"><?php echo htmlspecialchars($doctor['doctor_email']); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div>
                                        <div class="label">City</div>
                                        <div class="value"><?php echo htmlspecialchars($doctor['city_name']); ?></div>
                                    </div>
                                </div>

                                <?php if (!empty($doctor['hospital_name'])): ?>
                                    <div class="contact-item">
                                        <i class="fas fa-hospital"></i>
                                        <div>
                                            <div class="label">Hospital</div>
                                            <div class="value"><?php echo htmlspecialchars($doctor['hospital_name']); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- About -->
                            <?php if (!empty($doctor['short_detail']) || !empty($doctor['other'])): ?>
                                <div class="section-title" style="margin-top: 20px;">
                                    <i class="fas fa-info-circle"></i> About Doctor
                                </div>
                                <p class="about-text">
                                    <?php echo nl2br(htmlspecialchars($doctor['short_detail'])); ?>
                                    <?php if (!empty($doctor['other'])): ?>
                                        <br><br><?php echo nl2br(htmlspecialchars($doctor['other'])); ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <!-- Clinical Notes -->
                            <?php if (!empty($doctor['static_clinical_info'])): ?>
                                <div class="section-title" style="margin-top: 20px;">
                                    <i class="fas fa-notes-medical"></i> Clinical Notes
                                </div>
                                <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid var(--primary);">
                                    <?php echo nl2br(htmlspecialchars($doctor['static_clinical_info'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== CLINICAL INFORMATION ===== -->
            <?php
            $clinical_query = "SELECT ci.*, hospitals.hospital_name, hospitals.hospital_id
                            FROM clinical_info ci 
                            INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id 
                            LEFT JOIN hospitals ON dih.hospital_id = hospitals.hospital_id
                            WHERE dih.doctor_id = " . $doctor['doctor_id'] . "
                            ORDER BY ci.season, ci.shift";
            $clinical_result = mysqli_query($con, $clinical_query);
            ?>

            <?php if (mysqli_num_rows($clinical_result) > 0): ?>
                <div class="clinical-section" data-aos="fade-up" data-aos-delay="100">
                    <div class="section-header">
                        <h3><i class="fas fa-clock"></i> Clinical Information</h3>
                        <span class="badge bg-primary"><?php echo mysqli_num_rows($clinical_result); ?> Records</span>
                    </div>

                    <div class="clinical-grid">
                        <?php while ($clinical = mysqli_fetch_assoc($clinical_result)): ?>
                            <div class="clinical-card">
                                <div class="hospital-name">
                                    <i class="fas fa-hospital"></i>
                                    <?php
                                    if (!empty($clinical['hospital_name'])) {
                                        echo htmlspecialchars($clinical['hospital_name']);
                                    } else {
                                        echo 'Personal Clinic';
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
                                    <div class="clinical-item" style="border-top: 1px dashed var(--border); padding-top: 8px; margin-top: 4px;">
                                        <i class="fas fa-info-circle text-info"></i>
                                        <span class="label">Detail</span>
                                        <span class="value" style="font-size:0.8rem;"><?php echo htmlspecialchars($clinical['detail']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== REVIEWS SECTION ===== -->
            <div class="reviews-section" data-aos="fade-up" data-aos-delay="150">
                <div class="section-header">
                    <h3><i class="fas fa-star"></i> Patient Reviews</h3>
                    <?php if ($total_reviews > 0): ?>
                        <div class="rating-summary">
                            <i class="fas fa-star"></i>
                            <span class="score"><?php echo $avg_rating; ?></span>
                            <span style="opacity:0.7;">(<?php echo $total_reviews; ?> reviews)</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Review Form -->
                <div class="review-form">
                    <h4><i class="fas fa-pen me-2"></i> Share Your Experience</h4>
                    <form method="POST">
                        <input type="hidden" name="entity_id" value="<?php echo $doctor['entity_id']; ?>">
                        
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
                            <div class="rating-select">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <span class="star" data-value="<?php echo $i; ?>" onclick="setRating(this)">
                                        <i class="fas fa-star"></i>
                                    </span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="stars" id="ratingValue" value="5">
                            <span class="text-muted" id="ratingLabel">Excellent</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Your Review *</label>
                            <textarea class="form-control" name="comment" rows="4" required placeholder="Share your experience with this doctor..."></textarea>
                        </div>

                        <button type="submit" class="btn-submit-review">
                            <i class="fas fa-paper-plane me-2"></i> Submit Review
                        </button>
                    </form>
                </div>

                <!-- Reviews List -->
                <?php
                $feedback_query = "SELECT * FROM feedback WHERE entity_id = " . $doctor['entity_id'] . " AND status = 1 
                                  ORDER BY created_at DESC LIMIT 10";
                $feedback_result = mysqli_query($con, $feedback_query);
                ?>

                <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                    <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div>
                                    <span class="reviewer-name"><?php echo htmlspecialchars($feedback['commenter_name']); ?></span>
                                    <span class="reviewer-email"><?php echo htmlspecialchars($feedback['commenter_gmail']); ?></span>
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
                                <i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($feedback['created_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <i class="fas fa-comment-slash"></i>
                        <h5>No Reviews Yet</h5>
                        <p>Be the first to share your experience with this doctor!</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- ===== NOT FOUND ===== -->
            <div class="text-center py-5" data-aos="fade-up">
                <i class="fas fa-user-md fa-4x text-muted mb-3"></i>
                <h3>Doctor Not Found</h3>
                <p class="text-muted">The doctor you're looking for doesn't exist or has been removed.</p>
                <a href="<?php echo BASE_URL; ?>doctors" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left me-2"></i> Back to Doctors
                </a>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- ===== FOOTER ===== -->
<?php include BASE_PATH . '/includes/footer.php'; ?>

<script>
// ============================================
// STAR RATING
// ============================================
function setRating(el) {
    const stars = document.querySelectorAll('.star');
    const value = el.dataset.value;
    
    stars.forEach(star => {
        star.classList.remove('active');
        if (star.dataset.value <= value) {
            star.classList.add('active');
        }
    });
    
    document.getElementById('ratingValue').value = value;
    
    const labels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    document.getElementById('ratingLabel').textContent = labels[value];
}

// Initialize stars
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star');
    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const value = this.dataset.value;
            stars.forEach(s => {
                s.classList.remove('active');
                if (s.dataset.value <= value) {
                    s.classList.add('active');
                }
            });
        });
    });
    
    document.querySelector('.rating-select').addEventListener('mouseleave', function() {
        const current = document.getElementById('ratingValue').value;
        stars.forEach(star => {
            star.classList.remove('active');
            if (star.dataset.value <= current) {
                star.classList.add('active');
            }
        });
    });
    
    // Set default rating (5 stars)
    stars.forEach(star => {
        if (star.dataset.value <= 5) {
            star.classList.add('active');
        }
    });
});
</script>