<?php
include '../config.php';

// ── SehatNexa brand colors (fallback agar palette config me include na ho) ──
$color1 = $color1 ?? '#1C3F8F'; $color2 = $color2 ?? '#00A8CC';
$color3 = $color3 ?? '#27C96A'; $color4 = $color4 ?? '#00C9A7';
$color5 = $color5 ?? '#0D2B6E'; $color6 = $color6 ?? '#E8F7FF';
$color7 = $color7 ?? '#F0FFF8'; $color8 = $color8 ?? '#FFFFFF';
$gradient_primary = $gradient_primary ?? 'linear-gradient(135deg, #1C3F8F 0%, #00A8CC 100%)';
$gradient_green   = $gradient_green   ?? 'linear-gradient(135deg, #27C96A 0%, #00C9A7 100%)';

// ── RADIOLOGY AUTH ───────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'radiology') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id = (int)$_SESSION['user_id'];

// ── RADIOLOGY DATA ───────────────────────────────────────────────
$rad_q = mysqli_query($con, "SELECT r.*, c.city_name
          FROM radiology r
          LEFT JOIN cities c ON r.city_id = c.city_id
          WHERE r.user_id = $user_id AND r.approve = 1 LIMIT 1");
$my_rad = mysqli_fetch_assoc($rad_q);

if (!$my_rad) { session_destroy(); header("Location: " . BASE_URL . "login"); exit(); }
$my_rad_id = (int)$my_rad['rad_id'];

// ── HOSPITAL ID ──────────────────────────────────────────────────
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'radiology/'); exit();
}
$hospital_id = (int)$_GET['id'];

// ── HOSPITAL JOIN REQUEST: ACCEPT / REJECT ─────────────────
if (isset($_POST['handle_join_request'])) {
    header('Content-Type: application/json; charset=utf-8');

    $req_id   = (int)($_POST['request_id'] ?? 0);
    $decision = $_POST['decision'] ?? '';

    if ($req_id <= 0 || !in_array($decision, ['accept', 'reject'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit();
    }

    mysqli_begin_transaction($con);
    try {
        // Request isi radiology user ki aur isi hospital ki honi chahiye
        $q = mysqli_prepare($con, "SELECT hospital_id FROM hospital_to_radiology_join_requests WHERE id = ? AND userid = ? AND hospital_id = ? LIMIT 1");
        mysqli_stmt_bind_param($q, 'iii', $req_id, $user_id, $hospital_id);
        mysqli_stmt_execute($q);
        $req = mysqli_fetch_assoc(mysqli_stmt_get_result($q));
        mysqli_stmt_close($q);

        if (!$req) {
            mysqli_rollback($con);
            echo json_encode(['success' => false, 'message' => 'Request not found or already handled.']);
            exit();
        }

        if ($decision === 'accept') {
            $chk = mysqli_prepare($con, "SELECT assign_hosp_id FROM assign_hospital_radiology WHERE user_id = ? AND hospital_id = ? LIMIT 1");
            mysqli_stmt_bind_param($chk, 'ii', $user_id, $hospital_id);
            mysqli_stmt_execute($chk);
            $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
            mysqli_stmt_close($chk);

            if ($exists) {
                $upd = mysqli_prepare($con, "UPDATE assign_hospital_radiology SET status = 1, updated_at = NOW() WHERE assign_hosp_id = ?");
                $aid = (int)$exists['assign_hosp_id'];
                mysqli_stmt_bind_param($upd, 'i', $aid);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
            } else {
                $ins = mysqli_prepare($con, "INSERT INTO assign_hospital_radiology (user_id, hospital_id, status, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())");
                mysqli_stmt_bind_param($ins, 'ii', $user_id, $hospital_id);
                mysqli_stmt_execute($ins);
                mysqli_stmt_close($ins);
            }
        }

        $del = mysqli_prepare($con, "DELETE FROM hospital_to_radiology_join_requests WHERE id = ? AND userid = ?");
        mysqli_stmt_bind_param($del, 'ii', $req_id, $user_id);
        mysqli_stmt_execute($del);
        mysqli_stmt_close($del);

        mysqli_commit($con);

        echo json_encode([
            'success'  => true,
            'decision' => $decision,
            'message'  => $decision === 'accept' ? 'Request accepted. Hospital added.' : 'Request rejected.'
        ]);
    } catch (Throwable $e) {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    }
    exit();
}

// ── HOSPITAL DETAILS (read-only) ─────────────────────────────────
$query = "SELECT h.*, c.city_name, u.status as estatus, u.username, u.email as user_email
          FROM hospitals h
          LEFT JOIN cities c ON h.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = h.user_id
          WHERE h.hospital_id = $hospital_id AND h.approve = 1 AND u.status = 1
          LIMIT 1";
$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'radiology/'); exit();
}

