<?php
session_start();
require 'config/db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $commentId = $_POST['commentId'] ?? null;
    if ($commentId) {
        // 1. حذف کامنت اصلی
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        
        // 2. حذف تمام ریپلای‌های مربوط به این کامنت
        $stmt = $pdo->prepare("DELETE FROM comments WHERE parent_comment_id = ?");
        $stmt->execute([$commentId]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing comment ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}
?>