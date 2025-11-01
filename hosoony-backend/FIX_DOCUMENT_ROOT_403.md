# Fix 403 Forbidden Error - Document Root Issue

## Problem Analysis
✅ Laravel 12.33 working  
✅ Filament installed and upgraded  
✅ Admin panel provider registered  
✅ /admin routes exist and point to filament.admin.pages.dashboard  
❌ But visiting /admin gives 403 Forbidden  

**Root Cause**: Document root is set to `/public_html` instead of `/public_html/public`

## Solution Options

### Option 1: Change Document Root in cPanel (Recommended)

1. **Go to cPanel → Domains → Document Root**
2. **Change from**: `/public_html`  
3. **Change to**: `/public_html/public`
4. **Save changes**

### Option 2: Add Redirect in Root .htaccess (If you can't change document root)

If you cannot change the document root setting, add this to `/home/thme/public_html/.htaccess`:

```apache
# PHP Handler for cPanel
AddHandler application/x-httpd-php82 .php

# Redirect all requests to /public directory
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public
    RewriteRule ^(.*)$ public/$1 [L,QSA]
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
```

## Step-by-Step Implementation

### Step 1: Check Current Document Root
```bash
# Check current document root setting
ls -la /home/thme/public_html/
ls -la /home/thme/public_html/public/
```

### Step 2: Verify Laravel Structure
```bash
# Ensure Laravel files are in correct locations
ls -la /home/thme/public_html/public/index.php
ls -la /home/thme/public_html/public/.htaccess
ls -la /home/thme/public_html/vendor/
ls -la /home/thme/public_html/bootstrap/
```

### Step 3: Fix .htaccess in /public directory
```bash
# Create proper .htaccess in /public directory
cat > /home/thme/public_html/public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
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

# Security
Options -Indexes
EOF
```

### Step 4: Fix Root .htaccess (if using Option 2)
```bash
# Create root .htaccess with redirect
cat > /home/thme/public_html/.htaccess << 'EOF'
# PHP Handler for cPanel
AddHandler application/x-httpd-php82 .php

# Redirect all requests to /public directory
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public
    RewriteRule ^(.*)$ public/$1 [L,QSA]
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

### Step 5: Fix Permissions
```bash
# Set correct permissions
chmod -R 755 /home/thme/public_html
chmod -R 775 /home/thme/public_html/storage
chmod -R 775 /home/thme/public_html/bootstrap/cache
chmod 644 /home/thme/public_html/.env
chmod 644 /home/thme/public_html/public/.htaccess
chmod 644 /home/thme/public_html/.htaccess
```

### Step 6: Clear Laravel Caches
```bash
cd /home/thme/public_html

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

### Step 7: Test Application
```bash
# Test main application
curl -I https://thakaa.me/

# Test admin panel
curl -I https://thakaa.me/admin

# Test admin login
curl -I https://thakaa.me/admin/login

# Test with full response
curl https://thakaa.me/admin/login
```

## Troubleshooting

### Check Apache Error Log
```bash
# Check Apache error log for 403 errors
tail -n 20 /usr/local/apache/logs/error_log

# Look for errors like:
# client denied by server configuration: /home/thme/public_html/public/index.php
```

### Verify Document Root
```bash
# Check if document root is correct
echo "Document root should be: /home/thme/public_html/public"
echo "Current setting in cPanel should show: /public_html/public"
```

### Test PHP Execution
```bash
# Create test file in public directory
echo "<?php phpinfo(); ?>" > /home/thme/public_html/public/test.php

# Test PHP execution
curl https://thakaa.me/test.php

# Remove test file
rm /home/thme/public_html/public/test.php
```

## Expected Results

After implementing the fix:

- ✅ Main application loads: `https://thakaa.me/`
- ✅ Admin panel loads: `https://thakaa.me/admin`
- ✅ Admin login works: `https://thakaa.me/admin/login`
- ✅ No more 403 Forbidden errors
- ✅ Filament admin interface accessible

## Why This Fixes the 403 Error

The 403 Forbidden error occurs because:

1. **Document root is `/public_html`** instead of `/public_html/public`
2. **Apache tries to serve Laravel files directly** from the root directory
3. **Laravel's `index.php` is in `/public` directory**, not in root
4. **Apache blocks access** to files outside the document root

By either:
- **Changing document root** to `/public_html/public`, OR
- **Adding redirect** in root `.htaccess` to send requests to `/public`

We ensure that all web requests go through Laravel's `index.php` in the correct location.

## Next Steps

1. **Choose Option 1** (change document root) for cleanest setup
2. **Use Option 2** (redirect) if you cannot change document root
3. **Test all functionality** after implementation
4. **Monitor error logs** for any remaining issues
