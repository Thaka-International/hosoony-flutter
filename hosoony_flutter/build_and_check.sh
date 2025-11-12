#!/bin/bash

# Script to check and build AAB with proper error handling

set -e

echo "🔍 فحص الإعدادات قبل البناء"
echo "=============================="
echo ""

cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter

# Check keystore
if [ ! -f "android/keystore/hosoony-release-key.jks" ]; then
    echo "❌ Keystore غير موجود!"
    echo "يرجى تشغيل: ./android/generate_keystore.sh"
    exit 1
fi
echo "✅ Keystore موجود"

# Check key.properties
if [ ! -f "android/key.properties" ]; then
    echo "❌ ملف key.properties غير موجود!"
    echo "يرجى تشغيل: ./update_key_properties.sh"
    exit 1
fi
echo "✅ ملف key.properties موجود"

# Check if passwords are still default
if grep -q "your-keystore-password-here" android/key.properties; then
    echo "⚠️  ملف key.properties يحتوي على قيم افتراضية!"
    echo "يرجى تشغيل: ./update_key_properties.sh"
    exit 1
fi
echo "✅ كلمات المرور محدثة"

echo ""
echo "🚀 بدء البناء..."
echo ""

# Clean and build
flutter clean
flutter pub get

echo ""
echo "📦 بناء App Bundle..."
flutter build appbundle --release

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ البناء نجح!"
    echo "📍 الملف: build/app/outputs/bundle/release/app-release.aab"
    
    # Check file size
    if [ -f "build/app/outputs/bundle/release/app-release.aab" ]; then
        FILE_SIZE=$(du -h "build/app/outputs/bundle/release/app-release.aab" | cut -f1)
        echo "📊 حجم الملف: $FILE_SIZE"
    fi
else
    echo ""
    echo "❌ البناء فشل!"
    exit 1
fi




