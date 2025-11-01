# Fix Composer and Provider Errors

## Problem
- `composer: command not found` - Need to use full path to composer
- Laravel logs show provider registration errors
- Filament panel commands not working properly

## Solution: Fix Composer and Provider Issues

### Step 1: Update Repository
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
```

### Step 2: Use Full Path to Composer
```bash
cd /home/thme/public_html

# Use full path to composer
/usr/local/bin/composer dump-autoload

# Or use the cPanel composer path
/opt/cpanel/ea-php82/root/usr/bin/composer dump-autoload
```

### Step 3: Check AdminPanelProvider.php
```bash
# Check if the file exists and has content
ls -la /home/thme/public_html/app/Providers/Filament/AdminPanelProvider.php
cat /home/thme/public_html/app/Providers/Filament/AdminPanelProvider.php | head -20
```

### Step 4: Check Provider Registration
```bash
# Check if the provider is registered in config/app.php
grep -n "AdminPanelProvider" /home/thme/public_html/config/app.php

# Check the providers array
cat /home/thme/public_html/config/app.php | grep -A 30 "providers"
```

### Step 5: Fix Provider Registration
If the provider is not registered, add it:

```bash
# Check the current providers
cat /home/thme/public_html/config/app.php | grep -A 50 "providers"
```

### Step 6: Clear All Caches
```bash
cd /home/thme/public_html

# Clear all caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Step 7: Regenerate Autoloader
```bash
# Use full path to composer
/usr/local/bin/composer dump-autoload

# Or try the cPanel path
/opt/cpanel/ea-php82/root/usr/bin/composer dump-autoload
```

### Step 8: Rebuild Caches
```bash
# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 9: Test Application
```bash
# Test admin route
curl -I https://thakaa.me/admin

# Test admin login page
curl -I https://thakaa.me/admin/login

# Test with full response
curl https://thakaa.me/admin/login
```

## Alternative: Complete Filament Reinstall
If the above doesn't work:

### Step 1: Remove Filament
```bash
cd /home/thme/public_html

# Remove Filament using full composer path
/usr/local/bin/composer remove filament/filament

# Or try cPanel path
/opt/cpanel/ea-php82/root/usr/bin/composer remove filament/filament
```

### Step 2: Reinstall Filament
```bash
# Reinstall Filament
/usr/local/bin/composer require filament/filament

# Or try cPanel path
/opt/cpanel/ea-php82/root/usr/bin/composer require filament/filament

# Install Filament panels
php artisan filament:install --panels

# When prompted:
# - Panel ID: admin
# - Overwrite AdminPanelProvider.php: Yes (if it exists)
```

### Step 3: Publish Assets
```bash
# Publish Filament assets
php artisan filament:assets

# Publish Livewire assets
php artisan livewire:publish --assets
```

### Step 4: Clear and Rebuild Caches
```bash
# Clear all caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Alternative: Check Composer Paths
```bash
# Find composer installation
which composer
whereis composer
ls -la /usr/local/bin/composer
ls -la /opt/cpanel/ea-php82/root/usr/bin/composer

# Check if composer is in PATH
echo $PATH
```

## Expected Results
- ✅ Composer commands work with full path
- ✅ AdminPanelProvider.php properly registered
- ✅ No more provider registration errors
- ✅ Admin panel loads properly
- ✅ No more 403 Forbidden errors

## Troubleshooting
If issues persist:

1. **Check composer paths**: `which composer` or `whereis composer`
2. **Check file permissions**: `chmod -R 755 /home/thme/public_html`
3. **Check Laravel logs**: `tail -f /home/thme/public_html/storage/logs/laravel.log`
4. **Check Apache error logs**: `tail -f /usr/local/apache/logs/error_log`
5. **Test with different browsers**
6. **Clear browser cache completely**
