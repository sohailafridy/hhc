<?php 
// ============================================
// GET CURRENT URL AND SET ACTIVE STATES
// ============================================
$current_url = $_SERVER['REQUEST_URI'];
$url_parts = explode('/', trim($current_url, '/'));

// Initialize variables
$active = '';
$open = '';
$active_page = '';

// Get current page name
$current_page = end($url_parts);
$current_page = str_replace('.php', '', $current_page);

// ============================================
// CHECK IF WE'RE IN HOSPITAL SECTION
// ============================================
$in_hospital_section = in_array('hospital', $url_parts);

// ============================================
// DOCTORS
// ============================================
if (in_array('doctors', $url_parts) || in_array('doctor-add', $url_parts) || in_array('doctor-detail', $url_parts)) {
    $open = 'doctors';
    if ($current_page == 'doctors' || $current_page == 'list') {
        $active = 'list';
    } elseif ($current_page == 'doctor-add' || $current_page == 'add') {
        $active = 'add';
    } elseif ($current_page == 'doctor-detail' || $current_page == 'detail') {
        $active = 'detail';
    }
}

// ============================================
// BLOOD BANKS (Only List - No Add)
// ============================================
if (in_array('blood-banks', $url_parts) || in_array('blood-bank', $url_parts)) {
    $open = 'blood-banks';
    $active = 'list';
}

// ============================================
// LABORATORIES (Only List - No Add)
// ============================================
if (in_array('labs', $url_parts) || in_array('laboratories', $url_parts)) {
    $open = 'labs';
    $active = 'list';
}

// ============================================
// FEEDBACK
// ============================================
if (in_array('feedback', $url_parts)) {
    $open = 'feedback';
    $active = 'list';
}

// ============================================
// PROFILE
// ============================================
if (in_array('profile', $url_parts)) {
    $open = 'profile';
    $active = 'edit';
}

// ============================================
// BEDS
// ============================================
if (in_array('beds', $url_parts)) {
    $open = 'beds';
    $active = 'list';
}

// ============================================
// FACILITIES
// ============================================
if (in_array('facilities', $url_parts)) {
    $open = 'facilities';
    $active = 'list';
}

// ============================================
// RECYCLE BIN
// ============================================
if (in_array('recycle', $url_parts)) {
    $open = 'recycle';
    $active = 'list';
}

// ============================================
// DASHBOARD (Default)
// ============================================
if ($current_page == 'index' || $current_page == '' || $current_page == 'hospital') {
    $open = '';
    $active = '';
}

// ============================================
// DIRECT URL MATCHING
// ============================================

// Blood Bank List
if ($current_page == 'blood-banks') {
    $open = 'blood-banks';
    $active = 'list';
}

// Labs List
if ($current_page == 'labs') {
    $open = 'labs';
    $active = 'list';
}

// Doctors List
if ($current_page == 'doctors' || $current_page == 'list') {
    $open = 'doctors';
    $active = 'list';
}

// Doctor Add
if ($current_page == 'doctor-add' || $current_page == 'add') {
    $open = 'doctors';
    $active = 'add';
}

// Doctor Detail
if ($current_page == 'doctor-detail' || $current_page == 'detail') {
    $open = 'doctors';
    $active = 'detail';
}

// Facilities
if ($current_page == 'facilities') {
    $open = 'facilities';
    $active = 'list';
}

// Feedback
if ($current_page == 'feedback') {
    $open = 'feedback';
    $active = 'list';
}

// Profile
if ($current_page == 'profile') {
    $open = 'profile';
    $active = 'edit';
}

// Beds
if ($current_page == 'beds') {
    $open = 'beds';
    $active = 'list';
}

// Recycle Bin
if ($current_page == 'recycle') {
    $open = 'recycle';
    $active = 'list';
}

// Debug - uncomment to see values
// echo "<!-- Current Page: $current_page, Open: $open, Active: $active -->";
?>

<aside class="main-sidebar hidden-print">
    <section class="sidebar" id="sidebar-scroll">
        <ul class="sidebar-menu">
            <li class="nav-level">--- Hospital Panel</li>
            
            <!-- ========================================== -->
            <!-- DASHBOARD -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == '' && $active == '') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/">
                    <i class="icon-speedometer"></i>
                    <span> Dashboard</span>
                </a>
            </li>

            <!-- ========================================== -->
            <!-- DOCTORS (With Add + List) -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'doctors') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-user-md"></i>
                    <span> Doctors </span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/doctor-add">
                            <i class="icon-plus"></i> Add Doctor
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/doctors/list">
                            <i class="icon-arrow-right"></i> List
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ========================================== -->
            <!-- BLOOD BANKS (Only List - No Add) -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'blood-banks') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/blood-banks">
                    <i class="icon-drop"></i>
                    <span> Blood Banks </span>
                    <?php if ($open == 'blood-banks'): ?>
                        <i class="icon-arrow-down"></i>
                    <?php endif; ?>
                </a>
                <?php if ($open == 'blood-banks'): ?>
                <ul class="treeview-menu" style="display:block;">
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/blood-banks">
                            <i class="icon-arrow-right"></i> List
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
            </li>

            <!-- ========================================== -->
            <!-- LABORATORIES (Only List - No Add) -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'labs') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/labs">
                    <i class="icon-flask"></i>
                    <span> Laboratories </span>
                    <?php if ($open == 'labs'): ?>
                        <i class="icon-arrow-down"></i>
                    <?php endif; ?>
                </a>
                <?php if ($open == 'labs'): ?>
                <ul class="treeview-menu" style="display:block;">
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/labs">
                            <i class="icon-arrow-right"></i> List
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
            </li>

            <!-- ========================================== -->
            <!-- BEDS -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'beds') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/beds">
                    <i class="icon-bed"></i>
                    <span> Beds </span>
                </a>
            </li>

            <!-- ========================================== -->
            <!-- FACILITIES -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'facilities') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/facilities">
                    <i class="icon-grid"></i>
                    <span> Facilities </span>
                </a>
            </li>

            <!-- ========================================== -->
            <!-- FEEDBACK -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'feedback') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/feedback">
                    <i class="icon-star"></i>
                    <span> Feedback </span>
                </a>
            </li>

            <!-- ========================================== -->
            <!-- RECYCLE BIN -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'recycle') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/recycle">
                    <i class="icon-trash"></i>
                    <span style="color: #ef4444;"> Recycle Bin </span>
                </a>
            </li>

            <!-- ========================================== -->
            <!-- PROFILE -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'profile') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/profile">
                    <i class="icon-user"></i>
                    <span> Profile </span>
                </a>
            </li>

            <!-- ========================================== -->
            <!-- SEPARATOR -->
            <!-- ========================================== -->
            <li class="nav-level">--- Account</li>

            <!-- ========================================== -->
            <!-- LOGOUT -->
            <!-- ========================================== -->
            <li class="treeview">
                <a class="waves-effect waves-dark text-danger" href="<?php echo BASE_URL; ?>logout">
                    <i class="icon-logout"></i>
                    <span> Logout </span>
                </a>
            </li>

        </ul>
    </section>
</aside>