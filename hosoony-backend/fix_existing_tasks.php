<?php
// فحص المشكلة الحقيقية: المهام موجودة لكن لا تظهر في الفصول
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== فحص المشكلة الحقيقية ===\n\n";

try {
    // 1. تأكيد وجود المهام
    echo "1. المهام الموجودة:\n";
    $tasks = \App\Models\DailyTaskDefinition::where('is_active', true)->get();
    echo "عدد المهام النشطة: " . $tasks->count() . "\n";
    foreach ($tasks as $task) {
        echo "- {$task->name} ({$task->type})\n";
    }

    // 2. فحص الفصول
    echo "\n2. الفصول الموجودة:\n";
    $classes = \App\Models\ClassModel::where('status', 'active')->get();
    echo "عدد الفصول النشطة: " . $classes->count() . "\n";
    foreach ($classes as $class) {
        echo "- {$class->name} (ID: {$class->id})\n";
    }

    // 3. فحص المهام الموكلة (هذا هو المهم!)
    echo "\n3. المهام الموكلة للفصول:\n";
    $assignments = \App\Models\ClassTaskAssignment::with(['class', 'taskDefinition'])->get();
    echo "عدد المهام الموكلة: " . $assignments->count() . "\n";
    
    if ($assignments->isEmpty()) {
        echo "❌ المشكلة: المهام موجودة لكنها غير مربوطة بالفصول!\n";
        
        // حل المشكلة: ربط المهام بالفصول
        echo "\n4. ربط المهام بالفصول...\n";
        foreach ($classes as $class) {
            echo "ربط المهام بالفصل: {$class->name}\n";
            foreach ($tasks as $index => $task) {
                $assignment = \App\Models\ClassTaskAssignment::create([
                    'class_id' => $class->id,
                    'daily_task_definition_id' => $task->id,
                    'is_active' => true,
                    'order' => $index + 1,
                ]);
                echo "  ✅ ربطت: {$task->name}\n";
            }
        }
        
        echo "\n✅ تم ربط جميع المهام بالفصول!\n";
    } else {
        foreach ($assignments as $assignment) {
            echo "- {$assignment->taskDefinition->name} → {$assignment->class->name}\n";
        }
    }

    // 5. اختبار العلاقات
    echo "\n5. اختبار العلاقات:\n";
    $firstClass = \App\Models\ClassModel::where('status', 'active')->first();
    if ($firstClass) {
        echo "اختبار الفصل: {$firstClass->name}\n";
        echo "  - عدد المهام الموكلة: " . $firstClass->taskAssignments()->count() . "\n";
        echo "  - عدد المهام النشطة: " . $firstClass->activeTaskAssignments()->count() . "\n";
        
        if ($firstClass->taskAssignments()->count() > 0) {
            echo "  - أول مهمة: " . $firstClass->taskAssignments()->with('taskDefinition')->first()->taskDefinition->name . "\n";
        }
    }

    echo "\n=== انتهى الفحص ===\n";
    echo "الآن اذهب إلى لوحة التحكم واختبار المهام الموكلة.\n";

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
















