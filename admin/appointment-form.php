<?php
/**
 * Bella Vista Oasis Spa - Create / Edit Appointment Form
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

check_role(['admin', 'manager', 'front_desk'], 'login.php');

$pdo = get_db_connection();
$appointmentId = !empty($_GET['id']) ? (int)$_GET['id'] : null;
$isEdit = ($appointmentId !== null);

$appointment = null;
$assignedTherapistId = null;

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT a.*, c.id AS customer_id, u.first_name, u.last_name, u.email, u.phone
                           FROM appointments a
                           JOIN customers c ON a.customer_id = c.id
                           JOIN users u ON c.user_id = u.id
                           WHERE a.id = ? LIMIT 1");
    $stmt->execute([$appointmentId]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        flash_message('danger', 'Appointment not found.');
        redirect('admin/appointments.php');
    }

    // Get current therapist assignment
    $tStmt = $pdo->prepare("SELECT therapist_id FROM therapist_assignments WHERE appointment_id = ? LIMIT 1");
    $tStmt->execute([$appointmentId]);
    $assignedTherapistId = $tStmt->fetchColumn() ?: null;
}

// Fetch all active services
$servicesStmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY category, name ASC");
$services = $servicesStmt->fetchAll();

// Fetch all active rooms
$roomsStmt = $pdo->query("SELECT * FROM rooms WHERE is_active = 1 ORDER BY room_type, name ASC");
$rooms = $roomsStmt->fetchAll();

// Fetch all active therapists
$therapistsStmt = $pdo->query("SELECT t.id, t.specialization, t.rating_avg, u.first_name, u.last_name
                               FROM therapists t
                               JOIN employees e ON t.employee_id = e.id
                               JOIN users u ON e.user_id = u.id
                               WHERE t.is_available = 1 AND e.employment_status = 'active'
                               ORDER BY u.first_name ASC");
$therapists = $therapistsStmt->fetchAll();

// Fetch all customers for easy dropdown fallback
$custStmt = $pdo->query("SELECT c.id, u.first_name, u.last_name, u.email, u.phone 
                         FROM customers c 
                         JOIN users u ON c.user_id = u.id 
                         ORDER BY u.first_name ASC");
$customers = $custStmt->fetchAll();

$pageTitle = $isEdit ? "Edit Appointment #$appointmentId" : "Book New Appointment";
require_once __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb-area">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="appointments.php">Appointments</a></li>
                <li class="breadcrumb-item active"><?= $isEdit ? "Edit #$appointmentId" : "Create New" ?></li>
            </ol>
        </nav>
        <h2 class="font-heading mb-0" style="font-size: 1.75rem;">
            <?= $isEdit ? "Modify Appointment #$appointmentId" : "Create Bespoke Appointment" ?>
        </h2>
    </div>
    <div>
        <a href="appointments.php" class="btn btn-outline-gold">
            <i class="fa-solid fa-arrow-left"></i> Back to Roster
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="luxury-card">
            <div class="card-header">
                <h5><i class="fa-solid fa-calendar-check text-gold me-2"></i>Booking Particulars</h5>
                <span class="text-silver small"><?= $isEdit ? 'Update session details' : 'All times strictly buffered' ?></span>
            </div>
            <div class="card-body p-4">
                <form action="../process/admin_save_appointment.php" method="POST" id="appointmentForm">
                    <?= csrf_token_field() ?>
                    <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="appointment_id" value="<?= $appointmentId ?>">
                    <?php endif; ?>

                    <!-- Guest / Customer Selection -->
                    <div class="mb-4">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>Guest / Customer <span class="text-danger">*</span></span>
                            <a href="customers.php" target="_blank" class="small text-gold">+ Add New Guest</a>
                        </label>
                        <select name="customer_id" id="customerId" class="form-select" required>
                            <option value="">-- Choose Existing Guest --</option>
                            <?php foreach ($customers as $c): ?>
                                <?php
                                $selected = ($isEdit && (int)$appointment['customer_id'] === (int)$c['id']) ? 'selected' : '';
                                ?>
                                <option value="<?= $c['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?> (<?= htmlspecialchars($c['phone'] ?: $c['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Service Selection -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Ritual / Treatment Service <span class="text-danger">*</span></label>
                            <select name="service_id" id="serviceId" class="form-select" required onchange="handleServiceChange()">
                                <option value="">-- Select Luxury Service --</option>
                                <?php foreach ($services as $srv): ?>
                                    <?php
                                    $selected = ($isEdit && (int)$appointment['service_id'] === (int)$srv['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $srv['id'] ?>" 
                                            data-duration="<?= $srv['duration_minutes'] ?>"
                                            data-baseprice="<?= $srv['base_price'] ?>"
                                            data-maxguests="<?= $srv['max_guests'] ?>"
                                            data-addfee="<?= $srv['additional_guest_fee'] ?>"
                                            data-roomtype="<?= $srv['room_type'] ?>"
                                            <?= $selected ?>>
                                        <?= htmlspecialchars($srv['name']) ?> (<?= $srv['duration_minutes'] ?> mins &bull; <?= format_currency($srv['base_price']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Guest Count -->
                        <div class="col-12 col-md-4">
                            <label class="form-label">Number of Guests <span class="text-danger">*</span></label>
                            <input type="number" name="guest_count" id="guestCount" class="form-control" min="1" max="6" 
                                   value="<?= $isEdit ? (int)$appointment['guest_count'] : 1 ?>" required oninput="calculateLivePricing()">
                            <small class="text-silver" id="guestLimitHint">Subject to suite capacity</small>
                        </div>
                    </div>

                    <!-- Date & Time Slot -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Appointment Date <span class="text-danger">*</span></label>
                            <input type="date" name="appointment_date" id="appointmentDate" class="form-control" 
                                   min="<?= date('Y-m-d') ?>" 
                                   value="<?= $isEdit ? $appointment['appointment_date'] : date('Y-m-d') ?>" 
                                   required onchange="loadAvailableSlots()">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="startTime" class="form-control" 
                                   value="<?= $isEdit ? date('H:i', strtotime($appointment['start_time'])) : '13:00' ?>" 
                                   required onchange="calculateLivePricing()">
                            <small class="text-gold">Operating Hours: 1:00 PM – 9:00 PM</small>
                        </div>
                    </div>

                    <!-- Room Assignment & Therapist Assignment -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Treatment Room</label>
                            <select name="room_id" id="roomId" class="form-select">
                                <option value="">-- Auto-Assign Best Available Suite --</option>
                                <?php foreach ($rooms as $rm): ?>
                                    <?php
                                    $selected = ($isEdit && (int)$appointment['room_id'] === (int)$rm['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $rm['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($rm['name']) ?> (<?= ucfirst(str_replace('_', ' ', $rm['room_type'])) ?> &bull; Cap: <?= $rm['capacity'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-silver">Leave blank to automatically assign an available room.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Assigned Therapist</label>
                            <select name="therapist_id" id="therapistId" class="form-select">
                                <option value="">-- No Specific Preference (Auto) --</option>
                                <?php foreach ($therapists as $th): ?>
                                    <?php
                                    $selected = ($isEdit && (int)$assignedTherapistId === (int)$th['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $th['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($th['first_name'] . ' ' . $th['last_name']) ?> (★ <?= $th['rating_avg'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Status Selection (for Edit or Direct Confirm) -->
                    <div class="mb-4">
                        <label class="form-label">Appointment Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" <?= ($isEdit && $appointment['status'] === 'pending') ? 'selected' : '' ?>>Pending Confirmation</option>
                            <option value="confirmed" <?= (!$isEdit || $appointment['status'] === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                            <option value="in_progress" <?= ($isEdit && $appointment['status'] === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= ($isEdit && $appointment['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= ($isEdit && $appointment['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                            <option value="no_show" <?= ($isEdit && $appointment['status'] === 'no_show') ? 'selected' : '' ?>>No Show</option>
                        </select>
                    </div>

                    <!-- Special Requests & Health Notes -->
                    <div class="mb-4">
                        <label class="form-label">Concierge Notes & Guest Requests</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Aromatherapy preferences, pressure sensitivity, celebration notes..."><?= htmlspecialchars($appointment['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-3 border-top border-secondary border-opacity-25">
                        <a href="appointments.php" class="btn btn-dark-charcoal">Cancel</a>
                        <button type="submit" class="btn btn-gold">
                            <i class="fa-solid fa-circle-check"></i> <?= $isEdit ? 'Save Changes' : 'Confirm & Reserve Appointment' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Side: Live Price & Summary Widget -->
    <div class="col-12 col-lg-4">
        <div class="luxury-card">
            <div class="card-header">
                <h5><i class="fa-solid fa-receipt text-gold me-2"></i>Live Pricing Breakdown</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-silver">Base Service Price:</span>
                    <strong class="text-white" id="dispBasePrice">₱ 0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-silver">Guest Count:</span>
                    <strong class="text-white" id="dispGuests">1</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-silver">Additional Guest Charges:</span>
                    <strong class="text-white" id="dispAddCharges">₱ 0.00</strong>
                </div>
                <hr class="border-secondary border-opacity-25 my-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-gold fw-bold fs-6">Estimated Total:</span>
                    <span class="text-gold font-heading fs-4 fw-bold" id="dispTotalAmount">₱ 0.00</span>
                </div>
                <div class="p-3 rounded bg-black bg-opacity-25 border border-secondary border-opacity-25 text-silver small">
                    <i class="fa-solid fa-circle-info text-gold me-1"></i>
                    Family lounge includes up to 4 guests. Additional guests incur ₱750 per person up to max 6 guests. Single and Couples suites have fixed capacities.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleServiceChange() {
    const sSelect = document.getElementById('serviceId');
    const opt = sSelect.options[sSelect.selectedIndex];
    const maxGuests = opt.getAttribute('data-maxguests') || 1;
    const guestInput = document.getElementById('guestCount');

    guestInput.max = maxGuests;
    if (parseInt(guestInput.value) > parseInt(maxGuests)) {
        guestInput.value = maxGuests;
    }
    document.getElementById('guestLimitHint').textContent = `Max capacity for this service: ${maxGuests} guest(s)`;

    calculateLivePricing();
}

function calculateLivePricing() {
    const sSelect = document.getElementById('serviceId');
    const opt = sSelect.options[sSelect.selectedIndex];

    if (!opt || !opt.value) {
        document.getElementById('dispBasePrice').textContent = '₱ 0.00';
        document.getElementById('dispGuests').textContent = '1';
        document.getElementById('dispAddCharges').textContent = '₱ 0.00';
        document.getElementById('dispTotalAmount').textContent = '₱ 0.00';
        return;
    }

    const basePrice = parseFloat(opt.getAttribute('data-baseprice') || 0);
    const roomType = opt.getAttribute('data-roomtype') || 'single';
    const addFee = parseFloat(opt.getAttribute('data-addfee') || 0);
    const maxGuests = parseInt(opt.getAttribute('data-maxguests') || 1);

    let guests = parseInt(document.getElementById('guestCount').value) || 1;
    guests = Math.max(1, Math.min(guests, maxGuests));

    let extraCharges = 0;
    if (roomType === 'family' && guests > 4) {
        extraCharges = (guests - 4) * addFee;
    }

    const total = basePrice + extraCharges;

    document.getElementById('dispBasePrice').textContent = '₱ ' + basePrice.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('dispGuests').textContent = guests;
    document.getElementById('dispAddCharges').textContent = '₱ ' + extraCharges.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('dispTotalAmount').textContent = '₱ ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
}

document.addEventListener('DOMContentLoaded', function() {
    calculateLivePricing();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
