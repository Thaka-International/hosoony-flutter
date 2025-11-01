# Copy .env File Fix

## Issue
The `.env` file exists in `/home/thme/repos/hosoony/hosoony-backend/.env` but needs to be in `/home/thme/public_html/api/.env` for the web application to work.

## Quick Fix Commands

### Step 1: Copy .env File
```bash
cp /home/thme/repos/hosoony/hosoony-backend/.env /home/thme/public_html/api/.env
```

### Step 2: Update APP_URL
```bash
cd /home/thme/public_html/api
nano .env
```

Change the APP_URL line from:
```env
APP_URL=https://thakaa.me/api
```
to:
```env
APP_URL=https://thakaa.me
```

Save and exit (Ctrl+X, then Y, then Enter)

### Step 3: Set Correct Permissions
```bash
chmod 644 .env
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Step 4: Fix Storage Link
```bash
/opt/cpanel/ea-php82/root/usr/bin/php artisan storage:link
```

### Step 5: Test Laravel
```bash
curl https://thakaa.me/
curl https://thakaa.me/admin
curl https://thakaa.me/api/v1
```

## Expected Results
- Main page: Laravel welcome page or app
- Admin panel: Filament admin interface  
- API: JSON responses

## Alternative: Symlink (if copy doesn't work)
```bash
cd /home/thme/public_html/api
ln -s /home/thme/repos/hosoony/hosoony-backend/.env .env
```
