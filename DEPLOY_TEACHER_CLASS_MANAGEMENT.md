# 📦 دليل نشر إدارة الفصول للمعلمات

## ✅ حالة Git
- ✅ جميع التعديلات تم commit
- ✅ جميع التعديلات تم push إلى `origin/production`
- ✅ Repository نظيف وجاهز للنشر

## 📋 الملفات المضافة/المعدلة

### Backend:
1. `app/Http/Controllers/Api/V1/TeacherController.php` - APIs جديدة للمعلمات
2. `routes/api.php` - Routes جديدة

### APIs المضافة:
- `GET /api/v1/teacher/classes` - جلب فصول المعلمة
- `GET /api/v1/teacher/class/students` - جلب طلاب الفصل
- `GET /api/v1/teacher/class/schedules` - جلب جداول الفصل
- `GET /api/v1/teacher/class/task-assignments` - جلب المهام الموكلة
- `GET /api/v1/teacher/class/weekly-schedules` - جلب الخطة الأسبوعية
- `GET /api/v1/teacher/class/companions-publications` - جلب نشرة الرفيقات

## 🚀 خطوات النشر على السيرفر

### 1. الاتصال بالسيرفر:
```bash
ssh thme@your-server.com
```

### 2. الانتقال إلى مجلد المشروع:
```bash
cd /home/thme/public_html/hosoony2-git
```

### 3. جلب التحديثات:
```bash
git pull origin production
```

### 4. التأكد من وجود الملفات الجديدة:
```bash
ls -la app/Http/Controllers/Api/V1/TeacherController.php
cat routes/api.php | grep -A 5 "teacher/class"
```

### 5. تشغيل Composer (إذا لزم الأمر):
```bash
composer dump-autoload
```

### 6. تنظيف Cache:
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 7. إعادة تحميل التطبيق (إذا كان يستخدم OpCache):
```bash
# التحقق من حالة OpCache
php -r "opcache_reset();" || echo "OpCache not enabled"
```

## ✅ التحقق من النشر

### اختبار APIs:
```bash
# الحصول على token أولاً من login
TOKEN="your_token_here"

# اختبار جلب فصول المعلمة
curl -X GET "https://your-domain.com/api/v1/teacher/classes" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# اختبار جلب جداول الفصل
curl -X GET "https://your-domain.com/api/v1/teacher/class/schedules" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# اختبار جلب المهام الموكلة
curl -X GET "https://your-domain.com/api/v1/teacher/class/task-assignments" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# اختبار جلب الخطة الأسبوعية
curl -X GET "https://your-domain.com/api/v1/teacher/class/weekly-schedules" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# اختبار جلب نشرة الرفيقات
curl -X GET "https://your-domain.com/api/v1/teacher/class/companions-publications" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## 📱 تطبيق Flutter

### الملفات المضافة:
- `lib/features/teacher/pages/teacher_class_management_page.dart` - صفحة إدارة الفصول
- تحديثات في `lib/services/api_service.dart` - APIs جديدة

### ملاحظات:
- تطبيق Flutter يحتاج إلى rebuild بعد التحديثات
- تأكد من أن `baseUrl` في `Env` يشير إلى السيرفر الصحيح

## ⚠️ ملاحظات أمان

1. ✅ جميع APIs محمية بـ `auth:sanctum` middleware
2. ✅ التحقق من صلاحيات المعلمة في كل endpoint
3. ✅ عرض البيانات فقط للفصول المخصصة للمعلمة

## 🔍 استكشاف الأخطاء

### إذا كانت APIs لا تعمل:
1. تحقق من وجود `TeacherController.php`
2. تحقق من routes في `api.php`
3. شغل `php artisan route:list | grep teacher`
4. تحقق من logs: `tail -f storage/logs/laravel.log`

### إذا كانت هناك أخطاء 403:
- تأكد من أن المستخدم لديه role: `teacher` أو `teacher_support`
- تأكد من أن المعلمة مرتبطة بفصل في `users.class_id`

### إذا كانت هناك أخطاء 404:
- تحقق من أن Route service provider يعمل بشكل صحيح
- شغل `php artisan config:cache` و `php artisan route:cache`

## ✅ قائمة التحقق النهائية

- [ ] تم pull التحديثات من Git
- [ ] تم تشغيل composer dump-autoload
- [ ] تم تنظيف cache
- [ ] تم اختبار APIs
- [ ] تم التحقق من تطبيق Flutter
- [ ] تم التحقق من الأمان والصلاحيات

---
**تاريخ الإنشاء**: $(date)
**آخر تحديث**: $(date)

