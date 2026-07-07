<?php
include('../config.php');

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        header("Location: login?error=empty");
        exit();
    }
    
    // Query to check user with user type
    $query = "SELECT fu.*, fb.b_name as branch_name,fb.president as co_name
              FROM fixit_users fu 
              LEFT JOIN fixit_branches fb ON fu.branch_id = fb.branch_id  
              WHERE fu.username = '$username' AND fu.status = 1";
   
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password (base64 encoded)
        $stored_password = base64_decode($user['password']);
        
        if ($password === $stored_password) {
            // Check if user type is admin
            if ($user['branch_id'] == 0) {
                // Set session variables
                session_start();
                $_SESSION['fixit'] = true;
                $_SESSION['loggedin'] = true;
                $_SESSION['type'] = 'admin';
                $_SESSION['president'] = '-';
                $_SESSION['team_id'] = 0;
                $_SESSION['team_name'] = 'Founder of Fixit';
                $_SESSION['user_id'] = $user['fixit_user_id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect to admin panel
                header("Location: dashboard");
                exit();
            } else {
                // For other user types (you can extend this)
                session_start();
                $_SESSION['fixit'] = true;
                $_SESSION['loggedin'] = true;
                $_SESSION['type'] = 'Team Admin';
                $_SESSION['president'] = $user['co_name'];
                $_SESSION['team_id'] = $user['branch_id'];
                $_SESSION['team_name'] = $user['branch_name'];
                $_SESSION['user_id'] = $user['fixit_user_id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect based on user type or to dashboard
                header("Location: dashboard");
                exit();
            }
        } else {
            // Invalid password
            header("Location: fixit/login?error=invalid");
            exit();
        }
    } else {
        // User not found
        header("Location: fixit/login?error=invalid");
        exit();
    }
} else {
    // If not POST request, redirect to login
    header("Location: fixit/login");
    exit();
}
?>
