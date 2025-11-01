#!/bin/bash

# سكريبت تنفيذ الأوامر لإكمال خطة النشر التلقائي

echo "🚀 تنفيذ الأوامر لإكمال خطة النشر التلقائي..."

# 1. الانتقال إلى مجلد الريبو
echo "1. الانتقال إلى مجلد الريبو..."
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony-git

# 2. التحقق من حالة Git
echo "2. التحقق من حالة Git..."
echo "✅ المجلد الحالي: $(pwd)"
echo "✅ الفرع النشط: $(git branch --show-current)"
echo "✅ حالة Git:"
git status

# 3. إضافة ملف .cpanel.yml إلى Git
echo "3. إضافة ملف .cpanel.yml إلى Git..."
git add .cpanel.yml

# 4. إنشاء commit
echo "4. إنشاء commit..."
git commit -m "chore: add cPanel deployment to public_html from production

- إضافة ملف .cpanel.yml للنشر التلقائي
- تكوين rsync لنسخ الملفات إلى public_html
- إضافة أوامر Laravel بعد النشر
- إصلاح صلاحيات المجلدات
- تسجيل نجاح النشر

التاريخ: $(date)"

# 5. رفع التعديلات إلى GitHub
echo "5. رفع التعديلات إلى GitHub..."
git push origin production

# 6. التحقق من النتيجة
echo "6. التحقق من النتيجة..."
echo "✅ آخر commit: $(git log --oneline -1)"

echo ""
echo "🎉 تم تنفيذ الأوامر بنجاح!"
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
echo "✅ ملف .cpanel.yml محفوظ في GitHub"
echo "✅ النشر التلقائي جاهز"
echo "✅ إدارة نسخ محترفة عبر Git"
echo ""
echo "🚀 الآن جرب الخطوات في cPanel!"















