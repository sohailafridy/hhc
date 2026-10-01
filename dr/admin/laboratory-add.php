<?php include '../config.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Check if it's edit mode
$edit_mode = false;
$lab_data = null;
$created_at = date('Y-m-d');
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $lab_id = (int)$_GET['id'];
    $edit_query = "SELECT laboratories.*, u.status, u.username
     FROM laboratories 
     LEFT JOIN users u ON u.user_id = laboratories.user_id
     WHERE lab_id = $lab_id";
    $edit_result = mysqli_query($con, $edit_query);
    
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $lab_data = mysqli_fetch_assoc($edit_result);
    }
}

// Fetch cities for dropdown
$cities_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// Fetch hospitals for dropdown with city_id for filtering
$hospitals_query = "SELECT hospitals.user_id, hospital_id, hospital_name, city_id 
FROM hospitals 
LEFT JOIN users u ON u.user_id = hospitals.user_id
WHERE u.status = 1 AND hospitals.approve=1 ORDER BY hospital_name ASC";
$hospitals_result = mysqli_query($con, $hospitals_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $city_id     = mysqli_real_escape_string($con, $_POST['city_id']);
    $user_id     = (int)$_POST['user_id'];
    $lab_name    = mysqli_real_escape_string($con, $_POST['lab_name']);
    $lab_address = mysqli_real_escape_string($con, $_POST['lab_address']);
    $lab_phone   = mysqli_real_escape_string($con, $_POST['lab_phone']);
    $lab_email   = mysqli_real_escape_string($con, $_POST['lab_email']);
    $lab_type    = mysqli_real_escape_string($con, $_POST['lab_type']);
    $status      = isset($_POST['status']) ? 1 : 0;
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $pass = mysqli_real_escape_string($con, $_POST['password']);
    $password = base64_encode($pass);
    
    // Handle lab type specific fields
    $hospital_id = null;
    
    if ($lab_type == 1) {
        // Hospital Lab
        $hospital_id = mysqli_real_escape_string($con, $_POST['hospital_id']);
    }
    
    // Handle file upload for lab picture
    $lab_pic = '';
    if (isset($_FILES['lab_pic']) && $_FILES['lab_pic']['error'] == 0) {
        $target_dir = BASE_PATH."/admin/inc/uploads/laboratories/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["lab_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Allow certain file formats
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["lab_pic"]["tmp_name"], $target_file)) {
                $lab_pic = $file_name;
            }
        }
    }
    
    if ($edit_mode) {
        // Update existing laboratory
        $update_query = "UPDATE laboratories SET city_id = '$city_id', lab_name = '$lab_name', lab_address = '$lab_address', lab_phone = '$lab_phone', lab_email = '$lab_email', lab_type = '$lab_type'";
        
        // Update type-specific fields
        if ($lab_type == 1) {
            // Hospital lab: ensure valid hospital_id
            $update_query .= ", hospital_id = '" . $hospital_id . "'";
        } else {
            // Independent lab: set hospital_id to NULL
            $update_query .= ", hospital_id = NULL";
        }
        
        // Update picture only if new one is uploaded
        if (!empty($lab_pic)) {
            $update_query .= ", lab_pic = '$lab_pic'";
        }
        
        $update_query .= ", updated_at = NOW() WHERE lab_id = $lab_id";
        
        if (mysqli_query($con, $update_query)) {
            // Update users table (status, and password only if a new one was entered)
            $user_update = "UPDATE users SET status='". $status ."'";
            if ($pass !== '') {
                $user_update .= ", password='". $password ."'";
            }
            $user_update .= " WHERE user_id=". $user_id;
            mysqli_query($con, $user_update);

            $success_msg = "Laboratory updated successfully!";
            // Refresh data
            $edit_result = mysqli_query($con, "SELECT laboratories.*, u.status, u.username
                FROM laboratories
                LEFT JOIN users u ON u.user_id = laboratories.user_id
                 WHERE lab_id = $lab_id");
            $lab_data = mysqli_fetch_assoc($edit_result);
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    } else {
        // Insert new lab
        $hospital_id_value = ($lab_type == 1 && !empty($hospital_id)) ? "'" . $hospital_id . "'" : "NULL";

        $generate_user_id = "INSERT INTO users (username, email, password, user_type_id, status, created_at)
VALUES ('$username', '$lab_email', '$password', 3, 1, '$created_at')";
        mysqli_query($con, $generate_user_id);
        $userid = mysqli_insert_id($con);

        $insert_query = "INSERT INTO laboratories (user_id, city_id, hospital_id, lab_name, lab_address, lab_phone, lab_email, lab_type, lab_pic, approve, created_at) 
                       VALUES ($userid, '$city_id', $hospital_id_value, '$lab_name', '$lab_address', '$lab_phone', '$lab_email', '$lab_type', '$lab_pic', 1, NOW())";
        
        if (mysqli_query($con, $insert_query)) {
            $success_msg = "Laboratory added successfully!";
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/lab-add-admin.css">

<div class="content-wrapper">
    <div class="container-fluid">
        
        <!-- Header -->
        <div class="page-header animate-up">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="page-title">
                        <i class="icofont icofont-laboratory"></i> 
                        <?php echo $edit_mode ? 'Edit Laboratory Profile' : 'Add New Laboratory'; ?>
                    </h2>
                    <p class="page-subtitle">Manage laboratory information, services, and contact details</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>admin/laboratories/list" class="btn btn-outline-light rounded-pill">
                        <i class="icofont icofont-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($success_msg)): ?>
            <div class="alert alert-modern alert-success-modern animate-up">
                <i class="icofont icofont-check-circled fs-4 me-2"></i>
                <strong>Success!</strong> &nbsp; <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-modern alert-danger-modern animate-up">
                <i class="icofont icofont-warning-alt fs-4 me-2"></i>
                <strong>Error!</strong> &nbsp; <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="animate-up delay-1">
            <input type="hidden" name="lab_type" id="lab_type" value="<?php echo $edit_mode ? $lab_data['lab_type'] : '1'; ?>">
            <input type="hidden" name="user_id" value="<?php if(isset($lab_data['user_id'])){ echo $lab_data['user_id']; } ?>">
            <!-- Mode Selection -->
            <div class="modern-card">
                <div class="card-body-custom">
                    <div class="toggle-container">
                        <label class="custom-switch">
                            <input type="checkbox" id="independent_lab_toggle" <?php echo ($edit_mode && $lab_data['lab_type'] == 2) ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <div>
                            <div class="toggle-label">Independent Lab Mode</div>
                            <span class="toggle-description">Switch ON if this is an independent laboratory. Switch OFF for hospital labs.</span>
                        </div>
                        <div class="ms-auto">
                            <span id="mode_badge" class="badge-status hospital">
                                <i class="icofont icofont-hospital"></i> Hospital Lab Mode
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Location & Workplace Info -->
                <div class="col-lg-6">
                    <div class="modern-card h-100">
                        <div class="card-header-custom">
                            <h5><i class="icofont icofont-location-pin"></i> Location & Workplace</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <select class="form-control-modern" id="cityId" name="city_id" required>
                                    <option value="">Select City</option>
                                    <?php 
                                    mysqli_data_seek($cities_result, 0);
                                    while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                        <option value="<?php echo $city['city_id']; ?>" 
                                                <?php echo ($edit_mode && $lab_data['city_id'] == $city['city_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($city['city_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div id="hospital_section" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label">Hospital</label>
                                    <select class="form-control-modern" id="hospitalId" name="hospital_id">
                                        <option value="">Select Hospital</option>
                                        <?php 
                                        mysqli_data_seek($hospitals_result, 0);
                                        while ($hospital = mysqli_fetch_assoc($hospitals_result)): ?>
                                            <option value="<?php echo $hospital['hospital_id']; ?>" 
                                                    data-city="<?php echo $hospital['city_id']; ?>"
                                                    <?php echo ($edit_mode && $lab_data['hospital_id'] == $hospital['hospital_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($hospital['hospital_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Laboratory Information -->
                <div class="col-lg-6">
                    <div class="modern-card h-100">
                        <div class="card-header-custom">
                            <h5><i class="icofont icofont-laboratory"></i> Laboratory Details</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control-modern" name="username" 
                                               placeholder="Enter username" required autocomplete="off"
                                               value="<?php echo $edit_mode ? htmlspecialchars($lab_data['username'] ?? '') : ''; ?>" <?php echo $edit_mode ? 'readonly' : ''; ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control-modern" name="password" 
                                               placeholder="<?php echo $edit_mode ? 'Leave blank to keep current' : 'Enter password'; ?>"
                                               autocomplete="new-password" <?php echo $edit_mode ? '' : 'required'; ?>>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Laboratory Name</label>
                                <input type="text" class="form-control-modern" name="lab_name" required
                                       placeholder="e.g. City Medical Laboratory"
                                       value="<?php echo $edit_mode ? htmlspecialchars($lab_data['lab_name']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control-modern" name="lab_phone"
                                       placeholder="Contact Number"
                                       value="<?php echo $edit_mode ? htmlspecialchars($lab_data['lab_phone']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control-modern" name="lab_email"
                                       placeholder="lab@example.com"
                                       value="<?php echo $edit_mode ? htmlspecialchars($lab_data['lab_email']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <textarea class="form-control-modern" name="lab_address" rows="3"
                                          placeholder="Full address of laboratory"><?php echo $edit_mode ? htmlspecialchars($lab_data['lab_address']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="modern-card">
                        <div class="card-header-custom">
                            <h5><i class="icofont icofont-settings"></i> Additional Settings</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Laboratory Picture</label>
                                        <input type="file" class="form-control-modern" name="lab_pic" accept="image/*">
                                        <small class="text-muted mt-2 d-block">Recommended size: 500x500px (JPG, PNG)</small>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <?php if ($edit_mode && !empty($lab_data['lab_pic'])): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo $lab_data['lab_pic']; ?>" 
                                             alt="Current Picture" class="img-preview">
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <hr style="border-top: 1px solid #eee; margin: 30px 0;">
                            
                            <div class="form-group">
                                <label class="custom-switch" style="vertical-align: middle;">
                                    <input type="checkbox" name="status" value="1"
    <?php echo (!$edit_mode || (($lab_data['status'] ?? 0) == 1)) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <span class="ms-3 fw-bold">Active Status</span>
                                <small class="text-muted d-block mt-1">Enable to make this laboratory visible in the public directory.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4 mb-5 animate-up delay-2">
                <button type="submit" class="btn-action btn-save">
                    <i class="icofont icofont-save me-2"></i> Save Laboratory Details
                </button>
                <a href="<?php echo BASE_URL; ?>admin/laboratories/list" class="btn-action btn-cancel">
                    <i class="icofont icofont-close me-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('independent_lab_toggle');
    const hospitalSection = document.getElementById('hospital_section');
    const hospitalId = document.getElementById('hospitalId');
    const labTypeInput = document.getElementById('lab_type');
    const modeBadge = document.getElementById('mode_badge');
    const citySelect = document.getElementById('cityId');
    
    // Initial State
    updateView();
    filterHospitals();

    // Event Listeners
    toggle.addEventListener('change', updateView);
    citySelect.addEventListener('change', filterHospitals);

    function updateView() {
        if (toggle.checked) {
            // Independent Lab Mode
            hospitalSection.style.display = 'none';
            
            hospitalId.removeAttribute('required');
            
            labTypeInput.value = '2';
            modeBadge.className = 'badge-status clinic';
            modeBadge.innerHTML = '<i class="icofont icofont-building"></i> Independent Lab Mode';
        } else {
            // Hospital Lab Mode
            // Only show hospital if city is selected
            if (citySelect.value) {
                hospitalSection.style.display = 'block';
            } else {
                hospitalSection.style.display = 'none';
            }
            
            if (citySelect.value) {
                hospitalId.setAttribute('required', 'required');
            }
            
            labTypeInput.value = '1';
            modeBadge.className = 'badge-status hospital';
            modeBadge.innerHTML = '<i class="icofont icofont-hospital"></i> Hospital Lab Mode';
        }
    }

    function filterHospitals() {
        const selectedCity = citySelect.value;
        const options = hospitalId.options;
        let hasVisibleOptions = false;
        
        if (!selectedCity) {
            hospitalSection.style.display = 'none';
            hospitalId.value = '';
            hospitalId.removeAttribute('required');
            return;
        }

        // Show section if in Hospital Mode
        if (!toggle.checked) {
            hospitalSection.style.display = 'block';
            hospitalId.setAttribute('required', 'required');
        }

        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            if (option.value === "") continue; // Skip default option

            const hospitalCity = option.getAttribute('data-city');
            if (hospitalCity == selectedCity) {
                option.style.display = 'block';
                hasVisibleOptions = true;
            } else {
                option.style.display = 'none';
            }
        }
        
        // Reset selection if current selection is now hidden
        const currentOption = options[hospitalId.selectedIndex];
        if (currentOption && currentOption.style.display === 'none') {
            hospitalId.value = '';
        }
    }
});
</script>