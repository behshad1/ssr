# تنظیمات فایل config.php
echo "Setting up config.php..."
cat <<EOL | sudo tee /var/www/ssr-admin-panel/config.php > /dev/null
<?php
// اطلاعات اتصال به دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'ssrdatabase');
define('DB_USER', '$admin_user'); // نام کاربری ادمین که کاربر وارد کرده است
define('DB_PASS', '$admin_pass');  // رمز عبور ادمین که کاربر وارد کرده است

// سایر تنظیمات عمومی
define('PANEL_TITLE', 'ShadowsocksR Admin Panel');
?>
EOL
