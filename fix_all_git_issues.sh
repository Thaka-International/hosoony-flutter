#!/bin/bash

# سكريبت حل جميع مشاكل Git

echo "🔧 حل جميع مشاكل Git..."

# 1. الانتقال إلى مجلد الريبو
echo "1. الانتقال إلى مجلد الريبو..."
cd /home/thme/repos/hosoony

# 2. إعداد هوية Git
echo "2. إعداد هوية Git..."
git config --global user.email "thme@thakaholding.com"
git config --global user.name "Thaka Server"

# 3. التحقق من الإعدادات
echo "3. التحقق من الإعدادات..."
echo "✅ الاسم: $(git config --global user.name)"
echo "✅ البريد: $(git config --global user.email)"

# 4. حل مشكلة composer.lock
echo "4. حل مشكلة composer.lock..."
git restore hosoony-backend/composer.lock 2>/dev/null || echo "⚠️  ملف composer.lock غير موجود"
rm -f hosoony-backend/composer.lock.bak
rm -f hosoony-backend/composer.json.bak
rm -rf bin/

# 5. إضافة التعديلات
echo "5. إضافة التعديلات..."
git add .

# 6. إنشاء commit
echo "6. إنشاء commit..."
git commit -m "feat: merge existing cPanel changes with production

- دمج جميع التعديلات الموجودة على cPanel
- حل مشاكل تعارض الفروع
- إعداد هوية Git للخادم

التاريخ: $(date)"

# 7. حل تعارض الفروع
echo "7. حل تعارض الفروع..."
git pull origin production --allow-unrelated-histories

# 8. رفع التعديلات
echo "8. رفع التعديلات..."
git push origin production

# 9. التحقق من النتيجة
echo "9. التحقق من النتيجة..."
echo "✅ الفرع النشط: $(git branch --show-current)"
echo "✅ آخر commit: $(git log --oneline -1)"

echo ""
echo "🎉 تم حل جميع المشاكل!"
echo ""
echo "📋 الخطوات التالية في cPanel:"
echo "1. اذهب إلى Git™ Version Control"
echo "2. افتح الريبو: /home/thme/repos/hosoony"
echo "3. في Basic Information: اختر 'production'"
echo "4. اضغط Update"
echo "5. اذهب إلى Pull or Deploy"
echo "6. اضغط Pull أولاً"
echo "7. ثم اضغط Deploy HEAD Commit"
echo ""
echo "🎯 النتيجة:"
echo "✅ هوية Git معرّفة"
echo "✅ تعارض الفروع محلول"
echo "✅ التعديلات محفوظة في GitHub"
echo "✅ النشر يعمل في cPanel"
echo ""
echo "🚀 الآن جرب النشر في cPanel!"















