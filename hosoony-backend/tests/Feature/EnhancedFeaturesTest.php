<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\ClassTaskAssignment;
use App\Models\DailyTaskDefinition;
use App\Models\FeesPlan;
use App\Models\Program;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WeekdaySchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnhancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create required test data
        $this->program = Program::create([
            'name' => 'برنامج اختبار',
            'description' => 'برنامج للاختبار',
            'duration_months' => 12,
            'price' => 1000.00,
            'currency' => 'SAR',
            'status' => 'active',
        ]);

        $this->feesPlan = FeesPlan::create([
            'name' => 'خطة رسوم اختبار',
            'description' => 'خطة رسوم للاختبار',
            'amount' => 100.00,
            'currency' => 'SAR',
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);
    }

    public function test_task_location_field_works_correctly()
    {
        // Test creating task with in_class location
        $task = DailyTaskDefinition::create([
            'name' => 'حفظ سورة الفاتحة',
            'description' => 'حفظ سورة الفاتحة مع التجويد',
            'type' => 'hifz',
            'task_location' => 'in_class',
            'points_weight' => 5,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $this->assertEquals('in_class', $task->task_location);
        $this->assertTrue($task->is_active);

        // Test creating task with homework location
        $homeworkTask = DailyTaskDefinition::create([
            'name' => 'مراجعة سورة البقرة',
            'description' => 'مراجعة حفظ سورة البقرة',
            'type' => 'murajaah',
            'task_location' => 'homework',
            'points_weight' => 3,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        $this->assertEquals('homework', $homeworkTask->task_location);
    }

    public function test_weekday_schedule_creation_and_usage()
    {
        // Create a weekday schedule
        $schedule = WeekdaySchedule::create([
            'name' => 'جدول اختبار',
            'description' => 'جدول للاختبار',
            'schedule' => [
                'sunday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => true,
                ],
                'monday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => true,
                ],
            ],
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->assertTrue($schedule->is_active);
        $this->assertFalse($schedule->is_default);
        $this->assertTrue($schedule->hasDay('sunday'));
        $this->assertFalse($schedule->hasDay('friday'));

        // Test formatted schedule
        $formatted = $schedule->getFormattedSchedule();
        $this->assertArrayHasKey('الأحد', $formatted);
        $this->assertArrayHasKey('الاثنين', $formatted);
    }

    public function test_class_with_weekday_schedule()
    {
        // Create a weekday schedule
        $schedule = WeekdaySchedule::create([
            'name' => 'جدول اختبار',
            'description' => 'جدول للاختبار',
            'schedule' => [
                'sunday' => [
                    'start_time' => '07:00',
                    'end_time' => '08:00',
                    'is_active' => true,
                ],
            ],
            'is_active' => true,
            'is_default' => true,
        ]);

        // Create a class with the schedule (without program_id for now)
        $class = ClassModel::create([
            'program_id' => $this->program->id,
            'weekday_schedule_id' => $schedule->id,
            'name' => 'حلقة اختبار',
            'description' => 'حلقة للاختبار',
            'gender' => 'female',
            'max_students' => 20,
            'current_students' => 0,
            'status' => 'active',
            'start_date' => now(),
        ]);

        $this->assertEquals($schedule->id, $class->weekday_schedule_id);
        $this->assertInstanceOf(WeekdaySchedule::class, $class->weekdaySchedule);
        $this->assertEquals('جدول اختبار', $class->weekdaySchedule->name);
    }

    public function test_class_task_assignments()
    {
        // Create a task
        $task = DailyTaskDefinition::create([
            'name' => 'حفظ سورة الفاتحة',
            'description' => 'حفظ سورة الفاتحة',
            'type' => 'hifz',
            'task_location' => 'in_class',
            'points_weight' => 5,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        // Create a class (without program_id for now)
        $class = ClassModel::create([
            'program_id' => $this->program->id,
            'name' => 'حلقة اختبار',
            'description' => 'حلقة للاختبار',
            'gender' => 'female',
            'max_students' => 20,
            'current_students' => 0,
            'status' => 'active',
            'start_date' => now(),
        ]);

        // Create task assignment
        $assignment = ClassTaskAssignment::create([
            'class_id' => $class->id,
            'daily_task_definition_id' => $task->id,
            'is_active' => true,
            'order' => 1,
        ]);

        $this->assertEquals($class->id, $assignment->class_id);
        $this->assertEquals($task->id, $assignment->daily_task_definition_id);
        $this->assertTrue($assignment->is_active);
        $this->assertEquals(1, $assignment->order);

        // Test relationships
        $this->assertInstanceOf(ClassModel::class, $assignment->class);
        $this->assertInstanceOf(DailyTaskDefinition::class, $assignment->taskDefinition);

        // Test class relationships
        $this->assertCount(1, $class->taskAssignments);
        $this->assertCount(1, $class->activeTaskAssignments);
    }

    public function test_continuous_subscription_billing()
    {
        // Create a student
        $student = User::create([
            'name' => 'طالب اختبار',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'gender' => 'female',
            'status' => 'active',
        ]);

        // Create a subscription with end_date
        $subscription = Subscription::create([
            'student_id' => $student->id,
            'fees_plan_id' => $this->feesPlan->id,
            'billing_cycle' => 'monthly',
            'amount' => 100.00,
            'start_date' => now(),
            'end_date' => now()->addYear(), // Set end date for continuous subscription
            'next_billing_date' => now()->addMonth(),
            'is_active' => true,
            'auto_renew' => true,
            'status' => 'active',
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->isExpired());
        $this->assertFalse($subscription->isCancelled());

        // Test billing calculation
        $nextBilling = $subscription->calculateNextBillingDate();
        $this->assertTrue($nextBilling->isFuture());

        // Test adding billing record
        $subscription->addBillingRecord([
            'payment_id' => 1,
            'billing_cycle' => 'monthly',
            'status' => 'paid',
        ]);

        $this->assertNotNull($subscription->billing_history);
        $this->assertCount(1, $subscription->billing_history);
    }

    public function test_subscription_scopes()
    {
        // Create test students first
        $student1 = User::create([
            'name' => 'طالب 1',
            'email' => 'student1@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $student2 = User::create([
            'name' => 'طالب 2',
            'email' => 'student2@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $student3 = User::create([
            'name' => 'طالب 3',
            'email' => 'student3@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'gender' => 'female',
            'status' => 'active',
        ]);

        // Create test subscriptions
        $activeSubscription = Subscription::create([
            'student_id' => $student1->id,
            'fees_plan_id' => $this->feesPlan->id,
            'billing_cycle' => 'monthly',
            'amount' => 100.00,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'is_active' => true,
            'auto_renew' => true,
            'next_billing_date' => now()->addDays(5),
            'status' => 'active',
        ]);

        $inactiveSubscription = Subscription::create([
            'student_id' => $student2->id,
            'fees_plan_id' => $this->feesPlan->id,
            'billing_cycle' => 'monthly',
            'amount' => 100.00,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'is_active' => false,
            'auto_renew' => false,
            'status' => 'cancelled',
        ]);

        $dueSubscription = Subscription::create([
            'student_id' => $student3->id,
            'fees_plan_id' => $this->feesPlan->id,
            'billing_cycle' => 'monthly',
            'amount' => 100.00,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'is_active' => true,
            'auto_renew' => true,
            'next_billing_date' => now()->subDays(1), // Due yesterday
            'status' => 'active',
        ]);

        // Test scopes
        $activeSubscriptions = Subscription::active()->get();
        $this->assertCount(2, $activeSubscriptions);

        $dueSubscriptions = Subscription::dueForBilling()->get();
        $this->assertCount(1, $dueSubscriptions);
        $this->assertEquals($dueSubscription->id, $dueSubscriptions->first()->id);
    }

    public function test_subscription_cancellation()
    {
        $student = User::create([
            'name' => 'طالب اختبار',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'gender' => 'female',
            'status' => 'active',
        ]);

        $subscription = Subscription::create([
            'student_id' => $student->id,
            'fees_plan_id' => $this->feesPlan->id,
            'billing_cycle' => 'monthly',
            'amount' => 100.00,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'is_active' => true,
            'auto_renew' => true,
            'status' => 'active',
        ]);

        $this->assertTrue($subscription->isActive());

        // Cancel subscription
        $subscription->cancel('طلب الطالب');

        $this->assertFalse($subscription->is_active);
        $this->assertFalse($subscription->auto_renew);
        $this->assertNotNull($subscription->cancelled_at);
        $this->assertEquals('طلب الطالب', $subscription->cancellation_reason);
        $this->assertTrue($subscription->isCancelled());
    }

    public function test_unique_constraints()
    {
        // Test unique constraint on class_task_assignments
        $class = ClassModel::create([
            'program_id' => $this->program->id,
            'name' => 'حلقة اختبار',
            'description' => 'حلقة للاختبار',
            'gender' => 'female',
            'max_students' => 20,
            'current_students' => 0,
            'status' => 'active',
            'start_date' => now(),
        ]);

        $task = DailyTaskDefinition::create([
            'name' => 'حفظ سورة الفاتحة',
            'description' => 'حفظ سورة الفاتحة',
            'type' => 'hifz',
            'task_location' => 'in_class',
            'points_weight' => 5,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        // Create first assignment
        ClassTaskAssignment::create([
            'class_id' => $class->id,
            'daily_task_definition_id' => $task->id,
            'is_active' => true,
            'order' => 1,
        ]);

        // Try to create duplicate assignment
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        ClassTaskAssignment::create([
            'class_id' => $class->id,
            'daily_task_definition_id' => $task->id,
            'is_active' => true,
            'order' => 2,
        ]);
    }
}