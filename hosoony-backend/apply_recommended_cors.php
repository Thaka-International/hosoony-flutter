<?php
// سكريبت تطبيق إعدادات CORS الموصى بها
// استخدم هذا السكريبت لتطبيق الإعدادات الموصى بها والمتوافقة مع المتصفح

echo "🔧 تطبيق إعدادات CORS الموصى بها\n";
echo "================================\n\n";

try {
    // 1. تطبيق إعدادات CORS الموصى بها
    echo "1. 📝 تطبيق إعدادات CORS الموصى بها...\n";
    
    $corsContent = '<?php

return [
    "paths" => ["api/*", "sanctum/csrf-cookie"],

    "allowed_methods" => ["*"],

    // ✅ Explicit origins for both local dev and production
    "allowed_origins" => [
        "http://localhost",
        "http://localhost:*",
        "http://127.0.0.1",
        "http://127.0.0.1:*",
        "https://thakaa.me",
    ],

    "allowed_origins_patterns" => [],

    "allowed_headers" => ["*"],

    "exposed_headers" => [],

    "max_age" => 0,

    // ✅ Credentials allowed for Sanctum / Flutter Web with cookies or tokens
    "supports_credentials" => true,
];';

    file_put_contents('config/cors.php', $corsContent);
    echo "   ✅ تم تطبيق إعدادات CORS الموصى بها\n";
    
    // 2. مسح الكاش
    echo "\n2. 🧹 مسح الكاش...\n";
    
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
    
    // 3. مسح مجلدات الكاش
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
    
    // 4. اختبار الإعدادات
    echo "\n3. 🧪 اختبار الإعدادات...\n";
    
    $corsConfig = include 'config/cors.php';
    echo "   - allowed_origins: " . json_encode($corsConfig['allowed_origins']) . "\n";
    echo "   - supports_credentials: " . ($corsConfig['supports_credentials'] ? 'true' : 'false') . "\n";
    echo "   - allowed_methods: " . json_encode($corsConfig['allowed_methods']) . "\n";
    
    // 5. اختبار CORS headers
    echo "\n4. 🌐 اختبار CORS headers...\n";
    
    $testUrl = 'https://thakaa.me/api/user';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Origin: http://localhost:3000',
        'Access-Control-Request-Method: GET',
        'Access-Control-Request-Headers: Content-Type,Authorization'
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   - HTTP Code: $httpCode\n";
    if (strpos($response, 'Access-Control-Allow-Origin: *') !== false) {
        echo "   ✅ CORS headers صحيحة\n";
    } else {
        echo "   ❌ CORS headers تحتاج إصلاح\n";
    }
    
    echo "\n================================\n";
    echo "✅ تم تطبيق إعدادات CORS الموصى بها\n";
    echo "الآن جرب اختبار Flutter Web مرة أخرى\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في تطبيق إعدادات CORS: " . $e->getMessage() . "\n";
}












