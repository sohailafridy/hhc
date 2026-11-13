<?php 
// ============================================================
// Config hamesha include karein (BASE_URL, $con, session ke liye)
// header.php bhi include karega, lekin include_once duplicate nahi banayega
// ============================================================
include_once '../config.php';

// ============================================================
// City name fetch (title ke liye) — header.php se PEHLE
// ============================================================
$city_id_for_title = isset($_SESSION['city_id']) ? (int)$_SESSION['city_id'] : 0;
$current_city_name = '';
if ($city_id_for_title > 0 && isset($con)) {
    $city_name_q = mysqli_query($con, "SELECT city_name FROM cities WHERE city_id = $city_id_for_title LIMIT 1");
    if ($city_name_q && mysqli_num_rows($city_name_q) > 0) {
        $current_city_name = mysqli_fetch_assoc($city_name_q)['city_name'];
    }
}

// ============================================================
// $GLOBALS set karein (header.php inhein uthayega)
// ============================================================
$GLOBALS['page_title'] = $current_city_name 
    ? "Doctors, Hospitals, Labs & Blood Banks in {$current_city_name} | SehatNexa"
    : "Pakistan's Trusted Healthcare Directory — Doctors, Hospitals, Labs & Blood Banks | SehatNexa";

$GLOBALS['page_description'] = $current_city_name
    ? "Find the best doctors, hospitals, laboratories, radiology centers and blood banks in {$current_city_name}. Verified listings, patient reviews, timings and contact details — all in one place on SehatNexa."
    : "SehatNexa is Pakistan's trusted healthcare directory. Find verified doctors, hospitals, labs, radiology centers and blood banks near you. Read reviews, check timings and contact details.";

$GLOBALS['page_canonical'] = BASE_URL;
$GLOBALS['page_image']     = BASE_URL . 'admin/inc/uploads/default/hosp.jpg';

