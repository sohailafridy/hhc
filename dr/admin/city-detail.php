<?php include '../config.php'; ?>

<?php
// Handle delete operation - MUST be before any HTML output
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // First, get the city picture to delete the file
    $pic_query = "SELECT city_pic FROM cities WHERE city_id = $delete_id";
    $pic_result = mysqli_query($con, $pic_query);
    $city_pic_data = mysqli_fetch_assoc($pic_result);
    $city_pic = $city_pic_data ? $city_pic_data['city_pic'] : '';
    
    // Delete the city from database
    $delete_query = "UPDATE cities SET status = 0 WHERE city_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete the picture file if it exists
      //   if (!empty($city_pic)) {
      //       $pic_path = BASE_PATH."/admin/inc/uploads/cities/".$city_pic;
      //       if (file_exists($pic_path)) {
      //           unlink($pic_path);
      //       }
      //   }
        $_SESSION['success_msg'] = "City deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    
    // Redirect to cities list
    header('Location: ' . BASE_URL . 'admin/cities/list');
    exit();
}
?>

<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Get city ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . 'admin/cities/list');
    exit();
}

$city_id = $_GET['id'];

// Fetch city details
$query = "SELECT * FROM cities WHERE city_id = $city_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/cities/list');
    exit();
}

$city = mysqli_fetch_assoc($result);
?>

<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <div class="row">
         <div class="main-header">
            <h4>City Details</h4>
            <div class="pull-right">
               <a href="<?php echo BASE_URL; ?>admin/cities/add?id=<?php echo $city['city_id']; ?>" class="btn btn-warning">
                  <i class="icon-pencil"></i> Edit City
               </a>
               <a href="<?php echo BASE_URL; ?>admin/cities/list" class="btn btn-secondary">
                  <i class="icon-arrow-left"></i> Back to List
               </a>
            </div>
         </div>
      </div>
      
      <div class="row">
         <div class="col-lg-4">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">City Picture</h5>
               </div>
               <div class="card-block text-center">
                  <?php if (!empty($city['city_pic'])): ?>
                     <img src="<?php echo BASE_URL; ?>admin/inc/uploads/cities/<?php echo $city['city_pic']; ?>" 
                          alt="<?php echo $city['city_name']; ?>" class="img-fluid" style="max-height: 300px;">
                  <?php else: ?>
                     <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/city.jpg" 
                          alt="No Image" class="img-fluid" style="max-height: 300px;">
                  <?php endif; ?>
               </div>
            </div>
         </div>
         
         <div class="col-lg-8">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">City Information</h5>
               </div>
               <div class="card-block">
                  <table class="table table-bordered table-striped">
                     <tr>
                        <th width="30%">City ID</th>
                        <td><?php echo $city['city_id']; ?></td>
                     </tr>
                     <tr>
                        <th>City Name</th>
                        <td><?php echo htmlspecialchars($city['city_name']); ?></td>
                     </tr>
                     <tr>
                        <th>Status</th>
                        <td>
                           <?php if ($city['status'] == 1): ?>
                              <span class="badge badge-success">Active</span>
                           <?php else: ?>
                              <span class="badge badge-danger">Inactive</span>
                           <?php endif; ?>
                        </td>
                     </tr>
                     <tr>
                        <th>Created At</th>
                        <td><?php echo date('d M Y, h:i A', strtotime($city['created_at'])); ?></td>
                     </tr>
                     <?php if (!empty($city['city_pic'])): ?>
                     <tr>
                        <th>Picture File</th>
                        <td><?php echo htmlspecialchars($city['city_pic']); ?></td>
                     </tr>
                     <?php endif; ?>
                  </table>
               </div>
            </div>
            
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">Actions</h5>
               </div>
               <div class="card-block">
                  <a href="<?php echo BASE_URL; ?>admin/cities/add?id=<?php echo $city['city_id']; ?>" 
                     class="btn btn-warning">
                     <i class="icon-pencil"></i> Edit City
                  </a>
                  <a href="javascript:void(0)" onclick="deleteCity(<?php echo $city['city_id']; ?>)" 
                     class="btn btn-danger">
                     <i class="icon-trash"></i> Delete City
                  </a>
                  <a href="<?php echo BASE_URL; ?>admin/cities/list" class="btn btn-secondary">
                     <i class="icon-arrow-left"></i> Back to List
                  </a>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- Container-fluid ends -->
</div>

<script>
function deleteCity(cityId) {
    if (confirm('Are you sure you want to delete this city?')) {
        window.location.href = '?delete_id=' + cityId;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>