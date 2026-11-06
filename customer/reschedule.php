<?php
/**
 * Bella Vista Oasis Spa - Reschedule Appointment Form
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
    flash_message('warning', 'Please sign in to reschedule your booking.');
    redirect('../login.php');
}

$pdo = get_db_connection();
$userId = (int)$_SESSION['user_id'];
$customerId = (int)($_SESSION['customer_id'] ?? 0);

$appointmentId = (int)($_GET['id'] ?? 0);
if ($appointmentId <= 0) {
    flash_message('danger', 'Invalid booking reference.');
    redirect('appointments.php');
}

// Fetch appointment details
$stAppt = $pdo->prepare("SELECT a.*, s.name AS service_name, s.duration_minutes, s.base_price,
                                r.name AS room_name, r.room_type,
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
    flash_message('danger', 'Booking not found or unauthorized.');
    redirect('appointments.php');
}

// Verify 6-hour policy
$cancellationHours = (int)get_system_setting('cancellation_hours', DEFAULT_CANCELLATION_HOURS);
$currentApptTimestamp = strtotime("{$appt['appointment_date']} {$appt['start_time']}");
$nowTimestamp = time();
$hoursUntil = ($currentApptTimestamp - $nowTimestamp) / 3600;
$canReschedule = ($hoursUntil >= $cancellationHours) && in_array($appt['status'], ['pending', 'confirmed'], true);

$pageTitle = 'Reschedule Treatment';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-9 col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="appointments.php" class="text-gold text-decoration-none small">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to My Bookings
                </a>
                <h1 class="h3 font-heading text-white mt-2 mb-0">Reschedule Sanctuary Treatment</h1>
            </div>
            <span class="badge-status badge-<?= htmlspecialchars($appt['status'], ENT_QUOTES) ?>">
                <?= ucfirst(str_replace('_', ' ', $appt['status'])) ?>
            </span>
        </div>

        <?php if (!$canReschedule): ?>
            <div class="card-luxury p-4 p-md-5 text-center mb-4">
                <i class="fa-solid fa-clock-rotate-left text-warning fs-1 mb-3"></i>
                <h2 class="h4 font-heading text-white mb-2">Online Rescheduling Unavailable</h2>
                <p class="text-muted mb-4" style="max-width: 550px; margin: 0 auto;">
                    According to sanctuary policy, online rescheduling requires at least <strong><?= $cancellationHours ?> hours advance notice</strong>. 
                    Your session is scheduled for <?= format_date($appt['appointment_date']) ?> at <?= format_time($appt['start_time']) ?>.
                </p>
                <div class="p-3 rounded-2 text-start mx-auto mb-4" style="max-width: 480px; background: rgba(16, 19, 19, 0.6); border: 1px solid rgba(214, 183, 123, 0.2);">
                    <div class="small text-gold fw-semibold mb-1">Need Urgent Assistance?</div>
                    <p class="small text-muted mb-0">
                        Please contact our front desk concierge directly at <strong>+63 (02) 8876-5432</strong> or email <strong>concierge@bellavistaspa.com</strong> for special accommodation requests.
                    </p>
                </div>
                <div>
                    <a href="appointments.php" class="btn btn-gold">
                        <i class="fa-solid fa-arrow-left me-2"></i> Return to Appointments
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Current Appointment Summary Card -->
            <div class="card-luxury p-4 mb-4">
                <h2 class="h6 font-heading text-gold mb-3 pb-2 border-bottom" style="border-color: rgba(214, 183, 123, 0.2) !important;">
                    <i class="fa-solid fa-calendar-check me-2"></i> Current Reservation Details
                </h2>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Treatment Ritual:</span>
                        <strong class="text-white fs-5"><?= htmlspecialchars($appt['service_name'], ENT_QUOTES) ?></strong>
                        <div class="text-muted small"><?= $appt['duration_minutes'] ?> minutes session</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Currently Scheduled For:</span>
                        <strong class="text-gold"><?= format_date($appt['appointment_date'], 'l, F j, Y') ?></strong>
                        <div class="text-gold-light small"><?= format_time($appt['start_time']) ?> &ndash; <?= format_time($appt['end_time']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Treatment Suite:</span>
                        <strong class="text-white"><?= htmlspecialchars($appt['room_name'], ENT_QUOTES) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Therapist Specialist:</span>
                        <strong class="text-white">
                            <?= !empty($appt['therapist_first_name']) ? htmlspecialchars($appt['therapist_first_name'] . ' ' . $appt['therapist_last_name'], ENT_QUOTES) : 'Assigned Master Specialist' ?>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Reschedule Selection Card -->
            <div class="card-luxury p-4 p-md-5">
                <h2 class="h5 font-heading text-white mb-2">Select Your New Date & Time</h2>
                <p class="text-muted small mb-4">Choose a new date and available operating slot (1:00 PM – 9:00 PM). Existing payment and guest details will be automatically preserved.</p>

                <form id="rescheduleForm" onsubmit="event.preventDefault(); submitReschedule();">
                    <?= csrf_token_field() ?>
                    <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">
                    <input type="hidden" name="service_id" id="serviceId" value="<?= $appt['service_id'] ?>">
                    <input type="hidden" name="new_start_time" id="newStartTime" value="">

                    <div class="row g-4 mb-4">
                        <!-- Date Picker -->
                        <div class="col-md-5">
                            <label for="newDate" class="form-label form-label-luxury">
                                <i class="fa-regular fa-calendar text-gold me-1"></i> New Appointment Date
                            </label>
                            <input type="date" 
                                   id="newDate" 
                                   name="new_date" 
                                   class="form-control form-control-luxury mb-3" 
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                   value="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                   onchange="fetchSlots()">

                            <div class="p-3 rounded-2 small text-muted" style="background: rgba(16, 19, 19, 0.6); border: 1px dashed rgba(214, 183, 123, 0.2);">
                                <strong class="text-gold d-block mb-1">Operating Hours</strong>
                                <span>Daily 1:00 PM &ndash; 9:00 PM</span><br>
                                <span>Includes mandatory 30m suite sanitization</span>
                            </div>
                        </div>

                        <!-- Slots Grid -->
                        <div class="col-md-7">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label form-label-luxury mb-0">
                                    <i class="fa-regular fa-clock text-gold me-1"></i> Available Slots
                                </label>
                                <span id="slotIndicator" class="small text-muted">Checking...</span>
                            </div>

                            <div id="slotsGrid" class="row g-2 mb-3">
                                <div class="col-12 text-center py-4 text-muted">
                                    <i class="fa-solid fa-spinner fa-spin text-gold fs-4 mb-2"></i>
                                    <p class="small mb-0">Loading available slots...</p>
                                </div>
                            </div>

                            <!-- Selected Slot Preview -->
                            <div id="slotPreview" class="p-3 rounded-2 d-none" style="background: rgba(214, 183, 123, 0.08); border: 1px solid rgba(214, 183, 123, 0.3);">
                                <span class="text-gold fw-bold" id="slotPreviewText"></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top" style="border-color: rgba(214, 183, 123, 0.15) !important;">
                        <a href="appointments.php" class="btn btn-charcoal px-4">Cancel</a>
                        <button type="submit" class="btn btn-gold px-5" id="btnSubmitReschedule">
                            <i class="fa-solid fa-calendar-check me-2"></i> Save Rescheduled Appointment
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($canReschedule): ?>
        fetchSlots();
    <?php endif; ?>
});

function fetchSlots() {
    const serviceId = document.getElementById('serviceId').value;
    const date = document.getElementById('newDate').value;
    const grid = document.getElementById('slotsGrid');
    const indicator = document.getElementById('slotIndicator');

    if (!date) return;

    indicator.innerText = 'Checking...';
    grid.innerHTML = '<div class="col-12 text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin text-gold fs-4 mb-2"></i><p class="small mb-0">Checking schedule...</p></div>';

    fetch(`../process/get_time_slots.php?date=${encodeURIComponent(date)}&service_id=${encodeURIComponent(serviceId)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                indicator.innerText = 'Unavailable';
                grid.innerHTML = `<div class="col-12 text-center py-4 text-danger">${data.message}</div>`;
                return;
            }

            indicator.innerText = `${data.available_count} slots open`;
            grid.innerHTML = '';

            data.slots.forEach(slot => {
                const col = document.createElement('div');
                col.className = 'col-6 col-sm-4';

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `slot-btn w-100 ${slot.is_available ? '' : 'disabled'}`;
                btn.disabled = !slot.is_available;
                btn.innerHTML = `<span>${slot.display}</span>`;

                if (slot.is_available) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                        btn.classList.add('selected');
                        document.getElementById('newStartTime').value = slot.start_time_raw;
                        
                        const preview = document.getElementById('slotPreview');
                        const previewText = document.getElementById('slotPreviewText');
                        previewText.innerText = `Selected Slot: ${slot.display} until ${slot.display_end}`;
                        preview.classList.remove('d-none');
                    });
                }

                col.appendChild(btn);
                grid.appendChild(col);
            });
        })
        .catch(err => {
            indicator.innerText = 'Error';
            grid.innerHTML = '<div class="col-12 text-center py-4 text-danger">Could not load slots.</div>';
        });
}

function submitReschedule() {
    const newStartTime = document.getElementById('newStartTime').value;
    if (!newStartTime) {
        alert('Please select an available time slot.');
        return;
    }

    const btn = document.getElementById('btnSubmitReschedule');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Updating Schedule...';

    const formData = new FormData(document.getElementById('rescheduleForm'));

    fetch('../process/reschedule_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Rescheduling failed.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-calendar-check me-2"></i> Save Rescheduled Appointment';
            return;
        }

        alert(data.message);
        window.location.href = data.redirect || 'appointments.php';
    })
    .catch(err => {
        alert('Server communication error.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-calendar-check me-2"></i> Save Rescheduled Appointment';
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
