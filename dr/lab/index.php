<?php
include '../config.php';

// ── LAB AUTH ───────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'lab') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id = (int)$_SESSION['user_id'];

// ── LAB DATA ───────────────────────────────────────────────
  $lab_q = "SELECT l.*, c.city_name, u.status as estatus, u.username
          FROM laboratories l
          LEFT JOIN cities c ON l.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = l.user_id
          WHERE l.user_id = $user_id AND l.approve = 1 LIMIT 1"; 
$lab_r = mysqli_query($con, $lab_q);
$lab   = mysqli_fetch_assoc($lab_r);
$lab_name=  $lab['lab_name'];
if (!$lab) { session_destroy(); header("Location: " . BASE_URL . "login"); exit(); }

$lab_id = (int)$lab['lab_id'];

// ── ADD / UPDATE TEST MASTER RECORD ─────────────────────────
if (isset($_POST['save_master_test'])) {
    header('Content-Type: application/json; charset=utf-8');

    $test_id = (int)($_POST['test_id'] ?? 0);
    $test_name = trim($_POST['test_name'] ?? '');

    if ($test_name === '') {
        echo json_encode(['success' => false, 'message' => 'Please enter a test name.']);
        exit();
    }

    // Prevent duplicate test names (case-insensitive comparison).
    $duplicate = mysqli_prepare($con, "SELECT test_id FROM tbl_tests WHERE LOWER(test_name) = LOWER(?) AND test_id != ? LIMIT 1");
    mysqli_stmt_bind_param($duplicate, 'si', $test_name, $test_id);
    mysqli_stmt_execute($duplicate);
    $duplicate_result = mysqli_stmt_get_result($duplicate);
    $has_duplicate = mysqli_num_rows($duplicate_result) > 0;
    mysqli_stmt_close($duplicate);

    if ($has_duplicate) {
        echo json_encode(['success' => false, 'message' => 'This test already exists.']);
        exit();
    }

    if ($test_id > 0) {
        $stmt = mysqli_prepare($con, "UPDATE tbl_tests SET test_name = ? WHERE test_id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $test_name, $test_id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $action = 'updated';
    } else {
        $stmt = mysqli_prepare($con, "INSERT INTO tbl_tests (test_name, status) VALUES (?, 1)");
        mysqli_stmt_bind_param($stmt, 's', $test_name);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $action = 'added';
    }

    echo json_encode([
        'success' => (bool)$ok,
        'action' => $action,
        'message' => $ok ? ('Test ' . $action . ' successfully. Reloading...') : 'Unable to save test. Please try again.'
    ]);
    exit();
}

// ── SAVE AVAILABLE LAB TESTS ─────────────────────────────────
if (isset($_POST['save_lab_tests'])) {
    header('Content-Type: application/json; charset=utf-8');

    $selected_tests = $_POST['tests'] ?? [];
    if (!is_array($selected_tests)) {
        echo json_encode(['success' => false, 'message' => 'Invalid test data.']);
        exit();
    }

    $selected = [];
    foreach ($selected_tests as $test_id => $price) {
        $test_id = (int)$test_id;
        $price = trim((string)$price);
        if ($test_id > 0 && $price !== '' && is_numeric($price) && (float)$price >= 0) {
            $selected[$test_id] = (float)$price;
        }
    }

    mysqli_begin_transaction($con);
    try {
        $existing = [];
        $existing_q = mysqli_prepare($con, "SELECT test_id FROM tbl_lab_tests WHERE lab_id = ?");
        mysqli_stmt_bind_param($existing_q, "i", $lab_id);
        mysqli_stmt_execute($existing_q);
        $existing_result = mysqli_stmt_get_result($existing_q);
        while ($row = mysqli_fetch_assoc($existing_result)) {
            $existing[(int)$row['test_id']] = true;
        }
        mysqli_stmt_close($existing_q);

        $check_test = mysqli_prepare($con, "SELECT test_id FROM tbl_tests WHERE test_id = ? AND status = 1 LIMIT 1");
        $update_test = mysqli_prepare($con, "UPDATE tbl_lab_tests SET price = ?, status = 1 WHERE lab_id = ? AND test_id = ?");
        $insert_test = mysqli_prepare($con, "INSERT INTO tbl_lab_tests (lab_id, test_id, price, status) VALUES (?, ?, ?, 1)");
        $delete_test = mysqli_prepare($con, "DELETE FROM tbl_lab_tests WHERE lab_id = ? AND test_id = ?");

        foreach ($selected as $test_id => $price) {
            mysqli_stmt_bind_param($check_test, "i", $test_id);
            mysqli_stmt_execute($check_test);
            $valid_result = mysqli_stmt_get_result($check_test);
            if (!mysqli_fetch_assoc($valid_result)) {
                continue;
            }

            if (isset($existing[$test_id])) {
                mysqli_stmt_bind_param($update_test, "dii", $price, $lab_id, $test_id);
                mysqli_stmt_execute($update_test);
            } else {
                mysqli_stmt_bind_param($insert_test, "iid", $lab_id, $test_id, $price);
                mysqli_stmt_execute($insert_test);
            }
        }

        // Unchecked tests are removed from this lab's available-test list.
        foreach ($existing as $test_id => $unused) {
            if (!array_key_exists($test_id, $selected)) {
                mysqli_stmt_bind_param($delete_test, "ii", $lab_id, $test_id);
                mysqli_stmt_execute($delete_test);
            }
        }

        mysqli_stmt_close($check_test);
        mysqli_stmt_close($update_test);
        mysqli_stmt_close($insert_test);
        mysqli_stmt_close($delete_test);
        mysqli_commit($con);

        echo json_encode(['success' => true, 'message' => 'Available tests saved successfully.']);
    } catch (Throwable $e) {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'message' => 'Unable to save tests. Please try again.']);
    }
    exit();
}

