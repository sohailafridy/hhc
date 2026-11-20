<?php 
// ============================================
// BRAND COLORS
// ============================================
$colors_file = __DIR__ . '/colors.php';
if (!file_exists($colors_file)) {
    $colors_file = $_SERVER['DOCUMENT_ROOT'] . '/includes/colors.php';
}
if (file_exists($colors_file)) {
    require_once $colors_file;
}

$color1 = $color1 ?? '#1C3F8F';
$color2 = $color2 ?? '#00A8CC';
$color3 = $color3 ?? '#27C96A';
$color4 = $color4 ?? '#00C9A7';
$color5 = $color5 ?? '#0D2B6E';
$color6 = $color6 ?? '#E8F7FF';
$color7 = $color7 ?? '#F0FFF8';
$color8 = $color8 ?? '#FFFFFF';
$gradient_primary = $gradient_primary ?? "linear-gradient(135deg, $color1 0%, $color2 100%)";
$gradient_green   = $gradient_green   ?? "linear-gradient(135deg, $color3 0%, $color4 100%)";
$gradient_full    = $gradient_full    ?? "linear-gradient(135deg, $color1 0%, $color2 50%, $color3 100%)";

// ============================================
// ACTIVE STATE
// ============================================
$current_url = strtok($_SERVER['REQUEST_URI'], '?');
$url_parts   = explode('/', trim($current_url, '/'));
$url_parts   = array_map(function ($p) { return str_replace('.php', '', $p); }, $url_parts);

$active = '';
$open   = '';
$current_page = end($url_parts);

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

$sections = [
    'blood-banks' => ['blood-banks', 'blood-bank'],
    'labs'        => ['labs', 'laboratories'],
    'radiology'   => ['radiology'],
    'feedback'    => ['feedback'],
    'profile'     => ['profile'],
    'beds'        => ['beds'],
    'facilities'  => ['facilities'],
    'recycle'     => ['recycle'],
];
foreach ($sections as $key => $slugs) {
    if (array_intersect($slugs, $url_parts)) {
        $open   = $key;
        $active = 'list';
    }
}

if ($current_page == 'index' || $current_page == '' || $current_page == 'hospital') {
    $open   = '';
    $active = '';
}

// User card data (safe fallbacks)
$sn_name  = $_SESSION['name'] ?? $_SESSION['hospital_name'] ?? $_SESSION['user_name'] ?? 'Hospital';
$sn_name  = htmlspecialchars($sn_name);
$sn_init  = strtoupper(mb_substr($sn_name, 0, 1));
?>

<style>
/* ============================================
   SEHATNEXA SIDEBAR v3 — GLASS + FLOATING PILLS
   ============================================ */
:root {
    --sn-primary:    <?= $color1 ?>;
    --sn-accent:     <?= $color2 ?>;
    --sn-green:      <?= $color3 ?>;
    --sn-teal:       <?= $color4 ?>;
    --sn-dark:       <?= $color5 ?>;
    --sn-light-blue: <?= $color6 ?>;
    --sn-light-green:<?= $color7 ?>;
    --sn-white:      <?= $color8 ?>;
    --sn-grad-primary: <?= $gradient_primary ?>;
    --sn-grad-green:   <?= $gradient_green ?>;
    --sn-grad-full:    <?= $gradient_full ?>;
}

@keyframes snFloat {
    0%,100% { transform: translateY(0); }
    50%     { transform: translateY(-6px); }
}
@keyframes snPulse {
    0%   { box-shadow: 0 0 0 0 rgba(39,201,106,.6); }
    70%  { box-shadow: 0 0 0 8px rgba(39,201,106,0); }
    100% { box-shadow: 0 0 0 0 rgba(39,201,106,0); }
}
@keyframes snSlideIn {
    from { opacity: 0; transform: translateX(-14px); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes snGlow {
    0%,100% { opacity: .55; }
    50%     { opacity: 1; }
}

/* ---------- Container ---------- */
.main-sidebar .sidebar {
    position: relative;
    background:
        radial-gradient(420px 260px at -10% -5%, rgba(0,168,204,.35), transparent 60%),
        radial-gradient(380px 300px at 110% 105%, rgba(39,201,106,.28), transparent 60%),
        linear-gradient(185deg, #0a2260 0%, var(--sn-dark) 45%, #071a4a 100%);
    padding: 0 0 30px 0;
    border-right: 1px solid rgba(255,255,255,.07);
    overflow-x: hidden;
}
.main-sidebar .sidebar::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--sn-grad-full);
    z-index: 5;
}
/* decorative floating orbs */
.main-sidebar .sidebar::after {
    content: '';
    position: absolute;
    right: -40px; bottom: 120px;
    width: 130px; height: 130px;
    border-radius: 50%;
    background: var(--sn-grad-green);
    filter: blur(55px);
    opacity: .25;
    animation: snFloat 6s ease-in-out infinite;
    pointer-events: none;
}

.main-sidebar .sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    position: relative;
    z-index: 2;
}

