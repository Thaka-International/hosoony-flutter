# Hosoony Backend Deployment Script for cPanel

## Quick Setup Commands

### 1. Clone Repository
```bash
cd public_html
git clone https://github.com/Thaka-International/hosoony.git api
cd api
```

### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5. Storage Setup
```bash
php artisan storage:link
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 6. Cron Job Setup
Add this to cPanel Cron Jobs:
```bash
* * * * * php /home/USER/public_html/api/artisan schedule:run >> /dev/null 2>&1
```

## Environment Variables (.env)
Update these values in your .env file:

```env
APP_URL=https://thakaa.me/api
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
MAIL_FROM_ADDRESS="noreply@thakaa.me"
```

## Required PHP Extensions
- php-mysql
- php-mysqli
- php-pdo
- php-mbstring
- php-xml
- php-curl
- php-zip
- php-gd
- php-intl

## Default Login Credentials
- Admin: admin@hosoony.com / password
- Teacher: teacher.male@hosoony.com / password
- Student: student.female1@hosoony.com / password

## Important URLs
- Main App: https://thakaa.me/api
- Admin Panel: https://thakaa.me/api/admin
- API Docs: https://thakaa.me/api/public/openapi.yaml
- Health Check: https://thakaa.me/api/health
