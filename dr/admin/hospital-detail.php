<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php
// ============================================
// DOCTORS TABLE COLUMN NAMES
// Agar aapke DB mein naam alag hon to sirf yahan change karein
// ============================================
$DOC_COL_PMDC  = 'pmdc_no';
$DOC_COL_CNIC  = 'cnic';
$DOC_COL_NAME  = 'doctor_name';
$DOC_COL_EMAIL = 'doctor_email';

// ============================================
// AJAX: SEARCH DOCTOR (JSON) - includes se PEHLE
// ============================================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'search_doctor' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    header('Content-Type: application/json; charset=utf-8');

    $ajax_hospital_id = (int)$_GET['id'];
    $by = $_GET['by'] ?? 'name';
    $q  = trim($_GET['q'] ?? '');

    if (mb_strlen($q) < 2) {
        echo json_encode(['ok' => false, 'error' => 'Please enter at least 2 characters.']);
        exit();
    }

    try {
        $q_sql = mysqli_real_escape_string($con, $q);

        switch ($by) {
            case 'pmdc':
                $cond = "d.`$DOC_COL_PMDC` LIKE '%$q_sql%'";
                break;
            case 'cnic':
                // dashes ignore karke digits se match
                $digits = preg_replace('/\D/', '', $q);
                if ($digits === '') {
                    echo json_encode(['ok' => false, 'error' => 'Please enter CNIC digits.']);
                    exit();
                }
                $cond = "REPLACE(d.`$DOC_COL_CNIC`, '-', '') LIKE '%$digits%'";
                break;
            case 'email':
                $cond = "d.`$DOC_COL_EMAIL` LIKE '%$q_sql%'";
                break;
            default:
                $cond = "d.`$DOC_COL_NAME` LIKE '%$q_sql%'";
                break;
        }

        $sql = "SELECT d.*, dct.type AS specialization,
                       dih.doctor_in_hosp_id AS dih_id, dih.inactive AS dih_inactive
                FROM doctors d
                INNER JOIN users u ON u.user_id = d.user_id
                LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                LEFT JOIN doctor_in_hospital dih ON dih.doctor_id = d.doctor_id AND dih.hospital_id = $ajax_hospital_id
                WHERE d.approve = 1 AND u.status = 1 AND $cond
                ORDER BY d.`$DOC_COL_NAME` ASC
                LIMIT 20";
        $res = mysqli_query($con, $sql);

        $out = [];
        $seen = [];
        while ($row = mysqli_fetch_assoc($res)) {
            if (isset($seen[$row['doctor_id']])) { continue; }
            $seen[$row['doctor_id']] = true;

            if ($row['dih_id'] === null) {
                $status = 'available';
            } elseif ((int)$row['dih_inactive'] === 1) {
                $status = 'removed';
            } else {
                $status = 'active';
            }

            $out[] = [
                'doctor_id'      => (int)$row['doctor_id'],
                'name'           => $row[$DOC_COL_NAME] ?? '',
                'specialization' => $row['specialization'] ?? 'General',
                'phone'          => $row['doctor_phone'] ?? '',
                'email'          => $row[$DOC_COL_EMAIL] ?? '',
                'pmdc'           => $row[$DOC_COL_PMDC] ?? '',
                'cnic'           => $row[$DOC_COL_CNIC] ?? '',
                'pic'            => $row['doctor_pic'] ?? '',
                'status'         => $status,
            ];
        }
        echo json_encode(['ok' => true, 'doctors' => $out]);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'error' => 'Search failed: ' . $e->getMessage()]);
    }
    exit();
}

// ============================================
// Handle delete operation
// ============================================
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    $delete_query = "UPDATE users set status=0 WHERE user_id = $delete_id";

    if (mysqli_query($con, $delete_query)) {
        $_SESSION['success_msg'] = "Hospital deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }

    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}

