# Final Document Root Fix

## Issue
Document root is set to `/home/thme/public_html/api/public` but Laravel is not handling requests properly.

## Root Cause
The document root should point to the Laravel `public` directory, but there might be an issue with the Laravel application configuration.

## Solution Steps

### Step 1: Verify Laravel Files
```bash
# Check if Laravel files are in the correct location
ls -la /home/thme/public_html/api/public/
ls -la /home/thme/public_html/api/public/index.php
```

### Step 2: Check .env File
```bash
# Verify .env file exists and has correct settings
cat /home/thme/public_html/api/.env | grep APP_URL
```

Should show: `APP_URL=https://thakaa.me`

### Step 3: Fix Storage Link
```bash
cd /home/thme/public_html/api
/opt/cpanel/ea-php82/root/usr/bin/php artisan storage:link
```

### Step 4: Set Correct Permissions
```bash
chmod -R 755 /home/thme/public_html/api/storage
chmod -R 755 /home/thme/public_html/api/bootstrap/cache
chmod 644 /home/thme/public_html/api/.env
```

### Step 5: Test Laravel Directly
```bash
cd /home/thme/public_html/api
/opt/cpanel/ea-php82/root/usr/bin/php artisan serve --host=0.0.0.0 --port=8000
```

Test: `curl http://thakaa.me:8000`

### Step 6: Alternative - Move Laravel to Root
If the above doesn't work, move Laravel to the root:

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

# Set permissions
chmod -R 755 storage
chmod -R 755 ../bootstrap/cache
```

### Step 7: Update Document Root
In cPanel, change document root back to `/public_html`

## Test After Fix
```bash
curl https://thakaa.me/
curl https://thakaa.me/admin
curl https://thakaa.me/api/v1
```

## Expected Results
- Main page: Laravel welcome page or app
- Admin panel: Filament admin interface
- API: JSON responses

## Flutter App Configuration
Update Flutter app to use:
- API URL: `https://thakaa.me/api/v1`
- Admin URL: `https://thakaa.me/admin`
