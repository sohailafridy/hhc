<?php include '../config.php'; ?>

<?php
$entity_id = 0;
if (isset($_GET['entity_id'])) {
    $entity_id = $_GET['entity_id'];
}

// ============================================
// DELETE CLINICAL INFO
// ============================================
if(isset($_REQUEST['del_clinic_id']) && $_REQUEST['del_clinic_id'] != 0){
    $doctor_in_hospital = "DELETE FROM doctor_in_hospital
      WHERE doctor_in_hosp_id = (
         SELECT doctor_in_hosp_id
         FROM clinical_info
         WHERE clinical_info_id = '" . $_REQUEST['del_clinic_id'] . "'
      )";
    if(mysqli_query($con, $doctor_in_hospital)){
        $clinic_info = "DELETE FROM clinical_info WHERE clinical_info_id = '" . $_REQUEST['del_clinic_id'] . "'";
        if(mysqli_query($con, $clinic_info)){
            $_SESSION['success_msg'] = "Clinical information deleted successfully!";
        }
    }
}

// ============================================
// CHECK CLINICAL INFO STATUS
// ============================================
$check = mysqli_query($con, "SELECT COUNT(doctor_in_hosp_id) as ids 
    FROM doctor_in_hospital 
    WHERE doctor_in_hosp_id NOT IN (SELECT doctor_in_hosp_id FROM clinical_info) 
    AND doctor_in_hospital.doctor_id = '" . $_GET['id'] . "'");
$ids = mysqli_fetch_assoc($check);
$ids = $ids['ids'];

// ============================================
// EMERGENCY STATUS TOGGLE
// ============================================
if (isset($_POST['toggle_emergency']) && is_numeric($_POST['toggle_emergency']) && isset($_POST['status'])) {
    $doctor_id = $_POST['toggle_emergency'];
    $new_status = $_POST['status'] == 1 ? 1 : 0;
    $update_query = "UPDATE doctors SET emergency_status = $new_status WHERE doctor_id = $doctor_id";
    if (mysqli_query($con, $update_query)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
    exit();
}

// ============================================
// DELETE DOCTOR
// ============================================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $pic_query = "SELECT doctor_pic FROM doctors WHERE doctor_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $doctor_pic_data = mysqli_fetch_assoc($pic_result);
    $doctor_pic = $doctor_pic_data ? $doctor_pic_data['doctor_pic'] : '';
    
    $delete_query = "DELETE FROM doctors WHERE doctor_id = $delete_id";
    if (mysqli_query($con, $delete_query)) {
        if (!empty($doctor_pic)) {
            $pic_path = BASE_PATH . "/admin/inc/uploads/doctors/" . $doctor_pic;
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
        }
        $_SESSION['success_msg'] = "Doctor deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/admin/inc/nav.php'; ?>

<?php
// ============================================
// GET DOCTOR ID
// ============================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}

$doctor_id = $_GET['id'];

// ============================================
// FETCH DOCTOR DETAILS - WITH NEW FIELDS
// ============================================
$query = "SELECT d.*, 
                c.city_name,
                h.hospital_name,
                dc.cat_name,
                dct.type as cat_type,
                e.status as estatus,
                e.reference as ref,
                u.username,
                u.password
          FROM doctors d 
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
          LEFT JOIN dr_cat_types dct ON dct.dr_cat_type_id = d.cat_type_id
          LEFT JOIN dr_categories dc ON dc.dr_cat_id = dct.dr_cat_id
          LEFT JOIN entities e ON e.entity_id = d.entity_id
          LEFT JOIN users u ON u.user_id = d.user_id
          WHERE d.doctor_id = $doctor_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}

$doctor = mysqli_fetch_assoc($result);
$entity_id = $doctor['entity_id'];

// ============================================
// FETCH RATING & REVIEWS
// ============================================
$rating_query = "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews 
                 FROM feedback WHERE entity_id = $entity_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? $rating_data['total_reviews'] : 0;

$feedback_query = "SELECT f.* FROM feedback f WHERE f.entity_id = $entity_id AND status = 1 ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);

// ============================================
// FETCH CLINICAL INFO
// ============================================
$clinical_query = "SELECT clinical_info.*, hospitals.hospital_name, hospitals.hospital_id
                   FROM clinical_info 
                   LEFT JOIN doctor_in_hospital dih ON clinical_info.doctor_in_hosp_id = dih.doctor_in_hosp_id
                   LEFT JOIN hospitals ON dih.hospital_id = hospitals.hospital_id
                   WHERE dih.doctor_id = $doctor_id 
                   ORDER BY clinical_info.season, clinical_info.shift";
$clinical_result = mysqli_query($con, $clinical_query);

// ============================================
// FETCH DOCTOR-IN-HOSPITAL (for clinical info count)
// ============================================
$dih_query = "SELECT COUNT(*) as total FROM doctor_in_hospital WHERE doctor_id = $doctor_id";
$dih_result = mysqli_query($con, $dih_query);
$dih_count = mysqli_fetch_assoc($dih_result)['total'];
?>

<style>
:root {
    --primary: #4f46e5;
    --primary-light: #818cf8;
    --primary-dark: #3730a3;
    --success: #22c55e;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #06b6d4;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --bg: #f1f5f9;
    --card: #ffffff;
    --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
}

.content-wrapper {
    background: var(--bg);
    min-height: 100vh;
    padding: 24px 32px 60px;
}

/* ===== PAGE HEADER ===== */
/* ===== PAGE HEADER - FIXED TEXT COLORS ===== */
.page-header-modern {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 20px;
    padding: 28px 35px;
    color: white;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

/* Title */
.page-header-title h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 4px;
    color: #ffffff !important;
    text-shadow: 0 2px 10px rgba(0,0,0,0.15);
}

/* Subtitle / Meta text */
.page-header-title p {
    margin: 0;
    color: rgba(255,255,255,0.85) !important;
    font-size: 0.95rem;
    text-shadow: 0 1px 5px rgba(0,0,0,0.08);
}

/* Badges - White text on semi-transparent background */
.badge-custom {
    padding: 4px 14px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #ffffff !important;
}

.badge-custom.specialization {
    background: rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.9) !important;
    border: 1px solid rgba(255,255,255,0.1);
}

.badge-custom.mahre {
    background: rgba(245,158,11,0.25);
    color: #fcd34d !important;
    border: 1px solid rgba(245,158,11,0.2);
}

.badge-custom.notes {
    background: rgba(34,197,94,0.2);
    color: #86efac !important;
    border: 1px solid rgba(34,197,94,0.15);
}

.badge-custom i {
    color: inherit;
    opacity: 0.8;
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
    color: rgba(255,255,255,0.5);
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
    flex-wrap: wrap;
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
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
}

.btn-action-header:hover {
    background: rgba(255,255,255,0.25);
    color: white;
    transform: translateY(-2px);
}

.btn-action-header.danger {
    background: rgba(239,68,68,0.3);
}

.btn-action-header.danger:hover {
    background: rgba(239,68,68,0.5);
}

/* ===== DOCTOR INFO BADGES ===== */
.doctor-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.badge-custom {
    padding: 4px 14px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.badge-custom.specialization {
    background: rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.9);
    border: 1px solid rgba(255,255,255,0.1);
}

.badge-custom.mahre {
    background: rgba(245,158,11,0.2);
    color: #fbbf24;
    border: 1px solid rgba(245,158,11,0.2);
}

.badge-custom.notes {
    background: rgba(34,197,94,0.15);
    color: #4ade80;
    border: 1px solid rgba(34,197,94,0.15);
}

/* ===== STATS ROW ===== */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 14px;
    padding: 16px 20px;
    box-shadow: var(--shadow);
    text-align: center;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}

