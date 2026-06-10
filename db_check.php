<?php
require 'config/db.php';

try {
    $stmt = $pdo->query('SELECT 1 AS ok');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo 'DB Connection OK: ' . htmlspecialchars((string)($row['ok'] ?? ''));
} catch (Throwable $e) {
    echo 'DB Connection FAILED: ' . htmlspecialchars($e->getMessage());
}

