<?php
/**
 * Bella Vista Oasis Spa - AJAX Endpoint: Check Room Availability
 * 
 * Verifies operating hours and suite availability including the 30-minute cleaning buffer.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$date = trim($_GET['date'] ?? '');
$startTime = trim($_GET['start_time'] ?? '');
$duration = isset($_GET['duration']) ? (int)$_GET['duration'] : 60;
$excludeApptId = isset($_GET['exclude_appointment_id']) ? (int)$_GET['exclude_appointment_id'] : null;

if (!$roomId || empty($date) || empty($startTime) || $duration <= 0) {
    json_response([
        'available' => false,
        'message'   => 'Missing required parameters (room_id, date, start_time, duration).'
    ], 400);
}

// 1. Validate operating hours & past dates
$timeCheck = validate_booking_time($date, $startTime, $duration);
if (!$timeCheck['valid']) {
    json_response([
        'available' => false,
        'message'   => $timeCheck['message']
    ], 200);
}

$endTime = $timeCheck['end_time'];

// 2. Validate room availability with cleaning gap buffer
$isAvailable = check_availability($roomId, $date, $startTime, $endTime, $excludeApptId);

if ($isAvailable) {
    json_response([
        'available'  => true,
        'room_id'    => $roomId,
        'date'       => $date,
        'start_time' => $startTime,
        'end_time'   => $endTime,
        'message'    => 'The sanctuary suite is available for your requested session.'
    ]);
} else {
    json_response([
        'available'  => false,
        'room_id'    => $roomId,
        'date'       => $date,
        'start_time' => $startTime,
        'end_time'   => $endTime,
        'message'    => 'This suite is reserved or in mandatory sanitization during this slot. Please select another time or suite.'
    ]);
}
