<?php
/**
 * Bella Vista Oasis Spa - Customer Appointments History & Management
 */

require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Auth Guard
if (!is_logged_in() || get_user_role() !== 'customer') {
    flash_message('warning', 'Please sign in to view your bookings.');
    redirect('../login.php');
}

$pdo = get_db_connection();
$userId = (int)$_SESSION['user_id'];
$customerId = (int)($_SESSION['customer_id'] ?? 0);

$activeTab = $_GET['tab'] ?? 'upcoming';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Cancellation policy lead hours
$cancellationLeadHours = (int)get_system_setting('cancellation_hours', DEFAULT_CANCELLATION_HOURS);
$nowTimestamp = time();

// Count upcoming
$stCountUp = $pdo->prepare("SELECT COUNT(*) FROM appointments 
                            WHERE customer_id = ? 
                              AND (appointment_date > CURDATE() OR (appointment_date = CURDATE() AND end_time >= CURTIME()))
                              AND status IN ('pending', 'confirmed', 'in_progress')");
$stCountUp->execute([$customerId]);
$totalUpcoming = (int)$stCountUp->fetchColumn();

// Count past
$stCountPast = $pdo->prepare("SELECT COUNT(*) FROM appointments 
                              WHERE customer_id = ? 
                                AND ((appointment_date < CURDATE()) OR (appointment_date = CURDATE() AND end_time < CURTIME()) OR status IN ('completed', 'cancelled', 'no_show'))");
$stCountPast->execute([$customerId]);
$totalPast = (int)$stCountPast->fetchColumn();

// Fetch appointments based on active tab
if ($activeTab === 'past') {
    $totalRecords = $totalPast;
    $sql = "SELECT a.*, s.name AS service_name, s.duration_minutes, r.name AS room_name,
                   tu.first_name AS therapist_first_name, tu.last_name AS therapist_last_name,
                   (SELECT COUNT(*) FROM reviews rev WHERE rev.appointment_id = a.id) AS review_count
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN rooms r ON a.room_id = r.id
            LEFT JOIN therapist_assignments ta ON ta.appointment_id = a.id AND ta.status = 'assigned'
            LEFT JOIN therapists t ON ta.therapist_id = t.id
            LEFT JOIN employees te ON t.employee_id = te.id
            LEFT JOIN users tu ON te.user_id = tu.id
            WHERE a.customer_id = ? 
              AND ((a.appointment_date < CURDATE()) OR (a.appointment_date = CURDATE() AND a.end_time < CURTIME()) OR a.status IN ('completed', 'cancelled', 'no_show'))
            ORDER BY a.appointment_date DESC, a.start_time DESC
            LIMIT ? OFFSET ?";
} else {
    $totalRecords = $totalUpcoming;
    $sql = "SELECT a.*, s.name AS service_name, s.duration_minutes, r.name AS room_name,
                   tu.first_name AS therapist_first_name, tu.last_name AS therapist_last_name,
                   0 AS review_count
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
            LIMIT ? OFFSET ?";
}

$stAppts = $pdo->prepare($sql);
$stAppts->bindValue(1, $customerId, PDO::PARAM_INT);
$stAppts->bindValue(2, $perPage, PDO::PARAM_INT);
$stAppts->bindValue(3, $offset, PDO::PARAM_INT);
$stAppts->execute();
$appointments = $stAppts->fetchAll();

$totalPages = max(1, ceil($totalRecords / $perPage));

$pageTitle = 'My Sanctuary Bookings';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h1 class="h3 font-heading text-white mb-1">Reservation Schedule & History</h1>
            <p class="text-muted small mb-0">View scheduled holistic experiences, review history, or reschedule sessions.</p>
        </div>
        <a href="book.php" class="btn btn-gold">
            <i class="fa-solid fa-calendar-plus me-1"></i> Book New Experience
        </a>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'upcoming' ? 'active btn-gold' : 'btn-charcoal' ?>" 
           href="appointments.php?tab=upcoming" 
           style="border-radius: 8px;">
            <i class="fa-solid fa-calendar-days me-1"></i> Upcoming Experiences
            <span class="badge bg-warning text-dark ms-2"><?= $totalUpcoming ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'past' ? 'active btn-gold' : 'btn-charcoal' ?>" 
           href="appointments.php?tab=past" 
           style="border-radius: 8px;">
            <i class="fa-solid fa-clock-rotate-left me-1"></i> Completed & Past
            <span class="badge bg-secondary ms-2"><?= $totalPast ?></span>
        </a>
    </li>
</ul>

<!-- Appointments Table Card -->
<div class="card-luxury">
    <div class="table-responsive">
        <table class="table-luxury">
            <thead>
                <tr>
                    <th>Ref / Ritual</th>
                    <th>Schedule Date</th>
                    <th>Time Window</th>
                    <th>Suite / Therapist</th>
                    <th>Investment</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($appointments)): ?>
                    <?php foreach ($appointments as $appt): ?>
                        <?php
                            $apptTimestamp = strtotime("{$appt['appointment_date']} {$appt['start_time']}");
                            $hoursUntilAppt = ($apptTimestamp - $nowTimestamp) / 3600;
                            $canModify = ($hoursUntilAppt >= $cancellationLeadHours) && in_array($appt['status'], ['pending', 'confirmed'], true);
                        ?>
                        <tr>
                            <td>
                                <strong class="text-white d-block">
                                    <?= htmlspecialchars($appt['service_name'], ENT_QUOTES) ?>
                                </strong>
                                <span class="text-muted small">
                                    #BVO-<?= str_pad($appt['id'], 5, '0', STR_PAD_LEFT) ?> &bull; <?= $appt['duration_minutes'] ?> min &bull; <?= $appt['guest_count'] ?> <?= $appt['guest_count'] > 1 ? 'Guests' : 'Guest' ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-white"><?= format_date($appt['appointment_date'], 'M d, Y') ?></strong>
                                <div class="text-muted small"><?= date('l', strtotime($appt['appointment_date'])) ?></div>
                            </td>
                            <td>
                                <span class="text-gold-light small">
                                    <?= format_time($appt['start_time']) ?> &ndash; <?= format_time($appt['end_time']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small text-white">
                                    <i class="fa-solid fa-door-closed text-gold me-1"></i><?= htmlspecialchars($appt['room_name'], ENT_QUOTES) ?>
                                </div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-user-nurse text-gold me-1"></i>
                                    <?= !empty($appt['therapist_first_name']) ? htmlspecialchars($appt['therapist_first_name'] . ' ' . $appt['therapist_last_name'], ENT_QUOTES) : 'Assigned' ?>
                                </div>
                            </td>
                            <td>
                                <strong class="text-gold"><?= format_currency($appt['total_amount']) ?></strong>
                            </td>
                            <td>
                                <span class="badge-status badge-<?= htmlspecialchars($appt['status'], ENT_QUOTES) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $appt['status'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-charcoal btn-sm dropdown-toggle py-1 px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Options
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg" style="background: #171A1B; border: 1px solid rgba(214, 183, 123, 0.3);">
                                        <li>
                                            <a class="dropdown-item py-2" href="javascript:void(0)" onclick="viewAppointmentDetails(<?= $appt['id'] ?>)">
                                                <i class="fa-solid fa-circle-info text-gold me-2"></i> View Details
                                            </a>
                                        </li>

                                        <?php if ($appt['status'] === 'pending'): ?>
                                            <li>
                                                <a class="dropdown-item py-2" href="pay.php?appointment_id=<?= $appt['id'] ?>">
                                                    <i class="fa-solid fa-credit-card text-gold me-2"></i> Make Payment
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if ($canModify): ?>
                                            <li>
                                                <a class="dropdown-item py-2" href="reschedule.php?id=<?= $appt['id'] ?>">
                                                    <i class="fa-solid fa-calendar-week text-gold me-2"></i> Reschedule
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider" style="border-color: rgba(214, 183, 123, 0.15);"></li>
                                            <li>
                                                <a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="openCancelModal(<?= $appt['id'] ?>, '<?= htmlspecialchars($appt['service_name'], ENT_QUOTES) ?>', '<?= format_date($appt['appointment_date']) ?>')">
                                                    <i class="fa-solid fa-ban me-2"></i> Cancel Reservation
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if ($appt['status'] === 'completed' && (int)$appt['review_count'] === 0): ?>
                                            <li>
                                                <a class="dropdown-item py-2 text-gold" href="reviews.php?appointment_id=<?= $appt['id'] ?>">
                                                    <i class="fa-solid fa-star me-2"></i> Leave Guest Review
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-calendar-xmark fs-2 mb-3 text-gold opacity-50"></i>
                            <p class="mb-2">No <?= $activeTab === 'upcoming' ? 'upcoming' : 'past' ?> appointments recorded.</p>
                            <?php if ($activeTab === 'upcoming'): ?>
                                <a href="book.php" class="btn btn-gold btn-sm mt-2">
                                    <i class="fa-solid fa-plus me-1"></i> Book a Sanctuary Experience
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="p-3 border-top d-flex justify-content-between align-items-center" style="border-color: rgba(214, 183, 123, 0.15) !important;">
            <span class="text-muted small">Showing Page <?= $page ?> of <?= $totalPages ?> (<?= $totalRecords ?> total records)</span>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link btn-charcoal" href="appointments.php?tab=<?= $activeTab ?>&page=<?= $page - 1 ?>">&laquo; Prev</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                        <a class="page-link <?= ($i === $page) ? 'btn-gold' : 'btn-charcoal' ?>" href="appointments.php?tab=<?= $activeTab ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link btn-charcoal" href="appointments.php?tab=<?= $activeTab ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Cancel Appointment Confirmation -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-luxury">
            <div class="modal-header modal-header-luxury">
                <h5 class="modal-title font-heading text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Cancel Reservation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-white mb-2">Are you sure you wish to cancel this appointment?</p>
                <div class="p-3 rounded-2 mb-3" style="background: rgba(16, 19, 19, 0.7); border: 1px solid rgba(214, 183, 123, 0.2);">
                    <strong class="text-gold d-block" id="cancelModalService"></strong>
                    <span class="text-muted small" id="cancelModalDate"></span>
                </div>
                
                <div class="mb-3">
                    <label for="cancelReason" class="form-label form-label-luxury">Reason for cancellation (Optional)</label>
                    <textarea id="cancelReason" class="form-control form-control-luxury" rows="2" placeholder="Tell us how we can assist..."></textarea>
                </div>

                <div class="p-2 rounded-1 small text-muted" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25);">
                    <i class="fa-solid fa-circle-exclamation text-danger me-1"></i>
                    Subject to our 6-hour cancellation terms. Suites and therapist assignments will be released immediately.
                </div>
                <input type="hidden" id="cancelModalApptId" value="">
            </div>
            <div class="modal-footer modal-footer-luxury">
                <button type="button" class="btn btn-charcoal" data-bs-dismiss="modal">Keep Booking</button>
                <button type="button" class="btn btn-danger" id="btnConfirmCancel" onclick="executeCancellation()">
                    <span>Confirm Cancellation</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: View Appointment Details -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-luxury">
            <div class="modal-header modal-header-luxury">
                <h5 class="modal-title font-heading text-gold">
                    <i class="fa-solid fa-spa me-2"></i> Sanctuary Booking Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detailsModalBody">
                <div class="text-center py-4">
                    <i class="fa-solid fa-spinner fa-spin text-gold fs-4"></i>
                </div>
            </div>
            <div class="modal-footer modal-footer-luxury">
                <button type="button" class="btn btn-charcoal" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let cancelModalInstance = null;
let detailsModalInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    cancelModalInstance = new bootstrap.Modal(document.getElementById('cancelModal'));
    detailsModalInstance = new bootstrap.Modal(document.getElementById('detailsModal'));

    // If query view parameter is passed
    const urlParams = new URLSearchParams(window.location.search);
    const viewId = urlParams.get('view');
    if (viewId) {
        viewAppointmentDetails(viewId);
    }
});

