<?php
// Get settings for footer
$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'footer_text'");
$stmt->execute();
$result = $stmt->get_result();
$footerText = $result->fetch_assoc()['setting_value'] ?? 'Smart Lecture System - All Rights Reserved';

$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'developer_name'");
$stmt->execute();
$result = $stmt->get_result();
$developerName = $result->fetch_assoc()['setting_value'] ?? 'Smart Systems';

$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'project_version'");
$stmt->execute();
$result = $stmt->get_result();
$version = $result->fetch_assoc()['setting_value'] ?? '1.0.0';
?>
        </div><!-- End content-wrapper -->

        <div class="footer">
            <p><?php echo htmlspecialchars($footerText); ?> | Developed by <?php echo htmlspecialchars($developerName); ?> | v<?php echo htmlspecialchars($version); ?></p>
        </div>
    </div><!-- End main-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
