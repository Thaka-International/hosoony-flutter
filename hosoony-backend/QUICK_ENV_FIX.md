# Quick .env File Fix

## Issue Found
The `.env` file is missing from `/home/thme/public_html/api/`, which is why Laravel is not working.

## Quick Fix Commands

### Step 1: Create .env File
```bash
cd /home/thme/public_html/api
cp ENV_TEMPLATE.txt .env
```

### Step 2: Update APP_URL
```bash
nano .env
```

Change the APP_URL line to:
```env
APP_URL=https://thakaa.me
```

### Step 3: Generate Application Key
```bash
/opt/cpanel/ea-php82/root/usr/bin/php artisan key:generate
```

### Step 4: Fix Storage Link
```bash
/opt/cpanel/ea-php82/root/usr/bin/php artisan storage:link
```

### Step 5: Set Permissions
```bash
chmod 644 .env
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Step 6: Test Laravel
```bash
curl https://thakaa.me/
curl https://thakaa.me/admin
```

## Expected Results
- Main page: Laravel welcome page or app
- Admin panel: Filament admin interface
- API: JSON responses

## If Still Not Working
Try the alternative approach of moving Laravel to root directory.
