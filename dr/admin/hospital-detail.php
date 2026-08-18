<?php include '../config.php'; ?>

<?php
// Handle delete operation
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $pic_query = "SELECT hospital_pic FROM hospitals WHERE hospital_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $hospital_pic_data = mysqli_fetch_assoc($pic_result);
    $hospital_pic = $hospital_pic_data ? $hospital_pic_data['hospital_pic'] : '';
    
    $delete_query = "UPDATE entities set status=0 WHERE entity_id = $delete_id";
    
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

$hospital_id = $_GET['id'];

// Fetch hospital details with related information
$query = "SELECT h.*, c.city_name, e.status as estatus, u.username, u.email as user_email
          FROM hospitals h 
          LEFT JOIN cities c ON h.city_id = c.city_id
          LEFT JOIN entities e ON e.entity_id = h.entity_id
          LEFT JOIN users u ON u.user_id = h.user_id
          WHERE h.hospital_id = $hospital_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}

$hospital = mysqli_fetch_assoc($result);
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
    background: linear-gradient(135deg, #0d6efd 0%, #4facfe 100%);
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

.hospital-avatar {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.1);
}

.hospital-avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 16px;
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

.stat-card .stat-icon {
    font-size: 1.5rem;
    margin-bottom: 6px;
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

.stat-card .stat-icon.blue { color: var(--primary); }
.stat-card .stat-icon.green { color: var(--success); }
.stat-card .stat-icon.orange { color: var(--warning); }
.stat-card .stat-icon.red { color: var(--danger); }
.stat-card .stat-icon.purple { color: #8b5cf6; }

/* ===== MAIN GRID ===== */
.detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
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
    padding: 12px 0;
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
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
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
    font-size: 0.75rem;
    color: var(--muted);
    font-weight: 500;
}

/* ===== FACILITIES GRID ===== */
.facilities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
    width: 10px;
    height: 10px;
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

.facility-item .facility-desc {
    font-size: 0.75rem;
    color: var(--muted);
    margin-left: auto;
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

.doctor-mini-card .doctor-info .doctor-phone {
    font-size: 0.75rem;
    color: var(--muted);
}

/* ===== FEEDBACK CARD ===== */
.feedback-item {
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}

.feedback-item:last-child {
    border-bottom: none;
}

.feedback-item .feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
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
}

.feedback-item .feedback-date {
    font-size: 0.75rem;
    color: var(--muted);
    margin-top: 4px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .content-wrapper {
        padding: 16px;
    }
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    .page-header-actions {
        width: 100%;
        flex-wrap: wrap;
    }
}

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .facilities-grid {
        grid-template-columns: 1fr;
    }
    .page-header-title h1 {
        font-size: 1.4rem;
    }
    .beds-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    .page-header-left {
        flex-direction: column;
        text-align: center;
        width: 100%;
    }
    .page-header-actions {
        justify-content: center;
    }
    .beds-grid {
        grid-template-columns: 1fr;
    }
}
</style>

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
                <a href="javascript:void(0)" onclick="deleteHospital(<?php echo $hospital['entity_id']; ?>)" class="btn-action-header" style="background: rgba(239,68,68,0.3);">
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
function deleteHospital(entity_id) {
    if (confirm('Are you sure you want to delete this hospital? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + entity_id;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>