<?php
/**
 * Bella Vista Oasis Spa - Customer Portal Header & Topbar Component
 */

if (!isset($pageTitle)) {
    $pageTitle = 'Guest Portal';
}

$currentUserId = $_SESSION['user_id'] ?? 0;
$unreadCountHeader = 0;
if ($currentUserId > 0) {
    try {
        $dbH = get_db_connection();
        $stH = $dbH->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stH->execute([$currentUserId]);
        $unreadCountHeader = (int)$stH->fetchColumn();
    } catch (Exception $e) {
        $unreadCountHeader = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Bella Vista Oasis Spa</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Luxury Customer Portal CSS -->
    <link rel="stylesheet" href="../assets/css/customer.css">
</head>
<body>

<div class="customer-layout">
    <!-- Include Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="customer-main">
        <!-- Top Navigation Bar -->
        <header class="customer-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-charcoal d-lg-none" id="mobileSidebarToggle" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h2 class="topbar-title font-heading"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h2>
            </div>

            <div class="topbar-actions">
                <a href="notifications.php" class="btn btn-charcoal position-relative p-2 px-3" title="Notifications">
                    <i class="fa-solid fa-bell text-gold"></i>
                    <?php if ($unreadCountHeader > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark font-monospace" style="font-size: 0.65rem;">
                            <?= $unreadCountHeader ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (basename($_SERVER['PHP_SELF']) !== 'book.php'): ?>
                    <a href="book.php" class="btn btn-gold d-none d-sm-inline-flex">
                        <i class="fa-solid fa-calendar-plus"></i>
                        <span>Book Treatment</span>
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Dynamic Main Content Area -->
        <main class="customer-content">
            <!-- Flash Message Banner -->
            <?= display_flash() ?>
