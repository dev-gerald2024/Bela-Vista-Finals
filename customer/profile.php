<?php
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
if (!is_logged_in() || get_user_role() !== 'customer') { redirect('../login.php'); }
$page_title = 'My Profile';
$pdo = get_db_connection();
$user_id = $_SESSION['user_id'];

// Fetch user + customer data
$stmt = $pdo->prepare("SELECT u.*, c.date_of_birth, c.gender, c.address, c.emergency_contact_name, c.emergency_contact_phone, c.notes as customer_notes FROM users u LEFT JOIN customers c ON c.user_id = u.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="font-heading text-gold mb-0"><i class="fas fa-user-circle me-2"></i>My Profile</h2>
            </div>

            <?php display_flash(); ?>

            <div class="row g-4">
                <!-- Profile Info -->
                <div class="col-lg-8">
                    <div class="glass-card p-4 mb-4">
                        <h5 class="text-gold mb-4"><i class="fas fa-id-card me-2"></i>Personal Information</h5>
                        <form action="../process/update_profile.php" method="POST">
                            <?= csrf_token_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">First Name</label>
                                    <input type="text" name="first_name" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Last Name</label>
                                    <input type="text" name="last_name" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Email Address</label>
                                    <input type="email" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control bg-dark text-light border-secondary" value="<?= $user['date_of_birth'] ?? '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Gender</label>
                                    <select name="gender" class="form-select bg-dark text-light border-secondary">
                                        <option value="">Prefer not to say</option>
                                        <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted">Address</label>
                                    <textarea name="address" class="form-control bg-dark text-light border-secondary" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Emergency Contact Name</label>
                                    <input type="text" name="emergency_contact_name" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($user['emergency_contact_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Emergency Contact Phone</label>
                                    <input type="tel" name="emergency_contact_phone" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($user['emergency_contact_phone'] ?? '') ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-gold mt-4"><i class="fas fa-save me-2"></i>Update Profile</button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar cards -->
                <div class="col-lg-4">
                    <!-- Avatar / Account Info -->
                    <div class="glass-card p-4 text-center mb-4">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:100px;height:100px;background:linear-gradient(135deg,#D6B77B,#C4A265);font-size:2.5rem;color:#101313;">
                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                        </div>
                        <h5 class="text-gold font-heading"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                        <p class="text-muted mb-1"><?= htmlspecialchars($user['email']) ?></p>
                        <small class="text-muted">Member since <?= date('F Y', strtotime($user['created_at'])) ?></small>
                    </div>

                    <!-- Change Password -->
                    <div class="glass-card p-4">
                        <h5 class="text-gold mb-4"><i class="fas fa-lock me-2"></i>Change Password</h5>
                        <form action="../process/change_password.php" method="POST">
                            <?= csrf_token_field() ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Password</label>
                                <input type="password" name="current_password" class="form-control bg-dark text-light border-secondary" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">New Password</label>
                                <input type="password" name="new_password" class="form-control bg-dark text-light border-secondary" required minlength="8">
                                <small class="text-muted">Min 8 characters, include uppercase, lowercase, and number</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control bg-dark text-light border-secondary" required>
                            </div>
                            <button type="submit" class="btn btn-outline-gold w-100"><i class="fas fa-key me-2"></i>Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

