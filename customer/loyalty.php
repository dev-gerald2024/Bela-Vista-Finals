<?php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
if (!is_logged_in() || get_user_role() !== 'customer') { redirect('../login.php'); }
$page_title = 'Loyalty Rewards';
$pdo = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer_id = $stmt->fetch()['id'];

// Current balance
$stmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN transaction_type='earned' THEN points WHEN transaction_type='redeemed' THEN -points WHEN transaction_type='expired' THEN -points WHEN transaction_type='adjusted' THEN points ELSE 0 END), 0) as balance FROM loyalty_points WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$balance = $stmt->fetch()['balance'];

// Points history
$stmt = $pdo->prepare("SELECT * FROM loyalty_points WHERE customer_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$customer_id]);
$history = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <h2 class="font-heading text-gold mb-4"><i class="fas fa-crown me-2"></i>Loyalty Rewards</h2>

            <!-- Points Balance -->
            <div class="glass-card p-5 text-center mb-4">
                <i class="fas fa-star text-gold" style="font-size: 3rem;"></i>
                <h1 class="text-gold font-heading mt-3" style="font-size: 4rem;"><?= number_format($balance) ?></h1>
                <p class="text-muted fs-5">Loyalty Points</p>
                <div class="mt-3">
                    <span class="badge bg-dark px-3 py-2 fs-6" style="border: 1px solid rgba(214,183,123,0.3);">
                        <?php
                        if ($balance >= 1000) echo '👑 Platinum Member';
                        elseif ($balance >= 500) echo '💎 Gold Member';
                        elseif ($balance >= 100) echo '⭐ Silver Member';
                        else echo '🌟 Member';
                        ?>
                    </span>
                </div>
            </div>

            <!-- How It Works -->
            <div class="glass-card p-4 mb-4">
                <h5 class="text-gold mb-4"><i class="fas fa-info-circle me-2"></i>How Loyalty Rewards Work</h5>
                <div class="row g-4">
                    <div class="col-md-3 text-center">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;background:rgba(214,183,123,0.15);">
                            <i class="fas fa-spa text-gold fs-4"></i>
                        </div>
                        <h6 class="text-gold">Book & Enjoy</h6>
                        <p class="text-muted small">Book any spa service and enjoy your treatment</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;background:rgba(214,183,123,0.15);">
                            <i class="fas fa-coins text-gold fs-4"></i>
                        </div>
                        <h6 class="text-gold">Earn Points</h6>
                        <p class="text-muted small">Earn <strong>1 point for every ₱100</strong> spent</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;background:rgba(214,183,123,0.15);">
                            <i class="fas fa-layer-group text-gold fs-4"></i>
                        </div>
                        <h6 class="text-gold">Accumulate</h6>
                        <p class="text-muted small">Watch your points grow with every visit</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;background:rgba(214,183,123,0.15);">
                            <i class="fas fa-gift text-gold fs-4"></i>
                        </div>
                        <h6 class="text-gold">Redeem</h6>
                        <p class="text-muted small">Redeem points for discounts and free services</p>
                    </div>
                </div>
            </div>

            <!-- Points History -->
            <div class="glass-card p-4">
                <h5 class="text-gold mb-3"><i class="fas fa-history me-2"></i>Points History</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead><tr><th class="text-gold">Date</th><th class="text-gold">Type</th><th class="text-gold">Points</th><th class="text-gold">Description</th></tr></thead>
                        <tbody>
                        <?php if (empty($history)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No loyalty points yet. Book a service to start earning!</td></tr>
                        <?php else: ?>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($h['created_at'])) ?></td>
                                <td>
                                    <?php
                                    $type_badges = ['earned'=>'success','redeemed'=>'info','expired'=>'secondary','adjusted'=>'warning'];
                                    $badge = $type_badges[$h['transaction_type']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= ucfirst($h['transaction_type']) ?></span>
                                </td>
                                <td class="<?= $h['transaction_type'] === 'earned' || ($h['transaction_type'] === 'adjusted' && $h['points'] > 0) ? 'text-success' : 'text-danger' ?>">
                                    <?= $h['transaction_type'] === 'earned' || ($h['transaction_type'] === 'adjusted' && $h['points'] > 0) ? '+' : '-' ?><?= abs($h['points']) ?>
                                </td>
                                <td><?= htmlspecialchars($h['description'] ?? '') ?></td>
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

