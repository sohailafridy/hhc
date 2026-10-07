<?php 
// ============================================
// GET CURRENT URL AND SET ACTIVE STATES
// ============================================
$current_url = strtok($_SERVER['REQUEST_URI'], '?');
$url_parts = explode('/', trim($current_url, '/'));
$full_url_with_query = $_SERVER['REQUEST_URI'];

// Initialize variables
$active = '';
$open = '';
$user_type_param = '';
$in_users_section = false;
$is_dashboard = false;

// Check if we're in users section
if (in_array('users', $url_parts)) {
    $in_users_section = true;
}

// Check for user type in URL parameters (GET)
if (strpos($full_url_with_query, '?type=') !== false) {
    parse_str(parse_url($full_url_with_query, PHP_URL_QUERY), $query_params);
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
// USERS
// ============================================
if ($in_users_section) {
    $open = 'users';
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
// DASHBOARD (Default — only when nothing else active)
// ============================================
$current_page = end($url_parts);
$current_page = str_replace('.php', '', $current_page);

if (($current_page == 'admin' || $current_page == 'index' || $current_page == '') 
    && $open == '' && $active == '') {
    $is_dashboard = true;
}
?>

<style>
/* ============================================
   ADVANCED ADMIN SIDEBAR REDESIGN
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
   RECYCLE BIN SPECIAL
   ============================================ */
.main-sidebar .sidebar-menu > li.recycle-item > a > span {
    color: #f87171 !important;
}

.main-sidebar .sidebar-menu > li.recycle-item > a > i:first-child {
    color: #ef4444;
}

.main-sidebar .sidebar-menu > li.recycle-item > a:hover {
    background: rgba(239, 68, 68, 0.1);
}

.main-sidebar .sidebar-menu > li.recycle-item > a:hover > i:first-child {
    color: #fca5a5;
}

.main-sidebar .sidebar-menu > li.recycle-item.active > a {
    background: linear-gradient(90deg, rgba(239, 68, 68, 0.18) 0%, rgba(239, 68, 68, 0.05) 100%);
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.15);
}

.main-sidebar .sidebar-menu > li.recycle-item.active > a::before {
    background: linear-gradient(180deg, #ef4444, #f87171);
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
            <li class="nav-level">Navigation</li>
            
            <!-- ===== DASHBOARD ===== -->
            <li class="treeview <?php echo $is_dashboard ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin">
                    <i class="icon-speedometer"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- ===== CITIES ===== -->
            <li class="treeview <?php echo ($open == 'cities') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-book-open"></i>
                    <span>Cities</span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu" <?php echo ($open == 'cities') ? 'style="display:block;"' : ''; ?>>
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/cities/add">
                            <i class="icon-plus"></i> New
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
                    <span>Hospitals</span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu" <?php echo ($open == 'hospitals') ? 'style="display:block;"' : ''; ?>>
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/hospitals/add">
                            <i class="icon-plus"></i> New
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
                    <span>Doctors</span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu" <?php echo ($open == 'doctors') ? 'style="display:block;"' : ''; ?>>
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/doctors/add">
                            <i class="icon-plus"></i> New
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
                    <span>Laboratories</span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu" <?php echo ($open == 'laboratories') ? 'style="display:block;"' : ''; ?>>
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/laboratories/add">
                            <i class="icon-plus"></i> New
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
                    <span>Blood Banks</span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu" <?php echo ($open == 'blood-banks') ? 'style="display:block;"' : ''; ?>>
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/blood-banks/add">
                            <i class="icon-plus"></i> New
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
                    <span>Users</span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu" <?php echo ($open == 'users') ? 'style="display:block;"' : ''; ?>>
                    <li class="<?php echo ($active == 'all') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users">
                            <i class="icon-arrow-right"></i> All Users
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'admin') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=admin">
                            <i class="icon-arrow-right"></i> Admins
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'doctor') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=doctor">
                            <i class="icon-arrow-right"></i> Doctors
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'hospital') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=hospital">
                            <i class="icon-arrow-right"></i> Hospitals
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'lab') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=lab">
                            <i class="icon-arrow-right"></i> Laboratories
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'blood_bank') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=blood_bank">
                            <i class="icon-arrow-right"></i> Blood Banks
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'fixit') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=fixit">
                            <i class="icon-arrow-right"></i> Fixit Users
                        </a>
                    </li>
                </ul>
            </li>

            <!-- ========================================== -->
            <!-- ===== RECYCLE BIN (Direct Link) ===== -->
            <!-- ========================================== -->
            <li class="treeview recycle-item <?php echo ($open == 'recycle') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/recycle">
                    <i class="icon-trash"></i>
                    <span>Recycle Bin</span>
                </a>
            </li>

            <!-- ========================================== -->
            <!-- ===== MORE / LOGOUT ===== -->
            <!-- ========================================== -->
            <li class="nav-level">Account</li>
            <li class="treeview logout-item">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>logout">
                    <i class="icon-logout"></i>
                    <span>Logout</span>
                </a>
            </li>

        </ul>
    </section>
</aside>