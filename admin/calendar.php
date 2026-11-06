<?php
/**
 * Bella Vista Oasis Spa - Interactive Calendar Schedule
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_role(['admin', 'manager', 'front_desk'], 'login.php');

$pdo = get_db_connection();

// Current navigation inputs
$view = $_GET['view'] ?? 'month'; // 'month', 'week', 'day'
$currentDate = $_GET['date'] ?? date('Y-m-d');
$roomIdFilter = !empty($_GET['room_id']) ? (int)$_GET['room_id'] : null;
$therapistIdFilter = !empty($_GET['therapist_id']) ? (int)$_GET['therapist_id'] : null;

$timestamp = strtotime($currentDate) ?: time();
$currentDate = date('Y-m-d', $timestamp);

// Determine date range based on view
if ($view === 'day') {
    $startDate = $currentDate;
    $endDate = $currentDate;
    $prevDate = date('Y-m-d', strtotime('-1 day', $timestamp));
    $nextDate = date('Y-m-d', strtotime('+1 day', $timestamp));
    $viewTitle = date('F j, Y (l)', $timestamp);
} elseif ($view === 'week') {
    $dayOfWeek = date('w', $timestamp);
    $startDate = date('Y-m-d', strtotime("-$dayOfWeek days", $timestamp));
    $endDate = date('Y-m-d', strtotime("+ " . (6 - $dayOfWeek) . " days", $timestamp));
    $prevDate = date('Y-m-d', strtotime('-7 days', $timestamp));
    $nextDate = date('Y-m-d', strtotime('+7 days', $timestamp));
    $viewTitle = date('M d', strtotime($startDate)) . ' – ' . date('M d, Y', strtotime($endDate));
} else {
    // Month view
    $view = 'month';
    $firstOfMonth = date('Y-m-01', $timestamp);
    $lastOfMonth = date('Y-m-t', $timestamp);
    $startDayOfWeek = date('w', strtotime($firstOfMonth));
    $startDate = date('Y-m-d', strtotime("-$startDayOfWeek days", strtotime($firstOfMonth)));
    $endDayOfWeek = date('w', strtotime($lastOfMonth));
    $endDate = date('Y-m-d', strtotime("+ " . (6 - $endDayOfWeek) . " days", strtotime($lastOfMonth)));

    $prevDate = date('Y-m-d', strtotime('-1 month', strtotime($firstOfMonth)));
    $nextDate = date('Y-m-d', strtotime('+1 month', strtotime($firstOfMonth)));
    $viewTitle = date('F Y', $timestamp);
}

// Fetch Appointments within range
$sql = "SELECT a.*, s.name AS service_name, s.duration_minutes,
               u.first_name, u.last_name, u.phone,
               r.name AS room_name,
               tu.first_name AS therapist_fn, tu.last_name AS therapist_ln
        FROM appointments a
        JOIN services s ON a.service_id = s.id
        JOIN customers c ON a.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        LEFT JOIN rooms r ON a.room_id = r.id
        LEFT JOIN therapist_assignments ta ON a.id = ta.appointment_id
        LEFT JOIN therapists t ON ta.therapist_id = t.id
        LEFT JOIN employees te ON t.employee_id = te.id
        LEFT JOIN users tu ON te.user_id = tu.id
        WHERE a.appointment_date BETWEEN ? AND ?";
$params = [$startDate, $endDate];

if ($roomIdFilter) {
    $sql .= " AND a.room_id = ?";
    $params[] = $roomIdFilter;
}

if ($therapistIdFilter) {
    $sql .= " AND (a.preferred_therapist_id = ? OR ta.therapist_id = ?)";
    $params[] = $therapistIdFilter;
    $params[] = $therapistIdFilter;
}

$sql .= " ORDER BY a.appointment_date ASC, a.start_time ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

// Group appointments by date
$eventsByDate = [];
foreach ($appointments as $apt) {
    $eventsByDate[$apt['appointment_date']][] = $apt;
}

// Fetch all rooms & therapists for dropdowns
$allRooms = $pdo->query("SELECT id, name, room_type FROM rooms WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
$allTherapists = $pdo->query("SELECT t.id, u.first_name, u.last_name FROM therapists t JOIN employees e ON t.employee_id = e.id JOIN users u ON e.user_id = u.id WHERE e.employment_status = 'active' ORDER BY u.first_name ASC")->fetchAll();

// Today's summary sidebar
$todayApptsStmt = $pdo->prepare("SELECT a.*, s.name AS service_name, u.first_name, u.last_name, r.name AS room_name
                                 FROM appointments a
                                 JOIN services s ON a.service_id = s.id
                                 JOIN customers c ON a.customer_id = c.id
                                 JOIN users u ON c.user_id = u.id
                                 LEFT JOIN rooms r ON a.room_id = r.id
                                 WHERE a.appointment_date = CURDATE()
                                 ORDER BY a.start_time ASC");
$todayApptsStmt->execute();
$todayList = $todayApptsStmt->fetchAll();

$pageTitle = 'Spa Master Calendar';
require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Custom Calendar Luxury CSS */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #141718;
    border: 1px solid var(--bvo-charcoal-border);
    border-radius: 12px;
    overflow: hidden;
}

