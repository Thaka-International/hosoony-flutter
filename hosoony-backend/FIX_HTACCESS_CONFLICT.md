# Fix .htaccess Conflict and 405 Error

## Problem
- 405 Method Not Allowed error for POST requests to `/admin/login`
- Conflicting .htaccess files (moved `/public/.htaccess` but root `.htaccess` still exists)
- Apache restart failed due to permission issues

## Solution: Fix .htaccess Configuration

### Step 1: Update Repository
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
```

### Step 2: Check Current .htaccess Files
```bash
# Check if .htaccess exists in root
ls -la /home/thme/public_html/.htaccess

# Check if .htaccess exists in public directory
ls -la /home/thme/public_html/public/.htaccess

# Check content of root .htaccess
cat /home/thme/public_html/.htaccess
```

### Step 3: Create Proper .htaccess for Laravel Root
```bash
cd /home/thme/public_html

# Create proper .htaccess for Laravel in root directory
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

### Step 4: Remove Conflicting .htaccess from Public Directory
```bash
# Remove .htaccess from public directory if it exists
rm -f /home/thme/public_html/public/.htaccess

# Or rename it to backup
mv /home/thme/public_html/public/.htaccess.bak /home/thme/public_html/public/.htaccess.backup 2>/dev/null || true
```

### Step 5: Fix Permissions
```bash
# Fix permissions
chmod -R 755 /home/thme/public_html
find /home/thme/public_html -type f -exec chmod 644 {} \;
chown -R thme:thme /home/thme/public_html
chmod 644 /home/thme/public_html/.htaccess
```

### Step 6: Test PHP Execution
```bash
# Create a test PHP file
echo "<?php echo 'PHP is working: ' . phpversion(); ?>" > /home/thme/public_html/test.php

# Test PHP execution
curl https://thakaa.me/test.php

# Remove test file
rm /home/thme/public_html/test.php
```

### Step 7: Test Admin Routes
```bash
# Test admin route (GET)
curl -I https://thakaa.me/admin

# Test admin login page (GET)
curl -I https://thakaa.me/admin/login

# Test with full response
curl https://thakaa.me/admin/login
```

### Step 8: Test POST Request
```bash
# Test POST request to admin login
curl -X POST https://thakaa.me/admin/login -H "Content-Type: application/x-www-form-urlencoded" -d "email=test@example.com&password=test"
```

### Step 9: Check Browser Console
Open https://thakaa.me/admin in browser and check:
- No more 405 Method Not Allowed errors
- No more 403 Forbidden errors
- Admin login page loads properly
- POST requests work correctly

## Alternative: Try Different PHP Handler
If PHP 8.2 handler doesn't work:

```bash
cat > /home/thme/public_html/.htaccess <<'EOF'
# PHP Handler for cPanel
AddHandler application/x-httpd-php81 .php

# Laravel-friendly .htaccess for cPanel
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes +FollowSymLinks
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

## Expected Results
- ✅ No more 405 Method Not Allowed errors
- ✅ POST requests to admin login work
- ✅ PHP files execute properly
- ✅ Admin panel loads without errors
- ✅ Livewire assets load properly
- ✅ Filament admin panel working

## Troubleshooting
If issues persist:

1. **Check Apache error logs**: `tail -f /usr/local/apache/logs/error_log`
2. **Clear browser cache** completely
3. **Try incognito/private browsing mode**
4. **Test with different browsers**
5. **Contact hosting provider** if PHP handlers are not working
