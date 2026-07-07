<?php include '../config.php'; ?>

<?php
// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // First, get blood bank picture to delete file
    $pic_query = "SELECT bb_pic FROM blood_bank WHERE bb_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $bb_pic_data = mysqli_fetch_assoc($pic_result);
    $bb_pic = $bb_pic_data ? $bb_pic_data['bb_pic'] : '';
    
    // Delete blood bank from database
    $delete_query = "DELETE FROM blood_bank WHERE bb_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete picture file if it exists
        if (!empty($bb_pic)) {
            $pic_path = BASE_PATH."/admin/inc/uploads/blood-banks/".$bb_pic;
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
        }
        $_SESSION['success_msg'] = "Blood Bank deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
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

$bb_id = $_GET['id'];

// Fetch blood bank details with related information
$query = "SELECT bb.*, c.city_name 
          FROM blood_bank bb 
          LEFT JOIN cities c ON bb.city_id = c.city_id
          WHERE bb.bb_id = $bb_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/blood-banks/list');
    exit();
}

$blood_bank = mysqli_fetch_assoc($result);

// Fetch feedbacks for this blood bank
$feedback_query = "SELECT f.* FROM feedback f WHERE f.bloodb_id = $bb_id ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);

// Fetch available blood types for this blood bank
$blood_query = "SELECT * FROM bb_available_blood WHERE bb_id = $bb_id";
$blood_result = mysqli_query($con, $blood_query);


$available_blood_bags = mysqli_query($con, "SELECT * FROM bb_available_blood WHERE bb_id = $bb_id");
?>

<style>
    .blood-bank-profile-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .blood-bank-profile-header h2 {
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .blood-bank-profile-header p {
        color: rgba(255,255,255,0.95);
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        font-size: 16px;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .blood-bank-profile-header i {
        color: rgba(255,255,255,0.9);
        margin-right: 10px;
        font-size: 16px;
    }
    
    .blood-bank-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        object-fit: cover;
    }
    
    .blood-type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        padding: 25px;
    }
    
    .blood-bag-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .blood-bag-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border-color: #dc3545;
    }
    
    .blood-bag-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
    }
    
    .blood-bag-header {
        margin-bottom: 15px;
    }
    
    .blood-bag-header h5 {
        font-size: 18px;
        font-weight: 700;
        color: #495057;
        margin: 0 0 5px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .blood-bag-header h5 i {
        color: #dc3545;
        font-size: 16px;
    }
    
    .bag-count {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
    }
    
    .stock-indicator {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 10px;
    }
    
    .stock-high {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }
    
    .stock-medium {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
    }
    
    .stock-low {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
    }
    
    .info-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        overflow: hidden;
    }
    
    .info-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .info-card-header h5 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 18px;
    }
    
    .info-card-body {
        padding: 25px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 14px;
    }
    
    .info-value {
        font-weight: 500;
        color: #212529;
        font-size: 15px;
        text-align: right;
    }
    
    .feedback-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .feedback-item {
        padding: 20px 25px;
        border-bottom: 1px solid #f1f3f4;
        transition: background 0.3s ease;
    }
    
    .feedback-item:hover {
        background: #f8f9fa;
    }
    
    .feedback-item:last-child {
        border-bottom: none;
    }
    
    .feedback-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .feedback-name {
        font-weight: 600;
        color: #495057;
        font-size: 16px;
    }
    
    .feedback-email {
        color: #6c757d;
        font-size: 14px;
    }
    
    .feedback-rating {
        color: #ffc107;
        font-size: 18px;
    }
    
    .feedback-comment {
        color: #495057;
        font-size: 15px;
        line-height: 1.6;
        margin-top: 10px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #dc3545;
    }
    
    .feedback-date {
        color: #6c757d;
        font-size: 13px;
        margin-top: 10px;
    }
    
    .badge-status {
        padding: 6px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .badge-inactive {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    .btn-action {
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-edit {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
    }
    
    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    .btn-back {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    /* Clinical Record Cards */
    .clinical-record-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .clinical-record-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }
    
    .clinical-record-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .clinical-record-header h6 {
        font-size: 16px;
        margin: 0;
    }
    
    .clinical-record-body {
        padding: 20px;
    }
    
    .clinical-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .clinical-info-item:hover {
        background: #e9ecef;
    }
    
    .clinical-info-item i {
        font-size: 18px;
        margin-right: 12px;
        width: 24px;
        text-align: center;
    }
    
    .clinical-info-item div {
        flex: 1;
    }
    
    .clinical-info-item small {
        display: block;
        font-size: 12px;
        margin-bottom: 2px;
    }
    
    .clinical-info-item strong {
        font-size: 14px;
        color: #495057;
    }
    
    .clinical-contact {
        display: flex;
        align-items: center;
        padding: 15px;
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-radius: 8px;
        border-left: 4px solid #ffc107;
        margin-top: 10px;
    }
    
    .clinical-contact i {
        font-size: 20px;
        margin-right: 15px;
        color: #f39c12;
    }
    
    .clinical-contact div {
        flex: 1;
    }
    
    .clinical-contact small {
        display: block;
        font-size: 12px;
        color: #856404;
        margin-bottom: 2px;
    }
    
    .season-group .clinical-contact strong {
        font-size: 16px;
        color: #856404;
        font-weight: 600;
    }
    .padding{
      padding: 0 28px !important;
    }
</style>

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
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo $blood_bank['bb_pic']; ?>" 
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
                        <p class="text-muted">No feedbacks found for this hospital.</p>
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
function deleteHospital(hospitalId) {
    if (confirm('Are you sure you want to delete this hospital? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + hospitalId;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>