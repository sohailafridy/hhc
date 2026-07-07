<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointment System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4facfe;
            --primary-dark: #00f2fe;
            --secondary: #6c757d;
            --accent: #764ba2;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --hover-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            overflow-x: hidden;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            padding: 15px 0;
            transition: all 0.3s;
        }
        
        .navbar-brand {
            font-size: 24px;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            margin: 0 10px;
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background: var(--gradient);
            transition: width 0.3s;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .btn-login {
            background: var(--gradient);
            color: white !important;
            border-radius: 50px;
            padding: 8px 25px;
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
            transition: transform 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }

        /* Hero Section */
        .carousel-item {
            height: 85vh;
            min-height: 500px;
            position: relative;
        }
        
        .carousel-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6));
            z-index: 1;
        }
        
        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }
        
        .carousel-caption {
            z-index: 2;
            bottom: 30%;
            text-align: left;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-text {
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 600px;
        }
        
        .btn-hero {
            padding: 12px 35px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: white;
            color: var(--primary);
            border: none;
            transition: all 0.3s;
        }
        
        .btn-hero:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        /* Hero Form Styles */
        .hero-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
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

        /* Common Section Styles */
        .section-padding {
            padding: 100px 0;
            position: relative;
        }
        
        .section-header {
            margin-bottom: 60px;
            text-align: center;
        }
        
        .section-subtitle {
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.9rem;
            display: block;
            margin-bottom: 10px;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
        }
        
        .section-line {
            width: 70px;
            height: 4px;
            background: var(--gradient);
            margin: 0 auto;
            border-radius: 2px;
        }

        /* About Section */
        .about-img-box {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }
        
        .about-experience {
            position: absolute;
            bottom: 30px;
            left: -30px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: var(--hover-shadow);
            display: flex;
            align-items: center;
            border-left: 5px solid var(--primary);
        }
        
        .exp-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
            margin-right: 15px;
        }
        
        .exp-text {
            font-weight: 600;
            color: var(--dark);
            line-height: 1.2;
        }

        /* Doctor Cards Modern - Enhanced */
        .doctor-card-modern {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            margin-bottom: 30px;
            height: 100%;
            border: none;
        }
        
        .doctor-card-modern:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }
        
        .doctor-img-wrapper {
            height: 320px;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 50px 0;
            z-index: 1;
        }
        
        .doctor-img-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.7));
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .doctor-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        .doctor-card-modern:hover .doctor-img-wrapper img {
            transform: scale(1.15);
        }
        
        .doctor-social {
            position: absolute;
            top: 20px;
            right: -60px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: right 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 2;
        }
        
        .doctor-card-modern:hover .doctor-social {
            right: 20px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            background: white;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            opacity: 0;
            transform: translateX(20px);
            text-decoration: none;
        }
        
        .doctor-card-modern:hover .social-icon {
            opacity: 1;
            transform: translateX(0);
        }
        
        .doctor-card-modern:hover .social-icon:nth-child(1) { transition-delay: 0.1s; }
        .doctor-card-modern:hover .social-icon:nth-child(2) { transition-delay: 0.2s; }
        .doctor-card-modern:hover .social-icon:nth-child(3) { transition-delay: 0.3s; }
        
        .social-icon:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.1) !important;
        }
        
        .doctor-info {
            padding: 25px;
            text-align: left;
            position: relative;
        }
        
        .doctor-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
            transition: color 0.3s;
        }
        
        .doctor-card-modern:hover .doctor-name {
            color: var(--primary);
        }
        
        .doctor-spec {
            color: var(--secondary);
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 15px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .doctor-link-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 0;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            position: relative;
            transition: all 0.3s;
        }
        
        .doctor-link-btn i {
            margin-left: 8px;
            transition: transform 0.3s;
        }
        
        .doctor-link-btn:hover i {
            transform: translateX(5px);
        }
        
        .doctor-link-btn::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s;
        }
        
        .doctor-link-btn:hover::after {
            width: 100%;
        }

        /* Hospital Cards Modern */
        .hospital-card-modern {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.4s ease;
            position: relative;
            margin-bottom: 30px;
            border: 1px solid rgba(0,0,0,0.03);
            height: 100%;
        }
        
        .hospital-card-modern:hover {
            transform: translateY(-10px);
            box-shadow: var(--hover-shadow);
        }
        
        .hospital-img-wrapper {
            height: 200px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .hospital-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .hospital-card-modern:hover .hospital-img-wrapper img {
            transform: scale(1.1);
        }
        
        .hospital-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.4s;
        }
        
        .hospital-card-modern:hover .hospital-overlay {
            opacity: 1;
        }
        
        .hospital-link {
            width: 50px;
            height: 50px;
            background: white;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s;
            transform: scale(0.8);
        }
        
        .hospital-card-modern:hover .hospital-link {
            transform: scale(1);
        }
        
        .hospital-link:hover {
            background: var(--primary);
            color: white;
        }
        
        .hospital-info {
            padding: 20px;
            text-align: center;
        }
        
        .hospital-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
            line-height: 1.3;
        }
        
        .hospital-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .hospital-location,
        .hospital-phone {
            font-size: 0.85rem;
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .hospital-location i,
        .hospital-phone i {
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        /* Hospital Carousel Styles */
        #hospitalsCarousel {
            position: relative;
            padding: 0 60px;
        }
        
        #hospitalsCarousel.carousel-fade .carousel-item {
            opacity: 0;
            transition: opacity 0.6s ease-in-out;
        }
        
        #hospitalsCarousel.carousel-fade .carousel-item.active {
            opacity: 1;
        }
        
        #hospitalsCarousel .carousel-item {
            transition: transform 0.6s ease-in-out;
        }
        
        #hospitalsCarousel .carousel-control-prev,
        #hospitalsCarousel .carousel-control-next {
            width: 50px;
            height: 50px;
            background: var(--gradient);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.9;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
        }
        
        #hospitalsCarousel .carousel-control-prev {
            left: 10px;
        }
        
        #hospitalsCarousel .carousel-control-next {
            right: 10px;
        }
        
        #hospitalsCarousel .carousel-control-prev:hover,
        #hospitalsCarousel .carousel-control-next:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
        }
        
        #hospitalsCarousel .carousel-control-prev-icon,
        #hospitalsCarousel .carousel-control-next-icon {
            width: 20px;
            height: 20px;
        }
        
        #hospitalsCarousel .carousel-indicators {
            margin-bottom: -40px;
        }
        
        #hospitalsCarousel .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            opacity: 0.3;
            transition: all 0.3s;
        }
        
        #hospitalsCarousel .carousel-indicators button.active {
            opacity: 1;
            width: 30px;
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            #hospitalsCarousel {
                padding: 0 40px;
            }
            
            #hospitalsCarousel .carousel-control-prev,
            #hospitalsCarousel .carousel-control-next {
                width: 40px;
                height: 40px;
            }
        }

        /* Categories Modern */
        .category-box {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: all 0.4s;
            position: relative;
            z-index: 1;
            overflow: hidden;
            height: 100%;
            border: 1px solid #eee;
        }
        
        .category-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s;
        }
        
        .category-box:hover {
            transform: translateY(-10px);
            border-color: transparent;
        }
        
        .category-box:hover::before {
            opacity: 1;
        }
        
        .cat-icon-box {
            width: 80px;
            height: 80px;
            background: rgba(79, 172, 254, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            transition: all 0.4s;
        }
        
        .cat-icon-box i {
            font-size: 35px;
            color: var(--primary);
            transition: all 0.4s;
        }
        
        .category-box:hover .cat-icon-box {
            background: white;
            transform: scale(1.1) rotateY(180deg);
        }
        
        .category-box:hover .cat-icon-box i {
            color: var(--primary);
        }
        
        .category-title {
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--dark);
            transition: color 0.4s;
        }
        
        .category-desc {
            color: var(--secondary);
            font-size: 0.9rem;
            transition: color 0.4s;
        }
        
        .category-box:hover .category-title,
        .category-box:hover .category-desc {
            color: white;
        }

        /* Stats Section */
        .stats-section {
            background: var(--gradient);
            padding: 60px 0;
            color: white;
            margin-top: 50px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background-color: #1a1a1a;
            color: #b0b0b0;
            padding: 80px 0 30px;
        }
        
        .footer-logo {
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            display: block;
        }
        
        .footer-logo span {
            color: var(--primary);
        }
        
        .footer-desc {
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .footer h4 {
            color: white;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer h4::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 30px;
            height: 2px;
            background: var(--primary);
        }
        
        .footer-links li {
            margin-bottom: 15px;
        }
        
        .footer-links a {
            color: #b0b0b0;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .footer-links a:hover {
            color: white;
            padding-left: 10px;
        }
        
        .footer-links a::before {
            content: '\f105';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 10px;
            font-size: 12px;
            color: var(--primary);
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            border-radius: 50%;
            margin-right: 10px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        /* Background Shapes */
        .blob-bg {
            position: absolute;
            z-index: -1;
            opacity: 0.05;
        }
        
        .blob-1 { top: 10%; left: -10%; width: 500px; }
        .blob-2 { bottom: 10%; right: -10%; width: 500px; }
    </style>
</head>
<body>

    <!-- Navbar -->
   <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Hero Section -->
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="admin/inc/assets/images/light-box/sl1.jpg" alt="Healthcare">
                <div class="carousel-caption">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-7">
                                <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill" data-aos="fade-down">WELCOME TO DOCTORAPP</span>
                                <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">We Care About <br>Your Health</h1>
                                <p class="hero-text" data-aos="fade-up" data-aos-delay="400">Experience world-class healthcare with our team of expert doctors and state-of-the-art facilities designed for your comfort and recovery.</p>
                                <div data-aos="fade-up" data-aos-delay="600">
                                    <a href="#doctors" class="btn btn-hero me-3">Find a Doctor</a>
                                    <a href="#about" class="btn btn-outline-light rounded-pill px-4 py-3 fw-bold">Learn More</a>
                                </div>
                            </div>
                            <div class="col-lg-5" data-aos="fade-left" data-aos-delay="400">
                                <div class="hero-form bg-white p-4 rounded-3 shadow-lg">
                                    <h4 class="mb-3 text-dark fw-bold">Quick Appointment</h4>
                                    <form action="#" method="POST" class="appointment-form">
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Full Name</label>
                                            <input type="text" class="form-control" name="name" placeholder="Enter your name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Phone Number</label>
                                            <input type="tel" class="form-control" name="phone" placeholder="Enter your phone" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Select Department</label>
                                            <select class="form-select" name="department" required>
                                                <option value="">Choose Department</option>
                                                <option value="cardiology">Cardiology</option>
                                                <option value="neurology">Neurology</option>
                                                <option value="pediatrics">Pediatrics</option>
                                                <option value="orthopedics">Orthopedics</option>
                                                <option value="general">General Medicine</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Search Doctors</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="search" placeholder="Search by doctor name or specialty...">
                                                <button class="btn btn-outline-primary" type="button">
                                                    <i class="fas fa-search me-1"></i>Find
                                                </button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="city" value="Karachi">
                                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                                            <i class="fas fa-calendar-check me-2"></i>Book Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="admin/inc/assets/images/light-box/sl2.jpg" alt="Expert Doctors">
                <div class="carousel-caption">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8">
                                <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill">EXPERT MEDICAL TEAM</span>
                                <h1 class="hero-title">Qualified Doctors <br>You Can Trust</h1>
                                <p class="hero-text">Our medical professionals are here to provide you with the best medical advice and treatment plans tailored to your needs.</p>
                                <div>
                                    <a href="#doctors" class="btn btn-hero me-3">Book Appointment</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>

    <!-- About Section -->
    <section id="about" class="section-padding">
        <!-- SVG Blobs for background decoration -->
        <svg class="blob-bg blob-1" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path fill="#4facfe" d="M44.7,-76.4C58.9,-69.2,71.8,-59.1,81.6,-46.6C91.4,-34.1,98.1,-19.2,95.8,-5.3C93.5,8.6,82.2,21.5,71.4,32.4C60.6,43.3,50.3,52.2,39.3,59.5C28.3,66.8,16.6,72.5,4.3,75.4C-8,78.3,-20.9,78.4,-32.6,73.8C-44.3,69.2,-54.8,59.9,-63.4,49.1C-72,38.3,-78.7,26,-81.4,12.6C-84.1,-0.8,-82.8,-15.3,-75.7,-27.4C-68.6,-39.5,-55.7,-49.2,-42.6,-56.9C-29.5,-64.6,-16.2,-70.3,-1.5,-67.8C13.2,-65.3,26.4,-54.6,30.5,-83.6L44.7,-76.4Z" transform="translate(100 100)" />
        </svg>

        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                    <div class="about-img-box">
                        <img src="admin/inc/assets/images/light-box/v1.jpg" alt="About Hospital" class="img-fluid w-100">
                        <div class="about-experience">
                            <div class="exp-number">25+</div>
                            <div class="exp-text">Years of <br>Experience</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-5" data-aos="fade-left">
                    <span class="section-subtitle">About Us</span>
                    <h2 class="section-title">Best Medical Care For <br>Your Family</h2>
                    <div class="section-line ms-0 mb-4"></div>
                    <p class="text-secondary mb-4">We provide specialized medical care with a holistic approach. Our dedicated team of professionals ensures that you receive the best treatment in a comfortable environment.</p>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-3 rounded-circle text-primary me-3">
                                    <i class="fas fa-user-md fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Qualified Doctors</h5>
                                    <small class="text-muted">Top professionals</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-3 rounded-circle text-primary me-3">
                                    <i class="fas fa-ambulance fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Emergency Care</h5>
                                    <small class="text-muted">24/7 Support</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="#" class="btn btn-login">Read More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="0">
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Doctors</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-number">2k+</div>
                        <div class="stat-label">Happy Patients</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Departments</div>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section id="doctors" class="section-padding bg-light">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-subtitle">Our Team</span>
                <h2 class="section-title">Meet Our Expert Doctors</h2>
                <div class="section-line"></div>
                <p class="text-secondary mt-3 mx-auto" style="max-width: 600px;">Highly qualified doctors ready to serve you with dedication and expertise.</p>
            </div>
            
            <div class="row">
                <?php
                $doctor_query = "SELECT d.*, dct.type as specialization 
                               FROM doctors d 
                               LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                               WHERE d.status = 1 
                               ORDER BY d.created_at DESC LIMIT 4";
                $doctor_result = mysqli_query($con, $doctor_query);
                
                if (mysqli_num_rows($doctor_result) > 0) {
                    $delay = 0;
                    while ($doctor = mysqli_fetch_assoc($doctor_result)) {
                        $img_src = !empty($doctor['doctor_pic']) 
                            ? "admin/inc/uploads/doctors/" . $doctor['doctor_pic'] 
                            : "admin/inc/assets/images/avatar-1.png";
                ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="doctor-card-modern">
                        <div class="doctor-img-wrapper">
                            <img src="<?php echo $img_src; ?>" alt="<?php echo $doctor['doctor_name']; ?>">
                            <div class="doctor-social">
                                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="doctor-info">
                            <h5 class="doctor-name"><?php echo $doctor['doctor_name']; ?></h5>
                            <span class="doctor-spec"><?php echo $doctor['specialization'] ?? 'Specialist'; ?></span>
                            <p class="text-muted small mb-3"><?php echo substr($doctor['short_detail'], 0, 60) . '...'; ?></p>
                            <a href="admin/doctors/profile?id=<?php echo $doctor['doctor_id']; ?>" class="doctor-link-btn">
                                View Profile <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
                        $delay += 100;
                    }
                } else {
                    echo '<div class="col-12 text-center"><p>No doctors found.</p></div>';
                }
                ?>
            </div>
            
            <div class="row mt-5 text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="col-12">
                    <a href="doctors.php" class="btn btn-login btn-lg px-5 py-3">
                        View All Doctors <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Hospitals Section -->
    <section id="hospitals" class="section-padding">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-subtitle">Our Partners</span>
                <h2 class="section-title">Top Hospitals</h2>
                <div class="section-line"></div>
                <p class="text-secondary mt-3 mx-auto" style="max-width: 600px;">Partnering with the best healthcare facilities to provide you with exceptional medical care.</p>
            </div>
            
            <?php
            $hospital_query = "SELECT h.*, c.city_name 
                             FROM hospitals h 
                             LEFT JOIN cities c ON h.city_id = c.city_id 
                             WHERE h.status = 1 
                             ORDER BY h.created_at DESC LIMIT 4";
            $hospital_result = mysqli_query($con, $hospital_query);
            
            if (mysqli_num_rows($hospital_result) > 0) {
                $delay = 0;
            ?>
            <div class="row">
                <?php
                while ($hospital = mysqli_fetch_assoc($hospital_result)) {
                    $img_src = !empty($hospital['hospital_pic']) 
                        ? "admin/inc/uploads/hospitals/" . $hospital['hospital_pic'] 
                        : "https://via.placeholder.com/400x300/667eea/ffffff?text=" . urlencode($hospital['hospital_name']);
                ?>
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="hospital-card-modern">
                        <div class="hospital-img-wrapper">
                            <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($hospital['hospital_name']); ?>">
                            <div class="hospital-overlay">
                                <a href="#" class="hospital-link">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="hospital-info">
                            <h5 class="hospital-name"><?php echo htmlspecialchars($hospital['hospital_name']); ?></h5>
                            <div class="hospital-meta">
                                <span class="hospital-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($hospital['city_name'] ?? 'N/A'); ?>
                                </span>
                                <?php if (!empty($hospital['hospital_phone'])): ?>
                                <span class="hospital-phone">
                                    <i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($hospital['hospital_phone']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    $delay += 100;
                } 
                ?>
            </div>
            
            <div class="row mt-5 text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="col-12">
                    <a href="hospitals.php" class="btn btn-login btn-lg px-5 py-3">
                        View All Hospitals <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            
            <?php } else { ?>
            <div class="col-12 text-center">
                <p>No hospitals found.</p>
            </div>
            <?php } ?>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="section-padding bg-light">
        <svg class="blob-bg blob-2" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path fill="#764ba2" d="M39.9,-65.7C50.8,-57.8,58.3,-45.5,65.1,-33.1C71.8,-20.7,77.8,-8.2,76.6,3.8C75.3,15.8,66.8,27.2,57.5,37.1C48.2,47,38.1,55.3,26.9,61.1C15.6,66.9,3.3,70.2,-8.1,68.6C-19.5,67,-30,60.5,-40.4,53.4C-50.8,46.3,-61.1,38.6,-68.2,28.2C-75.3,17.8,-79.2,4.7,-77.6,-7.7C-76,-20.1,-68.9,-31.8,-59.5,-41.2C-50.1,-50.6,-38.4,-57.7,-26.8,-65.1C-15.1,-72.5,-3.6,-80.2,7.2,-78.9C18,-77.6,36,-67.2,39.9,-65.7Z" transform="translate(100 100)" />
        </svg>
        
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="section-subtitle">Services</span>
                <h2 class="section-title">Medical Departments</h2>
                <div class="section-line"></div>
            </div>
            
            <div class="row">
                <?php
                $cat_query = "SELECT * FROM dr_categories LIMIT 4";
                $cat_result = mysqli_query($con, $cat_query);
                
                $icons = ['fa-heart-pulse', 'fa-brain', 'fa-tooth', 'fa-baby']; 
                $i = 0;
                $delay = 0;
                
                if (mysqli_num_rows($cat_result) > 0) {
                    while ($cat = mysqli_fetch_assoc($cat_result)) {
                        $current_icon = $icons[$i % 4];
                        $i++;
                ?>
                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="category-box">
                        <div class="cat-icon-box">
                            <i class="fas <?php echo $current_icon; ?>"></i>
                        </div>
                        <h4 class="category-title"><?php echo $cat['cat_name']; ?></h4>
                        <p class="category-desc">Comprehensive diagnosis and treatment services for <?php echo strtolower($cat['cat_name']); ?>.</p>
                        <a href="#" class="btn btn-outline-primary rounded-pill btn-sm mt-3 stretched-link">Learn More</a>
                    </div>
                </div>
                <?php
                        $delay += 100;
                    }
                } else {
                    $fallback_cats = ['Cardiology', 'Neurology', 'Dental', 'Pediatrics'];
                    foreach($fallback_cats as $index => $cat_name) {
                        $current_icon = $icons[$index];
                ?>
                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="category-box">
                        <div class="cat-icon-box">
                            <i class="fas <?php echo $current_icon; ?>"></i>
                        </div>
                        <h4 class="category-title"><?php echo $cat_name; ?></h4>
                        <p class="category-desc">Comprehensive diagnosis and treatment services for <?php echo strtolower($cat_name); ?>.</p>
                        <a href="#" class="btn btn-outline-primary rounded-pill btn-sm mt-3 stretched-link">Learn More</a>
                    </div>
                </div>
                <?php
                        $delay += 100;
                    }
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4" data-aos="fade-up">
                    <a href="#" class="footer-logo"><i class="fas fa-heartbeat"></i> Doctor<span>App</span></a>
                    <p class="footer-desc">Your trusted partner in health. We are committed to providing advanced medical care with compassion and expertise to our community.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <h4>Quick Links</h4>
                    <ul class="list-unstyled footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#doctors">Our Doctors</a></li>
                        <li><a href="#categories">Services</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <h4>Our Services</h4>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#">Cardiology</a></li>
                        <li><a href="#">Neurology</a></li>
                        <li><a href="#">Dental Care</a></li>
                        <li><a href="#">Pediatrics</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <h4>Contact Info</h4>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#"><i class="fas fa-map-marker-alt me-2 text-primary"></i> 123 Health Street, City</a></li>
                        <li><a href="#"><i class="fas fa-phone-alt me-2 text-primary"></i> +1 234 567 8900</a></li>
                        <li><a href="#"><i class="fas fa-envelope me-2 text-primary"></i> info@doctorapp.com</a></li>
                        <li><a href="#"><i class="fas fa-clock me-2 text-primary"></i> Mon - Sat: 8:00 - 20:00</a></li>
                    </ul>
                </div>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin: 30px 0;">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> DoctorApp. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">Designed with <i class="fas fa-heart text-danger"></i> for better health</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
        
        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('shadow-sm');
                document.querySelector('.navbar').style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                document.querySelector('.navbar').classList.remove('shadow-sm');
                document.querySelector('.navbar').style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });
        
        // Initialize Hospitals Carousel with Auto-play
        document.addEventListener('DOMContentLoaded', function() {
            const hospitalsCarousel = document.getElementById('hospitalsCarousel');
            if (hospitalsCarousel) {
                const carousel = new bootstrap.Carousel(hospitalsCarousel, {
                    interval: 4000,
                    ride: 'carousel',
                    pause: false,
                    wrap: true
                });
                
                // Ensure carousel keeps cycling
                hospitalsCarousel.addEventListener('mouseenter', function() {
                    carousel.pause();
                });
                
                hospitalsCarousel.addEventListener('mouseleave', function() {
                    carousel.cycle();
                });
            }
        });
    </script>
</body>
</html>
