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

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $lab_id = $_GET['id'];
    $edit_query = "SELECT laboratories.*,e.status
     FROM laboratories 
     LEFT JOIN entities e ON e.entity_id = laboratories.entity_id
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
$hospitals_query = "SELECT hospitals.entity_id,hospital_id, hospital_name, city_id 
FROM hospitals 
LEFT JOIN entities e ON e.entity_id = hospitals.entity_id
WHERE e.status = 1 AND hospitals.approve=1 ORDER BY hospital_name ASC";
$hospitals_result = mysqli_query($con, $hospitals_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $city_id     = mysqli_real_escape_string($con, $_POST['city_id']);
    $entity_id     = mysqli_real_escape_string($con, $_POST['entity_id']);
    $lab_name    = mysqli_real_escape_string($con, $_POST['lab_name']);
    $lab_address = mysqli_real_escape_string($con, $_POST['lab_address']);
    $lab_phone   = mysqli_real_escape_string($con, $_POST['lab_phone']);
    $lab_email   = mysqli_real_escape_string($con, $_POST['lab_email']);
    $lab_type    = mysqli_real_escape_string($con, $_POST['lab_type']);
    $status      = isset($_POST['status']) ? 1 : 0;
    
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
            mysqli_query($con, "UPDATE entities set status='". $status ."' WHERE entity_id='". $entity_id ."'");
            $success_msg = "Laboratory updated successfully!";
            // Refresh data
            $edit_result = mysqli_query($con, "SELECT laboratories.*,e.status
                FROM laboratories
                LEFT JOIN entities e ON e.entity_id = laboratories.entity_id
                 WHERE lab_id = $lab_id");
            $lab_data = mysqli_fetch_assoc($edit_result);
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    } else {
        // Insert new lab
        $hospital_id_value = ($lab_type == 1 && !empty($hospital_id)) ? "'" . $hospital_id . "'" : "NULL";
            
        $generate_ent_it = "INSERT INTO entities (entity_type,status, created_at) VALUES ('lab',1,date('Y-m-d'))";
        mysqli_query($con, $generate_ent_it);
        $entity_id = mysqli_insert_id($con);

        $insert_query = "INSERT INTO laboratories (entity_id,city_id, hospital_id, lab_name, lab_address, lab_phone, lab_email, lab_type, lab_pic, approve, created_at) 
                       VALUES ($entity_id,'$city_id', $hospital_id_value, '$lab_name', '$lab_address', '$lab_phone', '$lab_email', '$lab_type', '$lab_pic', 1, NOW())";
        
        if (mysqli_query($con, $insert_query)) {
            $success_msg = "Laboratory added successfully!";
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    }
}
?>

<style>
    :root {
        --primary-color: #4facfe;
        --secondary-color: #00f2fe;
        --accent-color: #764ba2;
        --text-color: #2c3e50;
        --bg-color: #f4f7f6;
        --card-bg: #ffffff;
        --input-bg: #fdfdfd;
        --border-color: #e0e0e0;
        --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.05);
        --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-color);
        font-family: 'Ubuntu', sans-serif;
    }

    .content-wrapper {
        padding: 30px;
        min-height: 100vh;
    }

    .page-header {
        margin-bottom: 30px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        padding: 30px;
        border-radius: 20px;
        color: white;
        box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .page-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
    }

    .page-subtitle {
        opacity: 0.9;
        margin-top: 5px;
        font-size: 16px;
    }

    .modern-card {
        background: var(--card-bg);
        border-radius: 20px;
        box-shadow: var(--shadow-soft);
        border: none;
        margin-bottom: 30px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    
    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .card-header-custom {
        padding: 20px 25px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        background: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .card-header-custom h5 {
        margin: 0;
        font-weight: 600;
        color: #333;
        font-size: 18px;
        display: flex;
        align-items: center;
    }
    
    .card-header-custom h5 i {
        margin-right: 10px;
        color: var(--primary-color);
        font-size: 20px;
    }

    .card-body-custom {
        padding: 30px;
    }

    .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .form-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
        display: block;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control-modern {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        background-color: var(--input-bg);
        transition: all 0.3s;
        font-size: 15px;
        color: #333;
    }

    .form-control-modern:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(79, 172, 254, 0.1);
    }
    
    select.form-control-modern {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 16px;
    }

    .toggle-container {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 15px;
        border: 1px solid #eee;
        margin-bottom: 30px;
    }

    .custom-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        margin-right: 15px;
    }

    .custom-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    input:checked + .slider {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }
    
    .toggle-label {
        font-weight: 600;
        font-size: 16px;
    }
    
    .toggle-description {
        display: block;
        font-size: 13px;
        color: #777;
        margin-top: 2px;
    }

    .btn-action {
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-transform: uppercase;
        font-size: 14px;
    }
    
    .btn-save {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(56, 239, 125, 0.3);
        color: white;
    }
    
    .btn-cancel {
        background: white;
        color: #e74c3c;
        border: 2px solid #e74c3c;
        margin-left: 15px;
    }
    
    .btn-cancel:hover {
        background: #e74c3c;
        color: white;
    }

    .img-preview {
        width: 150px;
        height: 150px;
        border-radius: 15px;
        object-fit: cover;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border: 4px solid white;
    }
    
    .alert-modern {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
    }
    
    .alert-success-modern {
        background: rgba(56, 239, 125, 0.15);
        color: #065f46;
        border-left: 4px solid #38ef7d;
    }
    
    .alert-danger-modern {
        background: rgba(231, 76, 60, 0.15);
        color: #922b21;
        border-left: 4px solid #e74c3c;
    }

    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-status.hospital {
        background: rgba(79, 172, 254, 0.1);
        color: #4facfe;
    }
    
    .badge-status.clinic {
        background: rgba(118, 75, 162, 0.1);
        color: #764ba2;
    }
    
    /* Animation */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-up {
        animation: fadeInUp 0.5s ease-out forwards;
    }
    
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }

</style>

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
            <input type="hidden" name="entity_id" value="<?php if(isset($lab_data['entity_id'])){ echo $lab_data['entity_id']; } ?>">
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
                            <div class="form-group">
                                <label class="form-label">Laboratory Name</label>
                                <input type="text" class="form-control-modern" name="lab_name" required
                                       placeholder="e.g. City Medical Laboratory"
                                       value="<?php echo $edit_mode ? htmlspecialchars($lab_data['lab_name']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control-modern" name="lab_phone" required
                                       placeholder="Contact Number"
                                       value="<?php echo $edit_mode ? htmlspecialchars($lab_data['lab_phone']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control-modern" name="lab_email" required
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
