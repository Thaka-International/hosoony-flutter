# Migration Order Fix

## Issue Fixed
The `daily_log_items` table was trying to reference `daily_logs` table, but both had the same timestamp, causing a foreign key constraint error.

## Solution Applied
Renamed `daily_log_items` migration to run AFTER `daily_logs` migration.

## Commands to Run

```bash
cd /home/thme/repos/hosoony/hosoony-backend
git pull origin master

# Reset migrations (if needed)
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate:reset --force

# Run migrations with correct order
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate --force

# Run seeders
/opt/cpanel/ea-php82/root/usr/bin/php artisan db:seed --force

# Complete setup
/opt/cpanel/ea-php82/root/usr/bin/php artisan storage:link
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## What Was Fixed
- **Migration Order**: `daily_log_items` now runs after `daily_logs`
- **Foreign Key**: Proper dependency order established
- **Database Structure**: All tables will be created in correct sequence

## After Successful Migration
Your application will be available at:
- **Main API**: https://thakaa.me/api
- **Admin Panel**: https://thakaa.me/api/admin
