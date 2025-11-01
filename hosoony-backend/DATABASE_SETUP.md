# Database Setup for thakaa.me

## Current Issue
Database connection failed: `Access denied for user 'hosoony_user'@'localhost'`

## Solution: Create Database and User

### Step 1: Create Database in cPanel
1. **Login to cPanel**
2. **Go to "MySQL Databases"**
3. **Create Database:**
   - Database Name: `thme_hosoony_db` (cPanel will add your username prefix)
4. **Create User:**
   - Username: `thme_hosoony_user` (cPanel will add your username prefix)
   - Password: Choose a strong password
5. **Add User to Database:**
   - Select the user and database
   - Grant "ALL PRIVILEGES"
   - Click "Make Changes"

### Step 2: Update .env File
Edit the `.env` file with your actual database credentials:

```bash
cd /home/thme/repos/hosoony/hosoony-backend
nano .env
```

Update these lines:
```env
DB_DATABASE=thme_hosoony_db
DB_USERNAME=thme_hosoony_user
DB_PASSWORD=your_actual_password_here
```

### Step 3: Test Database Connection
```bash
/opt/cpanel/ea-php82/root/usr/bin/php artisan tinker
>>> DB::connection()->getPdo();
```

If successful, you'll see connection details. Exit with `exit`.

### Step 4: Run Migrations
```bash
/opt/cpanel/ea-php82/root/usr/bin/php artisan migrate --force
/opt/cpanel/ea-php82/root/usr/bin/php artisan db:seed --force
```

### Step 5: Complete Setup
```bash
/opt/cpanel/ea-php82/root/usr/bin/php artisan storage:link
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## Alternative: Use Existing Database
If you already have a database, just update the `.env` file with the correct credentials.

## Default Login Credentials (After Seeding)
- **Admin**: admin@hosoony.com / password
- **Teacher**: teacher.male@hosoony.com / password
- **Student**: student.female1@hosoony.com / password