/* ---------- User card ---------- */
.main-sidebar .sn-user {
    margin: 18px 14px 6px;
    padding: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-radius: 16px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    box-shadow: 0 10px 30px rgba(0,0,0,.25);
    position: relative;
    z-index: 2;
    animation: snSlideIn .5s ease both;
}
.main-sidebar .sn-avatar {
    width: 44px; height: 44px;
    border-radius: 14px;
    background: var(--sn-grad-green);
    color: #fff;
    font-weight: 800;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    flex-shrink: 0;
    box-shadow: 0 6px 16px rgba(39,201,106,.35);
}
.main-sidebar .sn-avatar::after {
    content: '';
    position: absolute;
    right: -3px; bottom: -3px;
    width: 12px; height: 12px;
    border-radius: 50%;
    background: var(--sn-green);
    border: 2px solid var(--sn-dark);
    animation: snPulse 2s infinite;
}
.main-sidebar .sn-user-info { min-width: 0; }
.main-sidebar .sn-user-name {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.main-sidebar .sn-user-role {
    color: var(--sn-teal);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .6px;
    text-transform: uppercase;
    margin-top: 3px;
}

/* ---------- Section label ---------- */
.main-sidebar .nav-level {
    color: #7fb4d6;
    font-size: 10.5px;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-weight: 800;
    padding: 20px 24px 8px;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.main-sidebar .nav-level::before {
    content: '';
    width: 16px; height: 3px;
    border-radius: 3px;
    background: var(--sn-grad-green);
}
.main-sidebar .nav-level::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(255,255,255,.18), transparent);
}

/* ---------- Items ---------- */
.main-sidebar .sidebar-menu > li.treeview {
    position: relative;
    margin: 5px 14px;
    border-radius: 14px;
    animation: snSlideIn .45s ease both;
}
.main-sidebar .sidebar-menu > li.treeview:nth-child(3)  { animation-delay: .05s; }
.main-sidebar .sidebar-menu > li.treeview:nth-child(4)  { animation-delay: .10s; }
.main-sidebar .sidebar-menu > li.treeview:nth-child(5)  { animation-delay: .15s; }
.main-sidebar .sidebar-menu > li.treeview:nth-child(6)  { animation-delay: .20s; }
.main-sidebar .sidebar-menu > li.treeview:nth-child(7)  { animation-delay: .25s; }
.main-sidebar .sidebar-menu > li.treeview:nth-child(8)  { animation-delay: .30s; }
.main-sidebar .sidebar-menu > li.treeview:nth-child(9)  { animation-delay: .35s; }
.main-sidebar .sidebar-menu > li.treeview:nth-child(10) { animation-delay: .40s; }
.main-sidebar .sidebar-menu > li.treeview:nth-child(11) { animation-delay: .45s; }

.main-sidebar .sidebar-menu > li.treeview > a {
    display: flex !important;
    align-items: center;
    gap: 14px;
    padding: 10px 14px 10px 10px;
    font-size: 14px;
    font-weight: 600;
    color: #0c2f6e !important;
    border-radius: 14px;
    text-decoration: none !important;
    position: relative;
    overflow: hidden;
    border: 1px solid transparent;
    transition: all .3s cubic-bezier(.4,0,.2,1);
}

/* animated gradient border ring on hover */
.main-sidebar .sidebar-menu > li.treeview > a:hover {
    background: rgba(255,255,255,.06);
    border-color: rgba(0,168,204,.45);
    color: #fff !important;
    transform: translateX(5px);
    box-shadow: 0 8px 22px rgba(0,0,0,.25);
}

/* shine */
.main-sidebar .sidebar-menu > li.treeview > a::after {
    content: '';
    position: absolute;
    top: 0; left: -90%;
    width: 45%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.14), transparent);
    transform: skewX(-22deg);
    transition: left .7s ease;
}
.main-sidebar .sidebar-menu > li.treeview > a:hover::after { left: 140%; }

