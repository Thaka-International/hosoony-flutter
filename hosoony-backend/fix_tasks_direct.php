<?php
// سكريبت إصلاح مباشر لمشكلة عدم ظهور المهام
// استخدم هذا السكريبت لحل المشكلة فوراً

echo "🔧 إصلاح مباشر لمشكلة عدم ظهور المهام\n";
echo "=====================================\n\n";

try {
    // 1. فحص البيانات
    echo "1. 📊 فحص البيانات:\n";
    
    $students = DB::table('users')->where('role', 'student')->get();
    echo "   - إجمالي الطلاب: " . $students->count() . "\n";
    
    $studentsWithClass = DB::table('users')->where('role', 'student')->whereNotNull('class_id')->get();
    echo "   - الطلاب المرتبطين بفصول: " . $studentsWithClass->count() . "\n";
    
    $activeClasses = DB::table('classes')->where('status', 'active')->get();
    echo "   - الفصول النشطة: " . $activeClasses->count() . "\n";
    
    $activeTasks = DB::table('class_task_assignments')->where('is_active', 1)->count();
    echo "   - المهام الموكلة النشطة: " . $activeTasks . "\n";
    
    echo "\n2. 👥 تفاصيل الطلاب:\n";
    foreach ($students as $student) {
        $tasks = 0;
        if ($student->class_id) {
            $tasks = DB::table('class_task_assignments')
                ->where('class_id', $student->class_id)
                ->where('is_active', 1)
                ->count();
        }
        echo "   - {$student->name}: الفصل {$student->class_id}, المهام {$tasks}\n";
    }
    
    echo "\n3. 🔧 إصلاح المشاكل:\n";
    
    // التأكد من أن جميع الطلاب مرتبطين بفصول
    $studentsWithoutClass = DB::table('users')
        ->where('role', 'student')
        ->whereNull('class_id')
        ->get();
        
    if ($studentsWithoutClass->count() > 0) {
        echo "   - ربط الطلاب غير المرتبطين بفصول...\n";
        $activeClass = DB::table('classes')->where('status', 'active')->first();
        if ($activeClass) {
            foreach ($studentsWithoutClass as $student) {
                DB::table('users')
                    ->where('id', $student->id)
                    ->update(['class_id' => $activeClass->id]);
                echo "     ✅ ربطت {$student->name} بالفصل {$activeClass->name}\n";
            }
        }
    } else {
        echo "   ✅ جميع الطلاب مرتبطين بفصول\n";
    }
    
    // التأكد من وجود مهام موكلة للفصول النشطة
    foreach ($activeClasses as $class) {
        $classTasks = DB::table('class_task_assignments')
            ->where('class_id', $class->id)
            ->where('is_active', 1)
            ->count();
            
        if ($classTasks == 0) {
            echo "   - إضافة مهام للفصل {$class->name}...\n";
            
            // إضافة جميع المهام النشطة للفصل
            $taskDefinitions = DB::table('daily_task_definitions')->where('is_active', 1)->get();
            foreach ($taskDefinitions as $index => $task) {
                DB::table('class_task_assignments')->insert([
                    'class_id' => $class->id,
                    'daily_task_definition_id' => $task->id,
                    'is_active' => 1,
                    'order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            echo "     ✅ أضفت " . $taskDefinitions->count() . " مهام للفصل\n";
        } else {
            echo "   ✅ الفصل {$class->name} لديه {$classTasks} مهام\n";
        }
    }
    
    echo "\n4. 📈 النتائج النهائية:\n";
    
    $finalStudents = DB::table('users')->where('role', 'student')->get();
    foreach ($finalStudents as $student) {
        if ($student->class_id) {
            $tasks = DB::table('class_task_assignments')
                ->where('class_id', $student->class_id)
                ->where('is_active', 1)
                ->count();
            echo "   - {$student->name}: {$tasks} مهام متاحة\n";
        }
    }
    
    echo "\n5. 🧪 اختبار نهائي:\n";
    
    $testStudent = DB::table('users')
        ->where('role', 'student')
        ->whereNotNull('class_id')
        ->first();
        
    if ($testStudent) {
        echo "   - الطالب التجريبي: {$testStudent->name}\n";
        echo "   - فصله: {$testStudent->class_id}\n";
        
        $tasks = DB::table('class_task_assignments')
            ->join('daily_task_definitions', 'class_task_assignments.daily_task_definition_id', '=', 'daily_task_definitions.id')
            ->where('class_task_assignments.class_id', $testStudent->class_id)
            ->where('class_task_assignments.is_active', 1)
            ->select('daily_task_definitions.name', 'daily_task_definitions.type')
            ->get();
            
        echo "   - المهام المتاحة: " . $tasks->count() . "\n";
        
        if ($tasks->count() > 0) {
            echo "   ✅ النظام يعمل بشكل صحيح!\n";
            echo "   - إذا لم تظهر المهام في الويب، المشكلة في المستخدم المسجل دخول\n";
            echo "   - جرب تسجيل الخروج والدخول مرة أخرى\n";
        } else {
            echo "   ❌ لا توجد مهام للعرض!\n";
        }
    }
    
    echo "\n=====================================\n";
    echo "✅ تم الانتهاء من الإصلاح المباشر\n";
    echo "الآن جرب تسجيل الخروج والدخول مرة أخرى في الويب\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الإصلاح: " . $e->getMessage() . "\n";
}












