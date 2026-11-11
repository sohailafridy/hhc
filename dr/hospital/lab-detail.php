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
// Get laboratory ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'hospital/labs');
    exit();
}

$lab_id = (int)$_GET['id'];
$success_msg = '';
$error_msg = '';

// ── HANDLE UPDATE FORM SUBMISSION ──
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_lab'])) {
    
    $lab_name    = mysqli_real_escape_string($con, $_POST['lab_name']);
    // Reg No, City, Hospital PHP me bhi update nahi ho rahe taaki koi bypass na kar sake
    $lab_phone   = mysqli_real_escape_string($con, $_POST['lab_phone']);
    $lab_email   = mysqli_real_escape_string($con, $_POST['lab_email']);
    $lab_address = mysqli_real_escape_string($con, $_POST['lab_address']);
    $status      = isset($_POST['status']) ? 1 : 0;
    
    // Update lab table (Only editable fields)
    $update_lab = "UPDATE laboratories SET 
                    lab_name = '$lab_name',
                    lab_phone = '$lab_phone',
                    lab_email = '$lab_email',
                    lab_address = '$lab_address',
                    updated_at = NOW()
                   WHERE lab_id = $lab_id";
    
    if (mysqli_query($con, $update_lab)) {
        // Update user status
        $lab_user_id = (int)$_POST['user_id'];
        mysqli_query($con, "UPDATE users SET status = '$status' WHERE user_id = $lab_user_id");
        
        // ── UPDATE LAB TESTS ──
        if (isset($_POST['tests'])) {
            $selected_tests = $_POST['tests'];
            $test_prices = $_POST['prices'] ?? [];
            $selected = [];
            
            foreach ($selected_tests as $test_id => $val) {
                $test_id = (int)$test_id;
                $price = isset($test_prices[$test_id]) ? trim((string)$test_prices[$test_id]) : '';
                if ($test_id > 0 && $price !== '' && is_numeric($price) && (float)$price >= 0) {
                    $selected[$test_id] = (float)$price;
                }
            }

            // Delete old tests and insert new ones
            mysqli_query($con, "DELETE FROM tbl_lab_tests WHERE lab_id = $lab_id");
            if (!empty($selected)) {
                $insert_test = mysqli_prepare($con, "INSERT INTO tbl_lab_tests (lab_id, test_id, price, status) VALUES (?, ?, ?, 1)");
                foreach ($selected as $test_id => $price) {
                    mysqli_stmt_bind_param($insert_test, "iid", $lab_id, $test_id, $price);
                    mysqli_stmt_execute($insert_test);
                }
                mysqli_stmt_close($insert_test);
            }
        }
        $success_msg = "Laboratory details updated successfully!";
    } else {
        $error_msg = "Error updating lab: " . mysqli_error($con);
    }
}

// ── FETCH DATA ──
$query = "SELECT l.*, 
                c.city_name,
                h.hospital_name,
                u.status as estatus,
                u.user_id as u_id
          FROM laboratories l 
          LEFT JOIN cities c ON l.city_id = c.city_id
          LEFT JOIN hospitals h ON l.hospital_id = h.hospital_id
          LEFT JOIN users u ON u.user_id = l.user_id
          WHERE l.lab_id = $lab_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'hospital/labs');
    exit();
}

$laboratory = mysqli_fetch_assoc($result);
$user_id_lab = (int)$laboratory['user_id'];

// Fetch feedbacks
$feedback_query = "SELECT f.* FROM feedback f 
WHERE f.user_id = $user_id_lab AND f.status = 1 ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);

// Fetch Available Tests (with selected status)
$tests_query = "SELECT t.test_id, t.test_name, COALESCE(lt.price, '') AS lab_price,
                       CASE WHEN lt.id IS NULL THEN 0 ELSE 1 END AS is_selected
                FROM tbl_tests t
                LEFT JOIN tbl_lab_tests lt ON lt.test_id = t.test_id AND lt.lab_id = $lab_id
                WHERE t.status = 1
                ORDER BY t.test_name ASC";
