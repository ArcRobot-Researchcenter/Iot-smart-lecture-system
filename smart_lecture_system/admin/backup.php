<?php
$pageTitle = 'Backup & Restore';
require_once 'includes/header.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$message = '';
$messageType = 'success';

if (isset($_GET['backup'])) {
    $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backupPath = '../database/backups/' . $backupFile;
    if (!is_dir('../database/backups/')) mkdir('../database/backups/', 0777, true);

    $command = "mysqldump -h " . DB_HOST . " -u " . DB_USER . " " . (DB_PASS ? "-p" . DB_PASS . " " : "") . DB_NAME . " > " . $backupPath;
    exec($command, $output, $returnCode);

    if ($returnCode === 0) {
        $stmt = $conn->prepare("INSERT INTO backup_logs (filename, file_size, backup_type) VALUES (?, ?, 'Manual')");
        $size = filesize($backupPath);
        $stmt->bind_param("ss", $backupFile, $size);
        $stmt->execute();
        $message = 'Database backup created successfully!';
    } else {
        $message = 'Backup failed. Please check server configuration.';
        $messageType = 'danger';
    }
}

if (isset($_GET['download']) && !empty($_GET['download'])) {
    $file = basename($_GET['download']);
    $path = '../database/backups/' . $file;
    if (file_exists($path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit();
    }
}

if (isset($_GET['delete_backup']) && !empty($_GET['delete_backup'])) {
    $file = basename($_GET['delete_backup']);
    $path = '../database/backups/' . $file;
    if (file_exists($path)) {
        unlink($path);
        $conn->query("DELETE FROM backup_logs WHERE filename = '$file'");
        $message = 'Backup deleted!';
    }
}

$backups = $conn->query("SELECT * FROM backup_logs ORDER BY created_at DESC");
?>

<?php if ($message): ?>
<?php echo showAlert($message, $messageType); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-database me-2"></i>Database Backup</h5>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-cloud-download-alt fa-4x mb-3" style="color: #D4AF37;"></i>
                <p>Create a full backup of your database including all tables, records, and settings.</p>
                <a href="?backup=1" class="btn btn-gold"><i class="fas fa-download me-2"></i>Create Backup</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="content-card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i>Backup History</h5>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Filename</th><th>Size</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php while ($backup = $backups->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($backup['filename']); ?></td>
                                <td><?php echo number_format($backup['file_size'] / 1024, 2) . ' KB'; ?></td>
                                <td><span class="badge-gold"><?php echo $backup['backup_type']; ?></span></td>
                                <td><?php echo date('M d, Y H:i', strtotime($backup['created_at'])); ?></td>
                                <td>
                                    <a href="?download=<?php echo urlencode($backup['filename']); ?>" class="btn-action btn-edit"><i class="fas fa-download"></i></a>
                                    <a href="?delete_backup=<?php echo urlencode($backup['filename']); ?>" class="btn-action btn-delete" onclick="return confirm('Delete this backup?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($backups->num_rows == 0): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No backups found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
