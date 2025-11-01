<?php
// سكريبت إصلاح مبسط لمشكلة عدم ظهور المهام
// استخدم هذا السكريبت لحل المشكلة فوراً

echo "🔧 إصلاح مبسط لمشكلة عدم ظهور المهام\n";
echo "===================================\n\n";

try {
    // 1. فحص الطلاب بطريقة أبسط
    echo "1. 👥 فحص الطلاب:\n";
    
    $students = DB::table('users')->where('role', 'student')->get();
    echo "   - إجمالي الطلاب: " . $students->count() . "\n";
    
    foreach ($students as $student) {
        echo "   - الطالب: {$student->name}\n";
        echo "     الفصل: {$student->class_id}\n";
        
        if ($student->class_id) {
            $tasks = DB::table('class_task_assignments')
                ->where('class_id', $student->class_id)
                ->where('is_active', 1)
                ->count();
            echo "     المهام: {$tasks}\n";
        } else {
            echo "     ❌ غير مرتبط بفصل\n";
        }
    }
    
    echo "\n2. 🔧 إصلاح المشكلة:\n";
    
    // ربط الطلاب غير المرتبطين بفصول
    $studentsWithoutClass = DB::table('users')
        ->where('role', 'student')
        ->whereNull('class_id')
        ->get();
        
    if ($studentsWithoutClass->count() > 0) {
        echo "   - طلاب غير مرتبطين بفصول: " . $studentsWithoutClass->count() . "\n";
        
        // ربطهم بالفصل النشط الأول
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
    
    echo "\n3. 📊 النتائج النهائية:\n";
    
    $allStudents = DB::table('users')->where('role', 'student')->get();
    foreach ($allStudents as $student) {
        if ($student->class_id) {
            $tasks = DB::table('class_task_assignments')
                ->where('class_id', $student->class_id)
                ->where('is_active', 1)
                ->count();
            echo "   - {$student->name}: {$tasks} مهام\n";
        }
    }
    
    echo "\n4. 🧪 اختبار نهائي:\n";
    
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
            ->select('daily_task_definitions.name')
            ->get();
            
        echo "   - المهام المتاحة: " . $tasks->count() . "\n";
        
        if ($tasks->count() > 0) {
            echo "   ✅ يجب أن تظهر المهام في الواجهة!\n";
            echo "   - إذا لم تظهر، جرب تسجيل الخروج والدخول مرة أخرى\n";
        } else {
            echo "   ❌ لا توجد مهام للعرض!\n";
        }
    }
    
    echo "\n===================================\n";
    echo "✅ تم الانتهاء من الإصلاح المبسط\n";
    echo "الآن جرب تسجيل الخروج والدخول مرة أخرى في الويب\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الإصلاح: " . $e->getMessage() . "\n";
}