# Clean Migration Fix

## Issue
The `daily_log_items` table already exists from the previous failed migration attempt, causing a "Table already exists" error.

## Solution: Clean Database and Re-run Migrations

### Option 1: Reset All Migrations (Recommended)
```bash
cd /home/thme/repos/hosoony/hosoony-backend

# Reset all migrations
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate:reset --force

# Run all migrations fresh
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate --force

# Run seeders
/opt/cpanel/ea-php82/root/usr/bin/php artisan db:seed --force
```

### Option 2: Manual Table Drop (If Option 1 fails)
```bash
# Connect to MySQL and drop the problematic table
mysql -u thme_hosoony_user -p thme_hosoony_db

# In MySQL prompt:
DROP TABLE IF EXISTS daily_log_items;
DROP TABLE IF EXISTS daily_logs;
EXIT;

# Then run migrations
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate --force
/opt/cpanel/ea-php82/root/usr/bin/php artisan db:seed --force
```

### Option 3: Fresh Database (If all else fails)
```bash
# Drop and recreate the entire database in cPanel
# Then run:
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate --force
/opt/cpanel/ea-php82/root/usr/bin/php artisan db:seed --force
```

## Complete Setup After Migration
```bash
/opt/cpanel/ea-php82/root/usr/bin/php artisan storage:link
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## After Successful Migration
Your application will be available at:
- **Main API**: https://thakaa.me/api
- **Admin Panel**: https://thakaa.me/api/admin
