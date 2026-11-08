<?php
/**
 * Bella Vista Oasis Spa - Appointments Management
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// RBAC: Admin, Manager, Front Desk allowed
check_role(['admin', 'manager', 'front_desk'], 'login.php');

$pdo = get_db_connection();

// --- Handle CSV Export if requested ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $where = ["1=1"];
    $params = [];

    $statusFilter = trim($_GET['status'] ?? '');
    if (!empty($statusFilter) && $statusFilter !== 'all') {
        $where[] = "a.status = ?";
        $params[] = $statusFilter;
    }

    $startDate = trim($_GET['start_date'] ?? '');
    if (!empty($startDate)) {
        $where[] = "a.appointment_date >= ?";
        $params[] = $startDate;
    }

    $endDate = trim($_GET['end_date'] ?? '');
    if (!empty($endDate)) {
        $where[] = "a.appointment_date <= ?";
        $params[] = $endDate;
    }

    $sql = "SELECT a.id, CONCAT(u.first_name, ' ', u.last_name) AS customer_name, u.email, u.phone,
                   s.name AS service_name, a.appointment_date, a.start_time, a.end_time,
                   r.name AS room_name, CONCAT(tu.first_name, ' ', tu.last_name) AS therapist_name,
                   a.guest_count, a.total_amount, a.status, a.created_at
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN customers c ON a.customer_id = c.id
            JOIN users u ON c.user_id = u.id
            LEFT JOIN rooms r ON a.room_id = r.id
            LEFT JOIN therapist_assignments ta ON a.id = ta.appointment_id
            LEFT JOIN therapists t ON ta.therapist_id = t.id
            LEFT JOIN employees te ON t.employee_id = te.id
            LEFT JOIN users tu ON te.user_id = tu.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY a.appointment_date DESC, a.start_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bella_vista_appointments_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Customer', 'Email', 'Phone', 'Service', 'Date', 'Start Time', 'End Time', 'Room', 'Therapist', 'Guests', 'Total Amount (PHP)', 'Status', 'Booked At']);

    foreach ($rows as $r) {
        fputcsv($output, [
            $r['id'],
            $r['customer_name'],
            $r['email'],
            $r['phone'],
            $r['service_name'],
            $r['appointment_date'],
            $r['start_time'],
            $r['end_time'],
            $r['room_name'] ?? 'N/A',
            $r['therapist_name'] ?? 'N/A',
            $r['guest_count'],
            $r['total_amount'],
            $r['status'],
            $r['created_at']
        ]);
    }
    fclose($output);
    exit;
}

// --- Filters & Pagination ---
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? 'all');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR a.id = ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = is_numeric($search) ? (int)$search : 0;
}

if (!empty($status) && $status !== 'all') {
    $where[] = "a.status = ?";
    $params[] = $status;
}

if (!empty($startDate)) {
    $where[] = "a.appointment_date >= ?";
    $params[] = $startDate;
}

if (!empty($endDate)) {
    $where[] = "a.appointment_date <= ?";
    $params[] = $endDate;
}

// Total Count
$countSql = "SELECT COUNT(*) 
             FROM appointments a
             JOIN services s ON a.service_id = s.id
             JOIN customers c ON a.customer_id = c.id
             JOIN users u ON c.user_id = u.id
             WHERE " . implode(' AND ', $where);
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();

// Pagination setup
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$totalPages = ceil($totalRecords / $perPage);
$offset = ($page - 1) * $perPage;

// Fetch Appointments
$sql = "SELECT a.*, s.name AS service_name, s.duration_minutes,
               u.first_name, u.last_name, u.email AS customer_email, u.phone AS customer_phone,
               r.name AS room_name,
               tu.first_name AS therapist_fn, tu.last_name AS therapist_ln,
               t.id AS therapist_id
        FROM appointments a
        JOIN services s ON a.service_id = s.id
        JOIN customers c ON a.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        LEFT JOIN rooms r ON a.room_id = r.id
        LEFT JOIN therapist_assignments ta ON a.id = ta.appointment_id
        LEFT JOIN therapists t ON ta.therapist_id = t.id
        LEFT JOIN employees te ON t.employee_id = te.id
        LEFT JOIN users tu ON te.user_id = tu.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY a.appointment_date DESC, a.start_time DESC
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

$pageTitle = 'Appointment Management';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs & Header Actions -->
<div class="breadcrumb-area">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Appointments</li>
            </ol>
        </nav>
        <h2 class="font-heading mb-0" style="font-size: 1.75rem;">Appointments Roster</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="appointments.php?export=csv&<?= http_build_query(array_filter($_GET)) ?>" class="btn btn-outline-gold">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </a>
        <a href="appointment-form.php" class="btn btn-gold">
            <i class="fa-solid fa-plus"></i> New Appointment
        </a>
    </div>
</div>

<!-- Filters & Search Form -->
<div class="luxury-card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="appointments.php" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Search Guest or ID</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Name, phone, or ID..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending Confirmation</option>
                    <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="no_show" <?= $status === 'no_show' ? 'selected' : '' ?>>No Show</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>

            <div class="col-12 col-md-2">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-gold w-100">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="appointments.php" class="btn btn-dark-charcoal" title="Reset Filters">
                    <i class="fa-solid fa-rotate"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Action Toolbar & Appointments Table -->
<form id="bulkForm" action="../process/admin_save_appointment.php" method="POST">
    <?= csrf_token_field() ?>
    <input type="hidden" name="action" value="bulk_update">

    <div class="luxury-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="text-silver small">Total: <strong class="text-gold"><?= number_format($totalRecords) ?></strong> bookings found</span>
            </div>

            <!-- Bulk Status Selector -->
            <div class="d-flex align-items-center gap-2">
                <select name="bulk_status" class="form-select form-select-sm" style="width: 170px;">
                    <option value="">Bulk Status Action...</option>
                    <option value="confirmed">Confirm Selected</option>
                    <option value="completed">Complete Selected</option>
                    <option value="cancelled">Cancel Selected</option>
                </select>
                <button type="button" class="btn btn-sm btn-outline-gold" onclick="submitBulkAction()">Apply</button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-luxury">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>ID</th>
                            <th>Guest</th>
                            <th>Service</th>
                            <th>Date & Time</th>
                            <th>Room</th>
                            <th>Therapist</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-silver">
                                    <i class="fa-solid fa-calendar-xmark fa-2x mb-2 d-block"></i>
                                    No appointments matched your search criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $appt): ?>
                                <tr id="row-appt-<?= $appt['id'] ?>">
                                    <td>
                                        <input type="checkbox" name="appointment_ids[]" value="<?= $appt['id'] ?>" class="form-check-input select-item">
                                    </td>
                                    <td>
                                        <a href="appointment-form.php?id=<?= $appt['id'] ?>" class="text-gold fw-bold">
                                            #<?= str_pad($appt['id'], 5, '0', STR_PAD_LEFT) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-white"><?= htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']) ?></div>
                                        <small class="text-silver d-block"><?= htmlspecialchars($appt['customer_phone'] ?? $appt['customer_email']) ?></small>
                                    </td>
                                    <td>
                                        <div class="text-white"><?= htmlspecialchars($appt['service_name']) ?></div>
                                        <small class="text-silver"><?= $appt['duration_minutes'] ?> mins &bull; <?= $appt['guest_count'] ?> guest(s)</small>
                                    </td>
                                    <td>
                                        <div class="text-white"><?= format_date($appt['appointment_date']) ?></div>
                                        <small class="text-gold"><?= format_time($appt['start_time']) ?> - <?= format_time($appt['end_time']) ?></small>
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
                                        <span class="badge-status badge-<?= htmlspecialchars($appt['status']) ?>" id="badge-<?= $appt['id'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $appt['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-white"><?= format_currency($appt['total_amount']) ?></td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-dark-charcoal" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-luxury">
                                                <li>
                                                    <a class="dropdown-item" href="appointment-form.php?id=<?= $appt['id'] ?>">
                                                        <i class="fa-solid fa-pen-to-square text-gold"></i> Edit Details
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-success" href="javascript:void(0)" onclick="quickUpdateStatus(<?= $appt['id'] ?>, 'confirmed')">
                                                        <i class="fa-solid fa-circle-check"></i> Confirm Booking
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-info" href="javascript:void(0)" onclick="quickUpdateStatus(<?= $appt['id'] ?>, 'in_progress')">
                                                        <i class="fa-solid fa-play"></i> Start Session
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-silver" href="javascript:void(0)" onclick="quickUpdateStatus(<?= $appt['id'] ?>, 'completed')">
                                                        <i class="fa-solid fa-flag-checkered"></i> Mark Completed
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-warning" href="javascript:void(0)" onclick="quickUpdateStatus(<?= $appt['id'] ?>, 'no_show')">
                                                        <i class="fa-solid fa-user-slash"></i> Mark No-Show
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="quickUpdateStatus(<?= $appt['id'] ?>, 'cancelled')">
                                                        <i class="fa-solid fa-ban"></i> Cancel Booking
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Footer -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-transparent border-top border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center">
                <span class="text-silver small">Showing page <?= $page ?> of <?= $totalPages ?></span>
                <nav>
                    <ul class="pagination pagination-luxury mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo;</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">&raquo;</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</form>

<script>
// Checkbox select all
document.getElementById('selectAll')?.addEventListener('change', function() {
    const isChecked = this.checked;
    document.querySelectorAll('.select-item').forEach(cb => cb.checked = isChecked);
});

// Bulk action submit
function submitBulkAction() {
    const checkedCount = document.querySelectorAll('.select-item:checked').length;
    const select = document.querySelector('select[name="bulk_status"]');
    if (checkedCount === 0) {
        alert('Please select at least one appointment from the list.');
        return;
    }
    if (!select.value) {
        alert('Please choose a status action to apply.');
        return;
    }
    if (confirm(`Are you sure you want to change ${checkedCount} appointment(s) to ${select.value}?`)) {
        document.getElementById('bulkForm').submit();
    }
}

// Quick AJAX status update
async function quickUpdateStatus(appointmentId, newStatus) {
    if (!confirm(`Are you sure you want to mark appointment #${appointmentId} as ${newStatus.replace('_', ' ')}?`)) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'quick_status');
        formData.append('appointment_id', appointmentId);
        formData.append('status', newStatus);

        const res = await apiRequest('../process/admin_save_appointment.php', formData);
        if (res.success) {
            const badge = document.getElementById(`badge-${appointmentId}`);
            if (badge) {
                badge.className = `badge-status badge-${newStatus}`;
                badge.textContent = newStatus.replace('_', ' ');
            }
            alert(res.message || 'Status updated successfully.');
        } else {
            alert(res.message || 'Error updating status.');
        }
    } catch (e) {
        alert('Request failed. Please try again.');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
