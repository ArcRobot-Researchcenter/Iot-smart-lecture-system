<?php
$pageTitle = 'My Profile';
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!password_verify($current, $result['password'])) {
            $message = 'Current password is incorrect!';
            $messageType = 'danger';
        } elseif ($new !== $confirm) {
            $message = 'New passwords do not match!';
            $messageType = 'danger';
        } elseif (strlen($new) < 6) {
            $message = 'Password must be at least 6 characters!';
            $messageType = 'danger';
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $_SESSION['user_id']);
            $stmt->execute();
            $message = 'Password changed successfully!';
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<?php if ($message): ?>
<?php echo showAlert($message, $messageType); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="content-card text-center">
            <div class="card-body">
                <img src="../assets/images/<?php echo $user['photo'] ?? 'default-user.png'; ?>" 
                     alt="Profile" class="rounded-circle mb-3" 
                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #D4AF37;">
                <h4 style="color: #D4AF37;"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted"><?php echo $user['role']; ?></p>
                <p><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
                <p><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
                <p><i class="fas fa-clock me-2"></i>Last Login: <?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-lock me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="change_password" value="1">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-gold"><i class="fas fa-key me-2"></i>Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
