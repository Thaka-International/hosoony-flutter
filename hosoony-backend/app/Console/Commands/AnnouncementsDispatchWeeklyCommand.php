<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AnnouncementsDispatchWeeklyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'announcements:dispatch-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch weekly announcements on Sunday at 08:00';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $weeklyConfig = config('quran_lms.reminders.times.weekly_at', ['weekday' => 'Sun', 'time' => '08:00']);
        $targetWeekday = $weeklyConfig['weekday'] ?? 'Sun';
        $targetTime = $weeklyConfig['time'] ?? '08:00';

        $currentWeekday = now()->format('D');
        $currentTime = now()->format('H:i');

        // Only run on the configured weekday and time
        if ($currentWeekday !== $targetWeekday || $currentTime !== $targetTime) {
            $this->info("Skipping weekly announcements dispatch. Current: {$currentWeekday} {$currentTime}, configured: {$targetWeekday} {$targetTime}");
            return Command::SUCCESS;
        }

        // Get pending weekly announcements
        $announcements = Announcement::where('status', 'pending')
            ->where('target_audience', 'weekly')
            ->whereNull('sent_at')
            ->get();

        $notificationsSent = 0;

        foreach ($announcements as $announcement) {
            $users = $this->getTargetUsers($announcement);

            foreach ($users as $user) {
                $this->sendNotification(
                    $user,
                    $announcement->title,
                    $announcement->content,
                    'announcement',
                    ['announcement_id' => $announcement->id]
                );
                $notificationsSent++;
            }

            // Mark announcement as sent
            $announcement->update([
                'sent_at' => now(),
                'status' => 'sent',
            ]);
        }

        $this->info("Weekly announcements dispatched: {$notificationsSent} notifications for {$announcements->count()} announcements");
        
        // Update last run time
        \App\Http\Controllers\Api\V1\OperationsController::updateLastRun('announcements:dispatch-weekly');
        
        return Command::SUCCESS;
    }

    /**
     * Get target users for announcement
     */
    private function getTargetUsers(Announcement $announcement)
    {
        if ($announcement->target_class_id) {
            return User::where('class_id', $announcement->target_class_id)
                ->where('status', 'active')
                ->get();
        }

        return User::where('status', 'active')->get();
    }

    /**
     * Send notification to user
     */
    private function sendNotification(User $user, string $title, string $message, string $type, array $data = []): void
    {
        Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'channel' => 'push',
            'data' => $data,
            'sent_at' => now(),
            'status' => 'sent',
        ]);
    }
}