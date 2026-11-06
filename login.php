<?php
/**
 * Bella Vista Oasis Spa - Guest & Staff Authentication Portal
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    $role = get_user_role();
    if ($role === 'customer') {
        redirect('customer/dashboard.php');
    } elseif ($role === 'therapist') {
        redirect('therapist/dashboard.php');
    } else {
        redirect('admin/dashboard.php');
    }
}

$errorMessage = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        $errorMessage = 'Invalid or expired session security token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $emailValue = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

        $result = login($email, $password);
        if ($result['success']) {
            flash_message('success', $result['message']);
            redirect($result['redirect']);
        } else {
            $errorMessage = $result['message'];
        }
    }
}

$pageTitle = 'Sign In | Bella Vista Oasis Spa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5 my-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-10">
            <!-- Luxury Glass Login Card -->
            <div class="luxury-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-crest mx-auto mb-3" style="width: 54px; height: 54px;">
                        <i class="fa-solid fa-spa text-gold fs-3"></i>
                    </div>
                    <h2 class="font-playfair text-gold mb-1">Sanctuary Portal</h2>
                    <p class="text-silver small font-inter">Sign in to your guest sanctuary or administrative account</p>
                </div>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger luxury-alert py-2 px-3 small mb-4" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2 text-danger"></i>
                        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('login.php') ?>" class="needs-luxury-validation" novalidate>
                    <?= csrf_token_field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $emailValue ?>" placeholder="name@example.com" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label mb-0">Password</label>
                            <a href="<?= url('forgot-password.php') ?>" class="small text-gold-muted text-decoration-none">Forgot Password?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small text-silver" for="remember">Keep me signed in on this device</label>
                    </div>

                    <button type="submit" class="btn btn-gold w-100 py-2 fw-semibold shadow-gold mb-3">
                        <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Enter Sanctuary
                    </button>
                </form>

                <div class="text-center pt-3 border-top border-gold-subtle">
                    <p class="small text-silver mb-2">New guest to Bella Vista Oasis?</p>
                    <a href="<?= url('register.php') ?>" class="btn btn-outline-gold btn-sm px-4">
                        <i class="fa-solid fa-user-plus me-1"></i> Create Guest Account
                    </a>
                </div>

                <!-- Demo Access Hints -->
                <div class="mt-4 pt-3 border-top border-gold-subtle text-center small text-silver-dark font-inter">
                    <div class="text-gold-muted fw-semibold mb-1">Quick Demo Credentials:</div>
                    <div>Admin: <code class="text-gold">admin@bellavistaspa.com</code> / <code class="text-gold">Admin@123</code></div>
                    <div>Guest: <code class="text-gold">customer@bellavistaspa.com</code> / <code class="text-gold">Admin@123</code></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
