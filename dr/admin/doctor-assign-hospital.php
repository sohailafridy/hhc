<?php include '../config.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = mysqli_real_escape_string($con, $_POST['doctor_id']);
    $hospitals = isset($_POST['hospitals']) ? $_POST['hospitals'] : [];
    
    if (!empty($hospitals)) {
        foreach ($hospitals as $hospital_id) {
            $hospital_id = mysqli_real_escape_string($con, $hospital_id);
            $insert_query = "INSERT INTO doctor_in_hospital (doctor_id, hospital_id, if_clinic) 
                           VALUES ('$doctor_id', '$hospital_id', 0)";
            mysqli_query($con, $insert_query);
        }
        $success_msg = "Hospitals assigned to doctor successfully!";
        // Refresh the page to show updated hospital list
        echo "<script>window.location.href = '?id=" . $doctor_id . "';</script>";
        exit;
    } else {
        $error_msg = "Please select at least one hospital.";
    }
}

// Validate doctor_id parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || empty($_GET['id'])) {
    die("Invalid doctor ID provided.");
}

$doctor_id = (int)$_GET['id'];
$get_city_id = mysqli_query($con,"SELECT city_id FROM doctors where doctor_id = $doctor_id");

if (!$get_city_id) {
    die("Error fetching doctor information: " . mysqli_error($con));
}

if (mysqli_num_rows($get_city_id) == 0) {
    die("Doctor not found.");
}

$city_id = mysqli_fetch_assoc($get_city_id)['city_id'];
$get_already_assign_hosp = mysqli_query($con,"SELECT hospital_id FROM doctor_in_hospital where doctor_id = $doctor_id");
$already_assign_hosp = [];
while($row = mysqli_fetch_assoc($get_already_assign_hosp)) {
    $already_assign_hosp[] = $row['hospital_id'];
}

// Get available hospitals (not already assigned)
if (!empty($already_assign_hosp)) {
    $hosp_ids = implode(',', $already_assign_hosp);
     $get_hosp = mysqli_query($con,"SELECT * FROM hospitals where hospital_id not in ($hosp_ids) and city_id = $city_id order by hospital_name");
} else {
    $get_hosp = mysqli_query($con,"SELECT * FROM hospitals where city_id = $city_id order by hospital_name");
}
?>

<style>
    .assign-container {
        max-width: 800px;
        margin: 30px auto;
        padding: 0 20px;
    }

    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .page-header h4 {
        color: #2c3e50;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hospital-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        padding: 30px;
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .hospital-card:hover {
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .hospital-item {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .hospital-item:hover {
        background: #e3f2fd;
        border-color: #4facfe;
        transform: translateX(5px);
    }

    .hospital-item.selected {
        background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
        border-color: #4facfe;
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.2);
    }

    .custom-checkbox {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .custom-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        border: 2px solid #4facfe;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .custom-checkbox input[type="checkbox"]:checked {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        border-color: #4facfe;
    }

    .custom-checkbox label {
        font-size: 1.1rem;
        font-weight: 500;
        color: #2c3e50;
        cursor: pointer;
        margin: 0;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .hospital-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .btn-assign {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-assign:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
    }

    .btn-assign:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border: none;
        font-weight: 500;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
    }

    .no-hospitals {
        text-align: center;
        padding: 40px;
        color: #6c757d;
        font-style: italic;
    }

    .no-hospitals i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }

    .select-all-container {
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e9ecef;
    }

    .select-all-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: #495057;
    }
</style>

<div class="content-wrapper">
   <!-- Container-fluid starts -->
   <div class="container-fluid">
      <div class="assign-container">
         <div class="page-header">
            <h4>
               <i class="fas fa-user-md me-3"></i>Assign Hospital to Doctor
            </h4>
            <p class="text-muted">Select hospitals to assign to this doctor</p>
         </div>

         <?php if (isset($success_msg)): ?>
            <div class="alert alert-success">
               <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
            </div>
         <?php endif; ?>

         <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger">
               <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_msg; ?>
            </div>
         <?php endif; ?>

         <div class="hospital-card">
            <form method="POST" id="assignForm">
               <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
               
               <?php if (mysqli_num_rows($get_hosp) > 0): ?>
                  <div class="select-all-container">
                     <div class="select-all-checkbox">
                        <input type="checkbox" id="selectAll">
                        <label for="selectAll">Select All Hospitals</label>
                     </div>
                  </div>

                  <?php while($rs = mysqli_fetch_assoc($get_hosp)) { ?>
                     <div class="hospital-item">
                        <div class="custom-checkbox">
                           <input class="form-check-input hospital-checkbox" name="hospitals[]" type="checkbox" value="<?php echo $rs['hospital_id']; ?>" id="hospital_<?php echo $rs['hospital_id']; ?>"  >
                           <label class="form-check-label" for="hospital_<?php echo $rs['hospital_id']; ?>">
                              <div class="hospital-icon">
                                 <i class="fas fa-hospital"></i>
                              </div>
                              <div>
                                 <div class="hospital-name"><?php echo htmlspecialchars($rs['hospital_name']); ?></div>
                                 <small class="text-muted">Hospital ID: <?php echo $rs['hospital_id']; ?></small>
                              </div>
                           </label>
                        </div>
                     </div>
                  <?php } ?>

                  <div class="text-center">
                     <button type="submit" class="btn-assign" id="assignBtn" disabled>
                        <i class="fas fa-check-circle"></i>
                        Assign Selected Hospitals
                     </button>
                  </div>
               <?php else: ?>
                  <div class="no-hospitals">
                     <i class="fas fa-hospital"></i>
                     <h5>No Available Hospitals</h5>
                     <p>All hospitals in this city are already assigned to this doctor.</p>
                  </div>
               <?php endif; ?>
            </form>
         </div>
      </div>
   </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const hospitalCheckboxes = document.querySelectorAll('.hospital-checkbox');
    const assignBtn = document.getElementById('assignBtn');
    const hospitalItems = document.querySelectorAll('.hospital-item');

    // Select All functionality
    selectAllCheckbox.addEventListener('change', function() {
        hospitalCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            updateHospitalItem(checkbox);
        });
        updateAssignButton();
    });

    // Individual checkbox functionality
    hospitalCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateHospitalItem(this);
            updateSelectAllCheckbox();
            updateAssignButton();
        });
    });

    // Click on hospital item to toggle checkbox
    hospitalItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.hospital-checkbox');
                checkbox.checked = !checkbox.checked;
                updateHospitalItem(checkbox);
                updateSelectAllCheckbox();
                updateAssignButton();
            }
        });
    });

    function updateHospitalItem(checkbox) {
        const hospitalItem = checkbox.closest('.hospital-item');
        if (checkbox.checked) {
            hospitalItem.classList.add('selected');
        } else {
            hospitalItem.classList.remove('selected');
        }
    }

    function updateSelectAllCheckbox() {
        const checkedCount = document.querySelectorAll('.hospital-checkbox:checked').length;
        selectAllCheckbox.checked = checkedCount === hospitalCheckboxes.length && hospitalCheckboxes.length > 0;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < hospitalCheckboxes.length;
    }

    function updateAssignButton() {
        const checkedCount = document.querySelectorAll('.hospital-checkbox:checked').length;
        assignBtn.disabled = checkedCount === 0;
    }

    // Form submission
    document.getElementById('assignForm').addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.hospital-checkbox:checked').length;
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Please select at least one hospital to assign.');
        }
    });
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>