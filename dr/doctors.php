<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Doctors - Doctor Appointment System</title>
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

        /* Page Header */
        .page-header {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('admin/inc/assets/images/light-box/sl1.jpg');
            background-size: cover;
            background-position: center;
            padding: 150px 0 80px;
            color: white;
            text-align: center;
            margin-bottom: 50px;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .breadcrumb {
            justify-content: center;
            background: transparent;
        }

        .breadcrumb-item a {
            color: white;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: rgba(255,255,255,0.7);
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
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-heartbeat me-2"></i>DoctorApp
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link active" href="doctors.php">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="hospitals.php">Hospitals</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#categories">Services</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link btn-login" href="admin/">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1 class="page-title" data-aos="fade-down">Our Expert Doctors</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" data-aos="fade-up" data-aos-delay="200">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Doctors</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Doctors Section -->
    <section class="section-padding mb-5">
        <div class="container">
            <div class="row">
                <?php
                // Fetch all doctors
                $doctor_query = "SELECT d.*, dct.type as specialization 
                               FROM doctors d 
                               LEFT JOIN dr_cat_types dct ON d.cat_type_id = dct.dr_cat_type_id 
                               WHERE d.status = 1 
                               ORDER BY d.created_at DESC";
                $doctor_result = mysqli_query($con, $doctor_query);
                
                if (mysqli_num_rows($doctor_result) > 0) {
                    $delay = 0;
                    while ($doctor = mysqli_fetch_assoc($doctor_result)) {
                        $img_src = !empty($doctor['doctor_pic']) 
                            ? "admin/inc/uploads/doctors/" . $doctor['doctor_pic'] 
                            : "admin/inc/assets/images/avatar-1.png";
                ?>
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay % 400; ?>">
                    <div class="doctor-card-modern">
                        <div class="doctor-img-wrapper">
                            <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>">
                            <div class="doctor-social">
                                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                        <div class="doctor-info">
                            <h5 class="doctor-name"><?php echo htmlspecialchars($doctor['doctor_name']); ?></h5>
                            <span class="doctor-spec"><?php echo htmlspecialchars($doctor['specialization'] ?? 'Specialist'); ?></span>
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
                ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        No doctors found in the database.
                    </div>
                </div>
                <?php } ?>
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
                        <li><a href="index.php#about">About Us</a></li>
                        <li><a href="index.php#doctors">Our Doctors</a></li>
                        <li><a href="index.php#categories">Services</a></li>
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
    </script>
</body>
</html>