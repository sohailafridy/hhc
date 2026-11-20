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
// AJAX: CHECK REG NO EXISTENCE
// ============================================
if (isset($_POST['check_reg_no'])) {
    header('Content-Type: application/json');
    $check_reg_no = mysqli_real_escape_string($con, trim($_POST['reg_no']));
    $current_reg_no = mysqli_real_escape_string($con, $hospital_data['reg_no'] ?? '');

    if ($check_reg_no === $current_reg_no) {
        echo json_encode(['exists' => false]);
        exit();
    }

    $q = "SELECT hospital_id FROM hospitals WHERE reg_no = '$check_reg_no' LIMIT 1";
    $res = mysqli_query($con, $q);
    
    if (mysqli_num_rows($res) > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
    exit();
}

// ============================================
// HANDLE FORM SUBMISSION
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['check_reg_no'])) {
    $hospital_name = mysqli_real_escape_string($con, $_POST['hospital_name']);
    $hospital_address = mysqli_real_escape_string($con, $_POST['hospital_address']);
    $hospital_phone = mysqli_real_escape_string($con, $_POST['hospital_phone']);
    $reg_no = mysqli_real_escape_string($con, $_POST['reg_no']);
    
    // Server-side double check for Reg No
    $check_q = "SELECT hospital_id FROM hospitals WHERE reg_no = '$reg_no' AND hospital_id != $hospital_id LIMIT 1";
    $check_res = mysqli_query($con, $check_q);
    
    if (mysqli_num_rows($check_res) > 0) {
        $error_msg = "Error: This Registration No is already in use by another hospital.";
    } else {
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
                            hospital_phone = '$hospital_phone',
                            reg_no = '$reg_no'";
        
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
}
?>

<?php include BASE_PATH.'/admin/inc/header.php'; ?>
<?php include BASE_PATH.'/admin/inc/top.php'; ?>
<?php include BASE_PATH.'/hospital/inc/nav.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-profile2-admin.css">

<style>
    .profile-pic-wrapper {
        position: relative;
        width: 150px;
        height: 150px;
        margin-bottom: 15px;
        border-radius: 12px;
        overflow: hidden;
        border: 3px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .profile-pic-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profile-pic-placeholder {
        font-size: 3rem;
        color: #cbd5e1;
    }
    .form-card {
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    .form-card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 16px 16px 0 0;
        padding: 20px;
    }
    .form-card-body {
        padding: 30px;
    }
    .form-label {
        font-weight: 600;
        color: #475569;
    }
    .is-invalid { border-color: #ef4444 !important; }
    .invalid-feedback { display: none; color: #ef4444; font-size: 0.85rem; margin-top: 5px; }
    .is-invalid ~ .invalid-feedback { display: block; }
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
            <h5 class="mb-0 text-primary"><i class="fas fa-edit me-2"></i> Edit Hospital Information</h5>
        </div>
        <div class="form-card-body">
            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <div class="row g-4">
                    
                    <!-- Profile Picture Column -->
                    <div class="col-md-4 text-center border-end">
                        <label class="form-label d-block mb-3">Profile Picture</label>
                        <div class="profile-pic-wrapper mx-auto">
                            <?php if (!empty($hospital_data['hospital_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital_data['hospital_pic']; ?>" alt="Hospital Image">
                            <?php else: ?>
                                <i class="fas fa-hospital profile-pic-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <input type="file" class="form-control mt-2" name="hospital_pic" accept="image/*">
                        <small class="text-muted mt-2 d-block">Allowed: jpg, png, webp</small>
                    </div>

                    <!-- Form Fields Column -->
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hospital Name *</label>
                                <input type="text" class="form-control" name="hospital_name" 
                                       value="<?php echo htmlspecialchars($hospital_data['hospital_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registration No *</label>
                                <input type="text" class="form-control" name="reg_no" id="reg_no" 
                                       value="<?php echo htmlspecialchars($hospital_data['reg_no'] ?? ''); ?>" placeholder="e.g. REG-12345" autocomplete="off">
                                <div class="invalid-feedback" id="reg_no_error">This Registration No is already taken.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone *</label>
                                <input type="text" class="form-control" name="hospital_phone" 
                                       value="<?php echo htmlspecialchars($hospital_data['hospital_phone']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control bg-light" 
                                       value="<?php echo htmlspecialchars($hospital_data['city_name']); ?>" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address *</label>
                                <textarea class="form-control" name="hospital_address" rows="3" required><?php echo htmlspecialchars($hospital_data['hospital_address']); ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn-submit" id="submitBtn">
                                <i class="fas fa-save me-2"></i> Update Profile
                            </button>
                            <a href="<?php echo BASE_URL; ?>hospital/index.php" class="btn-cancel ms-2">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const regInput = document.getElementById('reg_no');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('profileForm');
    const originalRegNo = "<?php echo htmlspecialchars($hospital_data['reg_no'] ?? ''); ?>";
    let checkTimeout = null;

    regInput.addEventListener('keyup', function() {
        const currentVal = this.value.trim();
        
        // Remove error state initially
        this.classList.remove('is-invalid');
        submitBtn.disabled = false;

        // If it matches the original, do nothing
        if (currentVal === originalRegNo || currentVal === '') {
            return;
        }

        // Debounce the API call
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(() => {
            let formData = new URLSearchParams();
            formData.append('check_reg_no', '1');
            formData.append('reg_no', currentVal);

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    regInput.classList.add('is-invalid');
                    submitBtn.disabled = true;
                } else {
                    regInput.classList.remove('is-invalid');
                    submitBtn.disabled = false;
                }
            })
            .catch(err => console.error(err));
        }, 500); // 500ms delay to prevent spamming DB on every keystroke
    });

    // Prevent submission if invalid
    form.addEventListener('submit', function(e) {
        if (regInput.classList.contains('is-invalid')) {
            e.preventDefault();
            alert('Please fix the Registration No error before submitting.');
        }
    });
});
</script>