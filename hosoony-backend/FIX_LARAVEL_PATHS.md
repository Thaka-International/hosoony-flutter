# Fix Laravel Paths After Migration

## Problem
PHP is working, but Laravel is still trying to load from the old `/api/` path. The error shows Laravel is looking for files in `/home/thme/public_html/api/vendor/` instead of `/home/thme/public_html/vendor/`.

## Solution: Fix Laravel Paths

### Step 1: Check Current File Structure
```bash
# Check if Laravel files are in the correct location
ls -la /home/thme/public_html/

# Check if vendor directory exists
ls -la /home/thme/public_html/vendor/

# Check if bootstrap directory exists
ls -la /home/thme/public_html/bootstrap/
```

### Step 2: Fix Laravel Bootstrap Paths
```bash
# Check the current index.php file
cat /home/thme/public_html/index.php
```

### Step 3: Update index.php with Correct Paths
```bash
cd /home/thme/public_html

# Create correct index.php
cat > index.php << 'EOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
EOF
```

### Step 4: Clear Laravel Caches
```bash
cd /home/thme/public_html

# Clear all Laravel caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 5: Test Laravel Application
```bash
# Test main application
curl https://thakaa.me/

# Test admin panel
curl https://thakaa.me/admin

# Test API
curl https://thakaa.me/api/v1
```

### Step 6: Check Laravel Logs
```bash
# Check Laravel logs for errors
tail -f /home/thme/public_html/storage/logs/laravel.log
```

### Step 7: Verify File Permissions
```bash
# Set correct permissions
chmod -R 755 /home/thme/public_html
chmod -R 777 /home/thme/public_html/storage
chmod -R 777 /home/thme/public_html/bootstrap/cache
chmod 644 /home/thme/public_html/.env
```

### Step 8: Check if All Laravel Files Are Present
```bash
# Check if all required Laravel files exist
ls -la /home/thme/public_html/app/
ls -la /home/thme/public_html/bootstrap/
ls -la /home/thme/public_html/config/
ls -la /home/thme/public_html/database/
ls -la /home/thme/public_html/resources/
ls -la /home/thme/public_html/routes/
ls -la /home/thme/public_html/storage/
ls -la /home/thme/public_html/vendor/
```

## Alternative: Complete File Migration
If files are still missing, do a complete migration:

```bash
# Remove all files from public_html
rm -rf /home/thme/public_html/*

# Copy all files from api directory
cp -r /home/thme/repos/hosoony/hosoony-backend/* /home/thme/public_html/

# Copy public directory contents
cp -r /home/thme/repos/hosoony/hosoony-backend/public/* /home/thme/public_html/

# Set permissions
chmod -R 755 /home/thme/public_html
chmod -R 777 /home/thme/public_html/storage
chmod -R 777 /home/thme/public_html/bootstrap/cache
chmod 644 /home/thme/public_html/.env
```

## Expected Results
- Laravel loads from correct paths
- No more "/api/" path references
- Application accessible at https://thakaa.me/
- Admin panel working
- API endpoints responding

## Troubleshooting
If issues persist:

1. **Check if all Laravel files are present**
2. **Verify file permissions**
3. **Clear all Laravel caches**
4. **Check .env file configuration**
5. **Review Laravel logs for specific errors**
