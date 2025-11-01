# .htaccess Fix for PHP 8.2

## Create .htaccess in public_html Root

Create `/home/thme/public_html/.htaccess` with the following content:

```apache
# Force PHP 8.2
AddHandler application/x-httpd-php82 .php

# Laravel rewrite rules
RewriteEngine On
RewriteRule ^(.*)$ api/public/$1 [L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Enable compression
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

# Cache static files
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
```

## Commands to Create the File

```bash
cd /home/thme/public_html

# Create the .htaccess file
cat > .htaccess << 'EOF'
# Force PHP 8.2
AddHandler application/x-httpd-php82 .php

# Laravel rewrite rules
RewriteEngine On
RewriteRule ^(.*)$ api/public/$1 [L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Enable compression
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

# Cache static files
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

# Set permissions
chmod 644 .htaccess

# Test Laravel
curl https://thakaa.me/
curl https://thakaa.me/admin
```

## What This Does

1. **Forces PHP 8.2**: `AddHandler application/x-httpd-php82 .php`
2. **Routes to Laravel**: `RewriteRule ^(.*)$ api/public/$1 [L]`
3. **Adds Security**: Security headers for protection
4. **Enables Compression**: Reduces file sizes
5. **Sets Caching**: Improves performance

## Expected Results

After creating this file:
- Main page: Laravel welcome page or app
- Admin panel: Filament admin interface
- API: JSON responses
- No PHP version errors