$hospital = mysqli_fetch_assoc($result);
$hosp_user_id = (int)$hospital['user_id'];

// ── PENDING REQUEST FROM THIS HOSPITAL (notification) ───────────
$pending_req = null;
$pq = mysqli_prepare($con, "SELECT id, created_at FROM hospital_to_radiology_join_requests WHERE userid = ? AND hospital_id = ? LIMIT 1");
mysqli_stmt_bind_param($pq, 'ii', $user_id, $hospital_id);
mysqli_stmt_execute($pq);
$pending_req = mysqli_fetch_assoc(mysqli_stmt_get_result($pq));
mysqli_stmt_close($pq);

// ── ALREADY WORKING IN THIS HOSPITAL? ───────────────────────────
$is_working = false;
$wq = mysqli_prepare($con, "SELECT assign_hosp_id FROM assign_hospital_radiology WHERE user_id = ? AND hospital_id = ? AND status = 1 LIMIT 1");
mysqli_stmt_bind_param($wq, 'ii', $user_id, $hospital_id);
mysqli_stmt_execute($wq);
$is_working = mysqli_num_rows(mysqli_stmt_get_result($wq)) > 0;
mysqli_stmt_close($wq);

// Beds
$beds_result = mysqli_query($con, "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id");
$beds = mysqli_fetch_assoc($beds_result);

// Facilities
$facilities_result = mysqli_query($con, "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id");
$facilities = [];
while ($row = mysqli_fetch_assoc($facilities_result)) { $facilities[] = $row; }
$total_facilities = count($facilities);
$available_facilities = 0;
foreach ($facilities as $fac) { if ($fac['is_available'] == 1) $available_facilities++; }

