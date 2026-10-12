<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: admin/dashboard.php");
    exit();
}

$message = '';
$messageType = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'Active'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['photo'] = $user['photo'];
                $_SESSION['last_activity'] = time();

                // Update last login
                $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();

                header("Location: admin/dashboard.php");
                exit();
            } else {
                $message = 'Invalid password. Please try again.';
            }
        } else {
            $message = 'Invalid username or account is inactive.';
        }
    }
}

// Get system settings
$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'system_name'");
$stmt->execute();
$result = $stmt->get_result();
$systemName = $result->fetch_assoc()['setting_value'] ?? 'Smart Lecture System';

$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'system_subtitle'");
$stmt->execute();
$result = $stmt->get_result();
$systemSubtitle = $result->fetch_assoc()['setting_value'] ?? 'Automated Lecture Management Platform';

$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'institution_name'");
$stmt->execute();
$result = $stmt->get_result();
$institutionName = $result->fetch_assoc()['setting_value'] ?? 'University';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($systemName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-university"></i>
            <h2><?php echo htmlspecialchars($systemName); ?></h2>
            <p><?php echo htmlspecialchars($institutionName); ?></p>
            <p style="font-size: 12px; color: #888;"><?php echo htmlspecialchars($systemSubtitle); ?></p>
        </div>

        <?php if ($message): ?>
            <?php echo showAlert($message, $messageType); ?>
        <?php endif; ?>

        <?php if (isset($_GET['timeout'])): ?>
            <?php echo showAlert('Your session has expired. Please login again.', 'warning'); ?>
        <?php endif; ?>

        <?php if (isset($_GET['logout'])): ?>
            <?php echo showAlert('You have been logged out successfully.', 'success'); ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user me-2"></i>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                    <button type="button" class="btn btn-outline-gold" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3 d-flex justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label" for="remember" style="color: #ccc;">Remember me</label>
                </div>
                <a href="forgot_password.php" style="color: var(--primary-gold); text-decoration: none; font-size: 14px;">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-gold w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>

        <div class="text-center mt-3">
            <p style="color: #888; font-size: 12px;">Default: admin / admin123</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
