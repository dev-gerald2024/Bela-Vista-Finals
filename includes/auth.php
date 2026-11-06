<?php
/**
 * Bella Vista Oasis Spa - Authentication & Identity Services
 * 
 * Handles user login, customer registration, session lifecycle, password reset tokens,
 * and role-based redirect resolution with audit logging.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Authenticates user credentials, sets up secure session variables, and logs audit.
 *
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'redirect' => string, 'user' => array]
 */
function login(string $email, string $password): array {
    $email = strtolower(trim($email));
    $pdo = get_db_connection();

    if (empty($email) || empty($password)) {
        return [
            'success'  => false,
            'message'  => 'Please provide both your registered email and password.',
            'redirect' => ''
        ];
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        log_audit(
            $user ? (int)$user['id'] : null,
            'AUTH_LOGIN_FAILED',
            'users',
            $user ? (int)$user['id'] : null,
            null,
            ['email' => $email, 'reason' => 'Invalid credentials']
        );

        return [
            'success'  => false,
            'message'  => 'Invalid email or password. Please verify and try again.',
            'redirect' => ''
        ];
    }

    if ((int)$user['is_active'] !== 1) {
        log_audit(
            (int)$user['id'],
            'AUTH_LOGIN_BLOCKED',
            'users',
            (int)$user['id'],
            null,
            ['email' => $email, 'reason' => 'Account is deactivated']
        );

        return [
            'success'  => false,
            'message'  => 'Your account is currently deactivated. Please contact concierge support.',
            'redirect' => ''
        ];
    }

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Populate standard user session data
    $_SESSION['user_id']       = (int)$user['id'];
    $_SESSION['user_email']    = $user['email'];
    $_SESSION['user_role']     = $user['role'];
    $_SESSION['first_name']    = $user['first_name'];
    $_SESSION['last_name']     = $user['last_name'];
    $_SESSION['user_name']     = trim($user['first_name'] . ' ' . $user['last_name']);
    $_SESSION['phone']         = $user['phone'];
    $_SESSION['auth_time']     = time();

    // Populate role-specific identity identifiers
    $redirect = 'index.php';

    if ($user['role'] === 'customer') {
        $cStmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ? LIMIT 1");
        $cStmt->execute([$user['id']]);
        $customer = $cStmt->fetch();

        if ($customer) {
            $_SESSION['customer_id'] = (int)$customer['id'];
        } else {
            // Auto-heal missing customer record if needed
            $insC = $pdo->prepare("INSERT INTO customers (user_id) VALUES (?)");
            $insC->execute([$user['id']]);
            $_SESSION['customer_id'] = (int)$pdo->lastInsertId();
        }
        $redirect = 'customer/dashboard.php';

    } elseif (in_array($user['role'], ['admin', 'manager', 'front_desk'], true)) {
        $eStmt = $pdo->prepare("SELECT id, position, department FROM employees WHERE user_id = ? LIMIT 1");
        $eStmt->execute([$user['id']]);
        $emp = $eStmt->fetch();
        if ($emp) {
            $_SESSION['employee_id'] = (int)$emp['id'];
            $_SESSION['employee_position'] = $emp['position'];
            $_SESSION['employee_department'] = $emp['department'];
        }
        $redirect = 'admin/dashboard.php';

    } elseif ($user['role'] === 'therapist') {
        $tStmt = $pdo->prepare("SELECT t.id AS therapist_id, e.id AS employee_id 
                                FROM employees e 
                                LEFT JOIN therapists t ON t.employee_id = e.id 
                                WHERE e.user_id = ? LIMIT 1");
        $tStmt->execute([$user['id']]);
        $therapist = $tStmt->fetch();
        if ($therapist) {
            $_SESSION['employee_id'] = (int)$therapist['employee_id'];
            $_SESSION['therapist_id'] = (int)$therapist['therapist_id'];
        }
        $redirect = 'therapist/dashboard.php';
    }

    log_audit(
        (int)$user['id'],
        'AUTH_LOGIN_SUCCESS',
        'users',
        (int)$user['id'],
        null,
        ['email' => $user['email'], 'role' => $user['role']]
    );

    return [
        'success'  => true,
        'message'  => 'Welcome back, ' . htmlspecialchars($user['first_name'], ENT_QUOTES) . '.',
        'redirect' => $redirect,
        'user'     => [
            'id'         => (int)$user['id'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name']
        ]
    ];
}

/**
 * Registers a new customer account, creating linked user, customer profile, and welcome bonus.
 *
 * @param array $data
 * @return array ['success' => bool, 'message' => string, 'user_id' => int|null, 'customer_id' => int|null]
 */
function register_customer(array $data): array {
    $pdo = get_db_connection();

    $email = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';
    $firstName = trim($data['first_name'] ?? '');
    $lastName = trim($data['last_name'] ?? '');
    $phone = trim($data['phone'] ?? '');

    // Optional customer profile fields
    $dob = !empty($data['date_of_birth']) ? $data['date_of_birth'] : null;
    $gender = $data['gender'] ?? 'prefer_not_to_say';
    $address = trim($data['address'] ?? '');
    $emergencyName = trim($data['emergency_contact_name'] ?? '');
    $emergencyPhone = trim($data['emergency_contact_phone'] ?? '');

    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please provide a valid email address.'];
    }

    if (empty($firstName) || empty($lastName)) {
        return ['success' => false, 'message' => 'Please provide your first and last name.'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
    }

    // Check if email already registered
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        return ['success' => false, 'message' => 'An account with this email address already exists.'];
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();

        // 1. Insert User record
        $userStmt = $pdo->prepare("INSERT INTO users 
            (`email`, `password_hash`, `role`, `first_name`, `last_name`, `phone`, `is_active`, `email_verified`) 
            VALUES (?, ?, 'customer', ?, ?, ?, 1, 1)");
        $userStmt->execute([$email, $passwordHash, $firstName, $lastName, $phone]);
        $userId = (int)$pdo->lastInsertId();

        // 2. Insert Customer profile record
        $custStmt = $pdo->prepare("INSERT INTO customers 
            (`user_id`, `date_of_birth`, `gender`, `address`, `emergency_contact_name`, `emergency_contact_phone`, `notes`) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $custStmt->execute([
            $userId,
            $dob,
            $gender,
            $address,
            $emergencyName,
            $emergencyPhone,
            'Registered via online guest portal.'
        ]);
        $customerId = (int)$pdo->lastInsertId();

        // 3. Award 50 Welcome Loyalty Points
        $pointsStmt = $pdo->prepare("INSERT INTO loyalty_points 
            (`customer_id`, `points`, `transaction_type`, `reference_type`, `reference_id`, `description`) 
            VALUES (?, 50, 'earned', 'welcome_bonus', ?, 'Welcome bonus points upon account registration')");
        $pointsStmt->execute([$customerId, $userId]);

        // 4. Send Welcome Notification
        $notifStmt = $pdo->prepare("INSERT INTO notifications 
            (`user_id`, `title`, `message`, `type`, `is_read`, `link`) 
            VALUES (?, ?, ?, 'system', 0, 'customer/loyalty.php')");
        $notifStmt->execute([
            $userId,
            'Welcome to Bella Vista Oasis Spa',
            'We are delighted to welcome you to our sanctuary. 50 complimentary loyalty rewards points have been credited to your account.',
        ]);

        // 5. Audit Log
        log_audit(
            $userId,
            'AUTH_CUSTOMER_REGISTER',
            'users',
            $userId,
            null,
            ['email' => $email, 'first_name' => $firstName, 'last_name' => $lastName, 'customer_id' => $customerId]
        );

        $pdo->commit();

        return [
            'success'     => true,
            'message'     => 'Registration successful. You may now sign in to your guest account.',
            'user_id'     => $userId,
            'customer_id' => $customerId
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Registration Exception: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An error occurred during account creation. Please try again.'
        ];
    }
}

/**
 * Logs out the current user, logs audit, and clears the session.
 *
 * @param string $redirectUrl
 * @return void
 */
function logout(string $redirectUrl = 'login.php'): void {
    if (is_logged_in()) {
        log_audit(
            $_SESSION['user_id'] ?? null,
            'AUTH_LOGOUT',
            'users',
            $_SESSION['user_id'] ?? null,
            null,
            ['email' => $_SESSION['user_email'] ?? '']
        );
    }

    // Unset all session keys
    $_SESSION = [];

    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    redirect($redirectUrl);
}

/**
 * Creates a password reset token for a registered user.
 *
 * @param string $email
 * @return array ['success' => bool, 'message' => string, 'token' => string|null]
 */
function forgot_password(string $email): array {
    $email = strtolower(trim($email));
    $pdo = get_db_connection();

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please provide a valid email address.'];
    }

    $stmt = $pdo->prepare("SELECT id, first_name, email FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Return generic success to prevent email enumeration
        return [
            'success' => true,
            'message' => 'If this email is registered in our sanctuary system, password reset instructions have been generated.',
            'token'   => null
        ];
    }

    $userId = (int)$user['id'];
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Invalidate existing active tokens for this user
    $invStmt = $pdo->prepare("UPDATE password_resets SET is_used = 1 WHERE user_id = ? AND is_used = 0");
    $invStmt->execute([$userId]);

    // Store new token
    $insStmt = $pdo->prepare("INSERT INTO password_resets (`user_id`, `token`, `expires_at`, `is_used`) VALUES (?, ?, ?, 0)");
    $insStmt->execute([$userId, $token, $expiresAt]);

    // Create in-app system notification
    $notifStmt = $pdo->prepare("INSERT INTO notifications (`user_id`, `title`, `message`, `type`, `is_read`, `link`) 
                               VALUES (?, 'Password Reset Requested', 'A password reset request was initiated for your sanctuary account.', 'system', 0, 'reset-password.php?token={$token}')");
    $notifStmt->execute([$userId]);

    log_audit(
        $userId,
        'AUTH_PASSWORD_RESET_REQUEST',
        'password_resets',
        (int)$pdo->lastInsertId(),
        null,
        ['email' => $email, 'expires_at' => $expiresAt]
    );

    return [
        'success' => true,
        'message' => 'Password reset instructions have been prepared. Please follow the instructions to reset your password.',
        'token'   => $token
    ];
}

/**
 * Validates a password reset token and sets a new password.
 *
 * @param string $token
 * @param string $newPassword
 * @return array ['success' => bool, 'message' => string]
 */
function reset_password(string $token, string $newPassword): array {
    $token = trim($token);
    $pdo = get_db_connection();

    if (empty($token)) {
        return ['success' => false, 'message' => 'Invalid or missing password reset token.'];
    }

    if (strlen($newPassword) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
    }

    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT id, user_id, expires_at FROM password_resets 
                           WHERE token = ? AND is_used = 0 AND expires_at > ? LIMIT 1");
    $stmt->execute([$token, $now]);
    $resetRow = $stmt->fetch();

    if (!$resetRow) {
        return [
            'success' => false,
            'message' => 'The password reset token is invalid or has expired. Please request a new link.'
        ];
    }

    $resetId = (int)$resetRow['id'];
    $userId = (int)$resetRow['user_id'];
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);

    try {
        $pdo->beginTransaction();

        // 1. Update user password
        $upUser = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $upUser->execute([$newHash, $userId]);

        // 2. Mark token used
        $upToken = $pdo->prepare("UPDATE password_resets SET is_used = 1 WHERE id = ?");
        $upToken->execute([$resetId]);

        // 3. Security audit
        log_audit(
            $userId,
            'AUTH_PASSWORD_RESET_SUCCESS',
            'users',
            $userId,
            null,
            ['reset_id' => $resetId]
        );

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Your password has been successfully updated. You may now sign in with your new credentials.'
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Password Reset Exception: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Unable to update password. Please try again or contact concierge support.'
        ];
    }
}
