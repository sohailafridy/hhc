<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php
// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    $pic_query = "SELECT lab_pic FROM laboratories WHERE user_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $lab_pic_data = mysqli_fetch_assoc($pic_result);
    $lab_pic = $lab_pic_data ? $lab_pic_data['lab_pic'] : '';

    $delete_query = "UPDATE users set status=0 WHERE user_id = $delete_id";

    if (mysqli_query($con, $delete_query)) {
        $_SESSION['success_msg'] = "Laboratory deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }

    header('Location: ' . BASE_URL . 'admin/laboratories/list');
    exit();
}
?>

<?php
/**
 * SehatNexa — Brand Color Palette
 */
$color1 = '#1C3F8F';
$color2 = '#00A8CC';
$color3 = '#27C96A';
$color4 = '#00C9A7';
$color5 = '#0D2B6E';
$color6 = '#E8F7FF';
$color7 = '#F0FFF8';
$color8 = '#FFFFFF';

$gradient_primary  = 'linear-gradient(135deg, #1C3F8F 0%, #00A8CC 100%)';
$gradient_green    = 'linear-gradient(135deg, #27C96A 0%, #00C9A7 100%)';
$gradient_full     = 'linear-gradient(135deg, #1C3F8F 0%, #00A8CC 50%, #27C96A 100%)';

$color_primary     = $color1;
$color_secondary   = $color3;
$color_accent      = $color2;
$color_success     = $color4;
$color_dark        = $color5;
$color_light_blue  = $color6;
$color_light_green = $color7;
$color_white       = $color8;
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Get laboratory ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'admin/laboratories/list');
    exit();
}

$lab_id = (int)$_GET['id'];

// Fetch laboratory details with related information
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
    header('Location: ' . BASE_URL . 'admin/laboratories/list');
    exit();
}

$laboratory = mysqli_fetch_assoc($result);
$user_id = (int)$laboratory['user_id'];

// Fetch feedbacks for this laboratory
$feedback_query = "SELECT f.* FROM feedback f 
WHERE f.user_id = $user_id ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);

