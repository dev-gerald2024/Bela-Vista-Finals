<?php
/**
 * Bella Vista Oasis Spa - Appointment Dynamic API Helper
 * Provides AJAX support for customer autocomplete, dynamic slots, rooms, and pricing.
 */
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in() || !in_array(get_user_role(), ['admin', 'manager', 'front_desk'], true)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$pdo = get_db_connection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- 1. Customer Search / Autocomplete ---
if ($action === 'search_customers') {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) {
        echo json_encode(['success' => true, 'customers' => []]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT c.id AS customer_id, u.first_name, u.last_name, u.email, u.phone
                           FROM customers c
                           JOIN users u ON c.user_id = u.id
                           WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?
                           ORDER BY u.first_name ASC LIMIT 10");
    $param = "%$q%";
    $stmt->execute([$param, $param, $param, $param]);
    $customers = $stmt->fetchAll();

    echo json_encode(['success' => true, 'customers' => $customers]);
    exit;
}

// --- 2. Calculate Pricing ---
if ($action === 'calculate_price') {
    $serviceId = (int)($_GET['service_id'] ?? 0);
    $guests = max(1, (int)($_GET['guests'] ?? 1));

    try {
        $calc = calculate_total_amount($serviceId, $guests);
        echo json_encode(['success' => true, 'data' => $calc]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// --- 3. Dynamic Slots, Rooms & Available Therapists ---
if ($action === 'get_available_resources') {
    $date = trim($_GET['date'] ?? date('Y-m-d'));
    $serviceId = (int)($_GET['service_id'] ?? 0);
    $excludeApptId = !empty($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : null;

    if (!$serviceId) {
        echo json_encode(['success' => false, 'message' => 'Service ID required.']);
        exit;
    }

    $sStmt = $pdo->prepare("SELECT * FROM services WHERE id = ? LIMIT 1");
    $sStmt->execute([$serviceId]);
    $service = $sStmt->fetch();

    if (!$service) {
        echo json_encode(['success' => false, 'message' => 'Service not found.']);
        exit;
    }

    $duration = (int)$service['duration_minutes'];
    $roomCategory = $service['room_type']; // single, couples, family

    // Map room type enum
    $typeMap = [
        'single' => 'single',
        'couples' => 'couples_suite',
        'family' => 'family_lounge'
    ];
    $dbRoomType = $typeMap[$roomCategory] ?? 'single';

    // Fetch compatible rooms
    $rStmt = $pdo->prepare("SELECT id, name, room_type, capacity, floor, status 
                            FROM rooms 
                            WHERE room_type = ? AND is_active = 1 AND status != 'maintenance'");
    $rStmt->execute([$dbRoomType]);
    $rooms = $rStmt->fetchAll();

    // Fetch active therapists
    $tStmt = $pdo->query("SELECT t.id, t.specialization, t.rating_avg, u.first_name, u.last_name
                          FROM therapists t
                          JOIN employees e ON t.employee_id = e.id
                          JOIN users u ON e.user_id = u.id
                          WHERE t.is_available = 1 AND e.employment_status = 'active'
                          ORDER BY u.first_name ASC");
    $therapists = $tStmt->fetchAll();

    // Operating hours
    $opStart = get_system_setting('operating_hours_start', '13:00');
    $opEnd = get_system_setting('operating_hours_end', '21:00');

    // Generate possible 30-min start time slots between opStart and opEnd
    $slots = [];
    $current = strtotime("$date $opStart");
    $closing = strtotime("$date $opEnd");

    while ($current + ($duration * 60) <= $closing) {
        $slotStart = date('H:i', $current);
        $slotEnd = date('H:i', $current + ($duration * 60));

        // Check if AT LEAST ONE room of this type is free during this slot
        $hasFreeRoom = false;
        foreach ($rooms as $rm) {
            if (check_availability((int)$rm['id'], $date, $slotStart, $slotEnd, $excludeApptId)) {
                $hasFreeRoom = true;
                break;
            }
        }

        $slots[] = [
            'start' => $slotStart,
            'end' => $slotEnd,
            'display' => format_time($slotStart) . ' - ' . format_time($slotEnd),
            'available' => $hasFreeRoom
        ];

        $current += 1800; // Step by 30 mins
    }

    echo json_encode([
        'success' => true,
        'date' => $date,
        'duration' => $duration,
        'service' => $service,
        'rooms' => $rooms,
        'therapists' => $therapists,
        'slots' => $slots
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action parameter.']);
