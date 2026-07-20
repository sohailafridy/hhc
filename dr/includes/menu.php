<?php
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
    }
?>
<style>
    .nav-link.active{
        padding-left: 10px;
box-shadow: 0 10px 25px rgba(37, 99, 235, .35), 0 4px 10px rgba(0, 0, 0, .08);
position: relative;
    }
</style>
<nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo clean_url(''); ?>">
                <i class="fas fa-heartbeat me-2"></i>DoctorApp
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link <?php echo is_active('home', $active_menu); ?>" href="<?php echo clean_url(''); ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('doctors', $active_menu); ?>" href="<?php echo clean_url('doctors'); ?>">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('hospitals', $active_menu); ?>" href="<?php echo clean_url('hospitals'); ?>">Hospitals</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('labs', $active_menu); ?>" href="<?php echo clean_url('labs'); ?>">Laboratories</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('blood-banks', $active_menu); ?>" href="<?php echo clean_url('blood-banks'); ?>">Blood Banks</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('about', $active_menu); ?>" href="<?php echo clean_url('about'); ?>">About</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo is_active('contact', $active_menu); ?>" href="<?php echo clean_url('contact'); ?>">Contact</a></li>
                    <!-- <li class="nav-item ms-lg-3">
                        <a class="nav-link btn-login" href="<?php echo clean_url('admin/'); ?>">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li> -->
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link btn-login fixit-btn" href="<?php echo clean_url('fixit/'); ?>">
                            Fixit
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>