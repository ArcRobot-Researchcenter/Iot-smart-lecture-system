<?php
$pageTitle = 'Notifications';
require_once 'includes/header.php';
redirectIfNotLoggedIn();

if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $conn->query("UPDATE notifications SET status = 'Read' WHERE id = $id");
}

if (isset($_GET['mark_all_read'])) {
    $conn->query("UPDATE notifications SET status = 'Read' WHERE status = 'Unread'");
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM notifications WHERE id = $id");
}

$notifications = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");
?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-bell me-2"></i>Notification Center</h5>
        <a href="?mark_all_read=1" class="btn btn-sm btn-outline-gold">Mark All Read</a>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Title</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while ($notif = $notifications->fetch_assoc()): ?>
                    <tr class="<?php echo $notif['status'] == 'Unread' ? 'fw-bold' : ''; ?>" style="<?php echo $notif['status'] == 'Unread' ? 'background: rgba(212,175,55,0.05);' : ''; ?>">
                        <td><span class="badge-gold"><?php echo $notif['type']; ?></span></td>
                        <td><?php echo htmlspecialchars($notif['title']); ?></td>
                        <td><?php echo htmlspecialchars($notif['message']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></td>
                        <td>
                            <?php if ($notif['status'] == 'Unread'): ?>
                            <span class="badge-status badge-pending">Unread</span>
                            <?php else: ?>
                            <span class="badge-status badge-active">Read</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($notif['status'] == 'Unread'): ?>
                            <a href="?mark_read=<?php echo $notif['id']; ?>" class="btn-action btn-edit"><i class="fas fa-check"></i></a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $notif['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($notifications->num_rows == 0): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No notifications</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
