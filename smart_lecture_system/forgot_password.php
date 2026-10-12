<?php
require_once 'config.php';
if (isLoggedIn()) {
    header("Location: admin/dashboard.php");
    exit();
}
$message = '';
$messageType = 'warning';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email'] ?? '');
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $message = 'If this email exists, a password reset link has been sent. (Demo: Contact admin to reset password)';
    } else {
        $message = 'If this email exists, a password reset link has been sent.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-key"></i>
            <h2>Forgot Password</h2>
        </div>
        <?php if ($message): ?>
            <?php echo showAlert($message, $messageType); ?>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter your email">
            </div>
            <button type="submit" class="btn btn-gold w-100 mb-3"><i class="fas fa-paper-plane me-2"></i>Send Reset Link</button>
        </form>
        <div class="text-center">
            <a href="index.php" style="color: #D4AF37; text-decoration: none;"><i class="fas fa-arrow-left me-1"></i>Back to Login</a>
        </div>
    </div>
</body>
</html>
