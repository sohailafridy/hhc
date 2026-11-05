<?php include('config.php') ?>
<?php
// colors.php is a PHP file on disk, so include it by file path (not BASE_URL, which is a web URL)
include(__DIR__ . '/includes/colors.php');
?>
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
    <meta name="theme-color" content="<?= $color5 ?>">
    <title>SehatNexa - Medical Login Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Brand colors from colors.php -> CSS variables -->
    <style>
        :root {
            --c-primary:    <?= $color1 ?>;
            --c-teal:       <?= $color2 ?>;
            --c-green:      <?= $color3 ?>;
            --c-mint:       <?= $color4 ?>;
            --c-dark:       <?= $color5 ?>;
            --c-tint-blue:  <?= $color6 ?>;
            --c-tint-green: <?= $color7 ?>;
            --c-white:      <?= $color8 ?>;
            --g-primary:    <?= $gradient_primary ?>;
            --g-green:      <?= $gradient_green ?>;
            --g-full:       <?= $gradient_full ?>;
        }
    </style>

    
    <link rel="stylesheet" href="<?= BASE_URL ?>style/login.css?v=<?= @filemtime(__DIR__ . '/style/login.css') ?: time() ?>">
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
                <img class="logo-img" src="<?= BASE_URL ?>includes/uploads/logo.png" alt="SehatNexa">
                <h1 class="logo-text">Welcome Back</h1>
                <p class="logo-subtitle"><i class="fas fa-shield-heart"></i> Medical Portal Login</p>
            </div>

            <!-- Error Message (if any) -->
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Invalid email or password. Please try again.</span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="login-process.php" id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="email">Username / Email</label>
                    <div class="input-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control wd-100" name="email" id="email"
                               placeholder="Username or Email" autocomplete="username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control wd-100" name="password" id="password"
                               placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Show or hide password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Sign In
                </button>
            </form>

            <!-- Services -->
            <div class="service-badges">
                <div class="service-badge"><i class="fas fa-user-md"></i>Doctors</div>
                <div class="service-badge"><i class="fas fa-hospital"></i>Hospitals</div>
                <div class="service-badge"><i class="fas fa-flask"></i>Labs</div>
                <div class="service-badge"><i class="fas fa-droplet"></i>Blood Banks</div>
            </div>

            <div class="login-footer">
                &copy; <?= date('Y') ?> SehatNexa. All rights reserved.
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        if (togglePassword) {
            togglePassword.addEventListener('click', function () {
                const isHidden = password.getAttribute('type') === 'password';
                password.setAttribute('type', isHidden ? 'text' : 'password');
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }

        // Form validation (username or email both allowed) + double-submit guard
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const emailOrUsername = document.getElementById('email').value.trim();
            const pass = password.value;

            if (!emailOrUsername || !pass) {
                e.preventDefault();
                alert('Please fill in all fields.');
                return;
            }

            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing in...';
        });
    </script>
</body>
</html>