<?php
require_once '../config.php';
check_auth();

$title = "Add New Patient";
$message = '';
$message_type = '';

// Create upload directory if it doesn't exist
$upload_dir = BASE_PATH . '/assets/upload/patients';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    $name = trim($_POST['name'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $blood_group = trim($_POST['blood_group'] ?? '');
    $allergies = trim($_POST['allergies'] ?? '');
    $emergency_contact = trim($_POST['emergency_contact'] ?? '');
    $emergency_relation = trim($_POST['emergency_relation'] ?? '');
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

        // Check if email already exists
        if (!empty($email)) {
            $stmt_check_email = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check_email->execute([$email]);
            if ($stmt_check_email->rowCount() > 0) {
                throw new Exception("Email already exists! Please use a different email.");
            }
        }

        // Check if CNIC already exists in patients
        if (!empty($cnic)) {
            $stmt_check_cnic = $pdo->prepare("SELECT id FROM patients WHERE cnic = ?");
            $stmt_check_cnic->execute([$cnic]);
            if ($stmt_check_cnic->rowCount() > 0) {
                throw new Exception("This CNIC is already registered!");
            }
        }

        // Generate MR Number
        $year = date('Y');
        $stmt_mr = $pdo->prepare("SELECT MAX(id) as last_id FROM patients");
        $stmt_mr->execute();
        $last = $stmt_mr->fetch(PDO::FETCH_ASSOC);
        $next_id = ($last['last_id'] ?? 0) + 1;
        $mr_number = "PAT-" . $year . str_pad($next_id, 4, '0', STR_PAD_LEFT);

        // Start transaction
        $pdo->beginTransaction();

        // Insert into users table first
        $hashed_password = base64_encode($password); // Matching your doctor page logic
        $stmt_user = $pdo->prepare("
            INSERT INTO users (username, email, password, role, status, created_at) 
            VALUES (?, ?, ?, 5, ?, NOW())
        ");
        $stmt_user->execute([$username, $email, $hashed_password, $status]);
        $user_id = $pdo->lastInsertId();

        

        // Insert into patients table with user_id
        $stmt_patient = $pdo->prepare("
            INSERT INTO patients 
            (mr_number, name, father_name, cnic, age, gender, phone, email, address, 
             blood_group, allergies, emergency_contact, emergency_relation, status, user_id, registration_date, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())
        ");

        $stmt_patient->execute([
            $mr_number,
            $name,
            $father_name,
            $cnic,
            $age,
            $gender,
            $phone,
            $email,
            $address,
            $blood_group,
            $allergies,
            $emergency_contact,
            $emergency_relation,
            $status,
            $user_id
        ]);

        $pdo->commit();

        $message = "Patient registered successfully!<br><strong>MR Number: $mr_number</strong><br>Username: <strong>$username</strong>";
        $message_type = "success";

    } catch (Exception $e) {
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
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2 text-primary"></i>
                    Add New Patient
                </h5>
                <a href="<?=BASE_URL?>/admin/patient/list" class="btn btn-outline-primary btn-sm">
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
                        <!-- Login Credentials -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" required placeholder="Enter username">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter email">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required placeholder="Enter password">
                        </div>

                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Father/Husband Name</label>
                            <input type="text" class="form-control" name="father_name">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">CNIC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="cnic" required placeholder="00000-0000000-0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Age <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="age" required min="1" max="150">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Blood Group</label>
                            <input type="text" class="form-control" name="blood_group" placeholder="A+, B-, etc.">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Allergies / Medical History</label>
                            <textarea class="form-control" name="allergies" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Emergency Contact</label>
                            <input type="text" class="form-control" name="emergency_contact">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Relation</label>
                            <input type="text" class="form-control" name="emergency_relation" placeholder="Son, Wife, etc.">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status" id="status" checked>
                                <label class="form-check-label fw-semibold" for="status">Active</label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fas fa-save me-2"></i>Register Patient
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
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