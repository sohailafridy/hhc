<?php
include '../config.php';

// ── RADIOLOGY AUTH ───────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'radiology') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id = (int)$_SESSION['user_id'];

// ── FETCH RADIOLOGY DATA ─────────────────────────────────────────
$rad_q = "SELECT r.*, c.city_name, u.email as user_email, u.username
          FROM radiology r
          LEFT JOIN cities c ON r.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = r.user_id
          WHERE r.user_id = $user_id AND r.approve = 1 LIMIT 1";
$rad_r = mysqli_query($con, $rad_q);
$rad   = mysqli_fetch_assoc($rad_r);

if (!$rad) { session_destroy(); header("Location: " . BASE_URL . "login"); exit(); }

$rad_id = (int)$rad['rad_id'];
$radName = $rad['rad_name'];
$radPhone = $rad['rad_phone'];
$radEmail = $rad['rad_email'];
$radCityId = $rad['city_id'];
$rad_pic = $rad['rad_pic'];
$username = $rad['username'];
$rad_address = $rad['rad_address'];

// ── CITIES ─────────────────────────────────────────────────
$cities_r = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE status=1 ORDER BY city_name ASC");
$cities = [];
while ($row = mysqli_fetch_assoc($cities_r)) $cities[] = $row;

// ── HANDLE UPDATE ──────────────────────────────────────────
$success_msg = $error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rad_name    = mysqli_real_escape_string($con, trim($_POST['rad_name']    ?? ''));
    $rad_phone   = mysqli_real_escape_string($con, trim($_POST['rad_phone']   ?? ''));
    $rad_email   = mysqli_real_escape_string($con, trim($_POST['rad_email']   ?? ''));
    $rad_address = mysqli_real_escape_string($con, trim($_POST['rad_address'] ?? ''));
    $city_id     = (int)($_POST['city_id'] ?? 0);

    $pic_clause = '';
    if (isset($_FILES['rad_pic']) && $_FILES['rad_pic']['error'] == 0) {
        $target_dir = BASE_PATH . "/admin/inc/uploads/radiology/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['rad_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $fname = time() . '_' . $user_id . '.' . $ext;
            if (move_uploaded_file($_FILES['rad_pic']['tmp_name'], $target_dir . $fname)) {
                $pic_clause = ", rad_pic = '$fname'";
            }
        }
    }

    $city_clause = $city_id > 0 ? ", city_id = $city_id" : '';

    $upd = "UPDATE radiology SET
                rad_name    = '$rad_name',
                rad_phone   = '$rad_phone',
                rad_email   = '$rad_email',
                rad_address = '$rad_address'
                $city_clause
                $pic_clause,
                updated_at  = NOW()
            WHERE rad_id = $rad_id";

    if (mysqli_query($con, $upd)) {
        if (!empty($rad_email)) {
            mysqli_query($con, "UPDATE users SET email='$rad_email' WHERE user_id=$user_id");
        }
        $success_msg = "Profile updated successfully!";
        ?>  
            <script>
                window.location.href = "<?= BASE_URL ?>radiology/profile";
            </script>
        <?php
        $rad_r = mysqli_query($con, $rad_q);
        $rad   = mysqli_fetch_assoc($rad_r);
    } else {
        $error_msg = "Error: " . mysqli_error($con);
    }
}
?>
<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/radiology/inc/nav.php'; ?>

