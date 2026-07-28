<?php
require_once '../config.php';
check_auth();

$visit_id = $_GET['visit_id'] ?? $_POST['visit_id'] ?? null;
if (!$visit_id) {
    header("Location: " . BASE_URL . "/admin/patient/list");
    exit;
}

// Get visit & patient info
$stmt = $pdo->prepare("
    SELECT v.*, p.name as patient_name, p.mr_number 
    FROM visits v LEFT JOIN patients p ON v.patient_id = p.id 
    WHERE v.id = ?
");
$stmt->execute([$visit_id]);
$visit = $stmt->fetch();

$test_id = $_GET['id'] ?? null;
$test = null;
if ($test_id) {
    $stmt = $pdo->prepare("SELECT * FROM visit_tests WHERE id = ?");
    $stmt->execute([$test_id]);
    $test = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $test_name = trim($_POST['test_name']);
    $result = trim($_POST['result']);
    $normal_range = trim($_POST['normal_range']);
    $notes = trim($_POST['notes']);

    try {
        if ($test_id) {
            // Update
            $stmt = $pdo->prepare("UPDATE visit_tests SET test_name=?, result=?, normal_range=?, notes=? WHERE id=?");
            $stmt->execute([$test_name, $result, $normal_range, $notes, $test_id]);
            $msg = "Test updated successfully!";
        } else {
            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO visit_tests (visit_id, test_name, result, normal_range, notes, test_date) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$visit_id, $test_name, $result, $normal_range, $notes]);
            $msg = "Test added successfully!";
        }
        echo '<script>alert("'.$msg.'"); window.opener.location.reload(); window.close();</script>';
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<?php include_once(BASE_PATH . '/inc/header.php'); ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5><?= $test_id ? 'Edit' : 'Add New' ?> Lab Test</h5>
            <small><?= htmlspecialchars($visit['patient_name']) ?> (<?= $visit['mr_number'] ?>)</small>
        </div>
        <div class="card-body">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="visit_id" value="<?= $visit_id ?>">

                <div class="mb-3">
                    <label class="form-label fw-bold">Test Name <span class="text-danger">*</span></label>
                    <input type="text" name="test_name" class="form-control" value="<?= htmlspecialchars($test['test_name'] ?? '') ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Result <span class="text-danger">*</span></label>
                        <input type="text" name="result" class="form-control" value="<?= htmlspecialchars($test['result'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Normal Range</label>
                        <input type="text" name="normal_range" class="form-control" value="<?= htmlspecialchars($test['normal_range'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Notes / Remarks</label>
                    <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($test['notes'] ?? '') ?></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save"></i> <?= $test_id ? 'Update Test' : 'Add Test' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>