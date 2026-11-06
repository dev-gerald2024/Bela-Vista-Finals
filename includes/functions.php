<?php
/**
 * Bella Vista Oasis Spa - Core Functions Library
 * 
 * Helper routines for security, formatting, booking math, room availability,
 * therapist scheduling, operating hour verification, and audit logging.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitizes generic user input (strings or arrays).
 *
 * @param mixed $data
 * @return mixed
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    if (is_null($data)) {
        return null;
    }
    $data = trim((string)$data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Safely redirects the browser to a given URL or route and exits.
 *
 * @param string $url Target location (relative or absolute)
 * @param int $statusCode HTTP status code (default 302)
 * @return void
 */
function redirect(string $url, int $statusCode = 302): void {
    // If not an absolute URL, prefix with BASE_URL
    if (!preg_match('#^https?://#i', $url)) {
        $url = url($url);
    }

    if (!headers_sent()) {
        header("Location: $url", true, $statusCode);
    } else {
        echo "<script>window.location.href=" . json_encode($url) . ";</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url, ENT_QUOTES) . "'></noscript>";
    }
    exit;
}

/**
 * Checks if a session user is logged in.
 *
 * @return bool
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
}

/**
 * Retrieves the current logged-in user's role.
 *
 * @return string|null
 */
function get_user_role(): ?string {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Enforces role-based access control.
 *
 * @param string|array $allowedRoles Single role string or array of allowed roles
 * @param string $redirectUrl Fallback URL if access is denied
 * @return void
 */
function check_role($allowedRoles, string $redirectUrl = 'login.php'): void {
    if (!is_logged_in()) {
        flash_message('warning', 'Please sign in to access this page.');
        redirect($redirectUrl);
    }

    $roles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    $currentRole = get_user_role();

    if (!in_array($currentRole, $roles, true)) {
        flash_message('danger', 'Access denied. You do not have permission to view this section.');
        // Redirect to their appropriate home dashboard
        if ($currentRole === 'customer') {
            redirect('customer/dashboard.php');
        } elseif (in_array($currentRole, ['admin', 'manager', 'front_desk'])) {
            redirect('admin/dashboard.php');
        } elseif ($currentRole === 'therapist') {
            redirect('therapist/dashboard.php');
        } else {
            redirect('index.php');
        }
    }
}

/**
 * Formats a numeric value into Philippine Peso luxury currency string.
 *
 * @param float|int|string|null $amount
 * @param bool $showSymbol
 * @return string
 */
function format_currency($amount, bool $showSymbol = true): string {
    $numeric = is_numeric($amount) ? (float)$amount : 0.00;
    $formatted = number_format($numeric, 2, '.', ',');
    return $showSymbol ? (CURRENCY_SYMBOL . ' ' . $formatted) : $formatted;
}

/**
 * Formats a date string into an elegant human-readable date.
 *
 * @param string|null $date
 * @param string $format
 * @return string
 */
function format_date(?string $date, string $format = 'M d, Y'): string {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : 'N/A';
}

/**
 * Formats a time string into 12-hour format with AM/PM.
 *
 * @param string|null $time
 * @param string $format
 * @return string
 */
function format_time(?string $time, string $format = 'g:i A'): string {
    if (empty($time)) {
        return 'N/A';
    }
    $timestamp = strtotime($time);
    return $timestamp ? date($format, $timestamp) : 'N/A';
}

/**
 * Generates an official, unique receipt number for billing and transactions.
 * Format: BVO-YYYYMMDD-XXXX (e.g., BVO-20241106-9482)
 *
 * @return string
 */
function generate_receipt_number(): string {
    $datePart = date('Ymd');
    $randomPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    return "BVO-{$datePart}-{$randomPart}";
}

/**
 * Sets a flash message to be displayed on the next rendered page.
 *
 * @param string $type Bootstrap alert / toast type ('success', 'danger', 'warning', 'info', 'gold')
 * @param string $message The message text or safe HTML
 * @return void
 */
function flash_message(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
        'time'    => time()
    ];
}

/**
 * Renders and clears any pending flash message as a styled luxury alert/toast.
 *
 * @return string HTML output
 */
