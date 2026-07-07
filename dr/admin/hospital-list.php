<?php include '../config.php'; ?>

<?php
// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // First, get the hospital picture to delete the file
    $pic_query = "SELECT hospital_pic FROM hospitals WHERE hospital_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $hospital_pic_data = mysqli_fetch_assoc($pic_result);
    $hospital_pic = $hospital_pic_data ? $hospital_pic_data['hospital_pic'] : '';
    
    // Delete the hospital from database
    $delete_query = "UPDATE hospitals SET status = 0 WHERE hospital_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete the picture file if it exists
      //   if (!empty($hospital_pic)) {
      //       $pic_path = BASE_PATH."/admin/inc/uploads/hospitals/".$hospital_pic;
      //       if (file_exists($pic_path)) {
      //           unlink($pic_path);
      //       }
      //   }
        $_SESSION['success_msg'] = "Hospital deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    
    // Redirect to remove delete_id from URL
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
// Pagination variables
$records_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search filters
$search_hospital = isset($_GET['search_hospital']) ? mysqli_real_escape_string($con, $_GET['search_hospital']) : '';
$search_city = isset($_GET['search_city']) ? mysqli_real_escape_string($con, $_GET['search_city']) : '';
$filter_status = isset($_GET['filter_status']) ? mysqli_real_escape_string($con, $_GET['filter_status']) : '';
// Build WHERE clause
$where_conditions = [];
if (!empty($search_hospital)) {
    $where_conditions[] = "h.hospital_name LIKE '%$search_hospital%'";
}
if (!empty($search_city)) {
    $where_conditions[] = "c.city_name LIKE '%$search_city%'";
}
if ($filter_status !== '') {
    $where_conditions[] = "h.status = $filter_status";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
// Count total records
$count_query = "SELECT COUNT(*) as total FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id $where_clause";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);
// Fetch hospitals data
$query = "SELECT h.entity_id, h.hospital_id, h.hospital_name, h.hospital_address, h.hospital_phone, h.hospital_pic, h.status, h.created_at, c.city_name 
          FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id $where_clause 
          ORDER BY h.created_at DESC 
          LIMIT $offset, $records_per_page";
