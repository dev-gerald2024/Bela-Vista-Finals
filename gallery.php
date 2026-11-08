<?php
/**
 * Bella Vista Oasis Spa - Gallery
 * A Visual Sanctuary of Serenity
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Visual Gallery & Sanctuary Ambiance | Bella Vista Oasis Spa';
require_once __DIR__ . '/includes/header.php';

// Gallery dataset with luxury placeholder gradients & details
$galleryItems = [
    [
        'id' => 1,
        'title' => 'The Couples Deluxe Suite',
        'category' => 'Rooms',
        'tag' => 'rooms',
        'desc' => 'Dual ergonomic therapy beds bathed in warm, dimmable ambient glow, adorned with fresh orchids and luxury Egyptian cotton linens.',
        'icon' => 'fa-heart',
        'bg_gradient' => 'radial-gradient(circle at 40% 40%, rgba(214, 183, 123, 0.25) 0%, rgba(23, 26, 27, 0.98) 75%)'
    ],
    [
        'id' => 2,
        'title' => 'Cedar Herbal Steam Chamber',
        'category' => 'Facilities',
        'tag' => 'facilities',
        'desc' => 'Handcrafted Nordic cedar wood infused with wild mountain eucalyptus and lemongrass vapors to open airways and promote detox.',
        'icon' => 'fa-smog',
        'bg_gradient' => 'radial-gradient(circle at 60% 30%, rgba(214, 183, 123, 0.22) 0%, rgba(16, 19, 19, 0.98) 80%)'
    ],
    [
        'id' => 3,
        'title' => 'Signature Hot Stone Placement',
        'category' => 'Treatments',
        'tag' => 'treatments',
        'desc' => 'Naturally smoothed basalt river stones heated to 52°C and placed along primary spinal chakras to release myofascial tension.',
        'icon' => 'fa-fire-flame-curved',
        'bg_gradient' => 'radial-gradient(circle at 30% 60%, rgba(235, 187, 85, 0.25) 0%, rgba(23, 26, 27, 0.98) 75%)'
    ],
    [
        'id' => 4,
        'title' => 'The Candlelit Twilight Corridor',
        'category' => 'Ambiance',
        'tag' => 'ambiance',
        'desc' => 'Acoustically insulated corridors with recessed floor luminaires guiding guests from reception into meditative private suites.',
        'icon' => 'fa-moon',
        'bg_gradient' => 'radial-gradient(circle at 50% 50%, rgba(214, 183, 123, 0.18) 0%, rgba(11, 13, 13, 0.98) 85%)'
    ],
    [
        'id' => 5,
        'title' => 'Royal Family Lounge & Spa Suite',
        'category' => 'Rooms',
        'tag' => 'rooms',
        'desc' => 'An expansive 60-square-meter sanctuary accommodating up to 6 guests with bespoke treatment zones and artisanal tea lounge.',
        'icon' => 'fa-couch',
        'bg_gradient' => 'radial-gradient(circle at 70% 40%, rgba(214, 183, 123, 0.28) 0%, rgba(23, 26, 27, 0.98) 70%)'
    ],
    [
        'id' => 6,
        'title' => 'Thermal Mineral Jacuzzi Pool',
        'category' => 'Facilities',
        'tag' => 'facilities',
        'desc' => 'Deep mineral soaking waters enriched with Epsom salts, volcanic minerals, and soothing lavender hydrosols.',
        'icon' => 'fa-water',
        'bg_gradient' => 'radial-gradient(circle at 40% 70%, rgba(214, 183, 123, 0.24) 0%, rgba(16, 19, 19, 0.98) 75%)'
    ],
    [
        'id' => 7,
        'title' => 'Traditional Hilot with Banana Leaves',
        'category' => 'Treatments',
        'tag' => 'treatments',
        'desc' => 'Warm virgin coconut oil smoothed over heated banana leaves to detect lamig (energy blockages) and restore somatic equilibrium.',
        'icon' => 'fa-leaf',
        'bg_gradient' => 'radial-gradient(circle at 50% 30%, rgba(214, 183, 123, 0.2) 0%, rgba(23, 26, 27, 0.98) 75%)'
    ],
    [
        'id' => 8,
        'title' => 'Living Botanical Wall & Zen Atrium',
        'category' => 'Ambiance',
        'tag' => 'ambiance',
        'desc' => 'Natural indoor waterfall cascading alongside lush tropical philodendrons and ferns, filling the air with negative ions.',
        'icon' => 'fa-mountain-sun',
        'bg_gradient' => 'radial-gradient(circle at 60% 60%, rgba(214, 183, 123, 0.22) 0%, rgba(11, 13, 13, 0.98) 80%)'
    ],
    [
        'id' => 9,
        'title' => 'Single Private Rejuvenation Suite',
        'category' => 'Rooms',
        'tag' => 'rooms',
        'desc' => 'Individual temperature-controlled haven with noise-canceling acoustics, private ensuite shower, and mood-adaptive illumination.',
        'icon' => 'fa-door-closed',
        'bg_gradient' => 'radial-gradient(circle at 35% 45%, rgba(214, 183, 123, 0.25) 0%, rgba(23, 26, 27, 0.98) 75%)'
    ],
    [
        'id' => 10,
        'title' => 'Artisanal Herbal Elixir Bar',
        'category' => 'Facilities',
        'tag' => 'facilities',
        'desc' => 'Post-treatment welcome bar offering fresh infusions of lemongrass, pandan, wild honey, and mountain ginger.',
        'icon' => 'fa-mug-hot',
        'bg_gradient' => 'radial-gradient(circle at 50% 50%, rgba(214, 183, 123, 0.22) 0%, rgba(16, 19, 19, 0.98) 75%)'
    ],
    [
        'id' => 11,
        'title' => 'Aromatherapy Foot Scrub Ritual',
        'category' => 'Treatments',
        'tag' => 'treatments',
        'desc' => 'Warm botanical foot bath enriched with lavender oil and pink Himalayan sea salt to stimulate reflex zones prior to therapy.',
        'icon' => 'fa-spa',
        'bg_gradient' => 'radial-gradient(circle at 65% 40%, rgba(214, 183, 123, 0.25) 0%, rgba(23, 26, 27, 0.98) 70%)'
    ],
    [
        'id' => 12,
        'title' => 'Reception Sanctuary & Tea Lounge',
        'category' => 'Ambiance',
        'tag' => 'ambiance',
        'desc' => 'High-ceilinged welcome atrium finished in brushed champagne gold, obsidian slate stone, and warm ambient brass fixtures.',
        'icon' => 'fa-bell-concierge',
        'bg_gradient' => 'radial-gradient(circle at 45% 45%, rgba(214, 183, 123, 0.2) 0%, rgba(16, 19, 19, 0.98) 80%)'
    ]
];
?>

<!-- Page Header Banner -->
<section class="page-header-banner">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb-bvo list-inline justify-content-center">
                <li class="list-inline-item"><a href="<?= url('index.php') ?>">Home</a></li>
                <li class="list-inline-item text-silver">/</li>
                <li class="list-inline-item text-gold active" aria-current="page">Gallery</li>
            </ol>
        </nav>
        <h1 class="display-4 font-playfair text-white mb-2">Visual <span class="text-gold">Sanctuary</span></h1>
        <p class="text-silver mx-auto font-inter lead" style="max-width: 650px;">
            A glimpse into the serene architecture, luxurious suites, and therapeutic spaces of Bella Vista Oasis Spa.
        </p>
    </div>
</section>

<!-- Gallery Section -->
<section class="py-5 bg-obsidian">
    <div class="container py-4">

        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
            <button type="button" class="btn btn-outline-gold active gallery-tab-btn px-4 py-2" data-filter="all">
                All (12)
            </button>
            <button type="button" class="btn btn-outline-gold gallery-tab-btn px-4 py-2" data-filter="rooms">
                <i class="fa-solid fa-bed me-1"></i> Rooms
            </button>
            <button type="button" class="btn btn-outline-gold gallery-tab-btn px-4 py-2" data-filter="facilities">
                <i class="fa-solid fa-water me-1"></i> Facilities
            </button>
            <button type="button" class="btn btn-outline-gold gallery-tab-btn px-4 py-2" data-filter="treatments">
                <i class="fa-solid fa-spa me-1"></i> Treatments
            </button>
            <button type="button" class="btn btn-outline-gold gallery-tab-btn px-4 py-2" data-filter="ambiance">
                <i class="fa-solid fa-moon me-1"></i> Ambiance
            </button>
        </div>

        <!-- Gallery Grid (Masonry / Fluid) -->
        <div class="row g-4" id="galleryGrid">
            <?php foreach ($galleryItems as $item): ?>
                <div class="col-lg-4 col-md-6 gallery-col" data-tag="<?= $item['tag'] ?>">
                    <div class="gallery-card" 
                         data-id="<?= $item['id'] ?>"
                         data-title="<?= htmlspecialchars($item['title'], ENT_QUOTES) ?>"
                         data-category="<?= htmlspecialchars($item['category'], ENT_QUOTES) ?>"
                         data-desc="<?= htmlspecialchars($item['desc'], ENT_QUOTES) ?>"
                         data-icon="<?= $item['icon'] ?>"
                         data-bg="<?= htmlspecialchars($item['bg_gradient'], ENT_QUOTES) ?>"
                         tabindex="0"
                         role="button"
                         aria-label="View <?= htmlspecialchars($item['title'], ENT_QUOTES) ?>">
                        
                        <!-- Visual Luxury Graphic Placeholder -->
                        <div class="gallery-img-placeholder" style="background: <?= $item['bg_gradient'] ?>; border: 1px solid rgba(214, 183, 123, 0.2);">
                            <div class="p-4 rounded-circle mb-2" style="background: rgba(214, 183, 123, 0.12); border: 1px solid var(--bvo-gold-border);">
                                <i class="fa-solid <?= $item['icon'] ?> text-gold fs-1"></i>
                            </div>
                            <span class="badge badge-gold-outline small"><?= htmlspecialchars($item['category'], ENT_QUOTES) ?></span>
                        </div>

                        <!-- Hover Overlay -->
                        <div class="gallery-overlay">
                            <span class="badge badge-gold mb-2 w-auto align-self-start"><?= htmlspecialchars($item['category'], ENT_QUOTES) ?></span>
                            <h5 class="text-white font-playfair mb-1"><?= htmlspecialchars($item['title'], ENT_QUOTES) ?></h5>
                            <p class="text-silver small mb-2 text-truncate"><?= htmlspecialchars($item['desc'], ENT_QUOTES) ?></p>
                            <span class="text-gold small fw-semibold"><i class="fa-solid fa-expand me-1"></i> Click to enlarge</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Reserve CTA Bar -->
        <div class="glass-card mt-5 p-4 text-center border-gold-subtle">
            <h4 class="text-white font-playfair mb-2">Ready to Experience This Serenity?</h4>
            <p class="text-silver small mb-3">Our suites are prepared with individualized climate control, custom aromatherapy, and 30-minute UV sanitization.</p>
            <a href="<?= url('booking.php') ?>" class="btn btn-gold px-4 py-2 shadow-gold fw-bold">
                <i class="fa-solid fa-calendar-check me-2"></i> Book Your Sanctuary Visit
            </a>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div class="lightbox-modal" id="galleryLightbox" role="dialog" aria-modal="true" aria-labelledby="lightboxTitle">
    <div class="lightbox-content">
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" id="lightboxClose" aria-label="Close"></button>
        
        <!-- Large Visual Placeholder Canvas -->
        <div id="lightboxVisual" class="d-flex flex-column align-items-center justify-content-center p-5 text-center" style="min-height: 400px; position: relative;">
            <div id="lightboxIconBox" class="p-4 rounded-circle mb-3" style="background: rgba(214, 183, 123, 0.15); border: 1px solid var(--bvo-gold);">
                <i id="lightboxIcon" class="fa-solid fa-spa text-gold display-3"></i>
            </div>
            <span id="lightboxBadge" class="badge badge-gold mb-2">Sanctuary Room</span>
            <h3 id="lightboxTitle" class="text-white font-playfair mb-3">The Couples Deluxe Suite</h3>
        </div>

        <!-- Description Bar -->
        <div class="p-4 bg-obsidian border-top border-gold-subtle">
            <p id="lightboxDesc" class="text-silver leading-relaxed mb-4">Detailed description goes here...</p>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <span class="text-gold small"><i class="fa-solid fa-shield-halved me-1"></i> Hospital-Grade Sanitized & UV Treated</span>
                <a href="<?= url('booking.php') ?>" class="btn btn-gold btn-sm px-4 fw-bold">
                    <i class="fa-solid fa-calendar-plus me-1"></i> Book Sanctuary Suite
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Interaction Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Filter Tabs
    const tabBtns = document.querySelectorAll('.gallery-tab-btn');
    const cols = document.querySelectorAll('.gallery-col');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');

            cols.forEach(col => {
                const tag = col.getAttribute('data-tag');
                if (filter === 'all' || tag === filter) {
                    col.style.display = 'block';
                } else {
                    col.style.display = 'none';
                }
            });
        });
    });

    // 2. Lightbox Modal
    const lightbox = document.getElementById('galleryLightbox');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxVisual = document.getElementById('lightboxVisual');
    const lightboxIcon = document.getElementById('lightboxIcon');
    const lightboxBadge = document.getElementById('lightboxBadge');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxDesc = document.getElementById('lightboxDesc');
    const cards = document.querySelectorAll('.gallery-card');

    function openLightbox(card) {
        const title = card.getAttribute('data-title');
        const category = card.getAttribute('data-category');
        const desc = card.getAttribute('data-desc');
        const icon = card.getAttribute('data-icon');
        const bg = card.getAttribute('data-bg');

        lightboxTitle.textContent = title;
        lightboxBadge.textContent = category;
        lightboxDesc.textContent = desc;
        lightboxIcon.className = 'fa-solid ' + icon + ' text-gold display-3';
        lightboxVisual.style.background = bg;

        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }

    cards.forEach(card => {
        card.addEventListener('click', function () {
            openLightbox(this);
        });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openLightbox(this);
            }
        });
    });

    lightboxClose.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            closeLightbox();
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