function display_flash(): string {
    if (empty($_SESSION['flash'])) {
        return '';
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
    $message = $flash['message']; // Can include safe formatted text

    $icon = 'fa-circle-info';
    if ($type === 'success') $icon = 'fa-circle-check';
    if ($type === 'danger')  $icon = 'fa-triangle-exclamation';
    if ($type === 'warning') $icon = 'fa-bell';
    if ($type === 'gold' || $type === 'info') $icon = 'fa-gem';

    return <<<HTML
    <div class="alert alert-{$type} alert-dismissible fade show luxury-alert" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa-solid {$icon} me-2 fs-5 text-gold"></i>
            <div>{$message}</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
HTML;
}

/**
 * Calculates total appointment amount based on service base price and guest count.
 * 
 * Rules:
 * - Single: max 1 guest, extra charges = 0.
 * - Couples: max 2 guests, extra charges = 0.
 * - Family: base price includes up to 4 guests. Accommodates up to 6 guests at ₱750 per additional guest.
 *
 * @param int $serviceId
 * @param int $guestCount
 * @return array ['base_price', 'additional_guest_charges', 'total_amount', 'guest_count', 'service']
 */
function calculate_total_amount(int $serviceId, int $guestCount = 1): array {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        throw new InvalidArgumentException("Service not found or is currently inactive.");
    }

    $basePrice = (float)$service['base_price'];
    $maxGuests = (int)$service['max_guests'];
    $addFee = (float)$service['additional_guest_fee'];
    $roomType = $service['room_type'];

    // Clamp guest count between 1 and max_guests
    $guestCount = max(1, min($guestCount, $maxGuests));
    $extraCharges = 0.00;

    if ($roomType === 'family') {
        // Family package includes up to 4 guests
        $includedGuests = 4;
        if ($guestCount > $includedGuests) {
            $extraGuests = $guestCount - $includedGuests;
            $extraCharges = $extraGuests * $addFee;
        }
    } else {
        // Single or couples rooms do not have extra guests beyond base capacity
        $extraCharges = 0.00;
    }

    $totalAmount = $basePrice + $extraCharges;

    return [
        'service_id'               => (int)$service['id'],
        'service_name'             => $service['name'],
        'room_type'                => $roomType,
        'duration_minutes'         => (int)$service['duration_minutes'],
        'guest_count'              => $guestCount,
        'base_price'               => $basePrice,
        'additional_guest_fee'     => $addFee,
        'additional_guest_charges' => $extraCharges,
        'total_amount'             => $totalAmount
    ];
}

/**
 * Retrieves a system setting value from the database with an optional default.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_system_setting(string $key, $default = null) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Checks whether a specific room is available on a given date and time range,
 * taking into account existing appointments, room schedules, and the mandatory cleaning buffer.
 *
 * @param int $roomId
 * @param string $date (YYYY-MM-DD)
 * @param string $startTime (HH:MM:SS or HH:MM)
 * @param string $endTime (HH:MM:SS or HH:MM)
 * @param int|null $excludeAppointmentId Optional appointment to exclude (for updates)
 * @return bool True if available, false if conflict
 */
