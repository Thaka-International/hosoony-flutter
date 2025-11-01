<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Program;
use App\Models\ClassModel;
use App\Models\ClassSchedule;
use App\Models\Session;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\FeesPlan;
use App\Models\Badge;
use App\Models\GamificationPoint;
use App\Models\StudentBadge;
use App\Models\DailyLog;
use App\Models\DailyTaskDefinition;
use App\Models\Activity;
use App\Models\Exam;
use App\Models\Announcement;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Programs
        $program1 = Program::create([
            'name' => 'برنامج حفظ القرآن الكريم',
            'description' => 'برنامج شامل لحفظ القرآن الكريم مع التجويد',
            'status' => 'active',
            'duration_months' => 24,
            'price' => 2000.00,
            'currency' => 'SAR',
        ]);

        $program2 = Program::create([
            'name' => 'برنامج التجويد المتقدم',
            'description' => 'برنامج متخصص في تعلم أحكام التجويد',
            'status' => 'active',
            'duration_months' => 12,
            'price' => 1500.00,
            'currency' => 'SAR',
        ]);

        // Create Classes
        $class1 = ClassModel::create([
            'program_id' => $program1->id,
            'name' => 'فصل الذكور - المستوى الأول',
            'description' => 'فصل للذكور في المستوى الأول',
            'gender' => 'male',
            'max_students' => 20,
            'current_students' => 0,
            'status' => 'active',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(24),
        ]);

        $class2 = ClassModel::create([
            'program_id' => $program1->id,
            'name' => 'فصل الإناث - المستوى الأول',
            'description' => 'فصل للإناث في المستوى الأول',
            'gender' => 'female',
            'max_students' => 20,
            'current_students' => 0,
            'status' => 'active',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(24),
        ]);

        // Create Class Schedules
        ClassSchedule::create([
            'class_id' => $class1->id,
            'day_of_week' => 'sunday',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'zoom_link' => 'https://zoom.us/j/123456789',
            'zoom_meeting_id' => '123 456 789',
            'zoom_password' => 'hosoony123',
            'notes' => 'جلسة حفظ',
            'is_active' => true,
        ]);

        ClassSchedule::create([
            'class_id' => $class1->id,
            'day_of_week' => 'tuesday',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'zoom_link' => 'https://zoom.us/j/123456789',
            'zoom_meeting_id' => '123 456 789',
            'zoom_password' => 'hosoony123',
            'notes' => 'جلسة مراجعة',
            'is_active' => true,
        ]);

        ClassSchedule::create([
            'class_id' => $class2->id,
            'day_of_week' => 'sunday',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'zoom_link' => 'https://zoom.us/j/987654321',
            'zoom_meeting_id' => '987 654 321',
            'zoom_password' => 'hosoony456',
            'notes' => 'جلسة حفظ',
            'is_active' => true,
        ]);

        ClassSchedule::create([
            'class_id' => $class2->id,
            'day_of_week' => 'tuesday',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'zoom_link' => 'https://zoom.us/j/987654321',
            'zoom_meeting_id' => '987 654 321',
            'zoom_password' => 'hosoony456',
            'notes' => 'جلسة مراجعة',
            'is_active' => true,
        ]);

        // Create Teachers
        $teacher1 = User::firstOrCreate(
            ['email' => 'teacher1@hosoony.com'],
            [
                'name' => 'أحمد محمد العلي',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'role' => 'teacher',
                'class_id' => $class1->id,
                'phone' => '+966501234567',
                'status' => 'active',
            ]
        );

        $teacher2 = User::firstOrCreate(
            ['email' => 'teacher2@hosoony.com'],
            [
                'name' => 'فاطمة أحمد الزهراء',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'role' => 'teacher',
                'class_id' => $class2->id,
                'phone' => '+966501234568',
                'status' => 'active',
            ]
        );

        // Create Students
        $students = [];
        for ($i = 1; $i <= 10; $i++) {
            $students[] = User::firstOrCreate(
                ['email' => "student$i@hosoony.com"],
                [
                    'name' => "طالب $i",
                    'password' => Hash::make('password'),
                    'gender' => 'male',
                    'role' => 'student',
                    'class_id' => $class1->id,
                    'phone' => "+96650123456$i",
                    'guardian_name' => "ولي أمر $i",
                    'guardian_phone' => "+96650123456$i",
                    'status' => 'active',
                ]
            );
        }

        for ($i = 11; $i <= 20; $i++) {
            $students[] = User::firstOrCreate(
                ['email' => "student$i@hosoony.com"],
                [
                    'name' => "طالبة $i",
                    'password' => Hash::make('password'),
                    'gender' => 'female',
                    'role' => 'student',
                    'class_id' => $class2->id,
                    'phone' => "+96650123456$i",
                    'guardian_name' => "ولي أمر $i",
                    'guardian_phone' => "+96650123456$i",
                    'status' => 'active',
                ]
            );
        }

        // Update class student counts
        $class1->update(['current_students' => 10]);
        $class2->update(['current_students' => 10]);

        // Create Fees Plans
        $feesPlan1 = FeesPlan::create([
            'name' => 'الخطة الشهرية',
            'description' => 'اشتراك شهري',
            'amount' => 200.00,
            'currency' => 'SAR',
            'billing_period' => 'monthly',
            'duration_months' => 1,
            'is_active' => true,
        ]);

        $feesPlan2 = FeesPlan::create([
            'name' => 'الخطة الربعية',
            'description' => 'اشتراك ربع سنوي',
            'amount' => 500.00,
            'currency' => 'SAR',
            'billing_period' => 'quarterly',
            'duration_months' => 3,
            'is_active' => true,
        ]);

        // Create Subscriptions
        foreach ($students as $student) {
            Subscription::create([
                'student_id' => $student->id,
                'fees_plan_id' => $feesPlan1->id,
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->addMonth(),
                'status' => 'active',
            ]);
        }

        // Create Payments
        foreach ($students as $student) {
            Payment::create([
                'student_id' => $student->id,
                'subscription_id' => $student->subscriptions()->first()->id,
                'amount' => 200.00,
                'currency' => 'SAR',
                'payment_method' => 'cash',
                'status' => 'completed',
                'due_date' => now()->startOfMonth(),
                'paid_date' => now()->startOfMonth(),
                'notes' => 'دفعة شهرية',
            ]);
        }

        // Create Badges
        $badges = [
            ['name' => 'الحافظ الصغير', 'description' => 'لحفظ أول جزء', 'points_required' => 100],
            ['name' => 'المثابر', 'description' => 'للحضور المستمر', 'points_required' => 200],
            ['name' => 'النجمة المضيئة', 'description' => 'للأداء المتميز', 'points_required' => 300],
        ];

        foreach ($badges as $badgeData) {
            Badge::create([
                'name' => $badgeData['name'],
                'description' => $badgeData['description'],
                'icon_url' => '/images/badges/star.png',
                'category' => 'achievement',
                'points_required' => $badgeData['points_required'],
                'criteria' => 'حفظ جزء واحد',
                'is_active' => true,
            ]);
        }

        // Create Activities
        $activity1 = Activity::create([
            'title' => 'حفظ سورة البقرة - الآيات 1-50',
            'description' => 'مهمة يومية لحفظ الآيات من 1 إلى 50 من سورة البقرة',
            'type' => 'daily_task',
            'points' => 50,
            'is_daily' => true,
            'is_recurring' => true,
            'created_by' => $teacher1->id,
            'status' => 'published',
            'instructions' => 'يجب على الطالب حفظ الآيات مع التجويد الصحيح',
            'requirements' => 'تسجيل صوتي للآيات المحفوظة',
        ]);

        $activity2 = Activity::create([
            'title' => 'مراجعة المحفوظ - الجزء الأول',
            'description' => 'مراجعة شاملة للجزء الأول من القرآن الكريم',
            'type' => 'assignment',
            'points' => 100,
            'is_daily' => false,
            'is_recurring' => false,
            'created_by' => $teacher1->id,
            'status' => 'published',
            'due_date' => now()->addWeek(),
            'instructions' => 'مراجعة كاملة للجزء الأول مع التركيز على التجويد',
            'requirements' => 'تسجيل كامل للجزء الأول',
        ]);

        $activity3 = Activity::create([
            'title' => 'اختبار التجويد - أحكام النون الساكنة',
            'description' => 'اختبار في أحكام النون الساكنة والتنوين',
            'type' => 'quiz',
            'points' => 75,
            'is_daily' => false,
            'is_recurring' => false,
            'created_by' => $teacher2->id,
            'status' => 'published',
            'due_date' => now()->addDays(3),
            'instructions' => 'اختبار نظري وعملي في أحكام النون الساكنة',
            'requirements' => 'إجابة على الأسئلة + تطبيق عملي',
        ]);

        // Assign activities to classes
        $activity1->classes()->attach([$class1->id, $class2->id]);
        $activity2->classes()->attach([$class1->id]);
        $activity3->classes()->attach([$class2->id]);

        // Create Gamification Points
        foreach ($students as $student) {
            GamificationPoint::create([
                'student_id' => $student->id,
                'source_type' => 'daily_log',
                'source_id' => 1,
                'points' => rand(50, 150),
                'description' => 'نقاط الحضور اليومي',
                'awarded_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        // Create Student Badges
        foreach (array_slice($students, 0, 5) as $student) {
            StudentBadge::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'badge_id' => 1,
                ],
                [
                    'awarded_at' => now()->subDays(rand(1, 15)),
                ]
            );
        }

        // Create Daily Task Definitions
        $taskDefinitions = [
            ['name' => 'حفظ آيات جديدة', 'type' => 'hifz', 'points_weight' => 10],
            ['name' => 'مراجعة المحفوظ', 'type' => 'murajaah', 'points_weight' => 8],
            ['name' => 'تلاوة القرآن', 'type' => 'tilawah', 'points_weight' => 5],
        ];

        foreach ($taskDefinitions as $taskData) {
            DailyTaskDefinition::create([
                'name' => $taskData['name'],
                'description' => 'مهمة يومية',
                'type' => $taskData['type'],
                'points_weight' => $taskData['points_weight'],
                'duration_minutes' => 30,
                'is_active' => true,
            ]);
        }

        // Create Daily Logs
        foreach ($students as $student) {
            DailyLog::create([
                'student_id' => $student->id,
                'log_date' => now()->subDays(rand(1, 7)),
                'status' => 'verified',
                'finish_order' => rand(1, 5),
                'verified_by' => $student->class_id == $class1->id ? $teacher1->id : $teacher2->id,
                'verified_at' => now()->subDays(rand(1, 7)),
                'notes' => 'تم إنجاز المهام بنجاح',
            ]);
        }

        // Create Exams
        Exam::create([
            'class_id' => $class1->id,
            'created_by' => $teacher1->id,
            'title' => 'امتحان نهاية الشهر',
            'description' => 'امتحان شامل للمادة المحفوظة',
            'type' => 'midterm',
            'total_points' => 100,
            'duration_minutes' => 60,
            'scheduled_at' => now()->addWeek(),
            'status' => 'published',
        ]);

        // Create Announcements
        Announcement::create([
            'created_by' => 1,
            'title' => 'إجازة عيد الفطر',
            'content' => 'نحيط علمكم بأن إجازة عيد الفطر ستبدأ من يوم...',
            'priority' => 'high',
            'target_audience' => 'all',
            'sent_at' => now(),
            'status' => 'sent',
        ]);

        // Create Activity Logs
        ActivityLog::create([
            'user_id' => 1,
            'action' => 'user_created',
            'description' => 'تم إنشاء مستخدم جديد',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Laravel Seeder',
        ]);

        ActivityLog::create([
            'user_id' => 1,
            'action' => 'payment_created',
            'description' => 'تم إنشاء دفعة جديدة',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Laravel Seeder',
        ]);

        ActivityLog::create([
            'user_id' => 1,
            'action' => 'subscription_created',
            'description' => 'تم إنشاء اشتراك جديد',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Laravel Seeder',
        ]);

        // Create Sessions
        Session::create([
            'class_id' => $class1->id,
            'teacher_id' => $teacher1->id,
            'title' => 'جلسة حفظ سورة البقرة',
            'description' => 'جلسة لحفظ آيات من سورة البقرة',
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(11, 0),
            'status' => 'scheduled',
            'notes' => 'جلسة عادية',
        ]);

        Session::create([
            'class_id' => $class2->id,
            'teacher_id' => $teacher2->id,
            'title' => 'جلسة مراجعة التجويد',
            'description' => 'جلسة لمراجعة أحكام التجويد',
            'starts_at' => now()->addDay()->setTime(14, 0),
            'ends_at' => now()->addDay()->setTime(16, 0),
            'status' => 'scheduled',
            'notes' => 'جلسة مراجعة',
        ]);

        $this->command->info('تم إنشاء البيانات التجريبية بنجاح!');
    }
}