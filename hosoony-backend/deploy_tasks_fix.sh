#!/bin/bash

# سكريبت نشر إصلاح المهام اليومية للإنتاج
# استخدم هذا السكريبت على الخادم

echo "🚀 بدء نشر إصلاح المهام اليومية للإنتاج..."
echo "================================================"

# 1. فحص البيانات الموجودة
echo "📊 فحص البيانات الموجودة..."
php artisan tinker --execute="
echo '=== فحص البيانات الموجودة ===\n';
\$classesCount = DB::table('classes')->where('status', 'active')->count();
\$tasksCount = DB::table('daily_task_definitions')->where('is_active', 1)->count();
\$assignmentsCount = DB::table('class_task_assignments')->count();
echo 'الفصول النشطة: ' . \$classesCount . '\n';
echo 'تعريفات المهام النشطة: ' . \$tasksCount . '\n';
echo 'المهام الموكلة: ' . \$assignmentsCount . '\n\n';
"

# 2. إنشاء تعريفات المهام
echo "🔧 إنشاء تعريفات المهام..."
php artisan tinker --execute="
echo '=== إنشاء تعريفات المهام ===\n';
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
        echo '✓ تم إنشاء المهمة: ' . \$taskData['name'] . '\n';
    } else {
        echo '- المهمة موجودة: ' . \$taskData['name'] . '\n';
    }
}
"

# 3. ربط المهام بالفصول
echo "🔗 ربط المهام بالفصول..."
php artisan tinker --execute="
echo '=== ربط المهام بالفصول ===\n';
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
            echo '  ✓ ربطت المهمة: ' . \$task->name . '\n';
        } else {
            echo '  - المهمة مربوطة: ' . \$task->name . '\n';
        }
    }
}
"

# 4. مسح الكاش
echo "🧹 مسح الكاش..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache

# 5. فحص النتائج النهائية
echo "📈 فحص النتائج النهائية..."
php artisan tinker --execute="
echo '=== النتائج النهائية ===\n';
\$finalAssignmentsCount = DB::table('class_task_assignments')->count();
\$activeAssignmentsCount = DB::table('class_task_assignments')->where('is_active', 1)->count();
echo 'إجمالي المهام الموكلة: ' . \$finalAssignmentsCount . '\n';
echo 'المهام الموكلة النشطة: ' . \$activeAssignmentsCount . '\n\n';

\$student = DB::table('users')->where('role', 'student')->whereNotNull('class_id')->first();
if (\$student) {
    echo 'اختبار مع الطالب: ' . \$student->name . '\n';
    \$studentTasks = DB::table('class_task_assignments')
        ->join('daily_task_definitions', 'class_task_assignments.daily_task_definition_id', '=', 'daily_task_definitions.id')
        ->where('class_task_assignments.class_id', \$student->class_id)
        ->where('class_task_assignments.is_active', 1)
        ->count();
    echo 'المهام المتاحة للطالب: ' . \$studentTasks . '\n';
    
    if (\$studentTasks > 0) {
        echo '✅ نجح الإصلاح! المهام تظهر للطلاب.\n';
    } else {
        echo '❌ فشل الإصلاح! لا توجد مهام للطلاب.\n';
    }
} else {
    echo '⚠️ لا يوجد طلاب مرتبطين بفصول\n';
}
"

echo "================================================"
echo "🎉 تم الانتهاء من نشر إصلاح المهام اليومية!"
echo "الآن يمكنك اختبار الموقع والتطبيق."
echo "================================================"












