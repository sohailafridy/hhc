<?php
include '../config.php';

// ── BLOOD BANK AUTH ────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'blood bank') {
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

// ── SAVE BLOOD STOCK (AJAX Handler) ────────────────────────
if (isset($_POST['save_blood_stock'])) {
    $bg_id = (int)$_POST['bg_id'];
    $stock = max(0, (int)$_POST['stock']);

    $check_q = mysqli_query($con, "SELECT avail_b_id FROM bb_available_blood WHERE bb_id=$bb_id AND bg_id=$bg_id LIMIT 1");

    if (mysqli_num_rows($check_q) > 0) {
        mysqli_query($con, "UPDATE bb_available_blood SET stock=$stock WHERE bb_id=$bb_id AND bg_id=$bg_id");
    } else {
        mysqli_query($con, "INSERT INTO bb_available_blood (bb_id, bg_id, stock) VALUES ($bb_id, $bg_id, $stock)");
    }

    echo json_encode(['success' => true, 'message' => 'Stock updated successfully']);
    exit();
}

$note_row = null;
$note_r   = mysqli_query($con, "SELECT * FROM notes WHERE user_id=$user_id LIMIT 1");
if (mysqli_num_rows($note_r) > 0) $note_row = mysqli_fetch_assoc($note_r);

// ── FETCH BLOOD GROUPS & STOCK ─────────────────────────────
$bg_query = "SELECT bg.bg_id, bg.bg_name, IFNULL(bab.stock, 0) as stock
             FROM blood_groups bg
             LEFT JOIN bb_available_blood bab ON bg.bg_id = bab.bg_id AND bab.bb_id = $bb_id
             ORDER BY bg.bg_id ASC";
$bg_result = mysqli_query($con, $bg_query);
$bg_rows   = $bg_result ? mysqli_fetch_all($bg_result, MYSQLI_ASSOC) : [];

// ── STATS ──────────────────────────────────────────────────
$rating_q    = mysqli_query($con, "SELECT AVG(stars) as avg, COUNT(*) as cnt FROM feedback WHERE user_id=$user_id AND status=1");
$rating_data = mysqli_fetch_assoc($rating_q);
$avg_rating  = $rating_data['avg'] ? round($rating_data['avg'], 1) : 0;
$total_reviews = (int)$rating_data['cnt'];

