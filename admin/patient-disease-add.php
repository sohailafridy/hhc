<?php
require_once '../config.php';
check_auth();

$patient_id = $_GET['patient_id'] ?? $_POST['patient_id'] ?? null;
if (!$patient_id) exit;

$disease_id = $_GET['id'] ?? null;

// Get patient
$stmt = $pdo->prepare("SELECT name, mr_number FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

$disease = null;
if ($disease_id) {
    $stmt = $pdo->prepare("SELECT * FROM patient_diseases WHERE id = ?");
    $stmt->execute([$disease_id]);
    $disease = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $disease_name = trim($_POST['disease_name']);
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);

    try {
        if ($disease_id) {
            $stmt = $pdo->prepare("UPDATE patient_diseases SET disease_name=?, status=?, notes=? WHERE id=?");
            $stmt->execute([$disease_name, $status, $notes, $disease_id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO patient_diseases (patient_id, disease_name, status, notes, diagnosed_date) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$patient_id, $disease_name, $status, $notes]);
        }
        echo '<script>alert("Disease saved successfully!"); window.opener.location.reload(); window.close();</script>';
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<?php include_once(BASE_PATH . '/inc/header.php'); ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5><?= $disease_id ? 'Edit' : 'Add New' ?> Chronic Disease</h5>
            <small><?= htmlspecialchars($patient['name']) ?> (<?= $patient['mr_number'] ?>)</small>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

                <div class="mb-3">
                    <label>Disease Name <span class="text-danger">*</span></label>
                    <input type="text" name="disease_name" class="form-control" value="<?= htmlspecialchars($disease['disease_name'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="Active" <?= ($disease['status'] ?? '') == 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Chronic" <?= ($disease['status'] ?? '') == 'Chronic' ? 'selected' : '' ?>>Chronic</option>
                        <option value="Resolved" <?= ($disease['status'] ?? '') == 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="4"><?= htmlspecialchars($disease['notes'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Disease</button>
            </form>
        </div>
    </div>
</div>

<?php include_once(BASE_PATH . '/inc/footer.php'); ?>