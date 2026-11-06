<?php
/**
 * Bella Vista Oasis Spa - Forgot Password Portal
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$infoMessage = '';
$errorMessage = '';
$generatedToken = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        $errorMessage = 'Invalid or expired session security token.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $res = forgot_password($email);
        $infoMessage = $res['message'];
        if (!empty($res['token'])) {
            $generatedToken = $res['token'];
        }
    }
}

$pageTitle = 'Forgot Password | Bella Vista Oasis Spa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="luxury-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-crest mx-auto mb-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-key text-gold fs-4"></i>
                    </div>
                    <h3 class="font-playfair text-gold mb-1">Recover Sanctuary Access</h3>
                    <p class="text-silver small font-inter">Enter your registered email address and we will generate instructions to reset your password.</p>
                </div>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger luxury-alert py-2 px-3 small mb-3">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($errorMessage, ENT_QUOTES) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($infoMessage)): ?>
                    <div class="alert alert-info luxury-alert py-3 px-3 small mb-4">
                        <i class="fa-solid fa-circle-info me-2 text-gold"></i><?= htmlspecialchars($infoMessage, ENT_QUOTES) ?>
                        
                        <?php if (!empty($generatedToken)): ?>
                            <div class="mt-3 pt-2 border-top border-gold-subtle">
                                <span class="text-gold fw-bold">Development / Demo Reset Link:</span><br>
                                <a href="<?= url('reset-password.php?token=' . $generatedToken) ?>" class="text-break text-gold-light">
                                    <?= url('reset-password.php?token=' . $generatedToken) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('forgot-password.php') ?>" class="needs-luxury-validation" novalidate>
                    <?= csrf_token_field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">Registered Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="name@example.com" autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold w-100 py-2 fw-semibold shadow-gold mb-3">
                        <i class="fa-solid fa-paper-plane me-2"></i> Request Reset Instructions
                    </button>
                </form>

                <div class="text-center pt-3 border-top border-gold-subtle">
                    <a href="<?= url('login.php') ?>" class="small text-silver text-decoration-none">
                        <i class="fa-solid fa-arrow-left me-1"></i> Return to Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
