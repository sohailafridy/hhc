<?php 
include '../config.php'; 

// BASE_URL error fix (Agar config.php me define nahi hai to ye handle kar lega)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/hhc/dr/'); 
}
?>
<?php include '../check_auth.php'; ?>
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
$lab_id = 0;

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

// ── AJAX HANDLERS FOR TEST MASTER ─────────────────────────────
if (isset($_POST['save_master_test'])) {
    header('Content-Type: application/json; charset=utf-8');
    $test_id = (int)($_POST['test_id'] ?? 0);
    $test_name = trim($_POST['test_name'] ?? '');

    if ($test_name === '') {
        echo json_encode(['success' => false, 'message' => 'Please enter a test name.']);
        exit();
    }

    $duplicate = mysqli_prepare($con, "SELECT test_id FROM tbl_tests WHERE LOWER(test_name) = LOWER(?) AND test_id != ? LIMIT 1");
    mysqli_stmt_bind_param($duplicate, 'si', $test_name, $test_id);
    mysqli_stmt_execute($duplicate);
    $duplicate_result = mysqli_stmt_get_result($duplicate);
    $has_duplicate = mysqli_num_rows($duplicate_result) > 0;
    mysqli_stmt_close($duplicate);

    if ($has_duplicate) {
        echo json_encode(['success' => false, 'message' => 'This test already exists.']);
        exit();
    }

    if ($test_id > 0) {
        $stmt = mysqli_prepare($con, "UPDATE tbl_tests SET test_name = ? WHERE test_id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $test_name, $test_id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($con, "INSERT INTO tbl_tests (test_name, status) VALUES (?, 1)");
        mysqli_stmt_bind_param($stmt, 's', $test_name);
        $ok = mysqli_stmt_execute($stmt);
        $test_id = mysqli_insert_id($con);
        mysqli_stmt_close($stmt);
    }

    echo json_encode([
        'success' => (bool)$ok,
        'test_id' => $test_id,
        'test_name' => $test_name,
        'message' => $ok ? 'Test saved successfully.' : 'Unable to save test.'
    ]);
    exit();
}

// Handle form submission (Save Lab Details + Tests)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['save_master_test'])) {

    $city_id     = mysqli_real_escape_string($con, $_POST['city_id']);
    $user_id     = (int)$_POST['user_id'];
    $reg_no      = mysqli_real_escape_string($con, $_POST['reg_no']);
    $lab_name    = mysqli_real_escape_string($con, $_POST['lab_name']);
    $lab_address = mysqli_real_escape_string($con, $_POST['lab_address']);
    $lab_phone   = mysqli_real_escape_string($con, $_POST['lab_phone']);
    $lab_email   = mysqli_real_escape_string($con, $_POST['lab_email']);
    $lab_type    = mysqli_real_escape_string($con, $_POST['lab_type']);
    $status      = isset($_POST['status']) ? 1 : 0;
    $username    = mysqli_real_escape_string($con, $_POST['username']);
    $pass        = mysqli_real_escape_string($con, $_POST['password']);
    $password    = base64_encode($pass);
    
    $hospital_id = null;
    if ($lab_type == 1) {
        $hospital_id = mysqli_real_escape_string($con, $_POST['hospital_id'] ?? '');
    }
    
    $lab_pic = '';
    if (isset($_FILES['lab_pic']) && $_FILES['lab_pic']['error'] == 0) {
        $target_dir = BASE_PATH."/admin/inc/uploads/laboratories/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["lab_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["lab_pic"]["tmp_name"], $target_file)) {
                $lab_pic = $file_name;
            }
        }
    }
    
    if ($edit_mode) {
        $update_query = "UPDATE laboratories SET city_id = '$city_id', reg_no = '$reg_no', lab_name = '$lab_name', lab_address = '$lab_address', lab_phone = '$lab_phone', lab_email = '$lab_email', lab_type = '$lab_type'";
        
        if ($lab_type == 1) {
            $update_query .= ", hospital_id = '" . $hospital_id . "'";
        } else {
            $update_query .= ", hospital_id = NULL";
        }
        
        if (!empty($lab_pic)) {
            $update_query .= ", lab_pic = '$lab_pic'";
        }
        
        $update_query .= ", updated_at = NOW() WHERE lab_id = $lab_id";
        
        if (mysqli_query($con, $update_query)) {
            $user_update = "UPDATE users SET status='". $status ."'";
            if ($pass !== '') {
                $user_update .= ", password='". $password ."'";
            }
            $user_update .= " WHERE user_id=". $user_id;
            mysqli_query($con, $user_update);

            $success_msg = "Laboratory updated successfully!";
            
            $edit_result = mysqli_query($con, "SELECT laboratories.*, u.status, u.username
                FROM laboratories
                LEFT JOIN users u ON u.user_id = laboratories.user_id
                 WHERE lab_id = $lab_id");
            $lab_data = mysqli_fetch_assoc($edit_result);
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    } else {
        $hospital_id_value = ($lab_type == 1 && !empty($hospital_id)) ? "'" . $hospital_id . "'" : "NULL";

        $generate_user_id = "INSERT INTO users (username, email, password, user_type_id, status, created_at)
VALUES ('$username', '$lab_email', '$password', 3, 1, '$created_at')";
        mysqli_query($con, $generate_user_id);
        $userid = mysqli_insert_id($con);

        if ($hospital_id_value !== "NULL") {
            mysqli_query($con, "INSERT INTO assign_hospital (user_id, hospital_id, status, created_at) VALUES ($userid, $hospital_id_value, 1, NOW())");
        }

        $insert_query = "INSERT INTO laboratories (user_id, city_id, hospital_id, reg_no, lab_name, lab_address, lab_phone, lab_email, lab_type, lab_pic, approve, created_at) 
                       VALUES ($userid, '$city_id', $hospital_id_value, '$reg_no', '$lab_name', '$lab_address', '$lab_phone', '$lab_email', '$lab_type', '$lab_pic', 1, NOW())";
        
        if (mysqli_query($con, $insert_query)) {
            $lab_id = mysqli_insert_id($con);
            $success_msg = "Laboratory added successfully!";
        } else {
            $error_msg = "Error: " . mysqli_error($con);
        }
    }

    // ── SAVE LAB TESTS ──
    if (empty($error_msg) && $lab_id > 0 && isset($_POST['tests'])) {
        $selected_tests = $_POST['tests']; 
        $test_prices = $_POST['prices'] ?? []; 
        $selected = [];
        
        foreach ($selected_tests as $test_id => $val) {
            $test_id = (int)$test_id;
            $price = isset($test_prices[$test_id]) ? trim((string)$test_prices[$test_id]) : '';
            
            if ($test_id > 0 && $price !== '' && is_numeric($price) && (float)$price >= 0) {
                $selected[$test_id] = (float)$price;
            }
        }

        mysqli_query($con, "DELETE FROM tbl_lab_tests WHERE lab_id = $lab_id");

        if (!empty($selected)) {
            $insert_test = mysqli_prepare($con, "INSERT INTO tbl_lab_tests (lab_id, test_id, price, status) VALUES (?, ?, ?, 1)");
            foreach ($selected as $test_id => $price) {
                mysqli_stmt_bind_param($insert_test, "iid", $lab_id, $test_id, $price);
                mysqli_stmt_execute($insert_test);
            }
            mysqli_stmt_close($insert_test);
        }
        if (!empty($success_msg)) {
            $success_msg .= " Available tests saved.";
        }
    }
}

$available_tests_q = mysqli_query($con, "
    SELECT t.test_id, t.test_name, COALESCE(lt.price, '') AS lab_price,
           CASE WHEN lt.id IS NULL THEN 0 ELSE 1 END AS is_selected
    FROM tbl_tests t
    LEFT JOIN tbl_lab_tests lt
        ON lt.test_id = t.test_id AND lt.lab_id = $lab_id
    WHERE t.status = 1
    ORDER BY t.test_name ASC
");
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/lab-add-admin.css">



<div class="content-wrapper">
    <div class="container-fluid">

        <!-- ═══ Page Header ═══ -->
        <div class="sn-page-header sn-animate">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="sn-page-title">
                        <i class="icofont icofont-laboratory"></i>
                        <?php echo $edit_mode ? 'Edit Laboratory Profile' : 'Add New Laboratory'; ?>
                    </h2>
                    <p class="sn-page-subtitle">Manage laboratory information, services, and contact details</p>
                </div>
                <a href="<?php echo BASE_URL; ?>admin/laboratories/list" class="sn-btn-light">
                    <i class="icofont icofont-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <?php if (isset($success_msg)): ?>
            <div class="sn-alert sn-alert-success sn-animate">
                <i class="icofont icofont-check-circled fs-4 me-2"></i>
                <strong>Success!</strong>&nbsp; <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_msg)): ?>
            <div class="sn-alert sn-alert-danger sn-animate">
                <i class="icofont icofont-warning-alt fs-4 me-2"></i>
                <strong>Error!</strong>&nbsp; <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <!-- ═══ MAIN FORM START ═══ -->
        <form id="labForm" method="POST" action="" enctype="multipart/form-data" class="sn-animate sn-delay-1">
            <input type="hidden" name="lab_type" id="lab_type" value="<?php echo ($edit_mode && isset($lab_data['lab_type'])) ? htmlspecialchars($lab_data['lab_type']) : '1'; ?>">
            <input type="hidden" name="user_id" value="<?php echo ($edit_mode && isset($lab_data['user_id'])) ? htmlspecialchars($lab_data['user_id']) : ''; ?>">
            
            <!-- Mode Toggle -->
            <div class="sn-card">
                <div class="sn-card-body">
                    <div class="sn-toggle-box">
                        <label class="custom-switch">
                            <input type="checkbox" id="independent_lab_toggle" <?php echo ($edit_mode && isset($lab_data['lab_type']) && $lab_data['lab_type'] == 2) ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <div>
                            <div class="sn-toggle-label">Independent Lab Mode</div>
                            <span class="sn-toggle-desc">Switch ON if this is an independent laboratory. Switch OFF for hospital labs.</span>
                        </div>
                        <div class="ms-auto">
                            <span id="mode_badge" class="sn-mode-badge hospital">
                                <i class="icofont icofont-hospital"></i> Hospital Lab Mode
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Location & Workplace -->
                <div class="col-lg-6">
                    <div class="sn-card h-100">
                        <div class="sn-card-header">
                            <h5><i class="icofont icofont-location-pin"></i> Location & Workplace</h5>
                        </div>
                        <div class="sn-card-body">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <select class="form-control-modern" id="cityId" name="city_id" required>
                                    <option value="">Select City</option>
                                    <?php 
                                    mysqli_data_seek($cities_result, 0);
                                    while ($city = mysqli_fetch_assoc($cities_result)): 
                                        $selected_city = ($edit_mode && isset($lab_data['city_id']) && $lab_data['city_id'] == $city['city_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $city['city_id']; ?>" <?php echo $selected_city; ?>>
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
                                        while ($hospital = mysqli_fetch_assoc($hospitals_result)): 
                                            $selected_hosp = ($edit_mode && isset($lab_data['hospital_id']) && $lab_data['hospital_id'] == $hospital['hospital_id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $hospital['hospital_id']; ?>" 
                                                    data-city="<?php echo $hospital['city_id']; ?>"
                                                    <?php echo $selected_hosp; ?>>
                                                <?php echo htmlspecialchars($hospital['hospital_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Laboratory Details -->
                <div class="col-lg-6">
                    <div class="sn-card h-100">
                        <div class="sn-card-header">
                            <h5><i class="icofont icofont-laboratory"></i> Laboratory Details</h5>
                        </div>
                        <div class="sn-card-body">
                            <div class="form-group">
                                <label class="form-label">Lab Registration Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control-modern" name="reg_no" id="reg_no" 
                                       placeholder="Enter Registration Number" required autocomplete="off"
                                       value="<?php echo ($edit_mode && !empty($lab_data['reg_no'])) ? htmlspecialchars($lab_data['reg_no']) : ''; ?>">
                                <small class="text-danger d-none" id="reg_no_error">This Registration Number is already taken.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control-modern" name="username" id="username" 
                                               placeholder="Enter username" required autocomplete="off"
                                               value="<?php echo ($edit_mode && !empty($lab_data['username'])) ? htmlspecialchars($lab_data['username']) : ''; ?>" <?php echo $edit_mode ? 'readonly' : ''; ?>>
                                        <?php if(!$edit_mode): ?>
                                            <small class="text-danger d-none" id="username_error">This Username is already taken.</small>
                                        <?php endif; ?>
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
                                       value="<?php echo ($edit_mode && !empty($lab_data['lab_name'])) ? htmlspecialchars($lab_data['lab_name']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control-modern" name="lab_phone"
                                       placeholder="Contact Number"
                                       value="<?php echo ($edit_mode && !empty($lab_data['lab_phone'])) ? htmlspecialchars($lab_data['lab_phone']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control-modern" name="lab_email"
                                       placeholder="lab@example.com"
                                       value="<?php echo ($edit_mode && !empty($lab_data['lab_email'])) ? htmlspecialchars($lab_data['lab_email']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <textarea class="form-control-modern" name="lab_address" rows="3"
                                          placeholder="Full address of laboratory"><?php echo ($edit_mode && !empty($lab_data['lab_address'])) ? htmlspecialchars($lab_data['lab_address']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Tests -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="sn-card">
                        <div class="sn-card-header">
                            <h5><i class="icofont icofont-flask"></i> Available Tests</h5>
                            <small>Select tests and set prices for this lab</small>
                        </div>
                        <div class="sn-card-body" id="tests-section-body">
                            <?php if ($available_tests_q && mysqli_num_rows($available_tests_q) > 0): ?>
                                <div class="available-tests-list" id="available-tests-list">
                                    <?php while ($test = mysqli_fetch_assoc($available_tests_q)): ?>
                                        <div class="available-test-row">
                                            <input
                                                type="checkbox"
                                                class="available-test-check"
                                                id="test_<?= (int)$test['test_id'] ?>"
                                                name="tests[<?= (int)$test['test_id'] ?>]"
                                                value="1" 
                                                <?= (int)$test['is_selected'] === 1 ? 'checked' : '' ?>
                                            >
                                            <label class="available-test-name" for="test_<?= (int)$test['test_id'] ?>">
                                                <?= htmlspecialchars($test['test_name']) ?>
                                            </label>
                                            <button type="button" class="test-edit-btn"
                                                data-test-id="<?= (int)$test['test_id'] ?>"
                                                data-test-name="<?= htmlspecialchars($test['test_name'], ENT_QUOTES) ?>"
                                                onclick="editMasterTest(this)">Edit</button>
                                            <div class="available-test-price">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    name="prices[<?= (int)$test['test_id'] ?>]"
                                                    value="<?= htmlspecialchars((string)$test['lab_price']) ?>"
                                                    placeholder="Price"
                                                    aria-label="Price for <?= htmlspecialchars($test['test_name']) ?>"
                                                    <?= (int)$test['is_selected'] === 1 ? '' : 'disabled' ?>
                                                >
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-2 mb-0">No tests found. Please add tests to the tests master list first.</p>
                            <?php endif; ?>

                            <!-- Master Test Form -->
                            <div id="masterTestForm" class="test-master-form">
                                <h6 class="form-title"><i class="fas fa-plus-circle me-1"></i> Add New Test / Update Test</h6>
                                <input type="hidden" name="test_id" id="masterTestId" value="0">
                                <div class="test-master-fields">
                                    <div class="field">
                                        <label for="masterTestName">Test Name</label>
                                        <input type="text" id="masterTestName" name="test_name" maxlength="255" placeholder="e.g. CBC, LFT, MRI" >
                                    </div>
                                    <button type="button" class="test-master-btn" id="saveMasterTestBtn" onclick="saveMasterTest()"><i class="fas fa-plus"></i> Add Test</button>
                                    <button type="button" class="test-master-cancel" id="cancelMasterTestEdit" style="display:none;" onclick="resetMasterTestForm()">Cancel</button>
                                </div>
                                <div id="masterTestMessage" class="test-master-message" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Settings -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="sn-card">
                        <div class="sn-card-header">
                            <h5><i class="icofont icofont-settings"></i> Additional Settings</h5>
                        </div>
                        <div class="sn-card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Laboratory Picture</label>
                                        <input type="file" class="form-control-modern" name="lab_pic" accept="image/*">
                                        <small class="text-muted mt-2 d-block">Recommended size: 500x500px (JPG, PNG)</small>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <?php if ($edit_mode && !empty($lab_data['lab_pic'])): ?>
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/laboratories/<?php echo htmlspecialchars($lab_data['lab_pic']); ?>" 
                                             alt="Current Picture" class="img-preview">
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <hr style="border-top: 1px solid #eef4f8; margin: 26px 0;">
                            
                            <div class="sn-toggle-box">
                                <label class="custom-switch" style="margin:0;">
                                    <input type="checkbox" name="status" value="1"
                                    <?php echo (!$edit_mode || (isset($lab_data['status']) && $lab_data['status'] == 1)) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <div>
                                    <div class="sn-toggle-label">Active Status</div>
                                    <span class="sn-toggle-desc">Enable to make this laboratory visible in the public directory.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="text-center mt-4 mb-5 sn-animate sn-delay-2">
                <button type="submit" id="saveBtn" class="sn-btn sn-btn-save">
                    <i class="icofont icofont-save"></i> Save Laboratory Details
                </button>
                <a href="<?php echo BASE_URL; ?>admin/laboratories/list" class="sn-btn sn-btn-cancel">
                    <i class="icofont icofont-close"></i> Cancel
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
    
    updateView();
    filterHospitals();

    toggle.addEventListener('change', updateView);
    citySelect.addEventListener('change', filterHospitals);

    function updateView() {
        if (toggle.checked) {
            hospitalSection.style.display = 'none';
            hospitalId.removeAttribute('required');
            labTypeInput.value = '2';
            modeBadge.className = 'sn-mode-badge clinic';
            modeBadge.innerHTML = '<i class="icofont icofont-building"></i> Independent Lab Mode';
        } else {
            if (citySelect.value) {
                hospitalSection.style.display = 'block';
                hospitalId.setAttribute('required', 'required');
            } else {
                hospitalSection.style.display = 'none';
            }
            labTypeInput.value = '1';
            modeBadge.className = 'sn-mode-badge hospital';
            modeBadge.innerHTML = '<i class="icofont icofont-hospital"></i> Hospital Lab Mode';
        }
    }

    function filterHospitals() {
        const selectedCity = citySelect.value;
        const options = hospitalId.options;
        
        if (!selectedCity) {
            hospitalSection.style.display = 'none';
            hospitalId.value = '';
            hospitalId.removeAttribute('required');
            return;
        }

        if (!toggle.checked) {
            hospitalSection.style.display = 'block';
            hospitalId.setAttribute('required', 'required');
        }

        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            if (option.value === "") continue;

            const hospitalCity = option.getAttribute('data-city');
            if (hospitalCity == selectedCity) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        }
        
        const currentOption = options[hospitalId.selectedIndex];
        if (currentOption && currentOption.style.display === 'none') {
            hospitalId.value = '';
        }
    }

    // AJAX Validation
    const regNoInput = document.getElementById('reg_no');
    const usernameInput = document.getElementById('username');
    const regNoError = document.getElementById('reg_no_error');
    const usernameError = document.getElementById('username_error');
    const saveBtn = document.getElementById('saveBtn');
    
    const ajaxUrl = '<?php echo BASE_URL; ?>admin/ajax_check.php'; 
    const currentRegNo = "<?php echo $edit_mode ? addslashes($lab_data['reg_no']) : ''; ?>";
    const currentUsername = "<?php echo $edit_mode ? addslashes($lab_data['username']) : ''; ?>";
    const currentLabId = "<?php echo $lab_id; ?>";

    let isRegNoValid = true;
    let isUsernameValid = true;

    if(regNoInput) {
        regNoInput.addEventListener('keyup', function() {
            let val = this.value.trim();
            if (val === currentRegNo) {
                regNoError.classList.add('d-none');
                isRegNoValid = true;
                checkFormState();
                return;
            }
            if (val.length > 0) {
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'field=reg_no&value=' + encodeURIComponent(val) + '&exclude_id=' + currentLabId
                }).then(response => response.json()).then(data => {
                    if (data.exists) {
                        regNoError.classList.remove('d-none');
                        isRegNoValid = false;
                    } else {
                        regNoError.classList.add('d-none');
                        isRegNoValid = true;
                    }
                    checkFormState();
                }).catch(err => console.error("AJAX Error:", err));
            } else {
                regNoError.classList.add('d-none');
                isRegNoValid = true;
                checkFormState();
            }
        });
    }

    if(usernameInput && !usernameInput.hasAttribute('readonly')) {
        usernameInput.addEventListener('keyup', function() {
            let val = this.value.trim();
            if (val.length > 0) {
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'field=username&value=' + encodeURIComponent(val) + '&exclude_id=' + currentLabId
                }).then(response => response.json()).then(data => {
                    if (data.exists) {
                        usernameError.classList.remove('d-none');
                        isUsernameValid = false;
                    } else {
                        usernameError.classList.add('d-none');
                        isUsernameValid = true;
                    }
                    checkFormState();
                }).catch(err => console.error("AJAX Error:", err));
            } else {
                usernameError.classList.add('d-none');
                isUsernameValid = true;
                checkFormState();
            }
        });
    }

    function checkFormState() {
        if (isRegNoValid && isUsernameValid) {
            saveBtn.disabled = false;
            saveBtn.style.opacity = '1';
            saveBtn.style.cursor = 'pointer';
        } else {
            saveBtn.disabled = true;
            saveBtn.style.opacity = '0.5';
            saveBtn.style.cursor = 'not-allowed';
        }
    }

    // Test Checkbox Logic (Enable/Disable Price)
    document.querySelectorAll('.available-test-check').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var priceInput = this.closest('.available-test-row').querySelector('.available-test-price input');
            priceInput.disabled = !this.checked;
            if (this.checked) {
                priceInput.focus();
            } else {
                priceInput.value = '';
            }
        });
    });

    // Form Submit Validation (Ensure price is entered for checked tests)
    document.getElementById('labForm').addEventListener('submit', function(e) {
        let isValid = true;
        document.querySelectorAll('.available-test-check:checked').forEach(function(chk) {
            let priceInput = chk.closest('.available-test-row').querySelector('.available-test-price input');
            if(priceInput.value.trim() === '' || isNaN(priceInput.value)) {
                isValid = false;
                priceInput.style.borderColor = 'red';
            } else {
                priceInput.style.borderColor = '#dbe7f0';
            }
        });
        
        if(!isValid) {
            e.preventDefault();
            alert('Please enter a valid price for all selected tests.');
        }
    });
});

