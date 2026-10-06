<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php
// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];   // bb_id

    // Blood bank ka user_id (aur picture) nikalein
    $bb_del_query = "SELECT user_id, bb_pic FROM blood_bank WHERE bb_id = $delete_id";
    $bb_del_result = mysqli_query($con, $bb_del_query);
    $bb_del_data = mysqli_fetch_assoc($bb_del_result);

    if ($bb_del_data) {
        $bb_user_id = (int)$bb_del_data['user_id'];

        // Soft delete: users table me status = 0
        $delete_query = "UPDATE users SET status = 0 WHERE user_id = $bb_user_id";

        if (mysqli_query($con, $delete_query)) {
            // Delete picture file if it exists
            // if (!empty($bb_del_data['bb_pic'])) {
            //     $pic_path = BASE_PATH."/admin/inc/uploads/blood-banks/".$bb_del_data['bb_pic'];
            //     if (file_exists($pic_path)) {
            //         unlink($pic_path);
            //     }
            // }
            $_SESSION['success_msg'] = "Blood Bank deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
        }
    } else {
        $_SESSION['error_msg'] = "Blood Bank not found.";
    }

    // Redirect to blood banks list
    header('Location: ' . BASE_URL . 'admin/blood-banks/list');
    exit();
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Get blood bank ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'admin/blood-banks/list');
    exit();
}

$bb_id = (int)$_GET['id'];

// Fetch blood bank details with related information (status users table se)
$query = "SELECT bb.*, c.city_name, u.status
          FROM blood_bank bb
          LEFT JOIN cities c ON bb.city_id = c.city_id
          LEFT JOIN users u ON u.user_id = bb.user_id
          WHERE bb.bb_id = $bb_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/blood-banks/list');
    exit();
}

$blood_bank = mysqli_fetch_assoc($result);
$user_id = (int)$blood_bank['user_id'];

// Fetch feedbacks for this blood bank
$feedback_query = "SELECT f.* FROM feedback f WHERE f.user_id = $user_id ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);

// Fetch available blood types for this blood bank
$blood_query = "SELECT * FROM bb_available_blood WHERE bb_id = $bb_id";
$blood_result = mysqli_query($con, $blood_query);

$available_blood_bags = mysqli_query($con, "SELECT * FROM bb_available_blood WHERE bb_id = $bb_id");
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/blood-bank-detail-admin.css">

<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <!-- Blood Bank Profile Header -->
      <div class="row">
         <div class="col-12 padding">
            <div class="blood-bank-profile-header">
               <div class="row align-items-center">
                  <div class="col-md-2 text-center">
                     <?php if (!empty($blood_bank['bb_pic'])): ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo htmlspecialchars($blood_bank['bb_pic']); ?>"
                             alt="<?php echo htmlspecialchars($blood_bank['bb_name']); ?>" class="blood-bank-avatar">
                     <?php else: ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/bb.jpg"
                             alt="<?php echo htmlspecialchars($blood_bank['bb_name']); ?>" class="blood-bank-avatar">
                     <?php endif; ?>
                  </div>
                  <div class="col-md-10">
                     <h2 class="mb-2"><?php echo htmlspecialchars($blood_bank['bb_name']); ?></h2>
                     <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($blood_bank['city_name']); ?></p>
                     <p class="mb-0"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($blood_bank['bb_contact']); ?></p>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Blood Bank Information -->
      <div class="row">
         <div class="col-lg-12">
            <div class="info-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-map-marker-alt me-2"></i>Location & Details</h5>
               </div>
               <div class="info-card-body">
                  <div class="info-item">
                     <span class="info-label">City</span>
                     <span class="info-value"><?php echo htmlspecialchars($blood_bank['city_name']); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Address</span>
                     <span class="info-value"><?php echo nl2br(htmlspecialchars($blood_bank['bb_address'])); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Status</span>
                     <span class="info-value">
                        <?php if ($blood_bank['status'] == 1): ?>
                           <span class="badge badge-success">Active</span>
                        <?php else: ?>
                           <span class="badge badge-danger">Inactive</span>
                        <?php endif; ?>
                     </span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Created At</span>
                     <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($blood_bank['created_at'])); ?></span>
                  </div>
                  <?php if (!empty($blood_bank['updated_at'])): ?>
                  <div class="info-item">
                     <span class="info-label">Updated At</span>
                     <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($blood_bank['updated_at'])); ?></span>
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
                  <a href="<?php echo BASE_URL; ?>admin/blood-banks/add?id=<?php echo $blood_bank['bb_id']; ?>" class="btn-action btn-edit me-3">
                     <i class="fas fa-edit"></i> Edit Blood Bank
                  </a>
                  <a href="javascript:void(0)" onclick="deleteBloodBank(<?php echo $blood_bank['bb_id']; ?>)" class="btn-action btn-delete me-3">
                     <i class="fas fa-trash"></i> Delete Blood Bank
                  </a>
                  <a href="<?php echo BASE_URL; ?>admin/blood-banks/list" class="btn-action btn-back">
                     <i class="fas fa-arrow-left"></i> Back to List
                  </a>
               </div>
            </div>
         </div>
      </div>

      <!-- Available Blood Types -->
      <div class="row mt-4">
         <div class="col-12">
            <div class="blood-type-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-tint me-2"></i>Available Blood Types</h5>
               </div>
               <div class="blood-type-grid">
                  <?php if (mysqli_num_rows($available_blood_bags) > 0): ?>
                     <?php while ($blood_bag = mysqli_fetch_assoc($available_blood_bags)): ?>
                        <div class="blood-bag-card">
                           <div class="blood-bag-header">
                              <h5><i class="fas fa-tint"></i> <?php echo htmlspecialchars($blood_bag['b_group']); ?></h5>
                              <span class="bag-count"><?php echo $blood_bag['stock']; ?> bags</span>
                           </div>
                           <?php
                           $stock = $blood_bag['stock'];
                           if ($stock > 20) {
                               echo '<span class="stock-indicator stock-high">High Stock</span>';
                           } elseif ($stock > 9 && $stock <= 20) {
                               echo '<span class="stock-indicator stock-medium">Medium Stock</span>';
                           } else {
                               echo '<span class="stock-indicator stock-low">Low Stock</span>';
                           }
                           ?>
                        </div>
                     <?php endwhile; ?>
                  <?php else: ?>
                     <div class="text-center py-5" style="grid-column: 1 / -1;">
                        <i class="fas fa-tint fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No blood availability information found.</p>
                     </div>
                  <?php endif; ?>
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
                        <p class="text-muted">No feedbacks found for this blood bank.</p>
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
function deleteBloodBank(bb_id) {
    if (confirm('Are you sure you want to delete this blood bank? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + bb_id;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>