<?php
include('config.php');

// ============================================
// START SESSION FIRST
// ============================================
session_start();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=empty");
        exit();
    }
    
    // Query to check user with user type
    $query = "SELECT u.*, ut.type as user_type_name 
              FROM users u 
              LEFT JOIN usertypes ut ON u.user_type_id = ut.usertypes_id  
              WHERE u.email = '$email' AND u.status = 1";
    
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password (base64 encoded)
        $stored_password = base64_decode($user['password']);
        
        if ($password === $stored_password) {
            
            // ============================================
            // SET SESSION VARIABLES
            // ============================================
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['type'] = $user['user_type_name'];  // ✅ Only one type
            
            // ============================================
            // REDIRECT BASED ON USER TYPE
            // ============================================
            if ($user['user_type_name'] == 'admin') {
                header("Location: admin/");
                exit();
            } 
            elseif ($user['user_type_name'] == 'hospital') {
                header("Location: hospital/");
                exit();
            } 
            elseif ($user['user_type_name'] == 'doctor') {
                header("Location: doctor/");
                exit();
            } 
            elseif ($user['user_type_name'] == 'lab') {
                header("Location: lab/");
                exit();
            } 
            elseif ($user['user_type_name'] == 'blood_bank') {
                header("Location: blood-bank/");
                exit();
            } 
            else {
                // Default redirect for other user types
                header("Location: dashboard.php");
                exit();
            }
            
        } else {
            // Invalid password
            header("Location: login.php?error=invalid");
            exit();
        }
    } else {
        // User not found
        header("Location: login.php?error=invalid");
        exit();
    }
} else {
    // If not POST request, redirect to login
    header("Location: login.php");
    exit();
}
?>