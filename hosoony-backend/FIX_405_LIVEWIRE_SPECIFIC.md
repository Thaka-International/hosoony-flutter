# Fix 405 Method Not Allowed - Livewire/Filament Issue

## Current Status
✅ Admin panel loads: `https://thakaa.me/admin` (302 redirect to login)  
✅ GET requests work  
❌ POST requests fail with 405 Method Not Allowed  
❌ Livewire/Filament forms not working  

## Root Cause
The 405 error occurs because Livewire/Filament uses AJAX POST requests that need special handling. The issue is likely:

1. **Missing Livewire route handling**
2. **Incorrect PHP handler for AJAX requests**
3. **Missing Livewire middleware**

## Step-by-Step Fix

### Step 1: Check Livewire Routes
```bash
cd /home/thme/public_html

# Check if Livewire routes are registered
php artisan route:list | grep livewire

# Check all routes
php artisan route:list
```

### Step 2: Fix .htaccess for Livewire
```bash
cd /home/thme/public_html

# Create .htaccess optimized for Livewire/Filament
cat > .htaccess << 'EOF'
# PHP Handler for cPanel
AddHandler application/x-httpd-php82 .php

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes +FollowSymLinks
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers (permissive for Livewire)
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    # Allow Livewire requests
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, X-Livewire"
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
    AddOutputFilterByType DEFLATE application/json
</IfModule>
EOF
```

### Step 3: Check Livewire Configuration
```bash
cd /home/thme/public_html

# Check Livewire configuration
php artisan config:show livewire

# Check if Livewire is properly installed
php artisan livewire:version
```

### Step 4: Publish Livewire Assets Again
```bash
# Publish Livewire assets
php artisan livewire:publish --assets

# Check if assets are published
ls -la /home/thme/public_html/public/vendor/livewire/
```

### Step 5: Clear All Caches
```bash
cd /home/thme/public_html

# Clear all caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 6: Test Livewire Endpoint
```bash
# Test Livewire endpoint directly
curl -X POST https://thakaa.me/livewire/message \
  -H "Content-Type: application/json" \
  -H "X-Livewire: true" \
  -d '{"fingerprint":{"id":"test","name":"test","locale":"en","path":"/admin/login","method":"GET","v":"3"},"serverMemo":{"children":[],"errors":[],"htmlHash":"","data":[],"dataMeta":[],"checksum":"test"},"updates":[]}'

# Test with verbose output
curl -v -X POST https://thakaa.me/livewire/message
```

### Step 7: Check Filament Configuration
```bash
# Check Filament configuration
php artisan config:show filament

# Check Filament routes
php artisan route:list | grep filament

# Check if Filament is properly installed
php artisan filament:version
```

### Step 8: Alternative PHP Handler
```bash
cd /home/thme/public_html

# Try alternative PHP handler format
cat > .htaccess << 'EOF'
# Alternative PHP Handler
<FilesMatch "\.php$">
    SetHandler application/x-httpd-php82
</FilesMatch>

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes +FollowSymLinks
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Minimal headers for testing
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
</IfModule>
EOF
```

### Step 9: Test Step by Step
```bash
# Test 1: Basic GET request
curl -I https://thakaa.me/admin

# Test 2: Admin login page
curl -I https://thakaa.me/admin/login

# Test 3: Livewire endpoint
curl -I https://thakaa.me/livewire/message

# Test 4: POST to admin login
curl -X POST https://thakaa.me/admin/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "email=test@example.com&password=test"
```

## Troubleshooting

### Check Apache Error Log
```bash
# Check Apache error log
tail -n 20 /usr/local/apache/logs/error_log

# Check Laravel logs
tail -n 20 /home/thme/public_html/storage/logs/laravel.log
```

### Check Livewire Routes
```bash
# List all routes
php artisan route:list

# Check specific Livewire routes
php artisan route:list | grep -i livewire
```

### Test PHP Execution
```bash
# Create test file for POST requests
cat > /home/thme/public_html/test_post.php << 'EOF'
<?php
header('Content-Type: application/json');
echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'post_data' => $_POST,
    'raw_data' => file_get_contents('php://input')
]);
?>
EOF

# Test POST request
curl -X POST https://thakaa.me/test_post.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "test=data"

# Remove test file
rm /home/thme/public_html/test_post.php
```

## Expected Results

After implementing the fix:

- ✅ GET requests work: `https://thakaa.me/admin`
- ✅ POST requests work: `https://thakaa.me/admin/login`
- ✅ Livewire components load properly
- ✅ Filament admin interface accessible
- ✅ No more 405 Method Not Allowed errors

## Quick Test

Run this command to test if the fix worked:

```bash
curl -X POST https://thakaa.me/admin/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "email=test@example.com&password=test"
```

If this returns a proper response (not 405), then the fix is working!
