<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Program;
use App\Models\User;
use App\Models\CompanionsPublication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompanionsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teacherSupport;
    private User $teacher;
    private User $student;
    private ClassModel $class;

    protected function setUp(): void
    {
        parent::setUp();
        
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

        // إنشاء المستخدمين
        $this->admin = User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'gender' => 'male',
            'status' => 'active',
        ]);

        $this->teacherSupport = User::create([
            'name' => 'مساعد المعلم',
            'email' => 'teacher_support@test.com',
            'password' => Hash::make('password'),
            'role' => 'teacher_support',
            'gender' => 'male',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        $this->teacher = User::create([
            'name' => 'المعلم',
            'email' => 'teacher@test.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'gender' => 'male',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        $this->student = User::create([
            'name' => 'الطالبة',
            'email' => 'student@test.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'gender' => 'female',
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);

        // إنشاء طالبات إضافية للاختبار
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "طالبة {$i}",
                'email' => "student{$i}@test.com",
                'password' => Hash::make('password'),
                'role' => 'student',
                'gender' => 'female',
                'class_id' => $this->class->id,
                'status' => 'active',
            ]);
        }
    }

    public function test_admin_can_generate_companions()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/classes/{$this->class->id}/companions/generate", [
                'target_date' => '2025-10-07',
                'grouping' => 'pairs',
                'algorithm' => 'random',
                'attendance_source' => 'all',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'publication_id',
                'pairings',
                'students_count',
                'groups_count',
            ]);

        $this->assertDatabaseHas('companions_publications', [
            'class_id' => $this->class->id,
            'target_date' => '2025-10-07 00:00:00',
            'grouping' => 'pairs',
            'algorithm' => 'random',
        ]);
    }

    public function test_teacher_support_can_generate_companions()
    {
        $response = $this->actingAs($this->teacherSupport, 'sanctum')
            ->postJson("/api/v1/classes/{$this->class->id}/companions/generate", [
                'target_date' => '2025-10-07',
                'grouping' => 'triplets',
                'algorithm' => 'manual',
                'attendance_source' => 'all',
                'locked_pairs' => [[1, 2], [3, 4, 5]],
            ]);

        $response->assertStatus(200);
    }

    public function test_teacher_cannot_generate_companions()
    {
        $response = $this->actingAs($this->teacher, 'sanctum')
            ->postJson("/api/v1/classes/{$this->class->id}/companions/generate", [
                'target_date' => '2025-10-07',
                'grouping' => 'pairs',
                'algorithm' => 'random',
                'attendance_source' => 'all',
            ]);

        $response->assertStatus(403);
    }

    public function test_student_cannot_generate_companions()
    {
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/classes/{$this->class->id}/companions/generate", [
                'target_date' => '2025-10-07',
                'grouping' => 'pairs',
                'algorithm' => 'random',
                'attendance_source' => 'all',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_publish_companions()
    {
        // إنشاء نشر أولاً
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => '2025-10-07',
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[1, 2], [3, 4], [5, 6]],
        ]);

        // Note: Route binding with dates in URL path requires URL encoding
        $targetDate = urlencode('2025-10-07');
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/classes/{$this->class->id}/companions/{$targetDate}/publish");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'publication_id',
                'room_assignments',
                'published_at',
            ]);

        $publication->refresh();
        $this->assertTrue($publication->isPublished());
        $this->assertNotNull($publication->room_assignments);
    }

    public function test_student_can_get_their_companions()
    {
        // إنشاء نشر منشور
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => '2025-10-07',
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[$this->student->id, 1], [2, 3], [4, 5]],
            'room_assignments' => [
                '1' => [$this->student->id, 1],
                '2' => [2, 3],
                '3' => [4, 5],
            ],
            'zoom_url_snapshot' => 'https://zoom.us/j/123456789',
            'zoom_password_snapshot' => 'password123',
            'published_at' => now(),
            'published_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/v1/me/companions?date=2025-10-07');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'date',
                'room_number',
                'zoom_url',
                'zoom_password',
                'companions',
            ]);

        $responseData = $response->json();
        $this->assertEquals('1', $responseData['room_number']);
        $this->assertEquals('https://zoom.us/j/123456789', $responseData['zoom_url']);
        $this->assertEquals('password123', $responseData['zoom_password']);
    }

    public function test_teacher_can_get_all_companions_for_class()
    {
        // إنشاء نشر منشور
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => '2025-10-07',
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[1, 2], [3, 4], [5, 6]],
            'room_assignments' => [
                '1' => [1, 2],
                '2' => [3, 4],
                '3' => [5, 6],
            ],
            'zoom_url_snapshot' => 'https://zoom.us/j/123456789',
            'zoom_password_snapshot' => 'password123',
            'published_at' => now(),
            'published_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->teacher, 'sanctum')
            ->getJson('/api/v1/me/companions?date=2025-10-07');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'date',
                'zoom_url',
                'zoom_password',
                'groups',
            ]);

        $responseData = $response->json();
        $this->assertCount(3, $responseData['groups']);
        $this->assertEquals('https://zoom.us/j/123456789', $responseData['zoom_url']);
    }

    public function test_lock_companions_before_publishing()
    {
        // إنشاء نشر أولاً
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => '2025-10-07',
            'grouping' => 'pairs',
            'algorithm' => 'manual',
            'attendance_source' => 'all',
            'pairings' => [[1, 2], [3, 4], [5, 6]],
        ]);

        $targetDate = urlencode('2025-10-07');
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/v1/classes/{$this->class->id}/companions/{$targetDate}/lock", [
                'locked_pairs' => [[1, 2], [3, 4]],
            ]);

    $response->assertStatus(200);

        $publication->refresh();
        $this->assertEquals([[1, 2], [3, 4]], $publication->locked_pairs);
    }

    public function test_cannot_publish_already_published_companions()
    {
        // إنشاء نشر منشور مسبقاً
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => '2025-10-07',
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[1, 2], [3, 4]],
            'published_at' => now(),
            'published_by' => $this->admin->id,
        ]);

        $targetDate = urlencode('2025-10-07');
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/classes/{$this->class->id}/companions/{$targetDate}/publish");

        $response->assertStatus(400)
            ->assertJson(['message' => 'تم نشر الرفيقات مسبقاً']);
    }

    public function test_validation_errors_for_invalid_data()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/classes/{$this->class->id}/companions/generate", [
                'target_date' => 'invalid-date',
                'grouping' => 'invalid-grouping',
                'algorithm' => 'invalid-algorithm',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_date', 'grouping', 'algorithm']);
    }
}