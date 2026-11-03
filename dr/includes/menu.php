<?php
include 'colors.php'; 
    // Get current URL path
    $current_url = $_SERVER['REQUEST_URI'];
    $current_path = parse_url($current_url, PHP_URL_PATH);
    
    // Remove query string and get clean path
    $clean_path = strtok($current_path, '?');
    
    // Normalize path for comparison
    $path_parts = explode('/', trim($clean_path, '/'));
    $current_page = end($path_parts);
    
    // Handle detail pages with query parameters
    if (strpos($current_page, '.php') !== false) {
        $current_page = str_replace('.php', '', $current_page);
    }
   
    // Map paths to menu items
    $active_menu = '';
    if ( $current_page == 'dr' || $current_page == '' || $current_page === 'index.php' || $current_page === 'home') {
        $active_menu = 'home';
    } elseif ($current_page === 'doctors' || $current_page === 'doctors.php' || $current_page === 'doctor-detail') {
        $active_menu = 'doctors';
    } elseif ($current_page === 'hospitals' || $current_page === 'hospitals.php' || $current_page === 'hospital-detail') {
        $active_menu = 'hospitals';
    } elseif ($current_page === 'labs' || $current_page === 'laboratories' || $current_page === 'lab-detail') {
        $active_menu = 'labs';
    } elseif ($current_page === 'blood-banks' || $current_page === 'blood-bank-detail') {
        $active_menu = 'blood-banks';
    } elseif ($current_page === 'blood-banks' || $current_page === 'blood-bank-detail') {
        $active_menu = 'blood-banks';
    } elseif ($current_page === 'about') {
        $active_menu = 'about';
    } elseif ($current_page === 'contact') {
        $active_menu = 'contact';
    } elseif ($current_page === 'all-specialities') {
        $active_menu = 'all-specialities';
    }
    
    // Function to add active class
    function is_active($menu_item, $active_menu) {
        return $menu_item === $active_menu ? 'active' : '';
    }
    
    // Function to generate clean URLs
    function clean_url($path) {
        // Remove .php extension if present
        $path = str_replace('.php', '', $path);
        // Use BASE_URL if available, otherwise use relative path
        return defined('BASE_URL') && BASE_URL ? BASE_URL . $path : $path;
    } ?>
<style>
    /* ── Navbar — SehatNexa Brand Colors ── */
    :root {
        --sn-navy  : #1C3F8F;
        --sn-teal  : #00A8CC;
        --sn-green : #27C96A;
        --sn-tgreen: #00C9A7;
        --sn-dark  : #0D2B6E;
        --sn-grad  : linear-gradient(135deg, #1C3F8F 0%, #00A8CC 100%);
        --sn-grad-g: linear-gradient(135deg, #27C96A 0%, #00C9A7 100%);
    }

    .navbar {
        background: rgba(255,255,255,0.97) !important;
        backdrop-filter: blur(20px);
        border-bottom: 2px solid rgba(0,168,204,0.12);
        padding: 10px 0;
        box-shadow: 0 2px 16px rgba(28,63,143,0.08);
    }

    .nav-link {
        font-weight: 600;
        color: var(--sn-dark) !important;
        margin: 0 4px;
        padding: 6px 12px !important;
        border-radius: 8px;
        transition: all 0.2s ease;
        position: relative;
    }

    .nav-link:hover {
        color: var(--sn-navy) !important;
        background: rgba(0,168,204,0.08);
    }

    .nav-link.active {
        color: var(--sn-navy) !important;
        background: rgba(28,63,143,0.08);
        box-shadow: 0 4px 12px rgba(28,63,143,0.12);
    }

    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0; left: 50%;
        transform: translateX(-50%);
        width: 20px; height: 2px;
        background: var(--sn-grad);
        border-radius: 2px;
    }

    /* Toggler — brand colored */
    .navbar-toggler {
        border: 2px solid rgba(0,168,204,0.3) !important;
        padding: 6px 10px;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .navbar-toggler:focus { box-shadow: 0 0 0 3px rgba(0,168,204,0.2) !important; }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%231C3F8F' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
    }

    /* Mobile collapse menu */
    @media (max-width: 991px) {
        #navbarNav {
            background: white;
            border-radius: 12px;
            padding: 12px;
            margin-top: 10px;
            box-shadow: 0 8px 24px rgba(28,63,143,0.1);
            border: 1px solid rgba(0,168,204,0.1);
        }

        .nav-link {
            padding: 10px 14px !important;
            border-radius: 8px;
            margin: 2px 0;
        }

        .nav-link.active::after { display: none; }
    }
</style>
</style>
<nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold me-auto" href="<?php echo clean_url(''); ?>">
                <img src="<?php echo BASE_URL; ?>includes/uploads/logo.png" alt="HHC Logo" style="height:45px; width:auto; max-width:160px; object-fit:contain;">
            </a>
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link <?php echo is_active('home', $active_menu); ?>" href="<?php echo clean_url(''); ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('doctors', $active_menu); ?>" href="<?php echo clean_url('doctors'); ?>">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('hospitals', $active_menu); ?>" href="<?php echo clean_url('hospitals'); ?>">Hospitals</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('labs', $active_menu); ?>" href="<?php echo clean_url('labs'); ?>">Laboratories</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('blood-banks', $active_menu); ?>" href="<?php echo clean_url('blood-banks'); ?>">Blood Banks</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('all-specialities', $active_menu); ?>" href="<?php echo clean_url('all-specialities'); ?>">All Specialities</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('about', $active_menu); ?>" href="<?php echo clean_url('about'); ?>">About</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('contact', $active_menu); ?>" href="<?php echo clean_url('contact'); ?>">Contact</a></li>
                    <!-- <li class="nav-item ms-lg-3">
                        <a class="nav-link btn-login" href="<?php echo clean_url('admin/'); ?>">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li> -->
                   <!--  <li class="nav-item ms-lg-3">
                        <a class="nav-link btn-login fixit-btn" href="<?php echo clean_url('fixit/'); ?>">
                            Fixit
                        </a>
                    </li> -->
                </ul>
            </div>
        </div>
    </nav>