/* active pill */
.main-sidebar .sidebar-menu > li.treeview.active > a {
    background: var(--sn-grad-primary);
    color: #fff !important;
    border-color: rgba(255,255,255,.22);
    box-shadow:
        0 12px 26px rgba(0,168,204,.35),
        inset 0 1px 0 rgba(255,255,255,.25);
}
/* active right indicator */
.main-sidebar .sidebar-menu > li.treeview.active > a::before {
    content: '';
    position: absolute;
    right: 0; top: 50%;
    transform: translateY(-50%);
    width: 4px; height: 55%;
    background: var(--sn-grad-green);
    border-radius: 6px 0 0 6px;
    box-shadow: 0 0 12px var(--sn-green);
    animation: snGlow 2s ease-in-out infinite;
}

/* ---------- Icon tile ---------- */
.main-sidebar .sidebar-menu > li.treeview > a > i:first-child {
    width: 38px; height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: var(--sn-accent);
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 12px;
    flex-shrink: 0;
    transition: all .3s ease;
}
.main-sidebar .sidebar-menu > li.treeview > a:hover > i:first-child {
    color: #fff;
    background: var(--sn-grad-green);
    border-color: transparent;
    transform: scale(1.1) rotate(-6deg);
    box-shadow: 0 6px 16px rgba(39,201,106,.4);
}
.main-sidebar .sidebar-menu > li.treeview.active > a > i:first-child {
    color: var(--sn-primary);
    background: #fff;
    border-color: transparent;
    box-shadow: 0 6px 14px rgba(0,0,0,.25);
}

/* arrow */
.main-sidebar .sidebar-menu > li.treeview > a > .icon-arrow-down {
    margin-left: auto;
    width: auto; height: auto;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    font-size: 11px;
    color: #8fb0e8 !important;
    transition: transform .3s ease;
}
.main-sidebar .sidebar-menu > li.treeview.active > a > .icon-arrow-down {
    transform: rotate(180deg);
    color: #fff !important;
}

/* ---------- Submenu ---------- */
.main-sidebar .treeview-menu {
    list-style: none;
    margin: 6px 0 4px;
    padding: 8px 8px 8px 0;
    position: relative;
    background: rgba(0,0,0,.18);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 14px;
}
.main-sidebar .treeview-menu::before {
    content: '';
    position: absolute;
    left: 26px; top: 14px; bottom: 14px;
    width: 2px;
    background: linear-gradient(180deg, var(--sn-accent), var(--sn-green));
    opacity: .5;
    border-radius: 2px;
}
.main-sidebar .treeview-menu > li {
    position: relative;
    margin: 2px 0;
    padding-left: 12px;
}
.main-sidebar .treeview-menu > li::before {
    content: '';
    position: absolute;
    left: 21px; top: 50%;
    width: 10px; height: 10px;
    transform: translateY(-50%);
    border-radius: 50%;
    background: var(--sn-dark);
    border: 2px solid var(--sn-accent);
    z-index: 2;
    transition: all .25s ease;
}
.main-sidebar .treeview-menu > li:hover::before,
.main-sidebar .treeview-menu > li.active::before {
    background: var(--sn-green);
    border-color: var(--sn-green);
    box-shadow: 0 0 0 4px rgba(39,201,106,.22);
}
.main-sidebar .treeview-menu > li > a {
    display: flex !important;
    align-items: center;
    gap: 10px;
    padding: 9px 12px 9px 28px;
    font-size: 13px;
    font-weight: 500;
    color: #a9c0ee !important;
    border-radius: 10px;
    text-decoration: none !important;
    transition: all .25s ease;
}
.main-sidebar .treeview-menu > li > a > i {
    font-size: 12px;
    color: var(--sn-accent);
    transition: all .25s ease;
}
.main-sidebar .treeview-menu > li > a:hover {
    background: rgba(0,168,204,.15);
    color: #fff !important;
    transform: translateX(4px);
}
.main-sidebar .treeview-menu > li.active > a {
    background: linear-gradient(90deg, rgba(39,201,106,.28), rgba(0,201,167,.05));
    color: #fff !important;
    font-weight: 700;
    box-shadow: inset 0 0 0 1px rgba(39,201,106,.32);
}
.main-sidebar .treeview-menu > li.active > a > i { color: var(--sn-green); }

