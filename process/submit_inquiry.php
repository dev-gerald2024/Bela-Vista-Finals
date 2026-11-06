<?php
/**
 * Bella Vista Oasis Spa - Submit Inquiry Process Handler
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_message('danger', 'Invalid request method.');
    redirect('contact.php');
}

// 1. Verify CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    flash_message('danger', 'Security validation failed (CSRF). Please reload the page and try again.');
    redirect('contact.php');
}

// 2. Extract and Sanitize Inputs
$name    = trim(sanitize_input($_POST['name'] ?? ''));
$email   = trim(strtolower(sanitize_input($_POST['email'] ?? '')));
$phone   = trim(sanitize_input($_POST['phone'] ?? ''));
$subject = trim(sanitize_input($_POST['subject'] ?? ''));
$message = trim(sanitize_input($_POST['message'] ?? ''));

// 3. Validation
$errors = [];
if (empty($name)) {
    $errors[] = 'Please provide your full name.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}
if (empty($subject)) {
    $errors[] = 'Please select an inquiry subject.';
}
if (empty($message) || strlen($message) < 10) {
    $errors[] = 'Please provide a detailed inquiry message (minimum 10 characters).';
}

if (!empty($errors)) {
    flash_message('danger', implode('<br>', $errors));
    redirect('contact.php');
}

// 4. Insert into Database
try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("INSERT INTO inquiries 
        (`name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) 
        VALUES (?, ?, ?, ?, ?, 'new', NOW())");
    $stmt->execute([$name, $email, $phone, $subject, $message]);

    $inquiryId = (int)$pdo->lastInsertId();

    // Log audit
    log_audit(
        $_SESSION['user_id'] ?? null,
        'INQUIRY_SUBMITTED',
        'inquiries',
        $inquiryId,
        null,
        ['name' => $name, 'email' => $email, 'subject' => $subject]
    );

    flash_message('success', 'Thank you, ' . htmlspecialchars($name, ENT_QUOTES) . '! Your message has been received by our private concierge. We will respond within 2 business hours.');
} catch (Exception $e) {
    error_log("Inquiry Submission Error: " . $e->getMessage());
    flash_message('danger', 'We encountered an unexpected technical issue transmitting your inquiry. Please call us directly at +63 (02) 8876-5432.');
}

redirect('contact.php');
