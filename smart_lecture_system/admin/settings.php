<?php
$pageTitle = 'System Settings';
require_once 'includes/header.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'institution_name', 'faculty_name', 'department_name', 'system_name',
        'system_subtitle', 'footer_text', 'developer_name', 'project_version',
        'ebulksms_username', 'ebulksms_apikey', 'sms_sender_name',
        'sms_reminder_minutes', 'holiday_mode', 'auto_backup'
    ];

    foreach ($settings as $key) {
        $value = sanitize($conn, $_POST[$key] ?? '');
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
    }

    if (!empty($_FILES['school_logo']['name'])) {
        $uploadDir = '../assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $logoName = 'school_logo_' . time() . '_' . basename($_FILES['school_logo']['name']);
        if (move_uploaded_file($_FILES['school_logo']['tmp_name'], $uploadDir . $logoName)) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('school_logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("ss", $logoName, $logoName);
            $stmt->execute();
        }
    }

    $message = 'Settings updated successfully!';
}

$settings = [];
$result = $conn->query("SELECT * FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<?php if ($message): ?>
<?php echo showAlert($message, $messageType); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-cog me-2"></i>System Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Institution Name</label>
                                <input type="text" name="institution_name" class="form-control" value="<?php echo htmlspecialchars($settings['institution_name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Faculty Name</label>
                                <input type="text" name="faculty_name" class="form-control" value="<?php echo htmlspecialchars($settings['faculty_name'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department Name</label>
                                <input type="text" name="department_name" class="form-control" value="<?php echo htmlspecialchars($settings['department_name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>System Name</label>
                                <input type="text" name="system_name" class="form-control" value="<?php echo htmlspecialchars($settings['system_name'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>System Subtitle</label>
                        <input type="text" name="system_subtitle" class="form-control" value="<?php echo htmlspecialchars($settings['system_subtitle'] ?? ''); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Footer Text</label>
                                <input type="text" name="footer_text" class="form-control" value="<?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Developer Name</label>
                                <input type="text" name="developer_name" class="form-control" value="<?php echo htmlspecialchars($settings['developer_name'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project Version</label>
                                <input type="text" name="project_version" class="form-control" value="<?php echo htmlspecialchars($settings['project_version'] ?? '1.0.0'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>School Logo</label>
                                <input type="file" name="school_logo" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" style="border-color: #D4AF37;">
                    <h6 class="mb-3" style="color: #B8860B;"><i class="fas fa-sms me-2"></i>SMS Configuration</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>eBulkSMS Username</label>
                                <input type="text" name="ebulksms_username" class="form-control" value="<?php echo htmlspecialchars($settings['ebulksms_username'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>eBulkSMS API Key</label>
                                <input type="password" name="ebulksms_apikey" class="form-control" value="<?php echo htmlspecialchars($settings['ebulksms_apikey'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sender Name</label>
                                <input type="text" name="sms_sender_name" class="form-control" value="<?php echo htmlspecialchars($settings['sms_sender_name'] ?? 'SmartLecture'); ?>" maxlength="11">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reminder Time (minutes before lecture)</label>
                                <select name="sms_reminder_minutes" class="form-select">
                                    <option value="5" <?php echo ($settings['sms_reminder_minutes'] ?? '30') == '5' ? 'selected' : ''; ?>>5 Minutes</option>
                                    <option value="10" <?php echo ($settings['sms_reminder_minutes'] ?? '30') == '10' ? 'selected' : ''; ?>>10 Minutes</option>
                                    <option value="15" <?php echo ($settings['sms_reminder_minutes'] ?? '30') == '15' ? 'selected' : ''; ?>>15 Minutes</option>
                                    <option value="30" <?php echo ($settings['sms_reminder_minutes'] ?? '30') == '30' ? 'selected' : ''; ?>>30 Minutes</option>
                                    <option value="60" <?php echo ($settings['sms_reminder_minutes'] ?? '30') == '60' ? 'selected' : ''; ?>>60 Minutes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Auto Backup</label>
                                <select name="auto_backup" class="form-select">
                                    <option value="OFF" <?php echo ($settings['auto_backup'] ?? 'OFF') == 'OFF' ? 'selected' : ''; ?>>OFF</option>
                                    <option value="ON" <?php echo ($settings['auto_backup'] ?? 'OFF') == 'ON' ? 'selected' : ''; ?>>ON</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold mt-3"><i class="fas fa-save me-2"></i>Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-toggle-on me-2"></i>Holiday Mode</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="holiday_toggle.php">
                    <div class="holiday-toggle">
                        <div>
                            <h6 class="mb-1" style="color: #D4AF37;">Holiday Mode</h6>
                            <p class="mb-0" style="color: #888; font-size: 13px;">Disable automatic reminders</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="holiday_mode" value="ON" 
                                <?php echo ($settings['holiday_mode'] ?? 'OFF') == 'ON' ? 'checked' : ''; ?>
                                onchange="this.form.submit()" style="width: 50px; height: 25px;">
                        </div>
                    </div>
                </form>
                <?php if (($settings['holiday_mode'] ?? 'OFF') == 'ON'): ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Automatic SMS reminders are currently disabled.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
