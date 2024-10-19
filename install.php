<?php
// نمایش خطاها برای دیباگ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

    // ایجاد دیتابیس اگر وجود ندارد
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "Database '$dbName' created successfully.<br>";

    // بررسی اینکه آیا دیتابیس واقعاً ایجاد شده است
    $result = $pdo->query("SHOW DATABASES LIKE '$dbName'");
    if (!$result->fetch()) {
        throw new Exception("Database '$dbName' was not created.");
    }

    // ساخت یوزر جدید و دادن دسترسی به دیتابیس
    $sqlUser = "CREATE USER IF NOT EXISTS '$dbUser'@'localhost' IDENTIFIED BY '$dbPass'";
    $pdo->exec($sqlUser);
    echo "User '$dbUser' created successfully.<br>";

    // بررسی اینکه آیا یوزر ایجاد شده است
    $resultUser = $pdo->query("SELECT User FROM mysql.user WHERE User = '$dbUser'");
    if (!$resultUser->fetch()) {
        throw new Exception("User '$dbUser' was not created.");
    }

    // اعطای دسترسی به یوزر جدید برای دیتابیس
    $sqlGrant = "GRANT ALL PRIVILEGES ON `$dbName`.* TO '$dbUser'@'localhost'";
    $pdo->exec($sqlGrant);
    $pdo->exec("FLUSH PRIVILEGES");
    echo "Privileges granted to '$dbUser' for '$dbName'.<br>";

    // اتصال به دیتابیس ایجاد شده با یوزر جدید
    $pdo = new PDO("mysql:host=localhost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ایجاد جدول users اگر وجود ندارد
    $sqlTable = "CREATE TABLE IF NOT EXISTS users (
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
    )";
    $pdo->exec($sqlTable);
    echo "Table 'users' created successfully.<br>";

    // ساخت فایل config.php و ذخیره اطلاعات اتصال به دیتابیس
    $configContent = "<?php\n";
    $configContent .= "define('DB_HOST', 'localhost');\n";
    $configContent .= "define('DB_NAME', '$dbName');\n";
    $configContent .= "define('DB_USER', '$dbUser');\n";
    $configContent .= "define('DB_PASS', '$dbPass');\n";
    
    file_put_contents('config.php', $configContent);
    echo "Config file created successfully.<br>";

} catch (PDOException $e) {
    die("PDO Error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
