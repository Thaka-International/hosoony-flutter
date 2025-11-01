# ملفات التحديث المطلوبة - دليل النشر في cPanel

## 📁 الملفات المطلوبة للتحديث

### 1. Backend Files (Laravel)

#### أ) TaskAssignmentsRelationManager.php
- **المسار المحلي**: `/Users/ibraheem.designer/Documents/hosoony2/hosoony-backend/app/Filament/Resources/ClassResource/RelationManagers/TaskAssignmentsRelationManager.php`
- **المسار في cPanel**: `/public_html/app/Filament/Resources/ClassResource/RelationManagers/TaskAssignmentsRelationManager.php`
- **الوصف**: إصلاح واجهة إدارة المهام وإضافة الأزرار المطلوبة

#### ب) DailyTasksService.php
- **المسار المحلي**: `/Users/ibraheem.designer/Documents/hosoony2/hosoony-backend/app/Services/DailyTasksService.php`
- **المسار في cPanel**: `/public_html/app/Services/DailyTasksService.php`
- **الوصف**: إصلاح منطق جلب المهام المربوطة بالفصول

### 2. Flutter Files (Mobile App)

#### أ) api_service.dart
- **المسار المحلي**: `/Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter/lib/services/api_service.dart`
- **المسار في cPanel**: `/public_html/hosoony_flutter/lib/services/api_service.dart`
- **الوصف**: إصلاح طلبات API لإرسال التاريخ المطلوب

## 🚀 خطوات النشر في cPanel

### الطريقة الأولى: رفع الملفات مباشرة

1. **افتح File Manager في cPanel**
2. **انتقل إلى المجلدات التالية:**

#### للـ Backend:
```
/public_html/app/Filament/Resources/ClassResource/RelationManagers/
/public_html/app/Services/
```

#### للـ Flutter:
```
/public_html/hosoony_flutter/lib/services/
```

3. **ارفع الملفات المحدثة:**
   - `TaskAssignmentsRelationManager.php`
   - `DailyTasksService.php`
   - `api_service.dart`

### الطريقة الثانية: استخدام Git (إذا كان متاحاً)

1. **افتح Terminal في cPanel**
2. **انتقل إلى مجلد المشروع:**
   ```bash
   cd /public_html
   ```
3. **اسحب التحديثات:**
   ```bash
   git pull origin master
   ```

## 📋 قائمة التحقق قبل النشر

### تأكد من:
- [ ] نسخ الملفات إلى المسارات الصحيحة
- [ ] التحقق من صلاحيات الملفات (644 للملفات، 755 للمجلدات)
- [ ] مسح الكاش بعد التحديث

## 🔧 أوامر مسح الكاش (في Terminal)

```bash
# انتقل إلى مجلد المشروع
cd /public_html

# مسح جميع أنواع الكاش
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

## 📱 إعادة بناء تطبيق Flutter (اختياري)

إذا كنت تريد إعادة بناء APK:

```bash
# انتقل إلى مجلد Flutter
cd /public_html/hosoony_flutter

# تنظيف وإعادة بناء
flutter clean
flutter pub get
flutter build apk --release
```

## 🧪 اختبار التحديثات

### 1. اختبار Backend:
- اذهب إلى `/admin/classes`
- اختر أي فصل
- اضغط على تبويب "المهام الموكلة"
- تأكد من ظهور الأزرار الجديدة

### 2. اختبار API:
- اختبر endpoint: `/api/v1/students/{id}/daily-tasks?date=2024-01-01`
- تأكد من إرجاع المهام المربوطة بالفصل فقط

### 3. اختبار تطبيق Flutter:
- سجل دخول كطالب
- اذهب إلى صفحة المهام اليومية
- تأكد من ظهور المهام الصحيحة

## 🐛 استكشاف الأخطاء

### إذا لم تظهر الأزرار:
1. تحقق من مسار الملفات
2. امسح كاش المتصفح
3. تحقق من رسائل الخطأ في Developer Tools

### إذا لم تظهر المهام للطلاب:
1. تأكد من ربط المهام بالفصل
2. تأكد من أن المهام مفعلة
3. تحقق من ربط الطالب بالفصل

## 📊 معلومات إضافية

### صلاحيات الملفات المطلوبة:
- **PHP Files**: 644
- **Directories**: 755
- **Executable Files**: 755

### متطلبات الخادم:
- **PHP**: 8.1 أو أحدث
- **Laravel**: 10.x
- **Filament**: 3.x

---

**تاريخ الإنشاء**: $(date)
**الإصدار**: 1.0
**الحالة**: جاهز للنشر
















