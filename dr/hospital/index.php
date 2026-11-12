<?php
// ============================================
// START SESSION & INCLUDE CONFIG
// ============================================
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
$hospital_query = "SELECT h.*, c.city_name 
                   FROM hospitals h 
                   LEFT JOIN cities c ON h.city_id = c.city_id 
                   WHERE h.user_id = $user_id AND h.approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];
$entity_id   = $hospital_data['entity_id'];

// ============================================
// AJAX: REMOVE LAB FROM HOSPITAL
// ============================================
if (isset($_POST['action']) && $_POST['action'] == 'remove_lab') {
    header('Content-Type: application/json');
    $lab_user_id = (int)$_POST['lab_user_id'];

    if ($lab_user_id > 0) {
        $delete = "DELETE FROM assign_hospital WHERE hospital_id = $hospital_id AND user_id = $lab_user_id";
        if (mysqli_query($con, $delete)) {
            echo json_encode(['success' => true, 'message' => 'Lab removed successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Lab ID.']);
    }
    exit();
}

// ============================================
// AJAX: REMOVE RADIOLOGY FROM HOSPITAL
// ============================================
if (isset($_POST['action']) && $_POST['action'] == 'remove_radiology') {
    header('Content-Type: application/json');
    $rad_user_id = (int)$_POST['rad_user_id'];

    if ($rad_user_id > 0) {
        $delete = "DELETE FROM assign_hospital_radiology WHERE hospital_id = $hospital_id AND user_id = $rad_user_id";
        if (mysqli_query($con, $delete)) {
            echo json_encode(['success' => true, 'message' => 'Radiology removed successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Radiology ID.']);
    }
    exit();
}

// ============================================
// STATISTICS
// ============================================
$doctors_query = "SELECT COUNT(*) as total FROM doctor_in_hospital WHERE hospital_id = $hospital_id AND inactive = 0";
$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = mysqli_fetch_assoc($doctors_result)['total'];

$beds_query = "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id";
$beds_result = mysqli_query($con, $beds_query);
$beds = mysqli_fetch_assoc($beds_result);
$total_beds = $beds ? $beds['total_beds'] : 0;

$facilities_query = "SELECT COUNT(*) as total FROM hospital_facilities WHERE hospital_id = $hospital_id";
$facilities_result = mysqli_query($con, $facilities_query);
$total_facilities = mysqli_fetch_assoc($facilities_result)['total'];

$available_facilities_query = "SELECT COUNT(*) as total FROM hospital_facilities 
                               WHERE hospital_id = $hospital_id AND is_available = 1";
$available_facilities_result = mysqli_query($con, $available_facilities_query);
$available_facilities = mysqli_fetch_assoc($available_facilities_result)['total'];

$reviews_query = "SELECT COUNT(*) as total FROM feedback WHERE user_id = $user_id AND status = 1";
$reviews_result = mysqli_query($con, $reviews_query);
$total_reviews = mysqli_fetch_assoc($reviews_result)['total'];

$rating_query = "SELECT AVG(stars) as avg_rating FROM feedback WHERE user_id = $user_id AND status = 1";
$rating_result = mysqli_query($con, $rating_query);
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;

// ============================================
// FETCH REGISTERED LABS & RADIOLOGY
// ============================================
$labs_query = "SELECT l.lab_id, l.lab_name, l.user_id as lab_user_id, l.lab_pic, c.city_name 
               FROM assign_hospital ah 
               INNER JOIN laboratories l ON l.user_id = ah.user_id
               LEFT JOIN cities c ON l.city_id = c.city_id
               LEFT JOIN users u ON u.user_id = l.user_id
               WHERE ah.hospital_id = $hospital_id AND ah.status = 1 AND l.approve = 1 AND u.status = 1
               ORDER BY l.lab_name ASC";
$labs_result = mysqli_query($con, $labs_query);
$total_labs = mysqli_num_rows($labs_result);

// Fetch Radiology (for Dashboard list & banner)
$rad_query = "SELECT r.rad_id, r.rad_name, r.user_id as rad_user_id, r.rad_pic, c.city_name 
              FROM assign_hospital_radiology ahr 
              INNER JOIN radiology r ON r.user_id = ahr.user_id 
              LEFT JOIN cities c ON r.city_id = c.city_id
              LEFT JOIN users u ON u.user_id = r.user_id
              WHERE ahr.hospital_id = $hospital_id AND ahr.status = 1 AND r.approve = 1 AND u.status = 1
              ORDER BY r.rad_name ASC";
$rad_res = mysqli_query($con, $rad_query);
$total_radiology = mysqli_num_rows($rad_res);

$radiology_list = [];
while($r = mysqli_fetch_assoc($rad_res)) {
    $radiology_list[] = $r;
}

// Fetch All Doctors (for banner)
$all_doctors_list = [];
$doc_query = "SELECT d.doctor_name, dct.type as specialization 
              FROM doctor_in_hospital dih
              INNER JOIN doctors d ON dih.doctor_id = d.doctor_id
              LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
              WHERE dih.hospital_id = $hospital_id AND dih.inactive = 0 AND d.approve = 1";
$doc_res = mysqli_query($con, $doc_query);
while($d = mysqli_fetch_assoc($doc_res)) {
    $all_doctors_list[] = $d;
}

// Recent Doctors
$recent_doctors_query = "SELECT d.*, dct.type as specialization, u.status as ustatus
                         FROM doctor_in_hospital dih
                         INNER JOIN doctors d ON dih.doctor_id = d.doctor_id
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                         LEFT JOIN users u ON u.user_id = d.user_id
                         WHERE dih.hospital_id = $hospital_id AND dih.inactive = 0 AND d.approve = 1 AND u.status = 1
                         ORDER BY d.created_at DESC LIMIT 5";
$recent_doctors_result = mysqli_query($con, $recent_doctors_query);

// ============================================
// NOTES BOARD
// ============================================
$notes_tbl = mysqli_query($con, "SHOW TABLES LIKE 'notes'");
if (mysqli_num_rows($notes_tbl) == 0) {
    mysqli_query($con, "CREATE TABLE notes (
        note_id    INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id    INT(11) NOT NULL,
        notes      TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

if (isset($_POST['save_note'])) {
    $note_text = mysqli_real_escape_string($con, trim($_POST['note_text'] ?? ''));
    
    $exists = mysqli_query($con, "SELECT note_id FROM notes WHERE user_id = $user_id LIMIT 1");
    if (mysqli_num_rows($exists) > 0) {
        $row = mysqli_fetch_assoc($exists);
        mysqli_query($con, "UPDATE notes SET notes = '$note_text', updated_at = NOW() WHERE note_id = {$row['note_id']}");
        $action = 'updated';
    } else {
        mysqli_query($con, "INSERT INTO notes (user_id, notes, created_at, updated_at) VALUES ($user_id, '$note_text', NOW(), NOW())");
        $action = 'added';
    }
    echo json_encode(['success' => true, 'action' => $action, 'time' => date('d M Y, h:i A')]);
    exit();
}

$note_row = null;
$note_result = mysqli_query($con, "SELECT * FROM notes WHERE user_id = $user_id LIMIT 1");
if (mysqli_num_rows($note_result) > 0) {
    $note_row = mysqli_fetch_assoc($note_result);
}

// Recent Reviews
$recent_reviews_query = "SELECT * FROM feedback 
                         WHERE user_id = $user_id AND status = 1
                         ORDER BY created_at DESC LIMIT 5";
$recent_reviews_result = mysqli_query($con, $recent_reviews_query);
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/hospital/inc/nav.php'; ?>

<!-- Libraries for Image & QR Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-hospital-admin.css">

<style>
/* ── 2-Column Dashboard Grid ────────────────────────── */
.two-col-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    align-items: start;
    margin-top: 24px;
}
.two-col-dashboard .col-stack {
    display: flex;
    flex-direction: column;
    gap: 24px;
}
.two-col-dashboard .info-card {
    margin-top: 0 !important;
}
@media (max-width: 992px) {
    .two-col-dashboard {
        grid-template-columns: 1fr;
    }
}

/* Custom styles for Lab List */
.lab-list-item {
    display: flex; align-items: center; padding: 12px;
    background: #f8fafc; border-radius: 10px;
    margin-bottom: 12px; border: 1px solid #e2e8f0; gap: 12px;
}
.lab-list-item:last-child { margin-bottom: 0; }
.lab-avatar {
    width: 45px; height: 45px; border-radius: 8px;
    background: #e0f2fe; color: #0284c7;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0; object-fit: cover;
}
.lab-info { flex: 1; min-width: 0; }
.lab-info .lab-name { font-weight: 700; font-size: 0.95rem; color: #0D2B6E; margin-bottom: 2px; }
.lab-info .lab-city { font-size: 0.8rem; color: #64748b; }
.btn-remove-lab {
    background: #fee2e2; color: #ef4444; border: none;
    padding: 6px 12px; border-radius: 6px; font-size: 0.8rem;
    font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-remove-lab:hover { background: #ef4444; color: #fff; }

/* Radiology Specific Colors */
.rad-list-item { border-left: 3px solid #4338ca; }
.rad-avatar { background: #e0e7ff; color: #4338ca; }

/* Modal Styles */
.custom-modal {
    display: none; position: fixed; z-index: 9999;
    left: 0; top: 0; width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.6); overflow: auto;
}
.custom-modal-content {
    background-color: #f1f5f9; margin: 5% auto; padding: 20px;
    border-radius: 12px; width: 90%; max-width: 800px;
    position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
.close-modal {
    position: absolute; right: 20px; top: 15px;
    font-size: 28px; font-weight: bold; color: #333;
    cursor: pointer; z-index: 10;
}
.close-modal:hover { color: #ef4444; }
.modal-actions {
    text-align: center; margin-top: 20px;
}

/* --- VISITING CARD DESIGN --- */
#visitCardPreview {
    width: 600px; height: 340px;
    background: #fff; border-radius: 16px;
    margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: flex; overflow: hidden;
    border: 2px solid #e2e8f0; position: relative;
}
#visitCardPreview .vc-left {
    width: 40%; background: linear-gradient(135deg, #0D2B6E 0%, #1C3F8F 100%);
    padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff;
}
#visitCardPreview .vc-logo {
    width: 100px; height: 100px; border-radius: 50%;
    background: #fff; padding: 10px; object-fit: contain; margin-bottom: 15px;
}
#visitCardPreview .vc-right {
    width: 60%; padding: 25px; position: relative; display: flex; flex-direction: column; justify-content: center;
}
#visitCardPreview .vc-name { font-size: 1.6rem; font-weight: 800; color: #0D2B6E; margin-bottom: 4px; }
#visitCardPreview .vc-type { font-size: 0.95rem; color: #00A8CC; font-weight: 600; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
#visitCardPreview .vc-detail { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 0.85rem; color: #475569; }
#visitCardPreview .vc-detail i { color: #1C3F8F; width: 16px; text-align: center; }
#visitCardPreview .vc-qr { position: absolute; bottom: 20px; right: 20px; padding: 5px; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; }

/* --- BANNER DESIGN --- */
#bannerPreview {
    width: 800px; height: 450px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 16px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 2px solid #cbd5e1; padding: 30px; position: relative; overflow: hidden;
    display: flex; flex-direction: column;
}
#bannerPreview .bn-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; border-bottom: 2px dashed #cbd5e1; padding-bottom: 15px; }
#bannerPreview .bn-logo { width: 80px; height: 80px; object-fit: contain; border-radius: 12px; background: #fff; padding: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
#bannerPreview .bn-title { font-size: 2rem; font-weight: 900; color: #0D2B6E; line-height: 1.1; margin: 0; }
#bannerPreview .bn-subtitle { color: #00A8CC; font-weight: 600; font-size: 1rem; }
#bannerPreview .bn-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; flex: 1; }
#bannerPreview .bn-section { background: rgba(255,255,255,0.7); padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; }
#bannerPreview .bn-section h4 { font-size: 1rem; font-weight: 800; color: #1C3F8F; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
#bannerPreview .bn-list { list-style: none; padding: 0; margin: 0; }
#bannerPreview .bn-list li { font-size: 0.85rem; color: #475569; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
#bannerPreview .bn-list li i { color: #27C96A; font-size: 0.7rem; }
#bannerPreview .bn-footer { margin-top: 15px; text-align: center; font-size: 0.8rem; color: #64748b; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
</style>

<div class="content-wrapper">

    <!-- ===== WELCOME SECTION ===== -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>👋 Welcome, <?php echo htmlspecialchars($hospital_data['hospital_name']); ?></h1>
            <p class="welcome-sub">Manage your hospital dashboard, doctors, beds, facilities and more.</p>
            <div class="welcome-badges">
                <span class="welcome-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hospital_data['city_name'] ?? 'N/A'); ?></span>
                <span class="welcome-badge"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($hospital_data['hospital_phone']); ?></span>
                <span class="welcome-badge"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($hospital_data['hospital_email'] ?? 'N/A'); ?></span>
                <span class="welcome-badge"><i class="fas fa-calendar-alt"></i> Member since <?php echo date('M Y', strtotime($hospital_data['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <!-- ===== STATS ROW ===== -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $total_doctors; ?></div>
                <div class="stat-label">Total Doctors</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-bed"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $total_beds; ?></div>
                <div class="stat-label">Total Beds</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-concierge-bell"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $available_facilities; ?>/<?php echo $total_facilities; ?></div>
                <div class="stat-label">Facilities Available</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $avg_rating > 0 ? $avg_rating : 'N/A'; ?></div>
                <div class="stat-label">Rating (<?php echo $total_reviews; ?> reviews)</div>
            </div>
        </div>
    </div>

    <!-- ===== 2-COLUMN DASHBOARD ===== -->
    <div class="two-col-dashboard">

        <!-- ============ LEFT COLUMN ============ -->
        <div class="col-stack">

            <!-- Recent Doctors -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-user-md"></i> Recent Doctors</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/doctors/list" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($recent_doctors_result) > 0): ?>
                        <?php while ($doctor = mysqli_fetch_assoc($recent_doctors_result)): ?>
                            <div class="doctor-item">
                                <div class="doctor-avatar"><?php echo strtoupper(substr($doctor['doctor_name'], 0, 1)); ?></div>
                                <div class="doctor-info">
                                    <div class="name">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></div>
                                    <div class="spec"><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></div>
                                    <div class="date">Added: <?php echo date('d M Y', strtotime($doctor['created_at'])); ?></div>
                                </div>
                                <a href="<?php echo BASE_URL; ?>hospital/doctor-add?id=<?php echo $doctor['doctor_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No doctors registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes Board -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-sticky-note"></i> Notes Board</h5>
                    <?php if ($note_row): ?>
                        <small id="noteLastSaved" style="color:#00A8CC; font-size:0.78rem; font-weight:600;">
                            <i class="fas fa-clock me-1"></i> Last saved: <?= date('d M Y, h:i A', strtotime($note_row['updated_at'])) ?>
                        </small>
                    <?php else: ?>
                        <small id="noteLastSaved" style="color:#94a3b8; font-size:0.78rem;"></small>
                    <?php endif; ?>
                </div>
                <div class="info-card-body">
                    <textarea id="noteTextarea" placeholder="Write your notes, reminders, or important info here..."
                              style="width:100%; min-height:140px; padding:14px 16px; border:2px solid rgba(0,168,204,0.2); border-radius:12px; font-size:0.92rem; color:#0D2B6E; line-height:1.7; resize:vertical; outline:none; transition:border-color 0.2s; font-family:inherit; background:#f0f9ff;"
                              onfocus="this.style.borderColor='#00A8CC'; this.style.background='#fff';"
                              onblur="this.style.borderColor='rgba(0,168,204,0.2)'; this.style.background='#f0f9ff';"
                    ><?= htmlspecialchars($note_row['notes'] ?? '') ?></textarea>
                    <div id="noteError" style="display:none;"></div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:12px; flex-wrap:wrap; gap:8px;">
                        <button id="saveNoteBtn" onclick="saveNote()" style="background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:white; border:none; padding:10px 24px; border-radius:10px; font-weight:700; font-size:0.88rem; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all 0.2s;">
                            <i class="fas fa-save"></i> Save Note
                        </button>
                        <div id="noteSavedBadge" style="display:none; align-items:center; gap:6px; background:rgba(39,201,106,0.12); border:1px solid rgba(39,201,106,0.3); border-radius:20px; padding:5px 14px; font-size:0.82rem; font-weight:700; color:#27C96A;">
                            <i class="fas fa-check-circle"></i> <span id="noteSavedText">Saved!</span>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- end LEFT column -->

        <!-- ============ RIGHT COLUMN ============ -->
        <div class="col-stack">

            <!-- Quick Actions -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="info-card-body">
                    <div class="quick-links">
                        
                        <a href="javascript:void(0)" onclick="openModal('visitCardModal')" class="quick-link-item" style="background: #fef3c7; border-color: #f59e0b; margin-bottom: 8px;">
                            <i class="fas fa-id-card" style="color:#d97706;"></i>
                            <span style="color:#b45309; font-weight:700;">Create Visiting Card</span>
                        </a>
                        
                        <a href="javascript:void(0)" onclick="openModal('bannerModal')" class="quick-link-item" style="background: #f0fdf4; border-color: #22c55e; margin-bottom: 15px;">
                            <i class="fas fa-image" style="color:#15803d;"></i>
                            <span style="color:#166534; font-weight:700;">Create Banner</span>
                        </a>

                        <a href="<?php echo BASE_URL; ?>hospital/profile" class="quick-link-item">
                            <i class="fas fa-edit"></i> <span>Edit Profile</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/doctors/list" class="quick-link-item">
                            <i class="fas fa-user-md"></i> <span>Manage Doctors</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/beds" class="quick-link-item">
                            <i class="fas fa-bed"></i> <span>Manage Beds</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/facilities" class="quick-link-item">
                            <i class="fas fa-concierge-bell"></i> <span>Manage Facilities</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/feedback" class="quick-link-item">
                            <i class="fas fa-star"></i> <span>View Reviews</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>logout" class="quick-link-item" style="color: var(--danger);">
                            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- REGISTERED LABS -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-flask"></i> Registered Labs (<?php echo $total_labs; ?>)</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/labs" class="btn btn-sm btn-primary">Manage</a>
                </div>
                <div class="info-card-body" id="labsContainer">
                    <?php if ($total_labs > 0): ?>
                        <?php while ($lab = mysqli_fetch_assoc($labs_result)): 
                            $pic_path = BASE_PATH . '/admin/inc/uploads/laboratories/' . trim($lab['lab_pic'] ?? '');
                            $has_pic = (!empty(trim($lab['lab_pic'] ?? '')) && strtolower(trim($lab['lab_pic'])) !== 'null' && file_exists($pic_path));
                        ?>
                            <div class="lab-list-item" id="lab-row-<?php echo $lab['lab_user_id']; ?>">
                                <?php if ($has_pic): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo htmlspecialchars($lab['lab_pic']); ?>" class="lab-avatar">
                                <?php else: ?>
                                    <div class="lab-avatar"><i class="fas fa-flask"></i></div>
                                <?php endif; ?>
                                <div class="lab-info">
                                    <div class="lab-name"><?php echo htmlspecialchars($lab['lab_name']); ?></div>
                                    <div class="lab-city"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($lab['city_name']); ?></div>
                                </div>
                                <button class="btn-remove-lab" onclick="removeLab(<?php echo $lab['lab_user_id']; ?>)">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No labs registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- REGISTERED RADIOLOGY (NEW SECTION) -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-x-ray"></i> Registered Radiology (<?php echo $total_radiology; ?>)</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/radiology" class="btn btn-sm btn-primary">Manage</a>
                </div>
                <div class="info-card-body" id="radiologyContainer">
                    <?php if ($total_radiology > 0): ?>
                        <?php foreach ($radiology_list as $rad): 
                            $rad_pic_path = BASE_PATH . '/admin/inc/uploads/radiology/' . trim($rad['rad_pic'] ?? '');
                            $has_rad_pic = (!empty(trim($rad['rad_pic'] ?? '')) && strtolower(trim($rad['rad_pic'])) !== 'null' && file_exists($rad_pic_path));
                        ?>
                            <div class="lab-list-item rad-list-item" id="rad-row-<?php echo $rad['rad_user_id']; ?>">
                                <?php if ($has_rad_pic): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/radiology/<?php echo htmlspecialchars($rad['rad_pic']); ?>" class="lab-avatar rad-avatar">
                                <?php else: ?>
                                    <div class="lab-avatar rad-avatar"><i class="fas fa-x-ray"></i></div>
                                <?php endif; ?>
                                <div class="lab-info">
                                    <div class="lab-name"><?php echo htmlspecialchars($rad['rad_name']); ?></div>
                                    <div class="lab-city"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($rad['city_name'] ?? 'N/A'); ?></div>
                                </div>
                                <button class="btn-remove-lab" onclick="removeRadiology(<?php echo $rad['rad_user_id']; ?>)">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No radiology centers registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Recent Reviews</h5>
                    <a href="<?php echo BASE_URL; ?>hospital/feedback" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($recent_reviews_result) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($recent_reviews_result)): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="review-name"><?php echo htmlspecialchars($review['commenter_name']); ?></span>
                                    <span class="review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['stars'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 0.75rem;"></i>
                                        <?php endfor; ?>
                                    </span>
                                </div>
                                <?php if (!empty($review['comment'])): ?>
                                    <p class="review-comment"><?php echo htmlspecialchars(substr($review['comment'], 0, 80)) . (strlen($review['comment']) > 80 ? '...' : ''); ?></p>
                                <?php endif; ?>
                                <div class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No reviews yet.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- end RIGHT column -->

    </div><!-- end 2-col dashboard -->

</div><!-- end content-wrapper -->


<!-- ========================================== -->
<!-- MODAL: VISITING CARD                       -->
<!-- ========================================== -->
<div id="visitCardModal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeModal('visitCardModal')">&times;</span>
        
        <div id="visitCardPreview">
            <div class="vc-left">
                <img src="<?php echo BASE_URL; ?>includes/uploads/logo.png" alt="Logo" class="vc-logo">
                <div style="text-align:center; color:rgba(255,255,255,0.7); font-size:0.8rem; margin-top:10px;">Scan to Visit<br>Hospital Profile</div>
            </div>
            <div class="vc-right">
                <div class="vc-name"><?php echo htmlspecialchars($hospital_data['hospital_name']); ?></div>
                <div class="vc-type">Hospital / Medical Center</div>
                
                <?php if (!empty($hospital_data['hospital_address']) && $hospital_data['hospital_address'] !== '0'): ?>
                <div class="vc-detail">
                    <i class="fas fa-map-marker-alt"></i> 
                    <?php echo htmlspecialchars($hospital_data['hospital_address']); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($hospital_data['hospital_phone']) && $hospital_data['hospital_phone'] !== '0'): ?>
                <div class="vc-detail">
                    <i class="fas fa-phone"></i> 
                    <?php echo htmlspecialchars($hospital_data['hospital_phone']); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($hospital_data['hospital_email']) && $hospital_data['hospital_email'] !== '0'): ?>
                <div class="vc-detail">
                    <i class="fas fa-envelope"></i> 
                    <?php echo htmlspecialchars($hospital_data['hospital_email']); ?>
                </div>
                <?php endif; ?>

                <div class="vc-detail">
                    <i class="fas fa-globe"></i> 
                    www.sehatnexa.com
                </div>

                <div class="vc-qr" id="qrcode"></div>
            </div>
        </div>

        <div class="modal-actions">
            <button onclick="downloadImage('visitCardPreview', 'Visiting_Card_<?php echo $hospital_id; ?>')" class="btn btn-success" style="padding:10px 24px; border-radius:8px; font-weight:bold;">
                <i class="fas fa-download"></i> Download Visiting Card
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: BANNER                              -->
<!-- ========================================== -->
<div id="bannerModal" class="custom-modal">
    <div class="custom-modal-content">
        <span class="close-modal" onclick="closeModal('bannerModal')">&times;</span>
        
        <div id="bannerPreview">
            <div class="bn-header">
                <img src="<?php echo BASE_URL; ?>includes/uploads/logo.png" alt="Logo" class="bn-logo">
                <div>
                    <h2 class="bn-title"><?php echo htmlspecialchars($hospital_data['hospital_name']); ?></h2>
                    <div class="bn-subtitle"><?php echo htmlspecialchars($hospital_data['city_name'] ?? ''); ?> • <?php echo htmlspecialchars($hospital_data['hospital_phone']); ?></div>
                </div>
            </div>

            <div class="bn-grid">
                <!-- Doctors Column -->
                <div class="bn-section">
                    <h4><i class="fas fa-user-md"></i> Our Doctors</h4>
                    <ul class="bn-list">
                        <?php if(!empty($all_doctors_list)): ?>
                            <?php foreach(array_slice($all_doctors_list, 0, 6) as $d): ?>
                                <li><i class="fas fa-check-circle"></i> Dr. <?php echo htmlspecialchars($d['doctor_name']); ?> <span style="font-size:0.7rem; color:#64748b;">(<?php echo htmlspecialchars($d['specialization'] ?? 'General'); ?>)</span></li>
                            <?php endforeach; ?>
                            <?php if(count($all_doctors_list) > 6): ?>
                                <li style="color:#00A8CC; font-weight:bold; margin-top:5px;">+ <?php echo count($all_doctors_list) - 6; ?> more doctors...</li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li>No doctors registered yet.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Labs & Radiology Column -->
                <div class="bn-section">
                    <h4><i class="fas fa-flask"></i> Diagnostics</h4>
                    <ul class="bn-list">
                        <?php if($total_labs > 0): ?>
                            <?php 
                                // Reset labs result for banner to avoid interference with main loop
                                mysqli_data_seek($labs_result, 0); 
                            ?>
                            <?php $lab_count = 0; while ($l = mysqli_fetch_assoc($labs_result)): $lab_count++; if($lab_count > 3) break; ?>
                                <li><i class="fas fa-check-circle"></i> <span style="font-weight:600;">Lab:</span> <?php echo htmlspecialchars($l['lab_name']); ?></li>
                            <?php endwhile; ?>
                        <?php endif; ?>

                        <?php if(!empty($radiology_list)): ?>
                            <?php foreach(array_slice($radiology_list, 0, 3) as $r): ?>
                                <li><i class="fas fa-check-circle"></i> <span style="font-weight:600;">Radiology:</span> <?php echo htmlspecialchars($r['rad_name']); ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if($total_labs == 0 && empty($radiology_list)): ?>
                            <li>No diagnostics registered.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="bn-footer">
                Visit us at sehatnexa.com
            </div>
        </div>

        <div class="modal-actions">
            <button onclick="downloadImage('bannerPreview', 'Hospital_Banner_<?php echo $hospital_id; ?>')" class="btn btn-success" style="padding:10px 24px; border-radius:8px; font-weight:bold;">
                <i class="fas fa-download"></i> Download Banner
            </button>
        </div>
    </div>
</div>


<?php include BASE_PATH . '/admin/inc/footer.php'; ?>

<script>
// ============================================
// SAVE NOTE
// ============================================
function saveNote() {
    var text = document.getElementById('noteTextarea').value.trim();
    var errBox  = document.getElementById('noteError');
    var btn     = document.getElementById('saveNoteBtn');
    var badge   = document.getElementById('noteSavedBadge');
    var badgeTxt = document.getElementById('noteSavedText');
    var lastSaved = document.getElementById('noteLastSaved');

    errBox.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    badge.style.display = 'none';

    var formData = new FormData();
    formData.append('save_note', '1');
    formData.append('note_text', text);

    fetch('<?= BASE_URL ?>hospital/index.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Note';

        if (data.success) {
            badgeTxt.textContent = data.action === 'updated' ? 'Updated!' : 'Saved!';
            badge.style.display = 'flex';
            lastSaved.innerHTML = '<i class="fas fa-clock me-1"></i> Last saved: ' + data.time;
            lastSaved.style.color = '#00A8CC';
            setTimeout(function() { badge.style.display = 'none'; }, 3000);
        } else {
            errBox.textContent = data.msg || 'Error occurred.';
            errBox.style.display = 'block';
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Note';
        errBox.textContent = 'Network error. Try again.';
        errBox.style.display = 'block';
    });
}

document.getElementById('noteTextarea').addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        saveNote();
    }
});

// ============================================
// REMOVE LAB
// ============================================
function removeLab(labUserId) {
    if (confirm("Are you sure you want to remove this lab from your hospital?")) {
        let formData = new URLSearchParams();
        formData.append('action', 'remove_lab');
        formData.append('lab_user_id', labUserId);

        fetch('<?= BASE_URL ?>hospital/index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                let row = document.getElementById('lab-row-' + labUserId);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Something went wrong. Please try again.");
        });
    }
}

// ============================================
// REMOVE RADIOLOGY
// ============================================
function removeRadiology(radUserId) {
    if (confirm("Are you sure you want to remove this radiology center from your hospital?")) {
        let formData = new URLSearchParams();
        formData.append('action', 'remove_radiology');
        formData.append('rad_user_id', radUserId);

        fetch('<?= BASE_URL ?>hospital/index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                let row = document.getElementById('rad-row-' + radUserId);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Something went wrong. Please try again.");
        });
    }
}

// ============================================
// MODAL & IMAGE GENERATION LOGIC
// ============================================
let qrGenerated = false;

function openModal(id) {
    document.getElementById(id).style.display = 'block';
    
    // Generate QR if opening Visiting Card and not generated yet
    if (id === 'visitCardModal' && !qrGenerated) {
        new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo BASE_URL; ?>hospital-detail?hospital_id=<?php echo $hospital_id; ?>",
            width: 70,
            height: 70,
            colorDark : "#0D2B6E",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
        qrGenerated = true;
    }
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Close modal if clicked outside
window.onclick = function(event) {
    if (event.target.classList.contains('custom-modal')) {
        event.target.style.display = "none";
    }
}

// Download function using html2canvas
function downloadImage(elementId, fileName) {
    let element = document.getElementById(elementId);
    let btn = event.target;
    let originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    btn.disabled = true;

    // Options for high quality
    html2canvas(element, {
        scale: 2, // High res
        useCORS: true,
        backgroundColor: null
    }).then(canvas => {
        // Create download link
        let link = document.createElement('a');
        link.download = fileName + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    }).catch(err => {
        console.error("Error generating image: ", err);
        alert("Failed to generate image. Please try again.");
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>