# تنظیمات فایل config.php
echo "Setting up config.php..."
cat <<EOL | sudo tee /var/www/ssr-admin-panel/config.php > /dev/null
<?php
// اطلاعات اتصال به دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'ssrdatabase');
efine('DB_USER', 'root'); // یا هر نام کاربری دیگری که صحیح است
define('DB_PASS', 'password'); // پسورد صحیح کاربر

// سایر تنظیمات عمومی
define('PANEL_TITLE', 'ShadowsocksR Admin Panel');
?>
EOL
