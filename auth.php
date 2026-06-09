<?php
session_start();

$input = json_decode(file_get_contents('php://input'), true);

if ($input) {
    $_POST = $input;
}

require 'config/db.php';

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

header('Content-Type: application/json'); // برای پاسخ AJAX

// برای درخواست OPTIONS (Preflight request) هم پاسخ بده
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;  // Preflight request موفقیت‌آمیز بود و نیازی به ادامه نیست
 }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    // ۱. بررسی وجود کاربر
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // کاربر وجود دارد: بررسی رمز عبور
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
        }
    } else {
        // کاربر وجود ندارد: ثبت‌نام جدید
        $newUserId = uniqid(); // تولید UUID ساده (در پروژه واقعی از uuid/v4 استفاده کنید)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (id, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$newUserId, $email, $hashedPassword]);
            
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['email'] = $email;
            echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error creating account.']);
        }
    }
} else {
    header("Location: index.php");
}
?>