// FAQ Schema
$GLOBALS['page_schema'] = json_encode([
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => [
        ["@type" => "Question", "name" => "How do I find a doctor near me?", "acceptedAnswer" => ["@type" => "Answer", "text" => "Select your city from the dropdown, choose a specialization, and browse verified doctor profiles."]],
        ["@type" => "Question", "name" => "Are the doctors and hospitals listed on SehatNexa verified?", "acceptedAnswer" => ["@type" => "Answer", "text" => "Yes. Doctors are verified using their PMDC registration number, and hospitals are reviewed before their profiles are approved."]],
        ["@type" => "Question", "name" => "How can I find a blood bank in an emergency?", "acceptedAnswer" => ["@type" => "Answer", "text" => "Visit our Blood Banks section, filter by your city, and check the available blood stock. In a life-threatening emergency, always call Rescue 1122 first."]],
        ["@type" => "Question", "name" => "Can I book a lab test through SehatNexa?", "acceptedAnswer" => ["@type" => "Answer", "text" => "You can view available tests and their prices on each laboratory's profile. To book a test, use the WhatsApp button on the lab's page."]],
        ["@type" => "Question", "name" => "Is SehatNexa free to use?", "acceptedAnswer" => ["@type" => "Answer", "text" => "Yes, SehatNexa is completely free for patients. Our platform is supported by advertising."]]
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// ============================================================
// AB header.php include karein (jo <html><head><body> render karega)
// ============================================================
include '../includes/header.php';
$city_id = (int)$city_id;

// ============================================================
// Ab baqi DB queries (categories waghera)
// ============================================================
$categories_query = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type, dct.treatment 
                   FROM dr_categories dc 
                   LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id 
                   ORDER BY dc.cat_name, dct.type";
$categories_result = mysqli_query($con, $categories_query);

$categories_data = [];
if ($categories_result) {
    while ($row = mysqli_fetch_assoc($categories_result)) {
        if (!isset($categories_data[$row['dr_cat_id']])) {
            $categories_data[$row['dr_cat_id']] = ['cat_name' => $row['cat_name'], 'types' => []];
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

$specific_types = "'Cardiologist (Heart Specialist)', 'Neurologist', 'Pulmonologist', 'Nephrologist', 'Rheumatologist', 'ENT Specialist'";
$categories_query = "SELECT dct.dr_cat_type_id, dct.type FROM dr_cat_types dct WHERE dct.type IN ($specific_types) ORDER BY dct.dr_cat_type_id ASC";
$categories_result = mysqli_query($con, $categories_query);
?>

<style>
    .urdu-text { font-family: 'Jameel Noori Nastaleeq', 'Noto Nastaliq Urdu', 'Arial', sans-serif; font-size: 1.1rem; text-align: right; direction: rtl; }
    .eng-subtext { text-align: left; direction: ltr; display: block; font-size: 0.75rem; }
</style>

<?php include BASE_PATH.'/includes/menu.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/home.css?v=<?= @filemtime(__DIR__ . '/style/home.css') ?: time() ?>">

<style>
:root {
    --brand-navy:       <?= $color1 ?>;
    --brand-teal:       <?= $color2 ?>;
    --brand-green:      <?= $color3 ?>;
    --brand-teal-green: <?= $color4 ?>;
    --brand-dark:       <?= $color5 ?>;
    --brand-light-blue: <?= $color6 ?>;
    --brand-light-green:<?= $color7 ?>;
    --brand-gradient:   <?= $gradient_primary ?>;
    --brand-gradient-green: <?= $gradient_green ?>;
    --brand-gradient-full:  <?= $gradient_full ?>;
}
</style>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-background">
        <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/hosp.jpg" alt="SehatNexa healthcare directory — doctors, hospitals and labs in Pakistan" class="hero-image">
        <div class="hero-overlay"></div>
    </div>
    <div class="hero-content">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-7">
                    <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill" data-aos="fade-down">
                        <i class="fas fa-heartbeat me-1"></i> WELCOME TO SEHATNEXA
                    </span>
                    <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">
                        Find Trusted Doctors, <br>Hospitals &amp; Labs
                        <?php if ($current_city_name): ?> in <?= htmlspecialchars($current_city_name) ?><?php endif; ?>
                    </h1>
                    <p class="hero-text" data-aos="fade-up" data-aos-delay="400">
                        Pakistan's trusted healthcare directory — verified doctors, hospitals, laboratories, radiology centers and blood banks. Read reviews, check timings and connect instantly.
                    </p>
                    <div data-aos="fade-up" data-aos-delay="600">
                        <div class="hero-city-wrap me-3">
                            <i class="fas fa-map-marker-alt hero-city-icon"></i>
                            <select class="hero-city-select" id="heroCitySelect" aria-label="Select your city">
                                <option value="">Select City</option>
                                <?php
                                $hero_cities_result = mysqli_query($con, "SELECT city_id, city_name FROM cities ORDER BY city_name ASC");
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
                        <h2 class="mb-4 text-dark fw-bold" style="font-size:1.25rem;">Find Your Doctor</h2>
                        <form action="doctors" method="GET" class="search-form">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Select City</label>
                                    <select class="form-select" name="city" required id="cityselect">
                                        <option value="">Choose City</option>
                                        <?php
                                        $cities_result = mysqli_query($con, "SELECT * FROM cities ORDER BY city_name ASC");
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

<!-- Services -->
<section id="top-services" class="section-padding" style="background: var(--brand-light-blue);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title heading-color">Our Healthcare Services<?php if ($current_city_name): ?> in <?= htmlspecialchars($current_city_name) ?><?php endif; ?></h2>
            <p class="section-subtitle normal-color">Comprehensive medical care at your fingertips</p>
        </div>
        <div class="row g-4 justify-content-center" data-aos="fade-up" data-aos-delay="200">
            <div class="col-lg-3 col-md-6"><a href="<?=BASE_URL?>hospitals" class="service-card text-decoration-none"><div class="service-card-body"><div class="service-icon-wrapper"><i class="fas fa-hospital"></i></div><h3 class="service-title">Hospitals</h3><p class="service-description">Find top-rated hospitals with advanced medical facilities</p><div class="service-arrow"><i class="fas fa-arrow-right"></i></div></div></a></div>
            <div class="col-lg-3 col-md-6"><a href="<?=BASE_URL?>doctors" class="service-card text-decoration-none"><div class="service-card-body"><div class="service-icon-wrapper"><i class="fas fa-user-md"></i></div><h3 class="service-title">Doctors</h3><p class="service-description">Connect with experienced medical specialists</p><div class="service-arrow"><i class="fas fa-arrow-right"></i></div></div></a></div>
            <div class="col-lg-3 col-md-6"><a href="<?=BASE_URL?>labs" class="service-card text-decoration-none"><div class="service-card-body"><div class="service-icon-wrapper"><i class="fas fa-flask"></i></div><h3 class="service-title">Laboratories</h3><p class="service-description">Advanced diagnostic and testing services</p><div class="service-arrow"><i class="fas fa-arrow-right"></i></div></div></a></div>
            <div class="col-lg-3 col-md-6"><a href="<?=BASE_URL?>blood-banks" class="service-card text-decoration-none"><div class="service-card-body"><div class="service-icon-wrapper"><i class="fas fa-tint"></i></div><h3 class="service-title">Blood Banks</h3><p class="service-description">Life-saving blood donation and services</p><div class="service-arrow"><i class="fas fa-arrow-right"></i></div></div></a></div>
        </div>
    </div>
</section>

<!-- Ad Slot -->
<div class="container my-4"><div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;"><span class="text-muted small">Advertisement</span></div></div>

<!-- Specialities -->
<section class="section-padding" style="background: var(--brand-light-blue);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title heading-color"><i class="fas fa-stethoscope me-3"></i>Specialities</h2>
            <p class="section-subtitle normal-color">Explore our specialized medical departments</p>
        </div>
        <div class="row g-3 justify-content-center" data-aos="fade-up" data-aos-delay="200">
            <?php 
            $bg_colors = ['#E8F7FF', '#F0FFF8', '#EFF6FF', '#F0FDFA', '#EEF2FF', '#E8F7FF', '#F0FFF8', '#EFF6FF'];
            $emojis = ['👴', '❤️', '🧠', '🧪', '🫁', '🫀', '🦴', '👁️', '🦷', '👶'];
            $index = 0;
            if ($categories_result && mysqli_num_rows($categories_result) > 0) {
                while($spec = mysqli_fetch_assoc($categories_result)) {
                    $cat_id = $spec['dr_cat_type_id'];
                    $cat_name = $spec['type'];
                    $bg_color = $bg_colors[$index % count($bg_colors)];
                    $emoji = $emojis[$index % count($emojis)];
                    $index++;
            ?>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="speciality-card">
                        <div class="speciality-card-body" style="background-color: <?= $bg_color ?>;">
                            <div class="speciality-icon"><span class="speciality-emoji"><?= $emoji ?></span></div>
                            <h3 class="speciality-title" title="<?= htmlspecialchars($cat_name) ?>"><?= htmlspecialchars($cat_name) ?></h3>
                            <a href="<?=BASE_URL?>doctors?search=&city=<?=$city_id?>&hospital=&specialization=<?= $cat_id ?>" class="speciality-btn"><i class="fas fa-heart"></i> DEPARTMENT</a>
                        </div>
                    </div>
                </div>
            <?php } } else { echo '<div class="col-12 text-center"><p>No Specialities Found</p></div>'; } ?>
        </div>
    </div>
    <div class="text-center mt-4" data-aos="fade-up"><a href="<?=BASE_URL?>all-specialities" class="btn-view-all btn-view-specialities"><i class="fas fa-stethoscope"></i> Explore All Specialities</a></div>
</section>

<!-- Top Hospitals -->
<section class="modern-cards section-padding" style="background: var(--brand-light-green);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title heading-color"><i class="fas fa-hospital-alt me-3"></i>Top Hospitals<?php if ($current_city_name): ?> in <?= htmlspecialchars($current_city_name) ?><?php endif; ?></h2>
            <p class="section-subtitle normal-color">Find the best healthcare facilities in your area</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $hospital_card_colors = ['#E8F7FF', '#F0FFF8', '#EFF6FF', '#F0FDFA', '#E8F7FF', '#F0FFF8'];
            $hospitals_query = $city_id > 0
                ? "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id LEFT JOIN users u ON u.user_id = h.user_id WHERE u.status = 1 AND h.approve = 1 AND h.city_id = $city_id ORDER BY h.hospital_id DESC LIMIT 6"
                : "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id LEFT JOIN users u ON u.user_id = h.user_id WHERE u.status = 1 AND h.approve = 1 ORDER BY h.hospital_id DESC LIMIT 6";
            $hospitals_result = mysqli_query($con, $hospitals_query);
            $hospital_index = 0;
            $has_hospitals = $hospitals_result && mysqli_num_rows($hospitals_result) > 0;
            if ($has_hospitals) {
                while($hospital = mysqli_fetch_assoc($hospitals_result)) {
                    $hosp_stars = mysqli_fetch_assoc(mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $hospital['user_id'] ."'"))['stars'];
                    $hospital_rating = $hosp_stars ? number_format((float)$hosp_stars, 1) : 'New';
                    $hospital_bg = $hospital_card_colors[$hospital_index % count($hospital_card_colors)];
                    $hospital_index++;
            ?>
            <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($hospital_index * 80); ?>">
                <div class="speciality-card hospital-mini-card">
                    <div class="speciality-card-body" style="background-color: <?php echo $hospital_bg; ?>;">
                        <div class="speciality-icon"><i class="fas fa-hospital-alt"></i></div>
                        <h3 class="speciality-title"><?php echo htmlspecialchars(substr($hospital['hospital_name'], 0, 25)); ?></h3>
                        <p class="hospital-mini-location"><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($hospital['city_name']); ?></p>
                        <div class="hospital-mini-rating"><i class="fas fa-star text-warning"></i><?php echo $hospital_rating; ?> Rating</div>
                        <a href="hospital-detail?hospital_id=<?php echo $hospital['hospital_id']; ?>" class="speciality-btn"><i class="fas fa-arrow-right"></i> DETAILS</a>
                    </div>
                </div>
            </div>
            <?php } } else { ?><div class="col-12"><div class="no-record-found">No Record Found</div></div><?php } ?>
        </div>
        <?php if ($has_hospitals): ?><div class="text-center mt-4" data-aos="fade-up"><a href="hospitals" class="btn-view-all btn-view-hospitals"><i class="fas fa-hospital-alt"></i> View All Hospitals</a></div><?php endif; ?>
    </div>
</section>

<!-- Ad Slot -->
<div class="container my-4"><div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;"><span class="text-muted small">Advertisement</span></div></div>

<!-- Top Doctors -->
<section class="modern-cards section-padding" style="background: var(--brand-light-blue);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title heading-color">Top Doctors<?php if ($current_city_name): ?> in <?= htmlspecialchars($current_city_name) ?><?php endif; ?></h2>
            <p class="section-subtitle normal-color">Connect with experienced medical professionals</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $doctor_card_colors = ['#E8F7FF', '#F0FFF8', '#EFF6FF', '#F0FDFA', '#EEF2FF', '#F0F9FF'];
            $doctors_query = $city_id > 0
                ? "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id LEFT JOIN users u ON d.user_id = u.user_id WHERE u.status = 1 AND d.approve=1 AND d.city_id=$city_id ORDER BY d.doctor_id DESC LIMIT 4"
                : "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id LEFT JOIN users u ON d.user_id = u.user_id WHERE u.status = 1 AND d.approve=1 ORDER BY d.doctor_id DESC LIMIT 6";
            $doctors_result = mysqli_query($con, $doctors_query);
            $doctor_index = 0;
            $has_doctors = $doctors_result && mysqli_num_rows($doctors_result) > 0;
            if ($has_doctors) {
                while($doctor = mysqli_fetch_assoc($doctors_result)) {
                    $doct_stars = mysqli_fetch_assoc(mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $doctor['user_id'] ."'"))['stars'];
                    $doctor_index++;
                    $doctor_rating = $doct_stars ? number_format((float)$doct_stars, 1) : 'New';
                    $doctor_specialization = !empty($doctor['specialization']) ? $doctor['specialization'] : 'Medical Specialist';
                    $doctor_bg = $doctor_card_colors[($doctor_index - 1) % count($doctor_card_colors)];
            ?>
            <div class="col-lg-2 col-md-3 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($doctor_index * 80); ?>">
                <div class="speciality-card doctor-mini-card">
                    <div class="speciality-card-body" style="background-color: <?php echo $doctor_bg; ?>;">
                        <div class="doctor-mini-media">
                            <?php if (!empty($doctor['doctor_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" alt="Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?> — <?php echo htmlspecialchars($doctor_specialization); ?> in <?php echo htmlspecialchars($doctor['city_name']); ?>" class="img-fluid">
                            <?php else: ?><div class="doctor-mini-icon-wrap"><i class="fas fa-user-md doctor-mini-icon"></i></div><?php endif; ?>
                        </div>
                        <div class="doctor-mini-content">
                            <div class="doctor-mini-specialty"><i class="fas fa-user-md"></i><?php echo htmlspecialchars($doctor_specialization); ?></div>
                            <h3 class="speciality-title"><?php echo htmlspecialchars($doctor['doctor_name']); ?></h3>
                            <div class="doctor-mini-meta"><i class="fas fa-location-dot"></i><?php echo htmlspecialchars($doctor['city_name']); ?></div>
                            <div class="doctor-mini-rating"><i class="fas fa-star text-warning"></i><?php echo $doctor_rating; ?> Rating</div>
                            <a href="doctor-detail?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="speciality-btn"><i class="fas fa-stethoscope"></i> PROFILE</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } } else { ?><div class="col-12"><div class="no-record-found">No Record Found</div></div><?php } ?>
        </div>
        <?php if ($has_doctors): ?><div class="text-center mt-4" data-aos="fade-up"><a href="doctors" class="btn-view-all btn-view-doctors"><i class="fas fa-user-md"></i> View All Doctors</a></div><?php endif; ?>
    </div>
</section>

<!-- Laboratories -->
<section class="modern-cards section-padding" style="background: var(--brand-light-green);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title heading-color">Laboratories<?php if ($current_city_name): ?> in <?= htmlspecialchars($current_city_name) ?><?php endif; ?></h2>
            <p class="section-subtitle normal-color">Advanced diagnostic and testing services</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $lab_card_colors = ['#F0FFF8', '#E8F7FF', '#EFF6FF', '#F0FDFA', '#E8F7FF', '#F0FFF8'];
            $labs_query = $city_id > 0
                ? "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id LEFT JOIN users u ON u.user_id = l.user_id WHERE u.status = 1 AND l.approve = 1 AND l.city_id=$city_id ORDER BY l.lab_id DESC LIMIT 6"
                : "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id LEFT JOIN users u ON u.user_id = l.user_id WHERE u.status = 1 AND l.approve = 1 ORDER BY l.lab_id DESC LIMIT 6";
            $labs_result = mysqli_query($con, $labs_query);
            $lab_index = 0;
            $has_labs = $labs_result && mysqli_num_rows($labs_result) > 0;
            if ($has_labs) {
                while($lab = mysqli_fetch_assoc($labs_result)) {
                    $lab_stars = mysqli_fetch_assoc(mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $lab['user_id'] ."'"))['stars'];
                    $lab_index++;
                    $lab_rating = $lab_stars ? number_format((float)$lab_stars, 1) : 'New';
                    $lab_bg = $lab_card_colors[($lab_index - 1) % count($lab_card_colors)];
            ?>
            <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($lab_index * 80); ?>">
                <div class="speciality-card service-mini-card lab-mini-card">
                    <div class="speciality-card-body" style="background-color: <?php echo $lab_bg; ?>;">
                        <div class="service-mini-media">
                            <?php if (!empty($lab['lab_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/labs/<?php echo $lab['lab_pic']; ?>" alt="<?php echo htmlspecialchars($lab['lab_name']); ?> — Laboratory in <?php echo htmlspecialchars($lab['city_name']); ?>" class="img-fluid">
                            <?php else: ?><div class="service-mini-icon-wrap"><i class="fas fa-flask service-mini-icon"></i></div><?php endif; ?>
                        </div>
                        <div class="service-mini-content">
                            <div class="service-mini-badge"><i class="fas fa-flask"></i>LABORATORY</div>
                            <h3 class="speciality-title"><?php echo htmlspecialchars($lab['lab_name']); ?></h3>
                            <div class="service-mini-meta"><i class="fas fa-location-dot"></i><?php echo htmlspecialchars($lab['city_name']); ?></div>
                            <div class="service-mini-rating"><i class="fas fa-star text-warning"></i><?php echo $lab_rating; ?> Rating</div>
                            <a href="lab-detail?lab_id=<?php echo $lab['lab_id']; ?>" class="speciality-btn"><i class="fas fa-vial"></i> DETAILS</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } } else { ?><div class="col-12"><div class="no-record-found">No Record Found</div></div><?php } ?>
        </div>
        <?php if ($has_labs): ?><div class="text-center mt-4" data-aos="fade-up"><a href="labs" class="btn-view-all btn-view-labs"><i class="fas fa-flask"></i> View All Laboratories</a></div><?php endif; ?>
    </div>
</section>

<!-- Radiology -->
<section class="modern-cards section-padding" style="background: var(--brand-light-blue);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title heading-color">Radiology Centers<?php if ($current_city_name): ?> in <?= htmlspecialchars($current_city_name) ?><?php endif; ?></h2>
            <p class="section-subtitle normal-color">Advanced imaging and diagnostic services</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $rad_card_colors = ['#E8F7FF', '#F0FFF8', '#EFF6FF', '#F0FDFA', '#EEF2FF', '#F0F9FF'];
            $rad_query = $city_id > 0
                ? "SELECT r.*, c.city_name FROM radiology r LEFT JOIN cities c ON r.city_id = c.city_id LEFT JOIN users u ON u.user_id = r.user_id WHERE u.status = 1 AND r.approve = 1 AND r.city_id=$city_id ORDER BY r.rad_id DESC LIMIT 6"
                : "SELECT r.*, c.city_name FROM radiology r LEFT JOIN cities c ON r.city_id = c.city_id LEFT JOIN users u ON u.user_id = r.user_id WHERE u.status = 1 AND r.approve = 1 ORDER BY r.rad_id DESC LIMIT 6";
            $rad_result = mysqli_query($con, $rad_query);
            $rad_index = 0;
            $has_rad = $rad_result && mysqli_num_rows($rad_result) > 0;
            if ($has_rad) {
                while($rad = mysqli_fetch_assoc($rad_result)) {
                    $rad_stars = mysqli_fetch_assoc(mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $rad['user_id'] ."'"))['stars'];
                    $rad_index++;
                    $rad_rating = $rad_stars ? number_format((float)$rad_stars, 1) : 'New';
                    $rad_bg = $rad_card_colors[($rad_index - 1) % count($rad_card_colors)];
            ?>
            <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($rad_index * 80); ?>">
                <div class="speciality-card service-mini-card rad-mini-card">
                    <div class="speciality-card-body" style="background-color: <?php echo $rad_bg; ?>;">
                        <div class="service-mini-media">
                            <?php if (!empty($rad['rad_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/radiology/<?php echo $rad['rad_pic']; ?>" alt="<?php echo htmlspecialchars($rad['rad_name']); ?> — Radiology Center in <?php echo htmlspecialchars($rad['city_name']); ?>" class="img-fluid">
                            <?php else: ?><div class="service-mini-icon-wrap"><i class="fas fa-camera service-mini-icon"></i></div><?php endif; ?>
                        </div>
                        <div class="service-mini-content">
                            <div class="service-mini-badge"><i class="fas fa-camera"></i>RADIOLOGY</div>
                            <h3 class="speciality-title"><?php echo htmlspecialchars($rad['rad_name']); ?></h3>
                            <div class="service-mini-meta"><i class="fas fa-location-dot"></i><?php echo htmlspecialchars($rad['city_name']); ?></div>
                            <div class="service-mini-rating"><i class="fas fa-star text-warning"></i><?php echo $rad_rating; ?> Rating</div>
                            <a href="radiology-detail?rad_id=<?php echo $rad['rad_id']; ?>" class="speciality-btn"><i class="fas fa-x-ray"></i> DETAILS</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } } else { ?><div class="col-12"><div class="no-record-found">No Record Found</div></div><?php } ?>
        </div>
        <?php if ($has_rad): ?><div class="text-center mt-4" data-aos="fade-up"><a href="radiology-list" class="btn-view-all btn-view-labs"><i class="fas fa-camera"></i> View All Radiology Centers</a></div><?php endif; ?>
    </div>
</section>

<!-- Blood Banks -->
<section class="modern-cards section-padding" style="background: var(--brand-light-blue);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title heading-color">Blood Banks<?php if ($current_city_name): ?> in <?= htmlspecialchars($current_city_name) ?><?php endif; ?></h2>
            <p class="section-subtitle normal-color">Life-saving blood donation and supply services</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $blood_card_colors = ['#E8F7FF', '#F0FFF8', '#EFF6FF', '#F0FDFA', '#EEF2FF', '#F0F9FF'];
            $blood_banks_query = $city_id > 0
                ? "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id LEFT JOIN users u ON u.user_id = bb.user_id WHERE u.status = 1 AND bb.approve = 1 AND bb.city_id=$city_id ORDER BY bb.bb_id DESC LIMIT 6"
                : "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id LEFT JOIN users u ON u.user_id = bb.user_id WHERE u.status = 1 AND bb.approve = 1 ORDER BY bb.bb_id DESC LIMIT 6";
            $blood_banks_result = mysqli_query($con, $blood_banks_query);
            $blood_index = 0;
            $has_blood_banks = $blood_banks_result && mysqli_num_rows($blood_banks_result) > 0;
            if ($has_blood_banks) {
                while($blood_bank = mysqli_fetch_assoc($blood_banks_result)) {
                    $bb_stars = mysqli_fetch_assoc(mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $blood_bank['user_id'] ."'"))['stars'];
                    $blood_index++;
                    $blood_rating = $bb_stars ? number_format((float)$bb_stars, 1) : 'New';
                    $blood_bg = $blood_card_colors[($blood_index - 1) % count($blood_card_colors)];
            ?>
            <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($blood_index * 80); ?>">
                <div class="speciality-card service-mini-card blood-mini-card">
                    <div class="speciality-card-body" style="background-color: <?php echo $blood_bg; ?>;">
                        <div class="service-mini-media">
                            <?php if (!empty($blood_bank['bb_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo $blood_bank['bb_pic']; ?>" alt="<?php echo htmlspecialchars($blood_bank['bb_name']); ?> — Blood Bank in <?php echo htmlspecialchars($blood_bank['city_name']); ?>" class="img-fluid">
                            <?php else: ?><div class="service-mini-icon-wrap"><i class="fas fa-tint service-mini-icon"></i></div><?php endif; ?>
                        </div>
                        <div class="service-mini-content">
                            <div class="service-mini-badge"><i class="fas fa-tint"></i>BLOOD BANK</div>
                            <h3 class="speciality-title"><?php echo htmlspecialchars($blood_bank['bb_name']); ?></h3>
                            <div class="service-mini-meta"><i class="fas fa-location-dot"></i><?php echo htmlspecialchars($blood_bank['city_name']); ?></div>
                            <div class="service-mini-rating"><i class="fas fa-star text-warning"></i><?php echo $blood_rating; ?> Rating</div>
                            <a href="blood-bank-detail?blood_bankid=<?php echo $blood_bank['bb_id']; ?>" class="speciality-btn"><i class="fas fa-droplet"></i> DETAILS</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } } else { ?><div class="col-12"><div class="no-record-found">No Record Found</div></div><?php } ?>
        </div>
        <?php if ($has_blood_banks): ?><div class="text-center mt-4" data-aos="fade-up"><a href="blood-banks" class="btn-view-all btn-view-blood"><i class="fas fa-tint"></i> View All Blood Banks</a></div><?php endif; ?>
    </div>
</section>

<!-- Ad Slot -->
<div class="container my-4"><div class="ad-slot text-center" style="min-height:90px; background:#f8f9fa; border:1px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center;"><span class="text-muted small">Advertisement</span></div></div>

<!-- Reviews -->
<section class="section-padding" id="reviews" style="background-color: var(--light);">
    <div class="container">
        <div class="section-header" data-aos="fade-up"><h2 class="section-title heading-color">Patient Reviews</h2><p class="section-subtitle normal-color">What our patients say about their experience</p></div>
        <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-aos="fade-up" data-aos-delay="200">
            <div class="carousel-inner">
                <?php
                $reviews_result = mysqli_query($con, "SELECT dr.* FROM feedback dr WHERE dr.status = 1 and user_id=1 ORDER BY dr.feedback_id DESC LIMIT 6");
                $reviews_array = [];
                while($review = mysqli_fetch_assoc($reviews_result)) { $reviews_array[] = $review; }
                $grouped_reviews = array_chunk($reviews_array, 3);
                foreach($grouped_reviews as $index => $review_group) {
                    $active_class = $index == 0 ? 'active' : '';
                ?>
                <div class="carousel-item <?php echo $active_class; ?>">
                    <div class="row">
                        <?php foreach($review_group as $review) { ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="review-card text-center h-100">
                                <div class="review-header justify-content-center">
                                    <div class="review-avatar"><?php echo strtoupper(substr($review['commenter_name'], 0, 1)); ?></div>
                                    <div class="review-info">
                                        <h3><?php echo htmlspecialchars($review['commenter_name']); ?></h3>
                                        <div class="review-rating"><?php for($i = 1; $i <= 5; $i++) { ?><i class="fas fa-star <?php echo $i <= $review['stars'] ? 'text-warning' : 'text-muted'; ?>"></i><?php } ?></div>
                                    </div>
                                </div>
                                <p class="review-text">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
            <div class="carousel-indicators">
                <?php for($i = 0; $i < count($grouped_reviews); $i++) { ?>
                <button type="button" data-bs-target="#reviewsCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i == 0 ? 'active' : ''; ?>"></button>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section-padding" style="background: var(--brand-light-green);">
    <div class="container">
        <div class="section-header" data-aos="fade-up"><h2 class="section-title heading-color">Frequently Asked Questions</h2><p class="section-subtitle normal-color">Common questions about finding healthcare services</p></div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion" data-aos="fade-up">
                    <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">How do I find a doctor near me?</button></h3><div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">Select your city from the dropdown, choose a specialization, and browse verified doctor profiles.</div></div></div>
                    <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Are the doctors and hospitals listed on SehatNexa verified?</button></h3><div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes. Doctors are verified using their PMDC registration number, and hospitals are reviewed before their profiles are approved.</div></div></div>
                    <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">How can I find a blood bank in an emergency?</button></h3><div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Visit our Blood Banks section, filter by your city, and check the available blood stock. In a life-threatening emergency, always call Rescue 1122 first.</div></div></div>
                    <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Can I book a lab test through SehatNexa?</button></h3><div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">You can view available tests and their prices on each laboratory's profile. To book a test, use the WhatsApp button on the lab's page.</div></div></div>
                    <div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">Is SehatNexa free to use?</button></h3><div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, SehatNexa is completely free for patients. Our platform is supported by advertising.</div></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include BASE_PATH.'/includes/footer.php';?>

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
            allOptions.push({ value: '<?php echo $type['dr_cat_type_id']; ?>', text: '<?php echo addslashes($type['treatment']); ?>', dataText: '<?php echo addslashes($category['cat_name'] . ' - ' . $type['type']); ?>' });
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