<?php include '../includes/header.php'; 
$city_id = (int)$city_id;

// 1. QUERY UPDATE: treatment column add kiya gaya hai
$categories_query = "SELECT dc.dr_cat_id, dc.cat_name, dct.dr_cat_type_id, dct.type, dct.treatment 
                   FROM dr_categories dc 
                   LEFT JOIN dr_cat_types dct ON dc.dr_cat_id = dct.dr_cat_id 
                   ORDER BY dc.cat_name, dct.type";
$categories_result = mysqli_query($con, $categories_query);

// 2. ARRAY UPDATE: treatment ko categories_data me save kiya gaya hai
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







// Sirf specific types fetch karne ke liye query
$specific_types = "'Cardiologist (Heart Specialist)', 'Neurologist', 'Pulmonologist', 'Nephrologist', 'Rheumatologist', 'ENT Specialist'";

$categories_query = "SELECT dct.dr_cat_type_id, dct.type 
                     FROM dr_cat_types dct 
                     WHERE dct.type IN ($specific_types)
                     ORDER BY dct.dr_cat_type_id ASC";

$categories_result = mysqli_query($con, $categories_query);





?>

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
    }
</style>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/home.css">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-background">
            <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/hosp.jpg" alt="Healthcare" class="hero-image">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-7">
                        <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill" data-aos="fade-down">WELCOME TO DOCTORAPP</span>
                        <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">We Care About <br>Your Health</h1>
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
                                            <input type="text" class="form-control" id="specializationSearch" placeholder="Search disease (e.g., دل)..." autocomplete="off" dir="auto">
                                            <input type="hidden" name="specialization" id="specializationValue" value="<?php echo $specialization; ?>">
                                            <div class="position-absolute w-100 bg-white border border-top-0 shadow-sm" id="specializationDropdown" style="display: none; z-index: 1000; max-height: 250px; overflow-y: auto;">
                                            </div>
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

    <!-- Services Tabs Section -->
    <section id="top-services" class="section-padding" style="background: linear-gradient(135deg, #f8f9ff 0%, #e8f4fd 100%);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Our Healthcare Services</h2>
                <p class="section-subtitle normal-color">Comprehensive medical care at your fingertips</p>
            </div>
            <div class="row g-4 justify-content-center" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>hospitals" class="service-card text-decoration-none">
                        <div class="service-card-body">
                            <div class="service-icon-wrapper">
                                <i class="fas fa-hospital"></i>
                            </div>
                            <h4 class="service-title">Hospitals</h4>
                            <p class="service-description">Find top-rated hospitals with advanced medical facilities</p>
                            <div class="service-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>doctors" class="service-card text-decoration-none">
                        <div class="service-card-body">
                            <div class="service-icon-wrapper">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <h4 class="service-title">Doctors</h4>
                            <p class="service-description">Connect with experienced medical specialists</p>
                            <div class="service-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>labs" class="service-card text-decoration-none">
                        <div class="service-card-body">
                            <div class="service-icon-wrapper">
                                <i class="fas fa-flask"></i>
                            </div>
                            <h4 class="service-title">Laboratories</h4>
                            <p class="service-description">Advanced diagnostic and testing services</p>
                            <div class="service-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="<?=BASE_URL?>blood-banks" class="service-card text-decoration-none">
                        <div class="service-card-body">
                            <div class="service-icon-wrapper">
                                <i class="fas fa-tint"></i>
                            </div>
                            <h4 class="service-title">Blood Banks</h4>
                            <p class="service-description">Life-saving blood donation and services</p>
                            <div class="service-arrow">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Specialities Section -->
