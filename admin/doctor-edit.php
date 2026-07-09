<?php
require_once '../config.php';
check_auth();

$title = "Edit Doctor";
$message = '';
$message_type = '';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    redirect('/admin/doctor/list');
}

// Fetch existing doctor and user data
$stmt = $pdo->prepare("SELECT d.*, u.username, u.email 
                       FROM doctors d 
                       LEFT JOIN users u ON d.user_id = u.id 
                       WHERE d.id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    redirect('/admin/doctor/list');
}

// Create upload directory if it doesn't exist
$upload_dir = BASE_PATH . '/assets/upload/doctors';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $other_info = trim($_POST['other_info'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    $img_path = $doctor['img']; // Keep existing image by default
    $user_id = $doctor['user_id'];

    try {
        // Check if username already exists (excluding current user)
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt_check->execute([$username, $user_id]);
        if ($stmt_check->rowCount() > 0) {
            throw new Exception("Username already exists! Please choose a different username.");
        }

        // Check if email already exists (excluding current user)
        $stmt_check_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt_check_email->execute([$email, $user_id]);
        if ($stmt_check_email->rowCount() > 0) {
            throw new Exception("Email already exists! Please use a different email.");
        }

        // Start transaction
        $pdo->beginTransaction();

        // Update users table
        if (!empty($password)) {
            $hashed_password = base64_encode($password);
            $stmt_user = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, status = ? WHERE id = ?");
            $stmt_user->execute([$username, $email, $hashed_password, $status, $user_id]);
        } else {
            $stmt_user = $pdo->prepare("UPDATE users SET username = ?, email = ?, status = ? WHERE id = ?");
            $stmt_user->execute([$username, $email, $status, $user_id]);
        }

        // Handle file upload
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $file_name = uniqid() . '_' . basename($_FILES['img']['name']);
            $file_path = $upload_dir . '/' . $file_name;
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validate file type
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($file_ext, $allowed_ext)) {
                if (move_uploaded_file($_FILES['img']['tmp_name'], $file_path)) {
                    // Delete old image if it exists
                    if (!empty($doctor['img']) && file_exists(BASE_PATH . '/' . $doctor['img'])) {
                        unlink(BASE_PATH . '/' . $doctor['img']);
                    }
                    $img_path = 'assets/upload/doctors/' . $file_name;
                } else {
                    throw new Exception("Error uploading file!");
                }
            } else {
                throw new Exception("Invalid file type! Only JPG, JPEG, PNG, GIF, WEBP allowed.");
            }
        }

        // Update doctors table
        $stmt_doctor = $pdo->prepare("UPDATE doctors SET name = ?, specialization = ?, other_info = ?, img = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt_doctor->execute([$name, $specialization, $other_info, $img_path, $status, $id]);

        // Commit transaction
        $pdo->commit();
        $message = "Doctor updated successfully!";
        $message_type = "success";
        
        // Refresh doctor data
        $stmt->execute([$id]);
        $doctor = $stmt->fetch();
    } catch (Exception $e) {
        // Rollback transaction only if active
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

include_once(BASE_PATH . '/inc/header.php');
?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-md me-2 text-primary"></i>Edit Doctor</h5>
                <a href="<?=BASE_URL?>/admin/doctor/list" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to List
                </a>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($doctor['username'] ?? ''); ?>" required placeholder="Enter username">
                            <div class="invalid-feedback">Please enter username</div>
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($doctor['email'] ?? ''); ?>" required placeholder="Enter email">
                            <div class="invalid-feedback">Please enter valid email</div>
                        </div>
                        <div class="col-md-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                            <div class="form-text">Only enter if you want to change the password</div>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Doctor Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required placeholder="e.g., Dr. Muhammad Younis">
                            <div class="invalid-feedback">Please enter doctor name</div>
                        </div>
                        <div class="col-md-6">
                            <label for="specialization" class="form-label fw-semibold">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required placeholder="e.g., Senior Consultant">
                            <div class="invalid-feedback">Please enter specialization</div>
                        </div>
                        <div class="col-md-8">
                            <label for="img" class="form-label fw-semibold">Profile Image</label>
                            <input type="file" class="form-control" id="img" name="img" accept="image/*">
                            <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF, WEBP. Leave blank to keep current image.</div>
                        </div>
                        <div class="col-md-4">
                            <?php if (!empty($doctor['img'])): ?>
                                <div class="mt-2">
                                    <p class="small mb-1">Current Image:</p>
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($doctor['img']); ?>" alt="Current Profile" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label for="other_info" class="form-label fw-semibold">Other Information</label>
                            <textarea class="form-control" id="other_info" name="other_info" rows="3" placeholder="Any additional information about the doctor"><?php echo htmlspecialchars($doctor['other_info']); ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="status" name="status" <?php echo $doctor['status'] == 1 ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-semibold" for="status">
                                    Active
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Update Doctor
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Bootstrap form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>