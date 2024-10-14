

<?php
// بارگذاری تنظیمات
require_once 'config.php';

// اتصال به دیتابیس با استفاده از PDO
try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
