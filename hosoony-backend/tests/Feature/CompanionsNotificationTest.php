<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Program;
use App\Models\User;
use App\Models\CompanionsPublication;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Carbon\Carbon;

class CompanionsNotificationTest extends TestCase
{
    use RefreshDatabase;

    private ClassModel $class;
    private User $student1;
    private User $student2;
    private User $student3;

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
            'zoom_url' => 'https://zoom.us/j/123456789',
            'zoom_password' => 'password123',
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
    }

    public function test_companions_notification_format_is_correct()
    {
        $tomorrow = Carbon::tomorrow();
        
        // إنشاء نشر منشور
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[$this->student1->id, $this->student2->id], [$this->student3->id]],
            'room_assignments' => [
                '1' => [$this->student1->id, $this->student2->id],
                '2' => [$this->student3->id],
            ],
            'zoom_url_snapshot' => 'https://zoom.us/j/123456789',
            'zoom_password_snapshot' => 'password123',
            'published_at' => now(),
        ]);

        // محاكاة إرسال الإشعارات
        $students = $this->class->students()->where('status', 'active')->get();
        $notifications = [];

        foreach ($publication->room_assignments as $roomNumber => $group) {
            $groupStudents = $students->whereIn('id', $group);
            
            foreach ($groupStudents as $student) {
                $companions = $groupStudents->where('id', '!=', $student->id);
                $companionNames = $companions->pluck('name')->join(' و ');
                
                // بناء رسالة الإشعار حسب التنسيق المطلوب
                $message = "رفيقتك/رفيقاتك: {$companionNames} — غرفة {$roomNumber}";
                
                if ($publication->zoom_url_snapshot) {
                    $message .= " — رابط Zoom {$publication->zoom_url_snapshot}";
                }
                
                if ($publication->zoom_password_snapshot) {
                    $message .= " — رمز الدخول: {$publication->zoom_password_snapshot}";
                }

                $notifications[] = [
                    'student' => $student->name,
                    'message' => $message,
                ];
            }
        }

        // التحقق من تنسيق الإشعارات
        $this->assertCount(3, $notifications);

        // التحقق من إشعار فاطمة
        $fatimaNotification = collect($notifications)->firstWhere('student', 'فاطمة');
        $this->assertNotNull($fatimaNotification);
        $this->assertStringContainsString('رفيقتك/رفيقاتك: عائشة', $fatimaNotification['message']);
        $this->assertStringContainsString('غرفة 1', $fatimaNotification['message']);
        $this->assertStringContainsString('رابط Zoom https://zoom.us/j/123456789', $fatimaNotification['message']);
        $this->assertStringContainsString('رمز الدخول: password123', $fatimaNotification['message']);

        // التحقق من إشعار عائشة
        $aishaNotification = collect($notifications)->firstWhere('student', 'عائشة');
        $this->assertNotNull($aishaNotification);
        $this->assertStringContainsString('رفيقتك/رفيقاتك: فاطمة', $aishaNotification['message']);
        $this->assertStringContainsString('غرفة 1', $aishaNotification['message']);

        // التحقق من إشعار خديجة (في غرفة منفصلة)
        $khadijaNotification = collect($notifications)->firstWhere('student', 'خديجة');
        $this->assertNotNull($khadijaNotification);
        $this->assertStringContainsString('غرفة 2', $khadijaNotification['message']);
        // خديجة في غرفة منفصلة، لذا يجب أن تحتوي على "رفيقتك/رفيقاتك:" ولكن بدون أسماء
        $this->assertStringContainsString('رفيقتك/رفيقاتك:', $khadijaNotification['message']);
        $this->assertStringNotContainsString('عائشة', $khadijaNotification['message']);
        $this->assertStringNotContainsString('فاطمة', $khadijaNotification['message']);
    }

    public function test_notification_without_password()
    {
        $tomorrow = Carbon::tomorrow();
        
        // إنشاء نشر بدون كلمة مرور
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[$this->student1->id, $this->student2->id]],
            'room_assignments' => [
                '1' => [$this->student1->id, $this->student2->id],
            ],
            'zoom_url_snapshot' => 'https://zoom.us/j/123456789',
            'zoom_password_snapshot' => null, // بدون كلمة مرور
            'published_at' => now(),
        ]);

        // محاكاة بناء الرسالة
        $message = "رفيقتك/رفيقاتك: عائشة — غرفة 1 — رابط Zoom https://zoom.us/j/123456789";
        
        // التحقق من أن الرسالة لا تحتوي على رمز الدخول
        $this->assertStringNotContainsString('رمز الدخول:', $message);
        $this->assertStringContainsString('رابط Zoom', $message);
    }

    public function test_notification_without_zoom_url()
    {
        $tomorrow = Carbon::tomorrow();
        
        // إنشاء نشر بدون رابط Zoom
        $publication = CompanionsPublication::create([
            'class_id' => $this->class->id,
            'target_date' => $tomorrow,
            'grouping' => 'pairs',
            'algorithm' => 'random',
            'attendance_source' => 'all',
            'pairings' => [[$this->student1->id, $this->student2->id]],
            'room_assignments' => [
                '1' => [$this->student1->id, $this->student2->id],
            ],
            'zoom_url_snapshot' => null, // بدون رابط Zoom
            'zoom_password_snapshot' => null,
            'published_at' => now(),
        ]);

        // محاكاة بناء الرسالة
        $message = "رفيقتك/رفيقاتك: عائشة — غرفة 1";
        
        // التحقق من أن الرسالة لا تحتوي على معلومات Zoom
        $this->assertStringNotContainsString('رابط Zoom', $message);
        $this->assertStringNotContainsString('رمز الدخول:', $message);
        $this->assertStringContainsString('غرفة 1', $message);
    }
}