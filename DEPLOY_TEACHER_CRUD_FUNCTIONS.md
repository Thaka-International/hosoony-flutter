# 📦 دليل نشر وظائف CRUD الكاملة للمعلمات

## ✅ حالة Git
- ✅ جميع التعديلات تم commit
- ✅ جميع التعديلات تم push إلى `origin/production`
- ✅ Repository نظيف وجاهز للنشر

## 📋 الملفات المضافة/المعدلة

### Backend:
1. `app/Http/Controllers/Api/V1/TeacherController.php` - إضافة جميع وظائف CRUD:
   - Class Schedules: Create, Update, Delete, Toggle
   - Task Assignments: Create, Update, Delete
   - Weekly Task Schedules: Update Details, Delete
   - Companions Publications: Generate, Lock, Unlock, Publish, Delete

2. `routes/api.php` - إضافة Routes الجديدة مع ترتيب صحيح:
   - Static routes قبل parameterized routes
   - جميع HTTP methods (GET, POST, PUT, PATCH, DELETE)

### Flutter:
1. `lib/services/api_service.dart` - إضافة جميع CRUD APIs
2. `lib/features/teacher/pages/teacher_class_management_page.dart` - صفحة إدارة الفصول مع وظائف التعديل

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

### 4. تنظيف Cache (مهم جداً):
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 5. إعادة بناء Route Cache:
```bash
php artisan route:cache
php artisan config:cache
```

### 6. تشغيل Composer:
```bash
composer dump-autoload
```

### 7. التحقق من Routes:
```bash
php artisan route:list | grep "teacher/class"
```

## ✅ التحقق من النشر

### اختبار APIs:
```bash
# الحصول على token أولاً
TOKEN="your_token_here"

# اختبار إنشاء جدول (POST)
curl -X POST "https://thakaa.me/api/v1/teacher/class/schedules" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "day_of_week": "saturday",
    "start_time": "16:00",
    "end_time": "18:00",
    "is_active": true
  }'

# اختبار تفعيل/إلغاء تفعيل (PATCH)
curl -X PATCH "https://thakaa.me/api/v1/teacher/class/schedules/1/toggle" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# اختبار تحديث جدول (PUT)
curl -X PUT "https://thakaa.me/api/v1/teacher/class/schedules/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "start_time": "17:00",
    "end_time": "19:00"
  }'

# اختبار حذف جدول (DELETE)
curl -X DELETE "https://thakaa.me/api/v1/teacher/class/schedules/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## 🔍 استكشاف الأخطاء

### إذا كانت APIs تعطي 405 Method Not Allowed:
1. **تحقق من ترتيب Routes**: يجب أن تكون static routes قبل parameterized
2. **شغل `php artisan route:clear`**: لمسح route cache
3. **تحقق من Route List**: `php artisan route:list | grep schedules`

### إذا كانت APIs تعطي 404:
- تحقق من أن Routes مسجلة: `php artisan route:list`
- تحقق من middleware groups
- تحقق من namespace في RouteServiceProvider

### إذا كانت APIs تعطي 403:
- تحقق من أن المستخدم لديه role صحيح
- تحقق من أن المعلمة مرتبطة بفصل (class_id)
- تحقق من الصلاحيات في TeacherController

## 📱 تطبيق Flutter

### الملفات المحدثة:
- `lib/services/api_service.dart` - جميع CRUD APIs
- `lib/features/teacher/pages/teacher_class_management_page.dart` - واجهات التعديل

### بعد النشر:
- تطبيق Flutter يجب أن يعمل مباشرة
- لا حاجة إلى rebuild إذا كان baseUrl صحيح

## ⚠️ ملاحظات أمان

1. ✅ جميع APIs محمية بـ `auth:sanctum` middleware
2. ✅ التحقق من صلاحيات المعلمة في كل endpoint
3. ✅ عرض البيانات فقط للفصول المخصصة للمعلمة
4. ✅ Validation كامل لجميع البيانات المدخلة

## ✅ قائمة التحقق النهائية

- [ ] تم pull التحديثات من Git
- [ ] تم تشغيل `php artisan route:clear`
- [ ] تم تشغيل `php artisan config:clear`
- [ ] تم تشغيل `php artisan cache:clear`
- [ ] تم تشغيل `php artisan route:cache`
- [ ] تم تشغيل `composer dump-autoload`
- [ ] تم اختبار POST API (إنشاء جدول)
- [ ] تم اختبار PUT API (تحديث جدول)
- [ ] تم اختبار PATCH API (تفعيل)
- [ ] تم اختبار DELETE API (حذف)
- [ ] تم التحقق من تطبيق Flutter

---
**تاريخ الإنشاء**: $(date)
**آخر تحديث**: $(date)
**Routes المضافة**: 15+ route جديد
**APIs المضافة**: 10+ endpoint جديد

