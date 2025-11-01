# Deployment Troubleshooting - 404 Error

## Issue
Getting 404 error when accessing https://thakaa.me/api

## Possible Causes & Solutions

### 1. Files Not in Correct Location
The Laravel application needs to be in the correct directory structure.

**Check current location:**
```bash
pwd
ls -la
```

**Expected structure:**
```
/home/thme/public_html/api/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
└── composer.json
```

### 2. Move Files to Correct Location
If files are in `/home/thme/repos/hosoony/hosoony-backend/`, move them to public_html:

```bash
# Create api directory in public_html
mkdir -p /home/thme/public_html/api

# Copy all files
cp -r /home/thme/repos/hosoony/hosoony-backend/* /home/thme/public_html/api/

# Set correct permissions
chmod -R 755 /home/thme/public_html/api
chmod -R 755 /home/thme/public_html/api/storage
chmod -R 755 /home/thme/public_html/api/bootstrap/cache
```

### 3. Update .env File Location
```bash
cd /home/thme/public_html/api
cp .env.example .env
nano .env
```

Update the APP_URL:
```env
APP_URL=https://thakaa.me/api
```

### 4. Test Local Access
```bash
cd /home/thme/public_html/api
/opt/cpanel/ea-php82/root/usr/bin/php artisan serve --host=0.0.0.0 --port=8000
```

Then test: `curl http://thakaa.me:8000`

### 5. Check Web Server Configuration
The web server needs to point to the `public` directory of Laravel.

**Create .htaccess in /home/thme/public_html/api/**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 6. Alternative: Direct Public Access
If the above doesn't work, try accessing directly:
- https://thakaa.me/api/public

## Quick Fix Commands
```bash
# Move files to correct location
mkdir -p /home/thme/public_html/api
cp -r /home/thme/repos/hosoony/hosoony-backend/* /home/thme/public_html/api/
cd /home/thme/public_html/api

# Set permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Test
curl https://thakaa.me/api/public
```

## After Fix
Your application should be accessible at:
- **Main API**: https://thakaa.me/api
- **Admin Panel**: https://thakaa.me/api/admin