function openCancelModal(id, service, date) {
    document.getElementById('cancelModalApptId').value = id;
    document.getElementById('cancelModalService').innerText = service;
    document.getElementById('cancelModalDate').innerText = date;
    document.getElementById('cancelReason').value = '';
    cancelModalInstance.show();
}

function executeCancellation() {
    const id = document.getElementById('cancelModalApptId').value;
    const reason = document.getElementById('cancelReason').value;
    const btn = document.getElementById('btnConfirmCancel');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Cancelling...';

    const formData = new FormData();
    formData.append('appointment_id', id);
    formData.append('reason', reason);
    formData.append('csrf_token', '<?= get_csrf_token() ?>');

    fetch('../process/cancel_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Cancellation failed.');
            btn.disabled = false;
            btn.innerHTML = 'Confirm Cancellation';
            return;
        }

        cancelModalInstance.hide();
        alert(data.message);
        location.reload();
    })
    .catch(err => {
        alert('Server communication error.');
        btn.disabled = false;
        btn.innerHTML = 'Confirm Cancellation';
    });
}

function viewAppointmentDetails(id) {
    const body = document.getElementById('detailsModalBody');
    body.innerHTML = '<div class="text-center py-5"><i class="fa-solid fa-spinner fa-spin text-gold fs-3 mb-2"></i><p class="text-muted small">Loading booking record...</p></div>';
    detailsModalInstance.show();

    fetch(`../process/get_appointment_details.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                return;
            }

            const a = data.appointment;
            body.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Service Name</span>
                        <h4 class="h5 font-heading text-gold mb-1">${a.service_name}</h4>
                        <span class="badge-status badge-${a.status}">${a.status_label}</span>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="text-muted small d-block">Booking Reference</span>
                        <strong class="fs-5 font-monospace text-white">${a.reference_number}</strong>
                    </div>
                    <div class="col-12"><hr style="border-color: rgba(214, 183, 123, 0.2);"></div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Date & Time</span>
                        <strong class="text-white">${a.formatted_date}</strong>
                        <div class="text-gold-light small">${a.formatted_time}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Suite / Room</span>
                        <strong class="text-white">${a.room_name} (${a.room_type})</strong>
                        <div class="text-muted small">${a.room_floor}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Assigned Specialist</span>
                        <strong class="text-white">${a.therapist_name}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Guest Attendance</span>
                        <strong class="text-white">${a.guest_count} ${a.guest_count > 1 ? 'Guests' : 'Guest'}</strong>
                    </div>
                    <div class="col-12"><hr style="border-color: rgba(214, 183, 123, 0.2);"></div>
                    <div class="col-12">
                        <div class="p-3 rounded-2" style="background: rgba(16, 19, 19, 0.6); border: 1px solid rgba(214, 183, 123, 0.15);">
                            <div class="d-flex justify-content-between mb-1 small text-muted">
                                <span>Base Ritual Price:</span>
                                <span>${a.formatted_base_price}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1 small text-muted">
                                <span>Additional Guest Charges:</span>
                                <span>${a.formatted_guest_charges}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2 border-top fw-bold text-gold fs-5" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                                <span>Total Amount:</span>
                                <span>${a.formatted_total_amount}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            body.innerHTML = '<div class="alert alert-danger">Error retrieving appointment details.</div>';
        });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
