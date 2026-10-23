<?php include '../includes/header.php'; ?>
<?php include BASE_PATH.'/includes/legal-config.php'; ?>
<?php
$page_title = 'Terms of Service';

// Sidebar (table of contents) - id => title
$toc = [
    'acceptance'     => 'Acceptance of Terms',
    'platform'       => 'About Our Platform',
    'medical'        => 'Medical Disclaimer',
    'eligibility'    => 'Eligibility',
    'accounts'       => 'Doctor & Hospital Accounts',
    'listings'       => 'Listings & Content',
    'reviews'        => 'Reviews & Feedback',
    'acceptable-use' => 'Acceptable Use',
    'ip'             => 'Intellectual Property',
    'third-party'    => 'Third-Party Links',
    'warranty'       => 'Disclaimer of Warranties',
    'liability'      => 'Limitation of Liability',
    'indemnity'      => 'Indemnification',
    'termination'    => 'Suspension & Termination',
    'changes'        => 'Changes to These Terms',
    'law'            => 'Governing Law',
    'contact'        => 'Contact Us',
];
?>
<link rel="stylesheet" href="<?= BASE_URL ?>style/legal-pages.css">
<script>document.title = <?php echo json_encode($page_title . ' | ' . $site_name); ?>;</script>
<body>

    <!-- Navbar -->
    <?php include BASE_PATH.'/includes/menu.php'; ?>

    <!-- Hero -->
    <section class="legal-hero">
        <div class="container position-relative">
            <div class="legal-hero-icon"><i class="fas fa-file-contract"></i></div>
            <h1>Terms of Service</h1>
            <p>Please read these terms carefully before using our website. They explain the rules for patients, doctors and hospitals who use the platform.</p>
            <span class="legal-updated"><i class="fas fa-calendar-check"></i> Last updated: <?php echo htmlspecialchars($last_updated); ?></span>
        </div>
    </section>

    <!-- Content -->
    <section class="legal-wrap">
        <div class="container">
            <div class="legal-layout">

                <!-- Table of contents -->
                <aside class="legal-toc" id="legalToc">
                    <h6><i class="fas fa-list-ul me-1"></i> On this page</h6>
                    <ol>
                        <?php foreach ($toc as $id => $label): ?>
                            <li><a href="#<?php echo $id; ?>"><?php echo htmlspecialchars($label); ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                    <div class="legal-toc-links">
                        <a href="privacy-policy"><i class="fas fa-user-shield"></i> Privacy Policy</a>
                    </div>
                </aside>

                <!-- Terms text -->
                <article class="legal-content">

                    <section class="legal-section" id="acceptance">
                        <h2>Acceptance of Terms</h2>
                        <p>These Terms of Service ("Terms") are an agreement between you and <strong><?php echo htmlspecialchars($site_name); ?></strong> ("we", "us" or "our"). By visiting, browsing or using our website, or by creating an account on it, you confirm that you have read, understood and agreed to these Terms and to our <a href="privacy-policy">Privacy Policy</a>.</p>
                        <p>If you do not agree with any part of these Terms, you must stop using the website.</p>
                    </section>

                    <section class="legal-section" id="platform">
                        <h2>About Our Platform</h2>
                        <p>We provide an online directory where people can search for doctors, hospitals and clinics, view their details and timings, and read or leave ratings and reviews. Doctors and hospitals can create accounts to manage their own listings.</p>
                        <p>We are an information service only. We are <strong>not</strong> a hospital, clinic or healthcare provider, and we do not employ the doctors listed. Any appointment, consultation, treatment or payment is strictly between you and the doctor or hospital.</p>
                    </section>

                    <section class="legal-section" id="medical">
                        <h2>Medical Disclaimer</h2>
                        <div class="legal-note danger">
                            <strong>In an emergency, do not use this website.</strong> Call your local emergency number (for example Rescue 1122 where available) or go to the nearest hospital immediately.
                        </div>
                        <p>Nothing on this website is medical advice, diagnosis or treatment. The information on listings, including specializations, qualifications and timings, is provided for general information only. Always seek the advice of a qualified doctor about any medical condition, and never ignore or delay professional advice because of something you read here.</p>
                        <p>The appearance of a doctor or hospital on our website is not an endorsement or a guarantee of their skill, quality or availability.</p>
                    </section>

                    <section class="legal-section" id="eligibility">
                        <h2>Eligibility</h2>
                        <p>You must be at least 18 years old, or have the permission of a parent or legal guardian, to use this website. Doctor and hospital accounts may be created only by the doctor or by an authorised representative of the hospital or clinic concerned.</p>
                    </section>

                    <section class="legal-section" id="accounts">
                        <h2>Doctor &amp; Hospital Accounts</h2>
                        <p>If you register as a doctor or hospital, you agree that:</p>
                        <ul>
                            <li>All information you give us, including your name, PMDC registration number, qualifications, CNIC, contact details and hospital information, is true, accurate and up to date.</li>
                            <li>You will not impersonate another person or claim qualifications, registrations or affiliations that you do not have.</li>
                            <li>Your registration is subject to review and approval by our team, and we may ask for documents to verify your identity or credentials. We may refuse or remove any registration at our discretion.</li>
                            <li>You are responsible for keeping your login details secret and for all activity under your account. Tell us immediately if you suspect unauthorised use.</li>
                            <li>You will keep your timings, contact details and other listing information current.</li>
                        </ul>
                    </section>

                    <section class="legal-section" id="listings">
                        <h2>Listings &amp; Content</h2>
                        <p>Doctors and hospitals are responsible for the content they provide. You confirm that you have the right to publish it and that it does not infringe anyone's rights or break any law.</p>
                        <p>By submitting content (such as text, photos or logos), you give us a free, non-exclusive, worldwide licence to host, display and use it on the website and in connection with running and promoting the directory.</p>
                        <p>We try to keep the directory accurate, but information such as timings, fees, availability and hospital affiliations can change without notice. We do not guarantee that any listing is complete, current or error-free, and you should confirm important details directly with the doctor or hospital.</p>
                    </section>

                    <section class="legal-section" id="reviews">
                        <h2>Reviews &amp; Feedback</h2>
                        <p>Reviews are opinions of individual users, not of us. If you leave a review, you agree that:</p>
                        <ul>
                            <li>It is based on your own genuine experience and is honest and fair.</li>
                            <li>It does not contain abusive, hateful, defamatory or discriminatory language, or personal details of other people.</li>
                            <li>It does not contain private medical information about you or anyone else.</li>
                            <li>You have not been paid or pressured to write it, and you are not writing it about yourself or a competitor.</li>
                        </ul>
                        <p>Doctors and hospitals must not post fake reviews, offer rewards for reviews, or pressure patients to change or remove them. We may edit, hide or delete any review, and may suspend accounts involved in review manipulation, at our discretion.</p>
                    </section>

                    <section class="legal-section" id="acceptable-use">
                        <h2>Acceptable Use</h2>
                        <p>You agree not to:</p>
                        <ul>
                            <li>Use the website for any unlawful, harmful, fraudulent or misleading purpose.</li>
                            <li>Copy, scrape, harvest or collect listings or contact details in bulk, or use them for spam or unsolicited marketing.</li>
                            <li>Upload viruses or malicious code, or try to disrupt, overload or damage the website.</li>
                            <li>Try to gain unauthorised access to accounts, the admin area, databases or other systems.</li>
                            <li>Interfere with the security features of the website or bypass any restriction we have put in place.</li>
                            <li>Use the website in a way that harms other users, doctors, hospitals or our reputation.</li>
                        </ul>
                    </section>

                    <section class="legal-section" id="ip">
                        <h2>Intellectual Property</h2>
                        <p>The website, including its design, layout, logo, text, graphics and software, belongs to us or our licensors and is protected by applicable intellectual property laws. You may view and use it for your personal, non-commercial purposes only. You may not copy, modify, distribute, sell or create derivative works from it without our written permission.</p>
                        <p>Content that doctors, hospitals and reviewers submit remains theirs, subject to the licence they give us under these Terms.</p>
                    </section>

                    <section class="legal-section" id="third-party">
                        <h2>Third-Party Links</h2>
                        <p>The website may contain links to websites, maps or services run by others. They are provided for convenience only. We do not control them and are not responsible for their content, accuracy or practices. Using them is at your own risk.</p>
                    </section>

                    <section class="legal-section" id="warranty">
                        <h2>Disclaimer of Warranties</h2>
                        <p>The website and everything on it are provided "as is" and "as available", without warranties of any kind, whether express or implied, to the fullest extent permitted by law. We do not promise that the website will always be available, uninterrupted, secure or free of errors, or that any listing, rating or review is accurate, complete or reliable.</p>
                    </section>

                    <section class="legal-section" id="liability">
                        <h2>Limitation of Liability</h2>
                        <p>To the fullest extent permitted by law, we and our team, owners and partners will not be liable for any indirect, incidental, special or consequential loss or damage, or for any loss of data, profit or reputation, arising from your use of, or inability to use, the website.</p>
                        <p>In particular, we are not responsible for the advice, treatment, conduct, fees or availability of any doctor or hospital listed on the website, nor for any loss or harm that results from relying on information in the directory. Nothing in these Terms limits any liability that cannot lawfully be limited.</p>
                    </section>

                    <section class="legal-section" id="indemnity">
                        <h2>Indemnification</h2>
                        <p>You agree to defend and compensate us against any claims, losses and expenses (including reasonable legal fees) that arise from your content, your breach of these Terms, or your misuse of the website.</p>
                    </section>

                    <section class="legal-section" id="termination">
                        <h2>Suspension &amp; Termination</h2>
                        <p>We may suspend, deactivate or permanently remove any account, listing or review at any time, with or without notice, if we believe these Terms have been broken, information is false or misleading, or it is necessary to protect users or the website.</p>
                        <p>You may stop using the website at any time and may ask us to delete your account or listing. Sections that by their nature should continue (such as intellectual property, disclaimers, limitation of liability and governing law) will remain in effect after termination.</p>
                    </section>

                    <section class="legal-section" id="changes">
                        <h2>Changes to These Terms</h2>
                        <p>We may update these Terms from time to time. We will change the "Last updated" date at the top of this page when we do. Your continued use of the website after the changes take effect means you accept the updated Terms.</p>
                    </section>

                    <section class="legal-section" id="law">
                        <h2>Governing Law</h2>
                        <p>These Terms are governed by the laws of Pakistan. Any dispute that arises from or relates to these Terms or the website will be subject to the jurisdiction of the courts of <?php echo htmlspecialchars($court_city); ?>, Pakistan.</p>
                    </section>

                    <section class="legal-section" id="contact">
                        <h2>Contact Us</h2>
                        <p>If you have any questions about these Terms, please contact us:</p>
                        <div class="legal-contact">
                            <div class="legal-contact-item">
                                <i class="fas fa-envelope"></i>
                                <div><small>Email</small><span><?php echo htmlspecialchars($contact_email); ?></span></div>
                            </div>
                            <div class="legal-contact-item">
                                <i class="fas fa-phone"></i>
                                <div><small>Phone</small><span><?php echo htmlspecialchars($contact_phone); ?></span></div>
                            </div>
                            <div class="legal-contact-item">
                                <i class="fas fa-location-dot"></i>
                                <div><small>Address</small><span><?php echo htmlspecialchars($contact_address); ?></span></div>
                            </div>
                        </div>
                    </section>

                </article>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include BASE_PATH.'/includes/footer.php'; ?>

    <script>
    // Sidebar: jis section par ho wo highlight ho
    (function () {
        var links = document.querySelectorAll('#legalToc ol a');
        var sections = [];
        links.forEach(function (a) {
            var el = document.getElementById(a.getAttribute('href').slice(1));
            if (el) { sections.push({ link: a, el: el }); }
        });
        if (!('IntersectionObserver' in window) || !sections.length) { return; }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    links.forEach(function (l) { l.classList.remove('active'); });
                    sections.forEach(function (s) {
                        if (s.el === entry.target) { s.link.classList.add('active'); }
                    });
                }
            });
        }, { rootMargin: '-20% 0px -65% 0px' });

        sections.forEach(function (s) { observer.observe(s.el); });
    })();
    </script>
</body>