#!/bin/bash

# نصب پیش‌نیازها
echo "Installing dependencies..."
sudo apt update
sudo apt install -y php php-mysql nginx git

# کلون کردن پروژه از گیت‌هاب
echo "Cloning the project from GitHub..."
git clone https://github.com/behshad1/ssr.git /var/www/ssr-admin-panel

# تنظیم پرمیشن‌ها برای Nginx
echo "Setting up permissions..."
sudo chown -R www-data:www-data /var/www/ssr-admin-panel

# تنظیمات Nginx (می‌توانید از Apache نیز استفاده کنید)
echo "Configuring Nginx..."
cat <<EOL > /etc/nginx/sites-available/ssr-panel
server {
    listen 80;
    server_name your_domain_or_ip;
    root /var/www/ssr-admin-panel;

    index admin_panel.php index.php index.html;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOL

sudo ln -s /etc/nginx/sites-available/ssr-panel /etc/nginx/sites-enabled/

# راه‌اندازی مجدد Nginx
sudo systemctl restart nginx

# ساخت دیتابیس
echo "Setting up the database..."
mysql -u root -p -e "SOURCE /var/www/ssr-admin-panel/sql/ssr_database.sql;"

# تنظیم کرون‌جاب برای به‌روزرسانی ترافیک
echo "Setting up the cron job..."
(crontab -l ; echo "* * * * * /usr/bin/php /var/www/ssr-admin-panel/update_users_traffic.php") | crontab -

echo "Installation completed. Please visit your_domain_or_ip to access the panel."
