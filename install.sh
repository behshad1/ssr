#!/bin/bash

# تابعی برای حذف کامل پروژه
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
sudo apt install -y php7.4 php7.4-fpm php7.4-mysql nginx git mysql-server

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

# گرفتن آی‌پی سرور
server_ip=$(curl -s http://checkip.amazonaws.com)

# درخواست پورت سفارشی از کاربر
read -p "Please enter the port number to run the panel (default: 8080): " port
port=${port:-8080}

# تنظیمات Nginx
echo "Configuring Nginx..."
sudo rm -f /etc/nginx/sites-enabled/ssr-panel  # حذف سیم‌لینک قدیمی اگر وجود دارد
cat <<EOL > /etc/nginx/sites-available/ssr-panel
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
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOL

# ایجاد لینک سیم‌لینک
sudo ln -sf /etc/nginx/sites-available/ssr-panel /etc/nginx/sites-enabled/

# راه‌اندازی مجدد Nginx
sudo systemctl restart nginx

# ساخت دیتابیس
echo "Setting up the database..."
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ssrdatabase;"
mysql -u root -p -e "SOURCE /var/www/ssr-admin-panel/sql/ssr_database.sql;"

# تنظیم کرون‌جاب برای به‌روزرسانی ترافیک
echo "Setting up the cron job..."
(crontab -l ; echo "* * * * * /usr/bin/php /var/www/ssr-admin-panel/update_users_traffic.php") | crontab -

# پیام پایانی نصب
echo "Installation completed. Please visit http://$server_ip:$port to access the panel."
