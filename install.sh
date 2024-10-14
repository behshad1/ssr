#!/bin/bash

# نصب پیش‌نیازها
echo "Installing dependencies..."
sudo apt update
sudo apt install -y php7.4 php7.4-fpm php7.4-mysql nginx git

# کلون کردن پروژه از گیت‌هاب
echo "Cloning the project from GitHub..."
git clone https://github.com/behshad1/ssr.git /var/www/ssr-admin-panel

# تنظیم پرمیشن‌ها برای Nginx
echo "Setting up permissions..."
sudo chown -R www-data:www-data /var/www/ssr-admin-panel

# گرفتن آی‌پی سرور
server_ip=$(curl -s http://checkip.amazonaws.com)

# تنظیمات Nginx
echo "Configuring Nginx..."
cat <<EOL > /etc/nginx/sites-available/ssr-panel
server {
    listen 80;
    server_name $server_ip;  # آی‌پی سرور خودکار جایگزین می‌شود
    root /var/www/ssr-admin-panel;

    index admin_panel.php index.php index.html;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOL

# ایجاد لینک از فایل تنظیمات در sites-available به sites-enabled
sudo ln -s /etc/nginx/sites-available/ssr-panel /etc/nginx/sites-enabled/

# راه‌اندازی مجدد Nginx
sudo systemctl restart nginx

# ساخت دیتابیس
echo "Setting up the database..."
mysql -u root -p -e "SOURCE /var/www/ssr-admin-panel/sql/ssr_database.sql;"

# تنظیم کرون‌جاب برای به‌روزرسانی ترافیک
echo "Setting up the cron job..."
(crontab -l ; echo "* * * * * /usr/bin/php /var/www/ssr-admin-panel/update_users_traffic.php") | crontab -

# پیام پایانی نصب
echo "Installation completed. Please visit http://$server_ip to access the panel."
