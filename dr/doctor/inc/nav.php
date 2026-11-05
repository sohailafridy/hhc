<?php
// ── ACTIVE STATE DETECTION ─────────────────────────────────
$current_url  = strtok($_SERVER['REQUEST_URI'], '?');
$url_parts    = explode('/', trim($current_url, '/'));
$current_page = str_replace('.php', '', end($url_parts));

$open = '';

if ($current_page === 'index' || $current_page === '' || $current_page === 'doctor') {
    $open = 'dashboard';
} elseif (in_array('feedback', $url_parts)) {
    $open = 'feedback';
} elseif (in_array('profile', $url_parts)) {
    $open = 'profile';
} elseif (in_array('labs', $url_parts) || in_array('laboratories', $url_parts)) {
    $open = 'labs';
} elseif (in_array('blood-banks', $url_parts) || in_array('blood-bank', $url_parts)) {
    $open = 'blood-banks';
}
?>
<style>
/* ── Doctor Sidebar ───────────────────────────────────────── */
.main-sidebar .sidebar {
    background: linear-gradient(180deg, #071124 0%, #0D2B6E 60%, #1C3F8F 100%);
    padding: 8px 0 30px;
}

.main-sidebar .sidebar-menu { list-style:none; padding:0; margin:0; }

.main-sidebar .nav-level {
    color: rgba(255,255,255,0.35);
    font-size: 10px; letter-spacing: 1.8px;
    text-transform: uppercase; font-weight: 700;
    padding: 22px 22px 8px; margin: 0;
}

/* Item */
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

.main-sidebar .sidebar-menu > li > a:hover {
    background: rgba(0,168,204,0.1);
    color: #fff !important;
    transform: translateX(3px);
}
.main-sidebar .sidebar-menu > li > a:hover::before { transform: translateY(-50%) scaleY(1); }

/* Active */
.main-sidebar .sidebar-menu > li.active > a {
    background: linear-gradient(90deg, rgba(0,168,204,0.2) 0%, rgba(39,201,106,0.08) 100%);
    color: #fff !important;
    box-shadow: 0 4px 14px rgba(0,168,204,0.15);
}
.main-sidebar .sidebar-menu > li.active > a::before { transform: translateY(-50%) scaleY(1); }

/* Icons */
.main-sidebar .sidebar-menu > li > a > i:first-child {
    font-size: 17px; width: 22px; text-align: center;
    color: rgba(255,255,255,0.4);
    transition: all 0.25s ease; flex-shrink: 0;
}
.main-sidebar .sidebar-menu > li > a:hover > i:first-child,
.main-sidebar .sidebar-menu > li.active > a > i:first-child {
    color: #00A8CC; transform: scale(1.1);
}

/* Logout */
.main-sidebar .sidebar-menu > li.logout-item > a { color: #fca5a5 !important; }
.main-sidebar .sidebar-menu > li.logout-item > a:hover { background: rgba(239,68,68,0.12); color: #fca5a5 !important; }
.main-sidebar .sidebar-menu > li.logout-item > a > i:first-child { color: #ef4444; }

/* Scrollbar */
.main-sidebar .sidebar::-webkit-scrollbar { width: 4px; }
.main-sidebar .sidebar::-webkit-scrollbar-thumb { background: rgba(0,168,204,0.3); border-radius: 10px; }
</style>

<aside class="main-sidebar hidden-print">
    <section class="sidebar" id="sidebar-scroll">
        <ul class="sidebar-menu">

            <li class="nav-level">Doctor Panel</li>

            <!-- Dashboard -->
            <li class="treeview <?= $open === 'dashboard' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>doctor/">
                    <i class="icon-speedometer"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- My Profile -->
            <li class="treeview <?= $open === 'profile' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>doctor/profile">
                    <i class="icon-user"></i>
                    <span>My Profile</span>
                </a>
            </li>

            <!-- Feedback / Reviews -->
            <li class="treeview <?= $open === 'feedback' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>doctor/feedback">
                    <i class="icon-star"></i>
                    <span>Patient Reviews</span>
                </a>
            </li>

            <!-- Laboratories -->
            <li class="treeview <?= $open === 'labs' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>doctor/labs">
                    <i class="icon-flask"></i>
                    <span>Laboratories</span>
                </a>
            </li>

            <!-- Blood Banks -->
            <li class="treeview <?= $open === 'blood-banks' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>doctor/blood-banks">
                    <i class="icon-drop"></i>
                    <span>Blood Banks</span>
                </a>
            </li>

            <li class="nav-level">Account</li>

            <!-- Logout -->
            <li class="treeview logout-item">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>logout">
                    <i class="icon-logout"></i>
                    <span>Logout</span>
                </a>
            </li>

        </ul>
    </section>
</aside>
