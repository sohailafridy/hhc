<?php
if (!function_exists('admin_top_time_ago')) {
    function admin_top_time_ago($datetime)
    {
        if (empty($datetime)) {
            return '';
        }
        $ts = strtotime($datetime);
        if (!$ts) {
            return '';
        }
        $diff = time() - $ts;
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            $m = (int) floor($diff / 60);
            return $m . ' min ago';
        }
        if ($diff < 86400) {
            $h = (int) floor($diff / 3600);
            return $h . ' hour' . ($h > 1 ? 's' : '') . ' ago';
        }
        if ($diff < 604800) {
            $d = (int) floor($diff / 86400);
            return $d . ' day' . ($d > 1 ? 's' : '') . ' ago';
        }
        return date('d M Y', $ts);
    }
}

if (!function_exists('admin_top_count')) {
    function admin_top_count($con, $sql)
    {
        $result = mysqli_query($con, $sql);
        if (!$result) {
            return 0;
        }
        $row = mysqli_fetch_assoc($result);
        return isset($row['total']) ? (int) $row['total'] : 0;
    }
}

if (!function_exists('admin_top_pic')) {
    function admin_top_pic($folder, $filename)
    {
        $fallback = BASE_URL . 'admin/inc/assets/images/avatar-1.png';
        if (empty($filename)) {
            return $fallback;
        }
        return BASE_URL . 'admin/inc/uploads/' . $folder . '/' . rawurlencode($filename);
    }
}

$top_doctors_count = admin_top_count(
    $con,
    "SELECT COUNT(*) as total FROM doctors d LEFT JOIN users u ON u.user_id = d.user_id WHERE u.status = 1 AND d.approve = 1"
);
$top_hospitals_count = admin_top_count(
    $con,
    "SELECT COUNT(*) as total FROM hospitals h LEFT JOIN users u ON u.user_id = h.user_id WHERE u.status = 1 AND h.approve = 1"
);
$top_labs_count = admin_top_count(
    $con,
    "SELECT COUNT(*) as total FROM laboratories l LEFT JOIN users u ON u.user_id = l.user_id WHERE u.status = 1 AND l.approve = 1"
);
$top_bb_count = admin_top_count(
    $con,
    "SELECT COUNT(*) as total FROM blood_bank bb LEFT JOIN users u ON u.user_id = bb.user_id WHERE u.status = 1 AND bb.approve = 1"
);
$top_users_count = admin_top_count($con, "SELECT COUNT(*) as total FROM users WHERE status = 1");
$top_cities_count = admin_top_count($con, "SELECT COUNT(*) as total FROM cities WHERE status = 1 AND approve = 1");
$top_feedback_count = admin_top_count($con, "SELECT COUNT(*) as total FROM feedback WHERE status = 1");
$top_recycle_count = admin_top_count($con, "SELECT COUNT(*) as total FROM users WHERE status = 0");

$top_notifications = [];

$pending_sets = [
    [
        'sql' => "SELECT doctor_id AS item_id, doctor_name AS item_name, doctor_pic AS pic, created_at FROM doctors WHERE approve = 0 ORDER BY created_at DESC LIMIT 5",
        'label' => 'Doctor pending approval',
        'folder' => 'doctors',
        'url' => BASE_URL . 'admin/doctors/profile?id=',
    ],
    [
        'sql' => "SELECT hospital_id AS item_id, hospital_name AS item_name, hospital_pic AS pic, created_at FROM hospitals WHERE approve = 0 ORDER BY created_at DESC LIMIT 5",
        'label' => 'Hospital pending approval',
        'folder' => 'hospitals',
        'url' => BASE_URL . 'admin/hospitals/detail?id=',
    ],
    [
        'sql' => "SELECT lab_id AS item_id, lab_name AS item_name, lab_pic AS pic, created_at FROM laboratories WHERE approve = 0 ORDER BY created_at DESC LIMIT 5",
        'label' => 'Laboratory pending approval',
        'folder' => 'laboratories',
        'url' => BASE_URL . 'admin/laboratories/detail?id=',
    ],
    [
        'sql' => "SELECT bb_id AS item_id, bb_name AS item_name, bb_pic AS pic, created_at FROM blood_bank WHERE approve = 0 ORDER BY created_at DESC LIMIT 5",
        'label' => 'Blood bank pending approval',
        'folder' => 'blood-banks',
        'url' => BASE_URL . 'admin/blood-banks/detail?id=',
    ],
];

