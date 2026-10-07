<?php 
// ============================================
// GET CURRENT URL AND SET ACTIVE STATES
// ============================================
$current_url = strtok($_SERVER['REQUEST_URI'], '?');
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
if (in_array('doctors', $url_parts) 
    || in_array('doctor-add', $url_parts) 
    || in_array('doctor-detail', $url_parts) 
    || in_array('past_registered_doctors', $url_parts)) {
    
    $open = 'doctors';
    
    if ($current_page == 'doctors' || $current_page == 'list') {
        $active = 'list';
    } elseif ($current_page == 'doctor-add' || $current_page == 'add') {
        $active = 'add';
    } elseif ($current_page == 'doctor-detail' || $current_page == 'detail') {
        $active = 'detail';
    } elseif ($current_page == 'past_registered_doctors') {
        $active = 'past';
    }
}

// ============================================
// BLOOD BANKS
// ============================================
if (in_array('blood-banks', $url_parts) || in_array('blood-bank', $url_parts)) {
    $open = 'blood-banks';
    $active = 'list';
}

// ============================================
// LABORATORIES
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
?>

<style>
/* ============================================
   ADVANCED SIDEBAR REDESIGN
   ============================================ */
.main-sidebar .sidebar {
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    padding: 8px 0 30px 0;
}

.main-sidebar .sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

/* Section Level Labels */
.main-sidebar .nav-level {
    color: #64748b;
    font-size: 10.5px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    font-weight: 700;
    padding: 22px 22px 10px 22px;
    margin: 0;
    opacity: 0.75;
}

/* Menu Item Base */
.main-sidebar .sidebar-menu > li {
    position: relative;
    margin: 3px 10px;
    border-radius: 12px;
    transition: all 0.25s ease;
    overflow: hidden;
}

.main-sidebar .sidebar-menu > li > a {
    display: flex !important;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: bold;
    border-radius: 12px;
    text-decoration: none !important;
    transition: all 0.25s ease;
    position: relative;
    z-index: 1;
}

/* Left Accent Bar */
.main-sidebar .sidebar-menu > li > a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%) scaleY(0);
    width: 3px;
    height: 60%;
    background: linear-gradient(180deg, #6366f1, #8b5cf6);
    border-radius: 0 4px 4px 0;
    transition: transform 0.3s ease;
}

/* Hover */
.main-sidebar .sidebar-menu > li > a:hover {
    background: rgba(99, 102, 241, 0.08);
    color: #fff !important;
    transform: translateX(3px);
}

.main-sidebar .sidebar-menu > li > a:hover::before {
    transform: translateY(-50%) scaleY(1);
}

/* Active State */
.main-sidebar .sidebar-menu > li.active > a {
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.18) 0%, rgba(139, 92, 246, 0.08) 100%);
    color: #fff !important;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.15);
}

.main-sidebar .sidebar-menu > li.active > a::before {
    transform: translateY(-50%) scaleY(1);
}

/* Icons */
.main-sidebar .sidebar-menu > li > a > i:first-child {
    font-size: 17px;
    width: 22px;
    text-align: center;
    color: #94a3b8;
    transition: all 0.25s ease;
    flex-shrink: 0;
}

.main-sidebar .sidebar-menu > li > a:hover > i:first-child,
.main-sidebar .sidebar-menu > li.active > a > i:first-child {
    color: #a5b4fc;
    transform: scale(1.1);
}

/* Arrow icon (dropdown) */
.main-sidebar .sidebar-menu > li > a > .icon-arrow-down {
    margin-left: auto;
    font-size: 12px;
    transition: transform 0.3s ease;
    color: #64748b;
}

.main-sidebar .sidebar-menu > li.active > a > .icon-arrow-down {
    transform: rotate(180deg);
    color: #a5b4fc;
}

/* ============================================
   TREEVIEW SUBMENU
   ============================================ */
.main-sidebar .treeview-menu {
    list-style: none;
    padding: 6px 0 4px 0;
    margin: 0;
    background: transparent;
    position: relative;
}

.main-sidebar .treeview-menu::before {
    content: '';
    position: absolute;
    left: 28px;
    top: 6px;
    bottom: 6px;
    width: 1.5px;
    background: linear-gradient(180deg, rgba(99, 102, 241, 0.4), rgba(139, 92, 246, 0.15));
    border-radius: 2px;
}

.main-sidebar .treeview-menu > li {
    margin: 2px 0;
    padding-left: 14px;
}

.main-sidebar .treeview-menu > li > a {
    display: flex !important;
    align-items: center;
    gap: 10px;
    padding: 9px 14px 9px 18px;
    color: #94a3b8 !important;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none !important;
    transition: all 0.22s ease;
    position: relative;
}

