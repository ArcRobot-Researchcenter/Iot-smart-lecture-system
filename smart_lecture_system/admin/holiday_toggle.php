<?php
require_once __DIR__ . '/../config.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$holidayMode = isset($_POST['holiday_mode']) ? 'ON' : 'OFF';
$stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('holiday_mode', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
$stmt->bind_param("ss", $holidayMode, $holidayMode);
$stmt->execute();

header("Location: settings.php?status=updated");
exit();
?>
