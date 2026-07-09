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
                // $current_page = basename($_SERVER['PHP_SELF']);
                // echo $current_page;exit;

                $slug = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
                $current_page = basename($slug);


                $is_home = ($current_page === 'hhc' || $current_page =='home' || $current_page =='');
              ?>
              <li class="nav-item"><a class="nav-link <?php echo $is_home ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>">Home</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'services') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/services">Services</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'about-us') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/about-us">About</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'doctors') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/doctors">Doctors</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'our-team') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/our-team">Our Team</a></li>
              <li class="nav-item"><a class="nav-link <?php echo ($current_page === 'contact') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/contact">Contact</a></li>
            </ul>
            <a href="<?php echo BASE_URL; ?>/login" class="btn btn-primary"><i class="bi bi-person"></i>+</a>
          </div>
        </div>
      </nav>
    </header>