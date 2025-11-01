# Fix Storage Directory Conflict

## Problem
The `storage` directory already exists in `/home/thme/public_html/` and is conflicting with the copy operation.

## Solution: Handle Directory Conflicts

### Step 1: Check Current Structure
```bash
# Check what's in the current public_html
ls -la /home/thme/public_html/

# Check what's in the api/public directory
ls -la /home/thme/public_html/api/public/
```

### Step 2: Remove Conflicting Directories
```bash
# Remove existing directories that will conflict
rm -rf /home/thme/public_html/storage
rm -rf /home/thme/public_html/bootstrap
rm -rf /home/thme/public_html/app
rm -rf /home/thme/public_html/config
rm -rf /home/thme/public_html/database
rm -rf /home/thme/public_html/resources
rm -rf /home/thme/public_html/routes
rm -rf /home/thme/public_html/tests
rm -rf /home/thme/public_html/vendor

# Remove existing Laravel files
rm -f /home/thme/public_html/artisan
rm -f /home/thme/public_html/composer.json
rm -f /home/thme/public_html/composer.lock
rm -f /home/thme/public_html/package.json
rm -f /home/thme/public_html/index.php
rm -f /home/thme/public_html/.env
```

### Step 3: Copy Laravel Files Properly
```bash
# Copy all Laravel application files
cp -r /home/thme/public_html/api/* /home/thme/public_html/

# Copy public directory contents (files only, not directories)
cp /home/thme/public_html/api/public/*.php /home/thme/public_html/ 2>/dev/null || true
cp /home/thme/public_html/api/public/*.html /home/thme/public_html/ 2>/dev/null || true
cp /home/thme/public_html/api/public/*.txt /home/thme/public_html/ 2>/dev/null || true
cp /home/thme/public_html/api/public/*.json /home/thme/public_html/ 2>/dev/null || true
cp /home/thme/public_html/api/public/*.js /home/thme/public_html/ 2>/dev/null || true
cp /home/thme/public_html/api/public/*.css /home/thme/public_html/ 2>/dev/null || true
cp /home/thme/public_html/api/public/*.ico /home/thme/public_html/ 2>/dev/null || true
cp /home/thme/public_html/api/public/*.xml /home/thme/public_html/ 2>/dev/null || true
cp /home/thme/public_html/api/public/*.yaml /home/thme/public_html/ 2>/dev/null || true

# Copy public subdirectories
cp -r /home/thme/public_html/api/public/css /home/thme/public_html/ 2>/dev/null || true
cp -r /home/thme/public_html/api/public/js /home/thme/public_html/ 2>/dev/null || true
```

### Step 4: Remove API Directory
```bash
# Remove the api directory (no longer needed)
rm -rf /home/thme/public_html/api
```

### Step 5: Create Final .htaccess
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

## Alternative: Clean Slate Approach
If you prefer a clean slate:

```bash
# Backup current public_html
mv /home/thme/public_html /home/thme/public_html_backup_$(date +%Y%m%d_%H%M%S)

# Create new public_html
mkdir -p /home/thme/public_html

# Copy everything from api directory
cp -r /home/thme/public_html_backup_*/api/* /home/thme/public_html/

# Copy public directory contents
cp -r /home/thme/public_html_backup_*/api/public/* /home/thme/public_html/

# Set permissions
chmod -R 755 /home/thme/public_html
chmod -R 777 /home/thme/public_html/storage
chmod -R 777 /home/thme/public_html/bootstrap/cache
chmod 644 /home/thme/public_html/.env
```

## Expected Results
- No more directory conflicts
- Laravel files properly moved to root
- PHP-FPM working
- Application accessible at https://thakaa.me/