.calendar-header-cell {
    background: #171A1B;
    color: var(--bvo-gold);
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    text-align: center;
    padding: 0.85rem 0.5rem;
    border-bottom: 1px solid var(--bvo-charcoal-border);
    border-right: 1px solid rgba(214, 183, 123, 0.08);
}

.calendar-day-cell {
    min-height: 110px;
    background: #121415;
    border-right: 1px solid rgba(214, 183, 123, 0.08);
    border-bottom: 1px solid rgba(214, 183, 123, 0.08);
    padding: 0.5rem;
    transition: background 0.2s ease;
    display: flex;
    flex-direction: column;
}

.calendar-day-cell:hover {
    background: #191D1E;
}

.calendar-day-cell.other-month {
    opacity: 0.35;
    background: #0E1010;
}

.calendar-day-cell.is-today {
    background: rgba(214, 183, 123, 0.05);
    border: 1px solid var(--bvo-gold);
}

.day-number {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--bvo-silver);
    margin-bottom: 0.35rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.is-today .day-number span.num-badge {
    background: var(--bvo-gold);
    color: #101313;
    padding: 1px 6px;
    border-radius: 50%;
    font-weight: 700;
}

.calendar-event-pill {
    font-size: 0.7rem;
    padding: 0.2rem 0.45rem;
    border-radius: 4px;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-decoration: none;
    display: block;
    border-left: 3px solid;
    transition: transform 0.15s ease;
}

.calendar-event-pill:hover {
    transform: scale(1.02);
}

