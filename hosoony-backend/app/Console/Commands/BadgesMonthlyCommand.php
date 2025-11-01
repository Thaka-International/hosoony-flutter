<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Badge;
use App\Models\StudentBadge;
use App\Models\GamificationPoint;
use Illuminate\Console\Command;
use Carbon\Carbon;

class BadgesMonthlyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Award monthly badges on last day at 20:00';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTime = now()->format('H:i');
        $isLastDayOfMonth = now()->isLastDayOfMonth();

        // Only run on last day of month at 20:00
        if (!$isLastDayOfMonth || $currentTime !== '20:00') {
            $this->info("Skipping monthly badges award. Current: " . now()->format('Y-m-d H:i') . ", configured: Last day 20:00");
            return Command::SUCCESS;
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // Get monthly badges
        $badges = Badge::where('category', 'monthly')
            ->where('is_active', true)
            ->get();

        $badgesAwarded = 0;

        foreach ($badges as $badge) {
            $students = $this->getEligibleStudents($badge, $monthStart, $monthEnd);

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

        $this->info("Monthly badges awarded: {$badgesAwarded} badges");
        
        // Update last run time
        \App\Http\Controllers\Api\V1\OperationsController::updateLastRun('badges:monthly');
        
        return Command::SUCCESS;
    }

    /**
     * Get students eligible for badge
     */
    private function getEligibleStudents(Badge $badge, Carbon $monthStart, Carbon $monthEnd)
    {
        $criteria = $badge->criteria ?? [];
        
        // Get students with required points in the month
        if (isset($criteria['min_points'])) {
            return User::where('role', 'student')
                ->where('status', 'active')
                ->whereHas('gamificationPoints', function ($query) use ($monthStart, $monthEnd, $criteria) {
                    $query->whereBetween('awarded_at', [$monthStart, $monthEnd])
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