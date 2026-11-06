<?php
/**
 * Bella Vista Oasis Spa - Contact Us & Private Concierge
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Concierge & Inquiries | Bella Vista Oasis Spa';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header Banner -->
<section class="page-header-banner">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb-bvo list-inline justify-content-center">
                <li class="list-inline-item"><a href="<?= url('index.php') ?>">Home</a></li>
                <li class="list-inline-item text-silver">/</li>
                <li class="list-inline-item text-gold active" aria-current="page">Contact Concierge</li>
            </ol>
        </nav>
        <h1 class="display-4 font-playfair text-white mb-2">Private <span class="text-gold">Concierge</span></h1>
        <p class="text-silver mx-auto font-inter lead" style="max-width: 650px;">
            Whether arranging a bespoke ritual, requesting corporate buyouts, or clarifying health protocols, our concierge is at your service.
        </p>
    </div>
</section>

<!-- Contact Info Cards & Form Section -->
<section class="py-5 bg-obsidian">
    <div class="container py-4">

        <!-- Info Cards Row -->
        <div class="row g-4 mb-5">
            <!-- Phone Card -->
            <div class="col-lg-3 col-md-6">
                <div class="glass-card text-center h-100 p-4">
                    <div class="p-3 rounded-circle d-inline-flex mb-3" style="background: rgba(214, 183, 123, 0.12); border: 1px solid var(--bvo-gold-border);">
                        <i class="fa-solid fa-phone text-gold fs-3"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Telephone</h5>
                    <p class="text-silver small mb-1">Direct Landline & VIP Mobile</p>
                    <a href="tel:+63288765432" class="text-gold fw-bold d-block mb-1 text-decoration-none">+63 (02) 8876-5432</a>
                    <a href="tel:+639171000000" class="text-silver small text-decoration-none">+63 917 100 0000</a>
                </div>
            </div>

            <!-- Email Card -->
            <div class="col-lg-3 col-md-6">
                <div class="glass-card text-center h-100 p-4">
                    <div class="p-3 rounded-circle d-inline-flex mb-3" style="background: rgba(214, 183, 123, 0.12); border: 1px solid var(--bvo-gold-border);">
                        <i class="fa-solid fa-envelope text-gold fs-3"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Electronic Mail</h5>
                    <p class="text-silver small mb-1">Concierge & Guest Support</p>
                    <a href="mailto:concierge@bellavistaspa.com" class="text-gold fw-bold d-block mb-1 text-decoration-none text-truncate">concierge@bellavistaspa.com</a>
                    <span class="text-silver small">Responses within 2 business hours</span>
                </div>
            </div>

            <!-- Address Card -->
            <div class="col-lg-3 col-md-6">
                <div class="glass-card text-center h-100 p-4">
                    <div class="p-3 rounded-circle d-inline-flex mb-3" style="background: rgba(214, 183, 123, 0.12); border: 1px solid var(--bvo-gold-border);">
                        <i class="fa-solid fa-location-dot text-gold fs-3"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Sanctuary Address</h5>
                    <p class="text-silver small mb-1">Manila & Tagaytay, Philippines</p>
                    <span class="text-white small d-block">123 Serenity Boulevard, Vista Heights</span>
                    <span class="text-silver small">Valet Parking Available</span>
                </div>
            </div>

            <!-- Operating Hours Card -->
            <div class="col-lg-3 col-md-6">
                <div class="glass-card text-center h-100 p-4">
                    <div class="p-3 rounded-circle d-inline-flex mb-3" style="background: rgba(214, 183, 123, 0.12); border: 1px solid var(--bvo-gold-border);">
                        <i class="fa-regular fa-clock text-gold fs-3"></i>
                    </div>
                    <h5 class="text-white font-playfair mb-2">Sanctuary Hours</h5>
                    <p class="text-silver small mb-1">Monday through Sunday</p>
                    <strong class="text-gold d-block mb-1">1:00 PM – 9:00 PM</strong>
                    <span class="text-silver small">Advance Reservation Required</span>
                </div>
            </div>
        </div>

        <div class="row g-5">
            <!-- Contact / Inquiry Form -->
            <div class="col-lg-7">
                <div class="glass-card p-4 p-md-5 border border-gold-subtle">
                    <div class="mb-4">
                        <span class="badge badge-gold mb-2">Direct Inquiry</span>
                        <h3 class="text-white font-playfair mb-2">Send an Inquiry to Concierge</h3>
                        <p class="text-silver small">
                            Have a specialized request or need assistance? Please provide your details below and our concierge team will reach out promptly.
                        </p>
                    </div>

                    <form action="<?= url('process/submit_inquiry.php') ?>" method="POST" class="needs-validation">
                        <?= csrf_token_field() ?>

                        <div class="row g-3">
                            <!-- Name -->
                            <div class="col-md-6">
                                <label for="inquiry_name" class="form-label">Full Name <span class="text-gold">*</span></label>
                                <input type="text" class="form-control" id="inquiry_name" name="name" required placeholder="e.g. Maria Santos" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES) ?>">
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="inquiry_email" class="form-label">Email Address <span class="text-gold">*</span></label>
                                <input type="email" class="form-control" id="inquiry_email" name="email" required placeholder="e.g. maria@example.com" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES) ?>">
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label for="inquiry_phone" class="form-label">Contact Phone</label>
                                <input type="tel" class="form-control" id="inquiry_phone" name="phone" placeholder="e.g. +63 917 123 4567" value="<?= htmlspecialchars($_SESSION['phone'] ?? '', ENT_QUOTES) ?>">
                            </div>

                            <!-- Subject -->
                            <div class="col-md-6">
                                <label for="inquiry_subject" class="form-label">Inquiry Subject <span class="text-gold">*</span></label>
                                <select class="form-select" id="inquiry_subject" name="subject" required>
                                    <option value="" disabled selected>Select Subject...</option>
                                    <option value="Booking & Reservation Inquiry">Booking & Reservation Inquiry</option>
                                    <option value="Couples / Family Suite Packages">Couples / Family Suite Packages</option>
                                    <option value="Private Buyout & Group Events">Private Buyout & Group Events</option>
                                    <option value="Health & Medical Declaration Questions">Health & Medical Declaration Questions</option>
                                    <option value="Gift Certificates & Loyalty Program">Gift Certificates & Loyalty Program</option>
                                    <option value="General Feedback & Compliments">General Feedback & Compliments</option>
                                </select>
                            </div>

                            <!-- Message -->
                            <div class="col-12">
                                <label for="inquiry_message" class="form-label">Message Details <span class="text-gold">*</span></label>
                                <textarea class="form-control" id="inquiry_message" name="message" rows="5" required placeholder="Please describe how we may tailor your sanctuary experience or answer your inquiry..."></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-gold btn-lg w-100 fw-bold shadow-gold">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Transmit Message to Concierge
                                </button>
                                <p class="text-silver small text-center mt-2 mb-0">
                                    <i class="fa-solid fa-lock text-gold me-1"></i> Your details are strictly protected under our Data Privacy Policy.
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Map & Sanctuary Location Column -->
            <div class="col-lg-5">
                <div class="d-flex flex-column gap-4 h-100">
                    <!-- Styled Dark Map Container -->
                    <div class="glass-card p-3 border border-gold-subtle flex-grow-1 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3 px-2 pt-2">
                            <div>
                                <h5 class="text-white font-playfair mb-0">Estate Location</h5>
                                <span class="text-silver small">Bella Vista Oasis Spa, Manila, Philippines</span>
                            </div>
                            <i class="fa-solid fa-map-location-dot text-gold fs-3"></i>
                        </div>

                        <!-- Stylized Interactive Dark Graphic Map -->
                        <div class="rounded-3 flex-grow-1 p-4 d-flex flex-column align-items-center justify-content-center text-center" 
                             style="min-height: 280px; background: radial-gradient(circle at center, rgba(214, 183, 123, 0.12) 0%, rgba(16, 19, 19, 0.98) 75%), linear-gradient(135deg, #171A1B 0%, #101313 100%); border: 1px dashed rgba(214, 183, 123, 0.3);">
                            <div class="p-3 rounded-circle mb-3 animate-pulse" style="background: rgba(214, 183, 123, 0.18); border: 2px solid var(--bvo-gold); box-shadow: 0 0 20px rgba(214, 183, 123, 0.35);">
                                <i class="fa-solid fa-spa text-gold fs-2"></i>
                            </div>
                            <h5 class="text-white font-playfair mb-1">Bella Vista Oasis Spa</h5>
                            <p class="text-gold small mb-2">123 Serenity Boulevard, Vista Heights, Manila</p>
                            <span class="text-silver small mb-3">Coordinates: 14.5995° N, 120.9842° E</span>
                            <div class="d-flex gap-2">
                                <a href="https://maps.google.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-gold btn-sm">
                                    <i class="fa-solid fa-diamond-turn-right me-1"></i> Open Google Maps
                                </a>
                                <a href="https://waze.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-gold btn-sm">
                                    <i class="fa-solid fa-car me-1"></i> Open Waze
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Arrival Protocol Note -->
                    <div class="glass-card p-4 border-gold-subtle">
                        <div class="d-flex gap-3">
                            <div class="text-gold mt-1"><i class="fa-solid fa-circle-exclamation fs-4"></i></div>
                            <div>
                                <h6 class="text-white font-playfair mb-1">Arrival Protocol</h6>
                                <p class="text-silver small mb-0">
                                    Please plan to arrive <strong>15 minutes prior</strong> to your scheduled time. This ensures ample leisure to stow personal belongings, complete health forms, and enjoy your organic herbal foot ritual.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
