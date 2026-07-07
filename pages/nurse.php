<?php
require_once '../config.php';
$title = "Our Nurses - HHC";
?>
<?php include_once BASE_PATH . '/inc/site_header.php'; ?>
<body>
  <div class="page-shell">
    <?php include_once BASE_PATH . '/inc/home_menu.php'; ?>





    <main>
      <section class="hero-section" id="nurse-hero">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="hero-content">
                <span class="section-kicker">Our Nursing Team</span>
                <h1 class="hero-title">Compassionate & Skilled Nurses</h1>
                <p class="hero-text">Our nursing team is dedicated to providing the highest quality of care with compassion and professionalism.</p>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-visual">
                <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80" alt="Our Nurses" class="w-100">
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="doctors-section section-padding" id="nurses">
        <div class="container">
          <div class="section-heading">
            <span class="section-kicker">Our Nurses</span>
            <h2 class="section-title">Meet Our Nursing Team</h2>
          </div>
          
          <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="doctor-card">
                <div class="position-relative overflow-hidden">
                  <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80" 
                       alt="Nurse 1" 
                       class="w-100">
                </div>
                <div class="doctor-card-body">
                  <div class="doctor-card-header">
                    <h3>Sarah Johnson</h3>
                    <span class="badge doctor-badge">
                      <i class="bi bi-person-check-fill"></i> Certified
                    </span>
                  </div>
                  <p>Registered Nurse - ICU</p>
                </div>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="doctor-card">
                <div class="position-relative overflow-hidden">
                  <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=400&q=80" 
                       alt="Nurse 2" 
                       class="w-100">
                </div>
                <div class="doctor-card-body">
                  <div class="doctor-card-header">
                    <h3>Maria Garcia</h3>
                    <span class="badge doctor-badge">
                      <i class="bi bi-person-check-fill"></i> Certified
                    </span>
                  </div>
                  <p>Pediatric Nurse Specialist</p>
                </div>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="doctor-card">
                <div class="position-relative overflow-hidden">
                  <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=400&q=80" 
                       alt="Nurse 3" 
                       class="w-100">
                </div>
                <div class="doctor-card-body">
                  <div class="doctor-card-header">
                    <h3>Ahmed Khan</h3>
                    <span class="badge doctor-badge">
                      <i class="bi bi-person-check-fill"></i> Certified
                    </span>
                  </div>
                  <p>Emergency Room Nurse</p>
                </div>
              </article>
            </div>
          </div>

          <div class="text-center mt-5">
            <div class="alert alert-info" role="alert">
              <i class="bi bi-info-circle me-2"></i>
              Full nurse management system coming soon!
            </div>
          </div>
        </div>
      </section>
    </main>

    <?php include_once BASE_PATH . '/inc/site_footer.php'; ?>
  </div>
</body>
</html>