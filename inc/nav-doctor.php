<!-- Sidebar -->
<nav id="sidebar" class="d-print-none">
    <div class="sidebar-header border-bottom py-4 px-3 d-flex align-items-center">
        <div class="rounded-3 p-2 bg-primary text-white me-2">
            <i class="fas fa-hospital-user fa-lg"></i>
        </div>
        <h4 class="mb-0 fw-bold text-primary">HHC</h4>
    </div>

    <ul class="list-unstyled components py-3">


            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/index.php"
                   class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>Doctor Dashboard
                </a>
            </li>

            <p class="small text-muted mb-0 fw-bold px-4 pt-3 pb-2 text-uppercase">
                User Management
            </p>

            <!-- <li class="<?php 
                    $active_pages = ['doctor-add.php', 'doctor-list.php', 'doctor-edit.php'];
                    $is_clients_active = in_array(basename($_SERVER['PHP_SELF']), $active_pages);
                    echo $is_clients_active ? 'active' : ''; 
                ?>">
                <?php
                    $current_page = basename($_SERVER['PHP_SELF']);
                ?>

                <a href="#clientsSubmenu"
                   data-bs-toggle="collapse"
                   aria-expanded="<?php echo $is_clients_active ? 'true' : 'false'; ?>"
                   class="dropdown-toggle <?php echo $is_clients_active ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Doctors
                </a>

                <ul class="collapse list-unstyled ps-3 <?php echo $is_clients_active ? 'show' : ''; ?>"
                    id="clientsSubmenu">

                    <li class="<?php echo $current_page == 'doctor-add.php' ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>/admin/doctor-add.php"
                           class="small py-2 <?php echo $current_page == 'doctor-add.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-tag me-2"></i> Add
                        </a>
                    </li>

                    <li class="<?php echo $current_page == 'doctor-list.php' || $current_page == 'doctor-edit.php' ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>/admin/doctor-list.php"
                           class="small py-2 <?php echo $current_page == 'doctor-list.php' || $current_page == 'doctor-edit.php' ? 'active' : ''; ?>">
                            <i class="fas fa-list-ul me-2"></i> List
                        </a>
                    </li>

                </ul>
            </li> -->

             <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/products.php"
                   class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog me-2"></i> Test Doctor Menu
                </a>
            </li> 


    </ul>

    <div class="sidebar-footer px-3 py-4 mt-auto border-top text-center text-muted small">
        <div class="form-check form-switch mb-2 d-flex justify-content-center align-items-center gap-2 border p-2 rounded-pill bg-light">
            <input class="form-check-input" type="checkbox" id="darkModeToggle">
            <label class="form-check-label fw-bold text-dark" for="darkModeToggle">
                <i class="fas fa-moon me-1"></i> Dark Mode
            </label>
        </div>

        <button id="fullScreenToggle"
                class="btn btn-sm btn-outline-secondary w-100 rounded-pill mb-3 py-2 border-dashed">
            <i class="fas fa-expand me-1"></i>
            <span>Enter Full Screen</span>
        </button>

        <p class="mb-0">© 2026 HHSC v1.0</p>
    </div>
</nav>
