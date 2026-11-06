<?php
/**
 * Bella Vista Oasis Spa - Get Appointment Details API
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Auth Guard
if (!is_logged_in() || get_user_role() !== 'customer') {
    json_response(['success' => false, 'message' => 'Unauthorized'], 401);
}

$pdo = get_db_connection();
$customerId = (int)($_SESSION['customer_id'] ?? 0);
$appointmentId = (int)($_GET['id'] ?? 0);

if ($appointmentId <= 0) {
    json_response(['success' => false, 'message' => 'Invalid ID'], 400);
}

$stmt = $pdo->prepare("SELECT a.*, s.name AS service_name, s.base_price, s.duration_minutes, 
                              r.name AS room_name, r.room_type, r.floor AS room_floor,
                              tu.first_name AS therapist_first_name, tu.last_name AS therapist_last_name
                       FROM appointments a
                       JOIN services s ON a.service_id = s.id
                       JOIN rooms r ON a.room_id = r.id
                       LEFT JOIN therapist_assignments ta ON ta.appointment_id = a.id AND ta.status = 'assigned'
                       LEFT JOIN therapists t ON ta.therapist_id = t.id
                       LEFT JOIN employees te ON t.employee_id = te.id
                       LEFT JOIN users tu ON te.user_id = tu.id
                       WHERE a.id = ? AND a.customer_id = ? LIMIT 1");
$stmt->execute([$appointmentId, $customerId]);
$a = $stmt->fetch();

if (!$a) {
    json_response(['success' => false, 'message' => 'Appointment not found'], 404);
}

$ref = 'BVO-' . date('Ymd', strtotime($a['appointment_date'])) . '-' . str_pad($a['id'], 4, '0', STR_PAD_LEFT);
$therapist = !empty($a['therapist_first_name']) ? ($a['therapist_first_name'] . ' ' . $a['therapist_last_name']) : 'Master Specialist Assigned';

json_response([
    'success' => true,
    'appointment' => [
        'id'                      => (int)$a['id'],
        'reference_number'        => $ref,
        'service_name'            => $a['service_name'],
        'duration_minutes'        => (int)$a['duration_minutes'],
        'appointment_date'        => $a['appointment_date'],
        'formatted_date'          => format_date($a['appointment_date'], 'l, F j, Y'),
        'formatted_time'          => format_time($a['start_time']) . ' – ' . format_time($a['end_time']),
        'room_name'               => $a['room_name'],
        'room_type'               => ucfirst(str_replace('_', ' ', $a['room_type'])),
        'room_floor'              => $a['room_floor'],
        'therapist_name'          => $therapist,
        'guest_count'             => (int)$a['guest_count'],
        'status'                  => $a['status'],
        'status_label'            => ucfirst(str_replace('_', ' ', $a['status'])),
        'total_amount'            => (float)$a['total_amount'],
        'formatted_total_amount'  => format_currency($a['total_amount']),
        'formatted_base_price'    => format_currency($a['base_price']),
        'formatted_guest_charges' => format_currency($a['additional_guest_charges'])
    ]
]);
