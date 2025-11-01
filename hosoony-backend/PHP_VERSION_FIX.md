# PHP Version Fix for Web Server

## Issue
Composer detected PHP version issue: "Your Composer dependencies require a PHP version '>= 8.2.0'"

## Root Cause
The web server is using a different PHP version than the CLI. CLI uses PHP 8.2, but web server might be using an older version.

## Solution Steps

### Step 1: Check Current PHP Version
```bash
# Check CLI PHP version
/opt/cpanel/ea-php82/root/usr/bin/php -v

# Check web server PHP version (if possible)
curl -I https://thakaa.me/
```

### Step 2: Update PHP Version in cPanel
1. **Login to cPanel**
2. **Go to "Select PHP Version" or "PHP Selector"**
3. **Select PHP 8.2** (or latest available)
4. **Enable required extensions:**
   - `php-mysql`
   - `php-mysqli`
   - `php-pdo`
   - `php-mbstring`
   - `php-xml`
   - `php-curl`
   - `php-zip`
   - `php-gd`
   - `php-intl`
5. **Click "Apply"**

### Step 3: Alternative - Create .htaccess with PHP Version
Create `/home/thme/public_html/.htaccess`:
```apache
# Set PHP version
AddHandler application/x-httpd-php82 .php

# Laravel rewrite rules
RewriteEngine On
RewriteRule ^(.*)$ api/public/$1 [L]
```

### Step 4: Test After PHP Update
```bash
curl https://thakaa.me/
curl https://thakaa.me/admin
```

### Step 5: If Still Not Working - Reinstall Dependencies
```bash
cd /home/thme/public_html
rm -rf vendor/
/opt/cpanel/ea-php82/root/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
```

## Expected Results
- Main page: Laravel welcome page or app
- Admin panel: Filament admin interface
- No PHP version errors

## Alternative: Use PHP 8.1 Compatible Version
If PHP 8.2 is not available, downgrade Laravel:

```bash
cd /home/thme/public_html
nano composer.json
```

Change:
```json
"php": "^8.2"
```
to:
```json
"php": "^8.1"
```

Then run:
```bash
rm composer.lock
/opt/cpanel/ea-php82/root/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
```
