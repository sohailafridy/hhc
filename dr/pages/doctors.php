<?php include '../includes/header.php'; ?>

<?php
    // Handle search and filters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    $hospital = isset($_GET['hospital']) ? (int)$_GET['hospital'] : '';
    $specialization = isset($_GET['specialization']) ? (int)$_GET['specialization'] : '';
    $city = isset($_GET['city']) ? (int)$_GET['city'] : $city_id; // Default to Kohat if not set
    $lady_doctor = 0;
    if(isset($_GET['lady_doctor']) && $_GET['lady_doctor'] == 1){
        $lady_doctor = $_GET['lady_doctor'];
    }

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 12;
    $offset = ($page - 1) * $per_page;

// 1. QUERY UPDATE: treatment column add kiya gaya hai
$categories_query = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type, dct.treatment 
                   FROM dr_categories dc 
                   LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id 
                   ORDER BY dc.cat_name, dct.type";
$categories_result = mysqli_query($con, $categories_query);

    // Group categories and types
$categories_data = [];
if ($categories_result) {
    while ($row = mysqli_fetch_assoc($categories_result)) {
        if (!isset($categories_data[$row['dr_cat_id']])) {
            $categories_data[$row['dr_cat_id']] = [
                'cat_name' => $row['cat_name'],
                'types' => []
            ];
        }
        if ($row['dr_cat_type_id']) {
            $categories_data[$row['dr_cat_id']]['types'][] = [
                'dr_cat_type_id' => $row['dr_cat_type_id'],
                'type' => $row['type'],
                'treatment' => $row['treatment'] // Naya column yahan add hua
            ];
        }
    }
}

?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-list.css">
<style>
    /* Urdu dropdown styling for better readability */
    .urdu-text {
        font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Arial', sans-serif;
        font-size: 1.1rem;
        text-align: right;
        direction: rtl;
    }
    .eng-subtext {
        text-align: left;
        direction: ltr;
        display: block;
        font-size: 0.75rem;
        margin-top: 4px;
    }
