<?php include '../config.php'; ?>

<?php

$entity_id =0;
if (isset($_GET['entity_id'])) {
    $entity_id =$_GET['entity_id'];
}

// delete clinical_info and doctor_in_hospital 
if(isset($_REQUEST['del_clinic_id']) && $_REQUEST['del_clinic_id'] !=0){
    $doctor_in_hospital = "DELETE FROM doctor_in_hospital
      WHERE doctor_in_hosp_id = (
         SELECT doctor_in_hosp_id
         FROM clinical_info
         WHERE clinical_info_id  = '" . $_REQUEST['del_clinic_id'] . "'
      )";
      if(mysqli_query($con,$doctor_in_hospital)){
        $clinic_info = "DELETE FROM clinical_info WHERE clinical_info_id = '" . $_REQUEST['del_clinic_id'] . "'";
        if(mysqli_query($con,$clinic_info)){
            $_SESSION['success_msg'] = "Clinical information deleted successfully!";
        }
      }
}


// clinical info if not added if exist hospital
$check = mysqli_query($con, "SELECT COUNT(doctor_in_hosp_id) as ids FROM doctor_in_hospital WHERE doctor_in_hosp_id NOT IN ( SELECT doctor_in_hosp_id FROM clinical_info ) and doctor_in_hospital.doctor_id = '".$_GET['id']."'
");
$ids = mysqli_fetch_assoc($check);
$ids = $ids['ids'];


// Handle emergency status toggle - MUST be before any HTML output
if (isset($_POST['toggle_emergency']) && is_numeric($_POST['toggle_emergency']) && isset($_POST['status'])) {
    $doctor_id = $_POST['toggle_emergency'];
    $new_status = $_POST['status'] == 1 ? 1 : 0;
    
    // Update emergency status in database
    $update_query = "UPDATE doctors SET emergency_status = $new_status WHERE doctor_id = $doctor_id";
    
    if (mysqli_query($con, $update_query)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
    exit();
}

// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // First, get doctor picture to delete file
    $pic_query = "SELECT doctor_pic FROM doctors WHERE doctor_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $doctor_pic_data = mysqli_fetch_assoc($pic_result);
    $doctor_pic = $doctor_pic_data ? $doctor_pic_data['doctor_pic'] : '';
    
    // Delete doctor from database
    $delete_query = "DELETE FROM doctors WHERE doctor_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete picture file if it exists
        if (!empty($doctor_pic)) {
            $pic_path = BASE_PATH."/admin/inc/uploads/doctors/".$doctor_pic;
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
        }
        $_SESSION['success_msg'] = "Doctor deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    
    // Redirect to doctors list
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Get doctor ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}

$doctor_id = $_GET['id'];

// Fetch doctor details with related information
$query = "SELECT d.*, 
                c.city_name,
                h.hospital_name,
                ci.morning_opening_time,
                ci.morning_closing_time,
                ci.shift,
                ci.season,
                ci.contact as clinical_contact,
                ci.days,
                ci.off_days,
                dc.cat_name,
                dct.type as cat_type,
                e.status as estatus,
                e.reference as ref,
                u.username,
                u.password
          FROM doctors d 
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
          LEFT JOIN doctor_in_hospital dih ON d.doctor_id = dih.doctor_id
          LEFT JOIN clinical_info ci ON dih.doctor_in_hosp_id = ci.doctor_in_hosp_id
          LEFT JOIN dr_cat_types dct ON dct.dr_cat_type_id = d.cat_type_id
          LEFT JOIN dr_categories dc ON dc.dr_cat_id = dct.dr_cat_id
          LEFT JOIN entities e ON e.entity_id = d.entity_id
          LEFT JOIN users u ON u.user_id = d.user_id
          WHERE d.doctor_id = $doctor_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/doctors/list');
    exit();
}

$doctor = mysqli_fetch_assoc($result);

// Fetch feedbacks for this doctor
$feedback_query = "SELECT f.* FROM feedback f WHERE f.entity_id = $entity_id ORDER BY f.created_at DESC LIMIT 10";
$feedback_result = mysqli_query($con, $feedback_query);


// Fetch clinical information for this doctor
 $clinical_query = "SELECT clinical_info.*,hospitals.hospital_name,hospitals.hospital_id
 FROM clinical_info 
   left join doctor_in_hospital dih on clinical_info.doctor_in_hosp_id = dih.doctor_in_hosp_id
   left join hospitals on dih.hospital_id = hospitals.hospital_id
