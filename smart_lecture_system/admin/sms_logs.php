<?php
$pageTitle = 'SMS Logs';
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$message = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM sms_logs WHERE id = $id");
    $message = 'Log deleted!';
}

$logs = $conn->query("SELECT s.*, l.full_name FROM sms_logs s LEFT JOIN lecturers l ON s.lecturer_id = l.id ORDER BY s.sent_at DESC");
?>

<?php if ($message): ?>
<?php echo showAlert($message, 'success'); ?>
<?php endif; ?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-envelope me-2"></i>SMS Logs</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-gold" onclick="exportToCSV('smsTable', 'sms_logs')"><i class="fas fa-file-csv"></i> CSV</button>
            <button class="btn btn-outline-gold" onclick="exportToExcel('smsTable', 'sms_logs')"><i class="fas fa-file-excel"></i> Excel</button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="data-table" id="smsTable">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Lecturer</th>
                        <th>Phone</th>
                        <th>Course</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y H:i', strtotime($log['sent_at'])); ?></td>
                        <td><?php echo htmlspecialchars($log['full_name'] ?? 'Broadcast'); ?></td>
                        <td><?php echo htmlspecialchars($log['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($log['course_id'] ?? 'N/A'); ?></td>
                        <td><small><?php echo htmlspecialchars(substr($log['message'], 0, 50)) . '...'; ?></small></td>
                        <td>
                            <span class="badge-status <?php echo $log['status'] == 'Sent' ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo $log['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="?delete=<?php echo $log['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this log?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
