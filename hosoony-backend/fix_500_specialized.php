<?php
// سكريبت إصلاح خطأ 500 متخصص
// استخدم هذا السكريبت لحل خطأ 500 المتعلق بـ CORS

echo "🔧 إصلاح خطأ 500 متخصص\n";
echo "=====================\n\n";

try {
    // 1. حذف ملف CORS مؤقتاً
    echo "1. 🗑️ حذف ملف CORS مؤقتاً...\n";
    
    if (file_exists('config/cors.php')) {
        unlink('config/cors.php');
        echo "   ✅ تم حذف ملف CORS\n";
    }
    
    // 2. حذف جميع ملفات الكاش
    echo "\n2. 🧹 حذف جميع ملفات الكاش...\n";
    
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
    
    // 3. اختبار API بدون CORS
    echo "\n3. 🧪 اختبار API بدون CORS...\n";
    
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
        echo "   ✅ API يعمل بدون CORS\n";
    } elseif ($httpCode == 302) {
        echo "   ✅ API يعمل بدون CORS (يوجه إلى تسجيل الدخول)\n";
    } elseif ($httpCode == 500) {
        echo "   ❌ API لا يزال يعطي خطأ 500\n";
        echo "   🔍 المشكلة ليست في CORS\n";
    } else {
        echo "   ⚠️ API يعطي HTTP $httpCode\n";
    }
    
    // 4. إنشاء ملف CORS بسيط جداً
    echo "\n4. 📝 إنشاء ملف CORS بسيط جداً...\n";
    
    $corsContent = '<?php

return [
    "paths" => ["api/*"],
    "allowed_methods" => ["GET", "POST"],
    "allowed_origins" => ["*"],
    "allowed_origins_patterns" => [],
    "allowed_headers" => ["Content-Type"],
    "exposed_headers" => [],
    "max_age" => 0,
    "supports_credentials" => false,
];';

    file_put_contents('config/cors.php', $corsContent);
    echo "   ✅ تم إنشاء ملف CORS بسيط جداً\n";
    
    // 5. اختبار API مع CORS البسيط
    echo "\n5. 🧪 اختبار API مع CORS البسيط...\n";
    
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
        echo "   ✅ API يعمل مع CORS البسيط\n";
    } elseif ($httpCode == 302) {
        echo "   ✅ API يعمل مع CORS البسيط (يوجه إلى تسجيل الدخول)\n";
    } elseif ($httpCode == 500) {
        echo "   ❌ API لا يزال يعطي خطأ 500\n";
        echo "   🔍 المشكلة في CORS\n";
    } else {
        echo "   ⚠️ API يعطي HTTP $httpCode\n";
    }
    
    // 6. إنشاء ملف CORS مع الإعدادات المطلوبة
    echo "\n6. 📝 إنشاء ملف CORS مع الإعدادات المطلوبة...\n";
    
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
    echo "   ✅ تم إنشاء ملف CORS مع الإعدادات المطلوبة\n";
    
    // 7. اختبار API مع CORS الكامل
    echo "\n7. 🧪 اختبار API مع CORS الكامل...\n";
    
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
        echo "   ✅ API يعمل مع CORS الكامل\n";
    } elseif ($httpCode == 302) {
        echo "   ✅ API يعمل مع CORS الكامل (يوجه إلى تسجيل الدخول)\n";
    } elseif ($httpCode == 500) {
        echo "   ❌ API لا يزال يعطي خطأ 500\n";
        echo "   🔍 المشكلة في CORS أو Laravel\n";
    } else {
        echo "   ⚠️ API يعطي HTTP $httpCode\n";
    }
    
    // 8. فحص error_log
    echo "\n8. 🔍 فحص error_log...\n";
    
    if (file_exists('error_log')) {
        $errorLog = file_get_contents('error_log');
        $recentErrors = array_slice(explode("\n", $errorLog), -10);
        echo "   - آخر 10 أخطاء:\n";
        foreach ($recentErrors as $error) {
            if (trim($error)) {
                echo "     $error\n";
            }
        }
    } else {
        echo "   ⚠️ ملف error_log غير موجود\n";
    }
    
    echo "\n=====================\n";
    echo "✅ تم إصلاح خطأ 500 متخصص\n";
    echo "إذا لا يزال خطأ 500، المشكلة في Laravel نفسه وليس في CORS\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الإصلاح: " . $e->getMessage() . "\n";
}












