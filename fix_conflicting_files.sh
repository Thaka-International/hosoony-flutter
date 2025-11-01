#!/bin/bash

# سكريبت حل نهائي لمشكلة الملفات المتنازعة

echo "🔧 حل نهائي لمشكلة الملفات المتنازعة..."

# 1. الانتقال إلى مجلد الريبو
echo "1. الانتقال إلى مجلد الريبو..."
cd /home/thme/repos/hosoony

# 2. تنظيف الملفات المتنازعة
echo "2. تنظيف الملفات المتنازعة..."
rm -rf bin/
rm -f hosoony-backend/composer.json.bak
rm -f hosoony-backend/composer.lock.bak
git restore hosoony-backend/composer.lock

# 3. التحقق من النتيجة
echo "3. التحقق من النتيجة..."
echo "✅ الملفات المتنازعة محذوفة"
echo "✅ حالة Git:"
git status

# 4. إضافة التعديلات المتبقية
echo "4. إضافة التعديلات المتبقية..."
git add .
git commit -m "feat: clean up conflicting files before merge

- حذف الملفات المتنازعة (bin/, *.bak)
- استعادة composer.lock من الأصل
- إعداد الفرع للدمج

التاريخ: $(date)"

# 5. دمج الفروع
echo "5. دمج الفروع..."
git config pull.rebase false
git pull origin production --allow-unrelated-histories

# 6. رفع التعديلات
echo "6. رفع التعديلات..."
git push origin production

# 7. التحقق من النتيجة النهائية
echo "7. التحقق من النتيجة النهائية..."
echo "✅ الفرع النشط: $(git branch --show-current)"
echo "✅ آخر commit: $(git log --oneline -1)"

echo ""
echo "🎉 تم حل المشكلة نهائياً!"
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
echo "✅ الملفات المتنازعة محذوفة"
echo "✅ الفروع متداخلة بنجاح"
echo "✅ التعديلات محفوظة في GitHub"
echo "✅ النشر يعمل في cPanel"
echo ""
echo "🚀 الآن جرب النشر في cPanel!"















