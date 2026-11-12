<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>

<?php
// ============================================================
// HOSPITAL AUTHENTICATION CHECK
// ============================================================
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$current_hospital_id = $hospital_data['hospital_id'];
$current_hospital_city_id = $hospital_data['city_id'];

// ============================================================
// SAFE EDIT MODE DETECTION (Optional: agar future me hospital ko edit ki zaroorat pade)
// ============================================================
$edit_mode = false;
$radiology_data = null;
$rad_id = 0;

if (!empty($_GET['id']) && is_numeric($_GET['id']) && (int)$_GET['id'] > 0) {
    $try_id = (int)$_GET['id'];
    $q = "SELECT radiology.*, u.status AS user_status, u.username, u.email
          FROM radiology
          LEFT JOIN users u ON u.user_id = radiology.user_id
          INNER JOIN assign_hospital_radiology ahr ON ahr.user_id = radiology.user_id
          WHERE radiology.rad_id = $try_id AND ahr.hospital_id = $current_hospital_id
          LIMIT 1";
    $r = mysqli_query($con, $q);
    if ($r && mysqli_num_rows($r) > 0) {
        $radiology_data = mysqli_fetch_assoc($r);
        $edit_mode = true;
        $rad_id = $try_id;
    }
}

// Cities list
$cities_result = mysqli_query($con, "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC");
$all_cities = [];
while ($c = mysqli_fetch_assoc($cities_result)) $all_cities[] = $c;

