<?php
include '../config.php';

// ── BLOOD BANK AUTH ────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'blood bank') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id = (int)$_SESSION['user_id'];

// ── FETCH BLOOD BANK DETAILS ───────────────────────────────
$bb_q = "SELECT bb.*, c.city_name, u.status as estatus, u.username, u.email as user_email
         FROM blood_bank bb
         LEFT JOIN cities c ON bb.city_id = c.city_id
         LEFT JOIN users u ON u.user_id = bb.user_id
         WHERE bb.user_id = $user_id AND bb.approve = 1 LIMIT 1";
$bb_r = mysqli_query($con, $bb_q);
$bb = mysqli_fetch_assoc($bb_r);

if (!$bb) { 
    session_destroy(); 
    header("Location: " . BASE_URL . "login"); 
    exit(); 
}

$bb_id = (int)$bb['bb_id'];

// ── FETCH CITIES ───────────────────────────────────────────
$cities_r = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE status=1 ORDER BY city_name ASC");
$cities = [];
while ($row = mysqli_fetch_assoc($cities_r)) {
    $cities[] = $row;
}

// ── FETCH NOTES (Admin Message) ────────────────────────────
$notes_q = "SELECT * FROM notes WHERE user_id = $user_id LIMIT 1";
$notes_r = mysqli_query($con, $notes_q);
$bb_note = ($notes_r && mysqli_num_rows($notes_r) > 0) ? mysqli_fetch_assoc($notes_r) : null;

