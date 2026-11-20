<?php 
// ============================================================
// 1. SEO FIX
// ============================================================
$GLOBALS['page_title'] = "Find Best Doctors in Pakistan — Book Appointment | SehatNexa";
$GLOBALS['page_description'] = "Search and find experienced doctors, specialists, and surgeons near you. Read patient reviews, check clinic timings, and book appointments online.";

// 2. Header Include karein
include '../includes/header.php'; 
?>

<?php
    // Handle search and filters (same as filterForm)
    $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    $hospital = isset($_GET['hospital']) ? (int)$_GET['hospital'] : '';
    $specialization = isset($_GET['specialization']) ? (int)$_GET['specialization'] : '';
    $city = isset($_GET['city']) ? (int)$_GET['city'] : $city_id;
    $lady_doctor = 0;
    if(isset($_GET['lady_doctor']) && $_GET['lady_doctor'] == 1){
        $lady_doctor = $_GET['lady_doctor'];
    }

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 12;
    $offset = ($page - 1) * $per_page;

    // Categories Query
    $categories_query = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type, dct.treatment 
                       FROM dr_categories dc 
                       LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id 
                       ORDER BY dc.cat_name, dct.type";
    $categories_result = mysqli_query($con, $categories_query);

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
                    'treatment' => $row['treatment']
                ];
            }
        }
    }
?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/doctor-list.css?v=<?= @filemtime(__DIR__ . '/style/doctor-list.css') ?: time() ?>">

