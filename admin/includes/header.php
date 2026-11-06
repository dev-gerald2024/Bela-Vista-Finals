<?php
/**
 * Bella Vista Oasis Spa - Admin Header & Layout Start
 */
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../../config/app.php';
}
if (!isset($pdo)) {
    require_once __DIR__ . '/../../config/database.php';
}
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/../../includes/functions.php';
}

$pageTitle = $pageTitle ?? 'Admin Portal';
$currentUserName = $_SESSION['user_name'] ?? 'Staff Member';
$currentUserRole = $_SESSION['user_role'] ?? 'staff';
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$userInitials = strtoupper(substr($_SESSION['first_name'] ?? 'B', 0, 1) . substr($_SESSION['last_name'] ?? 'V', 0, 1));

// Fetch recent notifications
$adminNotifications = [];
$unreadNotifCount = 0;
try {
    if ($pdo instanceof PDO) {
        $nStmt = $pdo->prepare("SELECT * FROM notifications 
                                WHERE (user_id = ? OR user_id IS NULL)
                                ORDER BY created_at DESC LIMIT 6");
        $nStmt->execute([$currentUserId]);
        $adminNotifications = $nStmt->fetchAll();

        $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications 
                                     WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0");
        $unreadStmt->execute([$currentUserId]);
        $unreadNotifCount = (int)$unreadStmt->fetchColumn();
    }
} catch (Exception $e) {
    // Ignore if notification query fails
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(get_csrf_token()) ?>">
    <title><?= htmlspecialchars($pageTitle) ?> — <?= APP_NAME ?></title>

    <!-- Google Fonts: Playfair Display & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js 4.4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Admin Custom Luxury Styles -->
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>

<div class="admin-wrapper">
    <!-- Include Sidebar Navigation -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Top Navbar -->
        <header class="admin-header">
            <div class="d-flex align-items-center gap-3">
                <button class="header-btn d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle Navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <!-- Search Bar -->
                <div class="header-search d-none d-sm-block">
                    <form action="appointments.php" method="GET">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" placeholder="Search appointments, guests..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </form>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="header-actions">
                <!-- Quick Link to Public Site -->
                <a href="<?= url('index.php') ?>" target="_blank" class="header-btn" data-bs-toggle="tooltip" title="View Public Website">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>

                <!-- Notifications Dropdown -->
                <div class="dropdown">
                    <button class="header-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                        <i class="fa-regular fa-bell"></i>
                        <?php if ($unreadNotifCount > 0): ?>
                            <span class="badge-dot"></span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-luxury p-2" style="width: 320px;">
                        <li class="d-flex align-items-center justify-content-between px-2 py-1 mb-2 border-bottom border-secondary border-opacity-25">
                            <span class="fw-semibold text-gold small">Notifications</span>
                            <span class="badge bg-gold text-dark"><?= $unreadNotifCount ?> Unread</span>
                        </li>
                        <?php if (empty($adminNotifications)): ?>
                            <li class="text-center py-3 text-muted small">No notifications found</li>
                        <?php else: ?>
                            <?php foreach ($adminNotifications as $notif): ?>
                                <li>
                                    <div class="dropdown-item p-2 rounded mb-1 <?= (int)$notif['is_read'] === 0 ? 'bg-black bg-opacity-25' : '' ?>" style="white-space: normal;">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="fa-solid fa-circle-info text-gold mt-1 small"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium small text-white"><?= htmlspecialchars($notif['title']) ?></div>
                                                <div class="text-silver small" style="font-size: 0.76rem;"><?= htmlspecialchars($notif['message']) ?></div>
                                                <div class="text-muted" style="font-size: 0.7rem;"><?= format_date($notif['created_at'], 'M d, g:i A') ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="d-flex align-items-center gap-2 bg-transparent border-0 p-0 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="sidebar-user-avatar" style="width: 36px; height: 36px; font-size: 0.85rem;">
                            <?= htmlspecialchars($userInitials) ?>
                        </div>
                        <div class="d-none d-md-block">
                            <div class="fw-semibold small text-white lh-1"><?= htmlspecialchars($currentUserName) ?></div>
                            <small class="text-gold text-uppercase" style="font-size: 0.68rem;"><?= htmlspecialchars(str_replace('_', ' ', $currentUserRole)) ?></small>
                        </div>
                        <i class="fa-solid fa-chevron-down text-silver small ms-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-luxury">
                        <li>
                            <div class="px-3 py-2 border-bottom border-secondary border-opacity-25">
                                <p class="small text-white fw-bold mb-0"><?= htmlspecialchars($currentUserName) ?></p>
                                <span class="small text-muted"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></span>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="employees.php">
                                <i class="fa-solid fa-user-circle"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <?php if ($currentUserRole === 'admin'): ?>
                            <li>
                                <a class="dropdown-item" href="settings.php">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>System Settings</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= url('logout.php') ?>">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content Area Wrapper -->
        <div class="admin-content">
            <!-- Flash Message Banner -->
            <?= display_flash() ?>
