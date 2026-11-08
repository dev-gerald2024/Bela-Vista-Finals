<?php
/**
 * Bella Vista Oasis Spa - Cancel Appointment Processor
 * 
 * Enforces the 6-hour minimum advance cancellation policy,
 * releases scheduled rooms and therapists, manages payment status,
 * issues notification, and records audit trail.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Auth Guard
if (!is_logged_in() || get_user_role() !== 'customer') {
    json_response(['success' => false, 'message' => 'Please sign in to cancel appointments.'], 401);
}

$pdo = get_db_connection();
$userId = (int)$_SESSION['user_id'];
$customerId = (int)($_SESSION['customer_id'] ?? 0);

// CSRF check
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!validate_csrf_token($csrfToken)) {
    json_response(['success' => false, 'message' => 'Invalid or expired security token.'], 403);
}

$appointmentId = (int)($_POST['appointment_id'] ?? 0);
$cancellationReason = trim($_POST['reason'] ?? 'Customer requested cancellation via portal');

if ($appointmentId <= 0) {
    json_response(['success' => false, 'message' => 'Invalid appointment reference.'], 400);
}

// 1. Fetch appointment & verify ownership
$stAppt = $pdo->prepare("SELECT a.*, s.name AS service_name 
                         FROM appointments a 
                         JOIN services s ON a.service_id = s.id 
                         WHERE a.id = ? AND a.customer_id = ? LIMIT 1");
$stAppt->execute([$appointmentId, $customerId]);
$appt = $stAppt->fetch();

if (!$appt) {
    json_response(['success' => false, 'message' => 'Appointment not found or you are not authorized to cancel it.'], 404);
}

// Check current status
if (in_array($appt['status'], ['cancelled', 'completed', 'no_show'], true)) {
    json_response(['success' => false, 'message' => "This appointment is already {$appt['status']} and cannot be cancelled."], 400);
}

// 2. Enforce 6-Hour Cancellation Policy
$cancellationLeadHours = (int)get_system_setting('cancellation_hours', DEFAULT_CANCELLATION_HOURS);
$appointmentDateTime = strtotime("{$appt['appointment_date']} {$appt['start_time']}");
$now = time();
$hoursUntil = ($appointmentDateTime - $now) / 3600;

if ($hoursUntil < $cancellationLeadHours) {
    json_response([
        'success' => false,
        'message' => "Cancellations must be requested at least {$cancellationLeadHours} hours before the appointment. Less than {$cancellationLeadHours} hours remain before your session. Please contact our concierge directly at +63 (02) 8876-5432."
    ], 422);
}

// 3. Perform cancellation atomically
try {
    $pdo->beginTransaction();

    $oldValues = [
        'status' => $appt['status'],
        'notes'  => $appt['notes']
    ];

    // A. Update appointment status
    $updatedNotes = trim(($appt['notes'] ?? '') . "\n[Cancelled by Customer on " . date('Y-m-d H:i:s') . ". Reason: {$cancellationReason}]");
    $upAppt = $pdo->prepare("UPDATE appointments SET status = 'cancelled', notes = ? WHERE id = ?");
    $upAppt->execute([$updatedNotes, $appointmentId]);

    // B. Free up room_schedules
    $delRoomSched = $pdo->prepare("DELETE FROM room_schedules WHERE appointment_id = ?");
    $delRoomSched->execute([$appointmentId]);

    // C. Free up cleaning_schedules
    $delClean = $pdo->prepare("DELETE FROM cleaning_schedules WHERE appointment_id = ?");
    $delClean->execute([$appointmentId]);

    // D. Update therapist assignments
    $upTherapist = $pdo->prepare("UPDATE therapist_assignments SET status = 'reassigned' WHERE appointment_id = ?");
    $upTherapist->execute([$appointmentId]);

    // E. Notification to Customer
    $refNum = 'BVO-' . date('Ymd', strtotime($appt['appointment_date'])) . '-' . str_pad($appointmentId, 4, '0', STR_PAD_LEFT);
    $notifTitle = "Appointment Cancelled (#{$refNum})";
    $notifMsg = "Your reservation for {$appt['service_name']} on " . date('M d, Y', strtotime($appt['appointment_date'])) . " at " . date('g:i A', strtotime($appt['start_time'])) . " has been successfully cancelled.";
    
    $insNotif = $pdo->prepare("INSERT INTO notifications (`user_id`, `title`, `message`, `type`, `is_read`, `link`) 
                               VALUES (?, ?, ?, 'booking', 0, 'customer/appointments.php')");
    $insNotif->execute([$userId, $notifTitle, $notifMsg]);

    // F. Audit Log
    log_audit(
        $userId,
        'APPOINTMENT_CANCEL_CUSTOMER',
        'appointments',
        $appointmentId,
        $oldValues,
        [
            'status' => 'cancelled',
            'reason' => $cancellationReason,
            'hours_lead_time' => round($hoursUntil, 2)
        ]
    );

    $pdo->commit();

    json_response([
        'success' => true,
        'message' => "Your appointment has been successfully cancelled and suite schedules have been released."
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Cancellation Exception: " . $e->getMessage());
    json_response([
        'success' => false,
        'message' => 'An unexpected server error occurred while processing the cancellation.'
    ], 500);
}