// ── NOTES ──────────────────────────────────────────────────
$notes_tbl = mysqli_query($con, "SHOW TABLES LIKE 'notes'");
if (mysqli_num_rows($notes_tbl) == 0) {
    mysqli_query($con, "CREATE TABLE notes (
        note_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

if (isset($_POST['save_note'])) {
    $note_text = mysqli_real_escape_string($con, trim($_POST['note_text'] ?? ''));
    $exists = mysqli_query($con, "SELECT note_id FROM notes WHERE user_id=$user_id LIMIT 1");
    if (mysqli_num_rows($exists) > 0) {
        $nr = mysqli_fetch_assoc($exists);
        mysqli_query($con, "UPDATE notes SET notes='$note_text', updated_at=NOW() WHERE note_id={$nr['note_id']}");
        $action = 'updated';
    } else {
        mysqli_query($con, "INSERT INTO notes (user_id, notes) VALUES ($user_id, '$note_text')");
        $action = 'added';
    }
    echo json_encode(['success' => true, 'action' => $action, 'time' => date('d M Y, h:i A')]);
    exit();
}

$note_row = null;
$note_r   = mysqli_query($con, "SELECT * FROM notes WHERE user_id=$user_id LIMIT 1");
if (mysqli_num_rows($note_r) > 0) $note_row = mysqli_fetch_assoc($note_r);

// ── STATS ──────────────────────────────────────────────────
$rating_q    = mysqli_query($con, "SELECT AVG(stars) as avg, COUNT(*) as cnt FROM feedback WHERE user_id=$user_id AND status=1");
$rating_data = mysqli_fetch_assoc($rating_q);
$avg_rating  = $rating_data['avg'] ? round($rating_data['avg'], 1) : 0;
$total_reviews = (int)$rating_data['cnt'];

// Nearby hospitals (same city)
$hosp_q    = mysqli_query($con, "SELECT COUNT(*) as c FROM hospitals h LEFT JOIN users u ON u.user_id=h.user_id WHERE h.city_id={$lab['city_id']} AND u.status=1 AND h.approve=1");
$hosp_cnt  = (int)mysqli_fetch_assoc($hosp_q)['c'];

// Nearby blood banks (same city)
$bb_q   = mysqli_query($con, "SELECT COUNT(*) as c FROM blood_bank bb LEFT JOIN users u ON u.user_id=bb.user_id WHERE bb.city_id={$lab['city_id']} AND u.status=1 AND bb.approve=1");
$bb_cnt = (int)mysqli_fetch_assoc($bb_q)['c'];

// ── NEARBY HOSPITALS ───────────────────────────────────────
$nearby_hosps = mysqli_query($con, "SELECT h.hospital_id, h.hospital_name, h.hospital_phone, c.city_name
    FROM hospitals h
    LEFT JOIN cities c ON h.city_id=c.city_id
    LEFT JOIN users u ON u.user_id=h.user_id
    WHERE h.city_id={$lab['city_id']} AND u.status=1 AND h.approve=1
    ORDER BY h.hospital_name ASC LIMIT 8");

// ── RECENT REVIEWS ─────────────────────────────────────────
$reviews_q = mysqli_query($con, "SELECT * FROM feedback WHERE user_id=$user_id AND status=1 ORDER BY created_at DESC LIMIT 5");

// ── AVAILABLE TESTS ─────────────────────────────────────────
$available_tests_q = mysqli_query($con, "
    SELECT t.test_id, t.test_name, COALESCE(lt.price, '') AS lab_price,
           CASE WHEN lt.id IS NULL THEN 0 ELSE 1 END AS is_selected
    FROM tbl_tests t
    LEFT JOIN tbl_lab_tests lt
        ON lt.test_id = t.test_id AND lt.lab_id = $lab_id
    WHERE t.status = 1
    ORDER BY t.test_name ASC
");
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/lab/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-hospital-admin.css">

<style>
.two-col-dashboard { display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start; margin-top:24px; }
.two-col-dashboard .col-stack { display:flex; flex-direction:column; gap:24px; }
.two-col-dashboard .info-card { margin-top:0 !important; }

/* Lab info row */
.lab-info-row { display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #e8f7ff; }
.lab-info-row:last-child { border-bottom:none; }
.lab-info-row i { color:#27C96A; width:18px; margin-top:3px; flex-shrink:0; }
.lab-info-row .li-label { font-size:0.72rem; font-weight:700; text-transform:uppercase; color:#94a3b8; letter-spacing:0.4px; }
.lab-info-row .li-val   { font-size:0.88rem; font-weight:600; color:#0D2B6E; }

/* Nearby hospital chip */
.hosp-chip-item {
    display:flex; align-items:center; gap:10px;
    padding:10px 14px; background:#f0f9ff;
    border:1px solid rgba(0,168,204,0.15); border-radius:12px;
    margin-bottom:8px; transition:all 0.2s;
    text-decoration:none;
}
.hosp-chip-item:last-child { margin-bottom:0; }
.hosp-chip-item:hover { background:#e8f7ff; border-color:rgba(39,201,106,0.3); transform:translateX(4px); }
.hosp-chip-item .hc-icon { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#1C3F8F,#00A8CC); display:flex; align-items:center; justify-content:center; color:white; font-size:0.9rem; flex-shrink:0; }
.hosp-chip-item .hc-name { font-weight:700; font-size:0.88rem; color:#0D2B6E; }
.hosp-chip-item .hc-city { font-size:0.75rem; color:#64748b; }

@media (max-width:992px) { .two-col-dashboard { grid-template-columns:1fr; } }

/* Test master add/edit form */
.test-master-form { margin-top:22px; padding-top:18px; border-top:1px solid #e8f7ff; }
.test-master-form .form-title { margin:0 0 12px; color:#0D2B6E; font-size:.9rem; font-weight:700; }
.test-master-fields { display:flex; gap:10px; align-items:end; flex-wrap:wrap; }
.test-master-fields .field { flex:1; min-width:180px; }
.test-master-fields label { display:block; margin-bottom:6px; color:#64748b; font-size:.76rem; font-weight:700; }
.test-master-fields input { width:100%; padding:10px 12px; border:1px solid #dbe7f0; border-radius:9px; outline:none; color:#0D2B6E; }
.test-master-fields input:focus { border-color:#27C96A; box-shadow:0 0 0 3px rgba(39,201,106,.1); }
.test-master-btn { border:0; border-radius:9px; padding:10px 16px; color:#fff; background:linear-gradient(135deg,#1C3F8F,#00A8CC); font-weight:700; cursor:pointer; white-space:nowrap; }
.test-master-cancel { border:1px solid #cbd5e1; border-radius:9px; padding:9px 13px; color:#64748b; background:#fff; font-weight:600; cursor:pointer; }
.test-edit-btn { border:0; background:transparent; color:#00A8CC; font-size:.78rem; font-weight:700; cursor:pointer; padding:4px 6px; }
.test-master-message { display:none; margin-top:10px; font-size:.83rem; font-weight:600; }

/* Available tests */
.available-tests-list { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.available-test-row {
    display:flex; align-items:center; gap:12px; padding:12px;
    border:1px solid rgba(0,168,204,0.16); border-radius:12px;
    background:#f8fcff; transition:all .2s;
}
.available-test-row:has(.available-test-check:checked) {
    border-color:rgba(39,201,106,.55); background:#f0fff8;
}
.available-test-check { width:18px; height:18px; accent-color:#27C96A; flex-shrink:0; cursor:pointer; }
.available-test-name { flex:1; min-width:0; color:#0D2B6E; font-size:.88rem; font-weight:600; }
.available-test-price { width:112px; flex-shrink:0; }
.available-test-price input {
    width:100%; padding:8px 9px; border:1px solid #dbe7f0;
    border-radius:8px; outline:none; font-size:.85rem; color:#0D2B6E;
}
.available-test-price input:focus { border-color:#27C96A; box-shadow:0 0 0 3px rgba(39,201,106,.1); }
.available-test-price input:disabled { background:#eef2f7; color:#94a3b8; }
@media (max-width:600px) {
    .available-tests-list { grid-template-columns:1fr; }
    .available-test-price { width:100px; }
}
</style>

<div class="content-wrapper">

    <!-- ── WELCOME ──────────────────────────────────────── -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>🔬 Welcome, <?= htmlspecialchars($lab_name) ?></h1>
            <p class="welcome-sub">Laboratory Dashboard — Manage your lab profile, reviews and more.</p>
            <div class="welcome-badges">
                <?php if (!empty($lab['city_name'])): ?>
                <span class="welcome-badge"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($lab['city_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($lab['lab_phone'])): ?>
                <span class="welcome-badge"><i class="fas fa-phone"></i> <?= htmlspecialchars($lab['lab_phone']) ?></span>
                <?php endif; ?>
                <?php if (!empty($lab['lab_email'])): ?>
                <span class="welcome-badge"><i class="fas fa-envelope"></i> <?= htmlspecialchars($lab['lab_email']) ?></span>
                <?php endif; ?>
                <span class="welcome-badge"><i class="fas fa-calendar-alt"></i> Since <?= !empty($lab['created_at']) ? date('M Y', strtotime($lab['created_at'])) : 'N/A' ?></span>
            </div>
        </div>
    </div>

    <!-- ── STATS ─────────────────────────────────────────── -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $avg_rating > 0 ? $avg_rating : 'N/A' ?></div>
                <div class="stat-label">Rating</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class="fas fa-comments"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $total_reviews ?></div>
                <div class="stat-label">Reviews</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-hospital"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $hosp_cnt ?></div>
                <div class="stat-label">Nearby Hospitals</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon navy"><i class="fas fa-tint"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $bb_cnt ?></div>
                <div class="stat-label">Blood Banks</div>
            </div>
        </div>
    </div>

    <!-- ── 2-COL DASHBOARD ───────────────────────────────── -->
    <div class="two-col-dashboard">

        <!-- LEFT -->
        <div class="col-stack">

            <!-- Lab Info -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-flask"></i> Lab Information</h5>
                    <a href="<?= BASE_URL ?>lab/profile" style="font-size:0.8rem; color:#27C96A; text-decoration:none; font-weight:600;"><i class="fas fa-edit me-1"></i>Edit</a>
                </div>
                <div class="info-card-body">
                    <?php
                    $info_rows = [
                        ['fas fa-flask',       'Lab Name',    $lab_name],
                        ['fas fa-map-marker-alt','Address',   $lab['lab_address'] ?? ''],
                        ['fas fa-city',        'City',        $lab['city_name'] ?? ''],
                        ['fas fa-phone',       'Phone',       $lab['lab_phone'] ?? ''],
                        ['fas fa-envelope',    'Email',       $lab['lab_email'] ?? ''],
                    ];
                    foreach ($info_rows as [$icon, $label, $val]):
                        if (empty($val)) continue; ?>
                    <div class="lab-info-row">
                        <i class="<?= $icon ?>"></i>
                        <div>
                            <div class="li-label"><?= $label ?></div>
                            <div class="li-val"><?= htmlspecialchars($val) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Notes Board -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-sticky-note"></i> Notes Board</h5>
                    <?php if ($note_row): ?>
                        <small id="noteLastSaved" style="color:#27C96A; font-size:0.78rem; font-weight:600;">
                            <i class="fas fa-clock me-1"></i>Last saved: <?= date('d M Y, h:i A', strtotime($note_row['updated_at'])) ?>
                        </small>
                    <?php else: ?>
                        <small id="noteLastSaved" style="color:#94a3b8; font-size:0.78rem;"></small>
                    <?php endif; ?>
                </div>
                <div class="info-card-body">
                    <textarea id="noteTextarea" placeholder="Write your lab notes, reminders..."
                              style="width:100%; min-height:120px; padding:12px 14px; border:2px solid rgba(39,201,106,0.2); border-radius:12px; font-size:0.9rem; color:#0D2B6E; line-height:1.7; resize:vertical; outline:none; transition:border-color 0.2s; font-family:inherit; background:#f0fff8;"
                              onfocus="this.style.borderColor='#27C96A'; this.style.background='#fff';"
                              onblur="this.style.borderColor='rgba(39,201,106,0.2)'; this.style.background='#f0fff8';"
                    ><?= htmlspecialchars($note_row['notes'] ?? '') ?></textarea>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:10px; gap:8px; flex-wrap:wrap;">
                        <button id="saveNoteBtn" onclick="saveNote()"
                                style="background:linear-gradient(135deg,#27C96A,#00C9A7); color:white; border:none; padding:9px 22px; border-radius:10px; font-weight:700; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:all 0.2s;"
                                onmouseover="this.style.background='linear-gradient(135deg,#1C3F8F,#00A8CC)'"
                                onmouseout="this.style.background='linear-gradient(135deg,#27C96A,#00C9A7)'">
                            <i class="fas fa-save"></i> Save Note
                        </button>
                        <div id="noteSavedBadge" style="display:none; align-items:center; gap:6px; background:rgba(39,201,106,0.12); border:1px solid rgba(39,201,106,0.3); border-radius:20px; padding:5px 14px; font-size:0.82rem; font-weight:700; color:#27C96A;">
                            <i class="fas fa-check-circle"></i><span id="noteSavedText">Saved!</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nearby Hospitals -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-hospital-alt"></i> Nearby Hospitals</h5>
                    <!-- <a href="<?= BASE_URL ?>lab/hospitals" style="font-size:0.8rem; color:#00A8CC; text-decoration:none; font-weight:600;">View All</a> -->
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($nearby_hosps) > 0):
                        while ($h = mysqli_fetch_assoc($nearby_hosps)): ?>
                    <div class="hosp-chip-item">
                        <div class="hc-icon"><i class="fas fa-hospital"></i></div>
                        <div>
                            <div class="hc-name"><?= htmlspecialchars($h['hospital_name']) ?></div>
                            <div class="hc-city"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($h['city_name']) ?>
                                <?php if (!empty($h['hospital_phone'])): ?>
                                &nbsp;·&nbsp;<i class="fas fa-phone me-1"></i><?= htmlspecialchars($h['hospital_phone']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; else: ?>
                        <p class="text-muted text-center py-2">No hospitals found in your city.</p>
                    <?php endif; ?>
                </div>
            </div>

            

        </div><!-- end LEFT -->

        <!-- RIGHT -->
        <div class="col-stack">

            <!-- Quick Actions -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="info-card-body">
                    <div class="quick-links">
                        <a href="<?= BASE_URL ?>lab/profile" class="quick-link-item">
                            <i class="fas fa-flask"></i><span>Lab Profile</span>
                        </a>
                        <a href="<?= BASE_URL ?>lab/feedback" class="quick-link-item">
                            <i class="fas fa-star"></i><span>Reviews</span>
                        </a>
                        <!-- <a href="<?= BASE_URL ?>lab/hospitals" class="quick-link-item">
                            <i class="fas fa-hospital"></i><span>Hospitals</span>
                        </a> -->
                        <a href="<?= BASE_URL ?>lab/blood-banks" class="quick-link-item">
                            <i class="fas fa-tint"></i><span>Blood Banks</span>
                        </a>
                        <a href="<?= BASE_URL ?>logout" class="quick-link-item" style="color:#ef4444;">
                            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Available Tests -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-vials"></i> Available Tests</h5>
                    <small style="font-size:.75rem;color:#64748b;">Select tests and set prices</small>
                </div>
                <div class="info-card-body">
                    <?php if ($available_tests_q && mysqli_num_rows($available_tests_q) > 0): ?>
                    <form id="availableTestsForm">
                        <div class="available-tests-list">
                            <?php while ($test = mysqli_fetch_assoc($available_tests_q)): ?>
                            <div class="available-test-row">
                                <input
                                    type="checkbox"
                                    class="available-test-check"
                                    id="test_<?= (int)$test['test_id'] ?>"
                                    name="tests[<?= (int)$test['test_id'] ?>]"
                                    value="<?= htmlspecialchars((string)$test['lab_price']) ?>"
                                    <?= (int)$test['is_selected'] === 1 ? 'checked' : '' ?>
                                >
                                <label class="available-test-name" for="test_<?= (int)$test['test_id'] ?>">
                                    <?= htmlspecialchars($test['test_name']) ?>
                                </label>
                                <button type="button" class="test-edit-btn"
                                    data-test-id="<?= (int)$test['test_id'] ?>"
                                    data-test-name="<?= htmlspecialchars($test['test_name'], ENT_QUOTES) ?>"
                                    onclick="editMasterTest(this)">Edit</button>
                                <div class="available-test-price">
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        name="prices[<?= (int)$test['test_id'] ?>]"
                                        value="<?= htmlspecialchars((string)$test['lab_price']) ?>"
                                        placeholder="Price"
                                        aria-label="Price for <?= htmlspecialchars($test['test_name']) ?>"
                                        <?= (int)$test['is_selected'] === 1 ? '' : 'disabled' ?>
                                    >
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-top:16px;">
                            <small style="color:#64748b;">Only checked tests will be available to patients.</small>
                            <button type="submit" id="saveAvailableTestsBtn"
                                style="background:linear-gradient(135deg,#27C96A,#00C9A7);color:#fff;border:0;padding:10px 20px;border-radius:10px;font-weight:700;cursor:pointer;">
                                <i class="fas fa-save"></i> Save Tests
                            </button>
                        </div>
                        <div id="availableTestsMessage" role="status" style="display:none;margin-top:12px;font-size:.85rem;font-weight:600;"></div>
                    </form>
                    <?php else: ?>
                        <p class="text-muted text-center py-2 mb-0">No tests found. Please add tests to the tests master list first.</p>
                    <?php endif; ?>

                    <!-- Add / Update Test Master -->
                    <form id="masterTestForm" class="test-master-form">
                        <h6 class="form-title"><i class="fas fa-plus-circle me-1"></i> Add New Test / Update Test</h6>
                        <input type="hidden" name="test_id" id="masterTestId" value="0">
                        <div class="test-master-fields">
                            <div class="field">
                                <label for="masterTestName">Test Name</label>
                                <input type="text" id="masterTestName" name="test_name" maxlength="255" placeholder="e.g. CBC, LFT, MRI" required>
                            </div>
                            <button type="submit" class="test-master-btn" id="saveMasterTestBtn"><i class="fas fa-plus"></i> Add Test</button>
                            <button type="button" class="test-master-cancel" id="cancelMasterTestEdit" style="display:none;" onclick="resetMasterTestForm()">Cancel</button>
                        </div>
                        <div id="masterTestMessage" class="test-master-message" role="status"></div>
                        <small style="display:block;color:#64748b;margin-top:9px;">New tests will be added to the common test list. Use the Edit button beside a test to change its name.</small>
                    </form>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Recent Reviews</h5>
                    <a href="<?= BASE_URL ?>lab/feedback" style="font-size:0.8rem; color:#27C96A; text-decoration:none; font-weight:600;">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($reviews_q) > 0):
                        while ($rev = mysqli_fetch_assoc($reviews_q)): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="review-name"><?= htmlspecialchars($rev['commenter_name']) ?></span>
                            <span class="review-stars">
                                <?php for ($i=1;$i<=5;$i++): ?>
                                    <i class="fas fa-star <?= $i<=$rev['stars']?'text-warning':'text-muted' ?>" style="font-size:0.75rem;"></i>
                                <?php endfor; ?>
                            </span>
                        </div>
                        <?php if (!empty($rev['comment'])): ?>
                            <p class="review-comment"><?= htmlspecialchars(mb_strimwidth($rev['comment'],0,80,'...')) ?></p>
                        <?php endif; ?>
                        <div class="review-date"><?= date('d M Y', strtotime($rev['created_at'])) ?></div>
                    </div>
                    <?php endwhile; else: ?>
                        <p class="text-muted text-center py-2">No reviews yet.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- end RIGHT -->
    </div>

</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>

<script>
function saveNote() {
    var text = document.getElementById('noteTextarea').value.trim();
    var btn = document.getElementById('saveNoteBtn');
    var badge = document.getElementById('noteSavedBadge');
    var badgeTxt = document.getElementById('noteSavedText');
    var lastSaved = document.getElementById('noteLastSaved');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    badge.style.display = 'none';

    var fd = new FormData();
    fd.append('save_note', '1');
    fd.append('note_text', text);

    fetch('<?= BASE_URL ?>lab/index.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Note';
            if (d.success) {
                badgeTxt.textContent = d.action === 'updated' ? 'Updated!' : 'Saved!';
                badge.style.display = 'flex';
                lastSaved.innerHTML = '<i class="fas fa-clock me-1"></i>Last saved: ' + d.time;
                lastSaved.style.color = '#27C96A';
                setTimeout(() => { badge.style.display = 'none'; }, 3000);
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Note';
        });
}
document.getElementById('noteTextarea').addEventListener('keydown', e => {
    if ((e.ctrlKey||e.metaKey) && e.key==='s') { e.preventDefault(); saveNote(); }
});

document.querySelectorAll('.available-test-check').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        var priceInput = this.closest('.available-test-row').querySelector('.available-test-price input');
        priceInput.disabled = !this.checked;
        if (this.checked) priceInput.focus();
    });
});

var availableTestsForm = document.getElementById('availableTestsForm');
if (availableTestsForm) {
    availableTestsForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var btn = document.getElementById('saveAvailableTestsBtn');
        var message = document.getElementById('availableTestsMessage');
        var formData = new FormData();
        formData.append('save_lab_tests', '1');

        var invalidPrice = false;
        this.querySelectorAll('.available-test-check:checked').forEach(function (checkbox) {
            var priceInput = checkbox.closest('.available-test-row').querySelector('.available-test-price input');
            var testId = checkbox.id.replace('test_', '');
            if (priceInput.value.trim() === '' || Number(priceInput.value) < 0) {
                invalidPrice = true;
                priceInput.focus();
                return;
            }
            formData.append('tests[' + testId + ']', priceInput.value);
        });

        if (invalidPrice) {
            message.textContent = 'Please enter a valid price for every selected test.';
            message.style.color = '#ef4444';
            message.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        message.style.display = 'none';

        fetch('<?= BASE_URL ?>lab/index.php', { method: 'POST', body: formData })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                message.textContent = data.message || (data.success ? 'Saved successfully.' : 'Unable to save tests.');
                message.style.color = data.success ? '#16a34a' : '#ef4444';
                message.style.display = 'block';
            })
            .catch(function () {
                message.textContent = 'Something went wrong. Please try again.';
                message.style.color = '#ef4444';
                message.style.display = 'block';
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save Tests';
            });
    });
}

function editMasterTest(button) {
    document.getElementById('masterTestId').value = button.dataset.testId;
    document.getElementById('masterTestName').value = button.dataset.testName;
    document.getElementById('saveMasterTestBtn').innerHTML = '<i class="fas fa-save"></i> Update Test';
    document.getElementById('cancelMasterTestEdit').style.display = 'inline-block';
    document.getElementById('masterTestName').focus();
    document.getElementById('masterTestForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function resetMasterTestForm() {
    document.getElementById('masterTestForm').reset();
    document.getElementById('masterTestId').value = '0';
    document.getElementById('saveMasterTestBtn').innerHTML = '<i class="fas fa-plus"></i> Add Test';
    document.getElementById('cancelMasterTestEdit').style.display = 'none';
    document.getElementById('masterTestMessage').style.display = 'none';
}

var masterTestForm = document.getElementById('masterTestForm');
if (masterTestForm) {
    masterTestForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('saveMasterTestBtn');
        var message = document.getElementById('masterTestMessage');
        var formData = new FormData(this);
        formData.append('save_master_test', '1');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        message.style.display = 'none';

        fetch('<?= BASE_URL ?>lab/index.php', { method: 'POST', body: formData })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                message.textContent = data.message || 'Unable to save test.';
                message.style.color = data.success ? '#16a34a' : '#ef4444';
                message.style.display = 'block';
                if (data.success) {
                    setTimeout(function () { window.location.reload(); }, 900);
                }
            })
            .catch(function () {
                message.textContent = 'Something went wrong. Please try again.';
                message.style.color = '#ef4444';
                message.style.display = 'block';
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = Number(document.getElementById('masterTestId').value) > 0
                    ? '<i class="fas fa-save"></i> Update Test'
                    : '<i class="fas fa-plus"></i> Add Test';
            });
    });
}

</script>
