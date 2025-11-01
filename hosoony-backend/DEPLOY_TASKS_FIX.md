# 🚀 أوامر نشر إصلاح المهام اليومية للإنتاج

## 📋 الملفات المطلوب رفعها:

### 1. ملف إصلاح المهام:
```bash
# رفع ملف إصلاح المهام إلى الخادم
scp fix_production_tasks.php user@server:/path/to/hosoony-backend/
```

### 2. ملفات التوثيق:
```bash
# رفع ملفات التوثيق
scp SOLUTION_DAILY_TASKS_FIX.md user@server:/path/to/hosoony-backend/
scp FIX_STUDENT_TASKS_DISPLAY.md user@server:/path/to/hosoony-backend/
```

## 🔧 أوامر التنفيذ على الخادم:

### 1. تشغيل سكريبت إصلاح المهام:
```bash
# الدخول إلى مجلد المشروع
cd /path/to/hosoony-backend

# تشغيل سكريبت إصلاح المهام
php artisan tinker --execute="
echo '=== إصلاح مشكلة المهام اليومية على الإنتاج ===\n';

// 1. فحص البيانات الموجودة
\$classesCount = DB::table('classes')->where('status', 'active')->count();
\$tasksCount = DB::table('daily_task_definitions')->where('is_active', 1)->count();
\$assignmentsCount = DB::table('class_task_assignments')->count();

echo 'الفصول النشطة: ' . \$classesCount . '\n';
echo 'تعريفات المهام النشطة: ' . \$tasksCount . '\n';
echo 'المهام الموكلة: ' . \$assignmentsCount . '\n\n';

// 2. إنشاء تعريفات مهام إذا لم تكن موجودة
\$taskDefinitions = [
    ['name' => 'حفظ آيات جديدة', 'description' => 'حفظ آيات جديدة من القرآن الكريم', 'type' => 'hifz', 'task_location' => 'homework', 'points_weight' => 10, 'duration_minutes' => 30, 'is_active' => 1],
    ['name' => 'مراجعة المحفوظ', 'description' => 'مراجعة السور والآيات المحفوظة سابقاً', 'type' => 'murajaah', 'task_location' => 'in_class', 'points_weight' => 8, 'duration_minutes' => 25, 'is_active' => 1],
    ['name' => 'تلاوة القرآن', 'description' => 'تلاوة القرآن الكريم مع التجويد', 'type' => 'tilawah', 'task_location' => 'in_class', 'points_weight' => 5, 'duration_minutes' => 20, 'is_active' => 1],
    ['name' => 'تعلم التجويد', 'description' => 'تعلم أحكام التجويد وتطبيقها', 'type' => 'tajweed', 'task_location' => 'in_class', 'points_weight' => 6, 'duration_minutes' => 15, 'is_active' => 1],
    ['name' => 'تفسير القرآن', 'description' => 'دراسة تفسير الآيات القرآنية', 'type' => 'tafseer', 'task_location' => 'homework', 'points_weight' => 7, 'duration_minutes' => 35, 'is_active' => 1],
];

foreach (\$taskDefinitions as \$taskData) {
    \$exists = DB::table('daily_task_definitions')->where('name', \$taskData['name'])->exists();
    if (!\$exists) {
        \$taskData['created_at'] = now();
        \$taskData['updated_at'] = now();
        DB::table('daily_task_definitions')->insert(\$taskData);
        echo 'تم إنشاء المهمة: ' . \$taskData['name'] . '\n';
    } else {
        echo 'المهمة موجودة: ' . \$taskData['name'] . '\n';
    }
}

// 3. ربط المهام بالفصول النشطة
\$classes = DB::table('classes')->where('status', 'active')->get();
\$tasks = DB::table('daily_task_definitions')->where('is_active', 1)->get();

foreach (\$classes as \$class) {
    echo 'ربط المهام بالفصل: ' . \$class->name . '\n';
    foreach (\$tasks as \$index => \$task) {
        \$exists = DB::table('class_task_assignments')->where('class_id', \$class->id)->where('daily_task_definition_id', \$task->id)->exists();
        if (!\$exists) {
            DB::table('class_task_assignments')->insert([
                'class_id' => \$class->id,
                'daily_task_definition_id' => \$task->id,
                'is_active' => 1,
                'order' => \$index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo '  ربطت المهمة: ' . \$task->name . '\n';
        }
    }
}

echo '\n=== تم الانتهاء من الإصلاح ===\n';
"
```

