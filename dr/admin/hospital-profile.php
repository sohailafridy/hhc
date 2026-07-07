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
    $delete_query = "DELETE FROM hospitals WHERE hospital_id = $delete_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete the picture file if it exists
        if (!empty($hospital_pic)) {
            $pic_path = BASE_PATH."/admin/inc/uploads/hospitals/".$hospital_pic;
            if (file_exists($pic_path)) {
                unlink($pic_path);
            }
        }
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

// Fetch hospital details with city name
$query = "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id WHERE h.hospital_id = $hospital_id";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: ' . BASE_URL . 'admin/hospitals/list');
    exit();
}

$hospital = mysqli_fetch_assoc($result);
?>

<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <div class="row">
         <div class="main-header">
            <h4>Hospital Details</h4>
            <div class="pull-right">
               <a href="<?php echo BASE_URL; ?>admin/hospitals/add?id=<?php echo $hospital['hospital_id']; ?>" class="btn btn-warning">
                  <i class="icon-pencil"></i> Edit Hospital
               </a>
               <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn btn-secondary">
                  <i class="icon-arrow-left"></i> Back to List
               </a>
            </div>
         </div>
      </div>
      
      <div class="row">
         <div class="col-lg-4">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">Hospital Picture</h5>
               </div>
               <div class="card-block text-center">
                  <?php if (!empty($hospital['hospital_pic'])): ?>
                     <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital['hospital_pic']; ?>" 
                          alt="<?php echo $hospital['hospital_name']; ?>" class="img-fluid" style="max-height: 300px;">
                  <?php else: ?>
                     <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/hosp.jpg" 
                          alt="No Image" class="img-fluid" style="max-height: 300px;">
                  <?php endif; ?>
               </div>
            </div>
         </div>
         
         <div class="col-lg-8">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">Hospital Information</h5>
               </div>
               <div class="card-block">
                  <table class="table table-bordered table-striped">
                     <tr>
                        <th width="30%">Hospital ID</th>
                        <td><?php echo $hospital['hospital_id']; ?></td>
                     </tr>
                     <tr>
                        <th>Hospital Name</th>
                        <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                     </tr>
                     <tr>
                        <th>City</th>
                        <td><?php echo htmlspecialchars($hospital['city_name']); ?></td>
                     </tr>
                     <tr>
                        <th>Hospital Address</th>
                        <td><?php echo nl2br(htmlspecialchars($hospital['hospital_address'])); ?></td>
                     </tr>
                     <tr>
                        <th>Helpline No</th>
                        <td><?php echo htmlspecialchars($hospital['hospital_phone']); ?></td>
                     </tr>
                     <tr>
                        <th>Status</th>
                        <td>
                           <?php if ($hospital['status'] == 1): ?>
                              <span class="badge badge-success">Active</span>
                           <?php else: ?>
                              <span class="badge badge-danger">Inactive</span>
                           <?php endif; ?>
                        </td>
                     </tr>
                     <tr>
                        <th>Created At</th>
                        <td><?php echo date('d M Y, h:i A', strtotime($hospital['created_at'])); ?></td>
                     </tr>
                     <?php if (!empty($hospital['updated_at'])): ?>
                     <tr>
                        <th>Updated At</th>
                        <td><?php echo date('d M Y, h:i A', strtotime($hospital['updated_at'])); ?></td>
                     </tr>
                     <?php endif; ?>
                     <?php if (!empty($hospital['hospital_pic'])): ?>
                     <tr>
                        <th>Picture File</th>
                        <td><?php echo htmlspecialchars($hospital['hospital_pic']); ?></td>
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
                  <a href="<?php echo BASE_URL; ?>admin/hospitals/add?id=<?php echo $hospital['hospital_id']; ?>" 
                     class="btn btn-warning">
                     <i class="icon-pencil"></i> Edit Hospital
                  </a>
                  <a href="javascript:void(0)" onclick="deleteHospital(<?php echo $hospital['hospital_id']; ?>)" 
                     class="btn btn-danger">
                     <i class="icon-trash"></i> Delete Hospital
                  </a>
                  <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn btn-secondary">
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
function deleteHospital(hospitalId) {
    if (confirm('Are you sure you want to delete this hospital?')) {
        window.location.href = '?delete_id=' + hospitalId;
    }
}
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>