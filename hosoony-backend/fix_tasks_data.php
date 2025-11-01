<?php
// سكريبت فحص وإصلاح بيانات المهام
// استخدم هذا السكريبت في Terminal

require_once 'vendor/autoload.php';

use App\Models\ClassModel;
use App\Models\DailyTaskDefinition;
use App\Models\ClassTaskAssignment;

echo "=== فحص وإصلاح بيانات المهام ===\n\n";

// 1. فحص الفصول
echo "1. فحص الفصول:\n";
$classes = ClassModel::all();
if ($classes->isEmpty()) {
    echo "❌ لا توجد فصول! يجب إنشاء فصول أولاً.\n";
    exit;
}

foreach ($classes as $class) {
    echo "✅ الفصل: {$class->name} (ID: {$class->id}, Status: {$class->status})\n";
}

// 2. فحص تعريفات المهام
echo "\n2. فحص تعريفات المهام:\n";
$taskDefinitions = DailyTaskDefinition::all();
if ($taskDefinitions->isEmpty()) {
    echo "❌ لا توجد تعريفات مهام! إنشاء تعريفات تجريبية...\n";
    
    $sampleTasks = [
        [
            'name' => 'حفظ سورة البقرة',
            'description' => 'حفظ آيات من سورة البقرة',
            'type' => 'hifz',
            'task_location' => 'homework',
            'points_weight' => 5,
            'duration_minutes' => 30,
            'is_active' => true,
        ],
        [
            'name' => 'مراجعة المحفوظ',
            'description' => 'مراجعة السور المحفوظة سابقاً',
            'type' => 'murajaah',
            'task_location' => 'in_class',
            'points_weight' => 3,
            'duration_minutes' => 20,
            'is_active' => true,
        ],
        [
            'name' => 'تلاوة القرآن',
            'description' => 'تلاوة القرآن الكريم',
            'type' => 'tilawah',
            'task_location' => 'in_class',
            'points_weight' => 2,
            'duration_minutes' => 15,
            'is_active' => true,
        ],
    ];
    
    foreach ($sampleTasks as $taskData) {
        $task = DailyTaskDefinition::create($taskData);
        echo "✅ تم إنشاء المهمة: {$task->name}\n";
    }
} else {
    foreach ($taskDefinitions as $task) {
        echo "✅ المهمة: {$task->name} (نشطة: " . ($task->is_active ? 'نعم' : 'لا') . ")\n";
    }
}

// 3. فحص المهام الموكلة
echo "\n3. فحص المهام الموكلة:\n";
$taskAssignments = ClassTaskAssignment::with(['class', 'taskDefinition'])->get();
if ($taskAssignments->isEmpty()) {
    echo "❌ لا توجد مهام موكلة! ربط المهام بالفصول...\n";
    
    $classes = ClassModel::where('status', 'active')->get();
    $tasks = DailyTaskDefinition::where('is_active', true)->get();
    
    foreach ($classes as $class) {
        echo "ربط المهام بالفصل: {$class->name}\n";
        
        foreach ($tasks as $index => $task) {
            $assignment = ClassTaskAssignment::create([
                'class_id' => $class->id,
                'daily_task_definition_id' => $task->id,
                'is_active' => true,
                'order' => $index + 1,
            ]);
            echo "  ✅ ربطت المهمة: {$task->name}\n";
        }
    }
} else {
    foreach ($taskAssignments as $assignment) {
        echo "✅ المهمة: {$assignment->taskDefinition->name} → الفصل: {$assignment->class->name}\n";
    }
}

// 4. اختبار العلاقات
echo "\n4. اختبار العلاقات:\n";
$firstClass = ClassModel::first();
if ($firstClass) {
    echo "اختبار الفصل: {$firstClass->name}\n";
    echo "  - عدد المهام الموكلة: " . $firstClass->taskAssignments()->count() . "\n";
    echo "  - عدد المهام النشطة: " . $firstClass->activeTaskAssignments()->count() . "\n";
    
    if ($firstClass->taskAssignments()->count() > 0) {
        echo "  - أول مهمة: " . $firstClass->taskAssignments()->with('taskDefinition')->first()->taskDefinition->name . "\n";
    }
}

echo "\n=== انتهى الفحص والإصلاح ===\n";
echo "الآن يمكنك الذهاب إلى لوحة التحكم واختبار المهام الموكلة.\n";
















