<?php include '../config.php'; ?>
<?php include '../check_auth.php'; ?>
<?php
// ============================================
// VALIDATE doctor id
// ============================================
$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['doctor_id'] ?? 0);
if ($doctor_id <= 0) {
    die("Invalid doctor ID provided.");
}

// ============================================
// HANDLE FORM SUBMISSION (includes se PEHLE)
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hospitals = isset($_POST['hospitals']) && is_array($_POST['hospitals']) ? $_POST['hospitals'] : [];

    if (!empty($hospitals)) {
        $stmt = mysqli_prepare($con, "INSERT INTO doctor_in_hospital (doctor_id, hospital_id, if_clinic) VALUES (?, ?, ?)");

        foreach ($hospitals as $hospital_id) {
            $hospital_id = (int) $hospital_id;
            // Personal Clinic (0) ke liye if_clinic = 1, warna 0
            $clinic = ($hospital_id === 0) ? 1 : 0;

            mysqli_stmt_bind_param($stmt, "iii", $doctor_id, $hospital_id, $clinic);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);

        $_SESSION['success_msg'] = "Hospitals assigned to doctor successfully!";

        // Filters ke saath wapas usi page par
        $back = "?id=" . $doctor_id;
        if (isset($_POST['f_city_id']) && $_POST['f_city_id'] !== '') {
            $back .= "&city_id=" . (int)$_POST['f_city_id'];
        }
        if (!empty($_POST['f_search'])) {
            $back .= "&search=" . urlencode($_POST['f_search']);
        }
        header("Location: " . $back);
        exit();
    } else {
        $error_msg = "Please select at least one hospital.";
    }
}

if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// ============================================
// DOCTOR INFO
// ============================================
$get_city_id = mysqli_query($con, "SELECT city_id FROM doctors WHERE doctor_id = $doctor_id");
if (!$get_city_id) {
    die("Error fetching doctor information: " . mysqli_error($con));
}
if (mysqli_num_rows($get_city_id) == 0) {
    die("Doctor not found.");
}
$doctor_city_id = (int)mysqli_fetch_assoc($get_city_id)['city_id'];

// ============================================
// ALREADY ASSIGNED
// ============================================
$get_already_assign_hosp = mysqli_query($con, "SELECT if_clinic, hospital_id FROM doctor_in_hospital WHERE doctor_id = $doctor_id");
$already_assign_hosp = [];
$clinic_check = 0;

while ($row = mysqli_fetch_assoc($get_already_assign_hosp)) {
    $already_assign_hosp[] = (int)$row['hospital_id'];
    if ($row['if_clinic'] == 1) {
        $clinic_check = 1;
    }
}

// ============================================
// FILTERS (City + Hospital name search)
// Default: doctor ka apna city. "All Cities" = 0
// ============================================
$city_filter = isset($_GET['city_id']) ? (int)$_GET['city_id'] : $doctor_city_id;
$search_raw  = trim($_GET['search'] ?? '');
$search_sql  = mysqli_real_escape_string($con, $search_raw);

// Cities list (table: cities, columns: city_id, city_name -> apne table ke mutabiq change karein)
$cities = [];
try {
    $city_q = mysqli_query($con, "SELECT city_id, city_name FROM cities ORDER BY city_name");
    if ($city_q) {
        while ($c = mysqli_fetch_assoc($city_q)) {
            $cities[] = $c;
        }
    }
} catch (Throwable $e) {
    $cities = [];
}

$where = "1";
if ($city_filter > 0) {
    $where .= " AND city_id = $city_filter";
}
if ($search_raw !== '') {
    $where .= " AND hospital_name LIKE '%$search_sql%'";
}
if (!empty($already_assign_hosp)) {
    $where .= " AND hospital_id NOT IN (" . implode(',', $already_assign_hosp) . ")";
}

$get_hosp = mysqli_query($con, "SELECT * FROM hospitals WHERE $where ORDER BY hospital_name");
if (!$get_hosp) {
    die("Error fetching hospitals: " . mysqli_error($con));
}
$hosp_count = mysqli_num_rows($get_hosp);

// Personal Clinic sirf tab dikhayen jab assign na ho, aur search se match kare
$show_clinic = ($clinic_check == 0) && ($search_raw === '' || stripos('personal clinic', $search_raw) !== false);
?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-assign-hospital-admin.css?v=<?= time() ?>">


