<?php
$pageTitle = 'Reports';
require_once 'includes/header.php';
redirectIfNotLoggedIn();

$reportType = $_GET['type'] ?? 'lecturers';

$lecturers = $conn->query("SELECT l.*, d.department_name FROM lecturers l LEFT JOIN departments d ON l.department_id = d.id ORDER BY l.full_name");
$courses = $conn->query("SELECT c.*, d.department_name FROM courses c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.course_code");
$timetable = $conn->query("SELECT t.*, c.course_code, c.course_title, l.full_name, l.phone_number FROM timetable t JOIN courses c ON t.course_id = c.id JOIN lecturers l ON t.lecturer_id = l.id ORDER BY t.day, t.start_time");
$smsLogs = $conn->query("SELECT s.*, l.full_name FROM sms_logs s LEFT JOIN lecturers l ON s.lecturer_id = l.id ORDER BY s.sent_at DESC");
?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-chart-bar me-2"></i>Reports</h5>
        <div class="d-flex gap-2">
            <a href="?type=lecturers" class="btn btn-sm <?php echo $reportType == 'lecturers' ? 'btn-gold' : 'btn-outline-gold'; ?>">Lecturers</a>
            <a href="?type=courses" class="btn btn-sm <?php echo $reportType == 'courses' ? 'btn-gold' : 'btn-outline-gold'; ?>">Courses</a>
            <a href="?type=timetable" class="btn btn-sm <?php echo $reportType == 'timetable' ? 'btn-gold' : 'btn-outline-gold'; ?>">Timetable</a>
            <a href="?type=sms" class="btn btn-sm <?php echo $reportType == 'sms' ? 'btn-gold' : 'btn-outline-gold'; ?>">SMS</a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($reportType == 'lecturers'): ?>
        <div class="d-flex justify-content-between mb-3">
            <h6>Lecturer Report</h6>
            <button class="btn btn-outline-gold btn-sm no-print" onclick="printTable('reportTable', 'Lecturer Report')"><i class="fas fa-print"></i> Print</button>
        </div>
        <table class="data-table" id="reportTable">
            <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Department</th><th>Position</th><th>Status</th></tr></thead>
            <tbody>
                <?php while ($row = $lecturers->fetch_assoc()): ?>
                <tr><td><?php echo htmlspecialchars($row['lecturer_id']); ?></td><td><?php echo htmlspecialchars($row['title'] . ' ' . $row['full_name']); ?></td><td><?php echo htmlspecialchars($row['phone_number']); ?></td><td><?php echo htmlspecialchars($row['email_address']); ?></td><td><?php echo htmlspecialchars($row['department_name'] ?? 'N/A'); ?></td><td><?php echo htmlspecialchars($row['position']); ?></td><td><?php echo $row['status']; ?></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php elseif ($reportType == 'courses'): ?>
        <div class="d-flex justify-content-between mb-3">
            <h6>Course Report</h6>
            <button class="btn btn-outline-gold btn-sm no-print" onclick="printTable('reportTable', 'Course Report')"><i class="fas fa-print"></i> Print</button>
        </div>
        <table class="data-table" id="reportTable">
            <thead><tr><th>Code</th><th>Title</th><th>Credit</th><th>Department</th><th>Level</th><th>Semester</th></tr></thead>
            <tbody>
                <?php while ($row = $courses->fetch_assoc()): ?>
                <tr><td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td><td><?php echo htmlspecialchars($row['course_title']); ?></td><td><?php echo $row['credit_unit']; ?></td><td><?php echo htmlspecialchars($row['department_name'] ?? 'N/A'); ?></td><td><?php echo htmlspecialchars($row['level']); ?></td><td><?php echo $row['semester']; ?></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php elseif ($reportType == 'timetable'): ?>
        <div class="d-flex justify-content-between mb-3">
            <h6>Timetable Report</h6>
            <button class="btn btn-outline-gold btn-sm no-print" onclick="printTable('reportTable', 'Timetable Report')"><i class="fas fa-print"></i> Print</button>
        </div>
        <table class="data-table" id="reportTable">
            <thead><tr><th>Day</th><th>Time</th><th>Course</th><th>Lecturer</th><th>Venue</th><th>Semester</th></tr></thead>
            <tbody>
                <?php while ($row = $timetable->fetch_assoc()): ?>
                <tr><td><?php echo $row['day']; ?></td><td><?php echo date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time'])); ?></td><td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong><br><?php echo htmlspecialchars($row['course_title']); ?></td><td><?php echo htmlspecialchars($row['full_name']); ?></td><td><?php echo htmlspecialchars($row['venue']); ?></td><td><?php echo $row['semester']; ?></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php elseif ($reportType == 'sms'): ?>
        <div class="d-flex justify-content-between mb-3">
            <h6>SMS Report</h6>
            <button class="btn btn-outline-gold btn-sm no-print" onclick="printTable('reportTable', 'SMS Report')"><i class="fas fa-print"></i> Print</button>
        </div>
        <table class="data-table" id="reportTable">
            <thead><tr><th>Date</th><th>Lecturer</th><th>Phone</th><th>Course</th><th>Status</th></tr></thead>
            <tbody>
                <?php while ($row = $smsLogs->fetch_assoc()): ?>
                <tr><td><?php echo date('M d, Y H:i', strtotime($row['sent_at'])); ?></td><td><?php echo htmlspecialchars($row['full_name'] ?? 'Broadcast'); ?></td><td><?php echo htmlspecialchars($row['phone_number']); ?></td><td><?php echo htmlspecialchars($row['course_id'] ?? 'N/A'); ?></td><td><span class="badge-status <?php echo $row['status'] == 'Sent' ? 'badge-active' : 'badge-inactive'; ?>"><?php echo $row['status']; ?></span></td></tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
