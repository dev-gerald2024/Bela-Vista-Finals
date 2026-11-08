<?php
/**
 * Bella Vista Oasis Spa - Simulated Payment Processor
 * 
 * Records payment transaction, generates official receipt, updates appointment
 * to 'confirmed', credits loyalty points (1 pt per ₱100), issues notification,
 * and logs security audit trail.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Auth Guard
if (!is_logged_in() || get_user_role() !== 'customer') {
    flash_message('danger', 'Please sign in to complete payment.');
    redirect('../login.php');
}

$pdo = get_db_connection();
$userId = (int)$_SESSION['user_id'];
$customerId = (int)($_SESSION['customer_id'] ?? 0);

// CSRF validation
$csrfToken = $_POST['csrf_token'] ?? null;
if (!validate_csrf_token($csrfToken)) {
    flash_message('danger', 'Security token invalid or expired. Please try again.');
    redirect('customer/payments.php');
}

$appointmentId = (int)($_POST['appointment_id'] ?? 0);
$paymentMethodId = (int)($_POST['payment_method_id'] ?? 0);
$paymentType = trim($_POST['payment_type'] ?? 'full_payment'); // 'downpayment', 'full_payment', 'balance'
$amount = (float)($_POST['amount'] ?? 0.00);

if ($appointmentId <= 0 || $paymentMethodId <= 0 || $amount <= 0) {
    flash_message('danger', 'Invalid payment submission parameters.');
    redirect('customer/payments.php');
}

// 1. Fetch appointment & verify ownership
$stAppt = $pdo->prepare("SELECT a.*, s.name AS service_name FROM appointments a 
                         JOIN services s ON a.service_id = s.id 
                         WHERE a.id = ? AND a.customer_id = ? LIMIT 1");
$stAppt->execute([$appointmentId, $customerId]);
$appt = $stAppt->fetch();

if (!$appt) {
    flash_message('danger', 'Appointment not found or unauthorized.');
    redirect('customer/payments.php');
}

// 2. Verify payment method is active
$stMethod = $pdo->prepare("SELECT * FROM payment_methods WHERE id = ? AND is_active = 1 LIMIT 1");
$stMethod->execute([$paymentMethodId]);
$method = $stMethod->fetch();

if (!$method) {
    flash_message('danger', 'Selected payment method is currently disabled.');
    redirect("customer/pay.php?appointment_id={$appointmentId}");
}

// 3. Process Payment atomically
try {
    $pdo->beginTransaction();

    // A. Generate transaction reference
    $txnRef = 'TXN-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

    // B. Insert payment record
    $insPay = $pdo->prepare("INSERT INTO payments 
        (`appointment_id`, `customer_id`, `amount`, `payment_type`, `payment_method_id`, `status`, `transaction_reference`, `paid_at`) 
        VALUES (?, ?, ?, ?, ?, 'completed', ?, NOW())");
    $insPay->execute([
        $appointmentId,
        $customerId,
        $amount,
        $paymentType,
        $paymentMethodId,
        $txnRef
    ]);
    $paymentId = (int)$pdo->lastInsertId();

    // C. Generate official receipt
    $receiptNumber = generate_receipt_number();
    $receiptDetails = json_encode([
        'service'          => $appt['service_name'],
        'appointment_date' => $appt['appointment_date'],
        'start_time'       => $appt['start_time'],
        'total_amount'     => (float)$appt['total_amount'],
        'amount_paid'      => $amount,
        'payment_type'     => $paymentType,
        'payment_method'   => $method['name'],
        'transaction_ref'  => $txnRef
    ], JSON_UNESCAPED_UNICODE);

    $insReceipt = $pdo->prepare("INSERT INTO receipts (`payment_id`, `receipt_number`, `issued_at`, `details`) 
                                 VALUES (?, ?, NOW(), ?)");
    $insReceipt->execute([$paymentId, $receiptNumber, $receiptDetails]);
    $receiptId = (int)$pdo->lastInsertId();

    // D. Update appointment status to 'confirmed' if pending
    if ($appt['status'] === 'pending') {
        $upAppt = $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ?");
        $upAppt->execute([$appointmentId]);
    }

    // E. Award Loyalty Points: 1 point per ₱100 paid
    $pointsEarned = (int)floor($amount / 100);
    if ($pointsEarned > 0) {
        $insPoints = $pdo->prepare("INSERT INTO loyalty_points 
            (`customer_id`, `points`, `transaction_type`, `reference_type`, `reference_id`, `description`) 
            VALUES (?, ?, 'earned', 'payment', ?, ?)");
        $insPoints->execute([
            $customerId,
            $pointsEarned,
            $paymentId,
            "Earned {$pointsEarned} loyalty points from payment ({$receiptNumber})"
        ]);
    }

    // F. Send Notification to Customer
    $notifTitle = "Payment Confirmed ({$receiptNumber})";
    $notifMsg = "We received your payment of " . format_currency($amount) . " for {$appt['service_name']} via {$method['name']}. Your appointment is confirmed!";
    $notifLink = "customer/receipt.php?payment_id={$paymentId}";

    $insNotif = $pdo->prepare("INSERT INTO notifications (`user_id`, `title`, `message`, `type`, `is_read`, `link`) 
                               VALUES (?, ?, ?, 'payment', 0, ?)");
    $insNotif->execute([$userId, $notifTitle, $notifMsg, $notifLink]);

    // G. Audit Log
    log_audit(
        $userId,
        'PAYMENT_COMPLETED_CUSTOMER',
        'payments',
        $paymentId,
        null,
        [
            'appointment_id' => $appointmentId,
            'amount'         => $amount,
            'payment_type'   => $paymentType,
            'method'         => $method['name'],
            'receipt_number' => $receiptNumber,
            'points_earned'  => $pointsEarned
        ]
    );

    $pdo->commit();

    flash_message('success', "Payment successfully processed! Receipt #{$receiptNumber} has been issued.");
    redirect("customer/receipt.php?payment_id={$paymentId}");

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Payment Process Exception: " . $e->getMessage());
    flash_message('danger', 'Unable to record payment transaction. Please try again.');
    redirect("customer/pay.php?appointment_id={$appointmentId}");
}
