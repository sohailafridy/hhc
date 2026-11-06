<?php
include '../config.php';

// ── DOCTOR AUTH ────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'blood bank') {
    header("Location: " . BASE_URL . "login"); exit();
}

$user_id   = (int)$_SESSION['user_id'];

// ── FETCH DOCTOR ───────────────────────────────────────────
$doc_q  = "SELECT d.*, dct.type as specialization, dct.dr_cat_type_id as cat_type_id_val,
                  dct.treatment as mahre_amraz,
                  c.city_name, u.status as estatus, u.username, u.email as user_email
           FROM doctors d
           LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
           LEFT JOIN cities c ON d.city_id = c.city_id
           LEFT JOIN users u ON u.user_id = d.user_id
           WHERE d.user_id = $user_id AND d.approve = 1 LIMIT 1";
$doc_r  = mysqli_query($con, $doc_q);
$doctor = mysqli_fetch_assoc($doc_r);

if (!$doctor) { session_destroy(); header("Location: " . BASE_URL . "login"); exit(); }

$doctor_id = (int)$doctor['doctor_id'];

// ── FETCH CATEGORIES FOR SPECIALIZATION ────────────────────
$cats_q  = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type
            FROM dr_categories dc
            LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id
            ORDER BY dc.cat_name, dct.type";
$cats_r  = mysqli_query($con, $cats_q);
$cats_data = [];
while ($row = mysqli_fetch_assoc($cats_r)) {
    if (!isset($cats_data[$row['dr_cat_id']])) {
        $cats_data[$row['dr_cat_id']] = ['cat_name' => $row['cat_name'], 'types' => []];
    }
    if ($row['dr_cat_type_id']) {
        $cats_data[$row['dr_cat_id']]['types'][] = [
            'id' => $row['dr_cat_type_id'], 'type' => $row['type']
        ];
    }
}

// ── FETCH CITIES ────────────────────────────────────────────
$cities_r = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE status=1 ORDER BY city_name ASC");
$cities   = [];
while ($row = mysqli_fetch_assoc($cities_r)) $cities[] = $row;

// ── HANDLE UPDATE ──────────────────────────────────────────
$success_msg = $error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_name    = mysqli_real_escape_string($con, trim($_POST['doctor_name']    ?? ''));
    $doctor_phone   = mysqli_real_escape_string($con, trim($_POST['doctor_phone']   ?? ''));
    $doctor_email   = mysqli_real_escape_string($con, trim($_POST['doctor_email']   ?? ''));
    $gender         = mysqli_real_escape_string($con, trim($_POST['gender']         ?? ''));
    $city_id        = (int)($_POST['city_id'] ?? 0);
    $cat_type_id    = (int)($_POST['cat_type_id'] ?? 0);
    $experience     = (int)($_POST['experience_years'] ?? 0);
    $short_detail   = mysqli_real_escape_string($con, trim($_POST['short_detail']   ?? ''));
    $notes          = mysqli_real_escape_string($con, trim($_POST['notes']          ?? ''));
    $mahre_amraz    = mysqli_real_escape_string($con, trim($_POST['mahre_amraz']    ?? ''));

    $pic_clause = '';
    if (isset($_FILES['doctor_pic']) && $_FILES['doctor_pic']['error'] == 0) {
        $target_dir = BASE_PATH . "/admin/inc/uploads/doctors/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['doctor_pic']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $fname = time() . '_' . $user_id . '.' . $ext;
            if (move_uploaded_file($_FILES['doctor_pic']['tmp_name'], $target_dir . $fname)) {
                $pic_clause = ", doctor_pic = '$fname'";
            }
        }
    }

    $city_clause = $city_id > 0 ? ", city_id = $city_id" : '';
    $cat_clause  = $cat_type_id > 0 ? ", cat_type_id = $cat_type_id" : '';

    $upd = "UPDATE doctors SET
                doctor_name     = '$doctor_name',
                doctor_phone    = '$doctor_phone',
                doctor_email    = '$doctor_email',
                gender          = '$gender',
                experience_years= '$experience',
                short_detail    = '$short_detail',
                notes           = '$notes'
                $city_clause
                $cat_clause
                $pic_clause,
                updated_at      = NOW()
            WHERE doctor_id = $doctor_id";

    if (mysqli_query($con, $upd)) {
        // Update email in users table too
        if (!empty($doctor_email)) {
            mysqli_query($con, "UPDATE users SET email='$doctor_email' WHERE user_id=$user_id");
        }
        // Update treatment in dr_cat_types
        $used_cat_type = $cat_type_id > 0 ? $cat_type_id : (int)$doctor['cat_type_id'];
        if ($used_cat_type > 0 && $mahre_amraz !== '') {
            mysqli_query($con, "UPDATE dr_cat_types SET treatment='$mahre_amraz' WHERE dr_cat_type_id=$used_cat_type");
        }
        $success_msg = "Profile updated successfully!";
        $doc_r  = mysqli_query($con, $doc_q);
        $doctor = mysqli_fetch_assoc($doc_r);
    } else {
        $error_msg = "Error: " . mysqli_error($con);
    }
}

// ── HOSPITALS DOCTOR WORKS AT ──────────────────────────────
$hosp_q = "SELECT h.hospital_name, c.city_name, dih.inactive
           FROM doctor_in_hospital dih
           LEFT JOIN hospitals h ON dih.hospital_id = h.hospital_id
           LEFT JOIN cities c ON h.city_id = c.city_id
           WHERE dih.doctor_id = $doctor_id
           ORDER BY dih.inactive ASC, h.hospital_name ASC";