function check_availability(int $roomId, string $date, string $startTime, string $endTime, ?int $excludeAppointmentId = null): bool {
    $pdo = get_db_connection();

    // 1. Check if room exists and is active/not under maintenance
    $roomStmt = $pdo->prepare("SELECT id, status, is_active FROM rooms WHERE id = ? LIMIT 1");
    $roomStmt->execute([$roomId]);
    $room = $roomStmt->fetch();

    if (!$room || (int)$room['is_active'] !== 1 || $room['status'] === 'maintenance') {
        return false;
    }

    // Standardize time formatting
    $startTime = date('H:i:s', strtotime($startTime));
    $endTime = date('H:i:s', strtotime($endTime));

    // Get cleaning gap buffer from system settings (default 30 mins)
    $cleaningGap = (int)get_system_setting('cleaning_gap_minutes', DEFAULT_CLEANING_GAP_MIN);

    // 2. Check room_schedules table for overlaps
    $schedSql = "SELECT COUNT(*) FROM room_schedules 
                 WHERE room_id = ? 
                   AND `date` = ? 
                   AND NOT (end_time <= ? OR start_time >= ?)";
    $schedParams = [$roomId, $date, $startTime, $endTime];

    if ($excludeAppointmentId !== null) {
        $schedSql .= " AND (appointment_id IS NULL OR appointment_id != ?)";
        $schedParams[] = $excludeAppointmentId;
    }

    $schedStmt = $pdo->prepare($schedSql);
    $schedStmt->execute($schedParams);
    if ((int)$schedStmt->fetchColumn() > 0) {
        return false;
    }

    // 3. Check active appointments in this room with cleaning buffer included
    // An existing appointment occupies [start_time, end_time + cleaning_gap]
    // A proposed appointment needs [start_time, end_time + cleaning_gap] to avoid subsequent clashes
    $proposedOccupiedEnd = date('H:i:s', strtotime("$endTime + {$cleaningGap} minutes"));

    $apptSql = "SELECT COUNT(*) FROM appointments
                WHERE room_id = ?
                  AND appointment_date = ?
                  AND status IN ('pending', 'confirmed', 'in_progress')
                  AND NOT (
                      ADDTIME(end_time, SEC_TO_TIME(? * 60)) <= ? 
                      OR start_time >= ?
                  )";
    $apptParams = [$roomId, $date, $cleaningGap, $startTime, $proposedOccupiedEnd];

    if ($excludeAppointmentId !== null) {
        $apptSql .= " AND id != ?";
        $apptParams[] = $excludeAppointmentId;
    }

    $apptStmt = $pdo->prepare($apptSql);
    $apptStmt->execute($apptParams);
    if ((int)$apptStmt->fetchColumn() > 0) {
        return false;
    }

    return true;
}

/**
 * Checks whether a therapist is available for an appointment window.
 * Verifies therapist status, weekly working schedule, day off status, and active assignments.
 *
 * @param int $therapistId
 * @param string $date (YYYY-MM-DD)
 * @param string $startTime (HH:MM:SS)
 * @param string $endTime (HH:MM:SS)
 * @param int|null $excludeAppointmentId
 * @return bool
 */
