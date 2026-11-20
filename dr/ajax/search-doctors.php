<?php
// ============================================================
// AJAX Doctors Search Endpoint
// ============================================================
include_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['ajax'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$search         = isset($_POST['search']) ? mysqli_real_escape_string($con, trim($_POST['search'])) : '';
$hospital       = isset($_POST['hospital']) ? (int)$_POST['hospital'] : 0;
$specialization = isset($_POST['specialization']) ? (int)$_POST['specialization'] : 0;
$city           = isset($_POST['city']) ? (int)$_POST['city'] : 0;
$lady_doctor    = isset($_POST['lady_doctor']) ? (int)$_POST['lady_doctor'] : 0;
$page           = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
$per_page       = 12;
$offset         = ($page - 1) * $per_page;

// Chips
$filter_available   = isset($_POST['filter_available']) ? 1 : 0;
$filter_lady        = isset($_POST['filter_lady']) ? 1 : 0;
$filter_experienced = isset($_POST['filter_experienced']) ? 1 : 0;
$filter_rated       = isset($_POST['filter_rated']) ? 1 : 0;
$filter_new         = isset($_POST['filter_new']) ? 1 : 0;

// Build WHERE with junction table join when hospital filter used
$join_clause = "";
$where_clause = " AND d.approve = 1 ";

if (!empty($hospital)) {
    // Correct: use doctor_in_hospital junction table
    $join_clause .= " INNER JOIN doctor_in_hospital dih ON dih.doctor_id = d.doctor_id AND dih.hospital_id = $hospital AND dih.inactive = 0 ";
}

if (!empty($search)) {
    $where_clause .= " AND d.doctor_name LIKE '%$search%' ";
}
if (!empty($specialization)) {
    $where_clause .= " AND d.cat_type_id = '$specialization' ";
}
if (!empty($city)) {
    $where_clause .= " AND d.city_id = '$city' ";
}
if (!empty($lady_doctor)) {
    $where_clause .= " AND d.gender = 'Female' ";
}
if ($filter_available) {
    $where_clause .= " AND (d.emergency_status IS NULL OR d.emergency_status != 1) ";
}
if ($filter_lady) {
    $where_clause .= " AND d.gender = 'Female' ";
}
if ($filter_experienced) {
    $where_clause .= " AND d.experience_years >= 5 ";
}

$base_select = "SELECT DISTINCT d.*, dct.type as doctor_of, c.city_name, h.hospital_name 
                FROM doctors d 
                LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                LEFT JOIN cities c ON d.city_id = c.city_id 
                LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                LEFT JOIN users u ON u.user_id = d.user_id
                $join_clause
                WHERE u.status = 1 AND d.approve = 1 $where_clause";

$count_result = mysqli_query($con, $base_select);
$total_doctors = $count_result ? mysqli_num_rows($count_result) : 0;

// Rated / New filters — re-run with subquery
if ($filter_rated || $filter_new) {
    if ($filter_rated) {
        $where_clause .= " AND d.user_id IN (SELECT user_id FROM feedback WHERE status = 1 GROUP BY user_id HAVING AVG(stars) > 0) ";
    }
    if ($filter_new) {
        $where_clause .= " AND d.user_id NOT IN (SELECT user_id FROM feedback WHERE status = 1) ";
    }
    $base_select = "SELECT DISTINCT d.*, dct.type as doctor_of, c.city_name, h.hospital_name 
                    FROM doctors d 
                    LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                    LEFT JOIN cities c ON d.city_id = c.city_id 
                    LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                    LEFT JOIN users u ON u.user_id = d.user_id
                    $join_clause
                    WHERE u.status = 1 AND d.approve = 1 $where_clause";
    $count_result = mysqli_query($con, $base_select);
    $total_doctors = $count_result ? mysqli_num_rows($count_result) : 0;
}

$total_pages = ($total_doctors > 0) ? ceil($total_doctors / $per_page) : 0;

$query = $base_select . " ORDER BY d.doctor_name ASC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($con, $query);

// Helpers
if (!function_exists('dr_instrument_icon')) {
    function dr_instrument_icon($spec, $index) {
        $s = strtolower((string)$spec);
        $map = [
            'cardio' => 'fa-heart-pulse', 'heart' => 'fa-heart-pulse',
            'dent' => 'fa-tooth', 'eye' => 'fa-eye', 'ophthal' => 'fa-eye',
            'neuro' => 'fa-brain', 'brain' => 'fa-brain', 'psych' => 'fa-brain',
            'pulmo' => 'fa-lungs', 'chest' => 'fa-lungs', 'lung' => 'fa-lungs',
            'ortho' => 'fa-bone', 'bone' => 'fa-bone',
            'pediat' => 'fa-baby', 'child' => 'fa-baby',
            'gyn' => 'fa-person-pregnant', 'obstet' => 'fa-person-pregnant',
            'radiolog' => 'fa-x-ray', 'patholog' => 'fa-microscope', 'lab' => 'fa-microscope',
        ];
        foreach ($map as $key => $icon) {
            if (strpos($s, $key) !== false) { return $icon; }
        }
        $defaults = ['fa-stethoscope', 'fa-syringe', 'fa-heart-pulse', 'fa-pills', 'fa-microscope', 'fa-kit-medical'];
        return $defaults[$index % count($defaults)];
    }
}

$doctor_palette = [
    ['#eef6ff', '#2f80ed'], ['#f3efff', '#7c4dff'], ['#eefbf5', '#10b981'],
    ['#fff6e8', '#f59e0b'], ['#fceef3', '#ec4899'], ['#edf8ff', '#0ea5e9'],
];
$dr_bg_icons = ['fa-stethoscope', 'fa-syringe', 'fa-pills', 'fa-kit-medical', 'fa-microscope', 'fa-heart-pulse'];

$html = '';

if (mysqli_num_rows($result) > 0) {
    $doctor_index = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $doctor_index++;
        $doct_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='" . (int)$row['user_id'] . "'");
        $doct_stars = mysqli_fetch_assoc($doct_stars_q)['stars'];
        $doctor_rating = $doct_stars ? number_format((float)$doct_stars, 1) : 'New';

        $doctor_specialization = !empty($row['doctor_of']) ? $row['doctor_of'] : 'Medical Specialist';
        list($doctor_bg, $doctor_accent) = $doctor_palette[($doctor_index - 1) % count($doctor_palette)];
        $rating_num   = $doct_stars ? (float)$doct_stars : 0;
        $filled_stars = (int)round($rating_num);
        $is_lady      = (isset($row['gender']) && strtolower($row['gender']) === 'female');
        $experience   = (int)($row['experience_years'] ?? 0);
        $art_icon = dr_instrument_icon($doctor_specialization, $doctor_index - 1);
        $bg_icon  = $dr_bg_icons[($doctor_index + 1) % count($dr_bg_icons)];
        if ($bg_icon === $art_icon) { $bg_icon = $dr_bg_icons[($doctor_index + 2) % count($dr_bg_icons)]; }
        $emergency_status = isset($row['emergency_status']) ? (int)$row['emergency_status'] : 0;
        $is_unavailable   = ($emergency_status === 1);

        ob_start();
        ?>
        <div class="dr-card" style="--dr-bg: <?php echo $doctor_bg; ?>; --dr-accent: <?php echo $doctor_accent; ?>;" 
             data-doctor-name="<?php echo htmlspecialchars($row['doctor_name']); ?>"
             data-doctor-spec="<?php echo htmlspecialchars($doctor_specialization); ?>"
             data-doctor-city="<?php echo htmlspecialchars($row['city_name'] ?? ''); ?>"
             data-doctor-hospital="<?php echo htmlspecialchars($row['hospital_name'] ?? ''); ?>"
             data-doctor-experience="<?php echo $experience; ?>"
             data-doctor-rating="<?php echo $rating_num; ?>"
             data-doctor-available="<?php echo $is_unavailable ? '0' : '1'; ?>"
             data-doctor-lady="<?php echo $is_lady ? '1' : '0'; ?>">
            <i class="fas <?php echo $bg_icon; ?> dr-bg-icon" aria-hidden="true"></i>
            <div class="dr-card-top">
                <div class="dr-art" aria-hidden="true">
                    <i class="fas <?php echo $art_icon; ?> dr-art-icon"></i>
                    <svg class="dr-ecg" viewBox="0 0 300 60" preserveAspectRatio="xMidYMid slice">
                        <path pathLength="100" d="M0 32 H64 L74 32 L84 10 L96 54 L106 32 H150 L158 24 L166 32 H196 L206 14 L218 50 L228 32 H300"/>
                    </svg>
                </div>
                <?php if ($is_lady): ?>
                    <span class="dr-pill dr-pill-female"><i class="fas fa-venus"></i> Lady Doctor</span>
                <?php endif; ?>
                <span class="dr-pill dr-pill-status <?php echo $is_unavailable ? 'is-unavailable' : 'is-available'; ?>">
                    <i class="fas <?php echo $is_unavailable ? 'fa-circle-xmark' : 'fa-circle-check'; ?>"></i>
                    <?php echo $is_unavailable ? 'Unavailable' : 'Available'; ?>
                </span>
                <?php if ($rating_num > 0): ?>
                    <span class="dr-pill dr-pill-rating"><i class="fas fa-star"></i> <?php echo $doctor_rating; ?></span>
                <?php else: ?>
                    <span class="dr-pill dr-pill-rating is-new"><i class="fas fa-bolt"></i> New</span>
                <?php endif; ?>

                <div class="dr-avatar">
                    <?php if (!empty($row['doctor_pic'])): ?>
                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo htmlspecialchars($row['doctor_pic']); ?>" alt="<?php echo htmlspecialchars($row['doctor_name']); ?>" loading="lazy">
                    <?php else: ?>
                        <div class="dr-avatar-icon"><i class="fas fa-user-md"></i></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dr-card-body">
                <span class="dr-spec"><i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars($doctor_specialization); ?></span>
                <h3 class="dr-name">Dr. <?php echo htmlspecialchars(ucfirst(strtolower($row['doctor_name']))); ?></h3>
                <p class="dr-qual"><?php echo !empty($row['short_detail']) ? htmlspecialchars($row['short_detail']) : '&nbsp;'; ?></p>
                <ul class="dr-meta">
                    <?php if (!empty($row['city_name'])): ?>
                        <li><i class="fas fa-location-dot"></i><span><?php echo htmlspecialchars($row['city_name']); ?></span></li>
                    <?php endif; ?>
                    <?php if (!empty($row['hospital_name'])): ?>
                        <li><i class="fas fa-hospital"></i><span><?php echo htmlspecialchars($row['hospital_name']); ?></span></li>
                    <?php endif; ?>
                    <?php if ($experience > 0): ?>
                        <li><i class="fas fa-briefcase-medical"></i><span><?php echo $experience; ?> Years Experience</span></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="dr-card-footer">
                <div class="dr-stars">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                        <i class="fas fa-star <?php echo $s <= $filled_stars ? 'on' : ''; ?>"></i>
                    <?php endfor; ?>
                </div>
                <a href="<?php echo BASE_URL; ?>doctor-detail?doctor_id=<?php echo (int)$row['doctor_id']; ?>" class="dr-btn">
                    View Profile <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php
        $html .= ob_get_clean();
    }
} else {
    $html = '<div class="col-12"><div class="no-doctors">'
          . '<i class="fas fa-user-md"></i>'
          . '<h3>No Doctors Found</h3>'
          . '<p class="text-muted">No doctors match your search criteria. Please try different filters.</p>'
          . '</div></div>';
}

