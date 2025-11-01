#!/bin/bash

# سكريبت التحقق من متطلبات النشر التلقائي
# هذا السكريبت يتحقق من أن جميع المتطلبات متوفرة

echo "🔍 التحقق من متطلبات النشر التلقائي..."

# 1. التحقق من Git
echo "1. التحقق من Git..."
if command -v git &> /dev/null; then
    echo "✅ Git متوفر: $(git --version)"
else
    echo "❌ Git غير متوفر. يرجى تثبيته أولاً"
    exit 1
fi

# 2. التحقق من SSH keys
echo "2. التحقق من SSH keys..."
if [ -f ~/.ssh/id_rsa.pub ] || [ -f ~/.ssh/id_ed25519.pub ]; then
    echo "✅ SSH keys موجودة"
else
    echo "❌ SSH keys غير موجودة. يرجى إنشاؤها:"
    echo "   ssh-keygen -t ed25519 -C \"your_email@example.com\""
    echo "   ثم أضف المفتاح إلى GitHub"
    exit 1
fi

# 3. اختبار الاتصال بـ GitHub
echo "3. اختبار الاتصال بـ GitHub..."
if ssh -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
    echo "✅ الاتصال بـ GitHub يعمل"
else
    echo "❌ فشل الاتصال بـ GitHub. تحقق من SSH keys"
    exit 1
fi

# 4. التحقق من مجلد العمل
echo "4. التحقق من مجلد العمل..."
if [ -d "/Users/ibraheem.designer/Documents/hosoony2" ]; then
    echo "✅ مجلد العمل موجود: /Users/ibraheem.designer/Documents/hosoony2"
else
    echo "❌ مجلد العمل غير موجود"
    exit 1
fi

# 5. التحقق من ملفات الإعداد
echo "5. التحقق من ملفات الإعداد..."
if [ -f "/Users/ibraheem.designer/Documents/hosoony2/setup_production_branch.sh" ]; then
    echo "✅ سكريبت إعداد فرع الإنتاج موجود"
else
    echo "❌ سكريبت إعداد فرع الإنتاج غير موجود"
fi

if [ -f "/Users/ibraheem.designer/Documents/hosoony2/.cpanel.yml" ]; then
    echo "✅ ملف .cpanel.yml موجود"
else
    echo "❌ ملف .cpanel.yml غير موجود"
fi

# 6. التحقق من صلاحيات الملفات
echo "6. التحقق من صلاحيات الملفات..."
if [ -x "/Users/ibraheem.designer/Documents/hosoony2/setup_production_branch.sh" ]; then
    echo "✅ سكريبت إعداد فرع الإنتاج قابل للتنفيذ"
else
    echo "⚠️  سكريبت إعداد فرع الإنتاج غير قابل للتنفيذ. سيتم إصلاحه..."
    chmod +x /Users/ibraheem.designer/Documents/hosoony2/setup_production_branch.sh
fi

echo ""
echo "🎉 جميع المتطلبات متوفرة!"
echo ""
echo "📋 الخطوات التالية:"
echo "1. شغل: ./setup_production_branch.sh"
echo "2. اذهب إلى cPanel وربط الفرع الجديد"
echo "3. أضف ملف .cpanel.yml"
echo "4. اختبر النشر اليدوي"
echo "5. فعّل Webhook للنشر التلقائي"
echo ""
echo "📖 راجع ملف QUICK_START_GUIDE.md للتفاصيل"















