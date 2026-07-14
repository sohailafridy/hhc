<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>Zamzam2 Management System</title>
    <!-- Favicon (Optional) -->
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="">
    <script>
        if (localStorage.getItem('dark-mode') === 'active') {
            document.body.classList.add('dark-mode');
        }
    </script>
    <div class="wrapper">
        <div class="overlay"></div>
        <?php 
            if($_SESSION['user_type']==1){
                include_once(BASE_PATH . '/inc/nav.php');
            }elseif($_SESSION['user_type']==2){
                include_once(BASE_PATH . '/inc/nav-doctor.php');
            }elseif($_SESSION['user_type']==3){
                include_once(BASE_PATH . '/inc/nav-nurse.php');
            }elseif($_SESSION['user_type']==4){
                include_once(BASE_PATH . '/inc/nav-manager.php');
            }elseif($_SESSION['user_type']==5){
                include_once(BASE_PATH . '/inc/nav-patient.php');
            }
        ?>
        <div id="content">
            <nav class="navbar app-topbar navbar-expand-lg navbar-light sticky-top shadow-sm border-bottom py-3 d-print-none">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-primary topbar-toggle me-2">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <div class="topbar-brand d-none d-md-flex align-items-center ms-2">
                        <div>
                            <span class="topbar-eyebrow">Admin Panel</span>
                            <span class="navbar-brand mb-0 h1">
                                <?php
                                if($_SESSION['user_type']==1){
                                    echo 'Admin Dashboard';
                                }elseif($_SESSION['user_type']==2){
                                    echo 'Doctor Dashboard';
                                }elseif($_SESSION['user_type']==3){
                                    echo 'Nurse Dashboard';
                                }elseif($_SESSION['user_type']==4){
                                    echo 'Manager Dashboard';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle topbar-user text-dark text-decoration-none d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="topbar-avatar bg-primary text-white rounded-circle d-inline-flex justify-content-center align-items-center me-2">
                                    <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                                </div>
                                <span class="d-none d-sm-inline fw-semibold"><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2 text-danger"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="container-fluid p-4">
                <!-- Main Content Starts Here -->
