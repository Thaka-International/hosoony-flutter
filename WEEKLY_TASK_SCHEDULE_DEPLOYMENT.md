# 📅 دليل نشر نظام الخطة الأسبوعية للأنصبة

## ✅ ما تم إنجازه

### Backend:
1. ✅ Migration: `2025_01_20_100000_create_weekly_task_schedules_table.php`
2. ✅ Model: `WeeklyTaskSchedule.php`
3. ✅ Relations: تم إضافتها في `ClassModel` و `ClassTaskAssignment`
4. ✅ Filament RelationManager: `WeeklyTaskSchedulesRelationManager.php`
5. ✅ API Update: تم تحديث `DailyTasksService` لإرجاع `weekly_task_details`

---

## 🚀 خطوات النشر

### 1. الانتقال لمجلد Backend
```bash
cd hosoony-backend
```

### 2. تشغيل Migration
```bash
php artisan migrate
```

**ملاحظة:** إذا كانت هناك مشكلة في Migration، يمكن تشغيل:
```bash
php artisan migrate --force
```

### 3. التحقق من الجدول
```bash
php artisan tinker
```
ثم داخل Tinker:
```php
Schema::hasTable('weekly_task_schedules'); // يجب أن يرجع true
exit
```

### 4. التحقق من Model
```php
\App\Models\WeeklyTaskSchedule::count(); // يجب أن يرجع 0 (جدول فارغ)
```

### 5. اختبار Filament
- افتح لوحة الإدارة
- اذهب إلى الفصول
- افتح فصل معين
- تحقق من وجود تبويب "الخطة الأسبوعية"

### 6. اختبار API
```bash
# اختبار جلب المهام اليومية (يجب أن يحتوي على weekly_task_details)
curl -X GET "https://thakaa.me/api/v1/students/{student_id}/daily-tasks?date=2025-01-20" \
  -H "Authorization: Bearer {token}"
```

---

## 📋 الملفات الجديدة/المعدلة

### ملفات جديدة:
1. `database/migrations/2025_01_20_100000_create_weekly_task_schedules_table.php`
2. `app/Models/WeeklyTaskSchedule.php`
3. `app/Filament/Resources/ClassResource/RelationManagers/WeeklyTaskSchedulesRelationManager.php`

### ملفات معدلة:
1. `app/Models/ClassModel.php` - إضافة relation `weeklyTaskSchedules()`
2. `app/Models/ClassTaskAssignment.php` - إضافة relation `weeklyTaskSchedules()`
3. `app/Services/DailyTasksService.php` - إضافة `weekly_task_details` في Response
4. `app/Filament/Resources/ClassResource.php` - إضافة `WeeklyTaskSchedulesRelationManager` في `getRelations()`

---

## 🔍 التحقق من النشر

### 1. التحقق من Migration:
```sql
SHOW TABLES LIKE 'weekly_task_schedules';
DESCRIBE weekly_task_schedules;
```

### 2. التحقق من API Response:
الـ Response يجب أن يحتوي على:
```json
{
  "date": "2025-01-20",
  "class_id": 1,
  "tasks": [
    {
      "task_id": 1,
      "class_task_assignment_id": 1,
      "task_name": "...",
      "weekly_task_details": "صفحة 23" // ⭐ جديد - أو null
    }
  ]
}
```

### 3. التحقق من Filament:
- افتح صفحة فصل
- يجب أن يظهر تبويب "الخطة الأسبوعية"
- يمكن إضافة/تعديل/حذف سجلات الجدول الأسبوعي

---

## 🐛 حل المشاكل المحتملة

### مشكلة 1: Migration فشل
```bash
# إعادة Migration
php artisan migrate:refresh --path=database/migrations/2025_01_20_100000_create_weekly_task_schedules_table.php
```

### مشكلة 2: Class غير موجود
```bash
# التأكد من أن WeeklyTaskSchedule موجود
php artisan tinker
\App\Models\WeeklyTaskSchedule::first();
```

### مشكلة 3: Tab لا يظهر في Filament
- تحقق من أن `WeeklyTaskSchedulesRelationManager` موجود في `ClassResource::getRelations()`
- امسح الكاش: `php artisan filament:cache-components`

---

## 📱 الخطوة التالية (Flutter)

بعد النشر الناجح في Backend، يجب تحديث Flutter:

1. تحديث Model لإضافة `weekly_task_details`
2. تحديث UI لعرض `weekly_task_details` بدلاً من `description`

---

## ✅ قائمة التحقق

- [ ] Migration تم تنفيذه بنجاح
- [ ] الجدول `weekly_task_schedules` موجود في قاعدة البيانات
- [ ] Tab "الخطة الأسبوعية" يظهر في Filament
- [ ] يمكن إضافة/تعديل/حذف سجلات الجدول
- [ ] API يرجع `weekly_task_details` في Response
- [ ] لا توجد أخطاء في Logs

---

**تاريخ الإنشاء**: 2025-01-20  
**الحالة**: جاهز للنشر


