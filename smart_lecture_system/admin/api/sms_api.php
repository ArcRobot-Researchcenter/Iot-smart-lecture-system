<?php
/**
 * eBulkSMS API Integration Class
 * Smart Lecture Timetable System
 */

class EBulkSMS {
    private $username;
    private $apiKey;
    private $jsonUrl = 'https://api.ebulksms.com/sendsms.json';
    private $senderName;

    public function __construct($username, $apiKey, $senderName = 'SmartLecture') {
        $this->username = $username;
        $this->apiKey = $apiKey;
        $this->senderName = substr($senderName, 0, 11);
    }

    /**
     * Send SMS using JSON API
     */
    public function sendSMS($recipients, $message, $flash = 0, $dndSender = 0) {
        $gsm = array();
        $countryCode = '234';

        // Handle single or multiple recipients
        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }

        foreach ($recipients as $recipient) {
            $mobileNumber = trim($recipient);

            // Format phone number
            if (substr($mobileNumber, 0, 1) == '0') {
                $mobileNumber = $countryCode . substr($mobileNumber, 1);
            } elseif (substr($mobileNumber, 0, 1) == '+') {
                $mobileNumber = substr($mobileNumber, 1);
            }

            $generatedId = uniqid('int_', false);
            $generatedId = substr($generatedId, 0, 30);

            $gsm['gsm'][] = array(
                'msidn' => $mobileNumber,
                'msgid' => $generatedId
            );
        }

        $messageData = array(
            'sender' => $this->senderName,
            'messagetext' => $message,
            'flash' => "{$flash}"
        );

        $request = array(
            'SMS' => array(
                'auth' => array(
                    'username' => $this->username,
                    'apikey' => $this->apiKey
                ),
                'message' => $messageData,
                'recipients' => $gsm,
                'dndsender' => $dndSender
            )
        );

        $jsonData = json_encode($request);

        if ($jsonData) {
            $response = $this->doPostRequest($this->jsonUrl, $jsonData, array('Content-Type: application/json'));
            $result = json_decode($response, true);

            if (isset($result['response']['status'])) {
                return array(
                    'success' => stripos($result['response']['status'], 'SUCCESS') !== false,
                    'status' => $result['response']['status'],
                    'response' => $result
                );
            }

            return array(
                'success' => false,
                'status' => 'Unknown',
                'response' => $result
            );
        }

        return array(
            'success' => false,
            'status' => 'JSON Encode Failed',
            'response' => null
        );
    }

    /**
     * Send single SMS to lecturer
     */
    public function sendLecturerReminder($lecturerPhone, $lecturerName, $courseCode, $courseTitle, $venue, $time) {
        $message = "Dear {$lecturerName},

";
        $message .= "This is a reminder that you have a scheduled lecture.

";
        $message .= "Course: {$courseCode}
";
        $message .= "Title: {$courseTitle}
";
        $message .= "Venue: {$venue}
";
        $message .= "Time: {$time}

";
        $message .= "Regards,
Department Timetable System";

        return $this->sendSMS($lecturerPhone, $message);
    }

    /**
     * Send broadcast SMS
     */
    public function sendBroadcast($recipients, $message) {
        return $this->sendSMS($recipients, $message);
    }

    /**
     * HTTP POST Request
     */
    private function doPostRequest($url, $data, $headers = array('Content-Type: application/x-www-form-urlencoded')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        try {
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode != 200) {
                return json_encode(array(
                    'response' => array(
                        'status' => 'HTTP Error: ' . $httpCode
                    )
                ));
            }

            return $response;
        } catch (Exception $e) {
            curl_close($ch);
            return json_encode(array(
                'response' => array(
                    'status' => 'cURL Error: ' . $e->getMessage()
                )
            ));
        }
    }
}

/**
 * SMS Helper Functions
 */

