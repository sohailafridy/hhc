<?php
include '../config.php';

// ── DOCTOR AUTH ────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'doctor') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id    = (int)$_SESSION['user_id'];

// ── DOCTOR DATA ────────────────────────────────────────────
$doc_q = "SELECT d.*, dct.type as specialization, dct.treatment as mahre_amraz,
                 c.city_name, u.status as estatus, u.username
          FROM doctors d
          LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = d.user_id
          WHERE d.user_id = $user_id AND d.approve = 1
          LIMIT 1";
$doc_r    = mysqli_query($con, $doc_q);
$doctor   = mysqli_fetch_assoc($doc_r);

if (!$doctor) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$doctor_id = (int)$doctor['doctor_id'];

// ── NOTES: auto-create table + AJAX save ───────────────────
$notes_tbl = mysqli_query($con, "SHOW TABLES LIKE 'notes'");
if (mysqli_num_rows($notes_tbl) == 0) {
    mysqli_query($con, "CREATE TABLE notes (
        note_id    INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id    INT(11) NOT NULL,
        notes      TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

if (isset($_POST['save_note'])) {
    $note_text = mysqli_real_escape_string($con, trim($_POST['note_text'] ?? ''));
    $exists    = mysqli_query($con, "SELECT note_id FROM notes WHERE user_id = $user_id LIMIT 1");
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

$note_row    = null;
$note_result = mysqli_query($con, "SELECT * FROM notes WHERE user_id = $user_id LIMIT 1");
if (mysqli_num_rows($note_result) > 0) { $note_row = mysqli_fetch_assoc($note_result); }

// ── STATS ──────────────────────────────────────────────────
// Total hospitals where doctor is active
$total_hospitals_q = mysqli_query($con, "SELECT COUNT(*) as c FROM doctor_in_hospital WHERE doctor_id=$doctor_id AND inactive=0");
$total_hospitals   = mysqli_fetch_assoc($total_hospitals_q)['c'];

// Total clinical records
$total_clinical_q  = mysqli_query($con, "SELECT COUNT(*) as c FROM clinical_info ci
    INNER JOIN doctor_in_hospital dih ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id
    WHERE dih.doctor_id = $doctor_id");
$total_clinical    = mysqli_fetch_assoc($total_clinical_q)['c'];

// Avg rating & review count
$rating_q    = mysqli_query($con, "SELECT AVG(stars) as avg, COUNT(*) as cnt FROM feedback WHERE user_id=$user_id AND status=1");
$rating_data = mysqli_fetch_assoc($rating_q);
$avg_rating  = $rating_data['avg'] ? round($rating_data['avg'], 1) : 0;
$total_reviews = (int)$rating_data['cnt'];

// Experience
$experience  = (int)($doctor['experience_years'] ?? 0);

// ── HOSPITALS & CLINICAL INFO ──────────────────────────────
$hospitals_q = "SELECT dih.doctor_in_hosp_id, dih.inactive,
                       h.hospital_id, h.hospital_name, h.hospital_phone, h.hospital_address,
                       c.city_name,
                       ci.morning_opening_time, ci.morning_closing_time,
                       ci.evening_opening_time, ci.evening_closing_time,
                       ci.days, ci.off_days, ci.contact, ci.season, ci.shift, ci.detail
                FROM doctor_in_hospital dih
                LEFT JOIN hospitals h ON dih.hospital_id = h.hospital_id
                LEFT JOIN cities c ON h.city_id = c.city_id
                LEFT JOIN clinical_info ci ON ci.doctor_in_hosp_id = dih.doctor_in_hosp_id
                WHERE dih.doctor_id = $doctor_id
                ORDER BY dih.inactive ASC, h.hospital_name ASC";
$hospitals_r = mysqli_query($con, $hospitals_q);

// ── RECENT REVIEWS ─────────────────────────────────────────
$reviews_q = mysqli_query($con, "SELECT * FROM feedback WHERE user_id=$user_id AND status=1 ORDER BY created_at DESC LIMIT 5");
?>

<?php include BASE_PATH . '/admin/inc/header.php'; ?>
<?php include BASE_PATH . '/admin/inc/top.php'; ?>
<?php include BASE_PATH . '/doctor/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-hospital-admin.css">

<style>
:root {
    --sn-navy:   #1C3F8F;
    --sn-teal:   #00A8CC;
    --sn-green:  #27C96A;
    --sn-tgreen: #00C9A7;
    --sn-dark:   #0D2B6E;
}

.two-col-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    align-items: start;
    margin-top: 24px;
}
.two-col-dashboard .col-stack { display: flex; flex-direction: column; gap: 24px; }
.two-col-dashboard .info-card { margin-top: 0 !important; }

/* Hospital assignment card */
.hosp-assign-card {
    background: #f0f9ff;
    border: 1px solid rgba(0,168,204,0.2);
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 14px;
    transition: all 0.25s;
    position: relative;
    overflow: hidden;
}
.hosp-assign-card:last-child { margin-bottom: 0; }
.hosp-assign-card::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 4px;
    background: var(--sn-teal);
    border-radius: 4px 0 0 4px;
}
.hosp-assign-card.inactive::before { background: #cbd5e1; }
.hosp-assign-card.inactive { background: #f8fafc; opacity: 0.75; }
.hosp-assign-card:hover { box-shadow: 0 6px 20px rgba(28,63,143,0.12); transform: translateY(-2px); }

.hosp-assign-name {
    font-weight: 700; font-size: 1rem; color: var(--sn-dark);
    display: flex; align-items: center; gap: 8px; margin-bottom: 6px;
}
.hosp-assign-name i { color: var(--sn-teal); }

.hosp-assign-meta {
    display: flex; flex-wrap: wrap; gap: 10px;
    margin-bottom: 10px; font-size: 0.8rem; color: #64748b;
}
.hosp-assign-meta span { display: flex; align-items: center; gap: 5px; }
.hosp-assign-meta i { color: var(--sn-teal); width: 14px; }

/* Timing pills */
.timing-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 10px;
}
.timing-pill {
    background: white;
    border: 1px solid rgba(0,168,204,0.15);
    border-radius: 10px;
    padding: 9px 12px;
    font-size: 0.78rem;
    display: flex; align-items: center; gap: 8px;
}
.timing-pill i { font-size: 0.85rem; flex-shrink: 0; }
.timing-pill .t-label { color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 0.68rem; display: block; }
.timing-pill .t-val   { color: var(--sn-dark); font-weight: 700; font-size: 0.8rem; display: block; margin-top: 1px; }
.timing-pill.morning  { border-left: 3px solid #f59e0b; }
.timing-pill.evening  { border-left: 3px solid var(--sn-navy); }
.timing-pill.days     { border-left: 3px solid var(--sn-green); }
.timing-pill.offdays  { border-left: 3px solid #ef4444; }
.timing-pill.season   { border-left: 3px solid var(--sn-tgreen); }
.timing-pill.contact  { border-left: 3px solid var(--sn-teal); }
.timing-pill.fullwidth { grid-column: 1 / -1; }

.no-clinical {
    font-size: 0.8rem; color: #94a3b8; font-style: italic;
    padding: 6px 0; text-align: center;
}

.badge-inactive {
    background: #f1f5f9; color: #94a3b8;
    border-radius: 20px; padding: 2px 10px;
    font-size: 0.68rem; font-weight: 700;
}
.badge-active {
    background: rgba(39,201,106,0.12); color: var(--sn-green);
    border-radius: 20px; padding: 2px 10px;
    font-size: 0.68rem; font-weight: 700;
}

@media (max-width: 992px) {
    .two-col-dashboard { grid-template-columns: 1fr; }
    .timing-grid       { grid-template-columns: 1fr; }
    .welcome-section{width: 100%;}
}
</style>

<div class="content-wrapper">

    <!-- ── WELCOME ──────────────────────────────────────── -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>👋 Welcome, Dr. <?= htmlspecialchars($doctor['doctor_name']) ?></h1>
            <p class="welcome-sub"><?= htmlspecialchars($doctor['specialization'] ?? 'General Practitioner') ?><?= !empty($doctor['mahre_amraz']) ? ' — ' . htmlspecialchars($doctor['mahre_amraz']) : '' ?></p>
            <div class="welcome-badges">
                <?php if (!empty($doctor['city_name'])): ?>
                <span class="welcome-badge"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($doctor['city_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($doctor['doctor_phone'])): ?>
                <span class="welcome-badge"><i class="fas fa-phone"></i> <?= htmlspecialchars($doctor['doctor_phone']) ?></span>
                <?php endif; ?>
                <?php if (!empty($doctor['pmdc_no'])): ?>
                <span class="welcome-badge"><i class="fas fa-id-card"></i> PMDC: <?= htmlspecialchars($doctor['pmdc_no']) ?></span>
                <?php endif; ?>
                <?php if (!empty($doctor['short_detail'])): ?>
                <span class="welcome-badge"><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($doctor['short_detail']) ?></span>
                <?php endif; ?>
                <span class="welcome-badge"><i class="fas fa-calendar-alt"></i> Since <?= date('M Y', strtotime($doctor['created_at'])) ?></span>
            </div>
        </div>
    </div>

    <!-- ── STATS ─────────────────────────────────────────── -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-hospital"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $total_hospitals ?></div>
                <div class="stat-label">Hospitals</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon teal"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $total_clinical ?></div>
                <div class="stat-label">Clinical Records</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-briefcase"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $experience ?></div>
                <div class="stat-label">Years Exp.</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon navy"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $avg_rating > 0 ? $avg_rating : 'N/A' ?></div>
                <div class="stat-label">Rating (<?= $total_reviews ?>)</div>
            </div>
        </div>
    </div>

    <!-- ── 2-COLUMN DASHBOARD ────────────────────────────── -->
    <div class="two-col-dashboard">

        <!-- ══ LEFT ══ -->
        <div class="col-stack">

            <!-- Hospitals & Timings -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-hospital-alt"></i> My Hospitals & Timings</h5>
                    <span style="background:rgba(0,168,204,0.1); color:#00A8CC; border-radius:20px; padding:3px 12px; font-size:0.78rem; font-weight:700;">
                        <?= $total_hospitals ?> Active
                    </span>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($hospitals_r) > 0):
                        while ($h = mysqli_fetch_assoc($hospitals_r)):
                            $is_inactive = $h['inactive'] == 1;
                            $has_morning = !empty($h['morning_opening_time']) && !empty($h['morning_closing_time']);
                            $has_evening = !empty($h['evening_opening_time']) && !empty($h['evening_closing_time']);
                    ?>
                    <div class="hosp-assign-card <?= $is_inactive ? 'inactive' : '' ?>">
                        <div class="hosp-assign-name">
                            <i class="fas fa-hospital"></i>
                            <?= htmlspecialchars($h['hospital_name'] ?? 'Personal Clinic') ?>
                            <span class="<?= $is_inactive ? 'badge-inactive' : 'badge-active' ?>">
                                <?= $is_inactive ? 'Inactive' : 'Active' ?>
                            </span>
                        </div>

                        <div class="hosp-assign-meta">
                            <?php if (!empty($h['city_name'])): ?>
                            <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($h['city_name']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($h['hospital_phone'])): ?>
                            <span><i class="fas fa-phone"></i><?= htmlspecialchars($h['hospital_phone']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($h['hospital_address'])): ?>
                            <span><i class="fas fa-map-pin"></i><?= htmlspecialchars(mb_strimwidth($h['hospital_address'],0,50,'...')) ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($has_morning || $has_evening || !empty($h['days'])): ?>
                        <div class="timing-grid">
                            <?php if ($has_morning): ?>
                            <div class="timing-pill morning">
                                <i class="fas fa-sun" style="color:#f59e0b;"></i>
                                <div>
                                    <span class="t-label">Morning</span>
                                    <span class="t-val"><?= date('h:i A', strtotime($h['morning_opening_time'])) ?> – <?= date('h:i A', strtotime($h['morning_closing_time'])) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($has_evening): ?>
                            <div class="timing-pill evening">
                                <i class="fas fa-moon" style="color:#1C3F8F;"></i>
                                <div>
                                    <span class="t-label">Evening</span>
                                    <span class="t-val"><?= date('h:i A', strtotime($h['evening_opening_time'])) ?> – <?= date('h:i A', strtotime($h['evening_closing_time'])) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($h['days'])): ?>
                            <div class="timing-pill days">
                                <i class="fas fa-calendar-check" style="color:#27C96A;"></i>
                                <div>
                                    <span class="t-label">Working Days</span>
                                    <span class="t-val"><?= htmlspecialchars($h['days']) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($h['off_days'])): ?>
                            <div class="timing-pill offdays">
                                <i class="fas fa-calendar-times" style="color:#ef4444;"></i>
                                <div>
                                    <span class="t-label">Off Days</span>
                                    <span class="t-val"><?= htmlspecialchars($h['off_days']) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($h['season'])): ?>
                            <div class="timing-pill season">
                                <i class="fas fa-leaf" style="color:#00C9A7;"></i>
                                <div>
                                    <span class="t-label">Season</span>
                                    <span class="t-val"><?= htmlspecialchars($h['season']) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($h['contact'])): ?>
                            <div class="timing-pill contact">
                                <i class="fas fa-phone" style="color:#00A8CC;"></i>
                                <div>
                                    <span class="t-label">Clinic Contact</span>
                                    <span class="t-val"><?= htmlspecialchars($h['contact']) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($h['detail'])): ?>
                            <div class="timing-pill fullwidth" style="border-left:3px solid #94a3b8;">
                                <i class="fas fa-info-circle" style="color:#94a3b8;"></i>
                                <div>
                                    <span class="t-label">Note</span>
                                    <span class="t-val"><?= htmlspecialchars($h['detail']) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <p class="no-clinical"><i class="fas fa-clock me-1"></i> No timing info added yet</p>
                        <?php endif; ?>
                    </div>
                    <?php endwhile;
                    else: ?>
                        <p class="text-muted text-center py-3"><i class="fas fa-hospital me-2"></i>Not assigned to any hospital yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes Board -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-sticky-note"></i> Notes Board</h5>
                    <?php if ($note_row): ?>
                        <small id="noteLastSaved" style="color:#00A8CC; font-size:0.78rem; font-weight:600;">
                            <i class="fas fa-clock me-1"></i>Last saved: <?= date('d M Y, h:i A', strtotime($note_row['updated_at'])) ?>
                        </small>
                    <?php else: ?>
                        <small id="noteLastSaved" style="color:#94a3b8; font-size:0.78rem;"></small>
                    <?php endif; ?>
                </div>
                <div class="info-card-body">
                    <textarea id="noteTextarea"
                              placeholder="Write your notes, reminders, or patient instructions..."
                              style="width:100%; min-height:130px; padding:14px 16px;
                                     border:2px solid rgba(0,168,204,0.2); border-radius:12px;
                                     font-size:0.92rem; color:#0D2B6E; line-height:1.7;
                                     resize:vertical; outline:none; transition:border-color 0.2s;
                                     font-family:inherit; background:#f0f9ff;"
                              onfocus="this.style.borderColor='#00A8CC'; this.style.background='#fff';"
                              onblur="this.style.borderColor='rgba(0,168,204,0.2)'; this.style.background='#f0f9ff';"
                    ><?= htmlspecialchars($note_row['notes'] ?? '') ?></textarea>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-top:10px; gap:8px; flex-wrap:wrap;">
                        <button id="saveNoteBtn" onclick="saveNote()"
                                style="background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:white; border:none; padding:9px 22px; border-radius:10px; font-weight:700; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:all 0.2s;"
                                onmouseover="this.style.background='linear-gradient(135deg,#27C96A,#00C9A7)'"
                                onmouseout="this.style.background='linear-gradient(135deg,#1C3F8F,#00A8CC)'">
                            <i class="fas fa-save"></i> Save Note
                        </button>
                        <div id="noteSavedBadge" style="display:none; align-items:center; gap:6px; background:rgba(39,201,106,0.12); border:1px solid rgba(39,201,106,0.3); border-radius:20px; padding:5px 14px; font-size:0.82rem; font-weight:700; color:#27C96A;">
                            <i class="fas fa-check-circle"></i><span id="noteSavedText">Saved!</span>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- end LEFT -->

        <!-- ══ RIGHT ══ -->
        <div class="col-stack">

            <!-- Quick Actions -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="info-card-body">
                    <div class="quick-links">
                        <a href="<?= BASE_URL ?>doctor/profile" class="quick-link-item">
                            <i class="fas fa-user-md"></i><span>My Profile</span>
                        </a>
                        <a href="<?= BASE_URL ?>doctor/feedback" class="quick-link-item">
                            <i class="fas fa-star"></i><span>My Reviews</span>
                        </a>
                        <a href="<?= BASE_URL ?>doctor/labs" class="quick-link-item">
                            <i class="fas fa-flask"></i><span>Laboratories</span>
                        </a>
                        <a href="<?= BASE_URL ?>doctor/blood-banks" class="quick-link-item">
                            <i class="fas fa-tint"></i><span>Blood Banks</span>
                        </a>
                        <a href="<?= BASE_URL ?>logout" class="quick-link-item" style="color:#ef4444;">
                            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Doctor Profile Summary -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-id-badge"></i> Profile Summary</h5>
                    <a href="<?= BASE_URL ?>doctor/profile" style="font-size:0.8rem; color:#00A8CC; text-decoration:none; font-weight:600;">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
                <div class="info-card-body" style="padding:16px 20px;">
                    <?php
                    $profile_fields = [
                        ['fas fa-stethoscope', 'Specialization', $doctor['specialization'] ?? 'General'],
                        ['fas fa-star',        'ماہرِ امراض',    $doctor['mahre_amraz'] ?? '—'],
                        ['fas fa-graduation-cap','Qualification', $doctor['short_detail'] ?? '—'],
                        ['fas fa-id-card',     'PMDC No.',       $doctor['pmdc_no'] ?? '—'],
                        ['fas fa-briefcase',   'Experience',     ($doctor['experience_years'] ?? 0) . ' Years'],
                        ['fas fa-venus-mars',  'Gender',         $doctor['gender'] ?? '—'],
                        ['fas fa-map-marker-alt','City',         $doctor['city_name'] ?? '—'],
                        ['fas fa-phone',       'Phone',          $doctor['doctor_phone'] ?? '—'],
                        ['fas fa-envelope',    'Email',          $doctor['doctor_email'] ?? '—'],
                    ];
                    foreach ($profile_fields as [$icon, $label, $val]):
                        if (empty($val) || $val === '—') continue;
                    ?>
                    <div style="display:flex; align-items:flex-start; gap:10px; padding:8px 0; border-bottom:1px solid #e8f7ff;">
                        <i class="<?= $icon ?>" style="color:#00A8CC; width:16px; margin-top:3px; flex-shrink:0;"></i>
                        <div>
                            <div style="font-size:0.72rem; font-weight:700; text-transform:uppercase; color:#94a3b8; letter-spacing:0.4px;"><?= $label ?></div>
                            <div style="font-size:0.88rem; font-weight:600; color:#0D2B6E;"><?= htmlspecialchars($val) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!empty($doctor['notes'])): ?>
                    <div style="margin-top:12px; background:#F0FFF8; border-left:3px solid #27C96A; border-radius:0 8px 8px 0; padding:10px 14px; font-size:0.85rem; color:#0D2B6E;">
                        <strong style="color:#27C96A;"><i class="fas fa-sticky-note me-1"></i>Notes:</strong><br>
                        <?= nl2br(htmlspecialchars($doctor['notes'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Recent Reviews</h5>
                    <a href="<?= BASE_URL ?>doctor/feedback" style="font-size:0.8rem; color:#00A8CC; text-decoration:none; font-weight:600;">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($reviews_q) > 0):
                        while ($rev = mysqli_fetch_assoc($reviews_q)): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="review-name"><?= htmlspecialchars($rev['commenter_name']) ?></span>
                                <span class="review-stars">
                                    <?php for ($i=1;$i<=5;$i++): ?>
                                        <i class="fas fa-star <?= $i<=$rev['stars'] ? 'text-warning' : 'text-muted' ?>" style="font-size:0.75rem;"></i>
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

    </div><!-- end 2-col -->

</div><!-- content-wrapper -->

<?php include BASE_PATH . '/admin/inc/footer.php'; ?>

<script>
function saveNote() {
    var text     = document.getElementById('noteTextarea').value.trim();
    var btn      = document.getElementById('saveNoteBtn');
    var badge    = document.getElementById('noteSavedBadge');
    var badgeTxt = document.getElementById('noteSavedText');
    var lastSaved= document.getElementById('noteLastSaved');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    badge.style.display = 'none';

    var fd = new FormData();
    fd.append('save_note',  '1');
    fd.append('note_text',  text);

    fetch('<?= BASE_URL ?>doctor/index.php', { method:'POST', body:fd })
        .then(function(r){ return r.json(); })
        .then(function(d){
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Note';
            if (d.success) {
                badgeTxt.textContent = d.action === 'updated' ? 'Updated!' : 'Saved!';
                badge.style.display  = 'flex';
                lastSaved.innerHTML  = '<i class="fas fa-clock me-1"></i>Last saved: ' + d.time;
                lastSaved.style.color = '#00A8CC';
                setTimeout(function(){ badge.style.display='none'; }, 3000);
            }
        })
        .catch(function(){
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Note';
        });
}

document.getElementById('noteTextarea').addEventListener('keydown', function(e){
    if ((e.ctrlKey||e.metaKey) && e.key==='s'){ e.preventDefault(); saveNote(); }
});
</script>
