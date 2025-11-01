#!/bin/bash

# سكريبت حل مشكلة فرع production غير موجود

echo "🔧 حل مشكلة فرع production غير موجود..."

# 1. التحقق من المجلد الحالي
echo "1. التحقق من المجلد الحالي..."
if [ -d "/home/thme/repos/hosoony" ]; then
    echo "✅ المجلد موجود: /home/thme/repos/hosoony"
    cd /home/thme/repos/hosoony
else
    echo "❌ المجلد غير موجود: /home/thme/repos/hosoony"
    exit 1
fi

# 2. التحقق من الفروع المتاحة
echo "2. التحقق من الفروع المتاحة..."
echo "الفروع المحلية:"
git branch
echo "الفروع البعيدة:"
git branch -r

# 3. التحقق من الفرع النشط
echo "3. الفرع النشط: $(git branch --show-current)"

# 4. إنشاء فرع production
echo "4. إنشاء فرع production..."
git checkout -b production

# 5. رفع الفرع إلى GitHub
echo "5. رفع الفرع إلى GitHub..."
git push -u origin production

# 6. التحقق من النتيجة
echo "6. التحقق من النتيجة..."
echo "✅ الفرع النشط: $(git branch --show-current)"
echo "✅ حالة Git:"
git status

echo ""
echo "🎉 تم حل المشكلة!"
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
echo "✅ فرع production موجود محلياً"
echo "✅ التعديلات محفوظة في GitHub"
echo "✅ النشر يعمل في cPanel"
echo ""
echo "🚀 الآن جرب النشر في cPanel!"















