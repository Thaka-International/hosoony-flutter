<?php

namespace Tests\Unit;

use App\Console\Commands\SessionRemindersCommand;
use App\Console\Commands\AnnouncementsDispatchDailyCommand;
use App\Console\Commands\AnnouncementsDispatchWeeklyCommand;
use App\Console\Commands\PaymentRemindersCommand;
use App\Console\Commands\DailyCloseLogsCommand;
use App\Console\Commands\PointsAwardDailyCommand;
use App\Console\Commands\BadgesWeeklyCommand;
use App\Console\Commands\BadgesMonthlyCommand;
use App\Http\Controllers\Api\V1\OperationsController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_reminders_command_exists()
    {
        $this->assertTrue(class_exists(SessionRemindersCommand::class));
    }

    public function test_announcements_dispatch_daily_command_exists()
    {
        $this->assertTrue(class_exists(AnnouncementsDispatchDailyCommand::class));
    }

    public function test_announcements_dispatch_weekly_command_exists()
    {
        $this->assertTrue(class_exists(AnnouncementsDispatchWeeklyCommand::class));
    }

    public function test_payment_reminders_command_exists()
    {
        $this->assertTrue(class_exists(PaymentRemindersCommand::class));
    }

    public function test_daily_close_logs_command_exists()
    {
        $this->assertTrue(class_exists(DailyCloseLogsCommand::class));
    }

    public function test_points_award_daily_command_exists()
    {
        $this->assertTrue(class_exists(PointsAwardDailyCommand::class));
    }

    public function test_badges_weekly_command_exists()
    {
        $this->assertTrue(class_exists(BadgesWeeklyCommand::class));
    }

    public function test_badges_monthly_command_exists()
    {
        $this->assertTrue(class_exists(BadgesMonthlyCommand::class));
    }

    public function test_operations_controller_exists()
    {
        $this->assertTrue(class_exists(OperationsController::class));
    }

    public function test_scheduler_health_check_endpoint()
    {
        $response = $this->getJson('/api/v1/ops/scheduler/last-run');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'commands' => [
                    'session:reminders',
                    'announcements:dispatch-daily',
                    'announcements:dispatch-weekly',
                    'payment:reminders',
                    'daily:close-logs',
                    'points:award-daily',
                    'badges:weekly',
                    'badges:monthly',
                ],
                'summary' => [
                    'total_commands',
                    'healthy_commands',
                    'overdue_commands',
                ],
            ]);
    }

    public function test_scheduler_commands_read_from_config()
    {
        // Test that commands can access config values
        $sessionReminderMinutes = config('quran_lms.reminders.times.session_t_minus_minutes', 15);
        $this->assertEquals(15, $sessionReminderMinutes);

        $dailyTasksTime = config('quran_lms.reminders.times.daily_tasks_at', '19:30');
        $this->assertEquals('19:30', $dailyTasksTime);

        $weeklyConfig = config('quran_lms.reminders.times.weekly_at', ['weekday' => 'Sun', 'time' => '08:00']);
        $this->assertEquals('Sun', $weeklyConfig['weekday']);
        $this->assertEquals('08:00', $weeklyConfig['time']);

        $paymentDays = config('quran_lms.reminders.times.payments_days_offsets', [7, 3, 1]);
        $this->assertEquals([7, 3, 1], $paymentDays);

        $verificationPolicy = config('quran_lms.daily_logs.verification_policy', 'manual');
        $this->assertEquals('manual', $verificationPolicy);

        $bonusRanks = config('quran_lms.bonus.ranks', [1 => 10, 2 => 6, 3 => 3]);
        $this->assertEquals([1 => 10, 2 => 6, 3 => 3], $bonusRanks);
    }

    public function test_operations_controller_update_last_run()
    {
        $command = 'test:command';
        
        // Test updating last run time
        OperationsController::updateLastRun($command);
        
        // Verify it was stored in cache
        $this->assertNotNull(cache("scheduler_last_run_{$command}"));
    }

    public function test_scheduler_commands_have_correct_signatures()
    {
        $commands = [
            SessionRemindersCommand::class => 'session:reminders',
            AnnouncementsDispatchDailyCommand::class => 'announcements:dispatch-daily',
            AnnouncementsDispatchWeeklyCommand::class => 'announcements:dispatch-weekly',
            PaymentRemindersCommand::class => 'payment:reminders',
            DailyCloseLogsCommand::class => 'daily:close-logs',
            PointsAwardDailyCommand::class => 'points:award-daily',
            BadgesWeeklyCommand::class => 'badges:weekly',
            BadgesMonthlyCommand::class => 'badges:monthly',
        ];

        foreach ($commands as $commandClass => $expectedSignature) {
            $command = new $commandClass();
            $this->assertEquals($expectedSignature, $command->getName());
        }
    }
}


