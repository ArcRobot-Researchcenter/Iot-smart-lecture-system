<?php
$pageTitle = 'Lecturer Management';
require_once 'includes/header.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$message = '';
$messageType = 'success';

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM lecturers WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'Lecturer deleted successfully!';
    } else {
        $message = 'Error deleting lecturer.';
        $messageType = 'danger';
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $lecturerId = sanitize($conn, $_POST['lecturer_id']);
    $title = sanitize($conn, $_POST['title']);
    $fullName = sanitize($conn, $_POST['full_name']);
    $phone = sanitize($conn, $_POST['phone_number']);
    $email = sanitize($conn, $_POST['email_address']);
    $departmentId = intval($_POST['department_id']);
    $position = sanitize($conn, $_POST['position']);
    $status = sanitize($conn, $_POST['status']);

    $photo = '';
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = '../assets/uploads/lecturers/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $photoName = time() . '_' . basename($_FILES['photo']['name']);
        $photoPath = $uploadDir . $photoName;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
            $photo = 'lecturers/' . $photoName;
        }
    }

    if (!empty($id)) {
        // Update
        if ($photo) {
            $stmt = $conn->prepare("UPDATE lecturers SET lecturer_id=?, title=?, full_name=?, phone_number=?, email_address=?, department_id=?, position=?, status=?, photo=? WHERE id=?");
            $stmt->bind_param("sssssssssi", $lecturerId, $title, $fullName, $phone, $email, $departmentId, $position, $status, $photo, $id);
        } else {
            $stmt = $conn->prepare("UPDATE lecturers SET lecturer_id=?, title=?, full_name=?, phone_number=?, email_address=?, department_id=?, position=?, status=? WHERE id=?");
            $stmt->bind_param("ssssssssi", $lecturerId, $title, $fullName, $phone, $email, $departmentId, $position, $status, $id);
        }
        $msg = 'updated';
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO lecturers (lecturer_id, title, full_name, phone_number, email_address, department_id, position, status, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $lecturerId, $title, $fullName, $phone, $email, $departmentId, $position, $status, $photo);
        $msg = 'added';
    }

    if ($stmt->execute()) {
        $message = "Lecturer {$msg} successfully!";
    } else {
        $message = "Error: " . $stmt->error;
        $messageType = 'danger';
    }
}

// Get all lecturers
$lecturers = $conn->query("SELECT l.*, d.department_name FROM lecturers l LEFT JOIN departments d ON l.department_id = d.id ORDER BY l.full_name");
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name");
?>

<?php if ($message): ?>
<?php echo showAlert($message, $messageType); ?>
<?php endif; ?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-chalkboard-teacher me-2"></i>Lecturer Management</h5>
        <div class="d-flex gap-2">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchLecturers" class="form-control" placeholder="Search lecturers..." onkeyup="searchTable('searchLecturers', 'lecturersTable')">
            </div>
            <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#lecturerModal" onclick="resetForm()">
                <i class="fas fa-plus me-1"></i> Add Lecturer
            </button>
            <button class="btn btn-outline-gold no-print" onclick="printTable('lecturersTable', 'Lecturer List')">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="data-table" id="lecturersTable">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th class="no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($lecturer = $lecturers->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="../assets/uploads/<?php echo $lecturer['photo'] ?: 'default-user.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($lecturer['full_name']); ?>" 
                                 style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #D4AF37;"
                                 onerror="this.src='../assets/images/default-user.png'">
                        </td>
                        <td><strong><?php echo htmlspecialchars($lecturer['lecturer_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($lecturer['title'] . ' ' . $lecturer['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($lecturer['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($lecturer['email_address']); ?></td>
                        <td><?php echo htmlspecialchars($lecturer['department_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($lecturer['position']); ?></td>
                        <td>
                            <span class="badge-status <?php echo $lecturer['status'] == 'Active' ? 'badge-active' : ($lecturer['status'] == 'On Leave' ? 'badge-pending' : 'badge-inactive'); ?>">
                                <?php echo $lecturer['status']; ?>
                            </span>
                        </td>
                        <td class="no-print">
                            <button class="btn-action btn-edit" onclick="editLecturer(<?php echo htmlspecialchars(json_encode($lecturer)); ?>)" data-bs-toggle="modal" data-bs-target="#lecturerModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?php echo $lecturer['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this lecturer?')">
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

<!-- Add/Edit Modal -->
<div class="modal fade" id="lecturerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-user-plus me-2"></i>Add Lecturer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="lecturerDbId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lecturer ID</label>
                                <input type="text" name="lecturer_id" id="lecturerId" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title</label>
                                <select name="title" id="title" class="form-select" required>
                                    <option value="">Select Title</option>
                                    <option value="Prof.">Prof.</option>
                                    <option value="Dr.">Dr.</option>
                                    <option value="Mr.">Mr.</option>
                                    <option value="Mrs.">Mrs.</option>
                                    <option value="Ms.">Ms.</option>
                                    <option value="Engr.">Engr.</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" id="fullName" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone_number" id="phoneNumber" class="form-control" required placeholder="08012345678">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email_address" id="emailAddress" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department_id" id="departmentId" class="form-select" required>
                                    <option value="">Select Department</option>
                                    <?php while ($dept = $departments->fetch_assoc()): ?>
                                    <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position</label>
                                <input type="text" name="position" id="position" class="form-control" placeholder="e.g. Senior Lecturer">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="On Leave">On Leave</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Photo</label>
                                <input type="file" name="photo" id="photo" class="form-control" accept="image/*" onchange="previewImage(this, 'photoPreview')">
                                <img id="photoPreview" style="width: 80px; height: 80px; border-radius: 10px; margin-top: 10px; display: none; object-fit: cover; border: 2px solid #D4AF37;">
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
function resetForm() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus me-2"></i>Add Lecturer';
    document.getElementById('lecturerDbId').value = '';
    document.getElementById('lecturerId').value = '';
    document.getElementById('title').value = '';
    document.getElementById('fullName').value = '';
    document.getElementById('phoneNumber').value = '';
    document.getElementById('emailAddress').value = '';
    document.getElementById('departmentId').value = '';
    document.getElementById('position').value = '';
    document.getElementById('status').value = 'Active';
    document.getElementById('photoPreview').style.display = 'none';
}

function editLecturer(data) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit me-2"></i>Edit Lecturer';
    document.getElementById('lecturerDbId').value = data.id;
    document.getElementById('lecturerId').value = data.lecturer_id;
    document.getElementById('title').value = data.title;
    document.getElementById('fullName').value = data.full_name;
    document.getElementById('phoneNumber').value = data.phone_number;
    document.getElementById('emailAddress').value = data.email_address;
    document.getElementById('departmentId').value = data.department_id;
    document.getElementById('position').value = data.position;
    document.getElementById('status').value = data.status;

    if (data.photo) {
        document.getElementById('photoPreview').src = '../assets/uploads/' + data.photo;
        document.getElementById('photoPreview').style.display = 'block';
    } else {
        document.getElementById('photoPreview').style.display = 'none';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
