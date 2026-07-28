<?php
require_once '../config.php';
check_auth();

$visit_id = $_GET['visit_id'] ?? $_POST['visit_id'] ?? null;
if (!$visit_id) exit;

$pres_id = $_GET['id'] ?? null;

// Get visit info
$stmt = $pdo->prepare("SELECT v.*, p.name as patient_name FROM visits v JOIN patients p ON v.patient_id = p.id WHERE v.id = ?");
$stmt->execute([$visit_id]);
$visit = $stmt->fetch();

$pres = null;
if ($pres_id) {
    $stmt = $pdo->prepare("SELECT * FROM visit_prescriptions WHERE id = ?");
    $stmt->execute([$pres_id]);
    $pres = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medicine_name = trim($_POST['medicine_name']);
    $dosage = trim($_POST['dosage']);
    $frequency = trim($_POST['frequency']);
    $duration = trim($_POST['duration']);
    $instructions = trim($_POST['instructions']);

    try {
        if ($pres_id) {
            $sql = "UPDATE visit_prescriptions SET medicine_name=?, dosage=?, frequency=?, duration=?, instructions=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$medicine_name, $dosage, $frequency, $duration, $instructions, $pres_id]);
        } else {
            $sql = "INSERT INTO visit_prescriptions (visit_id, medicine_name, dosage, frequency, duration, instructions) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$visit_id, $medicine_name, $dosage, $frequency, $duration, $instructions]);
        }
        echo '<script>alert("Medicine saved!"); window.opener.location.reload(); window.close();</script>';
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<?php include_once(BASE_PATH . '/inc/header.php'); ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5><?= $pres_id ? 'Edit' : 'Add New' ?> Prescription</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="visit_id" value="<?= $visit_id ?>">

                <div class="mb-3">
                    <label>Medicine Name <span class="text-danger">*</span></label>
                    <input type="text" name="medicine_name" class="form-control" value="<?= htmlspecialchars($pres['medicine_name'] ?? '') ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label>Dosage</label>
                        <input type="text" name="dosage" class="form-control" value="<?= htmlspecialchars($pres['dosage'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Frequency</label>
                        <input type="text" name="frequency" class="form-control" value="<?= htmlspecialchars($pres['frequency'] ?? '') ?>" placeholder="1+1 or 1-0-1">
                    </div>
                    <div class="col-md-4">
                        <label>Duration</label>
                        <input type="text" name="duration" class="form-control" value="<?= htmlspecialchars($pres['duration'] ?? '') ?>" placeholder="7 Days">
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label>Instructions / Special Notes</label>
                    <textarea name="instructions" class="form-control" rows="3"><?= htmlspecialchars($pres['instructions'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">Save Medicine</button>
            </form>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>