/* ---------- Recycle ---------- */
.main-sidebar .sidebar-menu > li.recycle-item > a > i:first-child {
    color: #ff8f8f;
    background: rgba(239,68,68,.14);
    border-color: rgba(239,68,68,.25);
}
.main-sidebar .sidebar-menu > li.recycle-item > a span { color: #ffadad; }
.main-sidebar .sidebar-menu > li.recycle-item > a:hover {
    border-color: rgba(239,68,68,.5);
}
.main-sidebar .sidebar-menu > li.recycle-item.active > a {
    background: linear-gradient(135deg, #b91c1c, #ef4444);
    box-shadow: 0 12px 26px rgba(239,68,68,.35);
}
.main-sidebar .sidebar-menu > li.recycle-item.active > a span { color: #fff; }

/* ---------- Logout ---------- */
.main-sidebar .sidebar-menu > li.logout-item > a {
    color: #ffb4b4 !important;
    background: rgba(239,68,68,.09);
    border: 1px solid rgba(239,68,68,.28);
}
.main-sidebar .sidebar-menu > li.logout-item > a > i:first-child {
    color: #ef4444;
    background: rgba(239,68,68,.16);
    border-color: transparent;
}
.main-sidebar .sidebar-menu > li.logout-item > a:hover {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: #fff !important;
    border-color: transparent;
    box-shadow: 0 10px 24px rgba(239,68,68,.4);
}
.main-sidebar .sidebar-menu > li.logout-item > a:hover > i:first-child {
    background: rgba(255,255,255,.22);
    color: #fff;
    box-shadow: none;
}

/* ---------- Footer card ---------- */
.main-sidebar .sn-footer {
    margin: 18px 14px 0;
    padding: 14px;
    border-radius: 16px;
    background: var(--sn-grad-full);
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 28px rgba(0,168,204,.3);
    z-index: 2;
}
.main-sidebar .sn-footer::before {
    content: '';
    position: absolute;
    right: -20px; top: -20px;
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,.18);
}
.main-sidebar .sn-footer b {
    display: block;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: .3px;
}
.main-sidebar .sn-footer small {
    display: block;
    margin-top: 3px;
    font-size: 11px;
    opacity: .9;
}

/* ---------- Scrollbar ---------- */
.main-sidebar .sidebar::-webkit-scrollbar { width: 5px; }
.main-sidebar .sidebar::-webkit-scrollbar-track { background: transparent; }
.main-sidebar .sidebar::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, var(--sn-accent), var(--sn-green));
    border-radius: 10px;
}
</style>

<aside class="main-sidebar hidden-print">
    <section class="sidebar" id="sidebar-scroll">

        <!-- USER CARD -->
        <div class="sn-user">
            <div class="sn-avatar"><?php echo $sn_init; ?></div>
            <div class="sn-user-info">
                <div class="sn-user-name"><?php echo $sn_name; ?></div>
                <div class="sn-user-role">Hospital Admin</div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="nav-level">Main Menu</li>

            <!-- DASHBOARD -->
            <li class="treeview <?php echo ($open == '' && $active == '') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/">
                    <i class="icon-speedometer"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- DOCTORS -->
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

            <!-- BLOOD BANKS -->
            <li class="treeview <?php echo ($open == 'blood-banks') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/blood-banks">
                    <i class="icon-drop"></i>
                    <span>Blood Banks</span>
                </a>
            </li>

            <!-- LABORATORIES -->
            <li class="treeview <?php echo ($open == 'labs') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/labs">
                    <i class="icon-flask"></i>
                    <span>Laboratories</span>
                </a>
            </li>

            <!-- RADIOLOGY -->
            <li class="treeview <?php echo ($open == 'radiology') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/radiology">
                    <i class="icon-camera"></i>
                    <span>Radiology</span>
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
            <li class="treeview recycle-item <?php echo ($open == 'recycle') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/recycle">
                    <i class="icon-trash"></i>
                    <span>Recycle Bin</span>
                </a>
            </li>

            <li class="nav-level">Account</li>

            <!-- PROFILE -->
            <li class="treeview <?php echo ($open == 'profile') ? 'active' : ''; ?>">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>hospital/profile">
                    <i class="icon-user"></i>
                    <span>Profile</span>
                </a>
            </li>

            <!-- LOGOUT -->
            <li class="treeview logout-item">
                <a class="waves-effect waves-dark" href="<?php echo BASE_URL; ?>logout">
                    <i class="icon-logout"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>

        <!-- FOOTER CARD -->
        <div class="sn-footer">
            <b>SehatNexa</b>
            <small>Your Health, Our Network</small>
        </div>

    </section>
</aside>