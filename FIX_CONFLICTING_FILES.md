# 🔧 حل نهائي لمشكلة الملفات المحلية المتنازعة

## 🔍 تحليل المشكلة:

**الخطأ**: `Your local changes to the following files would be overwritten by merge`

**الملفات المتنازعة**:
- `bin/composer`
- `hosoony-backend/composer.json.bak`
- `hosoony-backend/composer.lock`
- `hosoony-backend/composer.lock.bak`

## 🚀 الحل النهائي:

### الحل الأول: حذف الملفات المتنازعة (الأبسط)
```bash
# في الخادم
cd /home/thme/repos/hosoony

# حذف الملفات المتنازعة
rm -rf bin/
rm -f hosoony-backend/composer.json.bak
rm -f hosoony-backend/composer.lock.bak
git restore hosoony-backend/composer.lock

# إضافة التعديلات المتبقية
git add .

# دمج الفروع
git pull origin production --allow-unrelated-histories
```

### الحل الثاني: حفظ التعديلات مؤقتاً
```bash
# في الخادم
cd /home/thme/repos/hosoony

# حفظ التعديلات مؤقتاً
git stash

# دمج الفروع
git pull origin production --allow-unrelated-histories

# استعادة التعديلات
git stash pop
```

## 🎯 الحل الموصى به (خطوة بخطوة):

### الخطوة 1: تنظيف الملفات المتنازعة
```bash
# في الخادم
cd /home/thme/repos/hosoony
rm -rf bin/
rm -f hosoony-backend/composer.json.bak
rm -f hosoony-backend/composer.lock.bak
git restore hosoony-backend/composer.lock
```

### الخطوة 2: إضافة التعديلات المتبقية
```bash
# في الخادم
git add .
git commit -m "feat: clean up conflicting files before merge"
```

### الخطوة 3: دمج الفروع
```bash
# في الخادم
git config pull.rebase false
git pull origin production --allow-unrelated-histories
```

### الخطوة 4: رفع التعديلات
```bash
# في الخادم
git push origin production
```

## 🔧 الحل البديل (إذا لم يعمل الحل أعلاه):

### إعادة تعيين الفرع بالكامل
```bash
# في الخادم
cd /home/thme/repos/hosoony

# إعادة تعيين الفرع
git reset --hard origin/production

# إضافة التعديلات المحلية (بدون الملفات المتنازعة)
git add .
git commit -m "feat: merge existing cPanel changes with production"

# رفع التعديلات
git push origin production
```

## 🔍 تشخيص إضافي:

### تحقق من الملفات المتنازعة:
```bash
# في الخادم
git status
ls -la bin/ 2>/dev/null || echo "مجلد bin غير موجود"
ls -la hosoony-backend/*.bak 2>/dev/null || echo "لا توجد ملفات .bak"
```

### تحقق من حالة Git:
```bash
# في الخادم
git log --oneline -3
```

## ⚠️ تحذيرات:

- **احتفظ بنسخة احتياطية** من التعديلات المهمة
- **لا تحذف ملفات مهمة** بدون نسخة احتياطية
- **اختبر النشر** بعد التطبيق

## 🎯 النتيجة المتوقعة:

بعد تطبيق الحل:
- ✅ الملفات المتنازعة محذوفة
- ✅ الفروع متداخلة بنجاح
- ✅ التعديلات محفوظة في GitHub
- ✅ النشر يعمل في cPanel

## 📞 إذا استمرت المشكلة:

أرسل لي:
1. **نتيجة الأمر**: `git status`
2. **نتيجة الأمر**: `ls -la bin/`
3. **أي أخطاء** تظهر في Terminal

---
**🔧 جرب الحل الموصى به أعلاه!**

**الأوامر المطلوبة:**
```bash
rm -rf bin/
rm -f hosoony-backend/composer.json.bak
rm -f hosoony-backend/composer.lock.bak
git restore hosoony-backend/composer.lock
git add .
git commit -m "feat: clean up conflicting files before merge"
git config pull.rebase false
git pull origin production --allow-unrelated-histories
git push origin production
```