// ======= MASTER TEST AJAX FUNCTION =======
function saveMasterTest() {
    var btn = document.getElementById('saveMasterTestBtn');
    var message = document.getElementById('masterTestMessage');
    var testId = document.getElementById('masterTestId').value;
    var testName = document.getElementById('masterTestName').value.trim();

    if(testName === '') {
        message.textContent = 'Please enter a test name.';
        message.style.color = '#ef4444';
        message.style.display = 'block';
        return;
    }

    var formData = new URLSearchParams();
    formData.append('save_master_test', '1');
    formData.append('test_id', testId);
    formData.append('test_name', testName);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    message.style.display = 'none';

    fetch(window.location.href, { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString() 
    })
    .then(function (response) { return response.json(); })
    .then(function (data) {
        message.textContent = data.message || 'Unable to save test.';
        message.style.color = data.success ? '#16a34a' : '#ef4444';
        message.style.display = 'block';

        if (data.success) {
            setTimeout(function () { window.location.reload(); }, 900);
        }
    })
    .catch(function () {
        message.textContent = 'Something went wrong. Please try again.';
        message.style.color = '#ef4444';
        message.style.display = 'block';
    })
    .finally(function () {
        btn.disabled = false;
        btn.innerHTML = Number(document.getElementById('masterTestId').value) > 0
            ? '<i class="fas fa-save"></i> Update Test'
            : '<i class="fas fa-plus"></i> Add Test';
    });
}

function editMasterTest(button) {
    document.getElementById('masterTestId').value = button.dataset.testId;
    document.getElementById('masterTestName').value = button.dataset.testName;
    document.getElementById('saveMasterTestBtn').innerHTML = '<i class="fas fa-save"></i> Update Test';
    document.getElementById('cancelMasterTestEdit').style.display = 'inline-block';
    document.getElementById('masterTestName').focus();
    document.getElementById('masterTestForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function resetMasterTestForm() {
    document.getElementById('masterTestId').value = '0';
    document.getElementById('masterTestName').value = '';
    document.getElementById('saveMasterTestBtn').innerHTML = '<i class="fas fa-plus"></i> Add Test';
    document.getElementById('cancelMasterTestEdit').style.display = 'none';
    document.getElementById('masterTestMessage').style.display = 'none';
}
</script>