foreach ($pending_sets as $set) {
    $result = mysqli_query($con, $set['sql']);
    if (!$result) {
        continue;
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $top_notifications[] = [
            'title' => $row['item_name'],
            'text' => $set['label'],
            'time' => $row['created_at'],
            'pic' => admin_top_pic($set['folder'], $row['pic']),
            'url' => $set['url'] . (int) $row['item_id'],
        ];
    }
}

$fb_result = mysqli_query(
    $con,
    "SELECT commenter_name, comment, stars, created_at FROM feedback ORDER BY created_at DESC LIMIT 6"
);
if ($fb_result) {
    while ($row = mysqli_fetch_assoc($fb_result)) {
        $stars = (int) $row['stars'];
        $comment = trim((string) $row['comment']);
        $top_notifications[] = [
            'title' => $row['commenter_name'] ?: 'Reviewer',
            'text' => ($stars ? $stars . '★ ' : '') . ($comment !== '' ? $comment : 'New feedback'),
            'time' => $row['created_at'],
            'pic' => BASE_URL . 'admin/inc/assets/images/avatar-1.png',
            'url' => BASE_URL . 'admin',
        ];
    }
}

$recent_sets = [
    [
        'sql' => "SELECT doctor_id AS item_id, doctor_name AS item_name, doctor_pic AS pic, created_at FROM doctors ORDER BY created_at DESC LIMIT 4",
        'label' => 'New doctor added',
        'folder' => 'doctors',
        'url' => BASE_URL . 'admin/doctors/profile?id=',
    ],
    [
        'sql' => "SELECT hospital_id AS item_id, hospital_name AS item_name, hospital_pic AS pic, created_at FROM hospitals ORDER BY created_at DESC LIMIT 3",
        'label' => 'New hospital added',
        'folder' => 'hospitals',
        'url' => BASE_URL . 'admin/hospitals/detail?id=',
    ],
];

foreach ($recent_sets as $set) {
    $result = mysqli_query($con, $set['sql']);
    if (!$result) {
        continue;
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $top_notifications[] = [
            'title' => $row['item_name'],
            'text' => $set['label'],
            'time' => $row['created_at'],
            'pic' => admin_top_pic($set['folder'], $row['pic']),
            'url' => $set['url'] . (int) $row['item_id'],
        ];
    }
}

usort($top_notifications, function ($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});
$top_notifications = array_slice($top_notifications, 0, 6);
$top_notify_count = count($top_notifications);

$top_search_doctors = [];
$sd = mysqli_query(
    $con,
    "SELECT d.doctor_id, d.doctor_name, d.doctor_pic
     FROM doctors d
     LEFT JOIN users u ON u.user_id = d.user_id
     WHERE u.status = 1 AND d.approve = 1
     ORDER BY d.created_at DESC
     LIMIT 6"
);
if ($sd) {
    while ($row = mysqli_fetch_assoc($sd)) {
        $top_search_doctors[] = $row;
    }
}

$top_search_hospitals = [];
$sh = mysqli_query(
    $con,
    "SELECT h.hospital_id, h.hospital_name, h.hospital_pic, h.user_id
     FROM hospitals h
     LEFT JOIN users u ON u.user_id = h.user_id
     WHERE u.status = 1 AND h.approve = 1
     ORDER BY h.created_at DESC
     LIMIT 6"
);
if ($sh) {
    while ($row = mysqli_fetch_assoc($sh)) {
        $top_search_hospitals[] = $row;
    }
}

$top_search_labs = [];
$sl = mysqli_query(
    $con,
    "SELECT l.lab_id, l.lab_name, l.lab_pic
     FROM laboratories l
     LEFT JOIN users u ON u.user_id = l.user_id
     WHERE u.status = 1 AND l.approve = 1
     ORDER BY l.created_at DESC
     LIMIT 6"
);
if ($sl) {
    while ($row = mysqli_fetch_assoc($sl)) {
        $top_search_labs[] = $row;
    }
}

$display_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$display_type = isset($_SESSION['type']) ? ucfirst($_SESSION['type']) : '';

if (isset($_SESSION['type']) && $_SESSION['type'] == 'hospital' && isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
    $hosp_query = "SELECT hospital_name FROM hospitals WHERE user_id = $user_id AND approve = 1";
    $hosp_result = mysqli_query($con, $hosp_query);
    if ($hosp_result && mysqli_num_rows($hosp_result) > 0) {
        $hosp_data = mysqli_fetch_assoc($hosp_result);
        $display_name = $hosp_data['hospital_name'];
    }
}
?>
<!-- <div class="loader-bg">
      <div class="loader-bar">
      </div>
</div> -->
<div class="wrapper">

