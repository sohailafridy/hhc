<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<?php

$get_doc_in_hosp_q = mysqli_query($con, "SELECT `doctor_in_hosp_id` FROM `doctor_in_hospital` WHERE `doctor_id`='".$_GET['id']."' AND inactive=0 ");
$doctor_in_hosp_id = [];    
while($row = mysqli_fetch_assoc($get_doc_in_hosp_q)){
        $doctor_in_hosp_id[] = $row['doctor_in_hosp_id'];
    }

    $doctor_in_hosp_ids = implode(',', $doctor_in_hosp_id);
    $get_clinical_info = mysqli_query($con, "SELECT * FROM clinical_info WHERE doctor_in_hosp_id IN ($doctor_in_hosp_ids)");
    $clinical_info = [];
    while($row = mysqli_fetch_assoc($get_clinical_info)){
        $clinical_info[] = $row['doctor_in_hosp_id'];
    }

    $clinical_info_ids = implode(',', $clinical_info);
    if($clinical_info_ids =='' || $clinical_info_ids ==0){
        $clinical_info_ids = 0;
    }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = mysqli_real_escape_string($con, $_POST['doctor_id']);
    
    if (isset($_POST['clinical_data'])) {
        foreach ($_POST['clinical_data'] as $doctor_in_hosp_id => $data) {
            $doctor_in_hosp_id = mysqli_real_escape_string($con, $doctor_in_hosp_id);
            $morning_opening_time = mysqli_real_escape_string($con, $data['morning_opening_time']);
            $morning_closing_time = mysqli_real_escape_string($con, $data['morning_closing_time']);
            $evening_opening_time = mysqli_real_escape_string($con, $data['evening_opening_time']);
            $evening_closing_time = mysqli_real_escape_string($con, $data['evening_closing_time']);
            $shift = '';



            // Check if both shifts are empty
            if ((empty($morning_opening_time) || empty($morning_closing_time)) && 
                (empty($evening_opening_time) || empty($evening_closing_time))) {
                $error_msg = "Please fill at least one shift (Morning or Evening) for each hospital.";
                continue; // Skip this iteration
            }
            
            if (!empty($morning_opening_time) && !empty($morning_closing_time)) {
                $shift = 'Morning';
            }
            if (!empty($evening_opening_time) && !empty($evening_closing_time)) {
                $shift = 'Evening';
            }





            // $shift = mysqli_real_escape_string($con, $data['shift']);
            $season = mysqli_real_escape_string($con, $data['season']);
            $contact = mysqli_real_escape_string($con, $data['contact']);
            $days = mysqli_real_escape_string($con, $data['days']);
            $off_days = mysqli_real_escape_string($con, $data['off_days']);
            $detail = mysqli_real_escape_string($con, $data['detail']);
            
            // Check if clinical info already exists
            $check_query = "SELECT clinical_info_id FROM clinical_info WHERE doctor_in_hosp_id = $doctor_in_hosp_id";
            $check_result = mysqli_query($con, $check_query);
            
            if (mysqli_num_rows($check_result) > 0) {
                // Update existing record
                $update_query = "UPDATE clinical_info SET 
                    morning_opening_time = '$morning_opening_time',
                    morning_closing_time = '$morning_closing_time',
                    evening_opening_time = '$evening_opening_time',
                    evening_closing_time = '$evening_closing_time', 
                    season = '$season',
                    contact = '$contact',
                    days = '$days',
                    off_days = '$off_days',
                    shift = '$shift',
                    detail = '$detail'
                    WHERE doctor_in_hosp_id = $doctor_in_hosp_id";
                mysqli_query($con, $update_query);
            } else {
                // Insert new record
                $insert_query = "INSERT INTO clinical_info 
                    (doctor_in_hosp_id, morning_opening_time, morning_closing_time, evening_opening_time, evening_closing_time, season, contact, days, off_days, detail,shift) 
                    VALUES ('$doctor_in_hosp_id', '$morning_opening_time', '$morning_closing_time', '$evening_opening_time', '$evening_closing_time', '$season', '$contact', '$days', '$off_days', '$detail', '$shift')";
                mysqli_query($con, $insert_query);
                header("Location: " . BASE_URL . "profile?id=" . $_GET['id']);
            }
        }
        $success_msg = "Clinical information updated successfully!";
    }
}

// Validate doctor_id parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || empty($_GET['id'])) {
    die("Invalid doctor ID provided.");
}

$doctor_id = (int)$_GET['id'];