function check_therapist_availability(int $therapistId, string $date, string $startTime, string $endTime, ?int $excludeAppointmentId = null): bool {
    $pdo = get_db_connection();

    // 1. Check therapist active and available flag
    $stmt = $pdo->prepare("SELECT t.id, t.employee_id, t.is_available, e.employment_status
                           FROM therapists t
                           JOIN employees e ON t.employee_id = e.id
                           WHERE t.id = ? LIMIT 1");
    $stmt->execute([$therapistId]);
    $therapist = $stmt->fetch();

    if (!$therapist || (int)$therapist['is_available'] !== 1 || $therapist['employment_status'] !== 'active') {
        return false;
    }

    $employeeId = (int)$therapist['employee_id'];
    $dayOfWeek = date('l', strtotime($date));
    $startTime = date('H:i:s', strtotime($startTime));
    $endTime = date('H:i:s', strtotime($endTime));

    // 2. Check employee schedule for day of week
    $schedStmt = $pdo->prepare("SELECT is_day_off, shift_start, shift_end 
                                FROM employee_schedules 
                                WHERE employee_id = ? 
                                  AND day_of_week = ? 
                                  AND effective_from <= ? 
                                  AND (effective_until IS NULL OR effective_until >= ?)
                                ORDER BY id DESC LIMIT 1");
    $schedStmt->execute([$employeeId, $dayOfWeek, $date, $date]);
    $sched = $schedStmt->fetch();

    // If a schedule exists, verify they are working and within shift
    if ($sched) {
        if ((int)$sched['is_day_off'] === 1) {
            return false;
        }
        if ($startTime < $sched['shift_start'] || $endTime > $sched['shift_end']) {
            return false;
        }
    }

    // 3. Check for overlapping therapist assignments
    $assignSql = "SELECT COUNT(*) 
                  FROM therapist_assignments ta
                  JOIN appointments a ON ta.appointment_id = a.id
                  WHERE ta.therapist_id = ?
                    AND a.appointment_date = ?
                    AND a.status IN ('pending', 'confirmed', 'in_progress')
                    AND NOT (a.end_time <= ? OR a.start_time >= ?)";
    $assignParams = [$therapistId, $date, $startTime, $endTime];

    if ($excludeAppointmentId !== null) {
        $assignSql .= " AND a.id != ?";
        $assignParams[] = $excludeAppointmentId;
    }

    $assignStmt = $pdo->prepare($assignSql);
    $assignStmt->execute($assignParams);

    return ((int)$assignStmt->fetchColumn() === 0);
}

/**
 * Validates booking time against operating hours, past dates, and buffers.
 *
 * @param string $date (YYYY-MM-DD)
 * @param string $startTime (HH:MM or HH:MM:SS)
 * @param int $durationMinutes
 * @return array ['valid' => bool, 'start_time' => string, 'end_time' => string, 'date' => string, 'message' => string]
 */
function validate_booking_time(string $date, string $startTime, int $durationMinutes): array {
    $today = date('Y-m-d');
    $nowTimestamp = time();
    $bookingTimestamp = strtotime("{$date} {$startTime}");

    if (!$bookingTimestamp) {
        return [
            'valid'   => false,
            'message' => 'Invalid date or time format.'
        ];
    }

    // Check past date/time
    if ($date < $today) {
        return [
            'valid'   => false,
            'message' => 'Bookings cannot be scheduled on past dates.'
        ];
    }

    $arrivalBufferMin = (int)get_system_setting('arrival_minutes_before', DEFAULT_ARRIVAL_BUFFER_MIN);
    $minLeadTimeSeconds = $arrivalBufferMin * 60;

    if ($date === $today && ($bookingTimestamp - $nowTimestamp < $minLeadTimeSeconds)) {
        return [
            'valid'   => false,
            'message' => "Appointments today require at least {$arrivalBufferMin} minutes advance booking."
        ];
    }

    $operatingStart = get_system_setting('operating_hours_start', DEFAULT_OPERATING_START);
    $operatingEnd = get_system_setting('operating_hours_end', DEFAULT_OPERATING_END);

    // Standardize format HH:MM:SS
    $startFormatted = date('H:i:s', strtotime($startTime));
    $endTimestamp = strtotime("{$date} {$startFormatted} + {$durationMinutes} minutes");
    $endFormatted = date('H:i:s', $endTimestamp);

    $opStartFormatted = date('H:i:s', strtotime($operatingStart));
    $opEndFormatted = date('H:i:s', strtotime($operatingEnd));

    if ($startFormatted < $opStartFormatted) {
        $displayStart = format_time($opStartFormatted);
        return [
            'valid'   => false,
            'message' => "The spa opens at {$displayStart}. Please choose a time within operating hours."
        ];
    }

    if ($endFormatted > $opEndFormatted) {
        $displayEnd = format_time($opEndFormatted);
        return [
            'valid'   => false,
            'message' => "This {$durationMinutes}-minute session would conclude at " . format_time($endFormatted) . ", exceeding our closing time of {$displayEnd}."
        ];
    }

    return [
        'valid'      => true,
        'date'       => $date,
        'start_time' => $startFormatted,
        'end_time'   => $endFormatted,
        'message'    => 'Time slot is within official operating hours.'
    ];
}

/**
 * Records an entry into the immutable system audit log.
 *
 * @param int|null $userId
 * @param string $action
 * @param string|null $table
 * @param int|null $recordId
 * @param mixed|null $oldVals
 * @param mixed|null $newVals
 * @return bool
 */
function log_audit(?int $userId, string $action, ?string $table = null, ?int $recordId = null, $oldVals = null, $newVals = null): bool {
    try {
        $pdo = get_db_connection();

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipParts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ipAddress = trim($ipParts[0]);
        }
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Agent', 0, 500);

        $oldJson = $oldVals !== null ? json_encode($oldVals, JSON_UNESCAPED_UNICODE) : null;
        $newJson = $newVals !== null ? json_encode($newVals, JSON_UNESCAPED_UNICODE) : null;

        $stmt = $pdo->prepare("INSERT INTO audit_logs 
            (`user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        return $stmt->execute([
            $userId,
            $action,
            $table,
            $recordId,
            $oldJson,
            $newJson,
            $ipAddress,
            $userAgent
        ]);
    } catch (Exception $e) {
        error_log("Audit Log Failure: " . $e->getMessage());
        return false;
    }
}

/**
 * Emits a JSON response and exits immediately.
 *
 * @param array $data
 * @param int $statusCode
 * @return void
 */
function json_response(array $data, int $statusCode = 200): void {
    if (!headers_sent()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
