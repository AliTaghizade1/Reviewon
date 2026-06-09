<?php
session_start();
require 'config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $siteId = $_POST['siteId'];
    $emailToRemove = $_POST['email'];
    $currentUserId = $_SESSION['user_id'];

    // 1. بررسی مالکیت سایت
    $stmt = $pdo->prepare("SELECT owner_id, owner_id FROM sites WHERE id = ?");
    $stmt->execute([$siteId]);
    $site = $stmt->fetch();

    if (!$site || $site['owner_id'] !== $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'You are not the owner.']);
        exit;
    }

    // 2. امنیت: جلوگیری از حذف ادمین (اگر ادمین اشتباهاً در لیست دسترسی‌ها باشد)
    // البته در منطق ما ادمین در جدول site_access نیست، اما برای اطمینان:
    // اگر ایمیل درخواست شده با ایمیل ادمین برابر بود، جلوگیری می‌کنیم (اختیاری)
    
    // 3. حذف دسترسی
    try {
        $stmtDelete = $pdo->prepare("DELETE FROM site_access WHERE site_id = ? AND shared_with_email = ?");
        $stmtDelete->execute([$siteId, $emailToRemove]);
        
        if ($stmtDelete->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Access removed successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Record not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>