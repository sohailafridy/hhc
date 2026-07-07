<header class="site-header">
      <nav class="navbar navbar-expand-lg">
        <div class="container">
          <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>">
            <div class="brand-mark">HHC</div>
            <img src="<?php echo BASE_URL; ?>/assets/img/logo.jpeg" alt="Hhour Logo" class="logo-img" style="height: 90px; width: auto; border-radius: 12px;">
          </a>
          <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto gap-lg-3">
              <?php 
                $current_page = basename($_SERVER['PHP_SELF']);
                $is_home = ($current_page === 'index.php');
              ?>
              <li class="nav-item"><a class="nav-link <?php echo $is_home ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>">Home</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'services.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/services.php">Services</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'about.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/about.php">About</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'doctors.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/doctors.php">Doctors</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'our_team.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/our_team.php">Our Team</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'contact.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/pages/contact.php">Contact2</a></li>
            </ul>
            <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-primary"><i class="bi bi-person"></i>+</a>
          </div>
        </div>
      </nav>
    </header>