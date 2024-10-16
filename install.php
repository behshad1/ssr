<?php
// نمایش خطاها برای دیباگ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

// اطلاعات دیتابیس اصلی MySQL
$rootUser = 'root'; // این یوزر باید دسترسی ادمین داشته باشد
$rootPass = 'newpassword'; // رمز عبور جدید روت که تنظیم کردید

// نام دیتابیس و یوزر جدیدی که می‌خواهید بسازید
$dbName = 'ssrdatabase';
$dbUser = 'ssruser'; // نام کاربری یوزر جدید
$dbPass = 'password123'; // پسورد برای یوزر جدید

try {
    // اتصال به MySQL به عنوان root
    $pdo = new PDO('mysql:host=localhost', $rootUser, $rootPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ایجاد دیتابیس
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
    echo "Database '$dbName' created successfully.<br>";

    // ساخت یوزر جدید و دادن دسترسی به دیتابیس
    $pdo->exec("CREATE USER IF NOT EXISTS '$dbUser'@'localhost' IDENTIFIED BY '$dbPass'");
    $pdo->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$dbUser'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "User '$dbUser' created and granted privileges.<br>";

    // اتصال به دیتابیس ایجاد شده با یوزر جدید
    $pdo = new PDO("mysql:host=localhost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    // ساخت فایل config.php و ذخیره اطلاعات اتصال به دیتابیس
    $configContent = "<?php\n";
    $configContent .= "define('DB_HOST', 'localhost');\n";
    $configContent .= "define('DB_NAME', '$dbName');\n";
    $configContent .= "define('DB_USER', '$dbUser');\n";
    $configContent .= "define('DB_PASS', '$dbPass');\n";
    
    if (file_put_contents('config.php', $configContent)) {
        echo "Config file created successfully.<br>";
    } else {
        echo "Failed to create config file.<br>";
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
