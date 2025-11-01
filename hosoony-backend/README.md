# Hosoony Quranic Learning Management System

A comprehensive Laravel-based backend system for Quranic education management.

## Features

- **User Management**: Students, teachers, and administrators
- **Class Management**: Virtual classrooms and scheduling
- **Progress Tracking**: Daily logs and performance evaluation
- **Gamification**: Badges and points system
- **Payment Integration**: PayPal and other payment methods
- **Notifications**: SMS and email notifications
- **Admin Panel**: Filament-based admin interface
- **API**: RESTful API for mobile applications

## Technology Stack

- **Backend**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: MySQL 5.7+
- **Admin Panel**: Filament 3.x
- **Authentication**: Laravel Sanctum
- **Payments**: PayPal SDK
- **SMS**: Twilio
- **PDF**: DomPDF

## Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 5.7+
- Node.js 18+

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Thaka-International/hosoony.git
   cd hosoony
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

## Deployment

For cPanel deployment instructions, see [DEPLOYMENT.md](DEPLOYMENT.md)

## API Documentation

The API documentation is available at `/public/openapi.yaml` when the application is running.

## Default Credentials

- **Admin**: admin@hosoony.com / password
- **Teacher**: teacher.male@hosoony.com / password
- **Student**: student.female1@hosoony.com / password

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License.

## Support

For support and questions, please contact the development team.