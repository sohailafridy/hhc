<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<?php include BASE_PATH.'/admin/inc/top.php';?>
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Check if it's edit mode
$edit_mode = false;
$hospital_data = null;
$beds_data = null;
$facilities_data = [];
$created_at = date('Y-m-d');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $hospital_id = (int)$_GET['id'];
    $edit_query = "SELECT h.*, u.status as estatus, u.username, u.email as hospital_email
                   FROM hospitals h
                   LEFT JOIN users u ON u.user_id = h.user_id
                   WHERE h.hospital_id = $hospital_id";
    $edit_result = mysqli_query($con, $edit_query);

    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $hospital_data = mysqli_fetch_assoc($edit_result);
        
        // Fetch beds data
        $beds_query = "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id";
        $beds_result = mysqli_query($con, $beds_query);
        $beds_data = mysqli_fetch_assoc($beds_result);
        
        // Fetch facilities data
        $facilities_query = "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id";
        $facilities_result = mysqli_query($con, $facilities_query);
        while ($row = mysqli_fetch_assoc($facilities_result)) {
            $facilities_data[] = $row;
        }
    }
}

// Fetch cities for dropdown
$cities_query = "SELECT c.city_id, c.city_name, p.p_name
                 FROM cities c
                 LEFT JOIN provinces p ON c.province_id = p.p_id
                 WHERE c.status = 1
                 ORDER BY c.city_name ASC";
$cities_result = mysqli_query($con, $cities_query);

