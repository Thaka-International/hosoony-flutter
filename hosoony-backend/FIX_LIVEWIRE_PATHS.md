# Fix Livewire Path Issues

## Problem
The admin routes are working (302 redirect to login), but Livewire is still looking for files in the old `/api/` path structure, causing 403 errors in the browser.

## Solution: Fix Livewire Asset Paths

### Step 1: Update Repository
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
```

### Step 2: Clear All Caches and Rebuild
```bash
cd /home/thme/public_html

# Clear all caches completely
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Clear Livewire cache specifically
php artisan livewire:clear-cache

# Rebuild everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 3: Fix Livewire Configuration
```bash
# Check Livewire configuration
php artisan config:show livewire

# Publish Livewire assets
php artisan livewire:publish --assets
```

### Step 4: Update .htaccess for Livewire
```bash
cd /home/thme/public_html

# Update .htaccess to handle Livewire properly
cat > .htaccess << 'EOF'
# Traditional PHP handler for PHP 8.2
AddHandler application/x-httpd-php82 .php

# Laravel rewrite rules
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security headers
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

### Step 5: Check Filament Configuration
```bash
# Check Filament configuration
php artisan config:show filament

# Publish Filament assets
php artisan filament:assets
```

### Step 6: Test Admin Access
```bash
# Test admin route
curl -I https://thakaa.me/admin

# Test admin login page
curl -I https://thakaa.me/admin/login
```

### Step 7: Check Browser Console
Open https://thakaa.me/admin in browser and check:
- No more Livewire 403 errors
- Admin login page loads properly
- All assets load correctly

### Step 8: Verify File Structure
```bash
# Check if all required files exist
ls -la /home/thme/public_html/vendor/livewire/
ls -la /home/thme/public_html/vendor/filament/
ls -la /home/thme/public_html/public/
```

## Alternative: Minimal .htaccess
If the above doesn't work, try a minimal .htaccess:

```bash
cd /home/thme/public_html

cat > .htaccess << 'EOF'
# PHP handler
AddHandler application/x-httpd-php82 .php

# Laravel rewrite
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF
```

## Expected Results
- ✅ Admin panel loads without 403 errors
- ✅ Livewire assets load properly
- ✅ Filament admin panel working
- ✅ No more path reference errors
- ✅ Browser console shows no errors

## Troubleshooting
If issues persist:

1. **Check Apache error logs**: `tail -f /usr/local/apache/logs/error_log`
2. **Clear browser cache** completely
3. **Check Livewire configuration**: `php artisan config:show livewire`
4. **Verify Filament installation**: `php artisan filament:install --panels`
5. **Test with different browsers**
