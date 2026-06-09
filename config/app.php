<?php
// config/app.php — base URL for links in emails (supports XAMPP subfolders)

function app_base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = rtrim($scriptDir, '/');

    return $scheme . '://' . $host . $basePath;
}