// Define facility list
$facility_list = [
    'emergency' => 'Emergency',
    'icu' => 'ICU',
    'nicu' => 'NICU',
    'operation_theatre' => 'Operation Theatre',
    'pharmacy' => 'Pharmacy',
    'laboratory' => 'Laboratory',
    'blood_bank' => 'Blood Bank',
    'radiology' => 'Radiology',
    'mri' => 'MRI',
    'ct_scan' => 'CT Scan',
    'ambulance' => 'Ambulance',
    'parking' => 'Parking',
    'cafeteria' => 'Cafeteria',
    'prayer_area' => 'Prayer Area'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $city_id          = mysqli_real_escape_string($con, $_POST['city_id']);
    $user_id          = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $hospital_name    = mysqli_real_escape_string($con, $_POST['hospital_name']);
    $hospital_address = mysqli_real_escape_string($con, $_POST['hospital_address']);
    $hospital_phone   = mysqli_real_escape_string($con, $_POST['hospital_phone']);
    $status           = isset($_POST['status']) ? 1 : 0;
    $hospital_email   = mysqli_real_escape_string($con, $_POST['hospital_email']);
    $username         = mysqli_real_escape_string($con, $_POST['username']);
    $pass             = isset($_POST['password']) ? trim($_POST['password']) : '';
    $password         = !empty($pass) ? base64_encode($pass) : '';

    // Bed fields
    $total_beds   = (int)($_POST['total_beds'] ?? 0);
    $icu_beds     = (int)($_POST['icu_beds'] ?? 0);
    $general_beds = (int)($_POST['general_beds'] ?? 0);
    $private_beds = (int)($_POST['private_beds'] ?? 0);

    // Facilities from POST
    $facilities_post = $_POST['facilities'] ?? [];

    // Handle file upload for hospital picture
    $hospital_pic = '';
    if (isset($_FILES['hospital_pic']) && $_FILES['hospital_pic']['error'] == 0) {
        $target_dir = BASE_PATH . "/admin/inc/uploads/hospitals/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["hospital_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["hospital_pic"]["tmp_name"], $target_file)) {
                $hospital_pic = $file_name;
            }
        }
    }

    if ($edit_mode) {
        // Update existing hospital
        $update_query = "UPDATE hospitals SET 
                            city_id = '$city_id', 
                            hospital_name = '$hospital_name', 
                            hospital_address = '$hospital_address', 
                            hospital_phone = '$hospital_phone'";

        if (!empty($hospital_pic)) {
            $update_query .= ", hospital_pic = '$hospital_pic'";
        }

        $update_query .= ", updated_at = NOW() WHERE hospital_id = $hospital_id";

        if (mysqli_query($con, $update_query)) {
            // Update user status (and password only if a new one was entered)
            if (!empty($user_id)) {
                $user_update = "UPDATE users SET status = '$status'";
                if (!empty($password)) {
                    $user_update .= ", password = '$password'";
                }
                $user_update .= " WHERE user_id = $user_id";
                mysqli_query($con, $user_update);
            }

            // ===== UPDATE BEDS =====
            $beds_check = "SELECT id FROM hospital_beds WHERE hospital_id = $hospital_id";
            $beds_check_result = mysqli_query($con, $beds_check);
            
            if (mysqli_num_rows($beds_check_result) > 0) {
                $beds_update = "UPDATE hospital_beds SET 
                                    total_beds = $total_beds,
                                    icu_beds = $icu_beds,
                                    general_beds = $general_beds,
                                    private_beds = $private_beds,
                                    updated_at = NOW()
                                WHERE hospital_id = $hospital_id";
                mysqli_query($con, $beds_update);
            } else {
                if ($total_beds > 0 || $icu_beds > 0 || $general_beds > 0 || $private_beds > 0) {
                    $beds_insert = "INSERT INTO hospital_beds (hospital_id, total_beds, icu_beds, general_beds, private_beds, created_at, updated_at) 
                                    VALUES ($hospital_id, $total_beds, $icu_beds, $general_beds, $private_beds, NOW(), NOW())";
                    mysqli_query($con, $beds_insert);
                }
            }

            // ===== UPDATE FACILITIES =====
            // Delete existing facilities
            mysqli_query($con, "DELETE FROM hospital_facilities WHERE hospital_id = $hospital_id");
            
            // Insert new facilities
            foreach ($facilities_post as $key => $facility) {
                $facility_name = ucwords(str_replace('_', ' ', $key));
                $description = mysqli_real_escape_string($con, $facility['description'] ?? '');
                $is_available = isset($facility['available']) && $facility['available'] == '1' ? 1 : 0;
                
                // Only insert if available or has description
                if ($is_available == 1 || !empty($description)) {
                    $facility_insert = "INSERT INTO hospital_facilities (hospital_id, facility_name, description, is_available, created_at, updated_at) 
                                        VALUES ($hospital_id, '$facility_name', '$description', $is_available, NOW(), NOW())";
                    mysqli_query($con, $facility_insert);
                }
            }

            $success_msg = "Hospital updated successfully!";
            
            // Refresh data
            $edit_result = mysqli_query($con, "SELECT h.*, u.status as estatus, u.username, u.email as hospital_email
                                               FROM hospitals h
                                               LEFT JOIN users u ON u.user_id = h.user_id
                                               WHERE h.hospital_id = $hospital_id");
            $hospital_data = mysqli_fetch_assoc($edit_result);
            
            // Refresh beds data
            $beds_result = mysqli_query($con, "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id");
            $beds_data = mysqli_fetch_assoc($beds_result);
            
            // Refresh facilities data
            $facilities_result = mysqli_query($con, "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id");
            $facilities_data = [];
            while ($row = mysqli_fetch_assoc($facilities_result)) {
                $facilities_data[] = $row;
            }
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    } else {
        // Create user account for hospital
        $generate_user = "INSERT INTO users (username, email, password, user_type_id, status, created_at)
                          VALUES ('$username', '$hospital_email', '$password', 5, 1, '$created_at')";
        mysqli_query($con, $generate_user);
        $userid = mysqli_insert_id($con);

        // Insert new hospital
        $insert_query = "INSERT INTO hospitals 
                            (city_id, user_id, hospital_name, hospital_address, hospital_phone, hospital_pic, approve, created_at) 
                         VALUES 
                            ('$city_id', '$userid', '$hospital_name', '$hospital_address', '$hospital_phone', '$hospital_pic', 1, NOW())";

        if (mysqli_query($con, $insert_query)) {
            $hospital_id = mysqli_insert_id($con);
            
            // ===== INSERT BEDS =====
            if ($total_beds > 0 || $icu_beds > 0 || $general_beds > 0 || $private_beds > 0) {
                $beds_insert = "INSERT INTO hospital_beds (hospital_id, total_beds, icu_beds, general_beds, private_beds, created_at, updated_at) 
                                VALUES ($hospital_id, $total_beds, $icu_beds, $general_beds, $private_beds, NOW(), NOW())";
                mysqli_query($con, $beds_insert);
            }
            
            // ===== INSERT FACILITIES =====
            foreach ($facilities_post as $key => $facility) {
                $facility_name = ucwords(str_replace('_', ' ', $key));
                $description = mysqli_real_escape_string($con, $facility['description'] ?? '');
                $is_available = isset($facility['available']) && $facility['available'] == '1' ? 1 : 0;
                
                if ($is_available == 1 || !empty($description)) {
                    $facility_insert = "INSERT INTO hospital_facilities (hospital_id, facility_name, description, is_available, created_at, updated_at) 
                                        VALUES ($hospital_id, '$facility_name', '$description', $is_available, NOW(), NOW())";
                    mysqli_query($con, $facility_insert);
                }
            }
            
            $success_msg = "Hospital added successfully!";
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/hospital-add-admin.css">

<div class="content-wrapper">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="page-header-modern">
            <h4>
                <i class="fas fa-hospital"></i>
                <?php echo $edit_mode ? 'Edit Hospital' : 'Add New Hospital'; ?>
            </h4>
            <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn-cancel">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <!-- Alerts -->
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-modern alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-modern alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card">
            <div class="form-card-header">
                <h5><i class="fas fa-building"></i> Hospital Information</h5>
            </div>
            <div class="form-card-body">
                <form method="POST" action="" enctype="multipart/form-data" id="hospitalForm">

                    <input type="hidden" name="user_id" value="<?php echo $edit_mode && isset($hospital_data['user_id']) ? htmlspecialchars($hospital_data['user_id']) : ''; ?>">

                    <!-- Account Section -->
                    <div class="form-section-title">
                        <i class="fas fa-user-lock"></i> Login Account
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label" for="username">Username <span class="required">*</span></label>
                            <input type="text" class="form-control" id="username" name="username"
                                   placeholder="Enter username" required
                                   value="<?php echo $edit_mode ? htmlspecialchars($hospital_data['username'] ?? '') : ''; ?>"
                                   <?php echo $edit_mode ? 'readonly' : ''; ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="hospital_email">Email <span class="required">*</span></label>
                            <input type="email" class="form-control" id="hospital_email" name="hospital_email"
                                   placeholder="hospital@example.com" required
                                   value="<?php echo $edit_mode ? htmlspecialchars($hospital_data['hospital_email'] ?? '') : ''; ?>"
                                   <?php echo $edit_mode ? 'readonly' : ''; ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="password">
                                Password <?php echo $edit_mode ? '' : '<span class="required">*</span>'; ?>
                            </label>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="<?php echo $edit_mode ? 'Leave blank to keep current' : 'Enter password'; ?>"
                                   <?php echo $edit_mode ? '' : 'required'; ?>>
                            <?php if ($edit_mode): ?>
                                <small class="text-muted">Leave blank if you don't want to change password</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Basic Info Section -->
                    <div class="form-section-title">
                        <i class="fas fa-info-circle"></i> Basic Details
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="hospitalName">Hospital Name <span class="required">*</span></label>
                            <input type="text" class="form-control" id="hospitalName" name="hospital_name"
                                   placeholder="Enter hospital name" required
                                   value="<?php echo $edit_mode ? htmlspecialchars($hospital_data['hospital_name'] ?? '') : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="cityId">City <span class="required">*</span></label>
                            <select class="form-select" id="cityId" name="city_id" required>
                                <option value="">Select City</option>
                                <?php
                                if ($cities_result) {
                                    mysqli_data_seek($cities_result, 0);
                                    while ($city = mysqli_fetch_assoc($cities_result)):
                                ?>
                                    <option value="<?php echo $city['city_id']; ?>"
                                        <?php echo ($edit_mode && isset($hospital_data['city_id']) && $hospital_data['city_id'] == $city['city_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($city['city_name']); ?>
                                        <?php if (!empty($city['p_name'])): ?>
                                            (<?php echo htmlspecialchars($city['p_name']); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endwhile; } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="hospitalPhone">Helpline / Phone <span class="required">*</span></label>
                            <input type="text" class="form-control" id="hospitalPhone" name="hospital_phone"
                                   placeholder="e.g. 091-1234567" 
                                   value="<?php echo $edit_mode ? htmlspecialchars($hospital_data['hospital_phone'] ?? '') : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="hospitalAddress">Address <span class="required">*</span></label>
                            <textarea class="form-control" id="hospitalAddress" name="hospital_address"
                                      placeholder="Enter full hospital address" rows="3" required><?php echo $edit_mode ? htmlspecialchars($hospital_data['hospital_address'] ?? '') : ''; ?></textarea>
                        </div>
                    </div>

                    <!-- ===== BEDS SECTION ===== -->
                    <div class="form-section-title">
                        <i class="fas fa-bed"></i> Bed Availability
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="bed-grid">
                                <div class="bed-card">
                                    <label class="form-label">Total Beds</label>
                                    <input type="number" class="form-control" name="total_beds" 
                                           min="0" value="<?php echo $edit_mode && $beds_data ? $beds_data['total_beds'] : 0; ?>">
                                </div>
                                <div class="bed-card">
                                    <label class="form-label">ICU Beds</label>
                                    <input type="number" class="form-control" name="icu_beds" 
                                           min="0" value="<?php echo $edit_mode && $beds_data ? $beds_data['icu_beds'] : 0; ?>">
                                </div>
                                <div class="bed-card">
                                    <label class="form-label">General Beds</label>
                                    <input type="number" class="form-control" name="general_beds" 
                                           min="0" value="<?php echo $edit_mode && $beds_data ? $beds_data['general_beds'] : 0; ?>">
                                </div>
                                <div class="bed-card">
                                    <label class="form-label">Private Beds</label>
                                    <input type="number" class="form-control" name="private_beds" 
                                           min="0" value="<?php echo $edit_mode && $beds_data ? $beds_data['private_beds'] : 0; ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== FACILITIES SECTION ===== -->
                    <div class="form-section-title">
                        <i class="fas fa-concierge-bell"></i> Facilities & Services
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="facilities-grid">
                                <?php 
                                // Build existing facilities lookup array
                                $existing_facilities = [];
                                if ($edit_mode && !empty($facilities_data)) {
                                    foreach ($facilities_data as $fac) {
                                        $existing_facilities[$fac['facility_name']] = [
                                            'description' => $fac['description'] ?? '',
                                            'is_available' => $fac['is_available']
                                        ];
                                    }
                                }

                                foreach ($facility_list as $key => $label):
                                    $field_name = 'facility_' . $key;
                                    $is_checked = isset($existing_facilities[$label]) && $existing_facilities[$label]['is_available'] == 1;
                                    $description = isset($existing_facilities[$label]) ? $existing_facilities[$label]['description'] : '';
                                    $active_class = $is_checked ? 'active' : '';
                                ?>
                                <div class="facility-item <?php echo $active_class; ?>">
                                    <div class="facility-checkbox-wrapper">
                                        <input type="checkbox" 
                                               class="facility-checkbox" 
                                               id="chk_<?php echo $key; ?>" 
                                               data-target="facility_<?php echo $key; ?>"
                                               <?php echo $is_checked ? 'checked' : ''; ?>>
                                    </div>
                                    <div class="facility-label-wrapper">
                                        <label for="chk_<?php echo $key; ?>" class="facility-label">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                    <div class="facility-input-wrapper">
                                        <input type="text" 
                                               class="form-control facility-input" 
                                               id="facility_<?php echo $key; ?>" 
                                               name="facilities[<?php echo $key; ?>][description]" 
                                               value="<?php echo htmlspecialchars($description); ?>"
                                               placeholder="e.g. 24/7 available, 10 beds"
                                               <?php echo $is_checked ? '' : 'disabled'; ?>>
                                        <input type="hidden" name="facilities[<?php echo $key; ?>][available]" 
                                               value="<?php echo $is_checked ? '1' : '0'; ?>">
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Image + Status Section -->
                    <div class="form-section-title">
                        <i class="fas fa-image"></i> Picture & Status
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Hospital Picture</label>
                            <div class="image-upload-box" onclick="document.getElementById('hospital_pic').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to upload or drag image here</p>
                                <small class="text-muted">JPG, PNG, GIF, WEBP (Max recommended 2MB)</small>
                                <input type="file" id="hospital_pic" name="hospital_pic" accept="image/*" onchange="previewImage(this)">
                            </div>

                            <?php if ($edit_mode && !empty($hospital_data['hospital_pic'])): ?>
                                <div class="current-image-preview" id="currentPreview">
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo htmlspecialchars($hospital_data['hospital_pic']); ?>"
                                         alt="Current hospital image" id="previewImg">
                                </div>
                            <?php else: ?>
                                <div class="current-image-preview" id="currentPreview" style="display:none;">
                                    <img src="" alt="Preview" id="previewImg">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Active Status</label>
                            <div class="status-toggle-wrap">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="statusSwitch" name="status" value="1"
                                           <?php
                                           if ($edit_mode) {
                                               echo (isset($hospital_data['estatus']) && $hospital_data['estatus'] == 1) ? 'checked' : '';
                                           } else {
                                               echo 'checked';
                                           }
                                           ?>>
                                </div>
                                <div class="status-label">
                                    Active / Visible
                                    <small>When active, hospital will appear on the public website</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex flex-wrap gap-3 mt-4 pt-3 border-top">
                        <button type="submit" class="btn-submit">
                            <i class="fas <?php echo $edit_mode ? 'fa-save' : 'fa-plus-circle'; ?>"></i>
                            <?php echo $edit_mode ? 'Update Hospital' : 'Add Hospital'; ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>admin/hospitals/list" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<script>
// ===== FACILITY CHECKBOX TOGGLE =====
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.facility-checkbox');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const targetId = this.getAttribute('data-target');
            const inputField = document.getElementById(targetId);
            const hiddenField = this.closest('.facility-item').querySelector('input[type="hidden"]');
            const facilityItem = this.closest('.facility-item');
            
            if (this.checked) {
                inputField.disabled = false;
                inputField.focus();
                if (hiddenField) hiddenField.value = '1';
                facilityItem.classList.add('active');
            } else {
                inputField.disabled = true;
                inputField.value = '';
                if (hiddenField) hiddenField.value = '0';
                facilityItem.classList.remove('active');
            }
        });
    });
});

// ===== IMAGE PREVIEW =====
function previewImage(input) {
    const previewWrap = document.getElementById('currentPreview');
    const previewImg = document.getElementById('previewImg');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewWrap.style.display = 'inline-block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ===== SELECT2 =====
$(document).ready(function() {
    $('#cityId').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search city...',
        allowClear: true,
        width: '100%'
    });
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>