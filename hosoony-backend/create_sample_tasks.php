<?php
// إضافة بيانات تجريبية للمهام
// استخدم هذا السكريبت في Terminal أو في صفحة PHP مؤقتة

require_once 'vendor/autoload.php';

use App\Models\ClassModel;
use App\Models\DailyTaskDefinition;
use App\Models\ClassTaskAssignment;

echo "=== إضافة بيانات تجريبية للمهام ===\n\n";

// 1. إنشاء تعريفات مهام إذا لم تكن موجودة
$taskDefinitions = [
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

foreach ($taskDefinitions as $taskData) {
    $task = DailyTaskDefinition::firstOrCreate(
        ['name' => $taskData['name']],
        $taskData
    );
    echo "تم إنشاء/تحديث المهمة: {$task->name}\n";
}

// 2. ربط المهام بالفصول الموجودة
$classes = ClassModel::where('status', 'active')->get();
$tasks = DailyTaskDefinition::where('is_active', true)->get();

foreach ($classes as $class) {
    echo "\nربط المهام بالفصل: {$class->name}\n";
    
    foreach ($tasks as $index => $task) {
        $assignment = ClassTaskAssignment::firstOrCreate(
            [
                'class_id' => $class->id,
                'daily_task_definition_id' => $task->id,
            ],
            [
                'is_active' => true,
                'order' => $index + 1,
            ]
        );
        echo "- ربطت المهمة: {$task->name}\n";
    }
}

echo "\n=== تم الانتهاء من إضافة البيانات التجريبية ===\n";
echo "الآن يمكنك الذهاب إلى لوحة التحكم واختبار المهام الموكلة.\n";
















