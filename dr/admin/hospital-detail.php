<?php include '../config.php'; ?>

<?php
// Handle delete operation
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    $pic_query = "SELECT hospital_pic FROM hospitals WHERE user_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $hospital_pic_data = mysqli_fetch_assoc($pic_result);
    $hospital_pic = $hospital_pic_data ? $hospital_pic_data['hospital_pic'] : '';
    
    $delete_query = "UPDATE users set status=0 WHERE user_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        $_SESSION['success_msg'] = "Hospital deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    
    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<?php include BASE_PATH.'/admin/inc/top.php';?>
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Get hospital ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}

$hospital_id = (int)$_GET['id'];

// Fetch hospital details with related information
$query = "SELECT h.*, c.city_name, u.status as estatus, u.username, u.email as user_email
          FROM hospitals h 
          LEFT JOIN cities c ON h.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = h.user_id
          WHERE h.hospital_id = $hospital_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}

$hospital = mysqli_fetch_assoc($result);
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
$doctors_query = "SELECT d.*, dct.type as specialization 
                  FROM doctors d
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  LEFT JOIN users u ON d.user_id = u.user_id
                  WHERE d.hospital_id = $hospital_id AND u.status = 1 AND d.approve = 1
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

<link rel="stylesheet" href="<?= BASE_URL ?>style/hospital-detail-admin.css">

<div class="content-wrapper">

    <!-- ===== PAGE HEADER ===== -->
    <div class="page-header-modern">
        <div class="page-header-content">
            <div class="page-header-left">
                <?php if (!empty($hospital['hospital_pic']) && file_exists(BASE_PATH . '/admin/inc/uploads/hospitals/' . $hospital['hospital_pic'])): ?>
                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital['hospital_pic']; ?>" 
                         alt="<?php echo htmlspecialchars($hospital['hospital_name']); ?>" class="hospital-avatar">
                <?php else: ?>
                    <div class="hospital-avatar-placeholder">
                        <i class="fas fa-hospital"></i>
                    </div>
                <?php endif; ?>
                <div class="page-header-title">
                    <h1><?php echo htmlspecialchars($hospital['hospital_name']); ?></h1>
                    <p><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($hospital['city_name']); ?> 
                       <span class="mx-2">|</span> 
                       <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($hospital['hospital_phone']); ?>
                       <span class="mx-2">|</span>
                       <span class="badge <?php echo $hospital['estatus'] == 1 ? 'bg-success' : 'bg-danger'; ?>">
                           <?php echo $hospital['estatus'] == 1 ? 'Active' : 'Inactive'; ?>
                       </span>
                    </p>
                </div>
            </div>
            <div class="page-header-actions">
                <a href="<?php echo BASE_URL; ?>admin/hospitals/add?id=<?php echo $hospital['hospital_id']; ?>" class="btn-action-header">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="javascript:void(0)" onclick="deleteHospital(<?php echo $hospital['user_id']; ?>)" class="btn-action-header" style="background: rgba(239,68,68,0.3);">
                    <i class="fas fa-trash"></i> Delete
                </a>
                <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn-action-header" style="background: rgba(255,255,255,0.1);">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- ===== STATS ROW ===== -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
            <div class="stat-number"><?php echo $total_doctors; ?></div>
            <div class="stat-label">Total Doctors</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-bed"></i></div>
            <div class="stat-number"><?php echo $beds ? $beds['total_beds'] : 0; ?></div>
            <div class="stat-label">Total Beds</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-concierge-bell"></i></div>
            <div class="stat-number"><?php echo $available_facilities; ?>/<?php echo $total_facilities; ?></div>
            <div class="stat-label">Facilities Available</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-star"></i></div>
            <div class="stat-number"><?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?></div>
            <div class="stat-label">Rating (<?php echo $total_reviews; ?> reviews)</div>
        </div>
    </div>

    <!-- ===== DETAIL GRID ===== -->
    <div class="detail-grid">

        <!-- ===== LEFT COLUMN ===== -->
        <div class="left-column">

            <!-- Hospital Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-info-circle"></i> Hospital Information</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="label">Hospital Name</span>
                        <span class="value"><?php echo htmlspecialchars($hospital['hospital_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">City</span>
                        <span class="value"><?php echo htmlspecialchars($hospital['city_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone</span>
                        <span class="value"><?php echo htmlspecialchars($hospital['hospital_phone']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Address</span>
                        <span class="value" style="text-align: right; max-width: 60%;"><?php echo nl2br(htmlspecialchars($hospital['hospital_address'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Username</span>
                        <span class="value"><?php echo htmlspecialchars($hospital['username'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value"><?php echo htmlspecialchars($hospital['user_email'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Created At</span>
                        <span class="value"><?php echo date('d M Y, h:i A', strtotime($hospital['created_at'])); ?></span>
                    </div>
                    <?php if (!empty($hospital['updated_at'])): ?>
                    <div class="info-row">
                        <span class="label">Updated At</span>
                        <span class="value"><?php echo date('d M Y, h:i A', strtotime($hospital['updated_at'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Beds Information -->
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
                                    <span class="doctor-phone"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($doctor['doctor_phone'] ?? 'N/A'); ?></span>
                                </div>
                                <a href="<?php echo BASE_URL; ?>admin/doctors/profile?id=<?php echo $doctor['doctor_id']; ?>" 
                                   class="btn btn-sm btn-primary">View</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No doctors registered at this hospital</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Facilities -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-concierge-bell"></i> Facilities & Services (<?php echo $total_facilities; ?>)</h5>
                </div>
                <div class="info-card-body">
                    <?php if ($total_facilities > 0): ?>
                        <div class="facilities-grid">
                            <?php foreach ($facilities as $facility): ?>
                                <div class="facility-item">
                                    <span class="facility-status <?php echo $facility['is_available'] == 1 ? 'available' : 'unavailable'; ?>"></span>
                                    <span class="facility-name"><?php echo htmlspecialchars($facility['facility_name']); ?></span>
                                    <?php if (!empty($facility['description'])): ?>
                                        <span class="facility-desc"><?php echo htmlspecialchars($facility['description']); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No facilities available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ===== RIGHT COLUMN ===== -->
        <div class="right-column">

            <!-- Feedback / Reviews -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Patient Reviews (<?php echo $total_reviews; ?>)</h5>
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-star me-1"></i> <?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?>
                    </span>
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

        </div>
    </div>

</div>

<script>
function deleteHospital(user_id) {
    if (confirm('Are you sure you want to delete this hospital? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + user_id;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>