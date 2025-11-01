# 🔧 حل مشكلة التغييرات المحلية في composer.lock

## 🔍 تحليل المشكلة:

**الخطأ**: `Your local changes to the following files would be overwritten by checkout: hosoony-backend/composer.lock`

**السبب**: هناك تغييرات محلية في ملف `composer.lock` تمنع التبديل إلى فرع `production`

## 🚀 الحل السريع:

### الحل الأول: تجاهل التغييرات المحلية

```bash
# في الخادم
git restore hosoony-backend/composer.lock
git clean -fd
git checkout -B production origin/production
```

### الحل الثاني: حفظ التغييرات مؤقتاً

```bash
# في الخادم
git stash
git checkout -B production origin/production
git stash pop  # إذا كنت تريد استعادة التغييرات لاحقاً
```

### الحل الثالث: حذف الملفات المؤقتة

```bash
# في الخادم
rm -f hosoony-backend/composer.lock.bak
rm -f hosoony-backend/composer.json.bak
rm -rf bin/
git restore hosoony-backend/composer.lock
git checkout -B production origin/production
```

## 🎯 الحل الموصى به:

### الخطوة 1: تنظيف التغييرات المحلية
```bash
# في الخادم
git restore hosoony-backend/composer.lock
rm -f hosoony-backend/composer.lock.bak
rm -f hosoony-backend/composer.json.bak
rm -rf bin/
```

### الخطوة 2: التبديل إلى فرع production
```bash
# في الخادم
git checkout -B production origin/production
```

### الخطوة 3: التحقق من النتيجة
```bash
# في الخادم
git status
git branch --show-current
```

## 🔍 بعد حل المشكلة:

### في cPanel:
1. اذهب إلى **Git™ Version Control**
2. افتح الريبو: `/home/thme/repos/hosoony`
3. في **Basic Information**:
   - **Checked-Out Branch**: تأكد من أن `production` مختار
4. اضغط **Update**
5. اذهب إلى **Pull or Deploy**
6. اضغط **Pull** أولاً
7. ثم اضغط **Deploy HEAD Commit**

## ⚠️ تحذيرات:

- **لا تحذف ملفات مهمة** بدون نسخة احتياطية
- **احتفظ بنسخة احتياطية** من التغييرات المهمة
- **تأكد من أن الفرع صحيح** قبل النشر

## 🎯 النتيجة المتوقعة:

بعد تطبيق الحل:
- ✅ الفرع `production` يصبح نشط
- ✅ زر **Deploy HEAD Commit** يصبح مفعل
- ✅ النشر يعمل بنجاح

## 📞 إذا استمرت المشكلة:

أرسل لي:
1. **نتيجة الأمر**: `git status`
2. **نتيجة الأمر**: `git branch --show-current`
3. **أي أخطاء** تظهر في Terminal

---
**🔧 جرب الحل الموصى به أعلاه!**

**المشكلة بسيطة: ملف composer.lock محلي يمنع التبديل. احذفه وستعمل!**















