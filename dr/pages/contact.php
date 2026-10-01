<?php include '../includes/header.php'; ?>
<?php include BASE_PATH.'/includes/menu.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>style/contact-us.css">

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