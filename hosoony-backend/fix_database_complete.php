<?php
// سكريبت إصلاح شامل لقاعدة البيانات - المهام اليومية
// استخدم هذا السكريبت لحل مشكلة عدم ظهور المهام

echo "🔧 سكريبت الإصلاح الشامل لقاعدة البيانات\n";
echo "==========================================\n\n";

try {
    // 1. فحص البيانات الموجودة
    echo "1. 📊 فحص البيانات الموجودة...\n";
    
    $studentsCount = DB::table('users')->where('role', 'student')->whereNotNull('class_id')->count();
    $activeClassesCount = DB::table('classes')->where('status', 'active')->count();
    $activeTasksCount = DB::table('daily_task_definitions')->where('is_active', 1)->count();
    $assignmentsCount = DB::table('class_task_assignments')->where('is_active', 1)->count();
    
    echo "   - الطلاب المرتبطين بفصول: {$studentsCount}\n";
    echo "   - الفصول النشطة: {$activeClassesCount}\n";
    echo "   - تعريفات المهام النشطة: {$activeTasksCount}\n";
    echo "   - المهام الموكلة: {$assignmentsCount}\n\n";
    
    // 2. إنشاء تعريفات المهام إذا لم تكن موجودة
    echo "2. 📝 إنشاء تعريفات المهام...\n";
    
    $taskDefinitions = [
        [
            'name' => 'حفظ آيات جديدة',
            'description' => 'حفظ آيات جديدة من القرآن الكريم',
            'type' => 'hifz',
            'task_location' => 'homework',
            'points_weight' => 10,
            'duration_minutes' => 30,
            'is_active' => 1,
        ],
        [
            'name' => 'مراجعة المحفوظ',
            'description' => 'مراجعة السور والآيات المحفوظة سابقاً',
            'type' => 'murajaah',
            'task_location' => 'in_class',
            'points_weight' => 8,
            'duration_minutes' => 25,
            'is_active' => 1,
        ],
        [
            'name' => 'تلاوة القرآن',
            'description' => 'تلاوة القرآن الكريم مع التجويد',
            'type' => 'tilawah',
            'task_location' => 'in_class',
            'points_weight' => 5,
            'duration_minutes' => 20,
            'is_active' => 1,
        ],
        [
            'name' => 'تعلم التجويد',
            'description' => 'تعلم أحكام التجويد وتطبيقها',
            'type' => 'tajweed',
            'task_location' => 'in_class',
            'points_weight' => 6,
            'duration_minutes' => 15,
            'is_active' => 1,
        ],
        [
            'name' => 'تفسير القرآن',
            'description' => 'دراسة تفسير الآيات القرآنية',
            'type' => 'tafseer',
            'task_location' => 'homework',
            'points_weight' => 7,
            'duration_minutes' => 35,
            'is_active' => 1,
        ],
    ];
    
    $createdTasks = 0;
    foreach ($taskDefinitions as $taskData) {
        $exists = DB::table('daily_task_definitions')
            ->where('name', $taskData['name'])
            ->exists();
            
        if (!$exists) {
            $taskData['created_at'] = now();
            $taskData['updated_at'] = now();
            DB::table('daily_task_definitions')->insert($taskData);
            $createdTasks++;
            echo "   ✅ تم إنشاء المهمة: {$taskData['name']}\n";
        } else {
            echo "   - المهمة موجودة: {$taskData['name']}\n";
        }
    }
    
    // 3. ربط المهام بالفصول النشطة
    echo "\n3. 🔗 ربط المهام بالفصول...\n";
    
    $classes = DB::table('classes')->where('status', 'active')->get();
    $tasks = DB::table('daily_task_definitions')->where('is_active', 1)->get();
    
    $createdAssignments = 0;
    foreach ($classes as $class) {
        echo "   ربط المهام بالفصل: {$class->name}\n";
        
        foreach ($tasks as $index => $task) {
            $exists = DB::table('class_task_assignments')
                ->where('class_id', $class->id)
                ->where('daily_task_definition_id', $task->id)
                ->exists();
                
            if (!$exists) {
                DB::table('class_task_assignments')->insert([
                    'class_id' => $class->id,
                    'daily_task_definition_id' => $task->id,
                    'is_active' => 1,
                    'order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $createdAssignments++;
                echo "     ✅ ربطت المهمة: {$task->name}\n";
            } else {
                echo "     - المهمة مربوطة: {$task->name}\n";
            }
        }
    }
    
    // 4. فحص النتائج النهائية
    echo "\n4. 📈 فحص النتائج النهائية...\n";
    
    $finalAssignmentsCount = DB::table('class_task_assignments')->where('is_active', 1)->count();
    echo "   - المهام المنشأة: {$createdTasks}\n";
    echo "   - المهام المربوطة: {$createdAssignments}\n";
    echo "   - إجمالي المهام الموكلة: {$finalAssignmentsCount}\n";
    
    // 5. اختبار شامل مع طالب
    echo "\n5. 🧪 اختبار شامل مع طالب...\n";
    
    $testStudent = DB::table('users')
        ->where('role', 'student')
        ->whereNotNull('class_id')
        ->first();
        
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
                echo "\n   ✅ نجح الإصلاح! المهام تظهر للطلاب.\n";
            } else {
                echo "\n   ❌ فشل الإصلاح! لا توجد مهام للطلاب.\n";
            }
        } else {
            echo "\n   ❌ الفصل غير موجود!\n";
        }
    } else {
        echo "\n   ❌ لا يوجد طلاب مرتبطين بفصول!\n";
    }
    
    // 6. إحصائيات نهائية
    echo "\n6. 📊 الإحصائيات النهائية:\n";
    
    $finalStats = [
        'الطلاب المرتبطين بفصول' => DB::table('users')->where('role', 'student')->whereNotNull('class_id')->count(),
        'الفصول النشطة' => DB::table('classes')->where('status', 'active')->count(),
        'تعريفات المهام النشطة' => DB::table('daily_task_definitions')->where('is_active', 1)->count(),
        'المهام الموكلة' => DB::table('class_task_assignments')->where('is_active', 1)->count(),
    ];
    
    foreach ($finalStats as $metric => $count) {
        echo "   - {$metric}: {$count}\n";
    }
    
    echo "\n==========================================\n";
    echo "🎉 تم الانتهاء من الإصلاح الشامل!\n";
    echo "الآن يجب أن تظهر المهام للطلاب في الويب والتطبيق.\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الإصلاح: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}












