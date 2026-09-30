<?php include '../config.php'; ?>

<?php
// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // First, get laboratory picture to delete file
    $pic_query = "SELECT lab_pic FROM laboratories WHERE user_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $lab_pic_data = mysqli_fetch_assoc($pic_result);
    $lab_pic = $lab_pic_data ? $lab_pic_data['lab_pic'] : '';
    
    // Delete laboratory from database
    $delete_query = "UPDATE users set status=0 WHERE user_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete picture file if it exists
        // if (!empty($lab_pic)) {
        //     $pic_path = BASE_PATH."/admin/inc/uploads/laboratories/".$lab_pic;
        //     if (file_exists($pic_path)) {
        //         unlink($pic_path);
        //     }
        // }
        $_SESSION['success_msg'] = "Laboratory deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    
    // Redirect to laboratories list
    header('Location: ' . BASE_URL . 'admin/laboratories/list');
    exit();
}
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

?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/lab-detail-admin.css">
<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <!-- Laboratory Profile Header -->
      <div class="row">
         <div class="col-12 padding">
            <div class="laboratory-profile-header">
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
                  <div class="col-md-10">
                     <h2 class="mb-2"><?php echo htmlspecialchars($laboratory['lab_name']); ?></h2>
                     <p class="mb-1"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($laboratory['lab_email']); ?></p>
                     <p class="mb-0"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($laboratory['lab_phone']); ?></p>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Laboratory Information -->
      <div class="row">
         <div class="col-lg-6">
            <div class="info-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-flask me-2"></i>Laboratory Information</h5>
               </div>
               <div class="info-card-body">
                  <div class="info-item">
                     <span class="info-label">Laboratory ID</span>
                     <span class="info-value"><?php echo $laboratory['lab_id']; ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Phone</span>
                     <span class="info-value"><?php echo htmlspecialchars($laboratory['lab_phone']); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Email</span>
                     <span class="info-value"><?php echo htmlspecialchars($laboratory['lab_email']); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Status</span>
                     <span class="info-value">
                        <?php if ($laboratory['estatus'] == 1): ?>
                           <span class="badge-status badge-active">Active</span>
                        <?php else: ?>
                           <span class="badge-status badge-inactive">Inactive</span>
                        <?php endif; ?>
                     </span>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-lg-6">
            <div class="info-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-map-marker-alt me-2"></i>Location & Type</h5>
               </div>
               <div class="info-card-body">
                  <div class="info-item">
                     <span class="info-label">City</span>
                     <span class="info-value"><?php echo htmlspecialchars($laboratory['city_name']); ?></span>
                  </div>
                  <?php if ($laboratory['lab_type'] == 1): ?>
                     <div class="info-item">
                        <span class="info-label">Type</span>
                        <span class="info-value">Hospital Lab</span>
                     </div>
                     <div class="info-item">
                        <span class="info-label">Hospital</span>
                        <span class="info-value"><?php echo htmlspecialchars($laboratory['hospital_name']); ?></span>
                     </div>
                  <?php else: ?>
                     <div class="info-item">
                        <span class="info-label">Type</span>
                        <span class="info-value">Independent Lab</span>
                     </div>
                  <?php endif; ?>
                  <div class="info-item">
                     <span class="info-label">Address</span>
                     <span class="info-value"><?php echo nl2br(htmlspecialchars($laboratory['lab_address'])); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Created At</span>
                     <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($laboratory['created_at'])); ?></span>
                  </div>
                  <?php if (!empty($laboratory['updated_at'])): ?>
                  <div class="info-item">
                     <span class="info-label">Updated At</span>
                     <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($laboratory['updated_at'])); ?></span>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
      
      <!-- Actions -->
      <div class="row mt-4">
         <div class="col-12">
            <div class="info-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-cogs me-2"></i>Actions</h5>
               </div>
               <div class="info-card-body text-center">
                  <a href="<?php echo BASE_URL; ?>admin/laboratories/add?id=<?php echo $laboratory['lab_id']; ?>" class="btn-action btn-edit me-3">
                     <i class="fas fa-edit"></i> Edit Laboratory
                  </a>
                  <a href="javascript:void(0)" onclick="deleteLaboratory(<?php echo $laboratory['user_id']; ?>)" class="btn-action btn-delete me-3">
                     <i class="fas fa-trash"></i> Delete Laboratory
                  </a>
                  <a href="<?php echo BASE_URL; ?>admin/laboratories/list" class="btn-action btn-back">
                     <i class="fas fa-arrow-left"></i> Back to List
                  </a>
               </div>
            </div>
         </div>
      </div>
      
      <!-- Feedback Section -->
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