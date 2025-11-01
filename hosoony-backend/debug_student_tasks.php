<?php
/**
 * تشخيص مشكلة عدم ظهور المهام للطالبة
 * هذا السكريبت يفحص كل خطوة في المنطق
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\ClassModel;
use App\Models\ClassTaskAssignment;
use App\Models\DailyTaskDefinition;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== تشخيص مشكلة عدم ظهور المهام للطالبة ===\n\n";

// 1. فحص الطالبة
echo "1. فحص بيانات الطالبة:\n";
$student = User::where('role', 'student')->first();
if (!$student) {
    echo "❌ لا توجد طالبة في النظام\n";
    exit;
}

echo "✅ الطالبة: {$student->name} (ID: {$student->id})\n";
echo "✅ الفصل: {$student->class_id}\n";
echo "✅ الحالة: {$student->status}\n\n";

// 2. فحص الفصل
echo "2. فحص بيانات الفصل:\n";
if (!$student->class_id) {
    echo "❌ الطالبة غير مرتبطة بأي فصل\n";
    exit;
}

$class = ClassModel::find($student->class_id);
if (!$class) {
    echo "❌ الفصل غير موجود\n";
    exit;
}

echo "✅ الفصل: {$class->name} (ID: {$class->id})\n";
echo "✅ حالة الفصل: {$class->status}\n\n";

// 3. فحص المهام الموكلة للفصل
echo "3. فحص المهام الموكلة للفصل:\n";
$taskAssignments = $class->taskAssignments()->get();
echo "✅ إجمالي المهام الموكلة: " . $taskAssignments->count() . "\n";

$activeTaskAssignments = $class->activeTaskAssignments()->get();
echo "✅ المهام النشطة: " . $activeTaskAssignments->count() . "\n\n";

if ($activeTaskAssignments->count() == 0) {
    echo "❌ لا توجد مهام نشطة للفصل\n";
    echo "الحل: ربط المهام الموجودة بالفصل\n\n";
    
    // عرض المهام المتاحة
    $availableTasks = DailyTaskDefinition::where('is_active', true)->get();
    echo "المهام المتاحة في النظام:\n";
    foreach ($availableTasks as $task) {
        echo "  - {$task->name} ({$task->type})\n";
    }
    echo "\n";
} else {
    echo "✅ المهام النشطة:\n";
    foreach ($activeTaskAssignments as $assignment) {
        $task = $assignment->taskDefinition;
        echo "  - {$task->name} ({$task->type}) - {$task->description}\n";
    }
    echo "\n";
}

// 4. محاكاة منطق PwaController
echo "4. محاكاة منطق PwaController:\n";
$todayTasks = collect();
if ($student->class_id) {
    $taskAssignments = ClassModel::find($student->class_id)
        ?->activeTaskAssignments()
        ->with('taskDefinition')
        ->get();
    
    $todayTasks = $taskAssignments->map(function ($assignment) {
        return (object) [
            'id' => $assignment->id,
            'name' => $assignment->taskDefinition->name,
            'description' => $assignment->taskDefinition->description,
            'type' => $assignment->taskDefinition->type,
            'task_location' => $assignment->taskDefinition->task_location,
            'points_weight' => $assignment->taskDefinition->points_weight,
            'duration_minutes' => $assignment->taskDefinition->duration_minutes,
            'status' => 'pending',
            'notes' => $assignment->taskDefinition->description,
        ];
    });
}

echo "✅ عدد المهام التي ستظهر للطالبة: " . $todayTasks->count() . "\n\n";

if ($todayTasks->count() == 0) {
    echo "❌ المشكلة: لا توجد مهام ستظهر للطالبة\n";
    echo "السبب: إما أن الفصل غير مرتبط بمهام أو المهام غير نشطة\n\n";
    
    echo "الحلول:\n";
    echo "1. ربط المهام الموجودة بالفصل\n";
    echo "2. التأكد من أن المهام مفعلة (is_active = 1)\n";
    echo "3. التأكد من أن الفصل نشط (status = 'active')\n\n";
    
    // عرض SQL للربط
    echo "SQL لربط المهام بالفصل:\n";
    echo "INSERT INTO class_task_assignments (class_id, daily_task_definition_id, is_active, \`order\`, created_at, updated_at)\n";
    echo "SELECT \n";
    echo "    {$class->id} as class_id,\n";
    echo "    d.id as daily_task_definition_id,\n";
    echo "    1 as is_active,\n";
    echo "    ROW_NUMBER() OVER (ORDER BY d.id) as \`order\`,\n";
    echo "    NOW() as created_at,\n";
    echo "    NOW() as updated_at\n";
    echo "FROM daily_task_definitions d\n";
    echo "WHERE d.is_active = 1\n";
    echo "AND NOT EXISTS (\n";
    echo "    SELECT 1 FROM class_task_assignments cta \n";
    echo "    WHERE cta.class_id = {$class->id} \n";
    echo "    AND cta.daily_task_definition_id = d.id\n";
    echo ");\n\n";
} else {
    echo "✅ المهام التي ستظهر للطالبة:\n";
    foreach ($todayTasks as $task) {
        echo "  - {$task->name} ({$task->type}) - {$task->points_weight} نقاط\n";
    }
    echo "\n";
}

// 5. فحص إضافي
echo "5. فحص إضافي:\n";
echo "✅ عدد الفصول النشطة: " . ClassModel::where('status', 'active')->count() . "\n";
echo "✅ عدد المهام النشطة: " . DailyTaskDefinition::where('is_active', true)->count() . "\n";
echo "✅ عدد المهام الموكلة: " . ClassTaskAssignment::where('is_active', true)->count() . "\n\n";

echo "=== انتهاء التشخيص ===\n";















