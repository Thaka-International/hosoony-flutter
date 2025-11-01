# cPanel Terminal Deployment Fix

## Current Issue
Your terminal is using **PHP 8.1.33** but the original code requires **PHP 8.2**. The composer.lock file also needs to be regenerated.

## Solution: Use PHP 8.1 Compatible Version

### Step 1: Pull the Updated Code
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
```

### Step 2: Delete Old Lock File and Install Dependencies
```bash
# Remove the old lock file that has PHP 8.2 dependencies
rm composer.lock

# Install with PHP 8.1 compatible versions
../bin/composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-intl --ignore-platform-req=ext-iconv
```

### Step 3: If Step 2 Fails, Try This Alternative
```bash
# Update composer to get PHP 8.1 compatible versions
../bin/composer update --no-dev --optimize-autoloader --ignore-platform-req=ext-intl --ignore-platform-req=ext-iconv
```

### Step 4: Complete Laravel Setup
```bash
# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Seed the database
php artisan db:seed --force

# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## Alternative: Enable Missing PHP Extensions

If you prefer to keep the original PHP 8.2 code, enable the missing extensions:

### In cPanel:
1. Go to **cPanel → Select PHP Version**
2. Enable these extensions:
   - `intl` (Internationalization)
   - `iconv` (Character encoding conversion)
3. Click **Apply**

### Then run:
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git checkout HEAD~1 -- composer.json  # Revert to PHP 8.2 version
../bin/composer install --no-dev --optimize-autoloader
```

## Recommended Approach

**Use the PHP 8.1 compatible version** (Step 1-4 above) since your terminal is already configured for PHP 8.1. This avoids any version conflicts.

## After Successful Deployment

Your application will be available at:
- **Main API**: https://thakaa.me/api
- **Admin Panel**: https://thakaa.me/api/admin
- **API Docs**: https://thakaa.me/api/public/openapi.yaml

## Troubleshooting

If you still get errors:
1. Check PHP version: `php -v`
2. Check available extensions: `php -m`
3. Try with verbose output: `../bin/composer install -vvv`
