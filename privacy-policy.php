<?php
/**
 * Bella Vista Oasis Spa - Privacy Policy
 * In compliance with RA 10173 (Philippine Data Privacy Act of 2012)
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Privacy Policy & Data Protection | Bella Vista Oasis Spa';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header Banner -->
<section class="page-header-banner">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb-bvo list-inline justify-content-center">
                <li class="list-inline-item"><a href="<?= url('index.php') ?>">Home</a></li>
                <li class="list-inline-item text-silver">/</li>
                <li class="list-inline-item text-gold active" aria-current="page">Privacy Policy</li>
            </ol>
        </nav>
        <h1 class="display-4 font-playfair text-white mb-2">Privacy & <span class="text-gold">Data Protection</span> Policy</h1>
        <p class="text-silver mx-auto font-inter lead" style="max-width: 650px;">
            Our solemn commitment to protecting your personal identity, biometric health records, and sanctuary confidentiality.
        </p>
        <div class="mt-3">
            <span class="badge badge-gold-outline small">
                <i class="fa-regular fa-calendar-check me-1"></i> Last Updated: November 2026 • Version 2.4
            </span>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="py-5 bg-obsidian">
    <div class="container py-4" style="max-width: 900px;">
        <div class="glass-card p-4 p-md-5 border-gold-subtle leading-relaxed">

            <!-- Introductory Commitment -->
            <div class="mb-5 pb-4 border-bottom border-gold-subtle">
                <h3 class="text-gold font-playfair mb-3">Our Guiding Principle</h3>
                <p class="text-silver mb-0">
                    At <strong>Bella Vista Oasis Spa</strong> ("Bella Vista", "we", "our", or "the Sanctuary"), we believe true relaxation requires complete trust. As a premier luxury wellness establishment operating in the Republic of the Philippines, we hold ourselves to the highest ethical and legal standards regarding the collection, storage, and processing of your personal and sensitive health data, strictly conforming to the <strong>Philippine Data Privacy Act of 2012 (Republic Act No. 10173)</strong>, its Implementing Rules and Regulations (IRR), and all circulars issued by the National Privacy Commission (NPC).
                </p>
            </div>

            <!-- Section 1 -->
            <div class="mb-5">
                <h4 class="text-white font-playfair mb-3">
                    <span class="text-gold me-2">1.</span> Information We Collect
                </h4>
                <p class="text-silver mb-3">
                    To deliver bespoke wellness rituals and safeguard your physical well-being, we collect information across several categories:
                </p>
                <ul class="text-silver small d-flex flex-column gap-2 mb-0">
                    <li>
                        <strong class="text-white">A. Personal Identification Data:</strong> Full name, verified email address, mobile telephone number, date of birth, biological gender, home address, and designated emergency contact coordinates.
                    </li>
                    <li>
                        <strong class="text-white">B. Sensitive Health & Somatic Declarations:</strong> Self-disclosed medical conditions, history of surgeries, cardiovascular status, skin sensitivities and allergies (e.g. nut oils, floral extracts), pregnancy status and gestation weeks, chronic injuries, and targeted musculoskeletal pain zones.
                    </li>
                    <li>
                        <strong class="text-white">C. Reservation & Experience Preferences:</strong> Appointment timestamps, suite selections, preferred therapist assignments, custom pressure preferences, music and room temperature selections, and aromatic oil notes.
                    </li>
                    <li>
                        <strong class="text-white">D. Billing & Transactional Logs:</strong> Payment methods utilized, simulated transaction identifiers, deposit confirmations, receipt registers, and Oasis Club loyalty reward point accruals.
                    </li>
                    <li>
                        <strong class="text-white">E. Technical & Audit Telemetry:</strong> Anonymized session cookies, client IP address, browser user-agent, and cryptographic security tokens for CSRF mitigation.
                    </li>
                </ul>
            </div>

            <!-- Section 2 -->
            <div class="mb-5">
                <h4 class="text-white font-playfair mb-3">
                    <span class="text-gold me-2">2.</span> How We Utilize Your Information
                </h4>
                <p class="text-silver mb-3">
                    Your personal and health information is processed exclusively for legitimate wellness, clinical safety, and administrative purposes, specifically:
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--bvo-gold-border);">
                            <strong class="text-gold d-block mb-1"><i class="fa-solid fa-shield-heart me-1"></i> Health Safety Screening</strong>
                            <p class="text-silver small mb-0">Enabling your therapist to avoid contraindicated acupressure points, adapt body mechanics, and formulate safe organic topicals.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--bvo-gold-border);">
                            <strong class="text-gold d-block mb-1"><i class="fa-solid fa-calendar-check me-1"></i> Appointment Orchestration</strong>
                            <p class="text-silver small mb-0">Delivering reservation confirmations, automated 24-hour pre-arrival reminders, suite preparation, and rescheduling coordination.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--bvo-gold-border);">
                            <strong class="text-gold d-block mb-1"><i class="fa-solid fa-coins me-1"></i> Oasis Club Rewards</strong>
                            <p class="text-silver small mb-0">Accurately calculating loyalty points, issuing personalized birthday privileges, and managing gift vouchers.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--bvo-gold-border);">
                            <strong class="text-gold d-block mb-1"><i class="fa-solid fa-gavel me-1"></i> Legal & Fiscal Compliance</strong>
                            <p class="text-silver small mb-0">Maintaining official BIR-compliant electronic receipt logs and verifying age criteria for specialized facilities.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3 -->
            <div class="mb-5">
                <h4 class="text-white font-playfair mb-3">
                    <span class="text-gold me-2">3.</span> Data Protection & Safeguards
                </h4>
                <p class="text-silver mb-3">
                    We employ defense-in-depth architectural, administrative, and physical security measures to shield your data from unauthorized access, breach, or destruction:
                </p>
                <ul class="text-silver small d-flex flex-column gap-2 mb-0">
                    <li><strong class="text-white">Cryptographic Hashing:</strong> All account credentials utilize strong BCRYPT hashing with auto-generated salts. Passwords are never stored in plaintext.</li>
                    <li><strong class="text-white">Strict Role-Based Access Controls (RBAC):</strong> Concierge staff, therapists, and supervisors access only the minimum necessary data fields required to execute their specific responsibilities.</li>
                    <li><strong class="text-white">Granular Audit Logging:</strong> Every access, profile modification, and health form consultation generates an indelible, timestamped audit trail recording the acting user ID and IP address.</li>
                    <li><strong class="text-white">Transport Layer Security (TLS):</strong> All communications between your client device and our servers are encrypted via modern 256-bit TLS/SSL protocols.</li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div class="mb-5">
                <h4 class="text-white font-playfair mb-3">
                    <span class="text-gold me-2">4.</span> Your Rights under the Philippine Data Privacy Act (RA 10173)
                </h4>
                <p class="text-silver mb-3">
                    As an esteemed data subject under Philippine law, you are endowed with distinct rights which you may exercise at any time:
                </p>
                <div class="d-flex flex-column gap-3">
                    <div class="border-start border-2 border-gold ps-3">
                        <strong class="text-white">Right to be Informed:</strong>
                        <p class="small text-silver mb-0">You have the right to understand how, why, and for what duration your personal data is collected, stored, and utilized.</p>
                    </div>
                    <div class="border-start border-2 border-gold ps-3">
                        <strong class="text-white">Right of Access:</strong>
                        <p class="small text-silver mb-0">You may inspect or request a digital copy of all profile records, historical health declarations, and appointment registries maintained under your account.</p>
                    </div>
                    <div class="border-start border-2 border-gold ps-3">
                        <strong class="text-white">Right to Rectification:</strong>
                        <p class="small text-silver mb-0">You may dispute inaccuracies and request prompt correction of outdated contact or medical declarations.</p>
                    </div>
                    <div class="border-start border-2 border-gold ps-3">
                        <strong class="text-white">Right to Erasure or Blocking:</strong>
                        <p class="small text-silver mb-0">You may request the suspension, withdrawal, or deletion of your personal account, subject to mandatory statutory tax and accounting retention obligations.</p>
                    </div>
                    <div class="border-start border-2 border-gold ps-3">
                        <strong class="text-white">Right to Damages:</strong>
                        <p class="small text-silver mb-0">You have the right to be indemnified for any damages sustained due to inaccurate, incomplete, outdated, false, unlawfully obtained, or unauthorized use of your personal data.</p>
                    </div>
                </div>
            </div>

            <!-- Section 5 -->
            <div class="mb-5">
                <h4 class="text-white font-playfair mb-3">
                    <span class="text-gold me-2">5.</span> Cookie & Session Governance
                </h4>
                <p class="text-silver mb-3">
                    Our web portal utilizes strictly necessary HTTP cookies designed solely to maintain your authenticated guest session, remember your language preferences, and enforce anti-forgery CSRF protections. We do <strong>not</strong> load third-party ad networks, tracking pixels, or behavioral marketing surveillance trackers.
                </p>
            </div>

            <!-- Section 6 -->
            <div class="mb-5">
                <h4 class="text-white font-playfair mb-3">
                    <span class="text-gold me-2">6.</span> Third-Party Disclosure Policy
                </h4>
                <p class="text-silver mb-0">
                    Bella Vista Oasis Spa <strong>never sells, rents, monetizes, or trades</strong> guest records to commercial entities. Third-party data sharing is restricted exclusively to authorized PCI-DSS compliant payment gateways for transaction clearing, or legal mandates compelled by a valid Philippine judicial court order or lawful Department of Health directive.
                </p>
            </div>

            <!-- Section 7 -->
            <div class="mb-5">
                <h4 class="text-white font-playfair mb-3">
                    <span class="text-gold me-2">7.</span> Data Retention Schedule
                </h4>
                <p class="text-silver mb-0">
                    Active customer profiles and loyalty registers are maintained for as long as your account remains open. Health declaration records associated with specific appointments are securely archived for a duration of two (2) years following session completion to comply with wellness industry clinical guidelines, after which they are permanently purged. Financial billing receipts are preserved for five (5) years in alignment with Philippine Bureau of Internal Revenue (BIR) regulations.
                </p>
            </div>

            <!-- Section 8 -->
            <div class="p-4 rounded-3 border border-gold" style="background: rgba(23, 26, 27, 0.95);">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <i class="fa-solid fa-user-shield text-gold fs-2"></i>
                    <div>
                        <h5 class="text-white font-playfair mb-0">8. Privacy Inquiries & Data Protection Officer</h5>
                        <span class="text-silver small">Concierge Privacy Office</span>
                    </div>
                </div>
                <p class="text-silver small leading-relaxed mb-3">
                    To exercise your rights, submit a data subject access request, or report a privacy concern, please contact our designated Data Protection Officer (DPO):
                </p>
                <ul class="list-unstyled text-silver small mb-0">
                    <li class="mb-1"><i class="fa-solid fa-envelope text-gold me-2"></i><strong>Email:</strong> <a href="mailto:dpo@bellavistaspa.com" class="text-gold">dpo@bellavistaspa.com</a></li>
                    <li class="mb-1"><i class="fa-solid fa-phone text-gold me-2"></i><strong>Direct Line:</strong> +63 (02) 8876-5432 (Attn: Data Privacy Office)</li>
                    <li><i class="fa-solid fa-location-dot text-gold me-2"></i><strong>Office:</strong> Bella Vista Oasis Spa, 123 Serenity Boulevard, Vista Heights, Manila, Philippines</li>
                </ul>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
