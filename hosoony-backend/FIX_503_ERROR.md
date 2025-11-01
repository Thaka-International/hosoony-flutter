# Fix 503 Service Unavailable Error

## Problem
Getting 503 Service Unavailable errors, which indicates PHP-FPM is not running or not configured properly.

## Solution: Diagnose and Fix PHP-FPM

### Step 1: Check PHP-FPM Status
```bash
# Check if PHP-FPM is running
ps aux | grep php-fpm

# Check PHP-FPM processes
systemctl status php-fpm
```

### Step 2: Check PHP-FPM Configuration
```bash
# Check PHP-FPM configuration files
ls -la /opt/cpanel/ea-php82/root/etc/php-fpm.d/

# Check PHP-FPM main config
cat /opt/cpanel/ea-php82/root/etc/php-fpm.conf
```

### Step 3: Try Alternative PHP Handler
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

### Step 4: Test PHP Execution
```bash
# Create test PHP file
echo "<?php echo 'PHP is working: ' . phpversion(); ?>" > /home/thme/public_html/phpinfo.php

# Test PHP execution
curl https://thakaa.me/phpinfo.php

# Remove test file
rm /home/thme/public_html/phpinfo.php
```

### Step 5: Check Laravel Logs
```bash
# Check Laravel logs for errors
tail -f /home/thme/public_html/storage/logs/laravel.log

# Check if logs directory exists
ls -la /home/thme/public_html/storage/logs/
```

### Step 6: Check Apache Error Logs
```bash
# Check Apache error logs
tail -f /usr/local/apache/logs/error_log

# Or check cPanel error logs
tail -f /home/thme/public_html/error_log
```

### Step 7: Test Laravel Application
```bash
# Test main application
curl https://thakaa.me/

# Test admin panel
curl https://thakaa.me/admin

# Test API
curl https://thakaa.me/api/v1
```

### Step 8: Check PHP Version in cPanel
```bash
# Check PHP version
php -v

# Check PHP configuration
php -m | grep -E "(mysql|pdo|mbstring|xml|curl|zip|gd|intl)"
```

## Alternative: Use PHP 8.1 Handler
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

1. **Contact hosting provider** to enable PHP-FPM
2. **Check cPanel PHP version settings**
3. **Verify Apache modules are enabled**
4. **Check file permissions**
5. **Review Laravel logs for specific errors**
