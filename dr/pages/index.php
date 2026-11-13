<?php 
// ============================================================
// 1. SEO FIX: Title aur Description set karein
// ============================================================
$GLOBALS['page_title'] = "SehatNexa — Find Trusted Doctors, Hospitals, Labs & Blood Banks";
$GLOBALS['page_description'] = "Pakistan's trusted healthcare directory. Find verified doctors, hospitals, labs, radiology centers and blood banks. Read reviews, check timings and contact details.";

// ============================================================
// 2. Header Include karein
// ============================================================
include '../includes/header.php'; 
$city_id = (int)$city_id;

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

// Specialities Query
$specific_types = "'Cardiologist (Heart Specialist)', 'Neurologist', 'Pulmonologist', 'Nephrologist', 'Rheumatologist', 'ENT Specialist'";
$categories_query = "SELECT dct.dr_cat_type_id, dct.type 
                     FROM dr_cat_types dct 
                     WHERE dct.type IN ($specific_types)
                     ORDER BY dct.dr_cat_type_id ASC";
$categories_result = mysqli_query($con, $categories_query);
?>

<style>
    /* Urdu dropdown styling */
    .urdu-text { font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Arial', sans-serif; font-size: 1.1rem; text-align: right; direction: rtl; }
    .eng-subtext { text-align: left; direction: ltr; display: block; font-size: 0.75rem; }

    /* ==========================================
       SEHATNEXA BRAND THEME (Based on Logo)
       Navy Blue: #0D2B6E | Green: #27C96A
       ========================================== */
    
    .section-bg-light { background-color: #F8FAFC; } /* Very light slate */
    .section-bg-white { background-color: #FFFFFF; }

    .brand-heading { color: #0D2B6E !important; font-weight: 800; }
    .brand-subheading { color: #64748b !important; font-weight: 500; }

    /* Modern Brand Cards */
    .brand-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 4px 20px rgba(13, 43, 110, 0.03);
        transition: all 0.3s ease;
        overflow: hidden;
        height: 100%;
    }
    
    .brand-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(13, 43, 110, 0.1);
        border-color: #27C96A; /* Green Border on hover */
    }

    .brand-card-body {
        padding: 24px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        height: 100%;
    }

    .brand-icon-box {
        width: 65px; height: 65px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px;
        font-size: 1.8rem;
        background: linear-gradient(135deg, rgba(13,43,110,0.05) 0%, rgba(39,201,106,0.1) 100%);
        color: #0D2B6E;
        transition: all 0.3s ease;
    }

    .brand-card:hover .brand-icon-box {
        background: linear-gradient(135deg, #0D2B6E 0%, #27C96A 100%);
        color: #fff;
    }

    .brand-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .brand-meta {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .brand-btn {
        margin-top: auto;
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        background: #F1F5F9;
        color: #0D2B6E;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        transition: 0.2s;
        display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    }

    .brand-card:hover .brand-btn {
        background: #0D2B6E;
        color: #fff;
    }

    /* Service Cards (Top) */
    .service-card-new {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #E2E8F0;
        transition: 0.3s;
        display: block;
        height: 100%;
    }
    .service-card-new:hover {
        border-color: #0D2B6E;
        box-shadow: 0 10px 30px rgba(13,43,110,0.08);
    }
    .service-card-new .icon-wrap {
        width: 60px; height: 60px;
        border-radius: 50%;
        background: rgba(39,201,106,0.1);
        color: #27C96A;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
        transition: 0.3s;
    }
    .service-card-new:hover .icon-wrap {
        background: #27C96A;
        color: #fff;
    }
</style>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/home.css?v=<?= @filemtime(__DIR__ . '/style/home.css') ?: time() ?>">

<style>
/* Brand Root Colors */
:root {
    --brand-navy:       <?= $color1 ?>;
    --brand-teal:       <?= $color2 ?>;
    --brand-green:      <?= $color3 ?>;
    --brand-teal-green: <?= $color4 ?>;
    --brand-dark:       <?= $color5 ?>;
    --brand-light-blue: <?= $color6 ?>;
    --brand-light-green:<?= $color7 ?>;
}
</style>

    <!-- Hero Section (Kept same as before) -->
    <div class="hero-section">
        <div class="hero-background">
            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/hosp.jpg" alt="SehatNexa Healthcare Directory" class="hero-image">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-7">
                        <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill" data-aos="fade-down">WELCOME TO SEHATNEXA</span>
                        <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">Find Trusted Doctors, <br>Hospitals &amp; Labs</h1>
                        <p class="hero-text" data-aos="fade-up" data-aos-delay="400">Experience world-class healthcare with our team of expert doctors and state-of-the-art facilities designed for your comfort and recovery.</p>
                        <div data-aos="fade-up" data-aos-delay="600">
                            <div class="hero-city-wrap me-3">
                                <i class="fas fa-map-marker-alt hero-city-icon"></i>
                                <select class="hero-city-select" id="heroCitySelect">
                                    <option value="">Select City</option>
                                    <?php
                                    $hero_cities_query = "SELECT city_id, city_name FROM cities ORDER BY city_name ASC";
                                    $hero_cities_result = mysqli_query($con, $hero_cities_query);
                                    if ($hero_cities_result && mysqli_num_rows($hero_cities_result) > 0) {
                                        while ($hero_city = mysqli_fetch_assoc($hero_cities_result)) {
                                            $hero_selected = ($hero_city['city_id'] == $city_id) ? 'selected' : '';
                                            echo '<option value="' . $hero_city['city_id'] . '" ' . $hero_selected . '>' . htmlspecialchars($hero_city['city_name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <a href="about" class="btn btn-outline-light rounded-pill px-4 fw-bold d-inline-block mob-layout">Learn More</a>
                        </div>
                    </div>
                    <div class="col-lg-5" data-aos="fade-left" data-aos-delay="400">
                        <div class="hero-form">
                            <h4 class="mb-4 text-dark fw-bold">Find Your Doctor</h4>
                            <form action="doctors" method="GET" class="search-form">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Select City</label>
                                        <select class="form-select" name="city" required id="cityselect">
                                            <option value="">Choose City</option>
                                            <?php
                                            $cities_query = "SELECT * FROM cities ORDER BY city_name ASC";
                                            $cities_result = mysqli_query($con, $cities_query);
                                            if ($cities_result && mysqli_num_rows($cities_result) > 0) {
                                                while($city = mysqli_fetch_assoc($cities_result)) {
                                                    $selected = ($city['city_id'] == $city_id) ? 'selected' : '';
                                                    echo '<option value="' . $city['city_id'] . '" ' . $selected . '>' . htmlspecialchars($city['city_name']) . '</option>';
                                                }
                                            } else {
                                                echo '<option value="" disabled>No Record Found</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Search Doctor</label>
                                        <input type="text" class="form-control" name="search" placeholder="Enter doctor name...">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Filter by Disease / Treatment</label>
                                        <div class="input-with-icon position-relative">
                                            <input type="text" class="form-control" id="specializationSearch" placeholder="Search disease (e.g., دل)..." autocomplete="off" dir="auto" style="padding-right:36px;">
                                            <i class="fas fa-times" id="specializationClear" title="Clear" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:#94a3b8; font-size:0.85rem; z-index:10; display:none; transition:color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'"></i>
                                            <input type="hidden" name="specialization" id="specializationValue" value="<?php echo $specialization ?? ''; ?>">
                                            <div class="position-absolute w-100 bg-white border border-top-0 shadow-sm" id="specializationDropdown" style="display: none; z-index: 1000; max-height: 250px; overflow-y: auto;"></div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mt-3">
                                    <i class="fas fa-search me-2"></i>Search Doctors
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <section class="section-padding section-bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="brand-heading mb-2">Our Healthcare Services</h2>
                <p class="brand-subheading">Comprehensive medical care at your fingertips</p>
            </div>
            <div class="row g-4 justify-content-center" data-aos="fade-up" data-aos-delay="200">
                <!-- Service 1 -->
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>hospitals" class="text-decoration-none">
                        <div class="service-card-new p-4">
                            <div class="icon-wrap"><i class="fas fa-hospital"></i></div>
                            <h3 class="brand-title">Hospitals</h3>
                            <p class="text-muted small mb-0">Find top-rated hospitals with advanced medical facilities</p>
                        </div>
                    </a>
                </div>
                <!-- Service 2 -->
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>doctors" class="text-decoration-none">
                        <div class="service-card-new p-4">
                            <div class="icon-wrap"><i class="fas fa-user-md"></i></div>
                            <h3 class="brand-title">Doctors</h3>
                            <p class="text-muted small mb-0">Connect with experienced medical specialists</p>
                        </div>
                    </a>
                </div>
                <!-- Service 3 -->
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>labs" class="text-decoration-none">
                        <div class="service-card-new p-4">
                            <div class="icon-wrap"><i class="fas fa-flask"></i></div>
                            <h3 class="brand-title">Laboratories</h3>
                            <p class="text-muted small mb-0">Advanced diagnostic and testing services</p>
                        </div>
                    </a>
                </div>
                <!-- Service 4 -->
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>blood-banks" class="text-decoration-none">
                        <div class="service-card-new p-4">
                            <div class="icon-wrap"><i class="fas fa-tint"></i></div>
                            <h3 class="brand-title">Blood Banks</h3>
                            <p class="text-muted small mb-0">Life-saving blood donation and services</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Ad Space 1 -->
    <div class="container my-4" data-aos="fade-up">
        <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
            <span class="text-muted small">Advertisement Space</span>
        </div>
    </div>

    <!-- Specialities Section -->
    <section class="section-padding section-bg-white">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="brand-heading mb-2"><i class="fas fa-stethoscope text-success me-2"></i>Specialities</h2>
                <p class="brand-subheading">Explore our specialized medical departments</p>
            </div>
            <div class="row g-3 justify-content-center" data-aos="fade-up" data-aos-delay="200">
                <?php 
                if ($categories_result && mysqli_num_rows($categories_result) > 0) {
                    while($spec = mysqli_fetch_assoc($categories_result)) {
                        $cat_id = $spec['dr_cat_type_id'];
                        $cat_name = $spec['type'];
                ?>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="brand-card">
                            <div class="brand-card-body">
                                <div class="brand-icon-box">
                                    <i class="fas fa-heartbeat"></i>
                                </div>
                                <h3 class="brand-title" title="<?= htmlspecialchars($cat_name) ?>">
                                    <?= htmlspecialchars($cat_name) ?>
                                </h3>
                                <a href="<?=BASE_URL?>doctors?search=&city=<?=$city_id?>&hospital=&specialization=<?= $cat_id ?>" class="brand-btn">
                                    DEPARTMENT <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } } else { echo '<div class="col-12 text-center text-muted">No Specialities Found</div>'; } ?>
            </div>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="<?=BASE_URL?>all-specialities" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                Explore All Specialities
            </a>
        </div>
    </section>

    <!-- Top Hospitals Section -->
    <section class="section-padding section-bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="brand-heading mb-2"><i class="fas fa-hospital-alt text-primary me-2"></i>Top Hospitals</h2>
                <p class="brand-subheading">Find the best healthcare facilities in your area</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php
                if($city_id > 0){
                    $hospitals_query = "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id LEFT JOIN users u ON u.user_id = h.user_id WHERE u.status = 1 AND h.approve = 1 AND h.city_id = $city_id ORDER BY h.hospital_id DESC LIMIT 6";
                }else{
                    $hospitals_query = "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id LEFT JOIN users u ON u.user_id = h.user_id WHERE u.status = 1 AND h.approve = 1 ORDER BY h.hospital_id DESC LIMIT 6";
                }
                $hospitals_result = mysqli_query($con, $hospitals_query);
                $has_hospitals = $hospitals_result && mysqli_num_rows($hospitals_result) > 0;
                if ($has_hospitals) {
                while($hospital = mysqli_fetch_assoc($hospitals_result)) {
                    $hosp_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $hospital['user_id'] ."'");
                    $hosp_stars = mysqli_fetch_assoc($hosp_stars_q)['stars'];
                    $hospital_rating = $hosp_stars ? number_format((float)$hosp_stars, 1) : 'New';
                ?>
                <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up">
                    <div class="brand-card">
                        <div class="brand-card-body">
                            <div class="brand-icon-box">
                                <i class="fas fa-hospital-alt"></i>
                            </div>
                            <h3 class="brand-title text-truncate w-100"><?php echo htmlspecialchars($hospital['hospital_name']); ?></h3>
                            <div class="brand-meta">
                                <span><i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($hospital['city_name']); ?></span>
                                <span class="text-warning fw-bold"><i class="fas fa-star"></i> <?php echo $hospital_rating; ?> Rating</span>
                            </div>
                            <a href="hospital-detail?hospital_id=<?php echo $hospital['hospital_id']; ?>" class="brand-btn">
                                VIEW DETAILS
                            </a>
                        </div>
                    </div>
                </div>
                <?php } } else { echo '<div class="col-12 text-center text-muted">No Hospitals Found</div>'; } ?>
            </div>
            <?php if ($has_hospitals): ?>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="hospitals" class="btn btn-outline-primary rounded-pill px-4 fw-bold">View All Hospitals</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Ad Space 2 -->
    <div class="container my-4" data-aos="fade-up">
        <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
            <span class="text-muted small">Advertisement Space</span>
        </div>
    </div>

    <!-- Top Doctors Section -->
    <section class="section-padding section-bg-white">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="brand-heading mb-2">Top Doctors</h2>
                <p class="brand-subheading">Connect with experienced medical professionals</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php
                if($city_id > 0){
                     $doctors_query = "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id LEFT JOIN users u ON d.user_id = u.user_id WHERE u.status = 1 AND d.approve=1 AND d.city_id=$city_id ORDER BY d.doctor_id DESC LIMIT 6";
                }else{
                     $doctors_query = "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id LEFT JOIN users u ON d.user_id = u.user_id WHERE u.status = 1 AND d.approve=1 ORDER BY d.doctor_id DESC LIMIT 6";
                }
                $doctors_result = mysqli_query($con, $doctors_query);
                $has_doctors = $doctors_result && mysqli_num_rows($doctors_result) > 0;
                if ($has_doctors) {
                while($doctor = mysqli_fetch_assoc($doctors_result)) {
                    $doct_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $doctor['user_id'] ."'");
                    $doct_stars = mysqli_fetch_assoc($doct_stars_q)['stars'];
                    $doctor_rating = $doct_stars ? number_format((float)$doct_stars, 1) : 'New';
                    $doctor_specialization = !empty($doctor['specialization']) ? $doctor['specialization'] : 'Medical Specialist';
                ?>
                <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up">
                    <div class="brand-card">
                        <div class="brand-card-body">
                            <!-- Doctor Image Instead of Icon -->
                            <div class="mb-3" style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 3px solid rgba(39,201,106,0.2);">
                                <?php if (!empty($doctor['doctor_pic'])): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" alt="<?php echo $doctor['doctor_name']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-primary"><i class="fas fa-user-md fs-3"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="badge bg-light text-dark border mb-2"><?php echo htmlspecialchars($doctor_specialization); ?></div>
                            <h3 class="brand-title text-truncate w-100"><?php echo htmlspecialchars($doctor['doctor_name']); ?></h3>
                            <div class="brand-meta">
                                <span><i class="fas fa-map-marker-alt text-primary"></i> <?php echo htmlspecialchars($doctor['city_name']); ?></span>
                                <span class="text-warning fw-bold"><i class="fas fa-star"></i> <?php echo $doctor_rating; ?> Rating</span>
                            </div>
                            <a href="doctor-detail?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="brand-btn">
                                VIEW PROFILE
                            </a>
                        </div>
                    </div>
                </div>
                <?php } } else { echo '<div class="col-12 text-center text-muted">No Doctors Found</div>'; } ?>
            </div>
            <?php if ($has_doctors): ?>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="doctors" class="btn btn-outline-primary rounded-pill px-4 fw-bold">View All Doctors</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Laboratories Section -->
    <section class="section-padding section-bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="brand-heading mb-2">Laboratories</h2>
                <p class="brand-subheading">Advanced diagnostic and testing services</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php
                if($city_id > 0){
                    $labs_query = "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id LEFT JOIN users u ON u.user_id = l.user_id WHERE u.status = 1 AND l.approve = 1 AND l.city_id=$city_id ORDER BY l.lab_id DESC LIMIT 6";
                }else{
                    $labs_query = "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id LEFT JOIN users u ON u.user_id = l.user_id WHERE u.status = 1 AND l.approve = 1 ORDER BY l.lab_id DESC LIMIT 6";
                }
                $labs_result = mysqli_query($con, $labs_query);
                $has_labs = $labs_result && mysqli_num_rows($labs_result) > 0;
                if ($has_labs) {
                while($lab = mysqli_fetch_assoc($labs_result)) {
                    $lab_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $lab['user_id'] ."'");
                    $lab_stars = mysqli_fetch_assoc($lab_stars_q)['stars'];
                    $lab_rating = $lab_stars ? number_format((float)$lab_stars, 1) : 'New';
                ?>
                <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up">
                    <div class="brand-card">
                        <div class="brand-card-body">
                            <div class="brand-icon-box" style="color:#27C96A;">
                                <i class="fas fa-flask"></i>
                            </div>
                            <h3 class="brand-title text-truncate w-100"><?php echo htmlspecialchars($lab['lab_name']); ?></h3>
                            <div class="brand-meta">
                                <span><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($lab['city_name']); ?></span>
                                <span class="text-warning fw-bold"><i class="fas fa-star"></i> <?php echo $lab_rating; ?> Rating</span>
                            </div>
                            <a href="lab-detail?lab_id=<?php echo $lab['lab_id']; ?>" class="brand-btn">
                                VIEW DETAILS
                            </a>
                        </div>
                    </div>
                </div>
                <?php } } else { echo '<div class="col-12 text-center text-muted">No Labs Found</div>'; } ?>
            </div>
            <?php if ($has_labs): ?>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="labs" class="btn btn-outline-primary rounded-pill px-4 fw-bold">View All Laboratories</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Radiology Section -->
    <section class="section-padding section-bg-white">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="brand-heading mb-2">Radiology Centers</h2>
                <p class="brand-subheading">Advanced imaging and diagnostic services</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php
                if($city_id > 0){
                    $rad_query = "SELECT r.*, c.city_name FROM radiology r LEFT JOIN cities c ON r.city_id = c.city_id LEFT JOIN users u ON u.user_id = r.user_id WHERE u.status = 1 AND r.approve = 1 AND r.city_id=$city_id ORDER BY r.rad_id DESC LIMIT 6";
                }else{
                    $rad_query = "SELECT r.*, c.city_name FROM radiology r LEFT JOIN cities c ON r.city_id = c.city_id LEFT JOIN users u ON u.user_id = r.user_id WHERE u.status = 1 AND r.approve = 1 ORDER BY r.rad_id DESC LIMIT 6";
                }
                $rad_result = mysqli_query($con, $rad_query);
                $has_rad = $rad_result && mysqli_num_rows($rad_result) > 0;
                if ($has_rad) {
                while($rad = mysqli_fetch_assoc($rad_result)) {
                    $rad_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $rad['user_id'] ."'");
                    $rad_stars = mysqli_fetch_assoc($rad_stars_q)['stars'];
                    $rad_rating = $rad_stars ? number_format((float)$rad_stars, 1) : 'New';
                ?>
                <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up">
                    <div class="brand-card">
                        <div class="brand-card-body">
                            <div class="brand-icon-box" style="color:#0D2B6E;">
                                <i class="fas fa-x-ray"></i>
                            </div>
                            <h3 class="brand-title text-truncate w-100"><?php echo htmlspecialchars($rad['rad_name']); ?></h3>
                            <div class="brand-meta">
                                <span><i class="fas fa-map-marker-alt text-primary"></i> <?php echo htmlspecialchars($rad['city_name']); ?></span>
                                <span class="text-warning fw-bold"><i class="fas fa-star"></i> <?php echo $rad_rating; ?> Rating</span>
                            </div>
                            <a href="radiology-detail?rad_id=<?php echo $rad['rad_id']; ?>" class="brand-btn">
                                VIEW DETAILS
                            </a>
                        </div>
                    </div>
                </div>
                <?php } } else { echo '<div class="col-12 text-center text-muted">No Centers Found</div>'; } ?>
            </div>
            <?php if ($has_rad): ?>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="radiology-list" class="btn btn-outline-primary rounded-pill px-4 fw-bold">View All Radiology Centers</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Blood Banks Section -->
    <section class="section-padding section-bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="brand-heading mb-2">Blood Banks</h2>
                <p class="brand-subheading">Life-saving blood donation and supply services</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php
                if($city_id > 0){
                    $blood_banks_query = "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id LEFT JOIN users u ON u.user_id = bb.user_id WHERE u.status = 1 AND bb.approve = 1 AND bb.city_id=$city_id ORDER BY bb.bb_id DESC LIMIT 6";
                }else{
                    $blood_banks_query = "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id LEFT JOIN users u ON u.user_id = bb.user_id WHERE u.status = 1 AND bb.approve = 1 ORDER BY bb.bb_id DESC LIMIT 6";
                }
                $blood_banks_result = mysqli_query($con, $blood_banks_query);
                $has_blood_banks = $blood_banks_result && mysqli_num_rows($blood_banks_result) > 0;
                if ($has_blood_banks) {
                while($blood_bank = mysqli_fetch_assoc($blood_banks_result)) {
                    $bb_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $blood_bank['user_id'] ."'");
                    $bb_stars = mysqli_fetch_assoc($bb_stars_q)['stars'];
                    $blood_rating = $bb_stars ? number_format((float)$bb_stars, 1) : 'New';
                ?>
                <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up">
                    <div class="brand-card">
                        <div class="brand-card-body">
                            <div class="brand-icon-box" style="color:#EF4444;">
                                <i class="fas fa-tint"></i>
                            </div>
                            <h3 class="brand-title text-truncate w-100"><?php echo htmlspecialchars($blood_bank['bb_name']); ?></h3>
                            <div class="brand-meta">
                                <span><i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($blood_bank['city_name']); ?></span>
                                <span class="text-warning fw-bold"><i class="fas fa-star"></i> <?php echo $blood_rating; ?> Rating</span>
                            </div>
                            <a href="blood-bank-detail?blood_bankid=<?php echo $blood_bank['bb_id']; ?>" class="brand-btn">
                                VIEW DETAILS
                            </a>
                        </div>
                    </div>
                </div>
                <?php } } else { echo '<div class="col-12 text-center text-muted">No Blood Banks Found</div>'; } ?>
            </div>
            <?php if ($has_blood_banks): ?>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="blood-banks" class="btn btn-outline-primary rounded-pill px-4 fw-bold">View All Blood Banks</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Ad Space 3 -->
    <div class="container my-4" data-aos="fade-up">
        <div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;">
            <span class="text-muted small">Advertisement Space</span>
        </div>
    </div>

    <!-- Reviews Section -->
    <section class="section-padding d-none" id="reviews" style="background-color: #F8FAFC;">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="brand-heading mb-2">Patient Reviews</h2>
                <p class="brand-subheading">What our patients say about their experience</p>
            </div>
            
            <!-- Reviews Carousel -->
            <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-aos="fade-up" data-aos-delay="200">
                <div class="carousel-inner">
                    <?php
                    $reviews_query = "SELECT dr.* FROM feedback dr WHERE dr.status = 1 and user_id=1 ORDER BY dr.feedback_id DESC LIMIT 6";
                    $reviews_result = mysqli_query($con, $reviews_query);
                    $reviews_array = [];
                    while($review = mysqli_fetch_assoc($reviews_result)) { $reviews_array[] = $review; }
                    $grouped_reviews = array_chunk($reviews_array, 3);
                    foreach($grouped_reviews as $index => $review_group) {
                        $active_class = $index == 0 ? 'active' : '';
                    ?>
                    <div class="carousel-item <?php echo $active_class; ?>">
                        <div class="row justify-content-center">
                            <?php foreach($review_group as $review) { ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="brand-card p-4 text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-3">
                                        <div class="text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 me-3" style="width: 50px; height: 50px; background: #0D2B6E;">
                                            <?php echo strtoupper(substr($review['commenter_name'], 0, 1)); ?>
                                        </div>
                                        <div class="text-start">
                                            <h5 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($review['commenter_name']); ?></h5>
                                            <div class="text-warning small">
                                                <?php for($i = 1; $i <= 5; $i++) { ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['stars'] ? '' : 'text-muted opacity-25'; ?>"></i>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted fst-italic mb-3">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                                    <small class="text-secondary fw-semibold mt-auto"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include BASE_PATH.'/includes/footer.php';?>

<!-- Scripts -->
<script>
$(document).ready(function() {
    $('#cityselect').select2({ theme: 'bootstrap-5', placeholder: 'Search city...', allowClear: true, width: '100%' });
    $('#heroCitySelect').select2({ theme: 'bootstrap-5', placeholder: 'Select City', allowClear: false, width: '100%', minimumResultsForSearch: 0, dropdownCssClass: 'hero-city-dropdown' }).on('change', function() {
        var cityId = $(this).val();
        if (!cityId) return;
        fetch('<?php echo BASE_URL; ?>includes/set-city.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ city_id: parseInt(cityId) })
        }).then(function(response) { return response.json(); }).then(function(data) { if (data.success) { location.reload(); } });
    });
});
</script>

<script>
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
    const currentSpecialization = '<?php echo $specialization ?? ''; ?>';
    if (currentSpecialization) {
        const selectedOption = allOptions.find(option => option.value === currentSpecialization);
        if (selectedOption) { specializationSearch.value = selectedOption.text; }
    }
}

function filterSpecializations(searchTerm) {
    const filtered = allOptions.filter(option => option.text.toLowerCase().includes(searchTerm.toLowerCase()) || option.dataText.toLowerCase().includes(searchTerm.toLowerCase()));
    if (searchTerm === '') { specializationDropdown.style.display = 'none'; return; }
    if (filtered.length > 0) {
        let html = '';
        filtered.forEach(option => {
            html += `<div class="dropdown-item py-2 px-3" style="cursor: pointer;" data-value="${option.value}" data-text="${option.text}"><div class="fw-bold urdu-text mb-1">${option.text}</div><small class="text-muted eng-subtext">${option.dataText}</small></div>`;
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

document.addEventListener('DOMContentLoaded', function() {
    initializeSpecializationOptions();
    var clearBtn = document.getElementById('specializationClear');
    if (specializationSearch.value) { clearBtn.style.display = 'block'; }
    clearBtn.addEventListener('click', function() {
        specializationSearch.value = '';
        specializationValue.value = '';
        specializationDropdown.style.display = 'none';
        clearBtn.style.display = 'none';
        specializationSearch.focus();
    });
});
</script>