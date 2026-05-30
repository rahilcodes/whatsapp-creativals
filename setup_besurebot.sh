#!/bin/bash
# ============================================================
# panel.besurebot.com — One-Shot Setup Script
# Run this on your VPS as root:
#   bash setup_besurebot.sh
# ============================================================

set -e

DOMAIN="panel.besurebot.com"
APP_ROOT="/var/www/whatsapp-ai/app"
NGINX_CONF="/etc/nginx/sites-available/${DOMAIN}"

echo "=================================================="
echo "  Setting up whitelabel domain: ${DOMAIN}"
echo "  App path: ${APP_ROOT}"
echo "=================================================="

# ── Step 1: Create Nginx Virtual Host ─────────────────────
echo ""
echo "▶ Step 1/4 — Creating Nginx config for ${DOMAIN}..."

sudo bash -c "cat > ${NGINX_CONF} << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name panel.besurebot.com;

    root /var/www/whatsapp-ai/app/public;
    index index.php index.html;

    access_log /var/log/nginx/panel.besurebot.com.access.log;
    error_log  /var/log/nginx/panel.besurebot.com.error.log;

    add_header X-Frame-Options \"SAMEORIGIN\";
    add_header X-Content-Type-Options \"nosniff\";

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

    client_max_body_size 20M;
}
EOF"

# Enable the site
sudo ln -sf "${NGINX_CONF}" "/etc/nginx/sites-enabled/${DOMAIN}"

# Test and reload nginx
sudo nginx -t
sudo systemctl reload nginx

echo "   ✅ Nginx configured for ${DOMAIN}"

# ── Step 2: Update Database — set domain to panel.besurebot.com ───
echo ""
echo "▶ Step 2/4 — Updating reseller domain in database..."

cd "${APP_ROOT}"
php artisan tinker --execute="
\$r = App\Models\Reseller::where('name', 'Besure')->orWhere('domain', 'localhost')->first();
if (\$r) {
    \$r->domain = 'panel.besurebot.com';
    \$r->license_expires_at = now()->addYear();
    \$r->save();
    echo 'Updated reseller: ' . \$r->name . ' -> domain: ' . \$r->domain . PHP_EOL;
} else {
    echo 'ERROR: Reseller not found! Create it via /admin/resellers first.' . PHP_EOL;
}
"

echo "   ✅ Database updated"

# ── Step 3: Clear Laravel caches ─────────────────────────
echo ""
echo "▶ Step 3/4 — Clearing Laravel caches..."

cd "${APP_ROOT}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "   ✅ Caches cleared"

# ── Step 4: SSL Certificate via Let's Encrypt ─────────────
echo ""
echo "▶ Step 4/4 — Getting SSL certificate for ${DOMAIN}..."
echo "   (DNS must be pointed to this server first!)"
echo ""

# Check if certbot is installed
if ! command -v certbot &> /dev/null; then
    echo "   Installing certbot..."
    sudo apt install -y certbot python3-certbot-nginx
fi

# Check if DNS resolves to this server
SERVER_IP=$(curl -s ifconfig.me)
DOMAIN_IP=$(nslookup panel.besurebot.com 2>/dev/null | grep -A1 'Name:' | grep 'Address:' | awk '{print $2}' | head -1)

echo "   This server IP : ${SERVER_IP}"
echo "   Domain resolves: ${DOMAIN_IP}"

if [ "$SERVER_IP" = "$DOMAIN_IP" ]; then
    echo "   ✅ DNS is correctly pointed! Getting SSL certificate..."
    sudo certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos --email admin@ichatup.com --redirect
    echo "   ✅ SSL Certificate issued!"
else
    echo ""
    echo "   ⚠️  DNS not yet pointing to this server."
    echo "   Add this DNS record at BeSureBot's domain registrar:"
    echo ""
    echo "      Type: A"
    echo "      Name: panel"
    echo "      Value: ${SERVER_IP}"
    echo "      TTL: 300"
    echo ""
    echo "   Then re-run SSL setup manually:"
    echo "   sudo certbot --nginx -d panel.besurebot.com"
    echo ""
fi

# ── Done ──────────────────────────────────────────────────
echo ""
echo "=================================================="
echo "  ✅ SETUP COMPLETE!"
echo "=================================================="
echo ""
echo "  Panel URL : http://panel.besurebot.com"
echo "  (https once SSL is done)"
echo ""
echo "  Admin Login:"
echo "    Email   : admin@besurebot.com"
echo "    Password: Admin@1234"
echo ""
echo "  Reseller Admin : /reseller-admin"
echo "=================================================="
