#!/bin/bash
# Fix script — run on VPS after git pull
# paste everything below into your terminal

cd /var/www/whatsapp-ai

# 1. Pull the new files (Reseller model, migrations, everything)
git pull origin main

# 2. Refresh composer autoloader so Reseller class is found
cd /var/www/whatsapp-ai/app
composer dump-autoload --optimize

# 3. Run the new migrations (creates resellers table, adds columns)
php artisan migrate --force

# 4. Update the reseller domain + fix license in DB
php artisan tinker --execute="
\$r = App\Models\Reseller::first();
if(\$r){
    \$r->domain = 'panel.besurebot.com';
    \$r->license_expires_at = now()->addYear();
    \$r->save();
    echo 'Updated: '.\$r->name.' -> '.\$r->domain;
} else {
    echo 'No reseller found - create one at /admin/resellers first';
}
"

# 5. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Get SSL (DNS is already pointing - 69.62.81.21 is correct)
sudo certbot --nginx -d panel.besurebot.com --non-interactive --agree-tos --email admin@ichatup.com --redirect

echo ""
echo "============================================"
echo " DONE! Visit: https://panel.besurebot.com"
echo " Login: admin@besurebot.com / Admin@1234"
echo " Then go to: /reseller-admin"
echo "============================================"
