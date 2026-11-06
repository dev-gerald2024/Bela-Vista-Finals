<?php
/**
 * Bella Vista Oasis Spa - Real-time Time Slot & Availability API
 * 
 * Computes available booking slots for a selected date and service,
 * validating operating hours (13:00-21:00), service duration, 30-min cleaning buffer,
 * room availability, and therapist schedules.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_db_connection();

$date = trim($_GET['date'] ?? $_POST['date'] ?? '');
$serviceId = (int)($_GET['service_id'] ?? $_POST['service_id'] ?? 0);

if (empty($date) || $serviceId <= 0) {
    json_response(['success' => false, 'message' => 'Please specify both a valid date and service.'], 400);
}

// Validate date is not in the past
$today = date('Y-m-d');
if ($date < $today) {
    json_response(['success' => false, 'message' => 'Cannot retrieve slots for past dates.'], 400);
}

// Fetch service details
$stService = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1 LIMIT 1");
$stService->execute([$serviceId]);
$service = $stService->fetch();

if (!$service) {
    json_response(['success' => false, 'message' => 'Service not found or is currently inactive.'], 404);
}

$duration = (int)$service['duration_minutes'];
$cleaningGap = (int)get_system_setting('cleaning_gap_minutes', DEFAULT_CLEANING_GAP_MIN);
$opStart = get_system_setting('operating_hours_start', DEFAULT_OPERATING_START); // '13:00'
$opEnd = get_system_setting('operating_hours_end', DEFAULT_OPERATING_END);     // '21:00'

// Map service room type to rooms room_type
$targetRoomType = 'single';
if ($service['room_type'] === 'couples') {
    $targetRoomType = 'couples_suite';
} elseif ($service['room_type'] === 'family') {
    $targetRoomType = 'family_lounge';
}

// Fetch matching rooms
$stRooms = $pdo->prepare("SELECT id, name, room_type, capacity, floor FROM rooms 
                          WHERE room_type = ? AND is_active = 1 AND status != 'maintenance'");
$stRooms->execute([$targetRoomType]);
$rooms = $stRooms->fetchAll();

if (empty($rooms)) {
    json_response([
        'success' => false,
        'message' => 'No operational treatment suites found for this service category.'
    ]);
}

// Fetch all active therapists
$stTherapists = $pdo->prepare("SELECT t.id, t.specialization, t.rating_avg, u.first_name, u.last_name, t.photo_url
                               FROM therapists t
                               JOIN employees e ON t.employee_id = e.id
                               JOIN users u ON e.user_id = u.id
                               WHERE t.is_available = 1 AND e.employment_status = 'active'
                               ORDER BY t.rating_avg DESC");
$stTherapists->execute();
$allTherapists = $stTherapists->fetchAll();

// Generate candidate time slots in 30-min increments
$slots = [];
$currentStamp = strtotime("{$date} {$opStart}");
$closeStamp   = strtotime("{$date} {$opEnd}");
$nowStamp     = time();

while ($currentStamp < $closeStamp) {
    $slotStart = date('H:i:s', $currentStamp);
    $serviceEndStamp = $currentStamp + ($duration * 60);
    $cleaningEndStamp = $serviceEndStamp + ($cleaningGap * 60);
    $slotEnd = date('H:i:s', $serviceEndStamp);
    $cleaningEnd = date('H:i:s', $cleaningEndStamp);

    // Rule: service duration + 30 min cleaning must fit before 21:00
    if ($cleaningEndStamp > $closeStamp) {
        break; // Exceeds closing hours
    }

    // Past time check if date is today
    $isPast = false;
    if ($date === $today && ($currentStamp <= ($nowStamp + (DEFAULT_ARRIVAL_BUFFER_MIN * 60)))) {
        $isPast = true;
    }

    $availableRooms = [];
    $availableTherapists = [];

    if (!$isPast) {
        // Check room availability for this slot
        foreach ($rooms as $room) {
            if (check_availability((int)$room['id'], $date, $slotStart, $slotEnd)) {
                $availableRooms[] = [
                    'id'   => (int)$room['id'],
                    'name' => $room['name']
                ];
            }
        }

        // Check therapist availability for this slot
        foreach ($allTherapists as $therapist) {
            if (check_therapist_availability((int)$therapist['id'], $date, $slotStart, $slotEnd)) {
                $availableTherapists[] = [
                    'id'             => (int)$therapist['id'],
                    'name'           => $therapist['first_name'] . ' ' . $therapist['last_name'],
                    'specialization' => $therapist['specialization'],
                    'rating'         => (float)$therapist['rating_avg']
                ];
            }
        }
    }

    $isSlotAvailable = (!$isPast && !empty($availableRooms));

    $slots[] = [
        'start_time'           => date('H:i', $currentStamp),
        'start_time_raw'       => $slotStart,
        'end_time'             => date('H:i', $serviceEndStamp),
        'cleaning_end'         => date('H:i', $cleaningEndStamp),
        'display'              => date('g:i A', $currentStamp),
        'display_end'          => date('g:i A', $serviceEndStamp),
        'is_available'         => $isSlotAvailable,
        'reason'               => $isPast ? 'Slot in past' : (empty($availableRooms) ? 'Suites fully reserved' : 'Available'),
        'available_rooms'      => $availableRooms,
        'available_therapists' => $availableTherapists
    ];

    // Increment by 30 minutes
    $currentStamp = strtotime('+30 minutes', $currentStamp);
}

json_response([
    'success'              => true,
    'date'                 => $date,
    'service'              => [
        'id'               => (int)$service['id'],
        'name'             => $service['name'],
        'duration_minutes' => $duration,
        'room_type'        => $service['room_type']
    ],
    'slots'                => $slots,
    'available_count'      => count(array_filter($slots, fn($s) => $s['is_available']))
]);
