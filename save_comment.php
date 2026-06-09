<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require 'config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $siteId = $_POST['siteId'] ?? null;
    $url = $_POST['url'] ?? null;
    $selector = $_POST['selector'] ?? null;
    $deviceType = $_POST['deviceType'] ?? null;
    $content = $_POST['content'] ?? null;
    
    $parentCommentId = $_POST['parentCommentId'] ?? null;
    
    $offsetX = isset($_POST['offsetX']) ? $_POST['offsetX'] : 0;
    $offsetY = isset($_POST['offsetY']) ? $_POST['offsetY'] : 0;
    $authorId = $_SESSION['user_id'];
    
    // ✅ تغییر: اگر سلکتور خالی بود، مقدار پیش‌فرض 'body' را قرار بده
    if (empty($selector)) {
        $selector = 'body';
    }
    
    if ($siteId && $content) { // ✅ تغییر: شرط را فقط برای siteId و content چک کردیم (selector دیگر اجباری نیست)
        $commentId = uniqid();
        $stmt = $pdo->prepare("INSERT INTO comments (id, site_id, url, selector, device_type, content, offset_x, offset_y, author_id, parent_comment_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$commentId, $siteId, $url, $selector, $deviceType, $content, $offsetX, $offsetY, $authorId, $parentCommentId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->errorInfo()[2]]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing fields']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}
?>