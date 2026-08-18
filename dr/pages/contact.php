<?php include '../includes/header.php'; ?>
<?php include BASE_PATH.'/includes/menu.php'; ?>

<style>
/* ========================================
   ROOT VARIABLES
   ======================================== */
:root {
    --primary: #6366f1;
    --primary-light: #818cf8;
    --primary-dark: #4f46e5;
    --secondary: #8b5cf6;
    --accent: #f59e0b;
    --success: #22c55e;
    --text: #0f172a;
    --text-light: #64748b;
    --bg: #f1f5f9;
    --glass: rgba(255,255,255,0.75);
    --glass-dark: rgba(255,255,255,0.15);
    --shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
    --shadow-glow: 0 0 40px rgba(99,102,241,0.12);
}

/* ========================================
   GLOBAL
   ======================================== */
.section-padding {
    padding: 80px 0;
}

/* ========================================
   HERO SECTION - ANIMATED
   ======================================== */
.contact-hero {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #4f46e5 100%);
}

.contact-hero .hero-particles {
    position: absolute;
    inset: 0;
    z-index: 0;
    overflow: hidden;
}

.contact-hero .hero-particles .particle {
    position: absolute;
    width: 6px;
    height: 6px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    animation: floatParticle 20s infinite linear;
}

.contact-hero .hero-particles .particle:nth-child(1) { top: 5%; left: 5%; animation-duration: 18s; }
.contact-hero .hero-particles .particle:nth-child(2) { top: 20%; left: 90%; animation-duration: 22s; }
.contact-hero .hero-particles .particle:nth-child(3) { top: 50%; left: 10%; animation-duration: 16s; }
.contact-hero .hero-particles .particle:nth-child(4) { top: 70%; left: 85%; animation-duration: 24s; }
.contact-hero .hero-particles .particle:nth-child(5) { top: 35%; left: 50%; animation-duration: 20s; }
.contact-hero .hero-particles .particle:nth-child(6) { top: 85%; left: 30%; animation-duration: 19s; }

@keyframes floatParticle {
    0% { transform: translateY(0) rotate(0deg) scale(1); opacity: 0.2; }
    25% { opacity: 0.5; }
    50% { transform: translateY(-100px) rotate(180deg) scale(1.5); opacity: 0.3; }
    75% { opacity: 0.5; }
    100% { transform: translateY(0) rotate(360deg) scale(1); opacity: 0.2; }
}

.contact-hero .hero-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.3;
    animation: pulseGlow 7s ease-in-out infinite;
}

.contact-hero .hero-glow.glow-1 {
    width: 400px;
    height: 400px;
    top: -100px;
    right: -100px;
    background: var(--primary);
    animation-delay: 0s;
}

.contact-hero .hero-glow.glow-2 {
    width: 300px;
    height: 300px;
    bottom: -50px;
    left: -50px;
    background: var(--secondary);
    animation-delay: -3s;
}

@keyframes pulseGlow {
    0%, 100% { transform: scale(1); opacity: 0.2; }
    50% { transform: scale(1.2); opacity: 0.4; }
}

.contact-hero .hero-container {
    position: relative;
    z-index: 1;
    width: 100%;
    padding: 30px 0;
}

.contact-hero .hero-content {
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.contact-hero .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 24px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 50px;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 20px;
    animation: fadeInUp 0.8s ease-out;
}

.contact-hero .hero-badge i {
    color: var(--accent);
}

.contact-hero h1 {
    font-size: 3.5rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 12px;
    text-shadow: 0 4px 30px rgba(0,0,0,0.2);
    animation: fadeInUp 0.8s ease-out 0.2s backwards;
}

.contact-hero h1 .highlight {
    background: linear-gradient(135deg, var(--primary-light), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.contact-hero .hero-subtitle {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.6);
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.8;
    animation: fadeInUp 0.8s ease-out 0.4s backwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ========================================
   CONTACT CARDS SECTION
   ======================================== */
.contact-cards-section {
    background: var(--bg);
    padding: 60px 0 80px;
    position: relative;
}

.contact-cards-section .section-label {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 6px 18px;
    background: rgba(99,102,241,0.08);
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--primary);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
}

.contact-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 30px;
}

.contact-card-glass {
    background: var(--glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 32px 28px;
    text-align: center;
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.contact-card-glass::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
    opacity: 0;
    transition: opacity 0.4s;
}

.contact-card-glass:hover::before {
    opacity: 1;
}

.contact-card-glass:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow), var(--shadow-glow);
}

.contact-card-glass .icon-box {
    width: 70px;
    height: 70px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
    transition: all 0.3s ease;
}

.contact-card-glass:hover .icon-box {
    transform: scale(1.1) rotate(-5deg);
}

.contact-card-glass h4 {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 6px;
}

.contact-card-glass .card-desc {
    color: var(--text-light);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 12px;
}

.contact-card-glass .card-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.contact-card-glass .card-link:hover {
    gap: 14px;
    color: var(--primary-dark);
}

