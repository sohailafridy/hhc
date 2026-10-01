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

$hospital_query = "SELECT h.*, c.city_name 
                   FROM hospitals h 
                   LEFT JOIN cities c ON h.city_id = c.city_id 
                   WHERE h.user_id = $user_id AND h.approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];

// ============================================
// HANDLE FORM SUBMISSION
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hospital_name = mysqli_real_escape_string($con, $_POST['hospital_name']);
    $hospital_address = mysqli_real_escape_string($con, $_POST['hospital_address']);
    $hospital_phone = mysqli_real_escape_string($con, $_POST['hospital_phone']);
    
    $hospital_pic = '';
    if (isset($_FILES['hospital_pic']) && $_FILES['hospital_pic']['error'] == 0) {
        $target_dir = BASE_PATH . "/admin/inc/uploads/hospitals/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["hospital_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["hospital_pic"]["tmp_name"], $target_file)) {
                $hospital_pic = $file_name;
            }
        }
    }
    
    $update_query = "UPDATE hospitals SET 
                        hospital_name = '$hospital_name',
                        hospital_address = '$hospital_address',
                        hospital_phone = '$hospital_phone'";
    
    if (!empty($hospital_pic)) {
        $update_query .= ", hospital_pic = '$hospital_pic'";
    }
    
    $update_query .= " WHERE hospital_id = $hospital_id";
    
    if (mysqli_query($con, $update_query)) {
        $success_msg = "Profile updated successfully!";
        // Refresh data
        $hospital_query = "SELECT h.*, c.city_name 
                           FROM hospitals h 
                           LEFT JOIN cities c ON h.city_id = c.city_id 
                           WHERE h.hospital_id = $hospital_id";
        $hospital_result = mysqli_query($con, $hospital_query);
        $hospital_data = mysqli_fetch_assoc($hospital_result);
    } else {
        $error_msg = "Error: " . mysqli_error($con);
    }
}
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-profile2-admin.css">

<div class="content-wrapper">

    <div class="page-header">
        <h4><i class="fas fa-hospital me-2"></i> Hospital Profile</h4>
    </div>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-card-header">
            <h5><i class="fas fa-edit me-2"></i> Edit Hospital Information</h5>
        </div>
        <div class="form-card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Hospital Name *</label>
                        <input type="text" class="form-control" name="hospital_name" 
                               value="<?php echo htmlspecialchars($hospital_data['hospital_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" 
                               value="<?php echo htmlspecialchars($hospital_data['city_name']); ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-control" name="hospital_phone" 
                               value="<?php echo htmlspecialchars($hospital_data['hospital_phone']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" name="hospital_pic" accept="image/*">
                        <?php if (!empty($hospital_data['hospital_pic'])): ?>
                            <div class="image-preview">
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital_data['hospital_pic']; ?>" 
                                     alt="Hospital Image">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address *</label>
                        <textarea class="form-control" name="hospital_address" rows="3" required><?php echo htmlspecialchars($hospital_data['hospital_address']); ?></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save me-2"></i> Update Profile
                    </button>
                    <a href="<?php echo BASE_URL; ?>hospital/index.php" class="btn-cancel ms-2">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>