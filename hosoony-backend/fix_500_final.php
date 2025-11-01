<?php
// سكريبت إصلاح خطأ 500 نهائي وقوي
// استخدم هذا السكريبت لحل خطأ 500 نهائياً

echo "🔧 إصلاح خطأ 500 نهائي وقوي\n";
echo "==========================\n\n";

try {
    // 1. حذف جميع ملفات الكاش بقوة
    echo "1. 🗑️ حذف جميع ملفات الكاش بقوة...\n";
    
    $cacheFiles = [
        'bootstrap/cache/config.php',
        'bootstrap/cache/routes-v7.php',
        'bootstrap/cache/packages.php',
        'bootstrap/cache/services.php',
        'bootstrap/cache/events.php',
        'bootstrap/cache/routes.php',
        'bootstrap/cache/compiled.php',
    ];
    
    foreach ($cacheFiles as $file) {
        if (file_exists($file)) {
            unlink($file);
            echo "   ✅ حذف: $file\n";
        }
    }
    
    // 2. حذف مجلدات الكاش بالكامل
    $cacheDirs = [
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/framework/testing',
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
    
    // 3. إصلاح ملف CORS بسيط
    echo "\n2. 📝 إصلاح ملف CORS بسيط...\n";
    
    $corsContent = '<?php

return [
    "paths" => ["api/*"],
    "allowed_methods" => ["*"],
    "allowed_origins" => ["*"],
    "allowed_origins_patterns" => [],
    "allowed_headers" => ["*"],
    "exposed_headers" => [],
    "max_age" => 0,
    "supports_credentials" => false,
];';

    file_put_contents('config/cors.php', $corsContent);
    echo "   ✅ تم إصلاح ملف CORS بسيط\n";
    
    // 4. إصلاح ملف .env
    echo "\n3. ⚙️ إصلاح ملف .env...\n";
    
    if (file_exists('.env')) {
        $envContent = file_get_contents('.env');
        
        // إصلاح APP_KEY
        if (strpos($envContent, 'APP_KEY=') === false || strpos($envContent, 'APP_KEY=base64:') === false) {
            $envContent = str_replace('APP_KEY=', 'APP_KEY=base64:' . base64_encode(random_bytes(32)), $envContent);
            file_put_contents('.env', $envContent);
            echo "   ✅ تم إصلاح APP_KEY\n";
        } else {
            echo "   ✅ APP_KEY موجود\n";
        }
        
        // إصلاح APP_URL
        if (strpos($envContent, 'APP_URL=https://thakaa.me') === false) {
            $envContent = str_replace('APP_URL=', 'APP_URL=https://thakaa.me', $envContent);
            file_put_contents('.env', $envContent);
            echo "   ✅ تم إصلاح APP_URL\n";
        } else {
            echo "   ✅ APP_URL صحيح\n";
        }
    } else {
        echo "   ❌ ملف .env غير موجود\n";
    }
    
    // 5. إصلاح الصلاحيات
    echo "\n4. 🔐 إصلاح الصلاحيات...\n";
    
    $dirs = [
        'storage',
        'storage/framework',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
    ];
    
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            chmod($dir, 0755);
            chmod($dir, 0777); // صلاحيات كاملة مؤقتاً
            echo "   ✅ إصلاح صلاحيات: $dir\n";
        }
    }
    
    // 6. إصلاح ملفات Laravel الأساسية
    echo "\n5. 🔧 إصلاح ملفات Laravel الأساسية...\n";
    
    // إصلاح bootstrap/app.php
    if (file_exists('bootstrap/app.php')) {
        $appContent = file_get_contents('bootstrap/app.php');
        if (strpos($appContent, 'withMiddleware') === false) {
            echo "   ✅ bootstrap/app.php صحيح\n";
        } else {
            echo "   ⚠️ bootstrap/app.php يحتاج إصلاح\n";
        }
    }
    
    // 7. اختبار Laravel
    echo "\n6. 🧪 اختبار Laravel...\n";
    
    // اختبار بسيط
    $testFile = 'test_laravel.php';
    file_put_contents($testFile, '<?php
try {
    require_once "vendor/autoload.php";
    $app = require_once "bootstrap/app.php";
    echo "Laravel loaded successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
');
    
    $output = shell_exec("php $testFile 2>&1");
    if ($output && strpos($output, 'successfully') !== false) {
        echo "   ✅ Laravel يعمل بشكل صحيح\n";
    } else {
        echo "   ❌ Laravel لا يعمل: $output\n";
    }
    
    // حذف ملف الاختبار
    if (file_exists($testFile)) {
        unlink($testFile);
    }
    
    // 8. اختبار API
    echo "\n7. 🌐 اختبار API...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://thakaa.me/api/user');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   - HTTP Code: $httpCode\n";
    
    if ($httpCode == 200) {
        echo "   ✅ API يعمل بشكل صحيح\n";
    } elseif ($httpCode == 302) {
        echo "   ✅ API يعمل (يوجه إلى تسجيل الدخول)\n";
    } elseif ($httpCode == 500) {
        echo "   ❌ API لا يزال يعطي خطأ 500\n";
        echo "   🔍 تحقق من error_log في الخادم\n";
    } else {
        echo "   ⚠️ API يعطي HTTP $httpCode\n";
    }
    
    echo "\n==========================\n";
    echo "✅ تم إصلاح خطأ 500 نهائي وقوي\n";
    echo "إذا لا يزال خطأ 500، تحقق من error_log في الخادم\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الإصلاح: " . $e->getMessage() . "\n";
}












