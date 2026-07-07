<?php include '../config.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
      <?php include BASE_PATH.'/admin/inc/top.php';?>
      <!-- Side-Nav-->
      <?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Get provinces data for dropdown
$provinces_query = "SELECT p_id, p_name FROM provinces ORDER BY p_name ASC";
$provinces_result = mysqli_query($con, $provinces_query);
$provinces = mysqli_fetch_all($provinces_result, MYSQLI_ASSOC);

// Check if it's edit mode
$edit_mode = false;
$city_data = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $city_id = $_GET['id'];
    $edit_query = "SELECT * FROM cities WHERE city_id = $city_id";
    $edit_result = mysqli_query($con, $edit_query);
    
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $city_data = mysqli_fetch_assoc($edit_result);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_name = mysqli_real_escape_string($con, $_POST['city_name']);
    $province_id = mysqli_real_escape_string($con, $_POST['province_id']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Handle file upload for city picture
    $city_pic = '';
    if (isset($_FILES['city_pic']) && $_FILES['city_pic']['error'] == 0) {
        $target_dir = BASE_PATH."/admin/inc/uploads/cities/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["city_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Allow certain file formats
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["city_pic"]["tmp_name"], $target_file)) {
                $city_pic = $file_name;
            }
        }
    }
    
    if ($edit_mode) {
        // Update existing city
        $update_query = "UPDATE cities SET city_name = '$city_name', province_id = '$province_id', status = $status";
        
        // Update picture only if new one is uploaded
        if (!empty($city_pic)) {
            $update_query .= ", city_pic = '$city_pic'";
        }
        
        $update_query .= ", updated_at = NOW() WHERE city_id = $city_id";
        
        if (mysqli_query($con, $update_query)) {
            $success_msg = "City updated successfully!";
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    } else {
        // Insert new city
        $insert_query = "INSERT INTO cities (city_name, province_id, city_pic, status, created_at) 
                       VALUES ('$city_name', '$province_id', '$city_pic', $status, NOW())";
        
        if (mysqli_query($con, $insert_query)) {
            $success_msg = "City added successfully!";
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    }
}
?>

<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <div class="row">
         <div class="main-header">
            <h4><?php echo $edit_mode ? 'Edit City' : 'Add City'; ?></h4>
         </div>
      </div>
      
      <?php if (isset($success_msg)): ?>
         <div class="alert alert-success"><?php echo $success_msg; ?></div>
      <?php endif; ?>
      
      <?php if (isset($error_msg)): ?>
         <div class="alert alert-danger"><?php echo $error_msg; ?></div>
      <?php endif; ?>
      
      <div class="row">
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-header-text">City Information</h5>
               </div>
               <div class="card-block">
                  <form method="POST" action="" enctype="multipart/form-data">
                     <div class="form-group">
                        <label for="province">Province</label>
                        <select class="form-control" id="province" name="province_id" required>
                           <option value="">Select Province</option>
                           <?php foreach ($provinces as $province): ?>
                              <option value="<?php echo $province['p_id']; ?>" 
                                      <?php echo ($edit_mode && $city_data['province_id'] == $province['p_id']) ? 'selected' : ''; ?>>
                                 <?php echo htmlspecialchars($province['p_name']); ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                     </div>
                     <div class="form-group">
                        <label for="cityName">City Name</label>
                        <input type="text" class="form-control" id="cityName" name="city_name" 
                               placeholder="Enter city name" required
                               value="<?php echo $edit_mode ? htmlspecialchars($city_data['city_name']) : ''; ?>">
                     </div>
                     <div class="form-group">
                        <label for="cityPic">City Picture</label>
                        <input type="file" class="form-control" id="cityPic" name="city_pic" accept="image/*">
                        <?php if ($edit_mode && !empty($city_data['city_pic'])): ?>
                           <small class="form-text text-muted">
                              Current picture: 
                              <img src="<?php echo BASE_URL; ?>admin/inc/uploads/cities/<?php echo $city_data['city_pic']; ?>" 
                                   alt="<?php echo $city_data['city_name']; ?>" width="50" height="50">
                           </small>
                        <?php endif; ?>
                     </div>
                     <div class="form-group">
                        <div class="checkbox">
                           <label>
                              <input type="checkbox" name="status" value="1" 
                                     <?php echo ($edit_mode && $city_data['status'] == 1) ? 'checked' : ''; ?>>
                              Active Status
                           </label>
                        </div>
                     </div>
                     <button type="submit" class="btn btn-primary">
                        <?php echo $edit_mode ? 'Update City' : 'Submit'; ?>
                     </button>
                     <a href="<?php echo BASE_URL; ?>admin/cities/list" class="btn btn-secondary">Cancel</a>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- Container-fluid ends -->
</div>

<?php include BASE_PATH.'/admin/inc/footer.php';?>
