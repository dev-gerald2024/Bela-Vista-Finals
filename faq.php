<?php
/**
 * Bella Vista Oasis Spa - Frequently Asked Questions (FAQ)
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Frequently Asked Questions & Etiquette | Bella Vista Oasis Spa';
require_once __DIR__ . '/includes/header.php';

$faqs = [
    [
        'category' => 'booking',
        'q' => 'How do I reserve a sanctuary appointment online?',
        'a' => 'Reserving your experience is effortless through our digital portal. Navigate to <a href="' . url('booking.php') . '" class="text-gold">Online Reservation</a>, select your desired ritual and suite type, choose your preferred therapist (or allow our concierge to match you with a master practitioner), pick your date and time, and confirm your details. You will receive an immediate confirmation email and calendar invitation.'
    ],
    [
        'category' => 'booking',
        'q' => 'What is the cancellation policy and timeline?',
        'a' => 'We understand that unexpected obligations arise. You may cancel or amend your appointment without penalty up to <strong>6 hours before</strong> your scheduled start time through your Guest Sanctuary account or by contacting concierge directly. Cancellations made within less than 6 hours or failure to arrive (no-show) will forfeit any advance downpayment to honor our dedicated therapists’ reserved time and suite sterilization preparation.'
    ],
    [
        'category' => 'booking',
        'q' => 'How can I reschedule an existing reservation?',
        'a' => 'If you are at least 6 hours away from your reserved time, simply sign in to your guest account, visit "My Appointments", and click "Reschedule" to select an alternative date or slot. Alternatively, contact our concierge at +63 (02) 8876-5432 and our team will adjust your booking seamlessly.'
    ],
    [
        'category' => 'treatments',
        'q' => 'What signature therapies and rituals are offered?',
        'a' => 'We offer an array of curated restorative services including our Single Room Massage (₱800 / 60 mins), Couples Suite Retreat (₱1,800 / 90 mins for two), Family Lounge Gathering (₱4,000 / 90 mins for 4-6 guests), Aromatherapy Deep Tissue Release, Himalayan Hot Stone Indulgence, Ventosa Cupping, and Traditional Philippine Hilot using warm virgin coconut oil and heated banana leaves.'
    ],
    [
        'category' => 'pricing',
        'q' => 'How are your treatments priced, and are there hidden taxes or charges?',
        'a' => 'All displayed prices on our website and menus are fully inclusive of luxury amenities: welcome floral foot bath, private suite usage, post-treatment organic herbal tea, and all applicable government taxes. Gratuities are completely at your personal discretion.'
    ],
    [
        'category' => 'pricing',
        'q' => 'Are there additional guest fees for suites?',
        'a' => 'Our Single Private Suite is dedicated to 1 guest. The Couples Suite base price includes 2 guests. Our Family Lounge base price covers up to 4 guests, and can comfortably accommodate up to 6 guests with an additional guest fee of ₱750 per person (which includes full massage therapy, towels, and tea ceremony).'
    ],
    [
        'category' => 'policies',
        'q' => 'What are your operating hours and days?',
        'a' => 'Bella Vista Oasis Spa is open <strong>7 days a week, Monday through Sunday, from 1:00 PM to 9:00 PM</strong>. The last booking slot begins at 7:30 PM to allow for unhurried 90-minute rituals prior to closing.'
    ],
    [
        'category' => 'policies',
        'q' => 'Why must I arrive 15 minutes before my appointment time?',
        'a' => 'Arriving 15 minutes early allows you to transition unhurriedly into a meditative state. You will be welcomed with a refreshing cold towel, complete your brief pre-arrival health declaration, choose your preferred essential oils, and enjoy our signature lavender floral foot soak before entering your suite.'
    ],
    [
        'category' => 'policies',
        'q' => 'What happens if I arrive late for my scheduled session?',
        'a' => 'To respect the schedule of subsequent guests and maintain our strict 30-minute hospital-grade sterilization gap between sessions, your treatment will conclude at the originally scheduled end time. The full appointment fee will still apply.'
    ],
    [
        'category' => 'health',
        'q' => 'Why is a digital health declaration required for every guest?',
        'a' => 'Your somatic safety is our paramount responsibility. Our health declaration identifies acute injuries, spinal surgeries, cardiovascular conditions, skin sensitivities, or pregnancy. This ensures our therapists can modify pressure points, avoid contraindicated muscle groups, and customize oil blends specifically for your physical well-being.'
    ],
    [
        'category' => 'health',
        'q' => 'Can I receive treatments if I am pregnant?',
        'a' => 'Yes, after your first trimester (13+ weeks). We offer specialized Prenatal Gentle Flow rituals using supportive pregnancy bolsters, light lymphatic strokes, and unscented cold-pressed oils. We require physician clearance and do not perform deep abdominal or intense acupressure work during pregnancy.'
    ],
    [
        'category' => 'payments',
        'q' => 'What payment methods do you accept?',
        'a' => 'We accept all major credit and debit cards (Visa, MasterCard, American Express), digital wallets (GCash, Maya), bank transfers, and cash upon arrival. Online bookings may require a simulated downpayment to confirm suite availability.'
    ],
    [
        'category' => 'payments',
        'q' => 'Is a downpayment or deposit required to book?',
        'a' => 'For individual Single Suite bookings, reservations are confirmed with a nominal deposit or credit card pre-authorization. For Couples Suites and Family Lounge packages, a downpayment (or full advance settlement) confirms the reservation and reserves the master therapists exclusively for your party.'
    ],
    [
        'category' => 'payments',
        'q' => 'How does the Oasis Club Loyalty Points program work?',
        'a' => 'Every registered guest is automatically enrolled in the Oasis Club. You earn 50 welcome points immediately upon account creation, plus 1 loyalty point for every ₱50 spent on sanctuary treatments. Points can be redeemed for complimentary upgrades (such as Hot Stone add-ons, Himalayan scrubs, or discount vouchers).'
    ],
    [
        'category' => 'payments',
        'q' => 'Do you offer Gift Certificates and Corporate Vouchers?',
        'a' => 'Yes. Digital and hand-calligraphed luxury gift certificates are available for specific rituals or cash denominations. Each certificate carries a unique security code redeemable during online checkout or at our front desk concierge. Gift certificates are valid for 12 months from the date of issue.'
    ],
    [
        'category' => 'policies',
        'q' => 'How is my private medical and personal information protected?',
        'a' => 'We adhere strictly to the Republic Act No. 10173 (Philippine Data Privacy Act of 2012). Your personal contact details and confidential health declarations are stored in an encrypted database accessible only by authorized concierge and your designated therapist during your active treatment session.'
    ],
    [
        'category' => 'policies',
        'q' => 'What should I wear or bring to my appointment?',
        'a' => 'Simply bring yourself in comfortable clothing. Upon arrival, you will be escorted to your private suite where plush microfiber robes, disposable hygienic undergarments, and cushioned slippers are provided. Secure lockers with digital locks are available inside every suite for your personal items.'
    ],
    [
        'category' => 'treatments',
        'q' => 'Can I request a specific preferred therapist or gender?',
        'a' => 'Yes! During online reservation or when speaking with concierge, you may specify your preferred therapist (e.g. Master Elena or Master Marco) or designate your preference for a female or male practitioner. We will do our utmost to accommodate your request based on shift roster availability.'
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
                <li class="list-inline-item text-gold active" aria-current="page">FAQ & Etiquette</li>
            </ol>
        </nav>
        <h1 class="display-4 font-playfair text-white mb-2">Frequently Asked <span class="text-gold">Questions</span></h1>
        <p class="text-silver mx-auto font-inter lead" style="max-width: 650px;">
            Comprehensive answers to your questions regarding sanctuary etiquette, booking terms, safety protocols, and curated experiences.
        </p>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-obsidian">
    <div class="container py-4" style="max-width: 960px;">

        <!-- Live Search Bar -->
        <div class="glass-card p-4 mb-4 border-gold-subtle">
            <div class="row g-3 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-gold" style="border-color: rgba(156, 154, 150, 0.3);">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="faqSearchInput" class="form-control border-start-0 ps-0" placeholder="Search questions (e.g. cancellation, couples, health, loyalty)..." aria-label="Search FAQs">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="d-flex flex-wrap gap-1 justify-content-md-end">
                        <button type="button" class="btn btn-outline-gold btn-sm active faq-pill" data-cat="all">All</button>
                        <button type="button" class="btn btn-outline-gold btn-sm faq-pill" data-cat="booking">Booking</button>
                        <button type="button" class="btn btn-outline-gold btn-sm faq-pill" data-cat="treatments">Rituals</button>
                        <button type="button" class="btn btn-outline-gold btn-sm faq-pill" data-cat="policies">Policies</button>
                        <button type="button" class="btn btn-outline-gold btn-sm faq-pill" data-cat="payments">Payments</button>
                        <button type="button" class="btn btn-outline-gold btn-sm faq-pill" data-cat="health">Health</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Results Warning -->
        <div id="noFaqResults" class="glass-card text-center py-5 d-none mb-4">
            <i class="fa-solid fa-circle-question text-gold display-4 mb-3"></i>
            <h5 class="text-white font-playfair">No matching questions found</h5>
            <p class="text-silver small mb-3">Try different keywords or contact our private concierge directly.</p>
            <a href="<?= url('contact.php') ?>" class="btn btn-gold btn-sm px-4">Contact Concierge</a>
        </div>

        <!-- Accordion FAQ -->
        <div class="accordion accordion-luxury" id="faqAccordion">
            <?php foreach ($faqs as $index => $faq): 
                $collapseId = 'faqCollapse_' . $index;
                $headingId = 'faqHeading_' . $index;
            ?>
                <div class="accordion-item faq-item" data-category="<?= $faq['category'] ?>">
                    <h2 class="accordion-header" id="<?= $headingId ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                            <span class="faq-question-text"><?= htmlspecialchars($faq['q'], ENT_QUOTES) ?></span>
                        </button>
                    </h2>
                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse" aria-labelledby="<?= $headingId ?>" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?= $faq['a'] ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Still have questions card -->
        <div class="glass-card mt-5 p-4 text-center border-gold">
            <div class="row align-items-center g-3">
                <div class="col-md-8 text-md-start">
                    <h5 class="text-white font-playfair mb-1">Have an inquiry not answered above?</h5>
                    <p class="text-silver small mb-0">Our dedicated spa concierge is available daily between 1:00 PM and 9:00 PM to assist with special arrangements.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="<?= url('contact.php') ?>" class="btn btn-gold px-4 py-2 shadow-gold fw-bold">
                        <i class="fa-solid fa-headset me-2"></i> Inquire with Concierge
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Interactive Live FAQ Search & Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('faqSearchInput');
    const pills = document.querySelectorAll('.faq-pill');
    const items = document.querySelectorAll('.faq-item');
    const noResults = document.getElementById('noFaqResults');

    let currentCategory = 'all';

    function filterFaqs() {
        const query = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        items.forEach(item => {
            const cat = item.getAttribute('data-category');
            const qText = item.querySelector('.accordion-button').textContent.toLowerCase();
            const aText = item.querySelector('.accordion-body').textContent.toLowerCase();

            const matchesCategory = (currentCategory === 'all' || cat === currentCategory);
            const matchesQuery = (!query || qText.includes(query) || aText.includes(query));

            if (matchesCategory && matchesQuery) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    }

    searchInput.addEventListener('input', filterFaqs);

    pills.forEach(pill => {
        pill.addEventListener('click', function () {
            pills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.getAttribute('data-cat');
            filterFaqs();
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
