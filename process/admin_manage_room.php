<?php
/**
 * Bella Vista Oasis Spa - Process Room Management Actions
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_db_connection();
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

check_role(['admin', 'manager'], 'login.php');

$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!validate_csrf_token($csrfToken)) {
    if ($isAjax) {
        json_response(['success' => false, 'message' => 'CSRF verification failed.'], 403);
    }
    flash_message('danger', 'Security mismatch. Please try again.');
    redirect('admin/rooms.php');
}

$action = trim($_POST['action'] ?? '');
$currentUserId = (int)($_SESSION['user_id'] ?? 0);

// --- 1. Quick Status Toggle (Available, Occupied, Cleaning, Maintenance) ---
if ($action === 'quick_status') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $valid = ['available', 'occupied', 'cleaning', 'maintenance'];

    if (!$roomId || !in_array($status, $valid, true)) {
        json_response(['success' => false, 'message' => 'Invalid parameters.'], 400);
    }

    try {
        $old = $pdo->prepare("SELECT status FROM rooms WHERE id = ?");
        $old->execute([$roomId]);
        $oldStatus = $old->fetchColumn();

        $stmt = $pdo->prepare("UPDATE rooms SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $roomId]);

        log_audit($currentUserId, 'ROOM_STATUS_CHANGE', 'rooms', $roomId, ['status' => $oldStatus], ['status' => $status]);
        json_response(['success' => true, 'message' => 'Room status updated to ' . ucfirst($status)]);
    } catch (Exception $e) {
        json_response(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
    }
}

// --- 2. Add New Room ---
if ($action === 'create') {
    $name = trim($_POST['name'] ?? '');
    $roomType = trim($_POST['room_type'] ?? 'single');
    $capacity = max(1, (int)($_POST['capacity'] ?? 1));
    $floor = trim($_POST['floor'] ?? '1st Floor');
    $status = trim($_POST['status'] ?? 'available');
    $description = trim($_POST['description'] ?? '');
    $amenities = trim($_POST['amenities'] ?? '');

    if (empty($name)) {
        flash_message('danger', 'Room name is required.');
        redirect('admin/rooms.php');
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO rooms (name, room_type, capacity, floor, status, description, amenities, is_active) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $roomType, $capacity, $floor, $status, $description, $amenities]);
        $newId = (int)$pdo->lastInsertId();

        log_audit($currentUserId, 'ROOM_CREATED', 'rooms', $newId, null, [
            'name' => $name,
            'room_type' => $roomType,
            'capacity' => $capacity
        ]);

        flash_message('success', "Room '{$name}' created successfully.");
    } catch (Exception $e) {
        flash_message('danger', 'Failed to create room: ' . $e->getMessage());
    }
    redirect('admin/rooms.php');
}

// --- 3. Edit Room ---
if ($action === 'update') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $roomType = trim($_POST['room_type'] ?? 'single');
    $capacity = max(1, (int)($_POST['capacity'] ?? 1));
    $floor = trim($_POST['floor'] ?? '1st Floor');
    $status = trim($_POST['status'] ?? 'available');
    $description = trim($_POST['description'] ?? '');
    $amenities = trim($_POST['amenities'] ?? '');

    if (!$roomId || empty($name)) {
        flash_message('danger', 'Valid room ID and name are required.');
        redirect('admin/rooms.php');
    }

    try {
        $old = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $old->execute([$roomId]);
        $oldData = $old->fetch();

        $stmt = $pdo->prepare("UPDATE rooms SET name = ?, room_type = ?, capacity = ?, floor = ?, status = ?, description = ?, amenities = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$name, $roomType, $capacity, $floor, $status, $description, $amenities, $roomId]);

        log_audit($currentUserId, 'ROOM_UPDATED', 'rooms', $roomId, $oldData, [
            'name' => $name,
            'room_type' => $roomType,
            'status' => $status
        ]);

        flash_message('success', "Room '{$name}' updated successfully.");
    } catch (Exception $e) {
        flash_message('danger', 'Failed to update room: ' . $e->getMessage());
    }
    redirect('admin/rooms.php');
}

// --- 4. Toggle Active / Deactivate ---
if ($action === 'toggle_active') {
    $roomId = (int)($_POST['room_id'] ?? 0);
    if (!$roomId) {
        redirect('admin/rooms.php');
    }

    try {
        $stmt = $pdo->prepare("SELECT is_active, name FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $rm = $stmt->fetch();

        if ($rm) {
            $newActive = ((int)$rm['is_active'] === 1) ? 0 : 1;
            $pdo->prepare("UPDATE rooms SET is_active = ?, updated_at = NOW() WHERE id = ?")->execute([$newActive, $roomId]);
            log_audit($currentUserId, 'ROOM_TOGGLE_ACTIVE', 'rooms', $roomId, ['is_active' => $rm['is_active']], ['is_active' => $newActive]);
            $statusText = $newActive ? 'activated' : 'deactivated';
            flash_message('success', "Room '{$rm['name']}' has been {$statusText}.");
        }
    } catch (Exception $e) {
        flash_message('danger', 'Failed to change room status.');
    }
    redirect('admin/rooms.php');
}

redirect('admin/rooms.php');
