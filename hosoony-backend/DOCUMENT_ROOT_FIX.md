# Document Root Configuration Fix

## Issue Identified
The domain `thakaa.me` has document root `/public_html`, but Laravel application is in `/public_html/api/public/`.

## Solutions

### Option 1: Change Document Root (Recommended)
In cPanel:
1. Go to **Subdomains** or **Addon Domains**
2. Find `thakaa.me`
3. Change Document Root from `/public_html` to `/public_html/api/public`
4. Save changes

### Option 2: Move Laravel to Root
```bash
# Move Laravel files to public_html root
cd /home/thme/public_html
mv api/* .
mv api/.* . 2>/dev/null || true

# Update .env
nano .env
# Change: APP_URL=https://thakaa.me

# Fix storage link
cd public
rm storage
ln -s ../storage/app/public storage
```

### Option 3: Create Root Redirect
Create `/home/thme/public_html/index.php`:
```php
<?php
header('Location: /api/public/');
exit;
?>
```

### Option 4: Update .htaccess in Root
Create `/home/thme/public_html/.htaccess`:
```apache
RewriteEngine On
RewriteRule ^(.*)$ api/public/$1 [L]
```

## Test After Fix
```bash
curl https://thakaa.me/
curl https://thakaa.me/api
```

## Recommended Action
**Use Option 1** - Change document root to `/public_html/api/public` in cPanel.
