<?php
// سكريبت إصلاح مباشر لمشكلة عدم ظهور المهام
// استخدم هذا السكريبت لحل المشكلة فوراً

echo "🔧 إصلاح مباشر لمشكلة عدم ظهور المهام\n";
echo "=====================================\n\n";

try {
    // 1. فحص جميع الطلاب
    echo "1. 👥 فحص جميع الطلاب:\n";
    $students = \App\Models\User::where('role', 'student')->get();
    
    foreach ($students as $student) {
        echo "   - الطالب: {$student->name}\n";
        echo "     الفصل: {$student->class_id}\n";
        
        if ($student->class_id) {
            $tasks = \App\Models\ClassModel::find($student->class_id)
                ->activeTaskAssignments()
                ->with('taskDefinition')
                ->get();
                
            echo "     المهام المتاحة: " . $tasks->count() . "\n";
            
            if ($tasks->count() > 0) {
                echo "     المهام:\n";
                foreach ($tasks as $task) {
                    echo "       * {$task->taskDefinition->name}\n";
                }
            }
        } else {
            echo "     ❌ غير مرتبط بفصل\n";
        }
        echo "\n";
    }
    
    // 2. إصلاح المشكلة المحتملة
    echo "2. 🔧 إصلاح المشكلة:\n";
    
    // التأكد من أن جميع الطلاب مرتبطين بفصول نشطة
    $studentsWithoutClass = \App\Models\User::where('role', 'student')->whereNull('class_id')->get();
    if ($studentsWithoutClass->count() > 0) {
        echo "   - طلاب غير مرتبطين بفصول: " . $studentsWithoutClass->count() . "\n";
        
        // ربطهم بالفصل النشط
        $activeClass = \App\Models\ClassModel::where('status', 'active')->first();
        if ($activeClass) {
            foreach ($studentsWithoutClass as $student) {
                $student->update(['class_id' => $activeClass->id]);
                echo "     ✅ ربطت {$student->name} بالفصل {$activeClass->name}\n";
            }
        }
    } else {
        echo "   ✅ جميع الطلاب مرتبطين بفصول\n";
    }
    
    // 3. فحص النتائج النهائية
    echo "\n3. 📊 النتائج النهائية:\n";
    
    $allStudents = \App\Models\User::where('role', 'student')->get();
    foreach ($allStudents as $student) {
        if ($student->class_id) {
            $tasks = \App\Models\ClassModel::find($student->class_id)
                ->activeTaskAssignments()
                ->count();
            echo "   - {$student->name}: {$tasks} مهام\n";
        }
    }
    
    // 4. اختبار نهائي
    echo "\n4. 🧪 اختبار نهائي:\n";
    
    $testStudent = \App\Models\User::where('role', 'student')->whereNotNull('class_id')->first();
    if ($testStudent) {
        echo "   - الطالب التجريبي: {$testStudent->name}\n";
        
        // محاكاة الكود في PwaController
        $todayTasks = collect();
        if ($testStudent->class_id) {
            $taskAssignments = \App\Models\ClassModel::find($testStudent->class_id)
                ->activeTaskAssignments()
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
        
        echo "   - المهام للعرض: " . $todayTasks->count() . "\n";
        
        if ($todayTasks->count() > 0) {
            echo "   ✅ يجب أن تظهر المهام في الواجهة!\n";
            echo "   - إذا لم تظهر، جرب تسجيل الخروج والدخول مرة أخرى\n";
        } else {
            echo "   ❌ لا توجد مهام للعرض!\n";
        }
    }
    
    echo "\n=====================================\n";
    echo "✅ تم الانتهاء من الإصلاح المباشر\n";
    echo "الآن جرب تسجيل الخروج والدخول مرة أخرى في الويب\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الإصلاح: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}












