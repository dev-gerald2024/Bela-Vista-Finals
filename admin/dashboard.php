<?php
/**
 * Bella Vista Oasis Spa - Admin Executive Dashboard
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// RBAC: Admin, Manager, Front Desk allowed
check_role(['admin', 'manager', 'front_desk'], 'login.php');

$pageTitle = 'Executive Dashboard';
$pdo = get_db_connection();

// --- 1. Metric Stat Calculations ---
$todayDate = date('Y-m-d');

// Today's Bookings
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND status != 'cancelled'");
$stmt->execute([$todayDate]);
$todaysBookings = (int)$stmt->fetchColumn();

// Today's Revenue (completed payments)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE (DATE(paid_at) = ? OR (paid_at IS NULL AND DATE(created_at) = ?)) AND status = 'completed'");
$stmt->execute([$todayDate, $todayDate]);
$todaysRevenue = (float)$stmt->fetchColumn();

// Active Sessions Now
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND status = 'in_progress'");
$stmt->execute([$todayDate]);
$activeSessions = (int)$stmt->fetchColumn();

// Available Therapists
$stmt = $pdo->query("SELECT COUNT(*) FROM therapists t JOIN employees e ON t.employee_id = e.id WHERE t.is_available = 1 AND e.employment_status = 'active'");
$availableTherapists = (int)$stmt->fetchColumn();

// Available Rooms
$stmt = $pdo->query("SELECT COUNT(*) FROM rooms WHERE is_active = 1 AND status = 'available'");
$availableRooms = (int)$stmt->fetchColumn();

// Pending Appointments
$stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
$pendingAppointments = (int)$stmt->fetchColumn();

// --- 2. Today's Appointment Schedule / Timeline ---
$stmt = $pdo->prepare("SELECT a.*, s.name AS service_name, s.duration_minutes, r.name AS room_name,
                              u.first_name, u.last_name, u.phone AS customer_phone,
                              tu.first_name AS therapist_fn, tu.last_name AS therapist_ln
                       FROM appointments a
                       JOIN services s ON a.service_id = s.id
                       LEFT JOIN rooms r ON a.room_id = r.id
                       JOIN customers c ON a.customer_id = c.id
                       JOIN users u ON c.user_id = u.id
                       LEFT JOIN therapist_assignments ta ON a.id = ta.appointment_id
                       LEFT JOIN therapists t ON ta.therapist_id = t.id
                       LEFT JOIN employees te ON t.employee_id = te.id
                       LEFT JOIN users tu ON te.user_id = tu.id
                       WHERE a.appointment_date = ?
                       ORDER BY a.start_time ASC");
$stmt->execute([$todayDate]);
$todayTimeline = $stmt->fetchAll();

// --- 3. Revenue Trend (Past 7 Days) for Chart.js ---
$revLabels = [];
$revData = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $revLabels[] = date('M d', strtotime($d));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE (DATE(paid_at) = ? OR (paid_at IS NULL AND DATE(created_at) = ?)) AND status = 'completed'");
    $stmt->execute([$d, $d]);
    $revData[] = (float)$stmt->fetchColumn();
}

// --- 4. Service Popularity (Top 5) for Chart.js Doughnut ---
$stmt = $pdo->query("SELECT s.name, COUNT(a.id) AS booking_count 
                     FROM services s 
                     LEFT JOIN appointments a ON s.id = a.service_id 
                     GROUP BY s.id 
                     ORDER BY booking_count DESC LIMIT 5");
$servicePop = $stmt->fetchAll();
$serviceLabels = [];
$serviceCounts = [];
foreach ($servicePop as $sp) {
    $serviceLabels[] = $sp['name'];
    $serviceCounts[] = (int)$sp['booking_count'];
}

// Fallback if empty service data
if (empty($serviceLabels)) {
    $serviceLabels = ['No Data Yet'];
    $serviceCounts = [1];
}

// --- 5. Recent Bookings (Last 10) ---
$stmt = $pdo->query("SELECT a.*, s.name AS service_name, u.first_name, u.last_name, r.name AS room_name
                     FROM appointments a
                     JOIN services s ON a.service_id = s.id
                     JOIN customers c ON a.customer_id = c.id
                     JOIN users u ON c.user_id = u.id
                     LEFT JOIN rooms r ON a.room_id = r.id
                     ORDER BY a.created_at DESC LIMIT 10");
$recentBookings = $stmt->fetchAll();

// --- 6. Low Stock Alerts ---
$stmt = $pdo->query("SELECT * FROM inventory WHERE quantity <= reorder_level AND is_active = 1 ORDER BY quantity ASC LIMIT 6");
$lowStockItems = $stmt->fetchAll();

// --- 7. Recent Customer Reviews ---
$stmt = $pdo->query("SELECT r.*, u.first_name, u.last_name, s.name AS service_name
                     FROM reviews r
                     JOIN customers c ON r.customer_id = c.id
                     JOIN users u ON c.user_id = u.id
                     LEFT JOIN appointments a ON r.appointment_id = a.id
                     LEFT JOIN services s ON a.service_id = s.id
                     ORDER BY r.created_at DESC LIMIT 4");
$recentReviews = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div class="breadcrumb-area">
    <div>
        <h2 class="font-heading mb-1" style="font-size: 1.85rem;">Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?></h2>
        <p class="text-silver mb-0" style="font-size: 0.9rem;">
            <i class="fa-regular fa-calendar text-gold me-2"></i><?= date('l, F j, Y') ?> &bull; Operational Overview & Sanctuary Intelligence
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="appointment-form.php" class="btn btn-gold">
            <i class="fa-solid fa-plus"></i> New Appointment
        </a>
        <a href="calendar.php" class="btn btn-outline-gold">
            <i class="fa-solid fa-calendar-day"></i> Schedule View
        </a>
    </div>
</div>

<!-- 6 Stat Cards Row -->
<div class="row g-3 mb-4">
    <!-- Today's Bookings -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Today's Bookings</div>
                <div class="stat-value"><?= number_format($todaysBookings) ?></div>
                <div class="stat-trend positive">
                    <i class="fa-solid fa-arrow-trend-up"></i> Active Daily
                </div>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <!-- Today's Revenue -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Today's Revenue</div>
                <div class="stat-value" style="font-size: 1.35rem;"><?= format_currency($todaysRevenue) ?></div>
                <div class="stat-trend positive">
                    <i class="fa-solid fa-coins"></i> Collected
                </div>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-credit-card"></i>
            </div>
        </div>
    </div>

    <!-- Active Sessions Now -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Active Sessions</div>
                <div class="stat-value"><?= number_format($activeSessions) ?></div>
                <div class="stat-trend neutral">
                    <i class="fa-solid fa-spa"></i> In Treatment
                </div>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
    </div>

    <!-- Available Therapists -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Available Staff</div>
                <div class="stat-value"><?= number_format($availableTherapists) ?></div>
                <div class="stat-trend positive">
                    <i class="fa-solid fa-user-check"></i> On Duty
                </div>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-user-nurse"></i>
            </div>
        </div>
    </div>

    <!-- Available Rooms -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Available Rooms</div>
                <div class="stat-value"><?= number_format($availableRooms) ?></div>
                <div class="stat-trend positive">
                    <i class="fa-solid fa-door-open"></i> Ready
                </div>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-bath"></i>
            </div>
        </div>
    </div>

    <!-- Pending Appointments -->
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Pending Requests</div>
                <div class="stat-value text-gold"><?= number_format($pendingAppointments) ?></div>
                <div class="stat-trend <?= $pendingAppointments > 0 ? 'negative' : 'positive' ?>">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= $pendingAppointments > 0 ? 'Needs Action' : 'All Clear' ?>
                </div>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row: 7-Day Revenue & Service Popularity -->
<div class="row g-3 mb-4">
    <!-- Revenue Trend Chart -->
    <div class="col-12 col-lg-8">
        <div class="luxury-card">
            <div class="card-header">
                <h5><i class="fa-solid fa-chart-area text-gold me-2"></i>Revenue Performance (Last 7 Days)</h5>
                <span class="badge bg-gold text-dark">PHP Gross</span>
            </div>
            <div class="card-body">
                <div style="height: 280px; position: relative;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Popularity Doughnut -->
    <div class="col-12 col-lg-4">
        <div class="luxury-card">
            <div class="card-header">
                <h5><i class="fa-solid fa-chart-pie text-gold me-2"></i>Service Popularity</h5>
                <small class="text-silver">All-time share</small>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <div style="height: 250px; position: relative;">
                    <canvas id="servicePopChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Timeline & Low Stock Row -->
<div class="row g-3 mb-4">
    <!-- Today's Appointment Schedule / Timeline -->
    <div class="col-12 col-lg-8">
        <div class="luxury-card">
            <div class="card-header">
                <h5><i class="fa-solid fa-calendar-day text-gold me-2"></i>Today's Schedule (<?= date('M d') ?>)</h5>
                <a href="appointments.php" class="btn btn-sm btn-outline-gold">View All Bookings</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayTimeline)): ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-calendar-xmark text-silver fa-2x mb-3"></i>
                        <p class="text-silver mb-0">No appointments scheduled for today yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table-luxury">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Guest</th>
                                    <th>Service</th>
                                    <th>Room</th>
                                    <th>Therapist</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todayTimeline as $appt): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-gold"><?= format_time($appt['start_time']) ?></span>
                                            <small class="text-silver d-block" style="font-size: 0.72rem;"><?= format_time($appt['end_time']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-white"><?= htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']) ?></div>
                                            <small class="text-silver"><?= htmlspecialchars($appt['customer_phone'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <div class="text-white"><?= htmlspecialchars($appt['service_name']) ?></div>
                                            <small class="text-silver"><?= $appt['duration_minutes'] ?> mins &bull; <?= $appt['guest_count'] ?> guest(s)</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-25 text-silver">
                                                <?= htmlspecialchars($appt['room_name'] ?? 'Unassigned') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $appt['therapist_fn'] ? htmlspecialchars($appt['therapist_fn'] . ' ' . $appt['therapist_ln']) : '<span class="text-muted">Unassigned</span>' ?>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-<?= htmlspecialchars($appt['status']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $appt['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="appointment-form.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-dark-charcoal" title="Edit Appointment">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Low Stock Inventory Alerts -->
    <div class="col-12 col-lg-4">
        <div class="luxury-card">
            <div class="card-header">
                <h5><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Low Stock Alerts</h5>
                <a href="inventory.php" class="btn btn-sm btn-outline-gold">Restock</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockItems)): ?>
                    <div class="text-center py-4">
                        <i class="fa-solid fa-circle-check text-success fa-2x mb-2"></i>
                        <p class="text-silver small mb-0">All inventory levels are optimal.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush bg-transparent">
                        <?php foreach ($lowStockItems as $item): ?>
                            <div class="list-group-item bg-transparent border-secondary border-opacity-10 d-flex align-items-center justify-content-between p-3">
                                <div>
                                    <div class="fw-semibold text-white small"><?= htmlspecialchars($item['name']) ?></div>
                                    <small class="text-silver">SKU: <?= htmlspecialchars($item['sku']) ?> &bull; Reorder: <?= $item['reorder_level'] ?> <?= htmlspecialchars($item['unit']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger text-white fs-6"><?= $item['quantity'] ?> <?= htmlspecialchars($item['unit']) ?></span>
                                    <small class="d-block text-danger mt-1" style="font-size: 0.7rem;">Critically Low</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings Table & Recent Customer Reviews -->
<div class="row g-3">
    <!-- Recent Bookings Table (Last 10) -->
    <div class="col-12 col-lg-8">
        <div class="luxury-card">
            <div class="card-header">
                <h5><i class="fa-solid fa-list-check text-gold me-2"></i>Recent Bookings</h5>
                <a href="appointments.php" class="btn btn-sm btn-outline-gold">View Table</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-luxury">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Guest</th>
                                <th>Service</th>
                                <th>Date & Time</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $rb): ?>
                                <tr>
                                    <td>
                                        <span class="text-gold fw-bold">#<?= str_pad($rb['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($rb['first_name'] . ' ' . $rb['last_name']) ?></td>
                                    <td><?= htmlspecialchars($rb['service_name']) ?></td>
                                    <td>
                                        <div><?= format_date($rb['appointment_date']) ?></div>
                                        <small class="text-silver"><?= format_time($rb['start_time']) ?></small>
                                    </td>
                                    <td class="fw-semibold text-white"><?= format_currency($rb['total_amount']) ?></td>
                                    <td>
                                        <span class="badge-status badge-<?= htmlspecialchars($rb['status']) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $rb['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="appointment-form.php?id=<?= $rb['id'] ?>" class="btn btn-sm btn-dark-charcoal">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Reviews -->
    <div class="col-12 col-lg-4">
        <div class="luxury-card">
            <div class="card-header">
                <h5><i class="fa-solid fa-star text-gold me-2"></i>Guest Sentiments</h5>
                <a href="reviews.php" class="btn btn-sm btn-outline-gold">All Reviews</a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($recentReviews)): ?>
                    <p class="text-center text-silver my-4">No reviews recorded yet.</p>
                <?php else: ?>
                    <?php foreach ($recentReviews as $rev): ?>
                        <div class="p-3 mb-3 rounded" style="background: rgba(31, 35, 37, 0.6); border: 1px solid rgba(214, 183, 123, 0.12);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="fw-semibold text-white small"><?= htmlspecialchars($rev['first_name'] . ' ' . $rev['last_name']) ?></div>
                                <div class="text-gold small">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="fa-<?= $s <= $rev['rating'] ? 'solid' : 'regular' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-silver small mb-1 fst-italic">"<?= htmlspecialchars($rev['comment'] ?? 'No comment provided.') ?>"</p>
                            <small class="text-muted d-block text-end" style="font-size: 0.7rem;"><?= format_date($rev['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Dark Luxury Configuration Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue Line Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    const revGradient = revCtx.createLinearGradient(0, 0, 0, 250);
    revGradient.addColorStop(0, 'rgba(214, 183, 123, 0.35)');
    revGradient.addColorStop(1, 'rgba(214, 183, 123, 0.0)');

    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($revLabels) ?>,
            datasets: [{
                label: 'Revenue (₱)',
                data: <?= json_encode($revData) ?>,
                borderColor: '#D6B77B',
                backgroundColor: revGradient,
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#101313',
                pointBorderColor: '#D6B77B',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#171A1B',
                    titleColor: '#D6B77B',
                    bodyColor: '#F4F3EF',
                    borderColor: 'rgba(214, 183, 123, 0.3)',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return ' Revenue: ₱' + Number(context.raw).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(214, 183, 123, 0.08)' },
                    ticks: { color: '#9C9A96', font: { family: 'Inter', size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(214, 183, 123, 0.08)' },
                    ticks: {
                        color: '#9C9A96',
                        font: { family: 'Inter', size: 11 },
                        callback: function(value) { return '₱' + Number(value).toLocaleString(); }
                    }
                }
            }
        }
    });

    // 2. Service Popularity Doughnut Chart
    const servCtx = document.getElementById('servicePopChart').getContext('2d');
    new Chart(servCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($serviceLabels) ?>,
            datasets: [{
                data: <?= json_encode($serviceCounts) ?>,
                backgroundColor: [
                    '#D6B77B',
                    '#B59353',
                    '#8C703C',
                    '#6E7275',
                    '#38BDF8'
                ],
                borderColor: '#171A1B',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#D4D2CD',
                        font: { family: 'Inter', size: 11 },
                        padding: 12
                    }
                },
                tooltip: {
                    backgroundColor: '#171A1B',
                    titleColor: '#D6B77B',
                    bodyColor: '#F4F3EF',
                    borderColor: 'rgba(214, 183, 123, 0.3)',
                    borderWidth: 1
                }
            },
            cutout: '70%'
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
