`<?php
/**
 * Bella Vista Oasis Spa - Homepage
 * Where Luxury Meets Tranquility
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Bella Vista Oasis Spa | Where Luxury Meets Tranquility';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section text-center position-relative overflow-hidden">
    <div class="hero-glow-orb"></div>
    <div class="container position-relative z-2">
        <span class="hero-subtitle mb-3 d-inline-block">The Pinnacle of Holistic Serenity</span>
        <h1 class="display-3 fw-bold text-white mb-3 font-playfair">
            Bella Vista <span class="text-gold">Oasis Spa</span>
        </h1>
        <p class="lead text-silver mx-auto mb-4 font-inter" style="max-width: 680px; font-size: 1.25rem;">
            Where Luxury Meets Tranquility. Discover an elevated sanctuary of holistic rejuvenation, bespoke botanical therapies, and transcendent stillness.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= url('booking.php') ?>" class="btn btn-gold btn-lg px-4 py-3 shadow-gold">
                <i class="fa-solid fa-calendar-check me-2"></i> Book Your Experience
            </a>
            <a href="<?= url('services.php') ?>" class="btn btn-outline-gold btn-lg px-4 py-3">
                <i class="fa-solid fa-spa me-2"></i> Explore Services
            </a>
        </div>

        <!-- Quick Info Ribbon -->
        <div class="row g-3 justify-content-center mt-5 pt-3">
            <div class="col-auto">
                <div class="glass-card-sm d-flex align-items-center gap-2 border border-gold-subtle rounded-pill px-4 py-2">
                    <i class="fa-regular fa-clock text-gold"></i>
                    <span class="small text-silver">Open Daily: <strong>1:00 PM – 9:00 PM</strong></span>
                </div>
            </div>
            <div class="col-auto">
                <div class="glass-card-sm d-flex align-items-center gap-2 border border-gold-subtle rounded-pill px-4 py-2">
                    <i class="fa-solid fa-location-dot text-gold"></i>
                    <span class="small text-silver">Manila & Tagaytay Sanctuary</span>
                </div>
            </div>
            <div class="col-auto">
                <div class="glass-card-sm d-flex align-items-center gap-2 border border-gold-subtle rounded-pill px-4 py-2">
                    <i class="fa-solid fa-sparkles text-gold"></i>
                    <span class="small text-silver">Hospital-Grade UV Sterilization</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Spa Introduction Section -->
<section class="py-5" style="background: linear-gradient(180deg, #101313 0%, #171A1B 100%);">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="pe-lg-4">
                    <span class="badge badge-gold mb-3">Our Sanctuary Philosophy</span>
                    <h2 class="display-5 text-white font-playfair mb-4">
                        An Oasis Curated for <span class="text-gold">Body, Mind & Spirit</span>
                    </h2>
                    <p class="text-silver leading-relaxed mb-4">
                        Conceived as a sublime escape from the frenetic rhythms of modern life, Bella Vista Oasis Spa pairs timeless ancient wellness traditions with modern clinical hygiene. Every ritual is intentionally designed to restore equilibrium, using locally harvested organic botanicals, therapeutic essential oils, and heated volcanic basalt stones.
                    </p>
                    <p class="text-silver leading-relaxed mb-4">
                        Our master therapists undergo rigorous continuous training to deliver tailored treatments that ease tension, enhance lymphatic drainage, and guide your consciousness into deep, restful theta states.
                    </p>
                    <div class="row g-4 pt-2">
                        <div class="col-6">
                            <div class="border-start border-2 border-gold ps-3">
                                <h3 class="text-gold font-playfair fw-bold mb-0">100%</h3>
                                <p class="small text-silver mb-0">Organic Botanical Essences</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border-start border-2 border-gold ps-3">
                                <h3 class="text-gold font-playfair fw-bold mb-0">30 Min</h3>
                                <p class="small text-silver mb-0">Buffer Between Every Guest</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <div class="glass-card p-4 border border-gold shadow-lg" style="background: rgba(23, 26, 27, 0.85);">
                        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-gold-subtle">
                            <div>
                                <h4 class="text-gold font-playfair mb-1">Sanctuary Privileges</h4>
                                <p class="small text-silver mb-0">Included with every spa session</p>
                            </div>
                            <i class="fa-solid fa-gem text-gold fs-2"></i>
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex gap-3">
                                <div class="text-gold mt-1"><i class="fa-solid fa-leaf"></i></div>
                                <div>
                                    <strong class="text-white d-block">Welcome Floral Foot Ritual</strong>
                                    <span class="small text-silver">Warm lavender soak and organic sea salt polish prior to your therapy.</span>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <div class="text-gold mt-1"><i class="fa-solid fa-mug-hot"></i></div>
                                <div>
                                    <strong class="text-white d-block">Artisanal Herbal Elixir</strong>
                                    <span class="small text-silver">Post-treatment infusion of wild lemongrass, ginger, and raw honey.</span>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <div class="text-gold mt-1"><i class="fa-solid fa-volume-xmark"></i></div>
                                <div>
                                    <strong class="text-white d-block">Acoustic Soundproofing</strong>
                                    <span class="small text-silver">Dedicated private suites engineered with sound dampening and gentle ocean frequencies.</span>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <div class="text-gold mt-1"><i class="fa-solid fa-temperature-half"></i></div>
                                <div>
                                    <strong class="text-white d-block">Individual Climate & Scent Control</strong>
                                    <span class="small text-silver">Select your favorite aromatherapy profile and ambient room temperature.</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 text-center border-top border-gold-subtle">
                            <a href="<?= url('about.php') ?>" class="text-gold text-decoration-none fw-semibold small">
                                Read More About Our Heritage <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Services Section -->
<section class="py-5 bg-obsidian">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge badge-gold mb-2">Curated Treatments</span>
            <h2 class="display-5 text-white font-playfair mb-3">Featured Sanctuary Experiences</h2>
            <p class="text-silver mx-auto font-inter" style="max-width: 600px;">
                Handcrafted therapeutic sessions designed to release chronic tension and evoke timeless serenity.
            </p>
        </div>

        <div class="row g-4">
            <!-- Card 1: Single Room Massage -->
            <div class="col-lg-4 col-md-6">
                <div class="glass-card service-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="p-3 rounded-circle" style="background: rgba(214, 183, 123, 0.12); border: 1px solid rgba(214, 183, 123, 0.3);">
                                <i class="fa-solid fa-spa text-gold fs-3"></i>
                            </div>
                            <span class="badge badge-gold">Single Suite</span>
                        </div>
                        <h4 class="text-white font-playfair mb-2">Single Room Massage</h4>
                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span class="card-price">₱800</span>
                            <span class="text-silver small">/ 60 mins</span>
                        </div>
                        <p class="text-silver small mb-4 flex-grow-1">
                            Immerse in holistic rejuvenation tailored specifically for your body. Our certified therapists utilize cold-pressed virgin coconut and therapeutic botanical oils to soothe muscle tension, enhance circulation, and calm your senses in a private, tranquil room.
                        </p>
                        <div class="d-flex flex-column gap-2 mb-4 pt-2 border-top border-gold-subtle">
                            <div class="d-flex justify-content-between small text-silver">
                                <span><i class="fa-regular fa-clock text-gold me-2"></i>Duration:</span>
                                <strong class="text-white">60 Minutes</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-silver">
                                <span><i class="fa-solid fa-user text-gold me-2"></i>Capacity:</span>
                                <strong class="text-white">1 Guest</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-silver">
                                <span><i class="fa-solid fa-door-closed text-gold me-2"></i>Room:</span>
                                <strong class="text-white">Private Single Suite</strong>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="<?= url('booking.php?service_id=1') ?>" class="btn btn-gold btn-sm">
                                <i class="fa-solid fa-calendar-plus me-1"></i> Book This Service
                            </a>
                            <a href="<?= url('services.php#service-1') ?>" class="btn btn-outline-gold btn-sm">
                                Learn More Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Couples Suite -->
            <div class="col-lg-4 col-md-6">
                <div class="glass-card service-card" style="border-color: rgba(214, 183, 123, 0.45); box-shadow: 0 16px 40px rgba(0,0,0,0.6), 0 0 25px rgba(214,183,123,0.15);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="p-3 rounded-circle" style="background: rgba(214, 183, 123, 0.18); border: 1px solid rgba(214, 183, 123, 0.5);">
                                <i class="fa-solid fa-heart text-gold fs-3"></i>
                            </div>
                            <span class="badge badge-gold" style="background: var(--bvo-gold); color: #101313;">Signature Favorite</span>
                        </div>
                        <h4 class="text-white font-playfair mb-2">Couples Suite Indulgence</h4>
                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span class="card-price">₱1,800</span>
                            <span class="text-silver small">/ 90 mins (for 2)</span>
                        </div>
                        <p class="text-silver small mb-4 flex-grow-1">
                            An intimate sanctuary designed for two. Experience synchronized aromatherapy full-body massages side-by-side in our private couples suite, complemented with warm floral foot rituals, organic herbal tea, and ambient candlelight.
                        </p>
                        <div class="d-flex flex-column gap-2 mb-4 pt-2 border-top border-gold-subtle">
                            <div class="d-flex justify-content-between small text-silver">
                                <span><i class="fa-regular fa-clock text-gold me-2"></i>Duration:</span>
                                <strong class="text-white">90 Minutes</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-silver">
                                <span><i class="fa-solid fa-user-group text-gold me-2"></i>Capacity:</span>
                                <strong class="text-white">2 Guests (Included)</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-silver">
                                <span><i class="fa-solid fa-gem text-gold me-2"></i>Room:</span>
                                <strong class="text-white">Couples Deluxe Suite</strong>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="<?= url('booking.php?service_id=2') ?>" class="btn btn-gold btn-sm">
                                <i class="fa-solid fa-calendar-plus me-1"></i> Book Couples Suite
                            </a>
                            <a href="<?= url('services.php#service-2') ?>" class="btn btn-outline-gold btn-sm">
                                Learn More Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Family Lounge -->
            <div class="col-lg-4 col-md-6">
                <div class="glass-card service-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="p-3 rounded-circle" style="background: rgba(214, 183, 123, 0.12); border: 1px solid rgba(214, 183, 123, 0.3);">
                                <i class="fa-solid fa-users text-gold fs-3"></i>
                            </div>
                            <span class="badge badge-gold">Group & Family</span>
                        </div>
                        <h4 class="text-white font-playfair mb-2">Family Lounge Escape</h4>
                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span class="card-price">₱4,000</span>
                            <span class="text-silver small">/ 90 mins (up to 4-6)</span>
                        </div>
                        <p class="text-silver small mb-4 flex-grow-1">
                            A shared luxury experience for families or small groups of up to 4 guests (accommodates up to 6 with additional guest fee). Includes personalized massages, herbal steam therapy, reflexology, and artisanal refreshments in our expansive royal suite lounge.
                        </p>
                        <div class="d-flex flex-column gap-2 mb-4 pt-2 border-top border-gold-subtle">
                            <div class="d-flex justify-content-between small text-silver">
                                <span><i class="fa-regular fa-clock text-gold me-2"></i>Duration:</span>
                                <strong class="text-white">90 Minutes</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-silver">
                                <span><i class="fa-solid fa-people-roof text-gold me-2"></i>Capacity:</span>
                                <strong class="text-white">4 Guests (+₱750/extra)</strong>
                            </div>
                            <div class="d-flex justify-content-between small text-silver">
                                <span><i class="fa-solid fa-couch text-gold me-2"></i>Room:</span>
                                <strong class="text-white">Royal Family Lounge</strong>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="<?= url('booking.php?service_id=3') ?>" class="btn btn-gold btn-sm">
                                <i class="fa-solid fa-calendar-plus me-1"></i> Book Family Lounge
                            </a>
                            <a href="<?= url('services.php#service-3') ?>" class="btn btn-outline-gold btn-sm">
                                Learn More Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="<?= url('services.php') ?>" class="btn btn-outline-gold px-5 py-3">
                <i class="fa-solid fa-list-check me-2"></i> View All Treatments & Packages
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5" style="background: linear-gradient(180deg, #171A1B 0%, #101313 100%);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge badge-gold mb-2">The Bella Vista Distinction</span>
            <h2 class="display-5 text-white font-playfair mb-3">Why Discerning Guests Choose Us</h2>
            <p class="text-silver mx-auto font-inter" style="max-width: 650px;">
                Every touchpoint within our sanctuary is curated to guarantee uncompromised luxury, holistic healing, and absolute peace of mind.
            </p>
        </div>

        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-lg-3 col-md-6">
                <div class="glass-card text-center h-100 p-4">
                    <div class="mb-3 d-inline-flex p-3 rounded-circle" style="background: rgba(214, 183, 123, 0.12);">
                        <i class="fa-solid fa-certificate text-gold fs-2"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Premium Therapists</h5>
                    <p class="text-silver small mb-0">
                        All our practitioners hold prestigious national and international certifications with minimum 500+ clinical therapy hours and deep mastery of anatomy.
                    </p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-lg-3 col-md-6">
                <div class="glass-card text-center h-100 p-4">
                    <div class="mb-3 d-inline-flex p-3 rounded-circle" style="background: rgba(214, 183, 123, 0.12);">
                        <i class="fa-solid fa-bed text-gold fs-2"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Luxury Rooms</h5>
                    <p class="text-silver small mb-0">
                        Individual soundproof suites featuring ergonomic memory-foam beds, pure Egyptian cotton linens, ambient chromatic lighting, and private ensuites.
                    </p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-lg-3 col-md-6">
                <div class="glass-card text-center h-100 p-4">
                    <div class="mb-3 d-inline-flex p-3 rounded-circle" style="background: rgba(214, 183, 123, 0.12);">
                        <i class="fa-solid fa-shield-virus text-gold fs-2"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Hygiene First</h5>
                    <p class="text-silver small mb-0">
                        Hospital-grade medical sanitation and strict 30-minute UV-C sterilization protocols between every appointment ensure absolute cleanliness.
                    </p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-lg-3 col-md-6">
                <div class="glass-card text-center h-100 p-4">
                    <div class="mb-3 d-inline-flex p-3 rounded-circle" style="background: rgba(214, 183, 123, 0.12);">
                        <i class="fa-solid fa-hand-holding-heart text-gold fs-2"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Personalized Care</h5>
                    <p class="text-silver small mb-0">
                        Complimentary digital health consultation to customize your oil essences, pressure gradient, focus muscle regions, and tea preferences.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Business Highlights & Operating Hours Section -->
<section class="py-5 bg-obsidian border-top border-bottom border-gold-subtle">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge badge-gold">Sanctuary Hours</span>
                    <span class="text-silver small"><i class="fa-solid fa-circle text-success me-1 fs-6"></i> Accepting Reservations Today</span>
                </div>
                <h3 class="display-6 text-white font-playfair mb-3">
                    Operating Daily from <span class="text-gold">1:00 PM to 9:00 PM</span>
                </h3>
                <p class="text-silver mb-4 leading-relaxed">
                    We welcome you 7 days a week, Monday through Sunday. To preserve tranquility and ensure thorough 30-minute deep sanitization of all suites, advance appointments are required. Please arrive 15 minutes before your scheduled ritual to complete your personalized health declaration and enjoy our welcoming tea ceremony.
                </p>
                <div class="d-flex flex-wrap gap-4 text-silver small">
                    <div>
                        <i class="fa-solid fa-map-pin text-gold me-2"></i>
                        <strong>Address:</strong> Manila & Tagaytay Sanctuary, Philippines
                    </div>
                    <div>
                        <i class="fa-solid fa-phone text-gold me-2"></i>
                        <strong>Concierge:</strong> +63 (02) 8876-5432 / +63 917 100 0000
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="glass-card p-4 border border-gold" style="background: rgba(23, 26, 27, 0.9);">
                    <h5 class="text-gold font-playfair mb-3">Seamless Online Booking</h5>
                    <p class="text-silver small mb-3">
                        Select your preferred treatment, choose your suite type, designate your therapist, and secure your session in under two minutes.
                    </p>
                    <ul class="list-unstyled small text-silver mb-4">
                        <li class="mb-2"><i class="fa-solid fa-check text-gold me-2"></i> Instant confirmation & digital calendar invite</li>
                        <li class="mb-2"><i class="fa-solid fa-check text-gold me-2"></i> Flexible cancellation up to 6 hours prior</li>
                        <li class="mb-2"><i class="fa-solid fa-check text-gold me-2"></i> Earn loyalty points with every visit</li>
                    </ul>
                    <a href="<?= url('booking.php') ?>" class="btn btn-gold w-100 fw-bold">
                        <i class="fa-solid fa-calendar-check me-2"></i> Reserve Appointment Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Guest Testimonials Section -->
<section class="py-5" style="background: linear-gradient(180deg, #101313 0%, #171A1B 100%);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge badge-gold mb-2">Guest Praises</span>
            <h2 class="display-5 text-white font-playfair mb-3">Stories of Restored Vitality</h2>
            <p class="text-silver mx-auto font-inter" style="max-width: 600px;">
                Discover why over 5,000 discerning guests consider Bella Vista Oasis Spa their ultimate wellness retreat.
            </p>
        </div>

        <div class="row g-4">
            <!-- Review 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="glass-card h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-gold mb-3">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-silver italic mb-4">
                            "The Couples Suite Indulgence was nothing short of magical. The room aroma, the synchronized massage, and the warm foot ritual melted away weeks of corporate exhaustion. Bella Vista is unmatched in Manila."
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3 pt-3 border-top border-gold-subtle">
                        <div class="avatar-circle">
                            <span class="avatar-initial">C</span>
                        </div>
                        <div>
                            <strong class="text-white d-block">Clarissa & Edward M.</strong>
                            <span class="text-silver small">Couples Suite Guests • Verified</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="glass-card h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-gold mb-3">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-silver italic mb-4">
                            "As an executive with chronic lower back pain, Elena's deep tissue therapy was profoundly restorative. The room hygiene and sanitization protocols gave me absolute peace of mind. A true 5-star haven."
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3 pt-3 border-top border-gold-subtle">
                        <div class="avatar-circle">
                            <span class="avatar-initial">R</span>
                        </div>
                        <div>
                            <strong class="text-white d-block">Rafael D. Gutierrez</strong>
                            <span class="text-silver small">Single Suite Guest • Verified</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="glass-card h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-gold mb-3">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-silver italic mb-4">
                            "We celebrated my mother's 60th birthday in the Royal Family Lounge. The staff treated our entire family with royalty-level care. The herbal tea and steam therapy were heavenly!"
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-3 pt-3 border-top border-gold-subtle">
                        <div class="avatar-circle">
                            <span class="avatar-initial">B</span>
                        </div>
                        <div>
                            <strong class="text-white d-block">Bea & Family</strong>
                            <span class="text-silver small">Family Lounge Guests • Verified</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 bg-charcoal position-relative overflow-hidden">
    <div class="container py-4 text-center position-relative z-2">
        <span class="badge badge-gold mb-3">Begin Your Wellness Journey</span>
        <h2 class="display-5 text-white font-playfair mb-3">
            Ready to Rediscover <span class="text-gold">Pure Tranquility?</span>
        </h2>
        <p class="text-silver mx-auto mb-4 font-inter" style="max-width: 650px;">
            Reserve your sanctuary suite today. Choose your private room, therapeutic essences, and preferred appointment time.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= url('booking.php') ?>" class="btn btn-gold btn-lg px-5 py-3 shadow-gold fw-bold">
                <i class="fa-solid fa-calendar-check me-2"></i> Book Your Sanctuary Now
            </a>
            <a href="<?= url('contact.php') ?>" class="btn btn-outline-gold btn-lg px-4 py-3">
                <i class="fa-solid fa-comments me-2"></i> Speak with Concierge
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