// ============================================
// Handle ADD / remove / re-add doctor (doctor_in_hospital)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['doctor_action'])
    && isset($_GET['id']) && is_numeric($_GET['id'])) {

    $post_hospital_id = (int)$_GET['id'];
    $action = $_POST['doctor_action'];

    // ---------- ADD DOCTOR to this hospital ----------
    if ($action === 'add') {
        $add_doctor_id = (int)($_POST['doctor_id'] ?? 0);

        $doc_check = mysqli_query($con, "SELECT d.doctor_id
                                         FROM doctors d
                                         INNER JOIN users u ON u.user_id = d.user_id
                                         WHERE d.doctor_id = $add_doctor_id AND d.approve = 1 AND u.status = 1");

        if ($add_doctor_id <= 0 || !$doc_check || mysqli_num_rows($doc_check) == 0) {
            $_SESSION['error_msg'] = "Doctor not found or not approved.";
        } else {
            $exist_q = mysqli_query($con, "SELECT doctor_in_hosp_id, inactive
                                           FROM doctor_in_hospital
                                           WHERE doctor_id = $add_doctor_id AND hospital_id = $post_hospital_id
                                           LIMIT 1");
            if ($exist_q && mysqli_num_rows($exist_q) > 0) {
                $exist = mysqli_fetch_assoc($exist_q);
                if ((int)$exist['inactive'] === 0) {
                    $_SESSION['error_msg'] = "This doctor is already added to this hospital.";
                } else {
                    // Pehle remove hua tha: dobara active kar dein
                    $ex_id = (int)$exist['doctor_in_hosp_id'];
                    if (mysqli_query($con, "UPDATE doctor_in_hospital SET inactive = 0 WHERE doctor_in_hosp_id = $ex_id")) {
                        $_SESSION['success_msg'] = "Doctor re-added to hospital successfully!";
                    } else {
                        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
                    }
                }
            } else {
                $if_clinic = 0;
                $stmt = mysqli_prepare($con, "INSERT INTO doctor_in_hospital (doctor_id, hospital_id, if_clinic) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iii", $add_doctor_id, $post_hospital_id, $if_clinic);
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_msg'] = "Doctor added to hospital successfully!";
                } else {
                    $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
                }
                mysqli_stmt_close($stmt);
            }
        }

    // ---------- REMOVE / RE-ADD (existing doctor_in_hospital row) ----------
    } elseif (isset($_POST['doctor_in_hosp_id'])) {
        $dih_id = (int)$_POST['doctor_in_hosp_id'];

        if ($action === 'remove') {
            $inactive_value = 1;
            $msg = "Doctor removed from hospital successfully!";
        } elseif ($action === 'readd') {
            $inactive_value = 0;
            $msg = "Doctor re-added to hospital successfully!";
        } else {
            $inactive_value = null;
        }

        if ($inactive_value !== null) {
            $update_query = "UPDATE doctor_in_hospital
                             SET inactive = $inactive_value
                             WHERE doctor_in_hosp_id = $dih_id AND hospital_id = $post_hospital_id";
            if (mysqli_query($con, $update_query)) {
                // FIX: pehle $new_inactive use ho raha tha jo defined hi nahi tha
                if ($inactive_value === 1) {
                    mysqli_query($con, "DELETE FROM `clinical_info` WHERE `doctor_in_hosp_id` = $dih_id");
                }
                $_SESSION['success_msg'] = $msg;
            } else {
                $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
            }
        }
    }

    // Redirect back to same page (avoids form resubmission)
    header('Location: ' . $_SERVER['REQUEST_URI']);
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

// Current doctors (inactive = 0)
$doctors_query = "SELECT d.*, dct.type as specialization, dih.doctor_in_hosp_id
                  FROM doctor_in_hospital dih
                  INNER JOIN doctors d ON d.doctor_id = dih.doctor_id
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  LEFT JOIN users u ON d.user_id = u.user_id
                  WHERE dih.hospital_id = $hospital_id AND u.status = 1 AND d.approve = 1 AND dih.inactive = 0
                  ORDER BY d.doctor_name ASC";
$doctors_result = mysqli_query($con, $doctors_query);
$total_doctors = $doctors_result ? mysqli_num_rows($doctors_result) : 0;

// Past doctors (inactive = 1)
$past_doctors_query = "SELECT d.*, dct.type as specialization, dih.doctor_in_hosp_id
                  FROM doctor_in_hospital dih
                  INNER JOIN doctors d ON d.doctor_id = dih.doctor_id
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  LEFT JOIN users u ON d.user_id = u.user_id
                  WHERE dih.hospital_id = $hospital_id AND u.status = 1 AND d.approve = 1 AND dih.inactive = 1
                  ORDER BY d.doctor_name ASC";
$past_doctors_result = mysqli_query($con, $past_doctors_query);
$total_past_doctors = $past_doctors_result ? mysqli_num_rows($past_doctors_result) : 0;

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

<style>
    /* ===== Add Doctor panel ===== */
    .add-doctor-panel {
        display: none;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }
    .add-doctor-panel.open { display: block; }

    .ad-grid {
        display: grid;
        grid-template-columns: 150px minmax(160px, 1fr) auto;
        gap: 12px;
        align-items: end;
    }
    .ad-field { display: flex; flex-direction: column; min-width: 0; }
    .ad-label {
        display: block;
        margin: 0 0 6px;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
    }
    .ad-control {
        width: 100%;
        height: 40px;
        padding: 0 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background-color: #fff;
        font-size: 14px;
        color: #1e293b;
        box-sizing: border-box;
        outline: none;
    }
    .ad-control:focus {
        border-color: #4facfe;
        box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.18);
    }
    .ad-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 40px;
        padding: 0 18px;
        border: 1px solid #4facfe;
        border-radius: 8px;
        background: #4facfe;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }
    .ad-btn:hover { background: #2f9bf5; }

    .ad-results { margin-top: 14px; }
    .ad-message {
        font-size: 13px;
        color: #64748b;
        text-align: center;
        padding: 10px 0;
    }
    .ad-message.error { color: #dc2626; }

    .ad-result-card {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
    }
    .ad-result-card .ad-avatar,
    .ad-result-card .ad-avatar-ph {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        flex-shrink: 0;
        object-fit: cover;
    }
    .ad-result-card .ad-avatar-ph {
        background: #e2e8f0;
        color: #475569;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ad-info { flex: 1; min-width: 0; }
    .ad-info h6 { margin: 0 0 2px; font-size: 14px; font-weight: 700; color: #1e293b; }
    .ad-info .ad-spec { display: block; font-size: 12px; color: #4facfe; font-weight: 600; }
    .ad-info .ad-meta { display: block; font-size: 12px; color: #64748b; word-break: break-word; }
    .ad-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        background: #dcfce7;
        color: #166534;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .ad-grid { grid-template-columns: 1fr; }
        .ad-result-card { flex-wrap: wrap; }
    }
</style>

<div class="content-wrapper">

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

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
            <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
            <div class="stat-number"><?php echo $total_past_doctors; ?></div>
            <div class="stat-label">Total Past Doctors</div>
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

            <!-- Doctors (current) -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-user-md"></i> Doctors (<?php echo $total_doctors; ?>)</h5>
                    <button type="button" class="btn btn-sm btn-primary" id="toggleAddDoctor">
                        <i class="fas fa-plus me-1"></i> Add Doctor
                    </button>
                </div>
                <div class="info-card-body">

                    <!-- ===== ADD DOCTOR PANEL ===== -->
                    <div class="add-doctor-panel" id="addDoctorPanel">
                        <form id="doctorSearchForm" autocomplete="off">
                            <div class="ad-grid">
                                <div class="ad-field">
                                    <label class="ad-label" for="ad_by">Search By</label>
                                    <select id="ad_by" class="ad-control">
                                        <option value="pmdc">PMDC No</option>
                                        <option value="name">Name</option>
                                        <option value="cnic">CNIC</option>
                                        <option value="email">Email</option>
                                    </select>
                                </div>
                                <div class="ad-field">
                                    <label class="ad-label" for="ad_q">Search</label>
                                    <input type="text" id="ad_q" class="ad-control" placeholder="Enter PMDC number...">
                                </div>
                                <button type="submit" class="ad-btn">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>
                        <div class="ad-results" id="adResults"></div>
                    </div>

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
                                <div class="d-flex gap-1">
                                    <a href="<?php echo BASE_URL; ?>admin/doctors/profile?id=<?php echo $doctor['doctor_id']; ?>" 
                                       class="btn btn-sm btn-primary">View</a>
                                    <form method="POST" action="" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to remove this doctor from the hospital?');">
                                        <input type="hidden" name="doctor_action" value="remove">
                                        <input type="hidden" name="doctor_in_hosp_id" value="<?php echo (int)$doctor['doctor_in_hosp_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times me-1"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No doctors registered at this hospital</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Past Doctors -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-user-md"></i> Past Registered Doctors (<?php echo $total_past_doctors; ?>)</h5>
                </div>
                <div class="info-card-body">
                    <?php if ($total_past_doctors > 0): ?>
                        <?php while ($past_doctor = mysqli_fetch_assoc($past_doctors_result)): ?>
                            <div class="doctor-mini-card">
                                <?php if (!empty($past_doctor['doctor_pic'])): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $past_doctor['doctor_pic']; ?>" 
                                         alt="<?php echo htmlspecialchars($past_doctor['doctor_name']); ?>" class="doctor-avatar">
                                <?php else: ?>
                                    <div class="doctor-avatar-placeholder">
                                        <?php echo strtoupper(substr($past_doctor['doctor_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="doctor-info">
                                    <h6>Dr. <?php echo htmlspecialchars($past_doctor['doctor_name']); ?></h6>
                                    <span class="doctor-spec"><?php echo htmlspecialchars($past_doctor['specialization'] ?? 'General'); ?></span>
                                    <span class="doctor-phone"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($past_doctor['doctor_phone'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="<?php echo BASE_URL; ?>admin/doctors/profile?id=<?php echo $past_doctor['doctor_id']; ?>" 
                                       class="btn btn-sm btn-primary">View</a>
                                    <form method="POST" action="" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to re-add this doctor to the hospital?');">
                                        <input type="hidden" name="doctor_action" value="readd">
                                        <input type="hidden" name="doctor_in_hosp_id" value="<?php echo (int)$past_doctor['doctor_in_hosp_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus me-1"></i> Re-add
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No past doctors found for this hospital</p>
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

// ============================================
// ADD DOCTOR: toggle + search + add
// ============================================
(function () {
    const HOSPITAL_ID = <?php echo (int)$hospital_id; ?>;
    const DOC_PIC_URL = <?php echo json_encode(BASE_URL . 'admin/inc/uploads/doctors/'); ?>;
    const PROFILE_URL = <?php echo json_encode(BASE_URL . 'admin/doctors/profile?id='); ?>;

    const toggleBtn = document.getElementById('toggleAddDoctor');
    const panel     = document.getElementById('addDoctorPanel');
    const form      = document.getElementById('doctorSearchForm');
    const bySelect  = document.getElementById('ad_by');
    const qInput    = document.getElementById('ad_q');
    const results   = document.getElementById('adResults');

    if (!toggleBtn || !panel) { return; }

    const placeholders = {
        pmdc:  'Enter PMDC number...',
        name:  'Enter doctor name...',
        cnic:  'Enter CNIC (e.g. 35202-1234567-1)...',
        email: 'Enter email address...'
    };

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function showMessage(text, isError) {
        results.innerHTML = '<div class="ad-message' + (isError ? ' error' : '') + '">' + esc(text) + '</div>';
    }

    toggleBtn.addEventListener('click', function () {
        panel.classList.toggle('open');
        if (panel.classList.contains('open')) { qInput.focus(); }
    });

    bySelect.addEventListener('change', function () {
        qInput.placeholder = placeholders[this.value] || 'Search...';
        qInput.focus();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const q = qInput.value.trim();
        if (q.length < 2) {
            showMessage('Please enter at least 2 characters.', true);
            return;
        }

        showMessage('Searching...', false);

        const params = new URLSearchParams({
            id: HOSPITAL_ID,
            ajax: 'search_doctor',
            by: bySelect.value,
            q: q
        });

        fetch('?' + params.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) { showMessage(data.error || 'Search failed.', true); return; }
                renderResults(data.doctors);
            })
            .catch(() => showMessage('Something went wrong. Please try again.', true));
    });

    function renderResults(doctors) {
        if (!doctors.length) {
            showMessage('No doctor found.', false);
            return;
        }

        results.innerHTML = '';
        doctors.forEach(function (d) {
            const card = document.createElement('div');
            card.className = 'ad-result-card';

            let avatar;
            if (d.pic) {
                avatar = '<img class="ad-avatar" src="' + esc(DOC_PIC_URL + encodeURIComponent(d.pic)) + '" alt="">';
            } else {
                avatar = '<div class="ad-avatar-ph">' + esc((d.name || '?').charAt(0).toUpperCase()) + '</div>';
            }

            const meta = [];
            if (d.pmdc)  meta.push('<span class="ad-meta"><i class="fas fa-id-badge me-1"></i> PMDC: ' + esc(d.pmdc) + '</span>');
            if (d.cnic)  meta.push('<span class="ad-meta"><i class="fas fa-id-card me-1"></i> CNIC: ' + esc(d.cnic) + '</span>');
            if (d.email) meta.push('<span class="ad-meta"><i class="fas fa-envelope me-1"></i> ' + esc(d.email) + '</span>');
            if (d.phone) meta.push('<span class="ad-meta"><i class="fas fa-phone me-1"></i> ' + esc(d.phone) + '</span>');

            let action;
            if (d.status === 'active') {
                action = '<span class="ad-badge"><i class="fas fa-check me-1"></i> Already added</span>';
            } else {
                const label = d.status === 'removed' ? 'Re-add' : 'Add';
                action = '<button type="button" class="btn btn-sm btn-success ad-add-btn" data-id="' + d.doctor_id + '" data-name="' + esc(d.name) + '">' +
                         '<i class="fas fa-plus me-1"></i> ' + label + '</button>';
            }

            card.innerHTML =
                avatar +
                '<div class="ad-info">' +
                    '<h6>Dr. ' + esc(d.name) + '</h6>' +
                    '<span class="ad-spec">' + esc(d.specialization) + '</span>' +
                    meta.join('') +
                '</div>' +
                '<div class="d-flex gap-1">' +
                    '<a href="' + esc(PROFILE_URL + d.doctor_id) + '" class="btn btn-sm btn-primary">View</a>' +
                    action +
                '</div>';

            results.appendChild(card);
        });

        results.querySelectorAll('.ad-add-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('Add Dr. ' + this.dataset.name + ' to this hospital?')) { return; }

                const f = document.createElement('form');
                f.method = 'POST';
                f.action = '';
                f.innerHTML =
                    '<input type="hidden" name="doctor_action" value="add">' +
                    '<input type="hidden" name="doctor_id" value="' + esc(this.dataset.id) + '">';
                document.body.appendChild(f);
                f.submit();
            });
        });
    }
})();
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>