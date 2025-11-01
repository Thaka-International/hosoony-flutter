<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OperationsController extends Controller
{
    /**
     * Get scheduler health check and last run times
     */
    public function schedulerLastRun(): JsonResponse
    {
        $commands = [
            'session:reminders',
            'announcements:dispatch-daily',
            'announcements:dispatch-weekly',
            'payment:reminders',
            'daily:close-logs',
            'points:award-daily',
            'badges:weekly',
            'badges:monthly',
        ];

        $lastRuns = [];
        $healthStatus = 'healthy';

        foreach ($commands as $command) {
            $lastRun = Cache::get("scheduler_last_run_{$command}");
            $lastRunTime = $lastRun ? Carbon::parse($lastRun) : null;
            
            $lastRuns[$command] = [
                'last_run' => $lastRunTime?->toISOString(),
                'last_run_human' => $lastRunTime?->diffForHumans(),
                'status' => $this->getCommandStatus($command, $lastRunTime),
            ];

            // Check if command is overdue (only if it has run before)
            if ($lastRunTime && $this->isCommandOverdue($command, $lastRunTime)) {
                $healthStatus = 'warning';
            }
        }

        return response()->json([
            'status' => $healthStatus,
            'timestamp' => now()->toISOString(),
            'commands' => $lastRuns,
            'summary' => [
                'total_commands' => count($commands),
                'healthy_commands' => collect($lastRuns)->where('status', 'healthy')->count(),
                'overdue_commands' => collect($lastRuns)->where('status', 'overdue')->count(),
            ],
        ]);
    }

    /**
     * Get command status based on last run time
     */
    private function getCommandStatus(string $command, ?Carbon $lastRun): string
    {
        if (!$lastRun) {
            return 'never_run';
        }

        if ($this->isCommandOverdue($command, $lastRun)) {
            return 'overdue';
        }

        return 'healthy';
    }

    /**
     * Check if command is overdue based on expected frequency
     */
    private function isCommandOverdue(string $command, ?Carbon $lastRun): bool
    {
        if (!$lastRun) {
            return true;
        }

        $expectedIntervals = [
            'session:reminders' => 2, // 2 minutes
            'announcements:dispatch-daily' => 25, // 25 hours
            'announcements:dispatch-weekly' => 169, // 7 days + 1 hour
            'payment:reminders' => 2, // 2 hours
            'daily:close-logs' => 25, // 25 hours
            'points:award-daily' => 25, // 25 hours
            'badges:weekly' => 169, // 7 days + 1 hour
            'badges:monthly' => 745, // 31 days + 1 hour
        ];

        $expectedInterval = $expectedIntervals[$command] ?? 24;
        $hoursSinceLastRun = $lastRun->diffInHours(now());

        return $hoursSinceLastRun > $expectedInterval;
    }

    /**
     * Update last run time for a command (called by commands)
     */
    public static function updateLastRun(string $command): void
    {
        Cache::put("scheduler_last_run_{$command}", now()->toISOString(), now()->addDays(7));
    }
}