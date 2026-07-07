<?php
require_once '../config.php';
$title = "Patient Services - HHC";
?>
<?php include_once BASE_PATH . '/inc/site_header.php'; ?>
<body>
  <div class="page-shell">
    <?php include_once BASE_PATH . '/inc/home_menu.php'; ?>

    <main>
      <section class="hero-section" id="patients-hero">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="hero-content">
                <span class="section-kicker">Patient Services</span>
                <h1 class="hero-title">Your Health, Our Priority</h1>
                <p class="hero-text">We provide comprehensive patient services designed to make your healthcare experience as smooth and comfortable as possible.</p>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-visual">
                <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80" alt="Patient Care" class="w-100">
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="doctors-section section-padding" id="patients">
        <div class="container">
          <div class="section-heading">
            <span class="section-kicker">Our Services</span>
            <h2 class="section-title">Patient-Centered Healthcare</h2>
          </div>
          
          <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="doctor-card">
                <div class="position-relative overflow-hidden">
                  <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=400&q=80" 
                       alt="Patient Registration" 
                       class="w-100">
                </div>
                <div class="doctor-card-body">
                  <div class="doctor-card-header">
                    <h3>Patient Registration</h3>
                  </div>
                  <p>Easy and quick registration process for new patients</p>
                </div>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="doctor-card">
                <div class="position-relative overflow-hidden">
                  <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=400&q=80" 
                       alt="Appointments" 
                       class="w-100">
                </div>
                <div class="doctor-card-body">
                  <div class="doctor-card-header">
                    <h3>Online Appointments</h3>
                  </div>
                  <p>Book appointments with your preferred doctor online</p>
                </div>
              </article>
            </div>
            
            <div class="col-12 col-md-6 col-lg-4 fade-in-up">
              <article class="doctor-card">
                <div class="position-relative overflow-hidden">
                  <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=400&q=80" 
                       alt="Medical Records" 
                       class="w-100">
                </div>
                <div class="doctor-card-body">
                  <div class="doctor-card-header">
                    <h3>Medical Records</h3>
                  </div>
                  <p>Secure access to your medical history and records</p>
                </div>
              </article>
            </div>
          </div>

          <div class="text-center mt-5">
            <div class="alert alert-info" role="alert">
              <i class="bi bi-info-circle me-2"></i>
              Patient portal coming soon! For now, please contact us directly.
            </div>
          </div>
        </div>
      </section>
    </main>

    <?php include_once BASE_PATH . '/inc/site_footer.php'; ?>
  </div>
</body>
</html>