### 2. مسح الكاش:
```bash
# مسح جميع أنواع الكاش
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
```

### 3. فحص النتائج:
```bash
# فحص المهام الموكلة
php artisan tinker --execute="
echo '=== فحص النتائج ===\n';

\$assignments = DB::table('class_task_assignments')
    ->join('classes', 'class_task_assignments.class_id', '=', 'classes.id')
    ->join('daily_task_definitions', 'class_task_assignments.daily_task_definition_id', '=', 'daily_task_definitions.id')
    ->select('classes.name as class_name', 'daily_task_definitions.name as task_name', 'daily_task_definitions.type')
    ->get();

echo 'المهام الموكلة:\n';
foreach (\$assignments as \$assignment) {
    echo '- ' . \$assignment->class_name . ': ' . \$assignment->task_name . ' (' . \$assignment->type . ')\n';
}

\$student = DB::table('users')->where('role', 'student')->whereNotNull('class_id')->first();
if (\$student) {
    echo '\nاختبار مع الطالب: ' . \$student->name . '\n';
    \$studentTasks = DB::table('class_task_assignments')
        ->join('daily_task_definitions', 'class_task_assignments.daily_task_definition_id', '=', 'daily_task_definitions.id')
        ->where('class_task_assignments.class_id', \$student->class_id)
        ->where('class_task_assignments.is_active', 1)
        ->count();
    echo 'المهام المتاحة للطالب: ' . \$studentTasks . '\n';
}
"
```

## 🧪 اختبار النتائج:

### 1. اختبار نسخة الويب:
- اذهب إلى الموقع
- سجل دخول كطالبة
- اذهب إلى لوحة الطالب
- **يجب أن تظهر المهام اليومية**

### 2. اختبار تطبيق Flutter:
- سجل دخول كطالبة في التطبيق
- اذهب إلى صفحة المهام اليومية
- **يجب أن تظهر المهام اليومية**

## 📊 النتائج المتوقعة:

### قبل الإصلاح:
- المهام الموكلة: 0 ❌

### بعد الإصلاح:
- المهام الموكلة: 20+ ✅ (10+ مهام لكل فصل نشط)
- كل طالب يرى المهام الموكلة لفصله ✅

## 🔧 إذا لم تعمل:

### 1. تحقق من الأخطاء:
```bash
# فحص سجلات الأخطاء
tail -f storage/logs/laravel.log
```

### 2. تحقق من قاعدة البيانات:
```bash
# فحص المهام الموكلة
php artisan tinker --execute="
echo 'المهام الموكلة: ' . DB::table('class_task_assignments')->count() . '\n';
echo 'الفصول النشطة: ' . DB::table('classes')->where('status', 'active')->count() . '\n';
echo 'تعريفات المهام: ' . DB::table('daily_task_definitions')->where('is_active', 1)->count() . '\n';
"
```

### 3. إعادة تشغيل الخادم:
```bash
# إعادة تشغيل PHP-FPM
sudo systemctl restart php8.2-fpm

# إعادة تشغيل Nginx
sudo systemctl restart nginx
```

---

**📅 تاريخ النشر:** $(date)  
**🎯 الهدف:** إصلاح مشكلة عدم ظهور المهام اليومية للطلاب  
**✅ الحالة:** جاهز للنشر على الإنتاج












