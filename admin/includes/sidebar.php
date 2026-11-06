<?php
/**
 * Bella Vista Oasis Spa - Admin Sidebar Navigation
 */
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../config/app.php';
}

$currentUserRole = $_SESSION['user_role'] ?? 'guest';
$currentUserName = $_SESSION['user_name'] ?? 'Staff Member';
$userInitials = strtoupper(substr($_SESSION['first_name'] ?? 'B', 0, 1) . substr($_SESSION['last_name'] ?? 'V', 0, 1));
$currentPage = basename($_SERVER['PHP_SELF']);

// Dynamic badge counts (Pending Appointments & New Inquiries)
$pendingCount = 0;
$newInquiryCount = 0;
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $badgeStmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
        $pendingCount = (int)$badgeStmt->fetchColumn();

        $inqStmt = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'");
        $newInquiryCount = (int)$inqStmt->fetchColumn();
    }
} catch (Exception $e) {
    // Graceful fallback if query fails
}
?>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand / Logo -->
    <a href="dashboard.php" class="sidebar-brand">
        <div class="logo-icon">
            <i class="fa-solid fa-spa"></i>
        </div>
        <div>
            <h1 class="brand-title">Bella Vista</h1>
            <span class="brand-sub">Oasis Spa Luxury</span>
        </div>
    </a>

    <!-- Current User Info -->
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <?= htmlspecialchars($userInitials) ?>
        </div>
        <div class="sidebar-user-info">
            <h2 class="sidebar-user-name" title="<?= htmlspecialchars($currentUserName) ?>"><?= htmlspecialchars($currentUserName) ?></h2>
            <span class="sidebar-user-role"><?= htmlspecialchars(str_replace('_', ' ', $currentUserRole)) ?></span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <ul class="sidebar-nav">
        <li class="sidebar-heading">Main Operations</li>
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="appointments.php" class="nav-link <?= in_array($currentPage, ['appointments.php', 'appointment-form.php']) ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Appointments</span>
                <?php if ($pendingCount > 0): ?>
                    <span class="badge bg-warning text-dark"><?= $pendingCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a href="calendar.php" class="nav-link <?= $currentPage === 'calendar.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Calendar View</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="rooms.php" class="nav-link <?= $currentPage === 'rooms.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-door-open"></i>
                <span>Room Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="therapists.php" class="nav-link <?= $currentPage === 'therapists.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-nurse"></i>
                <span>Therapists</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="services.php" class="nav-link <?= $currentPage === 'services.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-spa"></i>
                <span>Services</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="customers.php" class="nav-link <?= $currentPage === 'customers.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Customers</span>
            </a>
        </li>

        <li class="sidebar-heading">Staff & Personnel</li>
        <li class="nav-item">
            <a href="employees.php" class="nav-link <?= $currentPage === 'employees.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-id-badge"></i>
                <span>Employees</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="attendance.php" class="nav-link <?= $currentPage === 'attendance.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock"></i>
                <span>Attendance</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="schedules.php" class="nav-link <?= $currentPage === 'schedules.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-alt"></i>
                <span>Staff Schedules</span>
            </a>
        </li>

        <li class="sidebar-heading">Finance & Inventory</li>
        <li class="nav-item">
            <a href="payments.php" class="nav-link <?= in_array($currentPage, ['payments.php', 'refunds.php']) ? 'active' : '' ?>">
                <i class="fa-solid fa-credit-card"></i>
                <span>Payments & Receipts</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="expenses.php" class="nav-link <?= $currentPage === 'expenses.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>Expenses</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="inventory.php" class="nav-link <?= $currentPage === 'inventory.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Inventory</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="suppliers.php" class="nav-link <?= $currentPage === 'suppliers.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-truck"></i>
                <span>Suppliers</span>
            </a>
        </li>

        <li class="sidebar-heading">Marketing & Guest Relations</li>
        <li class="nav-item">
            <a href="promotions.php" class="nav-link <?= $currentPage === 'promotions.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-tags"></i>
                <span>Promotions & Vouchers</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="loyalty.php" class="nav-link <?= $currentPage === 'loyalty.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-award"></i>
                <span>Loyalty Program</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="reviews.php" class="nav-link <?= $currentPage === 'reviews.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-star"></i>
                <span>Reviews</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="inquiries.php" class="nav-link <?= $currentPage === 'inquiries.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-envelope"></i>
                <span>Inquiries</span>
                <?php if ($newInquiryCount > 0): ?>
                    <span class="badge bg-gold text-dark"><?= $newInquiryCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a href="website-content.php" class="nav-link <?= $currentPage === 'website-content.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-globe"></i>
                <span>Website Content</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="reports.php" class="nav-link <?= $currentPage === 'reports.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i>
                <span>Reports & Analytics</span>
            </a>
        </li>

        <?php if ($currentUserRole === 'admin'): ?>
            <li class="sidebar-heading">Administration</li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gear"></i>
                    <span>System Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="audit-logs.php" class="nav-link <?= $currentPage === 'audit-logs.php' ? 'active' : '' ?>">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Audit Logs</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Logout Button -->
    <div class="sidebar-footer">
        <a href="<?= url('logout.php') ?>" class="btn-logout">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Sign Out</span>
        </a>
    </div>
</aside>
