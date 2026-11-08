<?php
require_once 'config/app.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
$page_title = 'Terms & Conditions';
require_once 'includes/header.php';
?>

<section class="page-header text-center py-5" style="background: linear-gradient(135deg, rgba(16,19,19,0.95), rgba(23,26,27,0.95));">
    <div class="container">
        <h1 class="display-4 font-heading text-gold mb-3">Terms & Conditions</h1>
        <p class="text-muted">Last Updated: November 2026</p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center bg-transparent">
                <li class="breadcrumb-item"><a href="index.php" class="text-gold">Home</a></li>
                <li class="breadcrumb-item active text-muted">Terms & Conditions</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5">
    <div class="container" style="max-width: 900px;">
        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">1. Acceptance of Terms</h2>
            <p>By accessing the Bella Vista Oasis Spa website, creating an account, or booking any service, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, please refrain from using our services.</p>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">2. Booking Terms</h2>
            <h5 class="text-gold mb-3">2.1 Appointment Booking</h5>
            <ul class="list-unstyled">
                <li class="mb-2"><i class="fas fa-gem text-gold me-2"></i>All appointments must be booked through our online portal or by contacting our front desk.</li>
                <li class="mb-2"><i class="fas fa-gem text-gold me-2"></i>Bookings are subject to availability of rooms, therapists, and time slots.</li>
                <li class="mb-2"><i class="fas fa-gem text-gold me-2"></i>A booking is considered confirmed only after a downpayment has been received and processed.</li>
                <li class="mb-2"><i class="fas fa-gem text-gold me-2"></i>You will receive a confirmation notification once your booking is confirmed.</li>
            </ul>

            <h5 class="text-gold mb-3 mt-4">2.2 Operating Hours</h5>
            <p>Bella Vista Oasis Spa operates daily, Monday through Sunday, from <strong class="text-gold">1:00 PM to 9:00 PM</strong>. The last appointment slot must allow sufficient time for the full treatment plus a 30-minute post-session cleaning and sanitization period before closing.</p>

            <h5 class="text-gold mb-3 mt-4">2.3 Customer Arrival</h5>
            <p>Customers are expected to arrive at least <strong class="text-gold">15 minutes</strong> before their scheduled appointment time for check-in, preparation, and any necessary health declaration review. Late arrivals may result in a shortened treatment session to accommodate subsequent bookings. No refund or discount will be provided for shortened sessions due to late arrival.</p>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">3. Service Pricing</h2>
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th class="text-gold">Service</th>
                            <th class="text-gold">Price</th>
                            <th class="text-gold">Duration</th>
                            <th class="text-gold">Capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Single Room Massage</td>
                            <td>₱800</td>
                            <td>60 minutes</td>
                            <td>1 person</td>
                        </tr>
                        <tr>
                            <td>Couples Suite</td>
                            <td>₱1,800</td>
                            <td>60 minutes</td>
                            <td>2 persons</td>
                        </tr>
                        <tr>
                            <td>Family Lounge</td>
                            <td>₱4,000</td>
                            <td>90 minutes</td>
                            <td>Up to 4 persons</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3">Additional guests for the Family Lounge: <strong class="text-gold">₱750 per additional guest</strong>, up to a maximum of 6 guests total. Prices are subject to change without prior notice. All prices are in Philippine Pesos (₱).</p>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">4. Cancellation Policy</h2>
            <ul class="list-unstyled">
                <li class="mb-2"><i class="fas fa-exclamation-triangle text-gold me-2"></i>Cancellations must be made at least <strong class="text-gold">6 hours</strong> before the scheduled appointment time.</li>
                <li class="mb-2"><i class="fas fa-exclamation-triangle text-gold me-2"></i>Cancellations made within the 6-hour window may result in <strong>forfeiture of the downpayment</strong>.</li>
                <li class="mb-2"><i class="fas fa-exclamation-triangle text-gold me-2"></i>No-shows (failure to arrive for a confirmed appointment) will result in full forfeiture of the downpayment.</li>
                <li class="mb-2"><i class="fas fa-exclamation-triangle text-gold me-2"></i>Refunds for eligible cancellations will be processed according to the original payment method.</li>
            </ul>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">5. Rescheduling Policy</h2>
            <ul class="list-unstyled">
                <li class="mb-2"><i class="fas fa-calendar-alt text-gold me-2"></i>Rescheduling requests must be made at least <strong class="text-gold">6 hours</strong> before the scheduled appointment.</li>
                <li class="mb-2"><i class="fas fa-calendar-alt text-gold me-2"></i>Rescheduled appointments are subject to availability.</li>
                <li class="mb-2"><i class="fas fa-calendar-alt text-gold me-2"></i>Rescheduling within the 6-hour window is treated as a late cancellation and may result in downpayment forfeiture.</li>
            </ul>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">6. Health Declaration</h2>
            <p>Customers are required to complete a health declaration form as part of the booking process. This includes disclosure of:</p>
            <ul class="list-unstyled">
                <li class="mb-2"><i class="fas fa-heartbeat text-gold me-2"></i>Existing medical conditions</li>
                <li class="mb-2"><i class="fas fa-heartbeat text-gold me-2"></i>Current injuries or physical limitations</li>
                <li class="mb-2"><i class="fas fa-heartbeat text-gold me-2"></i>Pregnancy status and weeks of pregnancy</li>
                <li class="mb-2"><i class="fas fa-heartbeat text-gold me-2"></i>Known allergies to products, oils, or fragrances</li>
            </ul>
            <p class="mt-3">This information is kept strictly confidential, accessible only to the assigned therapist and authorized medical-review personnel. Failure to disclose relevant health conditions may release Bella Vista Oasis Spa from liability for any adverse reactions.</p>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">7. Payment Terms</h2>
            <ul class="list-unstyled">
                <li class="mb-2"><i class="fas fa-credit-card text-gold me-2"></i>A downpayment is required to confirm a booking.</li>
                <li class="mb-2"><i class="fas fa-credit-card text-gold me-2"></i>The remaining balance must be settled on or before the appointment date.</li>
                <li class="mb-2"><i class="fas fa-credit-card text-gold me-2"></i>Accepted payment methods include Cash, GCash, Maya, Credit Card, Debit Card, and Bank Transfer.</li>
                <li class="mb-2"><i class="fas fa-credit-card text-gold me-2"></i>All payments in the online portal are simulated for demonstration purposes; no real monetary transactions are processed.</li>
            </ul>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">8. Customer Responsibilities</h2>
            <ul class="list-unstyled">
                <li class="mb-2"><i class="fas fa-user-check text-gold me-2"></i>Maintain the confidentiality of your account credentials.</li>
                <li class="mb-2"><i class="fas fa-user-check text-gold me-2"></i>Provide accurate personal and health information.</li>
                <li class="mb-2"><i class="fas fa-user-check text-gold me-2"></i>Treat our staff, therapists, and facilities with respect.</li>
                <li class="mb-2"><i class="fas fa-user-check text-gold me-2"></i>Follow all spa rules, safety guidelines, and instructions from staff.</li>
                <li class="mb-2"><i class="fas fa-user-check text-gold me-2"></i>Report any issues, concerns, or accidents to staff immediately.</li>
            </ul>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">9. Spa Policies</h2>
            <ul class="list-unstyled">
                <li class="mb-2"><i class="fas fa-spa text-gold me-2"></i>Please maintain a quiet and relaxing atmosphere during your visit.</li>
                <li class="mb-2"><i class="fas fa-spa text-gold me-2"></i>Mobile phones must be set to silent mode inside treatment areas.</li>
                <li class="mb-2"><i class="fas fa-spa text-gold me-2"></i>The spa is not responsible for lost or stolen personal belongings.</li>
                <li class="mb-2"><i class="fas fa-spa text-gold me-2"></i>Photography and video recording inside treatment rooms is strictly prohibited.</li>
                <li class="mb-2"><i class="fas fa-spa text-gold me-2"></i>Guests under 18 must be accompanied by a parent or guardian.</li>
            </ul>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">10. Limitation of Liability</h2>
            <p>Bella Vista Oasis Spa shall not be held liable for any injury, loss, or damage resulting from the use of our services, except in cases of proven gross negligence or willful misconduct. By using our services, you acknowledge that spa treatments carry inherent risks, and you assume responsibility for any pre-existing conditions not disclosed in the health declaration.</p>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">11. Privacy</h2>
            <p>Your personal information is handled in accordance with our <a href="privacy-policy.php" class="text-gold">Privacy Policy</a> and the Philippine Data Privacy Act of 2012 (RA 10173). We are committed to protecting your data and using it solely for the purpose of providing our services.</p>
        </div>

        <div class="glass-card p-5 mb-4">
            <h2 class="font-heading text-gold mb-4">12. Changes to Terms</h2>
            <p>Bella Vista Oasis Spa reserves the right to modify these Terms and Conditions at any time. Changes will be posted on this page with an updated revision date. Continued use of our services after modifications constitutes acceptance of the revised terms.</p>
        </div>

        <div class="glass-card p-5 text-center">
            <h2 class="font-heading text-gold mb-4">Contact Us</h2>
            <p>For questions about these Terms and Conditions, please contact us:</p>
            <p><i class="fas fa-envelope text-gold me-2"></i>legal@bellavistaspa.com</p>
            <p><i class="fas fa-phone text-gold me-2"></i>+63 (2) 8123-4567</p>
            <a href="contact.php" class="btn btn-gold mt-3">Send an Inquiry</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

