<?php
session_start();
require 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$siteId = $_GET['siteId'] ?? null;

if ($siteId) {
    // بررسی مالکیت سایت
    $stmt = $pdo->prepare("SELECT owner_id FROM sites WHERE id = ?");
    $stmt->execute([$siteId]);
    $site = $stmt->fetch();

    if (!$site || $site['owner_id'] !== $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }

    // دریافت لیست دسترسی‌ها
    $stmt = $pdo->prepare("SELECT * FROM site_access WHERE site_id = ?");
    $stmt->execute([$siteId]);
    $accessList = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $accessList]);
} else {
    echo json_encode(['success' => false, 'message' => 'No Site ID']);
}
?>