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

$hospital_query = "SELECT * FROM hospitals WHERE user_id = $user_id AND approve = 1";
$hospital_result = mysqli_query($con, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);

if (!$hospital_data) {
    session_destroy();
    header("Location: " . BASE_URL . "login");
    exit();
}

$hospital_id = $hospital_data['hospital_id'];

// ============================================
// UPDATE BEDS
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $total_beds = (int)$_POST['total_beds'];
    $icu_beds = (int)$_POST['icu_beds'];
    $general_beds = (int)$_POST['general_beds'];
    $private_beds = (int)$_POST['private_beds'];
    
    $check_query = "SELECT id FROM hospital_beds WHERE hospital_id = $hospital_id";
    $check_result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $update_query = "UPDATE hospital_beds SET 
                            total_beds = $total_beds,
                            icu_beds = $icu_beds,
                            general_beds = $general_beds,
                            private_beds = $private_beds,
                            updated_at = NOW()
                        WHERE hospital_id = $hospital_id";
    } else {
        $update_query = "INSERT INTO hospital_beds 
                            (hospital_id, total_beds, icu_beds, general_beds, private_beds, created_at, updated_at) 
                        VALUES 
                            ($hospital_id, $total_beds, $icu_beds, $general_beds, $private_beds, NOW(), NOW())";
    }
    
    if (mysqli_query($con, $update_query)) {
        $_SESSION['success_msg'] = "Beds updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($con);
    }
    header('Location: ' . BASE_URL . 'hospital/beds.php');
    exit();
}

// Get current beds data
$beds_query = "SELECT * FROM hospital_beds WHERE hospital_id = $hospital_id";
$beds_result = mysqli_query($con, $beds_query);
$beds = mysqli_fetch_assoc($beds_result);
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

.bed-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.bed-item {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px 20px;
    border: 1px solid #e2e8f0;
}

.bed-item label {
    font-weight: 600;
    font-size: 0.8rem;
    color: #64748b;
    display: block;
    margin-bottom: 4px;
}

.bed-item .form-control {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 1.1rem;
    font-weight: 700;
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
</style>

<div class="content-wrapper">

    <div class="page-header">
        <h4><i class="fas fa-bed me-2"></i> Bed Management</h4>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-card-header">
            <h5><i class="fas fa-edit me-2"></i> Update Bed Availability</h5>
        </div>
        <div class="form-card-body">
            <form method="POST">
                <div class="bed-grid">
                    <div class="bed-item">
                        <label>Total Beds</label>
                        <input type="number" class="form-control" name="total_beds" 
                               value="<?php echo $beds ? $beds['total_beds'] : 0; ?>" min="0">
                    </div>
                    <div class="bed-item">
                        <label>ICU Beds</label>
                        <input type="number" class="form-control" name="icu_beds" 
                               value="<?php echo $beds ? $beds['icu_beds'] : 0; ?>" min="0">
                    </div>
                    <div class="bed-item">
                        <label>General Beds</label>
                        <input type="number" class="form-control" name="general_beds" 
                               value="<?php echo $beds ? $beds['general_beds'] : 0; ?>" min="0">
                    </div>
                    <div class="bed-item">
                        <label>Private Beds</label>
                        <input type="number" class="form-control" name="private_beds" 
                               value="<?php echo $beds ? $beds['private_beds'] : 0; ?>" min="0">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save me-2"></i> Update Beds
                    </button>
                    <a href="<?php echo BASE_URL; ?>hospital/index.php" class="btn btn-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include BASE_PATH.'/admin/inc/footer.php'; ?>