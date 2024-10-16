<?php
// بررسی اینکه آیا فرم ارسال شده است
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // دریافت اطلاعات دیتابیس از فرم
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];

    try {
        // اتصال به دیتابیس
        $db = new PDO('mysql:host=' . $db_host, $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ایجاد دیتابیس
        $db->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $db->exec("USE `$db_name`");

        // ایجاد جداول
        $sql = "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL,
            `port` INT(5) NOT NULL,
            `traffic` VARCHAR(100) NOT NULL,
            `used_traffic` VARCHAR(100) DEFAULT '0',
            `remaining_traffic` VARCHAR(100) DEFAULT '0',
            `total_traffic` VARCHAR(100) DEFAULT '0',
            `ssr_link` TEXT,
            `converted_link` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ";

        $db->exec($sql);
        echo "Database and tables created successfully!";

        // نوشتن اطلاعات دیتابیس در فایل config.php
        $config_content = "<?php\n";
        $config_content .= "define('DB_HOST', '$db_host');\n";
        $config_content .= "define('DB_NAME', '$db_name');\n";
        $config_content .= "define('DB_USER', '$db_user');\n";
        $config_content .= "define('DB_PASS', '$db_pass');\n";

        file_put_contents('config.php', $config_content);
        echo "Configuration file created successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // نمایش فرم برای دریافت اطلاعات دیتابیس
    ?>
    <form method="post">
        <label for="db_host">Database Host:</label>
        <input type="text" name="db_host" value="localhost" required><br>

        <label for="db_name">Database Name:</label>
        <input type="text" name="db_name" required><br>

        <label for="db_user">Database User:</label>
        <input type="text" name="db_user" required><br>

        <label for="db_pass">Database Password:</label>
        <input type="password" name="db_pass" required><br>

        <input type="submit" value="Install">
    </form>
    <?php
}
?>
