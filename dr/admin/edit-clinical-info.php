<?php 
include '../config.php'; 
include BASE_PATH.'/admin/inc/header.php';

// Handle form submission
$update_message = '';
$show_post = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clinical_info_id'])) {
    // $show_post = true; // We'll show $_POST for debugging

    $doctor_id = mysqli_real_escape_string($con, $_POST['doctor_id']);
    $clinical_info_id = mysqli_real_escape_string($con, $_POST['clinical_info_id']);
    $morning_opening_time     = mysqli_real_escape_string($con, $_POST['morning_opening_time']);
    $morning_closing_time     = mysqli_real_escape_string($con, $_POST['morning_closing_time']);
    $evening_opening_time     = mysqli_real_escape_string($con, $_POST['evening_opening_time']);
    $evening_closing_time     = mysqli_real_escape_string($con, $_POST['evening_closing_time']);
    $days             = mysqli_real_escape_string($con, $_POST['days']);
    
    // Check if both shifts are empty
    // Debug: Show received values
    $update_message = '<div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Debug - Morning: "' . $morning_opening_time . '" - "' . $morning_closing_time . '" | Evening: "' . $evening_opening_time . '" - "' . $evening_closing_time . '"
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    
    if ((empty($morning_opening_time) || empty($morning_closing_time)) && 
        (empty($evening_opening_time) || empty($evening_closing_time))) {
        $update_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            Please fill at least one shift (Morning or Evening) completely.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } else {

        if ((!empty($morning_opening_time) || !empty($morning_closing_time)) && 
        (!empty($evening_opening_time) || !empty($evening_closing_time))) {
            $shift = 'both';
        }else{
           $shift            = mysqli_real_escape_string($con, $_POST['shift']); 
        }
        $off_days         = mysqli_real_escape_string($con, $_POST['off_days']);
        $contact          = mysqli_real_escape_string($con, $_POST['contact']);
        $detail           = mysqli_real_escape_string($con, $_POST['detail'] ?? '');

        $update_query = "UPDATE clinical_info SET 
            morning_opening_time = '$morning_opening_time',
            morning_closing_time = '$morning_closing_time',
            evening_opening_time = '$evening_opening_time',
            evening_closing_time = '$evening_closing_time',
            days         = '$days',
            shift        = '$shift',
            off_days     = '$off_days',
            contact      = '$contact',
            detail       = '$detail'
            WHERE clinical_info_id = '$clinical_info_id'";

        if (mysqli_query($con, $update_query)) {
            $update_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Clinical information updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            $update_message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> ' . mysqli_error($con) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }
}

// Fetch clinical information
$clinical_query = "SELECT * FROM clinical_info WHERE clinical_info_id = '" . mysqli_real_escape_string($con, $_GET['id']) . "'";
$clinical_result = mysqli_query($con, $clinical_query);
$clinical = mysqli_fetch_assoc($clinical_result);

if (!$clinical) {
    // Redirect or show error if no record found
    $clinical = []; // empty array to avoid errors
}
?>
<?php include BASE_PATH.'/admin/inc/header.php';?>
<!-- Navbar top-->
<?php include BASE_PATH.'/admin/inc/top.php';?>
<!-- Side-Nav-->
<?php include BASE_PATH.'/admin/inc/nav.php';?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/edit-clinical-info-admin.css">

<div class="content-wrapper">
    <!-- Container-fluid starts -->
    <div class="container-fluid">

        <!-- Show POST data for debugging (remove in production) -->
        <?php if ($show_post): ?>
        <div class="alert alert-info">
            <strong>POST Data Received:</strong><br>
            <pre><?php print_r($_POST); ?></pre>
        </div>
        <?php endif; ?>

        <!-- Update Success/Error Message -->
        <?php echo $update_message; ?>

        <!-- Clinic Information -->
        <div class="row">
            <div class="col-lg-12">
                <div class="info-card">
                    <div class="info-card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-map-marker-alt me-2"></i>Edit Clinic Information</h5>
                    </div>
                    <div class="info-card-body">
                        <?php if (!empty($clinical)): ?>
                        <div class="season-group mb-4">
                            <div class="season-group-header" style="background: linear-gradient(135deg, <?php echo $config['color']; ?> 0%, <?php echo $config['color']; ?>dd 100%);">
                                <h4 class="mb-0"></h4>
                            </div>
                            
                            <div class="season-group-body">
                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $_GET['id']; ?>" method="POST" class="row">
                                    <input type="hidden" name="clinical_info_id" value="<?php echo $clinical['clinical_info_id']; ?>">
                                    <input type="hidden" name="doctor_id" value="<?php if(isset($_REQUEST['doctor_id'])){echo $_REQUEST['doctor_id'];} ?>">

                                    <div class="col-lg-6 mb-3">
                                        <div class="clinical-record-card">
                                            <div class="clinical-record-body">

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <small class="text-muted">Shift</small>
                                                        <input class="form-control mb-2" type="text" name="shift" value="<?=$clinical['shift']?>">
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="clinical-info-item">
                                                            <i class="fas fa-clock text-primary"></i>
                                                            <div>
                                                                <small class="text-muted">Timing: Morning</small>
                                                                <input type="text" name="morning_opening_time" class="form-control mb-2"
                                                                       value="<?=$clinical['morning_opening_time']?>">
                                                                <input type="text" name="morning_closing_time" class="form-control"
                                                                       value="<?=$clinical['morning_closing_time']?>">
                                                            </div>
                                                        </div>

                                                        <div class="clinical-info-item mt-3">
                                                            <i class="fas fa-calendar-day text-success"></i>
                                                            <div>
                                                                <small class="text-muted">Working Days</small>
                                                                <input type="text" name="days" class="form-control"
                                                                       value="<?php echo htmlspecialchars($clinical['days'] ?? ''); ?>" 
                                                                       placeholder="e.g. Monday-Friday, Saturday">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="clinical-info-item">
                                                            <i class="fas fa-clock text-primary"></i>
                                                            <div>
                                                                <small class="text-muted">Timing: Evening</small>
                                                                <input type="text" name="evening_opening_time" class="form-control mb-2"
                                                                       value="<?=$clinical['evening_opening_time']?>">
                                                                <input type="text" name="evening_closing_time" class="form-control"
                                                                       value="<?=$clinical['evening_closing_time']?>">
                                                            </div>
                                                        </div>

                                                        <div class="clinical-info-item mt-3">
                                                            <i class="fas fa-calendar-times text-danger"></i>
                                                            <div>
                                                                <small class="text-muted">Off Days</small>
                                                                <input type="text" name="off_days" class="form-control"
                                                                       value="<?php echo htmlspecialchars($clinical['off_days'] ?? ''); ?>" 
                                                                       placeholder="e.g. Saturday,Sunday">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="clinical-contact mt-3">
                                                    <i class="fas fa-phone text-warning"></i>
                                                    <div>
                                                        <small class="text-muted">Contact</small>
                                                        <input type="text" name="contact" class="form-control"
                                                               value="<?php echo htmlspecialchars($clinical['contact'] ?? ''); ?>">
                                                    </div>
                                                </div>

                                                <div class="clinical-detail mt-3">
                                                    <i class="fas fa-info-circle text-primary"></i>
                                                    <div>
                                                        <small class="text-muted">Detail</small>
                                                        <textarea name="detail" class="form-control" rows="3" 
                                                                  placeholder="Additional details..."><?php echo htmlspecialchars($clinical['detail'] ?? ''); ?></textarea>
                                                    </div>
                                                </div>

                                                <!-- Buttons -->
                                                <div class="clinical-actions mt-4 text-end">
                                                    <button type="submit" class="btn btn-success me-2">
                                                        <i class="fas fa-save"></i> Update Information
                                                    </button>
                                                    <!-- Optional: Delete button -->
                                                    <!-- <button type="button" class="btn btn-danger" onclick="deleteClinical(<?php echo $clinical['clinical_info_id']; ?>)">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clinic-medical fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No clinical information found for this record.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Container-fluid ends -->
</div>

<?php include BASE_PATH.'/admin/inc/footer.php';?>