// ============================================================
// FORM SUBMIT
// ============================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $city_id     = mysqli_real_escape_string($con, $_POST['city_id'] ?? $current_hospital_city_id);
    $reg_no      = mysqli_real_escape_string($con, trim($_POST['reg_no'] ?? ''));
    $rad_name    = mysqli_real_escape_string($con, trim($_POST['rad_name'] ?? ''));
    $rad_address = mysqli_real_escape_string($con, $_POST['rad_address'] ?? '');
    $rad_phone   = mysqli_real_escape_string($con, trim($_POST['rad_phone'] ?? ''));
    $rad_email   = mysqli_real_escape_string($con, trim($_POST['rad_email'] ?? ''));
    $description = mysqli_real_escape_string($con, $_POST['description'] ?? '');
    $approve     = isset($_POST['approve']) ? 1 : 0;
    $status      = isset($_POST['status']) ? 1 : 0;

    $user_name    = mysqli_real_escape_string($con, trim($_POST['username'] ?? ''));
    $login_email  = mysqli_real_escape_string($con, trim($_POST['login_email'] ?? ''));
    $password_raw = $_POST['password'] ?? '';
    $user_type_id = 7; // Radiology User Type
    $created_at   = date('Y-m-d H:i:s');

    $user_id = $edit_mode ? (int)$radiology_data['user_id'] : 0;

    $exclude = $edit_mode ? " AND user_id != $user_id" : "";
    $chk = mysqli_query($con, "SELECT user_id FROM users WHERE username = '$user_name' $exclude LIMIT 1");

    $reg_dup = false;
    if ($reg_no !== '') {
        $reg_exclude = $edit_mode ? " AND rad_id != $rad_id" : "";
        $rq = mysqli_query($con, "SELECT rad_id FROM radiology WHERE reg_no = '$reg_no' $reg_exclude LIMIT 1");
        $reg_dup = ($rq && mysqli_num_rows($rq) > 0);
    }

    if ($user_name === '') {
        $error_msg = "Username is required.";
    } elseif ($chk && mysqli_num_rows($chk) > 0) {
        $error_msg = "Username already exists, please choose another.";
    } elseif (!$edit_mode && $password_raw === '') {
        $error_msg = "Password is required.";
    } elseif ($reg_dup) {
        $error_msg = "This registration number is already used by another radiology.";
    } elseif ($rad_name === '') {
        $error_msg = "Radiology name is required.";
    } elseif (empty($city_id)) {
        $error_msg = "City is required.";
    } else {

        $rad_pic = '';
        if (isset($_FILES['rad_pic']) && $_FILES['rad_pic']['error'] == 0) {
            $target_dir = BASE_PATH."/admin/inc/uploads/radiology/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

            $file_name = time() . '_' . basename($_FILES["rad_pic"]["name"]);
            $target_file = $target_dir . $file_name;
            $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                if (move_uploaded_file($_FILES["rad_pic"]["tmp_name"], $target_file)) {
                    $rad_pic = $file_name;
                }
            }
        }

        if ($edit_mode) {
            $up = "UPDATE radiology SET
                        city_id = '$city_id',
                        reg_no = '$reg_no',
                        rad_name = '$rad_name',
                        rad_address = '$rad_address',
                        rad_phone = '$rad_phone',
                        rad_email = '$rad_email',
                        description = '$description',
                        approve = '$approve',
                        updated_at = NOW()";
            if (!empty($rad_pic)) $up .= ", rad_pic = '$rad_pic'";
            $up .= " WHERE rad_id = $rad_id";

            if (mysqli_query($con, $up)) {
                $uu = "UPDATE users SET username = '$user_name', email = '$login_email', status = '$status'";
                if ($password_raw !== '') {
                    $pw = mysqli_real_escape_string($con, base64_encode($password_raw));
                    $uu .= ", password = '$pw'";
                }
                $uu .= " WHERE user_id = $user_id";
                mysqli_query($con, $uu);

                $success_msg = "Radiology updated successfully!";

                $rr = mysqli_query($con, "SELECT radiology.*, u.status AS user_status, u.username, u.email
                                          FROM radiology LEFT JOIN users u ON u.user_id = radiology.user_id
                                          WHERE radiology.rad_id = $rad_id LIMIT 1");
                $radiology_data = mysqli_fetch_assoc($rr);
            } else {
                $error_msg = "Error: " . mysqli_error($con);
            }

        } else {
            $pw = mysqli_real_escape_string($con, base64_encode($password_raw));

            // 1. Create User
            $ins_user = "INSERT INTO users (username, email, password, user_type_id, status, created_at)
                         VALUES ('$user_name', '$login_email', '$pw', $user_type_id, $status, '$created_at')";

            if (mysqli_query($con, $ins_user)) {
                $new_user_id = mysqli_insert_id($con);

                // 2. Create Radiology
                $ins = "INSERT INTO radiology
                          (reg_no, city_id, user_id, rad_name, rad_address, rad_phone, rad_email, rad_pic, approve, description, created_at)
                        VALUES
                          ('$reg_no', '$city_id', $new_user_id, '$rad_name', '$rad_address', '$rad_phone', '$rad_email','$rad_pic', '$approve', '$description', NOW())";

                if (mysqli_query($con, $ins)) {
                    
                    // 3. Assign to this Hospital automatically
                    $new_rad_id = mysqli_insert_id($con);
                    $assign_query = "INSERT INTO assign_hospital_radiology (user_id, hospital_id, status, created_at) 
                                     VALUES ($new_user_id, $current_hospital_id, 1, NOW())";
                    mysqli_query($con, $assign_query);

                    $success_msg = "Radiology added and registered to your hospital successfully!";
                } else {
                    $error_msg = "Error: " . mysqli_error($con);
                    // Rollback user if radiology fails
                    mysqli_query($con, "DELETE FROM users WHERE user_id = $new_user_id");
                }
            } else {
                $error_msg = "Error creating user: " . mysqli_error($con);
            }
        }
    }
}

