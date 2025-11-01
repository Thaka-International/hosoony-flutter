# Fix 419 Page Expired - CSRF Token Issue

## Problem Analysis
✅ Livewire endpoint is working  
✅ POST requests are reaching Laravel  
✅ Filament is properly installed  
❌ **419 Page Expired** - CSRF token issue  

**Root Cause**: The CSRF token is missing or invalid when submitting the login form via Livewire.

## Step-by-Step Fix

### Step 1: Check CSRF Configuration
```bash
cd /home/thme/public_html

# Check CSRF configuration
php artisan config:show session
php artisan config:show csrf
```

### Step 2: Check Session Configuration
```bash
# Check session configuration
php artisan config:show session

# Check if session driver is correct
grep -n "SESSION_DRIVER\|SESSION_LIFETIME" .env
```

### Step 3: Fix .htaccess for CSRF
```bash
cd /home/thme/public_html

# Create .htaccess optimized for CSRF and Livewire
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

# Security Headers (permissive for CSRF and Livewire)
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    # Allow Livewire and CSRF requests
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With, X-Livewire, X-CSRF-TOKEN"
    # Allow cookies for CSRF
    Header always set Access-Control-Allow-Credentials "true"
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

### Step 4: Check Session Configuration
```bash
# Check session configuration
cat .env | grep -E "SESSION_|CSRF_"

# Ensure session driver is file (not database)
sed -i 's/SESSION_DRIVER=.*/SESSION_DRIVER=file/' .env
sed -i 's/SESSION_LIFETIME=.*/SESSION_LIFETIME=120/' .env
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

### Step 6: Test CSRF Token Generation
```bash
# Test CSRF token generation
curl -c cookies.txt https://thakaa.me/admin/login

# Check if CSRF token is generated
grep -o 'csrf-token[^>]*content="[^"]*"' cookies.txt

# Test with CSRF token
curl -X POST https://thakaa.me/livewire/update \
  -H "Content-Type: application/json" \
  -H "X-Livewire: true" \
  -H "X-CSRF-TOKEN: test" \
  -b cookies.txt \
  -d '{"fingerprint":{"id":"test","name":"test","locale":"en","path":"/admin/login","method":"GET","v":"3"},"serverMemo":{"children":[],"errors":[],"htmlHash":"","data":[],"dataMeta":[],"checksum":"test"},"updates":[]}'
```

### Step 7: Check Session Storage
```bash
# Check session storage directory
ls -la /home/thme/public_html/storage/framework/sessions/

# Check permissions
chmod -R 775 /home/thme/public_html/storage/framework/sessions/
```

### Step 8: Test Admin Login Page
```bash
# Test admin login page
curl -c cookies.txt https://thakaa.me/admin/login

# Check if page loads properly
curl https://thakaa.me/admin/login
```

### Step 9: Alternative Session Configuration
```bash
# Try alternative session configuration
cat >> .env << 'EOF'

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# CSRF Configuration
CSRF_COOKIE_NAME=XSRF-TOKEN
CSRF_COOKIE_PATH=/
CSRF_COOKIE_DOMAIN=null
CSRF_COOKIE_SECURE=false
CSRF_COOKIE_HTTP_ONLY=false
CSRF_COOKIE_SAME_SITE=lax
EOF
```

### Step 10: Clear Caches Again
```bash
cd /home/thme/public_html

# Clear all caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Understanding the 419 Error

### What 419 Page Expired Means:
- **CSRF token is missing** from the request
- **CSRF token has expired** (older than session lifetime)
- **Session is not working** properly
- **CSRF middleware is blocking** the request

### How Filament Login Works:
1. **GET /admin/login** → Shows login form with CSRF token
2. **POST /livewire/update** → Submits form with CSRF token
3. **CSRF validation** → Checks token validity
4. **Authentication** → Processes login

## Test the Fix

### Step 1: Test CSRF Token Generation
```bash
# Get CSRF token
curl -c cookies.txt https://thakaa.me/admin/login

# Extract CSRF token
CSRF_TOKEN=$(grep -o 'csrf-token[^>]*content="[^"]*"' cookies.txt | sed 's/.*content="\([^"]*\)".*/\1/')
echo "CSRF Token: $CSRF_TOKEN"
```

### Step 2: Test Livewire with CSRF Token
```bash
# Test Livewire with CSRF token
curl -X POST https://thakaa.me/livewire/update \
  -H "Content-Type: application/json" \
  -H "X-Livewire: true" \
  -H "X-CSRF-TOKEN: $CSRF_TOKEN" \
  -b cookies.txt \
  -d '{"fingerprint":{"id":"test","name":"test","locale":"en","path":"/admin/login","method":"GET","v":"3"},"serverMemo":{"children":[],"errors":[],"htmlHash":"","data":[],"dataMeta":[],"checksum":"test"},"updates":[]}'
```

### Step 3: Test in Browser
1. Go to `https://thakaa.me/admin/login`
2. Check browser developer tools for CSRF token
3. Enter email and password
4. Click login
5. Should work now!

## Expected Results

After implementing the fix:

- ✅ Admin login page loads: `https://thakaa.me/admin/login`
- ✅ CSRF token is generated properly
- ✅ Livewire endpoint works with CSRF token
- ✅ Login form submission works
- ✅ No more 419 Page Expired errors

## Troubleshooting

### Check Session Storage
```bash
# Check session storage
ls -la /home/thme/public_html/storage/framework/sessions/

# Check session files
cat /home/thme/public_html/storage/framework/sessions/*
```

### Check Laravel Logs
```bash
# Check Laravel logs
tail -n 20 /home/thme/public_html/storage/logs/laravel.log
```

### Check CSRF Configuration
```bash
# Check CSRF configuration
php artisan config:show csrf
php artisan config:show session
```

## Quick Test

Run this command to test if CSRF is working:

```bash
# Get CSRF token
curl -c cookies.txt https://thakaa.me/admin/login

# Test with CSRF token
curl -X POST https://thakaa.me/livewire/update \
  -H "Content-Type: application/json" \
  -H "X-Livewire: true" \
  -H "X-CSRF-TOKEN: test" \
  -b cookies.txt \
  -d '{"fingerprint":{"id":"test","name":"test","locale":"en","path":"/admin/login","method":"GET","v":"3"},"serverMemo":{"children":[],"errors":[],"htmlHash":"","data":[],"dataMeta":[],"checksum":"test"},"updates":[]}'
```

If this returns a proper response (not 419), then CSRF is working and the login should work!

## Summary

The 419 Page Expired error occurs because the CSRF token is missing or invalid. The fix is to ensure:

1. **Session storage is working** properly
2. **CSRF token is generated** correctly
3. **CSRF token is included** in Livewire requests
4. **Session configuration** is correct

Try the fix and let me know if the CSRF token is generated properly!
