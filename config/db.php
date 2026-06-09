<?php
$host = 'localhost';
$db   = 'review_site_db';
$user = 'root'; // نام کاربری پیش‌فرض زمپ
$pass = '';     // رمز عبور پیش‌فرض زمپ (خالی)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>