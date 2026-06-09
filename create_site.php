<?php
session_start();
require 'config/db.php';

// بررسی می‌کنیم که آیا کاربر لاگین است و درخواست از نوع POST است
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    // تمیز کردن ورودی URL
    $url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    $userId = $_SESSION['user_id'];

    if ($url) {
        // تولید یک شناسه یکتا برای سایت
        $siteId = uniqid('', true);

        // ذخیره در دیتابیس
        $stmt = $pdo->prepare("INSERT INTO sites (id, url, owner_id) VALUES (?, ?, ?)");
        $stmt->execute([$siteId, $url, $userId]);
        
        // پس از ذخیره موفق، کاربر را به صفحه ابزار (Tool) برای همین سایت هدایت می‌کنیم
        // (در فازهای بعدی فایل tool.php را می‌سازیم)
        header("Location: tool.php?id=" . $siteId);
        exit;
    }
}

// اگر مشکلی پیش آمد یا دسترسی غیرمجاز بود، برگرد به داشبورد
header("Location: dashboard.php");
?>