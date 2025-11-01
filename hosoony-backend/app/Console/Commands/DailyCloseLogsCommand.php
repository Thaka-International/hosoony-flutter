<?php

namespace App\Console\Commands;

use App\Models\DailyLog;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DailyCloseLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:close-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close daily logs at 23:59 for yesterday';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTime = now()->format('H:i');
        
        // Only run at 23:59
        if ($currentTime !== '23:59') {
            $this->info("Skipping daily logs closure. Current time: {$currentTime}, configured time: 23:59");
            return Command::SUCCESS;
        }

        $yesterday = now()->subDay()->format('Y-m-d');
        
        // Get submitted logs from yesterday that are still open
        $logs = DailyLog::whereDate('log_date', $yesterday)
            ->where('status', 'submitted')
            ->get();

        $closedCount = 0;

        foreach ($logs as $log) {
            // Auto-verify if verification policy is set to auto
            $verificationPolicy = config('quran_lms.daily_logs.verification_policy', 'manual');
            
            if ($verificationPolicy === 'auto') {
                $log->update([
                    'status' => 'verified',
                    'verified_at' => now(),
                    'notes' => 'تم التحقق تلقائياً عند إغلاق اليوم',
                ]);
            } else {
                $log->update([
                    'status' => 'closed',
                    'notes' => 'تم إغلاق السجل تلقائياً',
                ]);
            }
            
            $closedCount++;
        }

        $this->info("Daily logs closed: {$closedCount} logs for date {$yesterday}");
        
        // Update last run time
        \App\Http\Controllers\Api\V1\OperationsController::updateLastRun('daily:close-logs');
        
        return Command::SUCCESS;
    }
}