<section class="section-padding" style="background: linear-gradient(135deg, #e8f4fd 0%, #d4eafc 100%);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title heading-color"><i class="fas fa-stethoscope me-3"></i>Specialities</h2>
            <p class="section-subtitle normal-color">Explore our specialized medical departments</p>
        </div>
        <div class="row g-3 justify-content-center" data-aos="fade-up" data-aos-delay="200">
            
            <?php 
            // Colors aur Emojis ka array (taake har card alag dikhe)
            $bg_colors = ['#fce4ec', '#ffebee', '#f3e5f5', '#fff9c4', '#e1f5fe', '#e0f2f1', '#e8f5e9', '#fff3e0'];
            $emojis = ['👴', '❤️', '🧠', '🧪', '🫁', '🫀', '🦴', '👁️', '🦷', '👶'];
            
            $index = 0;
            // Check karein ke result empty to nahi
            if ($categories_result && mysqli_num_rows($categories_result) > 0) {
                // Agar pehle se fetch ho chuka hai to pointer reset karein (agar zaroorat ho)
                // mysqli_data_seek($categories_result, 0); 
                
                while($spec = mysqli_fetch_assoc($categories_result)) {
                    $cat_id = $spec['dr_cat_type_id'];
                    $cat_name = $spec['type'];
                    
                    // Index ke hisaab se color aur emoji uthayen
                    $bg_color = $bg_colors[$index % count($bg_colors)];
                    $emoji = $emojis[$index % count($emojis)];
                    $index++;
            ?>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="speciality-card">
                        <div class="speciality-card-body" style="background-color: <?= $bg_color ?>;">
                            <div class="speciality-icon">
                                <span class="speciality-emoji"><?= $emoji ?></span>
                            </div>
                            <!-- Title dynamically set ho raha hai -->
                            <h4 class="speciality-title" title="<?= htmlspecialchars($cat_name) ?>">
                                <?= htmlspecialchars($cat_name) ?>
                            </h4>
                            <!-- Link me ID dynamically ja rahi hai -->
                            <a href="<?=BASE_URL?>doctors?search=&city=<?=$city_id?>&hospital=&specialization=<?= $cat_id ?>" class="speciality-btn">
                                <i class="fas fa-heart"></i>
                                DEPARTMENT
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                } 
            } else {
                echo '<div class="col-12 text-center"><p>No Specialities Found</p></div>';
            }
            ?>
            
        </div>
    </div>
    <div class="text-center mt-4" data-aos="fade-up">
        <a href="<?=BASE_URL?>all-specialities" class="btn-view-all btn-view-specialities">
            <i class="fas fa-stethoscope"></i>
            Explore All Specialities
        </a>
    </div>