WHERE dih.doctor_id = $doctor_id ORDER BY clinical_info.season, clinical_info.shift";

$clinical_result = mysqli_query($con, $clinical_query);
?>

<style>
    .doctor-profile-header {
        background: #ea6666;
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .doctor-profile-header h2 {
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .doctor-profile-header p {
        color: rgba(255,255,255,0.95);
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        font-size: 16px;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .doctor-profile-header i {
        color: rgba(255,255,255,0.9);
        margin-right: 10px;
        font-size: 16px;
    }
    
    .doctor-avatar {
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
        padding: 6px 25px;
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
    .icon-size{
      font-size: 10px !important;
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
    .padding-10{
      padding: 10px !important;
    }
</style>

<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <!-- Doctor Profile Header -->
      <div class="row">
         <div class="col-12 padding">
            <div class="doctor-profile-header">
               <div class="row align-items-center">
                  <div class="col-md-2 text-center">
                     <?php if (!empty($doctor['doctor_pic'])): ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                             alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
                     <?php else: ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/doctor.jpg" 
                             alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-avatar">
                     <?php endif; ?>
                  </div>
                  <div class="col-md-10">
                     <h2 class="mb-2">Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></h2>
                     <p class="mb-1"><i class="fas fa-stethoscope me-2"></i><?php echo htmlspecialchars($doctor['cat_type']); ?></p>
                     <small class="mb-1"><i class="fas fa-stethoscope me-2"></i><?php echo htmlspecialchars($doctor['cat_name']); ?></small>
                     <p class="mb-0"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($doctor['short_detail']); ?></p>
                     <p class="mb-0"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($doctor['other']); ?></p>
                  </div>
               </div>
            </div>
         </div>
      </div>
      

      <!-- Doctor Emergency Not Available -->
      <div class="row">
         <div class="col-lg-12">
            <div class="info-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-exclamation-triangle me-2"></i>Emergency Status</h5>
               </div>
               <div class="info-card-body">
                  <div class="row align-items-center">
                     <div class="col-md-8">
                        <div class="emergency-status-display">
                           <?php if ($doctor['emergency_status'] == 1): ?>
                              <span class="badge-modern badge bg-warning padding-10">
                                 <i class="fas fa-exclamation-triangle me-1"></i>
                                 Emergency Not Available Today
                              </span>
                           <?php else: ?>
                              <span class="badge-modern badge bg-success padding-10">
                                 <i class="fas fa-check-circle me-1"></i>
                                  Available
                              </span>
                           <?php endif; ?>
                        </div>
                     </div>
                     <div class="col-md-4 text-end">
                        <?php if ($doctor['estatus'] == 1): ?>
                           <?php if ($doctor['emergency_status'] == 1): ?>
                              <button onclick="toggleEmergencyStatus(<?php echo $doctor['doctor_id']; ?>, 0)" class="btn-action btn-success">
                                 <i class="fas fa-check me-2"></i>
                                 Enable Emergency
                              </button>
                           <?php else: ?>
                              <button onclick="toggleEmergencyStatus(<?php echo $doctor['doctor_id']; ?>, 1)" class="btn-action btn-warning">
                                 <i class="fas fa-times me-2"></i>
                                 Disable Emergency
                              </button>
                           <?php endif; ?>
                        <?php else: ?>
                           <button class="btn-action btn-secondary" disabled>
                              <i class="fas fa-ban me-2"></i>
                              Doctor Inactive
                           </button>
                        <?php endif; ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Doctor Information -->
      <div class="row">
         <div class="col-lg-6">
            <div class="info-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-user-md me-2"></i>Personal Information</h5>
               </div>
               <div class="info-card-body">

                <div class="info-item">
                     <span class="info-label">Username</span>
                     <span class="info-value"><?php echo $doctor['username']; ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Password</span>
                     <span class="info-value"><?php echo base64_decode($doctor['password']); ?></span>
                  </div>


                  <div class="info-item">
                     <span class="info-label">Doctor ID</span>
                     <span class="info-value"><?php echo $doctor['doctor_id']; ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Experience</span>
                     <span class="info-value"><?php echo $doctor['experience_years']; ?> Years</span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Phone</span>
                     <span class="info-value"><?php echo htmlspecialchars($doctor['doctor_phone']); ?></span>
                  </div>
                  <div class="info-item">
                     <span class="info-label">Email</span>
                     <span class="info-value"><?php echo htmlspecialchars($doctor['doctor_email']); ?></span>
                  </div>
                  <?php if (!empty($doctor['short_detail'])): ?>
                  <div class="info-item">
                     <span class="info-label">Short Detail</span>
                     <span class="info-value"><?php echo htmlspecialchars($doctor['short_detail']); ?></span>
                  </div>
                  <?php endif; ?>
                  <div class="info-item">
                     <span class="info-label">Status</span>
                     <span class="info-value">
                        <?php if ($doctor['estatus'] == 1): ?>
                           <span class="badge-status badge-active">Active</span>
                        <?php else: ?>
                           <span class="badge-status badge-inactive">Inactive</span>
                        <?php endif; ?>
                     </span>
                  </div>

                  <div class="info-item">
                     <?php
                        if ($doctor['estatus'] == 0){ ?>
                            <span class="info-label">Inactive Status Detail</span>
                            <p class="text-danger"> <?=$doctor['ref']?> </p>
                            <?php
                        }
                     ?>
                  </div>



                  <?php if (!empty($doctor['static_clinical_info'])): ?>
                  <div class="info-item" style="display: block; border-top: 1px solid #f1f3f4; padding-top: 15px;">
                     <span class="info-label" style="display: block; margin-bottom: 10px;">Clinical Info Summary</span>
                     <span class="info-value" style="display: block; text-align: left; background: #f8f9fa; padding: 10px; border-radius: 8px; border-left: 4px solid #667eea;">
                        <?php echo nl2br(htmlspecialchars($doctor['static_clinical_info'])); ?>
                     </span>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
         
         <div class="col-lg-6">
            <div class="info-card">
               <div class="info-card-header">
                  <h5><i class="fas fa-map-marker-alt me-2"></i>Location & Workplace</h5>
               </div>
               <div class="info-card-body">
                  <div class="info-item">
                     <span class="info-label">City</span>
                     <span class="info-value"><?php echo htmlspecialchars($doctor['city_name']); ?></span>
                  </div>
                  <?php if ($doctor['doctor_type'] == 1): ?>
                     <div class="info-item">
                        <span class="info-label">Type</span>
                        <span class="info-value">Hospital Doctor</span>
                     </div>
                     <div class="info-item">
                        <span class="info-label">Hospital</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['hospital_name']); ?></span>
                     </div>
                  <?php else: ?>
                     <div class="info-item">
                        <span class="info-label">Type</span>
                        <span class="info-value">Personal Clinic</span>
                     </div>
                     <div class="info-item">
                        <span class="info-label">Clinic Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['clinic_name']); ?></span>
                     </div>
                     <div class="info-item">
                        <span class="info-label">Clinic Address</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($doctor['clinic_address'])); ?></span>
                     </div>
                  <?php endif; ?>
                  <div class="info-item">
                     <span class="info-label">Created At</span>
                     <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($doctor['created_at'])); ?></span>
                  </div>
                  <?php if (!empty($doctor['updated_at'])): ?>
                  <div class="info-item">
                     <span class="info-label">Updated At</span>
                     <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($doctor['updated_at'])); ?></span>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
      
   <!-- Clinic Information -->
   <div class="row">
      <div class="col-lg-12">
         <div class="info-card">
            <div class="info-card-header d-flex justify-content-between align-items-center">
               <h5><i class="fas fa-map-marker-alt me-2"></i>Clinic Information</h5>
                     <?php  
                        if($ids !=0 && $ids !=''){ ?>
                           <a href="clinical-info?id=<?php echo $doctor_id; ?>" class="btn btn-warning">Update Clinical Info</a>
                        <?php }
                     ?>

            </div>
            <div class="info-card-body">
               <?php if (mysqli_num_rows($clinical_result) > 0): ?>
                  <?php 
                  // Group clinical data by season
                  $seasons_data = [];
                  mysqli_data_seek($clinical_result, 0);
                  while ($clinical = mysqli_fetch_assoc($clinical_result)) {
                      $season = strtolower(trim($clinical['season']));
                      if (empty($season)) {
                          $season = 'general';
                      }
                      if (!isset($seasons_data[$season])) {
                          $seasons_data[$season] = [];
                      }
                      $seasons_data[$season][] = $clinical;
                  }
                  
                  // Define season order and colors
                  $season_config = [
                      'summer' => ['name' => 'Summer', 'icon' => '', 'color' => '#ff6b6b'],
                      'winter' => ['name' => 'Winter', 'icon' => '', 'color' => '#4dabf7'],
                      'general' => ['name' => 'General', 'icon' => '', 'color' => '#868e96']
                  ];
                  ?>
                  
                  <?php foreach ($season_config as $season_key => $config): ?>
                     <?php if (isset($seasons_data[$season_key]) && !empty($seasons_data[$season_key])): ?>
                        <div class="season-group mb-4">
                           <div class="season-group-header" style="background: linear-gradient(135deg, <?php echo $config['color']; ?> 0%, <?php echo $config['color']; ?>dd 100%);">
                              <h4 class="mb-0">
                                 <span class="season-icon"><?php echo $config['icon']; ?></span>
                                 <?php echo $config['name']; ?> Season
                                 <span class="badge bg-light text-dark ms-2"><?php echo count($seasons_data[$season_key]); ?> Records</span>
                              </h4>
                           </div>
                           
                           <div class="season-group-body">
                              <div class="row">
                                 <?php foreach ($seasons_data[$season_key] as $clinical): ?>
                                    <div class="col-lg-6 mb-3">
                                       <h3 class="p-2">
                                          <?php 
                                                if($clinical['hospital_name'] !=''){
                                                    echo '<a href="' . BASE_URL . 'admin/hospitals/detail?id=' . $clinical['hospital_id'] . '" target="_blank">' . $clinical['hospital_name'] . '</a>';
                                                }else{echo 'Personal Clinic';}
                                          ?>
                                       </h3>
                                       <div class="clinical-record-card">
                                          <div class="clinical-record-body">
                                             <div class="row">
                                                <div class="col-md-6">
                                                   <div class="clinical-info-item">
                                                      <i class="fas fa-clock text-primary"></i>
                                                      <div>
                                                         <small class="text-muted">Timing</small>
                                                         <strong>
                                                          <?php
                                                                if(!empty($clinical['morning_opening_time'])){
                                                                    echo date('h:i A', strtotime($clinical['morning_opening_time']));
                                                                    echo " - ";
                                                                    echo date('h:i A', strtotime($clinical['morning_closing_time']));
                                                                }
         
                                                                if(!empty($clinical['evening_opening_time'])){
                                                                    echo "<br>";
                                                                    echo date('h:i A', strtotime($clinical['evening_opening_time']));
                                                                    echo " - ";
                                                                    echo date('h:i A', strtotime($clinical['evening_closing_time']));
                                                                }
                                                            ?>  
                                                         </strong> 
                                                      </div>
                                                   </div>
                                                   <div class="clinical-info-item">
                                                      <i class="fas fa-calendar-day text-success"></i>
                                                      <div>
                                                         <small class="text-muted">Working Days</small>
                                                         <strong><?php echo htmlspecialchars($clinical['days']); ?></strong>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-md-6">
                                                   <div class="clinical-info-item">
                                                      <i class="fas fa-user-md text-info"></i>
                                                      <div>
                                                         <small class="text-muted">Shift</small>
                                                         <strong>
                                                            <?php
                                                                if(!empty($clinical['morning_opening_time'])){
                                                                    echo 'Morning';
                                                                }
                                                                if(!empty($clinical['evening_opening_time'])){
                                                                    echo '<br> Evening';
                                                                }
                                                            ?>
                                                         </strong>
                                                      </div>
                                                   </div>
                                                   <div class="clinical-info-item">
                                                      <i class="fas fa-calendar-times text-danger"></i>
                                                      <div>
                                                         <small class="text-muted">Off Days</small>
                                                         <strong><?php echo htmlspecialchars($clinical['off_days']); ?></strong>
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                             <div class="clinical-contact">
                                                <i class="fas fa-phone text-warning"></i>
                                                <div>
                                                   <small class="text-muted">Contact</small>
                                                   <strong><?php echo htmlspecialchars($clinical['contact']); ?></strong>
                                                </div>
                                             </div>
                                             <?php if (!empty($clinical['detail'])): ?>
                                             <div class="clinical-detail">
                                                <i class="fas fa-info-circle text-primary"></i>
                                                <div>
                                                   <small class="text-muted">Detail</small>
                                                   <strong><?php echo htmlspecialchars($clinical['detail']); ?></strong>
                                                </div>
                                             </div>
                                             <?php endif; ?>
                                             <div class="clinical-actions mt-3">
                                                <a href="edit-clinical-info?id=<?=$clinical['clinical_info_id']?>&doctor_id=<?=$_GET['id']?>" class="btn-action btn-edit me-2 clinical-edit-trigger" title="Update" data-clinical-id="<?php echo $clinical['clinical_info_id']; ?>">
                                                   <i class="fas fa-edit icon-size"></i>
                                                </a>
                                                <a href="javascript:void(0)" onclick="deleteClinical(<?php echo $clinical['clinical_info_id']; ?>)" class="btn-action btn-delete" title="Delete">
                                                   <i class="fas fa-trash icon-size"></i>
                                                </a>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                        </div>
                     <?php endif; ?>
                  <?php endforeach; ?>
               <?php else: ?>
                  <div class="text-center py-5">
                     <i class="fas fa-clinic-medical fa-3x text-muted mb-3"></i>
                     <p class="text-muted">No clinical information found for this doctor.</p>
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
                  <a href="<?php echo BASE_URL; ?>admin/doctors/add?id=<?php echo $doctor['doctor_id']; ?>" class="btn-action btn-edit me-3">
                     <i class="fas fa-edit"></i> Edit Doctor
                  </a>
                  <a href="javascript:void(0)" onclick="deleteDoctor(<?php echo $doctor['doctor_id']; ?>)" class="btn-action btn-delete me-3">
                     <i class="fas fa-trash"></i> Delete Doctor
                  </a>
                  <a href="<?php echo BASE_URL; ?>admin/doctors/list" class="btn-action btn-back">
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
                        <p class="text-muted">No feedbacks found for this doctor.</p>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- Container-fluid ends -->
