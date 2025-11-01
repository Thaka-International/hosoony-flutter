<?php
/**
 * إصلاح سريع لمشكلة عدم ظهور المهام للطالبة
 * هذا السكريبت يربط جميع المهام النشطة بجميع الفصول النشطة
 */

require_once 'vendor/autoload.php';

use App\Models\ClassModel;
use App\Models\DailyTaskDefinition;
use App\Models\ClassTaskAssignment;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== إصلاح مشكلة عدم ظهور المهام للطالبة ===\n\n";

// 1. فحص البيانات الحالية
echo "1. فحص البيانات الحالية:\n";
$activeClasses = ClassModel::where('status', 'active')->get();
$activeTasks = DailyTaskDefinition::where('is_active', true)->get();
$existingAssignments = ClassTaskAssignment::where('is_active', true)->count();

echo "✅ الفصول النشطة: " . $activeClasses->count() . "\n";
echo "✅ المهام النشطة: " . $activeTasks->count() . "\n";
echo "✅ المهام الموكلة حالياً: " . $existingAssignments . "\n\n";

if ($activeClasses->count() == 0) {
    echo "❌ لا توجد فصول نشطة\n";
    exit;
}

if ($activeTasks->count() == 0) {
    echo "❌ لا توجد مهام نشطة\n";
    exit;
}

// 2. ربط المهام بالفصول
echo "2. ربط المهام بالفصول:\n";
$totalAssignments = 0;

foreach ($activeClasses as $class) {
    echo "معالجة الفصل: {$class->name} (ID: {$class->id})\n";
    
    foreach ($activeTasks as $task) {
        // التحقق من وجود الربط
        $existingAssignment = ClassTaskAssignment::where('class_id', $class->id)
            ->where('daily_task_definition_id', $task->id)
            ->first();
        
        if (!$existingAssignment) {
            // إنشاء ربط جديد
            $maxOrder = ClassTaskAssignment::where('class_id', $class->id)->max('order') ?? 0;
            
            ClassTaskAssignment::create([
                'class_id' => $class->id,
                'daily_task_definition_id' => $task->id,
                'is_active' => true,
                'order' => $maxOrder + 1,
            ]);
            
            echo "  ✅ ربط: {$task->name}\n";
            $totalAssignments++;
        } else {
            echo "  ⏭️ موجود: {$task->name}\n";
        }
    }
    echo "\n";
}

// 3. التحقق من النتيجة
echo "3. التحقق من النتيجة:\n";
$newTotalAssignments = ClassTaskAssignment::where('is_active', true)->count();
echo "✅ إجمالي المهام الموكلة الآن: " . $newTotalAssignments . "\n";
echo "✅ تم إضافة: " . ($newTotalAssignments - $existingAssignments) . " ربط جديد\n\n";

// 4. اختبار مع طالبة
echo "4. اختبار مع طالبة:\n";
$student = \App\Models\User::where('role', 'student')->whereNotNull('class_id')->first();
if ($student) {
    $class = ClassModel::find($student->class_id);
    if ($class) {
        $studentTasks = $class->activeTaskAssignments()->with('taskDefinition')->get();
        echo "✅ الطالبة: {$student->name}\n";
        echo "✅ الفصل: {$class->name}\n";
        echo "✅ المهام المتاحة: " . $studentTasks->count() . "\n";
        
        if ($studentTasks->count() > 0) {
            echo "✅ المهام:\n";
            foreach ($studentTasks as $assignment) {
                $task = $assignment->taskDefinition;
                echo "  - {$task->name} ({$task->type})\n";
            }
        } else {
            echo "❌ لا توجد مهام للطالبة\n";
        }
    } else {
        echo "❌ الفصل غير موجود\n";
    }
} else {
    echo "❌ لا توجد طالبة مرتبطة بفصل\n";
}

echo "\n=== انتهاء الإصلاح ===\n";
echo "الآن يجب أن تظهر المهام للطالبات في الويب والتطبيق!\n";















