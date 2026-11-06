<?php
/**
 * Bella Vista Oasis Spa - Global Header Template
 * 
 * Provides HTML <head>, luxury typography, CDNs, CSRF meta-tags,
 * and dynamic role-aware responsive navigation.
 */

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/app.php';
}
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? APP_NAME . ' | ' . APP_TAGLINE;
$currentRole = get_user_role();
$isLoggedIn = is_logged_in();
$userFullName = $_SESSION['user_name'] ?? 'Guest';
$userEmail = $_SESSION['user_email'] ?? '';
$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bella Vista Oasis Spa - The Pinnacle of Holistic Serenity and Restorative Luxury in Tagaytay. Bespoke massage therapy, hydrotherapy suites, and botanical wellness.">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Google Fonts: Playfair Display (Serif) & Inter (Sans-Serif) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.php" rel="stylesheet" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css';">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 Pro / Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Chart.js CDN (Loaded globally for analytics and dashboards) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- Bella Vista Oasis Luxury Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= APP_VERSION ?>">
</head>
<body class="bg-obsidian text-light d-flex flex-column min-vh-100">

<!-- Navigation Bar -->
<header class="sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark luxury-navbar py-3">
        <div class="container-fluid px-lg-5">
            <!-- Brand Logo & Monogram -->
            <a class="navbar-brand d-flex align-items-center" href="<?= url('index.php') ?>">
                <div class="brand-crest me-3">
                    <span class="crest-icon"><i class="fa-solid fa-spa text-gold"></i></span>
                </div>
                <div class="d-flex flex-column">
                    <span class="brand-name font-playfair tracking-wide text-gold fw-bold">BELLA VISTA</span>
                    <span class="brand-sub font-inter text-gold-muted text-uppercase">Oasis Spa & Wellness</span>
                </div>
            </a>

            <!-- Mobile Hamburger Toggle -->
            <button class="navbar-toggler luxury-toggler border-0 p-2" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 font-inter">
                    <?php if (!$isLoggedIn || $currentRole === 'customer'): ?>
                        <!-- Public & Customer Common Links -->
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('index.php') ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('services.php') ?>">Services & Suites</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('about.php') ?>">Our Heritage</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('contact.php') ?>">Concierge</a>
                        </li>
                        <?php if ($isLoggedIn && $currentRole === 'customer'): ?>
                            <li class="nav-item">
                                <a class="nav-link px-3 text-gold" href="<?= url('customer/dashboard.php') ?>">
                                    <i class="fa-solid fa-user-circle me-1"></i> Guest Sanctuary
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="<?= url('customer/appointments.php') ?>">Appointments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="<?= url('customer/health-declaration.php') ?>">Health Form</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="<?= url('customer/loyalty.php') ?>">Rewards</a>
                            </li>
                        <?php endif; ?>

                    <?php elseif (in_array($currentRole, ['admin', 'manager', 'front_desk'], true)): ?>
                        <!-- Administrative & Management Links -->
                        <li class="nav-item">
                            <a class="nav-link px-3 text-gold" href="<?= url('admin/dashboard.php') ?>">
                                <i class="fa-solid fa-chart-line me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('admin/appointments.php') ?>">Appointments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('admin/rooms.php') ?>">Rooms & Sanitization</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('admin/customers.php') ?>">Guests</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('admin/billing.php') ?>">Billing & POS</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle px-3" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Operations
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark luxury-dropdown">
                                <li><a class="dropdown-item" href="<?= url('admin/inventory.php') ?>"><i class="fa-solid fa-boxes-stacked me-2 text-gold"></i>Inventory</a></li>
                                <li><a class="dropdown-item" href="<?= url('admin/employees.php') ?>"><i class="fa-solid fa-users-gear me-2 text-gold"></i>Staff & Roster</a></li>
                                <li><a class="dropdown-item" href="<?= url('admin/reports.php') ?>"><i class="fa-solid fa-file-invoice-dollar me-2 text-gold"></i>Financial Reports</a></li>
                                <li><hr class="dropdown-divider border-gold-subtle"></li>
                                <li><a class="dropdown-item" href="<?= url('admin/settings.php') ?>"><i class="fa-solid fa-sliders me-2 text-gold"></i>System Settings</a></li>
                            </ul>
                        </li>

                    <?php elseif ($currentRole === 'therapist'): ?>
                        <!-- Master Therapist Portal -->
                        <li class="nav-item">
                            <a class="nav-link px-3 text-gold" href="<?= url('therapist/dashboard.php') ?>">
                                <i class="fa-solid fa-hands-holding me-1"></i> Therapist Portal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('therapist/schedule.php') ?>">My Shift Roster</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('therapist/appointments.php') ?>">Assigned Sessions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?= url('therapist/health-notes.php') ?>">Guest Health Notes</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Action Button & Auth Dropdown -->
                <div class="d-flex align-items-center gap-3">
                    <?php if (!$isLoggedIn): ?>
                        <a href="<?= url('login.php') ?>" class="btn btn-outline-gold btn-sm px-3">
                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Sign In
                        </a>
                        <a href="<?= url('booking.php') ?>" class="btn btn-gold btn-sm px-4 fw-semibold shadow-gold">
                            <i class="fa-solid fa-calendar-check me-1"></i> Book Sanctuary
                        </a>
                    <?php else: ?>
                        <!-- Quick Book Button for Customers -->
                        <?php if ($currentRole === 'customer'): ?>
                            <a href="<?= url('booking.php') ?>" class="btn btn-gold btn-sm px-3 fw-semibold shadow-gold d-none d-md-inline-flex align-items-center">
                                <i class="fa-solid fa-plus-circle me-1"></i> Reserve Session
                            </a>
                        <?php endif; ?>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-charcoal-glass dropdown-toggle d-flex align-items-center gap-2 border-gold-subtle text-light py-1 px-3 rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar-circle">
                                    <span class="avatar-initial"><?= strtoupper(substr($userFullName, 0, 1)) ?></span>
                                </div>
                                <span class="d-none d-sm-inline font-inter small"><?= htmlspecialchars($userFullName, ENT_QUOTES) ?></span>
                                <span class="badge badge-gold-outline small"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $currentRole)), ENT_QUOTES) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark luxury-dropdown mt-2 shadow-lg">
                                <li class="px-3 py-2 border-bottom border-gold-subtle">
                                    <div class="text-gold fw-bold font-playfair"><?= htmlspecialchars($userFullName, ENT_QUOTES) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 200px;"><?= htmlspecialchars($userEmail, ENT_QUOTES) ?></div>
                                </li>
                                <?php if ($currentRole === 'customer'): ?>
                                    <li><a class="dropdown-item py-2" href="<?= url('customer/profile.php') ?>"><i class="fa-solid fa-id-card me-2 text-gold"></i>Guest Profile</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= url('customer/appointments.php') ?>"><i class="fa-solid fa-calendar-days me-2 text-gold"></i>My Bookings</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= url('customer/loyalty.php') ?>"><i class="fa-solid fa-coins me-2 text-gold"></i>Loyalty Points</a></li>
                                <?php elseif ($currentRole === 'therapist'): ?>
                                    <li><a class="dropdown-item py-2" href="<?= url('therapist/profile.php') ?>"><i class="fa-solid fa-user-doctor me-2 text-gold"></i>Therapist Profile</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item py-2" href="<?= url('admin/profile.php') ?>"><i class="fa-solid fa-user-shield me-2 text-gold"></i>Staff Account</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= url('admin/settings.php') ?>"><i class="fa-solid fa-gear me-2 text-gold"></i>Preferences</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider border-gold-subtle my-1"></li>
                                <li>
                                    <a class="dropdown-item py-2 text-danger" href="<?= url('logout.php') ?>">
                                        <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Sign Out
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Main Container Wrap -->
<main class="flex-grow-1">
    <div class="container-fluid px-lg-5 pt-3">
        <?= display_flash() ?>
    </div>
