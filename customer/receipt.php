<?php
/**
 * Bella Vista Oasis Spa - Official Electronic Sanctuary Receipt
 */

require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Auth Guard
if (!is_logged_in() || get_user_role() !== 'customer') {
    flash_message('warning', 'Please sign in to view receipts.');
    redirect('../login.php');
}

$pdo = get_db_connection();
$userId = (int)$_SESSION['user_id'];
$customerId = (int)($_SESSION['customer_id'] ?? 0);

$paymentId = (int)($_GET['payment_id'] ?? 0);
$receiptNumber = trim($_GET['receipt_number'] ?? '');

if ($paymentId <= 0 && empty($receiptNumber)) {
    flash_message('danger', 'Receipt identifier is required.');
    redirect('customer/payments.php');
}

// Fetch receipt details
if ($paymentId > 0) {
    $stReceipt = $pdo->prepare("SELECT r.*, p.amount, p.payment_type, p.transaction_reference, p.paid_at,
                                       pm.name AS payment_method_name,
                                       a.id AS appointment_id, a.appointment_date, a.start_time, a.end_time,
                                       a.total_amount AS appointment_total, a.additional_guest_charges, a.guest_count,
                                       s.name AS service_name, s.base_price, s.duration_minutes,
                                       rm.name AS room_name,
                                       u.first_name, u.last_name, u.email, u.phone
                                FROM receipts r
                                JOIN payments p ON r.payment_id = p.id
                                JOIN payment_methods pm ON p.payment_method_id = pm.id
                                JOIN appointments a ON p.appointment_id = a.id
                                JOIN services s ON a.service_id = s.id
                                JOIN rooms rm ON a.room_id = rm.id
                                JOIN customers c ON p.customer_id = c.id
                                JOIN users u ON c.user_id = u.id
                                WHERE p.id = ? AND p.customer_id = ? LIMIT 1");
    $stReceipt->execute([$paymentId, $customerId]);
} else {
    $stReceipt = $pdo->prepare("SELECT r.*, p.amount, p.payment_type, p.transaction_reference, p.paid_at,
                                       pm.name AS payment_method_name,
                                       a.id AS appointment_id, a.appointment_date, a.start_time, a.end_time,
                                       a.total_amount AS appointment_total, a.additional_guest_charges, a.guest_count,
                                       s.name AS service_name, s.base_price, s.duration_minutes,
                                       rm.name AS room_name,
                                       u.first_name, u.last_name, u.email, u.phone
                                FROM receipts r
                                JOIN payments p ON r.payment_id = p.id
                                JOIN payment_methods pm ON p.payment_method_id = pm.id
                                JOIN appointments a ON p.appointment_id = a.id
                                JOIN services s ON a.service_id = s.id
                                JOIN rooms rm ON a.room_id = rm.id
                                JOIN customers c ON p.customer_id = c.id
                                JOIN users u ON c.user_id = u.id
                                WHERE r.receipt_number = ? AND p.customer_id = ? LIMIT 1");
    $stReceipt->execute([$receiptNumber, $customerId]);
}

$rec = $stReceipt->fetch();

if (!$rec) {
    flash_message('danger', 'Receipt not found or you are not authorized to view it.');
    redirect('customer/payments.php');
}

// Calculate total paid across all payments for this appointment to determine remaining balance
$stAllPaid = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE appointment_id = ? AND status = 'completed'");
$stAllPaid->execute([$rec['appointment_id']]);
$totalPaidAll = (float)$stAllPaid->fetchColumn();
$balanceLeft = max(0.00, (float)$rec['appointment_total'] - $totalPaidAll);

$pageTitle = 'Electronic Receipt';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9">
        <!-- Top Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <a href="payments.php" class="text-gold text-decoration-none small">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Payment History
            </a>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-gold btn-sm" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> Print Receipt
                </button>
                <a href="appointments.php" class="btn btn-charcoal btn-sm">
                    <i class="fa-solid fa-calendar-check me-1"></i> View Appointments
                </a>
            </div>
        </div>

        <!-- Luxury Receipt Container -->
        <div class="receipt-container">
            <!-- Receipt Header -->
            <div class="receipt-header">
                <div class="brand-icon-wrapper mx-auto mb-3" style="width: 54px; height: 54px; font-size: 1.6rem;">
                    <i class="fa-solid fa-spa"></i>
                </div>
                <h1 class="h3 font-heading text-white mb-1">Bella Vista Oasis Spa</h1>
                <p class="text-gold small mb-1" style="letter-spacing: 0.1em; text-transform: uppercase;">
                    The Pinnacle of Holistic Serenity & Luxury Wellness
                </p>
                <p class="text-muted small mb-0">
                    Tagaytay-Nasugbu Highway, Tagaytay City &bull; Concierge: +63 (02) 8876-5432
                </p>
            </div>

            <!-- Receipt Meta -->
            <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                <div>
                    <span class="text-muted small d-block">Receipt Number:</span>
                    <strong class="fs-5 font-monospace text-gold"><?= htmlspecialchars($rec['receipt_number'], ENT_QUOTES) ?></strong>
                </div>
                <div class="text-end">
                    <span class="text-muted small d-block">Date of Issue:</span>
                    <strong class="text-white"><?= format_datetime($rec['issued_at']) ?></strong>
                </div>
            </div>

            <!-- Customer & Transaction Details -->
            <div class="row g-3 mb-4 text-sm">
                <div class="col-sm-6">
                    <span class="text-muted small d-block">Billed To (Guest):</span>
                    <strong class="text-white"><?= htmlspecialchars($rec['first_name'] . ' ' . $rec['last_name'], ENT_QUOTES) ?></strong>
                    <div class="text-muted small"><?= htmlspecialchars($rec['email'], ENT_QUOTES) ?></div>
                    <?php if (!empty($rec['phone'])): ?>
                        <div class="text-muted small"><?= htmlspecialchars($rec['phone'], ENT_QUOTES) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <span class="text-muted small d-block">Payment Method & Reference:</span>
                    <strong class="text-white"><?= htmlspecialchars($rec['payment_method_name'], ENT_QUOTES) ?></strong>
                    <div class="text-muted small font-monospace">Ref: <?= htmlspecialchars($rec['transaction_reference'], ENT_QUOTES) ?></div>
                    <span class="badge badge-confirmed mt-1">Payment Successful</span>
                </div>
            </div>

            <!-- Treatment Schedule Info -->
            <div class="p-3 rounded-2 mb-4" style="background: rgba(16, 19, 19, 0.6); border: 1px solid rgba(214, 183, 123, 0.15);">
                <span class="text-gold fw-semibold small d-block mb-1">
                    <i class="fa-solid fa-calendar-check me-1"></i> Treatment Appointment
                </span>
                <div class="d-flex justify-content-between align-items-baseline">
                    <div>
                        <strong class="text-white"><?= htmlspecialchars($rec['service_name'], ENT_QUOTES) ?></strong>
                        <span class="text-muted small ms-2">(<?= $rec['duration_minutes'] ?> mins &bull; <?= htmlspecialchars($rec['room_name'], ENT_QUOTES) ?>)</span>
                    </div>
                    <span class="text-gold-light small">
                        <?= format_date($rec['appointment_date'], 'M d, Y') ?> &bull; <?= format_time($rec['start_time']) ?>
                    </span>
                </div>
            </div>

            <!-- Itemized Financial Breakdown -->
            <div class="mb-4">
                <div class="receipt-line text-muted">
                    <span>Base Ritual Fee (<?= $rec['guest_count'] ?> <?= $rec['guest_count'] > 1 ? 'Guests' : 'Guest' ?>)</span>
                    <span class="text-white"><?= format_currency($rec['base_price']) ?></span>
                </div>

                <?php if ((float)$rec['additional_guest_charges'] > 0): ?>
                    <div class="receipt-line text-muted">
                        <span>Additional Guest Charges</span>
                        <span class="text-white"><?= format_currency($rec['additional_guest_charges']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="receipt-line text-muted border-top pt-2" style="border-color: rgba(214, 183, 123, 0.15) !important;">
                    <span>Total Treatment Investment</span>
                    <strong class="text-white"><?= format_currency($rec['appointment_total']) ?></strong>
                </div>

                <div class="receipt-total-line">
                    <span>Amount Paid (<?= ucfirst(str_replace('_', ' ', $rec['payment_type'])) ?>)</span>
                    <span><?= format_currency($rec['amount']) ?></span>
                </div>

                <?php if ($balanceLeft > 0): ?>
                    <div class="receipt-line text-warning fw-semibold pt-2">
                        <span>Remaining Balance Payable at Concierge:</span>
                        <span><?= format_currency($balanceLeft) ?></span>
                    </div>
                <?php else: ?>
                    <div class="receipt-line text-success fw-semibold pt-2">
                        <span>Total Balance Remaining:</span>
                        <span>₱ 0.00 (Fully Settled)</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Receipt Footer & Policy -->
            <div class="text-center pt-3 border-top text-muted small" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                <p class="mb-1 text-gold fw-semibold">Thank you for letting us serve your wellness journey.</p>
                <p class="mb-0" style="font-size: 0.75rem;">
                    This electronic transaction receipt is issued by Bella Vista Oasis Spa. 
                    Loyalty points have been automatically applied to your guest account.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
