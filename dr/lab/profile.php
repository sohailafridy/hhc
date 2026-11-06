<?php
include '../config.php';

// ── LAB AUTH ───────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'lab') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id = (int)$_SESSION['user_id'];

// ── FETCH LAB ──────────────────────────────────────────────
$lab_q = "SELECT l.*, c.city_name, u.email as user_email, u.username
          FROM laboratories l
          LEFT JOIN cities c ON l.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = l.user_id
          WHERE l.user_id = $user_id AND l.approve = 1 LIMIT 1";
$lab_r = mysqli_query($con, $lab_q);
$lab   = mysqli_fetch_assoc($lab_r);
$labName = $lab['lab_name'];
$labPhone = $lab['lab_phone'];
$labEmail = $lab['lab_email'];
$labCityId = $lab['city_id'];
$lab_pic = $lab['lab_pic'];
$username = $lab['username'];
$lab_address = $lab['lab_address'];





if (!$lab) { session_destroy(); header("Location: " . BASE_URL . "login"); exit(); }

$lab_id = (int)$lab['lab_id'];

// ── CITIES ─────────────────────────────────────────────────
$cities_r = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE status=1 ORDER BY city_name ASC");
$cities = [];
while ($row = mysqli_fetch_assoc($cities_r)) $cities[] = $row;

// ── HANDLE UPDATE ──────────────────────────────────────────
$success_msg = $error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lab_name    = mysqli_real_escape_string($con, trim($_POST['lab_name']    ?? ''));
    $lab_phone   = mysqli_real_escape_string($con, trim($_POST['lab_phone']   ?? ''));
    $lab_email   = mysqli_real_escape_string($con, trim($_POST['lab_email']   ?? ''));
    $lab_address = mysqli_real_escape_string($con, trim($_POST['lab_address'] ?? ''));
    $city_id     = (int)($_POST['city_id'] ?? 0);

    $pic_clause = '';
    if (isset($_FILES['lab_pic']) && $_FILES['lab_pic']['error'] == 0) {
        $target_dir = BASE_PATH . "/admin/inc/uploads/labs/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['lab_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $fname = time() . '_' . $user_id . '.' . $ext;
            if (move_uploaded_file($_FILES['lab_pic']['tmp_name'], $target_dir . $fname)) {
                $pic_clause = ", lab_pic = '$fname'";
            }
        }
    }

    $city_clause = $city_id > 0 ? ", city_id = $city_id" : '';

    $upd = "UPDATE laboratories SET
                lab_name    = '$lab_name',
                lab_phone   = '$lab_phone',
                lab_email   = '$lab_email',
                lab_address = '$lab_address'
                $city_clause
                $pic_clause,
                updated_at  = NOW()
            WHERE lab_id = $lab_id";

    if (mysqli_query($con, $upd)) {
        if (!empty($lab_email)) {
            mysqli_query($con, "UPDATE users SET email='$lab_email' WHERE user_id=$user_id");
        }
        $success_msg = "Profile updated successfully!";
        ?>  
            <script>
                window.location.href = "<?= BASE_URL ?>lab/profile";
            </script>
        <?php
        $lab_r = mysqli_query($con, $lab_q);
        $lab   = mysqli_fetch_assoc($lab_r);
    } else {
        $error_msg = "Error: " . mysqli_error($con);
    }
}
?>
<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/lab/inc/nav.php'; ?>

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
        <?php if (!empty($lab_pic) && file_exists(BASE_PATH.'/admin/inc/uploads/labs/'.$lab_pic)): ?>
            <img src="<?= BASE_URL ?>admin/inc/uploads/labs/<?= htmlspecialchars($lab_pic) ?>" class="prof-avatar" alt="">
        <?php else: ?>
            <div class="prof-avatar-placeholder"><i class="fas fa-flask"></i></div>
        <?php endif; ?>
        <div class="prof-title">
            <h2><?= htmlspecialchars($labName) ?></h2>
            <p>
                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($lab['city_name'] ?? '') ?>
                <?php if (!empty($lab['lab_phone'])): ?> &nbsp;|&nbsp; <i class="fas fa-phone me-1"></i><?= htmlspecialchars($lab['lab_phone']) ?><?php endif; ?>
                <?php if (!empty($lab['lab_email'])): ?> &nbsp;|&nbsp; <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($lab['lab_email']) ?><?php endif; ?>
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
            <h5><i class="fas fa-edit"></i> Edit Lab Profile</h5>
        </div>
        <div class="form-card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Lab Name *</label>
                        <input type="text" class="form-control" name="lab_name" value="<?= htmlspecialchars($labName) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-control" name="lab_phone" value="<?= htmlspecialchars($labPhone ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="lab_email" value="<?= htmlspecialchars($labEmail ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <select class="form-control" name="city_id">
                            <option value="0">Select City</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?= $c['city_id'] ?>" <?= $labCityId==$c['city_id']?'selected':'' ?>><?= htmlspecialchars($c['city_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lab Picture</label>
                        <input type="file" class="form-control" name="lab_pic" accept="image/*">
                        <?php if (!empty($lab_pic)): ?>
                            <div class="image-preview">
                                <img src="<?= BASE_URL ?>admin/inc/uploads/labs/<?= htmlspecialchars($lab_pic) ?>" alt="">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username <small style="text-transform:none; color:#94a3b8; font-weight:400;">(not editable)</small></label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($username ?? '') ?>" disabled style="background:#f0fff8; color:#64748b;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address *</label>
                        <textarea class="form-control" name="lab_address" rows="3" required><?= htmlspecialchars($lab_address ?? '') ?></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Profile</button>
                    <a href="<?= BASE_URL ?>lab/index.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
<?php include BASE_PATH.'/admin/inc/footer.php'; ?>
