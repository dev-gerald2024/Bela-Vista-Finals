<?php
/**
 * Bella Vista Oasis Spa - Global Footer Template
 * 
 * Provides luxury brand footer, concierge contact info, operating hours,
 * social links, dynamic toast container, and global JS scripts.
 */
?>
</main>

<!-- Footer -->
<footer class="luxury-footer pt-5 mt-auto border-top border-gold-subtle">
    <div class="container-fluid px-lg-5">
        <div class="row g-4 pb-4">
            <!-- Brand & Philosophy -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="brand-crest me-2">
                        <i class="fa-solid fa-spa text-gold fs-4"></i>
                    </div>
                    <span class="font-playfair text-gold fs-4 fw-bold">BELLA VISTA</span>
                </div>
                <p class="text-silver small pe-lg-4 leading-relaxed font-inter">
                    The pinnacle of holistic serenity and restorative luxury in Tagaytay. 
                    Immerse in bespoke botanical therapies, master clinical care, and 
                    transformative calm curated within an exclusive sanctuary.
                </p>
                <div class="d-flex gap-3 mt-4">
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="social-btn text-gold" aria-label="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="social-btn text-gold" aria-label="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="https://tripadvisor.com" target="_blank" rel="noopener noreferrer" class="social-btn text-gold" aria-label="TripAdvisor">
                        <i class="fa-brands fa-tripadvisor"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="social-btn text-gold" aria-label="YouTube">
                        <i class="fa-brands fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Sanctuary Hours & Health Policy -->
            <div class="col-lg-3 col-md-6 font-inter">
                <h5 class="font-playfair text-gold mb-3 text-uppercase tracking-wider small fw-bold">Operating Sanctuary</h5>
                <ul class="list-unstyled text-silver small mb-0">
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fa-regular fa-clock text-gold me-2"></i>
                        <span><strong>Daily Hours:</strong> 1:00 PM – 9:00 PM</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fa-solid fa-sparkles text-gold me-2"></i>
                        <span><strong>Sanitization Gap:</strong> 30-min buffer</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fa-solid fa-person-walking-luggage text-gold me-2"></i>
                        <span><strong>Arrival:</strong> 15 mins prior</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fa-solid fa-shield-heart text-gold me-2"></i>
                        <span><strong>Health Safety:</strong> Form required</span>
                    </li>
                </ul>
            </div>

            <!-- Curated Navigation -->
            <div class="col-lg-2 col-md-6 font-inter">
                <h5 class="font-playfair text-gold mb-3 text-uppercase tracking-wider small fw-bold">Quick Links</h5>
                <ul class="list-unstyled text-silver small mb-0">
                    <li class="mb-2"><a href="<?= url('services.php') ?>" class="footer-link text-decoration-none">Services & Suites</a></li>
                    <li class="mb-2"><a href="<?= url('booking.php') ?>" class="footer-link text-decoration-none">Online Reservation</a></li>
                    <li class="mb-2"><a href="<?= url('about.php') ?>" class="footer-link text-decoration-none">Sanctuary Story</a></li>
                    <li class="mb-2"><a href="<?= url('privacy-policy.php') ?>" class="footer-link text-decoration-none">Health Privacy Policy</a></li>
                    <li class="mb-2"><a href="<?= url('terms.php') ?>" class="footer-link text-decoration-none">Booking & Etiquette</a></li>
                </ul>
            </div>

            <!-- Concierge & Estate Location -->
            <div class="col-lg-3 col-md-6 font-inter">
                <h5 class="font-playfair text-gold mb-3 text-uppercase tracking-wider small fw-bold">Private Concierge</h5>
                <ul class="list-unstyled text-silver small mb-0">
                    <li class="mb-2 d-flex align-items-start">
                        <i class="fa-solid fa-location-dot text-gold me-2 mt-1"></i>
                        <span>123 Serenity Boulevard, Vista Heights, Tagaytay City, Cavite, Philippines</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fa-solid fa-phone text-gold me-2"></i>
                        <a href="tel:+63288765432" class="footer-link text-decoration-none">+63 (02) 8876-5432</a>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fa-solid fa-mobile-screen-button text-gold me-2"></i>
                        <a href="tel:+639171000000" class="footer-link text-decoration-none">+63 917 100 0000 (VIP Mobile)</a>
                    </li>
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fa-solid fa-envelope text-gold me-2"></i>
                        <a href="mailto:concierge@bellavistaspa.com" class="footer-link text-decoration-none">concierge@bellavistaspa.com</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright & Credits -->
        <div class="border-top border-gold-subtle py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center small text-silver font-inter">
            <div>
                &copy; <?= date('Y') ?> <strong class="text-gold">Bella Vista Oasis Spa</strong>. All Rights Reserved.
            </div>
            <div class="mt-2 mt-sm-0">
                <span class="text-gold-muted">Designed for Discerning Wellness</span>
            </div>
        </div>
    </div>
</footer>

<!-- Global Toast Container (Fixed Top-Right) -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        <!-- Dynamic toasts will be rendered here by main.js -->
    </div>
</div>

<!-- Global Loading Overlay -->
<div id="global-loading-overlay" class="loading-overlay d-none">
    <div class="luxury-spinner text-center">
        <div class="spinner-gold mb-3"></div>
        <div class="font-playfair text-gold fs-5 tracking-wide">BELLA VISTA OASIS</div>
        <div class="text-silver small font-inter">Preparing your sanctuary experience...</div>
    </div>
</div>

<!-- Bootstrap 5.3 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bella Vista Oasis Core JavaScript -->
<script src="<?= asset('js/main.js') ?>?v=<?= APP_VERSION ?>"></script>
</body>
</html>
