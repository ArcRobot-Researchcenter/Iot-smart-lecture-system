<?php
/**
 * Auto SMS Reminder Cron Script
 * Run this every minute via cron job
 */

require_once '../../config.php';
require_once 'sms_api.php';

$holidayMode = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'holiday_mode'")->fetch_assoc()['setting_value'] ?? 'OFF';

if ($holidayMode === 'ON') {
    echo date('Y-m-d H:i:s') . " - Holiday mode is ON. No reminders sent.
";
    exit(0);
}

$result = checkAndSendAutoReminders($conn);

if ($result['success']) {
    echo date('Y-m-d H:i:s') . " - Sent: {$result['sent']}, Failed: {$result['failed']}
";
    if ($result['sent'] > 0) {
        $msg = "Auto reminders sent: {$result['sent']} successful, {$result['failed']} failed";
        $stmt = $conn->prepare("INSERT INTO notifications (type, title, message) VALUES ('System', 'Auto Reminders', ?)");
        $stmt->bind_param("s", $msg);
        $stmt->execute();
    }
} else {
    echo date('Y-m-d H:i:s') . " - Error: {$result['message']}
";
}
?>
