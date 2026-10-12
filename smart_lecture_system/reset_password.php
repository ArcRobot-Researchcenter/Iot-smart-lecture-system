<?php
/**
 * Emergency Password Reset Utility
 * Run this once, then delete it for security
 * URL: http://localhost/smart_lecture_system/reset_password.php
 */

require_once 'config.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters!';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Passwords do not match!';
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->bind_param("s", $hashed);

        if ($stmt->execute()) {
            $message = "Password successfully reset to: <strong>" . htmlspecialchars($newPassword) . "</strong><br>Please delete this file (reset_password.php) for security!";
            $success = true;
        } else {
            $message = 'Error updating password: ' . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Password Reset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0a0a0a, #1a1a1a, #2d2d2d); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-card { background: rgba(26,26,26,0.95); border: 2px solid #D4AF37; border-radius: 20px; padding: 40px; max-width: 450px; width: 100%; }
        .reset-card h3 { color: #D4AF37; text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="reset-card">
        <h3><i class="fas fa-key me-2"></i>Emergency Password Reset</h3>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label" style="color: #F4E4BC;">New Password</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label" style="color: #F4E4BC;">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-gold w-100"><i class="fas fa-redo me-2"></i>Reset Password</button>
        </form>

        <div class="text-center mt-3">
            <a href="index.php" style="color: #D4AF37;">Back to Login</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
