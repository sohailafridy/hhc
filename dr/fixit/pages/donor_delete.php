<?php
include '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $donorId = (int)$_POST['id'];
    
    // Delete donor
    $deleteQuery = "DELETE FROM f_donors WHERE f_donor_id = ?";
    $stmt = mysqli_prepare($con, $deleteQuery);
    mysqli_stmt_bind_param($stmt, 'i', $donorId);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Donor deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting donor']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
