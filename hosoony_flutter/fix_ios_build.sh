#!/bin/bash

# 🔧 سكريبت إصلاح مشكلة PhaseScriptExecution في iOS

echo "🔧 بدء إصلاح مشكلة iOS Build..."
echo "═══════════════════════════════════════════════════════════"
echo ""

# الانتقال إلى مجلد المشروع
cd "$(dirname "$0")"

# 1. تنظيف Flutter
echo "📦 تنظيف Flutter..."
flutter clean

# 2. تنظيف iOS
echo "📦 تنظيف iOS..."
cd ios/
rm -rf Pods Podfile.lock .symlinks
rm -rf ~/Library/Developer/Xcode/DerivedData/*

# 3. إعادة تثبيت CocoaPods
echo "📦 إعادة تثبيت CocoaPods..."
pod deintegrate 2>/dev/null || true
pod install --repo-update

# 4. إصلاح صلاحيات الـ scripts
echo "🔐 إصلاح صلاحيات الـ scripts..."
chmod +x Flutter/flutter_export_environment.sh 2>/dev/null || true
chmod -R 755 Flutter/ 2>/dev/null || true

# 5. العودة للمجلد الرئيسي
cd ..

# 6. تحديث packages
echo "📦 تحديث Flutter packages..."
flutter pub get

# 7. بناء iOS (بدون codesign للتجربة)
echo "🔨 بناء iOS..."
flutter build ios --no-codesign

echo ""
echo "✅ اكتمل الإصلاح!"
echo ""
echo "📝 الخطوات التالية:"
echo "   1. افتح Xcode: open ios/Runner.xcworkspace"
echo "   2. اضغط ⌘ + Shift + K (Clean Build Folder)"
echo "   3. اضغط ⌘ + B (Build)"
echo "   4. إذا نجح Build، اضغط ⌘ + R (Run)"
echo ""

