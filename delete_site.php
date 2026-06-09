<?php
session_start();
require 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteId = $_POST['siteId'] ?? null;
    $userId = $_SESSION['user_id'];

    if ($siteId) {
        // ۱. بررسی مالکیت سایت
        $stmt = $pdo->prepare("SELECT owner_id FROM sites WHERE id = ?");
        $stmt->execute([$siteId]);
        $site = $stmt->fetch();

        if ($site && $site['owner_id'] == $userId) {
            // ۲. حذف کامنت‌های مربوط به سایت (برای حفظ یکپارچگی دیتابیس)
            $stmtComments = $pdo->prepare("DELETE FROM comments WHERE site_id = ?");
            $stmtComments->execute([$siteId]);

            // ۳. حذف دسترسی‌های اشتراکی
            $stmtAccess = $pdo->prepare("DELETE FROM site_access WHERE site_id = ?");
            $stmtAccess->execute([$siteId]);

            // ۴. حذف خود سایت
            $stmtDelete = $pdo->prepare("DELETE FROM sites WHERE id = ?");
            $stmtDelete->execute([$siteId]);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this site.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Site ID is missing.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>