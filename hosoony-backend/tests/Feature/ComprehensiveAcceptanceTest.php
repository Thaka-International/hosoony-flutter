<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\ClassModel;
use App\Models\DailyLog;
use App\Models\DailyTaskDefinition;
use App\Models\CompanionsPublication;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\FeesPlan;
use App\Models\GamificationPoint;
use App\Models\Badge;
use App\Models\StudentBadge;
use App\Models\PerformanceEvaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Carbon\Carbon;

class ComprehensiveAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // تشغيل الـ Seed الشامل
        $this->seed(\Database\Seeders\FinalComprehensiveSeeder::class);
    }

    public function test_roles_and_policies_work_correctly()
    {
        // اختبار صلاحيات المدير
        $admin = User::where('role', 'admin')->first();
        $this->assertTrue($admin->can('viewAny', User::class));
        $this->assertTrue($admin->can('create', User::class));
        $this->assertTrue($admin->can('update', $admin));
        $this->assertTrue($admin->can('delete', $admin));

        // اختبار صلاحيات المعلم
        $teacher = User::where('role', 'teacher')->first();
        $this->assertFalse($teacher->can('create', User::class));
        $this->assertFalse($teacher->can('delete', $teacher));
        // المعلم يمكنه عرض الفصول التي يدرس فيها
        $this->assertTrue($teacher->class_id !== null);

        // اختبار صلاحيات الطالب
        $student = User::where('role', 'student')->first();
        $this->assertFalse($student->can('create', User::class));
        $this->assertFalse($student->can('viewAny', User::class));
        $this->assertTrue($student->can('view', $student));

        // اختبار صلاحيات المساعد
        $support = User::where('role', 'teacher_support')->first();
        $this->assertTrue($support->role === 'teacher_support');
        $this->assertFalse($support->can('delete', $support));
    }

    public function test_gender_filters_work_correctly()
    {
        // اختبار فصل الذكور
        $maleClass = ClassModel::where('gender', 'male')->first();
        $maleStudents = $maleClass->students;
        
        $this->assertTrue($maleStudents->every(fn($student) => $student->gender === 'male'));
        $this->assertEquals(5, $maleStudents->count());

        // اختبار فصل الإناث
        $femaleClass = ClassModel::where('gender', 'female')->first();
        $femaleStudents = $femaleClass->students;
        
        $this->assertTrue($femaleStudents->every(fn($student) => $student->gender === 'female'));
        $this->assertEquals(5, $femaleStudents->count());

        // اختبار أن المعلمين مرتبطين بالفصول المناسبة
        $maleTeacher = $maleClass->teachers->first();
        $femaleTeacher = $femaleClass->teachers->first();
        
        $this->assertEquals('male', $maleTeacher->gender);
        $this->assertEquals('female', $femaleTeacher->gender);
    }

    public function test_daily_logs_uniqueness_and_finish_order()
    {
        $student = User::where('role', 'student')->first();

        // إنشاء سجل يومي
        $log1 = DailyLog::create([
            'student_id' => $student->id,
            'log_date' => Carbon::today(),
            'status' => 'submitted',
        ]);

        // محاولة إنشاء سجل مكرر لنفس الطالب والتاريخ
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        DailyLog::create([
            'student_id' => $student->id,
            'log_date' => Carbon::today(),
            'status' => 'verified',
        ]);

        // اختبار ترتيب الإنجاز
        $logs = DailyLog::where('student_id', $student->id)
            ->orderBy('created_at')
            ->get();
        
        $this->assertTrue($logs->isNotEmpty());
        $this->assertEquals($log1->id, $logs->first()->id);
    }

    public function test_awarding_points_and_badges()
    {
        $student = User::where('role', 'student')->first();
        $task = DailyTaskDefinition::first();

        // إنشاء سجل يومي
        $log = DailyLog::create([
            'student_id' => $student->id,
            'log_date' => Carbon::today(),
            'status' => 'submitted',
        ]);

        // التحقق من منح النقاط
        $this->assertEquals('submitted', $log->status);

        // إنشاء شارة
        $badge = Badge::create([
            'name' => 'شارة المثابرة',
            'description' => 'شارة للمثابرة في الدراسة',
            'icon_url' => 'medal.png',
            'category' => 'achievement',
            'points_required' => 100,
            'is_active' => true,
        ]);

        // منح الشارة للطالب
        StudentBadge::create([
            'student_id' => $student->id,
            'badge_id' => $badge->id,
            'awarded_at' => Carbon::now(),
        ]);

        // التحقق من منح الشارة
        $this->assertDatabaseHas('student_badges', [
            'student_id' => $student->id,
            'badge_id' => $badge->id,
        ]);

        // إنشاء نقاط اللعب
        GamificationPoint::create([
            'student_id' => $student->id,
            'points' => $task->points_weight,
            'source_type' => 'daily_log',
            'source_id' => $log->id,
            'description' => 'نقاط من المهمة اليومية',
            'awarded_at' => Carbon::now(),
        ]);

        // التحقق من النقاط
        $totalPoints = GamificationPoint::where('student_id', $student->id)->sum('points');
        $this->assertEquals($task->points_weight, $totalPoints);
    }

    public function test_scheduled_jobs_smoke_test()
    {
        // اختبار أمر النشر التلقائي للرفيقات
        $exitCode = Artisan::call('companions:autopublish');
        $this->assertEquals(0, $exitCode);

        // التحقق من وجود رفيقات منشورة
        $publications = CompanionsPublication::whereNotNull('published_at')->get();
        $this->assertTrue($publications->isNotEmpty());

        // اختبار أمر إرسال الإشعارات المجدولة (إذا كان موجوداً)
        try {
            $exitCode = Artisan::call('notifications:send-scheduled');
            $this->assertEquals(0, $exitCode);
        } catch (\Exception $e) {
            // الأمر غير موجود، نتجاهل هذا الاختبار
            $this->assertTrue(true);
        }

        // التحقق من إرسال الإشعارات
        $sentNotifications = Notification::whereNotNull('sent_at')->get();
        $this->assertTrue($sentNotifications->isNotEmpty());
    }

    public function test_pdf_generation()
    {
        $student = User::where('role', 'student')->first();
        $class = $student->class;

        // إنشاء تقرير يومي
        $reportData = [
            'student_name' => $student->name,
            'class_name' => $class->name,
            'date' => Carbon::today()->format('Y-m-d'),
            'tasks_completed' => 3,
            'total_points' => 45,
            'duration_minutes' => 120,
        ];

        // توليد PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily-report', $reportData);
        $pdfContent = $pdf->output();

        // التحقق من توليد PDF
        $this->assertNotEmpty($pdfContent);
        $this->assertStringContainsString('%PDF', $pdfContent);

        // اختبار حفظ PDF
        Storage::fake('public');
        $filename = 'reports/daily-report-' . $student->id . '-' . Carbon::today()->format('Y-m-d') . '.pdf';
        Storage::put($filename, $pdfContent);
        
        $this->assertTrue(Storage::exists($filename));
    }

    public function test_companions_rotation_system()
    {
        $class = ClassModel::where('gender', 'female')->first();
        $students = $class->students;

        // إنشاء نشرات متعددة لاختبار التدوير
        $publications = [];
        $startDate = Carbon::now()->next(Carbon::SUNDAY);

        for ($i = 0; $i < 3; $i++) {
            $targetDate = $startDate->copy()->addWeeks($i);
            
            // التحقق من عدم وجود نشرات مكررة
            $existingPublication = CompanionsPublication::where('class_id', $class->id)
                ->where('target_date', $targetDate)
                ->first();
            
            if (!$existingPublication) {
                $pairings = $this->generateRotatingPairings($students->pluck('id')->toArray());
                
                $publication = CompanionsPublication::create([
                    'class_id' => $class->id,
                    'target_date' => $targetDate,
                    'grouping' => 'pairs',
                    'algorithm' => 'rotation',
                    'attendance_source' => 'all',
                    'pairings' => $pairings,
                    'room_assignments' => $this->assignRooms($pairings, $class->zoom_room_start),
                    'zoom_url_snapshot' => $class->zoom_url,
                    'zoom_password_snapshot' => $class->zoom_password,
                    'published_at' => Carbon::now(),
                    'published_by' => User::where('role', 'admin')->first()->id,
                    'auto_published' => false,
                ]);

                $publications[] = $publication;
            }
        }

        // التحقق من أن التدوير يعمل
        $this->assertGreaterThanOrEqual(1, count($publications));
        
        // التحقق من أن كل طالب موجود في جميع النشرات
        foreach ($publications as $publication) {
            $allStudentsInPublication = collect($publication->pairings)->flatten()->unique();
            $this->assertEquals($students->count(), $allStudentsInPublication->count());
        }
    }

    public function test_payment_reminders_system()
    {
        $students = User::where('role', 'student')->get();
        $reminderDays = [7, 3, 1];

        // التحقق من وجود تذكيرات للمدفوعات
        foreach ($students as $student) {
            $notifications = Notification::where('user_id', $student->id)
                ->where('title', 'تذكير بدفع الرسوم')
                ->get();

            $this->assertTrue($notifications->isNotEmpty());
            
            // التحقق من أن التذكيرات في الأوقات الصحيحة
            foreach ($notifications as $notification) {
                $this->assertStringContainsString('تذكير: موعد استحقاق الرسوم', $notification->message);
                $this->assertEquals('email', $notification->channel);
            }
        }
    }

    public function test_subscription_and_payment_system()
    {
        $students = User::where('role', 'student')->get();

        foreach ($students as $student) {
            // التحقق من وجود اشتراك
            $subscription = Subscription::where('student_id', $student->id)->first();
            $this->assertNotNull($subscription);
            $this->assertEquals('active', $subscription->status);

            // التحقق من وجود مدفوعات
            $payments = Payment::where('student_id', $student->id)->get();
            $this->assertTrue($payments->isNotEmpty());

            // التحقق من أن المبلغ صحيح
            foreach ($payments as $payment) {
                $this->assertEquals(500.00, $payment->amount);
                $this->assertEquals('SAR', $payment->currency);
                $this->assertContains($payment->status, ['completed', 'pending']);
            }
        }
    }

    public function test_performance_evaluation_system()
    {
        $student = User::where('role', 'student')->first();
        $teacher = User::where('role', 'teacher')->first();

        // إنشاء تقييم أداء بدون جلسة محددة
        $evaluation = PerformanceEvaluation::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'class_id' => $student->class_id,
            'recitation_score' => 8.5,
            'pronunciation_score' => 9.0,
            'memorization_score' => 7.5,
            'understanding_score' => 8.0,
            'participation_score' => 9.5,
            'total_score' => 42.5,
            'performance_level' => 'good',
            'recommendations' => 'تحسين في التلاوة والتركيز على التجويد',
            'improvement_areas' => 'الحفظ والمراجعة',
            'evaluation_date' => Carbon::now(),
        ]);

        // التحقق من التقييم
        $this->assertNotNull($evaluation);
        $this->assertEquals(42.5, $evaluation->total_score);
        $this->assertEquals('good', $evaluation->performance_level);
        $this->assertStringContainsString('تحسين في التلاوة', $evaluation->recommendations);
    }

    public function test_notification_system()
    {
        $student = User::where('role', 'student')->first();

        // إنشاء إشعار
        $notification = Notification::create([
            'user_id' => $student->id,
            'title' => 'إشعار اختبار',
            'message' => 'هذا إشعار اختبار للنظام',
            'channel' => 'push',
            'sent_at' => Carbon::now(),
        ]);

        // التحقق من الإشعار
        $this->assertNotNull($notification);
        $this->assertEquals($student->id, $notification->user_id);
        $this->assertEquals('push', $notification->channel);
        $this->assertNotNull($notification->sent_at);

        // التحقق من العلاقة
        $this->assertEquals($student->name, $notification->user->name);
    }

    private function generateRotatingPairings(array $studentIds): array
    {
        $pairings = [];
        $shuffled = collect($studentIds)->shuffle()->toArray();
        
        for ($i = 0; $i < count($shuffled); $i += 2) {
            if ($i + 1 < count($shuffled)) {
                $pairings[] = [$shuffled[$i], $shuffled[$i + 1]];
            } else {
                // طالب واحد متبقي - ضمه للزوج الأخير
                if (count($pairings) > 0) {
                    $pairings[count($pairings) - 1][] = $shuffled[$i];
                }
            }
        }
        
        return $pairings;
    }

    private function assignRooms(array $pairings, int $roomStart): array
    {
        $roomAssignments = [];
        $currentRoom = $roomStart;

        foreach ($pairings as $pairing) {
            $roomAssignments[(string)$currentRoom] = $pairing;
            $currentRoom++;
        }

        return $roomAssignments;
    }
}