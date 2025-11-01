# Fix 403 Forbidden Error

## Status
✅ Admin panel is working with credentials: `admin@hosoony.com` / `password`
❌ Getting 403 Forbidden errors for some resources

## Solution: Fix 403 Forbidden Errors

### Step 1: Update Repository
```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master
```

### Step 2: Fix File Permissions
```bash
# Set correct permissions for all files
chmod -R 755 /home/thme/public_html
chmod -R 777 /home/thme/public_html/storage
chmod -R 777 /home/thme/public_html/bootstrap/cache
chmod 644 /home/thme/public_html/.env

# Fix specific directories that might cause 403
chmod -R 755 /home/thme/public_html/public
chmod -R 755 /home/thme/public_html/resources
chmod -R 755 /home/thme/public_html/vendor
```

### Step 3: Update .htaccess to Allow More Resources
```bash
cd /home/thme/public_html

# Update .htaccess with more permissive CSP
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

# Security headers with more permissive CSP
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; img-src 'self' data: https:; connect-src 'self' https:;"
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

### Step 4: Clear Laravel Caches
```bash
cd /home/thme/public_html

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 5: Test Admin Panel
```bash
# Test admin panel
curl https://thakaa.me/admin

# Test API endpoints
curl https://thakaa.me/api/v1
```

### Step 6: Check Browser Console
Open https://thakaa.me/admin in browser and check:
- No more 403 errors
- All resources loading properly
- Admin panel fully functional

### Step 7: Verify File Structure
```bash
# Check if all required files exist
ls -la /home/thme/public_html/storage/
ls -la /home/thme/public_html/bootstrap/cache/
ls -la /home/thme/public_html/public/
```

## Alternative: Remove CSP Completely (Less Secure)
If 403 errors persist, remove CSP completely:

```bash
cd /home/thme/public_html

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

# Basic security headers (no CSP)
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
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

## Expected Results
- ✅ Admin panel fully functional
- ✅ No more 403 Forbidden errors
- ✅ All resources loading properly
- ✅ API endpoints responding
- ✅ File permissions correct

## Troubleshooting
If issues persist:

1. **Check file permissions** for all directories
2. **Clear browser cache** and reload
3. **Check Laravel logs** for specific errors
4. **Verify all external resources** are allowed
5. **Test with different browsers** to isolate issues
