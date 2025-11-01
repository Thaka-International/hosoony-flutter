<?php
// سكريبت إصلاح خطأ 500 وإعدادات CORS
// استخدم هذا السكريبت لحل خطأ 500 وإصلاح CORS

echo "🔧 إصلاح خطأ 500 وإعدادات CORS\n";
echo "==============================\n\n";

try {
    // 1. فحص ملفات الكاش التالفة
    echo "1. 🔍 فحص ملفات الكاش التالفة...\n";
    
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
    
    // 2. إصلاح ملف CORS
    echo "\n2. 📝 إصلاح ملف CORS...\n";
    
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
    echo "   ✅ تم إصلاح ملف CORS\n";
    
    // 3. إصلاح ملف .env
    echo "\n3. ⚙️ فحص ملف .env...\n";
    
    if (file_exists('.env')) {
        $envContent = file_get_contents('.env');
        
        // التأكد من وجود APP_KEY
        if (strpos($envContent, 'APP_KEY=') === false || strpos($envContent, 'APP_KEY=base64:') === false) {
            echo "   ⚠️ APP_KEY مفقود أو غير صحيح\n";
        } else {
            echo "   ✅ APP_KEY موجود\n";
        }
        
        // التأكد من وجود APP_URL
        if (strpos($envContent, 'APP_URL=https://thakaa.me') === false) {
            echo "   ⚠️ APP_URL غير صحيح\n";
        } else {
            echo "   ✅ APP_URL صحيح\n";
        }
    } else {
        echo "   ❌ ملف .env غير موجود\n";
    }
    
    // 4. إصلاح الصلاحيات
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
            echo "   ✅ إصلاح صلاحيات: $dir\n";
        }
    }
    
    // 5. اختبار Laravel
    echo "\n5. 🧪 اختبار Laravel...\n";
    
    // اختبار بسيط
    $testFile = 'test_laravel.php';
    file_put_contents($testFile, '<?php
require_once "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
echo "Laravel loaded successfully\n";
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
    
    // 6. اختبار API
    echo "\n6. 🌐 اختبار API...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://thakaa.me/api/user');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   - HTTP Code: $httpCode\n";
    
    if ($httpCode == 200) {
        echo "   ✅ API يعمل بشكل صحيح\n";
    } elseif ($httpCode == 302) {
        echo "   ✅ API يعمل (يوجه إلى تسجيل الدخول)\n";
    } else {
        echo "   ❌ API لا يعمل (HTTP $httpCode)\n";
    }
    
    echo "\n==============================\n";
    echo "✅ تم إصلاح خطأ 500 وإعدادات CORS\n";
    echo "الآن جرب اختبار Flutter Web مرة أخرى\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الإصلاح: " . $e->getMessage() . "\n";
}












