<?php
session_start();
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
if (!is_logged_in() || get_user_role() !== 'customer') { redirect('../login.php'); }
$page_title = 'Payment History';
$pdo = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT c.id FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer = $stmt->fetch();
$customer_id = $customer['id'];

// Fetch payments
$stmt = $pdo->prepare("
    SELECT p.*, pm.name as method_name, s.name as service_name, a.appointment_date, a.start_time, r.receipt_number
    FROM payments p
    LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
    LEFT JOIN appointments a ON p.appointment_id = a.id
    LEFT JOIN services s ON a.service_id = s.id
    LEFT JOIN receipts r ON r.payment_id = p.id
    WHERE p.customer_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$customer_id]);
$payments = $stmt->fetchAll();

// Totals
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total_paid FROM payments WHERE customer_id = ? AND status = 'completed'");
$stmt->execute([$customer_id]);
$total_paid = $stmt->fetch()['total_paid'];

// Outstanding balances
$stmt = $pdo->prepare("
    SELECT a.id, s.name as service_name, a.total_amount, a.appointment_date,
           COALESCE((SELECT SUM(amount) FROM payments WHERE appointment_id = a.id AND status = 'completed'), 0) as paid
    FROM appointments a
    LEFT JOIN services s ON a.service_id = s.id
    WHERE a.customer_id = ? AND a.status IN ('pending','confirmed')
    HAVING a.total_amount > paid
");
$stmt->execute([$customer_id]);
$outstanding = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <h2 class="font-heading text-gold mb-4"><i class="fas fa-credit-card me-2"></i>Payment History</h2>
            <?php display_flash(); ?>

            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="glass-card p-4 text-center">
                        <i class="fas fa-peso-sign text-gold fs-1 mb-2"></i>
                        <h3 class="text-gold">₱<?= number_format($total_paid, 2) ?></h3>
                        <p class="text-muted mb-0">Total Paid</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 text-center">
                        <i class="fas fa-receipt text-gold fs-1 mb-2"></i>
                        <h3 class="text-gold"><?= count($payments) ?></h3>
                        <p class="text-muted mb-0">Total Transactions</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 text-center">
                        <i class="fas fa-exclamation-circle text-gold fs-1 mb-2"></i>
                        <h3 class="text-gold"><?= count($outstanding) ?></h3>
                        <p class="text-muted mb-0">Outstanding Balances</p>
                    </div>
                </div>
            </div>

            <!-- Outstanding Balances -->
            <?php if (!empty($outstanding)): ?>
            <div class="glass-card p-4 mb-4">
                <h5 class="text-gold mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Outstanding Balances</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead><tr><th class="text-gold">Service</th><th class="text-gold">Date</th><th class="text-gold">Total</th><th class="text-gold">Paid</th><th class="text-gold">Balance</th><th class="text-gold">Action</th></tr></thead>
                        <tbody>
                        <?php foreach ($outstanding as $o): $balance = $o['total_amount'] - $o['paid']; ?>
                            <tr>
                                <td><?= htmlspecialchars($o['service_name']) ?></td>
                                <td><?= date('M d, Y', strtotime($o['appointment_date'])) ?></td>
                                <td>₱<?= number_format($o['total_amount'], 2) ?></td>
                                <td>₱<?= number_format($o['paid'], 2) ?></td>
                                <td class="text-warning fw-bold">₱<?= number_format($balance, 2) ?></td>
                                <td><a href="pay.php?appointment_id=<?= $o['id'] ?>" class="btn btn-gold btn-sm">Pay Now</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Payment History Table -->
            <div class="glass-card p-4">
                <h5 class="text-gold mb-3"><i class="fas fa-history me-2"></i>Transaction History</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th class="text-gold">Date</th>
                                <th class="text-gold">Service</th>
                                <th class="text-gold">Amount</th>
                                <th class="text-gold">Type</th>
                                <th class="text-gold">Method</th>
                                <th class="text-gold">Status</th>
                                <th class="text-gold">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($payments)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No payment records found</td></tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                                <td><?= htmlspecialchars($p['service_name'] ?? 'N/A') ?></td>
                                <td>₱<?= number_format($p['amount'], 2) ?></td>
                                <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $p['payment_type'])) ?></span></td>
                                <td><?= htmlspecialchars($p['method_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php
                                    $status_colors = ['completed'=>'success','pending'=>'warning','failed'=>'danger','refunded'=>'info'];
                                    $color = $status_colors[$p['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($p['status']) ?></span>
                                </td>
                                <td>
                                    <?php if ($p['receipt_number']): ?>
                                        <a href="receipt.php?receipt=<?= urlencode($p['receipt_number']) ?>" class="btn btn-outline-gold btn-sm"><i class="fas fa-file-alt"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