// Pagination HTML
$pagination_html = '';
if ($total_pages > 1 && $total_doctors > 0) {
    $qs_base = http_build_query([
        'search' => $search, 'hospital' => $hospital ?: '',
        'specialization' => $specialization ?: '', 'city' => $city ?: '',
        'lady_doctor' => $lady_doctor ?: '',
    ]);
    ob_start(); ?>
    <div class="pagination-container" id="paginationContainer">
        <nav aria-label="Doctors pagination">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $qs_base; ?>"><i class="fas fa-chevron-left"></i></a></li>
                <?php else: ?>
                    <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php
                $start_page = max(1, $page - 2);
                $end_page   = min($total_pages, $page + 2);
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1&' . $qs_base . '">1</a></li>';
                    if ($start_page > 2) echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                }
                for ($i = $start_page; $i <= $end_page; $i++) {
                    if ($i == $page) {
                        echo '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
                    } else {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '&' . $qs_base . '">' . $i . '</a></li>';
                    }
                }
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&' . $qs_base . '">' . $total_pages . '</a></li>';
                }
                ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $qs_base; ?>"><i class="fas fa-chevron-right"></i></a></li>
                <?php else: ?>
                    <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1"><i class="fas fa-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="text-center mt-3">
            <p class="text-muted">Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_doctors); ?> of <?php echo $total_doctors; ?> doctors</p>
        </div>
    </div>
    <?php
    $pagination_html = ob_get_clean();
}

echo json_encode([
    'success'         => true,
    'html'            => $html,
    'pagination_html' => $pagination_html,
    'total_doctors'   => $total_doctors,
    'current_page'    => $page,
    'total_pages'     => $total_pages,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);