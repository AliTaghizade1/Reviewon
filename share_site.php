<?php
session_start();
require 'config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $siteId = $_POST['siteId'];
    $sharedWithEmail = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $currentUserId = $_SESSION['user_id'];

    // 1. بررسی اینکه آیا سایت متعلق به کاربر فعلی است؟
    $stmt = $pdo->prepare("SELECT owner_id FROM sites WHERE id = ?");
    $stmt->execute([$siteId]);
    $site = $stmt->fetch();

    if (!$site || $site['owner_id'] !== $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'You do not own this site.']);
        exit;
    }

    // 2. اعتبارسنجی ایمیل
    if (!filter_var($sharedWithEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }

    // 3. بررسی تکراری نبودن (این کاربر قبلاً دسترسی نداشته باشد)
    $stmtCheck = $pdo->prepare("SELECT * FROM site_access WHERE site_id = ? AND shared_with_email = ?");
    $stmtCheck->execute([$siteId, $sharedWithEmail]);
    
    if ($stmtCheck->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Access already granted to this email.']);
        exit;
    }

    // 4. ذخیره دسترسی
    try {
        $stmtInsert = $pdo->prepare("INSERT INTO site_access (site_id, shared_with_email) VALUES (?, ?)");
        $stmtInsert->execute([$siteId, $sharedWithEmail]);
        echo json_encode(['success' => true, 'message' => 'Access shared successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}
?>