function fv($key, $default = '') {
    return htmlspecialchars($_POST[$key] ?? $default);
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<?php include BASE_PATH.'/admin/inc/top.php';?>
<?php include BASE_PATH.'/hospital/inc/nav.php';?>

<style>
:root {
    --brand-navy: #1C3F8F;
    --brand-teal: #00A8CC;
    --brand-green: #27C96A;
    --brand-dark: #0D2B6E;
    --brand-bg: #F5F8FC;
}
.rad-page-wrap { padding: 10px 0 40px; }

.rad-header {
    background: linear-gradient(120deg, var(--brand-dark) 0%, var(--brand-navy) 55%, var(--brand-teal) 100%);
    color: #fff; padding: 22px 28px;
    border-radius: 14px; margin-bottom: 22px;
    box-shadow: 0 6px 22px rgba(28,63,143,0.25);
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
}
.rad-header h4 { margin: 0; color: #fff; font-weight: 700; letter-spacing: 0.3px; }
.rad-header p  { margin: 4px 0 0; color: rgba(255,255,255,0.8); font-size: 13px; }

.rad-card {
    background: #fff; border-radius: 14px;
    border: 1px solid #e7eef7;
    box-shadow: 0 2px 10px rgba(13,43,110,0.05);
    margin-bottom: 22px; overflow: hidden;
}
.rad-card-header {
    background: var(--brand-bg);
    border-bottom: 1px solid #e7eef7;
    padding: 14px 22px;
    display: flex; align-items: center; gap: 10px;
}
.rad-card-header i { color: var(--brand-teal); font-size: 18px; }
.rad-card-header h5 { margin: 0; font-weight: 700; color: var(--brand-dark); font-size: 15px; }
.rad-card-body { padding: 22px; }

.rad-form-label {
    font-size: 13px; font-weight: 600; color: var(--brand-dark);
    margin-bottom: 6px; display: block;
}
.rad-form-label .req { color: #ef4444; }
.rad-form-control {
    border-radius: 9px !important;
    border: 1.5px solid #dbe6f4 !important;
    padding: 10px 14px !important;
    font-size: 14px;
    transition: all .2s ease;
    background: #fff;
    width: 100%;
}
.rad-form-control:focus {
    border-color: var(--brand-teal) !important;
    box-shadow: 0 0 0 3px rgba(0,168,204,0.12) !important;
    outline: none;
}

.rad-btn {
    background: linear-gradient(135deg, var(--brand-teal), var(--brand-green));
    color: #fff !important; border: none;
    padding: 11px 26px; border-radius: 10px;
    font-weight: 700; font-size: 14px;
    box-shadow: 0 4px 14px rgba(0,168,204,0.3);
    transition: all .2s ease; cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px;
}
.rad-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,168,204,0.4); }
.rad-btn:disabled { background: #cbd5e1 !important; box-shadow: none !important; cursor: not-allowed !important; transform: none !important; opacity: 0.7; }

.rad-btn-secondary {
    background: #fff; color: var(--brand-dark) !important;
    border: 1.5px solid #dbe6f4;
    padding: 10px 24px; border-radius: 10px;
    font-weight: 600; font-size: 14px;
    text-decoration: none !important;
    display: inline-flex; align-items: center; gap: 8px;
    transition: all .2s ease;
}
.rad-btn-secondary:hover { border-color: var(--brand-teal); color: var(--brand-teal) !important; }

.city-search-wrap { position: relative; }
.city-search-input {
    width: 100%;
    border-radius: 9px;
    border: 1.5px solid #dbe6f4;
    padding: 10px 14px;
    font-size: 14px;
    transition: all .2s ease;
    background: #fff;
}
.city-search-input:focus {
    border-color: var(--brand-teal);
    box-shadow: 0 0 0 3px rgba(0,168,204,0.12);
    outline: none;
}
.city-dropdown {
    position: absolute; top: 100%; left: 0; right: 0;
    background: #fff;
    border: 1.5px solid #dbe6f4;
    border-radius: 10px;
    max-height: 250px; overflow-y: auto;
    z-index: 50; margin-top: 6px;
    box-shadow: 0 8px 24px rgba(13,43,110,0.15);
    display: none;
}
.city-dropdown.open { display: block; }
.city-option {
    padding: 10px 14px; cursor: pointer; font-size: 14px;
    border-bottom: 1px solid #f0f4f9;
    transition: all .15s ease;
}
.city-option:last-child { border-bottom: none; }
.city-option:hover, .city-option.active {
    background: linear-gradient(90deg, rgba(0,168,204,0.1), rgba(39,201,106,0.05));
    color: var(--brand-dark); font-weight: 600;
}
.city-option.no-match { color: #999; cursor: default; }
.selected-city-tag {
    display: inline-flex; align-items: center; gap: 8px;
    background: linear-gradient(135deg, var(--brand-teal), var(--brand-green));
    color: #fff; padding: 5px 12px;
    border-radius: 20px; font-size: 12px;
    font-weight: 600; margin-top: 8px;
}
.selected-city-tag button {
    background: rgba(255,255,255,0.25);
    border: none; color: #fff;
    width: 18px; height: 18px;
    border-radius: 50%; cursor: pointer;
    font-size: 11px; line-height: 1;
    display: inline-flex; align-items: center; justify-content: center;
}

.rad-file-upload {
    border: 2px dashed #dbe6f4;
    border-radius: 12px; padding: 22px;
    text-align: center; cursor: pointer;
    transition: all .2s ease; background: #fafcff;
}
.rad-file-upload:hover { border-color: var(--brand-teal); background: rgba(0,168,204,0.03); }
.rad-file-upload i { font-size: 30px; color: var(--brand-teal); margin-bottom: 6px; display: block; }
.rad-file-upload small { color: #8894a8; }

.rad-switch {
    display: inline-flex; align-items: center; gap: 10px;
    cursor: pointer; user-select: none;
}
.rad-switch input { display: none; }
.rad-switch .sw {
    width: 42px; height: 24px; background: #dbe6f4;
    border-radius: 20px; position: relative;
    transition: all .25s ease;
}
.rad-switch .sw::after {
    content: ''; position: absolute; top: 3px; left: 3px;
    width: 18px; height: 18px; background: #fff;
    border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    transition: all .25s ease;
}
.rad-switch input:checked + .sw {
    background: linear-gradient(135deg, var(--brand-teal), var(--brand-green));
}
.rad-switch input:checked + .sw::after { left: 21px; }
.rad-switch span.lbl { font-size: 14px; font-weight: 600; color: var(--brand-dark); }

.rad-alert { border-radius: 10px; padding: 13px 18px; font-weight: 600; border: none; margin-bottom: 20px; }
.rad-alert-success { background: #e8f9f0; color: #0d7a45; border-left: 4px solid var(--brand-green); }
.rad-alert-danger  { background: #fee9e9; color: #b91c1c; border-left: 4px solid #ef4444; }

.dup-msg { display: none; margin-top: 4px; font-size: 12px; font-weight: 600; color: #ef4444; }
</style>

<div class="content-wrapper">
   <div class="container-fluid">
      <div class="rad-page-wrap">

         <div class="rad-header">
            <div>
               <h4>
                  <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus-circle'; ?>"></i>
                  <?php echo $edit_mode ? 'Edit Radiology Center' : 'Add New Radiology Center'; ?>
               </h4>
               <p><?php echo $edit_mode ? 'Update existing radiology details' : 'Register a new radiology center for your hospital'; ?></p>
            </div>
            <a href="<?php echo BASE_URL; ?>hospital/radiology" class="rad-btn-secondary">
               <i class="fas fa-arrow-left"></i> Back to List
            </a>
         </div>

         <?php if (isset($success_msg)): ?>
            <div class="rad-alert rad-alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                <br><br><a href="<?= BASE_URL ?>hospital/radiology" class="rad-btn" style="padding:6px 15px; font-size:12px;">Go to List</a>
            </div>
         <?php endif; ?>
         <?php if (isset($error_msg)): ?>
            <div class="rad-alert rad-alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div>
         <?php endif; ?>

         <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" id="exclude_rad_id"  value="<?php echo $edit_mode ? (int)$rad_id : 0; ?>">
            <input type="hidden" id="exclude_user_id" value="<?php echo $edit_mode ? (int)$radiology_data['user_id'] : 0; ?>">

            <!-- LOGIN DETAILS -->
            <div class="rad-card">
               <div class="rad-card-header">
                  <i class="fas fa-lock"></i>
                  <h5>Login Details</h5>
               </div>
               <div class="rad-card-body">
                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group mb-3">
                           <label class="rad-form-label">Username <span class="req">*</span></label>
                           <input type="text" class="form-control rad-form-control" id="username_input" name="username" required autocomplete="off"
                                  value="<?php echo fv('username', $edit_mode ? ($radiology_data['username'] ?? '') : ''); ?>">
                           <small class="dup-msg" id="username_msg"></small>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group mb-3">
                           <label class="rad-form-label">Email (for login)</label>
                           <input type="email" class="form-control rad-form-control" name="login_email" autocomplete="off"
                                  value="<?php echo fv('login_email', $edit_mode ? ($radiology_data['email'] ?? '') : ''); ?>">
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group mb-3">
                           <label class="rad-form-label">Password <?php echo $edit_mode ? '' : '<span class="req">*</span>'; ?></label>
                           <input type="password" class="form-control rad-form-control" name="password" autocomplete="new-password"
                                  placeholder="<?php echo $edit_mode ? 'Leave blank to keep current' : 'Enter password'; ?>"
                                  <?php echo $edit_mode ? '' : 'required'; ?>>
                        </div>
                     </div>
                  </div>
                  <label class="rad-switch mt-2">
                     <input type="checkbox" name="status" value="1"
                        <?php echo (!$edit_mode || (($radiology_data['user_status'] ?? 0) == 1)) ? 'checked' : ''; ?>>
                     <span class="sw"></span>
                     <span class="lbl">Active User Status</span>
                  </label>
               </div>
            </div>

            <!-- RADIOLOGY INFO -->
            <div class="rad-card">
               <div class="rad-card-header">
                  <i class="fas fa-camera"></i>
                  <h5>Radiology Information</h5>
               </div>
               <div class="rad-card-body">

                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group mb-3">
                           <label class="rad-form-label">Registration No</label>
                           <input type="text" class="form-control rad-form-control" id="reg_no_input" name="reg_no" placeholder="e.g. RAD-2024-001"
                                  value="<?php echo fv('reg_no', $edit_mode ? ($radiology_data['reg_no'] ?? '') : ''); ?>">
                           <small class="dup-msg" id="reg_no_msg"></small>
                        </div>
                     </div>

                     <div class="col-md-4">
                        <div class="form-group mb-3">
                           <label class="rad-form-label">City <span class="req">*</span></label>
                           <?php
                           $sel_city = $_POST['city_id'] ?? ($edit_mode ? $radiology_data['city_id'] : $current_hospital_city_id);
                           $sel_city_name = '';
                           foreach ($all_cities as $c) {
                               if ($sel_city == $c['city_id']) { $sel_city_name = $c['city_name']; break; }
                           }
                           ?>
                           <div class="city-search-wrap" id="cityWrap">
                              <input type="hidden" name="city_id" id="city_id_input" value="<?php echo htmlspecialchars($sel_city); ?>">
                              <input type="text" class="city-search-input" id="city_search" autocomplete="off"
                                     placeholder="Search city..."
                                     value="<?php echo htmlspecialchars($sel_city_name); ?>">
                              <div class="city-dropdown" id="city_dropdown">
                                 <?php foreach ($all_cities as $c): ?>
                                    <div class="city-option" data-id="<?php echo $c['city_id']; ?>" data-name="<?php echo htmlspecialchars($c['city_name']); ?>">
                                       <?php echo htmlspecialchars($c['city_name']); ?>
                                    </div>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                           <div class="selected-city-tag" id="selCityTag" style="<?php echo $sel_city_name ? '' : 'display:none;'; ?>">
                              <i class="fas fa-map-marker-alt"></i>
                              <span><?php echo htmlspecialchars($sel_city_name); ?></span>
                              <button type="button" id="clearCity">×</button>
                           </div>
                        </div>
                     </div>

                  </div>

                  <div class="form-group mb-3">
                     <label class="rad-form-label">Radiology Center Name <span class="req">*</span></label>
                     <input type="text" class="form-control rad-form-control" name="rad_name" required
                            value="<?php echo fv('rad_name', $edit_mode ? ($radiology_data['rad_name'] ?? '') : ''); ?>">
                  </div>

                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group mb-3">
                           <label class="rad-form-label">Phone</label>
                           <input type="text" class="form-control rad-form-control" name="rad_phone" placeholder="Optional"
                                  value="<?php echo fv('rad_phone', $edit_mode ? ($radiology_data['rad_phone'] ?? '') : ''); ?>">
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group mb-3">
                           <label class="rad-form-label">Public Email</label>
                           <input type="email" class="form-control rad-form-control" name="rad_email" placeholder="Optional"
                                  value="<?php echo fv('rad_email', $edit_mode ? ($radiology_data['rad_email'] ?? '') : ''); ?>">
                        </div>
                     </div>
                  </div>

                  <div class="form-group mb-3">
                     <label class="rad-form-label">Address</label>
                     <textarea class="form-control rad-form-control" name="rad_address" rows="3" placeholder="Optional"><?php echo fv('rad_address', $edit_mode ? ($radiology_data['rad_address'] ?? '') : ''); ?></textarea>
                  </div>

                  <div class="form-group mb-3">
                     <label class="rad-form-label">Description</label>
                     <textarea class="form-control rad-form-control" name="description" rows="3" placeholder="Optional"><?php echo fv('description', $edit_mode ? ($radiology_data['description'] ?? '') : ''); ?></textarea>
                  </div>

                  <div class="form-group mb-3">
                     <label class="rad-form-label">Radiology Picture</label>
                     <div class="rad-file-upload" onclick="document.getElementById('radPicInput').click();">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div style="font-weight:600;color:var(--brand-dark);">Click to upload image</div>
                        <small>JPG, PNG or GIF — max 5MB</small>
                        <input type="file" id="radPicInput" name="rad_pic" accept="image/*" style="display:none;">
                     </div>
                     <div id="picPreviewWrap" style="margin-top:12px;<?php echo ($edit_mode && !empty($radiology_data['rad_pic'])) ? '' : 'display:none;'; ?>">
                        <img id="picPreview"
                             src="<?php echo ($edit_mode && !empty($radiology_data['rad_pic'])) ? BASE_URL.'admin/inc/uploads/radiology/'.htmlspecialchars($radiology_data['rad_pic']) : ''; ?>"
                             style="width:80px;height:80px;object-fit:cover;border-radius:10px;border:2px solid var(--brand-teal);">
                     </div>
                  </div>

                  <label class="rad-switch mt-2">
                     <input type="checkbox" name="approve" value="1"
                        <?php echo (!$edit_mode || (($radiology_data['approve'] ?? 0) == 1)) ? 'checked' : ''; ?>>
                     <span class="sw"></span>
                     <span class="lbl">Approved</span>
                  </label>

               </div>
            </div>

            <div class="mb-4">
               <button type="submit" id="submitBtn" class="rad-btn">
                  <i class="fas fa-check"></i>
                  <?php echo $edit_mode ? 'Update Radiology' : 'Save & Register'; ?>
               </button>
               <a href="<?php echo BASE_URL; ?>hospital/radiology" class="rad-btn-secondary">Cancel</a>
            </div>

         </form>

      </div>
   </div>
</div>

<script>
(function() {
    // SEARCHABLE CITY DROPDOWN
    const wrap = document.getElementById('cityWrap');
    if (wrap) {
        const search   = document.getElementById('city_search');
        const dropdown = document.getElementById('city_dropdown');
        const hiddenId = document.getElementById('city_id_input');
        const tag      = document.getElementById('selCityTag');
        const tagSpan  = tag.querySelector('span');
        const clearBtn = document.getElementById('clearCity');
        const options  = Array.from(dropdown.querySelectorAll('.city-option'));

        const showDropdown = () => dropdown.classList.add('open');
        const hideDropdown = () => dropdown.classList.remove('open');

        const filterOptions = (q) => {
            q = q.toLowerCase().trim();
            let visibleCount = 0;
            options.forEach(opt => {
                const match = opt.dataset.name.toLowerCase().includes(q);
                opt.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            let noMatch = dropdown.querySelector('.no-match');
            if (visibleCount === 0) {
                if (!noMatch) {
                    noMatch = document.createElement('div');
                    noMatch.className = 'city-option no-match';
                    noMatch.textContent = 'No city found';
                    dropdown.appendChild(noMatch);
                }
            } else if (noMatch) noMatch.remove();
        };
        const selectCity = (id, name) => {
            hiddenId.value = id;
            search.value = name;
            tagSpan.textContent = name;
            tag.style.display = 'inline-flex';
            hideDropdown();
        };

        search.addEventListener('focus', () => { filterOptions(search.value); showDropdown(); });
        search.addEventListener('input', () => {
            hiddenId.value = '';
            tag.style.display = 'none';
            filterOptions(search.value);
            showDropdown();
        });
        options.forEach(opt => {
            opt.addEventListener('mousedown', (e) => {
                e.preventDefault();
                selectCity(opt.dataset.id, opt.dataset.name);
            });
        });
        clearBtn?.addEventListener('click', () => {
            hiddenId.value = '';
            search.value = '';
            tag.style.display = 'none';
            search.focus();
        });
        document.addEventListener('click', (e) => {
            if (!wrap.contains(e.target) && !dropdown.contains(e.target)) hideDropdown();
        });
        if (hiddenId.value) filterOptions('');
    }

    // IMAGE PREVIEW
    const fileInput   = document.getElementById('radPicInput');
    const previewWrap = document.getElementById('picPreviewWrap');
    const preview     = document.getElementById('picPreview');
    fileInput?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                previewWrap.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // LIVE DUPLICATE CHECK
    const submitBtn     = document.getElementById('submitBtn');
    const regInput      = document.getElementById('reg_no_input');
    const userInput     = document.getElementById('username_input');
    const regMsg        = document.getElementById('reg_no_msg');
    const userMsg       = document.getElementById('username_msg');
    const excludeRadId  = document.getElementById('exclude_rad_id')?.value || 0;
    const excludeUserId = document.getElementById('exclude_user_id')?.value || 0;

    const dupState = { reg_no: false, username: false };

    function updateSubmitState() {
        const anyDup = dupState.reg_no || dupState.username;
        if (submitBtn) submitBtn.disabled = anyDup;
    }

    function markDup(type, exists, input, msgEl, label) {
        dupState[type] = exists;
        if (input) {
            input.style.borderColor = exists ? '#ef4444' : '';
            input.style.boxShadow   = exists ? '0 0 0 3px rgba(239,68,68,0.12)' : '';
        }
        if (msgEl) {
            if (exists) {
                msgEl.textContent = '⚠️ This ' + label + ' is already taken.';
                msgEl.style.display = 'block';
            } else {
                msgEl.style.display = 'none';
            }
        }
        updateSubmitState();
    }

    let regTimer = null, userTimer = null;

    function checkDuplicate(type, value, excludeId, input, msgEl, label) {
        if (!value.trim()) { markDup(type, false, input, msgEl, label); return; }
        const url = '<?php echo BASE_URL; ?>admin/check_duplicate.php?type=' + encodeURIComponent(type)
                    + '&value=' + encodeURIComponent(value.trim())
                    + '&exclude_id=' + excludeId;
        fetch(url)
            .then(r => r.json())
            .then(data => markDup(type, !!data.exists, input, msgEl, label))
            .catch(() => markDup(type, false, input, msgEl, label));
    }

    regInput?.addEventListener('keyup', () => {
        clearTimeout(regTimer);
        regTimer = setTimeout(() => {
            checkDuplicate('reg_no', regInput.value, excludeRadId, regInput, regMsg, 'registration number');
        }, 400);
    });

    userInput?.addEventListener('keyup', () => {
        clearTimeout(userTimer);
        userTimer = setTimeout(() => {
            checkDuplicate('username', userInput.value, excludeUserId, userInput, userMsg, 'username');
        }, 400);
    });

    if (regInput?.value?.trim())  checkDuplicate('reg_no',   regInput.value,  excludeRadId,  regInput,  regMsg,  'registration number');
    if (userInput?.value?.trim()) checkDuplicate('username', userInput.value, excludeUserId, userInput, userMsg, 'username');
})();
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>