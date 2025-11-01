<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\Session;
use App\Models\DailyTaskDefinition;
use App\Models\User;
use Carbon\Carbon;

class FemaleCirclesAndTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء الحلقات النسائية من أ-1 إلى أ-15
        $this->createFemaleCircles();
        
        // إنشاء المهام اليومية المتكررة
        $this->createRecurringDailyTasks();
        
        // إنشاء الجلسات الأسبوعية
        $this->createWeeklySessions();
    }
    
    private function createFemaleCircles(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            ClassModel::create([
                'name' => "أ-{$i}",
                'description' => "الحلقة النسائية رقم {$i}",
                'program_id' => 1, // افتراض أن هناك برنامج رقم 1
                'gender' => 'female',
                'max_students' => 20,
                'status' => 'active',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->addMonths(6),
            ]);
        }
    }
    
    private function createRecurringDailyTasks(): void
    {
        $weekdays = [
            'sunday' => 'الأحد',
            'monday' => 'الاثنين', 
            'tuesday' => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس'
        ];
        
        $taskTypes = [
            'hifz' => 'حفظ',
            'murajaah' => 'مراجعة',
            'tilawah' => 'تلاوة',
            'tajweed' => 'تجويد',
            'tafseer' => 'تفسير'
        ];
        
        foreach ($weekdays as $day => $dayName) {
            foreach ($taskTypes as $type => $typeName) {
                DailyTaskDefinition::create([
                    'name' => "{$typeName} - {$dayName}",
                    'description' => "مهمة {$typeName} ليوم {$dayName} للحلقات النسائية",
                    'type' => $type,
                    'points_weight' => rand(5, 15),
                    'duration_minutes' => rand(30, 90),
                    'is_active' => true,
                ]);
            }
        }
    }
    
    private function createWeeklySessions(): void
    {
        $circles = ClassModel::where('name', 'like', 'أ-%')->get();
        $weekdays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
        
        // إنشاء جلسات للأسبوع القادم
        $nextWeek = now()->addWeek();
        
        foreach ($circles as $circle) {
            foreach ($weekdays as $dayIndex => $day) {
                $startTime = $nextWeek->copy()->startOfWeek()->addDays($dayIndex)->setTime(7 + $dayIndex, 0, 0);
                
                Session::create([
                    'title' => "جلسة {$circle->name} - " . $this->getDayName($day),
                    'description' => "جلسة تعليمية للحلقة النسائية {$circle->name}",
                    'class_id' => $circle->id,
                    'teacher_id' => User::where('role', 'teacher')->first()->id ?? 1,
                    'starts_at' => $startTime,
                    'ends_at' => $startTime->copy()->addHours(1),
                    'status' => 'scheduled',
                    'notes' => "جلسة أسبوعية للحلقة النسائية {$circle->name}",
                ]);
            }
        }
    }
    
    private function getDayName(string $day): string
    {
        return match($day) {
            'sunday' => 'الأحد',
            'monday' => 'الاثنين',
            'tuesday' => 'الثلاثاء', 
            'wednesday' => 'الأربعاء',
            'thursday' => 'الخميس',
            default => $day
        };
    }
}
