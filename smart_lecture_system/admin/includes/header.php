<?php
require_once __DIR__ . '/../../config.php';
redirectIfNotLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Smart Lecture System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-university"></i>
            <h4>Smart Lecture System</h4>
            <p>University Management</p>
        </div>

        <div class="sidebar-menu">
            <div class="menu-title">Main Menu</div>
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="lecturers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'lecturers.php' ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i> Lecturers
            </a>
            <a href="courses.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="timetable.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'timetable.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Timetable
            </a>

            <div class="menu-title">SMS Management</div>
            <a href="sms_logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sms_logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> SMS Logs
            </a>
            <a href="broadcast_sms.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'broadcast_sms.php' ? 'active' : ''; ?>">
                <i class="fas fa-broadcast-tower"></i> Broadcast SMS
            </a>
            <a href="todays_reminders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'todays_reminders.php' ? 'active' : ''; ?>">
                <i class="fas fa-bell"></i> Today's Reminders
            </a>

            <div class="menu-title">Reports & Tools</div>
            <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="backup.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'backup.php' ? 'active' : ''; ?>">
                <i class="fas fa-database"></i> Backup & Restore
            </a>
            <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i> Profile
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-gold d-lg-none me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h5><i class="fas fa-graduation-cap me-2"></i><?php echo $pageTitle ?? 'Dashboard'; ?></h5>
            </div>
            <div class="user-info">
                <div class="notification-badge me-3">
                    <a href="notifications.php" class="text-gold">
                        <i class="fas fa-bell fa-lg"></i>
                        <?php
                        $notifCount = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE status = 'Unread'")->fetch_assoc()['count'];
                        if ($notifCount > 0): ?>
                        <span class="badge"><?php echo $notifCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <img src="../assets/images/<?php echo $_SESSION['photo'] ?? 'default-user.png'; ?>" alt="User" onerror="this.src='../assets/images/default-user.png'">
                <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
                <span class="badge-gold ms-2"><?php echo $_SESSION['role'] ?? 'User'; ?></span>
            </div>
        </div>

        <div class="content-wrapper">