// ── HANDLE UPDATE ──────────────────────────────────────────
$success_msg = $error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bb_name    = mysqli_real_escape_string($con, trim($_POST['bb_name'] ?? ''));
    $bb_contact = mysqli_real_escape_string($con, trim($_POST['bb_contact'] ?? ''));
    $bb_address = mysqli_real_escape_string($con, trim($_POST['bb_address'] ?? ''));
    $city_id    = (int)($_POST['city_id'] ?? 0);
    $notes_text = mysqli_real_escape_string($con, trim($_POST['notes'] ?? ''));

    $pic_clause = '';
    if (isset($_FILES['bb_pic']) && $_FILES['bb_pic']['error'] == 0) {
        $target_dir = BASE_PATH . "/admin/inc/uploads/blood-banks/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['bb_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $fname = time() . '_' . $user_id . '.' . $ext;
            if (move_uploaded_file($_FILES['bb_pic']['tmp_name'], $target_dir . $fname)) {
                $pic_clause = ", bb_pic = '$fname'";
            }
        }
    }

    $city_clause = $city_id > 0 ? ", city_id = $city_id" : '';

    // Update Blood Bank Table
    $upd = "UPDATE blood_bank SET
                bb_name     = '$bb_name',
                bb_contact  = '$bb_contact',
                bb_address  = '$bb_address'
                $city_clause
                $pic_clause,
                updated_at  = NOW()
            WHERE bb_id = $bb_id";

    if (mysqli_query($con, $upd)) {
        
        // Handle Notes (Update or Insert)
        if ($bb_note) {
            mysqli_query($con, "UPDATE notes SET notes='$notes_text', updated_at=NOW() WHERE user_id=$user_id");
        } else {
            if (!empty($notes_text)) {
                mysqli_query($con, "INSERT INTO notes (user_id, notes, created_at, updated_at) VALUES ($user_id, '$notes_text', NOW(), NOW())");
            }
        }

        $success_msg = "Profile updated successfully!";
        
        // Refresh data
        $bb_r = mysqli_query($con, $bb_q);
        $bb = mysqli_fetch_assoc($bb_r);
        
        $notes_r = mysqli_query($con, $notes_q);
        $bb_note = ($notes_r && mysqli_num_rows($notes_r) > 0) ? mysqli_fetch_assoc($notes_r) : null;

    } else {
        $error_msg = "Error: " . mysqli_error($con);
    }
}
?>
<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/blood-bank/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-profile2-admin.css">
<style>
.content-wrapper { background:#C8E6F5; min-height:100vh; padding:24px 32px 60px; }
.prof-header {
    background: linear-gradient(135deg,#071124 0%,#0D2B6E 50%,#1C3F8F 100%);
    border-radius:18px; padding:28px 32px; color:white;
    margin-bottom:24px; display:flex; align-items:center; gap:24px;
    position:relative; overflow:hidden;
}
.prof-header::before { content:''; position:absolute; top:-40%; right:-8%; width:260px; height:260px; background:rgba(0,168,204,0.1); border-radius:50%; }
.prof-avatar { width:90px; height:90px; border-radius:50%; object-fit:cover; border:4px solid rgba(255,255,255,0.3); flex-shrink:0; position:relative; z-index:1; }
.prof-avatar-placeholder { width:90px; height:90px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:rgba(255,255,255,0.6); border:4px solid rgba(255,255,255,0.2); flex-shrink:0; position:relative; z-index:1; }
.prof-title { position:relative; z-index:1; }
.prof-title h2 { margin:0 0 4px; font-size:1.5rem; font-weight:800; }
.prof-title p  { margin:0; opacity:0.85; font-size:0.92rem; }
.form-card { background:white; border-radius:16px; box-shadow:0 2px 12px rgba(28,63,143,0.08); border:1px solid rgba(0,168,204,0.12); overflow:hidden; margin-bottom:22px; }
.form-card-header { padding:16px 24px; background:linear-gradient(135deg,#f0f9ff,#e8f7ff); border-bottom:1px solid rgba(0,168,204,0.12); display:flex; align-items:center; gap:10px; }
.form-card-header h5 { margin:0; font-weight:700; color:#0D2B6E; font-size:0.98rem; display:flex; align-items:center; gap:8px; }
.form-card-header h5 i { color:#1C3F8F; }
.form-card-body { padding:24px 28px; }
.form-label { font-weight:700; color:#0D2B6E; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.form-control { border:2px solid rgba(0,168,204,0.2); border-radius:10px; padding:10px 14px; font-size:0.9rem; color:#0D2B6E; transition:all 0.25s; }
.form-control:focus { border-color:#00A8CC; box-shadow:0 0 0 4px rgba(0,168,204,0.12); outline:none; }
.form-control:disabled { background:#f0f9ff; color:#64748b; }
.btn-submit { background:linear-gradient(135deg,#1C3F8F,#00A8CC); color:white; border:none; padding:11px 28px; border-radius:50px; font-weight:700; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all 0.25s; }
.btn-submit:hover { background:linear-gradient(135deg,#27C96A,#00C9A7); transform:translateY(-2px); box-shadow:0 6px 18px rgba(0,168,204,0.3); color:white; }
.btn-cancel { background:white; color:#ef4444; border:2px solid #ef4444; padding:11px 24px; border-radius:50px; font-weight:700; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:all 0.25s; }
.btn-cancel:hover { background:#ef4444; color:white; }
.image-preview { margin-top:12px; }
.image-preview img { width:90px; height:90px; border-radius:12px; object-fit:cover; border:2px solid rgba(0,168,204,0.2); }
</style>

<div class="content-wrapper">

    <!-- Profile Header -->
    <div class="prof-header">
        <?php if (!empty($bb['bb_pic'])): ?>
            <img src="<?= BASE_URL ?>admin/inc/uploads/blood-banks/<?= htmlspecialchars($bb['bb_pic']) ?>" class="prof-avatar" alt="">
        <?php else: ?>
            <div class="prof-avatar-placeholder"><i class="fas fa-tint"></i></div>
        <?php endif; ?>
        <div class="prof-title">
            <h2><?= htmlspecialchars($bb['bb_name']) ?></h2>
            <p>
                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($bb['city_name'] ?? 'City Not Set') ?>
                <?php if (!empty($bb['bb_contact'])): ?> &nbsp;|&nbsp; <i class="fas fa-phone me-1"></i><?= htmlspecialchars($bb['bb_contact']) ?><?php endif; ?>
            </p>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3"><i class="fas fa-check-circle me-2"></i><?= $success_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?= $error_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- Edit Form -->
    <div class="form-card">
        <div class="form-card-header">
            <h5><i class="fas fa-edit"></i> Edit Blood Bank Profile</h5>
        </div>
        <div class="form-card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Blood Bank Name *</label>
                        <input type="text" class="form-control" name="bb_name"
                               value="<?= htmlspecialchars($bb['bb_name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact Number *</label>
                        <input type="text" class="form-control" name="bb_contact"
                               value="<?= htmlspecialchars($bb['bb_contact'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <select class="form-control" name="city_id">
                            <option value="0">Select City</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?= $c['city_id'] ?>" <?= $bb['city_id'] == $c['city_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['city_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" name="bb_pic" accept="image/*">
                        <?php if (!empty($bb['bb_pic'])): ?>
                            <div class="image-preview">
                                <img src="<?= BASE_URL ?>admin/inc/uploads/blood-banks/<?= htmlspecialchars($bb['bb_pic']) ?>" alt="">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Full Address *</label>
                        <textarea class="form-control" name="bb_address" rows="2" required><?= htmlspecialchars($bb['bb_address'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Admin Message / Notes <small style="text-transform:none; color:#94a3b8; font-weight:400;">(Show on public detail page)</small></label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="e.g. Special discount on blood bags, emergency contact info, etc..."><?= htmlspecialchars($bb_note['notes'] ?? '') ?></textarea>
                    </div>

                </div>
                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Profile</button>
                    <a href="<?= BASE_URL ?>blood-bank/index.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
<?php include BASE_PATH.'/admin/inc/footer.php'; ?>