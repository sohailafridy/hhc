<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
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
    $clinic=0;
    if (!empty($hospitals)) {
        foreach ($hospitals as $hospital_id) {
            $hospital_id = mysqli_real_escape_string($con, $hospital_id);

            if($hospital_id==0){$clinic=1;}



            $insert_query = "INSERT INTO doctor_in_hospital (doctor_id, hospital_id, if_clinic) 
                           VALUES ('$doctor_id', '$hospital_id', '$clinic')";
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
$get_already_assign_hosp = mysqli_query($con,"SELECT if_clinic,hospital_id FROM doctor_in_hospital where doctor_id = $doctor_id");
$already_assign_hosp = [];

$clinic_check =0;


while($row = mysqli_fetch_assoc($get_already_assign_hosp)) {
    $already_assign_hosp[] = $row['hospital_id'];
    if ($row['if_clinic'] == 1) {
        $clinic_check =1;
    }
}
// Get available hospitals (not already assigned)
if (!empty($already_assign_hosp)) {
    $hosp_ids = implode(',', $already_assign_hosp);
     $get_hosp = mysqli_query($con,"SELECT * FROM hospitals where hospital_id not in ($hosp_ids) and city_id = $city_id order by hospital_name");
} else {
    $get_hosp = mysqli_query($con,"SELECT * FROM hospitals where city_id = $city_id order by hospital_name");
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-assign-hospital-admin.css">

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

                  <?php
                    if ($clinic_check==0) { ?>
                        <div class="hospital-item">
                            <div class="custom-checkbox">
                               <input class="form-check-input hospital-checkbox" name="hospitals[]" type="checkbox" value="0" id="personal_clinic">
                               <label class="form-check-label" for="personal_clinic">
                                  <div class="hospital-icon">
                                     <i class="fas fa-hospital"></i>
                                  </div>
                                  <div>
                                     <div class="hospital-name">Personal Clinic</div>
                                     <small class="text-muted">
                                         -
                                     </small>
                                  </div>
                               </label>
                            </div>
                        </div>
                    <?php }
                  ?>
                  


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