<style>
    .section-padding{
        padding: 0 0 !important;
    }
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

    /* ============================================================
       ADVANCED SEARCH BAR (top) — AJAX powered
       ============================================================ */
    .adv-search-wrap {
        position: sticky; top: 70px; z-index: 60;
        margin: 14px 0 22px;
    }
    .adv-search-box {
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 14px 16px;
        box-shadow: 0 8px 24px rgba(13,43,110,.08);
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .adv-search-box:focus-within {
        border-color: #00A8CC;
        box-shadow: 0 8px 26px rgba(0,168,204,.18);
    }
    .adv-search-row {
        display: flex; align-items: center; gap: 10px;
        flex-wrap: wrap;
    }
    .adv-search-input-wrap {
        flex: 1; min-width: 220px;
        display: flex; align-items: center; gap: 10px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 9px 14px;
        transition: border-color .2s ease, background .2s ease;
    }
    .adv-search-input-wrap:focus-within {
        border-color: #1C3F8F;
        background: #fff;
    }
    .adv-search-input-wrap i.search-icon { color: #1C3F8F; font-size: 1rem; }
    .adv-search-input-wrap input {
        flex: 1; border: none; outline: none; background: transparent;
        font-size: .95rem; font-weight: 600; color: #0D2B6E;
    }
    .adv-search-input-wrap input::placeholder { color: #94a3b8; font-weight: 500; }
    .adv-search-clear {
        border: none; background: #e2e8f0; color: #64748b;
        width: 28px; height: 28px; border-radius: 50%;
        cursor: pointer; display: none; align-items: center; justify-content: center;
        transition: all .2s ease;
    }
    .adv-search-clear:hover { background: #cbd5e1; color: #1C3F8F; }
    .adv-search-clear.show { display: flex; }

    .adv-search-count {
        font-size: .72rem; font-weight: 700; color: #64748b;
        padding: 3px 10px; border-radius: 20px; background: #f1f5f9;
        white-space: nowrap;
    }
    .adv-search-count.has-results { background: #dcfce7; color: #15803d; }
    .adv-search-count.no-results  { background: #fee2e2; color: #b91c1c; }

    .adv-filters {
        display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;
        padding-top: 12px; border-top: 1px dashed #e2e8f0;
    }
    .adv-filter-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px; border-radius: 20px;
        font-size: .78rem; font-weight: 700;
        background: #f1f5f9; color: #475569;
        border: 1.5px solid transparent;
        cursor: pointer; transition: all .2s ease;
        user-select: none;
    }
    .adv-filter-chip:hover { background: #e2e8f0; }
    .adv-filter-chip.active {
        background: linear-gradient(135deg, #1C3F8F, #00A8CC);
        color: #fff; border-color: transparent;
        box-shadow: 0 4px 12px rgba(0,168,204,.3);
    }
    .adv-filter-chip i { font-size: .72rem; }

    .adv-search-actions {
        display: flex; gap: 8px; align-items: center;
        margin-left: auto;
    }
    .adv-search-toggle {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: 10px;
        border: 1.5px solid #e2e8f0; background: #fff;
        color: #475569; font-weight: 700; font-size: .8rem;
        cursor: pointer; transition: all .2s ease;
    }
    .adv-search-toggle:hover { border-color: #1C3F8F; color: #1C3F8F; }
    .adv-search-toggle i { font-size: .8rem; transition: transform .2s ease; }
    .adv-search-toggle.expanded i { transform: rotate(180deg); }

    /* Advanced filter selects inside chips area */
    .adv-select-group {
        display: flex; flex-wrap: wrap; gap: 8px; width: 100%;
        margin-bottom: 10px; padding-bottom: 10px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .adv-select-group .adv-select-wrap {
        flex: 1; min-width: 180px;
        display: flex; align-items: center; gap: 8px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 7px 12px;
        transition: border-color .2s ease, background .2s ease;
    }
    .adv-select-group .adv-select-wrap:focus-within {
        border-color: #1C3F8F;
        background: #fff;
    }
    .adv-select-group .adv-select-wrap i { color: #1C3F8F; font-size: .85rem; }
    .adv-select-group select {
        flex: 1; border: none; outline: none; background: transparent;
        font-size: .85rem; font-weight: 600; color: #0D2B6E;
        cursor: pointer;
    }
    .adv-select-group select option { color: #0D2B6E; }

    /* Hidden state for hospital select until city chosen */
    .adv-select-wrap.is-hidden {
        display: none !important;
    }
    .adv-select-wrap.is-disabled {
        opacity: .55;
        pointer-events: none;
    }

    /* Specialization input inside advanced chips */
    .adv-specialization-wrap {
        position: relative; flex: 1; min-width: 220px;
        display: flex; align-items: center; gap: 8px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 7px 12px;
        transition: border-color .2s ease, background .2s ease;
    }
    .adv-specialization-wrap:focus-within {
        border-color: #1C3F8F;
        background: #fff;
    }
    .adv-specialization-wrap i.adv-spec-icon { color: #1C3F8F; font-size: .85rem; }
    .adv-specialization-wrap input[type="text"] {
        flex: 1; border: none; outline: none; background: transparent;
        font-size: .85rem; font-weight: 600; color: #0D2B6E;
        padding-right: 24px;
    }
    .adv-specialization-wrap .adv-spec-clear {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        cursor: pointer; color: #94a3b8; font-size: .8rem;
        display: none; transition: color .2s;
    }
    .adv-specialization-wrap .adv-spec-clear:hover { color: #ef4444; }
    .adv-spec-dropdown {
        position: absolute; top: 100%; left: 0; right: 0;
        background: #fff; border: 1.5px solid #e2e8f0; border-top: none;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 8px 20px rgba(13,43,110,.1);
        max-height: 240px; overflow-y: auto;
        z-index: 1000; display: none;
    }
    .adv-spec-dropdown .adv-spec-item {
        padding: 9px 14px; cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s ease;
    }
    .adv-spec-dropdown .adv-spec-item:hover { background: #f8fafc; }
    .adv-spec-dropdown .adv-spec-item:last-child { border-bottom: none; }
    .adv-spec-dropdown .adv-spec-item .spec-title {
        font-weight: 700; color: #0D2B6E; font-size: .9rem;
        margin-bottom: 2px;
    }
    .adv-spec-dropdown .adv-spec-item .spec-sub {
        font-size: .72rem; color: #64748b;
    }
    .adv-spec-dropdown .adv-spec-empty {
        padding: 14px; text-align: center;
        color: #94a3b8; font-size: .85rem;
    }

    .dr-card.search-hidden { display: none !important; }
    .no-doctors.search-hidden { display: none !important; }

    mark.search-hit {
        background: #fef08a; color: #854d0e;
        padding: 1px 3px; border-radius: 3px;
    }

    .adv-search-no-results {
        grid-column: 1 / -1;
        text-align: center; padding: 48px 20px;
        background: #fff; border-radius: 16px;
        border: 1.5px dashed #e2e8f0;
    }

    #doctorsContainer { position: relative; transition: opacity .2s ease; }
    #doctorsContainer.is-loading { opacity: .45; pointer-events: none; }
    .grid-loader {
        position: absolute; inset: 0;
        display: none; align-items: center; justify-content: center;
        background: rgba(255,255,255,.6); z-index: 5;
        border-radius: 12px;
    }
    #doctorsContainer.is-loading .grid-loader { display: flex; }
    .grid-loader .spinner-border { width: 42px; height: 42px; color: #1C3F8F; }

    @media (max-width: 768px) {
        .adv-search-wrap { position: relative; top: auto; }
        .adv-search-count { display: none; }
        .adv-search-actions { margin-left: 0; width: 100%; }
        .adv-search-toggle { flex: 1; justify-content: center; }
        .adv-select-group .adv-select-wrap,
        .adv-specialization-wrap { min-width: 100%; }
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
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Our Expert Doctors</h1><br>
                    <p class="lead mb-0" data-aos="fade-up" data-aos-delay="200">Meet our team of qualified medical professionals</p>
                    <a href="<?php echo BASE_URL; ?>/pages/outside/add-doctor.php" class="btn btn-primary btn-lg add-new-doctor" style='display: none;'>Add New Doctor</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ADVANCED SEARCH BAR -->
    <div class="container">
        <div class="adv-search-wrap">
            <div class="adv-search-box">
                <div class="adv-search-row">
                    <div class="adv-search-input-wrap">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="advSearchInput" 
                               placeholder="Search doctors by name, specialization, hospital, city..." 
                               autocomplete="off"
                               aria-label="Advanced search doctors">
                        <button type="button" class="adv-search-clear" id="advSearchClear" aria-label="Clear search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <span class="adv-search-count" id="advSearchCount">0 results</span>

                    <div class="adv-search-actions">
                        <button type="button" class="adv-search-toggle" id="advFilterToggle" aria-expanded="false">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>
                </div>

                <div class="adv-filters" id="advFilterChips" style="display:none;">

                    <!-- City / Hospital / Specialization selects -->
                    <div class="adv-select-group">

                        <!-- CITY (always visible) -->
                        <div class="adv-select-wrap">
                            <i class="fas fa-map-marker-alt"></i>
                            <select id="advCitySelect">
                                <option value="">All Cities</option>
                                <?php
                                $adv_city_q = "SELECT city_id, city_name FROM cities WHERE status = 1 ORDER BY city_name ASC";
                                $adv_city_r = mysqli_query($con, $adv_city_q);
                                while($cr = mysqli_fetch_assoc($adv_city_r)) {
                                    $sel = ($city == $cr['city_id']) ? 'selected' : '';
                                    echo '<option value="'.$cr['city_id'].'" '.$sel.'>'.$cr['city_name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- HOSPITAL (hidden until city chosen) -->
                        <div class="adv-select-wrap is-hidden" id="advHospitalWrap">
                            <i class="fas fa-hospital"></i>
                            <select id="advHospitalSelect">
                                <option value="">All Hospitals</option>
                                <?php
                                // Only render if city is set on page load
                                if (!empty($city) && $city != 0) {
                                    $adv_hosp_q = "SELECT DISTINCT h.hospital_id, h.hospital_name 
                                                   FROM hospitals h 
                                                   LEFT JOIN users u ON u.user_id = h.user_id
                                                   WHERE u.status = 1 AND h.city_id = '$city'
                                                   ORDER BY h.hospital_name ASC";
                                    $adv_hosp_r = mysqli_query($con, $adv_hosp_q);
                                    while($hr = mysqli_fetch_assoc($adv_hosp_r)) {
                                        $sel = ($hospital == $hr['hospital_id']) ? 'selected' : '';
                                        echo '<option value="'.$hr['hospital_id'].'" '.$sel.'>'.$hr['hospital_name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- SPECIALIZATION -->
                        <div class="adv-specialization-wrap">
                            <i class="fas fa-stethoscope adv-spec-icon"></i>
                            <input type="text" id="advSpecializationSearch" 
                                   placeholder="Search disease / treatment..." 
                                   autocomplete="off" dir="auto">
                            <i class="fas fa-times adv-spec-clear" id="advSpecializationClear" title="Clear"></i>
                            <input type="hidden" id="advSpecializationValue" value="<?php echo $specialization; ?>">
                            <div class="adv-spec-dropdown" id="advSpecializationDropdown"></div>
                        </div>
                    </div>

                    <!-- Quick filter chips -->
                    <div class="adv-filter-chip" data-filter="lady" role="button" tabindex="0">
                        <i class="fas fa-venus"></i> Lady Doctors
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Doctors Section -->
    <section class="section-padding">
        <div class="container">
            <!-- Filter Section (hidden fallback) -->
            <div class="filter-section" data-aos="fade-up" style="display: none;">
                <form method="GET" action="" id="filterForm">
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
                            <label class="form-label text-muted small">Filter by Disease / Treatment</label>
                            <div class="input-with-icon position-relative">
                                <i class="fas fa-stethoscope input-icon"></i>
                                <input type="text" class="form-control" id="specializationSearch" placeholder="Search disease (e.g., دل)..." autocomplete="off" dir="auto" style="padding-right:36px;">
                                <i class="fas fa-times" id="specializationClear"
                                   title="Clear"
                                   style="position:absolute; right:12px; top:50%; transform:translateY(-50%);
                                          cursor:pointer; color:#94a3b8; font-size:0.85rem; z-index:10;
                                          display:none; transition:color 0.2s;"
                                   onmouseover="this.style.color='#ef4444'"
                                   onmouseout="this.style.color='#94a3b8'"></i>
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

            <!-- Ad Space 1 -->
            <div class="my-4" data-aos="fade-up">
                <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                    <span class="text-muted small">Advertisement Space</span>
                </div>
            </div>

            <!-- Doctors Grid (AJAX target) -->
            <div class="doctors-grid" id="doctorsContainer">
                <div class="grid-loader"><div class="spinner-border" role="status"></div></div>
                <?php
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
                        
                        $doctor_palette = [
                            ['#eef6ff', '#2f80ed'],
                            ['#f3efff', '#7c4dff'],
                            ['#eefbf5', '#10b981'],
                            ['#fff6e8', '#f59e0b'],
                            ['#fceef3', '#ec4899'],
                            ['#edf8ff', '#0ea5e9'],
                        ];

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

                        $dr_bg_icons = ['fa-stethoscope', 'fa-syringe', 'fa-pills', 'fa-kit-medical', 'fa-microscope', 'fa-heart-pulse'];
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
                ?>

                <div class="dr-card" style="--dr-bg: <?php echo $doctor_bg; ?>; --dr-accent: <?php echo $doctor_accent; ?>;" 
                     data-aos="fade-up" data-aos-delay="<?php echo (($doctor_index - 1) % 4) * 80; ?>"
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
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo htmlspecialchars($row['doctor_pic']); ?>" alt="<?php echo htmlspecialchars($row['doctor_name']); ?> - <?php echo htmlspecialchars($doctor_specialization); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="dr-avatar-icon"><i class="fas fa-user-md"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="dr-card-body">
                        <span class="dr-spec" title="<?php echo htmlspecialchars($doctor_specialization); ?>">
                            <i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars($doctor_specialization); ?>
                        </span>
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

            <!-- Ad Space 2 -->
            <div class="my-4" data-aos="fade-up">
                <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                    <span class="text-muted small">Advertisement Space</span>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container" id="paginationContainer">
                <nav aria-label="Doctors pagination">
                    <ul class="pagination">
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
                
                <div class="text-center mt-3">
                    <p class="text-muted">
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_doctors); ?> of <?php echo $total_doctors; ?> doctors
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include BASE_PATH.'/includes/footer.php';?>

   <script>
$(document).ready(function() {
    $('#citySelect').select2({ theme: 'bootstrap-5', placeholder: 'Search city...', allowClear: true, width: '100%' });
    $('#hospitalSelect').select2({ theme: 'bootstrap-5', placeholder: 'Search hospital...', allowClear: true, width: '100%' });
});
</script>

<script>
// JS for Treatment Dropdown (hidden fallback form)
const specializationSearch = document.getElementById('specializationSearch');
const specializationValue = document.getElementById('specializationValue');
const specializationDropdown = document.getElementById('specializationDropdown');
let allOptions = [];

function initializeSpecializationOptions() {
    allOptions = [];
    <?php foreach ($categories_data as $category_id => $category): ?>
        <?php foreach ($category['types'] as $type): ?>
            allOptions.push({
                value: '<?php echo $type['dr_cat_type_id']; ?>',
                text: '<?php echo addslashes($type['treatment']); ?>',
                dataText: '<?php echo addslashes($category['cat_name'] . ' - ' . $type['type']); ?>'
            });
        <?php endforeach; ?>
    <?php endforeach; ?>
    
    const currentSpecialization = '<?php echo $specialization; ?>';
    if (currentSpecialization && specializationSearch) {
        const selectedOption = allOptions.find(option => option.value === currentSpecialization);
        if (selectedOption) { specializationSearch.value = selectedOption.text; }
    }
}

if (specializationSearch && specializationDropdown) {
    function filterSpecializations(searchTerm) {
        const filtered = allOptions.filter(option => option.text.toLowerCase().includes(searchTerm.toLowerCase()) || option.dataText.toLowerCase().includes(searchTerm.toLowerCase()));
        if (searchTerm === '') { specializationDropdown.style.display = 'none'; return; }
        if (filtered.length > 0) {
            let html = '';
            filtered.forEach(option => {
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

    specializationSearch.addEventListener('input', function() {
        filterSpecializations(this.value);
        document.getElementById('specializationClear').style.display = this.value ? 'block' : 'none';
    });

    specializationDropdown.addEventListener('click', function(e) {
        const item = e.target.closest('.dropdown-item');
        if (item) {
            specializationSearch.value = item.getAttribute('data-text');
            specializationValue.value = item.getAttribute('data-value');
            specializationDropdown.style.display = 'none';
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.position-relative')) { specializationDropdown.style.display = 'none'; }
    });

    var clearBtn = document.getElementById('specializationClear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            specializationSearch.value = '';
            specializationValue.value = '';
            specializationDropdown.style.display = 'none';
            clearBtn.style.display = 'none';
            specializationSearch.focus();
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initializeSpecializationOptions();
});
</script>

<script>
/* ============================================================
   ADVANCED SEARCH — AJAX DB SEARCH
   ============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    const input        = document.getElementById('advSearchInput');
    const clearBtn     = document.getElementById('advSearchClear');
    const countEl      = document.getElementById('advSearchCount');
    const toggleBtn    = document.getElementById('advFilterToggle');
    const chipsWrap    = document.getElementById('advFilterChips');
    const container    = document.getElementById('doctorsContainer');
    const paginationEl = document.getElementById('paginationContainer');

    const advCitySelect   = document.getElementById('advCitySelect');
    const advHospitalSel  = document.getElementById('advHospitalSelect');
    const advHospitalWrap = document.getElementById('advHospitalWrap');
    const advSpecSearch   = document.getElementById('advSpecializationSearch');
    const advSpecClear    = document.getElementById('advSpecializationClear');
    const advSpecValue    = document.getElementById('advSpecializationValue');
    const advSpecDropdown = document.getElementById('advSpecializationDropdown');

    if (!input || !container) return;

    const activeFilters = { available: false, lady: false, experienced: false, rated: false, new: false };
    let currentRequest = null;
    let debounceTimer  = null;
    let specDebounce   = null;

    /* ---------- Hospital wrap visibility based on city ---------- */
    function updateHospitalVisibility() {
        if (!advCitySelect || !advHospitalWrap) return;
        const cityVal = advCitySelect.value;
        if (cityVal && cityVal !== '') {
            advHospitalWrap.classList.remove('is-hidden');
        } else {
            advHospitalWrap.classList.add('is-hidden');
            // reset hospital selection when city cleared
            if (advHospitalSel) {
                advHospitalSel.value = '';
                const mainHosp = document.getElementById('hospitalSelect');
                if (mainHosp) mainHosp.value = '';
            }
        }
    }

    /* ---------- Load hospitals by city via AJAX ---------- */
    function loadHospitalsByCity(cityId, preselectHospitalId) {
        if (!advHospitalSel) return;
        // reset options
        advHospitalSel.innerHTML = '<option value="">Loading...</option>';

        const payload = new URLSearchParams();
        payload.set('ajax', '1');
        payload.set('city_id', cityId);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo BASE_URL; ?>ajax/get-hospitals.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            advHospitalSel.innerHTML = '<option value="">All Hospitals</option>';
            if (xhr.status !== 200) return;
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success && res.hospitals && res.hospitals.length > 0) {
                    res.hospitals.forEach(function(h) {
                        const opt = document.createElement('option');
                        opt.value = h.hospital_id;
                        opt.textContent = h.hospital_name;
                        if (preselectHospitalId && preselectHospitalId == h.hospital_id) {
                            opt.selected = true;
                        }
                        advHospitalSel.appendChild(opt);
                    });
                }
            } catch (e) { /* ignore */ }
        };
        xhr.onerror = function() {
            advHospitalSel.innerHTML = '<option value="">All Hospitals</option>';
        };
        xhr.send(payload.toString());
    }

    /* ---------- Init hospital wrap on page load ---------- */
    updateHospitalVisibility();
    if (advCitySelect && advCitySelect.value) {
        const currentHosp = advHospitalSel ? advHospitalSel.value : '';
        // already server-rendered; just ensure visible
    }

    /* ---------- Build base params from current URL ---------- */
    function getBaseParams() {
        const params = new URLSearchParams(window.location.search);
        return {
            hospital:       params.get('hospital')       || '',
            specialization: params.get('specialization') || '',
            city:           params.get('city')           || '',
            lady_doctor:    params.get('lady_doctor')    || '',
            page:           params.get('page')           || ''
        };
    }

    /* ---------- AJAX fetch doctors ---------- */
    function fetchDoctors(overridePage) {
        const term = input.value.trim();
        const base = getBaseParams();

        const advCity   = advCitySelect  ? advCitySelect.value  : '';
        const advHosp   = advHospitalSel ? advHospitalSel.value : '';
        const advSpec   = advSpecValue   ? advSpecValue.value   : '';

        const payload = new URLSearchParams();
        payload.set('ajax', '1');
        payload.set('search', term);

        const finalCity   = advCity || base.city;
        const finalHosp   = advHosp || base.hospital;
        const finalSpec   = advSpec || base.specialization;

        if (finalHosp) payload.set('hospital', finalHosp);
        if (finalSpec) payload.set('specialization', finalSpec);
        if (finalCity) payload.set('city', finalCity);
        if (base.lady_doctor) payload.set('lady_doctor', base.lady_doctor);

        if (overridePage) {
            payload.set('page', overridePage);
        } else if (base.page && base.page !== '1') {
            payload.set('page', base.page);
        }

        Object.keys(activeFilters).forEach(k => {
            if (activeFilters[k]) payload.set('filter_' + k, '1');
        });

        if (currentRequest) currentRequest.abort();
        const xhr = new XMLHttpRequest();
        currentRequest = xhr;

        container.classList.add('is-loading');

        xhr.open('POST', '<?php echo BASE_URL; ?>ajax/search-doctors.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            container.classList.remove('is-loading');
            if (xhr.status !== 200) return;
            try {
                const res = JSON.parse(xhr.responseText);
                if (!res.success) return;

                const loader = container.querySelector('.grid-loader');
                container.innerHTML = '';
                if (loader) container.appendChild(loader);
                container.insertAdjacentHTML('beforeend', res.html);

                const pEl = document.getElementById('paginationContainer');
                if (pEl) {
                    if (res.pagination_html) {
                        pEl.outerHTML = res.pagination_html;
                    } else {
                        pEl.style.display = 'none';
                    }
                }

                if (res.total_doctors > 0) {
                    countEl.textContent = res.total_doctors + ' doctors';
                    countEl.classList.remove('no-results');
                    countEl.classList.add('has-results');
                } else {
                    countEl.textContent = '0 doctors';
                    countEl.classList.remove('has-results');
                    countEl.classList.add('no-results');
                }
            } catch (e) { /* ignore */ }
        };
        xhr.onerror = function() { container.classList.remove('is-loading'); };
        xhr.send(payload.toString());

        clearBtn.classList.toggle('show', !!term);
    }

    /* ---------- input debounce ---------- */
    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchDoctors, 350);
    });

    /* ---------- clear ---------- */
    clearBtn.addEventListener('click', function() {
        input.value = '';
        Object.keys(activeFilters).forEach(k => activeFilters[k] = false);
        chipsWrap.querySelectorAll('.adv-filter-chip').forEach(c => c.classList.remove('active'));
        fetchDoctors();
        input.focus();
    });

    /* ---------- Esc ---------- */
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            input.value = '';
            Object.keys(activeFilters).forEach(k => activeFilters[k] = false);
            chipsWrap.querySelectorAll('.adv-filter-chip').forEach(c => c.classList.remove('active'));
            fetchDoctors();
        }
    });

    /* ---------- Toggle chips ---------- */
    toggleBtn.addEventListener('click', function() {
        const expanded = this.classList.toggle('expanded');
        this.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        chipsWrap.style.display = expanded ? 'flex' : 'none';
    });

    /* ---------- Chips click ---------- */
    chipsWrap.querySelectorAll('.adv-filter-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            const key = this.dataset.filter;
            activeFilters[key] = !activeFilters[key];
            this.classList.toggle('active', activeFilters[key]);
            fetchDoctors();
        });
        chip.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
        });
    });

    /* ---------- Advanced City select ---------- */
    if (advCitySelect) {
        advCitySelect.addEventListener('change', function() {
            const cityVal = this.value;

            // mirror to filterForm city select
            const mainCity = document.getElementById('citySelect');
            if (mainCity) {
                mainCity.value = cityVal;
                if (window.jQuery && window.jQuery(mainCity).data('select2')) {
                    window.jQuery(mainCity).trigger('change.select2');
                }
            }

            if (cityVal && cityVal !== '') {
                // show hospital select + load hospitals for that city
                updateHospitalVisibility();
                loadHospitalsByCity(cityVal, '');
            } else {
                // hide hospital select + clear
                updateHospitalVisibility();
                if (advHospitalSel) advHospitalSel.innerHTML = '<option value="">All Hospitals</option>';
            }

            fetchDoctors();
        });
    }

    /* ---------- Advanced Hospital select ---------- */
    if (advHospitalSel) {
        advHospitalSel.addEventListener('change', function() {
            const mainHosp = document.getElementById('hospitalSelect');
            if (mainHosp) {
                mainHosp.value = this.value;
                if (window.jQuery && window.jQuery(mainHosp).data('select2')) {
                    window.jQuery(mainHosp).trigger('change.select2');
                }
            }
            fetchDoctors();
        });
    }

    /* ---------- Advanced Specialization dropdown ---------- */
    function renderAdvSpecDropdown(filterTerm) {
        if (!advSpecDropdown) return;
        const term = (filterTerm || '').toLowerCase().trim();
        let filtered = allOptions;
        if (term) {
            filtered = allOptions.filter(o =>
                (o.text || '').toLowerCase().includes(term) ||
                (o.dataText || '').toLowerCase().includes(term)
            );
        }
        if (filtered.length === 0) {
            advSpecDropdown.innerHTML = '<div class="adv-spec-empty">No diseases found</div>';
            advSpecDropdown.style.display = 'block';
            return;
        }
        let html = '';
        filtered.slice(0, 30).forEach(o => {
            html += `<div class="adv-spec-item" data-value="${o.value}" data-text="${o.text}">
                        <div class="spec-title">${o.text}</div>
                        <div class="spec-sub">${o.dataText}</div>
                    </div>`;
        });
        advSpecDropdown.innerHTML = html;
        advSpecDropdown.style.display = 'block';
    }

    if (advSpecSearch) {
        // populate current specialization text
        if (advSpecValue && advSpecValue.value && typeof allOptions !== 'undefined') {
            const found = allOptions.find(o => o.value === advSpecValue.value);
            if (found) advSpecSearch.value = found.text;
        }

        advSpecSearch.addEventListener('focus', function() {
            renderAdvSpecDropdown(this.value);
        });

        advSpecSearch.addEventListener('input', function() {
            advSpecClear.style.display = this.value ? 'block' : 'none';
            clearTimeout(specDebounce);
            specDebounce = setTimeout(() => {
                renderAdvSpecDropdown(this.value);
            }, 150);
        });

        advSpecSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                advSpecDropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.adv-specialization-wrap')) {
                advSpecDropdown.style.display = 'none';
            }
        });

        advSpecDropdown.addEventListener('click', function(e) {
            const item = e.target.closest('.adv-spec-item');
            if (item) {
                advSpecSearch.value = item.getAttribute('data-text');
                advSpecValue.value = item.getAttribute('data-value');
                advSpecDropdown.style.display = 'none';

                const mainSpecSearch = document.getElementById('specializationSearch');
                const mainSpecValue  = document.getElementById('specializationValue');
                if (mainSpecSearch) mainSpecSearch.value = item.getAttribute('data-text');
                if (mainSpecValue)  mainSpecValue.value  = item.getAttribute('data-value');

                fetchDoctors();
            }
        });

        if (advSpecClear) {
            advSpecClear.addEventListener('click', function() {
                advSpecSearch.value = '';
                advSpecValue.value = '';
                advSpecDropdown.style.display = 'none';
                advSpecClear.style.display = 'none';
                advSpecSearch.focus();

                const mainSpecSearch = document.getElementById('specializationSearch');
                const mainSpecValue  = document.getElementById('specializationValue');
                if (mainSpecSearch) mainSpecSearch.value = '';
                if (mainSpecValue)  mainSpecValue.value  = '';

                fetchDoctors();
            });
        }
    }

    /* ---------- Init count ---------- */
    countEl.textContent = container.querySelectorAll('.dr-card').length + ' doctors';
    countEl.classList.add('has-results');

    /* ---------- Delegated pagination click (AJAX) ---------- */
    document.addEventListener('click', function(e) {
        const link = e.target.closest('#paginationContainer .page-link');
        if (!link) return;
        const parentLi = link.closest('.page-item');
        if (!parentLi || parentLi.classList.contains('disabled')) return;
        const href = link.getAttribute('href');
        if (!href || href === '#') return;

        try {
            const url = new URL(href, window.location.origin + window.location.pathname);
            const pageParam = url.searchParams.get('page');
            if (!pageParam) return;
            e.preventDefault();
            fetchDoctors(pageParam);
            window.scrollTo({ top: container.offsetTop - 120, behavior: 'smooth' });
        } catch (err) { /* fallback default */ }
    });
});
</script>
</body>
</html>