$get_doc_in_hosp = mysqli_query($con, "SELECT dih.doctor_in_hosp_id, h.hospital_name 
    FROM doctor_in_hospital dih 
    LEFT JOIN hospitals h ON dih.hospital_id = h.hospital_id 
    WHERE  dih.inactive=0 AND dih.doctor_id = $doctor_id and dih.doctor_in_hosp_id NOT IN ($clinical_info_ids)");

if (!$get_doc_in_hosp) {
    die("Error fetching hospital information: " . mysqli_error($con));
}

// Get existing clinical information
// $clinical_info = [];
// $clinical_result = mysqli_query($con, "SELECT * FROM clinical_info WHERE doctor_in_hosp_id IN (
//     SELECT doctor_in_hosp_id FROM doctor_in_hospital WHERE doctor_id = $doctor_id
// )");
// while ($row = mysqli_fetch_assoc($clinical_result)) {
//     $clinical_info[$row['doctor_in_hosp_id']] = $row;
// }
?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-clinical-info-admin.css">

<div class="content-wrapper">
   <div class="container-fluid">
      <div class="clinical-container">
         <div class="page-header">
            <h4>
               <i class="fas fa-user-md me-3"></i>Adding Clinical Information
            </h4>
            <p class="text-muted">Manage clinical details for each hospital</p>
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

         <div class="clinical-card">
            <form method="POST" id="clinicalForm">
               <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
               
               <?php if (mysqli_num_rows($get_doc_in_hosp) > 0): ?>
                  <?php $hospital_count = 0; while($rs = mysqli_fetch_assoc($get_doc_in_hosp)): $hospital_count++; ?>
                     
                     <?php if ($hospital_count > 1): ?>
                        <div class="section-divider"></div>
                     <?php endif; ?>

                     <div class="hospital-header">
                        <h5>
                           <div class="hospital-icon">
                              <i class="fas fa-hospital"></i>
                           </div>
                           <?php 
                                if($rs['hospital_name'] !='')
                                    {
                                        echo htmlspecialchars($rs['hospital_name']);
                                        
                                    }else{
                                       echo "Personal Clinic";  
                                    }
                           ?>
                        </h5>
                     </div>

                     <div class="form-grid">

                                     <!-- first shift -->
                                     <h4 class="section-subtitle mb-3">
                                        <i class="fas fa-sun me-2"></i>Morning Shift <br>
                                        <i class="empty">Leave empty if not have</i>
                                     </h4>
                        <div class="form-group">
                           <label class="form-label">
                              <i class="fas fa-clock me-2"></i>Opening Time
                           </label>
                           <input type="text"
                               class="form-input timepicker"
                               name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][morning_opening_time]"
                               value="<?php echo isset($clinical_info[$rs['doctor_in_hosp_id']]) ? htmlspecialchars($clinical_info[$rs['doctor_in_hosp_id']]['opening_time']) : ''; ?>"
                               placeholder="Select Opening Time">
                        </div>

                        <div class="form-group">
                           <label class="form-label">
                              <i class="fas fa-clock me-2"></i>Closing Time
                           </label>
                           <input type="text"
                           class="form-input timepicker"
                           name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][morning_closing_time]"
                           value="<?php echo isset($clinical_info[$rs['doctor_in_hosp_id']]) ? htmlspecialchars($clinical_info[$rs['doctor_in_hosp_id']]['closing_time']) : ''; ?>"
                           placeholder="Select Closing Time">
                        </div>
                        <!-- first shift end -->


                        <!-- second shift -->
                        <h4 class="section-subtitle mb-3 mt-4">
                            <i class="fas fa-moon me-2"></i>Evening Shift <br>
                            <i class="empty">Leave empty if not have</i>
                        </h4>
                         
                            <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-clock me-2"></i>Opening Time
                            </label>
                            <input type="text"
                               class="form-input timepicker"
                               name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][evening_opening_time]"
                               value="<?php echo isset($clinical_info[$rs['doctor_in_hosp_id']]) ? htmlspecialchars($clinical_info[$rs['doctor_in_hosp_id']]['second_opening_time']) : ''; ?>"
                               placeholder="Select Opening Time">
                            </div>

                            <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-clock me-2"></i>Closing Time
                            </label>
                            <input type="text"
                               class="form-input timepicker"
                               name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][evening_closing_time]"
                               value="<?php echo isset($clinical_info[$rs['doctor_in_hosp_id']]) ? htmlspecialchars($clinical_info[$rs['doctor_in_hosp_id']]['second_closing_time']) : ''; ?>"
                               placeholder="Select Closing Time">
                            </div>

                            
                        
                        <!-- second shift end -->



                        <div class="form-group">
                          <!--  <input type="text" 
                                  class="form-input" 
                                  name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][season]" 
                                  value="<?php echo isset($clinical_info[$rs['doctor_in_hosp_id']]) ? htmlspecialchars($clinical_info[$rs['doctor_in_hosp_id']]['season']) : ''; ?>"
                                  placeholder="e.g., Summer, Winter"> -->


                                  <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt me-2"></i>Season
                                    </label>

                                    <select class="form-input" name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][season]">
                                        <option value="">-- Select Season --</option>
                                        <option value="Summer" <?php echo (isset($clinical_info[$rs['doctor_in_hosp_id']]) && $clinical_info[$rs['doctor_in_hosp_id']]['season'] == 'Summer') ? 'selected' : ''; ?>>
                                            Summer
                                        </option>
                                        <option value="Winter" <?php echo (isset($clinical_info[$rs['doctor_in_hosp_id']]) && $clinical_info[$rs['doctor_in_hosp_id']]['season'] == 'Winter') ? 'selected' : ''; ?>>
                                            Winter
                                        </option>
                                    </select>
                                </div>




                        </div>

                        <div class="form-group">
                           <label class="form-label">
                              <i class="fas fa-phone me-2"></i>Contact
                           </label>
                           <input type="text" 
                                  class="form-input" 
                                  name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][contact]" 
                                  value="<?php echo isset($clinical_info[$rs['doctor_in_hosp_id']]) ? htmlspecialchars($clinical_info[$rs['doctor_in_hosp_id']]['contact']) : ''; ?>"
                                  placeholder="e.g., 0300-1234567">
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-week me-2"></i>Working Days
                            </label>

                            <select class="form-input" name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][days]">
                                <option value="">-- Select Working Days --</option>

                                <?php
                                $working_days = [
                                    "Monday to Friday",
                                    "Monday to Saturday",
                                    "Monday to Sunday",
                                    "Tuesday to Sunday",
                                    "Friday to Sunday",
                                    "Saturday & Sunday",
                                    "Sunday Only",
                                    "24/7"
                                ];

                                $selected = isset($clinical_info[$rs['doctor_in_hosp_id']])
                                    ? $clinical_info[$rs['doctor_in_hosp_id']]['days']
                                    : '';

                                foreach ($working_days as $day) {
                                    echo '<option value="'.$day.'" '.($selected == $day ? 'selected' : '').'>'.$day.'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-times me-2"></i>Off Days
                            </label>

                            <select class="form-input" name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][off_days]">
                                <option value="">-- Select Off Days --</option>

                                <?php
                                $off_days = [
                                    "None",
                                    "Monday",
                                    "Tuesday",
                                    "Wednesday",
                                    "Thursday",
                                    "Friday",
                                    "Saturday",
                                    "Sunday",
                                    "Saturday & Sunday",
                                    "Friday & Saturday"
                                ];

                                $selected = isset($clinical_info[$rs['doctor_in_hosp_id']])
                                    ? $clinical_info[$rs['doctor_in_hosp_id']]['off_days']
                                    : '';

                                foreach ($off_days as $day) {
                                    echo '<option value="'.$day.'" '.($selected == $day ? 'selected' : '').'>'.$day.'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                           <label class="form-label">
                              <i class="fas fa-info-circle me-2"></i>Detail
                           </label>
                           <textarea class="form-input" 
                                     name="clinical_data[<?php echo $rs['doctor_in_hosp_id']; ?>][detail]" 
                                     rows="4" 
                                     placeholder="Enter additional details about clinical information..."><?php echo isset($clinical_info[$rs['doctor_in_hosp_id']]) ? htmlspecialchars($clinical_info[$rs['doctor_in_hosp_id']]['detail']) : ''; ?></textarea>
                        </div>
                     </div>
                  <?php endwhile; ?>

                  <div class="text-center">
                     <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i>
                        Save Clinical Information
                     </button>
                  </div>
               <?php else: ?>
                  <div class="no-hospitals">
                     <i class="fas fa-hospital"></i>
                     <h5>No Hospitals Assigned</h5>
                     <p>This doctor has not been assigned to any hospitals yet.</p>
                     <a href="<?php echo BASE_URL; ?>admin/doctors/assign-hospitals?id=<?php echo $doctor_id; ?>" class="btn-save">
                        <i class="fas fa-plus"></i>
                        Assign Hospitals First
                     </a>
                  </div>
               <?php endif; ?>
            </form>
         </div>
      </div>
   </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add input validation and formatting
    const timeInputs = document.querySelectorAll('input[placeholder*="AM"], input[placeholder*="PM"]');
    timeInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Auto-format time input
            let value = this.value.replace(/[^\d:APM ]/g, '');
            this.value = value;
        });
    });

    const phoneInputs = document.querySelectorAll('input[placeholder*="0300"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Auto-format phone input
            let value = this.value.replace(/[^\d-]/g, '');
            this.value = value;
        });
    });

    // Form submission confirmation
    document.getElementById('clinicalForm').addEventListener('submit', function(e) {
        const inputs = this.querySelectorAll('input[type="text"]');
        let hasData = false;
        
        inputs.forEach(input => {
            if (input.value.trim() !== '') {
                hasData = true;
            }
        });
        
        if (!hasData) {
            e.preventDefault();
            alert('Please fill in at least one field before saving.');
        }
    });
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>
<script>
document.addEventListener("DOMContentLoaded", function () {

    flatpickr(".timepicker", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K", // 09:30 AM
        time_24hr: false,
        minuteIncrement: 5
    });

});
</script>