<style>
.content-wrapper { background:#C8E6F5; min-height:100vh; padding:24px 32px 60px; }
.prof-header {
    background: linear-gradient(135deg,#071124 0%,#0D2B6E 40%,#27C96A 100%);
    border-radius:18px; padding:28px 32px; color:white;
    margin-bottom:24px; display:flex; align-items:center; gap:24px;
    position:relative; overflow:hidden;
}
.prof-header::before { content:''; position:absolute; top:-40%; right:-8%; width:260px; height:260px; background:rgba(39,201,106,0.12); border-radius:50%; }
.prof-header::after  { content:''; position:absolute; bottom:-30%; left:-6%; width:180px; height:180px; background:rgba(0,168,204,0.08); border-radius:50%; }
.prof-avatar { width:90px; height:90px; border-radius:16px; object-fit:cover; border:4px solid rgba(255,255,255,0.3); flex-shrink:0; position:relative; z-index:1; }
.prof-avatar-placeholder { width:90px; height:90px; border-radius:16px; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; font-size:2.5rem; color:rgba(255,255,255,0.6); border:4px solid rgba(255,255,255,0.2); flex-shrink:0; position:relative; z-index:1; }
.prof-title { position:relative; z-index:1; }
.prof-title h2 { margin:0 0 4px; font-size:1.5rem; font-weight:800; }
.prof-title p  { margin:0; opacity:0.85; font-size:0.92rem; }
.form-card { background:white; border-radius:16px; box-shadow:0 2px 12px rgba(28,63,143,0.08); border:1px solid rgba(39,201,106,0.15); overflow:hidden; margin-bottom:22px; }
.form-card-header { padding:16px 24px; background:linear-gradient(135deg,#f0fff8,#e8f7ff); border-bottom:1px solid rgba(39,201,106,0.12); display:flex; align-items:center; gap:10px; }
.form-card-header h5 { margin:0; font-weight:700; color:#0D2B6E; font-size:0.98rem; display:flex; align-items:center; gap:8px; }
.form-card-header h5 i { color:#27C96A; }
.form-card-body { padding:24px 28px; }
.form-label { font-weight:700; color:#0D2B6E; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:6px; }
.form-control { border:2px solid rgba(39,201,106,0.2); border-radius:10px; padding:10px 14px; font-size:0.9rem; color:#0D2B6E; transition:all 0.25s; }
.form-control:focus { border-color:#27C96A; box-shadow:0 0 0 4px rgba(39,201,106,0.1); outline:none; }
.form-control:disabled { background:#f0fff8; color:#64748b; }
.btn-submit { background:linear-gradient(135deg,#27C96A,#00C9A7); color:white; border:none; padding:11px 28px; border-radius:50px; font-weight:700; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all 0.25s; }
.btn-submit:hover { background:linear-gradient(135deg,#1C3F8F,#00A8CC); transform:translateY(-2px); box-shadow:0 6px 18px rgba(39,201,106,0.3); color:white; }
.btn-cancel { background:white; color:#ef4444; border:2px solid #ef4444; padding:11px 24px; border-radius:50px; font-weight:700; font-size:0.85rem; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:all 0.25s; }
.btn-cancel:hover { background:#ef4444; color:white; }
.image-preview { margin-top:12px; }
.image-preview img { width:90px; height:90px; border-radius:12px; object-fit:cover; border:2px solid rgba(39,201,106,0.2); }
</style>

<div class="content-wrapper">

    <div class="prof-header">
        <?php if (!empty($rad_pic) && file_exists(BASE_PATH.'/admin/inc/uploads/radiology/'.$rad_pic)): ?>
            <img src="<?= BASE_URL ?>admin/inc/uploads/radiology/<?= htmlspecialchars($rad_pic) ?>" class="prof-avatar" alt="">
        <?php else: ?>
            <div class="prof-avatar-placeholder"><i class="fas fa-camera"></i></div>
        <?php endif; ?>
        <div class="prof-title">
            <h2><?= htmlspecialchars($radName) ?></h2>
            <p>
                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($rad['city_name'] ?? '') ?>
                <?php if (!empty($rad['rad_phone'])): ?> &nbsp;|&nbsp; <i class="fas fa-phone me-1"></i><?= htmlspecialchars($rad['rad_phone']) ?><?php endif; ?>
                <?php if (!empty($rad['rad_email'])): ?> &nbsp;|&nbsp; <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($rad['rad_email']) ?><?php endif; ?>
            </p>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3"><i class="fas fa-check-circle me-2"></i><?= $success_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?= $error_msg ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-card-header">
            <h5><i class="fas fa-edit"></i> Edit Radiology Profile</h5>
        </div>
        <div class="form-card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Radiology Name *</label>
                        <input type="text" class="form-control" name="rad_name" value="<?= htmlspecialchars($radName) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-control" name="rad_phone" value="<?= htmlspecialchars($radPhone ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="rad_email" value="<?= htmlspecialchars($radEmail ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <select class="form-control" name="city_id">
                            <option value="0">Select City</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?= $c['city_id'] ?>" <?= $radCityId==$c['city_id']?'selected':'' ?>><?= htmlspecialchars($c['city_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Radiology Picture</label>
                        <input type="file" class="form-control" name="rad_pic" accept="image/*">
                        <?php if (!empty($rad_pic)): ?>
                            <div class="image-preview">
                                <img src="<?= BASE_URL ?>admin/inc/uploads/radiology/<?= htmlspecialchars($rad_pic) ?>" alt="">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username <small style="text-transform:none; color:#94a3b8; font-weight:400;">(not editable)</small></label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($username ?? '') ?>" disabled style="background:#f0fff8; color:#64748b;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address *</label>
                        <textarea class="form-control" name="rad_address" rows="3" required><?= htmlspecialchars($rad_address ?? '') ?></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Profile</button>
                    <a href="<?= BASE_URL ?>radiology/index.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
<?php include BASE_PATH.'/admin/inc/footer.php'; ?>