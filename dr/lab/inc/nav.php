<?php
// ── ACTIVE STATE DETECTION ─────────────────────────────────
$current_url  = strtok($_SERVER['REQUEST_URI'], '?');
$url_parts    = explode('/', trim($current_url, '/'));
$current_page = str_replace('.php', '', end($url_parts));

$open = '';
if ($current_page === 'index' || $current_page === '' || $current_page === 'lab') {
    $open = 'dashboard';
} elseif (in_array('feedback', $url_parts)) {
    $open = 'feedback';
} elseif (in_array('profile', $url_parts)) {
    $open = 'profile';
} elseif (in_array('blood-banks', $url_parts)) {
    $open = 'blood-banks';
} elseif (in_array('hospitals', $url_parts)) {
    $open = 'hospitals';
} elseif (in_array('labs', $url_parts)) {
    $open = 'labs';
}
?>
<style>
/* ── Lab Sidebar ──────────────────────────────────────────── */
.main-sidebar .sidebar {
    background: linear-gradient(180deg, #071124 0%, #0D2B6E 40%, #00897B 100%);
    padding: 8px 0 30px;
}

.main-sidebar .sidebar-menu { list-style:none; padding:0; margin:0; }

.main-sidebar .nav-level {
    color: rgba(255,255,255,0.32);
    font-size: 10px; letter-spacing: 1.8px;
    text-transform: uppercase; font-weight: 700;
    padding: 22px 22px 8px; margin: 0;
}

.main-sidebar .sidebar-menu > li {
    position: relative; margin: 3px 10px;
    border-radius: 12px; background: transparent !important;
    transition: all 0.25s ease; overflow: hidden;
}

.main-sidebar .sidebar-menu > li > a {
    display: flex !important; align-items: center; gap: 14px;
    padding: 12px 16px; font-size: 14px; font-weight: 600;
    border-radius: 12px; text-decoration: none !important;
    color: rgba(255,255,255,0.82) !important;
    background: transparent !important;
    transition: all 0.25s ease; position: relative; z-index: 1;
}

.main-sidebar .sidebar-menu > li > a::before {
    content: ''; position: absolute; left: 0; top: 50%;
    transform: translateY(-50%) scaleY(0);
    width: 3px; height: 60%;
    background: linear-gradient(180deg, #27C96A, #00C9A7);
    border-radius: 0 4px 4px 0; transition: transform 0.3s ease;
}

.main-sidebar .sidebar-menu > li > a:hover {
    background: rgba(39,201,106,0.12) !important;
    color: #fff !important; transform: translateX(3px);
}
.main-sidebar .sidebar-menu > li > a:hover::before { transform: translateY(-50%) scaleY(1); }

.main-sidebar .sidebar-menu > li.active > a {
    background: linear-gradient(90deg, rgba(39,201,106,0.22) 0%, rgba(0,201,167,0.08) 100%) !important;
    color: #fff !important;
    box-shadow: 0 4px 14px rgba(39,201,106,0.18);
}
.main-sidebar .sidebar-menu > li.active > a::before { transform: translateY(-50%) scaleY(1); }

.main-sidebar .sidebar-menu > li > a > i:first-child {
    font-size: 17px; width: 22px; text-align: center;
    color: rgba(255,255,255,0.4);
    transition: all 0.25s ease; flex-shrink: 0;
}
.main-sidebar .sidebar-menu > li > a:hover > i:first-child,
.main-sidebar .sidebar-menu > li.active > a > i:first-child {
    color: #27C96A; transform: scale(1.1);
}

.main-sidebar .sidebar-menu > li.logout-item > a { color: #fca5a5 !important; }
.main-sidebar .sidebar-menu > li.logout-item > a:hover { background: rgba(239,68,68,0.12) !important; }
.main-sidebar .sidebar-menu > li.logout-item > a > i:first-child { color: #ef4444; }

.main-sidebar .sidebar::-webkit-scrollbar { width: 4px; }
.main-sidebar .sidebar::-webkit-scrollbar-thumb { background: rgba(39,201,106,0.3); border-radius: 10px; }
</style>

<aside class="main-sidebar hidden-print">
    <section class="sidebar" id="sidebar-scroll">
        <ul class="sidebar-menu">

            <li class="nav-level">Lab Panel</li>

            <li class="treeview <?= $open === 'dashboard' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>lab/">
                    <i class="icon-speedometer"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="treeview <?= $open === 'profile' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>lab/profile">
                    <i class="icon-chemistry"></i>
                    <span>Lab Profile</span>
                </a>
            </li>

            <li class="treeview <?= $open === 'labs' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>lab/labs">
                    <i class="icon-chemistry"></i>
                    <span>Other Labs</span>
                </a>
            </li>

            <li class="treeview <?= $open === 'feedback' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>lab/feedback">
                    <i class="icon-star"></i>
                    <span>Patient Reviews</span>
                </a>
            </li>
            <?php /*
            <li class="treeview <?= $open === 'hospitals' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>lab/hospitals">
                    <i class="icon-home"></i>
                    <span>Hospitals</span>
                </a>
            </li>
                   */ ?>
            <li class="treeview <?= $open === 'blood-banks' ? 'active' : '' ?>">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>lab/blood-banks">
                    <i class="icon-drop"></i>
                    <span>Blood Banks</span>
                </a>
            </li>

            <li class="nav-level">Account</li>

            <li class="treeview logout-item">
                <a class="waves-effect waves-dark" href="<?= BASE_URL ?>logout">
                    <i class="icon-logout"></i>
                    <span>Logout</span>
                </a>
            </li>

        </ul>
    </section>
</aside>
