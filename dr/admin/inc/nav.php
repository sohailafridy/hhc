<?php 
// ============================================
// GET CURRENT URL AND SET ACTIVE STATES
// ============================================
$current_url = $_SERVER['REQUEST_URI'];
$url_parts = explode('/', trim($current_url, '/'));

// Initialize variables
$active = '';
$open = '';
$user_type_param = '';
$in_users_section = false;

// Check if we're in users section
if (in_array('users', $url_parts)) {
    $in_users_section = true;
}

// Check for user type in URL parameters (GET)
if (strpos($current_url, '?type=') !== false) {
    parse_str(parse_url($current_url, PHP_URL_QUERY), $query_params);
    if (isset($query_params['type'])) {
        $user_type_param = $query_params['type'];
    }
}

// ============================================
// CITIES
// ============================================
if (in_array('cities', $url_parts)) {
    $open = 'cities';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}

// ============================================
// HOSPITALS
// ============================================
if (in_array('hospitals', $url_parts)) {
    $open = 'hospitals';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}

// ============================================
// DOCTORS
// ============================================
if (in_array('doctors', $url_parts)) {
    $open = 'doctors';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    } elseif (in_array('assign-hospitals', $url_parts)) {
        $active = 'assign-hospitals';
    }
}

// ============================================
// LABORATORIES
// ============================================
if (in_array('laboratories', $url_parts)) {
    $open = 'laboratories';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}

// ============================================
// BLOOD BANKS
// ============================================
if (in_array('blood-banks', $url_parts)) {
    $open = 'blood-banks';
    if (in_array('add', $url_parts)) {
        $active = 'add';
    } elseif (in_array('list', $url_parts)) {
        $active = 'list';
    }
}

// ============================================
// USERS - Set active/open states
// ============================================
if ($in_users_section) {
    $open = 'users';
    // Set active based on user type parameter
    if (empty($user_type_param)) {
        $active = 'all';
    } elseif ($user_type_param == 'admin') {
        $active = 'admin';
    } elseif ($user_type_param == 'doctor') {
        $active = 'doctor';
    } elseif ($user_type_param == 'hospital') {
        $active = 'hospital';
    } elseif ($user_type_param == 'lab') {
        $active = 'lab';
    } elseif ($user_type_param == 'blood_bank') {
        $active = 'blood_bank';
    } elseif ($user_type_param == 'fixit') {
        $active = 'fixit';
    }
}

// ============================================
// RECYCLE BIN
// ============================================
if (in_array('recycle', $url_parts)) {
    $open = 'recycle';
    $active = 'list';
}

// ============================================
// DIRECT URL MATCHING FOR RECYCLE BIN
// ============================================
if ($current_url == BASE_URL . 'admin/recycle' || strpos($current_url, 'admin/recycle') !== false) {
    $open = 'recycle';
    $active = 'list';
}

?>

<aside class="main-sidebar hidden-print ">
    <section class="sidebar" id="sidebar-scroll">
        <!-- Sidebar Menu-->
        <ul class="sidebar-menu">
            <li class="nav-level">--- Navigation</li>
            
            <!-- ===== DASHBOARD ===== -->
            <li class="treeview <?php echo ($open == '') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin">
                    <i class="icon-speedometer"></i><span> Dashboard</span>
                </a>                
            </li>

            <!-- ===== CITIES ===== -->
            <li class="treeview <?php echo ($open == 'cities') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-book-open"></i>
                    <span> Cities</span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/cities/add">
                            <i class="icon-arrow-right"></i>New
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/cities/list">
                            <i class="icon-arrow-right"></i> List
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ===== HOSPITALS ===== -->
            <li class="treeview <?php echo ($open == 'hospitals') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-book-open"></i>
                    <span> Hospitals </span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/hospitals/add">
                            <i class="icon-arrow-right"></i>New
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/hospitals/list">
                            <i class="icon-arrow-right"></i> List
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- ===== DOCTORS ===== -->
            <li class="treeview <?php echo ($open == 'doctors') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-book-open"></i>
                    <span> Doctors </span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/doctors/add">
                            <i class="icon-arrow-right"></i>New
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/doctors/list">
                            <i class="icon-arrow-right"></i> List
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ===== LABORATORIES ===== -->
            <li class="treeview <?php echo ($open == 'laboratories') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-book-open"></i>
                    <span> Laboratories </span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/laboratories/add">
                            <i class="icon-arrow-right"></i>New
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/laboratories/list">
                            <i class="icon-arrow-right"></i> List
                        </a>
                    </li>
                </ul>
            </li>
           
            <!-- ===== BLOOD BANKS ===== -->
            <li class="treeview <?php echo ($open == 'blood-banks') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-book-open"></i>
                    <span> Blood Banks </span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/blood-banks/add">
                            <i class="icon-arrow-right"></i>New
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/blood-banks/list">
                            <i class="icon-arrow-right"></i> List
                        </a>
                    </li>
                </ul>
            </li>
           
            <!-- ========================================== -->
            <!-- ===== USERS MENU ===== -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'users') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-users"></i>
                    <span> Users </span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu">
                    <!-- All Users -->
                    <li class="<?php echo ($active == 'all') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users">
                            <i class="icon-arrow-right"></i> All Users
                        </a>
                    </li>
                    <!-- Admins -->
                    <li class="<?php echo ($active == 'admin') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=admin">
                            <i class="icon-arrow-right"></i> Admins
                        </a>
                    </li>
                    <!-- Doctors -->
                    <li class="<?php echo ($active == 'doctor') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=doctor">
                            <i class="icon-arrow-right"></i> Doctors
                        </a>
                    </li>
                    <!-- Hospitals -->
                    <li class="<?php echo ($active == 'hospital') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=hospital">
                            <i class="icon-arrow-right"></i> Hospitals
                        </a>
                    </li>
                    <!-- Laboratories -->
                    <li class="<?php echo ($active == 'lab') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=lab">
                            <i class="icon-arrow-right"></i> Laboratories
                        </a>
                    </li>
                    <!-- Blood Banks -->
                    <li class="<?php echo ($active == 'blood_bank') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=blood_bank">
                            <i class="icon-arrow-right"></i> Blood Banks
                        </a>
                    </li>
                    <!-- Fixit Users -->
                    <li class="<?php echo ($active == 'fixit') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=fixit">
                            <i class="icon-arrow-right"></i> Fixit Users
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ========================================== -->
            <!-- ===== RECYCLE BIN ===== -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'recycle') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/recycle">
                    <i class="icon-trash"></i>
                    <span style="color: #ef4444;"> Recycle Bin</span>
                    <?php if ($open == 'recycle'): ?>
                        <i class="icon-arrow-down"></i>
                    <?php endif; ?>
                </a>
                <?php if ($open == 'recycle'): ?>
                <ul class="treeview-menu" style="display:block;">
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/recycle">
                            <i class="icon-arrow-right"></i> All Deleted
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
            </li>

            <!-- ========================================== -->
            <!-- ===== MORE / LOGOUT ===== -->
            <!-- ========================================== -->
            <li class="nav-level">--- More</li>
            <li class="treeview <?php echo ($open == '') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark text-danger" href="<?php echo BASE_URL; ?>logout">
                    <i class="icon-speedometer"></i><span> Logout</span>
                </a>                
            </li>

        </ul>
    </section>
</aside>