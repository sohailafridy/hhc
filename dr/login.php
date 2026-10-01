<?php include('config.php') ?>

<?php 
    if(isset($_SESSION['type'])){
        if($_SESSION['type'] == 'admin'){
            // Redirect based on user type or to dashboard
                header("Location: admin/");
                exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoctorApp - Medical Login Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>style/login.css">
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation"></div>
    
    <!-- Floating Medical Icons -->
    <div class="medical-icons">
        <i class="fas fa-heartbeat icon-float"></i>
        <i class="fas fa-stethoscope icon-float"></i>
        <i class="fas fa-user-md icon-float"></i>
        <i class="fas fa-hospital icon-float"></i>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h1 class="logo-text">DoctorApp</h1>
                <p class="logo-subtitle">Medical Portal Login</p>
            </div>

            <!-- Error Message (if any) -->
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Invalid email or password. Please try again.</span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="login-process.php">
                <div class="form-group">
                    <label class="form-label">Username/Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="text" class="form-control" name="email" placeholder="doctor@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <!-- <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div> -->

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Sign In
                </button>
            </form>

            <!-- Divider -->
            <!-- <div class="divider">
                <span>OR</span>
            </div> -->

            <!-- Social Login -->
            <!-- <div class="social-login">
                <span class="social-btn google">
                    <i class="fab fa-google"></i>
                    admin@gmail.com
                </span>
                <span class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i>
                    admin@gmail.com
            </span>
            </div> -->

            <!-- Register Link -->
            <div class="register-link">
                admin@gmail.com / 123
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form Validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields.');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }
        });

        // Social Login Handlers
        document.querySelector('.social-btn.google').addEventListener('click', function(e) {
            e.preventDefault();
            // Implement Google OAuth here
            alert('Google login coming soon!');
        });

        document.querySelector('.social-btn.facebook').addEventListener('click', function(e) {
            e.preventDefault();
            // Implement Facebook OAuth here
            alert('Facebook login coming soon!');
        });

        // Forgot Password Handler
        document.querySelector('.forgot-password').addEventListener('click', function(e) {
            e.preventDefault();
            // Implement forgot password functionality
            alert('Password reset coming soon!');
        });

        // Register Handler
        document.querySelector('.register-link a').addEventListener('click', function(e) {
            e.preventDefault();
            // Implement registration redirect
            alert('Registration coming soon!');
        });
    </script>
</body>
</html>
