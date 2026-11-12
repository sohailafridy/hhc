<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>

<?php
// Hospital Authentication Check
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
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<?php include BASE_PATH.'/admin/inc/top.php';?>
<?php include BASE_PATH.'/hospital/inc/nav.php';?>

<?php
// Get radiology ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'hospital/radiology');
    exit();
}

$rad_id = (int)$_GET['id'];
$success_msg = '';
$error_msg = '';

// ── HANDLE UPDATE FORM SUBMISSION ──
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_radiology'])) {
    
    $rad_name    = mysqli_real_escape_string($con, $_POST['rad_name']);
    $rad_phone   = mysqli_real_escape_string($con, $_POST['rad_phone']);
    $rad_email   = mysqli_real_escape_string($con, $_POST['rad_email']);
    $rad_address = mysqli_real_escape_string($con, $_POST['rad_address']);
    $status      = isset($_POST['status']) ? 1 : 0;
    
    // Update radiology table
    $update_rad = "UPDATE radiology SET 
                    rad_name = '$rad_name',
                    rad_phone = '$rad_phone',
                    rad_email = '$rad_email',
                    rad_address = '$rad_address',
                    updated_at = NOW()
                   WHERE rad_id = $rad_id";
    
    if (mysqli_query($con, $update_rad)) {
        $rad_user_id = (int)$_POST['user_id'];
        mysqli_query($con, "UPDATE users SET status = '$status' WHERE user_id = $rad_user_id");
        
        $success_msg = "Radiology details updated successfully!";
    } else {
        $error_msg = "Error updating radiology: " . mysqli_error($con);
    }
}

// ── FETCH DATA ──
$query = "SELECT r.*, c.city_name, h.hospital_name, u.status as estatus, u.user_id as u_id,u.username
          FROM radiology r 
          LEFT JOIN cities c ON r.city_id = c.city_id
          LEFT JOIN assign_hospital_radiology ahr ON r.user_id = ahr.user_id
          LEFT JOIN hospitals h ON ahr.hospital_id = h.hospital_id
          LEFT JOIN users u ON u.user_id = r.user_id
          WHERE r.rad_id = $rad_id AND ahr.hospital_id = $current_hospital_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'hospital/radiology');
    exit();
}

$radiology = mysqli_fetch_assoc($result);
$user_id_rad = (int)$radiology['user_id'];

// Fetch feedbacks
$feedback_query = "SELECT f.* FROM feedback f WHERE f.user_id = $user_id_rad AND f.status = 1 ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);

// Fetch ONLY ASSIGNED TESTS (Clean public view)
$tests_query = "SELECT t.test_id, t.test_name, c.category_name, ct.price AS clinic_price
                FROM tbl_radiology_clinic_tests ct
                INNER JOIN tbl_radiology_tests t ON ct.test_id = t.test_id
                INNER JOIN tbl_radiology_categories c ON ct.category_id = c.category_id
                WHERE ct.clinic_id = $rad_id AND ct.status = 1 AND t.status = 1 AND c.status = 1
                ORDER BY c.category_name ASC, t.test_name ASC";
$tests_result = mysqli_query($con, $tests_query);

// Group by category
$grouped_tests = [];
if ($tests_result && mysqli_num_rows($tests_result) > 0) {
    while($row = mysqli_fetch_assoc($tests_result)) {
        $grouped_tests[$row['category_name']][] = $row;
    }
}
?>

<style>
/* =========================================
   MODERN RESPONSIVE DESIGN FOR RADIOLOGY
   ========================================= */
:root {
    --rad-navy: #0D2B6E;
    --rad-teal: #00A8CC;
    --rad-green: #27C96A;
    --rad-bg: #f8fafc;
}

