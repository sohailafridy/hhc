<?php include '../config.php'; ?>
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
    $edit_query = "SELECT h.*, e.status as estatus, u.username, u.email as hospital_email
                   FROM hospitals h
                   LEFT JOIN entities e ON e.entity_id = h.entity_id
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
    $entity_id        = isset($_POST['entity_id']) ? mysqli_real_escape_string($con, $_POST['entity_id']) : '';
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
            // Update entity status
            if (!empty($entity_id)) {
                mysqli_query($con, "UPDATE entities SET status = '$status' WHERE entity_id = '$entity_id'");
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
            $edit_result = mysqli_query($con, "SELECT h.*, e.status as estatus, u.username, u.email as hospital_email
                                               FROM hospitals h
                                               LEFT JOIN entities e ON e.entity_id = h.entity_id
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
        // Create new entity
        $generate_ent = "INSERT INTO entities (entity_type, status, created_at) VALUES ('hospital', 1, NOW())";
        mysqli_query($con, $generate_ent);
        $entity_id = mysqli_insert_id($con);

        // Create user account for hospital
        $generate_user = "INSERT INTO users (username, email, password, user_type_id, status, created_at)
                          VALUES ('$username', '$hospital_email', '$password', 5, 1, '$created_at')";
        mysqli_query($con, $generate_user);
        $userid = mysqli_insert_id($con);

        // Insert new hospital
        $insert_query = "INSERT INTO hospitals 
                            (entity_id, city_id, user_id, hospital_name, hospital_address, hospital_phone, hospital_pic, approve, created_at) 
                         VALUES 
                            ($entity_id, '$city_id', '$userid', '$hospital_name', '$hospital_address', '$hospital_phone', '$hospital_pic', 1, NOW())";

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

<style>
:root {
    --primary: #4facfe;
    --primary-dark: #0d6efd;
    --accent: #00f2fe;
    --text: #1e293b;
    --muted: #64748b;
    --border: #e2e8f0;
    --bg: #f8fafc;
    --card: #ffffff;
    --success: #10b981;
    --danger: #ef4444;
}

.content-wrapper {
    background: var(--bg);
    min-height: 100vh;
}

.page-header-modern {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 28px;
}

.page-header-modern h4 {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header-modern h4 i {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.form-card {
    background: var(--card);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(15, 23, 42, 0.06);
    border: 1px solid rgba(255,255,255,0.8);
    overflow: hidden;
    margin-bottom: 30px;
}

.form-card-header {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    padding: 22px 28px;
    border-bottom: 1px solid var(--border);
}

.form-card-header h5 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-card-header h5 i {
    color: var(--primary-dark);
}

.form-card-body {
    padding: 32px 28px;
}

.form-section-title {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--muted);
    margin-bottom: 18px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

.form-label {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text);
    margin-bottom: 8px;
}

.form-label .required {
    color: var(--danger);
}

.form-control, .form-select {
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 0.95rem;
    transition: all 0.25s ease;
    background: #fff;
    height: auto;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(79, 172, 254, 0.15);
    outline: none;
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

/* Image Upload */
.image-upload-box {
    border: 2px dashed var(--border);
    border-radius: 16px;
    padding: 24px;
    text-align: center;
    background: #f8fafc;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.image-upload-box:hover {
    border-color: var(--primary);
    background: #f0f9ff;
}

.image-upload-box i {
    font-size: 2.2rem;
    color: var(--primary);
    margin-bottom: 10px;
}

.image-upload-box p {
    margin: 0;
    color: var(--muted);
    font-size: 0.9rem;
}

.image-upload-box input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.current-image-preview {
    margin-top: 16px;
    display: inline-block;
    position: relative;
}

.current-image-preview img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 14px;
    border: 3px solid white;
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

/* Status Toggle */
.status-toggle-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    background: #f8fafc;
    border-radius: 14px;
    border: 1.5px solid var(--border);
}

.status-toggle-wrap .form-check-input {
    width: 48px;
    height: 26px;
    cursor: pointer;
}

.status-toggle-wrap .form-check-input:checked {
    background-color: var(--success);
    border-color: var(--success);
}

.status-label {
    font-weight: 600;
    color: var(--text);
}

.status-label small {
    display: block;
    font-weight: 400;
    color: var(--muted);
    font-size: 0.8rem;
}

/* Bed Grid */
.bed-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.bed-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px 20px;
    border: 1px solid var(--border);
}

.bed-card .form-label {
    font-size: 0.8rem;
    margin-bottom: 4px;
}

.bed-card .form-control {
    padding: 8px 12px;
    font-size: 0.9rem;
}

/* ===== FACILITIES GRID ===== */
.facilities-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    background: #f8fafc;
    padding: 20px;
    border-radius: 16px;
    border: 1px solid var(--border);
}

.facility-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    padding: 8px 14px;
    border-radius: 10px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    min-height: 48px;
}

