#!/bin/bash

# 🔧 سكريبت الإصلاح السريع - تطبيق حصوني القرآني
# ================================================

echo "🔧 بدء الإصلاح السريع للمشاكل المكتشفة..."
echo "=========================================="

# متغيرات الإصلاح
SERVER_PATH="/home/thme/public_html"
BACKUP_DIR="/home/thme/backups/$(date +%Y%m%d_%H%M%S)"

echo "📁 إنشاء نسخة احتياطية..."
mkdir -p "$BACKUP_DIR"
cp -r "$SERVER_PATH" "$BACKUP_DIR/"
echo "✅ تم إنشاء النسخة الاحتياطية في: $BACKUP_DIR"

echo ""
echo "🔧 إصلاح مشاكل Laravel..."
echo "========================="

cd "$SERVER_PATH"

# 1. تنظيف الكاش
echo "🧹 تنظيف الكاش..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "✅ تم تنظيف الكاش"

# 2. إصلاح إعدادات Sanctum
echo "🔐 إصلاح إعدادات Sanctum..."
cat > config/sanctum.php << 'EOF'
<?php

use Laravel\Sanctum\Sanctum;

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s%s',
        'localhost,localhost:3000,localhost:8080,127.0.0.1,127.0.0.1:8000,127.0.0.1:3000,127.0.0.1:8080,::1',
        Sanctum::currentApplicationUrlWithPort() ? ','.parse_url(Sanctum::currentApplicationUrlWithPort(), PHP_URL_HOST) : '',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    'guard' => ['web'],

    'expiration' => null,

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
EOF
echo "✅ تم إصلاح إعدادات Sanctum"

# 3. إصلاح إعدادات CORS
echo "🌐 إصلاح إعدادات CORS..."
cat > config/cors.php << 'EOF'
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost',
        'http://localhost:*',
        'http://127.0.0.1',
        'http://127.0.0.1:*',
        'http://localhost:8080',
        'http://localhost:3000',
        'http://127.0.0.1:8080',
        'http://127.0.0.1:3000',
        'https://thakaa.me',
    ],
    'allowed_origins_patterns' => [
        '/^http:\/\/localhost:\d+$/',
        '/^http:\/\/127\.0\.0\.1:\d+$/',
        '/^http:\/\/192\.168\.\d+\.\d+:\d+$/',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
EOF
echo "✅ تم إصلاح إعدادات CORS"

# 4. إصلاح middleware للـ API
echo "🛡️ إصلاح middleware للـ API..."
if [ -f "bootstrap/app.php" ]; then
    # Laravel 11
    cat > bootstrap/app.php << 'EOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
EOF
else
    # Laravel 10 أو أقل
    if [ -f "app/Http/Kernel.php" ]; then
        echo "📝 تحديث Kernel.php..."
        # إضافة middleware للـ API
        sed -i '/protected \$middlewareGroups = \[/,/\]/{
            /api => \[/,/\]/{
                s/api => \[/api => [\n            \\Laravel\\Sanctum\\Http\\Middleware\\EnsureFrontendRequestsAreStateful::class,/
            }
        }' app/Http/Kernel.php
    fi
fi
echo "✅ تم إصلاح middleware للـ API"

# 5. إصلاح معالجة الأخطاء
echo "⚠️ إصلاح معالجة الأخطاء..."
if [ -f "app/Exceptions/Handler.php" ]; then
    cat > app/Exceptions/Handler.php << 'EOF'
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $exception->errors()
                ], 422);
            }
            
            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            
            if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'message' => 'This action is unauthorized'
                ], 403);
            }
        }
        
        return parent::render($request, $exception);
    }
}
EOF
fi
echo "✅ تم إصلاح معالجة الأخطاء"

# 6. إصلاح .env
echo "⚙️ إصلاح إعدادات .env..."
if [ -f ".env" ]; then
    # إضافة إعدادات Sanctum
    if ! grep -q "SANCTUM_STATEFUL_DOMAINS" .env; then
        echo "" >> .env
        echo "# Sanctum Settings" >> .env
        echo "SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,thakaa.me" >> .env
    fi
    
    # إضافة إعدادات CORS
    if ! grep -q "CORS_ALLOWED_ORIGINS" .env; then
        echo "" >> .env
        echo "# CORS Settings" >> .env
        echo "CORS_ALLOWED_ORIGINS=http://localhost:8080,http://localhost:3000,http://127.0.0.1:8080,http://127.0.0.1:3000,https://thakaa.me" >> .env
    fi
fi
echo "✅ تم إصلاح إعدادات .env"

# 7. إصلاح صلاحيات الملفات
echo "🔒 إصلاح صلاحيات الملفات..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
echo "✅ تم إصلاح صلاحيات الملفات"

# 8. إعادة بناء الكاش
echo "🔄 إعادة بناء الكاش..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
echo "✅ تم إعادة بناء الكاش"

# 9. اختبار الاتصال
echo "🧪 اختبار الاتصال..."
echo "اختبار قاعدة البيانات..."
php artisan tinker --execute="echo 'DB Connection: ' . (DB::connection()->getPdo() ? 'OK' : 'FAILED') . PHP_EOL;"

echo "اختبار Sanctum..."
php artisan tinker --execute="echo 'Sanctum: ' . (class_exists('Laravel\\Sanctum\\Sanctum') ? 'OK' : 'FAILED') . PHP_EOL;"

echo "اختبار Routes..."
php artisan route:list --path=api | head -5

echo ""
echo "🎉 انتهى الإصلاح السريع!"
echo "======================="
echo "📊 ملخص الإصلاحات:"
echo "✅ تنظيف الكاش"
echo "✅ إصلاح إعدادات Sanctum"
echo "✅ إصلاح إعدادات CORS"
echo "✅ إصلاح middleware للـ API"
echo "✅ إصلاح معالجة الأخطاء"
echo "✅ إصلاح إعدادات .env"
echo "✅ إصلاح صلاحيات الملفات"
echo "✅ إعادة بناء الكاش"
echo ""
echo "📁 النسخة الاحتياطية: $BACKUP_DIR"
echo "🔗 اختبار API: https://thakaa.me/api/v1/ops/scheduler/last-run"
echo ""
echo "⚠️ ملاحظة: قد تحتاج إعادة تشغيل الخادم لتطبيق جميع التغييرات"
echo ""
echo "✅ الإصلاح مكتمل!"











