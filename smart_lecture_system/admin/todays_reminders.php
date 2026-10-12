<?php
$pageTitle = "Today's Reminders";
require_once 'includes/header.php';
require_once 'api/sms_api.php';
redirectIfNotLoggedIn();

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_all'])) {
    $result = checkAndSendAutoReminders($conn);
    if ($result['success']) {
        $message = "Sent {$result['sent']} reminders. Failed: {$result['failed']}";
    } else {
        $message = $result['message'];
        $messageType = 'warning';
    }
}

$today = date('Y-m-d');
$dayName = date('l');

$classes = $conn->query("SELECT t.*, c.course_code, c.course_title, l.id as lecturer_id, l.full_name, l.phone_number FROM timetable t JOIN courses c ON t.course_id = c.id JOIN lecturers l ON t.lecturer_id = l.id WHERE t.day = '$dayName' AND t.status = 'Active' AND l.status = 'Active' ORDER BY t.start_time");

$totalClasses = $classes->num_rows;
$remindersSent = $conn->query("SELECT COUNT(*) as count FROM sent_reminders WHERE reminder_date = '$today' AND reminder_type = 'Manual'")->fetch_assoc()['count'];
?>

<?php if ($message): ?>
<?php echo showAlert($message, $messageType); ?>
<?php endif; ?>

<div class="content-card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-bell me-2"></i>Today's Reminders - <?php echo $dayName . ', ' . date('F d, Y'); ?></h5>
        <form method="POST" class="d-inline">
            <button type="submit" name="send_all" class="btn btn-gold" onclick="return confirm('Send reminders to all lecturers scheduled for today?')">
                <i class="fas fa-paper-plane me-2"></i>Send Today's Reminders
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-calendar-day"></i></div>
                    <h3><?php echo $totalClasses; ?></h3>
                    <p>Classes Today</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon" style="background: rgba(40, 167, 69, 0.2); color: #28a745;"><i class="fas fa-check-circle"></i></div>
                    <h3><?php echo $remindersSent; ?></h3>
                    <p>Reminders Sent</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon" style="background: rgba(255, 193, 7, 0.2); color: #ffc107;"><i class="fas fa-clock"></i></div>
                    <h3><?php echo $totalClasses - $remindersSent; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Course</th>
                        <th>Lecturer</th>
                        <th>Phone</th>
                        <th>Venue</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($class = $classes->fetch_assoc()):
                        $checkStmt = $conn->prepare("SELECT id FROM sent_reminders WHERE timetable_id = ? AND reminder_date = ?");
                        $checkStmt->bind_param("is", $class['id'], $today);
                        $checkStmt->execute();
                        $reminded = $checkStmt->get_result()->num_rows > 0;
                    ?>
                    <tr>
                        <td><?php echo date('h:i A', strtotime($class['start_time'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($class['course_code']); ?></strong><br><?php echo htmlspecialchars($class['course_title']); ?></td>
                        <td><?php echo htmlspecialchars($class['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($class['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($class['venue']); ?></td>
                        <td>
                            <?php if ($reminded): ?>
                                <span class="badge-status badge-active"><i class="fas fa-check me-1"></i>Sent</span>
                            <?php else: ?>
                                <span class="badge-status badge-pending"><i class="fas fa-clock me-1"></i>Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($totalClasses == 0): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No classes scheduled for today</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
