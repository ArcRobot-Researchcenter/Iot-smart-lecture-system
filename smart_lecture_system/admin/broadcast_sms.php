<?php
$pageTitle = 'Broadcast SMS';
require_once 'includes/header.php';
require_once 'api/sms_api.php';
redirectIfNotLoggedIn();

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smsMessage = $_POST['message'] ?? '';
    $recipientType = $_POST['recipient_type'] ?? '';
    $selectedLecturers = $_POST['lecturers'] ?? [];
    $selectedDept = $_POST['department'] ?? '';

    $recipients = [];
    if ($recipientType == 'all') {
        $result = $conn->query("SELECT phone_number FROM lecturers WHERE status = 'Active'");
        while ($row = $result->fetch_assoc()) { $recipients[] = $row['phone_number']; }
    } elseif ($recipientType == 'department' && $selectedDept) {
        $stmt = $conn->prepare("SELECT phone_number FROM lecturers WHERE department_id = ? AND status = 'Active'");
        $stmt->bind_param("i", $selectedDept);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $recipients[] = $row['phone_number']; }
    } elseif ($recipientType == 'selected' && !empty($selectedLecturers)) {
        $ids = implode(',', array_map('intval', $selectedLecturers));
        $result = $conn->query("SELECT phone_number FROM lecturers WHERE id IN ($ids)");
        while ($row = $result->fetch_assoc()) { $recipients[] = $row['phone_number']; }
    }

    if (!empty($recipients) && !empty($smsMessage)) {
        $result = sendBroadcastSMS($conn, $recipients, $smsMessage);
        if ($result['success']) {
            $message = 'Broadcast SMS sent to ' . count($recipients) . ' recipients!';
        } else {
            $message = 'Failed: ' . ($result['message'] ?? 'Unknown error');
            $messageType = 'danger';
        }
    } else {
        $message = 'Please select recipients and enter a message.';
        $messageType = 'danger';
    }
}

$lecturers = $conn->query("SELECT * FROM lecturers WHERE status = 'Active' ORDER BY full_name");
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name");
?>

<?php if ($message): ?>
<?php echo showAlert($message, $messageType); ?>
<?php endif; ?>

<div class="content-card">
    <div class="card-header">
        <h5><i class="fas fa-broadcast-tower me-2"></i>Broadcast SMS</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" class="form-control" rows="5" required maxlength="480" placeholder="Enter your broadcast message here..."></textarea>
                <small class="text-muted">Max 480 characters (3 SMS pages)</small>
            </div>
            <div class="form-group">
                <label>Recipients</label>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="recipient_type" id="allLecturers" value="all" checked onchange="toggleRecipients()">
                        <label class="form-check-label" for="allLecturers">All Lecturers</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="recipient_type" id="deptLecturers" value="department" onchange="toggleRecipients()">
                        <label class="form-check-label" for="deptLecturers">By Department</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="recipient_type" id="selectedLecturers" value="selected" onchange="toggleRecipients()">
                        <label class="form-check-label" for="selectedLecturers">Selected Lecturers</label>
                    </div>
                </div>
                <div id="deptSelect" style="display: none;">
                    <select name="department" class="form-select">
                        <option value="">Select Department</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div id="lecturerSelect" style="display: none;">
                    <div class="row">
                        <?php while ($lec = $lecturers->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="lecturers[]" value="<?php echo $lec['id']; ?>" id="lec<?php echo $lec['id']; ?>">
                                <label class="form-check-label" for="lec<?php echo $lec['id']; ?>"><?php echo htmlspecialchars($lec['full_name']); ?></label>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-paper-plane me-2"></i>Send Broadcast</button>
        </form>
    </div>
</div>

<script>
function toggleRecipients() {
    document.getElementById('deptSelect').style.display = document.getElementById('deptLecturers').checked ? 'block' : 'none';
    document.getElementById('lecturerSelect').style.display = document.getElementById('selectedLecturers').checked ? 'block' : 'none';
}
</script>

<?php require_once 'includes/footer.php'; ?>