function sendLectureReminder($conn, $timetableId, $lecturerId, $lecturerPhone, $lecturerName, $courseCode, $courseTitle, $venue, $time, $type = 'Manual') {
    global $conn;

    // Get SMS credentials from settings
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'ebulksms_username'");
    $stmt->execute();
    $result = $stmt->get_result();
    $username = $result->fetch_assoc()['setting_value'] ?? '';

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'ebulksms_apikey'");
    $stmt->execute();
    $result = $stmt->get_result();
    $apiKey = $result->fetch_assoc()['setting_value'] ?? '';

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'sms_sender_name'");
    $stmt->execute();
    $result = $stmt->get_result();
    $senderName = $result->fetch_assoc()['setting_value'] ?? 'SmartLecture';

    if (empty($username) || empty($apiKey)) {
        return array('success' => false, 'message' => 'SMS credentials not configured');
    }

    $sms = new EBulkSMS($username, $apiKey, $senderName);
    $result = $sms->sendLecturerReminder($lecturerPhone, $lecturerName, $courseCode, $courseTitle, $venue, $time);

    // Log SMS
    $status = $result['success'] ? 'Sent' : 'Failed';
    $response = json_encode($result['response']);

    $stmt = $conn->prepare("INSERT INTO sms_logs (lecturer_id, phone_number, course_id, message, status, response) VALUES (?, ?, ?, ?, ?, ?)");
    $message = "Reminder: {$courseCode} - {$courseTitle} at {$venue}, {$time}";
    $stmt->bind_param("isssss", $lecturerId, $lecturerPhone, $courseCode, $message, $status, $response);
    $stmt->execute();

    // Record sent reminder to prevent duplicates
    if ($result['success']) {
        $today = date('Y-m-d');
        $stmt = $conn->prepare("INSERT IGNORE INTO sent_reminders (timetable_id, reminder_date, reminder_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $timetableId, $today, $type);
        $stmt->execute();
    }

    return $result;
}

function checkAndSendAutoReminders($conn) {
    // Check if holiday mode is on
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'holiday_mode'");
    $stmt->execute();
    $result = $stmt->get_result();
    $holidayMode = $result->fetch_assoc()['setting_value'] ?? 'OFF';

    if ($holidayMode === 'ON') {
        return array('success' => false, 'message' => 'Holiday mode is active');
    }

    // Get reminder minutes setting
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'sms_reminder_minutes'");
    $stmt->execute();
    $result = $stmt->get_result();
    $reminderMinutes = intval($result->fetch_assoc()['setting_value'] ?? 30);

    $now = date('H:i:s');
    $today = date('Y-m-d');
    $dayName = date('l');

    // Calculate target time
    $targetTime = date('H:i:s', strtotime("+{$reminderMinutes} minutes"));

    // Find lectures that need reminders
    $query = "SELECT t.*, c.course_code, c.course_title, l.id as lecturer_id, l.full_name, l.phone_number 
              FROM timetable t 
              JOIN courses c ON t.course_id = c.id 
              JOIN lecturers l ON t.lecturer_id = l.id 
              WHERE t.day = ? AND t.start_time BETWEEN ? AND ? 
              AND t.status = 'Active' AND l.status = 'Active'";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $dayName, $now, $targetTime);
    $stmt->execute();
    $result = $stmt->get_result();

    $sent = 0;
    $failed = 0;

    while ($row = $result->fetch_assoc()) {
        // Check if reminder already sent today
        $checkStmt = $conn->prepare("SELECT id FROM sent_reminders WHERE timetable_id = ? AND reminder_date = ? AND reminder_type = 'Auto'");
        $checkStmt->bind_param("is", $row['id'], $today);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows == 0) {
            $time = date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time']));
            $result = sendLectureReminder(
                $conn, 
                $row['id'], 
                $row['lecturer_id'], 
                $row['phone_number'], 
                $row['full_name'], 
                $row['course_code'], 
                $row['course_title'], 
                $row['venue'], 
                $time, 
                'Auto'
            );

            if ($result['success']) {
                $sent++;
            } else {
                $failed++;
            }
        }
    }

    return array('success' => true, 'sent' => $sent, 'failed' => $failed);
}

function sendBroadcastSMS($conn, $recipients, $message) {
    // Get SMS credentials
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'ebulksms_username'");
    $stmt->execute();
    $result = $stmt->get_result();
    $username = $result->fetch_assoc()['setting_value'] ?? '';

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'ebulksms_apikey'");
    $stmt->execute();
    $result = $stmt->get_result();
    $apiKey = $result->fetch_assoc()['setting_value'] ?? '';

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'sms_sender_name'");
    $stmt->execute();
    $result = $stmt->get_result();
    $senderName = $result->fetch_assoc()['setting_value'] ?? 'SmartLecture';

    if (empty($username) || empty($apiKey)) {
        return array('success' => false, 'message' => 'SMS credentials not configured');
    }

    $sms = new EBulkSMS($username, $apiKey, $senderName);
    $result = $sms->sendBroadcast($recipients, $message);

    // Log each SMS
    if (is_array($recipients)) {
        foreach ($recipients as $phone) {
            $status = $result['success'] ? 'Sent' : 'Failed';
            $response = json_encode($result['response']);

            $stmt = $conn->prepare("INSERT INTO sms_logs (phone_number, message, status, response) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $phone, $message, $status, $response);
            $stmt->execute();
        }
    }

    return $result;
}
?>
