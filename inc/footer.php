            </div> <!-- .container-fluid -->
        </div> <!-- #content -->
    </div> <!-- .wrapper -->

    <!-- Modal for Dynamic Content (Optional) -->
    <div class="modal fade" id="mainModal" tabindex="-1" aria-labelledby="mainModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    $(document).ready(function() {
        // Sidebar Toggle
        $('#sidebarCollapse, .overlay').on('click', function() {
            $('#sidebar, .overlay').toggleClass('active');
        });

        // Initialize Tooltips & Popovers
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Sticky Navbar on Scroll
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('.navbar').addClass('shadow-sm bg-white');
            } else {
                $('.navbar').removeClass('shadow-sm');
            }
        });

        // Auto-close Alerts after 5 seconds
        setTimeout(function() {
            $('.alert-dismissible').fadeOut('slow');
        }, 5000);

        // Dark Mode Logic
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            // Check state on load
            if (localStorage.getItem('dark-mode') === 'active') {
                darkModeToggle.checked = true;
            }

            darkModeToggle.addEventListener('change', () => {
                if (darkModeToggle.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('dark-mode', 'active');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('dark-mode', 'inactive');
                }
            });
        }

        // Full Screen Toggle Logic
        const fsToggle = document.getElementById('fullScreenToggle');
        if (fsToggle) {
            fsToggle.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.log(`Error attempting to enable full-screen mode: ${err.message}`);
                    });
                    fsToggle.querySelector('span').innerText = 'Exit Full Screen';
                    fsToggle.querySelector('i').className = 'fas fa-compress me-1';
                } else {
                    document.exitFullscreen();
                    fsToggle.querySelector('span').innerText = 'Enter Full Screen';
                    fsToggle.querySelector('i').className = 'fas fa-expand me-1';
                }
            });

            // Handle ESC key or manual exit
            document.addEventListener('fullscreenchange', () => {
                if (!document.fullscreenElement) {
                    fsToggle.querySelector('span').innerText = 'Enter Full Screen';
                    fsToggle.querySelector('i').className = 'fas fa-expand me-1';
                }
            });
        }
    });
    </script>
</body>
</html>