</div>

<!-- Clinical Edit Modal -->
<div class="modal fade" id="clinicalEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="clinicalEditForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Clinical Timing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="clinical_info_id" id="clinicalInfoId">
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-clock me-2"></i>Opening Time</label>
          <input type="time" class="form-control" name="morning_opening_time" id="MorningopeningTime" required>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-clock me-2"></i>Closing Time</label>
          <input type="time" class="form-control" name="morning_closing_time" id="MorningclosingTime" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
  </div>

<script>
function deleteDoctor(doctorId) {
    if (confirm('Are you sure you want to delete this doctor? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + doctorId;
    }
}

function toggleEmergencyStatus(doctorId, newStatus) {
    var action = newStatus == 1 ? 'disable emergency services' : 'enable emergency services';
    if (confirm('Are you sure you want to ' + action + ' for this doctor?')) {
        // Create AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Reload the page to show updated status
                window.location.reload();
            }
        };
        
        xhr.send('toggle_emergency=' + doctorId + '&status=' + newStatus);
    }
}


function deleteClinical(clinicalId) {
    if (confirm('Are you sure you want to delete this clinical information? This action cannot be undone.')) {
        window.location.href = 'profile?id=<?php echo $doctor['doctor_id']; ?>&del_clinic_id=' + clinicalId;
    }
}

