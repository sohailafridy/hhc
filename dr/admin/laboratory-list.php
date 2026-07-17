<?php include '../config.php'; ?>

<?php
// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // First, get the laboratory picture to delete the file
    $pic_query = "SELECT lab_pic FROM laboratories WHERE lab_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $lab_pic_data = mysqli_fetch_assoc($pic_result);
    $lab_pic = $lab_pic_data ? $lab_pic_data['lab_pic'] : '';
    
    // Delete the laboratory from database
    $delete_query = "UPDATE entities set status=0 WHERE entity_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete the picture file if it exists
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
    
    // Redirect to remove delete_id from URL
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
// Pagination variables
$records_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search filters
$search_lab = isset($_GET['search_lab']) ? mysqli_real_escape_string($con, $_GET['search_lab']) : '';
$search_city = isset($_GET['search_city']) ? mysqli_real_escape_string($con, $_GET['search_city']) : '';
$filter_city_id = isset($_GET['filter_city_id']) ? mysqli_real_escape_string($con, $_GET['filter_city_id']) : '';
$filter_status = isset($_GET['filter_status']) ? mysqli_real_escape_string($con, $_GET['filter_status']) : '';
$filter_type = isset($_GET['filter_type']) ? mysqli_real_escape_string($con, $_GET['filter_type']) : '';

// Build WHERE clause
$where_conditions = [];
if (!empty($search_lab)) {
    $where_conditions[] = "(l.lab_name LIKE '%$search_lab%' OR l.lab_email LIKE '%$search_lab%')";
}
if (!empty($search_city)) {
    $where_conditions[] = "c.city_name LIKE '%$search_city%'";
}
if (!empty($filter_city_id) && is_numeric($filter_city_id)) {
    $where_conditions[] = "l.city_id = $filter_city_id";
}
if ($filter_status !== '') {
    $where_conditions[] = "e.status = $filter_status";
}else{
   $where_conditions[] = "e.status = 1";
}
if ($filter_type !== '') {
    $where_conditions[] = "l.lab_type = $filter_type";
}
$where_conditions[] = "l.approve = 1";
// Fetch cities for dropdown
$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total records
 $count_query = "SELECT COUNT(*) as total 
                FROM laboratories l
                LEFT JOIN entities e ON e.entity_id = l.entity_id
                LEFT JOIN cities c ON l.city_id = c.city_id
                $where_clause";


$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch laboratories data with related information
$query = "SELECT l.*, 
                c.city_name,
                h.hospital_name,
                e.entity_id as e_id,
                e.status as estatus,
                COUNT(f.feedback_id) as total_feedbacks,
                AVG(f.stars) as avg_rating
          FROM laboratories l
          LEFT JOIN cities c ON l.city_id = c.city_id
          LEFT JOIN hospitals h ON l.hospital_id = h.hospital_id
          LEFT JOIN feedback f ON l.entity_id = f.entity_id
          LEFT JOIN entities e ON e.entity_id = l.entity_id
          $where_clause
          GROUP BY l.lab_id
          ORDER BY l.created_at DESC 
          LIMIT $offset, $records_per_page";
$result = mysqli_query($con, $query);
?>

