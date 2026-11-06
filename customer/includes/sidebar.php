<?php
/**
 * Bella Vista Oasis Spa - Customer Sidebar Navigation Component
 * 
 * Includes luxury brand identity, guest profile summary, navigation links with
 * active state highlighting, unread notification counter, and secure logout.
 */

// Determine active page
$currentScript = basename($_SERVER['PHP_SELF']);
$userId = $_SESSION['user_id'] ?? 0;
$userFirstName = $_SESSION['first_name'] ?? 'Valued';
$userLastName = $_SESSION['last_name'] ?? 'Guest';
$userName = trim($userFirstName . ' ' . $userLastName);
$userRole = $_SESSION['user_role'] ?? 'customer';

// Calculate initials
$initials = strtoupper(substr($userFirstName, 0, 1) . substr($userLastName, 0, 1));
if (empty($initials)) {
    $initials = 'BV';
}

// Fetch unread notification count
$unreadCount = 0;
if ($userId > 0) {
    try {
        $db = get_db_connection();
        $notifStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $notifStmt->execute([$userId]);
        $unreadCount = (int)$notifStmt->fetchColumn();
    } catch (Exception $e) {
        $unreadCount = 0;
    }
}
?>

<!-- Customer Sidebar Navigation -->
<aside class="customer-sidebar" id="customerSidebar">
    <!-- Brand Header -->
    <div class="sidebar-brand">
        <div class="brand-icon-wrapper">
            <i class="fa-solid fa-spa"></i>
        </div>
        <div>
            <h1 class="brand-title">Bella Vista</h1>
            <p class="brand-subtitle">Oasis Spa Concierge</p>
        </div>
    </div>

    <!-- Guest Profile Info -->
    <div class="sidebar-user">
        <div class="user-avatar-gold">
            <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div style="min-width: 0;">
            <p class="user-name" title="<?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>
            </p>
            <span class="user-role-badge">
                <i class="fa-solid fa-crown me-1"></i>Guest Member
            </span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <div class="nav-section-title">Sanctuary Portal</div>

        <a href="dashboard.php" class="nav-link-custom <?= ($currentScript === 'dashboard.php') ? 'active' : '' ?>">
            <span><i class="fa-solid fa-compass menu-icon"></i>Overview</span>
        </a>

        <a href="book.php" class="nav-link-custom <?= ($currentScript === 'book.php') ? 'active' : '' ?>">
            <span><i class="fa-solid fa-calendar-plus menu-icon"></i>Book Appointment</span>
            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size: 0.65rem; font-weight: 700;">NEW</span>
        </a>

        <a href="appointments.php" class="nav-link-custom <?= ($currentScript === 'appointments.php' || $currentScript === 'reschedule.php') ? 'active' : '' ?>">
            <span><i class="fa-solid fa-calendar-check menu-icon"></i>My Bookings</span>
        </a>

        <div class="nav-section-title mt-3">Account & Billing</div>

        <a href="payments.php" class="nav-link-custom <?= ($currentScript === 'payments.php' || $currentScript === 'pay.php' || $currentScript === 'receipt.php') ? 'active' : '' ?>">
            <span><i class="fa-solid fa-receipt menu-icon"></i>Payments & Receipts</span>
        </a>

        <a href="loyalty.php" class="nav-link-custom <?= ($currentScript === 'loyalty.php') ? 'active' : '' ?>">
            <span><i class="fa-solid fa-gem menu-icon"></i>Loyalty Rewards</span>
        </a>

        <a href="reviews.php" class="nav-link-custom <?= ($currentScript === 'reviews.php') ? 'active' : '' ?>">
            <span><i class="fa-solid fa-star menu-icon"></i>Guest Reviews</span>
        </a>

        <div class="nav-section-title mt-3">Concierge & Settings</div>

        <a href="notifications.php" class="nav-link-custom <?= ($currentScript === 'notifications.php') ? 'active' : '' ?>">
            <span><i class="fa-solid fa-bell menu-icon"></i>Notifications</span>
            <?php if ($unreadCount > 0): ?>
                <span class="nav-badge"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>

        <a href="support.php" class="nav-link-custom <?= ($currentScript === 'support.php') ? 'active' : '' ?>">
            <span><i class="fa-solid fa-headset menu-icon"></i>Concierge Support</span>
        </a>

        <a href="profile.php" class="nav-link-custom <?= ($currentScript === 'profile.php') ? 'active' : '' ?>">
            <span><i class="fa-solid fa-user-gear menu-icon"></i>My Profile</span>
        </a>
    </nav>

    <!-- Sidebar Footer / Logout -->
    <div class="sidebar-footer">
        <a href="../logout.php" class="btn btn-charcoal w-100 d-flex align-items-center justify-content-center text-decoration-none">
            <i class="fa-solid fa-arrow-right-from-bracket me-2 text-gold"></i>
            <span>Sign Out</span>
        </a>
    </div>
</aside>
