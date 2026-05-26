#!/bin/bash

echo "🚀 Starting Deployment..."

# 1. Update system & install prerequisites
sudo apt update
sudo apt install -y nginx curl git unzip software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
sudo npm install -g pm2

# 2. Install Composer
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# 3. Clone code from GitHub
cd /var/www
sudo rm -rf whatsapp-ai
sudo git clone https://github.com/rahilcodes/whatsapp-creativals.git whatsapp-ai
cd whatsapp-ai

# 4. Set up Laravel Backend
cd app
composer install --optimize-autoloader --no-dev

# Preserve live configuration (.env) on redeployment
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
touch database/database.sqlite
php artisan migrate --force
php artisan storage:link --force

# Copy persistent credentials from secure location
if [ -f /var/www/google-service-account.json ]; then
    cp /var/www/google-service-account.json storage/app/google-service-account.json
fi

# 5. Set up Node.js Engine
cd ../bot
npm install
cp .env.example .env
sed -i "s/LARAVEL_URL=.*/LARAVEL_URL=http:\/\/127.0.0.1/" .env

# 6. Configure Nginx (only if not already created)
if [ ! -f /etc/nginx/sites-available/whatsapp-ai ]; then
    sudo bash -c 'cat > /etc/nginx/sites-available/whatsapp-ai <<EOF
server {
    listen 80;
    server_name 69.62.81.21;
    root /var/www/whatsapp-ai/app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF'

    sudo ln -sf /etc/nginx/sites-available/whatsapp-ai /etc/nginx/sites-enabled/
    sudo rm -f /etc/nginx/sites-enabled/default
    sudo systemctl restart nginx
fi
sudo systemctl restart php8.2-fpm
sudo chown -R www-data:www-data /var/www/whatsapp-ai/app/storage
sudo chown -R www-data:www-data /var/www/whatsapp-ai/app/bootstrap/cache
sudo chown -R www-data:www-data /var/www/whatsapp-ai/app/database

# 7. Start the background WhatsApp Engine
pm2 stop whatsapp-engine || true
pm2 start src/index.js --name "whatsapp-engine"

# 8. Start the Laravel Queue Worker
pm2 stop laravel-queue || true
pm2 start artisan --name "laravel-queue" --interpreter php --cwd "/var/www/whatsapp-ai/app" -- queue:work --sleep=3 --tries=3

pm2 save

echo "=========================================================="
echo "✅ DEPLOYMENT COMPLETE! Go to http://69.62.81.21"
echo "=========================================================="
