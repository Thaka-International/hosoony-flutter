<?php
// فحص بيانات المهام في قاعدة البيانات
// استخدم هذا السكريبت في Terminal أو في صفحة PHP مؤقتة

require_once 'vendor/autoload.php';

use App\Models\ClassModel;
use App\Models\DailyTaskDefinition;
use App\Models\ClassTaskAssignment;

echo "=== فحص بيانات المهام ===\n\n";

// 1. فحص الفصول الموجودة
echo "1. الفصول الموجودة:\n";
$classes = ClassModel::all();
foreach ($classes as $class) {
    echo "- ID: {$class->id}, Name: {$class->name}, Status: {$class->status}\n";
}

echo "\n2. تعريفات المهام الموجودة:\n";
$taskDefinitions = DailyTaskDefinition::all();
foreach ($taskDefinitions as $task) {
    echo "- ID: {$task->id}, Name: {$task->name}, Type: {$task->type}, Active: " . ($task->is_active ? 'Yes' : 'No') . "\n";
}

echo "\n3. المهام الموكلة للفصول:\n";
$taskAssignments = ClassTaskAssignment::with(['class', 'taskDefinition'])->get();
if ($taskAssignments->isEmpty()) {
    echo "لا توجد مهام موكلة!\n";
} else {
    foreach ($taskAssignments as $assignment) {
        echo "- Class: {$assignment->class->name}, Task: {$assignment->taskDefinition->name}, Active: " . ($assignment->is_active ? 'Yes' : 'No') . "\n";
    }
}

echo "\n4. اختبار العلاقات:\n";
$firstClass = ClassModel::first();
if ($firstClass) {
    echo "الفصل الأول: {$firstClass->name}\n";
    echo "عدد المهام الموكلة: " . $firstClass->taskAssignments()->count() . "\n";
    echo "عدد المهام النشطة: " . $firstClass->activeTaskAssignments()->count() . "\n";
}

echo "\n=== انتهى الفحص ===\n";
















