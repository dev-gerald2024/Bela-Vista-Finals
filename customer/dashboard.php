<?php
/**
 * Bella Vista Oasis Spa - Customer Portal Dashboard
 */

require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Role & Session Guard
if (!is_logged_in() || get_user_role() !== 'customer') {
    flash_message('warning', 'Please sign in to access your guest sanctuary portal.');
    redirect('../login.php');
}

$pdo = get_db_connection();
$userId = (int)$_SESSION['user_id'];
$customerFirstName = $_SESSION['first_name'] ?? 'Guest';

// Ensure customer profile ID is available in session
if (empty($_SESSION['customer_id'])) {
    $stmtC = $pdo->prepare("SELECT id FROM customers WHERE user_id = ? LIMIT 1");
    $stmtC->execute([$userId]);
    $cRow = $stmtC->fetch();
    if ($cRow) {
        $_SESSION['customer_id'] = (int)$cRow['id'];
    } else {
        $insC = $pdo->prepare("INSERT INTO customers (user_id) VALUES (?)");
        $insC->execute([$userId]);
        $_SESSION['customer_id'] = (int)$pdo->lastInsertId();
    }
}
$customerId = (int)$_SESSION['customer_id'];

// 1. Fetch Quick Stats
// Upcoming Appointments
$stUp = $pdo->prepare("SELECT COUNT(*) FROM appointments 
                       WHERE customer_id = ? 
                         AND (appointment_date > CURDATE() OR (appointment_date = CURDATE() AND end_time >= CURTIME()))
                         AND status IN ('pending', 'confirmed', 'in_progress')");
$stUp->execute([$customerId]);
$upcomingCount = (int)$stUp->fetchColumn();

// Total Bookings Ever
$stTot = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE customer_id = ?");
$stTot->execute([$customerId]);
$totalBookings = (int)$stTot->fetchColumn();

// Loyalty Points Balance
$stPts = $pdo->prepare("SELECT COALESCE(SUM(
                            CASE 
                                WHEN transaction_type = 'earned' THEN points 
                                WHEN transaction_type = 'redeemed' THEN -points 
                                WHEN transaction_type = 'expired' THEN -points 
                                WHEN transaction_type = 'adjusted' THEN points 
                                ELSE 0 
                            END
                        ), 0) FROM loyalty_points WHERE customer_id = ?");
$stPts->execute([$customerId]);
$loyaltyBalance = max(0, (int)$stPts->fetchColumn());

