<?php
/**
 * Bella Vista Oasis Spa - Password Reset Confirmation Portal
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        $errorMessage = 'Invalid security token. Please try again.';
    } else {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword !== $confirmPassword) {
            $errorMessage = 'Passwords do not match.';
        } else {
            $res = reset_password($token, $newPassword);
            if ($res['success']) {
                flash_message('success', $res['message']);
                redirect('login.php');
            } else {
                $errorMessage = $res['message'];
            }
        }
    }
}

$pageTitle = 'Set New Password | Bella Vista Oasis Spa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="luxury-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-crest mx-auto mb-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-lock-open text-gold fs-4"></i>
                    </div>
                    <h3 class="font-playfair text-gold mb-1">Set New Password</h3>
                    <p class="text-silver small font-inter">Create a secure new password for your sanctuary account.</p>
                </div>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger luxury-alert py-2 px-3 small mb-3">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($errorMessage, ENT_QUOTES) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($token)): ?>
                    <div class="alert alert-warning luxury-alert small py-3">
                        No reset token provided. Please request a new link from the forgot password page.
                        <div class="mt-2">
                            <a href="<?= url('forgot-password.php') ?>" class="btn btn-outline-gold btn-sm">Request Link</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?= url('reset-password.php') ?>" class="needs-luxury-validation" novalidate>
                        <?= csrf_token_field() ?>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password (Min 8 chars)</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-gold w-100 py-2 fw-semibold shadow-gold">
                            <i class="fa-solid fa-shield-check me-2"></i> Update Password & Sign In
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
