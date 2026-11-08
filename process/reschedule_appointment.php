<?php
/**
 * Bella Vista Oasis Spa - Reschedule Appointment Processor
 * 
 * Validates 6-hour rescheduling policy, operating hours, and room availability,
 * then atomically updates appointments, room_schedules, and cleaning_schedules.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Auth Guard
if (!is_logged_in() || get_user_role() !== 'customer') {
    json_response(['success' => false, 'message' => 'Please sign in to reschedule.'], 401);
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
$newDate = trim($_POST['new_date'] ?? '');
$newStartTime = trim($_POST['new_start_time'] ?? '');

if ($appointmentId <= 0 || empty($newDate) || empty($newStartTime)) {
    json_response(['success' => false, 'message' => 'Please provide appointment ID, new date, and time.'], 400);
}

// 1. Fetch existing appointment and verify ownership
$stAppt = $pdo->prepare("SELECT a.*, s.name AS service_name, s.duration_minutes, s.room_type 
                         FROM appointments a 
                         JOIN services s ON a.service_id = s.id 
                         WHERE a.id = ? AND a.customer_id = ? LIMIT 1");
$stAppt->execute([$appointmentId, $customerId]);
$appt = $stAppt->fetch();

if (!$appt) {
    json_response(['success' => false, 'message' => 'Appointment not found or unauthorized.'], 404);
}

// Check allowed status
if (!in_array($appt['status'], ['pending', 'confirmed'], true)) {
    json_response(['success' => false, 'message' => "Appointments with status '{$appt['status']}' cannot be rescheduled."], 422);
}

// 2. Validate 6-Hour Lead Time Policy on OLD Appointment
$cancellationHours = (int)get_system_setting('cancellation_hours', DEFAULT_CANCELLATION_HOURS);
$currentApptTimestamp = strtotime("{$appt['appointment_date']} {$appt['start_time']}");
$now = time();
$hoursUntil = ($currentApptTimestamp - $now) / 3600;

if ($hoursUntil < $cancellationHours) {
    json_response([
        'success' => false,
        'message' => "Rescheduling requires at least {$cancellationHours} hours advance notice. Less than {$cancellationHours} hours remain before your session. Please contact our concierge directly."
    ], 422);
}

// 3. Validate NEW Date & Operating Hours
$duration = (int)$appt['duration_minutes'];
$timeCheck = validate_booking_time($newDate, $newStartTime, $duration);
if (!$timeCheck['valid']) {
    json_response(['success' => false, 'message' => $timeCheck['message']], 422);
}

$formattedNewStart = $timeCheck['start_time'];
$formattedNewEnd = $timeCheck['end_time'];

$cleaningGap = (int)get_system_setting('cleaning_gap_minutes', DEFAULT_CLEANING_GAP_MIN);
$cleaningEndTimestamp = strtotime("{$newDate} {$formattedNewEnd} + {$cleaningGap} minutes");
$formattedCleaningEnd = date('H:i:s', $cleaningEndTimestamp);

// 4. Verify Room Availability for the New Slot (excluding current appointment)
$roomId = (int)$appt['room_id'];
if (!check_availability($roomId, $newDate, $formattedNewStart, $formattedNewEnd, $appointmentId)) {
    // Check if another room of the same type is available
    $targetRoomType = 'single';
    if ($appt['room_type'] === 'couples') {
        $targetRoomType = 'couples_suite';
    } elseif ($appt['room_type'] === 'family') {
        $targetRoomType = 'family_lounge';
    }

    $stRooms = $pdo->prepare("SELECT id FROM rooms WHERE room_type = ? AND is_active = 1 AND status != 'maintenance'");
    $stRooms->execute([$targetRoomType]);
    $altRooms = $stRooms->fetchAll();

    $newRoomId = null;
    foreach ($altRooms as $ar) {
        $candId = (int)$ar['id'];
        if (check_availability($candId, $newDate, $formattedNewStart, $formattedNewEnd, $appointmentId)) {
            $newRoomId = $candId;
            break;
        }
    }

    if (!$newRoomId) {
        json_response([
            'success' => false,
            'message' => 'No treatment suites are available for the selected date and time. Please pick another slot.'
        ], 409);
    }

    $roomId = $newRoomId;
}

// 5. Update appointment atomically
try {
    $pdo->beginTransaction();

    $oldValues = [
        'appointment_date' => $appt['appointment_date'],
        'start_time'       => $appt['start_time'],
        'end_time'         => $appt['end_time'],
        'room_id'          => $appt['room_id']
    ];

    // A. Update appointment
    $upAppt = $pdo->prepare("UPDATE appointments 
                             SET appointment_date = ?, start_time = ?, end_time = ?, room_id = ? 
                             WHERE id = ?");
    $upAppt->execute([$newDate, $formattedNewStart, $formattedNewEnd, $roomId, $appointmentId]);

    // B. Update room schedule
    $delRoomSched = $pdo->prepare("DELETE FROM room_schedules WHERE appointment_id = ?");
    $delRoomSched->execute([$appointmentId]);

    $insRoomSched = $pdo->prepare("INSERT INTO room_schedules 
        (`room_id`, `appointment_id`, `date`, `start_time`, `end_time`, `includes_cleaning`) 
        VALUES (?, ?, ?, ?, ?, 1)");
    $insRoomSched->execute([$roomId, $appointmentId, $newDate, $formattedNewStart, $formattedCleaningEnd]);

    // C. Update cleaning schedule
    $delClean = $pdo->prepare("DELETE FROM cleaning_schedules WHERE appointment_id = ?");
    $delClean->execute([$appointmentId]);

    $insClean = $pdo->prepare("INSERT INTO cleaning_schedules 
        (`room_id`, `appointment_id`, `scheduled_start`, `scheduled_end`, `date`, `status`) 
        VALUES (?, ?, ?, ?, ?, 'awaiting_cleaning')");
    $insClean->execute([$roomId, $appointmentId, $formattedNewEnd, $formattedCleaningEnd, $newDate]);

    // D. Notification
    $refNum = 'BVO-' . date('Ymd', strtotime($newDate)) . '-' . str_pad($appointmentId, 4, '0', STR_PAD_LEFT);
    $notifTitle = "Appointment Rescheduled (#{$refNum})";
    $notifMsg = "Your {$appt['service_name']} session has been rescheduled to " . date('M d, Y', strtotime($newDate)) . " at " . date('g:i A', strtotime($formattedNewStart)) . ".";
    
    $insNotif = $pdo->prepare("INSERT INTO notifications (`user_id`, `title`, `message`, `type`, `is_read`, `link`) 
                               VALUES (?, ?, ?, 'booking', 0, 'customer/appointments.php')");
    $insNotif->execute([$userId, $notifTitle, $notifMsg]);

    // E. Audit Log
    log_audit(
        $userId,
        'APPOINTMENT_RESCHEDULE_CUSTOMER',
        'appointments',
        $appointmentId,
        $oldValues,
        [
            'new_date'       => $newDate,
            'new_start_time' => $formattedNewStart,
            'new_end_time'   => $formattedNewEnd,
            'new_room_id'    => $roomId
        ]
    );

    $pdo->commit();

    json_response([
        'success'  => true,
        'message'  => 'Your appointment has been successfully rescheduled.',
        'redirect' => 'appointments.php'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Reschedule Exception: " . $e->getMessage());
    json_response([
        'success' => false,
        'message' => 'Server error occurred while rescheduling.'
    ], 500);
}
