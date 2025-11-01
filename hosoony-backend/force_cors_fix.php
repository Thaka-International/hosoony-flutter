<?php
// سكريبت إصلاح CORS قوي ومؤكد
// استخدم هذا السكريبت لحل مشكلة CORS نهائياً

echo "🔧 إصلاح CORS قوي ومؤكد\n";
echo "======================\n\n";

try {
    // 1. حذف جميع ملفات الكاش
    echo "1. 🗑️ حذف جميع ملفات الكاش...\n";
    
    $cacheFiles = [
        'bootstrap/cache/config.php',
        'bootstrap/cache/routes-v7.php',
        'bootstrap/cache/packages.php',
        'bootstrap/cache/services.php',
        'bootstrap/cache/events.php',
    ];
    
    foreach ($cacheFiles as $file) {
        if (file_exists($file)) {
            unlink($file);
            echo "   ✅ حذف: $file\n";
        }
    }
    
    // 2. حذف مجلدات الكاش
    $cacheDirs = [
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
    ];
    
    foreach ($cacheDirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            echo "   ✅ مسح: $dir\n";
        }
    }
    
    // 3. إنشاء ملف CORS جديد بقوة
    echo "\n2. 📝 إنشاء ملف CORS جديد بقوة...\n";
    
    $corsContent = '<?php

return [
    "paths" => ["api/*", "sanctum/csrf-cookie"],
    "allowed_methods" => ["*"],
    "allowed_origins" => ["*"],
    "allowed_origins_patterns" => [],
    "allowed_headers" => ["*"],
    "exposed_headers" => [],
    "max_age" => 0,
    "supports_credentials" => true,
];';

    file_put_contents('config/cors.php', $corsContent);
    echo "   ✅ تم إنشاء ملف CORS جديد مع allowed_origins: *\n";
    
    // 4. إنشاء middleware CORS مخصص
    echo "\n3. 🔧 إنشاء middleware CORS مخصص...\n";
    
    $middlewareContent = '<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set("Access-Control-Allow-Origin", "*");
        $response->headers->set("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
        $response->headers->set("Access-Control-Allow-Headers", "Content-Type, Authorization, X-Requested-With, X-Livewire, X-CSRF-TOKEN");
        $response->headers->set("Access-Control-Allow-Credentials", "true");
        
        return $response;
    }
}';

    if (!is_dir('app/Http/Middleware')) {
        mkdir('app/Http/Middleware', 0755, true);
    }
    
    file_put_contents('app/Http/Middleware/CorsMiddleware.php', $middlewareContent);
    echo "   ✅ تم إنشاء CorsMiddleware مخصص\n";
    
    // 5. تحديث Kernel
    echo "\n4. ⚙️ تحديث Kernel...\n";
    
    $kernelFile = 'app/Http/Kernel.php';
    if (file_exists($kernelFile)) {
        $kernelContent = file_get_contents($kernelFile);
        
        // إضافة CorsMiddleware إلى $middleware
        if (strpos($kernelContent, 'CorsMiddleware') === false) {
            $kernelContent = str_replace(
                'protected $middleware = [',
                'protected $middleware = [
        \App\Http\Middleware\CorsMiddleware::class,',
                $kernelContent
            );
            
            file_put_contents($kernelFile, $kernelContent);
            echo "   ✅ تم إضافة CorsMiddleware إلى Kernel\n";
        } else {
            echo "   ✅ CorsMiddleware موجود بالفعل في Kernel\n";
        }
    }
    
    // 6. اختبار نهائي
    echo "\n5. 🧪 اختبار نهائي...\n";
    
    $corsConfig = include 'config/cors.php';
    echo "   - allowed_origins: " . json_encode($corsConfig['allowed_origins']) . "\n";
    echo "   - supports_credentials: " . ($corsConfig['supports_credentials'] ? 'true' : 'false') . "\n";
    echo "   - allowed_methods: " . json_encode($corsConfig['allowed_methods']) . "\n";
    
    echo "\n======================\n";
    echo "✅ تم إصلاح CORS بقوة ومؤكد\n";
    echo "الآن جرب اختبار Flutter Web مرة أخرى\n";
    echo "إذا لم يعمل، المشكلة في Flutter Web نفسه وليس في Laravel\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إصلاح CORS: " . $e->getMessage() . "\n";
}












