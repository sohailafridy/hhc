<?php include '../config.php'; ?>

<?php
$entity_id = $_GET['entity_id'];
// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // First, get hospital picture to delete file
    $pic_query = "SELECT hospital_pic FROM hospitals WHERE hospital_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $hospital_pic_data = mysqli_fetch_assoc($pic_result);
    $hospital_pic = $hospital_pic_data ? $hospital_pic_data['hospital_pic'] : '';
    
    // Delete hospital from database
    $delete_query = "UPDATE hospitals SET status = 0 WHERE hospital_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete picture file if it exists
        // if (!empty($hospital_pic)) {
        //     $pic_path = BASE_PATH."/admin/inc/uploads/hospitals/".$hospital_pic;
        //     if (file_exists($pic_path)) {
        //         unlink($pic_path);
        //     }
        // }
        $_SESSION['success_msg'] = "Hospital deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    
    // Redirect to hospitals list
    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Get hospital ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}

$hospital_id = $_GET['id'];

// Fetch hospital details with related information
$query = "SELECT h.*, c.city_name 
          FROM hospitals h 
          LEFT JOIN cities c ON h.city_id = c.city_id
          WHERE h.hospital_id = $hospital_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}

$hospital = mysqli_fetch_assoc($result);

// Fetch feedbacks for this hospital
$feedback_query = "SELECT f.* FROM feedback f WHERE f.entity_id = $entity_id ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);
?>

<style>
    .hospital-profile-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .hospital-profile-header h2 {
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .hospital-profile-header p {
        color: rgba(255,255,255,0.95);
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        font-size: 16px;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .hospital-profile-header i {
        color: rgba(255,255,255,0.9);
        margin-right: 10px;
        font-size: 16px;
    }
    
    .hospital-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        object-fit: cover;
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
        border-left: 4px solid #667eea;
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
      <!-- Hospital Profile Header -->
      <div class="row">
         <div class="col-12 padding">
            <div class="hospital-profile-header">
               <div class="row align-items-center">
                  <div class="col-md-2 text-center">
                     <?php if (!empty($hospital['hospital_pic'])): ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital['hospital_pic']; ?>" 
                             alt="<?php echo htmlspecialchars($hospital['hospital_name']); ?>" class="hospital-avatar">
                     <?php else: ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/hosp.jpg" 
                             alt="<?php echo htmlspecialchars($hospital['hospital_name']); ?>" class="hospital-avatar">
                     <?php endif; ?>
                  </div>
                  <div class="col-md-10">
                     <h2 class="mb-2"><?php echo htmlspecialchars($hospital['hospital_name']); ?></h2>
                     <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($hospital['city_name']); ?></p>
                     <p class="mb-0"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($hospital['hospital_phone']); ?></p>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Hospital Information -->
      <div class="row">
         <div class="col-lg-6">
            <div class="info-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-hospital me-2"></i>Hospital Information</h5>
               </div>
               <div class="info-card-body">
                  <div class="info-item">
                     <span class="info-label">Hospital ID</span>
                     <span class="info-value"><?php echo $hospital['hospital_id']; ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Name</span>
                     <span class="info-value"><?php echo htmlspecialchars($hospital['hospital_name']); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Phone</span>
                     <span class="info-value"><?php echo htmlspecialchars($hospital['hospital_phone']); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Status</span>
                     <span class="info-value">
                        <?php if ($hospital['status'] == 1): ?>
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
                  <h5><i class="fas fa-map-marker-alt me-2"></i>Location & Details</h5>
               </div>
               <div class="info-card-body">
                  <div class="info-item">
                     <span class="info-label">City</span>
                     <span class="info-value"><?php echo htmlspecialchars($hospital['city_name']); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Address</span>
                     <span class="info-value"><?php echo nl2br(htmlspecialchars($hospital['hospital_address'])); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Created At</span>
                     <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($hospital['created_at'])); ?></span>
                  </div>
                  <?php if (!empty($hospital['updated_at'])): ?>
                  <div class="info-item">
                     <span class="info-label">Updated At</span>
                     <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($hospital['updated_at'])); ?></span>
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
                  <a href="<?php echo BASE_URL; ?>admin/hospitals/add?id=<?php echo $hospital['hospital_id']; ?>" class="btn-action btn-edit me-3">
                     <i class="fas fa-edit"></i> Edit Hospital
                  </a>
                  <a href="javascript:void(0)" onclick="deleteHospital(<?php echo $hospital['hospital_id']; ?>)" class="btn-action btn-delete me-3">
                     <i class="fas fa-trash"></i> Delete Hospital
                  </a>
                  <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn-action btn-back">
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