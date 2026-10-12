<?php
$pageTitle = 'Timetable Management';
require_once 'includes/header.php';
require_once 'api/sms_api.php';
redirectIfNotLoggedIn();

$message = '';
$messageType = 'success';

if (isset($_GET['send_sms']) && is_numeric($_GET['send_sms'])) {
    $timetableId = intval($_GET['send_sms']);
    $stmt = $conn->prepare("SELECT t.*, c.course_code, c.course_title, l.id as lecturer_id, l.full_name, l.phone_number FROM timetable t JOIN courses c ON t.course_id = c.id JOIN lecturers l ON t.lecturer_id = l.id WHERE t.id = ?");
    $stmt->bind_param("i", $timetableId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $time = date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time']));
        $result = sendLectureReminder($conn, $timetableId, $row['lecturer_id'], $row['phone_number'], $row['full_name'], $row['course_code'], $row['course_title'], $row['venue'], $time, 'Manual');
        if ($result['success']) {
            $message = 'SMS reminder sent successfully!';
        } else {
            $message = 'Failed to send SMS: ' . ($result['message'] ?? 'Unknown error');
            $messageType = 'danger';
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM timetable WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'Timetable entry deleted!';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $courseId = intval($_POST['course_id']);
    $lecturerId = intval($_POST['lecturer_id']);
    $venue = sanitize($conn, $_POST['venue']);
    $day = sanitize($conn, $_POST['day']);
    $startTime = sanitize($conn, $_POST['start_time']);
    $endTime = sanitize($conn, $_POST['end_time']);
    $session = sanitize($conn, $_POST['session']);
    $semester = sanitize($conn, $_POST['semester']);
    $academicYear = sanitize($conn, $_POST['academic_year']);

    if (!empty($id)) {
        $stmt = $conn->prepare("UPDATE timetable SET course_id=?, lecturer_id=?, venue=?, day=?, start_time=?, end_time=?, session=?, semester=?, academic_year=? WHERE id=?");
        $stmt->bind_param("iisssssssi", $courseId, $lecturerId, $venue, $day, $startTime, $endTime, $session, $semester, $academicYear, $id);
        $msg = 'updated';
    } else {
        $stmt = $conn->prepare("INSERT INTO timetable (course_id, lecturer_id, venue, day, start_time, end_time, session, semester, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssssss", $courseId, $lecturerId, $venue, $day, $startTime, $endTime, $session, $semester, $academicYear);
        $msg = 'added';
    }

    if ($stmt->execute()) {
        $message = "Timetable entry {$msg} successfully!";
    } else {
        $message = "Error: " . $stmt->error;
        $messageType = 'danger';
    }
}

$filterDay = $_GET['filter_day'] ?? '';
$filterLecturer = $_GET['filter_lecturer'] ?? '';
$filterSemester = $_GET['filter_semester'] ?? '';

$query = "SELECT t.*, c.course_code, c.course_title, l.full_name, l.phone_number FROM timetable t JOIN courses c ON t.course_id = c.id JOIN lecturers l ON t.lecturer_id = l.id WHERE 1=1";
if ($filterDay) $query .= " AND t.day = '$filterDay'";
if ($filterLecturer) $query .= " AND t.lecturer_id = " . intval($filterLecturer);
if ($filterSemester) $query .= " AND t.semester = '$filterSemester'";
$query .= " ORDER BY FIELD(t.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), t.start_time";

$timetable = $conn->query($query);
$courses = $conn->query("SELECT * FROM courses ORDER BY course_code");
$lecturers = $conn->query("SELECT * FROM lecturers WHERE status = 'Active' ORDER BY full_name");
?>

<?php if ($message): ?>
<?php echo showAlert($message, $messageType); ?>
<?php endif; ?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-calendar-alt me-2"></i>Timetable Management</h5>
        <div class="d-flex gap-2 flex-wrap">
            <select class="form-select" style="width: 150px;" onchange="location.href='?filter_day='+this.value">
                <option value="">All Days</option>
                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
                <option value="<?php echo $d; ?>" <?php echo $filterDay == $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#timetableModal" onclick="resetTimetableForm()">
                <i class="fas fa-plus me-1"></i> Add
            </button>
            <button class="btn btn-outline-gold no-print" onclick="printTable('timetableTable', 'Lecture Timetable')">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="data-table" id="timetableTable">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Course</th>
                        <th>Lecturer</th>
                        <th>Venue</th>
                        <th>Semester</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $timetable->fetch_assoc()): ?>
                    <tr>
                        <td><span class="badge-gold"><?php echo $row['day']; ?></span></td>
                        <td><?php echo date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong><br><small><?php echo htmlspecialchars($row['course_title']); ?></small></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?><br><small><?php echo htmlspecialchars($row['phone_number']); ?></small></td>
                        <td><?php echo htmlspecialchars($row['venue']); ?></td>
                        <td><?php echo $row['semester']; ?></td>
                        <td class="no-print">
                            <a href="?send_sms=<?php echo $row['id']; ?>" class="btn-action btn-sms" onclick="return confirm('Send SMS reminder to <?php echo htmlspecialchars($row['full_name']); ?>?')" title="Send SMS">
                                <i class="fas fa-sms"></i>
                            </a>
                            <button class="btn-action btn-edit" onclick="editTimetable(<?php echo htmlspecialchars(json_encode($row)); ?>)" data-bs-toggle="modal" data-bs-target="#timetableModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this entry?')">
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

<div class="modal fade" id="timetableModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timetableModalTitle"><i class="fas fa-plus me-2"></i>Add Timetable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="timetableId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Course</label>
                                <select name="course_id" id="timetableCourse" class="form-select" required>
                                    <option value="">Select Course</option>
                                    <?php $courses->data_seek(0); while ($c = $courses->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_title']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lecturer</label>
                                <select name="lecturer_id" id="timetableLecturer" class="form-select" required>
                                    <option value="">Select Lecturer</option>
                                    <?php $lecturers->data_seek(0); while ($l = $lecturers->fetch_assoc()): ?>
                                    <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['title'] . ' ' . $l['full_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Venue</label>
                                <input type="text" name="venue" id="timetableVenue" class="form-control" required placeholder="e.g. LT 101">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Day</label>
                                <select name="day" id="timetableDay" class="form-select" required>
                                    <option value="">Select Day</option>
                                    <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
                                    <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" name="start_time" id="timetableStart" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" name="end_time" id="timetableEnd" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Session</label>
                                <input type="text" name="session" id="timetableSession" class="form-control" placeholder="2025/2026">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester" id="timetableSemester" class="form-select">
                                    <option value="First">First</option>
                                    <option value="Second">Second</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Academic Year</label>
                                <input type="text" name="academic_year" id="timetableYear" class="form-control" placeholder="2025/2026">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetTimetableForm() {
    document.getElementById('timetableModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Add Timetable';
    document.getElementById('timetableId').value = '';
    document.getElementById('timetableCourse').value = '';
    document.getElementById('timetableLecturer').value = '';
    document.getElementById('timetableVenue').value = '';
    document.getElementById('timetableDay').value = '';
    document.getElementById('timetableStart').value = '';
    document.getElementById('timetableEnd').value = '';
    document.getElementById('timetableSession').value = '';
    document.getElementById('timetableSemester').value = 'First';
    document.getElementById('timetableYear').value = '';
}

function editTimetable(data) {
    document.getElementById('timetableModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Timetable';
    document.getElementById('timetableId').value = data.id;
    document.getElementById('timetableCourse').value = data.course_id;
    document.getElementById('timetableLecturer').value = data.lecturer_id;
    document.getElementById('timetableVenue').value = data.venue;
    document.getElementById('timetableDay').value = data.day;
    document.getElementById('timetableStart').value = data.start_time;
    document.getElementById('timetableEnd').value = data.end_time;
    document.getElementById('timetableSession').value = data.session;
    document.getElementById('timetableSemester').value = data.semester;
    document.getElementById('timetableYear').value = data.academic_year;
}
</script>

<?php require_once 'includes/footer.php'; ?>
