<?php
// سكريبت فحص مباشر لمحاكاة كود PwaController بالضبط
// استخدم هذا السكريبت لمعرفة سبب عدم ظهور المهام

echo "🔍 فحص مباشر لمحاكاة كود PwaController\n";
echo "=====================================\n\n";

try {
    // محاكاة الكود في PwaController::studentDashboard()
    echo "1. 🔄 محاكاة كود PwaController::studentDashboard():\n";
    
    // محاكاة Auth::user() - استخدام فاطمة الطالبة
    $user = \App\Models\User::where('name', 'فاطمة الطالبة')->first();
    if (!$user) {
        echo "   ❌ لم يتم العثور على فاطمة الطالبة!\n";
        exit;
    }
    
    echo "   - المستخدم: {$user->name}\n";
    echo "   - الدور: {$user->role}\n";
    echo "   - الفصل: {$user->class_id}\n\n";
    
    // محاكاة الكود بالضبط
    $todayTasks = collect();
    if ($user->class_id) {
        echo "2. 📝 جلب المهام الموكلة للفصل:\n";
        
        $taskAssignments = \App\Models\ClassModel::find($user->class_id)
            ?->activeTaskAssignments()
            ->with('taskDefinition')
            ->get();
            
        echo "   - عدد المهام الموكلة: " . $taskAssignments->count() . "\n";
        
        if ($taskAssignments->count() > 0) {
            echo "   - المهام الموكلة:\n";
            foreach ($taskAssignments as $assignment) {
                echo "     * {$assignment->taskDefinition->name} ({$assignment->taskDefinition->type})\n";
            }
        }
        
        echo "\n3. 🔄 تحويل المهام إلى تنسيق العرض:\n";
        
        $todayTasks = $taskAssignments->map(function ($assignment) {
            return (object) [
                'id' => $assignment->id,
                'name' => $assignment->taskDefinition->name,
                'description' => $assignment->taskDefinition->description,
                'type' => $assignment->taskDefinition->type,
                'task_location' => $assignment->taskDefinition->task_location,
                'points_weight' => $assignment->taskDefinition->points_weight,
                'duration_minutes' => $assignment->taskDefinition->duration_minutes,
                'status' => 'pending', // Default status
                'notes' => $assignment->taskDefinition->description,
            ];
        });
        
        echo "   - عدد المهام المعالجة: " . $todayTasks->count() . "\n";
        
        if ($todayTasks->count() > 0) {
            echo "   - المهام المعالجة:\n";
            foreach ($todayTasks as $task) {
                echo "     * {$task->name} ({$task->type}) - {$task->points_weight} نقاط\n";
            }
        }
    } else {
        echo "   ❌ المستخدم غير مرتبط بفصل!\n";
    }
    
    echo "\n4. 📊 النتائج النهائية:\n";
    echo "   - \$todayTasks->count(): " . $todayTasks->count() . "\n";
    
    if ($todayTasks->count() > 0) {
        echo "   ✅ يجب أن تظهر المهام في الواجهة!\n";
        echo "   - إذا لم تظهر، المشكلة في الواجهة الأمامية\n";
    } else {
        echo "   ❌ لا توجد مهام للعرض!\n";
        echo "   - المشكلة في الكود أو البيانات\n";
    }
    
    echo "\n5. 🔍 فحص إضافي للعلاقات:\n";
    
    // فحص العلاقة مباشرة
    $class = \App\Models\ClassModel::find($user->class_id);
    if ($class) {
        echo "   - الفصل: {$class->name}\n";
        echo "   - حالة الفصل: {$class->status}\n";
        echo "   - نشط: " . ($class->isActive() ? 'نعم' : 'لا') . "\n";
        
        // فحص العلاقة taskAssignments
        $allAssignments = $class->taskAssignments()->count();
        echo "   - إجمالي المهام الموكلة: {$allAssignments}\n";
        
        // فحص العلاقة activeTaskAssignments
        $activeAssignments = $class->activeTaskAssignments()->count();
        echo "   - المهام الموكلة النشطة: {$activeAssignments}\n";
        
        // فحص مع taskDefinition
        $withDefinitions = $class->activeTaskAssignments()->with('taskDefinition')->get();
        echo "   - المهام مع التعريفات: " . $withDefinitions->count() . "\n";
        
        if ($withDefinitions->count() > 0) {
            echo "   - التعريفات:\n";
            foreach ($withDefinitions as $assignment) {
                echo "     * {$assignment->taskDefinition->name}\n";
            }
        }
    }
    
    echo "\n=====================================\n";
    echo "✅ تم الانتهاء من الفحص المباشر\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الفحص: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}












