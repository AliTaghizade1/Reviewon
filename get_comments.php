<?php
session_start();
require 'config/db.php';
// اضافه کردن هدرهای جلوگیری از کش
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$siteId = $_GET['siteId'] ?? null;
if ($siteId) {
    // اضافه کردن ORDER BY برای مرتب‌سازی جدیدترین‌ها
$stmt = $pdo->prepare("
    SELECT 
        c.*, 
        c.author_id, 
        c.parent_comment_id,
        COALESCE(NULLIF(TRIM(u.name), ''), u.email) AS author_name
    FROM comments c 
    JOIN users u ON c.author_id = u.id 
    WHERE c.site_id = ? 
    ORDER BY c.created_at DESC
    ");
    
    // اگر ستون name را تازه اضافه کرده‌اید ممکن است برای برخی رکوردها null باشد.
    // COALESCE باعث می‌شود در این حالت از email استفاده شود.
    $stmt->execute([$siteId]);
    $comments = $stmt->fetchAll();
    
    // ✅ تغییر کلیدی: تبدیل is_resolved به عدد
    foreach ($comments as &$comment) {
        $comment['is_resolved'] = (int)$comment['is_resolved'];
    }
    
    echo json_encode(['success' => true, 'data' => $comments]);
} else {
    echo json_encode(['success' => false, 'message' => 'No Site ID']);
}
?>