<style>
   .laboratory-profile-card {
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 15px;
      overflow: hidden;
      background: #ffffff;
      height: 100%;
   }
   
   .laboratory-profile-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 20px rgba(0,0,0,0.12);
   }
   
   .laboratory-profile-header {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      padding: 12px 10px;
      color: white;
   }
   
   .laboratory-profile-body {
      padding: 12px 10px;
   }
   
   .laboratory-avatar {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
   }
   
   .laboratory-info-item {
      margin-bottom: 8px;
      padding-bottom: 8px;
      border-bottom: 1px solid #e9ecef;
   }
   
   .laboratory-info-item:last-child {
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
   
   .laboratory-profile-header h5 {
      font-size: 0.95rem;
      font-weight: 600;
      margin-bottom: 5px;
   }
   
   .laboratory-profile-header .text-light {
      font-size: 0.75rem;
   }
   
   .badge-modern {
      padding: 3px 8px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.7rem;
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
      border-left: 4px solid #dc3545;
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
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 35px;
      box-shadow: 0 10px 40px rgba(220, 53, 69, 0.2);
      border: none;
      position: relative;
      overflow: hidden;
   }
   
   .search-filter-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
      pointer-events: none;
   }
   
   .search-filter-card h5 {
      color: white;
      font-weight: 700;
      font-size: 1.3rem;
      margin-bottom: 25px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2);
      position: relative;
      z-index: 1;
   }
   
   .search-filter-card .form-label {
      color: rgba(255,255,255,0.9);
      font-weight: 600;
      font-size: 0.85rem;
      margin-bottom: 8px;
      text-shadow: 0 1px 2px rgba(0,0,0,0.2);
   }
   
   .search-filter-card .form-control,
   .search-filter-card .form-select {
      background: rgba(255,255,255,0.95);
      border: 2px solid rgba(255,255,255,0.3);
      border-radius: 12px;
      padding: 12px 15px;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
   }
   
   .search-filter-card .form-control:focus,
   .search-filter-card .form-select:focus {
      background: white;
      border-color: rgba(255,255,255,0.8);
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      transform: translateY(-2px);
   }
   
   .search-filter-card .btn {
      border-radius: 12px;
      padding: 12px 20px;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      border: none;
      text-transform: uppercase;
      letter-spacing: 0.5px;
   }
   
   .search-filter-card .btn-primary {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
   }
   
   .search-filter-card .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
   }
   
   .search-filter-card .btn-secondary {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
   }
   
   .search-filter-card .btn-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
   }
   
   .form-check-input:checked {
      background-color: #dc3545;
      border-color: #dc3545;
   }
   
   .form-check-label {
      color: rgba(255,255,255,0.9);
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      margin-left: 8px;
   }
   
   .checkbox-wrapper {
      background: rgba(255,255,255,0.1);
      border-radius: 12px;
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
               <i class="fas fa-flask me-2"></i>Laboratories List
            </h4>
            <a href="<?php echo BASE_URL; ?>admin/laboratories/add" class="btn btn-primary pull-right">
               <i class="icon-plus"></i> Add Laboratory
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
                     <div class="col-md-3">
                        <div class="form-group">
                           <label for="search_lab" class="form-label">
                              <i class="fas fa-flask me-1"></i>Search Laboratory
                           </label>
                           <input type="text" class="form-control" id="search_lab" name="search_lab" 
                                  value="<?php echo htmlspecialchars($search_lab); ?>" 
                                  placeholder="Name or Email">
                        </div>
                     </div>
                     <div class="col-md-2">
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
                     <div class="col-md-2">
                        <div class="form-group">
                           <label for="filter_status" class="form-label">
                              <i class="fas fa-toggle-on me-1"></i>Status
                           </label>
                           <select class="form-select" id="filter_status" name="filter_status">
                              <option value="">All Status</option>
                              <option value="1" <?php echo ($filter_status == '1') ? 'selected' : ''; ?>>Active</option>
                              <option value="0" <?php echo ($filter_status == '0') ? 'selected' : ''; ?>>Inactive</option>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-2">
                        <div class="form-group">
                           <label for="filter_type" class="form-label">
                              <i class="fas fa-hospital me-1"></i>Type
                           </label>
                           <select class="form-select" id="filter_type" name="filter_type">
                              <option value="">All Types</option>
                              <option value="1" <?php echo ($filter_type == '1') ? 'selected' : ''; ?>>Hospital Lab</option>
                              <option value="2" <?php echo ($filter_type == '2') ? 'selected' : ''; ?>>Independent Lab</option>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2">
                           <i class="fas fa-search me-1"></i>Search Filters
                        </button>
                        <a href="<?php echo BASE_URL; ?>admin/laboratories/list" class="btn btn-secondary">
                           <i class="fas fa-redo me-1"></i>Reset All
                        </a>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
      
      <!-- Laboratories List -->
      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">
                     <i class="fas fa-list me-2"></i>Laboratories (<?php echo $total_records; ?> records)
                  </h5>
               </div>
               <div class="card-block">
                  <?php if (mysqli_num_rows($result) > 0): ?>
                     <div class="row mb-3">
                        <?php while ($lab = mysqli_fetch_assoc($result)): ?>
                           <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6 mb-5 height">
                              <div class="laboratory-profile-card text-center">
                                 <div class="laboratory-profile-header">
                                    <?php if (!empty($lab['lab_pic'])): ?>
                                       <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo $lab['lab_pic']; ?>" 
                                            alt="<?php echo htmlspecialchars($lab['lab_name']); ?>" 
                                            class="laboratory-avatar mb-2">
                                    <?php else: ?>
                                       <div class="laboratory-avatar bg-white d-flex align-items-center justify-content-center mb-2">
                                          <i class="fas fa-flask fa-3x text-danger"></i>
                                       </div>
                                    <?php endif; ?>
                                    <h5 class="mb-1">
                                       <?php echo htmlspecialchars($lab['lab_name']); ?>
                                    </h5>
                                    <div class="text-light small mb-2">
                                       <i class="fas fa-envelope me-1"></i>
                                       <?php echo htmlspecialchars($lab['lab_email']); ?>
                                    </div>
                                    <div class="d-flex justify-content-center flex-wrap gap-1">
                                       <?php if ($lab['estatus'] == 1): ?>
                                          <span class="badge-modern badge bg-success">Active</span>
                                       <?php else: ?>
                                          <span class="badge-modern badge bg-danger">Inactive</span>
                                       <?php endif; ?>
                                       
                                       <?php if ($lab['lab_type'] == 1): ?>
                                          <span class="badge-modern badge bg-info">
                                             <i class="fas fa-hospital me-1"></i>Hospital Lab
                                          </span>
                                       <?php else: ?>
                                          <span class="badge-modern badge bg-warning">
                                             <i class="fas fa-building me-1"></i>Independent
                                          </span>
                                       <?php endif; ?>
                                       
                                       <span class="badge-modern badge bg-secondary">
                                          <i class="fas fa-comments me-1"></i>
                                          <?php echo (int)$lab['total_feedbacks']; ?> Feedbacks
                                       </span>
                                    </div>
                                 </div>
                                 
                                 <div class="laboratory-profile-body">
                                    <div class="laboratory-info-item text-start">
                                       <div class="info-label">
                                          <i class="fas fa-city me-1 text-secondary"></i>City
                                       </div>
                                       <div class="info-value">
                                          <?php echo htmlspecialchars($lab['city_name'] ?? 'N/A'); ?>
                                       </div>
                                    </div>
                                    
                                    <?php if ($lab['lab_type'] == 1 && !empty($lab['hospital_name'])): ?>
                                       <div class="laboratory-info-item text-start">
                                          <div class="info-label">
                                             <i class="fas fa-hospital me-1 text-danger"></i>Hospital
                                          </div>
                                          <div class="info-value">
                                             <?php echo htmlspecialchars($lab['hospital_name']); ?>
                                          </div>
                                       </div>
                                    <?php endif; ?>
                                    
                                    <div class="laboratory-info-item text-start">
                                       <div class="info-label">
                                          <i class="fas fa-phone me-1 text-primary"></i>Contact
                                       </div>
                                       <div class="info-value">
                                          <?php echo htmlspecialchars($lab['lab_phone']); ?>
                                       </div>
                                    </div>
                                    
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                       <a href="<?php echo BASE_URL; ?>admin/laboratories/detail?id=<?php echo $lab['lab_id']; ?>" 
                                          class="btn btn-primary btn-xs" style="font-size: 0.7rem; padding: 4px 8px;">
                                          <i class="fas fa-eye me-1"></i> View
                                       </a>
                                       <div>
                                          <a href="<?php echo BASE_URL; ?>admin/laboratories/add?id=<?php echo $lab['lab_id']; ?>" 
                                             class="btn btn-xs btn-warning" title="Edit" style="font-size: 0.7rem; padding: 4px 8px;">
                                             <i class="icon-pencil"></i>
                                          </a>
                                          <a href="javascript:void(0)" onclick="deleteLaboratory(<?php echo $lab['entity_id']; ?>)" 
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
                        <i class="fas fa-info-circle me-2"></i>No laboratories found.
                     </div>
                  <?php endif; ?>
                  
                  <!-- Pagination -->
                  <?php if ($total_pages > 1): ?>
                     <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                           <?php if ($page > 1): ?>
                              <li class="page-item">
                                 <a class="page-link" href="?page=<?php echo $page - 1; ?>&search_lab=<?php echo urlencode($search_lab); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_status=<?php echo $filter_status; ?>&filter_type=<?php echo $filter_type; ?>">
                                    <i class="fas fa-chevron-left me-1"></i>Previous
                                 </a>
                              </li>
                           <?php endif; ?>
                           
                           <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                              <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                 <a class="page-link" href="?page=<?php echo $i; ?>&search_lab=<?php echo urlencode($search_lab); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_status=<?php echo $filter_status; ?>&filter_type=<?php echo $filter_type; ?>">
                                    <?php echo $i; ?>
                                 </a>
                              </li>
                           <?php endfor; ?>
                           
                           <?php if ($page < $total_pages): ?>
                              <li class="page-item">
                                 <a class="page-link" href="?page=<?php echo $page + 1; ?>&search_lab=<?php echo urlencode($search_lab); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_city_id=<?php echo $filter_city_id; ?>&filter_status=<?php echo $filter_status; ?>&filter_type=<?php echo $filter_type; ?>">
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
function deleteLaboratory(entity_id) {
    if (confirm('Are you sure you want to delete this laboratory? This action cannot be undone.')) {
        window.location.href = '?delete_id=' + entity_id;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>