<header class="main-header-top hidden-print">
         <a href="<?php echo BASE_URL; ?>admin" class="logo"><img class="img-fluid able-logo" src="<?php echo BASE_URL; ?>admin/inc/assets/images/logo.png" alt="Theme-logo"></a>
         <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#!" data-toggle="offcanvas" class="sidebar-toggle"></a>
            <ul class="top-nav lft-nav">
               <li>
                  <a href="<?php echo BASE_URL; ?>admin" class="dropdown-toggle drop icon-circle drop-image">
                     <i class="ti-home"> </i><span> Dashboard</span>
                  </a>
               </li>
               <li class="dropdown">
                  <a href="#!" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle drop icon-circle drop-image">
                     <span>Quick Lists </span><i class=" icofont icofont-simple-down"></i>
                  </a>
                  <ul class="dropdown-menu settings-menu">
                     <li><a href="<?php echo BASE_URL; ?>admin/doctors/list">Doctors (<?php echo $top_doctors_count; ?>)</a></li>
                     <li><a href="<?php echo BASE_URL; ?>admin/hospitals/list">Hospitals (<?php echo $top_hospitals_count; ?>)</a></li>
                     <li><a href="<?php echo BASE_URL; ?>admin/laboratories/list">Laboratories (<?php echo $top_labs_count; ?>)</a></li>
                     <li><a href="<?php echo BASE_URL; ?>admin/blood-banks/list">Blood Banks (<?php echo $top_bb_count; ?>)</a></li>
                     <li><a href="<?php echo BASE_URL; ?>admin/cities/list">Cities (<?php echo $top_cities_count; ?>)</a></li>
                     <li><a href="<?php echo BASE_URL; ?>admin/users">Users (<?php echo $top_users_count; ?>)</a></li>
                  </ul>
               </li>
               <li class="dropdown pc-rheader-submenu message-notification search-toggle">
                  <a href="#!" id="morphsearch-search" class="drop icon-circle txt-white">
                     <i class="ti-search"></i>
                  </a>
               </li>
            </ul>
            <!-- Navbar Right Menu-->
           
               <ul class="top-nav">
                  <!--Notification Menu-->
                    
                  <li class="dropdown notification-menu">
                     <a href="#!" data-toggle="dropdown" aria-expanded="false" class="dropdown-toggle">
                        <i class="icon-bell"></i>
                        <?php if ($top_notify_count > 0): ?>
                        <span class="badge badge-danger header-badge"><?php echo $top_notify_count; ?></span>
                        <?php endif; ?>
                     </a>
                     <ul class="dropdown-menu">
                        <li class="not-head">You have <b class="text-primary"><?php echo $top_notify_count; ?></b> notifications.</li>
                        <?php if (empty($top_notifications)): ?>
                        <li class="bell-notification">
                           <a href="javascript:;" class="media">
                              <div class="media-body"><span class="block">No notifications yet</span></div>
                           </a>
                        </li>
                        <?php else: ?>
                        <?php foreach ($top_notifications as $note): ?>
                        <li class="bell-notification">
                           <a href="<?php echo htmlspecialchars($note['url']); ?>" class="media">
                              <span class="media-left media-icon">
                    <img class="img-circle" src="<?php echo htmlspecialchars($note['pic']); ?>" alt="User Image">
                  </span>
                              <div class="media-body"><span class="block"><?php echo htmlspecialchars($note['title']); ?> — <?php echo htmlspecialchars($note['text']); ?></span><span class="text-muted block-time"><?php echo htmlspecialchars(admin_top_time_ago($note['time'])); ?></span></div>
                           </a>
                        </li>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        <li class="not-footer">
                           <a href="<?php echo BASE_URL; ?>admin">See dashboard</a>
                        </li>
                     </ul>
                  </li>
                  <!-- recycle -->
                  <li class="pc-rheader-submenu ">
                     <a href="<?php echo BASE_URL; ?>admin/recycle" class="drop icon-circle" title="Recycle Bin">
                        <i class="icon-trash"></i>
                        <?php if ($top_recycle_count > 0): ?>
                        <span class="badge badge-danger header-badge"><?php echo $top_recycle_count; ?></span>
                        <?php endif; ?>
                     </a>

                  </li>
                  <!-- window screen -->
                  <li class="pc-rheader-submenu">
                     <a href="#!" class="drop icon-circle" onclick="javascript:toggleFullScreen()">
                        <i class="icon-size-fullscreen"></i>
                     </a>

                  </li>
                  <!-- User Menu - DYNAMIC -->
                  <li class="dropdown">
                     <a href="#!" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle drop icon-circle drop-image">
                        <span><img class="img-circle" src="<?php echo BASE_URL; ?>admin/inc/assets/images/avatar-1.png" style="width:40px;" alt="User Image"></span>
                        <span>
                            <strong><?php echo htmlspecialchars($display_name); ?></strong>
                            <span class="text-muted small">(<?php echo htmlspecialchars($display_type); ?>)</span>
                            <i class="icofont icofont-simple-down"></i>
                        </span>
                     </a>
                     <ul class="dropdown-menu settings-menu">
                        <?php if (isset($_SESSION['type']) && $_SESSION['type'] == 'hospital'): ?>
                            <li><a href="<?php echo BASE_URL; ?>hospital/profile"><i class="icon-user"></i> My Profile</a></li>
                            <li><a href="<?php echo BASE_URL; ?>hospital/"><i class="icon-speedometer"></i> Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>admin"><i class="icon-speedometer"></i> Dashboard</a></li>
                            <li><a href="<?php echo BASE_URL; ?>admin/users"><i class="icon-user"></i> Users (<?php echo $top_users_count; ?>)</a></li>
                            <li><a href="<?php echo BASE_URL; ?>admin"><?php echo $top_feedback_count; ?> Feedbacks</a></li>
                        <?php endif; ?>
                        <li class="p-0">
                           <div class="dropdown-divider m-0"></div>
                        </li>
                        <li><a href="<?php echo BASE_URL; ?>logout"><i class="icon-logout"></i> Logout</a></li>
                     </ul>
                  </li>
               </ul>

               <!-- search -->
               <div id="morphsearch" class="morphsearch">
                  <form class="morphsearch-form" action="<?php echo BASE_URL; ?>admin/doctors/list" method="get">

                     <input class="morphsearch-input" type="search" name="search_doctor" placeholder="Search doctors..." />

                     <button class="morphsearch-submit" type="submit">Search</button>

                  </form>
                  <div class="morphsearch-content">
                     <div class="dummy-column">
                        <h2>Doctors</h2>
                        <?php if (empty($top_search_doctors)): ?>
                        <p>No doctors found</p>
                        <?php else: ?>
                        <?php foreach ($top_search_doctors as $doc): ?>
                        <a class="dummy-media-object" href="<?php echo BASE_URL; ?>admin/doctors/profile?id=<?php echo (int) $doc['doctor_id']; ?>">
                           <img class="round" src="<?php echo htmlspecialchars(admin_top_pic('doctors', $doc['doctor_pic'])); ?>" alt="<?php echo htmlspecialchars($doc['doctor_name']); ?>" />
                           <h3><?php echo htmlspecialchars($doc['doctor_name']); ?></h3>
                        </a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                     </div>
                     <div class="dummy-column">
                        <h2>Hospitals</h2>
                        <?php if (empty($top_search_hospitals)): ?>
                        <p>No hospitals found</p>
                        <?php else: ?>
                        <?php foreach ($top_search_hospitals as $hosp): ?>
                        <a class="dummy-media-object" href="<?php echo BASE_URL; ?>admin/hospitals/detail?id=<?php echo (int) $hosp['hospital_id']; ?>&user_id=<?php echo (int) $hosp['user_id']; ?>">
                           <img src="<?php echo htmlspecialchars(admin_top_pic('hospitals', $hosp['hospital_pic'])); ?>" alt="<?php echo htmlspecialchars($hosp['hospital_name']); ?>" />
                           <h3><?php echo htmlspecialchars($hosp['hospital_name']); ?></h3>
                        </a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                     </div>
                     <div class="dummy-column">
                        <h2>Laboratories</h2>
                        <?php if (empty($top_search_labs)): ?>
                        <p>No laboratories found</p>
                        <?php else: ?>
                        <?php foreach ($top_search_labs as $lab): ?>
                        <a class="dummy-media-object" href="<?php echo BASE_URL; ?>admin/laboratories/detail?id=<?php echo (int) $lab['lab_id']; ?>">
                           <img src="<?php echo htmlspecialchars(admin_top_pic('laboratories', $lab['lab_pic'])); ?>" alt="<?php echo htmlspecialchars($lab['lab_name']); ?>" />
                           <h3><?php echo htmlspecialchars($lab['lab_name']); ?></h3>
                        </a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                     </div>
                  </div>
                  <!-- /morphsearch-content -->
                  <span class="morphsearch-close"><i class="icofont icofont-search-alt-1"></i></span>
               </div>
               <!-- search end -->
            </div>
         </nav>
      </header>
