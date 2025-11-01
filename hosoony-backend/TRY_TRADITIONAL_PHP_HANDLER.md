# Try Traditional PHP Handler

## Status
PHP-FPM is running (we can see the processes), but we're getting 503 errors. Let's try the traditional PHP handler.

## Solution: Use Traditional PHP Handler

### Step 1: Update .htaccess with Traditional Handler
```bash
cd /home/thme/public_html

# Create .htaccess with traditional PHP handler
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
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'"
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

# Caching
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
EOF
```

### Step 2: Test PHP Execution
```bash
# Create test PHP file
echo "<?php echo 'PHP is working: ' . phpversion(); ?>" > /home/thme/public_html/phpinfo.php

# Test PHP execution
curl https://thakaa.me/phpinfo.php

# Remove test file
rm /home/thme/public_html/phpinfo.php
```

### Step 3: Test Laravel Application
```bash
# Test main application
curl https://thakaa.me/

# Test admin panel
curl https://thakaa.me/admin

# Test API
curl https://thakaa.me/api/v1
```

### Step 4: Check Laravel Logs
```bash
# Check Laravel logs for errors
tail -f /home/thme/public_html/storage/logs/laravel.log

# Check if logs directory exists
ls -la /home/thme/public_html/storage/logs/
```

### Step 5: Check Apache Error Logs
```bash
# Check Apache error logs
tail -f /usr/local/apache/logs/error_log

# Or check cPanel error logs
tail -f /home/thme/public_html/error_log
```

### Step 6: Check PHP Version
```bash
# Check PHP version
php -v

# Check PHP configuration
php -m | grep -E "(mysql|pdo|mbstring|xml|curl|zip|gd|intl)"
```

## Alternative: Try PHP 8.1 Handler
If PHP 8.2 is causing issues:

```bash
cd /home/thme/public_html

cat > .htaccess << 'EOF'
# PHP 8.1 handler
AddHandler application/x-httpd-php81 .php

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
EOF
```

## Expected Results
- PHP files execute properly
- Laravel application loads
- Admin panel accessible
- API endpoints respond
- No more 503 errors

## Troubleshooting
If issues persist:

1. **Check cPanel PHP version settings**
2. **Verify Apache modules are enabled**
3. **Check file permissions**
4. **Review Laravel logs for specific errors**
5. **Contact hosting provider for PHP handler configuration**
