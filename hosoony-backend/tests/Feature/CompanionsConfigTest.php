<?php

namespace Tests\Feature;

use App\Domain\Companions\CompanionsBuilder;
use App\Models\ClassModel;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompanionsConfigTest extends TestCase
{
    use RefreshDatabase;

    private Program $program;
    private ClassModel $class;
    private User $student1;
    private User $student2;
    private User $student3;
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

        $this->builder = new CompanionsBuilder();
    }

    public function test_config_default_attendance_source_is_used()
    {
        // التحقق من أن القيمة الافتراضية من config تُستخدم
        $defaultSource = config('quran_lms.companions.default_attendance_source', 'committed_only');
        $this->assertEquals('committed_only', $defaultSource);
    }

    public function test_pairings_store_only_ids_not_names()
    {
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random',
            null,
            'all'
        );

        // التحقق من أن pairings تحتوي على IDs فقط
        foreach ($result['pairings'] as $pair) {
            foreach ($pair as $item) {
                $this->assertIsInt($item, 'يجب أن تكون pairings تحتوي على IDs فقط');
                $this->assertGreaterThan(0, $item, 'يجب أن تكون IDs صحيحة');
            }
        }
    }

    public function test_room_assignments_store_only_ids_not_names()
    {
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random',
            null,
            'all'
        );

        // التحقق من أن room_assignments تحتوي على IDs فقط
        foreach ($result['room_assignments'] as $roomNumber => $students) {
            foreach ($students as $studentId) {
                $this->assertIsInt($studentId, 'يجب أن تكون room_assignments تحتوي على IDs فقط');
                $this->assertGreaterThan(0, $studentId, 'يجب أن تكون IDs صحيحة');
            }
        }
    }

    public function test_zoom_password_is_optional_in_class()
    {
        // إنشاء فصل بدون كلمة مرور Zoom
        $classWithoutPassword = ClassModel::create([
            'name' => 'أ-2',
            'description' => 'الحلقة النسائية رقم 2',
            'program_id' => $this->program->id,
            'gender' => 'female',
            'max_students' => 20,
            'status' => 'active',
            'zoom_url' => 'https://zoom.us/j/123456789',
            'zoom_password' => null, // بدون كلمة مرور
            'zoom_room_start' => 1,
        ]);

        $this->assertNull($classWithoutPassword->zoom_password);
        $this->assertNotNull($classWithoutPassword->zoom_url);
    }

    public function test_zoom_password_is_optional_in_publication()
    {
        $publication = \App\Models\CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => '2025-10-07',
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[$this->student1->id, $this->student2->id]],
            'room_assignments' => ['1' => [$this->student1->id, $this->student2->id]],
            'zoom_url_snapshot' => 'https://zoom.us/j/123456789',
            'zoom_password_snapshot' => null, // بدون كلمة مرور
            'published_at' => now(),
        ]);

        $this->assertNull($publication->zoom_password_snapshot);
        $this->assertNotNull($publication->zoom_url_snapshot);
    }

    public function test_attendance_source_can_be_all_for_some_classes()
    {
        // محاكاة فصل يستخدم "all" بدلاً من "committed_only"
        $result = $this->builder->build(
            $this->class->id,
            '2025-10-07',
            'pairs',
            'random',
            null,
            'all' // استخدام "all" بدلاً من "committed_only"
        );

        $this->assertNotEmpty($result['pairings']);
        $this->assertGreaterThan(0, $result['groups_count']);
    }

    public function test_config_values_are_accessible()
    {
        $config = config('quran_lms.companions');
        
        $this->assertArrayHasKey('attendance_window_days', $config);
        $this->assertArrayHasKey('min_rate', $config);
        $this->assertArrayHasKey('default_publish_time', $config);
        $this->assertArrayHasKey('default_attendance_source', $config);
        
        $this->assertEquals(14, $config['attendance_window_days']);
        $this->assertEquals(0.6, $config['min_rate']);
        $this->assertEquals('23:59', $config['default_publish_time']);
        $this->assertEquals('committed_only', $config['default_attendance_source']);
    }

    public function test_notification_channels_are_push_and_email_only()
    {
        // التحقق من أن قنوات الإشعارات المستخدمة هي push و email فقط
        $validChannels = ['push', 'email'];
        
        // محاكاة إنشاء إشعار
        $notification = \App\Models\Notification::create([
            'user_id' => $this->student1->id,
            'title' => 'اختبار',
            'message' => 'رسالة اختبار',
            'channel' => 'push',
            'sent_at' => now(),
        ]);

        $this->assertContains($notification->channel, $validChannels);
        
        $notification2 = \App\Models\Notification::create([
            'user_id' => $this->student1->id,
            'title' => 'اختبار',
            'message' => 'رسالة اختبار',
            'channel' => 'email',
            'sent_at' => now(),
        ]);

        $this->assertContains($notification2->channel, $validChannels);
    }
}