// --- Working in Hospitals
$working_in_q = mysqli_query($con, "SELECT h.hospital_id, h.hospital_name, h.hospital_phone, h.hospital_address, c.city_name
    FROM assign_hospital asg
    LEFT JOIN hospitals h ON h.hospital_id = asg.hospital_id
    LEFT JOIN cities c ON h.city_id = c.city_id
    LEFT JOIN users u ON u.user_id = asg.user_id
    WHERE u.status = 1 AND h.approve = 1 AND asg.user_id = '". $user_id ."'
    ORDER BY h.hospital_name");
$working_in_count = $working_in_q ? mysqli_num_rows($working_in_q) : 0;

// --- Available Tests (matches frontend schema: tbl_lab_tests + tbl_tests)
$tests_q = mysqli_query($con, "SELECT t.test_id, t.test_name, t.description, lt.price
    FROM tbl_lab_tests lt
    INNER JOIN tbl_tests t ON lt.test_id = t.test_id
    WHERE lt.lab_id = $lab_id AND lt.status = 1 AND t.status = 1
    ORDER BY t.test_name ASC");
$tests_count = $tests_q ? mysqli_num_rows($tests_q) : 0;
$tests_rows  = $tests_q ? mysqli_fetch_all($tests_q, MYSQLI_ASSOC) : [];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/lab-detail-admin.css">

<div class="content-wrapper">
   <div class="container-fluid">

      <!-- Hero Header -->
      <div class="row">
         <div class="col-12 padding">
            <div class="sn-hero">
               <div class="row align-items-center">
                  <div class="col-md-2 text-center mb-3 mb-md-0">
                     <?php if (!empty($laboratory['lab_pic'])): ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo $laboratory['lab_pic']; ?>" 
                             alt="<?php echo htmlspecialchars($laboratory['lab_name']); ?>" class="sn-avatar">
                     <?php else: ?>
                        <div class="sn-avatar-placeholder">
                           <i class="fas fa-flask"></i>
                        </div>
                     <?php endif; ?>
                  </div>
                  <div class="col-md-10">
                     <h2 class="mb-2">
                        <?php echo !empty($laboratory['lab_name']) && $laboratory['lab_name'] !== '0' ? htmlspecialchars($laboratory['lab_name']) : 'Laboratory Name Not Available'; ?>
                     </h2>

                     <?php if (!empty($laboratory['lab_email']) && $laboratory['lab_email'] !== '0' && $laboratory['lab_email'] !== 'null'): ?>
                        <p class="mb-1"><i class="fas fa-envelope"></i><?php echo htmlspecialchars($laboratory['lab_email']); ?></p>
                     <?php endif; ?>

                     <?php if (!empty($laboratory['lab_phone']) && $laboratory['lab_phone'] !== '0' && $laboratory['lab_phone'] !== 'null'): ?>
                        <p class="mb-0"><i class="fas fa-phone"></i><?php echo htmlspecialchars($laboratory['lab_phone']); ?></p>
                     <?php endif; ?>

                     <?php if (isset($laboratory['estatus']) && $laboratory['estatus'] == 1): ?>
                        <span class="sn-status-pill sn-status-active"><i class="fas fa-circle" style="font-size:7px;vertical-align:middle;margin-right:5px;"></i> Active</span>
                     <?php else: ?>
                        <span class="sn-status-pill sn-status-inactive"><i class="fas fa-circle" style="font-size:7px;vertical-align:middle;margin-right:5px;"></i> Inactive</span>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Laboratory Information -->
      <div class="row">
         <div class="col-lg-6">
            <div class="sn-card">
               <div class="sn-card-header">
                  <h5><i class="fas fa-flask"></i> Laboratory Information</h5>
               </div>
               <div class="sn-card-body">
                  <div class="sn-info-item">
                     <span class="sn-info-label">Laboratory ID</span>
                     <span class="sn-info-value"><?php echo htmlspecialchars($laboratory['lab_id'] ?? '-'); ?></span>
                  </div>
                  <?php if (!empty($laboratory['reg_no'])): ?>
                  <div class="sn-info-item">
                     <span class="sn-info-label">Registration No</span>
                     <span class="sn-info-value"><?php echo htmlspecialchars($laboratory['reg_no']); ?></span>
                  </div>
                  <?php endif; ?>
                  <?php if (!empty($laboratory['lab_phone']) && $laboratory['lab_phone'] !== '0' && $laboratory['lab_phone'] !== 'null'): ?>
                  <div class="sn-info-item">
                     <span class="sn-info-label">Phone</span>
                     <span class="sn-info-value"><?php echo htmlspecialchars($laboratory['lab_phone']); ?></span>
                  </div>
                  <?php endif; ?>
                  <?php if (!empty($laboratory['lab_email']) && $laboratory['lab_email'] !== '0' && $laboratory['lab_email'] !== 'null'): ?>
                  <div class="sn-info-item">
                     <span class="sn-info-label">Email</span>
                     <span class="sn-info-value"><?php echo htmlspecialchars($laboratory['lab_email']); ?></span>
                  </div>
                  <?php endif; ?>
                  <div class="sn-info-item">
                     <span class="sn-info-label">Status</span>
                     <span class="sn-info-value">
                        <?php if (isset($laboratory['estatus']) && $laboratory['estatus'] == 1): ?>
                           <span class="sn-badge sn-badge-active">Active</span>
                        <?php else: ?>
                           <span class="sn-badge sn-badge-inactive">Inactive</span>
                        <?php endif; ?>
                     </span>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-lg-6">
            <div class="sn-card">
               <div class="sn-card-header">
                  <h5><i class="fas fa-map-marker-alt"></i> Location & Type</h5>
               </div>
               <div class="sn-card-body">
                  <?php if (!empty($laboratory['city_name']) && $laboratory['city_name'] !== '0'): ?>
                  <div class="sn-info-item">
                     <span class="sn-info-label">City</span>
                     <span class="sn-info-value"><?php echo htmlspecialchars($laboratory['city_name']); ?></span>
                  </div>
                  <?php endif; ?>

                  <?php if (isset($laboratory['lab_type']) && $laboratory['lab_type'] == 1): ?>
                     <div class="sn-info-item">
                        <span class="sn-info-label">Type</span>
                        <span class="sn-info-value"><span class="sn-badge sn-badge-type">Hospital Lab</span></span>
                     </div>
                     <?php if (!empty($laboratory['hospital_name']) && $laboratory['hospital_name'] !== '0'): ?>
                     <div class="sn-info-item">
                        <span class="sn-info-label">Hospital</span>
                        <span class="sn-info-value"><?php echo htmlspecialchars($laboratory['hospital_name']); ?></span>
                     </div>
                     <?php endif; ?>
                  <?php else: ?>
                     <div class="sn-info-item">
                        <span class="sn-info-label">Type</span>
                        <span class="sn-info-value"><span class="sn-badge sn-badge-type">Independent Lab</span></span>
                     </div>
                  <?php endif; ?>

                  <?php if (!empty($laboratory['lab_address']) && $laboratory['lab_address'] !== '0'): ?>
                  <div class="sn-info-item">
                     <span class="sn-info-label">Address</span>
                     <span class="sn-info-value"><?php echo nl2br(htmlspecialchars($laboratory['lab_address'])); ?></span>
                  </div>
                  <?php endif; ?>

                  <?php if (!empty($laboratory['created_at']) && $laboratory['created_at'] !== '0000-00-00 00:00:00'): ?>
                  <div class="sn-info-item">
                     <span class="sn-info-label">Created At</span>
                     <span class="sn-info-value"><?php echo date('d M Y, h:i A', strtotime($laboratory['created_at'])); ?></span>
                  </div>
                  <?php endif; ?>

                  <?php if (!empty($laboratory['updated_at']) && $laboratory['updated_at'] !== '0000-00-00 00:00:00'): ?>
                  <div class="sn-info-item">
                     <span class="sn-info-label">Updated At</span>
                     <span class="sn-info-value"><?php echo date('d M Y, h:i A', strtotime($laboratory['updated_at'])); ?></span>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

      <!-- Working in Hospitals -->
      <div class="row mt-2">
         <div class="col-12">
            <div class="sn-card">
               <div class="sn-card-header">
                  <h5><i class="fas fa-hospital-alt"></i> Working in Hospitals (<?php echo $working_in_count; ?>)</h5>
               </div>
               <div class="sn-card-body">
                  <?php if ($working_in_count > 0): ?>
                     <div class="sn-hospitals-grid">
                        <?php while ($h = mysqli_fetch_assoc($working_in_q)): ?>
                           <div class="sn-hosp-chip">
                              <div class="sn-hosp-icon"><i class="fas fa-hospital"></i></div>
                              <div>
                                 <div class="sn-hosp-name"><?= htmlspecialchars($h['hospital_name']) ?></div>
                                 <div class="sn-hosp-meta">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($h['city_name'] ?? 'N/A') ?>
                                    <?php if (!empty($h['hospital_phone'])): ?>
                                       <br><i class="fas fa-phone me-1"></i><?= htmlspecialchars($h['hospital_phone']) ?>
                                    <?php endif; ?>
                                 </div>
                              </div>
                           </div>
                        <?php endwhile; ?>
                     </div>
                  <?php else: ?>
                     <div class="sn-empty">
                        <i class="fas fa-hospital"></i>
                        <p>No hospitals assigned to this laboratory yet.</p>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

      <!-- Available Tests (NEW) -->
      <!-- Available Tests -->
<div class="row mt-2">
   <div class="col-12">
      <div class="sn-card">
         <div class="sn-card-header">
            <h5><i class="fas fa-vials"></i> Available Tests (<?php echo $tests_count; ?>)</h5>
         </div>
         <div class="sn-card-body">
            <?php if ($tests_count > 0): ?>
               <div class="sn-tests-grid">
                  <?php foreach ($tests_rows as $t): ?>
                     <div class="sn-test-chip">
                        <div class="sn-test-icon"><i class="fas fa-vial"></i></div>
                        <div class="sn-test-info">
                           <div class="sn-test-name">
                              <?= htmlspecialchars($t['test_name']) ?>
                           </div>
                           <div class="sn-test-meta">
                              <?php if (!empty($t['price']) && $t['price'] > 0): ?>
                                 <span class="sn-test-tag sn-test-price">
                                    <i class="fas fa-tag me-1"></i>Rs. <?= number_format($t['price'], 0) ?>
                                 </span>
                              <?php else: ?>
                                 <span class="sn-test-tag">
                                    <i class="fas fa-question-circle me-1"></i>Price on request
                                 </span>
                              <?php endif; ?>
                           </div>
                           <?php if (!empty($t['description'])): ?>
                              <div class="sn-test-desc"><?= htmlspecialchars($t['description']) ?></div>
                           <?php endif; ?>
                        </div>
                     </div>
                  <?php endforeach; ?>
               </div>
            <?php else: ?>
               <div class="sn-empty">
                  <i class="fas fa-vials"></i>
                  <p>No tests added for this laboratory yet.</p>
               </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>

      <!-- Actions -->
      <div class="row mt-2">
         <div class="col-12">
            <div class="sn-card">
               <div class="sn-card-header">
                  <h5><i class="fas fa-cogs"></i> Actions</h5>
               </div>
               <div class="sn-card-body text-center">
                  <a href="<?php echo BASE_URL; ?>admin/laboratories/add?id=<?php echo $laboratory['lab_id']; ?>" class="sn-btn sn-btn-edit me-2 mb-2">
                     <i class="fas fa-edit"></i> Edit Laboratory
                  </a>
                  <a href="javascript:void(0)" onclick="deleteLaboratory(<?php echo $laboratory['user_id']; ?>)" class="sn-btn sn-btn-delete me-2 mb-2">
                     <i class="fas fa-trash"></i> Delete Laboratory
                  </a>
                  <a href="<?php echo BASE_URL; ?>admin/laboratories/list" class="sn-btn sn-btn-back mb-2">
                     <i class="fas fa-arrow-left"></i> Back to List
                  </a>
               </div>
            </div>
         </div>
      </div>

      <!-- Feedback Section -->
      <div class="row mt-2">
         <div class="col-12">
            <div class="sn-card">
               <div class="sn-card-header">
                  <h5><i class="fas fa-comments"></i> Patient Feedbacks (<?php echo mysqli_num_rows($feedback_result); ?>)</h5>
               </div>
               <div class="p-0">
                  <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                     <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                        <div class="sn-feedback-item">
                           <div class="sn-feedback-head">
                              <div>
                                 <span class="sn-fb-name"><?php echo htmlspecialchars($feedback['commenter_name']); ?></span>
                                 <span class="sn-fb-email"><?php echo htmlspecialchars($feedback['commenter_gmail']); ?></span>
                              </div>
                              <div class="sn-fb-stars">
                                 <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $feedback['stars'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                 <?php endfor; ?>
                              </div>
                           </div>
                           <?php if (!empty($feedback['comment'])): ?>
                              <div class="sn-fb-comment">
                                 <?php echo nl2br(htmlspecialchars($feedback['comment'])); ?>
                              </div>
                           <?php endif; ?>
                           <div class="sn-fb-date">
                              <i class="fas fa-calendar me-1"></i>
                              <?php echo date('d M Y, h:i A', strtotime($feedback['created_at'])); ?>
                           </div>
                        </div>
                     <?php endwhile; ?>
                  <?php else: ?>
                     <div class="sn-empty">
                        <i class="fas fa-comments"></i>
                        <p>No feedbacks found for this laboratory.</p>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

   </div>
   <!-- Container-fluid ends -->
</div>

<script>
function deleteLaboratory(user_id) {
    if (confirm('Are you sure you want to delete this laboratory? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + user_id;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>