.facility-item:hover {
    border-color: var(--primary);
    box-shadow: 0 2px 8px rgba(79, 172, 254, 0.1);
}

.facility-item.active {
    border-color: var(--success);
    background: #f0fdf4;
}

.facility-checkbox-wrapper {
    flex: 0 0 36px;
}

.facility-checkbox-wrapper input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
    cursor: pointer;
}

.facility-label-wrapper {
    flex: 0 0 130px;
}

.facility-label {
    font-weight: 600;
    color: var(--text);
    cursor: pointer;
    font-size: 0.85rem;
    margin: 0;
}

.facility-input-wrapper {
    flex: 1;
    min-width: 120px;
}

.facility-input-wrapper .form-control {
    padding: 4px 10px;
    font-size: 0.8rem;
    border-radius: 8px;
    height: 34px;
    border: 1.5px solid var(--border);
    background: #fff;
}

.facility-input-wrapper .form-control:disabled {
    background: #f1f5f9;
    cursor: not-allowed;
    opacity: 0.6;
}

.facility-input-wrapper .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
}

/* Buttons */
.btn-submit {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 13px 32px;
    font-weight: 700;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 18px rgba(13, 110, 253, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(13, 110, 253, 0.4);
    color: white;
}

.btn-cancel {
    background: white;
    color: var(--muted);
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 13px 28px;
    font-weight: 600;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #f1f5f9;
    color: var(--text);
    border-color: #cbd5e1;
}

/* Alert */
.alert-modern {
    border-radius: 14px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    border: none;
}

.alert-modern.alert-success {
    background: #ecfdf5;
    color: #065f46;
}

.alert-modern.alert-danger {
    background: #fef2f2;
    color: #991b1b;
}

.alert-modern i {
    font-size: 1.25rem;
}

/* Select2 override */
.select2-container--bootstrap-5 .select2-selection {
    border: 1.5px solid var(--border) !important;
    border-radius: 12px !important;
    min-height: 48px !important;
    padding: 6px 12px !important;
}

.select2-container--bootstrap-5.select2-container--focus .select2-selection {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 4px rgba(79, 172, 254, 0.15) !important;
}

@media (max-width: 1200px) {
    .facilities-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 992px) {
    .facilities-grid {
        grid-template-columns: 1fr;
    }
    .facility-label-wrapper {
        flex: 0 0 110px;
    }
}

@media (max-width: 768px) {
    .form-card-body {
        padding: 24px 18px;
    }
    .page-header-modern h4 {
        font-size: 1.3rem;
    }
    .bed-grid {
        grid-template-columns: 1fr 1fr;
    }
    .facility-item {
        flex-wrap: wrap;
        gap: 6px;
        padding: 10px 12px;
    }
    .facility-checkbox-wrapper {
        flex: 0 0 30px;
    }
    .facility-label-wrapper {
        flex: 1;
    }
    .facility-input-wrapper {
        flex: 1 1 100%;
    }
}

@media (max-width: 576px) {
    .bed-grid {
        grid-template-columns: 1fr;
    }
    .facilities-grid {
        padding: 12px;
    }
}
</style>

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

                    <input type="hidden" name="entity_id" value="<?php echo $edit_mode && isset($hospital_data['entity_id']) ? htmlspecialchars($hospital_data['entity_id']) : ''; ?>">

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
                                   placeholder="e.g. 091-1234567" required
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