// Clinical Edit Modal logic
$(function(){
  $(document).on('click', '.clinical-edit-trigger', function(e){
    e.preventDefault();
    var card = $(this).closest('.clinical-record-card');
    var id = $(this).data('clinical-id') || card.data('clinical-id');
    var opening = card.data('opening-time');
    var closing = card.data('closing-time');
    $('#clinicalInfoId').val(id);
    $('#MorningopeningTime').val(opening);
    $('#MorningclosingTime').val(closing);
    $('#clinicalEditModal').modal('show');
  });

  $('#clinicalEditForm').on('submit', function(e){
    e.preventDefault();
    var form = $(this);
    var data = form.serialize() + '&action=update_clinical';
    $.post('edit-clinical-info.php', data, function(resp){
      $('#clinicalEditModal').modal('hide');
      var id = $('#clinicalInfoId').val();
      var opening = $('#MorningopeningTime').val();
      var closing = $('#MorningclosingTime').val();
      var card = $('.clinical-record-card[data-clinical-id="'+id+'"]');
      card.data('opening-time', opening);
      card.data('closing-time', closing);
      // Update displayed timing text
      var formatted = formatTime(opening) + ' - ' + formatTime(closing);
      card.find('.clinical-info-item .text-muted:contains("Timing")').next('strong').text(formatted);
    }).fail(function(){
      alert('Failed to update clinical information.');
    });
  });

  function formatTime(hm){
    try {
      var parts = hm.split(':');
      var h = parseInt(parts[0], 10);
      var m = parts[1];
      var ampm = h >= 12 ? 'PM' : 'AM';
      h = h % 12; if (h === 0) h = 12;
      return h + ':' + m + ' ' + ampm;
    } catch(e) { return hm; }
  }
});

</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>