$tests_result = mysqli_query($con, $tests_query);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/lab-detail-admin.css">
<style>
/* Custom styles for edit mode */
.edit-mode-container .form-control-modern {
    background-color: #fff;
    border: 1px solid #cbd5e1;
}
.view-mode-container .form-control-modern {
    background-color: #f8fafc;
    border: 1px solid transparent;
    pointer-events: none;
}
/* Readonly fields will look greyed out even in edit mode */
.readonly-field {
    background-color: #f0f0f0 !important;
    pointer-events: none;
    color: #64748b;
}
.btn-edit-top {
    background: linear-gradient(135deg,#1C3F8F,#00A8CC); 
    color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 600;
}
.btn-edit-top:hover { color: white; opacity: 0.9; }
.available-tests-list { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.available-test-row {
    display:flex; align-items:center; gap:12px; padding:12px;
    border:1px solid rgba(0,168,204,0.16); border-radius:12px;
    background:#f8fcff;
}
.available-test-check { width:18px; height:18px; accent-color:#27C96A; flex-shrink:0; cursor:pointer; }
.available-test-name { flex:1; color:#0D2B6E; font-size:.88rem; font-weight:600; }
.available-test-price { width:112px; }
.available-test-price input {
    width:100%; padding:8px 9px; border:1px solid #dbe7f0;
    border-radius:8px; outline:none; font-size:.85rem;
}
.available-test-price input:disabled { background:#eef2f7; color:#94a3b8; }
</style>

<div class="content-wrapper">
   <div class="container-fluid">
      
      <!-- Profile Header with Edit Button -->
      <div class="row">
         <div class="col-12 padding">
            <div class="laboratory-profile-header position-relative">
               <div class="row align-items-center">
                  <div class="col-md-2 text-center">
                     <?php if (!empty($laboratory['lab_pic'])): ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo $laboratory['lab_pic']; ?>" 
                             alt="<?php echo htmlspecialchars($laboratory['lab_name']); ?>" class="laboratory-avatar">
                     <?php else: ?>
                        <div class="laboratory-avatar d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                           <i class="fas fa-flask fa-3x text-white"></i>
                        </div>
                     <?php endif; ?>
                  </div>
                  <div class="col-md-10 d-flex justify-content-between align-items-center">
                      <div>
                          <h2 class="mb-2">
                              <?php echo !empty($laboratory['lab_name']) ? htmlspecialchars($laboratory['lab_name']) : 'Laboratory Name'; ?>
                          </h2>
                          <?php if (!empty($laboratory['lab_email'])): ?>
                              <p class="mb-1"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($laboratory['lab_email']); ?></p>
                          <?php endif; ?>
                          <?php if (!empty($laboratory['lab_phone'])): ?>
                              <p class="mb-0"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($laboratory['lab_phone']); ?></p>
                          <?php endif; ?>
                      </div>
                      <div>
                          <!-- Edit Button -->
                          <button type="button" class="btn-edit-top" id="editBtn" onclick="toggleEditMode()" style="display:none;">
                              <i class="fas fa-edit me-1"></i> Edit
                          </button>
                      </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Success/Error Messages -->
      <?php if ($success_msg): ?>
          <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
              <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
      <?php endif; ?>
      <?php if ($error_msg): ?>
          <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
              <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
      <?php endif; ?>

      <!-- Main Form -->
      <form method="POST" action="" id="labForm" class="view-mode-container" onsubmit="return true;">
          <input type="hidden" name="update_lab" value="1">
          <input type="hidden" name="user_id" value="<?php echo $user_id_lab; ?>">

          <div class="row mt-3">
             <!-- Laboratory Information -->
             <div class="col-lg-6">
                <div class="info-card">
                   <div class="info-card-header">
                      <h5><i class="fas fa-flask me-2"></i>Laboratory Information</h5>
                   </div>
                   <div class="info-card-body">
                      <div class="mb-3">
                          <label class="form-label fw-bold">Laboratory Name</label>
                          <input type="text" class="form-control form-control-modern" name="lab_name" value="<?php echo htmlspecialchars($laboratory['lab_name']); ?>" required>
                      </div>
                      
                      <!-- REG NO: Always Readonly -->
                      <div class="mb-3">
                          <label class="form-label fw-bold">Registration No</label>
                          <input type="text" class="form-control form-control-modern readonly-field" name="reg_no" value="<?php echo htmlspecialchars($laboratory['reg_no']); ?>" readonly>
                      </div>

                      <div class="mb-3">
                          <label class="form-label fw-bold">Phone</label>
                          <input type="text" class="form-control form-control-modern" name="lab_phone" value="<?php echo htmlspecialchars($laboratory['lab_phone']); ?>">
                      </div>

                      <div class="mb-3">
                          <label class="form-label fw-bold">Email</label>
                          <input type="email" class="form-control form-control-modern" name="lab_email" value="<?php echo htmlspecialchars($laboratory['lab_email']); ?>">
                      </div>

                      <div class="mb-3">
                          <label class="form-label fw-bold d-block">Status</label>
                          <div class="form-check form-switch">
                              <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" value="1" <?php echo ($laboratory['estatus'] == 1) ? 'checked' : ''; ?> disabled>
                              <label class="form-check-label" for="statusSwitch">Active</label>
                          </div>
                      </div>
                   </div>
                </div>
             </div>
             
             <!-- Location & Type -->
             <div class="col-lg-6">
                <div class="info-card">
                   <div class="info-card-header">
                      <h5><i class="fas fa-map-marker-alt me-2"></i>Location & Type</h5>
                   </div>
                   <div class="info-card-body">
                      <!-- CITY: Always Readonly -->
                      <div class="mb-3">
                          <label class="form-label fw-bold">City</label>
                          <input type="text" class="form-control form-control-modern readonly-field" value="<?php echo htmlspecialchars($laboratory['city_name']); ?>" readonly>
                      </div>

                      <!-- HOSPITAL: Always Readonly -->
                      <div class="mb-3">
                          <label class="form-label fw-bold">Hospital</label>
                          <input type="text" class="form-control form-control-modern readonly-field" value="<?php echo htmlspecialchars($laboratory['hospital_name']); ?>" readonly>
                      </div>

                      <div class="mb-3">
                          <label class="form-label fw-bold">Address</label>
                          <textarea class="form-control form-control-modern" name="lab_address" rows="3"><?php echo htmlspecialchars($laboratory['lab_address']); ?></textarea>
                      </div>

                      <div class="info-item">
                         <span class="info-label">Created At</span>
                         <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($laboratory['created_at'])); ?></span>
                      </div>
                   </div>
                </div>
             </div>
          </div>

          <!-- Available Tests Section -->
          <div class="row mt-4">
             <div class="col-12">
                <div class="info-card">
                   <div class="info-card-header">
                      <h5><i class="fas fa-vials me-2"></i>Available Tests</h5>
                   </div>
                   <div class="info-card-body">
                      <?php if ($tests_result && mysqli_num_rows($tests_result) > 0): ?>
                          <div class="available-tests-list">
                              <?php while ($test = mysqli_fetch_assoc($tests_result)): ?>
                                  <div class="available-test-row">
                                      <input type="checkbox" class="available-test-check" 
                                             id="test_<?= (int)$test['test_id'] ?>" 
                                             name="tests[<?= (int)$test['test_id'] ?>]" 
                                             value="1" 
                                             <?= (int)$test['is_selected'] === 1 ? 'checked' : '' ?>>
                                      <label class="available-test-name" for="test_<?= (int)$test['test_id'] ?>">
                                          <?= htmlspecialchars($test['test_name']) ?>
                                      </label>
                                      <div class="available-test-price">
                                          <input type="number" min="0" step="0.01"
                                                 name="prices[<?= (int)$test['test_id'] ?>]"
                                                 value="<?= htmlspecialchars((string)$test['lab_price']) ?>"
                                                 placeholder="Price"
                                                 <?= (int)$test['is_selected'] === 1 ? '' : 'disabled' ?>>
                                      </div>
                                  </div>
                              <?php endwhile; ?>
                          </div>
                      <?php else: ?>
                          <p class="text-muted text-center py-3 mb-0">No tests available.</p>
                      <?php endif; ?>
                   </div>
                </div>
             </div>
          </div>

          <!-- Submit & Cancel Buttons (Hidden by default) -->
          <div class="row mt-4 mb-5" id="actionButtons" style="display: none;">
             <div class="col-12 text-center">
                 <button type="submit" class="btn-action btn-edit me-3" style="background:#1C3F8F; color:white; padding:10px 30px; border-radius:8px; font-weight:bold; border:none;">
                     <i class="fas fa-save me-1"></i> Save Changes
                 </button>
                 <button type="button" class="btn-action btn-cancel" onclick="toggleEditMode()" style="background:#f1f5f9; color:#475569; padding:10px 30px; border-radius:8px; font-weight:bold; border:none;">
                     <i class="fas fa-times me-1"></i> Cancel
                 </button>
             </div>
          </div>
      </form>

      <!-- Feedback Section (Outside form) -->
      <div class="row mt-4">
         <div class="col-12">
            <div class="feedback-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-comments me-2"></i>Patient Feedbacks (<?php echo mysqli_num_rows($feedback_result); ?>)</h5>
               </div>
               <div class="info-card-body p-0">
                  <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                     <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                        <div class="feedback-item">
                           <div class="feedback-header">
                              <div>
                                 <span class="feedback-name"><?php echo htmlspecialchars($feedback['commenter_name']); ?></span>
                                 <span class="feedback-email ms-2"><?php echo htmlspecialchars($feedback['commenter_gmail']); ?></span>
                              </div>
                              <div class="feedback-rating">
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
                              <i class="fas fa-calendar me-2"></i>
                              <?php echo date('d M Y, h:i A', strtotime($feedback['created_at'])); ?>
                           </div>
                        </div>
                     <?php endwhile; ?>
                  <?php else: ?>
                     <div class="text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No feedbacks found for this laboratory.</p>
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
    const formContainer = document.getElementById('labForm');
    const editBtn = document.getElementById('editBtn');
    const actionButtons = document.getElementById('actionButtons');
    const testCheckboxes = document.querySelectorAll('.available-test-check');
    const inputs = formContainer.querySelectorAll('input, select, textarea');

    if (isEditing) {
        // Enable Edit Mode
        formContainer.classList.remove('view-mode-container');
        formContainer.classList.add('edit-mode-container');
        editBtn.style.display = 'none';
        actionButtons.style.display = 'block';
        
        inputs.forEach(input => {
            // Skip hidden and readonly-field (Reg No, City, Hospital)
            if(input.type !== 'hidden' && !input.classList.contains('readonly-field')) {
                input.removeAttribute('readonly');
                if(input.tagName === 'SELECT') input.removeAttribute('disabled');
            }
        });

        // Enable test checkboxes interaction
        testCheckboxes.forEach((chk) => {
            chk.disabled = false;
        });

    } else {
        // Switch back to View Mode (Reload to discard changes)
        window.location.reload();
    }
}

// Initial setup for tests checkboxes logic
document.querySelectorAll('.available-test-check').forEach(function (checkbox) {
    // Initially disable checkboxes if not editing
    if(!isEditing) {
        checkbox.disabled = true; 
    }
    
    checkbox.addEventListener('change', function () {
        var priceInput = this.closest('.available-test-row').querySelector('.available-test-price input');
        priceInput.disabled = !this.checked;
    });
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>