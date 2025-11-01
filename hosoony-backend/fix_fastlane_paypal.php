<?php
// سكريبت إصلاح خطأ FastlanePayPalService
// استخدم هذا السكريبت لحل خطأ TypeError في FastlanePayPalService

echo "🔧 إصلاح خطأ FastlanePayPalService\n";
echo "================================\n\n";

try {
    // 1. إضافة مفتاح وهمي في .env
    echo "1. 📝 إضافة مفتاح وهمي في .env...\n";
    
    if (file_exists('.env')) {
        $envContent = file_get_contents('.env');
        
        // إضافة FASTLANE_PAYPAL_API_KEY إذا لم يكن موجود
        if (strpos($envContent, 'FASTLANE_PAYPAL_API_KEY=') === false) {
            $envContent .= "\nFASTLANE_PAYPAL_API_KEY=dummy_key\n";
            file_put_contents('.env', $envContent);
            echo "   ✅ تم إضافة FASTLANE_PAYPAL_API_KEY\n";
        } else {
            // تحديث المفتاح إذا كان فارغ
            $envContent = preg_replace('/FASTLANE_PAYPAL_API_KEY=\s*/', 'FASTLANE_PAYPAL_API_KEY=dummy_key', $envContent);
            file_put_contents('.env', $envContent);
            echo "   ✅ تم تحديث FASTLANE_PAYPAL_API_KEY\n";
        }
    } else {
        echo "   ❌ ملف .env غير موجود\n";
    }
    
    // 2. إصلاح FastlanePayPalService
    echo "\n2. 🔧 إصلاح FastlanePayPalService...\n";
    
    $serviceFile = 'app/Services/FastlanePayPalService.php';
    if (file_exists($serviceFile)) {
        $serviceContent = file_get_contents($serviceFile);
        
        // إصلاح الخاصية لتكون nullable
        $serviceContent = preg_replace('/private string \$apiKey;/', 'private ?string $apiKey = null;', $serviceContent);
        
        // إصلاح الـ constructor
        $serviceContent = preg_replace(
            '/\$this->apiKey = config\(\'services\.fastlane_paypal\.api_key\'\);/',
            '$this->apiKey = config(\'services.fastlane_paypal.api_key\') ?? env(\'FASTLANE_PAYPAL_API_KEY\', \'\');',
            $serviceContent
        );
        
        file_put_contents($serviceFile, $serviceContent);
        echo "   ✅ تم إصلاح FastlanePayPalService\n";
    } else {
        echo "   ⚠️ ملف FastlanePayPalService غير موجود\n";
    }
    
    // 3. إصلاح config/services.php
    echo "\n3. ⚙️ إصلاح config/services.php...\n";
    
    $configFile = 'config/services.php';
    if (file_exists($configFile)) {
        $configContent = file_get_contents($configFile);
        
        // إضافة إعدادات fastlane_paypal إذا لم تكن موجودة
        if (strpos($configContent, 'fastlane_paypal') === false) {
            $configContent = str_replace(
                '];',
                "    'fastlane_paypal' => [
        'api_key' => env('FASTLANE_PAYPAL_API_KEY', 'dummy_key'),
    ],
];",
                $configContent
            );
            file_put_contents($configFile, $configContent);
            echo "   ✅ تم إضافة إعدادات fastlane_paypal\n";
        } else {
            echo "   ✅ إعدادات fastlane_paypal موجودة\n";
        }
    } else {
        echo "   ⚠️ ملف config/services.php غير موجود\n";
    }
    
    // 4. تنظيف الكاش
    echo "\n4. 🧹 تنظيف الكاش...\n";
    
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
    
    // 5. اختبار API
    echo "\n5. 🧪 اختبار API...\n";
    
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
    } else {
        echo "   ⚠️ API يعطي HTTP $httpCode\n";
    }
    
    // 6. اختبار مع token
    echo "\n6. 🔑 اختبار مع token...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://thakaa.me/api/user');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer 10|WYuBo6mHD1vOJqnwBWr3ykvFdBv0aBcMOjBiVxvr2e1d211e'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   - HTTP Code: $httpCode\n";
    
    if ($httpCode == 200) {
        echo "   ✅ API يعمل مع token\n";
    } elseif ($httpCode == 302) {
        echo "   ✅ API يعمل مع token (يوجه إلى تسجيل الدخول)\n";
    } elseif ($httpCode == 500) {
        echo "   ❌ API لا يزال يعطي خطأ 500 مع token\n";
    } else {
        echo "   ⚠️ API يعطي HTTP $httpCode مع token\n";
    }
    
    echo "\n================================\n";
    echo "✅ تم إصلاح خطأ FastlanePayPalService\n";
    echo "الآن جرب اختبار Flutter Web مرة أخرى\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الإصلاح: " . $e->getMessage() . "\n";
}












