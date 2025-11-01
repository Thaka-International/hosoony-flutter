<?php

namespace Tests\Feature;

use App\Domain\Companions\CompanionsBuilder;
use App\Models\ClassModel;
use App\Models\Program;
use App\Models\User;
use App\Models\CompanionsPublication;
use App\Models\DailyLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Carbon\Carbon;

class CompanionsBuilderTest extends TestCase
{
    use RefreshDatabase;

    private CompanionsBuilder $builder;
    private ClassModel $class;
    private Collection $students;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->builder = new CompanionsBuilder();
        
        // إنشاء برنامج وفصل
        $program = Program::create([
            'name' => 'برنامج تجريبي',
            'description' => 'وصف البرنامج',
            'status' => 'active',
        ]);

        $this->class = ClassModel::create([
            'name' => 'أ-1',
            'description' => 'الحلقة النسائية رقم 1',
            'program_id' => $program->id,
            'gender' => 'female',
            'max_students' => 20,
            'status' => 'active',
            'zoom_room_start' => 1,
        ]);

        // إنشاء طالبات
        $this->students = collect();
        for ($i = 1; $i <= 6; $i++) {
            $student = User::create([
                'name' => "طالبة {$i}",
                'email' => "student{$i}@test.com",
                'password' => bcrypt('password'),
                'role' => 'student',
                'gender' => 'female',
                'class_id' => $this->class->id,
                'status' => 'active',
            ]);
            $this->students->push($student);
        }
    }

    public function test_random_algorithm_with_pairs()
    {
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random'
        );

        $this->assertArrayHasKey('pairings', $result);
        $this->assertArrayHasKey('room_assignments', $result);
        $this->assertEquals(3, $result['groups_count']); // 6 طالبات = 3 ثنائيات
        
        // التحقق من أن كل ثنائية تحتوي على طالبتين
        foreach ($result['pairings'] as $pair) {
            $this->assertCount(2, $pair);
        }
    }

    public function test_random_algorithm_with_triplets()
    {
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'triplets',
            'random'
        );

        $this->assertArrayHasKey('pairings', $result);
        $this->assertArrayHasKey('room_assignments', $result);
        $this->assertEquals(2, $result['groups_count']); // 6 طالبات = 2 ثلاثيات
        
        // التحقق من أن كل ثلاثية تحتوي على 3 طالبات
        foreach ($result['pairings'] as $triplet) {
            $this->assertCount(3, $triplet);
        }
    }

    public function test_manual_algorithm_with_locked_pairs()
    {
        $lockedPairs = [
            [$this->students[0]->id, $this->students[1]->id],
            [$this->students[2]->id, $this->students[3]->id]
        ];

        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'manual',
            $lockedPairs
        );

        $this->assertArrayHasKey('pairings', $result);
        
        // التحقق من وجود الثنائيات المثبتة
        $foundLockedPairs = 0;
        foreach ($result['pairings'] as $pair) {
            if (in_array($this->students[0]->id, $pair) && in_array($this->students[1]->id, $pair)) {
                $foundLockedPairs++;
            }
            if (in_array($this->students[2]->id, $pair) && in_array($this->students[3]->id, $pair)) {
                $foundLockedPairs++;
            }
        }
        
        $this->assertEquals(2, $foundLockedPairs);
    }

    public function test_rotation_algorithm()
    {
        // إنشاء نشر سابق للتدوير
        CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => '2025-10-06',
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [
                [$this->students[0]->id, $this->students[1]->id],
                [$this->students[2]->id, $this->students[3]->id],
                [$this->students[4]->id, $this->students[5]->id]
            ],
            'published_at' => now(),
        ]);

        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'rotation'
        );

        $this->assertArrayHasKey('pairings', $result);
        $this->assertEquals(3, $result['groups_count']);
    }

    public function test_committed_only_filters_low_attendance_students()
    {
        // إنشاء سجلات حضور للطالبات
        $cutoffDate = Carbon::now()->subDays(10);
        
        // طالبة ملتزمة (حضور 80%)
        for ($i = 0; $i < 8; $i++) {
            DailyLog::create([
                'student_id' => $this->students[0]->id,
                'log_date' => $cutoffDate->copy()->addDays($i),
                'verified_at' => now(),
            ]);
        }
        
        // طالبة غير ملتزمة (حضور 30%)
        for ($i = 0; $i < 3; $i++) {
            DailyLog::create([
                'student_id' => $this->students[1]->id,
                'log_date' => $cutoffDate->copy()->addDays($i),
                'verified_at' => now(),
            ]);
        }

        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random',
            null,
            'committed_only'
        );

        // يجب أن تكون النتيجة أقل من العدد الأصلي بسبب التصفية
        $this->assertLessThan($this->students->count(), $result['students_count']);
    }

    public function test_room_assignments_start_from_zoom_room_start()
    {
        $this->class->update(['zoom_room_start' => 5]);
        
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random'
        );

        $this->assertArrayHasKey('room_assignments', $result);
        
        // التحقق من أن أرقام الغرف تبدأ من zoom_room_start
        $roomNumbers = array_keys($result['room_assignments']);
        $this->assertContains(5, $roomNumbers);
        $this->assertContains(6, $roomNumbers);
        $this->assertContains(7, $roomNumbers);
    }

    public function test_insufficient_students_returns_empty_result()
    {
        // حذف معظم الطالبات ليبقى طالبة واحدة فقط
        $this->students->skip(1)->each(function ($student) {
            $student->delete();
        });

        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random'
        );

        $this->assertEmpty($result['pairings']);
        $this->assertEmpty($result['room_assignments']);
        $this->assertStringContainsString('لا يوجد عدد كافي', $result['message']);
    }
}