// Pending Payments (count of appointments in 'pending' status without completed full payment)
$stPend = $pdo->prepare("SELECT COUNT(*) FROM appointments a 
                         WHERE a.customer_id = ? 
                           AND a.status = 'pending'
                           AND NOT EXISTS (
                               SELECT 1 FROM payments p 
                               WHERE p.appointment_id = a.id 
                                 AND p.status = 'completed' 
                                 AND p.payment_type = 'full_payment'
                           )");
$stPend->execute([$customerId]);
$pendingPaymentsCount = (int)$stPend->fetchColumn();

// 2. Fetch Next Upcoming Appointment
$stNext = $pdo->prepare("SELECT a.*, s.name AS service_name, s.duration_minutes, s.room_type, r.name AS room_name,
                                tu.first_name AS therapist_first_name, tu.last_name AS therapist_last_name
                         FROM appointments a
                         JOIN services s ON a.service_id = s.id
                         JOIN rooms r ON a.room_id = r.id
                         LEFT JOIN therapist_assignments ta ON ta.appointment_id = a.id AND ta.status = 'assigned'
                         LEFT JOIN therapists t ON ta.therapist_id = t.id
                         LEFT JOIN employees te ON t.employee_id = te.id
                         LEFT JOIN users tu ON te.user_id = tu.id
                         WHERE a.customer_id = ? 
                           AND (a.appointment_date > CURDATE() OR (a.appointment_date = CURDATE() AND a.end_time >= CURTIME()))
                           AND a.status IN ('pending', 'confirmed', 'in_progress')
                         ORDER BY a.appointment_date ASC, a.start_time ASC
                         LIMIT 1");
$stNext->execute([$customerId]);
$nextAppt = $stNext->fetch();

// 3. Fetch Recent Booking History (Last 5)
$stRecent = $pdo->prepare("SELECT a.*, s.name AS service_name, s.duration_minutes, r.name AS room_name,
                                  tu.first_name AS therapist_first_name, tu.last_name AS therapist_last_name
                           FROM appointments a
                           JOIN services s ON a.service_id = s.id
                           JOIN rooms r ON a.room_id = r.id
                           LEFT JOIN therapist_assignments ta ON ta.appointment_id = a.id AND ta.status = 'assigned'
                           LEFT JOIN therapists t ON ta.therapist_id = t.id
                           LEFT JOIN employees te ON t.employee_id = te.id
                           LEFT JOIN users tu ON te.user_id = tu.id
                           WHERE a.customer_id = ?
                           ORDER BY a.appointment_date DESC, a.start_time DESC
                           LIMIT 5");
$stRecent->execute([$customerId]);
$recentBookings = $stRecent->fetchAll();

// 4. Fetch Recent Notifications (Last 5)
$stNotifs = $pdo->prepare("SELECT * FROM notifications 
                           WHERE user_id = ? 
                           ORDER BY created_at DESC 
                           LIMIT 5");
$stNotifs->execute([$userId]);
$recentNotifications = $stNotifs->fetchAll();

$pageTitle = 'Dashboard Overview';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card-luxury p-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(23, 26, 27, 0.95), rgba(16, 19, 19, 0.9));">
            <div class="position-absolute end-0 top-0 bottom-0 d-none d-md-flex align-items-center pe-5 opacity-25">
                <i class="fa-solid fa-spa text-gold" style="font-size: 10rem;"></i>
            </div>
            <div class="position-relative z-1">
                <span class="badge badge-pending mb-2">
                    <i class="fa-solid fa-certificate text-gold me-1"></i>Guest Sanctuary
                </span>
                <h1 class="h2 font-heading mb-2">Welcome, <?= htmlspecialchars($customerFirstName, ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="text-muted mb-3" style="max-width: 650px;">
                    Step into an atmosphere of quiet luxury and renewed vitality. Your holistic journey of wellness and relaxation awaits at Bella Vista Oasis.
                </p>
                <div class="d-flex flex-wrap gap-2 pt-1">
                    <a href="book.php" class="btn btn-gold">
                        <i class="fa-solid fa-calendar-plus"></i>
                        <span>Book New Experience</span>
                    </a>
                    <a href="appointments.php" class="btn btn-gold-outline">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>View Booking History</span>
                    </a>
                    <a href="profile.php" class="btn btn-charcoal">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>My Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <p class="stat-label">Upcoming Visits</p>
                <p class="stat-value"><?= $upcomingCount ?></p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <p class="stat-label">Total Bookings</p>
                <p class="stat-value"><?= $totalBookings ?></p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-book-bookmark"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <p class="stat-label">Loyalty Rewards</p>
                <p class="stat-value text-gold"><?= number_format($loyaltyBalance) ?></p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-gem"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <p class="stat-label">Pending Payments</p>
                <p class="stat-value <?= $pendingPaymentsCount > 0 ? 'text-warning' : 'text-muted' ?>">
                    <?= $pendingPaymentsCount ?>
                </p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-credit-card"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Next Upcoming Appointment Card -->
    <div class="col-lg-7">
        <div class="card-luxury h-100">
            <div class="card-luxury-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-leaf text-gold fs-5"></i>
                    <h3 class="h5 font-heading mb-0">Next Upcoming Experience</h3>
                </div>
                <?php if ($nextAppt): ?>
                    <span class="badge-status badge-<?= htmlspecialchars($nextAppt['status'], ENT_QUOTES) ?>">
                        <i class="fa-solid fa-circle text-gold" style="font-size: 0.45rem;"></i>
                        <?= ucfirst(str_replace('_', ' ', $nextAppt['status'])) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-luxury-body">
                <?php if ($nextAppt): ?>
                    <div class="p-3 mb-4 rounded-3" style="background: rgba(16, 19, 19, 0.6); border: 1px solid rgba(214, 183, 123, 0.15);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h4 class="h5 font-heading text-gold mb-1">
                                    <?= htmlspecialchars($nextAppt['service_name'], ENT_QUOTES, 'UTF-8') ?>
                                </h4>
                                <p class="text-muted small mb-0">
                                    <i class="fa-regular fa-clock me-1 text-gold"></i><?= $nextAppt['duration_minutes'] ?> Minutes Session &bull; 
                                    <i class="fa-solid fa-door-closed ms-2 me-1 text-gold"></i><?= htmlspecialchars($nextAppt['room_name'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                            <span class="fs-5 fw-bold text-gold">
                                <?= format_currency($nextAppt['total_amount']) ?>
                            </span>
                        </div>

                        <hr style="border-color: rgba(214, 183, 123, 0.15);">

                        <div class="row g-2 text-sm">
                            <div class="col-sm-6">
                                <span class="text-muted d-block small">Date & Time</span>
                                <strong class="text-white">
                                    <i class="fa-regular fa-calendar me-1 text-gold"></i>
                                    <?= format_date($nextAppt['appointment_date'], 'l, F j, Y') ?>
                                </strong>
                                <div class="text-gold-light small">
                                    <?= format_time($nextAppt['start_time']) ?> &ndash; <?= format_time($nextAppt['end_time']) ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block small">Therapist Specialist</span>
                                <strong class="text-white">
                                    <i class="fa-solid fa-user-nurse me-1 text-gold"></i>
                                    <?= !empty($nextAppt['therapist_first_name']) 
                                        ? htmlspecialchars($nextAppt['therapist_first_name'] . ' ' . $nextAppt['therapist_last_name'], ENT_QUOTES) 
                                        : 'Master Therapist Assigned' ?>
                                </strong>
                                <div class="text-muted small">
                                    Guests: <?= (int)$nextAppt['guest_count'] ?> <?= $nextAppt['guest_count'] > 1 ? 'Persons' : 'Person' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($nextAppt['status'] === 'pending'): ?>
                            <a href="pay.php?appointment_id=<?= $nextAppt['id'] ?>" class="btn btn-gold btn-sm">
                                <i class="fa-solid fa-credit-card me-1"></i>Pay Now / Deposit
                            </a>
                        <?php endif; ?>
                        
                        <a href="appointments.php" class="btn btn-gold-outline btn-sm">
                            <i class="fa-solid fa-circle-info me-1"></i>Manage Booking
                        </a>
                        
                        <a href="reschedule.php?id=<?= $nextAppt['id'] ?>" class="btn btn-charcoal btn-sm">
                            <i class="fa-solid fa-calendar-week me-1"></i>Reschedule
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="stat-icon mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <h4 class="h6 font-heading text-muted mb-2">No Upcoming Appointments</h4>
                        <p class="text-muted small mb-4" style="max-width: 400px; margin: 0 auto;">
                            You currently have no scheduled spa sessions. Give yourself the peaceful restorative escape you deserve.
                        </p>
                        <a href="book.php" class="btn btn-gold">
                            <i class="fa-solid fa-plus me-1"></i>Reserve Your Sanctuary Slot
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Notifications Panel -->
    <div class="col-lg-5">
        <div class="card-luxury h-100">
            <div class="card-luxury-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-bell text-gold"></i>
                    <h3 class="h5 font-heading mb-0">Recent Notifications</h3>
                </div>
                <a href="notifications.php" class="small text-gold text-decoration-none hover-gold">
                    View All <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-luxury-body p-0">
                <?php if (!empty($recentNotifications)): ?>
                    <ul class="list-group list-group-flush bg-transparent">
                        <?php foreach ($recentNotifications as $notif): ?>
                            <li class="list-group-item bg-transparent border-0 px-4 py-3" style="border-bottom: 1px solid rgba(214, 183, 123, 0.08) !important;">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="rounded-circle p-2 mt-1" style="background: rgba(214, 183, 123, 0.12); color: var(--bvo-gold); width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fa-solid fa-<?= $notif['type'] === 'booking' ? 'calendar-check' : ($notif['type'] === 'payment' ? 'receipt' : 'bell') ?> small"></i>
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                                            <h4 class="h6 mb-0 text-truncate text-white" style="font-size: 0.9rem;">
                                                <?= htmlspecialchars($notif['title'], ENT_QUOTES, 'UTF-8') ?>
                                            </h4>
                                            <span class="text-muted" style="font-size: 0.7rem;">
                                                <?= date('M d', strtotime($notif['created_at'])) ?>
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-0 text-truncate">
                                            <?= htmlspecialchars($notif['message'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa-regular fa-bell-slash text-muted fs-3 mb-2"></i>
                        <p class="text-muted small mb-0">No notifications at this time.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Booking History Table -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card-luxury">
            <div class="card-luxury-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-gold"></i>
                    <h3 class="h5 font-heading mb-0">Recent Booking History</h3>
                </div>
                <a href="appointments.php" class="small text-gold text-decoration-none hover-gold">
                    View Complete History <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table-luxury">
                    <thead>
                        <tr>
                            <th>Ref / Service</th>
                            <th>Schedule Date</th>
                            <th>Time Window</th>
                            <th>Therapist</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentBookings)): ?>
                            <?php foreach ($recentBookings as $b): ?>
                                <tr>
                                    <td>
                                        <strong class="text-white d-block">
                                            <?= htmlspecialchars($b['service_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                        <span class="text-muted small">#BVO-<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?> &bull; <?= $b['duration_minutes'] ?> min</span>
                                    </td>
                                    <td>
                                        <span class="text-white"><?= format_date($b['appointment_date'], 'M d, Y') ?></span>
                                    </td>
                                    <td>
                                        <span class="text-gold-light small">
                                            <?= format_time($b['start_time']) ?> &ndash; <?= format_time($b['end_time']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            <?= !empty($b['therapist_first_name']) ? htmlspecialchars($b['therapist_first_name'] . ' ' . $b['therapist_last_name'], ENT_QUOTES) : 'Assigned' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-gold"><?= format_currency($b['total_amount']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-<?= htmlspecialchars($b['status'], ENT_QUOTES) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $b['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="appointments.php?view=<?= $b['id'] ?>" class="btn btn-charcoal btn-sm py-1 px-2" title="View Details">
                                            <i class="fa-solid fa-eye text-gold"></i>
                                        </a>
                                        <?php if ($b['status'] === 'pending'): ?>
                                            <a href="pay.php?appointment_id=<?= $b['id'] ?>" class="btn btn-gold btn-sm py-1 px-2 ms-1" title="Pay Now">
                                                <i class="fa-solid fa-credit-card"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No prior reservations recorded. Click <a href="book.php" class="text-gold">here</a> to schedule your first treatment.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
