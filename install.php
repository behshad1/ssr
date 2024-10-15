<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['db_host'];
    $dbname = $_POST['db_name'];
    $dbuser = $_POST['db_user'];
    $dbpass = $_POST['db_pass'];
    $adminUsername = $_POST['admin_username']; // نام کاربری ادمین
    $adminPassword = $_POST['admin_password']; // رمز عبور ادمین

    // تلاش برای اتصال به دیتابیس
    try {
        $pdo = new PDO("mysql:host=$host", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ایجاد دیتابیس اگر وجود ندارد
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`; USE `$dbname`;");

        // ایجاد جدول کاربران
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL, // اضافه کردن فیلد برای رمز عبور
            port INT NOT NULL,
            traffic VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            used_traffic VARCHAR(50),
            remaining_traffic VARCHAR(50),
            total_traffic VARCHAR(50),
            ssr_link TEXT,
            converted_link TEXT
        );");

        // هَش کردن رمز عبور ادمین
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

        // ذخیره اطلاعات ادمین در جدول users
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$adminUsername, $hashedPassword]);

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
        <label for="db_host">Database Host:</label>
        <input type="text" name="db_host" required><br><br>
        
        <label for="db_name">Database Name:</label>
        <input type="text" name="db_name" required><br><br>
        
        <label for="db_user">Database User:</label>
        <input type="text" name="db_user" required><br><br>
        
        <label for="db_pass">Database Password:</label>
        <input type="password" name="db_pass" required><br><br>

        <label for="admin_username">Admin Username:</label>
        <input type="text" name="admin_username" required><br><br>
        
        <label for="admin_password">Admin Password:</label>
        <input type="password" name="admin_password" required><br><br>

        <input type="submit" value="Install">
    </form>
</body>
</html>
