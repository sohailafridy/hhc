<?php include '../config.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
      <?php include BASE_PATH.'/admin/inc/top.php';?>
      <!-- Side-Nav-->
      <?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Check if it's edit mode
$edit_mode = false;
$blood_bank_data = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $bb_id = $_GET['id'];
    $edit_query = "SELECT * FROM blood_bank WHERE bb_id = $bb_id";
    $edit_result = mysqli_query($con, $edit_query);
    
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $blood_bank_data = mysqli_fetch_assoc($edit_result);
    }
}

// Fetch cities for dropdown
$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_id = mysqli_real_escape_string($con, $_POST['city_id']);
    $bb_name = mysqli_real_escape_string($con, $_POST['bb_name']);
    $bb_address = mysqli_real_escape_string($con, $_POST['bb_address']);
    $bb_contact = mysqli_real_escape_string($con, $_POST['bb_contact']);
    $bb_comment = mysqli_real_escape_string($con, $_POST['bb_comment']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Handle file upload for blood bank picture
    $bb_pic = '';
    if (isset($_FILES['bb_pic']) && $_FILES['bb_pic']['error'] == 0) {
        $target_dir = BASE_PATH."/admin/inc/uploads/blood-banks/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["bb_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Allow certain file formats
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["bb_pic"]["tmp_name"], $target_file)) {
                $bb_pic = $file_name;
            }
        }
    }
    
    if ($edit_mode) {
        // Update existing blood bank
        $update_query = "UPDATE blood_bank SET city_id = '$city_id', bb_name = '$bb_name', bb_address = '$bb_address', bb_contact = '$bb_contact', bb_comment = '$bb_comment', status = $status";
        
        // Update picture only if new one is uploaded
        if (!empty($bb_pic)) {
            $update_query .= ", bb_pic = '$bb_pic'";
        }
        
        $update_query .= ", updated_at = NOW() WHERE bb_id = $bb_id";
        
        if (mysqli_query($con, $update_query)) {
            $success_msg = "Blood Bank updated successfully!";
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    } else {

         $generate_ent_it = "INSERT INTO entities (entity_type, created_at) VALUES ('blood_bank',date('Y-m-d'))";
        mysqli_query($con, $generate_ent_it);
        $entity_id = mysqli_insert_id($con);

        // Insert new blood bank
        $insert_query = "INSERT INTO blood_bank (entity_id,city_id, bb_name, bb_address, bb_contact, bb_pic, bb_comment, status, created_at) 
                       VALUES ($entity_id,'$city_id', '$bb_name', '$bb_address', '$bb_contact', '$bb_pic', '$bb_comment', $status, NOW())";
        
        if (mysqli_query($con, $insert_query)) {
            $success_msg = "Blood Bank added successfully!";
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
            <h4><?php echo $edit_mode ? 'Edit Blood Bank' : 'Add Blood Bank'; ?></h4>
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
                  <h5 class="card-header-text">Blood Bank Information</h5>
               </div>
               <div class="card-block">
                  <form method="POST" action="" enctype="multipart/form-data">
                     <div class="form-group">
                        <label for="cityId">City</label>
                        <select class="form-control" id="cityId" name="city_id" required>
                           <option value="">Select City</option>
                           <?php while ($city = mysqli_fetch_assoc($cities_result)): ?>
                              <option value="<?php echo $city['city_id']; ?>" 
                                      <?php echo ($edit_mode && $blood_bank_data['city_id'] == $city['city_id']) ? 'selected' : ''; ?>>
                                 <?php echo htmlspecialchars($city['city_name']); ?>
                              </option>
                           <?php endwhile; ?>
                        </select>
                     </div>
                     <div class="form-group">
                        <label for="bbName">Blood Bank Name</label>
                        <input type="text" class="form-control" id="bbName" name="bb_name" 
                               placeholder="Enter blood bank name" required
                               value="<?php echo $edit_mode ? htmlspecialchars($blood_bank_data['bb_name']) : ''; ?>">
                     </div>
                     <div class="form-group">
                        <label for="bbAddress">Address</label>
                        <textarea class="form-control" id="bbAddress" name="bb_address" 
                                  placeholder="Enter blood bank address" rows="3" required><?php echo $edit_mode ? htmlspecialchars($blood_bank_data['bb_address']) : ''; ?></textarea>
                     </div>
                     <div class="form-group">
                        <label for="bbContact">Contact Number</label>
                        <input type="text" class="form-control" id="bbContact" name="bb_contact" 
                               placeholder="Enter contact number" required
                               value="<?php echo $edit_mode ? htmlspecialchars($blood_bank_data['bb_contact']) : ''; ?>">
                     </div>
                     <div class="form-group">
                        <label for="bbComment">Additional Information</label>
                        <textarea class="form-control" id="bbComment" name="bb_comment" 
                                  placeholder="Enter additional information or comments" rows="3"><?php echo $edit_mode ? htmlspecialchars($blood_bank_data['bb_comment']) : ''; ?></textarea>
                     </div>
                     <div class="form-group">
                        <label for="bbPic">Blood Bank Picture</label>
                        <input type="file" class="form-control" id="bbPic" name="bb_pic" accept="image/*">
                        <?php if ($edit_mode && !empty($blood_bank_data['bb_pic'])): ?>
                           <small class="form-text text-muted">
                              Current picture: 
                              <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo $blood_bank_data['bb_pic']; ?>" 
                                   alt="<?php echo $blood_bank_data['bb_name']; ?>" width="50" height="50">
                           </small>
                        <?php endif; ?>
                     </div>
                     <div class="form-group">
                        <div class="checkbox">
                           <label>
                              <input type="checkbox" name="status" value="1" 
                                     <?php echo ($edit_mode && $blood_bank_data['status'] == 1) ? 'checked' : ''; ?>>
                              Active Status
                           </label>
                        </div>
                     </div>
                     <button type="submit" class="btn btn-primary">
                        <?php echo $edit_mode ? 'Update Blood Bank' : 'Submit'; ?>
                     </button>
                     <a href="<?php echo BASE_URL; ?>admin/blood-banks/list" class="btn btn-secondary">Cancel</a>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- Container-fluid ends -->
</div>

<?php include BASE_PATH.'/admin/inc/footer.php';?>
