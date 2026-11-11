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
// FETCH REGISTERED LABS
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
// NOTES BOARD — auto-create table if needed
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

// AJAX: save note
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

// Fetch existing note
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

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-hospital-admin.css">

<style>
/* ── 2-Column Dashboard Grid ────────────────────────── */
.two-col-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;      /* 2 equal columns */
    gap: 24px;
    align-items: start;                  /* don't stretch cards */
    margin-top: 24px;
}

.two-col-dashboard .col-stack {
    display: flex;
    flex-direction: column;
    gap: 24px;                           /* vertical gap between cards in same column */
}

/* Remove the old margin hack on notes card since gap handles spacing */
.two-col-dashboard .info-card {
    margin-top: 0 !important;
}

/* Responsive: 1 column on mobile/tablet */
@media (max-width: 992px) {
    .two-col-dashboard {
        grid-template-columns: 1fr;
    }
}

/* Custom styles for Lab List */
.lab-list-item {
    display: flex;
    align-items: center;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 12px;
    border: 1px solid #e2e8f0;
    gap: 12px;
}
.lab-list-item:last-child {
    margin-bottom: 0;
}
.lab-avatar {
    width: 45px;
    height: 45px;
    border-radius: 8px;
    background: #e0f2fe;
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.lab-info {
    flex: 1;
    min-width: 0;
}
.lab-info .lab-name {
    font-weight: 700;
    font-size: 0.95rem;
    color: #0D2B6E;
    margin-bottom: 2px;
}
.lab-info .lab-city {
    font-size: 0.8rem;
    color: #64748b;
}
.btn-remove-lab {
    background: #fee2e2;
    color: #ef4444;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-remove-lab:hover {
    background: #ef4444;
    color: #fff;
}
</style>

<div class="content-wrapper">

    <!-- ===== WELCOME SECTION ===== -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>👋 Welcome, <?php echo htmlspecialchars($hospital_data['hospital_name']); ?></h1>
            <p class="welcome-sub">Manage your hospital dashboard, doctors, beds, facilities and more.</p>
            <div class="welcome-badges">
                <span class="welcome-badge">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo htmlspecialchars($hospital_data['city_name'] ?? 'N/A'); ?>
                </span>
                <span class="welcome-badge">
                    <i class="fas fa-phone"></i>
                    <?php echo htmlspecialchars($hospital_data['hospital_phone']); ?>
                </span>
                <span class="welcome-badge">
                    <i class="fas fa-envelope"></i>
                    <?php echo htmlspecialchars($hospital_data['hospital_email'] ?? 'N/A'); ?>
                </span>
                <span class="welcome-badge">
                    <i class="fas fa-calendar-alt"></i>
                    Member since <?php echo date('M Y', strtotime($hospital_data['created_at'])); ?>
                </span>
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
                                <div class="doctor-avatar">
                                    <?php echo strtoupper(substr($doctor['doctor_name'], 0, 1)); ?>
                                </div>
                                <div class="doctor-info">
                                    <div class="name">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></div>
                                    <div class="spec"><?php echo htmlspecialchars($doctor['specialization'] ?? 'General'); ?></div>
                                    <div class="date">Added: <?php echo date('d M Y', strtotime($doctor['created_at'])); ?></div>
                                </div>
                                <a href="<?php echo BASE_URL; ?>hospital/doctor-add?id=<?php echo $doctor['doctor_id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">Edit</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No doctors registered yet.</p>
                    <?php endif; ?>
                </div>
            </div><!-- end Recent Doctors -->

            <!-- Notes Board -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-sticky-note"></i> Notes Board</h5>
                    <?php if ($note_row): ?>
                        <small id="noteLastSaved" style="color:#00A8CC; font-size:0.78rem; font-weight:600;">
                            <i class="fas fa-clock me-1"></i>
                            Last saved: <?= date('d M Y, h:i A', strtotime($note_row['updated_at'])) ?>
                        </small>
                    <?php else: ?>
                        <small id="noteLastSaved" style="color:#94a3b8; font-size:0.78rem;"></small>
                    <?php endif; ?>
                </div>
                <div class="info-card-body">
                    <textarea id="noteTextarea"
                              placeholder="Write your notes, reminders, or important info here..."
                              style="width:100%; min-height:140px; padding:14px 16px;
                                     border:2px solid rgba(0,168,204,0.2); border-radius:12px;
                                     font-size:0.92rem; color:#0D2B6E; line-height:1.7;
                                     resize:vertical; outline:none; transition:border-color 0.2s;
                                     font-family:inherit; background:#f0f9ff;"
                              onfocus="this.style.borderColor='#00A8CC'; this.style.background='#fff';"
                              onblur="this.style.borderColor='rgba(0,168,204,0.2)'; this.style.background='#f0f9ff';"
                    ><?= htmlspecialchars($note_row['notes'] ?? '') ?></textarea>

                    <div id="noteError" style="display:none;"></div>

                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:12px; flex-wrap:wrap; gap:8px;">
                        <button id="saveNoteBtn" onclick="saveNote()"
                                style="background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:white;
                                       border:none; padding:10px 24px; border-radius:10px;
                                       font-weight:700; font-size:0.88rem; cursor:pointer;
                                       display:inline-flex; align-items:center; gap:8px;
                                       transition:all 0.2s;"
                                onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 18px rgba(28,63,143,0.3)'"
                                onmouseout="this.style.transform=''; this.style.boxShadow=''">
                            <i class="fas fa-save"></i> Save Note
                        </button>

                        <div id="noteSavedBadge" style="display:none; align-items:center; gap:6px;
                             background:rgba(39,201,106,0.12); border:1px solid rgba(39,201,106,0.3);
                             border-radius:20px; padding:5px 14px; font-size:0.82rem;
                             font-weight:700; color:#27C96A;">
                            <i class="fas fa-check-circle"></i> <span id="noteSavedText">Saved!</span>
                        </div>
                    </div>
                </div>
            </div><!-- end Notes Board -->

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
                        <a href="<?php echo BASE_URL; ?>hospital/profile" class="quick-link-item">
                            <i class="fas fa-edit"></i>
                            <span>Edit Profile</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/doctors/list" class="quick-link-item">
                            <i class="fas fa-user-md"></i>
                            <span>Manage Doctors</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/beds" class="quick-link-item">
                            <i class="fas fa-bed"></i>
                            <span>Manage Beds</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/facilities" class="quick-link-item">
                            <i class="fas fa-concierge-bell"></i>
                            <span>Manage Facilities</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/feedback" class="quick-link-item">
                            <i class="fas fa-star"></i>
                            <span>View Reviews</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>hospital/recycle" class="quick-link-item" style="color: var(--danger);">
                            <i class="fas fa-trash"></i>
                            <span>Recycle Bin</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>logout" class="quick-link-item" style="color: var(--danger);">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ===== NEW: REGISTERED LABS ===== -->
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
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo htmlspecialchars($lab['lab_pic']); ?>" 
                                         class="lab-avatar" style="object-fit: cover;">
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
            <!-- ===== END REGISTERED LABS ===== -->

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

// Ctrl+S shortcut
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
                // Remove the row visually without reloading
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
</script>