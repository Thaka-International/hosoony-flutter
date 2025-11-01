# Fix PHP File Download Issue

## Issue
PHP files are being downloaded instead of executed, indicating PHP handler is not working correctly.

## Solution: Update .htaccess

### Step 1: Remove Current .htaccess
```bash
cd /home/thme/public_html
rm .htaccess
```

### Step 2: Create New .htaccess with Correct PHP Handler
```bash
cat > .htaccess << 'EOF'
# Force PHP 8.2 - Try different handler formats
<IfModule mod_php.c>
    php_value engine On
</IfModule>

# Alternative PHP handler
AddType application/x-httpd-php82 .php
AddHandler application/x-httpd-php82 .php

# Another alternative
<FilesMatch "\.php$">
    SetHandler application/x-httpd-php82
</FilesMatch>

# Laravel rewrite rules
RewriteEngine On
RewriteRule ^(.*)$ api/public/$1 [L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
EOF
```

### Step 3: Alternative - Check Available PHP Handlers
```bash
# Check what PHP handlers are available
ls -la /opt/cpanel/ea-php*/root/usr/bin/php*
```

### Step 4: Try Different PHP Handler Versions
If PHP 8.2 handler doesn't work, try:

```bash
cat > .htaccess << 'EOF'
# Try PHP 8.1
AddHandler application/x-httpd-php81 .php

# Laravel rewrite rules
RewriteEngine On
RewriteRule ^(.*)$ api/public/$1 [L]
EOF
```

### Step 5: Test PHP Execution
```bash
# Create a test PHP file
echo "<?php phpinfo(); ?>" > test.php

# Test if PHP works
curl https://thakaa.me/test.php

# Remove test file
rm test.php
```

### Step 6: Test Laravel
```bash
curl https://thakaa.me/
curl https://thakaa.me/admin
```

## Alternative: Check cPanel PHP Settings
1. **Login to cPanel**
2. **Go to "Select PHP Version"**
3. **Check current PHP version**
4. **Enable PHP extensions**
5. **Try different PHP versions if needed**

## Expected Results
- PHP files execute instead of downloading
- Laravel application loads properly
- Admin panel accessible
