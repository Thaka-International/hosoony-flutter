#!/bin/bash

# سكريبت تشخيص مشكلة زر Deploy غير مفعل

echo "🔍 تشخيص مشكلة زر Deploy غير مفعل..."

# 1. التحقق من حالة Git
echo "1. التحقق من حالة Git..."
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony-git 2>/dev/null || {
    echo "❌ مجلد hosoony-git غير موجود"
    echo "📋 الحل: شغل setup_production_branch.sh أولاً"
    exit 1
}

echo "✅ مجلد العمل: $(pwd)"

# 2. التحقق من الفرع النشط
echo "2. التحقق من الفرع النشط..."
CURRENT_BRANCH=$(git branch --show-current)
echo "✅ الفرع النشط: $CURRENT_BRANCH"

if [ "$CURRENT_BRANCH" != "production" ]; then
    echo "⚠️  الفرع النشط ليس production. تحويل إلى production..."
    git checkout production
fi

# 3. التحقق من حالة الفرع
echo "3. التحقق من حالة الفرع..."
git status

# 4. التحقق من آخر commits
echo "4. آخر 5 commits:"
git log --oneline -5

# 5. التحقق من ملف .cpanel.yml
echo "5. التحقق من ملف .cpanel.yml..."
if [ -f ".cpanel.yml" ]; then
    echo "✅ ملف .cpanel.yml موجود"
    echo "📄 محتوى الملف:"
    cat .cpanel.yml
else
    echo "❌ ملف .cpanel.yml غير موجود"
    echo "📋 الحل: أنشئ الملف أولاً"
fi

# 6. التحقق من الاتصال بـ GitHub
echo "6. التحقق من الاتصال بـ GitHub..."
if ssh -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
    echo "✅ الاتصال بـ GitHub يعمل"
else
    echo "❌ فشل الاتصال بـ GitHub"
    echo "📋 الحل: أعد إعداد SSH keys"
fi

# 7. التحقق من الفرع على GitHub
echo "7. التحقق من الفرع على GitHub..."
git fetch origin
git branch -r | grep production

echo ""
echo "🎯 التوصيات:"
echo "1. تأكد من أن الفرع النشط هو 'production'"
echo "2. تأكد من وجود ملف .cpanel.yml"
echo "3. تأكد من أن آخر commit تم push إلى GitHub"
echo "4. في cPanel: جرب Pull أولاً ثم Deploy"
echo ""
echo "📋 إذا استمرت المشكلة:"
echo "- احذف الريبو من cPanel وأنشئه من جديد"
echo "- تأكد من أن Repository Path صحيح"
echo "- تحقق من سجلات الأخطاء في cPanel"















