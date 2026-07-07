<?php
require_once '../config.php';
$title = "Contact Us - HHC";
?>
<?php include_once BASE_PATH . '/inc/site_header.php'; ?>
<body>
  <div class="page-shell">
    <?php include_once BASE_PATH . '/inc/home_menu.php'; ?>

    <main>
      <section class="hero-section" id="contact-hero">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="hero-content">
                <span class="section-kicker">Get In Touch</span>
                <h1 class="hero-title">Contact Us Today</h1>
                <p class="hero-text">Have questions or need to schedule an appointment? We're here to help! Reach out to us anytime.</p>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-visual">
                <img src="https://images.unsplash.com/photo-1432888622747-4eb9a8efeb07?auto=format&fit=crop&w=800&q=80" alt="Contact" class="w-100">
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="contact-section section-padding" id="contact">
        <div class="container">
          <div class="section-heading">
            <span class="section-kicker">Contact Details</span>
            <h2 class="section-title">Get In Touch</h2>
          </div>
          <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
              <div class="card border-0 shadow-sm p-4">
                <h4 class="fw-bold mb-4"><i class="bi bi-geo-alt text-primary me-2"></i>Contact Info</h4>
                
                <div class="mb-4">
                  <h6 class="fw-semibold mb-2"><i class="bi bi-telephone text-primary me-2"></i>Phone</h6>
                  <a href="tel:+923343021818" class="text-decoration-none text-muted">+92 334 302 1818</a>
                </div>
                
                <div class="mb-4">
                  <h6 class="fw-semibold mb-2"><i class="bi bi-envelope text-primary me-2"></i>Email</h6>
                  <a href="mailto:Info@Hassanhomehealthcare.Com" class="text-decoration-none text-muted">Info@Hassanhomehealthcare.Com</a>
                </div>
                
                <div class="mb-4">
                  <h6 class="fw-semibold mb-2"><i class="bi bi-geo-alt text-primary me-2"></i>Address</h6>
                  <p class="text-muted mb-0">Kohat, Khyber Pakhtunkhwa, Pakistan</p>
                </div>
                
                <hr>
                
                <h4 class="fw-bold mb-4"><i class="bi bi-clock text-primary me-2"></i>Working Hours</h4>
                <ul class="list-unstyled">
                  <li class="mb-2 d-flex justify-content-between"><span>Monday - Friday</span><strong>9:00 AM - 5:00 PM</strong></li>
                  <li class="mb-2 d-flex justify-content-between"><span>Saturday</span><strong>9:00 AM - 5:00 PM</strong></li>
                  <li class="d-flex justify-content-between"><span>Sunday</span><strong>Closed</strong></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="section-padding pt-0" id="map">
        <div class="container">
          <div class="section-heading">
            <span class="section-kicker">Our Location</span>
            <h2 class="section-title">Find Us on the Map</h2>
          </div>
          <div class="card border-0 shadow-sm overflow-hidden">
            <iframe
              src="https://www.google.com/maps?q=Kohat,+Khyber+Pakhtunkhwa,+Pakistan&z=14&output=embed"
              width="100%"
              height="420"
              style="border:0;"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              title="HHC location map"></iframe>
          </div>
        </div>
      </section>
    </main>

    <?php include_once BASE_PATH . '/inc/site_footer.php'; ?>
  </div>
</body>
</html>
