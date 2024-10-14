<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['db_host'];
    $dbname = $_POST['db_name'];
    $dbuser = $_POST['db_user'];
    $dbpass = $_POST['db_pass'];
    
    // اطلاعات یوزرنیم و پسورد مدیر
    $admin_user = $_POST['admin_user'];
    $admin_pass = $_POST['admin_pass'];
    
    // هش کردن پسورد برای امنیت
    $admin_pass_hashed = password_hash($admin_pass, PASSWORD_BCRYPT);

    // تلاش برای اتصال به دیتابیس
    try {
        $pdo = new PDO("mysql:host=$host", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ایجاد دیتابیس اگر وجود ندارد
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`; USE `$dbname`;");

        // ایجاد جدول کاربران پنل
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            port INT,
            traffic VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            used_traffic VARCHAR(50),
            remaining_traffic VARCHAR(50),
            total_traffic VARCHAR(50),
            ssr_link TEXT,
            converted_link TEXT
        );");

        // ذخیره اطلاعات مدیر در جدول
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$admin_user, $admin_pass_hashed]);

        // ذخیره اطلاعات کانفیگ در فایل db.php
        $configContent = "<?php\n";
        $configContent .= "\$host = '$host';\n";
        $configContent .= "\$db = '$dbname';\n";
        $configContent .= "\$user = '$dbuser';\n";
        $configContent .= "\$pass = '$dbpass';\n";
        $configContent .= "try {\n";
        $configContent .= "\$db = new PDO(\"mysql:host=\$host;dbname=\$db\", \$user, \$pass);\n";
        $configContent .= "\$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
        $configContent .= "} catch (PDOException \$e) {\n";
        $configContent .= "die('Connection failed: ' . \$e->getMessage());\n";
        $configContent .= "}\n?>";

        file_put_contents('../includes/db.php', $configContent);

        echo "Installation complete!";
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installation</title>
</head>
<body>
    <h1>Install Panel</h1>
    <form method="post">
        <!-- تنظیمات دیتابیس -->
        <label for="db_host">Database Host:</label>
        <input type="text" name="db_host" required><br><br>
        
        <label for="db_name">Database Name:</label>
        <input type="text" name="db_name" required><br><br>
        
        <label for="db_user">Database User:</label>
        <input type="text" name="db_user" required><br><br>
        
        <label for="db_pass">Database Password:</label>
        <input type="password" name="db_pass" required><br><br>

        <!-- تنظیمات یوزر و پسورد مدیر -->
        <label for="admin_user">Admin Username:</label>
        <input type="text" name="admin_user" required><br><br>
        
        <label for="admin_pass">Admin Password:</label>
        <input type="password" name="admin_pass" required><br><br>

        <input type="submit" value="Install">
    </form>
</body>
</html>
