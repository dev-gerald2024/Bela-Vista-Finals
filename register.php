<?php
/**
 * Bella Vista Oasis Spa - Guest Registration Portal
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('customer/dashboard.php');
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        $errorMessage = 'Invalid or expired security token. Please try again.';
    } else {
        $regData = [
            'first_name'              => $_POST['first_name'] ?? '',
            'last_name'               => $_POST['last_name'] ?? '',
            'email'                   => $_POST['email'] ?? '',
            'phone'                   => $_POST['phone'] ?? '',
            'password'                => $_POST['password'] ?? '',
            'date_of_birth'           => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
            'gender'                  => $_POST['gender'] ?? 'prefer_not_to_say',
            'emergency_contact_name'  => $_POST['emergency_contact_name'] ?? '',
            'emergency_contact_phone' => $_POST['emergency_contact_phone'] ?? '',
            'address'                 => $_POST['address'] ?? ''
        ];

        $confirmPassword = $_POST['confirm_password'] ?? '';
        if ($regData['password'] !== $confirmPassword) {
            $errorMessage = 'Passwords do not match. Please verify.';
        } else {
            $result = register_customer($regData);
            if ($result['success']) {
                flash_message('success', $result['message']);
                // Auto login the newly registered user
                login($regData['email'], $regData['password']);
                redirect('customer/dashboard.php');
            } else {
                $errorMessage = $result['message'];
            }
        }
    }
}

$pageTitle = 'Guest Registration | Bella Vista Oasis Spa';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="luxury-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-crest mx-auto mb-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-spa text-gold fs-3"></i>
                    </div>
                    <h2 class="font-playfair text-gold mb-1">Create Guest Account</h2>
                    <p class="text-silver small font-inter">Join our sanctuary circle to book sessions, earn reward points, and access private health records.</p>
                </div>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger luxury-alert py-2 px-3 small mb-4">
                        <i class="fa-solid fa-circle-exclamation me-2 text-danger"></i>
                        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('register.php') ?>" class="needs-luxury-validation" novalidate>
                    <?= csrf_token_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required value="<?= htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Mobile Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="+63 9XX XXX XXXX" required value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Password * (Min 8 chars)</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                        </div>

                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="prefer_not_to_say">Prefer not to say</option>
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">Address / Residence</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="City, Province / Country" value="<?= htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" value="<?= htmlspecialchars($_POST['emergency_contact_name'] ?? '', ENT_QUOTES) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                            <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" value="<?= htmlspecialchars($_POST['emergency_contact_phone'] ?? '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top border-gold-subtle">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agree_terms" required>
                            <label class="form-check-label small text-silver" for="agree_terms">
                                I agree to the <a href="<?= url('terms.php') ?>" class="text-gold" target="_blank">Sanctuary Terms & Conditions</a> and consent to confidential health declarations as required by spa protocols.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-gold w-100 py-2 fw-semibold shadow-gold">
                            <i class="fa-solid fa-user-check me-2"></i> Complete Registration (Receive 50 Bonus Points)
                        </button>
                    </div>
                </form>

                <div class="text-center pt-3 mt-3 border-top border-gold-subtle">
                    <span class="small text-silver">Already have a sanctuary account?</span>
                    <a href="<?= url('login.php') ?>" class="small text-gold ms-1 text-decoration-none fw-semibold">Sign In Here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
