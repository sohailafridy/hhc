<?php
include '../config.php';

// ============================================
// HOSPITAL AUTHENTICATION
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id = $_SESSION['user_id'];

$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];

// ============================================
// UPDATE FACILITIES - FIXED
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Delete existing facilities
    mysqli_query($con, "DELETE FROM hospital_facilities WHERE hospital_id = $hospital_id");
    
    // ============================================
    // FIX: Get available facilities from checkbox array
    // ============================================
    $available_facilities = isset($_POST['facilities_available']) ? $_POST['facilities_available'] : [];
    $facility_descriptions = isset($_POST['facility_descriptions']) ? $_POST['facility_descriptions'] : [];
    
    // Predefined facility list
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
    
    foreach ($facility_list as $key => $facility_name) {
        $is_available = in_array($key, $available_facilities) ? 1 : 0;
        $description = isset($facility_descriptions[$key]) ? mysqli_real_escape_string($con, $facility_descriptions[$key]) : '';
        
        // Only insert if available OR has description
        if ($is_available == 1 || !empty($description)) {
            $insert_query = "INSERT INTO hospital_facilities 
                                (hospital_id, facility_name, description, is_available, created_at, updated_at) 
                             VALUES 
                                ($hospital_id, '$facility_name', '$description', $is_available, NOW(), NOW())";
            mysqli_query($con, $insert_query);
        }
    }
    
    $_SESSION['success_msg'] = "Facilities updated successfully!";
    header('Location: ' . BASE_URL . 'hospital/facilities');
    exit();
}

// Get current facilities
$facilities_query = "SELECT * FROM hospital_facilities WHERE hospital_id = $hospital_id";
$facilities_result = mysqli_query($con, $facilities_query);
$facilities = [];
while ($row = mysqli_fetch_assoc($facilities_result)) {
    $facilities[] = $row;
}

// Predefined facility list with keys
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

// Merge existing facilities with predefined list
$existing_facilities = [];
foreach ($facilities as $fac) {
    $existing_facilities[$fac['facility_name']] = $fac;
}
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-facalities-admin.css">

<div class="content-wrapper">

    <div class="page-header">
        <h4><i class="fas fa-concierge-bell me-2"></i> Facilities Management</h4>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i> 
            <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-card-header">
            <h5><i class="fas fa-edit me-2"></i> Update Facilities & Services</h5>
        </div>
        <div class="form-card-body">
            <form method="POST" id="facilityForm">
                
                <?php foreach ($facility_list as $key => $facility): 
                    $exists = isset($existing_facilities[$facility]);
                    $is_available = $exists && $existing_facilities[$facility]['is_available'] == 1;
                    $description = $exists ? $existing_facilities[$facility]['description'] : '';
                    $active_class = $is_available ? 'active' : '';
                ?>
                    <div class="facility-item <?php echo $active_class; ?>">
                        <div class="facility-check">
                            <input type="checkbox" 
                                   class="facility-checkbox" 
                                   id="chk_<?php echo $key; ?>"
                                   name="facilities_available[]" 
                                   value="<?php echo $key; ?>"
                                   <?php echo $is_available ? 'checked' : ''; ?>>
                        </div>
                        <div class="facility-label">
                            <label for="chk_<?php echo $key; ?>"><?php echo $facility; ?></label>
                        </div>
                        <div class="facility-input">
                            <input type="text" 
                                   class="facility-desc" 
                                   id="desc_<?php echo $key; ?>"
                                   name="facility_descriptions[<?php echo $key; ?>]" 
                                   value="<?php echo htmlspecialchars($description); ?>"
                                   placeholder="e.g. 24/7 available, 10 beds"
                                   <?php echo $is_available ? '' : 'disabled'; ?>
                                   <?php echo $is_available ? 'required' : ''; ?>>
                            <span class="required-star <?php echo $is_available ? 'show' : ''; ?>">*</span>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="mt-4">
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-save me-2"></i> Update Facilities
                    </button>
                    <a href="<?php echo BASE_URL; ?>hospital/index.php" class="btn-cancel ms-2">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.facility-checkbox');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            // Get the facility item
            const facilityItem = this.closest('.facility-item');
            
            // Find the description input
            const inputField = facilityItem.querySelector('.facility-desc');
            
            // Find the required star
            const requiredStar = facilityItem.querySelector('.required-star');
            
            if (this.checked) {
                // Enable input and make it required
                inputField.disabled = false;
                inputField.required = true;
                inputField.focus();
                facilityItem.classList.add('active');
                if (requiredStar) requiredStar.classList.add('show');
            } else {
                // Disable input and clear value
                inputField.disabled = true;
                inputField.required = false;
                inputField.value = '';
                facilityItem.classList.remove('active');
                if (requiredStar) requiredStar.classList.remove('show');
            }
        });
    });
    
    // ============================================
    // FORM VALIDATION
    // ============================================
    document.getElementById('facilityForm').addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('.facility-checkbox:checked');
        let hasError = false;
        let errorMessage = '';
        
        checkboxes.forEach(function(checkbox) {
            const facilityItem = checkbox.closest('.facility-item');
            const inputField = facilityItem.querySelector('.facility-desc');
            const facilityName = facilityItem.querySelector('.facility-label').textContent.trim();
            
            if (inputField.value.trim() === '') {
                inputField.style.borderColor = '#ef4444';
                inputField.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.12)';
                hasError = true;
                errorMessage += '• Please enter description for ' + facilityName + '\n';
            } else {
                inputField.style.borderColor = '#22c55e';
                inputField.style.boxShadow = 'none';
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Please fill in description for all checked facilities:\n\n' + errorMessage);
            
            // Focus on first empty field
            const firstError = document.querySelector('.facility-desc[style*="border-color: #ef4444"]');
            if (firstError) {
                firstError.focus();
            }
        }
    });
    
    // Clear error state on input
    document.querySelectorAll('.facility-desc').forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.style.borderColor = '#22c55e';
                this.style.boxShadow = 'none';
            }
        });
    });
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>