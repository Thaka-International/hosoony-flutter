# PHP-FPM Setup Guide for PHP 8.2 with Original Root

## Issue Identified
PHP-FPM was missing, which is why PHP files were being served as text instead of being executed.

## Solution: Enable PHP-FPM with Original Document Root

### Step 1: Update Repository
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
```

### Step 2: Enable PHP-FPM in cPanel
1. **Login to cPanel**
2. **Go to "Select PHP Version"**
3. **Select PHP 8.2** (or latest available)
4. **Enable PHP-FPM** (if available as an option)
5. **Enable required extensions:**
   - `php-mysql`
   - `php-mysqli`
   - `php-pdo`
   - `php-mbstring`
   - `php-xml`
   - `php-curl`
   - `php-zip`
   - `php-gd`
   - `php-intl`
6. **Click "Apply"**

### Step 3: Set Document Root to Original Location
In cPanel:
1. **Go to "Subdomains" or "Addon Domains"**
2. **Find `thakaa.me`**
3. **Change Document Root to `/public_html/api/public`**
4. **Save changes**

### Step 4: Update .htaccess in api/public for PHP-FPM
```bash
cd /home/thme/public_html/api/public

# Create .htaccess for PHP-FPM
cat > .htaccess << 'EOF'
# PHP-FPM handler for PHP 8.2
<FilesMatch "\.php$">
    SetHandler "proxy:unix:/opt/cpanel/ea-php82/root/usr/var/run/php-fpm.sock|fcgi://localhost"
</FilesMatch>

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
</IfModule>
EOF
```

### Step 5: Alternative PHP-FPM Handler
If the above doesn't work, try:
```bash
cd /home/thme/public_html/api/public

cat > .htaccess << 'EOF'
# Alternative PHP-FPM handler
<FilesMatch "\.php$">
    SetHandler "proxy:fcgi://127.0.0.1:9000"
</FilesMatch>

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

### Step 6: Test PHP-FPM
```bash
# Create test PHP file in public directory
echo "<?php echo 'PHP-FPM is working: ' . phpversion(); ?>" > /home/thme/public_html/api/public/phpinfo.php

# Test PHP execution
curl https://thakaa.me/phpinfo.php

# Remove test file
rm /home/thme/public_html/api/public/phpinfo.php
```

### Step 7: Test Laravel
```bash
curl https://thakaa.me/
curl https://thakaa.me/admin
curl https://thakaa.me/api/v1
```

### Step 8: Check PHP-FPM Status
```bash
# Check if PHP-FPM is running
ps aux | grep php-fpm

# Check PHP-FPM configuration
ls -la /opt/cpanel/ea-php82/root/etc/php-fpm.d/
```

## Expected Results
- PHP files execute properly
- Laravel application loads
- Admin panel accessible
- API endpoints respond

## Troubleshooting
If PHP-FPM still doesn't work:

### Option 1: Use Traditional PHP Handler
```bash
cd /home/thme/public_html/api/public

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
EOF
```

### Option 2: Contact Hosting Provider
If PHP-FPM is not available:
1. **Contact hosting provider**
2. **Request PHP-FPM enablement**
3. **Ask for PHP 8.2 with FPM support**

## After PHP-FPM Setup
Your application should be accessible at:
- **Main API**: https://thakaa.me/api/v1
- **Admin Panel**: https://thakaa.me/admin
- **Flutter App**: Ready for deployment

## Document Root Configuration
- **Document Root**: `/public_html/api/public`
- **PHP Version**: 8.2
- **Handler**: PHP-FPM
- **Laravel Entry Point**: `index.php` in public directory
