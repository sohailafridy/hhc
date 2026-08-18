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

.form-label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e293b;
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #4facfe;
    box-shadow: 0 0 0 3px rgba(79,172,254,0.12);
}

.image-preview {
    margin-top: 12px;
}

.image-preview img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 12px;
    border: 3px solid white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

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
</style>

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