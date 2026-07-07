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
        $shift            = mysqli_real_escape_string($con, $_POST['shift']);
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
<style>
    .doctor-profile-header {
        background: #ea6666;
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .doctor-profile-header h2 {
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    .doctor-profile-header p {
        color: rgba(255,255,255,0.95);
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        font-size: 16px;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .doctor-profile-header i {
        color: rgba(255,255,255,0.9);
        margin-right: 10px;
        font-size: 16px;
    }
    
    .doctor-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        object-fit: cover;
    }
    
    .info-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        overflow: hidden;
    }
    
    .info-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .info-card-header h5 {
        margin: 0;
        color: #495057;
        font-weight: 600;
        font-size: 18px;
    }
    
    .info-card-body {
        padding: 25px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 14px;
    }
    
    .info-value {
        font-weight: 500;
        color: #212529;
        font-size: 15px;
        text-align: right;
    }
    
    .feedback-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .feedback-item {
        padding: 20px 25px;
        border-bottom: 1px solid #f1f3f4;
        transition: background 0.3s ease;
    }
    
    .feedback-item:hover {
        background: #f8f9fa;
    }
    
    .feedback-item:last-child {
        border-bottom: none;
    }
    
    .feedback-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .feedback-name {
        font-weight: 600;
        color: #495057;
        font-size: 16px;
    }
    
    .feedback-email {
        color: #6c757d;
        font-size: 14px;
    }
    
    .feedback-rating {
        color: #ffc107;
        font-size: 18px;
    }
    
    .feedback-comment {
        color: #495057;
        font-size: 15px;
        line-height: 1.6;
        margin-top: 10px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #667eea;
    }
    
    .feedback-date {
        color: #6c757d;
        font-size: 13px;
        margin-top: 10px;
    }
    
    .badge-status {
        padding: 6px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .badge-inactive {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    .btn-action {
        padding: 6px 25px;
        border-radius: 50px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-edit {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
    }
    
    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    .btn-back {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    /* Clinical Record Cards */
    .clinical-record-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .clinical-record-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }
    
    .clinical-record-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .clinical-record-header h6 {
        font-size: 16px;
        margin: 0;
    }
    
    .clinical-record-body {
        padding: 20px;
    }
    
    .clinical-info-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .clinical-info-item:hover {
        background: #e9ecef;
    }
    
    .clinical-info-item i {
        font-size: 18px;
        margin-right: 12px;
        width: 24px;
        text-align: center;
    }
    
    .clinical-info-item div {
        flex: 1;
    }
    
    .clinical-info-item small {
        display: block;
        font-size: 12px;
        margin-bottom: 2px;
    }
    
    .clinical-info-item strong {
        font-size: 14px;
        color: #495057;
    }
    
    .clinical-contact {
        display: flex;
        align-items: center;
        padding: 15px;
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-radius: 8px;
        border-left: 4px solid #ffc107;
        margin-top: 10px;
    }
    
    .clinical-contact i {
        font-size: 20px;
        margin-right: 15px;
        color: #f39c12;
    }
    
    .clinical-contact div {
        flex: 1;
    }
    
    .clinical-contact small {
        display: block;
        font-size: 12px;
        color: #856404;
        margin-bottom: 2px;
    }
    
    .season-group .clinical-contact strong {
        font-size: 16px;
        color: #856404;
        font-weight: 600;
    }
    .padding{
      padding: 0 28px !important;
    }
    .padding-10{
      padding: 10px !important;
    }
</style>

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