$hosp_r = mysqli_query($con, $hosp_q);
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
.hosp-chip { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:20px; font-size:0.8rem; font-weight:600; margin:4px; }
.hosp-chip.active   { background:rgba(39,201,106,0.12); color:#27C96A; border:1px solid rgba(39,201,106,0.25); }
.hosp-chip.inactive { background:#f1f5f9; color:#94a3b8; border:1px solid #e2e8f0; }
.image-preview { margin-top:12px; }
.image-preview img { width:90px; height:90px; border-radius:12px; object-fit:cover; border:2px solid rgba(0,168,204,0.2); }
</style>

<div class="content-wrapper">

    <!-- Profile Header -->
    <div class="prof-header">
        <?php if (!empty($doctor['doctor_pic'])): ?>
            <img src="<?= BASE_URL ?>admin/inc/uploads/doctors/<?= htmlspecialchars($doctor['doctor_pic']) ?>" class="prof-avatar" alt="">
        <?php else: ?>
            <div class="prof-avatar-placeholder"><i class="fas fa-user-md"></i></div>
        <?php endif; ?>
        <div class="prof-title">
            <h2>Dr. <?= htmlspecialchars($doctor['doctor_name']) ?></h2>
            <p>
                <i class="fas fa-stethoscope me-1"></i><?= htmlspecialchars($doctor['specialization'] ?? 'General') ?>
                <?php if (!empty($doctor['city_name'])): ?> &nbsp;|&nbsp; <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($doctor['city_name']) ?><?php endif; ?>
                <?php if (!empty($doctor['pmdc_no'])): ?> &nbsp;|&nbsp; <i class="fas fa-id-card me-1"></i>PMDC: <?= htmlspecialchars($doctor['pmdc_no']) ?><?php endif; ?>
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
            <h5><i class="fas fa-edit"></i> Edit My Profile</h5>
        </div>
        <div class="form-card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Doctor Name *</label>
                        <input type="text" class="form-control" name="doctor_name"
                               value="<?= htmlspecialchars($doctor['doctor_name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">PMDC No. <small style="text-transform:none; color:#94a3b8; font-weight:400;">(not editable)</small></label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($doctor['pmdc_no'] ?? '—') ?>" disabled
                               style="background:#f0f9ff; color:#64748b; cursor:not-allowed;">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-control" name="doctor_phone"
                               value="<?= htmlspecialchars($doctor['doctor_phone'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="doctor_email"
                               value="<?= htmlspecialchars($doctor['doctor_email'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gender *</label>
                        <select class="form-control" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male"   <?= ($doctor['gender']??'') === 'Male'   ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($doctor['gender']??'') === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other"  <?= ($doctor['gender']??'') === 'Other'  ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Experience (Years)</label>
                        <input type="number" class="form-control" name="experience_years" min="0" max="60"
                               value="<?= (int)($doctor['experience_years'] ?? 0) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <select class="form-control" name="city_id">
                            <option value="0">Select City</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?= $c['city_id'] ?>" <?= $doctor['city_id']==$c['city_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['city_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Specialization</label>
                        <select class="form-control" name="cat_type_id">
                            <option value="0">Select Specialization</option>
                            <?php foreach ($cats_data as $cat): ?>
                                <optgroup label="<?= htmlspecialchars($cat['cat_name']) ?>">
                                    <?php foreach ($cat['types'] as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= $doctor['cat_type_id']==$t['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['type']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" name="doctor_pic" accept="image/*">
                        <?php if (!empty($doctor['doctor_pic'])): ?>
                            <div class="image-preview">
                                <img src="<?= BASE_URL ?>admin/inc/uploads/doctors/<?= htmlspecialchars($doctor['doctor_pic']) ?>" alt="">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">ماہرِ امراض (Mahre Amraz)</label>
                        <input type="text" class="form-control" name="mahre_amraz"
                               value="<?= htmlspecialchars($doctor['mahre_amraz'] ?? '') ?>"
                               placeholder="ماہرِ قلب، ماہرِ اعصاب..." dir="rtl">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Qualifications / Short Detail</label>
                        <input type="text" class="form-control" name="short_detail"
                               value="<?= htmlspecialchars($doctor['short_detail'] ?? '') ?>"
                               placeholder="MBBS, FCPS etc.">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes / Special Offers</label>
                        <textarea class="form-control" name="notes" rows="3"
                                  placeholder="e.g. Free consultation, discount..."><?= htmlspecialchars($doctor['notes'] ?? '') ?></textarea>
                    </div>

                </div>
                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Profile</button>
                    <a href="<?= BASE_URL ?>doctor/index.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Hospitals -->
    <div class="form-card">
        <div class="form-card-header">
            <h5><i class="fas fa-hospital-alt"></i> My Hospitals</h5>
        </div>
        <div class="form-card-body">
            <?php if (mysqli_num_rows($hosp_r) > 0):
                while ($h = mysqli_fetch_assoc($hosp_r)): ?>
                <span class="hosp-chip <?= $h['inactive'] ? 'inactive' : 'active' ?>">
                    <i class="fas fa-hospital"></i>
                    <?= htmlspecialchars($h['hospital_name'] ?? 'Personal Clinic') ?>
                    <?php if (!empty($h['city_name'])): ?>
                        <span style="opacity:0.7;">— <?= htmlspecialchars($h['city_name']) ?></span>
                    <?php endif; ?>
                    <?= $h['inactive'] ? '<small>(Inactive)</small>' : '' ?>
                </span>
            <?php endwhile; else: ?>
                <p class="text-muted mb-0">Not assigned to any hospital yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php include BASE_PATH.'/admin/inc/footer.php'; ?>