</style>
<body>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Our Expert Doctors</h1>
                    <p class="lead mb-0" data-aos="fade-up" data-aos-delay="200">Meet our team of qualified medical professionals</p>
                    <a href="<?php echo BASE_URL; ?>/pages/outside/add-doctor.php" class="btn btn-primary btn-lg add-new-doctor" style='display: none;'>Add New Doctor</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section class="section-padding">
        <div class="container">
            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label text-muted small">Search by Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-search input-icon"></i>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter doctor name...">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label text-muted small">Filter by City</label>
                            <div class="input-with-icon">
                                <i class="fas fa-map-marker-alt input-icon"></i>
                                <select class="form-select select2-bootstrap-5-theme" name="city" id="citySelect" data-dropdown-css-class="select2-bootstrap-5-dropdown">
                                    <option value="">All Cities</option>
                                    <?php
                                    $city_query = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
                                    $city_result = mysqli_query($con, $city_query);
                                    while($city_row = mysqli_fetch_assoc($city_result)) {
                                        $selected = ($city == $city_row['city_id']) ? 'selected' : '';
                                        echo '<option value="'.$city_row['city_id'].'" '.$selected.'>'.$city_row['city_name'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label text-muted small">Filter by Hospital</label>
                            <div class="input-with-icon">
                                <i class="fas fa-hospital input-icon"></i>
                                <select class="form-select" name="hospital" id="hospitalSelect">
                                    <option value="">All Hospitals</option>
                                    <?php
                                    if($city !=0){
                                        $hospital_query = "SELECT DISTINCT h.hospital_id, h.hospital_name 
                                                    FROM hospitals h 
                                                    LEFT JOIN users u ON u.user_id = h.user_id
                                                    WHERE u.status = 1 
                                                    AND h.city_id = '$city'
                                                    
                                                    ORDER BY h.hospital_name ASC"; 
                                    }else{
                                        $hospital_query = "SELECT DISTINCT h.hospital_id, h.hospital_name 
                                                    FROM hospitals h 
                                                    LEFT JOIN users u ON u.user_id = h.user_id
                                                    WHERE u.status = 1 
                                                    ORDER BY h.hospital_name ASC";
                                    }
                                     
                                    $hospital_result = mysqli_query($con, $hospital_query);
                                    while($hospital_row = mysqli_fetch_assoc($hospital_result)) {
                                        $selected = ($hospital == $hospital_row['hospital_id']) ? 'selected' : '';
                                        echo '<option value="'.$hospital_row['hospital_id'].'" '.$selected.'>'.$hospital_row['hospital_name'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <!-- LABEL UPDATE: Disease / Treatment -->
                            <label class="form-label text-muted small">Filter by Disease / Treatment</label>
                            <div class="input-with-icon position-relative">
                                <i class="fas fa-stethoscope input-icon"></i>
                                <input type="text" class="form-control" id="specializationSearch" placeholder="Search disease (e.g., دل)..." autocomplete="off" dir="auto">
                                <input type="hidden" name="specialization" id="specializationValue" value="<?php echo $specialization; ?>">
                                <div class="position-absolute w-100 bg-white border border-top-0 shadow-sm" id="specializationDropdown" style="display: none; z-index: 1000; max-height: 250px; overflow-y: auto;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Additional Filters</label>
                            <div class="form-check custom-checkbox-toggle">
                                <input class="form-check-input" type="checkbox" id="lady_doctor" name="lady_doctor" value="1" <?php echo ($lady_doctor) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="lady_doctor">
                                    <i class="fas fa-venus text-pink"></i> Lady Doctors Only
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-style">
                                    <i class="fas fa-search me-1"></i>Search Doctors
                                </button>
                                <a href="doctors" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-1"></i>Reset All
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <script>
                // City-Hospital dependency
                document.getElementById('citySelect').addEventListener('change', function() {
                    var cityId = this.value;
                    var hospitalSelect = document.getElementById('hospitalSelect');
                    
                    // Clear current hospital options
                    hospitalSelect.innerHTML = '<option value="">All Hospitals</option>';
                    
                    if (cityId) {
                        // Fetch hospitals for selected city
                        fetch('get_hospitals.php?city_id=' + cityId)
                            .then(response => response.text())
                            .then(data => {
                                hospitalSelect.innerHTML = '<option value="">All Hospitals</option>' + data;
                            })
                            .catch(error => {
                                console.error('Error fetching hospitals:', error);
                            });
                    }
                });

                // 2. JAVASCRIPT UPDATE: Treatment (Urdu) logic
                const specializationSearch = document.getElementById('specializationSearch');
                const specializationValue = document.getElementById('specializationValue');
                const specializationDropdown = document.getElementById('specializationDropdown');
                let allOptions = [];

                // Initialize options
                function initializeSpecializationOptions() {
                    allOptions = [];
                    
                    // Build options from PHP data
                    <?php foreach ($categories_data as $category_id => $category): ?>
                        <?php foreach ($category['types'] as $type): ?>
                            allOptions.push({
                                value: '<?php echo $type['dr_cat_type_id']; ?>',
                                // Yahan text me treatment de diya hai jo ke urdu me hai
                                text: '<?php echo addslashes($type['treatment']); ?>',
                                // Search filter ke liye dataText me Speciality aur English Type rakh diya hai
                                dataText: '<?php echo addslashes($category['cat_name'] . ' - ' . $type['type']); ?>'
                            });
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    
                    // Set initial value if selected
                    const currentSpecialization = '<?php echo $specialization; ?>';
                    if (currentSpecialization) {
                        const selectedOption = allOptions.find(option => option.value === currentSpecialization);
                        if (selectedOption) {
                            specializationSearch.value = selectedOption.text;
                        }
                    }
                }

                // Filter options based on search
                function filterSpecializations(searchTerm) {
                    const filtered = allOptions.filter(option => 
                        option.text.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        option.dataText.toLowerCase().includes(searchTerm.toLowerCase()) // Ye line ensure karegi ke English type se bhi search ho jaye
                    );
                    
                    if (searchTerm === '') {
                        specializationDropdown.style.display = 'none';
                        return;
                    }
                    
                    if (filtered.length > 0) {
                        let html = '';
                        filtered.forEach(option => {
                            // UI me urdu text right align aur english text left align kiya gaya hai
                            html += `<div class="dropdown-item py-2 px-3" style="cursor: pointer;" data-value="${option.value}" data-text="${option.text}">
                                        <div class="fw-bold urdu-text mb-1">${option.text}</div>
                                        <small class="text-muted eng-subtext">${option.dataText}</small>
                                    </div>`;
                        });
                        specializationDropdown.innerHTML = html;
                        specializationDropdown.style.display = 'block';
                    } else {
                        specializationDropdown.innerHTML = '<div class="p-3 text-muted">No diseases found</div>';
                        specializationDropdown.style.display = 'block';
                    }
                }

                // Handle search input
                specializationSearch.addEventListener('input', function() {
                    filterSpecializations(this.value);
                });

                // Handle dropdown item click
                specializationDropdown.addEventListener('click', function(e) {
                    const item = e.target.closest('.dropdown-item');
                    if (item) {
                        const value = item.getAttribute('data-value');
                        const text = item.getAttribute('data-text');
                        
                        specializationSearch.value = text;
                        specializationValue.value = value;
                        specializationDropdown.style.display = 'none';
                    }
                });

                // Hide dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.position-relative')) {
                        specializationDropdown.style.display = 'none';
                    }
                });

                // Initialize on page load
                document.addEventListener('DOMContentLoaded', function() {
                    initializeSpecializationOptions();
                });
            </script>

            <!-- Doctors Grid -->
            <div class="doctors-grid" id="doctorsContainer">
                <?php
                $where_clause = '';
                $where_clause = ' AND d.approve = 1';
                if (!empty($search)) {
                    $where_clause .= " AND d.doctor_name LIKE '%$search%' ";
                }
                if (!empty($hospital)) {
                    $where_clause .= " AND d.hospital_id = '$hospital' ";
                }
                if (!empty($specialization)) {
                    $where_clause .= " AND d.cat_type_id = '$specialization' ";
                }
                if (!empty($city) && $city !=0) {
                    $where_clause .= " AND d.city_id = '$city' ";
                }
                
               
                if (!empty($lady_doctor) && $lady_doctor == 1) {
                    $where_clause .= " AND d.gender = 'Female' ";
                }
                   $query = "SELECT d.*, dct.type as doctor_of, c.city_name, h.hospital_name 
                         FROM doctors d 
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                         LEFT JOIN cities c ON d.city_id = c.city_id 
                         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                         LEFT JOIN users u ON u.user_id = d.user_id
                         WHERE u.status = 1 AND d.approve = 1 $where_clause
                         ORDER BY d.doctor_name ASC
                         LIMIT $per_page OFFSET $offset";



                    $all_doctors = mysqli_query($con,"SELECT d.*, dct.type as doctor_of, c.city_name, h.hospital_name 
                         FROM doctors d 
                         LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                         LEFT JOIN cities c ON d.city_id = c.city_id 
                         LEFT JOIN users u ON d.user_id = u.user_id 
                         LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id 
                         WHERE u.status = 1 AND d.approve = 1 $where_clause");

                    $total_doctors = mysqli_num_rows($all_doctors);
                    $total_pages = ceil($total_doctors / $per_page);
                $result = mysqli_query($con, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    $doctor_index = 0;
                    while($row = mysqli_fetch_assoc($result)) {
                        $doctor_index++;
                        $doct_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". (int)$row['user_id'] ."'");
                        $doct_stars = mysqli_fetch_assoc($doct_stars_q);
                        $doct_stars = $doct_stars['stars'];
                        $doctor_rating = $doct_stars ? number_format((float)$doct_stars, 1) : 'New';
                        ?>

<?php
/* ==========================================================
   STEP A: Doctors Grid se PEHLE palette + helper function
   ========================================================== */
$doctor_palette = [
    ['#eef6ff', '#2f80ed'], // blue
    ['#f3efff', '#7c4dff'], // purple
    ['#eefbf5', '#10b981'], // green
    ['#fff6e8', '#f59e0b'], // orange
    ['#fceef3', '#ec4899'], // pink
    ['#edf8ff', '#0ea5e9'], // sky
];

// Specialization ke hisaab se medical instrument/icon
if (!function_exists('dr_instrument_icon')) {
    function dr_instrument_icon($spec, $index) {
        $s = strtolower((string)$spec);
        $map = [
            'cardio'   => 'fa-heart-pulse',
            'heart'    => 'fa-heart-pulse',
            'dent'     => 'fa-tooth',
            'eye'      => 'fa-eye',
            'ophthal'  => 'fa-eye',
            'neuro'    => 'fa-brain',
            'brain'    => 'fa-brain',
            'psych'    => 'fa-brain',
            'pulmo'    => 'fa-lungs',
            'chest'    => 'fa-lungs',
            'lung'     => 'fa-lungs',
            'ortho'    => 'fa-bone',
            'bone'     => 'fa-bone',
            'pediat'   => 'fa-baby',
            'child'    => 'fa-baby',
            'gyn'      => 'fa-person-pregnant',
            'obstet'   => 'fa-person-pregnant',
            'radiolog' => 'fa-x-ray',
            'patholog' => 'fa-microscope',
            'lab'      => 'fa-microscope',
        ];
        foreach ($map as $key => $icon) {
            if (strpos($s, $key) !== false) {
                return $icon;
            }
        }
        $defaults = ['fa-stethoscope', 'fa-syringe', 'fa-heart-pulse', 'fa-pills', 'fa-microscope', 'fa-kit-medical'];
        return $defaults[$index % count($defaults)];
    }
}

$dr_bg_icons = ['fa-stethoscope', 'fa-syringe', 'fa-pills', 'fa-kit-medical', 'fa-microscope', 'fa-heart-pulse'];

/* ==========================================================
   STEP B: Loop ke andar values prepare karein
   ========================================================== */
$doctor_specialization = !empty($row['doctor_of']) ? $row['doctor_of'] : 'Medical Specialist';
list($doctor_bg, $doctor_accent) = $doctor_palette[($doctor_index - 1) % count($doctor_palette)];

$rating_num   = $doct_stars ? (float)$doct_stars : 0;
$filled_stars = (int)round($rating_num);
$is_lady      = (isset($row['gender']) && strtolower($row['gender']) === 'female');
$experience   = (int)($row['experience_years'] ?? 0);

$art_icon = dr_instrument_icon($doctor_specialization, $doctor_index - 1);
$bg_icon  = $dr_bg_icons[($doctor_index + 1) % count($dr_bg_icons)];
if ($bg_icon === $art_icon) {
    $bg_icon = $dr_bg_icons[($doctor_index + 2) % count($dr_bg_icons)];
}

// NEW: emergency status
$emergency_status = isset($row['emergency_status']) ? (int)$row['emergency_status'] : 0;
$is_unavailable   = ($emergency_status === 1);
?>

                        <div class="dr-card"
                             style="--dr-bg: <?php echo $doctor_bg; ?>; --dr-accent: <?php echo $doctor_accent; ?>;"
                             data-aos="fade-up" data-aos-delay="<?php echo (($doctor_index - 1) % 4) * 80; ?>">

                            <!-- ===== Background instrument (card ke peeche) ===== -->
                            <i class="fas <?php echo $bg_icon; ?> dr-bg-icon" aria-hidden="true"></i>

                            <!-- ===== Top banner ===== -->
                            <div class="dr-card-top">

                                <!-- Medical art: bara instrument + ECG heartbeat line -->
                                <div class="dr-art" aria-hidden="true">
                                    <i class="fas <?php echo $art_icon; ?> dr-art-icon"></i>
                                    <svg class="dr-ecg" viewBox="0 0 300 60" preserveAspectRatio="xMidYMid slice">
                                        <path pathLength="100" d="M0 32 H64 L74 32 L84 10 L96 54 L106 32 H150 L158 24 L166 32 H196 L206 14 L218 50 L228 32 H300"/>
                                    </svg>
                                </div>

                                <?php if ($is_lady): ?>
                                    <span class="dr-pill dr-pill-female"><i class="fas fa-venus"></i> Lady Doctor</span>
                                <?php endif; ?>

                                <!-- NEW: Emergency / Availability pill -->
                                <span class="dr-pill dr-pill-status <?php echo $is_unavailable ? 'is-unavailable' : 'is-available'; ?>"
                                      title="<?php echo $is_unavailable ? 'Doctor is currently unavailable' : 'Doctor is available'; ?>">
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
                                        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo htmlspecialchars($row['doctor_pic']); ?>"
                                             alt="<?php echo htmlspecialchars($row['doctor_name']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="dr-avatar-icon"><i class="fas fa-user-md"></i></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- ===== Body ===== -->
                            <div class="dr-card-body">
                                <span class="dr-spec" title="<?php echo htmlspecialchars($doctor_specialization); ?>">
                                    <i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars($doctor_specialization); ?>
                                </span>

                                <h4 class="dr-name">Dr. <?php echo htmlspecialchars($row['doctor_name']); ?></h4>
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

                            <!-- ===== Footer ===== -->
                            <div class="dr-card-footer">
                                <div class="dr-stars" title="<?php echo $rating_num > 0 ? $doctor_rating . ' / 5' : 'No ratings yet'; ?>">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="fas fa-star <?php echo $s <= $filled_stars ? 'on' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>

                                <a href="doctor-detail?doctor_id=<?php echo (int)$row['doctor_id']; ?>" class="dr-btn">
                                    View Profile <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="col-12">
                        <div class="no-doctors">
                            <i class="fas fa-user-md"></i>
                            <h3>No Doctors Found</h3>
                            <p class="text-muted">No doctors match your search criteria. Please try different filters.</p>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav aria-label="Doctors pagination">
                    <ul class="pagination">
                        <!-- Previous Button -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&hospital=<?php echo $hospital; ?>&specialization=<?php echo $specialization; ?>&city=<?php echo $city; ?>&lady_doctor=<?php echo $lady_doctor; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1&search='.urlencode($search).'&hospital='.$hospital.'&specialization='.$specialization.'&city='.$city.'&lady_doctor='.$lady_doctor.'">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            if ($i == $page) {
                                echo '<li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                            } else {
                                echo '<li class="page-item"><a class="page-link" href="?page='.$i.'&search='.urlencode($search).'&hospital='.$hospital.'&specialization='.$specialization.'&city='.$city.'&lady_doctor='.$lady_doctor.'">'.$i.'</a></li>';
                            }
                        }
                        
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&search='.urlencode($search).'&hospital='.$hospital.'&specialization='.$specialization.'&city='.$city.'&lady_doctor='.$lady_doctor.'">'.$total_pages.'</a></li>';
                        }
                        ?>

                        <!-- Next Button -->
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&hospital=<?php echo $hospital; ?>&specialization=<?php echo $specialization; ?>&city=<?php echo $city; ?>&lady_doctor=<?php echo $lady_doctor; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <!-- Results Info -->
                <div class="text-center mt-3">
                    <p class="text-muted">
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_doctors); ?> of <?php echo $total_doctors; ?> doctors
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
   <?php include BASE_PATH.'/includes/footer.php';?>

   <script>
$(document).ready(function() {
    $('#citySelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search city...',
        allowClear: true,
        width: '100%'
    });
    
    $('#hospitalSelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search hospital...',
        allowClear: true,
        width: '100%'
    });
});
</script>
</body>
</html>