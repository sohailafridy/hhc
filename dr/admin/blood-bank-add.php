<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
      <?php include BASE_PATH.'/admin/inc/top.php';?>
      <!-- Side-Nav-->
      <?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// ------------------------------------------------------------
// Helper: edit mode ka data fetch karne ke liye
// ------------------------------------------------------------
function fetch_blood_bank($con, $bb_id) {
    $q = "SELECT blood_bank.*, u.status, u.username, u.email
          FROM blood_bank
          LEFT JOIN users u ON u.user_id = blood_bank.user_id
          WHERE blood_bank.bb_id = $bb_id";
    $r = mysqli_query($con, $q);
    if ($r && mysqli_num_rows($r) > 0) {
        return mysqli_fetch_assoc($r);
    }
    return null;
}

// Check if it's edit mode
$edit_mode = false;
$blood_bank_data = null;
$bb_id = 0;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $bb_id = (int)$_GET['id'];
    $blood_bank_data = fetch_blood_bank($con, $bb_id);
    if ($blood_bank_data) {
        $edit_mode = true;
    }
}

// Fetch cities for dropdown
$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_id    = mysqli_real_escape_string($con, $_POST['city_id']);
    $bb_name    = mysqli_real_escape_string($con, $_POST['bb_name']);
    $bb_address = mysqli_real_escape_string($con, $_POST['bb_address']);
    $bb_contact = mysqli_real_escape_string($con, $_POST['bb_contact']);
    $bb_comment = mysqli_real_escape_string($con, $_POST['bb_comment']);
    $status     = isset($_POST['status']) ? 1 : 0;

    // Login credentials
    $user_name    = mysqli_real_escape_string($con, trim($_POST['username'] ?? ''));
    $bb_email     = mysqli_real_escape_string($con, trim($_POST['email'] ?? ''));
    $password_raw = $_POST['password'] ?? '';
    $user_type_id = 6;   // <-- blood bank ki user_type_id yahan set karein
    $created_at   = date('Y-m-d H:i:s');

    // Existing user id (edit mode me)
    $user_id = $edit_mode ? (int)$blood_bank_data['user_id'] : 0;

    // Duplicate username check
    $exclude = $edit_mode ? " AND user_id != $user_id" : "";
    $chk = mysqli_query($con, "SELECT user_id FROM users WHERE username = '$user_name' $exclude LIMIT 1");

    if ($user_name === '') {
        $error_msg = "Username is required.";
    } elseif (mysqli_num_rows($chk) > 0) {
        $error_msg = "Username already exists, please choose another.";
    } elseif (!$edit_mode && $password_raw === '') {
        $error_msg = "Password is required.";
    } else {

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
            // ---------------- UPDATE ----------------
            $update_query = "UPDATE blood_bank SET city_id = '$city_id', bb_name = '$bb_name', bb_address = '$bb_address', bb_contact = '$bb_contact', bb_comment = '$bb_comment'";

            // Update picture only if new one is uploaded
            if (!empty($bb_pic)) {
                $update_query .= ", bb_pic = '$bb_pic'";
            }

            $update_query .= ", updated_at = NOW() WHERE bb_id = $bb_id";

            if (mysqli_query($con, $update_query)) {

                // users table update (password sirf tab change hoga jab naya diya ho)
                $user_update = "UPDATE users SET username = '$user_name', email = '$bb_email', status = '$status'";
                if ($password_raw !== '') {
                    $password = mysqli_real_escape_string($con, base64_encode($password_raw));
                    $user_update .= ", password = '$password'";
                }
                $user_update .= " WHERE user_id = $user_id";
                mysqli_query($con, $user_update);

                $success_msg = "Blood Bank updated successfully!";

                // Form me latest data dikhane ke liye dobara fetch
                $blood_bank_data = fetch_blood_bank($con, $bb_id);
            } else {
                $error_msg = "Error: " . mysqli_error($con);
            }

        } else {
            // ---------------- INSERT ----------------
            $password = mysqli_real_escape_string($con, base64_encode($password_raw));

            $generate_user_id = "INSERT INTO users (username, email, password, user_type_id, status, created_at)
                                 VALUES ('$user_name', '$bb_email', '$password', $user_type_id, $status, '$created_at')";

            if (mysqli_query($con, $generate_user_id)) {
                $userid = mysqli_insert_id($con);

                $insert_query = "INSERT INTO blood_bank (user_id, city_id, bb_name, bb_address, bb_contact, bb_pic, bb_comment, approve, created_at)
                                 VALUES ($userid, '$city_id', '$bb_name', '$bb_address', '$bb_contact', '$bb_pic', '$bb_comment', 1, NOW())";

                if (mysqli_query($con, $insert_query)) {
                    $success_msg = "Blood Bank added successfully!";
                } else {
                    $error_msg = "Error: " . mysqli_error($con);
                    // blood bank insert fail ho to user record bhi hata dein
                    mysqli_query($con, "DELETE FROM users WHERE user_id = $userid");
                }
            } else {
                $error_msg = "Error creating user: " . mysqli_error($con);
            }
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

                     <!-- Login details -->
                     <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="Enter username" required autocomplete="off"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ($edit_mode ? ($blood_bank_data['username'] ?? '') : '')); ?>">
                     </div>
                     <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="Enter email" autocomplete="off"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ($edit_mode ? ($blood_bank_data['email'] ?? '') : '')); ?>">
                     </div>
                     <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="<?php echo $edit_mode ? 'Leave blank to keep current password' : 'Enter password'; ?>"
                               autocomplete="new-password" <?php echo $edit_mode ? '' : 'required'; ?>>
                     </div>

                     <!-- Blood bank details -->
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
                              <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo htmlspecialchars($blood_bank_data['bb_pic']); ?>"
                                   alt="<?php echo htmlspecialchars($blood_bank_data['bb_name']); ?>" width="50" height="50">
                           </small>
                        <?php endif; ?>
                     </div>
                     <div class="form-group">
                        <div class="checkbox">
                           <label>
                              <input type="checkbox" name="status" value="1"
                                 <?php echo (!$edit_mode || (($blood_bank_data['status'] ?? 0) == 1)) ? 'checked' : ''; ?>>
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