.stat-card .stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text);
    line-height: 1.2;
}

.stat-card .stat-label {
    font-size: 0.8rem;
    color: var(--muted);
    font-weight: 500;
    margin-top: 2px;
}

.stat-card .stat-icon {
    font-size: 1.2rem;
    margin-bottom: 4px;
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
    box-shadow: var(--shadow);
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
    font-size: 1.1rem;
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
    font-size: 0.85rem;
}

.info-row .value {
    font-weight: 500;
    color: var(--text);
    text-align: right;
    font-size: 0.9rem;
}

.info-row .value .badge-info {
    padding: 2px 12px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.info-row .value .badge-info.primary { background: #dbeafe; color: #1e40af; }
.info-row .value .badge-info.success { background: #d1fae5; color: #065f46; }
.info-row .value .badge-info.warning { background: #fef3c7; color: #92400e; }

/* ===== MAHRE AMRAZ & NOTES SECTION ===== */
.mahre-section {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    padding: 16px 20px;
    border: 1px solid var(--border);
    margin-top: 8px;
}

.mahre-section .mahre-label {
    font-size: 0.75rem;
    color: var(--muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.mahre-section .mahre-value {
    font-weight: 600;
    color: var(--text);
    font-size: 1rem;
    margin-top: 2px;
}

.mahre-section .notes-label {
    font-size: 0.75rem;
    color: var(--muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 12px;
}

.mahre-section .notes-value {
    color: var(--text);
    font-size: 0.95rem;
    margin-top: 2px;
    padding: 8px 12px;
    background: white;
    border-radius: 8px;
    border-left: 3px solid var(--primary);
}

/* ===== CLINICAL CARDS ===== */
.clinical-grid-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.clinical-card-modern {
    background: white;
    border-radius: 12px;
    border: 1px solid var(--border);
    overflow: hidden;
    transition: all 0.3s ease;
}

.clinical-card-modern:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.clinical-card-header {
    padding: 12px 18px;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
}

.clinical-card-header h6 {
    margin: 0;
    font-weight: 600;
    font-size: 0.9rem;
}

.clinical-card-body {
    padding: 14px 18px;
}

.clinical-info-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 0;
    border-bottom: 1px solid #f8fafc;
}

.clinical-info-row:last-child {
    border-bottom: none;
}

.clinical-info-row i {
    width: 20px;
    color: var(--primary);
    font-size: 0.9rem;
}

.clinical-info-row .c-label {
    font-weight: 500;
    color: var(--muted);
    font-size: 0.75rem;
    min-width: 80px;
}

.clinical-info-row .c-value {
    font-weight: 500;
    color: var(--text);
    font-size: 0.8rem;
}

/* ===== FEEDBACK ITEMS ===== */
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
}

.feedback-item .feedback-name {
    font-weight: 600;
    font-size: 0.9rem;
}

.feedback-item .feedback-rating {
    color: var(--warning);
    font-size: 0.85rem;
}

.feedback-item .feedback-comment {
    color: var(--text);
    font-size: 0.9rem;
    line-height: 1.6;
    margin: 4px 0 0;
}

.feedback-item .feedback-date {
    font-size: 0.75rem;
    color: var(--muted);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .content-wrapper { padding: 16px; }
    .clinical-grid-cards { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    .page-header-actions {
        width: 100%;
    }
    .page-header-left {
        flex-direction: column;
        text-align: center;
        width: 100%;
    }
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    .info-row .value {
        text-align: left;
        width: 100%;
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
                <h1 style="color: #ffffff !important; text-shadow: 0 2px 10px rgba(0,0,0,0.15);">
                    Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                </h1>
                <p style="color: rgba(255,255,255,0.85) !important; text-shadow: 0 1px 5px rgba(0,0,0,0.08);">
                    <i class="fas fa-stethoscope me-1"></i> <?php echo htmlspecialchars($doctor['cat_type'] ?? 'General'); ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($doctor['cat_name'] ?? 'N/A'); ?>
                    <span class="mx-2">|</span>
                    <span class="badge <?php echo $doctor['estatus'] == 1 ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $doctor['estatus'] == 1 ? 'Active' : 'Inactive'; ?>
                    </span>
                    <?php if ($doctor['emergency_status'] == 1): ?>
                        <span class="badge bg-warning text-dark ms-1">
                            <i class="fas fa-exclamation-triangle"></i> Emergency
                        </span>
                    <?php endif; ?>
                </p>
                
                <!-- ===== DOCTOR BADGES - FIXED COLORS ===== -->
                <div class="doctor-badges">
                    <!-- Short Detail Badge -->
                    <?php if (!empty($doctor['short_detail'])): ?>
                        <span class="badge-custom specialization">
                            <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($doctor['short_detail']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Mahere Amraz Badge -->
                    <?php if (!empty($doctor['mahre_amraz'])): ?>
                        <span class="badge-custom mahre">
                            <i class="fas fa-star"></i> ماہرِ امراض: <?php echo htmlspecialchars($doctor['mahre_amraz']); ?>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Notes Badge -->
                    <?php if (!empty($doctor['notes'])): ?>
                        <span class="badge-custom notes">
                            <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($doctor['notes']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="<?php echo BASE_URL; ?>admin/doctors/add?id=<?php echo $doctor['doctor_id']; ?>" class="btn-action-header">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="javascript:void(0)" onclick="deleteDoctor(<?php echo $doctor['doctor_id']; ?>)" class="btn-action-header danger">
                <i class="fas fa-trash"></i> Delete
            </a>
            <a href="<?php echo BASE_URL; ?>admin/doctors/list" class="btn-action-header">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

    <!-- ===== STATS ROW ===== -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-number"><?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?></div>
            <div class="stat-label">Rating</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-number"><?php echo $total_reviews; ?></div>
            <div class="stat-label">Reviews</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏥</div>
            <div class="stat-number"><?php echo $dih_count; ?></div>
            <div class="stat-label">Hospitals</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-number"><?php echo mysqli_num_rows($clinical_result); ?></div>
            <div class="stat-label">Clinical Records</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-number"><?php echo $doctor['experience_years'] ?? 0; ?></div>
            <div class="stat-label">Experience (Yrs)</div>
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
                        <span class="label">Doctor ID</span>
                        <span class="value">#<?php echo $doctor['doctor_id']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Full Name</span>
                        <span class="value">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></span>
                    </div>
                    
                    <!-- ===== SPECIALIZATION ===== -->
                    <div class="info-row">
                        <span class="label">Specialization</span>
                        <span class="value">
                            <span class="badge-info primary"><?php echo htmlspecialchars($doctor['cat_type'] ?? 'General'); ?></span>
                        </span>
                    </div>
                    
                    <!-- ===== SHORT DETAIL ===== -->
                    <?php if (!empty($doctor['short_detail'])): ?>
                        <div class="info-row">
                            <span class="label">Qualifications</span>
                            <span class="value" style="text-align: right;"><?php echo htmlspecialchars($doctor['short_detail']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- ===== MAHRE AMRAZ (NEW) ===== -->
                    <div class="info-row">
                        <span class="label">
                            <i class="fas fa-star" style="color: #f59e0b;"></i> ماہرِ امراض
                        </span>
                        <span class="value">
                            <?php if (!empty($doctor['mahre_amraz'])): ?>
                                <span class="badge-info warning"><?php echo htmlspecialchars($doctor['mahre_amraz']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <!-- ===== NOTES (NEW) ===== -->
                    <?php if (!empty($doctor['notes'])): ?>
                        <div class="info-row" style="display: block; border-bottom: none; padding-bottom: 4px;">
                            <span class="label" style="display: block; margin-bottom: 6px;">
                                <i class="fas fa-sticky-note" style="color: #22c55e;"></i> Notes
                            </span>
                            <div style="background: #f0fdf4; padding: 10px 14px; border-radius: 8px; border-left: 3px solid #22c55e; font-size: 0.9rem; color: var(--text);">
                                <?php echo nl2br(htmlspecialchars($doctor['notes'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- ===== OTHER FIELDS ===== -->
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
                    <?php if (!empty($doctor['other'])): ?>
                        <div class="info-row">
                            <span class="label">Other Info</span>
                            <span class="value" style="text-align: right;"><?php echo htmlspecialchars($doctor['other']); ?></span>
                        </div>
                    <?php endif; ?>
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
                        <span class="label">Password</span>
                        <span class="value">
                            <span class="badge bg-secondary"><?php echo str_repeat('•', 8); ?></span>
                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="showPassword('<?php echo base64_decode($doctor['password']); ?>')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </span>
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
                            <span class="value text-danger" style="text-align: right;"><?php echo htmlspecialchars($doctor['ref']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span class="label">Created At</span>
                        <span class="value"><?php echo date('d M Y, h:i A', strtotime($doctor['created_at'])); ?></span>
                    </div>
                    <?php if (!empty($doctor['updated_at'])): ?>
                        <div class="info-row">
                            <span class="label">Updated At</span>
                            <span class="value"><?php echo date('d M Y, h:i A', strtotime($doctor['updated_at'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Location & Workplace -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-map-marker-alt"></i> Location & Workplace</h5>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="label">City</span>
                        <span class="value"><?php echo htmlspecialchars($doctor['city_name'] ?? 'N/A'); ?></span>
                    </div>
                    <?php if ($doctor['doctor_type'] == 1): ?>
                        <div class="info-row">
                            <span class="label">Type</span>
                            <span class="value"><span class="badge bg-info">Hospital</span></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Hospital</span>
                            <span class="value"><?php echo htmlspecialchars($doctor['hospital_name'] ?? 'N/A'); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="info-row">
                            <span class="label">Type</span>
                            <span class="value"><span class="badge bg-success">Personal Clinic</span></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Clinic Name</span>
                            <span class="value"><?php echo htmlspecialchars($doctor['clinic_name'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Clinic Address</span>
                            <span class="value" style="text-align: right;"><?php echo nl2br(htmlspecialchars($doctor['clinic_address'] ?? 'N/A')); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Static Clinical Info -->
            <?php if (!empty($doctor['static_clinical_info'])): ?>
                <div class="info-card">
                    <div class="info-card-header">
                        <h5><i class="fas fa-notes-medical"></i> Clinical Notes</h5>
                    </div>
                    <div class="info-card-body">
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-primary">
                            <?php echo nl2br(htmlspecialchars($doctor['static_clinical_info'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- ===== RIGHT COLUMN ===== -->
        <div class="right-column">

            <!-- Clinical Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-clock"></i> Clinical Information</h5>
                    <div>
                        <?php if ($ids != 0 && $ids != ''): ?>
                            <a href="clinical-info?id=<?php echo $doctor_id; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-plus"></i> Add Clinical
                            </a>
                        <?php endif; ?>
                        <span class="badge bg-primary ms-1"><?php echo mysqli_num_rows($clinical_result); ?> Records</span>
                    </div>
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
                        
                        <div class="clinical-grid-cards">
                            <?php foreach ($seasons as $season_name => $records): ?>
                                <?php if (!empty($records)): ?>
                                    <?php foreach ($records as $clinical): ?>
                                        <div class="clinical-card-modern">
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
                                                <span class="ms-auto">
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo $season_name; ?>
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="clinical-card-body">
                                                <?php if (!empty($clinical['morning_opening_time']) || !empty($clinical['morning_closing_time'])): ?>
                                                    <div class="clinical-info-row">
                                                        <i class="fas fa-sun text-warning"></i>
                                                        <span class="c-label">Morning</span>
                                                        <span class="c-value">
                                                            <?php 
                                                            echo date('h:i A', strtotime($clinical['morning_opening_time']));
                                                            echo ' - ';
                                                            echo date('h:i A', strtotime($clinical['morning_closing_time']));
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($clinical['evening_opening_time']) || !empty($clinical['evening_closing_time'])): ?>
                                                    <div class="clinical-info-row">
                                                        <i class="fas fa-moon text-primary"></i>
                                                        <span class="c-label">Evening</span>
                                                        <span class="c-value">
                                                            <?php 
                                                            echo date('h:i A', strtotime($clinical['evening_opening_time']));
                                                            echo ' - ';
                                                            echo date('h:i A', strtotime($clinical['evening_closing_time']));
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="clinical-info-row">
                                                    <i class="fas fa-calendar-day text-success"></i>
                                                    <span class="c-label">Working Days</span>
                                                    <span class="c-value"><?php echo htmlspecialchars($clinical['days'] ?? 'N/A'); ?></span>
                                                </div>
                                                
                                                <div class="clinical-info-row">
                                                    <i class="fas fa-calendar-times text-danger"></i>
                                                    <span class="c-label">Off Days</span>
                                                    <span class="c-value"><?php echo htmlspecialchars($clinical['off_days'] ?? 'None'); ?></span>
                                                </div>
                                                
                                                <?php if (!empty($clinical['contact'])): ?>
                                                    <div class="clinical-info-row">
                                                        <i class="fas fa-phone"></i>
                                                        <span class="c-label">Contact</span>
                                                        <span class="c-value"><?php echo htmlspecialchars($clinical['contact']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($clinical['detail'])): ?>
                                                    <div class="clinical-info-row">
                                                        <i class="fas fa-info-circle text-info"></i>
                                                        <span class="c-label">Detail</span>
                                                        <span class="c-value"><?php echo htmlspecialchars($clinical['detail']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="clinical-info-row mt-2 pt-2 border-top">
                                                    <a href="edit-clinical-info?id=<?php echo $clinical['clinical_info_id']; ?>&doctor_id=<?php echo $doctor_id; ?>" 
                                                       class="btn btn-sm btn-warning me-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" onclick="deleteClinical(<?php echo $clinical['clinical_info_id']; ?>)" 
                                                       class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No clinical information found.</p>
                            <?php if ($ids != 0 && $ids != ''): ?>
                                <a href="clinical-info?id=<?php echo $doctor_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Clinical Info
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Emergency Status -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-ambulance"></i> Emergency Status</h5>
                </div>
                <div class="info-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <?php if ($doctor['emergency_status'] == 1): ?>
                                <span class="badge bg-warning text-dark p-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Emergency Not Available
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success p-2">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Available
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if ($doctor['estatus'] == 1): ?>
                                <?php if ($doctor['emergency_status'] == 1): ?>
                                    <button onclick="toggleEmergencyStatus(<?php echo $doctor['doctor_id']; ?>, 0)" 
                                            class="btn btn-sm btn-success">
                                        <i class="fas fa-check me-1"></i> Enable
                                    </button>
                                <?php else: ?>
                                    <button onclick="toggleEmergencyStatus(<?php echo $doctor['doctor_id']; ?>, 1)" 
                                            class="btn btn-sm btn-warning">
                                        <i class="fas fa-times me-1"></i> Disable
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <i class="fas fa-ban me-1"></i> Doctor Inactive
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ===== FEEDBACK SECTION (Full Width) ===== -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Patient Feedbacks</h5>
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-star me-1"></i> <?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?>
                        <span class="ms-1">(<?php echo $total_reviews; ?> reviews)</span>
                    </span>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                        <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <span class="feedback-name">
                                        <?php echo htmlspecialchars($feedback['commenter_name']); ?>
                                        <small class="text-muted ms-2"><?php echo htmlspecialchars($feedback['commenter_gmail']); ?></small>
                                    </span>
                                    <span class="feedback-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? 'text-warning' : 'text-muted'; ?>"></i>
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
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No feedbacks found for this doctor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function deleteDoctor(doctorId) {
    if (confirm('Are you sure you want to delete this doctor? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + doctorId;
    }
}

function toggleEmergencyStatus(doctorId, newStatus) {
    var action = newStatus == 1 ? 'disable emergency services' : 'enable emergency services';
    if (confirm('Are you sure you want to ' + action + ' for this doctor?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                window.location.reload();
            }
        };
        xhr.send('toggle_emergency=' + doctorId + '&status=' + newStatus);
    }
}

function deleteClinical(clinicalId) {
    if (confirm('Are you sure you want to delete this clinical information? This action cannot be undone.')) {
        window.location.href = 'profile?id=<?php echo $doctor['doctor_id']; ?>&del_clinic_id=' + clinicalId;
    }
}

function showPassword(password) {
    alert('Password: ' + password);
}
</script>

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>