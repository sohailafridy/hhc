<?php include '../includes/header.php'; ?>

<style>
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--light);
        overflow-x: hidden;
    }
    
    /* Hero Section */
    .hero-section {
        height: 85vh;
        min-height: 500px;
        position: relative;
        overflow: hidden;
        background: var(--gradient);
    }
    
    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    .hero-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.3;
    }
    
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.4));
        z-index: 2;
    }
    
    .hero-content {
        position: relative;
        z-index: 3;
        height: 100%;
        display: flex;
        align-items: center;
    }
    
    .hero-title {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        color: white;
    }
    
    .hero-text {
        font-size: 1.2rem;
        margin-bottom: 30px;
        max-width: 600px;
        color: rgba(255, 255, 255, 0.9);
    }
    
    .btn-hero {
        padding: 12px 35px;
        border-radius: 50px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        background: var(--secondary);
        color: var(--primary);
        border: none;
        transition: all 0.3s;
    }
    
    .btn-hero:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-3px);
        box-shadow: var(--hover-shadow);
    }

    /* Hero Form Styles */
    .hero-form {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
        border-radius: 20px;
        padding: 30px;
    }
    
    .hero-form:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .hero-form .form-control,
    .hero-form .form-select {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .hero-form .form-control:focus,
    .hero-form .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.1);
    }
    
    .hero-form .form-label {
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--dark);
    }
    
    .hero-form .btn-primary {
        background: var(--gradient);
        border: none;
        transition: all 0.3s ease;
    }
    
    .hero-form .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
    }

    /* Section Styles */
    .section-padding {
        padding: 80px 0;
    }
    
    .section-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 15px;
    }
    
    .section-subtitle {
        font-size: 1.1rem;
        color: var(--secondary);
        max-width: 600px;
        margin: 0 auto;
    }

    /* Card Styles */
    .service-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--hover-shadow);
    }
    
    .service-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: var(--gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
    }
    
    .service-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 15px;
    }
    
    .service-description {
        color: var(--secondary);
        line-height: 1.6;
    }

    .about-us-img {
        border-radius: 28px;
        /* border: 8px solid rgba(255, 255, 255, 0.9); */
        box-shadow:
            0 40px 90px rgba(15, 23, 42, 0.2),
            0 18px 40px rgba(102, 126, 234, 0.25),
            0 0 40px rgba(255, 160, 120, 0.25);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(248, 249, 250, 0.6));
        backdrop-filter: blur(14px);
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .about-us-img:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow:
            0 50px 110px rgba(15, 23, 42, 0.25),
            0 22px 45px rgba(102, 126, 234, 0.3),
            0 0 50px rgba(255, 160, 120, 0.35);
    }

    /* Hospital/Doctor/Lab Cards */
    .modern-cards .entity-card {
        background: rgba(255, 255, 255, 0.92);
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.12), 0 6px 20px rgba(102, 126, 234, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        transition: all 0.4s ease;
        height: 100%;
        position: relative;
        padding-bottom: 10px;
    }

    .modern-cards .entity-card::before {
        content: '';
        position: absolute;
        inset: -50% 10% auto;
        height: 180px;
        background: radial-gradient(circle, rgba(255, 191, 148, 0.45), transparent 70%);
        opacity: 0.9;
        pointer-events: none;
        transition: transform 0.4s ease;
    }
    
    .modern-cards .entity-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 35px 90px rgba(15, 23, 42, 0.18), 0 12px 28px rgba(102, 126, 234, 0.2);
    }

    .modern-cards .entity-card:hover::before {
        transform: translateY(-12px) scale(1.1);
    }
    
    .modern-cards .entity-image {
        height: 240px;
        background: linear-gradient(145deg, rgba(255, 160, 120, 0.25), rgba(82, 168, 255, 0.2));
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 22px 20px 10px;
    }
    
    .modern-cards .entity-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 26px;
        border: 6px solid rgba(255, 255, 255, 0.95);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.18), 0 10px 22px rgba(255, 160, 120, 0.22);
    }
    
    .modern-cards .entity-content {
        padding: 26px 26px 20px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.85));
        text-align: center;
    }
    
    .entity-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #4a8bd6;
        margin-bottom: 12px;
    }
    
    .entity-description {
        color: #6d737c;
        font-size: 0.95rem;
        margin-bottom: 18px;
    }
    
    .entity-meta {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        font-size: 0.85rem;
        color: #2f3b4a;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 16px;
        padding: 10px 14px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.8);
    }

    /* Review Section */
    .review-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .review-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }
    
    .review-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .review-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        margin-right: 15px;
    }
    
    .review-info h5 {
        margin: 0;
        font-size: 1rem;
        color: var(--dark);
    }
    
    .review-rating {
        color: #ffc107;
        font-size: 0.9rem;
    }
    
    .review-text {
        color: var(--secondary);
        line-height: 1.6;
        font-style: italic;
    }

    .btn-view-all {
        background: var(--gradient);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-view-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(79, 172, 254, 0.3);
        color: white;
    }

    /* Entity Actions Styling */
    .entity-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(79, 172, 254, 0.1);
    }

   

    /* detail btns */
      .detail-btns {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        border: none;
        border-radius: 25px;
        padding: 8px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: inline-block;
    }

    .detail-btns::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
        transition: left 0.3s ease;
    }

    .detail-btns:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        color: white;
        text-decoration: none;
    }

    .detail-btns:hover::before {
        left: 100%;
    }

    .detail-btns:active {
        transform: translateY(0);
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
    }




    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .hero-section {
            height: auto;
            min-height: 100vh;
            padding: 100px 0 50px;
        }
        
        .hero-content {
            padding: 20px 0;
            width: 100%;
        }
        
        .hero-title {
            font-size: 2rem;
            line-height: 1.2;
            margin-bottom: 15px;
        }
        
        .hero-text {
            font-size: 1rem;
            margin-bottom: 20px;
        }
        
        .hero-form {
            margin-top: 30px;
            padding: 20px;
            width: 100%;
            max-width: 100%;
        }
        
        .btn-hero {
            padding: 10px 25px;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .section-padding {
            padding: 50px 0;
        }
        
        .section-title {
            font-size: 1.8rem;
        }
        
        .section-subtitle {
            font-size: 1rem;
        }
        
        .entity-card {
            margin-bottom: 20px;
        }
        
        .review-card {
            margin-bottom: 20px;
        }
        
        .service-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
        
        .container {
            padding-left: 15px;
            padding-right: 15px;
            max-width: 100%;
        }
    }

    @media (max-width: 576px) {
        .hero-title {
            font-size: 1.8rem;
        }
        
        .hero-form {
            padding: 15px;
        }
        
        .section-padding {
            padding: 40px 0;
        }
        
        .section-title {
            font-size: 1.6rem;
        }
        
        .container {
            padding-left: 10px;
            padding-right: 10px;
        }
    }
    .heading-color{
        color: var(--dark-blue);
    }
    .normal-color{
        color: var(--dark-blue);
    }

    /* Sidebar Filter Styles */
    .sidebar-filter {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 20px;
        height: fit-content;
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .filter-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary);
    }

    .filter-category {
        margin-bottom: 15px;
    }

    .filter-category-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        margin-bottom: 8px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        background: rgba(255, 255, 255, 0.7);
    }

    .filter-category-item:hover {
        background: var(--gradient);
        color: white;
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.2);
    }

    .filter-category-item.active {
        background: var(--gradient);
        color: white;
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
    }

    .filter-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: rgba(79, 172, 254, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .filter-category-item:hover .filter-icon,
    .filter-category-item.active .filter-icon {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .filter-name {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .filter-count {
        margin-left: auto;
        background: rgba(79, 172, 254, 0.1);
        padding: 2px 8px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .filter-category-item:hover .filter-count,
    .filter-category-item.active .filter-count {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .see-more-btn {
        width: 100%;
        padding: 10px;
        border: 2px solid var(--primary);
        background: transparent;
        color: var(--primary);
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    .see-more-btn:hover {
        background: var(--gradient);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
    }

    .main-content-with-sidebar {
        padding-left: 30px;
    }

    @media (max-width: 991px) {
        .sidebar-filter {
            margin-bottom: 30px;
            position: relative;
        }
        .main-content-with-sidebar {
            padding-left: 0;
        }
    }

   
</style>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

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
                            <a href="doctors" class="btn btn-hero me-3 d-inline-block">Find a Doctor</a>
                            <a href="about" class="btn btn-outline-light rounded-pill px-4 py-3 fw-bold d-inline-block">Learn More</a>
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
                                            while($city = mysqli_fetch_assoc($cities_result)) {
                                                $selected = ($city['city_id'] == $city_id) ? 'selected' : '';
                                                echo '<option value="' . $city['city_id'] . '" ' . $selected . '>' . htmlspecialchars($city['city_name']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Search Doctor</label>
                                        <input type="text" class="form-control" name="search" placeholder="Enter doctor name..." required>
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

    <!-- Main Content with Sidebar Filter -->
    <section class="section-padding" style="background-color: var(--bg-light);">
        <div class="container">
            <div class="row">
                <!-- Sidebar Filter -->
                <div class="col-lg-3 col-md-4">
                    <div class="sidebar-filter" data-aos="fade-right">
                        <h3 class="filter-title">Filter by Category</h3>
                        
                        <div class="filter-category">
                            <div class="filter-category-item active" data-category="hospitals">
                                <div class="filter-icon">
                                    <i class="fas fa-hospital"></i>
                                </div>
                                <span class="filter-name">Hospitals</span>
                                <span class="filter-count">5+</span>
                            </div>
                            
                            <div class="filter-category-item" data-category="doctors">
                                <div class="filter-icon">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <span class="filter-name">Doctors</span>
                                <span class="filter-count">5+</span>
                            </div>
                            
                            <div class="filter-category-item" data-category="labs">
                                <div class="filter-icon">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <span class="filter-name">Laboratories</span>
                                <span class="filter-count">5+</span>
                            </div>
                            
                            <div class="filter-category-item" data-category="blood-banks">
                                <div class="filter-icon">
                                    <i class="fas fa-tint"></i>
                                </div>
                                <span class="filter-name">Blood Banks</span>
                                <span class="filter-count">5+</span>
                            </div>
                            
                            <button class="see-more-btn" onclick="showMoreFilters()">
                                <i class="fas fa-plus me-2"></i>See More
                            </button>
                        </div>
                        
                        <div class="filter-category" id="moreFilters" style="display: none;">
                            <h5 class="mb-3">More Options</h5>
                            <div class="filter-category-item" data-category="pharmacies">
                                <div class="filter-icon">
                                    <i class="fas fa-pills"></i>
                                </div>
                                <span class="filter-name">Pharmacies</span>
                                <span class="filter-count">3+</span>
                            </div>
                            
                            <div class="filter-category-item" data-category="ambulance">
                                <div class="filter-icon">
                                    <i class="fas fa-ambulance"></i>
                                </div>
                                <span class="filter-name">Ambulance Services</span>
                                <span class="filter-count">2+</span>
                            </div>
                            
                            <div class="filter-category-item" data-category="emergency">
                                <div class="filter-icon">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <span class="filter-name">Emergency Care</span>
                                <span class="filter-count">24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content Area -->
                <div class="col-lg-9 col-md-8 main-content-with-sidebar">
                    <!-- About Section -->
                    <div class="section-header mb-5" data-aos="fade-up">
                        <h2 class="section-title heading-color">About DoctorApp</h2>
                        <p class="section-subtitle normal-color">Your trusted healthcare platform connecting you with the best medical professionals and facilities</p>
                    </div>
                    
                    <div class="row mb-5">
                        <div class="col-lg-6" data-aos="fade-right">
                            <img src="<?=BASE_URL?>includes/uploads/home-about.png" alt="About Us img" class="img-fluid about-us-img">
                        </div>
                        <div class="col-lg-6" data-aos="fade-left">
                            <h3 class="mb-4 heading-color">Your Health, Our Priority</h3>
                            <p class="mb-4 normal-color">DoctorApp is a comprehensive healthcare platform designed to make medical services accessible to everyone. We connect patients with qualified doctors, hospitals, laboratories, and blood banks across the country.</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="service-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 heading-color">Expert Doctors</h5>
                                            <p class="mb-0 text-muted normal-color">Qualified medical professionals</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="service-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            <i class="fas fa-hospital"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 heading-color">Top Hospitals</h5>
                                            <p class="mb-0 text-muted normal-color">Best medical facilities</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="service-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            <i class="fas fa-flask"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 heading-color">Laboratories</h5>
                                            <p class="mb-0 text-muted normal-color">Advanced diagnostic services</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="service-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            <i class="fas fa-tint"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 heading-color">Blood Banks</h5>
                                            <p class="mb-0 text-muted normal-color">Life-saving blood services</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Top Hospitals Section -->
    <section class="modern-cards section-padding" style="background-color: var(--light);" id="hospitals-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Top Hospitals</h2>
                <p class="section-subtitle normal-color">Find the best healthcare facilities in your area</p>
            </div>
            <div class="row">
                <?php
                if($city_id > 0){
                    $hospitals_query = "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id WHERE h.status = 1 AND h.city_id = $city_id ORDER BY h.hospital_id DESC LIMIT 5";
                }else{
                    $hospitals_query = "SELECT h.*, c.city_name FROM hospitals h LEFT JOIN cities c ON h.city_id = c.city_id WHERE h.status = 1 ORDER BY h.hospital_id DESC LIMIT 5";
                }
                  
                 $hospitals_result = mysqli_query($con, $hospitals_query);
                while($hospital = mysqli_fetch_assoc($hospitals_result)) {
                    $hosp_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE hospital_id='". $hospital['hospital_id'] ."'");
                    $hosp_stars = mysqli_fetch_assoc($hosp_stars_q);
                    $hosp_stars = $hosp_stars['stars'];
                ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="entity-card">
                        <div class="entity-image">
                            <?php if (!empty($hospital['hospital_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/hospitals/<?php echo $hospital['hospital_pic']; ?>" 
                                    alt="<?php echo $hospital['hospital_name']; ?>" class="img-fluid" style="max-height: 300px;">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/hosp.jpg" 
                                    alt="No Image" class="img-fluid" style="max-height: 300px;">
                            <?php endif; ?>    
                        </div>
                        <div class="entity-content">
                            <h5 class="entity-title heading-color"><?php echo htmlspecialchars($hospital['hospital_name']); ?></h5>
                            <p class="entity-description normal-color"><?php echo htmlspecialchars(substr($hospital['hospital_address'], 0, 100)) . '...'; ?></p>
                            <small class="entity-contact normal-color"><?php echo htmlspecialchars($hospital['hospital_phone']); ?></small>
                            <div class="entity-meta">
                                <span><i class="fas fa-map-marker-alt me-1 normal-color"></i><?php echo htmlspecialchars($hospital['city_name']); ?></span>
                                <span><i class="fas fa-star text-warning me-1"></i><?=number_format($hosp_stars,1)?></span>
                            </div>
                            <a href="hospital-detail?hospital_id=<?php echo $hospital['hospital_id']; ?>" class="btn btn-primary btn-sm detail-btns mt-3">View Details</a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="hospitals" class="btn-view-all detail-btns">View All Hospitals</a>
            </div>
        </div>
    </section>

    <!-- Top Doctors Section -->
    <section class="modern-cards section-padding" style="background-color: var(--bg-light);" id="doctors-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Top Doctors</h2>
                <p class="section-subtitle normal-color">Connect with experienced medical professionals</p>
            </div>
            <div class="row">
                <?php
                if($city_id > 0){
                    $doctors_query = "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id WHERE d.status = 1 AND d.city_id=$city_id ORDER BY d.doctor_id DESC LIMIT 5";
                }else{
                    $doctors_query = "SELECT d.*, c.city_name, dct.type as specialization FROM doctors d LEFT JOIN cities c ON d.city_id = c.city_id LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_id WHERE d.status = 1 ORDER BY d.doctor_id DESC LIMIT 5";
                }
                
                $doctors_result = mysqli_query($con, $doctors_query);
                while($doctor = mysqli_fetch_assoc($doctors_result)) {
                    $doct_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE doctor_id='". $doctor['doctor_id'] ."'");
                    $doct_stars = mysqli_fetch_assoc($doct_stars_q);
                    $doct_stars = $doct_stars['stars'];
                ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="entity-card">
                        <div class="entity-image">
                            <?php if (!empty($doctor['doctor_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/doctors/<?php echo $doctor['doctor_pic']; ?>" 
                                    alt="<?php echo $doctor['doctor_name']; ?>" class="img-fluid" style="max-height: 300px;">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/doctor.jpg" 
                                    alt="No Image" class="img-fluid" style="max-height: 300px;">
                            <?php endif; ?>     
                        </div>
                        <div class="entity-content">
                            <h5 class="entity-title heading-color"><?php echo htmlspecialchars($doctor['doctor_name']); ?></h5>
                            <p class="entity-description normal-color"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                            <div class="entity-meta">
                                <span><i class="fas fa-map-marker-alt me-1 normal-color"></i><?php echo htmlspecialchars($doctor['city_name']); ?></span>
                                <span><i class="fas fa-star text-warning me-1"></i><?=number_format($doct_stars,1)?></span>
                            </div>
                            <div class="entity-actions">
                                <a href="doctor-detail?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn detail-btns">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="doctors" class="btn-view-all">View All Doctors</a>
            </div>
        </div>
    </section>

    <!-- Laboratories Section -->
    <section class="modern-cards section-padding" style="background-color: var(--light);" id="labs-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Laboratories</h2>
                <p class="section-subtitle normal-color">Advanced diagnostic and testing services</p>
            </div>
            <div class="row">
                <?php
                if($city_id > 0){
                    $labs_query = "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id WHERE l.status AND l.city_id=$city_id ORDER BY l.lab_id DESC LIMIT 5";
                }else{
                    $labs_query = "SELECT l.*, c.city_name FROM laboratories l LEFT JOIN cities c ON l.city_id = c.city_id WHERE l.status ORDER BY l.lab_id DESC LIMIT 5";
                }
                
                $labs_result = mysqli_query($con, $labs_query);
                while($lab = mysqli_fetch_assoc($labs_result)) {
                    $lab_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE lab_id='". $lab['lab_id'] ."'");
                    $lab_stars = mysqli_fetch_assoc($lab_stars_q);
                    $lab_stars = $lab_stars['stars'];
                ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="entity-card">
                        <div class="entity-image">
                             <?php if (!empty($lab['lab_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/labs/<?php echo $lab['lab_pic']; ?>" 
                                    alt="<?php echo $lab['lab_name']; ?>" class="img-fluid" style="max-height: 300px;">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/lab.jpg" 
                                    alt="No Image" class="img-fluid" style="max-height: 300px;">
                            <?php endif; ?>     
                        </div>
                        <div class="entity-content">
                            <h5 class="entity-title heading-color"><?php echo htmlspecialchars($lab['lab_name']); ?></h5>
                            <p class="entity-description normal-color"><?php echo htmlspecialchars(substr($lab['lab_address'], 0, 100)) . '...'; ?></p>
                            <div class="entity-meta">
                                <span><i class="fas fa-map-marker-alt me-1 normal-color"></i><?php echo htmlspecialchars($lab['city_name']); ?></span>
                                <span><i class="fas fa-star text-warning me-1"></i><?=number_format($lab_stars,1)?></span>
                            </div>
                            <div class="entity-actions">
                                <a href="lab-detail?lab_id=<?php echo $lab['lab_id']; ?>" class="btn detail-btns">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="labs" class="btn-view-all">View All Laboratories</a>
            </div>
        </div>
    </section>

    <!-- Blood Banks Section -->
    <section class="modern-cards section-padding" style="background-color: var(--bg-light);" id="blood-banks-section">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Blood Banks</h2>
                <p class="section-subtitle normal-color">Life-saving blood donation and supply services</p>
            </div>
            <div class="row">
                <?php
                if($city_id > 0){
                    $blood_banks_query = "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id WHERE bb.status = 1 AND bb.city_id=$city_id ORDER BY bb.bb_id DESC LIMIT 5";
                }else{
                    $blood_banks_query = "SELECT bb.*, c.city_name FROM blood_bank bb LEFT JOIN cities c ON bb.city_id = c.city_id WHERE bb.status = 1 ORDER BY bb.bb_id DESC LIMIT 5";
                }
                
                $blood_banks_result = mysqli_query($con, $blood_banks_query);
                while($blood_bank = mysqli_fetch_assoc($blood_banks_result)) {
                    $bb_stars_q = mysqli_query($con, "SELECT AVG(stars) as stars FROM `feedback` WHERE bloodb_id='". $blood_bank['bb_id'] ."'");
                    $bb_stars = mysqli_fetch_assoc($bb_stars_q);
                    $bb_stars = $bb_stars['stars'];
                ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="entity-card">
                        <div class="entity-image">
                             <?php if (!empty($blood_bank['bb_pic'])): ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/blood-banks/<?php echo $blood_bank['bb_pic']; ?>" 
                                    alt="<?php echo $blood_bank['blood_bank_name']; ?>" class="img-fluid" style="max-height: 300px;">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>admin/inc/uploads/default/bb.jpg" 
                                    alt="No Image" class="img-fluid" style="max-height: 300px;">
                            <?php endif; ?>     
                        </div>
                        <div class="entity-content">
                            <h5 class="entity-title heading-color"><?php echo htmlspecialchars($blood_bank['bb_name']); ?></h5>
                            <p class="entity-description normal-color"><?php echo htmlspecialchars(substr($blood_bank['bb_address'], 0, 100)) . '...'; ?></p>
                            <div class="entity-meta">
                                <span><i class="fas fa-map-marker-alt me-1 normal-color"></i><?php echo htmlspecialchars($blood_bank['city_name']); ?></span>
                                <span><i class="fas fa-phone me-1 normal-color"></i>Emergency: <?php echo htmlspecialchars($blood_bank['bb_contact']); ?></span>
                                <span><i class="fas fa-star text-warning me-1"></i><?=number_format($bb_stars,1)?></span>
                            </div>
                            <div class="entity-actions">
                                <a href="blood-bank-detail?blood_bankid=<?php echo $blood_bank['bb_id']; ?>" class="btn detail-btns">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="blood-banks" class="btn-view-all">View All Blood Banks</a>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="section-padding" id="reviews" style="background-color: var(--light);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title heading-color">Patient Reviews</h2>
                <p class="section-subtitle normal-color">What our patients say about their experience</p>
            </div>
            
            <!-- Reviews Carousel -->
            <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-aos="fade-up" data-aos-delay="200">
                <div class="carousel-inner">
                    <?php
                    $reviews_query = "SELECT dr.* FROM feedback dr WHERE dr.status = 0 and web_id=1 ORDER BY dr.feedback_id DESC LIMIT 6";
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

    <!-- Mobile-specific Back to Top Button -->
    <button id="mobileBackToTop" class="mobile-back-to-top" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
        <span>Top</span>
    </button>

<style>
    /* Mobile-specific Back to Top Button */
    .mobile-back-to-top {
        position: fixed;
        bottom: 80px;
        right: 15px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        border-radius: 25px;
        color: white;
        font-size: 10px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        transition: all 0.3s ease;
        z-index: 9998;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
    }
    
    .mobile-back-to-top i {
        font-size: 16px;
        margin-bottom: 2px;
    }
    
    .mobile-back-to-top.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .mobile-back-to-top:hover {
        background: linear-gradient(135deg, #218838, #1ea085);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.5);
    }
    
    /* Only show on mobile devices */
    @media (min-width: 769px) {
        .mobile-back-to-top {
            display: none !important;
        }
    }
    
    /* Adjust for small mobile */
    @media (max-width: 480px) {
        .mobile-back-to-top {
            bottom: 75px;
            right: 10px;
            width: 45px;
            height: 45px;
            font-size: 9px;
        }
        
        .mobile-back-to-top i {
            font-size: 14px;
        }
    }
</style>
<script>
$(document).ready(function() {
    $('#cityselect').select2({
        theme: 'bootstrap-5',
        placeholder: 'Search city...',
        allowClear: true,
        width: '100%'
    });
    
    // Filter category interactions
    $('.filter-category-item').click(function() {
        // Remove active class from all items
        $('.filter-category-item').removeClass('active');
        
        // Add active class to clicked item
        $(this).addClass('active');
        
        // Get the category
        var category = $(this).data('category');
        
        // Scroll to the relevant section
        scrollToSection(category);
    });
    
    // Mobile Back to Top Button functionality
    if (window.innerWidth <= 768) {
        // Show/Hide Mobile Back to Top Button on Scroll
        $(window).scroll(function() {
            const mobileBackToTopButton = document.getElementById('mobileBackToTop');
            if ($(window).scrollTop() > 300) {
                $('#mobileBackToTop').addClass('show');
            } else {
                $('#mobileBackToTop').removeClass('show');
            }
        });
    }
});

function showMoreFilters() {
    var moreFilters = $('#moreFilters');
    var seeMoreBtn = $('.see-more-btn');
    
    if (moreFilters.is(':visible')) {
        moreFilters.slideUp();
        seeMoreBtn.html('<i class="fas fa-plus me-2"></i>See More');
    } else {
        moreFilters.slideDown();
        seeMoreBtn.html('<i class="fas fa-minus me-2"></i>See Less');
    }
}

function scrollToSection(category) {
    var targetSection;
    
    switch(category) {
        case 'hospitals':
            targetSection = $('#hospitals-section');
            break;
        case 'doctors':
            targetSection = $('#doctors-section');
            break;
        case 'labs':
            targetSection = $('#labs-section');
            break;
        case 'blood-banks':
            targetSection = $('#blood-banks-section');
            break;
        case 'pharmacies':
            // Show message or redirect to pharmacies page
            alert('Pharmacies section coming soon!');
            return;
        case 'ambulance':
            // Show message or redirect to ambulance page
            alert('Ambulance services section coming soon!');
            return;
        case 'emergency':
            // Show emergency contact or redirect
            alert('Emergency: Call 911 or your local emergency number!');
            return;
        default:
            return;
    }
    
    if (targetSection.length) {
        $('html, body').animate({
            scrollTop: targetSection.offset().top - 100
        }, 800);
    }
}
</script>