<?php
require_once '../config.php';
$title = "About Us - HHC";
?>
<?php include_once BASE_PATH . '/inc/site_header.php'; ?>
<body>
  <div class="page-shell">
    <?php include_once BASE_PATH . '/inc/home_menu.php'; ?>

    <main>
      <section class="hero-section" id="about-hero">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="hero-content">
                <span class="section-kicker">About HHC</span>
                <h1 class="hero-title">Trusted Healthcare at Your Home</h1>
                <p class="hero-text">Hassan Home Health Care has been providing quality healthcare services to the community of Kohat and beyond for years. We bring professional medical care right to your doorstep.</p>
                <div class="hero-buttons">
                  <a href="<?php echo BASE_URL; ?>/pages/services.php" class="btn btn-primary">
                    <span>Our Services</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                  </a>
                  <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="btn btn-outline-primary">Contact Us</a>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-visual">
                <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80" alt="Healthcare Team" class="w-100">
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="about-section section-padding bg-light" id="about">
        <div class="container">
          <div class="section-heading">
            <span class="section-kicker">Our Story</span>
            <h2 class="section-title">Why Choose HHC?</h2>
          </div>
          
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=600&q=80" alt="About Us" class="w-100 rounded-4 shadow">
            </div>
            <div class="col-lg-6">
              <h3 class="mb-4">Compassionate Care You Can Trust</h3>
              <p class="text-muted mb-4">At HHC, we believe that quality healthcare should be accessible to everyone. Our team of dedicated professionals works tirelessly to ensure that you receive the best possible care in the comfort of your own home.</p>
              <ul class="about-list">
                <li class="about-list-item">
                  <i class="bi bi-check-lg"></i>
                  Certified & Experienced Staff
                </li>
                <li class="about-list-item">
                  <i class="bi bi-check-lg"></i>
                  24/7 Emergency Support
                </li>
                <li class="about-list-item">
                  <i class="bi bi-check-lg"></i>
                  Affordable Healthcare Services
                </li>
                <li class="about-list-item">
                  <i class="bi bi-check-lg"></i>
                  Personalized Treatment Plans
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>
    </main>

    <?php include_once BASE_PATH . '/inc/site_footer.php'; ?>
  </div>
</body>
</html>