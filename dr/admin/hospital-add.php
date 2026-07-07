<?php include '../config.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
      <?php include BASE_PATH.'/admin/inc/top.php';?>
      <!-- Side-Nav-->
      <?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Check if it's edit mode
$edit_mode = false;
$hospital_data = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $hospital_id = $_GET['id'];
    $edit_query = "SELECT * FROM hospitals WHERE hospital_id = $hospital_id";
    $edit_result = mysqli_query($con, $edit_query);
    
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $hospital_data = mysqli_fetch_assoc($edit_result);
    }
}

// Fetch cities for dropdown
$cities_query = "SELECT city_id, city_name,provinces.p_name
FROM 
cities 
LEFT JOIN provinces ON cities.province_id = provinces.p_id
WHERE cities.status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_id = mysqli_real_escape_string($con, $_POST['city_id']);
    $hospital_name = mysqli_real_escape_string($con, $_POST['hospital_name']);
    $hospital_address = mysqli_real_escape_string($con, $_POST['hospital_address']);
    $hospital_phone = mysqli_real_escape_string($con, $_POST['hospital_phone']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Handle file upload for hospital picture
    $hospital_pic = '';
    if (isset($_FILES['hospital_pic']) && $_FILES['hospital_pic']['error'] == 0) {
        $target_dir = BASE_PATH."/admin/inc/uploads/hospitals/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["hospital_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Allow certain file formats
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["hospital_pic"]["tmp_name"], $target_file)) {
                $hospital_pic = $file_name;
            }
        }
    }
    
    if ($edit_mode) {
        // Update existing hospital
        $update_query = "UPDATE hospitals SET city_id = '$city_id', hospital_name = '$hospital_name', hospital_address = '$hospital_address', hospital_phone = '$hospital_phone', status = $status";
        
        // Update picture only if new one is uploaded
        if (!empty($hospital_pic)) {
            $update_query .= ", hospital_pic = '$hospital_pic'";
        }
        
        $update_query .= ", updated_at = NOW() WHERE hospital_id = $hospital_id";
        
        if (mysqli_query($con, $update_query)) {
            $success_msg = "Hospital updated successfully!";
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    } else {

        $generate_ent_it = "INSERT INTO entities (entity_type, created_at) VALUES ('hospital',date('Y-m-d'))";
        mysqli_query($con, $generate_ent_it);
        $entity_id = mysqli_insert_id($con);

        // Insert new hospital
        $insert_query = "INSERT INTO hospitals (entity_id,city_id, hospital_name, hospital_address, hospital_phone, hospital_pic, status, created_at) 
                       VALUES ($entity_id,'$city_id', '$hospital_name', '$hospital_address', '$hospital_phone', '$hospital_pic', $status, NOW())";
        
        if (mysqli_query($con, $insert_query)) {
            $success_msg = "Hospital added successfully!";
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
            <h4><?php echo $edit_mode ? 'Edit Hospital' : 'Add Hospital'; ?></h4>
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
                  <h5 class="card-header-text">Hospital Information</h5>
               </div>
               <div class="card-block">
                  <form method="POST" action="" enctype="multipart/form-data">
                     <div class="form-group">
                        <label for="cityId">City</label>
                        <select class="form-control" id="cityId" name="city_id" required>
                           <option value="">Select City</option>
                           <?php while ($city = mysqli_fetch_assoc($cities_result)): ?>
                              <option value="<?php echo $city['city_id']; ?>" 
                                      <?php echo ($edit_mode && $hospital_data['city_id'] == $city['city_id']) ? 'selected' : ''; ?>>
                                 <?php echo htmlspecialchars($city['city_name'])." (".htmlspecialchars($city['p_name']).")"; ?>
                              </option>
                           <?php endwhile; ?>
                        </select>
                     </div>
                     <div class="form-group">
                        <label for="hospitalName">Hospital Name</label>
                        <input type="text" class="form-control" id="hospitalName" name="hospital_name" 
                               placeholder="Enter hospital name" required
                               value="<?php echo $edit_mode ? htmlspecialchars($hospital_data['hospital_name']) : ''; ?>">
                     </div>
                     <div class="form-group">
                        <label for="hospitalAddress">Hospital Address</label>
                        <textarea class="form-control" id="hospitalAddress" name="hospital_address" 
                                  placeholder="Enter hospital address" rows="3" required><?php echo $edit_mode ? htmlspecialchars($hospital_data['hospital_address']) : ''; ?></textarea>
                     </div>
                     <div class="form-group">
                        <label for="hospitalPhone">Helpline No</label>
                        <input type="text" class="form-control" id="hospitalPhone" name="hospital_phone" 
                               placeholder="Enter hospital phone" required
                               value="<?php echo $edit_mode ? htmlspecialchars($hospital_data['hospital_phone']) : ''; ?>">
                     </div>
                     <div class="form-group">
                        <label for="hospital_pic">Hospital Picture</label>
                        <input type="file" class="form-control" id="hospital_pic" name="hospital_pic" accept="image/*">
                        <?php if ($edit_mode && !empty($hospital_data['hospital_pic'])): ?>
                           <small class="form-text text-muted">
                              Current picture: 
                              <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital_data['hospital_pic']; ?>" 
                                   alt="<?php echo $hospital_data['hospital_name']; ?>" width="50" height="50">
                           </small>
                        <?php endif; ?>
                     </div>
                     <div class="form-group">
                        <div class="checkbox">
                           <label>
                              <input type="checkbox" name="status" value="1" 
                                     <?php echo ($edit_mode && $hospital_data['status'] == 1) ? 'checked' : 'checked'; ?>>
                              Active Status
                           </label>
                        </div>
                     </div>
                     <button type="submit" class="btn btn-primary">
                        <?php echo $edit_mode ? 'Update Hospital' : 'Submit'; ?>
                     </button>
                     <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn btn-secondary">Cancel</a>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- Container-fluid ends -->
</div>

<?php include BASE_PATH.'/admin/inc/footer.php';?>
<script>
    $(document).ready(function() {
        $('#cityId').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search city...',
            allowClear: true,
            width: '100%'
        });

    });
</script>