.pill-pending { background: rgba(214, 183, 123, 0.15); color: #E8D19F; border-left-color: var(--bvo-gold); }
.pill-confirmed { background: rgba(46, 184, 114, 0.15); color: #34D399; border-left-color: var(--bvo-success); }
.pill-in_progress { background: rgba(56, 189, 248, 0.15); color: #38BDF8; border-left-color: var(--bvo-info); }
.pill-completed { background: rgba(156, 154, 150, 0.15); color: #D4D2CD; border-left-color: var(--bvo-silver); }
.pill-cancelled { background: rgba(239, 68, 68, 0.15); color: #F87171; border-left-color: var(--bvo-danger); }
.pill-no_show { background: rgba(249, 115, 22, 0.15); color: #FB923C; border-left-color: #F97316; }

/* Day View Timeline Table */
.day-timeline-table td, .day-timeline-table th {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--bvo-charcoal-border);
}
</style>

<!-- Calendar Controls & Navigation Header -->
<div class="breadcrumb-area">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Calendar</li>
            </ol>
        </nav>
        <h2 class="font-heading mb-0" style="font-size: 1.75rem;"><?= htmlspecialchars($viewTitle) ?></h2>
    </div>

    <!-- View Toggle & Navigation Buttons -->
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group" role="group">
            <a href="?view=day&date=<?= $currentDate ?>&room_id=<?= $roomIdFilter ?>&therapist_id=<?= $therapistIdFilter ?>" class="btn btn-sm <?= $view === 'day' ? 'btn-gold' : 'btn-dark-charcoal' ?>">Day</a>
            <a href="?view=week&date=<?= $currentDate ?>&room_id=<?= $roomIdFilter ?>&therapist_id=<?= $therapistIdFilter ?>" class="btn btn-sm <?= $view === 'week' ? 'btn-gold' : 'btn-dark-charcoal' ?>">Week</a>
            <a href="?view=month&date=<?= $currentDate ?>&room_id=<?= $roomIdFilter ?>&therapist_id=<?= $therapistIdFilter ?>" class="btn btn-sm <?= $view === 'month' ? 'btn-gold' : 'btn-dark-charcoal' ?>">Month</a>
        </div>

        <div class="btn-group" role="group">
            <a href="?view=<?= $view ?>&date=<?= $prevDate ?>&room_id=<?= $roomIdFilter ?>&therapist_id=<?= $therapistIdFilter ?>" class="btn btn-sm btn-dark-charcoal" title="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <a href="?view=<?= $view ?>&date=<?= date('Y-m-d') ?>&room_id=<?= $roomIdFilter ?>&therapist_id=<?= $therapistIdFilter ?>" class="btn btn-sm btn-dark-charcoal">
                Today
            </a>
            <a href="?view=<?= $view ?>&date=<?= $nextDate ?>&room_id=<?= $roomIdFilter ?>&therapist_id=<?= $therapistIdFilter ?>" class="btn btn-sm btn-dark-charcoal" title="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>

        <a href="appointment-form.php?date=<?= $currentDate ?>" class="btn btn-sm btn-gold">
            <i class="fa-solid fa-plus"></i> New Slot
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="luxury-card mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-3 align-items-center">
            <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
            <input type="hidden" name="date" value="<?= htmlspecialchars($currentDate) ?>">

            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-door-open"></i></span>
                    <select name="room_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Rooms & Suites</option>
                        <?php foreach ($allRooms as $rm): ?>
                            <option value="<?= $rm['id'] ?>" <?= $roomIdFilter === (int)$rm['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rm['name']) ?> (<?= ucfirst(str_replace('_', ' ', $rm['room_type'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-user-nurse"></i></span>
                    <select name="therapist_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Therapists</option>
                        <?php foreach ($allTherapists as $th): ?>
                            <option value="<?= $th['id'] ?>" <?= $therapistIdFilter === (int)$th['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($th['first_name'] . ' ' . $th['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-2 text-end">
                <?php if ($roomIdFilter || $therapistIdFilter): ?>
                    <a href="calendar.php?view=<?= $view ?>&date=<?= $currentDate ?>" class="btn btn-sm btn-outline-gold w-100">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Clear Filters
                    </a>
                <?php else: ?>
                    <span class="text-silver small"><i class="fa-solid fa-sliders text-gold me-1"></i>Filters Active</span>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Main Calendar Display Area -->
    <div class="col-12 col-lg-9">
        <?php if ($view === 'month'): ?>
            <!-- Month Calendar Grid -->
            <div class="calendar-grid">
                <div class="calendar-header-cell">Sun</div>
                <div class="calendar-header-cell">Mon</div>
                <div class="calendar-header-cell">Tue</div>
                <div class="calendar-header-cell">Wed</div>
                <div class="calendar-header-cell">Thu</div>
                <div class="calendar-header-cell">Fri</div>
                <div class="calendar-header-cell">Sat</div>

                <?php
                $iterDate = $startDate;
                $currentMonth = date('m', $timestamp);

                while ($iterDate <= $endDate):
                    $isOtherMonth = (date('m', strtotime($iterDate)) !== $currentMonth);
                    $isToday = ($iterDate === date('Y-m-d'));
                    $dayNum = date('j', strtotime($iterDate));
                    $dayEvents = $eventsByDate[$iterDate] ?? [];
                ?>
                    <div class="calendar-day-cell <?= $isOtherMonth ? 'other-month' : '' ?> <?= $isToday ? 'is-today' : '' ?>">
                        <div class="day-number">
                            <span class="num-badge"><?= $dayNum ?></span>
                            <a href="appointment-form.php?date=<?= $iterDate ?>" class="text-silver small" title="Create booking on this day">
                                <i class="fa-solid fa-plus fa-xs"></i>
                            </a>
                        </div>

                        <div class="events-container overflow-hidden">
                            <?php foreach (array_slice($dayEvents, 0, 3) as $ev): ?>
                                <a href="appointment-form.php?id=<?= $ev['id'] ?>" 
                                   class="calendar-event-pill pill-<?= $ev['status'] ?>" 
                                   title="<?= htmlspecialchars($ev['first_name'] . ' ' . $ev['last_name'] . ' - ' . $ev['service_name']) ?>">
                                    <strong><?= format_time($ev['start_time'], 'g:i') ?></strong> <?= htmlspecialchars($ev['first_name']) ?>
                                </a>
                            <?php endforeach; ?>

                            <?php if (count($dayEvents) > 3): ?>
                                <a href="?view=day&date=<?= $iterDate ?>" class="small text-gold fw-bold mt-1 d-block" style="font-size: 0.68rem;">
                                    +<?= count($dayEvents) - 3 ?> more...
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                    $iterDate = date('Y-m-d', strtotime('+1 day', strtotime($iterDate)));
                endwhile;
                ?>
            </div>

        <?php elseif ($view === 'week'): ?>
            <!-- Week Calendar Grid -->
            <div class="calendar-grid">
                <?php
                $iterDate = $startDate;
                while ($iterDate <= $endDate):
                    $isToday = ($iterDate === date('Y-m-d'));
                ?>
                    <div class="calendar-header-cell <?= $isToday ? 'bg-black text-gold' : '' ?>">
                        <div><?= date('D', strtotime($iterDate)) ?></div>
                        <div class="fs-6 fw-bold"><?= date('M j', strtotime($iterDate)) ?></div>
                    </div>
                <?php
                    $iterDate = date('Y-m-d', strtotime('+1 day', strtotime($iterDate)));
                endwhile;
                ?>

                <?php
                $iterDate = $startDate;
                while ($iterDate <= $endDate):
                    $isToday = ($iterDate === date('Y-m-d'));
                    $dayEvents = $eventsByDate[$iterDate] ?? [];
                ?>
                    <div class="calendar-day-cell <?= $isToday ? 'is-today' : '' ?>" style="min-height: 400px;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-silver small"><?= count($dayEvents) ?> booked</span>
                            <a href="appointment-form.php?date=<?= $iterDate ?>" class="btn btn-xs btn-outline-gold" style="font-size: 0.65rem;">+ Add</a>
                        </div>
                        <?php foreach ($dayEvents as $ev): ?>
                            <a href="appointment-form.php?id=<?= $ev['id'] ?>" class="calendar-event-pill pill-<?= $ev['status'] ?> mb-2 p-2">
                                <div class="fw-bold text-truncate"><?= htmlspecialchars($ev['first_name'] . ' ' . $ev['last_name']) ?></div>
                                <div class="small opacity-75"><?= format_time($ev['start_time']) ?> &bull; <?= htmlspecialchars($ev['room_name'] ?? 'No Suite') ?></div>
                                <div class="small text-truncate text-gold"><?= htmlspecialchars($ev['service_name']) ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php
                    $iterDate = date('Y-m-d', strtotime('+1 day', strtotime($iterDate)));
                endwhile;
                ?>
            </div>

        <?php else: ?>
            <!-- Day Detailed Schedule View -->
            <div class="luxury-card">
                <div class="card-header">
                    <h5><i class="fa-solid fa-clock text-gold me-2"></i>Day Schedule: <?= date('l, F j, Y', strtotime($currentDate)) ?></h5>
                    <a href="appointment-form.php?date=<?= $currentDate ?>" class="btn btn-sm btn-gold">+ Add to This Day</a>
                </div>
                <div class="card-body p-0">
                    <?php $dayEvents = $eventsByDate[$currentDate] ?? []; ?>
                    <?php if (empty($dayEvents)): ?>
                        <div class="text-center py-5 text-silver">
                            <i class="fa-solid fa-calendar-check fa-2x mb-2 d-block"></i>
                            No appointments on this date.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-luxury day-timeline-table">
                                <thead>
                                    <tr>
                                        <th>Window</th>
                                        <th>Guest</th>
                                        <th>Treatment</th>
                                        <th>Room</th>
                                        <th>Therapist</th>
                                        <th>Status</th>
                                        <th class="text-end">Manage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dayEvents as $ev): ?>
                                        <tr>
                                            <td class="text-gold fw-bold">
                                                <?= format_time($ev['start_time']) ?> – <?= format_time($ev['end_time']) ?>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-white"><?= htmlspecialchars($ev['first_name'] . ' ' . $ev['last_name']) ?></div>
                                                <small class="text-silver"><?= htmlspecialchars($ev['phone'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <div class="text-white"><?= htmlspecialchars($ev['service_name']) ?></div>
                                                <small class="text-silver"><?= $ev['duration_minutes'] ?> mins &bull; <?= $ev['guest_count'] ?> guest(s)</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-25 text-silver">
                                                    <?= htmlspecialchars($ev['room_name'] ?? 'Unassigned') ?>
                                                </span>
                                            </td>
                                            <td><?= $ev['therapist_fn'] ? htmlspecialchars($ev['therapist_fn'] . ' ' . $ev['therapist_ln']) : '<span class="text-muted">Auto-assign</span>' ?></td>
                                            <td>
                                                <span class="badge-status badge-<?= $ev['status'] ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $ev['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="appointment-form.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-dark-charcoal">
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
        <?php endif; ?>
    </div>

    <!-- Right Side: Today's Schedule Sidebar -->
    <div class="col-12 col-lg-3">
        <div class="luxury-card">
            <div class="card-header">
                <h5><i class="fa-solid fa-sun text-gold me-2"></i>Today's Agenda (<?= date('M d') ?>)</h5>
            </div>
            <div class="card-body p-3">
                <?php if (empty($todayList)): ?>
                    <p class="text-center text-silver small my-3">No bookings active for today.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush bg-transparent">
                        <?php foreach ($todayList as $tAppt): ?>
                            <div class="p-2 mb-2 rounded bg-black bg-opacity-25 border border-secondary border-opacity-25">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-gold small"><?= format_time($tAppt['start_time']) ?></span>
                                    <span class="badge-status badge-<?= $tAppt['status'] ?>" style="font-size: 0.65rem; padding: 2px 6px;">
                                        <?= ucfirst(str_replace('_', ' ', $tAppt['status'])) ?>
                                    </span>
                                </div>
                                <div class="fw-semibold text-white small"><?= htmlspecialchars($tAppt['first_name'] . ' ' . $tAppt['last_name']) ?></div>
                                <div class="text-silver small" style="font-size: 0.74rem;"><?= htmlspecialchars($tAppt['service_name']) ?></div>
                                <div class="text-muted small" style="font-size: 0.7rem;"><?= htmlspecialchars($tAppt['room_name'] ?? 'Room TBD') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <hr class="border-secondary border-opacity-25">
                <!-- Color Legend -->
                <div class="small">
                    <span class="text-gold fw-bold d-block mb-2">Status Legend:</span>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge-status badge-pending">Pending</span>
                        <span class="badge-status badge-confirmed">Confirmed</span>
                        <span class="badge-status badge-in_progress">In Progress</span>
                        <span class="badge-status badge-completed">Completed</span>
                        <span class="badge-status badge-cancelled">Cancelled</span>
                        <span class="badge-status badge-no_show">No Show</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
