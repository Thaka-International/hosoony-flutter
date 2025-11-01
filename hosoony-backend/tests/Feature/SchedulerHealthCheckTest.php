<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\V1\OperationsController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Carbon\Carbon;

class SchedulerHealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduler_health_check_returns_healthy_status()
    {
        $response = $this->getJson('/api/v1/ops/scheduler/last-run');
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
            ]);
    }

    public function test_scheduler_health_check_shows_never_run_status()
    {
        $response = $this->getJson('/api/v1/ops/scheduler/last-run');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        
        // All commands should show never_run status initially
        foreach ($data['commands'] as $command => $info) {
            $this->assertEquals('never_run', $info['status']);
            $this->assertNull($info['last_run']);
        }
    }

    public function test_scheduler_health_check_shows_overdue_status()
    {
        // Set a very old last run time for session:reminders
        Cache::put('scheduler_last_run_session:reminders', now()->subHours(3)->toISOString(), now()->addDays(7));
        
        $response = $this->getJson('/api/v1/ops/scheduler/last-run');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        
        // session:reminders should show overdue status
        $this->assertEquals('overdue', $data['commands']['session:reminders']['status']);
        $this->assertEquals('warning', $response->json()['status']); // Overall status should be warning due to overdue command
    }

    public function test_scheduler_health_check_shows_healthy_status_for_recent_run()
    {
        // Set a recent last run time for session:reminders
        Cache::put('scheduler_last_run_session:reminders', now()->subMinutes(1)->toISOString(), now()->addDays(7));
        
        $response = $this->getJson('/api/v1/ops/scheduler/last-run');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        
        // session:reminders should show healthy status
        $this->assertEquals('healthy', $data['commands']['session:reminders']['status']);
        $this->assertNotNull($data['commands']['session:reminders']['last_run']);
    }

    public function test_scheduler_health_check_summary_counts()
    {
        // Set some commands as healthy and some as overdue
        Cache::put('scheduler_last_run_session:reminders', now()->subMinutes(1)->toISOString(), now()->addDays(7));
        Cache::put('scheduler_last_run_payment:reminders', now()->subHours(3)->toISOString(), now()->addDays(7));
        
        $response = $this->getJson('/api/v1/ops/scheduler/last-run');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        
        $this->assertEquals(8, $data['summary']['total_commands']);
        $this->assertEquals(1, $data['summary']['healthy_commands']);
        $this->assertEquals(1, $data['summary']['overdue_commands']);
    }

    public function test_operations_controller_update_last_run_method()
    {
        $command = 'test:command';
        
        // Test updating last run time
        OperationsController::updateLastRun($command);
        
        // Verify it was stored in cache
        $this->assertNotNull(Cache::get("scheduler_last_run_{$command}"));
        
        // Verify the timestamp is recent
        $lastRun = Carbon::parse(Cache::get("scheduler_last_run_{$command}"));
        $this->assertTrue($lastRun->isAfter(now()->subMinute()));
    }

    public function test_scheduler_health_check_includes_all_required_commands()
    {
        $response = $this->getJson('/api/v1/ops/scheduler/last-run');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        
        $expectedCommands = [
            'session:reminders',
            'announcements:dispatch-daily',
            'announcements:dispatch-weekly',
            'payment:reminders',
            'daily:close-logs',
            'points:award-daily',
            'badges:weekly',
            'badges:monthly',
        ];
        
        foreach ($expectedCommands as $command) {
            $this->assertArrayHasKey($command, $data['commands']);
            $this->assertArrayHasKey('last_run', $data['commands'][$command]);
            $this->assertArrayHasKey('last_run_human', $data['commands'][$command]);
            $this->assertArrayHasKey('status', $data['commands'][$command]);
        }
    }
}
