#!/bin/bash

# سكريبت حل مشكلة composer.lock على الخادم

echo "🔧 حل مشكلة composer.lock على الخادم..."

# 1. التحقق من المجلد الحالي
echo "1. التحقق من المجلد الحالي..."
if [ -d "/home/thme/repos/hosoony" ]; then
    echo "✅ المجلد موجود: /home/thme/repos/hosoony"
    cd /home/thme/repos/hosoony
else
    echo "❌ المجلد غير موجود: /home/thme/repos/hosoony"
    exit 1
fi

# 2. تنظيف التغييرات المحلية
echo "2. تنظيف التغييرات المحلية..."
git restore hosoony-backend/composer.lock 2>/dev/null || echo "⚠️  ملف composer.lock غير موجود"

# 3. حذف الملفات المؤقتة
echo "3. حذف الملفات المؤقتة..."
rm -f hosoony-backend/composer.lock.bak
rm -f hosoony-backend/composer.json.bak
rm -rf bin/

# 4. التبديل إلى فرع production
echo "4. التبديل إلى فرع production..."
git checkout -B production origin/production

# 5. التحقق من النتيجة
echo "5. التحقق من النتيجة..."
echo "✅ الفرع النشط: $(git branch --show-current)"
echo "✅ حالة Git:"
git status

echo ""
echo "🎯 الخطوات التالية:"
echo "1. في cPanel: اذهب إلى Git™ Version Control"
echo "2. افتح الريبو: /home/thme/repos/hosoony"
echo "3. في Basic Information: تأكد من أن 'production' مختار"
echo "4. اضغط Update"
echo "5. اذهب إلى Pull or Deploy"
echo "6. اضغط Pull أولاً"
echo "7. ثم اضغط Deploy HEAD Commit"
echo ""
echo "✅ تم حل المشكلة! الآن جرب النشر في cPanel"















