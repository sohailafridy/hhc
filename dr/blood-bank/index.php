<?php
include '../config.php';

// ── BLOOD BANK AUTH ────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'blood_bank') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id = (int)$_SESSION['user_id'];

// ── BLOOD BANK DATA ────────────────────────────────────────
$bb_q = "SELECT bb.*, c.city_name, u.status as estatus, u.username
         FROM blood_bank bb
         LEFT JOIN cities c ON bb.city_id = c.city_id
         LEFT JOIN users u ON u.user_id = bb.user_id
         WHERE bb.user_id = $user_id AND bb.approve = 1 LIMIT 1";
$bb_r = mysqli_query($con, $bb_q);
$bb   = mysqli_fetch_assoc($bb_r);

if (!$bb) { session_destroy(); header("Location: " . BASE_URL . "login"); exit(); }

$bb_id = (int)$bb['bb_id'];

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
$hosp_cnt = (int)mysqli_fetch_assoc(mysqli_query($con,
    "SELECT COUNT(*) as c FROM hospitals h LEFT JOIN users u ON u.user_id=h.user_id
     WHERE h.city_id={$bb['city_id']} AND u.status=1 AND h.approve=1"))['c'];

// Nearby labs (same city)
$lab_cnt = (int)mysqli_fetch_assoc(mysqli_query($con,
    "SELECT COUNT(*) as c FROM laboratories l LEFT JOIN users u ON u.user_id=l.user_id
     WHERE l.city_id={$bb['city_id']} AND u.status=1 AND l.approve=1"))['c'];

// ── NEARBY HOSPITALS ───────────────────────────────────────
$nearby_hosps = mysqli_query($con,
    "SELECT h.hospital_id, h.hospital_name, h.hospital_phone, c.city_name
     FROM hospitals h
     LEFT JOIN cities c ON h.city_id=c.city_id
     LEFT JOIN users u ON u.user_id=h.user_id
     WHERE h.city_id={$bb['city_id']} AND u.status=1 AND h.approve=1
     ORDER BY h.hospital_name ASC LIMIT 8");

// ── NEARBY LABS ────────────────────────────────────────────
$nearby_labs = mysqli_query($con,
    "SELECT l.lab_id, l.lab_name, l.lab_phone, c.city_name
     FROM laboratories l
     LEFT JOIN cities c ON l.city_id=c.city_id
     LEFT JOIN users u ON u.user_id=l.user_id
     WHERE l.city_id={$bb['city_id']} AND u.status=1 AND l.approve=1
     ORDER BY l.lab_name ASC LIMIT 6");

// ── RECENT REVIEWS ─────────────────────────────────────────
$reviews_q = mysqli_query($con,
    "SELECT * FROM feedback WHERE user_id=$user_id AND status=1
     ORDER BY created_at DESC LIMIT 5");
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/blood-bank/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-hospital-admin.css">

<style>
.two-col-dashboard { display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start; margin-top:24px; }
.two-col-dashboard .col-stack { display:flex; flex-direction:column; gap:24px; }
.two-col-dashboard .info-card { margin-top:0 !important; }

/* Info row */
.bb-info-row { display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #e8f7ff; }
.bb-info-row:last-child { border-bottom:none; }
.bb-info-row i { color:#00A8CC; width:18px; margin-top:3px; flex-shrink:0; }
.bb-info-row .li-label { font-size:0.72rem; font-weight:700; text-transform:uppercase; color:#94a3b8; letter-spacing:0.4px; }
.bb-info-row .li-val   { font-size:0.88rem; font-weight:600; color:#0D2B6E; }

/* Nearby chip */
.nearby-item {
    display:flex; align-items:center; gap:10px;
    padding:10px 14px; background:#f0f9ff;
    border:1px solid rgba(0,168,204,0.15); border-radius:12px;
    margin-bottom:8px; transition:all 0.2s;
}
.nearby-item:last-child { margin-bottom:0; }
.nearby-item:hover { background:#e8f7ff; border-color:rgba(0,168,204,0.3); transform:translateX(4px); }
.nearby-item .ni-icon { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#1C3F8F,#00A8CC); display:flex; align-items:center; justify-content:center; color:white; font-size:0.9rem; flex-shrink:0; }
.nearby-item .ni-name { font-weight:700; font-size:0.88rem; color:#0D2B6E; }
.nearby-item .ni-sub  { font-size:0.75rem; color:#64748b; }

@media (max-width:992px) { .two-col-dashboard { grid-template-columns:1fr; } }
</style>

<div class="content-wrapper">

    <!-- WELCOME -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>🩸 Welcome, <?= htmlspecialchars($bb['bb_name']) ?></h1>
            <p class="welcome-sub">Blood Bank Dashboard — Manage your profile, reviews and nearby facilities.</p>
            <div class="welcome-badges">
                <?php if (!empty($bb['city_name'])): ?>
                <span class="welcome-badge"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($bb['city_name']) ?></span>
                <?php endif; ?>
                <?php if (!empty($bb['bb_contact'])): ?>
                <span class="welcome-badge"><i class="fas fa-phone"></i> <?= htmlspecialchars($bb['bb_contact']) ?></span>
                <?php endif; ?>
                <?php if (!empty($bb['created_at'])): ?>
                <span class="welcome-badge"><i class="fas fa-calendar-alt"></i> Since <?= date('M Y', strtotime($bb['created_at'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon teal"><i class="fas fa-star"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $avg_rating > 0 ? $avg_rating : 'N/A' ?></div>
                <div class="stat-label">Rating</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-comments"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $total_reviews ?></div>
                <div class="stat-label">Reviews</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-hospital"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $hosp_cnt ?></div>
                <div class="stat-label">Nearby Hospitals</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon navy"><i class="fas fa-flask"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $lab_cnt ?></div>
                <div class="stat-label">Nearby Labs</div>
            </div>
        </div>
    </div>

    <!-- 2-COL DASHBOARD -->
    <div class="two-col-dashboard">

        <!-- LEFT -->
        <div class="col-stack">

            <!-- BB Info -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-tint"></i> Blood Bank Information</h5>
                    <a href="<?= BASE_URL ?>blood-bank/profile" style="font-size:0.8rem; color:#00A8CC; text-decoration:none; font-weight:600;"><i class="fas fa-edit me-1"></i>Edit</a>
                </div>
                <div class="info-card-body">
                    <?php
                    $rows = [
                        ['fas fa-tint',         'Name',    $bb['bb_name']],
                        ['fas fa-map-marker-alt','Address', $bb['bb_address'] ?? ''],
                        ['fas fa-city',          'City',   $bb['city_name'] ?? ''],
                        ['fas fa-phone',         'Contact',$bb['bb_contact'] ?? ''],
                    ];
                    foreach ($rows as [$icon,$label,$val]):
                        if (empty($val)) continue; ?>
                    <div class="bb-info-row">
                        <i class="<?= $icon ?>"></i>
                        <div>
                            <div class="li-label"><?= $label ?></div>
                            <div class="li-val"><?= htmlspecialchars($val) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Nearby Hospitals -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-hospital-alt"></i> Nearby Hospitals</h5>
                    <a href="<?= BASE_URL ?>blood-bank/hospitals" style="font-size:0.8rem; color:#00A8CC; text-decoration:none; font-weight:600;">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($nearby_hosps) > 0):
                        while ($h = mysqli_fetch_assoc($nearby_hosps)): ?>
                    <div class="nearby-item">
                        <div class="ni-icon"><i class="fas fa-hospital"></i></div>
                        <div>
                            <div class="ni-name"><?= htmlspecialchars($h['hospital_name']) ?></div>
                            <div class="ni-sub">
                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($h['city_name']) ?>
                                <?php if (!empty($h['hospital_phone'])): ?> · <i class="fas fa-phone me-1"></i><?= htmlspecialchars($h['hospital_phone']) ?><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; else: ?>
                        <p class="text-muted text-center py-2">No hospitals found in your city.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nearby Labs -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-flask"></i> Nearby Laboratories</h5>
                    <a href="<?= BASE_URL ?>blood-bank/labs" style="font-size:0.8rem; color:#27C96A; text-decoration:none; font-weight:600;">View All</a>
                </div>
                <div class="info-card-body">
                    <?php if (mysqli_num_rows($nearby_labs) > 0):
                        while ($l = mysqli_fetch_assoc($nearby_labs)): ?>
                    <div class="nearby-item">
                        <div class="ni-icon" style="background:linear-gradient(135deg,#27C96A,#00C9A7);"><i class="fas fa-flask"></i></div>
                        <div>
                            <div class="ni-name"><?= htmlspecialchars($l['lab_name']) ?></div>
                            <div class="ni-sub">
                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($l['city_name']) ?>
                                <?php if (!empty($l['lab_phone'])): ?> · <i class="fas fa-phone me-1"></i><?= htmlspecialchars($l['lab_phone']) ?><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; else: ?>
                        <p class="text-muted text-center py-2">No laboratories found in your city.</p>
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
                    <textarea id="noteTextarea" placeholder="Write your notes, reminders..."
                              style="width:100%; min-height:120px; padding:12px 14px; border:2px solid rgba(0,168,204,0.2); border-radius:12px; font-size:0.9rem; color:#0D2B6E; line-height:1.7; resize:vertical; outline:none; transition:border-color 0.2s; font-family:inherit; background:#f0f9ff;"
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

        </div><!-- LEFT -->

        <!-- RIGHT -->
        <div class="col-stack">

            <!-- Quick Actions -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="info-card-body">
                    <div class="quick-links">
                        <a href="<?= BASE_URL ?>blood-bank/profile" class="quick-link-item">
                            <i class="fas fa-tint"></i><span>BB Profile</span>
                        </a>
                        <a href="<?= BASE_URL ?>blood-bank/feedback" class="quick-link-item">
                            <i class="fas fa-star"></i><span>Reviews</span>
                        </a>
                        <a href="<?= BASE_URL ?>blood-bank/hospitals" class="quick-link-item">
                            <i class="fas fa-hospital"></i><span>Hospitals</span>
                        </a>
                        <a href="<?= BASE_URL ?>blood-bank/labs" class="quick-link-item">
                            <i class="fas fa-flask"></i><span>Laboratories</span>
                        </a>
                        <a href="<?= BASE_URL ?>logout" class="quick-link-item" style="color:#ef4444;">
                            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-star"></i> Recent Reviews</h5>
                    <a href="<?= BASE_URL ?>blood-bank/feedback" style="font-size:0.8rem; color:#00A8CC; text-decoration:none; font-weight:600;">View All</a>
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

        </div><!-- RIGHT -->
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
    fetch('<?= BASE_URL ?>blood-bank/index.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Note';
            if (d.success) {
                badgeTxt.textContent = d.action === 'updated' ? 'Updated!' : 'Saved!';
                badge.style.display = 'flex';
                lastSaved.innerHTML = '<i class="fas fa-clock me-1"></i>Last saved: ' + d.time;
                lastSaved.style.color = '#00A8CC';
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
</script>
