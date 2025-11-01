<?php
// سكريبت فحص مباشر للمشكلة على الخادم
// استخدم هذا السكريبت لمعرفة سبب عدم ظهور المهام

echo "🔍 فحص مباشر لمشكلة المهام على الخادم\n";
echo "=====================================\n\n";

try {
    // 1. فحص المستخدم الحالي
    echo "1. 👤 فحص المستخدم الحالي:\n";
    $user = Auth::user();
    if ($user) {
        echo "   - الاسم: {$user->name}\n";
        echo "   - الدور: {$user->role}\n";
        echo "   - الفصل: {$user->class_id}\n";
    } else {
        echo "   ❌ لا يوجد مستخدم مسجل دخول!\n";
        exit;
    }
    echo "\n";
    
    // 2. فحص الفصل
    echo "2. 🏫 فحص الفصل:\n";
    if ($user->class_id) {
        $class = \App\Models\ClassModel::find($user->class_id);
        if ($class) {
            echo "   - اسم الفصل: {$class->name}\n";
            echo "   - حالة الفصل: {$class->status}\n";
            echo "   - نشط: " . ($class->isActive() ? 'نعم' : 'لا') . "\n";
        } else {
            echo "   ❌ الفصل غير موجود!\n";
        }
    } else {
        echo "   ❌ المستخدم غير مرتبط بفصل!\n";
    }
    echo "\n";
    
    // 3. فحص المهام الموكلة للفصل
    echo "3. 📝 فحص المهام الموكلة للفصل:\n";
    if ($user->class_id) {
        $taskAssignments = \App\Models\ClassModel::find($user->class_id)
            ?->activeTaskAssignments()
            ->with('taskDefinition')
            ->get();
            
        echo "   - عدد المهام الموكلة: " . $taskAssignments->count() . "\n";
        
        if ($taskAssignments->count() > 0) {
            echo "   - المهام:\n";
            foreach ($taskAssignments as $assignment) {
                echo "     * {$assignment->taskDefinition->name} ({$assignment->taskDefinition->type})\n";
            }
        } else {
            echo "   ❌ لا توجد مهام موكلة للفصل!\n";
        }
    }
    echo "\n";
    
    // 4. محاكاة الكود في PwaController
    echo "4. 🔄 محاكاة كود PwaController:\n";
    $todayTasks = collect();
    if ($user->class_id) {
        $taskAssignments = \App\Models\ClassModel::find($user->class_id)
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
    
    echo "   - عدد المهام المعالجة: " . $todayTasks->count() . "\n";
    
    if ($todayTasks->count() > 0) {
        echo "   - المهام المعالجة:\n";
        foreach ($todayTasks as $task) {
            echo "     * {$task->name} ({$task->type}) - {$task->points_weight} نقاط\n";
        }
    } else {
        echo "   ❌ لا توجد مهام معالجة!\n";
    }
    echo "\n";
    
    // 5. فحص مباشر لقاعدة البيانات
    echo "5. 🗄️ فحص مباشر لقاعدة البيانات:\n";
    
    $directCheck = DB::table('class_task_assignments')
        ->join('classes', 'class_task_assignments.class_id', '=', 'classes.id')
        ->join('daily_task_definitions', 'class_task_assignments.daily_task_definition_id', '=', 'daily_task_definitions.id')
        ->where('class_task_assignments.class_id', $user->class_id)
        ->where('class_task_assignments.is_active', 1)
        ->select('daily_task_definitions.name', 'daily_task_definitions.type', 'daily_task_definitions.points_weight')
        ->get();
        
    echo "   - عدد المهام من الاستعلام المباشر: " . $directCheck->count() . "\n";
    
    if ($directCheck->count() > 0) {
        echo "   - المهام من الاستعلام المباشر:\n";
        foreach ($directCheck as $task) {
            echo "     * {$task->name} ({$task->type}) - {$task->points_weight} نقاط\n";
        }
    }
    echo "\n";
    
    // 6. تشخيص المشكلة
    echo "6. 🔧 تشخيص المشكلة:\n";
    
    if ($todayTasks->count() == 0 && $directCheck->count() > 0) {
        echo "   ❌ المشكلة في العلاقات في Laravel!\n";
        echo "   - البيانات موجودة في قاعدة البيانات\n";
        echo "   - لكن العلاقات لا تعمل بشكل صحيح\n";
    } elseif ($todayTasks->count() > 0) {
        echo "   ✅ البيانات صحيحة والعلاقات تعمل!\n";
        echo "   - المشكلة قد تكون في الواجهة الأمامية\n";
    } else {
        echo "   ❌ لا توجد بيانات في قاعدة البيانات!\n";
    }
    
    echo "\n=====================================\n";
    echo "✅ تم الانتهاء من الفحص المباشر\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الفحص: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}












