<?php include '../config.php'; ?>

<?php
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
    
    // First, get the doctor picture to delete the file
    $pic_query = "SELECT doctor_pic FROM doctors WHERE doctor_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $doctor_pic_data = mysqli_fetch_assoc($pic_result);
    $doctor_pic = $doctor_pic_data ? $doctor_pic_data['doctor_pic'] : '';
    
    // Delete the doctor from database
    $delete_query = "DELETE FROM doctors WHERE doctor_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete the picture file if it exists
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
    
    // Redirect to remove delete_id from URL
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
// Pagination variables
$records_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search filters
$search_doctor = isset($_GET['search_doctor']) ? mysqli_real_escape_string($con, $_GET['search_doctor']) : '';
$search_city = isset($_GET['search_city']) ? mysqli_real_escape_string($con, $_GET['search_city']) : '';
$filter_city_id = isset($_GET['filter_city_id']) ? mysqli_real_escape_string($con, $_GET['filter_city_id']) : '';
$filter_specialization = isset($_GET['filter_specialization']) ? mysqli_real_escape_string($con, $_GET['filter_specialization']) : '';
$filter_emergency = isset($_GET['filter_emergency']) ? mysqli_real_escape_string($con, $_GET['filter_emergency']) : '';
$gender = isset($_GET['gender']) ? mysqli_real_escape_string($con, $_GET['gender']) : '';

// Build WHERE clause
$where_conditions = [];
if (!empty($search_doctor)) {
    $where_conditions[] = "(d.doctor_name LIKE '%$search_doctor%' OR d.specialization LIKE '%$search_doctor%')";
}
if (!empty($search_doctor)) {
    $where_conditions[] = "(d.doctor_name LIKE '%$search_doctor%')";
}
if (!empty($search_city)) {
    $where_conditions[] = "c.city_name LIKE '%$search_city%'";
}
if (!empty($filter_city_id) && is_numeric($filter_city_id)) {
    $where_conditions[] = "d.city_id = $filter_city_id";
}
if (!empty($filter_specialization) && is_numeric($filter_specialization)) {
    $where_conditions[] = "d.cat_type_id = $filter_specialization";
}
if ($filter_emergency == '1') {
    $where_conditions[] = "d.emergency_status = 1";
}
if ($gender != '') {
    $where_conditions[] = "d.gender = '$gender'";
}
$where_conditions[] = "d.approve = 1";
// Fetch cities for dropdown
$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// Fetch specializations for dropdown
$specializations_query = "SELECT dr_cat_type_id, type FROM dr_cat_types ORDER BY type ASC";
$specializations_result = mysqli_query($con, $specializations_query);

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total records
 $count_query = "SELECT COUNT(*) as total 
                FROM doctors d
                LEFT JOIN cities c ON d.city_id = c.city_id
                LEFT JOIN dr_cat_types on dr_cat_types.dr_cat_type_id  = d.cat_type_id
                $where_clause"; 
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch doctors data with related information
 $query = "SELECT d.*, 
                c.city_name,
                h.hospital_name,
                COUNT(f.feedback_id) as total_feedbacks,
                AVG(f.stars) as avg_rating,
                dct.type as speciality
          FROM doctors d
          LEFT JOIN cities c ON d.city_id = c.city_id
          LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
          LEFT JOIN feedback f ON d.entity_id = f.entity_id
          LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id
          $where_clause
          GROUP BY d.doctor_id
          ORDER BY d.created_at DESC 
          LIMIT $offset, $records_per_page";
$result = mysqli_query($con, $query);
?>

