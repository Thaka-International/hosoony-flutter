# Directory Structure Check

## Current Issue
Getting 301 redirect instead of Laravel application response.

## Commands to Check Current Setup

```bash
# Check current directory
pwd

# Check if files are in public_html
ls -la /home/thme/public_html/

# Check if api directory exists
ls -la /home/thme/public_html/api/

# Check Laravel files
ls -la /home/thme/public_html/api/public/

# Check if .env exists
ls -la /home/thme/public_html/api/.env

# Check Laravel artisan
/opt/cpanel/ea-php82/root/usr/bin/php /home/thme/public_html/api/artisan --version
```

## Expected Structure
```
/home/thme/public_html/api/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← This should contain index.php
│   ├── index.php
│   ├── .htaccess
│   └── ...
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
└── composer.json
```

## If Files Are Missing
```bash
# Copy from repo to public_html
cp -r /home/thme/repos/hosoony/hosoony-backend/* /home/thme/public_html/api/

# Set permissions
chmod -R 755 /home/thme/public_html/api
chmod -R 755 /home/thme/public_html/api/storage
chmod -R 755 /home/thme/public_html/api/bootstrap/cache
```

## Test Laravel Directly
```bash
cd /home/thme/public_html/api
/opt/cpanel/ea-php82/root/usr/bin/php artisan serve --host=0.0.0.0 --port=8000
```

Then test: `curl http://thakaa.me:8000`
