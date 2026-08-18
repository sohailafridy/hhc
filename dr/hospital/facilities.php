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

<style>
.content-wrapper {
    background: #f8fafc;
    min-height: 100vh;
    padding: 24px 32px 60px;
}

.page-header {
    margin-bottom: 28px;
}

.page-header h4 {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1e293b;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
}

.form-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.form-card-header {
    padding: 18px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.form-card-header h5 {
    margin: 0;
    font-weight: 700;
}

.form-card-body {
    padding: 24px;
}

/* ===== FACILITY ITEMS ===== */
.facility-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.facility-item:hover {
    border-color: #4facfe;
    background: #f0f7ff;
}

.facility-item.active {
    border-color: #22c55e;
    background: #f0fdf4;
}

.facility-item .facility-check {
    flex: 0 0 40px;
}

.facility-item .facility-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #4facfe;
    cursor: pointer;
}

.facility-item .facility-label {
    flex: 0 0 150px;
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e293b;
}

.facility-item .facility-label label {
    cursor: pointer;
}

.facility-item .facility-input {
    flex: 1;
}

.facility-item .facility-input input {
    width: 100%;
    padding: 6px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    background: white;
}

.facility-item .facility-input input:focus {
    border-color: #4facfe;
    box-shadow: 0 0 0 3px rgba(79,172,254,0.12);
    outline: none;
}

.facility-item .facility-input input:disabled {
    background: #f1f5f9;
    cursor: not-allowed;
    opacity: 0.6;
}

.facility-item .facility-input .required-star {
    color: #ef4444;
    display: none;
    font-size: 0.7rem;
    margin-left: 4px;
}

.facility-item .facility-input .required-star.show {
    display: inline;
}

/* ===== BUTTONS ===== */
.btn-submit {
    background: linear-gradient(135deg, #4facfe, #0d6efd);
    color: white;
    border: none;
    padding: 10px 30px;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(13,110,253,0.3);
    color: white;
}

.btn-cancel {
    background: #f1f5f9;
    color: #64748b;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-cancel:hover {
    background: #e2e8f0;
    color: #1e293b;
}

@media (max-width: 768px) {
    .content-wrapper { padding: 16px; }
    .facility-item { flex-wrap: wrap; gap: 8px; }
    .facility-item .facility-label { flex: 1; }
    .facility-item .facility-input { flex: 1 1 100%; }
}
</style>

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