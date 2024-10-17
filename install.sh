#!/bin/bash

# تابع حذف کامل پروژه و تنظیمات قبلی
uninstall() {
    echo "Uninstalling SSR Admin Panel..."
    sudo rm -rf /var/www/ssr-admin-panel
    sudo rm -f /etc/nginx/sites-available/ssr-panel
    sudo rm -f /etc/nginx/sites-enabled/ssr-panel
    sudo systemctl restart nginx
    echo "Uninstallation completed."
}

# بررسی وجود دایرکتوری پروژه
if [ -d "/var/www/ssr-admin-panel" ]; then
    echo "The SSR Admin Panel already exists. What do you want to do?"
    echo "1) Uninstall and Reinstall"
    echo "2) Cancel"
    
    read -p "Please enter your choice [1-2]: " choice

    case $choice in
        1)
            uninstall
            ;;
        2)
            echo "Installation canceled."
            exit 0
            ;;
        *)
            echo "Invalid choice. Installation canceled."
            exit 1
            ;;
    esac
fi

# نصب پیش‌نیازها
echo "Installing dependencies..."
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql nginx git mysql-server

# بررسی نصب MySQL
if ! command -v mysql &> /dev/null; then
    echo "Error: MySQL installation failed or MySQL is not installed correctly."
    exit 1
fi

# کلون کردن پروژه از گیت‌هاب
echo "Cloning the project from GitHub..."
git clone https://github.com/behshad1/ssr.git /var/www/ssr-admin-panel

# تنظیم پرمیشن‌ها برای Nginx
echo "Setting up permissions..."
sudo chown -R www-data:www-data /var/www/ssr-admin-panel

# تنظیم user و group برای PHP
echo "Configuring PHP user and group..."
sudo sed -i "s/^user = .*/user = www-data/" /etc/php/8.1/fpm/pool.d/www.conf
sudo sed -i "s/^group = .*/group = www-data/" /etc/php/8.1/fpm/pool.d/www.conf

# غیرفعال کردن توابع exec, passthru, system
echo "Modifying PHP configuration..."
sudo sed -i "s/^disable_functions = .*/disable_functions = exec,passthru,system,/" /etc/php/8.1/fpm/php.ini

# گرفتن آی‌پی سرور
server_ip=$(curl -s http://checkip.amazonaws.com)

# درخواست پورت سفارشی از کاربر
echo "Requesting port number from user..."
read -p "Please enter the port number to run the panel (default: 8080): " port
port=${port:-8080}  # اگر کاربر چیزی وارد نکرد، پورت پیش‌فرض 8080 خواهد بود
echo "Port entered: $port"


# تنظیمات Nginx
echo "Configuring Nginx..."
sudo rm -f /etc/nginx/sites-enabled/ssr-panel  # حذف سیم‌لینک قدیمی اگر وجود دارد
cat <<EOL | sudo tee /etc/nginx/sites-available/ssr-panel > /dev/null
server {
    listen $port;
    server_name $server_ip;
    root /var/www/ssr-admin-panel;

    index admin_panel.php index.php index.html;

    location / {
        try_files \$uri \$uri/ =404;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOL

# ایجاد لینک سیم‌لینک
sudo ln -sf /etc/nginx/sites-available/ssr-panel /etc/nginx/sites-enabled/

# راه‌اندازی مجدد Nginx
echo "Restarting Nginx..."
sudo systemctl restart nginx



# وارد کردن اطلاعات دیتابیس در فایل کانفیگ
echo "Configuring database settings..."
sed -i "s/DB_NAME=.*/DB_NAME=\"ssrdatabase\"/" /var/www/ssr-admin-panel/config.php
sed -i "s/DB_USER=.*/DB_USER=\"ssruser\"/" /var/www/ssr-admin-panel/config.php
sed -i "s/DB_PASS=.*/DB_PASS=\"password123\"/" /var/www/ssr-admin-panel/config.php

# تنظیم پسورد دیتابیس در MySQL
mysql -u root -p -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$db_password';"

# تنظیم کرون‌جاب برای به‌روزرسانی ترافیک
echo "Setting up the cron job..."
(crontab -l ; echo "* * * * * /usr/bin/php /var/www/ssr-admin-panel/update_users_traffic.php") | crontab -

# افزودن مجوز برای کاربر www-data
echo "Configuring sudoers for www-data..."
echo "www-data ALL=(ALL) NOPASSWD: /usr/local/bin/ssrrmu.sh" | sudo tee -a /etc/sudoers

# پیام پایانی نصب
echo "Installation completed. Please visit http://$server_ip:$port to access the panel."