<style>
   .doctor-profile-card {
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 15px;
      overflow: hidden;
      background: #ffffff;
      height: 100%;
   }
   
   .doctor-profile-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 20px rgba(0,0,0,0.12);
   }
   
   .doctor-profile-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 12px 10px;
      color: white;
   }
   
   .doctor-profile-body {
      padding: 12px 10px;
   }
   
   .doctor-avatar {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
   }
   
   .doctor-info-item {
      margin-bottom: 8px;
      padding-bottom: 8px;
      border-bottom: 1px solid #e9ecef;
   }
   
   .doctor-info-item:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
   }
   
   .info-label {
      font-weight: 600;
      color: #495057;
      font-size: 0.75rem;
      margin-bottom: 3px;
   }
   
   .info-value {
      color: #212529;
      font-size: 0.85rem;
   }
   
   .doctor-profile-header h5 {
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 5px;
   }
   
   .doctor-profile-header .text-light {
      font-size: 0.75rem;
   }
   
   .badge-modern {
      padding: 3px 8px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.7rem;
   }
   
   .emergency-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 11px;
      font-weight: 600;
      z-index: 10;
      box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
      display: flex;
      align-items: center;
      gap: 5px;
      animation: pulse 2s infinite;
   }
   
   .emergency-badge i {
      font-size: 12px;
   }
   
   @keyframes pulse {
      0% {
         box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
      }
      50% {
         box-shadow: 0 3px 15px rgba(220, 53, 69, 0.5);
      }
      100% {
         box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
      }
   }
   
   .feedback-section {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 20px;
      margin-top: 20px;
   }
   
   .feedback-item {
      background: white;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
      border-left: 4px solid #667eea;
   }
   
   .feedback-item:last-child {
      margin-bottom: 0;
   }
   
   .star-rating {
      color: #ffc107;
      font-size: 1.1rem;
   }
   
   .badge-modern {
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;
   }
   
   .search-filter-card {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      border-radius: 16px;
      padding: 35px;
      margin-bottom: 40px;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.1);
      position: relative;
      overflow: hidden;
   }
   
   .search-filter-card::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
      animation: shimmer 3s ease-in-out infinite;
   }
   
   @keyframes shimmer {
      0% { transform: rotate(0deg); }
      50% { transform: rotate(180deg); }
      100% { transform: rotate(360deg); }
   }
   
   .search-filter-card h5 {
      color: #ffffff;
      font-weight: 800;
      font-size: 1.5rem;
      margin-bottom: 30px;
      text-shadow: 0 3px 6px rgba(0,0,0,0.4);
      position: relative;
      z-index: 1;
      letter-spacing: 1px;
      text-transform: uppercase;
   }
   
   .search-filter-card .form-label {
      color: #e8e8e8;
      font-weight: 700;
      font-size: 0.9rem;
      margin-bottom: 10px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
      letter-spacing: 0.5px;
      text-transform: uppercase;
   }
   
   .search-filter-card .form-control,
   .search-filter-card .form-select {
      background: rgba(255,255,255,0.08);
      border: 2px solid rgba(255,255,255,0.15);
      border-radius: 10px;
      padding: 14px 18px;
      font-size: 0.95rem;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: inset 0 2px 8px rgba(0,0,0,0.2);
      color: #ffffff;
   }
   
   .search-filter-card .form-control::placeholder,
   .search-filter-card .form-select::placeholder {
      color: rgba(255,255,255,0.6);
   }
   
   .search-filter-card .form-control:focus,
   .search-filter-card .form-select:focus {
      background: rgba(255,255,255,0.12);
      border-color: #4dabf7;
      box-shadow: 0 8px 25px rgba(77, 171, 247, 0.3), inset 0 2px 8px rgba(0,0,0,0.2);
      transform: translateY(-3px);
      color: #ffffff;
      outline: none;
   }
   
   .search-filter-card .btn {
      border-radius: 10px;
      padding: 14px 24px;
      font-weight: 700;
      font-size: 0.95rem;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 6px 20px rgba(0,0,0,0.3);
      border: none;
      text-transform: uppercase;
      letter-spacing: 1px;
      position: relative;
      overflow: hidden;
   }
   
   .search-filter-card .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
   }
   
   .search-filter-card .btn:hover::before {
      left: 100%;
   }
   
   .search-filter-card .btn-primary {
      background: linear-gradient(135deg, #4dabf7 0%, #339af0 50%, #228be6 100%);
      color: #ffffff;
   }
   
   .search-filter-card .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(77, 171, 247, 0.5);
      background: linear-gradient(135deg, #339af0 0%, #228be6 50%, #1c7ed6 100%);
   }
   
   .search-filter-card .btn-secondary {
      background: linear-gradient(135deg, #495057 0%, #343a40 50%, #212529 100%);
      color: #ffffff;
   }
   
   .search-filter-card .btn-secondary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(73, 80, 87, 0.5);
      background: linear-gradient(135deg, #343a40 0%, #212529 50%, #000000 100%);
   }
   
   .form-check-input {
      background-color: rgba(255,255,255,0.1);
      border: 2px solid rgba(255,255,255,0.3);
      border-radius: 6px;
      width: 20px;
      height: 20px;
      transition: all 0.3s ease;
   }
   
   .form-check-input:checked {
      background-color: #4dabf7;
      border-color: #4dabf7;
      box-shadow: 0 0 10px rgba(77, 171, 247, 0.5);
   }
   
   .form-check-label {
      color: #e8e8e8;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      margin-left: 10px;
      text-shadow: 0 1px 3px rgba(0,0,0,0.3);
   }
   
   .checkbox-wrapper {
      background: rgba(255,255,255,0.05);
      border-radius: 12px;
      padding: 18px;
      border: 1px solid rgba(255,255,255,0.1);
      padding: 15px;
      border: 2px solid rgba(255,255,255,0.2);
      transition: all 0.3s ease;
   }
   
   .checkbox-wrapper:hover {
      background: rgba(255,255,255,0.15);
      border-color: rgba(255,255,255,0.3);
   }
   .height{
      height: 32% !important;
      margin-bottom: 10px;
   }
</style>

<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <div class="row">
         <div class="main-header">
            <h4>
               <i class="fas fa-user-md me-2"></i>Doctors List
            </h4>
            <a href="<?php echo BASE_URL; ?>admin/doctors/add" class="btn btn-primary pull-right">
               <i class="icon-plus"></i> Add Doctor
            </a>
         </div>
      </div>
      
      <?php 
      // Display session messages
      if (isset($_SESSION['success_msg'])): ?>
         <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error_msg'])): ?>
         <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
      <?php endif; ?>
      
      <!-- Search and Filter Section -->
      <div class="row">
         <div class="col-lg-12">
            <div class="search-filter-card">
               <h5 class="mb-4">
                  <i class="fas fa-search me-2"></i>Search & Filter
               </h5>
               <form method="GET" action="">
                  <div class="row g-3">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="search_doctor" class="form-label">
                              <i class="fas fa-user-md me-1"></i>Search Doctor
                           </label>
                           <input type="text" class="form-control" id="search_doctor" name="search_doctor" 
                                  value="<?php echo htmlspecialchars($search_doctor); ?>" 
                                  placeholder="Name or Specialization">
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="filter_city_id" class="form-label">
                              <i class="fas fa-city me-1"></i>Filter by City
                           </label>
                           <select class="form-select" id="filter_city_id" name="filter_city_id">
                              <option value="">All Cities</option>
                              <?php 
                              mysqli_data_seek($cities_result, 0);
                              while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                 <option value="<?php echo $city['city_id']; ?>" 
                                         <?php echo ($filter_city_id == $city['city_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city['city_name']); ?>
                                 </option>
                              <?php endwhile; ?>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="filter_specialization" class="form-label">
                              <i class="fas fa-stethoscope me-1"></i>Specialization
                           </label>
                           <select class="form-select" id="filter_specialization" name="filter_specialization" data-dropdown-css-class="select2-bootstrap-5-dropdown">
                              <option value="">All Specializations</option>
                              <?php 
                              while ($specialization = mysqli_fetch_assoc($specializations_result)): ?>
                                 <option value="<?php echo $specialization['dr_cat_type_id']; ?>" 
                                         <?php echo ($filter_specialization == $specialization['dr_cat_type_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($specialization['type']); ?>
                                 </option>
                              <?php endwhile; ?>
                           </select>
                        </div>
                     </div>
                  </div>
                  <div class="row g-3 mt-2">
                     <div class="col-md-6">
                        <div class="checkbox-wrapper">
                           <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="filter_emergency" name="filter_emergency" value="1" 
                                     <?php echo (isset($_GET['filter_emergency']) && $_GET['filter_emergency'] == '1') ? 'checked' : ''; ?>>
                              <label class="form-check-label" for="filter_emergency">
                                 <i class="fas fa-exclamation-triangle me-1"></i>
                                 Not Available (Emergency Status)
                              </label>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="checkbox-wrapper">
                           <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="gender" name="gender" value="Female" 
                                     <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'checked' : ''; ?>>
                              <label class="form-check-label" for="gender">
                                 <i class="fas fa-exclamation-triangle me-1"></i>
                                 Lady Doctors
                              </label>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row mt-4">
                     <div class="col-md-6">
                        <button type="submit" class="btn btn-primary me-2">
                           <i class="fas fa-search me-1"></i>Search Filters
                        </button>
                        <a href="<?php echo BASE_URL; ?>admin/doctors/list" class="btn btn-secondary">
                           <i class="fas fa-redo me-1"></i>Reset All
                        </a>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      
      <!-- Doctors List -->
      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">
                     <i class="fas fa-list me-2"></i>Doctors (<?php echo $total_records; ?> records)
                  </h5>
               </div>
               <div class="card-block">
                  <?php if (mysqli_num_rows($result) > 0): ?>
                     <div class="row mb-3">
                        <?php while ($doctor = mysqli_fetch_assoc($result)): ?>
                           <div class="col-lg-2 col-md-2 col-sm-3 col-xs-4 mb-4 height">
                              <div class="doctor-profile-card text-center">
                                 <?php if ($doctor['emergency_status'] == 1): ?>
                                    <div class="emergency-badge bg-danger text-white">
                                       <i class="fas fa-exclamation-triangle"></i>
                                       Emergency Not Available Today
                                    </div>
                                 <?php endif; ?>
                                 
                                 <div class="doctor-profile-header">
                                    <?php if (!empty($doctor['doctor_pic'])): ?>
                                       <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                            alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" 
                                            class="doctor-avatar mb-2">
                                    <?php else: ?>
                                       <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/doctor.jpg" 
                                            alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" 
                                            class="doctor-avatar mb-2">
                                    <?php endif; ?>
                                    <h5 class="mb-1">
                                       <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                                    </h5>
                                    <div class="text-light small mb-2">
                                       <i class="fas fa-stethoscope me-1"></i>
                                       <?php echo htmlspecialchars($doctor['speciality']); ?>
                                    </div>
                                    <div class="d-flex justify-content-center flex-wrap gap-1">
                                       <?php if ($doctor['status'] == 1): ?>
                                          <?php if ($doctor['emergency_status'] == 1): ?>
                                             <span class="badge-modern badge bg-warning" onclick="toggleEmergencyStatus(<?php echo $doctor['doctor_id']; ?>, 0)" style="cursor: pointer;" title="Click to enable emergency">Active</span>
                                          <?php else: ?>
                                             <span class="badge-modern badge bg-success" onclick="toggleEmergencyStatus(<?php echo $doctor['doctor_id']; ?>, 1)" style="cursor: pointer;" title="Click to disable emergency">Active</span>
                                          <?php endif; ?>
                                       <?php else: ?>
                                          <span class="badge-modern badge bg-danger">Inactive</span>
                                       <?php endif; ?>
                                       
                                       <?php if ($doctor['doctor_type'] == 1): ?>
                                          <span class="badge-modern badge bg-info">
                                             <i class="fas fa-hospital me-1"></i>Hospital
                                          </span>
                                       <?php else: ?>
                                          <span class="badge-modern badge bg-success">
                                             <i class="fas fa-clinic-medical me-1"></i>Clinic
                                          </span>
                                       <?php endif; ?>
                                       
                                       <span class="badge-modern badge bg-secondary">
                                          <i class="fas fa-comments me-1"></i>
                                          <?php echo (int)$doctor['total_feedbacks']; ?> Feedbacks
                                       </span>
                                    </div>
                                 </div>
                                 
                                 <div class="doctor-profile-body">
                                    <div class="doctor-info-item text-start">
                                       <div class="info-label">
                                          <i class="fas fa-city me-1 text-secondary"></i>City
                                       </div>
                                       <div class="info-value">
                                          <?php echo htmlspecialchars($doctor['city_name'] ?? 'N/A'); ?>
                                       </div>
                                    </div>
                                    
                                    <?php if ($doctor['doctor_type'] == 1 && !empty($doctor['hospital_name'])): ?>
                                       <div class="doctor-info-item text-start">
                                          <div class="info-label">
                                             <i class="fas fa-hospital me-1 text-danger"></i>Hospital
                                          </div>
                                          <div class="info-value">
                                             <?php echo htmlspecialchars($doctor['hospital_name']); ?>
                                          </div>
                                       </div>
                                    <?php elseif ($doctor['doctor_type'] == 2): ?>
                                       <div class="doctor-info-item text-start">
                                          <div class="info-label">
                                             <i class="fas fa-clinic-medical me-1 text-success"></i>Clinic Name
                                          </div>
                                          <div class="info-value">
                                             <?php echo htmlspecialchars($doctor['clinic_name'] ?? 'N/A'); ?>
                                          </div>
                                       </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                       <a href="<?php echo BASE_URL; ?>admin/doctors/profile?id=<?php echo $doctor['doctor_id']; ?>" 
                                          class="btn btn-primary btn-xs" style="font-size: 0.7rem; padding: 4px 8px;">
                                          <i class="fas fa-user me-1"></i> View
                                       </a>
                                        <a href="<?php echo BASE_URL; ?>admin/doctors/assign-hospitals?id=<?php echo $doctor['doctor_id']; ?>" 
                                          class="btn btn-secondary btn-xs" style="font-size: 0.7rem; padding: 4px 8px;">
                                          <i class="fas fa-user me-1"></i> Assign
                                       </a>
                                       <div>
                                          <a href="<?php echo BASE_URL; ?>admin/doctors/add?id=<?php echo $doctor['doctor_id']; ?>" 
                                             class="btn btn-xs btn-warning" title="Edit" style="font-size: 0.7rem; padding: 4px 8px;">
                                             <i class="icon-pencil"></i>
                                          </a>
                                          <a href="javascript:void(0)" onclick="deleteDoctor(<?php echo $doctor['doctor_id']; ?>)" 
                                             class="btn btn-xs btn-danger" title="Delete" style="font-size: 0.7rem; padding: 4px 8px;">
                                             <i class="icon-trash"></i>
                                          </a>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        <?php endwhile; ?>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>No doctors found.
                     </div>
                  <?php endif; ?>
                  
                  <!-- Pagination -->
                  <?php if ($total_pages > 1): ?>
                     <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                           <?php if ($page > 1): ?>
                              <li class="page-item">
                                 <a class="page-link" href="?page=<?php echo $page - 1; ?>&search_doctor=<?php echo urlencode($search_doctor); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_specialization=<?php echo $filter_specialization; ?>&filter_emergency=<?php echo $filter_emergency; ?>&gender=<?php echo $gender; ?>">
                                    <i class="fas fa-chevron-left me-1"></i>Previous
                                 </a>
                              </li>
                           <?php endif; ?>
                           
                           <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                              <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                 <a class="page-link" href="?page=<?php echo $i; ?>&search_doctor=<?php echo urlencode($search_doctor); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_specialization=<?php echo $filter_specialization; ?>&filter_emergency=<?php echo $filter_emergency; ?>&gender=<?php echo $gender; ?>">
                                    <?php echo $i; ?>
                                 </a>
                              </li>
                           <?php endfor; ?>
                           
                           <?php if ($page < $total_pages): ?>
                              <li class="page-item">
                                 <a class="page-link" href="?page=<?php echo $page + 1; ?>&search_doctor=<?php echo urlencode($search_doctor); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_specialization=<?php echo $filter_specialization; ?>&filter_emergency=<?php echo $filter_emergency; ?>&gender=<?php echo $gender; ?>">
                                    Next<i class="fas fa-chevron-right ms-1"></i>
                                 </a>
                              </li>
                           <?php endif; ?>
                        </ul>
                     </nav>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- Container-fluid ends -->
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
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>


<script>
$(document).ready(function() {
    $('#filter_city_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search city...',
        allowClear: true,
        width: '100%'
    });
    
    $('#filter_specialization').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search specialization...',
        allowClear: true,
        width: '100%'
    });
});
</script>