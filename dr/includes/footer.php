 <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- Logo Section -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>" class="footer-logo">
                        <i class="fas fa-heartbeat me-2"></i>DoctorApp
                    </a>
                    <p class="footer-desc">
                        Your trusted healthcare platform connecting you with top doctors, hospitals, and medical facilities. Experience quality healthcare at your fingertips.
                    </p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/sohail.fixit"><i class="fab fa-facebook-f"></i></a>
                        <!-- <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a> -->
                        <a href="https://www.tiktok.com/@sohail_fixit"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="footer-links list-unstyled">
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>">Home</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>doctors">Doctors</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>hospitals">Hospitals</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>labs">Laboratories</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>blood-banks">Blood Banks</a></li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Services</h5>
                    <ul class="footer-links list-unstyled">
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>about">About Us</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>contact">Contact</a></li>
                        <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '../'; ?>about#reviews">Patient Reviews</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5>Contact Info</h5>
                    <div class="footer-contact">
                        <p class="mb-3">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Home Town Kohat, Pakistan
                        </p>
                        <p class="mb-3">
                            <i class="fas fa-phone me-2"></i>
                            +92 (337) 132-0001
                        </p>
                        <p class="mb-3">
                            <i class="fas fa-envelope me-2"></i>
                            soahil.it99@gmail.com
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            24/7
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Footer -->
            <div class="border-top border-secondary pt-4 mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0 text-muted">&copy; 2026 DoctorApp. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0">
                            <a href="#" class="text-muted me-3">Privacy Policy</a>
                            <a href="#" class="text-muted">Terms of Service</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Footer Styles -->
    <style>
        .footer {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c3e50 100%);
            color: #b0b0b0;
            padding: 80px 0 30px;
            margin-top: 80px;
        }
        
        .footer-logo {
            font-size: 28px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .footer-logo i {
            color: var(--primary);
        }
        
        .footer-desc {
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer h5::after {
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
        
        .footer-contact {
            color: #b0b0b0;
        }
        
        .footer-contact i {
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3498db, #FF6B35);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 18px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
        }
        
        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .back-to-top:hover {
            background: linear-gradient(135deg, #2980b9, #e55a2b);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        
        /* Mobile Responsive Styles for Back to Top Button */
        @media (max-width: 768px) {
            .back-to-top {
                bottom: 10px !important;
                right: 20px !important;
                width: 45px !important;
                height: 45px !important;
                font-size: 16px !important;
                z-index: 9999 !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: translateY(0) !important;
            }
        }
        
        @media (max-width: 576px) {
            .back-to-top {
                bottom: 15px !important;
                right: 15px !important;
                width: 40px !important;
                height: 40px !important;
                font-size: 14px !important;
                z-index: 9999 !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: translateY(0) !important;
            }
        }
        
        /* Extra small mobile devices */
        @media (max-width: 480px) {
            .back-to-top {
                bottom: 10px !important;
                right: 10px !important;
                width: 35px !important;
                height: 35px !important;
                font-size: 12px !important;
                z-index: 99999 !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: translateY(0) !important;
                display: block !important;
            }
        }
    </style>

    <!-- Required Jqurey -->
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/Jquery/dist/jquery.min.js"></script>
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/jquery-ui/jquery-ui.min.js"></script>
   <script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/tether/dist/js/tether.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- live search select2 -->
   <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
   
<script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Book Appointment Function
        function bookAppointment(doctorId) {
            // Redirect to home page with doctor ID or open appointment modal
            window.location.href = '../index.php?doctor_id=' + doctorId + '#appointment';
        }
        
        // Back to Top Function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        // Show/Hide Back to Top Button on Scroll
        window.addEventListener('scroll', function() {
            const backToTopButton = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
    </script>
</body>
</html>