<?php
include('config.php');

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        header("Location: login?error=empty");
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
            // Check if user type is admin
            if ($user['user_type_name'] == 'admin') {
                // Set session variables
                session_start();
                $_SESSION['loggedin'] = true;
                $_SESSION['type'] = 'admin';
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect to admin panel
                header("Location: admin/");
                exit();
            } else {
                // For other user types (you can extend this)
                session_start();
                $_SESSION['loggedin'] = true;
                $_SESSION['type'] = $user['user_type_name'];
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect based on user type or to dashboard
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
