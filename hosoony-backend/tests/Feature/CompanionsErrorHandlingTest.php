<?php

namespace Tests\Feature;

use App\Domain\Companions\CompanionsBuilder;
use App\Models\ClassModel;
use App\Models\Program;
use App\Models\User;
use App\Models\CompanionsPublication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Carbon\Carbon;

class CompanionsErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    private Program $program;
    private ClassModel $class;
    private User $student1;
    private User $student2;
    private User $student3;
    private User $student4;
    private User $student5;
    private CompanionsBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->program = Program::create([
            'name' => 'برنامج تجريبي',
            'description' => 'وصف البرنامج',
            'status' => 'active',
        ]);

        $this->class = ClassModel::create([
            'name' => 'أ-1',
            'description' => 'الحلقة النسائية رقم 1',
            'program_id' => $this->program->id,
            'gender' => 'female',
            'max_students' => 20,
            'status' => 'active',
            'zoom_room_start' => 1,
        ]);

        // إنشاء طالبات
        $this->student1 = User::create([
            'name' => 'فاطمة',
            'email' => 'fatima@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'gender' => 'female',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        $this->student2 = User::create([
            'name' => 'عائشة',
            'email' => 'aisha@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'gender' => 'female',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        $this->student3 = User::create([
            'name' => 'خديجة',
            'email' => 'khadija@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'gender' => 'female',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        $this->student4 = User::create([
            'name' => 'مريم',
            'email' => 'maryam@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'gender' => 'female',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        $this->student5 = User::create([
            'name' => 'زينب',
            'email' => 'zainab@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'gender' => 'female',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        $this->builder = new CompanionsBuilder();
    }

    public function test_cannot_publish_twice_same_class_and_date()
    {
        $tomorrow = Carbon::tomorrow();
        
        // إنشاء نشر أول
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[$this->student1->id, $this->student2->id]],
            'room_assignments' => ['1' => [$this->student1->id, $this->student2->id]],
            'published_at' => now(),
        ]);

        // محاولة النشر مرة ثانية
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'triplets',
            'algorithm' => 'rotation',
            'attendance_source' => 'committed_only',
            'published_at' => now(),
        ]);
    }

    public function test_validate_locked_pairs_size()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('حجم المجموعة المثبتة يجب أن يكون 2');
        
        $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'manual',
            [[$this->student1->id, $this->student2->id, $this->student3->id]], // ثلاثية في وضع ثنائيات
            'all'
        );
    }

    public function test_validate_locked_pairs_student_exists()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('الطالبة رقم 999 غير موجودة في الفصل أو غير مؤهلة');
        
        $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'manual',
            [[$this->student1->id, 999]], // طالبة غير موجودة
            'all'
        );
    }

    public function test_validate_students_belong_to_same_class()
    {
        // إنشاء فصل آخر
        $otherClass = ClassModel::create([
            'name' => 'أ-2',
            'description' => 'الحلقة النسائية رقم 2',
            'program_id' => $this->program->id,
            'gender' => 'female',
            'max_students' => 20,
            'status' => 'active',
        ]);

        $otherStudent = User::create([
            'name' => 'طالبة أخرى',
            'email' => 'other@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'gender' => 'female',
            'class_id' => $otherClass->id,
            'status' => 'active',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('الطالبة رقم ' . $otherStudent->id . ' غير موجودة في الفصل أو غير مؤهلة');
        
        $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'manual',
            [[$this->student1->id, $otherStudent->id]], // طالبة من فصل آخر
            'all'
        );
    }

    public function test_validate_students_are_female()
    {
        // إنشاء طالب ذكر
        $maleStudent = User::create([
            'name' => 'طالب ذكر',
            'email' => 'male@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'gender' => 'male',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('الطالبة رقم ' . $maleStudent->id . ' ليست أنثى');
        
        $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'manual',
            [[$this->student1->id, $maleStudent->id]], // طالب ذكر
            'all'
        );
    }

    public function test_fail_safe_for_odd_number_of_students_in_pairs()
    {
        // حذف طالبة واحدة ليبقى 4 طالبات (عدد فردي للثنائيات)
        $this->student5->delete();
        
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random',
            null,
            'all'
        );

        // يجب أن تكون هناك مجموعتان (ثنائية وثلاثية أو ثنائيتان)
        $this->assertCount(2, $result['pairings']);
        
        // التحقق من أن جميع الطالبات الأربع موجودة
        $allStudentIds = [];
        foreach ($result['pairings'] as $pair) {
            foreach ($pair as $studentId) {
                $allStudentIds[] = $studentId;
            }
        }
        
        $expectedStudentIds = [$this->student1->id, $this->student2->id, $this->student3->id, $this->student4->id];
        foreach ($expectedStudentIds as $expectedId) {
            $this->assertContains($expectedId, $allStudentIds, "الطالبة رقم {$expectedId} غير موجودة في الرفيقات");
        }
    }

    public function test_locked_pairs_are_preserved_in_rotation()
    {
        $lockedPairs = [[$this->student1->id, $this->student2->id]];
        
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'rotation',
            $lockedPairs,
            'all'
        );

        // التحقق من أن الثنائية المثبتة موجودة
        $this->assertContains($lockedPairs[0], $result['pairings']);
    }

    public function test_locked_pairs_are_preserved_in_random()
    {
        $lockedPairs = [[$this->student1->id, $this->student2->id]];
        
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random',
            $lockedPairs,
            'all'
        );

        // التحقق من أن الثنائية المثبتة موجودة
        $this->assertContains($lockedPairs[0], $result['pairings']);
    }

    public function test_no_duplicate_students_in_pairings()
    {
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random',
            null,
            'all'
        );

        $allStudentIds = [];
        foreach ($result['pairings'] as $pair) {
            foreach ($pair as $studentId) {
                $this->assertNotContains($studentId, $allStudentIds, "الطالبة رقم {$studentId} مكررة");
                $allStudentIds[] = $studentId;
            }
        }
    }

    public function test_all_students_are_included_in_pairings()
    {
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random',
            null,
            'all'
        );

        $allStudentIds = [];
        foreach ($result['pairings'] as $pair) {
            foreach ($pair as $studentId) {
                $allStudentIds[] = $studentId;
            }
        }

        $expectedStudentIds = [$this->student1->id, $this->student2->id, $this->student3->id, $this->student4->id, $this->student5->id];
        
        foreach ($expectedStudentIds as $expectedId) {
            $this->assertContains($expectedId, $allStudentIds, "الطالبة رقم {$expectedId} غير موجودة في الرفيقات");
        }
    }

    public function test_api_returns_409_for_duplicate_publication()
    {
        $admin = User::create([
            'name' => 'مدير',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $tomorrow = Carbon::tomorrow();
        
        // إنشاء نشر منشور
        CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[$this->student1->id, $this->student2->id]],
            'room_assignments' => ['1' => [$this->student1->id, $this->student2->id]],
            'published_at' => now(),
        ]);

        $targetDate = urlencode($tomorrow->toDateString());
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/classes/{$this->class->id}/companions/{$targetDate}/publish");

        $response->assertStatus(409)
            ->assertJson(['message' => 'تم نشر الرفيقات مسبقاً']);
    }
}