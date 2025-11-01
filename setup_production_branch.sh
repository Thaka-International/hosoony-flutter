#!/bin/bash

# سكريبت إعداد فرع الإنتاج على GitHub
# هذا السكريبت يطبق الاقتراح المقدم خطوة بخطوة

echo "🚀 بدء إعداد فرع الإنتاج على GitHub..."

# 1. إنشاء مجلد جديد للعمل مع Git
echo "📁 إنشاء مجلد جديد للعمل مع Git..."
cd /Users/ibraheem.designer/Documents/hosoony2
mkdir -p hosoony-git
cd hosoony-git

# 2. Clone الريبو من GitHub
echo "📥 Clone الريبو من GitHub..."
git clone git@github.com:Thaka-International/hosoony.git .
if [ $? -ne 0 ]; then
    echo "❌ فشل في Clone الريبو. تأكد من إعداد SSH keys"
    exit 1
fi

# 3. جلب كل الفروع
echo "🔄 جلب كل الفروع..."
git fetch --all --prune

# 4. إنشاء فرع production من prod-snapshot-20251015
echo "🌿 إنشاء فرع production..."
git checkout -B production origin/prod-snapshot-20251015

# 5. دفع الفرع إلى GitHub
echo "📤 دفع فرع production إلى GitHub..."
git push -u origin production

# 6. إنشاء Tag ثابت
echo "🏷️ إنشاء Tag ثابت..."
git tag -a prod-20251015 -m "Production snapshot 2025-10-15"
git push origin prod-20251015

echo "✅ تم إعداد فرع الإنتاج بنجاح!"
echo ""
echo "📋 الخطوات التالية:"
echo "1. انتقل إلى cPanel وربط الفرع الجديد"
echo "2. أنشئ ملف .cpanel.yml للنشر التلقائي"
echo "3. فعّل Webhook للنشر التلقائي"
echo ""
echo "📁 مجلد العمل: $(pwd)"
echo "🌿 الفرع النشط: production"
echo "🏷️ Tag: prod-20251015"















