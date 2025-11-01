# Fix Broken Filament Installation

## Problem
- Livewire assets returning 404 (not accessible via web)
- AdminPanelProvider.php file was removed, breaking Filament
- Application now returns 403 Forbidden errors
- Composer autoloader can't find the missing provider

## Solution: Restore Filament Installation

### Step 1: Update Repository
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
```

### Step 2: Copy AdminPanelProvider from Repository
```bash
# Copy the AdminPanelProvider from the repository
cp /home/thme/repos/hosoony/hosoony-backend/app/Providers/Filament/AdminPanelProvider.php /home/thme/public_html/app/Providers/Filament/AdminPanelProvider.php

# Check if file exists
ls -la /home/thme/public_html/app/Providers/Filament/AdminPanelProvider.php
```

### Step 3: Fix Livewire Assets Path
The issue is that Livewire assets are in `/public/vendor/livewire/` but the web server is looking for them at `/vendor/livewire/`. Let's fix this:

```bash
# Check current Livewire assets location
ls -la /home/thme/public_html/public/vendor/livewire/

# The assets are there, but the web server can't access them
# This is likely a .htaccess or path issue
```

### Step 4: Clear Composer Autoloader
```bash
cd /home/thme/public_html

# Regenerate Composer autoloader
composer dump-autoload

# Clear all caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Step 5: Rebuild Caches
```bash
# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 6: Test Application
```bash
# Test admin route
curl -I https://thakaa.me/admin

# Test admin login page
curl -I https://thakaa.me/admin/login
```

### Step 7: Fix Livewire Assets Access
The Livewire assets are getting 404 because of path issues. Let's check the .htaccess:

```bash
# Check current .htaccess
cat /home/thme/public_html/.htaccess

# The issue might be that the rewrite rules are not handling /vendor/ paths correctly
```

### Step 8: Update .htaccess for Vendor Assets
```bash
cd /home/thme/public_html

# Update .htaccess to handle vendor assets properly
cat > .htaccess <<'EOF'
# PHP Handler for cPanel
AddHandler application/x-httpd-php82 .php

# Laravel-friendly .htaccess for cPanel
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes +FollowSymLinks
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Allow access to vendor assets
    RewriteCond %{REQUEST_URI} ^/vendor/
    RewriteRule ^(.*)$ public/$1 [L]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
EOF
```

### Step 9: Test Livewire Assets
```bash
# Test if Livewire assets are now accessible
curl -I https://thakaa.me/vendor/livewire/livewire.min.js

# Test if assets load
curl https://thakaa.me/vendor/livewire/livewire.min.js | head -5
```

### Step 10: Test Admin Panel
```bash
# Test admin route
curl -I https://thakaa.me/admin

# Test admin login page
curl -I https://thakaa.me/admin/login
```

## Alternative: Reinstall Filament Completely
If the above doesn't work:

```bash
# Remove Filament completely
composer remove filament/filament

# Reinstall Filament
composer require filament/filament

# Install Filament panels
php artisan filament:install --panels

# When prompted:
# - Panel ID: admin
# - Overwrite AdminPanelProvider.php: Yes

# Publish assets
php artisan filament:assets
php artisan livewire:publish --assets

# Clear caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Expected Results
- ✅ AdminPanelProvider.php restored
- ✅ No more 403 Forbidden errors
- ✅ Livewire assets accessible via web
- ✅ Admin panel loads properly
- ✅ Login form works via Livewire

## Troubleshooting
If issues persist:

1. **Check file permissions**: `chmod -R 755 /home/thme/public_html`
2. **Check Composer autoloader**: `composer dump-autoload`
3. **Check Laravel logs**: `tail -f /home/thme/public_html/storage/logs/laravel.log`
4. **Test with different browsers**
5. **Clear browser cache completely**