</section>

    <!-- Top Hospitals Section -->
    <section class="modern-cards section-padding" style="background-color: var(--light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color"><i class="fas fa-hospital-alt me-3"></i>Top Hospitals</h2>
                <p class="section-subtitle normal-color">Find the best healthcare facilities in your area</p>
            </div>
            <div class="row g-3 justify-content-center">
                <?php
                $hospital_card_colors = ['#fce4ec', '#ffebee', '#f3e5f5', '#fff9c4', '#e1f5fe', '#e0f2f1'];
                if($city_id > 0){
                    $hospitals_query = "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id LEFT JOIN users u ON u.user_id = h.user_id WHERE u.status = 1 AND h.approve = 1 AND h.city_id = $city_id ORDER BY h.hospital_id DESC LIMIT 6";
                }else{
                    $hospitals_query = "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id LEFT JOIN users u ON u.user_id = h.user_id WHERE u.status = 1 AND h.approve = 1 ORDER BY h.hospital_id DESC LIMIT 6";
                }
                  
                 $hospitals_result = mysqli_query($con, $hospitals_query);
                $hospital_index = 0;
                $has_hospitals = $hospitals_result && mysqli_num_rows($hospitals_result) > 0;
                if ($has_hospitals) {
                while($hospital = mysqli_fetch_assoc($hospitals_result)) {
                    $hosp_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $hospital['user_id'] ."'");
                    $hosp_stars = mysqli_fetch_assoc($hosp_stars_q);
                    $hosp_stars = $hosp_stars['stars'];
                    $hospital_rating = $hosp_stars ? number_format((float)$hosp_stars, 1) : 'New';
                    $hospital_bg = $hospital_card_colors[$hospital_index % count($hospital_card_colors)];
                    $hospital_index++;
                ?>
                <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($hospital_index * 80); ?>">
                    <div class="speciality-card hospital-mini-card">
                        <div class="speciality-card-body" style="background-color: <?php echo $hospital_bg; ?>;">
                            <div class="speciality-icon">
                                <i class="fas fa-hospital-alt"></i>
                            </div>
                            <h4 class="speciality-title"><?php echo htmlspecialchars(substr($hospital['hospital_name'], 0, 25)); ?></h4>
                            <p class="hospital-mini-location">
                                <i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($hospital['city_name']); ?>
                            </p>
                            <div class="hospital-mini-rating">
                                <i class="fas fa-star text-warning"></i><?php echo $hospital_rating; ?> Rating
                            </div>
                            <a href="hospital-detail?hospital_id=<?php echo $hospital['hospital_id']; ?>" class="speciality-btn">
                                <i class="fas fa-arrow-right"></i>
                                DETAILS
                            </a>
                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                <div class="col-12">
                    <div class="no-record-found">No Record Found</div>
                </div>
                <?php } ?>
            </div>
            <?php if ($has_hospitals): ?>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="hospitals" class="btn-view-all btn-view-hospitals">
                    <i class="fas fa-hospital-alt"></i>
                    View All Hospitals
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Top Doctors Section -->
    <section class="modern-cards section-padding" style="background-color: var(--bg-light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Top Doctors</h2>
                <p class="section-subtitle normal-color">Connect with experienced medical professionals</p>
            </div>
            <div class="row g-3 justify-content-center">
                <?php
                $doctor_card_colors = ['#eef6ff', '#f3efff', '#eefbf5', '#fff6e8', '#fceef3', '#edf8ff'];
                if($city_id > 0){
                     $doctors_query = "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id LEFT JOIN users u ON d.user_id = u.user_id WHERE u.status = 1 AND d.approve=1 AND d.city_id=$city_id ORDER BY d.doctor_id DESC LIMIT 4";
                }else{
                     $doctors_query = "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id LEFT JOIN users u ON d.user_id = u.user_id WHERE u.status = 1 AND d.approve=1 ORDER BY d.doctor_id DESC LIMIT 6";
                }
                
                $doctors_result = mysqli_query($con, $doctors_query);
                $doctor_index = 0;
                $has_doctors = $doctors_result && mysqli_num_rows($doctors_result) > 0;
                if ($has_doctors) {
                while($doctor = mysqli_fetch_assoc($doctors_result)) {
                    $doct_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $doctor['user_id'] ."'");
                    $doct_stars = mysqli_fetch_assoc($doct_stars_q);
                    $doct_stars = $doct_stars['stars'];
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
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                        alt="<?php echo $doctor['doctor_name']; ?>" class="img-fluid">
                                <?php else: ?>
                                    <div class="doctor-mini-icon-wrap">
                                        <i class="fas fa-user-md doctor-mini-icon"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="doctor-mini-content">
                                <div class="doctor-mini-specialty">
                                    <i class="fas fa-user-md"></i><?php echo htmlspecialchars($doctor_specialization); ?>
                                </div>
                                <h4 class="speciality-title"><?php echo htmlspecialchars($doctor['doctor_name']); ?></h4>
                                <div class="doctor-mini-meta">
                                    <i class="fas fa-location-dot"></i><?php echo htmlspecialchars($doctor['city_name']); ?>
                                </div>
                                <div class="doctor-mini-rating">
                                    <i class="fas fa-star text-warning"></i><?php echo $doctor_rating; ?> Rating
                                </div>
                                <a href="doctor-detail?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="speciality-btn">
                                    <i class="fas fa-stethoscope"></i>
                                    PROFILE
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                <div class="col-12">
                    <div class="no-record-found">No Record Found</div>
                </div>
                <?php } ?>
            </div>
            <?php if ($has_doctors): ?>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="doctors" class="btn-view-all btn-view-doctors">
                    <i class="fas fa-user-md"></i>
                    View All Doctors
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Laboratories Section -->
    <section class="modern-cards section-padding" style="background-color: var(--light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Laboratories</h2>
                <p class="section-subtitle normal-color">Advanced diagnostic and testing services</p>
            </div>
            <div class="row g-3 justify-content-center">
                <?php
                $lab_card_colors = ['#e8fff9', '#eef8ff', '#f3efff', '#eefbf5', '#fff6e8', '#edf8ff'];
                if($city_id > 0){
                    $labs_query = "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id LEFT JOIN users u ON u.user_id = l.user_id WHERE u.status = 1 AND l.approve = 1 AND l.city_id=$city_id ORDER BY l.lab_id DESC LIMIT 6";
                }else{
                    $labs_query = "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id LEFT JOIN users u ON u.user_id = l.user_id WHERE u.status = 1 AND l.approve = 1 ORDER BY l.lab_id DESC LIMIT 6";
                }
                
                $labs_result = mysqli_query($con, $labs_query);
                $lab_index = 0;
                $has_labs = $labs_result && mysqli_num_rows($labs_result) > 0;
                if ($has_labs) {
                while($lab = mysqli_fetch_assoc($labs_result)) {
                    $lab_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $lab['user_id'] ."'");
                    $lab_stars = mysqli_fetch_assoc($lab_stars_q);
                    $lab_stars = $lab_stars['stars'];
                    $lab_index++;
                    $lab_rating = $lab_stars ? number_format((float)$lab_stars, 1) : 'New';
                    $lab_bg = $lab_card_colors[($lab_index - 1) % count($lab_card_colors)];
                ?>
                <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($lab_index * 80); ?>">
                    <div class="speciality-card service-mini-card lab-mini-card">
                        <div class="speciality-card-body" style="background-color: <?php echo $lab_bg; ?>;">
                            <div class="service-mini-media">
                                <?php if (!empty($lab['lab_pic'])): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/labs/<?php echo $lab['lab_pic']; ?>" 
                                        alt="<?php echo $lab['lab_name']; ?>" class="img-fluid">
                                <?php else: ?>
                                    <div class="service-mini-icon-wrap">
                                        <i class="fas fa-flask service-mini-icon"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="service-mini-content">
                                <div class="service-mini-badge">
                                    <i class="fas fa-flask"></i>LABORATORY
                                </div>
                                <h4 class="speciality-title"><?php echo htmlspecialchars($lab['lab_name']); ?></h4>
                                <div class="service-mini-meta">
                                    <i class="fas fa-location-dot"></i><?php echo htmlspecialchars($lab['city_name']); ?>
                                </div>
                                <div class="service-mini-rating">
                                    <i class="fas fa-star text-warning"></i><?php echo $lab_rating; ?> Rating
                                </div>
                                <a href="lab-detail?lab_id=<?php echo $lab['lab_id']; ?>" class="speciality-btn">
                                    <i class="fas fa-vial"></i>
                                    DETAILS
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                <div class="col-12">
                    <div class="no-record-found">No Record Found</div>
                </div>
                <?php } ?>
            </div>
            <?php if ($has_labs): ?>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="labs" class="btn-view-all btn-view-labs">
                    <i class="fas fa-flask"></i>
                    View All Laboratories
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Blood Banks Section -->
    <section class="modern-cards section-padding" style="background-color: var(--bg-light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Blood Banks</h2>
                <p class="section-subtitle normal-color">Life-saving blood donation and supply services</p>
            </div>
            <div class="row g-3 justify-content-center">
                <?php
                $blood_card_colors = ['#fff1f1', '#fff6e8', '#fceef3', '#eef6ff', '#f3efff', '#edf8ff'];
                if($city_id > 0){
                    $blood_banks_query = "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id LEFT JOIN users u ON u.user_id = bb.user_id WHERE u.status = 1 AND bb.approve = 1 AND bb.city_id=$city_id ORDER BY bb.bb_id DESC LIMIT 6";
                }else{
                    $blood_banks_query = "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id LEFT JOIN users u ON u.user_id = bb.user_id WHERE u.status = 1 AND bb.approve = 1 ORDER BY bb.bb_id DESC LIMIT 6";
                }
                
                $blood_banks_result = mysqli_query($con, $blood_banks_query);
                $blood_index = 0;
                $has_blood_banks = $blood_banks_result && mysqli_num_rows($blood_banks_result) > 0;
                if ($has_blood_banks) {
                while($blood_bank = mysqli_fetch_assoc($blood_banks_result)) {
                    $bb_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE user_id='". $blood_bank['user_id'] ."'");
                    $bb_stars = mysqli_fetch_assoc($bb_stars_q);
                    $bb_stars = $bb_stars['stars'];
                    $blood_index++;
                    $blood_rating = $bb_stars ? number_format((float)$bb_stars, 1) : 'New';
                    $blood_bg = $blood_card_colors[($blood_index - 1) % count($blood_card_colors)];
                ?>
                <div class="col-lg-2 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?php echo 100 + ($blood_index * 80); ?>">
                    <div class="speciality-card service-mini-card blood-mini-card">
                        <div class="speciality-card-body" style="background-color: <?php echo $blood_bg; ?>;">
                            <div class="service-mini-media">
                                <?php if (!empty($blood_bank['bb_pic'])): ?>
                                    <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo $blood_bank['bb_pic']; ?>" 
                                        alt="<?php echo $blood_bank['bb_name']; ?>" class="img-fluid">
                                <?php else: ?>
                                    <div class="service-mini-icon-wrap">
                                        <i class="fas fa-tint service-mini-icon"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="service-mini-content">
                                <div class="service-mini-badge">
                                    <i class="fas fa-tint"></i>BLOOD BANK
                                </div>
                                <h4 class="speciality-title"><?php echo htmlspecialchars($blood_bank['bb_name']); ?></h4>
                                <div class="service-mini-meta">
                                    <i class="fas fa-location-dot"></i><?php echo htmlspecialchars($blood_bank['city_name']); ?>
                                </div>
                                <div class="service-mini-rating">
                                    <i class="fas fa-star text-warning"></i><?php echo $blood_rating; ?> Rating
                                </div>
                                <a href="blood-bank-detail?blood_bankid=<?php echo $blood_bank['bb_id']; ?>" class="speciality-btn">
                                    <i class="fas fa-droplet"></i>
                                    DETAILS
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }
                } else { ?>
                <div class="col-12">
                    <div class="no-record-found">No Record Found</div>
                </div>
                <?php } ?>
            </div>
            <?php if ($has_blood_banks): ?>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="blood-banks" class="btn-view-all btn-view-blood">
                    <i class="fas fa-tint"></i>
                    View All Blood Banks
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="section-padding d-none" id="reviews" style="background-color: var(--light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Patient Reviews</h2>
                <p class="section-subtitle normal-color">What our patients say about their experience</p>
            </div>
            
            <!-- Reviews Carousel -->
            <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-aos="fade-up" data-aos-delay="200">
                <div class="carousel-inner">
                    <?php
                    $reviews_query = "SELECT dr.* FROM feedback dr WHERE dr.status = 1 and user_id=1 ORDER BY dr.feedback_id DESC LIMIT 6";
                    $reviews_result = mysqli_query($con, $reviews_query);
                    $reviews_array = [];
                    while($review = mysqli_fetch_assoc($reviews_result)) {
                        $reviews_array[] = $review;
                    }
                    
                    // Group reviews in sets of 3
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
                                        <div class="review-avatar">
                                            <?php echo strtoupper(substr($review['commenter_name'], 0, 1)); ?>
                                        </div>
                                        <div class="review-info">
                                            <h5><?php echo htmlspecialchars($review['commenter_name']); ?></h5>
                                            <div class="review-rating">
                                                <?php for($i = 1; $i <= 5; $i++) { ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['stars'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php } ?>
                                            </div>
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
                
                <!-- Carousel Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                
                <!-- Carousel Indicators -->
                <div class="carousel-indicators">
                    <?php 
                    $total_groups = count($grouped_reviews);
                    for($i = 0; $i < $total_groups; $i++) { 
                        $active_class = $i == 0 ? 'active' : '';
                    ?>
                    <button type="button" data-bs-target="#reviewsCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $active_class; ?>" aria-current="true" aria-label="Slide <?php echo $i + 1; ?>"></button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include BASE_PATH.'/includes/footer.php';?>
<script>
$(document).ready(function() {
    $('#cityselect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search city...',
        allowClear: true,
        width: '100%'
    });

    $('#heroCitySelect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select City',
        allowClear: false,
        width: '100%',
        minimumResultsForSearch: 0,
        dropdownCssClass: 'hero-city-dropdown'
    }).on('change', function() {
        var cityId = $(this).val();
        if (!cityId) return;

        fetch('<?php echo BASE_URL; ?>includes/set-city.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ city_id: parseInt(cityId) })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                location.reload();
            }
        });
    });
});
</script>


<script>
// 3. JAVASCRIPT UPDATE: Treatment show karne aur search karne ke liye update kiya gaya hai
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