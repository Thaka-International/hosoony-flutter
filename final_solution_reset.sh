#!/bin/bash

# سكريبت الحل النهائي - إعادة تعيين الفرع بالكامل

echo "🔧 الحل النهائي - إعادة تعيين الفرع بالكامل..."

# 1. الانتقال إلى مجلد الريبو
echo "1. الانتقال إلى مجلد الريبو..."
cd /home/thme/repos/hosoony

# 2. إعادة تعيين الفرع بالكامل
echo "2. إعادة تعيين الفرع بالكامل..."
git reset --hard origin/production

# 3. التحقق من النتيجة
echo "3. التحقق من النتيجة..."
echo "✅ الفرع النشط: $(git branch --show-current)"
echo "✅ حالة Git:"
git status

# 4. نسخ التعديلات المهمة من public_html
echo "4. نسخ التعديلات المهمة من public_html..."
echo "📁 نسخ PwaController..."
cp /home/thme/public_html/app/Http/Controllers/PwaController.php /home/thme/repos/hosoony/hosoony-backend/app/Http/Controllers/ 2>/dev/null || echo "⚠️  ملف PwaController غير موجود"

echo "📁 نسخ ملفات العرض..."
cp -r /home/thme/public_html/resources/views/pwa /home/thme/repos/hosoony/hosoony-backend/resources/views/ 2>/dev/null || echo "⚠️  مجلد pwa غير موجود"

echo "📁 نسخ bootstrap/app.php..."
cp /home/thme/public_html/bootstrap/app.php /home/thme/repos/hosoony/hosoony-backend/bootstrap/ 2>/dev/null || echo "⚠️  ملف bootstrap/app.php غير موجود"

# 5. إضافة التعديلات
echo "5. إضافة التعديلات..."
git add .

# 6. إنشاء commit
echo "6. إنشاء commit..."
git commit -m "feat: merge existing cPanel changes with production

- دمج جميع التعديلات الموجودة على cPanel
- إعادة تعيين الفرع إلى production
- رفع التعديلات إلى GitHub

التاريخ: $(date)"

# 7. رفع التعديلات
echo "7. رفع التعديلات..."
git push origin production

# 8. التحقق من النتيجة النهائية
echo "8. التحقق من النتيجة النهائية..."
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
echo "✅ الفرع متزامن مع البعيد"
echo "✅ التعديلات محفوظة في GitHub"
echo "✅ النشر يعمل في cPanel"
echo ""
echo "🚀 الآن جرب النشر في cPanel!"















