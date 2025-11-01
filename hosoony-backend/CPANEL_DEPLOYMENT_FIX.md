# cPanel Deployment Fix for thakaa.me

## Issue Identified
Your WHM shows `thakaa.me` is set to **PHP 8.2**, but the terminal shows **PHP 8.1.33**. This version mismatch is causing deployment failures.

## Solutions

### Option 1: Use PHP 8.2 (Recommended)
Since your WHM is configured for PHP 8.2, let's revert to the original Laravel 12 configuration:

```bash
# In your cPanel terminal, run:
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
../bin/composer install --no-dev --optimize-autoloader
```

### Option 2: Fix PHP Version Mismatch
If you want to use PHP 8.1, update your WHM settings:

1. **In WHM MultiPHP Manager:**
   - Find `thakaa.me` domain
   - Change PHP version from `PHP 8.2` to `PHP 8.1`
   - Click "Apply"

2. **Then run deployment:**
   ```bash
   cd /home/thme/repos/hosoony/hosoony-backend
   ../bin/composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-intl --ignore-platform-req=ext-iconv
   ```

### Option 3: Enable Missing PHP Extensions
Enable the missing extensions in cPanel:

1. **Go to cPanel → Select PHP Version**
2. **Enable these extensions:**
   - `intl` (Internationalization)
   - `iconv` (Character encoding conversion)
3. **Click "Apply"**

## Quick Fix Commands

### For PHP 8.2 (Current WHM Setting):
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
../bin/composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

### For PHP 8.1 (If you change WHM setting):
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
../bin/composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-intl --ignore-platform-req=ext-iconv
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

## Recommended Action
**Use Option 1** - Keep PHP 8.2 in WHM and use the updated composer.json that supports PHP 8.2. This is the most straightforward solution.

## After Successful Deployment
Your application will be available at:
- **Main API**: https://thakaa.me/api
- **Admin Panel**: https://thakaa.me/api/admin
- **API Docs**: https://thakaa.me/api/public/openapi.yaml
