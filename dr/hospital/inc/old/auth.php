<?php
// ============================================
// HOSPITAL AUTHENTICATION CHECK
// This file is included at the top of every hospital page
// ============================================

if (!isset($_SESSION['user_id']) || $_SESSION['type'] != 'hospital') {
    header("Location: " . BASE_URL . "login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get hospital data
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
$entity_id = $hospital_data['entity_id'];
?>