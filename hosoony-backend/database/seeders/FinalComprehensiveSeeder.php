<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Program;
use App\Models\ClassModel;
use App\Models\DailyTaskDefinition;
use App\Models\CompanionsPublication;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\FeesPlan;
use Carbon\Carbon;

class FinalComprehensiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('بدء إنشاء البيانات الشاملة...');

        // إنشاء البرامج
        $this->createPrograms();
        
        // إنشاء المستخدمين
        $this->createUsers();
        
        // إنشاء الفصول
        $this->createClasses();
        
        // إنشاء المهام اليومية
        $this->createDailyTasks();
        
        // إنشاء رفيقات الأحد
        $this->createCompanionsPublications();
        
        // إنشاء تذكيرات المدفوعات
        $this->createPaymentReminders();
        
        // إنشاء الاشتراكات والمدفوعات
        $this->createSubscriptionsAndPayments();

        $this->command->info('تم إنشاء البيانات الشاملة بنجاح!');
    }

    private function createPrograms(): void
    {
        $this->command->info('إنشاء البرامج...');

        Program::create([
            'name' => 'برنامج الحفظ المتقدم',
            'description' => 'برنامج شامل لحفظ القرآن الكريم مع التفسير والتجويد',
            'status' => 'active',
            'currency' => 'SAR',
        ]);

        Program::create([
            'name' => 'برنامج المراجعة والتثبيت',
            'description' => 'برنامج مراجعة وتثبيت المحفوظ مع التركيز على التجويد',
            'status' => 'active',
            'currency' => 'SAR',
        ]);
    }

    private function createUsers(): void
    {
        $this->command->info('إنشاء المستخدمين...');

        // 1 Admin
        User::create([
            'name' => 'أحمد المدير',
            'email' => 'admin@hosoony.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'gender' => 'male',
            'status' => 'active',
        ]);

        // 2 معلمين (M/F)
        User::create([
            'name' => 'محمد المعلم',
            'email' => 'teacher.male@hosoony.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'gender' => 'male',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'فاطمة المعلمة',
            'email' => 'teacher.female@hosoony.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'gender' => 'female',
            'status' => 'active',
        ]);

        // 2 Supports
        User::create([
            'name' => 'علي المساعد',
            'email' => 'support1@hosoony.com',
            'password' => Hash::make('password'),
            'role' => 'teacher_support',
            'gender' => 'male',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'زينب المساعدة',
            'email' => 'support2@hosoony.com',
            'password' => Hash::make('password'),
            'role' => 'teacher_support',
            'gender' => 'female',
            'status' => 'active',
        ]);

        // 10 طلاب (5/5)
        $maleStudents = [
            ['name' => 'عبدالله الطالب', 'email' => 'student.male1@hosoony.com'],
            ['name' => 'سعد الطالب', 'email' => 'student.male2@hosoony.com'],
            ['name' => 'خالد الطالب', 'email' => 'student.male3@hosoony.com'],
            ['name' => 'عمر الطالب', 'email' => 'student.male4@hosoony.com'],
            ['name' => 'يوسف الطالب', 'email' => 'student.male5@hosoony.com'],
        ];

        $femaleStudents = [
            ['name' => 'فاطمة الطالبة', 'email' => 'student.female1@hosoony.com'],
            ['name' => 'عائشة الطالبة', 'email' => 'student.female2@hosoony.com'],
            ['name' => 'خديجة الطالبة', 'email' => 'student.female3@hosoony.com'],
            ['name' => 'مريم الطالبة', 'email' => 'student.female4@hosoony.com'],
            ['name' => 'زينب الطالبة', 'email' => 'student.female5@hosoony.com'],
        ];

        foreach ($maleStudents as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'gender' => 'male',
                'status' => 'active',
            ]);
        }

        foreach ($femaleStudents as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'gender' => 'female',
                'status' => 'active',
            ]);
        }
    }

    private function createClasses(): void
    {
        $this->command->info('إنشاء الفصول...');

        $programs = Program::all();
        $teachers = User::where('role', 'teacher')->get();
        $students = User::where('role', 'student')->get();

        // حلقة ذكور - الأحد
        $maleClass = ClassModel::create([
            'name' => 'حلقة الذكور - الأحد',
            'description' => 'حلقة الذكور ليوم الأحد',
            'program_id' => $programs->first()->id,
            'gender' => 'male',
            'max_students' => 10,
            'status' => 'active',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => Carbon::now()->addMonths(3),
            'zoom_url' => 'https://zoom.us/j/111111111',
            'zoom_password' => 'male123',
            'zoom_room_start' => 1,
        ]);

        // حلقة إناث - الثلاثاء
        $femaleClass = ClassModel::create([
            'name' => 'حلقة الإناث - الثلاثاء',
            'description' => 'حلقة الإناث ليوم الثلاثاء',
            'program_id' => $programs->last()->id,
            'gender' => 'female',
            'max_students' => 10,
            'status' => 'active',
            'start_date' => Carbon::now()->startOfMonth(),
            'end_date' => Carbon::now()->addMonths(3),
            'zoom_url' => 'https://zoom.us/j/222222222',
            'zoom_password' => 'female123',
            'zoom_room_start' => 10,
        ]);

        // ربط المعلمين بالفصول
        $maleTeacher = $teachers->where('gender', 'male')->first();
        $femaleTeacher = $teachers->where('gender', 'female')->first();

        $maleTeacher->update(['class_id' => $maleClass->id]);
        $femaleTeacher->update(['class_id' => $femaleClass->id]);

        // ربط الطلاب بالفصول
        $maleStudents = $students->where('gender', 'male');
        $femaleStudents = $students->where('gender', 'female');

        foreach ($maleStudents as $student) {
            $student->update(['class_id' => $maleClass->id]);
        }

        foreach ($femaleStudents as $student) {
            $student->update(['class_id' => $femaleClass->id]);
        }
    }

    private function createDailyTasks(): void
    {
        $this->command->info('إنشاء المهام اليومية الافتراضية...');

        $taskTypes = [
            ['name' => 'حفظ جديد', 'description' => 'حفظ آيات جديدة من القرآن الكريم', 'type' => 'hifz'],
            ['name' => 'مراجعة', 'description' => 'مراجعة المحفوظ السابق', 'type' => 'murajaah'],
            ['name' => 'تلاوة', 'description' => 'تلاوة القرآن الكريم مع التجويد', 'type' => 'tilawah'],
            ['name' => 'تفسير', 'description' => 'دراسة تفسير الآيات', 'type' => 'tafseer'],
            ['name' => 'تجويد', 'description' => 'تعلم أحكام التجويد', 'type' => 'tajweed'],
        ];

        foreach ($taskTypes as $taskType) {
            DailyTaskDefinition::create([
                'name' => $taskType['name'],
                'description' => $taskType['description'],
                'type' => $taskType['type'],
                'points_weight' => rand(1, 3),
                'duration_minutes' => rand(30, 90),
                'is_active' => true,
            ]);
        }
    }

    private function createCompanionsPublications(): void
    {
        $this->command->info('إنشاء رفيقات الأحد...');

        $classes = ClassModel::all();
        $sunday = Carbon::now()->next(Carbon::SUNDAY);

        foreach ($classes as $class) {
            $students = $class->students;
            
            if ($students->count() >= 2) {
                // إنشاء رفيقات للأحد القادم
                $pairings = $this->generateRotatingPairings($students->pluck('id')->toArray());
                
                CompanionsPublication::create([
                    'class_id' => $class->id,
                    'target_date' => $sunday,
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
            }
        }
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

    private function createPaymentReminders(): void
    {
        $this->command->info('إنشاء تذكيرات المدفوعات...');

        $students = User::where('role', 'student')->get();
        $reminderDays = [7, 3, 1]; // أيام قبل الاستحقاق

        foreach ($students as $student) {
            $dueDate = Carbon::now()->addDays(30); // استحقاق بعد 30 يوم

            foreach ($reminderDays as $daysBefore) {
                $reminderDate = $dueDate->copy()->subDays($daysBefore);
                
                if ($reminderDate->isFuture()) {
                    Notification::create([
                        'user_id' => $student->id,
                        'title' => 'تذكير بدفع الرسوم',
                        'message' => "تذكير: موعد استحقاق الرسوم بعد {$daysBefore} أيام. المبلغ: 500 ريال",
                        'channel' => 'email',
                        'sent_at' => $reminderDate,
                    ]);
                }
            }
        }
    }

    private function createSubscriptionsAndPayments(): void
    {
        $this->command->info('إنشاء الاشتراكات والمدفوعات...');

        $programs = Program::all();
        $students = User::where('role', 'student')->get();

        // إنشاء خطة رسوم
        $feesPlan = FeesPlan::create([
            'name' => 'الخطة الأساسية',
            'description' => 'خطة الرسوم الأساسية للطلاب',
            'amount' => 500.00,
            'currency' => 'SAR',
            'duration_months' => 3,
            'is_active' => true,
        ]);

        foreach ($students as $student) {
            // إنشاء اشتراك
            $subscription = Subscription::create([
                'student_id' => $student->id,
                'fees_plan_id' => $feesPlan->id,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(3),
                'status' => 'active',
            ]);

            // إنشاء مدفوعات (بعض مدفوعة، بعض معلقة)
            if (rand(0, 1)) {
                Payment::create([
                    'student_id' => $student->id,
                    'subscription_id' => $subscription->id,
                    'amount' => $feesPlan->amount,
                    'currency' => 'SAR',
                    'payment_method' => 'bank_transfer',
                    'status' => 'completed',
                    'due_date' => Carbon::now()->addDays(30),
                    'paid_date' => Carbon::now()->subDays(rand(1, 30)),
                    'notes' => 'رسوم الفصل الدراسي',
                ]);
            } else {
                Payment::create([
                    'student_id' => $student->id,
                    'subscription_id' => $subscription->id,
                    'amount' => $feesPlan->amount,
                    'currency' => 'SAR',
                    'payment_method' => 'bank_transfer',
                    'status' => 'pending',
                    'due_date' => Carbon::now()->addDays(30),
                    'notes' => 'رسوم الفصل الدراسي',
                ]);
            }
        }
    }
}