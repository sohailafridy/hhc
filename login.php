<?php
require_once 'config.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    redirect('/admin/dashboard');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Prepare Statement to check users table as per screenshot
        $stmt = $pdo->prepare("SELECT id, username, email, role, password FROM users WHERE username = ? AND status = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && base64_encode($password) === $user['password']) {
            // Password is correct, start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_type'] = $user['role'];
            $_SESSION['name'] = $user['username']; // Using username as name since it's not in the table

            // Also keep admin_id for backward compatibility with check_auth()
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['username'];

            if($_SESSION['user_type']==1){
                redirect('/admin/dashboard');
            }elseif($_SESSION['user_type']==2){
                redirect('/doctor/dashboard');
            }elseif($_SESSION['user_type']==3){
                redirect('/nurse/dashboard');
            }elseif($_SESSION['user_type']==4){
                redirect('/manager/dashboard');
            }elseif($_SESSION['user_type']==5){
                redirect('/patient/dashboard');
            }

        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zamzam2</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-header {
            background: #fff;
            padding: 2.5rem 2rem 1rem;
            text-align: center;
        }
        .login-header i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .login-body {
            padding: 1.5rem 2.5rem 2.5rem;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }
        .btn-login {
            background: var(--primary-color);
            color: #fff;
            padding: 0.75rem;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        .btn-login:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <i class="fas fa-boxes-stacked"></i>
                <h3 class="fw-bold text-dark">Welcome Back</h3>
                <p class="text-muted">Enter your admin credentials</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label text-muted small fw-bold text-uppercase">Username</label>
                        <input type="text" class="form-control" name="username" id="username" placeholder="Username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label text-muted small fw-bold text-uppercase">Password</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>
                    <div class="mb-3 form-check d-flex justify-content-between">
                        <div>
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label text-muted small" for="remember">Remember me</label>
                        </div>
                        <a href="#" class="text-primary small text-decoration-none">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Log In
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <p class="text-muted small mb-0">System Version 1.0.0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