// Current doctors
$doctors_result = mysqli_query($con, "SELECT d.*, dct.type as specialization, dih.doctor_in_hosp_id
                  FROM doctor_in_hospital dih
                  INNER JOIN doctors d ON d.doctor_id = dih.doctor_id
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  LEFT JOIN users u ON d.user_id = u.user_id
                  WHERE dih.hospital_id = $hospital_id AND u.status = 1 AND d.approve = 1 AND dih.inactive = 0
                  ORDER BY d.doctor_name ASC");
$total_doctors = $doctors_result ? mysqli_num_rows($doctors_result) : 0;

// Past doctors
$past_doctors_result = mysqli_query($con, "SELECT d.*, dct.type as specialization, dih.doctor_in_hosp_id
                  FROM doctor_in_hospital dih
                  INNER JOIN doctors d ON d.doctor_id = dih.doctor_id
                  LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
                  LEFT JOIN users u ON d.user_id = u.user_id
                  WHERE dih.hospital_id = $hospital_id AND u.status = 1 AND d.approve = 1 AND dih.inactive = 1
                  ORDER BY d.doctor_name ASC");
$total_past_doctors = $past_doctors_result ? mysqli_num_rows($past_doctors_result) : 0;

// Registered labs
$labs_result = mysqli_query($con, "SELECT l.*, c.city_name
               FROM assign_hospital ah
               INNER JOIN laboratories l ON l.user_id = ah.user_id
               LEFT JOIN cities c ON l.city_id = c.city_id
               LEFT JOIN users u ON u.user_id = l.user_id
               WHERE ah.hospital_id = $hospital_id AND ah.status = 1 AND l.approve = 1 AND u.status = 1
               ORDER BY l.lab_name ASC");
$total_labs = $labs_result ? mysqli_num_rows($labs_result) : 0;

// Registered radiology
$radiology_result = mysqli_query($con, "SELECT r.*, c.city_name
                    FROM assign_hospital_radiology ahr
                    INNER JOIN radiology r ON r.user_id = ahr.user_id
                    LEFT JOIN cities c ON r.city_id = c.city_id
                    LEFT JOIN users u ON u.user_id = r.user_id
                    WHERE ahr.hospital_id = $hospital_id AND ahr.status = 1 AND r.approve = 1 AND u.status = 1
                    ORDER BY r.rad_name ASC");
$total_radiology = $radiology_result ? mysqli_num_rows($radiology_result) : 0;

// Feedback
$feedback_result = mysqli_query($con, "SELECT f.* FROM feedback f WHERE f.user_id = $hosp_user_id AND f.status = 1 ORDER BY f.created_at DESC LIMIT 10");

$rating_result = mysqli_query($con, "SELECT AVG(stars) as avg_rating, COUNT(feedback_id) as total_reviews
                 FROM feedback WHERE user_id = $hosp_user_id AND status = 1");
$rating_data = mysqli_fetch_assoc($rating_result);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'] ? (int)$rating_data['total_reviews'] : 0;
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/radiology/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/hospital-detail-admin.css">

<style>
    /* ===== SehatNexa brand colors (from $color1..$color8) ===== */
    :root {
        --brand-navy: <?= $color1 ?>;
        --brand-teal: <?= $color2 ?>;
        --brand-green: <?= $color3 ?>;
        --brand-mint: <?= $color4 ?>;
        --brand-dark: <?= $color5 ?>;
        --brand-light-blue: <?= $color6 ?>;
        --brand-light-green: <?= $color7 ?>;
    }

    /* Page header */
    .page-header-modern { background: <?= $gradient_primary ?> !important; }
    .page-header-modern .hospital-avatar-placeholder { background: rgba(255,255,255,.18) !important; color: <?= $color8 ?> !important; }
    .page-header-modern .btn-action-header { color: <?= $color8 ?> !important; }
    .page-header-modern .btn-action-header:hover { background: <?= $color3 ?> !important; }

    /* Cards */
    .info-card { border-color: <?= $color6 ?> !important; }
    .info-card-header { border-bottom: 1px solid <?= $color6 ?> !important; }
    .info-card-header h5 { color: <?= $color5 ?> !important; }
    .info-card-header h5 i { color: <?= $color2 ?> !important; }
    .info-row { border-bottom-color: <?= $color6 ?> !important; }
    .info-row .label { color: #64748b; }
    .info-row .value { color: <?= $color5 ?> !important; }

    /* Stats */
    .stat-number { color: <?= $color5 ?> !important; }
    .stat-icon.blue   { background: <?= $color6 ?> !important; color: <?= $color1 ?> !important; }
    .stat-icon.green  { background: <?= $color7 ?> !important; color: <?= $color3 ?> !important; }
    .stat-icon.orange { background: <?= $color6 ?> !important; color: <?= $color2 ?> !important; }
    .stat-icon.purple { background: <?= $color7 ?> !important; color: <?= $color4 ?> !important; }

    /* Beds / doctors / facilities */
    .bed-item { background: <?= $color6 ?> !important; }
    .bed-number { color: <?= $color1 ?> !important; }
    .doctor-mini-card { background: <?= $color6 ?> !important; border-color: rgba(0,168,204,.15) !important; }
    .doctor-avatar-placeholder { background: <?= $gradient_primary ?> !important; color: <?= $color8 ?> !important; }
    .doctor-info h6 { color: <?= $color5 ?> !important; }
    .doctor-spec { color: <?= $color2 ?> !important; }
    .facility-status.available { background: <?= $color3 ?> !important; }

    /* ===== Join request notification banner ===== */
    .join-banner {
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
        padding: 16px 20px; margin-bottom: 20px;
        background: linear-gradient(135deg, <?= $color6 ?>, <?= $color7 ?>);
        border: 1px solid rgba(0,168,204,.3); border-left: 5px solid <?= $color3 ?>;
        border-radius: 14px;
        box-shadow: 0 4px 14px rgba(28,63,143,.10);
        transition: opacity .3s, transform .3s;
    }
    .join-banner.removing { opacity: 0; transform: translateY(-10px); }
    .join-banner .jb-icon {
        width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
        background: <?= $gradient_primary ?>; color: <?= $color8 ?>;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .join-banner .jb-text { flex: 1; min-width: 200px; }
    .join-banner .jb-title { font-weight: 700; font-size: .95rem; color: <?= $color5 ?>; }
    .join-banner .jb-sub { font-size: .8rem; color: #64748b; margin-top: 2px; }
    .join-banner .jb-actions { display: flex; gap: 10px; }
    .jb-btn {
        border: 0; border-radius: 9px; padding: 10px 18px; font-size: .85rem; font-weight: 700;
        color: <?= $color8 ?>; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: opacity .2s, transform .2s;
    }
    .jb-btn:hover:not(:disabled) { transform: translateY(-1px); }
    .jb-btn:disabled { opacity: .55; cursor: not-allowed; }
    .jb-btn.accept { background: <?= $gradient_green ?>; }
    .jb-btn.reject { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .join-msg { display: none; margin-bottom: 16px; font-size: .88rem; font-weight: 600; }

    .working-banner {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 18px; margin-bottom: 20px;
        background: <?= $color7 ?>; border: 1px solid rgba(39,201,106,.35); border-left: 5px solid <?= $color3 ?>;
        border-radius: 12px; color: <?= $color5 ?>; font-weight: 600; font-size: .9rem;
    }
    .working-banner i { color: <?= $color3 ?>; }

    /* ===== Labs & Radiology list ===== */
    .lab-list-item {
        display: flex; align-items: center; padding: 12px;
        background: <?= $color6 ?>; border-radius: 10px;
        margin-bottom: 12px; border: 1px solid rgba(0,168,204,.15); border-left: 3px solid <?= $color2 ?>; gap: 12px;
    }
    .lab-list-item:last-child { margin-bottom: 0; }
    .lab-avatar {
        width: 45px; height: 45px; border-radius: 8px;
        background: <?= $color8 ?>; color: <?= $color2 ?>;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; flex-shrink: 0; object-fit: cover;
    }
    .lab-info { flex: 1; min-width: 0; }
    .lab-info .lab-name { font-weight: 700; font-size: 0.95rem; color: <?= $color5 ?>; margin-bottom: 2px; }
    .lab-info .lab-city { font-size: 0.8rem; color: #64748b; }
    .rad-list-item { background: <?= $color7 ?>; border-color: rgba(39,201,106,.2); border-left: 3px solid <?= $color3 ?>; }
    .rad-avatar { background: <?= $color8 ?>; color: <?= $color3 ?>; }
    .you-badge {
        display: inline-block; margin-left: 6px; padding: 2px 9px; border-radius: 12px;
        background: <?= $gradient_green ?>; color: <?= $color8 ?>; font-size: .68rem; font-weight: 700; vertical-align: middle;
    }
</style>

<div class="content-wrapper">

    <!-- ===== PAGE HEADER (view only) ===== -->
    <div class="page-header-modern">
        <div class="page-header-content">
            <div class="page-header-left">
                <?php if (!empty($hospital['hospital_pic']) && file_exists(BASE_PATH . '/admin/inc/uploads/hospitals/' . $hospital['hospital_pic'])): ?>
                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo htmlspecialchars($hospital['hospital_pic']); ?>"
                         alt="<?php echo htmlspecialchars($hospital['hospital_name']); ?>" class="hospital-avatar">
                <?php else: ?>
                    <div class="hospital-avatar-placeholder">
                        <i class="fas fa-hospital"></i>
                    </div>
                <?php endif; ?>
                <div class="page-header-title">
                    <h1><?php echo htmlspecialchars($hospital['hospital_name']); ?></h1>
                    <p><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($hospital['city_name'] ?? ''); ?>
                       <span class="mx-2">|</span>
                       <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($hospital['hospital_phone'] ?? ''); ?>
                       <span class="mx-2">|</span>
                       <span class="badge bg-success">Active</span>
                    </p>
                </div>
            </div>
            <div class="page-header-actions">
                <a href="<?php echo BASE_URL; ?>radiology/" class="btn-action-header" style="background: rgba(255,255,255,0.1);">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- ===== NOTIFICATION: JOIN REQUEST ===== -->
    <?php if ($pending_req): ?>
    <div class="join-banner" id="joinBanner">
        <div class="jb-icon"><i class="fas fa-bell"></i></div>
        <div class="jb-text">
            <div class="jb-title"><?php echo htmlspecialchars($hospital['hospital_name']); ?> wants you to join their hospital.</div>
            <div class="jb-sub"><i class="fas fa-clock me-1"></i>Requested on <?php echo date('d M Y, h:i A', strtotime($pending_req['created_at'])); ?></div>
        </div>
        <div class="jb-actions">
            <button type="button" class="jb-btn accept" onclick="handleJoinRequest(<?php echo (int)$pending_req['id']; ?>, 'accept', this)">
                <i class="fas fa-check"></i> Accept
            </button>
            <button type="button" class="jb-btn reject" onclick="handleJoinRequest(<?php echo (int)$pending_req['id']; ?>, 'reject', this)">
                <i class="fas fa-times"></i> Reject
            </button>
        </div>
    </div>
    <?php elseif ($is_working): ?>
    <div class="working-banner">
        <i class="fas fa-check-circle"></i> You are working in this hospital.
    </div>
    <?php endif; ?>
    <div class="join-msg" id="joinMsg" role="status"></div>

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
        <div class="stat-card">
            <div class="stat-icon" style="background:<?php echo $color7; ?>; color:<?php echo $color4; ?>;"><i class="fas fa-flask"></i></div>
            <div class="stat-number"><?php echo $total_labs; ?></div>
            <div class="stat-label">Total Labs</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:<?php echo $color6; ?>; color:<?php echo $color1; ?>;"><i class="fas fa-x-ray"></i></div>
            <div class="stat-number"><?php echo $total_radiology; ?></div>
            <div class="stat-label">Total Radiology</div>
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
                        <span class="value"><?php echo htmlspecialchars($hospital['city_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone</span>
                        <span class="value"><?php echo htmlspecialchars($hospital['hospital_phone'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Address</span>
                        <span class="value" style="text-align: right; max-width: 60%;"><?php echo nl2br(htmlspecialchars($hospital['hospital_address'] ?? '')); ?></span>
                    </div>
                    <?php if (!empty($hospital['user_email'])): ?>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value"><?php echo htmlspecialchars($hospital['user_email']); ?></span>
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
                </div>
                <div class="info-card-body">
                    <?php if ($total_doctors > 0): ?>
                        <?php while ($doctor = mysqli_fetch_assoc($doctors_result)): ?>
                            <div class="doctor-mini-card">
                                <?php if (!empty($doctor['doctor_pic'])): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo htmlspecialchars($doctor['doctor_pic']); ?>"
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
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No doctors registered at this hospital</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Registered Labs -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-flask"></i> Registered Labs (<?php echo $total_labs; ?>)</h5>
                </div>
                <div class="info-card-body">
                    <?php if ($total_labs > 0): ?>
                        <?php while ($lab = mysqli_fetch_assoc($labs_result)):
                            $pic_path = BASE_PATH . '/admin/inc/uploads/laboratories/' . trim($lab['lab_pic'] ?? '');
                            $has_pic = (!empty(trim($lab['lab_pic'] ?? '')) && strtolower(trim($lab['lab_pic'])) !== 'null' && file_exists($pic_path));
                        ?>
                            <div class="lab-list-item">
                                <?php if ($has_pic): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo htmlspecialchars($lab['lab_pic']); ?>" class="lab-avatar" alt="">
                                <?php else: ?>
                                    <div class="lab-avatar"><i class="fas fa-flask"></i></div>
                                <?php endif; ?>
                                <div class="lab-info">
                                    <div class="lab-name"><?php echo htmlspecialchars($lab['lab_name']); ?></div>
                                    <div class="lab-city"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($lab['city_name'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No labs registered at this hospital</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Registered Radiology -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-x-ray"></i> Registered Radiology (<?php echo $total_radiology; ?>)</h5>
                </div>
                <div class="info-card-body">
                    <?php if ($total_radiology > 0): ?>
                        <?php while ($rad = mysqli_fetch_assoc($radiology_result)):
                            $rad_pic_path = BASE_PATH . '/admin/inc/uploads/radiology/' . trim($rad['rad_pic'] ?? '');
                            $has_rad_pic = (!empty(trim($rad['rad_pic'] ?? '')) && strtolower(trim($rad['rad_pic'])) !== 'null' && file_exists($rad_pic_path));
                        ?>
                            <div class="lab-list-item rad-list-item">
                                <?php if ($has_rad_pic): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/radiology/<?php echo htmlspecialchars($rad['rad_pic']); ?>" class="lab-avatar rad-avatar" alt="">
                                <?php else: ?>
                                    <div class="lab-avatar rad-avatar"><i class="fas fa-x-ray"></i></div>
                                <?php endif; ?>
                                <div class="lab-info">
                                    <div class="lab-name">
                                        <?php echo htmlspecialchars($rad['rad_name']); ?>
                                        <?php if ((int)$rad['rad_id'] === $my_rad_id): ?><span class="you-badge">You</span><?php endif; ?>
                                    </div>
                                    <div class="lab-city"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($rad['city_name'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-2">No radiology centers registered</p>
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
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo htmlspecialchars($past_doctor['doctor_pic']); ?>"
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

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>

<script>
function handleJoinRequest(requestId, decision, button) {
    var banner = document.getElementById('joinBanner');
    var message = document.getElementById('joinMsg');
    var buttons = banner.querySelectorAll('.jb-btn');

    buttons.forEach(function (b) { b.disabled = true; });
    var originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    message.style.display = 'none';

    var fd = new FormData();
    fd.append('handle_join_request', '1');
    fd.append('request_id', requestId);
    fd.append('decision', decision);

    fetch(window.location.href, { method: 'POST', body: fd })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            message.textContent = data.message || '';
            message.style.color = data.success ? '#16a34a' : '#ef4444';
            message.style.display = 'block';

            if (!data.success) {
                buttons.forEach(function (b) { b.disabled = false; });
                button.innerHTML = originalHtml;
                return;
            }

            banner.classList.add('removing');

            if (decision === 'accept') {
                // Page reload: "You are working in this hospital" dikhane ke liye
                setTimeout(function () { window.location.reload(); }, 900);
            } else {
                // Reject ke baad dashboard par wapas
                setTimeout(function () { window.location.href = '<?= BASE_URL ?>radiology/'; }, 900);
            }
        })
        .catch(function () {
            message.textContent = 'Something went wrong. Please try again.';
            message.style.color = '#ef4444';
            message.style.display = 'block';
            buttons.forEach(function (b) { b.disabled = false; });
            button.innerHTML = originalHtml;
        });
}
</script>