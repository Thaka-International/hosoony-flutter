# PHP 8.2 Deployment Fix

## Issue Fixed
The `openspout/openspout` package was requiring PHP 8.3+, but you're using PHP 8.2. I've fixed this by constraining openspout to version 4.22 which supports PHP 8.2.

## Quick Deployment Commands

Run these commands in your cPanel terminal:

```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
rm composer.lock
/opt/cpanel/ea-php82/root/usr/bin/php ../bin/composer install --no-dev --optimize-autoloader
/opt/cpanel/ea-php82/root/usr/bin/php artisan key:generate
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate --force
/opt/cpanel/ea-php82/root/usr/bin/php artisan db:seed --force
/opt/cpanel/ea-php82/root/usr/bin/php artisan storage:link
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## What Was Fixed

1. **✅ Constrained openspout**: Added `"openspout/openspout": "^4.22"` to composer.json
2. **✅ PHP 8.2 Compatible**: All dependencies now work with PHP 8.2
3. **✅ Updated .cpanel.yml**: Removed extension ignore flags
4. **✅ Full Laravel 12**: Back to the latest Laravel version

## Alternative: If You Still Get Extension Errors

Enable missing extensions in cPanel:
1. Go to **cPanel → Select PHP Version**
2. Enable: `intl` and `iconv` extensions
3. Click **Apply**

Then run:
```bash
/opt/cpanel/ea-php82/root/usr/bin/php ../bin/composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-intl --ignore-platform-req=ext-iconv
```

## After Successful Deployment

Your application will be available at:
- **Main API**: https://thakaa.me/api
- **Admin Panel**: https://thakaa.me/api/admin
- **API Docs**: https://thakaa.me/api/public/openapi.yaml