$result = mysqli_query($con, $query);
?>
<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <div class="row">
         <div class="main-header">
            <h4>Hospitals List</h4>
            <a href="<?php echo BASE_URL; ?>admin/hospitals/add" class="btn btn-primary pull-right">
               <i class="icon-plus"></i> Add Hospital
            </a>
         </div>
      </div>
      
      <?php 
      // Display session messages
      if (isset($_SESSION['success_msg'])): ?>
         <div class="alert alert-success"><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error_msg'])): ?>
         <div class="alert alert-danger"><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
      <?php endif; ?>
      
      <!-- Search and Filter Section -->
      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">Search & Filter</h5>
               </div>
               <div class="card-block">
                  <form method="GET" action="">
                     <div class="row">
                        <div class="col-md-3">
                           <div class="form-group">
                              <label for="search_hospital">Search by Hospital Name</label>
                              <input type="text" class="form-control" id="search_hospital" name="search_hospital" 
                                     value="<?php echo htmlspecialchars($search_hospital); ?>" placeholder="Enter hospital name">
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label for="search_city">Search by City Name</label>
                              <input type="text" class="form-control" id="search_city" name="search_city" 
                                     value="<?php echo htmlspecialchars($search_city); ?>" placeholder="Enter city name">
                           </div>
                        </div>
                        <div class="col-md-3">
                           <div class="form-group">
                              <label for="filter_status">Filter by Status</label>
                              <select class="form-control" id="filter_status" name="filter_status">
                                 <option value="">All Status</option>
                                 <option value="1" <?php echo ($filter_status == '1') ? 'selected' : ''; ?>>Active</option>
                                 <option value="0" <?php echo ($filter_status == '0') ? 'selected' : ''; ?>>Inactive</option>
                              </select>
                           </div>
                        </div>
                        <div class="col-md-2">
                           <div class="form-group">
                              <label>&nbsp;</label><br>
                              <button type="submit" class="btn btn-primary">Search</button>
                           </div>
                        </div>
                        <div class="col-md-1">
                           <div class="form-group">
                              <label>&nbsp;</label><br>
                              <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn btn-secondary">Reset</a>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
      
      <!-- Hospitals Table -->
      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">Hospitals (<?php echo $total_records; ?> records)</h5>
               </div>
               <div class="card-block">
                  <div class="table-responsive">
                     <table class="table table-bordered table-striped">
                        <thead>
                           <tr>
                              <th>#</th>
                              <th>Hospital Picture</th>
                              <th>Hospital Name</th>
                              <th>City</th>
                              <th>Helpline No</th>
                              <th>Status</th>
                              <th>Created At</th>
                              <th>Actions</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php if (mysqli_num_rows($result) > 0): ?>
                              <?php $serial = $offset + 1; ?>
                              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                 <tr>
                                    <td><?php echo $serial++; ?></td>
                                    <td>
                                       <?php if (!empty($row['hospital_pic'])): ?>
                                          <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $row['hospital_pic']; ?>" 
                                               alt="<?php echo $row['hospital_name']; ?>" width="50" height="50">
                                       <?php else: ?>
                                          <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/hosp.jpg" 
                                               alt="No Image" width="50" height="50">
                                       <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['hospital_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['city_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['hospital_phone']); ?></td>
                                    <td>
                                       <?php if ($row['status'] == 1): ?>
                                          <span class="badge badge-success">Active</span>
                                       <?php else: ?>
                                          <span class="badge badge-danger">Inactive</span>
                                       <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                                    <td>
                                       <a href="<?php echo BASE_URL; ?>admin/hospitals/detail?id=<?php echo $row['hospital_id']; ?>&entity_id=<?php echo $row['entity_id']; ?>" 
                                          class="btn btn-sm btn-info" title="View Details">
                                          <i class="icon-eye"></i>
                                       </a>
                                       <a href="<?php echo BASE_URL; ?>admin/hospitals/add?id=<?php echo $row['hospital_id']; ?>" 
                                          class="btn btn-sm btn-warning" title="Edit">
                                          <i class="icon-pencil"></i>
                                       </a>
                                       <a href="javascript:void(0)" onclick="deleteHospital(<?php echo $row['hospital_id']; ?>)" 
                                          class="btn btn-sm btn-danger" title="Delete">
                                          <i class="icon-trash"></i>
                                       </a>
                                    </td>
                                 </tr>
                              <?php endwhile; ?>
                           <?php else: ?>
                              <tr>
                                 <td colspan="8" class="text-center">No hospitals found.</td>
                              </tr>
                           <?php endif; ?>
                        </tbody>
                     </table>
                  </div>
                  
                  <!-- Pagination -->
                  <?php if ($total_pages > 1): ?>
                     <nav aria-label="Page navigation">
                        <ul class="pagination">
                           <?php if ($page > 1): ?>
                              <li class="page-item">
                                 <a class="page-link" href="?page=<?php echo $page - 1; ?>&search_hospital=<?php echo urlencode($search_hospital); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_status=<?php echo $filter_status; ?>">
                                    Previous
                                 </a>
                              </li>
                           <?php endif; ?>
                           
                           <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                              <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                 <a class="page-link" href="?page=<?php echo $i; ?>&search_hospital=<?php echo urlencode($search_hospital); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_status=<?php echo $filter_status; ?>">
                                    <?php echo $i; ?>
                                 </a>
                              </li>
                           <?php endfor; ?>
                           
                           <?php if ($page < $total_pages): ?>
                              <li class="page-item">
                                 <a class="page-link" href="?page=<?php echo $page + 1; ?>&search_hospital=<?php echo urlencode($search_hospital); ?>&search_city=<?php echo urlencode($search_city); ?>&filter_status=<?php echo $filter_status; ?>">
                                    Next
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
function deleteHospital(hospitalId) {
    if (confirm('Are you sure you want to delete this hospital?')) {
        window.location.href = '?delete_id=' + hospitalId;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>