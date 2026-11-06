<?php $base = defined('BASE_URL') ? BASE_URL : '../'; ?>

<footer class="sn-footer">
    <div class="container">
        <div class="row gy-4">

            <!-- Logo & About -->
            <div class="col-lg-4 col-md-6">
                <a href="<?= $base ?>" class="sn-footer-logo">
                    <img src="<?= $base ?>includes/uploads/logo.png" alt="SehatNexa" style="height:55px; width:auto; object-fit:contain;">
                </a>
                <p class="sn-footer-desc">
                    Pakistan ka trusted healthcare directory — doctors, hospitals, labs aur blood banks ek jagah. Sehat mein deri nahi honi chahiye.
                </p>
                <div class="sn-social-links">
                    <a href="https://www.facebook.com/sohail.fixit" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.tiktok.com/@sehatnexa.official" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    <a href="https://wa.me/923371320001" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-3 col-6">
                <h5 class="sn-footer-heading">Quick Links</h5>
                <ul class="sn-footer-links list-unstyled">
                    <li><a href="<?= $base ?>">Home</a></li>
                    <li><a href="<?= $base ?>doctors">Doctors</a></li>
                    <li><a href="<?= $base ?>hospitals">Hospitals</a></li>
                    <li><a href="<?= $base ?>labs">Laboratories</a></li>
                    <li><a href="<?= $base ?>blood-banks">Blood Banks</a></li>
                    <li><a href="<?= $base ?>all-specialities">All Specialities</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div class="col-lg-2 col-md-3 col-6">
                <h5 class="sn-footer-heading">Company</h5>
                <ul class="sn-footer-links list-unstyled">
                    <li><a href="<?= $base ?>about">About Us</a></li>
                    <li><a href="<?= $base ?>contact">Contact</a></li>
                    <li><a href="<?= $base ?>about#reviews">Reviews</a></li>
                    <li><a href="<?= $base ?>privacy-policy">Privacy Policy</a></li>
                    <li><a href="<?= $base ?>terms-of-service">Terms of Service</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-4 col-md-6">
                <h5 class="sn-footer-heading">Contact Info</h5>
                <ul class="sn-footer-contact list-unstyled">
                    <li>
                        <span class="sn-contact-icon"><i class="fas fa-map-marker-alt"></i></span>
                        Kohat, Khyber Pakhtunkhwa, Pakistan
                    </li>
                    <li>
                        <span class="sn-contact-icon"><i class="fas fa-phone"></i></span>
                        <a href="tel:+923371320001">+92 337 132-0001</a>
                    </li>
                    <li>
                        <span class="sn-contact-icon"><i class="fas fa-envelope"></i></span>
                        <a href="mailto:sohail.it99@gmail.com">sohail.it99@gmail.com</a>
                    </li>
                    <li>
                        <span class="sn-contact-icon"><i class="fas fa-clock"></i></span>
                        24/7 Available
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="sn-footer-bottom">
            <p class="mb-0">&copy; <?= date('Y') ?> <strong>SehatNexa</strong>. All rights reserved.</p>
            <p class="mb-0">
                <a href="<?= $base ?>privacy-policy">Privacy Policy</a>
                <span class="sn-divider">|</span>
                <a href="<?= $base ?>terms-of-service">Terms of Service</a>
            </p>
        </div>
    </div>
</footer>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/923049744263?text=Hello%2C%20I%20need%20help%20with%20SehatNexa" target="_blank" class="sn-whatsapp-btn" title="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- Back to Top -->
<button id="backToTop" class="sn-back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
/* ── SehatNexa Footer ─────────────────────────────────────── */
.sn-footer {
    background: linear-gradient(135deg, #071124 0%, #0D2B6E 60%, #1C3F8F 100%);
    color: rgba(255,255,255,0.75);
    padding: 70px 0 0;
    margin-top: 80px;
}

.sn-footer-logo {
    display: inline-block;
    margin-bottom: 18px;
    filter: brightness(0) invert(1);  /* logo white ho jaye dark bg pe */
    opacity: 0.92;
    transition: opacity 0.2s;
}
.sn-footer-logo:hover { opacity: 1; }

.sn-footer-desc {
    font-size: 0.92rem;
    line-height: 1.85;
    color: rgba(255,255,255,0.62);
    margin-bottom: 24px;
    max-width: 320px;
}

/* Social */
.sn-social-links { display: flex; gap: 10px; }

.sn-social-links a {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.3s;
}

.sn-social-links a:hover {
    background: linear-gradient(135deg, #1C3F8F, #00A8CC);
    border-color: transparent;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0,168,204,0.35);
}

/* Headings */
.sn-footer-heading {
    color: white;
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 20px;
    padding-bottom: 10px;
    position: relative;
}

.sn-footer-heading::after {
    content: '';
    position: absolute;
    left: 0; bottom: 0;
    width: 28px; height: 2px;
    background: linear-gradient(90deg, #00A8CC, #27C96A);
    border-radius: 2px;
}

/* Links */
.sn-footer-links li { margin-bottom: 10px; }

.sn-footer-links a {
    color: rgba(255,255,255,0.62);
    text-decoration: none;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s;
}

.sn-footer-links a::before {
    content: '\f105';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    font-size: 0.75rem;
    color: #00A8CC;
    flex-shrink: 0;
}

.sn-footer-links a:hover {
    color: white;
    padding-left: 6px;
}

/* Contact */
.sn-footer-contact li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.62);
    margin-bottom: 14px;
    line-height: 1.6;
}