.contact-card-glass .card-link i {
    transition: transform 0.3s ease;
}

.contact-card-glass .card-link:hover i {
    transform: translateX(4px);
}

/* ========================================
   CONTACT FORM SECTION
   ======================================== */
.contact-form-section {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
    padding: 80px 0;
    color: white;
    position: relative;
    overflow: hidden;
}

.contact-form-section .section-label-light {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 6px 18px;
    background: rgba(255,255,255,0.06);
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
}

.contact-form-section .section-label-light i {
    color: var(--accent);
}

.contact-form-glass {
    background: var(--glass-dark);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 28px;
    padding: 40px;
    border: 1px solid rgba(255,255,255,0.06);
    transition: all 0.3s ease;
}

.contact-form-glass:hover {
    border-color: rgba(255,255,255,0.12);
}

.contact-form-glass h3 {
    font-size: 1.8rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 6px;
}

.contact-form-glass .form-desc {
    color: rgba(255,255,255,0.4);
    font-size: 1rem;
    margin-bottom: 24px;
}

.contact-form-glass .form-control {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 12px 16px;
    color: #fff;
    transition: all 0.3s ease;
}

.contact-form-glass .form-control:focus {
    background: rgba(255,255,255,0.06);
    border-color: var(--primary-light);
    box-shadow: 0 0 0 4px rgba(99,102,241,0.08);
}

.contact-form-glass .form-control::placeholder {
    color: rgba(255,255,255,0.25);
}

.contact-form-glass .form-label {
    color: rgba(255,255,255,0.5);
    font-weight: 500;
    font-size: 0.85rem;
}

.contact-form-glass select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='rgba(255,255,255,0.3)' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 40px;
}

.contact-form-glass select.form-control option {
    color: var(--text);
    background: #fff;
}

.btn-submit-glass {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    padding: 14px 36px;
    border-radius: 50px;
    font-weight: 700;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 8px 30px rgba(99,102,241,0.3);
    width: 100%;
    font-size: 1rem;
}

.btn-submit-glass:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(99,102,241,0.5);
    color: white;
}

/* ========================================
   MAP / INFO SECTION
   ======================================== */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 16px;
}

.info-item-glass {
    background: var(--glass-dark);
    backdrop-filter: blur(16px);
    border-radius: 16px;
    padding: 18px 20px;
    border: 1px solid rgba(255,255,255,0.04);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 14px;
}

.info-item-glass:hover {
    border-color: rgba(255,255,255,0.08);
    transform: translateX(4px);
}

.info-item-glass .info-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(255,255,255,0.04);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    font-size: 1.1rem;
    flex-shrink: 0;
}