<div class="content-wrapper">
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
               <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_msg); ?>
            </div>
         <?php endif; ?>

         <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger">
               <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_msg); ?>
            </div>
         <?php endif; ?>

         <div class="hospital-card">

            <!-- ========== FILTER BAR (GET) ========== -->
            <form method="GET" class="hf-bar">
               <input type="hidden" name="id" value="<?php echo $doctor_id; ?>">
               <div class="hf-grid">

                  <div class="hf-field">
                     <label class="hf-label" for="hf_city"><i class="fas fa-city me-1"></i> City</label>
                     <select id="hf_city" name="city_id" class="hf-control" onchange="this.form.submit()">
                        <option value="0" <?php echo ($city_filter == 0) ? 'selected' : ''; ?>>All Cities</option>
                        <?php foreach ($cities as $c): ?>
                           <option value="<?php echo (int)$c['city_id']; ?>" <?php echo ($city_filter == (int)$c['city_id']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($c['city_name']); ?>
                           </option>
                        <?php endforeach; ?>
                     </select>
                  </div>

                  <div class="hf-field">
                     <label class="hf-label" for="hf_search"><i class="fas fa-search me-1"></i> Hospital Name</label>
                     <input type="text" id="hf_search" name="search" class="hf-control"
                            value="<?php echo htmlspecialchars($search_raw); ?>"
                            placeholder="Search hospital by name...">
                  </div>

                  <div class="hf-actions">
                     <button type="submit" class="hf-btn hf-btn-primary">
                        <i class="fas fa-search"></i> Search
                     </button>
                     <a href="?id=<?php echo $doctor_id; ?>" class="hf-btn hf-btn-reset">
                        <i class="fas fa-undo"></i> Reset
                     </a>
                  </div>

               </div>
            </form>

            <div class="result-info">
               <?php echo $hosp_count; ?> hospital(s) found
               <?php if ($search_raw !== ''): ?>
                  for "<strong><?php echo htmlspecialchars($search_raw); ?></strong>"
               <?php endif; ?>
            </div>

            <!-- ========== ASSIGN FORM (POST) ========== -->
            <form method="POST" id="assignForm">
               <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
               <input type="hidden" name="f_city_id" value="<?php echo $city_filter; ?>">
               <input type="hidden" name="f_search" value="<?php echo htmlspecialchars($search_raw); ?>">

               <?php if ($hosp_count > 0 || $show_clinic): ?>
                  <div class="select-all-container">
                     <div class="select-all-checkbox">
                        <input type="checkbox" id="selectAll">
                        <label for="selectAll">Select All Hospitals</label>
                     </div>
                  </div>

                  <?php if ($show_clinic): ?>
                     <div class="hospital-item">
                        <div class="custom-checkbox">
                           <input class="form-check-input hospital-checkbox" name="hospitals[]" type="checkbox" value="0" id="personal_clinic">
                           <label class="form-check-label" for="personal_clinic">
                              <div class="hospital-icon">
                                 <i class="fas fa-hospital"></i>
                              </div>
                              <div>
                                 <div class="hospital-name">Personal Clinic</div>
                                 <small class="text-muted">-</small>
                              </div>
                           </label>
                        </div>
                     </div>
                  <?php endif; ?>

                  <?php while ($rs = mysqli_fetch_assoc($get_hosp)): ?>
                     <div class="hospital-item">
                        <div class="custom-checkbox">
                           <input class="form-check-input hospital-checkbox" name="hospitals[]" type="checkbox"
                                  value="<?php echo (int)$rs['hospital_id']; ?>" id="hospital_<?php echo (int)$rs['hospital_id']; ?>">
                           <label class="form-check-label" for="hospital_<?php echo (int)$rs['hospital_id']; ?>">
                              <div class="hospital-icon">
                                 <i class="fas fa-hospital"></i>
                              </div>
                              <div>
                                 <div class="hospital-name"><?php echo htmlspecialchars($rs['hospital_name']); ?></div>
                                 <small class="text-muted">Hospital ID: <?php echo (int)$rs['hospital_id']; ?></small>
                              </div>
                           </label>
                        </div>
                     </div>
                  <?php endwhile; ?>

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
                     <p>
                        <?php if ($search_raw !== ''): ?>
                           No hospital matches your search. Try another name or select "All Cities".
                        <?php else: ?>
                           All hospitals for this filter are already assigned to this doctor.
                        <?php endif; ?>
                     </p>
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

    if (!selectAllCheckbox || !assignBtn) { return; }

    // Select All
    selectAllCheckbox.addEventListener('change', function() {
        hospitalCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            updateHospitalItem(checkbox);
        });
        updateAssignButton();
    });

    // Individual checkbox
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
            if (e.target.type !== 'checkbox' && e.target.tagName !== 'LABEL' && !e.target.closest('label')) {
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
        hospitalItem.classList.toggle('selected', checkbox.checked);
    }

    function updateSelectAllCheckbox() {
        const checkedCount = document.querySelectorAll('.hospital-checkbox:checked').length;
        selectAllCheckbox.checked = checkedCount === hospitalCheckboxes.length && hospitalCheckboxes.length > 0;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < hospitalCheckboxes.length;
    }

    function updateAssignButton() {
        assignBtn.disabled = document.querySelectorAll('.hospital-checkbox:checked').length === 0;
    }

    document.getElementById('assignForm').addEventListener('submit', function(e) {
        if (document.querySelectorAll('.hospital-checkbox:checked').length === 0) {
            e.preventDefault();
            alert('Please select at least one hospital to assign.');
        }
    });
});
</script>

<?php include BASE_PATH.'/admin/inc/footer.php';?>