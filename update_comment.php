<?php
session_start();

require 'config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $commentId = $_POST['commentId'] ?? '';
    $newContent = $_POST['content'] ?? '';

    if ($commentId && $newContent) {
        $stmt = $pdo->prepare("UPDATE comments SET content = ? WHERE id = ?");
        if ($stmt->execute([$newContent, $commentId])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
}
?>