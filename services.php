<?php
/**
 * Bella Vista Oasis Spa - Services & Treatments
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Services & Sanctuary Rituals | Bella Vista Oasis Spa';

// Attempt to load services from Database using PDO
$services = [];
$dbConnected = false;

try {
    $pdo = get_db_connection();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY id ASC");
        $stmt->execute();
        $services = $stmt->fetchAll();
        $dbConnected = true;
    }
} catch (Exception $e) {
    error_log("Services DB Load Failed: " . $e->getMessage());
    $services = [];
    $dbConnected = false;
}

// Fallback hardcoded services if DB is not connected or returns empty
if (empty($services)) {
    $services = [
        [
            'id' => 1,
            'name' => 'Single Room Massage',
            'category' => 'Signature Massage',
            'description' => 'Immerse in holistic rejuvenation tailored specifically for your body. Our certified therapists utilize cold-pressed virgin coconut and therapeutic botanical oils to soothe muscle tension, enhance circulation, and calm your senses.',
            'duration_minutes' => 60,
            'base_price' => 800.00,
            'max_guests' => 1,
            'additional_guest_fee' => 0.00,
            'room_type' => 'single'
        ],
        [
            'id' => 2,
            'name' => 'Couples Suite Retreat',
            'category' => 'Couples Wellness',
            'description' => 'An intimate sanctuary designed for two. Experience synchronized aromatherapy full-body massages side-by-side in our private couples suite, complemented with warm floral foot rituals, organic herbal tea, and ambient candlelight.',
            'duration_minutes' => 90,
            'base_price' => 1800.00,
            'max_guests' => 2,
            'additional_guest_fee' => 850.00,
            'room_type' => 'couples'
        ],
        [
            'id' => 3,
            'name' => 'Family Lounge Gathering',
            'category' => 'Group & Family',
            'description' => 'A shared luxury experience for families or small groups of up to 4 guests (accommodates up to 6 with additional guest fee). Includes personalized massages, herbal steam therapy, reflexology, and artisanal refreshments in our expansive royal suite lounge.',
            'duration_minutes' => 90,
            'base_price' => 4000.00,
            'max_guests' => 6,
            'additional_guest_fee' => 750.00,
            'room_type' => 'family'
        ],
        [
            'id' => 4,
            'name' => 'Aromatherapy Deep Tissue Release',
            'category' => 'Therapeutic Specialty',
            'description' => 'Targeted firm pressure and slow myofascial strokes combined with custom botanical oils to release chronic muscle tightness, relieve back stiffness, and restore natural posture.',
            'duration_minutes' => 90,
            'base_price' => 1200.00,
            'max_guests' => 1,
            'additional_guest_fee' => 0.00,
            'room_type' => 'single'
        ],
        [
            'id' => 5,
            'name' => 'Himalayan Hot Stone Indulgence',
            'category' => 'Thermal Healing',
            'description' => 'Smooth heated river basalt and pink Himalayan mineral stones placed gently on key chakra energy meridians, paired with rhythmic long strokes to dissolve deep physical stress.',
            'duration_minutes' => 75,
            'base_price' => 1400.00,
            'max_guests' => 1,
            'additional_guest_fee' => 0.00,
            'room_type' => 'single'
        ],
        [
            'id' => 6,
            'name' => 'Ventosa Cupping & Traditional Hilot',
            'category' => 'Traditional Holistic',
            'description' => 'An ancient Filipino healing art combining thermal glass suction cups and heated banana leaf strips with warm virgin coconut oil to release trapped body chills (lamig) and align energy flow.',
            'duration_minutes' => 75,
            'base_price' => 1100.00,
            'max_guests' => 1,
            'additional_guest_fee' => 0.00,
            'room_type' => 'single'
        ]
    ];
}

// Extract distinct categories for filter buttons
$categories = array_unique(array_column($services, 'category'));
sort($categories);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header Banner -->
<section class="page-header-banner">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb-bvo list-inline justify-content-center">
                <li class="list-inline-item"><a href="<?= url('index.php') ?>">Home</a></li>
                <li class="list-inline-item text-silver">/</li>
                <li class="list-inline-item text-gold active" aria-current="page">Services & Treatments</li>
            </ol>
        </nav>
        <h1 class="display-4 font-playfair text-white mb-2">Sanctuary Rituals & <span class="text-gold">Suites</span></h1>
        <p class="text-silver mx-auto font-inter lead" style="max-width: 650px;">
            Every therapy is an unhurried, multisensory journey curated for deep physical restoration and spiritual stillness.
        </p>
    </div>
</section>

<!-- Services Main Section -->
<section class="py-5 bg-obsidian">
    <div class="container py-4">

        <!-- Category Filter Buttons -->
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
            <button type="button" class="btn btn-outline-gold active category-filter-btn px-4 py-2" data-filter="all">
                All Treatments
            </button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="btn btn-outline-gold category-filter-btn px-4 py-2" data-filter="<?= htmlspecialchars($cat, ENT_QUOTES) ?>">
                    <?= htmlspecialchars($cat, ENT_QUOTES) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Services Grid -->
        <div class="row g-4" id="servicesGrid">
            <?php foreach ($services as $s): 
                $roomLabel = 'Single Private Suite';
                $roomBadgeClass = 'badge-gold';
                $roomTypeRaw = strtolower($s['room_type'] ?? 'single');
                if (str_contains($roomTypeRaw, 'couple')) {
                    $roomLabel = 'Couples Deluxe Suite';
                    $roomBadgeClass = 'badge-gold';
                } elseif (str_contains($roomTypeRaw, 'family')) {
                    $roomLabel = 'Royal Family Lounge';
                    $roomBadgeClass = 'badge-gold';
                }

                $bookUrl = is_logged_in() 
                    ? url('booking.php?service_id=' . $s['id'])
                    : url('login.php?redirect=' . urlencode('booking.php?service_id=' . $s['id']));
                
                $priceDisplay = format_currency($s['base_price'] ?? $s['price'] ?? 0);
                $duration = (int)($s['duration_minutes'] ?? 60);
                $maxGuests = (int)($s['max_guests'] ?? 1);
                $addFee = (float)($s['additional_guest_fee'] ?? 0.00);
            ?>
                <div class="col-lg-4 col-md-6 service-item" data-category="<?= htmlspecialchars($s['category'], ENT_QUOTES) ?>" id="service-<?= $s['id'] ?>">
                    <div class="glass-card service-card h-100">
                        <div class="card-body">
                            <!-- Category & Room Badge -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge badge-gold-outline small text-uppercase font-inter">
                                    <?= htmlspecialchars($s['category'], ENT_QUOTES) ?>
                                </span>
                                <span class="badge <?= $roomBadgeClass ?> small">
                                    <i class="fa-solid fa-door-closed me-1"></i> <?= $roomLabel ?>
                                </span>
                            </div>

                            <!-- Service Name -->
                            <h4 class="text-white font-playfair mb-2">
                                <?= htmlspecialchars($s['name'], ENT_QUOTES) ?>
                            </h4>

                            <!-- Price & Duration Bar -->
                            <div class="d-flex align-items-baseline gap-2 mb-3">
                                <span class="card-price"><?= $priceDisplay ?></span>
                                <span class="text-silver small">/ <?= $duration ?> mins</span>
                            </div>

                            <!-- Description -->
                            <p class="text-silver small leading-relaxed mb-4 flex-grow-1">
                                <?= htmlspecialchars($s['description'] ?? '', ENT_QUOTES) ?>
                            </p>

                            <!-- Specifications / Details -->
                            <div class="d-flex flex-column gap-2 mb-4 pt-3 border-top border-gold-subtle">
                                <div class="d-flex justify-content-between small text-silver">
                                    <span><i class="fa-regular fa-clock text-gold me-2"></i>Duration:</span>
                                    <strong class="text-white"><?= $duration ?> Minutes</strong>
                                </div>
                                <div class="d-flex justify-content-between small text-silver">
                                    <span><i class="fa-solid fa-users text-gold me-2"></i>Max Guests:</span>
                                    <strong class="text-white">Up to <?= $maxGuests ?> Guest<?= $maxGuests > 1 ? 's' : '' ?></strong>
                                </div>
                                <?php if ($addFee > 0): ?>
                                    <div class="d-flex justify-content-between small text-silver">
                                        <span><i class="fa-solid fa-user-plus text-gold me-2"></i>Additional Guest:</span>
                                        <strong class="text-gold"><?= format_currency($addFee) ?> / person</strong>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between small text-silver">
                                    <span><i class="fa-solid fa-sparkles text-gold me-2"></i>Hygiene Buffer:</span>
                                    <strong class="text-white">30-min UV Deep Clean</strong>
                                </div>
                            </div>

                            <!-- Action CTA -->
                            <div class="mt-auto">
                                <a href="<?= $bookUrl ?>" class="btn btn-gold w-100 fw-bold">
                                    <i class="fa-solid fa-calendar-check me-2"></i> Book This Ritual
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Custom Request Notice -->
        <div class="glass-card mt-5 p-4 text-center border-gold-subtle">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div class="text-md-start">
                    <h5 class="text-gold font-playfair mb-1">Seeking a Tailored Group or Corporate Wellness Package?</h5>
                    <p class="text-silver small mb-0">Our private concierge can customize full-day buyouts, bridal sanctuaries, and bespoke aromatics.</p>
                </div>
                <a href="<?= url('contact.php') ?>" class="btn btn-outline-gold px-4 py-2 text-nowrap">
                    <i class="fa-solid fa-envelope me-2"></i> Inquire with Concierge
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterButtons = document.querySelectorAll('.category-filter-btn');
    const serviceItems = document.querySelectorAll('.service-item');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filterValue = this.getAttribute('data-filter');

            serviceItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                if (filterValue === 'all' || itemCategory === filterValue) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
