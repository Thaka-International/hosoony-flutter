# Fix PHP File Download Issue - Server Not Executing PHP

## Problem Analysis
❌ Server is downloading `login (1)` file instead of executing PHP  
❌ PHP files are being served as downloads  
❌ PHP handler is not working correctly  
❌ Back to 405 Method Not Allowed  

**Root Cause**: The PHP handler in `.htaccess` is not working, so Apache is serving PHP files as downloads instead of executing them.

## Step-by-Step Fix

### Step 1: Check Current PHP Handler
```bash
cd /home/thme/public_html

# Check current .htaccess
cat .htaccess

# Check if PHP handler is correct
grep -n "AddHandler\|SetHandler\|AddType" .htaccess
```

### Step 2: Try Different PHP Handler Formats
```bash
cd /home/thme/public_html

# Option 1: Try AddHandler with different syntax
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
EOF
```

### Step 3: Test PHP Execution
```bash
# Create test PHP file
echo "<?php phpinfo(); ?>" > /home/thme/public_html/test.php

# Test PHP execution
curl https://thakaa.me/test.php

# If it downloads instead of executing, try next option
rm /home/thme/public_html/test.php
```

### Step 4: Try Alternative PHP Handler
```bash
cd /home/thme/public_html

# Option 2: Try FilesMatch
cat > .htaccess << 'EOF'
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
EOF
```

### Step 5: Test PHP Execution Again
```bash
# Create test PHP file
echo "<?php phpinfo(); ?>" > /home/thme/public_html/test.php

# Test PHP execution
curl https://thakaa.me/test.php

# If it downloads instead of executing, try next option
rm /home/thme/public_html/test.php
```

### Step 6: Try AddType
```bash
cd /home/thme/public_html

# Option 3: Try AddType
cat > .htaccess << 'EOF'
AddType application/x-httpd-php82 .php

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
EOF
```

### Step 7: Test PHP Execution
```bash
# Create test PHP file
echo "<?php phpinfo(); ?>" > /home/thme/public_html/test.php

# Test PHP execution
curl https://thakaa.me/test.php

# If it downloads instead of executing, try next option
rm /home/thme/public_html/test.php
```

### Step 8: Try Without PHP Handler
```bash
cd /home/thme/public_html

# Option 4: Try without explicit PHP handler
cat > .htaccess << 'EOF'
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
EOF
```

### Step 9: Test PHP Execution
```bash
# Create test PHP file
echo "<?php phpinfo(); ?>" > /home/thme/public_html/test.php

# Test PHP execution
curl https://thakaa.me/test.php

# If it downloads instead of executing, try next option
rm /home/thme/public_html/test.php
```

### Step 10: Check cPanel PHP Settings
```bash
# Check PHP version
php -v

# Check PHP modules
php -m | grep -E "(mysql|pdo|mbstring|xml|curl|zip|gd|intl)"

# Check if PHP is working from command line
php -r "echo 'PHP is working';"
```

## Troubleshooting

### Check Apache Error Log
```bash
# Check Apache error log
tail -n 20 /usr/local/apache/logs/error_log

# Look for errors like:
# - "No input file specified"
# - "Primary script unknown"
# - "File does not exist"
```

### Check File Permissions
```bash
# Check file permissions
ls -la /home/thme/public_html/index.php
ls -la /home/thme/public_html/.htaccess

# Fix permissions if needed
chmod 644 /home/thme/public_html/index.php
chmod 644 /home/thme/public_html/.htaccess
```

### Check PHP Configuration
```bash
# Check PHP configuration
php -i | grep -E "(cgi|fpm|handler)"

# Check if PHP is working
php -r "echo phpinfo();"
```

## Alternative: Check cPanel Settings

The issue might be in cPanel settings:

1. **Go to cPanel → Select PHP Version**
2. **Make sure PHP 8.2 is selected**
3. **Check if PHP extensions are enabled**
4. **Try switching to a different PHP version temporarily**

## Alternative: Check Document Root

```bash
# Check if document root is correct
ls -la /home/thme/public_html/
ls -la /home/thme/public_html/index.php
ls -la /home/thme/public_html/vendor/
ls -la /home/thme/public_html/bootstrap/
```

## Expected Results

After implementing the fix:

- ✅ PHP files execute instead of downloading
- ✅ `https://thakaa.me/test.php` shows PHP info instead of downloading
- ✅ Admin panel loads properly
- ✅ Login form works
- ✅ No more 405 Method Not Allowed errors

## Quick Test

Run this command to test if PHP is working:

```bash
# Create test file
echo "<?php echo 'PHP is working!'; ?>" > /home/thme/public_html/test.php

# Test PHP execution
curl https://thakaa.me/test.php

# Should show "PHP is working!" not download the file
rm /home/thme/public_html/test.php
```

## Most Likely Solution

Try the **AddHandler** format first:

```bash
cd /home/thme/public_html

cat > .htaccess << 'EOF'
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
EOF
```

Then test:
```bash
echo "<?php echo 'PHP is working!'; ?>" > /home/thme/public_html/test.php
curl https://thakaa.me/test.php
rm /home/thme/public_html/test.php
```

If this shows "PHP is working!" instead of downloading, then the fix is working!
