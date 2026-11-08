<?php
require_once 'config/app.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
$page_title = 'Announcements & Promotions';
require_once 'includes/header.php';

// Fetch active promotions
$promotions = [];
$pdo = null;
try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT * FROM promotions WHERE is_active = 1 AND end_date >= CURDATE() ORDER BY start_date DESC");
    $promotions = $stmt->fetchAll();
} catch (Exception $e) {}

// Fetch announcements from website_contents
$announcements_content = [];
try {
    if (!$pdo) { throw new Exception('No DB'); }
    $stmt = $pdo->query("SELECT * FROM website_contents WHERE section_key LIKE 'announcement_%' AND is_published = 1 ORDER BY updated_at DESC");
    $announcements_content = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<section class="page-header text-center py-5" style="background: linear-gradient(135deg, rgba(16,19,19,0.95), rgba(23,26,27,0.95));">
    <div class="container">
        <h1 class="display-4 font-heading text-gold mb-3">Announcements & Promotions</h1>
        <p class="text-muted">Stay updated with our latest offers and news</p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center bg-transparent">
                <li class="breadcrumb-item"><a href="index.php" class="text-gold">Home</a></li>
                <li class="breadcrumb-item active text-muted">Announcements</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <!-- Active Promotions -->
        <h2 class="font-heading text-gold mb-4"><i class="fas fa-tags me-2"></i>Current Promotions</h2>

        <?php if (!empty($promotions)): ?>
            <div class="row g-4 mb-5">
                <?php foreach ($promotions as $promo): ?>
                    <?php
                    $days_left = (strtotime($promo['end_date']) - time()) / 86400;
                    $badge = $days_left <= 3 ? '<span class="badge bg-danger">Ending Soon</span>' :
                            ($days_left <= 7 ? '<span class="badge bg-warning text-dark">This Week</span>' :
                            '<span class="badge bg-success">Ongoing</span>');
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="glass-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="text-gold font-heading mb-0"><?= htmlspecialchars($promo['title']) ?></h5>
                                <?= $badge ?>
                            </div>
                            <p class="text-muted"><?= htmlspecialchars($promo['description']) ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-gold fw-bold fs-4">
                                    <?= $promo['discount_type'] === 'percentage' ? $promo['discount_value'] . '% OFF' : '₱' . number_format($promo['discount_value'], 0) . ' OFF' ?>
                                </span>
                                <?php if ($promo['promo_code']): ?>
                                    <code class="px-3 py-1 rounded" style="background: rgba(214,183,123,0.15); color: #D6B77B; font-size: 1.1rem;">
                                        <?= htmlspecialchars($promo['promo_code']) ?>
                                    </code>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-calendar me-1"></i>Valid: <?= date('M d', strtotime($promo['start_date'])) ?> — <?= date('M d, Y', strtotime($promo['end_date'])) ?>
                            </small>
                            <?php if ($promo['min_booking_amount'] > 0): ?>
                                <small class="text-muted d-block"><i class="fas fa-info-circle me-1"></i>Min. booking: ₱<?= number_format($promo['min_booking_amount'], 0) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Sample promotions when DB is empty -->
            <div class="row g-4 mb-5">
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="text-gold font-heading mb-0">Grand Opening Special</h5>
                            <span class="badge bg-success">New</span>
                        </div>
                        <p class="text-muted">Celebrate our grand opening with exclusive discounts on all spa treatments. Experience luxury at an exceptional value.</p>
                        <span class="text-gold fw-bold fs-4">20% OFF</span>
                        <code class="ms-3 px-3 py-1 rounded" style="background: rgba(214,183,123,0.15); color: #D6B77B;">WELCOME20</code>
                        <small class="text-muted d-block mt-2"><i class="fas fa-calendar me-1"></i>Valid through December 2026</small>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="text-gold font-heading mb-0">Couples Retreat Package</h5>
                            <span class="badge bg-success">Ongoing</span>
                        </div>
                        <p class="text-muted">Book a Couples Suite and receive complimentary aromatherapy upgrade for both guests. The perfect romantic escape.</p>
                        <span class="text-gold fw-bold fs-4">Free Upgrade</span>
                        <small class="text-muted d-block mt-2"><i class="fas fa-calendar me-1"></i>Available year-round</small>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="text-gold font-heading mb-0">Family Weekend Special</h5>
                            <span class="badge bg-warning text-dark">Limited</span>
                        </div>
                        <p class="text-muted">Enjoy our Family Lounge package every Saturday and Sunday with a special discount for families of 4 or more.</p>
                        <span class="text-gold fw-bold fs-4">₱500 OFF</span>
                        <code class="ms-3 px-3 py-1 rounded" style="background: rgba(214,183,123,0.15); color: #D6B77B;">FAMILYFUN</code>
                        <small class="text-muted d-block mt-2"><i class="fas fa-calendar me-1"></i>Weekends only</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- General Announcements -->
        <h2 class="font-heading text-gold mb-4 mt-5"><i class="fas fa-bullhorn me-2"></i>Latest News</h2>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <span class="badge bg-info mb-2">Announcement</span>
                    <h5 class="text-gold font-heading">Extended Holiday Hours</h5>
                    <small class="text-muted"><i class="fas fa-clock me-1"></i>November 2026</small>
                    <p class="mt-3">In celebration of the holiday season, Bella Vista Oasis Spa will be extending our operating hours on select dates. Watch this space for the detailed schedule.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <span class="badge bg-success mb-2">New Service</span>
                    <h5 class="text-gold font-heading">New Aromatherapy Menu</h5>
                    <small class="text-muted"><i class="fas fa-clock me-1"></i>Coming Soon</small>
                    <p class="mt-3">We are excited to announce our new premium aromatherapy menu featuring essential oils sourced from around the world. Stay tuned for our official launch.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <span class="badge bg-warning text-dark mb-2">Reminder</span>
                    <h5 class="text-gold font-heading">Health & Safety Commitment</h5>
                    <small class="text-muted"><i class="fas fa-clock me-1"></i>Ongoing</small>
                    <p class="mt-3">Your safety is our priority. All treatment rooms undergo a mandatory 30-minute cleaning and sanitization protocol between sessions, using hospital-grade disinfectants.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <span class="badge bg-primary mb-2">Loyalty Program</span>
                    <h5 class="text-gold font-heading">Earn While You Relax</h5>
                    <small class="text-muted"><i class="fas fa-clock me-1"></i>Now Active</small>
                    <p class="mt-3">Join our loyalty rewards program and earn 1 point for every ₱100 spent. Accumulate points and redeem them for exclusive discounts and complimentary services.</p>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-5">
            <div class="glass-card p-5">
                <h3 class="font-heading text-gold mb-3">Don't Miss Out!</h3>
                <p class="text-muted mb-4">Create an account to receive exclusive promotions and early access to special offers.</p>
                <a href="register.php" class="btn btn-gold btn-lg me-3">Create Account</a>
                <a href="services.php" class="btn btn-outline-gold btn-lg">View Services</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

