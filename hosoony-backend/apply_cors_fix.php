<?php
// سكريبت تطبيق إعدادات CORS على الخادم الفعلي
// استخدم هذا السكريبت لتطبيق إعدادات CORS الجديدة

echo "🔧 تطبيق إعدادات CORS على الخادم الفعلي\n";
echo "=====================================\n\n";

try {
    // 1. نسخ ملف CORS الجديد
    echo "1. 📁 نسخ ملف CORS الجديد...\n";
    
    $corsConfig = '<?php

return [
    "paths" => ["api/*", "sanctum/csrf-cookie"],
    "allowed_methods" => ["*"],
    "allowed_origins" => [
        "http://localhost:*",
        "http://127.0.0.1:*",
        "https://thakaa.me",
    ],
    "allowed_origins_patterns" => [],
    "allowed_headers" => ["*"],
    "exposed_headers" => [],
    "max_age" => 0,
    "supports_credentials" => true,
];';

    file_put_contents('config/cors.php', $corsConfig);
    echo "   ✅ تم نسخ ملف CORS الجديد\n";
    
    // 2. مسح الكاش
    echo "\n2. 🧹 مسح الكاش...\n";
    
    // مسح config cache
    if (file_exists('bootstrap/cache/config.php')) {
        unlink('bootstrap/cache/config.php');
        echo "   ✅ تم مسح config cache\n";
    }
    
    // مسح route cache
    if (file_exists('bootstrap/cache/routes-v7.php')) {
        unlink('bootstrap/cache/routes-v7.php');
        echo "   ✅ تم مسح route cache\n";
    }
    
    // مسح view cache
    $viewCacheDir = 'storage/framework/views';
    if (is_dir($viewCacheDir)) {
        $files = glob($viewCacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "   ✅ تم مسح view cache\n";
    }
    
    // 3. اختبار إعدادات CORS
    echo "\n3. 🧪 اختبار إعدادات CORS...\n";
    
    $corsConfig = include 'config/cors.php';
    echo "   - allowed_origins: " . json_encode($corsConfig['allowed_origins']) . "\n";
    echo "   - supports_credentials: " . ($corsConfig['supports_credentials'] ? 'true' : 'false') . "\n";
    echo "   - allowed_methods: " . json_encode($corsConfig['allowed_methods']) . "\n";
    
    echo "\n=====================================\n";
    echo "✅ تم تطبيق إعدادات CORS بنجاح\n";
    echo "الآن جرب اختبار Flutter Web مرة أخرى\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في تطبيق إعدادات CORS: " . $e->getMessage() . "\n";
}












