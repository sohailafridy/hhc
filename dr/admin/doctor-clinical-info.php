<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php
// ============================================
// VALIDATE doctor id
// ============================================
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || (int)$_GET['id'] <= 0) {
    die("Invalid doctor ID provided.");
}
$doctor_id = (int)$_GET['id'];

// ============================================
// HANDLE FORM SUBMISSION (includes se PEHLE)
// ============================================
$errors = [];
$saved_count = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clinical_data']) && is_array($_POST['clinical_data'])) {

    foreach ($_POST['clinical_data'] as $dih_id => $data) {
        $dih_id = (int)$dih_id;

        // Sirf is doctor ka apna ACTIVE hospital allow karein
        $own_q = mysqli_query($con, "SELECT doctor_in_hosp_id FROM doctor_in_hospital
                                     WHERE doctor_in_hosp_id = $dih_id AND doctor_id = $doctor_id AND inactive = 0");
        if (!$own_q || mysqli_num_rows($own_q) == 0) {
            continue;
        }

        $morning_opening_time = mysqli_real_escape_string($con, trim($data['morning_opening_time'] ?? ''));
        $morning_closing_time = mysqli_real_escape_string($con, trim($data['morning_closing_time'] ?? ''));
        $evening_opening_time = mysqli_real_escape_string($con, trim($data['evening_opening_time'] ?? ''));
        $evening_closing_time = mysqli_real_escape_string($con, trim($data['evening_closing_time'] ?? ''));

        $has_morning = ($morning_opening_time !== '' && $morning_closing_time !== '');
        $has_evening = ($evening_opening_time !== '' && $evening_closing_time !== '');

        // Dono shifts khali hon to skip
        if (!$has_morning && !$has_evening) {
            $errors[] = "Please fill at least one shift (Morning or Evening) for each hospital.";
            continue;
        }

        $shift = '';
        if ($has_morning) { $shift = 'Morning'; }
        if ($has_evening) { $shift = 'Evening'; }

        $season   = mysqli_real_escape_string($con, $data['season'] ?? '');
        $contact  = mysqli_real_escape_string($con, $data['contact'] ?? '');
        $days     = mysqli_real_escape_string($con, $data['days'] ?? '');
        $off_days = mysqli_real_escape_string($con, $data['off_days'] ?? '');
        $detail   = mysqli_real_escape_string($con, $data['detail'] ?? '');

        // Row pehle se hai (blank bhi ho sakti hai)?
        $check_result = mysqli_query($con, "SELECT clinical_info_id FROM clinical_info WHERE doctor_in_hosp_id = $dih_id");

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $ok = mysqli_query($con, "UPDATE clinical_info SET
                    morning_opening_time = '$morning_opening_time',
                    morning_closing_time = '$morning_closing_time',
                    evening_opening_time = '$evening_opening_time',
                    evening_closing_time = '$evening_closing_time',
                    season   = '$season',
                    contact  = '$contact',
                    days     = '$days',
                    off_days = '$off_days',
                    shift    = '$shift',
                    detail   = '$detail'
                    WHERE doctor_in_hosp_id = $dih_id");
        } else {
            $ok = mysqli_query($con, "INSERT INTO clinical_info
                    (doctor_in_hosp_id, morning_opening_time, morning_closing_time, evening_opening_time, evening_closing_time, season, contact, days, off_days, detail, shift)
                    VALUES ($dih_id, '$morning_opening_time', '$morning_closing_time', '$evening_opening_time', '$evening_closing_time', '$season', '$contact', '$days', '$off_days', '$detail', '$shift')");
        }

        if ($ok) {
            $saved_count++;
        } else {
            $errors[] = mysqli_error($con);
        }
    }

    if (empty($errors) && $saved_count > 0) {
        header("Location: " . BASE_URL . "admin/doctors/profile?id=" . $doctor_id);
        exit();
    }
    if ($saved_count > 0) {
        $success_msg = "Clinical information saved for $saved_count hospital(s).";
    }
}

// ============================================
// HOSPITALS jinki clinical info abhi tak NAHI bhari
// (row na ho, ya row ho lekin koi shift time na bhara ho)
// ============================================
$get_doc_in_hosp = mysqli_query($con, "SELECT dih.doctor_in_hosp_id, h.hospital_name
    FROM doctor_in_hospital dih
    LEFT JOIN hospitals h ON dih.hospital_id = h.hospital_id
    WHERE dih.inactive = 0
      AND dih.doctor_id = $doctor_id
      AND NOT EXISTS (
            SELECT 1 FROM clinical_info ci
            WHERE ci.doctor_in_hosp_id = dih.doctor_in_hosp_id
              AND (
                    (COALESCE(ci.morning_opening_time,'') <> '' AND COALESCE(ci.morning_closing_time,'') <> '')
                 OR (COALESCE(ci.evening_opening_time,'') <> '' AND COALESCE(ci.evening_closing_time,'') <> '')
              )
      )");

if (!$get_doc_in_hosp) {
    die("Error fetching hospital information: " . mysqli_error($con));
}

$working_days = [
    "Monday to Friday", "Monday to Saturday", "Monday to Sunday", "Tuesday to Sunday",
    "Friday to Sunday", "Saturday & Sunday", "Sunday Only", "24/7"
];
$off_days_list = [
    "None", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday",
    "Saturday", "Sunday", "Saturday & Sunday", "Friday & Saturday"
];
?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

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
               <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_msg); ?>
            </div>
         <?php endif; ?>

         <?php foreach (array_unique($errors) as $err): ?>
            <div class="alert alert-danger">
               <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($err); ?>
            </div>
         <?php endforeach; ?>

         <div class="clinical-card">
            <form method="POST" id="clinicalForm">
               <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">

               <?php if (mysqli_num_rows($get_doc_in_hosp) > 0): ?>
                  <?php
                  $hospital_count = 0;
                  while ($rs = mysqli_fetch_assoc($get_doc_in_hosp)):
                      $hospital_count++;
                      $fname = "clinical_data[" . (int)$rs['doctor_in_hosp_id'] . "]";
                  ?>

                     <?php if ($hospital_count > 1): ?>
                        <div class="section-divider"></div>
                     <?php endif; ?>

                     <div class="hospital-header">
                        <h5>
                           <div class="hospital-icon">
                              <i class="fas fa-hospital"></i>
                           </div>
                           <?php
                                if ($rs['hospital_name'] != '') {
                                    echo htmlspecialchars($rs['hospital_name']);
                                } else {
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
                           <label class="form-label"><i class="fas fa-clock me-2"></i>Opening Time</label>
                           <input type="text" class="form-input timepicker"
                                  name="<?php echo $fname; ?>[morning_opening_time]"
                                  placeholder="Select Opening Time">
                        </div>

                        <div class="form-group">
                           <label class="form-label"><i class="fas fa-clock me-2"></i>Closing Time</label>
                           <input type="text" class="form-input timepicker"
                                  name="<?php echo $fname; ?>[morning_closing_time]"
                                  placeholder="Select Closing Time">
                        </div>

                        <!-- second shift -->
                        <h4 class="section-subtitle mb-3 mt-4">
                           <i class="fas fa-moon me-2"></i>Evening Shift <br>
                           <i class="empty">Leave empty if not have</i>
                        </h4>

                        <div class="form-group">
                           <label class="form-label"><i class="fas fa-clock me-2"></i>Opening Time</label>
                           <input type="text" class="form-input timepicker"
                                  name="<?php echo $fname; ?>[evening_opening_time]"
                                  placeholder="Select Opening Time">
                        </div>

                        <div class="form-group">
                           <label class="form-label"><i class="fas fa-clock me-2"></i>Closing Time</label>
                           <input type="text" class="form-input timepicker"
                                  name="<?php echo $fname; ?>[evening_closing_time]"
                                  placeholder="Select Closing Time">
                        </div>

                        <!-- season -->
                        <div class="form-group">
                           <label class="form-label"><i class="fas fa-calendar-alt me-2"></i>Season</label>
                           <select class="form-input" name="<?php echo $fname; ?>[season]">
                              <option value="">-- Select Season --</option>
                              <option value="Summer">Summer</option>
                              <option value="Winter">Winter</option>
                           </select>
                        </div>

                        <div class="form-group">
                           <label class="form-label"><i class="fas fa-phone me-2"></i>Contact</label>
                           <input type="text" class="form-input"
                                  name="<?php echo $fname; ?>[contact]"
                                  placeholder="e.g., 0300-1234567">
                        </div>

                        <div class="form-group">
                           <label class="form-label"><i class="fas fa-calendar-week me-2"></i>Working Days</label>
                           <select class="form-input" name="<?php echo $fname; ?>[days]">
                              <option value="">-- Select Working Days --</option>
                              <?php foreach ($working_days as $day): ?>
                                 <option value="<?php echo htmlspecialchars($day); ?>"><?php echo htmlspecialchars($day); ?></option>
                              <?php endforeach; ?>
                           </select>
                        </div>

                        <div class="form-group">
                           <label class="form-label"><i class="fas fa-calendar-times me-2"></i>Off Days</label>
                           <select class="form-input" name="<?php echo $fname; ?>[off_days]">
                              <option value="">-- Select Off Days --</option>
                              <?php foreach ($off_days_list as $day): ?>
                                 <option value="<?php echo htmlspecialchars($day); ?>"><?php echo htmlspecialchars($day); ?></option>
                              <?php endforeach; ?>
                           </select>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                           <label class="form-label"><i class="fas fa-info-circle me-2"></i>Detail</label>
                           <textarea class="form-input"
                                     name="<?php echo $fname; ?>[detail]"
                                     rows="4"
                                     placeholder="Enter additional details about clinical information..."></textarea>
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
                     <h5>No Hospitals Available</h5>
                     <p>Either this doctor has no active hospital, or clinical information is already added for all hospitals.</p>
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
    const phoneInputs = document.querySelectorAll('input[placeholder*="0300"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^\d-]/g, '');
        });
    });

    const form = document.getElementById('clinicalForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[type="text"]');
            let hasData = false;
            inputs.forEach(input => {
                if (input.value.trim() !== '') { hasData = true; }
            });
            if (!hasData) {
                e.preventDefault();
                alert('Please fill in at least one field before saving.');
            }
        });
    }
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