.info-item-glass .info-content .ilabel {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.info-item-glass .info-content .ivalue {
    color: rgba(255,255,255,0.7);
    font-weight: 500;
    font-size: 0.9rem;
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 992px) {
    .contact-hero h1 { font-size: 2.8rem; }
    .info-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .contact-hero { min-height: 60vh; }
    .contact-hero h1 { font-size: 2.2rem; }
    .contact-hero .hero-subtitle { font-size: 1rem; }
    .contact-form-glass { padding: 24px; }
    .contact-form-glass h3 { font-size: 1.4rem; }
    .contact-cards-grid { grid-template-columns: 1fr; }
    .info-grid { grid-template-columns: 1fr; }
}

@media (max-width: 480px) {
    .contact-hero h1 { font-size: 1.8rem; }
    .contact-card-glass { padding: 24px 20px; }
    .contact-form-glass { padding: 18px; }
    .contact-cards-section { padding: 40px 0 60px; }
}
</style>

<!-- ======================================== -->
<!-- HERO SECTION -->
<!-- ======================================== -->
<section class="contact-hero">
    <!-- Particles -->
    <div class="hero-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Glow Orbs -->
    <div class="hero-glow glow-1"></div>
    <div class="hero-glow glow-2"></div>

    <div class="container hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-headset"></i> 24/7 Support Available
            </div>
            <h1>
                Get in <br>
                <span class="highlight">Touch</span>
            </h1>
            <p class="hero-subtitle">
                Have questions about our services? Need medical assistance? Our team is always ready to help you find the best healthcare solutions for your needs.
            </p>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- CONTACT CARDS SECTION -->
<!-- ======================================== -->
<section class="contact-cards-section">
    <div class="container">
        <div style="text-align:center;">
            <div class="section-label" style="justify-content:center;">
                <i class="fas fa-address-card"></i> Contact Information
            </div>
            <h2 style="font-size:2rem; font-weight:800; color:var(--text); margin-bottom:6px;">
                We're Here to Help
            </h2>
            <p style="color:var(--text-light); font-size:1rem;">
                Choose your preferred way to reach us
            </p>
        </div>

        <div class="contact-cards-grid">
            <!-- Email -->
            <div class="contact-card-glass" data-aos="fade-up" data-aos-delay="0">
                <div class="icon-box"><i class="fas fa-envelope"></i></div>
                <h4>Email Us</h4>
                <p class="card-desc">Send us an email and we'll respond within 24 hours</p>
                <a href="mailto:sohail.it99@gmail.com" class="card-link">
                    sohail.it99@gmail.com <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- Phone -->
            <div class="contact-card-glass" data-aos="fade-up" data-aos-delay="100">
                <div class="icon-box"><i class="fas fa-phone"></i></div>
                <h4>Call Us</h4>
                <p class="card-desc">Available 24/7 for emergency and general inquiries</p>
                <a href="tel:+923371320001" class="card-link">
                    +92 337 1320 001 <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <!-- WhatsApp -->
            <div class="contact-card-glass" data-aos="fade-up" data-aos-delay="200">
                <div class="icon-box"><i class="fab fa-whatsapp"></i></div>
                <h4>WhatsApp</h4>
                <p class="card-desc">Quick support via WhatsApp for instant responses</p>
                <a href="https://wa.me/+923371320001" class="card-link" target="_blank">
                    Chat Now <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- CONTACT FORM + INFO SECTION -->
<!-- ======================================== -->
<section class="contact-form-section">
    <div class="container">
        <div class="row g-4">
            <!-- Form Column -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="contact-form-glass">
                    <div class="section-label-light">
                        <i class="fas fa-pen"></i> Send a Message
                    </div>
                    <h3>Let's Talk</h3>
                    <p class="form-desc">Fill out the form below and we'll get back to you as soon as possible</p>

                    <form method="POST" action="submit-contact.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Your Name *</label>
                                <input type="text" class="form-control" name="name" required placeholder="Enter your full name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="email" required placeholder="your@email.com">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" placeholder="+92 300 000 0000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject *</label>
                                <select class="form-control" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="appointment">Appointment Booking</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="feedback">Feedback</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="partnership">Partnership</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message *</label>
                            <textarea class="form-control" name="message" rows="5" required placeholder="Tell us how we can help you..."></textarea>
                        </div>

                        <button type="submit" class="btn-submit-glass">
                            <i class="fas fa-paper-plane me-2"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>

            <!-- Info Column -->
            <div class="col-lg-5" data-aos="fade-left">
                <div class="contact-form-glass" style="height:100%; display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div class="section-label-light">
                            <i class="fas fa-info-circle"></i> Quick Info
                        </div>
                        <h3 style="font-size:1.4rem; margin-bottom:16px;">Connect With Us</h3>

                        <div class="info-grid">
                            <div class="info-item-glass">
                                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div class="info-content">
                                    <div class="ilabel">Address</div>
                                    <div class="ivalue">Main Office, Kohat, Pakistan</div>
                                </div>
                            </div>
                            <div class="info-item-glass">
                                <div class="info-icon"><i class="fas fa-phone"></i></div>
                                <div class="info-content">
                                    <div class="ilabel">Phone</div>
                                    <div class="ivalue">+92 337 1320 001</div>
                                </div>
                            </div>
                            <div class="info-item-glass">
                                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                                <div class="info-content">
                                    <div class="ilabel">Email</div>
                                    <div class="ivalue">sohail.it99@gmail.com</div>
                                </div>
                            </div>
                            <div class="info-item-glass">
                                <div class="info-icon"><i class="fas fa-clock"></i></div>
                                <div class="info-content">
                                    <div class="ilabel">Working Hours</div>
                                    <div class="ivalue">24/7 Emergency Support</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:20px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.04);">
                        <div style="display:flex; gap:12px; flex-wrap:wrap;">
                            <a href="https://wa.me/+923371320001" target="_blank" style="display:inline-flex; align-items:center; gap:8px; padding:8px 18px; background:rgba(255,255,255,0.04); border-radius:50px; color:rgba(255,255,255,0.5); text-decoration:none; transition:all 0.3s ease; font-size:0.85rem;">
                                <i class="fab fa-whatsapp" style="color:#25D366;"></i> WhatsApp
                            </a>
                            <a href="mailto:sohail.it99@gmail.com" style="display:inline-flex; align-items:center; gap:8px; padding:8px 18px; background:rgba(255,255,255,0.04); border-radius:50px; color:rgba(255,255,255,0.5); text-decoration:none; transition:all 0.3s ease; font-size:0.85rem;">
                                <i class="fas fa-envelope" style="color:var(--primary-light);"></i> Email
                            </a>
                            <a href="tel:+923371320001" style="display:inline-flex; align-items:center; gap:8px; padding:8px 18px; background:rgba(255,255,255,0.04); border-radius:50px; color:rgba(255,255,255,0.5); text-decoration:none; transition:all 0.3s ease; font-size:0.85rem;">
                                <i class="fas fa-phone" style="color:var(--accent);"></i> Call
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ======================================== -->
<!-- FOOTER -->
<!-- ======================================== -->
<?php include BASE_PATH.'/includes/footer.php'; ?>