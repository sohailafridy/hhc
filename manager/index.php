<?php
require_once '../config.php';
check_auth();

$title = "Dashboard";

// Get counts from database
try {
    // Get total active doctors
    $stmt_doctors = $pdo->query("SELECT COUNT(*) as total FROM doctors d LEFT JOIN users u ON d.user_id = u.id WHERE u.status = 1");
    $total_doctors = $stmt_doctors->fetch()['total'];

    // Get total inactive doctors
    $stmt_inactive_doctors = $pdo->query("SELECT COUNT(*) as total FROM doctors d LEFT JOIN users u ON d.user_id = u.id WHERE u.status = 0");
    $inactive_doctors = $stmt_inactive_doctors->fetch()['total'];

    // Get total patients (placeholder - you can replace with real patients table when available)
    $total_patients = 0;
    // Uncomment when you have a patients table:
    // $stmt_patients = $pdo->query("SELECT COUNT(*) as total FROM patients");
    // $total_patients = $stmt_patients->fetch()['total'];

} catch (Exception $e) {
    $total_doctors = 0;
    $inactive_doctors = 0;
    $total_patients = 0;
}

include_once(BASE_PATH . '/inc/header.php');
?>

<div class="row">
    <!-- Total Doctors Card -->
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card dashboard-glass-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-user-md text-primary fa-lg"></i>
                    </div>
                    <div>
                        <!-- <h2 class="mb-0 fw-bold text-primary"><?php echo $total_doctors; ?></h2> -->
                        <p class="mb-0 text-muted small">Coming Soon</p>
                    </div>
                </div>
            </div>
            <div class="card-footer dashboard-glass-footer border-0 py-3">
                <!-- <a href="<?php echo BASE_URL; ?>/admin/doctor-list.php" class="text-decoration-none text-primary fw-semibold small">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a> -->
            </div>
        </div>
    </div>

    <!-- Inactive Doctors Card -->
    <!-- <div class="col-md-6 col-xl-3 mb-4">
        <div class="card dashboard-glass-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-danger bg-opacity-10 p-3 me-3">
                        <i class="fas fa-user-times text-danger fa-lg"></i>
                    </div>
                    <div>
                        <h2 class="mb-0 fw-bold text-danger"><?php echo $inactive_doctors; ?></h2>
                        <p class="mb-0 text-muted small">Inactive Doctors</p>
                    </div>
                </div>
            </div>
            <div class="card-footer dashboard-glass-footer border-0 py-3">
                <a href="<?php echo BASE_URL; ?>/admin/doctor-list.php" class="text-decoration-none text-danger fw-semibold small">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div> -->

    <!-- Total Patients Card -->
    <!-- <div class="col-md-6 col-xl-3 mb-4">
        <div class="card dashboard-glass-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-user-injured text-success fa-lg"></i>
                    </div>
                    <div>
                        <h2 class="mb-0 fw-bold text-success"><?php echo $total_patients; ?></h2>
                        <p class="mb-0 text-muted small">Registered Patients</p>
                    </div>
                </div>
            </div>
            <div class="card-footer dashboard-glass-footer border-0 py-3">
                <span class="text-muted small text-success fw-semibold">
                    Coming Soon <i class="fas fa-clock ms-1"></i>
                </span>
            </div>
        </div>
    </div> -->

    <!-- Quick Actions Card -->
    <!-- <div class="col-md-6 col-xl-3 mb-4">
        <div class="card dashboard-glass-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-plus-circle text-warning fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold text-warning">Quick Actions</h5>
                        <p class="mb-0 text-muted small">Add New Doctor</p>
                    </div>
                </div>
            </div>
            <div class="card-footer dashboard-glass-footer border-0 py-3">
                <a href="<?php echo BASE_URL; ?>/admin/doctor-add.php" class="text-decoration-none text-warning fw-semibold small">
                    Add Doctor <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div> -->
</div>

<!-- Welcome Section -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card dashboard-glass-card border-0 shadow-sm">
            <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                    <i class="fas fa-home text-primary fa-lg"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-bold">Welcome to HHC Admin</h4>
                    <p class="mb-0 text-muted small">Home Health Care Management System</p>
                </div>
            </div>
            <p class="text-muted">Manage your doctors, patients, and clinic operations from this dashboard.</p>
            </div>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>
