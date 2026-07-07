<?php
require_once '../config.php';
$title = "Our Services - HHC";
?>
<?php include_once BASE_PATH . '/inc/site_header.php'; ?>
<body>
  <div class="page-shell">
    <?php include_once BASE_PATH . '/inc/home_menu.php'; ?>

    <main>
      <section class="hero-section" id="services-hero">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="hero-content">
                <span class="section-kicker">What We Offer</span>
                <h1 class="hero-title">Comprehensive Healthcare Services</h1>
                <p class="hero-text">We provide a wide range of healthcare services tailored to meet your specific needs. From primary care to specialized treatments, we've got you covered.</p>
                <div class="hero-buttons">
                  <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="btn btn-primary">
                    <span>Book Now</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-visual">
                <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80" alt="Our Services" class="w-100">
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="services-section section-padding" id="services">
        <div class="container">
          <div class="section-heading">
            <span class="section-kicker">Our Services</span>
            <h2 class="section-title">Healthcare Services We Provide</h2>
          </div>
          
          <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="service-card service-card-blue">
                <div class="service-icon">
                  <i class="bi bi-heart-pulse"></i>
                </div>
                <h3>Primary Health Care</h3>
                <p>Comprehensive primary care services for all your health needs.</p>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="service-card service-card-yellow">
                <div class="service-icon">
                  <i class="bi bi-ear"></i>
                </div>
                <h3>Laryngological Clinic</h3>
                <p>Specialized care for ear, nose, and throat conditions.</p>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="service-card service-card-cyan">
                <div class="service-icon">
                  <i class="bi bi-emoji-smile"></i>
                </div>
                <h3>Pediatric Clinic</h3>
                <p>Gentle and compassionate care for your little ones.</p>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="service-card service-card-blue">
                <div class="service-icon">
                  <i class="bi bi-eye"></i>
                </div>
                <h3>Ophthalmology Clinic</h3>
                <p>Complete eye care and vision correction services.</p>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="service-card service-card-yellow">
                <div class="service-icon">
                  <i class="bi bi-hospital"></i>
                </div>
                <h3>Outpatient Surgery</h3>
                <p>Safe and convenient outpatient surgical procedures.</p>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="service-card service-card-cyan">
                <div class="service-icon">
                  <i class="bi bi-teeth"></i>
                </div>
                <h3>Dental Clinic</h3>
                <p>Comprehensive dental care for healthy smiles.</p>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="service-card service-card-blue">
                <div class="service-icon">
                  <i class="bi bi-people"></i>
                </div>
                <h3>Gynaecological Clinic</h3>
                <p>Specialized women's health and wellness services.</p>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="service-card service-card-yellow">
                <div class="service-icon">
                  <i class="bi bi-house-heart"></i>
                </div>
                <h3>Home Health Care</h3>
                <p>Healthcare services delivered in the comfort of your home.</p>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="service-card service-card-cyan">
                <div class="service-icon">
                  <i class="bi bi-activity"></i>
                </div>
                <h3>Physiotherapy</h3>
                <p>Rehabilitation and physical therapy services.</p>
              </article>
            </div>
          </div>
        </div>
      </section>
    </main>

    <?php include_once BASE_PATH . '/inc/site_footer.php'; ?>
  </div>
</body>
</html>