#!/bin/bash

# 🔧 سكريبت لإزالة ملفات APK من git history

echo "🔧 إزالة ملفات APK من git history..."
echo "═══════════════════════════════════════════════════════════"
echo ""

cd "$(dirname "$0")"

# قائمة بملفات APK المعروفة
APK_FILES=(
    "hosoony_app.apk"
    "hosoony_fixed_login.apk"
    "hosoony_fixed_all_issues.apk"
    "hosoony_daily_tasks_fixed.apk"
    "hosoony_production_debug.apk"
    "hosoony_app_debug.apk"
    "hosoony_error_handling_fixed.apk"
    "hosoony_final_fixed.apk"
    "hosoony_remember_me.apk"
    "../hosoony_app.apk"
    "../hosoony_fixed_login.apk"
    "../hosoony_fixed_all_issues.apk"
    "../hosoony_daily_tasks_fixed.apk"
    "../hosoony_production_debug.apk"
    "../hosoony_app_debug.apk"
    "../hosoony_error_handling_fixed.apk"
    "../hosoony_final_fixed.apk"
    "../hosoony_remember_me.apk"
)

echo "📋 الملفات التي سيتم إزالتها:"
for file in "${APK_FILES[@]}"; do
    echo "  - $file"
done

echo ""
echo "⚠️  تحذير: هذا سيعيد كتابة git history!"
echo "هل تريد المتابعة؟ (y/n)"
read -r response

if [[ "$response" != "y" ]]; then
    echo "❌ تم الإلغاء"
    exit 1
fi

echo ""
echo "🔄 إزالة الملفات من git history..."

# إزالة الملفات من git history باستخدام git filter-branch
git filter-branch --force --index-filter \
    "git rm --cached --ignore-unmatch $(printf '%s ' "${APK_FILES[@]}")" \
    --prune-empty --tag-name-filter cat -- --all

# تنظيف
git for-each-ref --format="%(refname)" refs/original/ | xargs -n 1 git update-ref -d

# تنظيف reflog
git reflog expire --expire=now --all

# تنظيف garbage collection
git gc --prune=now --aggressive

echo ""
echo "✅ اكتملت الإزالة!"
echo ""
echo "📝 الخطوات التالية:"
echo "   1. تحقق من النتيجة: git log --all"
echo "   2. Force push: git push flutter-repo master --force"
echo ""

