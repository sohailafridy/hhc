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
    } elseif (in_array('import-doctors', $url_parts)) {
        $active = 'import-doctors';
    } elseif (in_array('import-all', $url_parts)) {
        $active = 'import-all';
    } elseif (in_array('import-cities', $url_parts)) {
        $active = 'import-cities';
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
// RADIOLOGY  ← NEW MODULE
// ============================================
if (in_array('radiology', $url_parts)) {
    $open = 'radiology';
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
    } elseif ($user_type_param == 'radiology') {
        $active = 'radiology';
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
// UTILITIES (Delete All Clinics etc.)
// ============================================
if (in_array('delete-all-clinics', $url_parts)) {
    $open = 'utilities';
    $active = 'delete-all-clinics';
}

// ============================================
// CONTACTED USERS
// ============================================
$is_contacted = (strpos($current_url, 'contacted_users') !== false);
if ($is_contacted) {
    $open   = 'contacted';
    $active = 'contacted';
}
$current_page = end($url_parts);
$current_page = str_replace('.php', '', $current_page);

if (($current_page == 'admin' || $current_page == 'index' || $current_page == '') 
    && $open == '' && $active == '') {
    $is_dashboard = true;
}
?>

<style>
/* ── SehatNexa Admin Sidebar — Brand Colors ───────────────────
   #1C3F8F navy | #00A8CC teal | #27C96A green | #0D2B6E dark
────────────────────────────────────────────────────────────── */
.main-sidebar .sidebar {
    background: linear-gradient(180deg, #071124 0%, #0D2B6E 60%, #1C3F8F 100%);
    padding: 8px 0 30px;
}

.main-sidebar .sidebar-menu { list-style: none; padding: 0; margin: 0; }

/* Section labels */
.main-sidebar .nav-level {
    color: rgba(255,255,255,0.3);
    font-size: 10px; letter-spacing: 1.8px;
    text-transform: uppercase; font-weight: 700;
    padding: 22px 22px 8px; margin: 0;
}

/* Menu item */
.main-sidebar .sidebar-menu > li {
    position: relative;
    margin: 3px 10px;
    border-radius: 12px;
    background: transparent !important;
    transition: all 0.25s ease;
    overflow: hidden;
}

.main-sidebar .sidebar-menu > li > a {
    display: flex !important;
    align-items: center; gap: 14px;
    padding: 12px 16px;
    font-size: 14px; font-weight: 600;
    border-radius: 12px;
    text-decoration: none !important;
    color: rgba(255,255,255,0.82) !important;
    background: transparent !important;
    transition: all 0.25s ease;
    position: relative; z-index: 1;
}

/* Accent bar */
.main-sidebar .sidebar-menu > li > a::before {
    content: '';
    position: absolute; left: 0; top: 50%;
    transform: translateY(-50%) scaleY(0);
    width: 3px; height: 60%;
    background: linear-gradient(180deg, #00A8CC, #27C96A);
    border-radius: 0 4px 4px 0;
    transition: transform 0.3s ease;
}

/* Hover */
.main-sidebar .sidebar-menu > li > a:hover {
    background: rgba(0,168,204,0.1) !important;
    color: #fff !important;
    transform: translateX(3px);
}
.main-sidebar .sidebar-menu > li > a:hover::before { transform: translateY(-50%) scaleY(1); }

/* Active */
.main-sidebar .sidebar-menu > li.active > a {
    background: linear-gradient(90deg, rgba(0,168,204,0.2) 0%, rgba(39,201,106,0.08) 100%) !important;
    color: #fff !important;
    font-weight: 700;
    box-shadow: 0 4px 14px rgba(0,168,204,0.15);
}
.main-sidebar .sidebar-menu > li.active > a::before { transform: translateY(-50%) scaleY(1); }

/* Icons */
.main-sidebar .sidebar-menu > li > a > i:first-child {
    font-size: 17px; width: 22px; text-align: center;
    color: rgba(255,255,255,0.45);
    transition: all 0.25s ease; flex-shrink: 0;
}
.main-sidebar .sidebar-menu > li > a:hover > i:first-child,
.main-sidebar .sidebar-menu > li.active > a > i:first-child {
    color: #00A8CC; transform: scale(1.1);
}

/* Arrow dropdown icon */
.main-sidebar .sidebar-menu > li > a > .icon-arrow-down {
    margin-left: auto; font-size: 12px;
    transition: transform 0.3s ease;
    color: rgba(255,255,255,0.3);
}
.main-sidebar .sidebar-menu > li.active > a > .icon-arrow-down {
    transform: rotate(180deg); color: #00A8CC;
}

/* ── Submenu ──────────────────────────────────────────────── */
.main-sidebar .treeview-menu {
    list-style: none; padding: 6px 0 4px; margin: 0;
    background: transparent; position: relative;
}

.main-sidebar .treeview-menu::before {
    content: '';
    position: absolute; left: 28px; top: 6px; bottom: 6px;
    width: 1.5px;
    background: linear-gradient(180deg, rgba(0,168,204,0.4), rgba(39,201,106,0.15));
    border-radius: 2px;
}

.main-sidebar .treeview-menu > li { margin: 2px 0; padding-left: 14px; }

.main-sidebar .treeview-menu > li > a {
    display: flex !important; align-items: center; gap: 10px;
    padding: 9px 14px 9px 18px;
    color: rgba(255,255,255,0.6) !important;
    font-size: 13px; font-weight: 500;
    border-radius: 8px;
    text-decoration: none !important;
    background: transparent !important;
    transition: all 0.22s ease; position: relative;
}

.main-sidebar .treeview-menu > li > a > i {
    font-size: 12px; color: rgba(255,255,255,0.35);
    transition: all 0.22s ease;
}

.main-sidebar .treeview-menu > li > a:hover {
    background: rgba(0,168,204,0.1) !important;
    color: #fff !important; transform: translateX(4px);
}
.main-sidebar .treeview-menu > li > a:hover > i { color: #00A8CC; }

.main-sidebar .treeview-menu > li.active > a {
    background: linear-gradient(90deg, rgba(0,168,204,0.22) 0%, rgba(39,201,106,0.06) 100%) !important;
    color: #fff !important; font-weight: 600;
    box-shadow: inset 0 0 0 1px rgba(0,168,204,0.2);
}
.main-sidebar .treeview-menu > li.active > a > i { color: #00A8CC; }

.main-sidebar .treeview-menu > li.active > a::after {
    content: '';
    position: absolute; left: 0; top: 50%;
    transform: translateY(-50%);
    width: 3px; height: 55%;
    background: linear-gradient(180deg, #00A8CC, #27C96A);
    border-radius: 0 4px 4px 0;
}

/* ── Contacted Users ──────────────────────────────────────── */
.main-sidebar .sidebar-menu > li.contacted-item > a > i:first-child { color: #00A8CC; }
.main-sidebar .sidebar-menu > li.contacted-item > a:hover { background: rgba(0,168,204,0.12) !important; }
.main-sidebar .sidebar-menu > li.contacted-item.active > a {
    background: linear-gradient(90deg, rgba(0,168,204,0.2) 0%, rgba(0,168,204,0.05) 100%) !important;
    box-shadow: 0 4px 14px rgba(0,168,204,0.15);
}
.main-sidebar .sidebar-menu > li.contacted-item.active > a::before {
    background: linear-gradient(180deg, #00A8CC, #27C96A);
}

/* Unread badge */
.nav-unread-badge {
    margin-left: auto;
    background: linear-gradient(135deg, #ef4444, #f87171);
    color: white; font-size: 10px; font-weight: 800;
    padding: 2px 7px; border-radius: 20px;
    min-width: 20px; text-align: center; line-height: 1.6;
    box-shadow: 0 2px 8px rgba(239,68,68,0.4);
    animation: badgePulse 2s ease-in-out infinite;
}

@keyframes badgePulse {
    0%, 100% { transform: scale(1); }
    50%       { transform: scale(1.1); }
}

/* ── Recycle Bin ──────────────────────────────────────────── */
.main-sidebar .sidebar-menu > li.recycle-item > a > i:first-child { color: #ef4444; }
.main-sidebar .sidebar-menu > li.recycle-item > a:hover { background: rgba(239,68,68,0.1) !important; }
.main-sidebar .sidebar-menu > li.recycle-item.active > a {
    background: linear-gradient(90deg, rgba(239,68,68,0.18) 0%, rgba(239,68,68,0.05) 100%) !important;
    box-shadow: 0 4px 14px rgba(239,68,68,0.15);
}
.main-sidebar .sidebar-menu > li.recycle-item.active > a::before {
    background: linear-gradient(180deg, #ef4444, #f87171);
}

/* ── Utility item ─────────────────────────────────────────── */
.main-sidebar .sidebar-menu > li.utility-item > a > i:first-child { color: #f59e0b; }
.main-sidebar .sidebar-menu > li.utility-item > a > span { color: #fbbf24 !important; }
.main-sidebar .sidebar-menu > li.utility-item > a:hover { background: rgba(245,158,11,0.1) !important; }
.main-sidebar .sidebar-menu > li.utility-item.active > a {
    background: linear-gradient(90deg, rgba(245,158,11,0.18) 0%, rgba(245,158,11,0.05) 100%) !important;
    box-shadow: 0 4px 14px rgba(245,158,11,0.15);
}

/* ── Logout ───────────────────────────────────────────────── */
.main-sidebar .sidebar-menu > li.logout-item > a { color: #fca5a5 !important; }
.main-sidebar .sidebar-menu > li.logout-item > a:hover { background: rgba(239,68,68,0.12) !important; color: #fca5a5 !important; }
.main-sidebar .sidebar-menu > li.logout-item > a > i:first-child { color: #ef4444; }

/* ── Scrollbar ────────────────────────────────────────────── */
.main-sidebar .sidebar::-webkit-scrollbar { width: 4px; }
.main-sidebar .sidebar::-webkit-scrollbar-thumb {
    background: rgba(0,168,204,0.3); border-radius: 10px;
}
.main-sidebar .sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(0,168,204,0.5);
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
                    <!-- ✅ NAYA LINK: Import Cities -->
                    <li class="<?php echo ($active == 'import-cities') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/import-cities">
                            <i class="icon-cloud-upload"></i> Import Cities
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
                    <!-- ✅ NAYA LINK: Import Doctors -->
                    <li class="<?php echo ($active == 'import-doctors') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/import-doctors">
                            <i class="icon-cloud-upload"></i> Import Doctors
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
            <!-- ===== RADIOLOGY (NEW MODULE) ===== -->
            <!-- ========================================== -->
            <li class="treeview <?php echo ($open == 'radiology') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="#!">
                    <i class="icon-book-open"></i>
                    <span>Radiology</span>
                    <i class="icon-arrow-down"></i>
                </a>
                <ul class="treeview-menu" <?php echo ($open == 'radiology') ? 'style="display:block;"' : ''; ?>>
                    <li class="<?php echo ($active == 'add') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/radiology/add">
                            <i class="icon-plus"></i> New
                        </a>
                    </li>
                    <li class="<?php echo ($active == 'list') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/radiology/list">
                            <i class="icon-arrow-right"></i> List
                        </a>
                    </li>
                </ul>
            </li>

            <li class="<?php echo ($active == 'import-all') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/import-all">
                            <i class="icon-cloud-upload"></i> Import All
                        </a>
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
                    <li class="<?php echo ($active == 'radiology') ? 'active' : ''; ?>">
                        <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/users?type=radiology">
                            <i class="icon-arrow-right"></i> Radiology
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
            <!-- ===== CONTACTED USERS ===== -->
            <!-- ========================================== -->
            <li class="treeview contacted-item <?php echo $is_contacted ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>admin/contacted_users.php">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>Contacted Users</span>
                    <?php
                    // Unread badge
                    $unread_nav = mysqli_query($con, "SELECT COUNT(*) as c FROM contact WHERE status = 0");
                    $unread_nav_cnt = mysqli_fetch_assoc($unread_nav)['c'] ?? 0;
                    if ($unread_nav_cnt > 0):
                    ?>
                    <span class="nav-unread-badge"><?= $unread_nav_cnt ?></span>
                    <?php endif; ?>
                </a>
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

            <li class="treeview recycle-item">
                <a class="waves-effect waves-dark" href="https://pmdc.pk/" target="_blank">
                    <i class="icon-trash"></i>
                    <span>PMDC Doctor Check</span>
                </a>
            </li>

            <!-- ========================================== -->
            <!-- ===== UTILITIES (Danger Zone) ===== -->
            <!-- ========================================== -->
            <li class="nav-level">Utilities</li>

            <!-- ===== DELETE ALL CLINICS ===== -->
            <li class="treeview utility-item <?php echo ($active == 'delete-all-clinics') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark"
                   href="<?php echo BASE_URL; ?>admin/delete-all-clinics"
                   onclick="return confirm('⚠️ WARNING\n\nYe page kholne par ALL doctors ki clinic data delete ho sakti hai.\n\nKya aap waqai kholein?');">
                    <i class="fas fa-broom"></i>
                    <span>Delete All Clinics</span>
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