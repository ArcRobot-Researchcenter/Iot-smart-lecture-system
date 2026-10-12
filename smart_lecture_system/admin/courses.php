<?php
$pageTitle = 'Course Management';
require_once 'includes/header.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$message = '';
$messageType = 'success';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'Course deleted successfully!';
    } else {
        $message = 'Error deleting course.';
        $messageType = 'danger';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $courseCode = sanitize($conn, $_POST['course_code']);
    $courseTitle = sanitize($conn, $_POST['course_title']);
    $creditUnit = intval($_POST['credit_unit']);
    $departmentId = intval($_POST['department_id']);
    $level = sanitize($conn, $_POST['level']);
    $semester = sanitize($conn, $_POST['semester']);

    if (!empty($id)) {
        $stmt = $conn->prepare("UPDATE courses SET course_code=?, course_title=?, credit_unit=?, department_id=?, level=?, semester=? WHERE id=?");
        $stmt->bind_param("ssisssi", $courseCode, $courseTitle, $creditUnit, $departmentId, $level, $semester, $id);
        $msg = 'updated';
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_title, credit_unit, department_id, level, semester) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $courseCode, $courseTitle, $creditUnit, $departmentId, $level, $semester);
        $msg = 'added';
    }

    if ($stmt->execute()) {
        $message = "Course {$msg} successfully!";
    } else {
        $message = "Error: " . $stmt->error;
        $messageType = 'danger';
    }
}

$courses = $conn->query("SELECT c.*, d.department_name FROM courses c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.course_code");
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name");
?>

<?php if ($message): ?>
<?php echo showAlert($message, $messageType); ?>
<?php endif; ?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-book me-2"></i>Course Management</h5>
        <div class="d-flex gap-2">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchCourses" class="form-control" placeholder="Search courses..." onkeyup="searchTable('searchCourses', 'coursesTable')">
            </div>
            <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#courseModal" onclick="resetCourseForm()">
                <i class="fas fa-plus me-1"></i> Add Course
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="data-table" id="coursesTable">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Title</th>
                        <th>Credit Unit</th>
                        <th>Department</th>
                        <th>Level</th>
                        <th>Semester</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                        <td><?php echo $course['credit_unit']; ?></td>
                        <td><?php echo htmlspecialchars($course['department_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($course['level']); ?></td>
                        <td><span class="badge-gold"><?php echo $course['semester']; ?></span></td>
                        <td class="no-print">
                            <button class="btn-action btn-edit" onclick="editCourse(<?php echo htmlspecialchars(json_encode($course)); ?>)" data-bs-toggle="modal" data-bs-target="#courseModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?php echo $course['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this course?')">
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

<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalTitle"><i class="fas fa-plus me-2"></i>Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="courseId">
                    <div class="form-group">
                        <label>Course Code</label>
                        <input type="text" name="course_code" id="courseCode" class="form-control" required placeholder="e.g. CPE 101">
                    </div>
                    <div class="form-group">
                        <label>Course Title</label>
                        <input type="text" name="course_title" id="courseTitle" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Credit Unit</label>
                                <input type="number" name="credit_unit" id="creditUnit" class="form-control" value="3" min="1" max="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" id="courseDeptId" class="form-select" required>
                                    <option value="">Select</option>
                                    <?php while ($dept = $departments->fetch_assoc()): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Level</label>
                                <select name="level" id="level" class="form-select">
                                    <option value="100">100 Level</option>
                                    <option value="200">200 Level</option>
                                    <option value="300">300 Level</option>
                                    <option value="400">400 Level</option>
                                    <option value="500">500 Level</option>
                                    <option value="PG">Postgraduate</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester" id="semester" class="form-select">
                                    <option value="First">First Semester</option>
                                    <option value="Second">Second Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
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
function resetCourseForm() {
    document.getElementById('courseModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Add Course';
    document.getElementById('courseId').value = '';
    document.getElementById('courseCode').value = '';
    document.getElementById('courseTitle').value = '';
    document.getElementById('creditUnit').value = '3';
    document.getElementById('courseDeptId').value = '';
    document.getElementById('level').value = '100';
    document.getElementById('semester').value = 'First';
}

function editCourse(data) {
    document.getElementById('courseModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Course';
    document.getElementById('courseId').value = data.id;
    document.getElementById('courseCode').value = data.course_code;
    document.getElementById('courseTitle').value = data.course_title;
    document.getElementById('creditUnit').value = data.credit_unit;
    document.getElementById('courseDeptId').value = data.department_id;
    document.getElementById('level').value = data.level;
    document.getElementById('semester').value = data.semester;
}
</script>

<?php require_once 'includes/footer.php'; ?>
