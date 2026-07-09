<?php
require_once '../config.php';
check_auth();

$title = "Add Doctor";
$message = '';
$message_type = '';

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
    $img_path = '';
    $user_id = null;

    try {
        // Check if username already exists
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check->execute([$username]);
        if ($stmt_check->rowCount() > 0) {
            throw new Exception("Username already exists! Please choose a different username.");
        }

        // Check if email already exists (optional, but good practice)
        $stmt_check_email = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check_email->execute([$email]);
        if ($stmt_check_email->rowCount() > 0) {
            throw new Exception("Email already exists! Please use a different email.");
        }

        // Start transaction
        $pdo->beginTransaction();

        // Insert into users table first
        $hashed_password = base64_encode($password); // Match login.php's password handling
        $stmt_user = $pdo->prepare("INSERT INTO users (username, email, password, role, status, created_at) VALUES (?, ?, ?, 2, ?, NOW())");
        $stmt_user->execute([$username, $email, $hashed_password, $status]);
        $user_id = $pdo->lastInsertId();

        // Handle file upload
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $file_name = uniqid() . '_' . basename($_FILES['img']['name']);
            $file_path = $upload_dir . '/' . $file_name;
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validate file type
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif','webp'];
            if (in_array($file_ext, $allowed_ext)) {
                if (move_uploaded_file($_FILES['img']['tmp_name'], $file_path)) {
                    $img_path = 'assets/upload/doctors/' . $file_name;
                } else {
                    throw new Exception("Error uploading file!");
                }
            } else {
                throw new Exception("Invalid file type! Only JPG, JPEG, PNG, GIF allowed.");
            }
        }

        // Insert into doctors table with user_id
        $stmt_doctor = $pdo->prepare("INSERT INTO doctors (name, specialization, other_info, img, status, user_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt_doctor->execute([$name, $specialization, $other_info, $img_path, $status, $user_id]);

        // Commit transaction
        $pdo->commit();
        $message = "Doctor added successfully!";
        $message_type = "success";
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
                <h5 class="mb-0"><i class="fas fa-user-md me-2 text-primary"></i>Add New Doctor</h5>
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
                            <input type="text" class="form-control" id="username" name="username" required placeholder="Enter username">
                            <div class="invalid-feedback">Please enter username</div>
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="Enter email">
                            <div class="invalid-feedback">Please enter valid email</div>
                        </div>
                        <div class="col-md-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
                            <div class="invalid-feedback">Please enter password</div>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Doctor Name</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., Dr. Muhammad Younis">
                            <div class="invalid-feedback">Please enter doctor name</div>
                        </div>
                        <div class="col-md-6">
                            <label for="specialization" class="form-label fw-semibold">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" required placeholder="e.g., Senior Consultant">
                            <div class="invalid-feedback">Please enter specialization</div>
                        </div>
                        <div class="col-12">
                            <label for="img" class="form-label fw-semibold">Profile Image</label>
                            <input type="file" class="form-control" id="img" name="img" accept="image/*">
                            <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF</div>
                        </div>
                        <div class="col-12">
                            <label for="other_info" class="form-label fw-semibold">Other Information</label>
                            <textarea class="form-control" id="other_info" name="other_info" rows="3" placeholder="Any additional information about the doctor"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label fw-semibold" for="status">
                                    Active
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Save Doctor
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