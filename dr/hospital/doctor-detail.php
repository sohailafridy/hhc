<?php
// ============================================
// START SESSION & INCLUDE CONFIG
// ============================================
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

include '../config.php';

// ============================================
// HOSPITAL AUTHENTICATION CHECK
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id = (int)$_SESSION['user_id'];   // logged-in HOSPITAL user

// Get hospital data
$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = (int)$hospital_data['hospital_id'];
$hospital_name = $hospital_data['hospital_name'];

// ============================================
// GET DOCTOR ID
// ============================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . BASE_URL . "hospital/doctors/list");
    exit();
}

$doctor_id = (int)$_GET['id'];

// ============================================
// FETCH DOCTOR DETAILS  (status / reference ab users table se)
// ============================================
$query = "SELECT d.*, 
                 c.city_name,
                 dct.type as specialization,
                 u.status as estatus,
                 u.reference as ref,
                 u.username,
                 u.email as user_email
          FROM doctors d
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
          LEFT JOIN users u ON d.user_id = u.user_id
          INNER JOIN doctor_in_hospital dih ON dih.doctor_id = d.doctor_id
          WHERE dih.doctor_id = $doctor_id AND dih.hospital_id = $hospital_id AND d.approve = 1
          LIMIT 1";

$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_msg'] = "Doctor not found or you don't have permission.";
    header("Location: " . BASE_URL . "hospital/doctors/list");
    exit();
}

$doctor = mysqli_fetch_assoc($result);
$doctor_user_id = (int)$doctor['user_id'];   // doctor ka user_id (hospital ke $user_id se alag)

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
// FETCH RATING & REVIEWS  (feedback.user_id = doctor ka user_id)
// ============================================
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews 
                 FROM feedback WHERE user_id = $doctor_user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;

$reviews_query = "SELECT * FROM feedback 
                  WHERE user_id = $doctor_user_id AND status = 1 
                  ORDER BY created_at DESC LIMIT 10";
$reviews_result = mysqli_query($con, $reviews_query);
$total_reviews_count = mysqli_num_rows($reviews_result);
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-detail-admin.css">

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