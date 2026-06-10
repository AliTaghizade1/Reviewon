<?php
// Database connection settings
$host = 'sql201.infinityfree.com';
$port = 3306;

$db   = 'if0_42143305_review_site_db';
$user = 'if0_42143305';
$pass = 'VP3G8MTF9tvE7';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