.main-sidebar .treeview-menu > li > a > i {
    font-size: 12px;
    color: #64748b;
    transition: all 0.22s ease;
}

.main-sidebar .treeview-menu > li > a:hover {
    background: rgba(99, 102, 241, 0.1);
    color: #e2e8f0 !important;
    transform: translateX(4px);
}

.main-sidebar .treeview-menu > li > a:hover > i {
    color: #a5b4fc;
}

.main-sidebar .treeview-menu > li.active > a {
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.22) 0%, rgba(139, 92, 246, 0.06) 100%);
    color: #fff !important;
    font-weight: 600;
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.2);
}

.main-sidebar .treeview-menu > li.active > a > i {
    color: #a5b4fc;
}

.main-sidebar .treeview-menu > li.active > a::after {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 55%;
    background: linear-gradient(180deg, #6366f1, #8b5cf6);
    border-radius: 0 4px 4px 0;
}

/* ============================================
   LOGOUT SPECIAL
   ============================================ */
.main-sidebar .sidebar-menu > li.logout-item > a {
    color: #f87171 !important;
}

.main-sidebar .sidebar-menu > li.logout-item > a:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #fca5a5 !important;
}

.main-sidebar .sidebar-menu > li.logout-item > a > i:first-child {
    color: #ef4444;
}

.main-sidebar .sidebar-menu > li.logout-item > a:hover > i:first-child {
    color: #fca5a5;
}

/* ============================================
   SCROLLBAR
   ============================================ */
.main-sidebar .sidebar::-webkit-scrollbar {
    width: 5px;
}
.main-sidebar .sidebar::-webkit-scrollbar-track {
    background: transparent;
}
.main-sidebar .sidebar::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.3);
    border-radius: 10px;
}
.main-sidebar .sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(99, 102, 241, 0.5);
}
</style>

<aside class="main-sidebar hidden-print">
    <section class="sidebar" id="sidebar-scroll">
        <ul class="sidebar-menu">
            <li class="nav-level">Hospital Panel</li>
            
            <!-- DASHBOARD -->
            <li class="treeview <?php echo ($open == '' && $active == '') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/">
                    <i class="icon-speedometer"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- DOCTORS (With Add + List + Past Registered) -->
            <li class="treeview <?php echo ($open == 'doctors') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-user-md"></i>
                    <span>Doctors</span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu" <?php echo ($open == 'doctors') ? 'style="display:block;"' : ''; ?>>
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
                    <li class="<?php echo ($active == 'past') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/past_registered_doctors.php">
                            <i class="icon-refresh"></i> Past Registered Doctors
                        </a>
                    </li>
                </ul>
            </li>

            <!-- BLOOD BANKS (Direct Link) -->
            <li class="treeview <?php echo ($open == 'blood-banks') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/blood-banks">
                    <i class="icon-drop"></i>
                    <span>Blood Banks</span>
                </a>
            </li>

            <!-- LABORATORIES (Direct Link) -->
            <li class="treeview <?php echo ($open == 'labs') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/labs">
                    <i class="icon-flask"></i>
                    <span>Laboratories</span>
                </a>
            </li>

            <!-- BEDS -->
            <li class="treeview <?php echo ($open == 'beds') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/beds">
                    <i class="icon-bed"></i>
                    <span>Beds</span>
                </a>
            </li>

            <!-- FACILITIES -->
            <li class="treeview <?php echo ($open == 'facilities') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/facilities">
                    <i class="icon-grid"></i>
                    <span>Facilities</span>
                </a>
            </li>

            <!-- FEEDBACK -->
            <li class="treeview <?php echo ($open == 'feedback') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/feedback">
                    <i class="icon-star"></i>
                    <span>Feedback</span>
                </a>
            </li>

            <!-- RECYCLE BIN -->
            <li class="treeview <?php echo ($open == 'recycle') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/recycle">
                    <i class="icon-trash"></i>
                    <span style="color: #f87171;">Recycle Bin</span>
                </a>
            </li>

            <!-- PROFILE -->
            <li class="treeview <?php echo ($open == 'profile') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/profile">
                    <i class="icon-user"></i>
                    <span>Profile</span>
                </a>
            </li>

            <!-- SEPARATOR -->
            <li class="nav-level">Account</li>

            <!-- LOGOUT -->
            <li class="treeview logout-item">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>logout">
                    <i class="icon-logout"></i>
                    <span>Logout</span>
                </a>
            </li>

        </ul>
    </section>
</aside>