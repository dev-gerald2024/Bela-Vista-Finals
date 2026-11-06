<?php
/**
 * Bella Vista Oasis Spa - About Us / Our Heritage
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Our Heritage & Philosophy | Bella Vista Oasis Spa';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header Banner with Breadcrumb -->
<section class="page-header-banner">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb-bvo list-inline justify-content-center">
                <li class="list-inline-item"><a href="<?= url('index.php') ?>">Home</a></li>
                <li class="list-inline-item text-silver">/</li>
                <li class="list-inline-item text-gold active" aria-current="page">About Us</li>
            </ol>
        </nav>
        <h1 class="display-4 font-playfair text-white mb-2">Our Heritage & <span class="text-gold">Philosophy</span></h1>
        <p class="text-silver mx-auto font-inter lead" style="max-width: 650px;">
            A sanctuary born from the union of timeless indigenous healing traditions and refined luxury hospitality.
        </p>
    </div>
</section>

<!-- Company Background Story -->
<section class="py-5 bg-obsidian">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge badge-gold mb-3">Our Genesis</span>
                <h2 class="display-6 text-white font-playfair mb-4">
                    The Genesis of <span class="text-gold">Bella Vista Oasis</span>
                </h2>
                <p class="text-silver leading-relaxed mb-4">
                    Established overlooking the mist-shrouded ridges of Tagaytay and expanded into Manila’s most exclusive enclave, Bella Vista Oasis Spa was conceived with a single guiding aspiration: to build a cathedral of unhurried stillness where human beings can untangle the frictions of modern life and reconnect with the vitality of their breath and body.
                </p>
                <p class="text-silver leading-relaxed mb-4">
                    The name <em>Bella Vista</em> translates to "Beautiful View" — representing not merely outward scenic panoramas, but the inner clarity that emerges when the nervous system enters profound physical and mental stillness. In our sanctuary, time behaves differently: phones are stowed away, lighting softens to warm candle tones, and the subtle scent of cold-pressed ylang-ylang and mountain lemongrass anchors you in the present moment.
                </p>
                <p class="text-silver leading-relaxed">
                    Every detail of our architecture — from acoustic insulation that blocks urban clamor to custom cedar steam suites and hypoallergenic Egyptian linens — was selected with surgical care to ensure your physical sanctuary remains inviolable.
                </p>
            </div>
            <div class="col-lg-6">
                <!-- Photo Placeholder Area with Luxury Gradient & Frame -->
                <div class="position-relative">
                    <div class="glass-card p-2 border border-gold shadow-lg" style="background: rgba(23, 26, 27, 0.9);">
                        <div class="rounded-3 p-5 text-center d-flex flex-column align-items-center justify-content-center" 
                             style="min-height: 380px; background: radial-gradient(circle at center, rgba(214, 183, 123, 0.15) 0%, rgba(16, 19, 19, 0.95) 75%), linear-gradient(135deg, #171A1B 0%, #101313 100%); border: 1px dashed rgba(214, 183, 123, 0.3);">
                            <div class="p-4 rounded-circle mb-3" style="background: rgba(214, 183, 123, 0.12); border: 1px solid var(--bvo-gold);">
                                <i class="fa-solid fa-mountain-sun text-gold display-4"></i>
                            </div>
                            <h4 class="text-white font-playfair mb-2">The Sanctuary Grounds</h4>
                            <p class="text-silver small mb-3" style="max-width: 320px;">
                                A private architectural retreat enveloped by lush tropical gardens, cascading stone fountains, and therapeutic cedar aromas.
                            </p>
                            <span class="badge badge-gold-outline small">Tagaytay Ridge & Manila Suites</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Cards Section -->
<section class="py-5" style="background: linear-gradient(180deg, #101313 0%, #171A1B 100%);">
    <div class="container py-4">
        <div class="row g-4 justify-content-center">
            <!-- Mission Card -->
            <div class="col-lg-6">
                <div class="gold-bordered-card p-4 p-md-5 h-100 position-relative overflow-hidden">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="p-3 rounded-circle" style="background: rgba(214, 183, 123, 0.15); border: 1px solid var(--bvo-gold);">
                            <i class="fa-solid fa-compass text-gold fs-3"></i>
                        </div>
                        <div>
                            <span class="badge badge-gold mb-1">Our Purpose</span>
                            <h3 class="text-gold font-playfair mb-0">Our Mission</h3>
                        </div>
                    </div>
                    <p class="text-silver leading-relaxed fs-6 mb-0">
                        "To provide an extraordinary refuge of authentic restorative care, delivering masterfully executed therapeutic rituals using pure organic botanical ingredients, immaculate hospital-grade hygiene, and personalized Filipino warmth, so that every guest departs deeply revitalized in body, centered in mind, and uplifted in spirit."
                    </p>
                </div>
            </div>

            <!-- Vision Card -->
            <div class="col-lg-6">
                <div class="glass-card p-4 p-md-5 h-100 position-relative overflow-hidden border border-gold">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="p-3 rounded-circle" style="background: rgba(214, 183, 123, 0.15); border: 1px solid var(--bvo-gold);">
                            <i class="fa-solid fa-eye text-gold fs-3"></i>
                        </div>
                        <div>
                            <span class="badge badge-gold mb-1">Our Horizon</span>
                            <h3 class="text-gold font-playfair mb-0">Our Vision</h3>
                        </div>
                    </div>
                    <p class="text-silver leading-relaxed fs-6 mb-0">
                        "To stand as Southeast Asia’s most revered sanctuary of wellness luxury — recognized internationally for pioneering the preservation of indigenous Philippine healing arts like Hilot while setting the benchmark for environmental purity, personalized guest safety, and bespoke wellness hospitality."
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- History Timeline Section -->
<section class="py-5 bg-obsidian">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge badge-gold mb-2">A Journey of Excellence</span>
            <h2 class="display-5 text-white font-playfair mb-3">Our Milestones & Heritage</h2>
            <p class="text-silver mx-auto font-inter" style="max-width: 600px;">
                From a boutique 4-suite ridge retreat to an acclaimed award-winning oasis of restorative wellness.
            </p>
        </div>

        <div class="timeline-luxury">
            <!-- Milestone 1 -->
            <div class="row g-4 align-items-center timeline-item">
                <div class="col-md-6 text-md-end">
                    <span class="badge badge-gold mb-2">October 2018</span>
                    <h4 class="text-white font-playfair mb-2">The Founding on the Ridge</h4>
                    <p class="text-silver small mb-0">
                        Bella Vista Oasis Spa opens its handcrafted doors in Tagaytay with four private treatment suites, rooted in traditional Philippine Hilot and botanical herbal compresses.
                    </p>
                </div>
                <div class="timeline-node d-none d-md-block"></div>
                <div class="col-md-6">
                    <div class="glass-card-sm border border-gold-subtle p-3 rounded-3" style="max-width: 320px;">
                        <span class="text-gold fw-bold small"><i class="fa-solid fa-seedling me-1"></i> Foundation Stone</span>
                        <div class="text-silver small">4 Private suites • 6 Master Hilot practitioners</div>
                    </div>
                </div>
            </div>

            <!-- Milestone 2 -->
            <div class="row g-4 align-items-center timeline-item">
                <div class="col-md-6 order-md-2">
                    <span class="badge badge-gold mb-2">May 2020</span>
                    <h4 class="text-white font-playfair mb-2">Organic Farm Partnership</h4>
                    <p class="text-silver small mb-0">
                        We established direct cooperative partnerships with indigenous organic growers in Laguna and Benguet to source virgin cold-pressed coconut oil, therapeutic lemongrass, and mountain ginger exclusively for our blends.
                    </p>
                </div>
                <div class="timeline-node d-none d-md-block"></div>
                <div class="col-md-6 text-md-end order-md-1">
                    <div class="glass-card-sm border border-gold-subtle p-3 rounded-3 ms-md-auto" style="max-width: 320px;">
                        <span class="text-gold fw-bold small"><i class="fa-solid fa-leaf me-1"></i> 100% Farm-To-Skin</span>
                        <div class="text-silver small">Zero synthetic preservatives or mineral oils</div>
                    </div>
                </div>
            </div>

            <!-- Milestone 3 -->
            <div class="row g-4 align-items-center timeline-item">
                <div class="col-md-6 text-md-end">
                    <span class="badge badge-gold mb-2">November 2022</span>
                    <h4 class="text-white font-playfair mb-2">Metropolitan Flagship Expansion</h4>
                    <p class="text-silver small mb-0">
                        Responding to continuous guest demand, we unveiled our state-of-the-art Manila urban sanctuary, featuring specialized couples hydrotherapy suites, acoustic soundproofing, and our signature Family Lounge.
                    </p>
                </div>
                <div class="timeline-node d-none d-md-block"></div>
                <div class="col-md-6">
                    <div class="glass-card-sm border border-gold-subtle p-3 rounded-3" style="max-width: 320px;">
                        <span class="text-gold fw-bold small"><i class="fa-solid fa-hotel me-1"></i> Urban Sanctuary</span>
                        <div class="text-silver small">Expanded to 12 luxury private suites</div>
                    </div>
                </div>
            </div>

            <!-- Milestone 4 -->
            <div class="row g-4 align-items-center timeline-item">
                <div class="col-md-6 order-md-2">
                    <span class="badge badge-gold mb-2">December 2024</span>
                    <h4 class="text-white font-playfair mb-2">Luxury Spa Awards Recognition</h4>
                    <p class="text-silver small mb-0">
                        Awarded "Best Luxury Boutique Day Spa in the Philippines" at the Asian Hospitality & Wellness Gala for unmatched clinical sanitation and bespoke guest care standards.
                    </p>
                </div>
                <div class="timeline-node d-none d-md-block"></div>
                <div class="col-md-6 text-md-end order-md-1">
                    <div class="glass-card-sm border border-gold-subtle p-3 rounded-3 ms-md-auto" style="max-width: 320px;">
                        <span class="text-gold fw-bold small"><i class="fa-solid fa-trophy me-1"></i> Industry Benchmark</span>
                        <div class="text-silver small">Over 10,000 satisfied luxury patrons</div>
                    </div>
                </div>
            </div>

            <!-- Milestone 5 -->
            <div class="row g-4 align-items-center timeline-item mb-0">
                <div class="col-md-6 text-md-end">
                    <span class="badge badge-gold mb-2">Present Day</span>
                    <h4 class="text-white font-playfair mb-2">Digital Sanctuary Innovation</h4>
                    <p class="text-silver small mb-0">
                        Launch of our comprehensive digital reservation management system, allowing guests to effortlessly schedule bespoke rituals, specify aromatic preferences, and submit pre-arrival health declarations securely.
                    </p>
                </div>
                <div class="timeline-node d-none d-md-block"></div>
                <div class="col-md-6">
                    <div class="glass-card-sm border border-gold-subtle p-3 rounded-3" style="max-width: 320px;">
                        <span class="text-gold fw-bold small"><i class="fa-solid fa-shield-halved me-1"></i> Modern Care</span>
                        <div class="text-silver small">Digital wellness tracking & automated room prep</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values Section -->
<section class="py-5" style="background: linear-gradient(180deg, #101313 0%, #171A1B 100%);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge badge-gold mb-2">Our Pillars</span>
            <h2 class="display-5 text-white font-playfair mb-3">The Four Pillars of Bella Vista</h2>
            <p class="text-silver mx-auto font-inter" style="max-width: 600px;">
                Guiding every stroke, ritual, and guest encounter throughout our sanctuary.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="glass-card h-100 p-4 text-center">
                    <div class="p-3 rounded-circle d-inline-flex mb-3" style="background: rgba(214, 183, 123, 0.15);">
                        <i class="fa-solid fa-spa text-gold fs-3"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Botanical Purity</h5>
                    <p class="text-silver small mb-0">
                        We formulate all massage oils, body scrubs, and floral teas with zero paraffin, mineral oils, or synthetic perfumes. Only raw organic essences touch your skin.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="glass-card h-100 p-4 text-center">
                    <div class="p-3 rounded-circle d-inline-flex mb-3" style="background: rgba(214, 183, 123, 0.15);">
                        <i class="fa-solid fa-hands-holding text-gold fs-3"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Master Practitioners</h5>
                    <p class="text-silver small mb-0">
                        Our therapists are certified artisans with profound anatomical comprehension, regular skill assessments, and deep mindfulness training.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="glass-card h-100 p-4 text-center">
                    <div class="p-3 rounded-circle d-inline-flex mb-3" style="background: rgba(214, 183, 123, 0.15);">
                        <i class="fa-solid fa-pump-medical text-gold fs-3"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Absolute Hygiene</h5>
                    <p class="text-silver small mb-0">
                        A strict 30-minute UV sanitization buffer between every appointment ensures every room, headrest, and linen is clinically pristine.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="glass-card h-100 p-4 text-center">
                    <div class="p-3 rounded-circle d-inline-flex mb-3" style="background: rgba(214, 183, 123, 0.15);">
                        <i class="fa-solid fa-heart-pulse text-gold fs-3"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Bespoke Safety</h5>
                    <p class="text-silver small mb-0">
                        Every treatment is customized around your personal health declaration, contraindications, stress hotspots, and comfort thresholds.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-charcoal text-center border-top border-gold-subtle">
    <div class="container py-3">
        <h3 class="display-6 font-playfair text-white mb-3">Immerse Yourself in Our Story</h3>
        <p class="text-silver mx-auto mb-4" style="max-width: 600px;">
            Experience the calm firsthand. Book your private sanctuary suite and let our master therapists restore your balance.
        </p>
        <a href="<?= url('services.php') ?>" class="btn btn-gold btn-lg px-5 py-3 shadow-gold">
            <i class="fa-solid fa-calendar-check me-2"></i> Explore Our Treatments
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
