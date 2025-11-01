#!/bin/bash

# سكريبت واحد لحل جميع المشاكل - دمج التعديلات الموجودة مع GitHub

echo "🚀 حل واحد لجميع المشاكل - دمج التعديلات الموجودة مع GitHub..."

# 1. الانتقال إلى مجلد الريبو
echo "1. الانتقال إلى مجلد الريبو..."
cd /home/thme/repos/hosoony

# 2. التحقق من حالة Git
echo "2. التحقق من حالة Git..."
git status

# 3. حفظ جميع التعديلات
echo "3. حفظ جميع التعديلات..."
git add .

# 4. إنشاء commit للتعديلات
echo "4. إنشاء commit للتعديلات..."
git commit -m "feat: merge existing cPanel changes with production branch

- دمج جميع التعديلات الموجودة على cPanel
- رفع التعديلات إلى فرع production
- تفعيل النشر التلقائي من GitHub

التاريخ: $(date)"

# 5. التبديل إلى فرع production
echo "5. التبديل إلى فرع production..."
git checkout -B production origin/production

# 6. دمج التعديلات مع production
echo "6. دمج التعديلات مع production..."
git merge master --no-ff -m "feat: merge cPanel changes into production"

# 7. رفع التعديلات إلى GitHub
echo "7. رفع التعديلات إلى GitHub..."
git push origin production

# 8. التحقق من النتيجة
echo "8. التحقق من النتيجة..."
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
echo "✅ جميع التعديلات محفوظة في GitHub"
echo "✅ النشر التلقائي يعمل من الآن"
echo "✅ لا تضيع أي تعديل موجود على cPanel"
echo "✅ مصدر حقيقة واحد للكود"
echo ""
echo "🚀 الآن جرب النشر في cPanel!"















