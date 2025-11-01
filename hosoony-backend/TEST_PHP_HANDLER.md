# Test PHP Handler and Fix 405 Error

## Status
✅ .htaccess file created with PHP handler
✅ API routes are properly defined
❌ Still getting 405 Method Not Allowed error

## Solution: Test and Fix PHP Handler

### Step 1: Test PHP Execution
```bash
# Create a test PHP file
echo "<?php echo 'PHP is working: ' . phpversion(); ?>" > /home/thme/public_html/test.php

# Test PHP execution
curl https://thakaa.me/test.php

# Remove test file
rm /home/thme/public_html/test.php
```

### Step 2: Test Admin Routes
```bash
# Test admin route
curl -I https://thakaa.me/admin

# Test admin login page
curl -I https://thakaa.me/admin/login

# Test with full response
curl https://thakaa.me/admin/login
```

### Step 3: Check Current .htaccess
```bash
# Verify .htaccess content
cat /home/thme/public_html/.htaccess
```

### Step 4: Try Alternative PHP Handler
If PHP 8.2 handler doesn't work, try PHP 8.1:

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

### Step 5: Try Generic PHP Handler
If specific version handlers don't work:

```bash
cat > /home/thme/public_html/.htaccess <<'EOF'
# PHP Handler for cPanel
AddHandler application/x-httpd-php .php

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

### Step 6: Test API Endpoints
```bash
# Test API endpoint
curl -I https://thakaa.me/api/v1

# Test specific API route
curl -I https://thakaa.me/api/v1/me
```

### Step 7: Check Apache Error Logs
```bash
# Check Apache error logs
tail -f /usr/local/apache/logs/error_log
```

### Step 8: Check Browser Console
Open https://thakaa.me/admin in browser and check:
- No more 405 Method Not Allowed errors
- PHP files execute properly
- Admin login page loads properly
- All assets (CSS, JS) load correctly

## Expected Results
- ✅ PHP files execute properly
- ✅ No more 405 Method Not Allowed errors
- ✅ Admin panel loads without errors
- ✅ API endpoints respond correctly
- ✅ Livewire assets load properly
- ✅ Filament admin panel working

## Troubleshooting
If issues persist:

1. **Check cPanel PHP version**: Go to "Select PHP Version" and ensure PHP 8.2 is selected
2. **Check Apache modules**: Ensure mod_rewrite and mod_php are enabled
3. **Clear browser cache** completely
4. **Try incognito/private browsing mode**
5. **Test with different browsers**
6. **Contact hosting provider** if PHP handlers are not working
