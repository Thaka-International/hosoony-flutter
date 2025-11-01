<?php

namespace App\Console\Commands;

use App\Models\DailyLog;
use App\Models\GamificationPoint;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class PointsAwardDailyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:award-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Award daily points at midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTime = now()->format('H:i');
        
        // Only run at midnight
        if ($currentTime !== '00:00') {
            $this->info("Skipping daily points award. Current time: {$currentTime}, configured time: 00:00");
            return Command::SUCCESS;
        }

        $yesterday = now()->subDay()->format('Y-m-d');
        
        // Get verified logs from yesterday
        $logs = DailyLog::whereDate('log_date', $yesterday)
            ->where('status', 'verified')
            ->with(['items.taskDefinition', 'student'])
            ->get();

        $pointsAwarded = 0;
        $studentsProcessed = 0;

        foreach ($logs as $log) {
            $totalPoints = 0;
            
            // Calculate points from completed tasks
            foreach ($log->items as $item) {
                if ($item->status === 'completed') {
                    $totalPoints += $item->taskDefinition->points_weight;
                }
            }

            // Add finish order bonus
            $bonusConfig = config('quran_lms.bonus.ranks', [1 => 10, 2 => 6, 3 => 3]);
            if ($log->finish_order && isset($bonusConfig[$log->finish_order])) {
                $totalPoints += $bonusConfig[$log->finish_order];
            }

            if ($totalPoints > 0) {
                // Check if points already awarded for this log
                $existingPoints = GamificationPoint::where('student_id', $log->student_id)
                    ->where('source_type', 'daily_log')
                    ->where('source_id', $log->id)
                    ->first();

                if (!$existingPoints) {
                    GamificationPoint::create([
                        'student_id' => $log->student_id,
                        'source_type' => 'daily_log',
                        'source_id' => $log->id,
                        'points' => $totalPoints,
                        'description' => "Daily tasks completion - Order: {$log->finish_order}",
                        'awarded_at' => now(),
                    ]);
                    
                    $pointsAwarded += $totalPoints;
                }
            }
            
            $studentsProcessed++;
        }

        $this->info("Daily points awarded: {$pointsAwarded} points to {$studentsProcessed} students for date {$yesterday}");
        
        // Update last run time
        \App\Http\Controllers\Api\V1\OperationsController::updateLastRun('points:award-daily');
        
        return Command::SUCCESS;
    }
}