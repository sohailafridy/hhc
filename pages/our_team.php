<?php
require_once '../config.php';
$title = "Our Team - HHC";

$doctors = [];
try {
  $stmt = $pdo->prepare("SELECT d.*, u.username, u.email 
                       FROM doctors d 
                       LEFT JOIN users u ON d.user_id = u.id 
                       WHERE u.status = 1 
                       ORDER BY d.created_at DESC");
  $stmt->execute();
  $doctors = $stmt->fetchAll();
} catch (Exception $e) {
  $doctors = [];
}
?>
<?php include_once BASE_PATH . '/inc/site_header.php'; ?>
<body>
  <div class="page-shell">
    <?php include_once BASE_PATH . '/inc/home_menu.php'; ?>

    <main>
      <section class="hero-section" id="team-hero">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-6">
              <div class="hero-content">
                <span class="section-kicker">Meet Our Team</span>
                <h1 class="hero-title">Dedicated Healthcare Professionals</h1>
                <p class="hero-text">Our team consists of highly qualified and experienced medical professionals committed to providing exceptional healthcare services.</p>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-visual">
                <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80" alt="Our Team" class="w-100">
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="doctors-section section-padding" id="team">
        <div class="container">
          <div class="section-heading">
            <span class="section-kicker">Our Doctors</span>
            <h2 class="section-title">Meet Our Medical Experts</h2>
          </div>
          
          <div class="row g-4">
            <?php if (count($doctors) > 0): ?>
              <?php foreach ($doctors as $doctor): ?>
                <div class="col-12 col-md-6 col-lg-4 fade-in-up">
                  <article class="doctor-card">
                    <div class="position-relative overflow-hidden">
                      <?php if (!empty($doctor['img'])): ?>
                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($doctor['img']); ?>" 
                             alt="<?php echo htmlspecialchars($doctor['name']); ?>" 
                             class="w-100">
                      <?php else: ?>
                        <img src="<?php echo BASE_URL . '/assets/img/dr-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($doctor['name']); ?>" 
                             class="w-100">
                      <?php endif; ?>
                    </div>
                    <div class="doctor-card-body">
                      <div class="doctor-card-header">
                        <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                        <span class="badge doctor-badge">
                          <i class="bi bi-person-check-fill"></i> Verified
                        </span>
                      </div>
                      <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    </div>
                  </article>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12 text-center py-5">
                <div class="text-muted">
                  <i class="bi bi-person-x fs-1 mb-3"></i>
                  <h4>Team Members Coming Soon!</h4>
                  <p>Check back soon for updates on our medical team!</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </main>

    <?php include_once BASE_PATH . '/inc/site_footer.php'; ?>
  </div>
</body>
</html>