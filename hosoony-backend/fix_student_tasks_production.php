<?php
// سكريبت إصلاح المهام اليومية للإنتاج
// يتم تشغيله تلقائياً عند النشر عبر .cpanel.yml

echo "🔧 إصلاح المهام اليومية على الإنتاج...\n";

try {
    // 1. فحص البيانات الموجودة
    $classesCount = DB::table('classes')->where('status', 'active')->count();
    $tasksCount = DB::table('daily_task_definitions')->where('is_active', 1)->count();
    $assignmentsCount = DB::table('class_task_assignments')->count();
    
    echo "📊 الفصول النشطة: {$classesCount}\n";
    echo "📊 تعريفات المهام النشطة: {$tasksCount}\n";
    echo "📊 المهام الموكلة: {$assignmentsCount}\n";
    
    // 2. إنشاء تعريفات مهام إذا لم تكن موجودة
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
            echo "✅ تم إنشاء المهمة: {$taskData['name']}\n";
        }
    }
    
    // 3. ربط المهام بالفصول النشطة
    $classes = DB::table('classes')->where('status', 'active')->get();
    $tasks = DB::table('daily_task_definitions')->where('is_active', 1)->get();
    
    $createdAssignments = 0;
    foreach ($classes as $class) {
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
            }
        }
    }
    
    // 4. فحص النتائج النهائية
    $finalAssignmentsCount = DB::table('class_task_assignments')->count();
    
    echo "📈 النتائج النهائية:\n";
    echo "   - المهام المنشأة: {$createdTasks}\n";
    echo "   - المهام المربوطة: {$createdAssignments}\n";
    echo "   - إجمالي المهام الموكلة: {$finalAssignmentsCount}\n";
    
    // 5. اختبار مع طالب
    $student = DB::table('users')
        ->where('role', 'student')
        ->whereNotNull('class_id')
        ->first();
        
    if ($student) {
        $studentTasks = DB::table('class_task_assignments')
            ->where('class_id', $student->class_id)
            ->where('is_active', 1)
            ->count();
            
        echo "🧪 اختبار الطالب: {$student->name}\n";
        echo "   - المهام المتاحة: {$studentTasks}\n";
        
        if ($studentTasks > 0) {
            echo "✅ نجح الإصلاح! المهام تظهر للطلاب.\n";
        } else {
            echo "❌ فشل الإصلاح! لا توجد مهام للطلاب.\n";
        }
    }
    
    echo "🎉 تم الانتهاء من إصلاح المهام اليومية!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}












