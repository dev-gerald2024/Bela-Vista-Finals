<?php
/**
 * Bella Vista Oasis Spa - Server-side Appointment Booking Processor
 * 
 * Validates multi-step booking data, re-checks availability to prevent race conditions,
 * atomically inserts records into appointments, appointment_guests, therapist_assignments,
 * room_schedules, cleaning_schedules, and health_declarations.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Auth check
if (!is_logged_in() || get_user_role() !== 'customer') {
    json_response(['success' => false, 'message' => 'Please sign in to complete your reservation.'], 401);
}

$pdo = get_db_connection();
$userId = (int)$_SESSION['user_id'];

// Ensure customer profile ID
if (empty($_SESSION['customer_id'])) {
    $cStmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ? LIMIT 1");
    $cStmt->execute([$userId]);
    $cRow = $cStmt->fetch();
    if ($cRow) {
        $_SESSION['customer_id'] = (int)$cRow['id'];
    } else {
        $ins = $pdo->prepare("INSERT INTO customers (user_id) VALUES (?)");
        $ins->execute([$userId]);
        $_SESSION['customer_id'] = (int)$pdo->lastInsertId();
    }
}
$customerId = (int)$_SESSION['customer_id'];

// Handle JSON input or form POST
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $input = json_decode($raw, true) ?? [];
    }
}

// CSRF Validation
$csrfToken = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!validate_csrf_token($csrfToken)) {
    json_response(['success' => false, 'message' => 'Security token expired or invalid. Please refresh the page.'], 403);
}

// 1. Extract & Sanitize Primary Fields
$serviceId = (int)($input['service_id'] ?? 0);
$appointmentDate = trim($input['appointment_date'] ?? '');
$startTime = trim($input['start_time'] ?? '');
$guestCount = max(1, (int)($input['guest_count'] ?? 1));
$preferredTherapistId = !empty($input['preferred_therapist_id']) ? (int)$input['preferred_therapist_id'] : null;
$clientRoomId = !empty($input['room_id']) ? (int)$input['room_id'] : null;
$notes = trim($input['notes'] ?? '');
$termsAccepted = !empty($input['terms_accepted']);

if (!$termsAccepted) {
    json_response(['success' => false, 'message' => 'You must accept the terms and conditions and cancellation policy to proceed.'], 422);
}

// 2. Fetch and Validate Service
$stService = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1 LIMIT 1");
$stService->execute([$serviceId]);
$service = $stService->fetch();

if (!$service) {
    json_response(['success' => false, 'message' => 'Selected spa treatment is invalid or currently unavailable.'], 404);
}

$duration = (int)$service['duration_minutes'];
$maxGuests = (int)$service['max_guests'];
if ($guestCount > $maxGuests) {
    json_response(['success' => false, 'message' => "This treatment accommodates a maximum of {$maxGuests} guests."], 422);
}

// 3. Validate Date & Operating Hours
$timeCheck = validate_booking_time($appointmentDate, $startTime, $duration);
if (!$timeCheck['valid']) {
    json_response(['success' => false, 'message' => $timeCheck['message']], 422);
}

$formattedStartTime = $timeCheck['start_time'];
$formattedEndTime = $timeCheck['end_time'];

$cleaningGap = (int)get_system_setting('cleaning_gap_minutes', DEFAULT_CLEANING_GAP_MIN);
$cleaningEndTimestamp = strtotime("{$appointmentDate} {$formattedEndTime} + {$cleaningGap} minutes");
$formattedCleaningEnd = date('H:i:s', $cleaningEndTimestamp);

// 4. Calculate Pricing
try {
    $pricing = calculate_total_amount($serviceId, $guestCount);
} catch (Exception $e) {
    json_response(['success' => false, 'message' => $e->getMessage()], 422);
}

$totalAmount = $pricing['total_amount'];
$additionalGuestCharges = $pricing['additional_guest_charges'];

// 5. Room Allocation & Re-check Availability (Anti-race condition)
$targetRoomType = 'single';
if ($service['room_type'] === 'couples') {
    $targetRoomType = 'couples_suite';
} elseif ($service['room_type'] === 'family') {
    $targetRoomType = 'family_lounge';
}

$stCandidateRooms = $pdo->prepare("SELECT id, name FROM rooms 
                                   WHERE room_type = ? AND is_active = 1 AND status != 'maintenance'");
$stCandidateRooms->execute([$targetRoomType]);
$candidateRooms = $stCandidateRooms->fetchAll();

if (empty($candidateRooms)) {
    json_response(['success' => false, 'message' => 'No treatment suites configured for this service.'], 422);
}

$assignedRoomId = null;

// If user or system suggested a room, check that first
if ($clientRoomId) {
    foreach ($candidateRooms as $cr) {
        if ((int)$cr['id'] === $clientRoomId && check_availability($clientRoomId, $appointmentDate, $formattedStartTime, $formattedEndTime)) {
            $assignedRoomId = $clientRoomId;
            break;
        }
    }
}

// Otherwise find the first available candidate room
if (!$assignedRoomId) {
    foreach ($candidateRooms as $cr) {
        $roomId = (int)$cr['id'];
        if (check_availability($roomId, $appointmentDate, $formattedStartTime, $formattedEndTime)) {
            $assignedRoomId = $roomId;
            break;
        }
    }
}

if (!$assignedRoomId) {
    json_response([
        'success' => false, 
        'message' => 'The selected suite slot was just booked by another guest. Please choose a different time slot.'
    ], 409);
}

// 6. Therapist Allocation & Verification
$assignedTherapistId = null;
if ($preferredTherapistId) {
    if (check_therapist_availability($preferredTherapistId, $appointmentDate, $formattedStartTime, $formattedEndTime)) {
        $assignedTherapistId = $preferredTherapistId;
    }
}

// If no preferred therapist or preferred is busy, try to auto-assign an available therapist
if (!$assignedTherapistId) {
    $stAvailTherapists = $pdo->prepare("SELECT t.id FROM therapists t 
                                        JOIN employees e ON t.employee_id = e.id 
                                        WHERE t.is_available = 1 AND e.employment_status = 'active'
                                        ORDER BY t.rating_avg DESC");
    $stAvailTherapists->execute();
    $therapistCandidates = $stAvailTherapists->fetchAll();

    foreach ($therapistCandidates as $tc) {
        $tid = (int)$tc['id'];
        if (check_therapist_availability($tid, $appointmentDate, $formattedStartTime, $formattedEndTime)) {
            $assignedTherapistId = $tid;
            break;
        }
    }
}

// 7. Atomic Database Transaction
try {
    $pdo->beginTransaction();

    // A. Insert Appointment
    $insAppt = $pdo->prepare("INSERT INTO appointments 
        (`customer_id`, `service_id`, `room_id`, `appointment_date`, `start_time`, `end_time`, `guest_count`, 
         `total_amount`, `additional_guest_charges`, `status`, `notes`, `preferred_therapist_id`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
    
    $insAppt->execute([
        $customerId,
        $serviceId,
        $assignedRoomId,
        $appointmentDate,
        $formattedStartTime,
        $formattedEndTime,
        $guestCount,
        $totalAmount,
        $additionalGuestCharges,
        $notes,
        $assignedTherapistId
    ]);

    $appointmentId = (int)$pdo->lastInsertId();

    // B. Insert Additional Guests
    $additionalGuests = $input['guests'] ?? [];
    if (is_array($additionalGuests) && !empty($additionalGuests)) {
        $insGuest = $pdo->prepare("INSERT INTO appointment_guests 
            (`appointment_id`, `guest_name`, `guest_email`, `guest_phone`) 
            VALUES (?, ?, ?, ?)");
        
        foreach ($additionalGuests as $g) {
            $gName = trim($g['name'] ?? '');
            if (!empty($gName)) {
                $gEmail = trim($g['email'] ?? '');
                $gPhone = trim($g['phone'] ?? '');
                $insGuest->execute([$appointmentId, $gName, $gEmail, $gPhone]);
            }
        }
    }

    // C. Insert Therapist Assignment (if allocated)
    if ($assignedTherapistId) {
        $insAssign = $pdo->prepare("INSERT INTO therapist_assignments 
            (`appointment_id`, `therapist_id`, `assigned_at`, `status`) 
            VALUES (?, ?, NOW(), 'assigned')");
        $insAssign->execute([$appointmentId, $assignedTherapistId]);
    }

    // D. Insert Room Schedule (covers appointment + cleaning gap buffer)
    $insRoomSched = $pdo->prepare("INSERT INTO room_schedules 
        (`room_id`, `appointment_id`, `date`, `start_time`, `end_time`, `includes_cleaning`) 
        VALUES (?, ?, ?, ?, ?, 1)");
    $insRoomSched->execute([
        $assignedRoomId,
        $appointmentId,
        $appointmentDate,
        $formattedStartTime,
        $formattedCleaningEnd
    ]);

    // E. Insert Cleaning Schedule
    $insClean = $pdo->prepare("INSERT INTO cleaning_schedules 
        (`room_id`, `appointment_id`, `scheduled_start`, `scheduled_end`, `date`, `status`) 
        VALUES (?, ?, ?, ?, ?, 'awaiting_cleaning')");
    $insClean->execute([
        $assignedRoomId,
        $appointmentId,
        $formattedEndTime,
        $formattedCleaningEnd,
        $appointmentDate
    ]);

    // F. Insert Health Declaration
    $hasMedical = !empty($input['has_medical_conditions']) ? 1 : 0;
    $medicalDetail = trim($input['medical_conditions_detail'] ?? '');
    $hasInjuries = !empty($input['has_injuries']) ? 1 : 0;
    $injuriesDetail = trim($input['injuries_detail'] ?? '');
    $isPregnant = !empty($input['is_pregnant']) ? 1 : 0;
    $pregnancyWeeks = ($isPregnant && !empty($input['pregnancy_weeks'])) ? (int)$input['pregnancy_weeks'] : null;
    $hasAllergies = !empty($input['has_allergies']) ? 1 : 0;
    $allergiesDetail = trim($input['allergies_detail'] ?? '');
    $healthNotes = trim($input['health_additional_notes'] ?? '');

    $insHealth = $pdo->prepare("INSERT INTO health_declarations 
        (`appointment_id`, `customer_id`, `has_medical_conditions`, `medical_conditions_detail`, 
         `has_injuries`, `injuries_detail`, `is_pregnant`, `pregnancy_weeks`, 
         `has_allergies`, `allergies_detail`, `additional_notes`, `declared_at`, `is_acknowledged`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
    
    $insHealth->execute([
        $appointmentId,
        $customerId,
        $hasMedical,
        $medicalDetail,
        $hasInjuries,
        $injuriesDetail,
        $isPregnant,
        $pregnancyWeeks,
        $hasAllergies,
        $allergiesDetail,
        $healthNotes
    ]);

    // G. Generate System Notification
    $refNumber = 'BVO-' . date('Ymd') . '-' . str_pad($appointmentId, 4, '0', STR_PAD_LEFT);
    $notifTitle = "Appointment Reserved (#{$refNumber})";
    $notifMsg = "Your booking for {$service['name']} on " . date('M d, Y', strtotime($appointmentDate)) . " at " . date('g:i A', strtotime($formattedStartTime)) . " is currently pending. Please proceed with payment.";
    $notifLink = "customer/pay.php?appointment_id={$appointmentId}";

    $insNotif = $pdo->prepare("INSERT INTO notifications (`user_id`, `title`, `message`, `type`, `is_read`, `link`) 
                               VALUES (?, ?, ?, 'booking', 0, ?)");
    $insNotif->execute([$userId, $notifTitle, $notifMsg, $notifLink]);

    // H. Audit Log
    log_audit(
        $userId,
        'APPOINTMENT_CREATE_CUSTOMER',
        'appointments',
        $appointmentId,
        null,
        [
            'service_id'       => $serviceId,
            'appointment_date' => $appointmentDate,
            'start_time'       => $formattedStartTime,
            'end_time'         => $formattedEndTime,
            'room_id'          => $assignedRoomId,
            'total_amount'     => $totalAmount,
            'guest_count'      => $guestCount
        ]
    );

    $pdo->commit();

    json_response([
        'success'        => true,
        'message'        => 'Your treatment appointment has been successfully booked.',
        'appointment_id' => $appointmentId,
        'reference'      => $refNumber,
        'total_amount'   => $totalAmount,
        'redirect'       => "pay.php?appointment_id={$appointmentId}&booked=1"
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Booking Exception: " . $e->getMessage());
    json_response([
        'success' => false,
        'message' => 'An unexpected server error occurred while processing your booking. Please try again.'
    ], 500);
}
