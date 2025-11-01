<?php
// سكريبت فحص شامل لقاعدة البيانات - المهام اليومية
// استخدم هذا السكريبت لفحص البيانات المطلوبة

echo "🔍 فحص شامل لقاعدة البيانات - المهام اليومية\n";
echo "===============================================\n\n";

try {
    // 1. فحص جدول المستخدمين والطلاب
    echo "1. 📊 فحص المستخدمين والطلاب:\n";
    $totalUsers = DB::table('users')->count();
    $students = DB::table('users')->where('role', 'student')->get();
    $studentsWithClass = DB::table('users')->where('role', 'student')->whereNotNull('class_id')->get();
    
    echo "   - إجمالي المستخدمين: {$totalUsers}\n";
    echo "   - إجمالي الطلاب: " . $students->count() . "\n";
    echo "   - الطلاب المرتبطين بفصول: " . $studentsWithClass->count() . "\n";
    
    if ($studentsWithClass->count() > 0) {
        echo "   - أمثلة على الطلاب:\n";
        foreach ($studentsWithClass->take(3) as $student) {
            echo "     * {$student->name} (الفصل: {$student->class_id})\n";
        }
    }
    echo "\n";
    
    // 2. فحص جدول الفصول
    echo "2. 🏫 فحص الفصول:\n";
    $totalClasses = DB::table('classes')->count();
    $activeClasses = DB::table('classes')->where('status', 'active')->get();
    
    echo "   - إجمالي الفصول: {$totalClasses}\n";
    echo "   - الفصول النشطة: " . $activeClasses->count() . "\n";
    
    if ($activeClasses->count() > 0) {
        echo "   - الفصول النشطة:\n";
        foreach ($activeClasses as $class) {
            $studentsInClass = DB::table('users')->where('class_id', $class->id)->where('role', 'student')->count();
            echo "     * {$class->name} (ID: {$class->id}) - الطلاب: {$studentsInClass}\n";
        }
    }
    echo "\n";
    
    // 3. فحص جدول تعريفات المهام
    echo "3. 📝 فحص تعريفات المهام اليومية:\n";
    $totalTaskDefinitions = DB::table('daily_task_definitions')->count();
    $activeTaskDefinitions = DB::table('daily_task_definitions')->where('is_active', 1)->get();
    
    echo "   - إجمالي تعريفات المهام: {$totalTaskDefinitions}\n";
    echo "   - تعريفات المهام النشطة: " . $activeTaskDefinitions->count() . "\n";
    
    if ($activeTaskDefinitions->count() > 0) {
        echo "   - المهام النشطة:\n";
        foreach ($activeTaskDefinitions as $task) {
            echo "     * {$task->name} ({$task->type}) - {$task->points_weight} نقاط\n";
        }
    }
    echo "\n";
    
    // 4. فحص جدول ربط المهام بالفصول
    echo "4. 🔗 فحص ربط المهام بالفصول:\n";
    $totalAssignments = DB::table('class_task_assignments')->count();
    $activeAssignments = DB::table('class_task_assignments')->where('is_active', 1)->get();
    
    echo "   - إجمالي المهام الموكلة: {$totalAssignments}\n";
    echo "   - المهام الموكلة النشطة: " . $activeAssignments->count() . "\n";
    
    if ($activeAssignments->count() > 0) {
        echo "   - المهام الموكلة:\n";
        $assignments = DB::table('class_task_assignments')
            ->join('classes', 'class_task_assignments.class_id', '=', 'classes.id')
            ->join('daily_task_definitions', 'class_task_assignments.daily_task_definition_id', '=', 'daily_task_definitions.id')
            ->select('classes.name as class_name', 'daily_task_definitions.name as task_name', 'daily_task_definitions.type', 'class_task_assignments.is_active')
            ->get();
            
        foreach ($assignments as $assignment) {
            $status = $assignment->is_active ? 'نشط' : 'غير نشط';
            echo "     * {$assignment->class_name}: {$assignment->task_name} ({$assignment->type}) - {$status}\n";
        }
    }
    echo "\n";
    
    // 5. فحص جدول السجلات اليومية
    echo "5. 📊 فحص السجلات اليومية:\n";
    $totalLogs = DB::table('daily_logs')->count();
    $todayLogs = DB::table('daily_logs')->whereDate('log_date', today())->count();
    
    echo "   - إجمالي السجلات اليومية: {$totalLogs}\n";
    echo "   - سجلات اليوم: {$todayLogs}\n";
    echo "\n";
    
    // 6. اختبار شامل مع طالب محدد
    echo "6. 🧪 اختبار شامل مع طالب:\n";
    $testStudent = DB::table('users')->where('role', 'student')->whereNotNull('class_id')->first();
    
    if ($testStudent) {
        echo "   - الطالب التجريبي: {$testStudent->name}\n";
        echo "   - فصله: {$testStudent->class_id}\n";
        
        // فحص فصل الطالب
        $studentClass = DB::table('classes')->where('id', $testStudent->class_id)->first();
        if ($studentClass) {
            echo "   - اسم الفصل: {$studentClass->name}\n";
            echo "   - حالة الفصل: {$studentClass->status}\n";
            
            // فحص المهام الموكلة للفصل
            $studentTasks = DB::table('class_task_assignments')
                ->join('daily_task_definitions', 'class_task_assignments.daily_task_definition_id', '=', 'daily_task_definitions.id')
                ->where('class_task_assignments.class_id', $testStudent->class_id)
                ->where('class_task_assignments.is_active', 1)
                ->select('daily_task_definitions.name', 'daily_task_definitions.type', 'daily_task_definitions.points_weight')
                ->get();
                
            echo "   - المهام المتاحة للطالب: " . $studentTasks->count() . "\n";
            
            if ($studentTasks->count() > 0) {
                echo "   - المهام:\n";
                foreach ($studentTasks as $task) {
                    echo "     * {$task->name} ({$task->type}) - {$task->points_weight} نقاط\n";
                }
            } else {
                echo "   ❌ لا توجد مهام متاحة للطالب!\n";
            }
        } else {
            echo "   ❌ الفصل غير موجود!\n";
        }
    } else {
        echo "   ❌ لا يوجد طلاب مرتبطين بفصول!\n";
    }
    echo "\n";
    
    // 7. تشخيص المشاكل
    echo "7. 🔧 تشخيص المشاكل:\n";
    
    $issues = [];
    
    if ($studentsWithClass->count() == 0) {
        $issues[] = "لا يوجد طلاب مرتبطين بفصول";
    }
    
    if ($activeClasses->count() == 0) {
        $issues[] = "لا توجد فصول نشطة";
    }
    
    if ($activeTaskDefinitions->count() == 0) {
        $issues[] = "لا توجد تعريفات مهام نشطة";
    }
    
    if ($activeAssignments->count() == 0) {
        $issues[] = "لا توجد مهام مربوطة بالفصول";
    }
    
    if (count($issues) > 0) {
        echo "   ❌ المشاكل المكتشفة:\n";
        foreach ($issues as $issue) {
            echo "     * {$issue}\n";
        }
    } else {
        echo "   ✅ لا توجد مشاكل واضحة في البيانات\n";
    }
    echo "\n";
    
    // 8. التوصيات
    echo "8. 💡 التوصيات:\n";
    
    if ($activeAssignments->count() == 0) {
        echo "   🔧 يجب ربط المهام بالفصول:\n";
        echo "     - تشغيل سكريبت إصلاح المهام\n";
        echo "     - أو إضافة بيانات يدوياً في جدول class_task_assignments\n";
    }
    
    if ($activeTaskDefinitions->count() == 0) {
        echo "   🔧 يجب إنشاء تعريفات المهام:\n";
        echo "     - إضافة بيانات في جدول daily_task_definitions\n";
    }
    
    if ($studentsWithClass->count() == 0) {
        echo "   🔧 يجب ربط الطلاب بالفصول:\n";
        echo "     - تحديث class_id في جدول users\n";
    }
    
    echo "\n===============================================\n";
    echo "✅ تم الانتهاء من الفحص الشامل\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الفحص: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}












