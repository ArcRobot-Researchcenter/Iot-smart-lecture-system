<?php
$pageTitle = 'Dashboard';
require_once 'includes/header.php';
require_once 'api/sms_api.php';

// Get statistics
$totalLecturers = $conn->query("SELECT COUNT(*) as count FROM lecturers WHERE status = 'Active'")->fetch_assoc()['count'];
$totalCourses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
$totalTimetable = $conn->query("SELECT COUNT(*) as count FROM timetable WHERE status = 'Active'")->fetch_assoc()['count'];

$today = date('Y-m-d');
$dayName = date('l');
$lecturesToday = $conn->query("SELECT COUNT(*) as count FROM timetable t 
    JOIN lecturers l ON t.lecturer_id = l.id 
    WHERE t.day = '$dayName' AND t.status = 'Active' AND l.status = 'Active'")->fetch_assoc()['count'];

$smsToday = $conn->query("SELECT COUNT(*) as count FROM sms_logs WHERE DATE(sent_at) = '$today'")->fetch_assoc()['count'];
$activeDepts = $conn->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'];

// Upcoming classes (next 3)
$upcomingQuery = "SELECT t.*, c.course_code, c.course_title, l.full_name, l.phone_number 
    FROM timetable t 
    JOIN courses c ON t.course_id = c.id 
    JOIN lecturers l ON t.lecturer_id = l.id 
    WHERE t.day = '$dayName' AND t.start_time > CURTIME() AND t.status = 'Active' AND l.status = 'Active'
    ORDER BY t.start_time LIMIT 5";
$upcomingClasses = $conn->query($upcomingQuery);

// Recent SMS logs
$recentSMS = $conn->query("SELECT s.*, l.full_name FROM sms_logs s LEFT JOIN lecturers l ON s.lecturer_id = l.id ORDER BY s.sent_at DESC LIMIT 5");

// Check holiday mode
$holidayMode = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'holiday_mode'")->fetch_assoc()['setting_value'] ?? 'OFF';

// Daily lecture stats for chart
$dailyStats = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
foreach ($days as $day) {
    $count = $conn->query("SELECT COUNT(*) as count FROM timetable WHERE day = '$day' AND status = 'Active'")->fetch_assoc()['count'];
    $dailyStats[] = $count;
}

// Weekly SMS stats
$weeklySMS = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $count = $conn->query("SELECT COUNT(*) as count FROM sms_logs WHERE DATE(sent_at) = '$date'")->fetch_assoc()['count'];
    $weeklySMS[] = $count;
}
$weeklyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $weeklyLabels[] = date('D', strtotime("-$i days"));
}
?>

<!-- Holiday Mode Alert -->
<?php if ($holidayMode === 'ON'): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Holiday Mode is ON!</strong> Automatic SMS reminders are currently disabled.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row g-4 mb-4" id="dashboard-stats">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <h3 id="stat_lecturers"><?php echo $totalLecturers; ?></h3>
            <p>Total Lecturers</p>
            <div class="trend"><i class="fas fa-arrow-up"></i> Active</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon" style="background: rgba(23, 162, 184, 0.2); color: #17a2b8;"><i class="fas fa-book"></i></div>
            <h3 id="stat_courses"><?php echo $totalCourses; ?></h3>
            <p>Total Courses</p>
            <div class="trend" style="background: rgba(23, 162, 184, 0.15); color: #17a2b8;"><i class="fas fa-list"></i> All</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon" style="background: rgba(255, 193, 7, 0.2); color: #ffc107;"><i class="fas fa-calendar-check"></i></div>
            <h3 id="stat_timetable"><?php echo $totalTimetable; ?></h3>
            <p>Lecture Schedules</p>
            <div class="trend" style="background: rgba(255, 193, 7, 0.15); color: #ffc107;"><i class="fas fa-check"></i> Active</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon" style="background: rgba(220, 53, 69, 0.2); color: #dc3545;"><i class="fas fa-calendar-day"></i></div>
            <h3 id="stat_today"><?php echo $lecturesToday; ?></h3>
            <p>Lectures Today</p>
            <div class="trend" style="background: rgba(220, 53, 69, 0.15); color: #dc3545;"><i class="fas fa-clock"></i> <?php echo $dayName; ?></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon" style="background: rgba(40, 167, 69, 0.2); color: #28a745;"><i class="fas fa-sms"></i></div>
            <h3 id="stat_sms"><?php echo $smsToday; ?></h3>
            <p>SMS Sent Today</p>
            <div class="trend" style="background: rgba(40, 167, 69, 0.15); color: #28a745;"><i class="fas fa-paper-plane"></i> Sent</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="icon" style="background: rgba(108, 117, 125, 0.2); color: #6c757d;"><i class="fas fa-building"></i></div>
            <h3 id="stat_depts"><?php echo $activeDepts; ?></h3>
            <p>Active Departments</p>
            <div class="trend" style="background: rgba(108, 117, 125, 0.15); color: #6c757d;"><i class="fas fa-university"></i> All</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="chart-container">
            <h5><i class="fas fa-chart-bar me-2"></i>Daily Lecture Statistics</h5>
            <canvas id="dailyChart" height="200"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="chart-container">
            <h5><i class="fas fa-chart-line me-2"></i>Weekly SMS Statistics</h5>
            <canvas id="weeklyChart" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Upcoming Classes & Recent SMS -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-clock me-2"></i>Upcoming Classes Today</h5>
                <a href="timetable.php" class="btn btn-sm btn-outline-gold">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-container">
                    <table class="data-table mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Course</th>
                                <th>Lecturer</th>
                                <th>Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($upcomingClasses->num_rows > 0): ?>
                                <?php while ($class = $upcomingClasses->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('h:i A', strtotime($class['start_time'])) . ' - ' . date('h:i A', strtotime($class['end_time'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($class['course_code']); ?></strong><br><small><?php echo htmlspecialchars($class['course_title']); ?></small></td>
                                    <td><?php echo htmlspecialchars($class['full_name']); ?></td>
                                    <td><span class="badge-gold"><?php echo htmlspecialchars($class['venue']); ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No upcoming classes today</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-envelope me-2"></i>Recent SMS Activity</h5>
                <a href="sms_logs.php" class="btn btn-sm btn-outline-gold">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-container">
                    <table class="data-table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Lecturer</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentSMS->num_rows > 0): ?>
                                <?php while ($sms = $recentSMS->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, H:i', strtotime($sms['sent_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($sms['full_name'] ?? 'Broadcast'); ?></td>
                                    <td>
                                        <?php if ($sms['status'] == 'Sent'): ?>
                                            <span class="badge-status badge-active"><i class="fas fa-check me-1"></i>Sent</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-inactive"><i class="fas fa-times me-1"></i>Failed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No SMS activity today</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Daily Lecture Chart
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($days); ?>,
        datasets: [{
            label: 'Lectures',
            data: <?php echo json_encode($dailyStats); ?>,
            backgroundColor: 'rgba(212, 175, 55, 0.7)',
            borderColor: '#D4AF37',
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});

// Weekly SMS Chart
const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
new Chart(weeklyCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($weeklyLabels); ?>,
        datasets: [{
            label: 'SMS Sent',
            data: <?php echo json_encode($weeklySMS); ?>,
            borderColor: '#D4AF37',
            backgroundColor: 'rgba(212, 175, 55, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#D4AF37',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
