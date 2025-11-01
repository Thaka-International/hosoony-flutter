<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Badge;
use App\Models\StudentBadge;
use App\Models\GamificationPoint;
use Illuminate\Console\Command;
use Carbon\Carbon;

class BadgesWeeklyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Award weekly badges on Sunday at 20:00';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentWeekday = now()->format('D');
        $currentTime = now()->format('H:i');

        // Only run on Sunday at 20:00
        if ($currentWeekday !== 'Sun' || $currentTime !== '20:00') {
            $this->info("Skipping weekly badges award. Current: {$currentWeekday} {$currentTime}, configured: Sun 20:00");
            return Command::SUCCESS;
        }

        $weekStart = now()->subWeek()->startOfWeek();
        $weekEnd = now()->subWeek()->endOfWeek();

        // Get weekly badges
        $badges = Badge::where('category', 'weekly')
            ->where('is_active', true)
            ->get();

        $badgesAwarded = 0;

        foreach ($badges as $badge) {
            $students = $this->getEligibleStudents($badge, $weekStart, $weekEnd);

            foreach ($students as $student) {
                // Check if student already has this badge
                $existingBadge = StudentBadge::where('student_id', $student->id)
                    ->where('badge_id', $badge->id)
                    ->first();

                if (!$existingBadge) {
                    StudentBadge::create([
                        'student_id' => $student->id,
                        'badge_id' => $badge->id,
                        'awarded_at' => now(),
                    ]);

                    $badgesAwarded++;
                }
            }
        }

        $this->info("Weekly badges awarded: {$badgesAwarded} badges");
        
        // Update last run time
        \App\Http\Controllers\Api\V1\OperationsController::updateLastRun('badges:weekly');
        
        return Command::SUCCESS;
    }

    /**
     * Get students eligible for badge
     */
    private function getEligibleStudents(Badge $badge, Carbon $weekStart, Carbon $weekEnd)
    {
        $criteria = $badge->criteria ?? [];
        
        // Get students with required points in the week
        if (isset($criteria['min_points'])) {
            return User::where('role', 'student')
                ->where('status', 'active')
                ->whereHas('gamificationPoints', function ($query) use ($weekStart, $weekEnd, $criteria) {
                    $query->whereBetween('awarded_at', [$weekStart, $weekEnd])
                        ->selectRaw('SUM(points) as total_points')
                        ->having('total_points', '>=', $criteria['min_points']);
                })
                ->get();
        }

        // Default: get all active students
        return User::where('role', 'student')
            ->where('status', 'active')
            ->get();
    }
}