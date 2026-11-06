<?php
/**
 * Bella Vista Oasis Spa - Application Configuration
 * 
 * Global settings, timezone, session security, CSRF protection, and path helpers.
 */

// Strict error reporting for clean production logs
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set Application Timezone
date_default_timezone_set('Asia/Manila');

// Brand & System Metadata
defined('APP_NAME') || define('APP_NAME', 'Bella Vista Oasis Spa');
defined('APP_TAGLINE') || define('APP_TAGLINE', 'The Pinnacle of Holistic Serenity & Luxury Wellness');
defined('APP_VERSION') || define('APP_VERSION', '1.0.0');
defined('APP_ENV') || define('APP_ENV', 'production'); // 'development' or 'production'

// Default Operating Rules (Fallbacks if not yet loaded from DB)
defined('DEFAULT_OPERATING_START') || define('DEFAULT_OPERATING_START', '13:00');
defined('DEFAULT_OPERATING_END') || define('DEFAULT_OPERATING_END', '21:00');
defined('DEFAULT_CLEANING_GAP_MIN') || define('DEFAULT_CLEANING_GAP_MIN', 30);
defined('DEFAULT_CANCELLATION_HOURS') || define('DEFAULT_CANCELLATION_HOURS', 6);
defined('DEFAULT_ARRIVAL_BUFFER_MIN') || define('DEFAULT_ARRIVAL_BUFFER_MIN', 15);
defined('CURRENCY_CODE') || define('CURRENCY_CODE', 'PHP');
defined('CURRENCY_SYMBOL') || define('CURRENCY_SYMBOL', '₱');

// Define Base URL dynamically or statically
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Calculate folder path relative to document root
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? 'C:/xampp/htdocs'));
    $appRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    
    if (strpos($appRoot, $docRoot) === 0) {
        $subDir = substr($appRoot, strlen($docRoot));
        $baseUrl = rtrim($protocol . $host . $subDir, '/');
    } else {
        // Fallback for custom alias or vhost
        $baseUrl = rtrim($protocol . $host . '/Bela Vista', '/');
    }
    define('BASE_URL', $baseUrl);
}

// Session Security Configuration
if (session_status() === PHP_SESSION_NONE) {
    // 24 hours session lifetime
    $sessionLifetime = 86400;
    
    ini_set('session.gc_maxlifetime', (string)$sessionLifetime);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $cookieParams = [
        'lifetime' => $sessionLifetime,
        'path'     => '/',
        'domain'   => '',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    
    session_set_cookie_params($cookieParams);
    session_name('BVO_SPA_SESSION');
    session_start();
}

/**
 * Generates a cryptographically secure CSRF token and saves it in the session.
 *
 * @param bool $refresh Force new token generation
 * @return string
 */
function generate_csrf_token(bool $refresh = false): string {
    if ($refresh || empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Returns current CSRF token without forcing regeneration.
 *
 * @return string
 */
function get_csrf_token(): string {
    return generate_csrf_token(false);
}

/**
 * Outputs a hidden HTML input field with the current CSRF token.
 *
 * @return string
 */
function csrf_token_field(): string {
    $token = htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validates a submitted CSRF token against the current session token.
 *
 * @param string|null $token Submitted token (from $_POST or header)
 * @return bool
 */
function validate_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    // Token expires after 4 hours
    $tokenMaxAge = 14400;
    if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > $tokenMaxAge)) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Resolves a full application URL from a relative path.
 *
 * @param string $path
 * @return string
 */
function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return empty($path) ? BASE_URL : BASE_URL . '/' . $path;
}

/**
 * Resolves an asset URL (CSS, JS, images).
 *
 * @param string $path
 * @return string
 */
function asset(string $path = ''): string {
    return url('assets/' . ltrim($path, '/'));
}
