<?php
/**
 * Bella Vista Oasis Spa - Simulated Payment Checkout
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Auth Guard
if (!is_logged_in() || get_user_role() !== 'customer') {
    flash_message('warning', 'Please sign in to proceed with payment.');
    redirect('../login.php');
}

$pdo = get_db_connection();
$userId = (int)$_SESSION['user_id'];
$customerId = (int)($_SESSION['customer_id'] ?? 0);

$appointmentId = (int)($_GET['appointment_id'] ?? 0);
if ($appointmentId <= 0) {
    flash_message('danger', 'Please choose an appointment to settle.');
    redirect('appointments.php');
}

// Fetch Appointment
$stAppt = $pdo->prepare("SELECT a.*, s.name AS service_name, s.duration_minutes, s.base_price,
                                r.name AS room_name,
                                tu.first_name AS therapist_first_name, tu.last_name AS therapist_last_name
                         FROM appointments a
                         JOIN services s ON a.service_id = s.id
                         JOIN rooms r ON a.room_id = r.id
                         LEFT JOIN therapist_assignments ta ON ta.appointment_id = a.id AND ta.status = 'assigned'
                         LEFT JOIN therapists t ON ta.therapist_id = t.id
                         LEFT JOIN employees te ON t.employee_id = te.id
                         LEFT JOIN users tu ON te.user_id = tu.id
                         WHERE a.id = ? AND a.customer_id = ? LIMIT 1");
$stAppt->execute([$appointmentId, $customerId]);
$appt = $stAppt->fetch();

if (!$appt) {
    flash_message('danger', 'Appointment not found or unauthorized.');
    redirect('appointments.php');
}

// Calculate already paid amounts
$stPaid = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE appointment_id = ? AND status = 'completed'");
$stPaid->execute([$appointmentId]);
$totalPaid = (float)$stPaid->fetchColumn();

$totalAmount = (float)$appt['total_amount'];
$balanceDue = max(0.00, $totalAmount - $totalPaid);

// Calculate 20% downpayment
$downpaymentPct = (float)get_system_setting('downpayment_percentage', 20);
$minDownpayment = round($totalAmount * ($downpaymentPct / 100), 2);

// Active Payment Methods
$stMethods = $pdo->prepare("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY id ASC");
$stMethods->execute();
$paymentMethods = $stMethods->fetchAll();

$pageTitle = 'Sanctuary Payment Checkout';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="appointments.php" class="text-gold text-decoration-none small">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to My Bookings
                </a>
                <h1 class="h3 font-heading text-white mt-2 mb-0">Secure Sanctuary Checkout</h1>
            </div>
            <span class="badge-status badge-<?= htmlspecialchars($appt['status'], ENT_QUOTES) ?>">
                <?= ucfirst(str_replace('_', ' ', $appt['status'])) ?>
            </span>
        </div>

        <?php if ($balanceDue <= 0): ?>
            <div class="card-luxury p-5 text-center">
                <i class="fa-solid fa-circle-check text-success fs-1 mb-3"></i>
                <h2 class="h4 font-heading text-white mb-2">Appointment Fully Settled</h2>
                <p class="text-muted mb-4">This booking has no outstanding balance. We look forward to welcoming you.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="appointments.php" class="btn btn-gold-outline">View My Bookings</a>
                    <a href="payments.php" class="btn btn-gold">Payment History</a>
                </div>
            </div>
        <?php else: ?>
            <form action="../process/process_payment.php" method="POST">
                <?= csrf_token_field() ?>
                <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">

                <div class="row g-4">
                    <!-- Left Column: Appointment Summary & Payment Option Selection -->
                    <div class="col-lg-6">
                        <div class="card-luxury p-4 h-100">
                            <h2 class="h6 font-heading text-gold mb-3 pb-2 border-bottom" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                                <i class="fa-solid fa-file-invoice me-2"></i> Treatment Summary
                            </h2>

                            <div class="mb-3">
                                <h3 class="h5 font-heading text-white mb-1"><?= htmlspecialchars($appt['service_name'], ENT_QUOTES) ?></h3>
                                <p class="text-muted small mb-0">
                                    <i class="fa-regular fa-clock text-gold me-1"></i><?= $appt['duration_minutes'] ?> Mins &bull; 
                                    <i class="fa-solid fa-door-closed text-gold ms-2 me-1"></i><?= htmlspecialchars($appt['room_name'], ENT_QUOTES) ?>
                                </p>
                            </div>

                            <div class="p-3 rounded-2 mb-4" style="background: rgba(16, 19, 19, 0.7); border: 1px solid rgba(214, 183, 123, 0.15);">
                                <div class="row g-2 small">
                                    <div class="col-6 text-muted">Date:</div>
                                    <div class="col-6 text-end text-white fw-semibold"><?= format_date($appt['appointment_date'], 'M d, Y') ?></div>
                                    <div class="col-6 text-muted">Time:</div>
                                    <div class="col-6 text-end text-gold-light"><?= format_time($appt['start_time']) ?> &ndash; <?= format_time($appt['end_time']) ?></div>
                                    <div class="col-6 text-muted">Total Treatment Cost:</div>
                                    <div class="col-6 text-end text-white fw-bold"><?= format_currency($totalAmount) ?></div>
                                    <?php if ($totalPaid > 0): ?>
                                        <div class="col-6 text-muted">Previously Paid:</div>
                                        <div class="col-6 text-end text-success">- <?= format_currency($totalPaid) ?></div>
                                    <?php endif; ?>
                                    <div class="col-12"><hr class="my-1" style="border-color: rgba(214, 183, 123, 0.2);"></div>
                                    <div class="col-6 text-white fw-bold">Balance Due:</div>
                                    <div class="col-6 text-end text-gold fw-bold fs-6"><?= format_currency($balanceDue) ?></div>
                                </div>
                            </div>

                            <h2 class="h6 font-heading text-gold mb-3 pb-2 border-bottom" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                                <i class="fa-solid fa-coins me-2"></i> Choose Payment Amount
                            </h2>

                            <!-- Payment Type Options -->
                            <div class="space-y-2 mb-3">
                                <?php if ($totalPaid <= 0): ?>
                                    <div class="p-3 mb-2 rounded-2 border cursor-pointer payment-type-card selected" 
                                         onclick="setPaymentType('downpayment', <?= $minDownpayment ?>)"
                                         style="background: rgba(214, 183, 123, 0.08); border-color: var(--bvo-gold) !important;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_type" id="typeDown" value="downpayment" checked>
                                            <label class="form-check-label text-white w-100" for="typeDown">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong class="text-white d-block">Reservation Downpayment (<?= (int)$downpaymentPct ?>%)</strong>
                                                        <span class="text-muted small">Secures your suite. Balance settled at arrival.</span>
                                                    </div>
                                                    <span class="fs-5 fw-bold text-gold"><?= format_currency($minDownpayment) ?></span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="p-3 rounded-2 border cursor-pointer payment-type-card <?= ($totalPaid > 0) ? 'selected' : '' ?>" 
                                     onclick="setPaymentType('<?= ($totalPaid > 0) ? 'balance' : 'full_payment' ?>', <?= $balanceDue ?>)"
                                     style="background: rgba(23, 26, 27, 0.8); border-color: rgba(214, 183, 123, 0.2) !important;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_type" id="typeFull" value="<?= ($totalPaid > 0) ? 'balance' : 'full_payment' ?>" <?= ($totalPaid > 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label text-white w-100" for="typeFull">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="text-white d-block"><?= ($totalPaid > 0) ? 'Remaining Balance' : 'Full Payment (100%)' ?></strong>
                                                    <span class="text-muted small">Complete total payment today.</span>
                                                </div>
                                                <span class="fs-5 fw-bold text-gold"><?= format_currency($balanceDue) ?></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="amount" id="paymentAmountInput" value="<?= ($totalPaid <= 0) ? $minDownpayment : $balanceDue ?>">
                        </div>
                    </div>

                    <!-- Right Column: Payment Method Selection & Checkout -->
                    <div class="col-lg-6">
                        <div class="card-luxury p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <h2 class="h6 font-heading text-gold mb-3 pb-2 border-bottom" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                                    <i class="fa-solid fa-credit-card me-2"></i> Select Payment Method
                                </h2>

                                <div class="row g-2 mb-4">
                                    <?php foreach ($paymentMethods as $index => $m): ?>
                                        <div class="col-sm-6">
                                            <div class="p-3 rounded-2 border h-100 method-select-card <?= $index === 1 ? 'selected' : '' ?>"
                                                 id="methodCard_<?= $m['id'] ?>"
                                                 onclick="selectMethod(<?= $m['id'] ?>, '<?= htmlspecialchars($m['name'], ENT_QUOTES) ?>')"
                                                 style="background: rgba(23, 26, 27, 0.8); border-color: rgba(214, 183, 123, 0.2); cursor: pointer;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="radio" name="payment_method_id" id="pm_<?= $m['id'] ?>" value="<?= $m['id'] ?>" <?= $index === 1 ? 'checked' : '' ?>>
                                                    <label for="pm_<?= $m['id'] ?>" class="text-white small fw-semibold mb-0 cursor-pointer">
                                                        <?= htmlspecialchars($m['name'], ENT_QUOTES) ?>
                                                    </label>
                                                </div>
                                                <p class="text-muted mb-0 mt-1" style="font-size: 0.72rem;">
                                                    <?= htmlspecialchars($m['description'], ENT_QUOTES) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Dynamic Simulated Payment Container -->
                                <div id="methodSimContainer" class="p-3 rounded-2 mb-4" style="background: rgba(16, 19, 19, 0.7); border: 1px dashed rgba(214, 183, 123, 0.25);">
                                    <div id="simGCash" class="sim-content text-center py-2">
                                        <i class="fa-solid fa-qrcode text-gold fs-1 mb-2"></i>
                                        <p class="text-white small fw-bold mb-1">Bella Vista Oasis Spa Official GCash Merchant</p>
                                        <p class="text-muted small mb-0 font-monospace">0917-888-1234 (BVO SPA CORP)</p>
                                        <span class="badge bg-success mt-2">Test Environment: Instant Verification</span>
                                    </div>

                                    <div id="simCard" class="sim-content d-none">
                                        <div class="mb-2">
                                            <label class="form-label form-label-luxury">Card Number (Simulated)</label>
                                            <input type="text" class="form-control form-control-luxury font-monospace" value="4111 2222 3333 4444" readonly>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label form-label-luxury">Expiry</label>
                                                <input type="text" class="form-control form-control-luxury text-center" value="12/28" readonly>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label form-label-luxury">CVV</label>
                                                <input type="text" class="form-control form-control-luxury text-center font-monospace" value="789" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="simCash" class="sim-content d-none text-center py-2">
                                        <i class="fa-solid fa-money-bill-wave text-gold fs-1 mb-2"></i>
                                        <p class="text-white small fw-bold mb-1">Pay Upon Concierge Arrival</p>
                                        <p class="text-muted small mb-0">Record this transaction to immediately confirm your reservation.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Button -->
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted small">Amount to Charge:</span>
                                    <span class="fs-4 font-heading text-gold fw-bold" id="displayChargeAmount">
                                        <?= format_currency(($totalPaid <= 0) ? $minDownpayment : $balanceDue) ?>
                                    </span>
                                </div>
                                <button type="submit" class="btn btn-gold w-100 py-3 fs-6">
                                    <i class="fa-solid fa-lock me-2"></i> Simulate & Authorize Payment
                                </button>
                                <p class="text-center text-muted small mt-2 mb-0" style="font-size: 0.72rem;">
                                    This is a simulated sandbox checkout. No real financial debit occurs.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function setPaymentType(type, amount) {
    document.getElementById('paymentAmountInput').value = amount;
    document.getElementById('displayChargeAmount').innerText = '₱ ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});

    document.querySelectorAll('.payment-type-card').forEach(c => {
        c.style.background = 'rgba(23, 26, 27, 0.8)';
        c.style.borderColor = 'rgba(214, 183, 123, 0.2)';
    });

    const targetCard = event.currentTarget;
    targetCard.style.background = 'rgba(214, 183, 123, 0.08)';
    targetCard.style.borderColor = 'var(--bvo-gold)';
    targetCard.querySelector('input[type="radio"]').checked = true;
}

function selectMethod(id, name) {
    document.querySelectorAll('.method-select-card').forEach(c => {
        c.style.borderColor = 'rgba(214, 183, 123, 0.2)';
        c.style.background = 'rgba(23, 26, 27, 0.8)';
    });

    const target = document.getElementById('methodCard_' + id);
    target.style.borderColor = 'var(--bvo-gold)';
    target.style.background = 'rgba(214, 183, 123, 0.08)';
    target.querySelector('input[type="radio"]').checked = true;

    // Toggle simulation UI
    document.querySelectorAll('.sim-content').forEach(el => el.classList.add('d-none'));
    if (name.includes('Card')) {
        document.getElementById('simCard').classList.remove('d-none');
    } else if (name.includes('Cash')) {
        document.getElementById('simCash').classList.remove('d-none');
    } else {
        document.getElementById('simGCash').classList.remove('d-none');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