.rad-profile-header {
    background: linear-gradient(135deg, #071124 0%, #0D2B6E 60%, #00A8CC 100%);
    border-radius: 16px;
    padding: 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(13,43,110,0.2);
    display: flex;
    align-items: center;
    gap: 24px;
}

.rad-avatar-wrap {
    flex-shrink: 0;
    width: 110px;
    height: 110px;
    border-radius: 20px;
    background: rgba(255,255,255,0.15);
    border: 3px solid rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.rad-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
.rad-avatar-wrap i { font-size: 3rem; color: #fff; }

.rad-header-info { flex: 1; }
.rad-header-info h2 { margin: 0 0 8px; font-weight: 800; font-size: 1.8rem; color: #fff; }
.rad-header-meta { display: flex; flex-wrap: wrap; gap: 15px; font-size: 0.9rem; color: rgba(255,255,255,0.85); }
.rad-header-meta span { display: flex; align-items: center; gap: 6px; }

.btn-edit-modern {
    background: #fff; color: var(--rad-navy);
    border: none; padding: 10px 24px; border-radius: 50px;
    font-weight: 700; font-size: 0.9rem; cursor: pointer;
    transition: all 0.2s; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: inline-flex; align-items: center; gap: 8px;
}
.btn-edit-modern:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }

/* Cards */
.rad-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    margin-bottom: 24px;
    overflow: hidden;
}
.rad-card-header {
    background: #f8fafc;
    padding: 16px 24px;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 700; color: var(--rad-navy);
    display: flex; align-items: center; gap: 10px;
}
.rad-card-header i { color: var(--rad-teal); font-size: 1.1rem; }
.rad-card-body { padding: 24px; }

/* Forms */
.rad-form-label { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: block; }
.rad-input {
    width: 100%; padding: 10px 14px; border-radius: 10px;
    border: 1.5px solid #e2e8f0; font-size: 0.95rem; color: var(--rad-navy);
    background: #fff; transition: all 0.2s;
}
.rad-input:focus { border-color: var(--rad-teal); outline: none; box-shadow: 0 0 0 3px rgba(0,168,204,0.1); }
.rad-input:disabled, .rad-input[readonly] { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; border-color: #e2e8f0; }
textarea.rad-input { resize: vertical; min-height: 100px; }

/* Actions */
.rad-actions-bar {
    display: flex; gap: 12px; justify-content: flex-end;
    margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;
}
.btn-save {
    background: linear-gradient(135deg, var(--rad-teal), var(--rad-green));
    color: #fff; border: none; padding: 12px 28px; border-radius: 10px;
    font-weight: 700; font-size: 0.95rem; cursor: pointer;
    box-shadow: 0 4px 15px rgba(39,201,106,0.3);
}
.btn-save:hover { transform: translateY(-2px); color: #fff; }
.btn-cancel {
    background: #fff; color: #64748b; border: 1.5px solid #cbd5e1;
    padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 0.95rem; cursor: pointer;
}
.btn-cancel:hover { background: #f1f5f9; }

/* Category & Tests View */
.rad-cat-title {
    font-weight: 800; color: var(--rad-navy); font-size: 1rem;
    margin: 20px 0 12px; padding-bottom: 8px;
    border-bottom: 2px dashed #e2e8f0; display: flex; align-items: center; gap: 8px;
}
.rad-cat-title i { color: var(--rad-teal); }

/* Feedbacks */
.feedback-item { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; }
.feedback-item:last-child { border-bottom: none; }
.feedback-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
.feedback-name { font-weight: 700; color: var(--rad-navy); font-size: 0.95rem; }
.feedback-stars i { color: #f59e0b; font-size: 0.85rem; }
.feedback-comment { font-size: 0.9rem; color: #475569; line-height: 1.6; margin-bottom: 10px; }
.feedback-date { font-size: 0.75rem; color: #94a3b8; }

/* Responsive Overrides */
@media (max-width: 768px) {
    .rad-profile-header { flex-direction: column; text-align: center; padding: 24px; gap: 16px; }
    .rad-header-meta { justify-content: center; }
    .rad-header-info h2 { font-size: 1.5rem; }
    .btn-edit-modern { width: 100%; justify-content: center; margin-top: 10px; }
    .rad-actions-bar { flex-direction: column; }
    .btn-save, .btn-cancel { width: 100%; }
}
</style>

<div class="content-wrapper" style="background:#f1f5f9; min-height:100vh; padding: 24px;">
   <div class="container-fluid p-0">

      <!-- Header -->
      <div class="rad-profile-header">
         <div class="rad-avatar-wrap">
             <?php if (!empty($radiology['rad_pic'])): ?>
                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/radiology/<?php echo $radiology['rad_pic']; ?>" alt="Logo">
             <?php else: ?>
                <i class="fas fa-camera"></i>
             <?php endif; ?>
         </div>
         <div class="rad-header-info">
             <h2><?php echo htmlspecialchars($radiology['rad_name']); ?></h2>
             <div class="rad-header-meta">
                 <?php if (!empty($radiology['city_name'])): ?>
                     <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($radiology['city_name']); ?></span>
                 <?php endif; ?>
                 <?php if (!empty($radiology['rad_phone'])): ?>
                     <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($radiology['rad_phone']); ?></span>
                 <?php endif; ?>
                 <?php if (!empty($radiology['reg_no'])): ?>
                     <span><i class="fas fa-id-card"></i>REG-NO: <?php echo htmlspecialchars($radiology['reg_no']); ?></span>
                     <span><i class="fas fa-id-card"></i>User: <?php echo htmlspecialchars($radiology['username']); ?></span>
                 <?php endif; ?>
             </div>
         </div>
         <div>
             <button type="button" class="btn-edit-modern" id="editBtn" onclick="toggleEditMode()" style="display:none;">
                 <i class="fas fa-edit"></i> Edit Details
             </button>
         </div>
      </div>

      <!-- Alerts -->
      <?php if ($success_msg): ?>
          <div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;">
              <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
      <?php endif; ?>
      <?php if ($error_msg): ?>
          <div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;">
              <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
      <?php endif; ?>

      <!-- Main Form -->
      <form method="POST" id="radForm" class="view-mode">
          <input type="hidden" name="update_radiology" value="1">
          <input type="hidden" name="user_id" value="<?php echo $user_id_rad; ?>">

          <div class="row">
             <!-- Basic Info -->
             <div class="col-lg-6">
                <div class="rad-card h-100">
                   <div class="rad-card-header"><i class="fas fa-info-circle"></i> Basic Information</div>
                   <div class="rad-card-body">
                      <div class="mb-3">
                          <label class="rad-form-label">Center Name</label>
                          <input type="text" class="rad-input" name="rad_name" value="<?php echo htmlspecialchars($radiology['rad_name']); ?>" readonly required>
                      </div>
                      <div class="mb-3">
                          <label class="rad-form-label">Phone</label>
                          <input type="text" class="rad-input" name="rad_phone" value="<?php echo htmlspecialchars($radiology['rad_phone']); ?>" readonly>
                      </div>
                      <div class="mb-3">
                          <label class="rad-form-label">Email</label>
                          <input type="email" class="rad-input" name="rad_email" value="<?php echo htmlspecialchars($radiology['rad_email']); ?>" readonly>
                      </div>
                      <div class="mb-0">
                          <label class="rad-form-label">Active Status</label>
                          <div class="form-check form-switch mt-2">
                              <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" value="1" <?php echo ($radiology['estatus'] == 1) ? 'checked' : ''; ?> disabled>
                              <label class="form-check-label ms-2 fw-bold text-dark" for="statusSwitch">User Account Active</label>
                          </div>
                      </div>
                   </div>
                </div>
             </div>
             
             <!-- Location -->
             <div class="col-lg-6">
                <div class="rad-card h-100">
                   <div class="rad-card-header"><i class="fas fa-map-marker-alt"></i> Location Details</div>
                   <div class="rad-card-body">
                      <div class="mb-3">
                          <label class="rad-form-label">City</label>
                          <input type="text" class="rad-input" value="<?php echo htmlspecialchars($radiology['city_name']); ?>" readonly>
                      </div>
                      <div class="mb-3">
                          <label class="rad-form-label">Assigned Hospital</label>
                          <input type="text" class="rad-input" value="<?php echo htmlspecialchars($radiology['hospital_name']); ?>" readonly>
                      </div>
                      <div class="mb-3">
                          <label class="rad-form-label">Address</label>
                          <textarea class="rad-input" name="rad_address" rows="3" readonly><?php echo htmlspecialchars($radiology['rad_address']); ?></textarea>
                      </div>
                      <div class="mb-0">
                          <label class="rad-form-label">Registration No</label>
                          <input type="text" class="rad-input" value="<?php echo htmlspecialchars($radiology['reg_no']); ?>" readonly>
                      </div>
                   </div>
                </div>
             </div>
          </div>

          <!-- Tests (Public View Style - No Inputs) -->
          <div class="row mt-4">
             <div class="col-12">
                <div class="rad-card">
                   <div class="rad-card-header"><i class="fas fa-vials"></i> Available Tests & Services</div>
                   <div class="rad-card-body">
                      <?php if (!empty($grouped_tests)): ?>
                          <?php foreach($grouped_tests as $cat_name => $tests): ?>
                              <div class="rad-cat-title"><i class="fas fa-folder-open"></i> <?php echo htmlspecialchars($cat_name); ?></div>
                              
                              <div class="table-responsive">
                                  <table class="table table-borderless align-middle mb-4" style="background:#f8fafc; border-radius:12px; overflow:hidden;">
                                      <thead style="background:#f1f5f9;">
                                          <tr>
                                              <th style="color:#0D2B6E; font-weight:700; font-size:0.85rem; text-transform:uppercase;">Test Name</th>
                                              <th style="color:#0D2B6E; font-weight:700; font-size:0.85rem; text-transform:uppercase; width:150px;">Price</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php foreach($tests as $test): ?>
                                              <tr style="border-bottom:1px solid #e2e8f0;">
                                                  <td style="font-weight:600; color:#0D2B6E; padding: 12px 16px;">
                                                      <i class="fas fa-caret-right text-muted me-2"></i>
                                                      <?= htmlspecialchars($test['test_name']) ?>
                                                  </td>
                                                  <td style="padding: 12px 16px;">
                                                      <?php if(!empty($test['clinic_price']) && $test['clinic_price'] > 0): ?>
                                                          <span class="badge bg-success" style="font-size:0.85rem; padding:6px 12px; border-radius:8px;">
                                                              Rs. <?= number_format($test['clinic_price'], 0) ?>
                                                          </span>
                                                      <?php else: ?>
                                                          <span class="badge bg-secondary" style="font-size:0.85rem; padding:6px 12px; border-radius:8px;">
                                                              Price not mentioned
                                                          </span>
                                                      <?php endif; ?>
                                                  </td>
                                              </tr>
                                          <?php endforeach; ?>
                                      </tbody>
                                  </table>
                              </div>
                          <?php endforeach; ?>
                      <?php else: ?>
                          <div class="text-center py-4">
                              <i class="fas fa-camera fa-2x text-muted mb-2"></i>
                              <p class="text-muted mb-0">No tests selected by this center yet.</p>
                          </div>
                      <?php endif; ?>
                   </div>
                </div>
             </div>
          </div>

          <!-- Submit & Cancel Buttons -->
          <div class="row mt-4 mb-5" id="actionButtons" style="display: none;">
             <div class="col-12">
                 <div class="rad-actions-bar">
                     <button type="button" class="btn-cancel" onclick="toggleEditMode()">
                         <i class="fas fa-times me-1"></i> Cancel
                     </button>
                     <button type="submit" class="btn-save">
                         <i class="fas fa-save me-1"></i> Save Changes
                     </button>
                 </div>
             </div>
          </div>
      </form>

      <!-- Feedback Section -->
      <div class="row mt-4">
         <div class="col-12">
            <div class="rad-card">
               <div class="rad-card-header">
                   <i class="fas fa-comments"></i> Patient Feedbacks (<?php echo mysqli_num_rows($feedback_result); ?>)
               </div>
               <div class="p-0">
                  <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                     <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                        <div class="feedback-item">
                           <div class="feedback-head">
                              <div>
                                 <div class="feedback-name"><?php echo htmlspecialchars($feedback['commenter_name']); ?></div>
                                 <div class="feedback-date mt-1"><i class="far fa-envelope"></i> <?php echo htmlspecialchars($feedback['commenter_gmail']); ?></div>
                              </div>
                              <div class="feedback-stars">
                                 <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                 <?php endfor; ?>
                              </div>
                           </div>
                           <?php if (!empty($feedback['comment'])): ?>
                           <div class="feedback-comment">
                              <?php echo nl2br(htmlspecialchars($feedback['comment'])); ?>
                           </div>
                           <?php endif; ?>
                           <div class="feedback-date">
                              <i class="fas fa-calendar"></i> <?php echo date('d M Y, h:i A', strtotime($feedback['created_at'])); ?>
                           </div>
                        </div>
                     <?php endwhile; ?>
                  <?php else: ?>
                     <div class="text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No feedbacks found for this radiology center.</p>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

   </div>
</div>

<script>
let isEditing = false;

function toggleEditMode() {
    isEditing = !isEditing;
    const formContainer = document.getElementById('radForm');
    const editBtn = document.getElementById('editBtn');
    const actionButtons = document.getElementById('actionButtons');
    const inputs = formContainer.querySelectorAll('input:not([type="hidden"]), select, textarea');

    if (isEditing) {
        // Enable Edit Mode
        formContainer.classList.remove('view-mode');
        formContainer.classList.add('edit-mode');
        editBtn.style.display = 'none';
        actionButtons.style.display = 'block';
        
        inputs.forEach(input => {
            input.removeAttribute('readonly');
        });

        document.getElementById('statusSwitch').removeAttribute('disabled');

    } else {
        window.location.reload();
    }
}

// Function to toggle edit button visibility (assuming you have logic for this)
document.addEventListener('DOMContentLoaded', function() {
    // If you want to always show the edit button, uncomment the next line:
    document.getElementById('editBtn').style.display = 'inline-flex';
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>