.sn-contact-icon {
    width: 34px; height: 34px;
    border-radius: 10px;
    background: rgba(0,168,204,0.15);
    border: 1px solid rgba(0,168,204,0.2);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #00A8CC;
    font-size: 0.85rem;
    flex-shrink: 0;
    margin-top: 1px;
}

.sn-footer-contact a {
    color: rgba(255,255,255,0.62);
    text-decoration: none;
    transition: color 0.2s;
}
.sn-footer-contact a:hover { color: #00A8CC; }

/* Bottom bar */
.sn-footer-bottom {
    margin-top: 50px;
    padding: 20px 0;
    border-top: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.45);
}

.sn-footer-bottom strong { color: #00A8CC; }

.sn-footer-bottom a {
    color: rgba(255,255,255,0.45);
    text-decoration: none;
    transition: color 0.2s;
}
.sn-footer-bottom a:hover { color: white; }

.sn-divider { margin: 0 10px; opacity: 0.35; }

/* WhatsApp floating */
.sn-whatsapp-btn {
    position: fixed;
    bottom: 90px; right: 28px;
    width: 50px; height: 50px;
    background: #25D366;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(37,211,102,0.45);
    z-index: 1000;
    transition: all 0.3s;
    animation: snPulseWa 2.5s infinite;
}

.sn-whatsapp-btn:hover {
    background: #1ebe5d;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 6px 22px rgba(37,211,102,0.6);
}

@keyframes snPulseWa {
    0%   { box-shadow: 0 0 0 0   rgba(37,211,102,0.7); }
    70%  { box-shadow: 0 0 0 14px rgba(37,211,102,0); }
    100% { box-shadow: 0 0 0 0   rgba(37,211,102,0); }
}

/* Back to top */
.sn-back-to-top {
    position: fixed;
    bottom: 28px; right: 28px;
    width: 46px; height: 46px;
    background: linear-gradient(135deg, #1C3F8F, #00A8CC);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(28,63,143,0.35);
    z-index: 1000;
    opacity: 0; visibility: hidden;
    transform: translateY(14px);
    transition: all 0.3s ease;
}

.sn-back-to-top.show {
    opacity: 1; visibility: visible;
    transform: translateY(0);
}

.sn-back-to-top:hover {
    background: linear-gradient(135deg, #27C96A, #00C9A7);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,201,167,0.4);
}

/* Mobile */
@media (max-width: 768px) {
    .sn-footer { padding: 50px 0 0; }
    .sn-footer-desc { max-width: 100%; }
    .sn-footer-bottom { justify-content: center; text-align: center; }
    .sn-whatsapp-btn { bottom: 76px; right: 18px; width: 45px; height: 45px; font-size: 1.4rem; }
    .sn-back-to-top  { bottom: 18px; right: 18px; width: 42px; height: 42px;
                        opacity: 1 !important; visibility: visible !important; transform: translateY(0) !important; }
}

@media (max-width: 480px) {
    .sn-whatsapp-btn { bottom: 68px; right: 12px; width: 40px; height: 40px; font-size: 1.3rem; }
    .sn-back-to-top  { bottom: 12px; right: 12px; width: 38px; height: 38px; }
}
</style>

<!-- JS -->
<script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/Jquery/dist/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>admin/inc/assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Mobile: replace horizontal AOS with fade-up
    if (window.matchMedia('(max-width: 991px)').matches) {
        document.querySelectorAll('[data-aos="fade-left"], [data-aos="fade-right"]').forEach(function(el) {
            el.setAttribute('data-aos', 'fade-up');
        });
    }
    AOS.init({ duration: 1000, once: true });

    // Back to top show/hide
    window.addEventListener('scroll', function() {
        var btn = document.getElementById('backToTop');
        if (btn) btn.classList.toggle('show', window.pageYOffset > 300);
    });
</script>
</body>
</html>