$hosp_cnt = (int)mysqli_fetch_assoc(mysqli_query($con,
    "SELECT COUNT(*) as c FROM hospitals h LEFT JOIN users u ON u.user_id=h.user_id
     WHERE h.city_id={$bb['city_id']} AND u.status=1 AND h.approve=1"))['c'];

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

// ── VISITING CARD / BANNER DATA ────────────────────────────
// ⚠️ Apne project k mutabiq in 2 paths ko adjust kar lein:
$logo_url   = BASE_URL . 'images/logo.png';                       // SehatNexa logo ka path
$detail_url = BASE_URL . 'hospital-detail?hospital_id=' . $bb_id; // QR scan pe ye link khulega

$card_data = [
    'name'    => $bb['bb_name'] ?? '',
    'address' => $bb['bb_address'] ?? '',
    'city'    => $bb['city_name'] ?? '',
    'contact' => $bb['bb_contact'] ?? '',
    'url'     => $detail_url,
    'logo'    => $logo_url,
    'stock'   => array_map(function ($r) {
        return ['name' => $r['bg_name'], 'stock' => (int)$r['stock']];
    }, $bg_rows),
];
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

/* Blood stock */
.stock-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; background: #fff; border: 1px solid #e2e8f0;
    border-radius: 10px; margin-bottom: 10px; transition: all 0.2s;
}
.stock-row:hover { border-color: #00A8CC; box-shadow: 0 2px 8px rgba(0,168,204,0.1); }
.stock-row .bg-name { font-weight: 800; font-size: 1.1rem; color: #ef4444; width: 50px; text-align: center; }
.stock-row .stock-input-wrapper { display: flex; align-items: center; gap: 8px; flex: 1; margin-left: 15px; }
.stock-row input[type="number"] {
    width: 80px; padding: 6px 10px; border: 2px solid #e2e8f0; border-radius: 8px;
    font-weight: 600; color: #0D2B6E; text-align: center; transition: all 0.2s;
}
.stock-row input[type="number"]:focus { border-color: #00A8CC; outline: none; }
.btn-save-stock {
    background: #f0f9ff; color: #00A8CC; border: 1px solid #00A8CC;
    padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; font-weight: 700;
    cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 5px;
}
.btn-save-stock:hover { background: #00A8CC; color: white; }
.btn-save-stock:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Create buttons ── */
.create-btn-row { display:flex; gap:12px; flex-wrap:wrap; margin-top:18px; }
.btn-create {
    display:inline-flex; align-items:center; gap:8px; padding:10px 20px;
    border-radius:12px; font-weight:700; font-size:0.88rem; cursor:pointer;
    border:2px solid rgba(255,255,255,0.85); background:rgba(255,255,255,0.15);
    color:#fff; transition:all .2s; backdrop-filter:blur(4px);
}
.btn-create:hover { background:#fff; color:#1C3F8F; transform:translateY(-2px); }

/* ── Modal ── */
.gen-modal { position:fixed; inset:0; background:rgba(13,43,110,0.65); z-index:99999; display:none; align-items:center; justify-content:center; padding:20px; }
.gen-modal.show { display:flex; }
.gen-modal-box { background:#fff; border-radius:18px; width:100%; max-width:900px; max-height:92vh; overflow:auto; box-shadow:0 20px 60px rgba(0,0,0,.35); }
.gen-modal-head { display:flex; align-items:center; justify-content:space-between; padding:16px 22px; border-bottom:1px solid #e8f7ff; }
.gen-modal-head h5 { margin:0; color:#0D2B6E; font-weight:800; font-size:1.05rem; }
.gen-modal-close { background:none; border:none; font-size:1.5rem; line-height:1; cursor:pointer; color:#64748b; }
.gen-modal-body { padding:20px 22px; background:#f0f9ff; }
.gen-preview-wrap { width:100%; overflow:hidden; border-radius:12px; box-shadow:0 6px 24px rgba(13,43,110,.18); background:#fff; }
.gen-preview-inner { transform-origin:top left; }
.gen-modal-foot { display:flex; gap:10px; justify-content:flex-end; padding:14px 22px; border-top:1px solid #e8f7ff; flex-wrap:wrap; }
.gen-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 20px; border-radius:10px; font-weight:700; font-size:.85rem; cursor:pointer; border:none; }
.gen-btn.primary { background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:#fff; }
.gen-btn.primary:hover { background:linear-gradient(135deg,#27C96A,#00C9A7); }
.gen-btn.light { background:#e2e8f0; color:#334155; }
.gen-btn:disabled { opacity:.6; cursor:not-allowed; }

/* ── VISITING CARD (1050 x 600) ── */
.vc { width:1050px; height:600px; position:relative; background:#fff; font-family:'Segoe UI',Arial,sans-serif; overflow:hidden; display:flex; flex-direction:column; }
.vc-top { height:150px; background:linear-gradient(135deg,#0D2B6E,#1C3F8F 55%,#00A8CC); display:flex; align-items:center; justify-content:space-between; padding:0 50px; color:#fff; position:relative; }
.vc-top::after { content:''; position:absolute; right:-60px; top:-60px; width:220px; height:220px; border-radius:50%; background:rgba(255,255,255,.08); }
.vc-logo-pill { background:#fff; border-radius:16px; padding:10px 18px; display:flex; align-items:center; height:84px; z-index:1; }
.vc-logo-pill img { height:64px; width:auto; display:block; }
.vc-logo-text { font-size:30px; font-weight:800; color:#1C3F8F; }
.vc-type { text-align:right; z-index:1; }
.vc-type .t1 { font-size:34px; font-weight:800; letter-spacing:.5px; }
.vc-type .t2 { font-size:17px; opacity:.9; margin-top:4px; }
.vc-body { flex:1; display:flex; padding:34px 50px 20px; gap:30px; }
.vc-info { flex:1; display:flex; flex-direction:column; justify-content:center; }
.vc-name { font-size:42px; font-weight:800; color:#0D2B6E; line-height:1.15; margin-bottom:22px; }
.vc-row { display:flex; align-items:flex-start; gap:14px; margin-bottom:14px; font-size:21px; color:#334155; font-weight:600; }
.vc-ico { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
.vc-qr { width:250px; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.vc-qr-box { padding:12px; background:#fff; border:3px solid #00A8CC; border-radius:18px; width:230px; height:230px; display:flex; align-items:center; justify-content:center; }
.vc-qr-box img, .vc-qr-box canvas { width:200px !important; height:200px !important; }
.vc-qr-cap { margin-top:12px; font-size:16px; font-weight:700; color:#1C3F8F; text-align:center; }
.vc-foot { height:46px; background:#0D2B6E; color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:600; letter-spacing:.5px; }

/* ── BANNER (1200 x 420) ── */
.bn { width:1200px; height:420px; position:relative; font-family:'Segoe UI',Arial,sans-serif; overflow:hidden; background:linear-gradient(120deg,#0D2B6E,#1C3F8F 50%,#00A8CC); color:#fff; display:flex; align-items:center; padding:0 50px; gap:36px; }
.bn::before { content:''; position:absolute; left:-120px; bottom:-160px; width:420px; height:420px; border-radius:50%; background:rgba(255,255,255,.07); }
.bn::after  { content:''; position:absolute; right:260px; top:-140px; width:340px; height:340px; border-radius:50%; background:rgba(255,255,255,.06); }
.bn-left { flex:1; z-index:1; }
.bn-logo { background:#fff; border-radius:14px; padding:8px 16px; display:inline-flex; align-items:center; height:70px; margin-bottom:18px; }
.bn-logo img { height:54px; width:auto; display:block; }
.bn-logo .vc-logo-text { font-size:26px; }
.bn-name { font-size:42px; font-weight:800; line-height:1.15; margin-bottom:8px; }
.bn-tag { font-size:22px; font-weight:600; color:#ffe4e6; margin-bottom:16px; }
.bn-meta { font-size:18px; font-weight:600; opacity:.95; margin-bottom:6px; }
.bn-groups { display:flex; flex-wrap:wrap; gap:10px; margin-top:18px; max-width:620px; }
.bn-chip { background:rgba(255,255,255,.16); border:2px solid rgba(255,255,255,.55); border-radius:12px; padding:6px 14px; font-weight:800; font-size:18px; display:flex; align-items:center; gap:8px; }
.bn-chip b { color:#fecaca; }
.bn-chip span { font-size:14px; font-weight:600; opacity:.9; }
.bn-qr { z-index:1; background:#fff; border-radius:20px; padding:16px; text-align:center; width:230px; flex-shrink:0; }
.bn-qr .vc-qr-box { border:none; padding:0; width:196px; height:196px; margin:0 auto; }
.bn-qr .vc-qr-box img, .bn-qr .vc-qr-box canvas { width:196px !important; height:196px !important; }
.bn-qr .cap { color:#1C3F8F; font-weight:800; font-size:16px; margin-top:8px; }

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

            <!-- CREATE BUTTONS -->
            <div class="create-btn-row">
                <button type="button" class="btn-create" onclick="openGen('card')"><i class="fas fa-id-card"></i> Create Visiting Card</button>
                <button type="button" class="btn-create" onclick="openGen('banner')"><i class="fas fa-image"></i> Create Banner</button>
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
                        ['fas fa-tint',          'Name',    $bb['bb_name']],
                        ['fas fa-map-marker-alt','Address', $bb['bb_address'] ?? ''],
                        ['fas fa-city',          'City',    $bb['city_name'] ?? ''],
                        ['fas fa-phone',         'Contact', $bb['bb_contact'] ?? ''],
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

            <!-- MANAGE BLOOD STOCK -->
            <div class="info-card">
                <div class="info-card-header">
                    <h5><i class="fas fa-tint"></i> Manage Blood Stock</h5>
                    <small style="color:#94a3b8; font-size:0.75rem;">Update available bags</small>
                </div>
                <div class="info-card-body">
                    <?php if (count($bg_rows) > 0):
                        foreach ($bg_rows as $bg): ?>
                        <div class="stock-row">
                            <div class="bg-name"><?= htmlspecialchars($bg['bg_name']) ?></div>
                            <div class="stock-input-wrapper">
                                <input type="number" min="0" id="stock_<?= (int)$bg['bg_id'] ?>" value="<?= (int)$bg['stock'] ?>" placeholder="0">
                                <span style="font-size:0.8rem; color:#64748b; font-weight:600;">Bags</span>
                            </div>
                            <button class="btn-save-stock" onclick="saveStock(<?= (int)$bg['bg_id'] ?>, this)">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    <?php endforeach; else: ?>
                        <p class="text-muted text-center py-2">No blood groups found in database.</p>
                    <?php endif; ?>
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

<!-- ══ GENERATOR MODAL ══ -->
<div class="gen-modal" id="genModal">
    <div class="gen-modal-box">
        <div class="gen-modal-head">
            <h5 id="genTitle">Preview</h5>
            <button type="button" class="gen-modal-close" onclick="closeGen()">&times;</button>
        </div>
        <div class="gen-modal-body">
            <div class="gen-preview-wrap" id="genPreviewWrap">
                <div class="gen-preview-inner" id="genPreviewInner"></div>
            </div>
        </div>
        <div class="gen-modal-foot">
            <button type="button" class="gen-btn light" onclick="closeGen()">Close</button>
            <button type="button" class="gen-btn primary" id="genDownloadBtn" onclick="downloadGen()"><i class="fas fa-download"></i> Download PNG</button>
        </div>
    </div>
</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
// ── NOTES SAVE FUNCTION ──
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

// ── BLOOD STOCK SAVE FUNCTION (AJAX) ──
function saveStock(bgId, btn) {
    var stockInput = document.getElementById('stock_' + bgId);
    var stockValue = stockInput.value;
    if (stockValue === '' || stockValue < 0) stockValue = 0;

    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    var fd = new FormData();
    fd.append('save_blood_stock', '1');
    fd.append('bg_id', bgId);
    fd.append('stock', stockValue);

    fetch('<?= BASE_URL ?>blood-bank/index.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
            btn.disabled = false;
            if (d.success) {
                btn.innerHTML = '<i class="fas fa-check"></i> Saved';
                btn.style.background = '#27C96A';
                btn.style.color = 'white';
                btn.style.borderColor = '#27C96A';

                // keep banner data in sync with the latest stock
                var item = BB.stock.find(s => s.id == bgId);
                if (item) item.stock = parseInt(stockValue, 10) || 0;

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '#f0f9ff';
                    btn.style.color = '#00A8CC';
                    btn.style.borderColor = '#00A8CC';
                }, 2000);
            } else {
                btn.innerHTML = originalText;
                alert('Error saving stock');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('Error connecting to server');
        });
}

// ══════════════════════════════════════════════
//  VISITING CARD & BANNER GENERATOR
// ══════════════════════════════════════════════
var BB = <?= json_encode($card_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
// attach bg_id to stock items (same order as DB rows)
var BG_IDS = <?= json_encode(array_map(function($r){ return (int)$r['bg_id']; }, $bg_rows)) ?>;
BB.stock.forEach(function (s, i) { s.id = BG_IDS[i]; });

var genType = 'card';

function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
}

function logoHtml() {
    return '<img src="' + esc(BB.logo) + '" crossorigin="anonymous" alt="SehatNexa" ' +
           'onerror="this.outerHTML=\'<span class=&quot;vc-logo-text&quot;>SehatNexa</span>\'">';
}

function cardHtml() {
    var rows = '';
    if (BB.address) rows += '<div class="vc-row"><div class="vc-ico"><i class="fas fa-map-marker-alt"></i></div><div>' + esc(BB.address) + '</div></div>';
    if (BB.city)    rows += '<div class="vc-row"><div class="vc-ico"><i class="fas fa-city"></i></div><div>' + esc(BB.city) + '</div></div>';
    if (BB.contact) rows += '<div class="vc-row"><div class="vc-ico"><i class="fas fa-phone"></i></div><div>' + esc(BB.contact) + '</div></div>';

    return '<div class="vc">' +
        '<div class="vc-top">' +
            '<div class="vc-logo-pill">' + logoHtml() + '</div>' +
            '<div class="vc-type"><div class="t1">🩸 Blood Bank</div><div class="t2">Donate Blood · Save Lives</div></div>' +
        '</div>' +
        '<div class="vc-body">' +
            '<div class="vc-info"><div class="vc-name">' + esc(BB.name) + '</div>' + rows + '</div>' +
            '<div class="vc-qr"><div class="vc-qr-box" data-qr="1"></div><div class="vc-qr-cap">Scan to view details</div></div>' +
        '</div>' +
        '<div class="vc-foot">www.sehatnexa.com</div>' +
    '</div>';
}

function bannerHtml() {
    var chips = BB.stock.map(function (s) {
        return '<div class="bn-chip"><b>' + esc(s.name) + '</b><span>' + s.stock + ' bags</span></div>';
    }).join('');

    var meta = '';
    if (BB.address) meta += '<div class="bn-meta">📍 ' + esc(BB.address) + (BB.city ? ', ' + esc(BB.city) : '') + '</div>';
    else if (BB.city) meta += '<div class="bn-meta">📍 ' + esc(BB.city) + '</div>';
    if (BB.contact) meta += '<div class="bn-meta">📞 ' + esc(BB.contact) + '</div>';

    return '<div class="bn">' +
        '<div class="bn-left">' +
            '<div class="bn-logo">' + logoHtml() + '</div>' +
            '<div class="bn-name">' + esc(BB.name) + '</div>' +
            '<div class="bn-tag">🩸 Blood Available · Donate Blood, Save Lives</div>' +
            meta +
            (chips ? '<div class="bn-groups">' + chips + '</div>' : '') +
        '</div>' +
        '<div class="bn-qr"><div class="vc-qr-box" data-qr="1"></div><div class="cap">Scan for details</div></div>' +
    '</div>';
}

function renderInto(container, type) {
    container.innerHTML = (type === 'card') ? cardHtml() : bannerHtml();
    var qrBox = container.querySelector('[data-qr]');
    var size = (type === 'card') ? 200 : 196;
    new QRCode(qrBox, {
        text: BB.url, width: size, height: size,
        colorDark: '#0D2B6E', colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
}

function fitPreview() {
    var wrap  = document.getElementById('genPreviewWrap');
    var inner = document.getElementById('genPreviewInner');
    var w = (genType === 'card') ? 1050 : 1200;
    var h = (genType === 'card') ? 600  : 420;
    var scale = Math.min(1, wrap.clientWidth / w);
    inner.style.width  = w + 'px';
    inner.style.transform = 'scale(' + scale + ')';
    wrap.style.height = (h * scale) + 'px';
}

function openGen(type) {
    genType = type;
    document.getElementById('genTitle').textContent = (type === 'card') ? 'Visiting Card Preview' : 'Banner Preview';
    document.getElementById('genModal').classList.add('show');
    renderInto(document.getElementById('genPreviewInner'), type);
    fitPreview();
}

function closeGen() {
    document.getElementById('genModal').classList.remove('show');
}

window.addEventListener('resize', function () {
    if (document.getElementById('genModal').classList.contains('show')) fitPreview();
});
document.getElementById('genModal').addEventListener('click', function (e) {
    if (e.target === this) closeGen();
});

function downloadGen() {
    var btn = document.getElementById('genDownloadBtn');
    var old = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

    // Render an unscaled copy off-screen so the PNG is full resolution
    var stage = document.createElement('div');
    stage.style.cssText = 'position:fixed;left:-99999px;top:0;';
    document.body.appendChild(stage);
    renderInto(stage, genType);

    setTimeout(function () {
        var target = stage.firstElementChild;
        html2canvas(target, { scale: 2, useCORS: true, backgroundColor: null })
            .then(function (canvas) {
                var a = document.createElement('a');
                var safe = (BB.name || 'blood-bank').replace(/[^a-z0-9]+/gi, '-').toLowerCase();
                a.download = safe + (genType === 'card' ? '-visiting-card.png' : '-banner.png');
                a.href = canvas.toDataURL('image/png');
                a.click();
            })
            .catch(function () { alert('Download failed. Please try again.'); })
            .finally(function () {
                document.body.removeChild(stage);
                btn.disabled = false;
                btn.innerHTML = old;
            });
    }, 400);
}
</script>