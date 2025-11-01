# Move Laravel to Default Root Directory

## Problem
cPanel keeps reading from the default root directory (`/home/thme/public_html/`) instead of `/home/thme/public_html/api/public/`, causing PHP execution issues.

## Solution: Move Laravel Files to Default Root

### Step 1: Update Repository
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
```

### Step 2: Backup Current Files
```bash
# Create backup of current structure
cp -r /home/thme/public_html/api /home/thme/public_html/api_backup_$(date +%Y%m%d_%H%M%S)
```

### Step 3: Move Laravel Files to Default Root
```bash
# Move Laravel application files to default root
cp -r /home/thme/public_html/api/* /home/thme/public_html/

# Move public directory contents to root
cp -r /home/thme/public_html/api/public/* /home/thme/public_html/

# Remove the api directory (no longer needed)
rm -rf /home/thme/public_html/api
```

### Step 4: Create Final .htaccess
```bash
cd /home/thme/public_html

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

### Step 5: Update Laravel Configuration
```bash
# Update bootstrap/paths.php or config/app.php if needed
cd /home/thme/public_html

# Check if .env exists and update paths
if [ -f .env ]; then
    # Update APP_URL if needed
    sed -i 's|APP_URL=.*|APP_URL=https://thakaa.me|' .env
fi
```

### Step 6: Set Correct Permissions
```bash
# Set permissions for Laravel
chmod -R 755 /home/thme/public_html
chmod -R 777 /home/thme/public_html/storage
chmod -R 777 /home/thme/public_html/bootstrap/cache
chmod 644 /home/thme/public_html/.env
```

### Step 7: Test PHP-FPM
```bash
# Create test PHP file
echo "<?php echo 'PHP-FPM is working: ' . phpversion(); ?>" > /home/thme/public_html/phpinfo.php

# Test PHP execution
curl https://thakaa.me/phpinfo.php

# Remove test file
rm /home/thme/public_html/phpinfo.php
```

### Step 8: Test Laravel Application
```bash
# Test main application
curl https://thakaa.me/

# Test admin panel
curl https://thakaa.me/admin

# Test API
curl https://thakaa.me/api/v1
```

### Step 9: Verify File Structure
```bash
# Check the new structure
ls -la /home/thme/public_html/

# Verify Laravel files are in root
ls -la /home/thme/public_html/app/
ls -la /home/thme/public_html/config/
ls -la /home/thme/public_html/database/
ls -la /home/thme/public_html/vendor/
```

## Final Directory Structure
```
/home/thme/public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/
├── .env
├── .htaccess
├── artisan
├── composer.json
├── composer.lock
├── index.php
└── package.json
```

## Expected Results
- PHP files execute properly
- Laravel application loads from root
- Admin panel accessible
- API endpoints respond
- No more document root issues

## Troubleshooting
If issues persist:

### Option 1: Alternative PHP Handler
```bash
cat > .htaccess << 'EOF'
# Traditional PHP handler
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

### Option 2: Check PHP Version
```bash
# Verify PHP version in cPanel
# Go to "Select PHP Version" and ensure PHP 8.2 is selected
```

## After Migration
Your application will be accessible at:
- **Main Application**: https://thakaa.me/
- **Admin Panel**: https://thakaa.me/admin
- **API**: https://thakaa.me/api/v1
- **Flutter App**: Ready for deployment
