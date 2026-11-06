<?php
/**
 * Bella Vista Oasis Spa - Process Admin Appointment Save / Update / Bulk Actions
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_db_connection();
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Verify Authentication
if (!is_logged_in() || !in_array(get_user_role(), ['admin', 'manager', 'front_desk'], true)) {
    if ($isAjax) {
        json_response(['success' => false, 'message' => 'Unauthorized access.'], 403);
    }
    flash_message('danger', 'Unauthorized access.');
    redirect('login.php');
}

// CSRF Validation
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!validate_csrf_token($csrfToken)) {
    if ($isAjax) {
        json_response(['success' => false, 'message' => 'Invalid or expired security token.'], 403);
    }
    flash_message('danger', 'Security token mismatch. Please try again.');
    redirect('admin/appointments.php');
}

$action = trim($_POST['action'] ?? '');
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

// ============================================================================
// 1. QUICK STATUS UPDATE (AJAX)
// ============================================================================
if ($action === 'quick_status') {
    $appointmentId = (int)($_POST['appointment_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $validStatuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];

    if (!$appointmentId || !in_array($newStatus, $validStatuses, true)) {
        json_response(['success' => false, 'message' => 'Invalid status or appointment ID.'], 400);
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? LIMIT 1");
        $stmt->execute([$appointmentId]);
        $oldAppt = $stmt->fetch();

        if (!$oldAppt) {
            json_response(['success' => false, 'message' => 'Appointment not found.'], 404);
        }

        $upStmt = $pdo->prepare("UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ?");
        $upStmt->execute([$newStatus, $appointmentId]);

        // If completed or cancelled, update cleaning schedule or room status if necessary
        if ($newStatus === 'completed' && !empty($oldAppt['room_id'])) {
            $pdo->prepare("UPDATE rooms SET status = 'cleaning' WHERE id = ?")->execute([$oldAppt['room_id']]);
        } elseif ($newStatus === 'in_progress' && !empty($oldAppt['room_id'])) {
            $pdo->prepare("UPDATE rooms SET status = 'occupied' WHERE id = ?")->execute([$oldAppt['room_id']]);
        }

        // Notify customer
        $cStmt = $pdo->prepare("SELECT user_id FROM customers WHERE id = ? LIMIT 1");
        $cStmt->execute([$oldAppt['customer_id']]);
        $custUserId = $cStmt->fetchColumn();

        if ($custUserId) {
            $notifTitle = "Appointment #" . str_pad($appointmentId, 5, '0', STR_PAD_LEFT) . " Update";
            $notifMsg = "Your spa appointment status is now " . ucfirst(str_replace('_', ' ', $newStatus)) . ".";
            $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'booking', ?)")
                ->execute([$custUserId, $notifTitle, $notifMsg, 'customer/appointments.php']);
        }

        log_audit($currentUserId, 'APPOINTMENT_STATUS_UPDATE', 'appointments', $appointmentId, ['status' => $oldAppt['status']], ['status' => $newStatus]);

        json_response(['success' => true, 'message' => 'Status successfully updated to ' . ucfirst(str_replace('_', ' ', $newStatus))]);
    } catch (Exception $e) {
        json_response(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ============================================================================
// 2. BULK UPDATE ACTION
// ============================================================================
if ($action === 'bulk_update') {
    $appointmentIds = $_POST['appointment_ids'] ?? [];
    $bulkStatus = trim($_POST['bulk_status'] ?? '');
    $validStatuses = ['confirmed', 'completed', 'cancelled'];

    if (empty($appointmentIds) || !is_array($appointmentIds) || !in_array($bulkStatus, $validStatuses, true)) {
        flash_message('danger', 'Please choose valid appointments and status.');
        redirect('admin/appointments.php');
    }

    $inPlaceholders = implode(',', array_fill(0, count($appointmentIds), '?'));
    $params = array_merge([$bulkStatus], array_map('intval', $appointmentIds));

    $sql = "UPDATE appointments SET status = ?, updated_at = NOW() WHERE id IN ($inPlaceholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    log_audit($currentUserId, 'APPOINTMENT_BULK_UPDATE', 'appointments', null, null, [
        'bulk_status' => $bulkStatus,
        'count' => count($appointmentIds)
    ]);

    flash_message('success', count($appointmentIds) . ' appointments updated to ' . ucfirst($bulkStatus) . '.');
    redirect('admin/appointments.php');
}

// ============================================================================
// 3. CREATE / UPDATE SINGLE APPOINTMENT
// ============================================================================
if ($action === 'create' || $action === 'update') {
    $appointmentId = !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null;
    $customerId = (int)($_POST['customer_id'] ?? 0);
    $serviceId = (int)($_POST['service_id'] ?? 0);
    $guestCount = max(1, (int)($_POST['guest_count'] ?? 1));
    $date = trim($_POST['appointment_date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $roomId = !empty($_POST['room_id']) ? (int)$_POST['room_id'] : null;
    $therapistId = !empty($_POST['therapist_id']) ? (int)$_POST['therapist_id'] : null;
    $status = trim($_POST['status'] ?? 'pending');
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    if (!$customerId || !$serviceId || empty($date) || empty($startTime)) {
        flash_message('danger', 'Please fill in all required fields (Guest, Service, Date, Start Time).');
        redirect($action === 'update' ? "admin/appointment-form.php?id=$appointmentId" : "admin/appointment-form.php");
    }

    // Fetch service info
    $sStmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1 LIMIT 1");
    $sStmt->execute([$serviceId]);
    $service = $sStmt->fetch();

    if (!$service) {
        flash_message('danger', 'Selected service is invalid or inactive.');
        redirect('admin/appointments.php');
    }

    $duration = (int)$service['duration_minutes'];

    // Validate booking time against spa operating hours
    $timeCheck = validate_booking_time($date, $startTime, $duration);
    if (!$timeCheck['valid']) {
        flash_message('danger', $timeCheck['message']);
        redirect($action === 'update' ? "admin/appointment-form.php?id=$appointmentId" : "admin/appointment-form.php");
    }

    $formattedStart = $timeCheck['start_time'];
    $formattedEnd = $timeCheck['end_time'];

    // Auto-assign room if not specified
    if (!$roomId) {
        $typeMap = [
            'single' => 'single',
            'couples' => 'couples_suite',
            'family' => 'family_lounge'
        ];
        $targetRoomType = $typeMap[$service['room_type']] ?? 'single';

        $roomCandidates = $pdo->prepare("SELECT id FROM rooms WHERE room_type = ? AND is_active = 1 AND status != 'maintenance' ORDER BY id ASC");
        $roomCandidates->execute([$targetRoomType]);
        $candList = $roomCandidates->fetchAll(PDO::FETCH_COLUMN);

        foreach ($candList as $candidateId) {
            if (check_availability((int)$candidateId, $date, $formattedStart, $formattedEnd, $appointmentId)) {
                $roomId = (int)$candidateId;
                break;
            }
        }

        if (!$roomId) {
            flash_message('danger', "All suites for this {$service['name']} category are booked or being sanitized at the chosen time. Please select another time.");
            redirect($action === 'update' ? "admin/appointment-form.php?id=$appointmentId" : "admin/appointment-form.php");
        }
    } else {
        // Explicit room selected - verify availability
        if (!check_availability($roomId, $date, $formattedStart, $formattedEnd, $appointmentId)) {
            flash_message('danger', "Selected room is not available at {$formattedStart} on {$date} due to scheduling or buffer requirements.");
            redirect($action === 'update' ? "admin/appointment-form.php?id=$appointmentId" : "admin/appointment-form.php");
        }
    }

    // Verify therapist availability if therapist chosen
    if ($therapistId) {
        if (!check_therapist_availability($therapistId, $date, $formattedStart, $formattedEnd, $appointmentId)) {
            flash_message('danger', "The selected therapist is unavailable during this time window. Please choose another therapist or auto-assign.");
            redirect($action === 'update' ? "admin/appointment-form.php?id=$appointmentId" : "admin/appointment-form.php");
        }
    }

    // Calculate total amount
    $pricing = calculate_total_amount($serviceId, $guestCount);
    $totalAmount = $pricing['total_amount'];
    $extraCharges = $pricing['additional_guest_charges'];

    try {
        $pdo->beginTransaction();

        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO appointments 
                (customer_id, service_id, room_id, appointment_date, start_time, end_time, guest_count, total_amount, additional_guest_charges, status, notes, preferred_therapist_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $customerId,
                $serviceId,
                $roomId,
                $date,
                $formattedStart,
                $formattedEnd,
                $guestCount,
                $totalAmount,
                $extraCharges,
                $status,
                $notes,
                $therapistId
            ]);
            $appointmentId = (int)$pdo->lastInsertId();

            // Insert room schedule
            $rSched = $pdo->prepare("INSERT INTO room_schedules (room_id, appointment_id, `date`, start_time, end_time, includes_cleaning) VALUES (?, ?, ?, ?, ?, 1)");
            $rSched->execute([$roomId, $appointmentId, $date, $formattedStart, $formattedEnd]);

            // Cleaning buffer schedule
            $cleaningGap = (int)get_system_setting('cleaning_gap_minutes', 30);
            $cleanEnd = date('H:i:s', strtotime("$formattedEnd + {$cleaningGap} minutes"));
            $cleanStmt = $pdo->prepare("INSERT INTO cleaning_schedules (room_id, appointment_id, scheduled_start, scheduled_end, `date`, status) VALUES (?, ?, ?, ?, ?, 'awaiting_cleaning')");
            $cleanStmt->execute([$roomId, $appointmentId, $formattedEnd, $cleanEnd, $date]);

            // Therapist Assignment
            if ($therapistId) {
                $assignStmt = $pdo->prepare("INSERT INTO therapist_assignments (appointment_id, therapist_id, assigned_at, status) VALUES (?, ?, NOW(), 'assigned')");
                $assignStmt->execute([$appointmentId, $therapistId]);
            }

            log_audit($currentUserId, 'APPOINTMENT_CREATED', 'appointments', $appointmentId, null, [
                'customer_id' => $customerId,
                'service_id' => $serviceId,
                'date' => $date,
                'total_amount' => $totalAmount
            ]);

            flash_message('success', "Appointment #$appointmentId reserved successfully!");
        } else {
            // Update existing
            $oldStmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? LIMIT 1");
            $oldStmt->execute([$appointmentId]);
            $oldData = $oldStmt->fetch();

            $stmt = $pdo->prepare("UPDATE appointments SET 
                customer_id = ?, service_id = ?, room_id = ?, appointment_date = ?, start_time = ?, end_time = ?, guest_count = ?, total_amount = ?, additional_guest_charges = ?, status = ?, notes = ?, preferred_therapist_id = ?, updated_at = NOW()
                WHERE id = ?");
            $stmt->execute([
                $customerId,
                $serviceId,
                $roomId,
                $date,
                $formattedStart,
                $formattedEnd,
                $guestCount,
                $totalAmount,
                $extraCharges,
                $status,
                $notes,
                $therapistId,
                $appointmentId
            ]);

            // Update room schedule
            $pdo->prepare("DELETE FROM room_schedules WHERE appointment_id = ?")->execute([$appointmentId]);
            $rSched = $pdo->prepare("INSERT INTO room_schedules (room_id, appointment_id, `date`, start_time, end_time, includes_cleaning) VALUES (?, ?, ?, ?, ?, 1)");
            $rSched->execute([$roomId, $appointmentId, $date, $formattedStart, $formattedEnd]);

            // Update cleaning schedule
            $pdo->prepare("DELETE FROM cleaning_schedules WHERE appointment_id = ?")->execute([$appointmentId]);
            $cleaningGap = (int)get_system_setting('cleaning_gap_minutes', 30);
            $cleanEnd = date('H:i:s', strtotime("$formattedEnd + {$cleaningGap} minutes"));
            $cleanStmt = $pdo->prepare("INSERT INTO cleaning_schedules (room_id, appointment_id, scheduled_start, scheduled_end, `date`, status) VALUES (?, ?, ?, ?, ?, 'awaiting_cleaning')");
            $cleanStmt->execute([$roomId, $appointmentId, $formattedEnd, $cleanEnd, $date]);

            // Therapist assignment update
            $pdo->prepare("DELETE FROM therapist_assignments WHERE appointment_id = ?")->execute([$appointmentId]);
            if ($therapistId) {
                $assignStmt = $pdo->prepare("INSERT INTO therapist_assignments (appointment_id, therapist_id, assigned_at, status) VALUES (?, ?, NOW(), 'assigned')");
                $assignStmt->execute([$appointmentId, $therapistId]);
            }

            log_audit($currentUserId, 'APPOINTMENT_UPDATED', 'appointments', $appointmentId, $oldData, [
                'customer_id' => $customerId,
                'status' => $status,
                'total_amount' => $totalAmount
            ]);

            flash_message('success', "Appointment #$appointmentId updated successfully.");
        }

        // Notify customer
        $cStmt = $pdo->prepare("SELECT user_id FROM customers WHERE id = ? LIMIT 1");
        $cStmt->execute([$customerId]);
        $custUserId = $cStmt->fetchColumn();

        if ($custUserId) {
            $notifTitle = "Appointment Confirmation #" . str_pad($appointmentId, 5, '0', STR_PAD_LEFT);
            $notifMsg = "Your session for {$service['name']} is scheduled on " . format_date($date) . " at " . format_time($formattedStart) . ".";
            $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'booking', ?)")
                ->execute([$custUserId, $notifTitle, $notifMsg, 'customer/appointments.php']);
        }

        $pdo->commit();
        redirect('admin/appointments.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        flash_message('danger', 'Failed to save appointment: ' . $e->getMessage());
        redirect($action === 'update' ? "admin/appointment-form.php?id=$appointmentId" : "admin/appointment-form.php");
    }
}

redirect('admin/appointments.php');
