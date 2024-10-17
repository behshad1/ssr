<?php
// نمایش خطاها برای دیباگ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php'; // اطمینان حاصل کنید که مسیر درست باشد

try {
    // اتصال به MySQL به عنوان کاربر روت
    $pdo = new PDO('mysql:host=' . DB_HOST, 'root', 'newpassword'); // رمز عبور روت را درست وارد کنید
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ایجاد دیتابیس
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    echo "Database '" . DB_NAME . "' created successfully.<br>";

    // انتخاب دیتابیس
    $pdo->exec("USE `" . DB_NAME . "`");

    // ساخت یوزر جدید و دادن دسترسی به دیتابیس
    $pdo->exec("CREATE USER IF NOT EXISTS '" . DB_USER . "'@'localhost' IDENTIFIED BY '" . DB_PASS . "'");
    $pdo->exec("GRANT ALL PRIVILEGES ON `" . DB_NAME . "`.* TO '" . DB_USER . "'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "User '" . DB_USER . "' created and granted privileges.<br>";

    // ایجاد جدول users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        port INT(5) NOT NULL,
        traffic BIGINT DEFAULT 0,
        used_traffic BIGINT DEFAULT 0,
        remaining_traffic BIGINT DEFAULT 0,
        total_traffic BIGINT DEFAULT 0,
        ssr_link TEXT,
        converted_link TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'users' created successfully.<br>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
