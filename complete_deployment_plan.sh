#!/bin/bash

# سكريبت إكمال خطة النشر التلقائي

echo "🚀 إكمال خطة النشر التلقائي..."

# 1. إضافة ملف .cpanel.yml إلى الريبو
echo "1. إضافة ملف .cpanel.yml إلى الريبو..."
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony-git

# نسخ ملف .cpanel.yml
cp /Users/ibraheem.designer/Documents/hosoony2/.cpanel.yml .

# إضافة الملف إلى Git
git add .cpanel.yml
git commit -m "chore: add cPanel deployment to public_html from production

- إضافة ملف .cpanel.yml للنشر التلقائي
- تكوين rsync لنسخ الملفات إلى public_html
- إضافة أوامر Laravel بعد النشر
- إصلاح صلاحيات المجلدات
- تسجيل نجاح النشر

التاريخ: $(date)"

# رفع التعديلات
git push origin production

echo "✅ تم رفع ملف .cpanel.yml إلى GitHub"

echo ""
echo "📋 الخطوات التالية في cPanel:"
echo ""
echo "🔧 الخطوة 3: ربط cPanel بالفرع الجديد"
echo "1. اذهب إلى Git™ Version Control في cPanel"
echo "2. افتح الريبو الموجود: /home/thme/repos/hosoony"
echo "3. في Basic Information:"
echo "   - Checked-Out Branch: اختر 'production'"
echo "4. اضغط Update"
echo ""
echo "🚀 الخطوة 4: تشغيل نشر يدوي أول مرة"
echo "1. اذهب إلى Pull or Deploy"
echo "2. اضغط Pull أولاً"
echo "3. ثم اضغط Deploy HEAD Commit"
echo "4. راقب سجل Logs للتأكد من:"
echo "   - نجاح rsync إلى /home/thme/public_html"
echo "   - نجاح أوامر artisan"
echo ""
echo "🔗 الخطوة 5: تفعيل Webhook للنشر التلقائي"
echo "1. في cPanel: Git™ Version Control » Manage Repository » Pull or Deploy"
echo "2. انسخ رابط Deployment via Webhook"
echo "3. في GitHub: Settings » Webhooks » Add webhook"
echo "4. Payload URL: ضع رابط الـ Webhook"
echo "5. Content type: application/json"
echo "6. Which events?: Just the push event"
echo "7. اضغط Add webhook"
echo ""
echo "🎯 النتيجة:"
echo "✅ أي push إلى production سيُفعّل نشرًا تلقائيًا إلى public_html"
echo "✅ النشر التلقائي يعمل من الآن"
echo "✅ إدارة نسخ محترفة عبر Git"
echo ""
echo "🔄 سير العمل اليومي:"
echo "1. تعديل الكود على فرع تطوير"
echo "2. دمج في production عبر Pull Request"
echo "3. النشر التلقائي يعمل فوراً!"
echo ""
echo "🎉 تم إعداد النشر التلقائي بنجاح!"















