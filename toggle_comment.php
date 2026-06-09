<?php
session_start();
require 'config/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'debug' => []];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
        
        $commentId = $_POST['commentId'] ?? '';
        $isResolved = $_POST['isResolved'] ?? '0'; // مقدار دریافتی
        
        // تبدیل به عدد صحیح برای اطمینان
        $intValue = (int)$isResolved;

        $response['debug']['input_raw'] = $isResolved;
        $response['debug']['input_int'] = $intValue;

        if ($commentId) {
            // 1. بررسی وضعیت فعلی قبل از آپدیت
            $stmtCheck = $pdo->prepare("SELECT is_resolved FROM comments WHERE id = ?");
            $stmtCheck->execute([$commentId]);
            $currentStatus = $stmtCheck->fetchColumn();
            $response['debug']['status_before'] = $currentStatus;

            // 2. اجرای آپدیت
            $sql = "UPDATE comments SET is_resolved = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            // لاگ کردن کوئری برای بررسی
            $response['debug']['query'] = str_replace(['?', '?'], [$intValue, $commentId], $sql);
            
            $result = $stmt->execute([$intValue, $commentId]);

            if ($result) {
                // 3. بررسی وضعیت بعد از آپدیت
                $stmtCheck->execute([$commentId]); // اجرای مجدد برای گرفتن وضعیت جدید
                $newStatus = $stmtCheck->fetchColumn();
                $response['debug']['status_after'] = $newStatus;
                $response['debug']['rows_affected'] = $stmt->rowCount();

                if ($stmt->rowCount() > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Updated';
                } else {
                    // اگر rowCount صفر بود، یعنی مقدار جدید با مقدار قدیمی یکی بوده یا آپدیت نشده
                    $response['message'] = 'No changes made (Row count 0)';
                    $response['debug']['warning'] = 'Database did not update the row.';
                }
            } else {
                $response['message'] = 'Execute failed';
                $response['debug']['error_info'] = $stmt->errorInfo();
            }
        } else {
            $response['message'] = 'Missing ID';
        }
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>