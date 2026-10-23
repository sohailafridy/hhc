<?php include '../includes/header.php'; ?>
<?php include BASE_PATH.'/includes/legal-config.php'; ?>
<?php
$page_title = 'Privacy Policy';

// Sidebar (table of contents) - id => title
$toc = [
    'introduction'   => 'Introduction',
    'information'    => 'Information We Collect',
    'use'            => 'How We Use Your Information',
    'public'         => 'Information Shown Publicly',
    'sharing'        => 'Sharing of Information',
    'cookies'        => 'Cookies & Similar Technologies',
    'security'       => 'Data Security',
    'retention'      => 'Data Retention',
    'rights'         => 'Your Rights & Choices',
    'third-party'    => 'Third-Party Links',
    'children'       => 'Children\'s Privacy',
    'changes'        => 'Changes to This Policy',
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
            <div class="legal-hero-icon"><i class="fas fa-user-shield"></i></div>
            <h1>Privacy Policy</h1>
            <p>Your privacy matters to us. This page explains what information we collect, why we collect it and how we protect it.</p>
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
                        <a href="terms-of-service"><i class="fas fa-file-contract"></i> Terms of Service</a>
                    </div>
                </aside>

                <!-- Policy text -->
                <article class="legal-content">

                    <section class="legal-section" id="introduction">
                        <h2>Introduction</h2>
                        <p>Welcome to <strong><?php echo htmlspecialchars($site_name); ?></strong> ("we", "us" or "our"). We run an online directory that helps people find doctors, hospitals and clinics, read ratings and reviews, and check timings and contact details.</p>
                        <p>This Privacy Policy explains what personal information we collect when you visit our website or use an account on it, how we use that information, and the choices you have. By using our website you agree to the practices described here. If you do not agree, please do not use the website.</p>
                        <div class="legal-note">
                            <strong>Important:</strong> We do not ask for, collect or store patients' medical records, test reports or treatment details through this website. Please do not post such information in reviews or messages.
                        </div>
                    </section>

                    <section class="legal-section" id="information">
                        <h2>Information We Collect</h2>
                        <p>The information we collect depends on how you use the website.</p>

                        <div class="legal-table-wrap">
                            <table class="legal-table">
                                <thead>
                                    <tr>
                                        <th>Who</th>
                                        <th>What we may collect</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Visitors</td>
                                        <td>Search terms and filters you use (doctor name, city, hospital, specialization), pages visited, IP address, browser and device type, and cookies. You do not need an account to search the directory.</td>
                                    </tr>
                                    <tr>
                                        <td>Reviewers</td>
                                        <td>Your name, email address, star rating and the comment you write when you leave feedback about a doctor or hospital.</td>
                                    </tr>
                                    <tr>
                                        <td>Doctors</td>
                                        <td>Name, photo, gender, email, phone number, PMDC registration number, CNIC (for verification), specialization, qualifications, experience, city, the hospitals or clinics you work at, clinic timings, and your account username and password.</td>
                                    </tr>
                                    <tr>
                                        <td>Hospitals</td>
                                        <td>Hospital name, address, phone number, city, photo, bed and facility information, the doctors associated with the hospital, and your account username and password.</td>
                                    </tr>
                                    <tr>
                                        <td>People who contact us</td>
                                        <td>Your name, email address or phone number, and the message you send us.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <p>We collect this information when you type it into a form, create or update an account, leave a review, contact us, or simply browse the website (through cookies and server logs).</p>
                    </section>

                    <section class="legal-section" id="use">
                        <h2>How We Use Your Information</h2>
                        <p>We use the information we collect to:</p>
                        <ul>
                            <li>Run the directory and show doctor, hospital and clinic listings to visitors.</li>
                            <li>Verify doctors and hospitals before their profiles are approved and published.</li>
                            <li>Create and manage doctor, hospital and administrator accounts.</li>
                            <li>Display average ratings and reviews, and moderate them for abuse or fake content.</li>
                            <li>Improve the website, understand how it is used and fix technical problems.</li>
                            <li>Prevent fraud, spam and misuse, and keep the website secure.</li>
                            <li>Reply to your questions, requests and complaints.</li>
                            <li>Comply with legal obligations and enforce our <a href="terms-of-service">Terms of Service</a>.</li>
                        </ul>
                    </section>

                    <section class="legal-section" id="public">
                        <h2>Information Shown Publicly</h2>
                        <p>The purpose of the directory is to make certain information public. A doctor's public profile may show their name, photo, specialization, qualifications, experience, city, associated hospitals or clinics, timings, the contact details provided for the listing, and their average rating and reviews. A hospital's public page may show its name, address, phone number, facilities and the doctors who practise there.</p>
                        <p>Reviews are public and show the reviewer's name and comment. Sensitive identifiers such as CNIC numbers and account passwords are <strong>never</strong> displayed publicly.</p>
                        <div class="legal-note warning">
                            Anything you choose to publish on a public profile or in a review can be seen, copied and shared by anyone. Please share only what you are comfortable making public.
                        </div>
                    </section>

                    <section class="legal-section" id="sharing">
                        <h2>Sharing of Information</h2>
                        <p>We do <strong>not</strong> sell your personal information. We may share it only in these situations:</p>
                        <ul>
                            <li><strong>Service providers:</strong> companies that help us run the website, such as hosting, analytics, email or SMS providers, who may use the data only to provide their service to us.</li>
                            <li><strong>Legal reasons:</strong> when required by law, court order or a lawful request from a government authority.</li>
                            <li><strong>Protection:</strong> to protect the rights, safety and property of our users, the public or ourselves, and to investigate fraud or abuse.</li>
                            <li><strong>Business changes:</strong> if the website is merged with or transferred to another organisation, your information may be transferred as part of that process.</li>
                            <li><strong>With your consent:</strong> in any other case where you have clearly agreed.</li>
                        </ul>
                    </section>

                    <section class="legal-section" id="cookies">
                        <h2>Cookies &amp; Similar Technologies</h2>
                        <p>Cookies are small files stored on your device. We use them to keep doctors, hospitals and administrators logged in during a session, to remember basic preferences such as your selected city, and to understand how visitors use the website.</p>
                        <p>You can block or delete cookies from your browser settings. Some parts of the website, such as logging in to your account, may not work properly without them.</p>
                    </section>

                    <section class="legal-section" id="security">
                        <h2>Data Security</h2>
                        <p>We take reasonable technical and organisational steps to protect your information from loss, misuse and unauthorised access, including limiting administrator access and requiring a login for account areas.</p>
                        <p>However, no website or internet transmission is completely secure, so we cannot guarantee absolute security. You are responsible for keeping your account password confidential and for logging out on shared devices. If you think your account has been compromised, please contact us immediately.</p>
                    </section>

                    <section class="legal-section" id="retention">
                        <h2>Data Retention</h2>
                        <p>We keep your information for as long as your account or listing is active and as long as needed for the purposes in this policy, to resolve disputes and to meet legal requirements.</p>
                        <p>When a doctor is removed from a hospital, or a profile is deactivated, we may keep the record in an inactive state for history and auditing purposes. You may ask us to permanently delete your data at any time (see "Your Rights &amp; Choices" below), and we will do so unless we are legally required to keep it.</p>
                    </section>

                    <section class="legal-section" id="rights">
                        <h2>Your Rights &amp; Choices</h2>
                        <p>You can ask us to:</p>
                        <ul>
                            <li>Tell you what personal information we hold about you.</li>
                            <li>Correct information that is inaccurate or out of date.</li>
                            <li>Delete your account, profile or review and the personal data connected to it.</li>
                            <li>Stop using your information for a particular purpose, or withdraw a consent you gave earlier.</li>
                        </ul>
                        <p>To make a request, contact us using the details below. We may need to verify your identity first, and we will try to respond within 30 days.</p>
                    </section>

                    <section class="legal-section" id="third-party">
                        <h2>Third-Party Links</h2>
                        <p>Our website may link to or embed content from other websites, such as maps, social media pages or hospital websites. We do not control these sites and are not responsible for their privacy practices. Please read their policies before sharing information with them.</p>
                    </section>

                    <section class="legal-section" id="children">
                        <h2>Children's Privacy</h2>
                        <p>Our website is intended for general audiences and accounts are meant for adults. We do not knowingly collect personal information from children under 18. If you believe a child has given us personal information, please contact us and we will delete it.</p>
                    </section>

                    <section class="legal-section" id="changes">
                        <h2>Changes to This Policy</h2>
                        <p>We may update this Privacy Policy from time to time. When we do, we will change the "Last updated" date at the top of this page. If the changes are significant, we will try to give a more prominent notice. Please review this page regularly. Continued use of the website after a change means you accept the updated policy.</p>
                    </section>

                    <section class="legal-section" id="contact">
                        <h2>Contact Us</h2>
                        <p>If you have any questions about this Privacy Policy or want to exercise your rights, please contact us:</p>
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