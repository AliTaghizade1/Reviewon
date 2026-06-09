<?php
// config.php   ← فایل پیکربندی اتصال
require 'config/db.php';   // $pdo (PDO instance)

// دریافت لیست تمام جداول در دیتابیس فعلی
$sqlTables = "
    SELECT TABLE_NAME
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_TYPE = 'BASE TABLE'";
$tables = $pdo->query($sqlTables)->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "<h2>جدول: <strong>$table</strong></h2>\n";
    echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
    echo "<tr><th>ستون</th><th>نوع</th><th>کلید</th><th>مقدار پیش‌فرض</th><th>NULL؟</th><th>شرح</th></tr>\n";

    // دریافت ستون‌ها و ویژگی‌ها از information_schema
    $sqlColumns = "
        SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY,
               COLUMN_DEFAULT, IS_NULLABLE, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = :tbl
        ORDER BY ORDINAL_POSITION";
    $stmt = $pdo->prepare($sqlColumns);
    $stmt->execute([':tbl' => $table]);

    while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$col['COLUMN_NAME']}</td>";
        echo "<td>{$col['COLUMN_TYPE']}</td>";
        echo "<td>{$col['COLUMN_KEY']}</td>";
        echo "<td>" . ($col['COLUMN_DEFAULT'] !== null ? $col['COLUMN_DEFAULT'] : 'NULL') . "</td>";
        echo "<td>{$col['IS_NULLABLE']}</td>";
        echo "<td>{$col['COLUMN_COMMENT']}</td>";
        echo "</tr>\n";
    }
    echo "</table><br>\n";

    // (اختیاری) نمایش عبارت CREATE TABLE
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . htmlentities($create['Create Table']) . "</pre>\n";
}
?>